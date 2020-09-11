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
$ket = "Gudang : ".$whName;
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
$sheet->setCellValue("E$row","Q");
$sheet->setCellValue("F$row","L");
$sheet->setCellValue("G$row","S");
$sheet->setCellValue("H$row","C");
$sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($center, $allBorders));
$nmr = 0;
$str = $row;
if ($reports != null){
    while ($rpt = $reports->FetchAssoc()) {
        $row++;
        $nmr++;
        $sheet->setCellValue("A$row",$nmr);
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValueExplicit("B$row",$rpt["item_code"],PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue("C$row",$rpt["item_name"]);
        $sheet->setCellValue("D$row",$rpt["s_uom_code"]);
        $sheet->setCellValue("E$row",$rpt["qty_stock"]);
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
        $sheet->setCellValue("F$row",$lqty);
        $sheet->setCellValue("G$row",$sqty);
        $sheet->setCellValue("H$row",$cqty);
        $sheet->getStyle("F$row:H$row")->applyFromArray($idrFormat);
        $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($allBorders));
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
