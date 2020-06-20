<?php
/** @var $company Company */ /** @var $monthNames string[] */ /** @var $parentAccounts CoaGroup[] */ /** @var $kodeInduk int */
/** @var $cabangList Cabang[] */ /** @var $cabangId int */
/** @var $month int */ /** @var $year int */ /** @var int $status */ /** @var string $statusName */
/** @var $report null|ReaderBase */

$selectedCabang = null;
$strCabang = null;
foreach ($cabangList as $cabang) {
    if ($cabang->Id == $idCabang) {
        $selectedCabang = $cabang;
        $strCabang = " (Cabang: ".$cabang->Kode." - ".$cabang->Cabang.")";
        break;
    }
}

$phpExcel = new PHPExcel();
$phpExcel->getProperties()->setCreator("Rekasys System (c) Budi Aditya")->setTitle("CostRevenue")->setCompany("Rekasystem Infotama Inc");

$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Rekapitulasi Cost-Revenue");

$sheet->setCellValue("A1", sprintf("%s - %s", $company->EntityCd, $company->CompanyName.$strCabang));
$sheet->mergeCells("A1:F1");
$sheet->getStyle("A1")->applyFromArray(array(
    "font" => array("bold" => true, "size" => 18)
));
if($kodeInduk == "4"){
   $sheet->setCellValue("A2", "Laporan Pendapatan");
}else{
   $sheet->setCellValue("A2", "Laporan Biaya-biaya");
}

$sheet->mergeCells("A2:F2");
$sheet->setCellValue("A3", sprintf("Periode: %s %s", $monthNames[$month], $year));
$sheet->mergeCells("A3:F3");
$sheet->setCellValue("A4", sprintf("(Voucher Status: %s)", $statusName));
$sheet->mergeCells("A4:F4");
$sheet->getStyle("A2:A4")->applyFromArray(array(
    "font" => array("size" => 12)
));

// Column Header
$sheet->setCellValue("A6", "Akun.");
$sheet->setCellValue("B6", "Nama Akun");
$sheet->setCellValue("C6", "S/d Bulan lalu");
$sheet->setCellValue("D6", sprintf("Mutasi %s %s", $monthNames[$month], $year));
$sheet->setCellValue("D7", "Debet");
$sheet->setCellValue("E7", "Kredit");
$sheet->setCellValue("F6", "S/d Bulan ini");
$sheet->mergeCells("A6:A7");
$sheet->mergeCells("B6:B7");
$sheet->mergeCells("C6:C7");
$sheet->mergeCells("D6:E6");
$sheet->mergeCells("F6:F7");
$sheet->getStyle("A6:F7")->applyFromArray(array(
    "font" => array("bold" => true),
    "alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,"vertical" => PHPExcel_Style_Alignment::VERTICAL_CENTER),
    "borders" => array(
        "top" => array("style" => PHPExcel_Style_Border::BORDER_DOUBLE),
        "bottom" => array("style" => PHPExcel_Style_Border::BORDER_THIN)
    )
));
$sheet->getStyle("D6:E6")->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
for ($i = 0; $i < 6; $i++) {
    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Tulis datanya...
$sumDebit = 0;
$sumCredit = 0;
$sumPrevSaldo = 0;
$sumSaldo = 0;
$startDate = mktime(0, 0, 0, $month, 1, $year);
$endDate = mktime(0, 0, 0, $month + 1, 0, $year);
$brs = 7;
while($row = $report->FetchAssoc()) {
    $brs++;
    $posisiSaldo = $row["psaldo"];
    $sumDebit += $row["total_debit"];
    $sumCredit += $row["total_credit"];
    if ($posisiSaldo == "DK") {
        $prevSaldo = ($row["bal_debit_amt"] - $row["bal_credit_amt"]) + ($row["total_debit_prev"] - $row["total_credit_prev"]);
        $saldo = $row["total_debit"] - $row["total_credit"];
    } else  if($posisiSaldo == "KD") {
        $prevSaldo = ($row["bal_credit_amt"] - $row["bal_debit_amt"]) + ($row["total_credit_prev"] - $row["total_debit_prev"]);
        $saldo = $row["total_credit"] - $row["total_debit"];
    } else {
        throw new Exception("Invalid posisi_saldo! CODE: " . $posisiSaldo);
    }
    $sumPrevSaldo += $prevSaldo;
    $sumSaldo += $prevSaldo + $saldo;
    if($prevSaldo + $saldo <> 0){
       $sheet->setCellValue("A$brs",$row["acc_no"]);
       $sheet->setCellValue("B$brs",$row["acc_name"]);
       $sheet->setCellValue("C$brs",$prevSaldo);
       $sheet->setCellValue("D$brs",$row["total_debit"]);
       $sheet->setCellValue("E$brs",$row["total_credit"]);
       $sheet->setCellValue("F$brs",$prevSaldo + $saldo);
    }else{
       $brs--;
    }
}
// Grand Total..
$brs++;
$sheet->mergeCells("A$brs:B$brs");
$sheet->setCellValue("A$brs","TOTAL :");
$sheet->setCellValue("C$brs","=SUM(C8:C" . ($brs - 1) . ")");
$sheet->setCellValue("D$brs","=SUM(D8:D" . ($brs - 1) . ")");
$sheet->setCellValue("E$brs","=SUM(E8:E" . ($brs - 1) . ")");
$sheet->setCellValue("F$brs","=SUM(F8:F" . ($brs - 1) . ")");
$sheet->getStyle("A$brs:F$brs")->applyFromArray(array(
    "font" => array("bold" => true),
    "alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,"vertical" => PHPExcel_Style_Alignment::VERTICAL_CENTER),
    "borders" => array(
        "top" => array("style" => PHPExcel_Style_Border::BORDER_THIN),
        "bottom" => array("style" => PHPExcel_Style_Border::BORDER_THIN)
    )
));
// Border Styling
$sheet->getStyle("A6:A$brs")->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("A6:A$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("B6:B$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("C6:C$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("D6:D$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("E6:E$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("F6:F$brs")->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$sheet->getStyle("C8:F$brs")->getNumberFormat()->setFormatCode('#,##0.00');

// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);


// Sent header
header('Content-Type: application/vnd.ms-excel');
header(sprintf('Content-Disposition: attachment;filename="costrevenue-%s.xls"', $kodeInduk));
header('Cache-Control: max-age=0');

// Write to php output
$writer = new PHPExcel_Writer_Excel5($phpExcel);
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();

// End of File: recap.excel.php
