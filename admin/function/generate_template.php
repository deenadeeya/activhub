<?php
require_once __DIR__ . '/../../connect.php';
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers with correct format
$headers = ['NAMA MURID', 'MATRIK', 'JANTINA', 'KELAS', 'NO IC'];
$sheet->fromArray($headers, null, 'A1');

// Set column widths
$sheet->getColumnDimension('A')->setWidth(25); // NAMA MURID
$sheet->getColumnDimension('B')->setWidth(15); // MATRIK
$sheet->getColumnDimension('C')->setWidth(10); // JANTINA
$sheet->getColumnDimension('D')->setWidth(15); // KELAS
$sheet->getColumnDimension('E')->setWidth(15); // NO IC

// Add data validation for gender
$validation = $sheet->getCell('C2')->getDataValidation();
$validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
$validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
$validation->setAllowBlank(false);
$validation->setShowInputMessage(true);
$validation->setShowErrorMessage(true);
$validation->setShowDropDown(true);
$validation->setErrorTitle('Input error');
$validation->setError('Value is not in list');
$validation->setPromptTitle('Pick from list');
$validation->setPrompt('Please pick a value from the drop-down list');
$validation->setFormula1('"Lelaki,Perempuan,Male,Female"');

// Create Excel file
$writer = new Xlsx($spreadsheet);

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="student_import_template.xlsx"');
header('Cache-Control: max-age=0');

// Save to browser
$writer->save('php://output');
exit;
