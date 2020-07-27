<?php
$phpExcel = new PHPExcel();
$headers = array(
  'Content-Type: application/vnd.ms-excel'
, 'Content-Disposition: attachment;filename="rekap-faktur-pajak-keluaran.xls"'
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);
// Excel MetaData
$phpExcel->getProperties()->setCreator("Rekasystem Infotama Inc (c) Budi Aditya")->setTitle("Print Laporan")->setCompany("Rekasystem Infotama Inc");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Rekapitulasi AR Invoice");
//helper for styling
$center = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
$right = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT));
$allBorders = array("borders" => array("allborders" => array("style" => PHPExcel_Style_Border::BORDER_THIN)));
$idrFormat = array("numberformat" => array("code" => '_([$-421]* #,##0_);_([$-421]* (#,##0);_([$-421]* "-"??_);_(@_)'));
// OK mari kita bikin ini cuma bisa di read-only
//$password = "" . time();
//$sheet->getProtection()->setSheet(true);
//$sheet->getProtection()->setPassword($password);

// FORCE Custom Margin for continous form
/*
$sheet->getPageMargins()->setTop(0)
    ->setRight(0.2)
    ->setBottom(0)
    ->setLeft(0.2)
    ->setHeader(0)
    ->setFooter(0);
*/
$row = 1;
$sheet->setCellValue("A$row",$company_name);
// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);
$row++;
$sheet->setCellValue("A$row", "REKAPITULASI A/R INVOICE");
$row++;
$sheet->setCellValue("A$row", "Dari Tgl. " . date('d-m-Y', $StartDate) . " - " . date('d-m-Y', $EndDate));
$row++;
$sheet->setCellValue("A$row", "No.");
$sheet->setCellValue("B$row", "Cabang");
$sheet->setCellValue("C$row", "Tanggal");
$sheet->setCellValue("D$row", "No. Invoice");
$sheet->setCellValue("E$row", "Customer");
$sheet->setCellValue("F$row", "Keterangan");
$sheet->setCellValue("G$row", "Salesman");
$sheet->setCellValue("H$row", "JTP");
$sheet->setCellValue("I$row", "DPP");
$sheet->setCellValue("J$row", "PPN");
$sheet->setCellValue("K$row", "Jumlah");
$sheet->setCellValue("L$row", "Terbayar");
$sheet->setCellValue("M$row", "Outstanding");
$sheet->getStyle("A$row:M$row")->applyFromArray(array_merge($center, $allBorders));
$nmr = 0;
$str = $row;
if ($dfaktur != null) {
    $ivn = null;
    $sma = false;
    $tTotal = 0;
    $tPaid = 0;
    $tBalance = 0;
    while ($rpt = $dfaktur->FetchAssoc()) {
        $row++;
        $sheet->setCellValue("A$row", $nmr);
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("B$row", $rpt["cabang_code"]);
        $sheet->setCellValue("C$row", date('d-m-Y', strtotime($rpt["invoice_date"])));
        $sheet->setCellValue("D$row", $rpt["invoice_no"]);
        $sheet->setCellValue("E$row", $rpt["customer_name"]);
        $sheet->setCellValue("F$row", $rpt["invoice_descs"]);
        $sheet->setCellValue("G$row", $rpt["sales_name"]);
        $sheet->setCellValue("H$row", date('d-m-Y', strtotime($rpt["due_date"])));
        $sheet->setCellValue("I$row", $rpt["base_amount"]-$rpt["disc_amount"]);
        $sheet->setCellValue("J$row", $rpt["ppn_amount"]);
        $sheet->setCellValue("K$row", $rpt["total_amount"]);
        $sheet->setCellValue("L$row", $rpt["paid_amount"]);
        $sheet->setCellValue("M$row", $rpt["balance_amount"]);
        $sheet->getStyle("A$row:M$row")->applyFromArray(array_merge($allBorders));
    }
    $row++;
}

// Flush to client

foreach ($headers as $header) {
    header($header);
}
// Hack agar client menutup loading dialog box... (Ada JS yang checking cookie ini pada common.js)
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();
