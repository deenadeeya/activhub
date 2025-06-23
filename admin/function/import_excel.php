<?php
require_once '../../includes/session_check.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Database Connection
require_once __DIR__ . '/../../config/connect.php';
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// 2. Check for ZipArchive extension
if (!class_exists('ZipArchive')) {
    $_SESSION['import_result'] = [
        'success' => 0,
        'invalid' => 0,
        'duplicate' => 0,
        'fail' => 1,
        'errors' => [
            "ZipArchive extension is not enabled on your server.",
            "To fix this:",
            "1. Open XAMPP Control Panel",
            "2. Click 'Config' next to Apache",
            "3. Select 'PHP (php.ini)'",
            "4. Find ';extension=zip' and remove the semicolon",
            "5. Save and restart Apache",
            "Alternative: Use CSV import instead"
        ]
    ];
    header("Location: ../admin_studentList.php");
    exit();
}

// 3. PhpSpreadsheet Setup
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == UPLOAD_ERR_OK) {
    try {
        // Check file type
        $fileExtension = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, ['xlsx', 'xls'])) {
            throw new Exception("Invalid file type. Please upload an Excel file (.xlsx or .xls)");
        }

        // 3. Load Excel File
        $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // 4. Normalize Headers
        $normalizeHeader = function ($header) {
            $header = trim($header);
            $header = preg_replace('/[^\p{L}\p{N}\s]/u', '', $header);
            $header = preg_replace('/\s+/u', ' ', $header);
            return mb_strtoupper($header);
        };

        $headerRow = array_map($normalizeHeader, $rows[0] ?? []);
        $expectedHeaders = array_map(
            $normalizeHeader,
            ['NAMA MURID', 'MATRIK', 'JANTINA', 'KELAS', 'NO IC']
        );

        // 5. Header Validation
        if ($headerRow !== $expectedHeaders) {
            throw new Exception("Invalid header format. Expected:\n" .
                implode(' | ', $expectedHeaders) . "\n\nReceived:\n" .
                implode(' | ', $headerRow));
        }

        // 6. Process Student Data
        $results = [
            'success' => 0,
            'fail' => 0,
            'duplicate' => 0,
            'invalid' => 0,
            'errors' => []
        ];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Skip empty rows
            if (count(array_filter($row, function ($v) {
                return $v !== null && $v !== '';
            })) == 0) {
                continue;
            }            // 7. Map and Validate Data
            $data = [
                'name' => trim($row[0] ?? ''),
                'matrix' => trim($row[1] ?? ''),
                'gender' => strtoupper(substr(trim($row[2] ?? ''), 0, 1)),
                'class' => trim($row[3] ?? ''),
                'ic' => preg_replace('/[^0-9]/', '', trim($row[4] ?? ''))
            ];

            // 8. Validate Fields
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
                $results['errors'][] = "Row " . ($i + 1) . ": " . implode(", ", $errors);
                continue;
            }

            // 9. Check Class Exists
            $class_stmt = $conn->prepare("SELECT class_id FROM class WHERE class_name = ?");
            $class_stmt->bind_param("s", $data['class']);
            if (!$class_stmt->execute()) {
                $results['fail']++;
                $results['errors'][] = "Row " . ($i + 1) . ": Database error - " . $class_stmt->error;
                continue;
            }

            $class_result = $class_stmt->get_result();
            if ($class_result->num_rows == 0) {
                $results['fail']++;
                $results['errors'][] = "Row " . ($i + 1) . ": Class not found";
                continue;
            }
            $class_id = $class_result->fetch_assoc()['class_id'];

            // 10. Check Duplicate IC
            $check_stmt = $conn->prepare("SELECT student_ic FROM student WHERE student_ic = ?");
            $check_stmt->bind_param("s", $data['ic']);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                $results['duplicate']++;
                continue;
            }

            // 11. Insert Student (without temp_password)
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
                $results['errors'][] = "Row " . ($i + 1) . ": Insert failed - " . $insert_stmt->error;
            }
        }        // 12. Prepare Result Message
        $_SESSION['import_result'] = [
            'success' => $results['success'],
            'invalid' => $results['invalid'],
            'duplicate' => $results['duplicate'],
            'fail' => $results['fail'],
            'errors' => array_slice($results['errors'], 0, 10) // Show first 10 errors
        ];
    } catch (Exception $e) {
        $_SESSION['import_result'] = [
            'success' => 0,
            'invalid' => 0,
            'duplicate' => 0,
            'fail' => 1,
            'errors' => ["IMPORT ERROR: " . $e->getMessage()]
        ];
    }header("Location: ../admin_studentList.php");
    exit();
} else {
    // Handle file upload errors
    $error_message = "Error: ";
    if (!isset($_FILES['excel_file'])) {
        $error_message .= "No file uploaded";
    } else {
        switch($_FILES['excel_file']['error']) {
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
    
    $_SESSION['import_result'] = $error_message;
    header("Location: ../admin_studentList.php");
    exit();
}
