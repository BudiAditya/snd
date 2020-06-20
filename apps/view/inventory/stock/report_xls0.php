<?php
$phpExcel = new PHPExcel();
$headers = array(
    'Content-Type: application/vnd.ms-excel'
, 'Content-Disposition: attachment;filename="print-rekap-stock.xls"'
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);
// Excel MetaData
$phpExcel->getProperties()->setCreator("Rekasystem Infotama Inc (c) Budi Aditya")->setTitle("Print Laporan")->setCompany("Rekasystem Infotama Inc");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Rekapitulasi Stock Barang");
//helper for styling
$center = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
$right = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT));
$allBorders = array("borders" => array("allborders" => array("style" => PHPExcel_Style_Border::BORDER_THIN)));
$idrFormat = array("numberformat" => array("code" => '_([$-421]* #,##0_);_([$-421]* (#,##0);_([$-421]* "-"??_);_(@_)'));
$row = 1;
$ket = null;
if ($userJenisProduk != '-'){
    $ket.= ' - Jenis Barang : '.$userJenisBarang;
}
$sheet->setCellValue("A$row",$company_name);
// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);
$row++;
$sheet->setCellValue("A$row","REKAPITULASI STOCK BARANG");
$row++;
$sheet->setCellValue("A$row",$ket);
$row++;
$sheet->setCellValue("A$row","No.");
$sheet->setCellValue("B$row","Gudang");
$sheet->setCellValue("C$row","Kode");
$sheet->setCellValue("D$row","Nama Barang");
$sheet->setCellValue("E$row","Satuan");
$sheet->setCellValue("F$row","Qty Stock");
if ($userTypeHarga == 0){
    $sheet->getStyle("A$row:F$row")->applyFromArray(array_merge($center, $allBorders));
}elseif ($userTypeHarga == 1){
    $sheet->setCellValue("G$row","Harga Beli");
    $sheet->setCellValue("H$row","Nilai Stock");
    $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($center, $allBorders));
}else{
    $sheet->setCellValue("G$row","Harga Jual");
    $sheet->setCellValue("H$row","Nilai Stock");
    $sheet->getStyle("A$row:H")->applyFromArray(array_merge($center, $allBorders));
}
$nmr = 0;
$str = $row;
if ($reports != null){
    while ($rpt = $reports->FetchAssoc()) {
        $row++;
        $nmr++;
        $sheet->setCellValue("A$row",$nmr);
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("B$row",$rpt["wh_code"],PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue("C$row",$rpt["item_code"],PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue("D$row",$rpt["bnama"]);
        $sheet->setCellValue("E$row",$rpt["bsatbesar"]);
        $sheet->setCellValue("F$row",$rpt["qty_stock"]);
        if ($userTypeHarga == 0){
            $sheet->getStyle("A$row:F$row")->applyFromArray(array_merge($allBorders));
        }elseif ($userTypeHarga == 1){
            $sheet->setCellValue("G$row",$rpt["hrg_beli"]);
            $sheet->setCellValue("H$row","=Round(E$row*F$row,0)");
            $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($allBorders));
        }else{
            $sheet->setCellValue("G$row",$rpt["hrg_jual"]);
            $sheet->setCellValue("H$row","=Round(E$row*F$row,0)");
            $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($allBorders));
        }
    }

    if ($userTypeHarga > 0){
        $edr = $row;
        $row++;
        $sheet->setCellValue("A$row","TOTAL NILAI STOCK");
        $sheet->mergeCells("A$row:G$row");
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("H$row","=SUM(H$str:H$edr)");
        $sheet->getStyle("F$str:H$row")->applyFromArray($idrFormat);
        $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($allBorders));
        $row++;
    }
}
// Flush to client

// comment fo debugging
foreach ($headers as $header) {
    header($header);
}

// Hack agar client menutup loading dialog box... (Ada JS yang checking cookie ini pada common.js)
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();
