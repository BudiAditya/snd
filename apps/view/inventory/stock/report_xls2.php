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
if ($scabangCode != null){
    $ket.= 'Cabang/Gudang: '.$scabangCode;
}else{
    $ket.= 'Semua Cabang/Gudang';
}
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
$sheet->setCellValue("B$row","Kode");
$sheet->setCellValue("C$row","Nama Barang");
$sheet->setCellValue("D$row","Satuan");
$sheet->setCellValue("E$row","Qty Stock");
if ($userTypeHarga == 0){
    $sheet->setCellValue("F$row","SO Qty");
    $sheet->setCellValue("G$row","Stock - SO");
    $sheet->getStyle("A$row:G$row")->applyFromArray(array_merge($center, $allBorders));
}elseif ($userTypeHarga == 1){
    $sheet->setCellValue("F$row","Harga Beli");
    $sheet->setCellValue("G$row","Nilai Stock");
    $sheet->setCellValue("H$row","SO Qty");
    $sheet->setCellValue("I$row","Stock - SO");
    $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($center, $allBorders));
}else{
    $sheet->setCellValue("F$row","Harga Jual");
    $sheet->setCellValue("G$row","Nilai Stock");
    $sheet->setCellValue("H$row","SO Qty");
    $sheet->setCellValue("I$row","Stock - SO");
    $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($center, $allBorders));
}
$nmr = 0;
$str = $row;
if ($reports != null){
    while ($rpt = $reports->FetchAssoc()) {
        $row++;
        $nmr++;
        $sheet->setCellValue("A$row",$nmr);
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("B$row",$rpt["item_code"]);
        $sheet->setCellValue("C$row",$rpt["bnama"]);
        $sheet->setCellValue("D$row",$rpt["bsatbesar"]);
        $sheet->setCellValue("E$row",$rpt["qty_stock"]);
        if ($userTypeHarga == 0){
            $sheet->setCellValue("F$row",$rpt["so_qty"]);
            $sheet->setCellValue("G$row","=E$row-F$row");
            $sheet->getStyle("A$row:G$row")->applyFromArray(array_merge($allBorders));
        }elseif ($userTypeHarga == 1){
            $sheet->setCellValue("F$row",$rpt["hrg_beli"]);
            $sheet->setCellValue("G$row","=Round(E$row*F$row,0)");
            $sheet->setCellValue("H$row",$rpt["so_qty"]);
            $sheet->setCellValue("I$row","=E$row-H$row");
            $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($allBorders));
        }else{
            $sheet->setCellValue("F$row",$rpt["hrg_jual"]);
            $sheet->setCellValue("G$row","=Round(E$row*F$row,0)");
            $sheet->setCellValue("H$row",$rpt["so_qty"]);
            $sheet->setCellValue("I$row","=E$row-H$row");
            $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($allBorders));
        }
    }

    if ($userTypeHarga > 0){
        $edr = $row;
        $row++;
        $sheet->setCellValue("A$row","TOTAL NILAI STOCK");
        $sheet->mergeCells("A$row:F$row");
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("G$row","=SUM(G$str:G$edr)");
        $sheet->getStyle("E$str:I$row")->applyFromArray($idrFormat);
        $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($allBorders));
        $row++;
    }else{
        $sheet->getStyle("E$str:G$row")->applyFromArray($idrFormat);
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
