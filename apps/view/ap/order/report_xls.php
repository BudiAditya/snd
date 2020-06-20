<?php
$phpExcel = new PHPExcel();
$headers = array(
    'Content-Type: application/vnd.ms-excel'
, 'Content-Disposition: attachment;filename="print-rekap-ar-return.xls"'
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);
// Excel MetaData
$phpExcel->getProperties()->setCreator("Rekasystem Infotama Inc (c) Budi Aditya")->setTitle("Print Laporan")->setCompany("Rekasystem Infotama Inc");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Rekapitulasi Order Pembelian");
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
if ($JnsLaporan < 3) {
    $sheet->setCellValue("A$row","REKAPITULASI PURCHASE ORDER (PO)");
    $row++;
    $sheet->setCellValue("A$row","Dari Tgl. ".date('d-m-Y',$StartDate)." - ".date('d-m-Y',$EndDate));
    $row++;
    $sheet->setCellValue("A$row","No.");
    $sheet->setCellValue("B$row","Cabang");
    $sheet->setCellValue("C$row","Tanggal");
    $sheet->setCellValue("D$row","No. Bukti");
    $sheet->setCellValue("E$row","Nama Supplier");
    $sheet->setCellValue("F$row","Keterangan");
    $sheet->setCellValue("G$row","Nilai Order");
    $sheet->setCellValue("H$row","Status");
    if ($JnsLaporan == 2) {
        $sheet->setCellValue("I$row", 'Kode Barang');
        $sheet->setCellValue("J$row", 'Nama Barang');
        $sheet->setCellValue("K$row", 'Order');
        $sheet->setCellValue("L$row", 'Diterima');
        $sheet->setCellValue("M$row", 'Outstanding');
        $sheet->setCellValue("N$row", 'Harga');
        $sheet->setCellValue("O$row", 'Jumlah');
        $sheet->getStyle("A$row:O$row")->applyFromArray(array_merge($center, $allBorders));
    }else {
        $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($center, $allBorders));
    }
    $nmr = 0;
    $str = $row;
    if ($Reports != null){
        $sts = null;
        $csi = null;
        $ivn = null;
        $sma = false;
        while ($rpt = $Reports->FetchAssoc()) {
            $row++;
            if ($ivn <> $rpt["po_no"]) {
                $nmr++;
                $sma = false;
            } else {
                $sma = true;
            }
            if (!$sma) {
                $csi = $rpt["supplier_name"].' ('.$rpt["supplier_code"].')';
                $sheet->setCellValue("A$row",$nmr);
                $sheet->getStyle("A$row")->applyFromArray($center);
                $sheet->setCellValue("B$row",$rpt["cabang_code"]);
                $sheet->setCellValue("C$row",date('d-m-Y',strtotime($rpt["po_date"])));
                $sheet->setCellValue("D$row",$rpt["po_no"]);
                $sheet->setCellValue("E$row",$csi);
                $sheet->setCellValue("F$row",$rpt["po_descs"]);
                $sheet->setCellValue("G$row",$rpt["total_amount"]);
                if($rpt["po_status"]==1){
                    $sheet->setCellValue("H$row","Open");
                }else{
                    $sheet->setCellValue("H$row","Closed");
                }
                $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($allBorders));
            }
            if ($JnsLaporan == 2) {
                $sheet->setCellValueExplicit("I$row", $rpt['item_code'],PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValue("J$row", $rpt['item_descs']);
                $sheet->setCellValue("K$row", $rpt['order_qty']);
                $sheet->setCellValue("L$row", $rpt['receipt_qty']);
                $sheet->setCellValue("M$row", "=K$row-L$row");
                $sheet->setCellValue("N$row", $rpt['price']);
                $sheet->setCellValue("O$row", $rpt['sub_total']);
                $sheet->getStyle("I$row:O$row")->applyFromArray(array_merge($allBorders));
            }
            $ivn = $rpt["po_no"];
        }
        $edr = $row;
        $row++;
        $sheet->setCellValue("A$row","TOTAL ORDER");
        $sheet->mergeCells("A$row:F$row");
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("G$row","=SUM(G$str:G$edr)");
        $sheet->getStyle("G$str:G$row")->applyFromArray($idrFormat);
        if ($JnsLaporan == 2) {
            $sheet->mergeCells("H$row:J$row");
            $sheet->getStyle("A$row:O$row")->applyFromArray(array_merge($allBorders));
            $sheet->setCellValue("K$row","=SUM(K$str:K$edr)");
            $sheet->setCellValue("L$row","=SUM(L$str:L$edr)");
            $sheet->setCellValue("M$row","=SUM(M$str:M$edr)");
            $sheet->setCellValue("N$row","=SUM(N$str:N$edr)");
            $sheet->setCellValue("O$row","=SUM(O$str:O$edr)");
            $sheet->getStyle("K$str:O$row")->applyFromArray($idrFormat);
        }else{
            $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($allBorders));
        }
        $row++;
    }
}else{
    // rekap item yang terjual
    $sheet->setCellValue("A$row", "REKAPITULASI ITEM PURCHASE ORDER");
    $row++;
    $sheet->setCellValue("A$row", "Dari Tgl. " . date('d-m-Y', $StartDate) . " - " . date('d-m-Y', $EndDate));
    $row++;
    $sheet->setCellValue("A$row", "No.");
    $sheet->setCellValue("B$row", "Supplier");
    $sheet->setCellValue("C$row", "Kode Barang");
    $sheet->setCellValue("D$row", "Nama Barang");
    $sheet->setCellValue("E$row", "Satuan");
    $sheet->setCellValue("F$row", "Order");
    $sheet->setCellValue("G$row", "Diterima");
    $sheet->setCellValue("H$row", "Outstanding");
    $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($center, $allBorders));
    $nmr = 0;
    $str = $row;
    $csi = null;
    if ($Reports != null) {
        while ($rpt = $Reports->FetchAssoc()) {
            $csi = $rpt["supplier_name"].' ('.$rpt["supplier_code"].')';
            $row++;
            $nmr++;
            $sheet->setCellValue("A$row", $nmr);
            $sheet->setCellValue("B$row", $csi);
            $sheet->setCellValueExplicit("C$row", $rpt['item_code'],PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue("D$row", $rpt['item_descs']);
            $sheet->setCellValue("E$row", $rpt['satuan']);
            $sheet->setCellValue("F$row", $rpt['sum_orderqty']);
            $sheet->setCellValue("G$row", $rpt['sum_receiptqty']);
            $sheet->setCellValue("H$row", "=F$row-G$row");
            $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($allBorders));
        }
        $edr = $row;
        $row++;
        $sheet->setCellValue("A$row", "T O T A L");
        $sheet->mergeCells("A$row:E$row");
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("F$row","=SUM(F$str:F$edr)");
        $sheet->setCellValue("G$row","=SUM(G$str:G$edr)");
        $sheet->setCellValue("H$row","=SUM(H$str:H$edr)");
        $sheet->getStyle("F$str:H$row")->applyFromArray($idrFormat);
        $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($allBorders));
    }
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
