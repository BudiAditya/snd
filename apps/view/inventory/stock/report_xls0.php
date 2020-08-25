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
$sheet->setCellValue("F$row","Q");
$sheet->setCellValue("G$row","L");
$sheet->setCellValue("H$row","S");
$sheet->setCellValue("I$row","C");
$sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($center, $allBorders));
$nmr = 0;
$str = $row;
if ($reports != null){
    while ($rpt = $reports->FetchAssoc()) {
        $row++;
        $nmr++;
        $sheet->setCellValue("A$row",$nmr);
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValueExplicit("B$row",$rpt["wh_code"],PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("C$row",$rpt["item_code"],PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue("D$row",$rpt["item_name"]);
        $sheet->setCellValue("E$row",$rpt["s_uom_code"]);
        $sheet->setCellValue("F$row",$rpt["qty_stock"]);
        $sld = $rpt["qty_stock"];
        if ($sld >= $rpt["s_uom_qty"] && $rpt["s_uom_qty"] > 0){
            $aqty = array();
            $sqty = round($sld/$rpt["s_uom_qty"],2);
            $aqty = explode('.',$sqty);
            $lqty = $aqty[0];
            $sqty = $sld - ($lqty * $rpt["s_uom_qty"]);
        }else {
            $lqty = 0;
            $sqty = $sld;
        }
        if ($rpt["entity_id"] == 1){
            $cqty = round($sld * $rpt["qty_convert"],2);
        }else{
            $cqty = 0;
        }
        $sheet->setCellValue("G$row",$lqty);
        $sheet->setCellValue("H$row",$sqty);
        $sheet->setCellValue("I$row",$cqty);
        $sheet->getStyle("F$row:I$row")->applyFromArray($idrFormat);
        $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($allBorders));
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
