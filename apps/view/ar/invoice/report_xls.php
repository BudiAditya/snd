<?php
$phpExcel = new PHPExcel();
$headers = array(
  'Content-Type: application/vnd.ms-excel'
, 'Content-Disposition: attachment;filename="sales-report.xls"'
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);
// Excel MetaData
$phpExcel->getProperties()->setCreator("Rekasystem Infotama Inc (c) Budi Aditya")->setTitle("Print Laporan")->setCompany("Rekasystem Infotama Inc");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Rekapitulasi Penjualan");
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
    if ($JnsLaporan == 1){
        $sheet->setCellValue("A$row", "REKAPITULASI PENJUALAN");
    }else{
        $sheet->setCellValue("A$row", "DETAIL PENJUALAN");
    }
    $row++;
    $sheet->setCellValue("A$row", "Dari Tgl. " . date('d-m-Y', $StartDate) . " - " . date('d-m-Y', $EndDate));
    $row++;
    $sheet->setCellValue("A$row", "No.");
    $sheet->setCellValue("B$row", "Cab");
    $sheet->setCellValue("C$row", "Tanggal");
    $sheet->setCellValue("D$row", "No. Invoice");
    $sheet->setCellValue("E$row", "Customer");
    $sheet->setCellValue("F$row", "Cs Code");
    $sheet->setCellValue("G$row", "Area");
    $sheet->setCellValue("H$row", "Salesman");
    if ($JnsLaporan == 1) {
        $sheet->setCellValue("I$row", "JTP");
        $sheet->setCellValue("J$row", "DPP");
        $sheet->setCellValue("K$row", "PPN");
        $sheet->setCellValue("L$row", "Jumlah");
        $sheet->setCellValue("M$row", "Retur");
        $sheet->setCellValue("N$row", "Terbayar");
        $sheet->setCellValue("O$row", "Outstanding");
        $sheet->getStyle("A$row:O$row")->applyFromArray(array_merge($center, $allBorders));
    }elseif ($JnsLaporan == 2) {
        $sheet->setCellValue("I$row", 'Kode');
        $sheet->setCellValue("J$row", 'Brand');
        $sheet->setCellValue("K$row", 'Nama Barang');
        $sheet->setCellValue("L$row", 'QTY');
        $sheet->setCellValue("M$row", 'Harga');
        $sheet->setCellValue("N$row", 'Jumlah');
        $sheet->setCellValue("O$row", 'Discount');
        $sheet->setCellValue("P$row", 'DPP');
        $sheet->setCellValue("Q$row", 'PPN');
        $sheet->setCellValue("R$row", 'TOTAL');
        $sheet->getStyle("A$row:R$row")->applyFromArray(array_merge($center, $allBorders));
    }
    $nmr = 1;
    $str = $row;
    if ($Reports != null) {
        $ivn = null;
        $sma = false;
        $tTotal = 0;
        $tPaid = 0;
        $tBalance = 0;
        while ($rpt = $Reports->FetchAssoc()) {
            $row++;
            $sheet->setCellValue("A$row", $nmr++);
            $sheet->getStyle("A$row")->applyFromArray($center);
            $sheet->setCellValueExplicit("B$row", $rpt["cabang_code"]);
            $sheet->setCellValue("C$row", date('d-m-Y', strtotime($rpt["invoice_date"])));
            $sheet->setCellValue("D$row", $rpt["invoice_no"]);
            $sheet->setCellValue("E$row", $rpt["customer_name"]);
            $sheet->setCellValueExplicit("F$row", $rpt["customer_code"],PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue("G$row", $rpt["area_code"]);
            $sheet->setCellValue("H$row", $rpt["sales_name"]);
            if ($JnsLaporan == 1) {
                $sheet->setCellValue("I$row", date('d-m-Y', strtotime($rpt["due_date"])));
                $sheet->setCellValue("J$row", $rpt["base_amount"] - $rpt["disc_amount"]);
                $sheet->setCellValue("K$row", $rpt["ppn_amount"]);
                $sheet->setCellValue("L$row", $rpt["total_amount"]);
                $sheet->setCellValue("M$row", $rpt["return_amount"]);
                $sheet->setCellValue("N$row", $rpt["paid_amount"]);
                $sheet->setCellValue("O$row", $rpt["balance_amount"]);
                $sheet->getStyle("A$row:O$row")->applyFromArray(array_merge($allBorders));
            }elseif ($JnsLaporan == 2) {
                $sheet->setCellValue("I$row", $rpt['brand_name']);
                $sheet->setCellValueExplicit("J$row", $rpt['item_code'],PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValue("K$row", $rpt['item_name']);
                $sheet->setCellValue("L$row", $rpt['sales_qty']);
                $sheet->setCellValue("M$row", $rpt['price']);
                $sheet->setCellValue("N$row", $rpt['sub_total']);
                $sheet->setCellValue("O$row", $rpt['disc_amount']);
                $sheet->setCellValue("P$row", $rpt['sub_total']-$rpt['disc_amount']);
                $sheet->setCellValue("Q$row", $rpt['ppn_amount']);
                $sheet->setCellValue("R$row", $rpt['sub_total']-$rpt['disc_amount']+$rpt['ppn_amount']);
                $sheet->getStyle("A$row:R$row")->applyFromArray(array_merge($allBorders));
            }
            $ivn = $rpt["invoice_no"];
        }
        $edr = $row;
        $row++;
        $sheet->setCellValue("A$row", "GRAND TOTAL INVOICE");
        $sheet->mergeCells("A$row:H$row");
        $sheet->getStyle("A$row")->applyFromArray($center);
        if ($JnsLaporan == 1) {
            $sheet->setCellValue("J$row", "=SUM(J$str:J$edr)");
            $sheet->setCellValue("K$row", "=SUM(K$str:K$edr)");
            $sheet->setCellValue("L$row", "=SUM(L$str:L$edr)");
            $sheet->setCellValue("M$row", "=SUM(M$str:M$edr)");
            $sheet->setCellValue("N$row", "=SUM(N$str:N$edr)");
            $sheet->setCellValue("O$row", "=SUM(O$str:O$edr)");
            $sheet->getStyle("J$str:O$row")->applyFromArray($idrFormat);
            $sheet->getStyle("A$row:O$row")->applyFromArray(array_merge($allBorders));
        }else{
            $sheet->setCellValue("M$row", "=SUM(M$str:M$edr)");
            $sheet->setCellValue("N$row", "=SUM(N$str:N$edr)");
            $sheet->setCellValue("O$row", "=SUM(O$str:O$edr)");
            $sheet->setCellValue("P$row", "=SUM(P$str:P$edr)");
            $sheet->setCellValue("Q$row", "=SUM(Q$str:Q$edr)");
            $sheet->setCellValue("R$row", "=SUM(R$str:R$edr)");
            $sheet->getStyle("L$str:R$row")->applyFromArray($idrFormat);
            $sheet->getStyle("A$row:R$row")->applyFromArray(array_merge($allBorders));
        }
        $row++;
    }
}elseif ($JnsLaporan == 3){
    // rekap item yang terjual
    $sheet->setCellValue("A$row", "REKAPITULASI BARANG TERJUAL");
    $row++;
    $sheet->setCellValue("A$row", "Dari Tgl. " . date('d-m-Y', $StartDate) . " - " . date('d-m-Y', $EndDate));
    $row++;
    $sheet->setCellValue("A$row", "No.");
    $sheet->setCellValue("B$row", "Brand");
    $sheet->setCellValue("C$row", "Kode");
    $sheet->setCellValue("D$row", "Nama Barang");
    $sheet->setCellValue("E$row", "L");
    $sheet->setCellValue("F$row", "S");
    $sheet->setCellValue("G$row", "PCS");
    $sheet->setCellValue("H$row", "LTR");
    $sheet->setCellValue("I$row", "DPP");
    $sheet->setCellValue("J$row", "PPN");
    $sheet->setCellValue("K$row", "NILAI");
    $sheet->getStyle("A$row:K$row")->applyFromArray(array_merge($center, $allBorders));
    $nmr = 0;
    $str = $row;
    if ($Reports != null) {
        while ($rpt = $Reports->FetchAssoc()) {
            $row++;
            $nmr++;
            if ($rpt["entity_id"] == 1) {
                $cqty = round($rpt["sum_qty"] * $rpt["qty_convert"], 2);
            }else{
                $cqty = 0;
            }
            $sheet->setCellValue("A$row", $nmr);
            $sheet->setCellValue("B$row", $rpt['brand_name']);
            $sheet->setCellValueExplicit("C$row", $rpt['item_code'],PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue("D$row", $rpt['item_name']);
            $sheet->setCellValue("E$row", $rpt['sum_lqty']);
            $sheet->setCellValue("F$row", $rpt['sum_sqty']);
            $sheet->setCellValue("G$row", $rpt['sum_qty']);
            $sheet->setCellValue("H$row", $cqty);
            $sheet->setCellValue("I$row", $rpt['sum_dpp']);
            $sheet->setCellValue("J$row", $rpt['sum_ppn']);
            $sheet->setCellValue("K$row", $rpt['sum_dpp']+$rpt['sum_ppn']);
            $sheet->getStyle("A$row:K$row")->applyFromArray(array_merge($allBorders));
        }
        $edr = $row;
        $row++;
        $sheet->setCellValue("A$row", "T O T A L");
        $sheet->mergeCells("A$row:D$row");
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("E$row","=SUM(E$str:E$edr)");
        $sheet->setCellValue("F$row","=SUM(F$str:F$edr)");
        $sheet->setCellValue("G$row","=SUM(G$str:G$edr)");
        $sheet->setCellValue("H$row","=SUM(H$str:H$edr)");
        $sheet->setCellValue("I$row","=SUM(I$str:I$edr)");
        $sheet->setCellValue("J$row","=SUM(J$str:J$edr)");
        $sheet->setCellValue("K$row","=SUM(K$str:K$edr)");
        $sheet->getStyle("E$str:K$row")->applyFromArray($idrFormat);
        $sheet->getStyle("A$row:K$row")->applyFromArray(array_merge($allBorders));
    }
}elseif ($JnsLaporan == 4){
    // rekap per outlet
    $sheet->setCellValue("A$row", "REKAPITULASI PER OUTLET - " .$userCabName);
    $row++;
    $sheet->setCellValue("A$row", "Dari Tgl. " . date('d-m-Y', $StartDate) . " - " . date('d-m-Y', $EndDate));
    $row++;
    $sheet->setCellValue("A$row", "No.");
    $sheet->setCellValue("B$row", "Tanggal");
    $sheet->setCellValue("C$row", "No. Invoice");
    $sheet->setCellValue("D$row", "Outlet");
    $sheet->setCellValue("E$row", "Nama Outlet");
    $sheet->setCellValue("F$row", "Alamat");
    $sheet->setCellValue("G$row", "Salesman");
    $sheet->setCellValue("H$row", "QTY");
    $sheet->setCellValue("I$row", "Jumlah");
    $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($center, $allBorders));
    $nmr = 0;
    $str = $row;
    if ($Reports != null) {
        while ($rpt = $Reports->FetchAssoc()) {
            $row++;
            $nmr++;
            $sheet->setCellValue("A$row", $nmr);
            $sheet->setCellValue("B$row", $rpt['invoice_date']);
            $sheet->setCellValue("C$row", $rpt['invoice_no']);
            $sheet->setCellValue("D$row", $rpt['customer_code']);
            $sheet->setCellValue("E$row", $rpt['customer_name']);
            $sheet->setCellValue("F$row", $rpt['customer_address']);
            $sheet->setCellValue("G$row", $rpt['sales_name']);
            $sheet->setCellValue("H$row", $rpt['sum_qty']);
            $sheet->setCellValue("I$row", $rpt['total_amount']);
            $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($allBorders));
        }
        $edr = $row;
        $row++;
        $sheet->setCellValue("A$row", "T O T A L");
        $sheet->mergeCells("A$row:G$row");
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("H$row","=SUM(H$str:H$edr)");
        $sheet->setCellValue("I$row","=SUM(I$str:I$edr)");
        $sheet->getStyle("H$str:I$row")->applyFromArray($idrFormat);
        $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($allBorders));
    }
}elseif ($JnsLaporan == 5){
    // rekap item yang terjual
    $sheet->setCellValue("A$row", "REKAPITULASI PER PRODUK - " . $userCabName);
    $row++;
    $sheet->setCellValue("A$row", "Dari Tgl. " . date('d-m-Y', $StartDate) . " - " . date('d-m-Y', $EndDate));
    $row++;
    $sheet->setCellValue("A$row", "No.");
    $sheet->setCellValue("B$row", "Kode");
    $sheet->setCellValue("C$row", "Nama Barang");
    $sheet->setCellValue("D$row", "Satuan");
    $sheet->setCellValue("E$row", "Q T Y");
    $sheet->setCellValue("F$row", "Terkirim");
    $sheet->setCellValue("G$row", "Tidak Terkirim");
    $sheet->setCellValue("H$row", "Selisih");
    $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($center, $allBorders));
    $nmr = 0;
    $str = $row;
    if ($Reports != null) {
        while ($rpt = $Reports->FetchAssoc()) {
            $row++;
            $nmr++;
            $sheet->setCellValue("A$row", $nmr);
            $sheet->setCellValue("B$row", $rpt['item_code']);
            $sheet->setCellValue("C$row", $rpt['item_descs']);
            $sheet->setCellValue("D$row", $rpt['satuan']);
            $sheet->setCellValue("E$row", $rpt['sum_qty']);
            $sheet->setCellValue("F$row", "");
            $sheet->setCellValue("G$row", "");
            $sheet->setCellValue("H$row", "");
            $sheet->getStyle("A$row:H$row")->applyFromArray(array_merge($allBorders));
        }
        $edr = $row;
        $row++;
        $sheet->setCellValue("A$row", "T O T A L");
        $sheet->mergeCells("A$row:D$row");
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("E$row","=SUM(E$str:E$edr)");
        $sheet->getStyle("D$str:H$row")->applyFromArray($idrFormat);
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
