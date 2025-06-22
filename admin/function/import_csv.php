<?php
require_once '../../includes/session_check.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
require_once __DIR__ . '/../../config/connect.php';
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
    try {
        // Check file type
        $fileExtension = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
        if ($fileExtension !== 'csv') {
            throw new Exception("Invalid file type. Please upload a CSV file (.csv)");
        }

        // Read CSV file
        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$file) {
            throw new Exception("Could not open the uploaded file");
        }

        // Read header row
        $headers = fgetcsv($file);
        if (!$headers) {
            throw new Exception("Could not read CSV headers");
        }

        // Normalize headers
        $normalizeHeader = function ($header) {
            $header = trim($header);
            $header = preg_replace('/[^\p{L}\p{N}\s]/u', '', $header);
            $header = preg_replace('/\s+/u', ' ', $header);
            return mb_strtoupper($header);
        };

        $headerRow = array_map($normalizeHeader, $headers);
        $expectedHeaders = array_map(
            $normalizeHeader,
            ['NAMA MURID', 'MATRIK', 'JANTINA', 'KELAS', 'NO IC']
        );

        // Header validation
        if ($headerRow !== $expectedHeaders) {
            throw new Exception("Invalid header format. Expected:\n" .
                implode(' | ', $expectedHeaders) . "\n\nReceived:\n" .
                implode(' | ', $headerRow));
        }

        // Process student data
        $results = [
            'success' => 0,
            'fail' => 0,
            'duplicate' => 0,
            'invalid' => 0,
            'errors' => []
        ];

        $rowNumber = 1;
        while (($row = fgetcsv($file)) !== false) {
            $rowNumber++;
            
            // Skip empty rows
            if (count(array_filter($row, function ($v) {
                return $v !== null && $v !== '';
            })) == 0) {
                continue;
            }

            // Map and validate data
            $data = [
                'name' => trim($row[0] ?? ''),
                'matrix' => trim($row[1] ?? ''),
                'gender' => strtoupper(substr(trim($row[2] ?? ''), 0, 1)),
                'class' => trim($row[3] ?? ''),
                'ic' => preg_replace('/[^0-9]/', '', trim($row[4] ?? ''))
            ];

            // Validate fields
            $errors = [];
            foreach ($data as $key => $value) {
                if (empty($value)) {
                    $errors[] = "Missing $key";
                }
            }

            if (!preg_match('/^\d{10,12}$/', $data['ic'])) {
                $errors[] = "Invalid IC format";
            }

            if (!in_array($data['gender'], ['M', 'L', 'F', 'P'])) {
                $errors[] = "Invalid gender (must be M/L for Male, F/P for Female)";
            }

            if (!empty($errors)) {
                $results['invalid']++;
                $results['errors'][] = "Row $rowNumber: " . implode(", ", $errors);
                continue;
            }

            // Check class exists
            $class_stmt = $conn->prepare("SELECT class_id FROM class WHERE class_name = ?");
            $class_stmt->bind_param("s", $data['class']);
            if (!$class_stmt->execute()) {
                $results['fail']++;
                $results['errors'][] = "Row $rowNumber: Database error - " . $class_stmt->error;
                continue;
            }

            $class_result = $class_stmt->get_result();
            if ($class_result->num_rows == 0) {
                $results['fail']++;
                $results['errors'][] = "Row $rowNumber: Class '{$data['class']}' not found";
                continue;
            }
            $class_id = $class_result->fetch_assoc()['class_id'];

            // Check duplicate IC
            $check_stmt = $conn->prepare("SELECT student_ic FROM student WHERE student_ic = ?");
            $check_stmt->bind_param("s", $data['ic']);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                $results['duplicate']++;
                continue;
            }

            // Insert student
            $password = password_hash($data['ic'], PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO student 
                (student_ic, matrix, student_fname, student_pass, student_class, gender) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param(
                "ssssis",
                $data['ic'],
                $data['matrix'],
                $data['name'],
                $password,
                $class_id,
                $data['gender']
            );

            if ($insert_stmt->execute()) {
                $results['success']++;
            } else {
                $results['fail']++;
                $results['errors'][] = "Row $rowNumber: Insert failed - " . $insert_stmt->error;
            }
        }

        fclose($file);

        // Prepare result message
        $_SESSION['import_result'] = [
            'success' => $results['success'],
            'invalid' => $results['invalid'],
            'duplicate' => $results['duplicate'],
            'fail' => $results['fail'],
            'errors' => array_slice($results['errors'], 0, 10)
        ];

    } catch (Exception $e) {
        $_SESSION['import_result'] = [
            'success' => 0,
            'invalid' => 0,
            'duplicate' => 0,
            'fail' => 1,
            'errors' => ["IMPORT ERROR: " . $e->getMessage()]
        ];
    }

    header("Location: ../admin_studentList.php");
    exit();
} else {
    // Handle file upload errors
    $error_message = "Error: ";
    if (!isset($_FILES['csv_file'])) {
        $error_message .= "No file uploaded";
    } else {
        switch($_FILES['csv_file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message .= "File too large";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message .= "File upload incomplete";
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message .= "No file selected";
                break;
            default:
                $error_message .= "File upload failed";
        }
    }
    
    $_SESSION['import_result'] = [
        'success' => 0,
        'invalid' => 0,
        'duplicate' => 0,
        'fail' => 1,
        'errors' => [$error_message]
    ];
    header("Location: ../admin_studentList.php");
    exit();
}
?>
