<?php
require_once(LIBRARY . "PHPExcel.php");
ini_set('memory_limit', '2048M');
// Ini file pure akan membuat file excel dan tidak ada HTML fragment
$reader = new PHPExcel_Reader_Excel2007(); // Report Template
$phpExcel = $reader->load(APPS . "templates/sales-report.xlsx");
$filename = "sales-report-".$year.".xlsx";
$headers = array(
    'Content-Type: application/vnd.ms-excel'
, sprintf('Content-Disposition: attachment;filename="%s"', $filename)
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel2007($phpExcel);

// Tulis data master items
$sheet = $phpExcel->setActiveSheetIndexByName("D.BASE");
$brs = 2;
if ($dItems <> null) {
    while ($data = $dItems->FetchAssoc()) {
        $brs++;
        $sheet->setCellValueExplicit("A" . $brs, $data["item_code"],PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue("B" . $brs, $data["item_name"]);
        $sheet->setCellValue("C" . $brs, $data["old_code"]);
        $sheet->setCellValue("D" . $brs, $data["brand_name"]);
        $sheet->setCellValue("E" . $brs, $data["brand_name"]);
        $sheet->setCellValue("F" . $brs, $data["s_uom_qty"]);
        $sheet->setCellValue("G" . $brs, $data["principal_name"]);
    }
}

// Hmm Reset Pointer
$sheet = $phpExcel->setActiveSheetIndexByName("OMSET BY CABANG");
$sheet->getStyle("A1");

// Flush to client

foreach ($headers as $header) {
    header($header);
}

// Hack agar client menutup loading dialog box... (Ada JS yang checking cookie ini pada common.js)
setcookie("startDownload", 1);
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();
exit();
