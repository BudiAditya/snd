<?php
$phpExcel = new PHPExcel();
$headers = array(
    'Content-Type: application/vnd.ms-excel'
, 'Content-Disposition: attachment;filename="print-kartu-stock.xls"'
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);
// Excel MetaData
$phpExcel->getProperties()->setCreator("Rekasystem Infotama Inc (c) Budi Aditya")->setTitle("Print Laporan")->setCompany("Rekasystem Infotama Inc");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Kartu Stock Barang");
//helper for styling
$center = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
$right = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT));
$allBorders = array("borders" => array("allborders" => array("style" => PHPExcel_Style_Border::BORDER_THIN)));
$idrFormat = array("numberformat" => array("code" => '_([$-421]* #,##0_);_([$-421]* (#,##0);_([$-421]* "-"??_);_(@_)'));
$row = 1;
$sheet->setCellValue("A$row",$company_name);
// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);
$row++;
$sheet->setCellValue("A$row","KARTU STOCK BARANG");
$row++;
$sheet->setCellValue("A$row","Cabang/Gudang: ".$stock->KdCabang.' - '.$stock->NmCabang);
$row++;
$sheet->setCellValue("A$row","Nama Barang: ".$stock->ItemName.' ('.$stock->ItemCode.')');
$row++;
//$sheet->setCellValue("A$row", "Dari Tgl. " . date('d-m-Y', $startDate) . " - " . date('d-m-Y', $endDate));
//$row++;
$sheet->setCellValue("A$row","No.");
$sheet->setCellValue("B$row","Tanggal");
$sheet->setCellValue("C$row","Transaksi");
$sheet->setCellValue("D$row","Relasi");
$sheet->setCellValue("E$row","Keterangan");
$sheet->setCellValue("F$row","Awal");
$sheet->setCellValue("G$row","Masuk");
$sheet->setCellValue("H$row","Keluar");
$sheet->setCellValue("I$row","Koreksi");
$sheet->setCellValue("J$row","Saldo");
$sheet->getStyle("A$row:J$row")->applyFromArray(array_merge($center, $allBorders));
$nmr = 0;
$str = $row;
if($stkcard != null) {
    $saldo = 0;
    while ($rpt = $stkcard->FetchAssoc()) {
        $row++;
        $nmr++;
        if ($nmr == 1){
            $saldo = $rpt["saldo"];
        }else{
            $saldo = ($saldo + $rpt["awal"] + $rpt["masuk"]) - $rpt["keluar"] + $rpt["koreksi"];
        }
        $sheet->setCellValue("A$row",$nmr);
        $sheet->setCellValue("B$row",$rpt["trx_date"]);
        $sheet->setCellValue("C$row",$rpt["trx_type"]);
        $sheet->setCellValue("D$row",$rpt["relasi"]);
        $sheet->setCellValue("E$row",$rpt["notes"]);
        $sheet->setCellValue("F$row",$rpt["awal"]);
        $sheet->setCellValue("G$row",$rpt["masuk"]);
        $sheet->setCellValue("H$row",$rpt["keluar"]);
        $sheet->setCellValue("I$row",$rpt["koreksi"]);
        $sheet->setCellValue("J$row",$saldo);
        $sheet->getStyle("A$row:J$row")->applyFromArray(array_merge($allBorders));
    }
    $sheet->getStyle("F$str:J$row")->applyFromArray($idrFormat);
}
// Flush to client
// comment for debugging
foreach ($headers as $header) {
    header($header);
}

// Hack agar client menutup loading dialog box... (Ada JS yang checking cookie ini pada common.js)
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();
