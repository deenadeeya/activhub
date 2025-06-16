<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Database Connection
require_once __DIR__ . '/../../connect.php';
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// 2. PhpSpreadsheet Setup
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excel_file']['tmp_name'])) {
    try {
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
            }

            // 7. Map and Validate Data
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
                $errors[] = "Invalid gender";
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
        }

        // 12. Prepare Result Message
        $_SESSION['import_result'] = [
            'success' => $results['success'],
            'invalid' => $results['invalid'],
            'duplicate' => $results['duplicate'],
            'fail' => $results['fail'],
            'errors' => array_slice($results['errors'], 0, 5) // Show first 5 errors
        ];
    } catch (Exception $e) {
        $_SESSION['import_result'] = "IMPORT ERROR:\n\n" . $e->getMessage();
    }

    header("Location: ../admin_studentList.php");
    exit();
} else {
    $_SESSION['import_result'] = "Error: No file uploaded";
    header("Location: ../admin_studentList.php");
    exit();
}
