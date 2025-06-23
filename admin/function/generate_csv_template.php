<?php
require_once __DIR__ . '/../../config/connect.php';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="student_import_template.csv"');
header('Cache-Control: no-cache, must-revalidate');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV header
$headers = ['NAMA MURID', 'MATRIK', 'JANTINA', 'KELAS', 'NO IC'];
fputcsv($output, $headers);

// Write sample data row
$sampleData = [
    'Muhammad Ahmad Bin Ali',
    'A123456',
    'L',
    '1 Bestari',
    '001122334455'
];
fputcsv($output, $sampleData);

// Write another sample for female student
$sampleData2 = [
    'Siti Nurhaliza Binti Hassan',
    'A123457',
    'P',
    '2 Bijak',
    '001122334456'
];
fputcsv($output, $sampleData2);

// Close output stream
fclose($output);
exit();
?>
