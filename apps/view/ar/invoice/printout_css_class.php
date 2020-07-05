<!DOCTYPE HTML>
<html>
<?php
require_once (LIBRARY . "gen_functions.php");
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
/** @var $invoice Invoice */
/** @var $cabang Cabang */
/** @var $customer Customer */
?>
<head>
    <title>SND System | Print Nota Penjualan (Invoicing)</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

    <script type="text/javascript" src="<?php print($helper->path("public/js/sweetalert.min.js")); ?>"></script>

    <style scoped>
        .f1{
            width:200px;
        }
    </style>

    <style type="text/css">
        #fd{
            margin:0;
            padding:5px 10px;
        }
        .ftitle{
            font-size:14px;
            font-weight:bold;
            padding:5px 0;
            margin-bottom:10px;
            border-bottom:1px solid #ccc;
        }
        .fitem{
            margin-bottom:5px;
        }
        .fitem label{
            display:inline-block;
            width:100px;
        }
        .numberbox .textbox-text{
            text-align: right;
            color: blue;
        }
        .pagebreak { page-break-before: always; } /* page-break-after works, as well */
    </style>
</head>
<body style="background-color:white;">
<?php //include(VIEW . "main/menu.php"); ?>
<div align="right">
    <input type="button" class="button" onclick="printDiv('printInvoice')" value="Print Invoice" />
    <a href="<?php print($helper->site_url("ar.invoice")); ?>">Daftar Invoice</a>
</div>
<div id="printInvoice">
<?php
$kop = 'I N V O I C E';
foreach ($report as $idx => $invoice) {
?>
    <div class="pagebreak"> </div>
    <font size="2" face="Tahoma">
    <br>
    <table cellpadding="1" cellspacing="1" style="width:900px" bgcolor="white">
        <tr>
            <td valign="top">
                <table cellpadding="1" cellspacing="1" style="width:300px">
                    <tr>
                        <td colspan="3">
                            <?php print($cabang->NamaCabang);?>
                            <br>
                            <?php print($cabang->Alamat.' - '.$cabang->Kota);?>
							<br>
                            <?php print('Tlp: '.$cabang->Notel);?>
                            <br>
                            <?php print('NPWP: '.$cabang->Npwp);?>
                        </td>
                    </tr>
                </table>
            </td>
            <td valign="top">
                <table cellpadding="1" cellspacing="1" style="width:300px">
                    <tr>
                        <td width="25%"><font size="+1"><?php print($kop);?></font></td>
                    </tr>
                    <tr>
                        <td width="25%"><u>Kepada Yth:</u></td>
                    </tr>
                    <tr>
                        <td width="25%"><?php print($invoice->CustomerName. ' ('.$invoice->CustomerCode.')');?></td>
                    </tr>
                    <tr>
                        <td width="25%"><?php print($invoice->CustomerAddress);?></td>
                    </tr>
                </table>
            </td>
            <td valign="top">
                <table cellpadding="1" cellspacing="1" style="width:300px">
                    <tr>
                        <td width="20%">Nomor</td>
                        <td>:</td>
                        <td><?php print($invoice->InvoiceNo);?></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>:</td>
                        <td><?php print($invoice->FormatInvoiceDate(JS_DATE));?></td>
                    </tr>
                    <tr>
                        <td>J T P</td>
                        <td>:</td>
                        <td><?php
                                if ($invoice->CreditTerms > 0) {
                                    print($invoice->FormatDueDate(JS_DATE) . ' (' . $invoice->CreditTerms . ' hr)');
                                } else {
                                    print('CASH');
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Salesman</td>
                        <td>:</td>
                        <td><?php print($invoice->SalesName);?></td>
                    </tr>
                    <tr>
                        <td>Gudang</td>
                        <td>:</td>
                        <td><?php print($invoice->GudangCode);?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table cellpadding="2" cellspacing="0" style="width:850px;" bgcolor="white">
        <tr align="center">
            <td class="bT bB bL" rowspan="2" width="3%">No.</td>
            <td class="bT bB bL" rowspan="2" width="5%">BRAND</td>
            <td class="bT bB bL bR" rowspan="2" width="30%">NAMA PRODUK</td>
            <td class="bT" colspan="2" width="18%">QTY</td>
            <td class="bT bB bL" rowspan="2" width="10%">HARGA SATUAN</td>
            <td class="bT bL" colspan="2" width="10%">DISKON</td>
            <td class="bT bB bL" rowspan="2" width="10%">DPP</td>
            <td class="bT bB bL" rowspan="2" width="8%">PPN</td>
            <td class="bT bB bL bR" rowspan="2" width="10%">JUMLAH</td>
        </tr>
        <tr align="center">
            <td class="bT bB bL" width="5%" style="border-left: 0px;">L</td>
            <td class="bT bB bL" width="5%">S</td>
            <td class="bT bB bL" width="4%">%</td>
            <td class="bT bB bL" width="6%">Rp</td>
        </tr>
        <?php
        $qjns = 0;
        $qqty = 0;
        $nmr = 1;
        $lqty = 0;
        $sqty = 0;
        if ($invoice->Jbaris < 15){
            $brs = 15;
        }else{
            $brs = 35;
        }
        foreach($invoice->Details as $idx => $detail) {
            print('<tr valign="top">');
            printf('<td class="bL" align="center">%s. </td>',$nmr++);
            printf('<td class="bL"> %s</td>',$detail->EntityCode);
            printf('<td class="bL bR"> %s</td>',$detail->ItemDescs);
            printf('<td align="center">%s</td>',$detail->Lqty > 0 ? number_format($detail->Lqty) : '');
            printf('<td class="bL" align="center">%s</td>', $detail->Sqty > 0 ? number_format($detail->Sqty) : '');
            if ($detail->IsFree == 0) {
                printf('<td class="bL" align="right">%s</td>', number_format($detail->Price, 2));
                if ($detail->DiscAmount > 0) {
                    printf('<td class="bL" align="right">%s</td>', $detail->DiscFormula);
                    printf('<td class="bL" align="right">%s</td>', number_format($detail->DiscAmount));
                } else {
                    print('<td class="bL">&nbsp;</td>');
                    print('<td class="bL">&nbsp;</td>');
                }
                printf('<td class="bL" align="right">%s</td>', number_format($detail->SubTotal-$detail->DiscAmount));
                printf('<td class="bL" align="right">%s</td>', number_format($detail->PpnAmount));
                printf('<td class="bL bR" align="right">%s</td>', number_format($detail->SubTotal + $detail->PpnAmount - $detail->DiscAmount));
            } else {
                print('<td class="bL" align="center">*Bonus*</td><td class="bL">&nbsp;</td><td class="bL">&nbsp;</td><td class="bL">&nbsp;</td><td class="bL">&nbsp;</td><td class="bL bR">&nbsp;</td>');
            }
            print('</tr>');
            $lqty+= $detail->Lqty;
            $sqty+= $detail->Sqty;
            $qjns++;
            $qqty+= $detail->SalesQty;
        }
        if ($nmr < $brs){
            for ($x = $nmr; $x <= $brs; $x++) {
                print('<tr>');
                print('<td class="bL">&nbsp;</td><td class="bL">&nbsp;</td class="bL"><td class="bL bR">&nbsp;</td><td>&nbsp;</td><td class="bL">&nbsp;</td><td class="bL">&nbsp;</td><td class="bL">&nbsp;</td><td class="bL">&nbsp;</td><td class="bL">&nbsp;</td><td class="bL">&nbsp;</td><td class="bL bR">&nbsp;</td>');
                print('</tr>');
            }
        }
        print('<tr>');
        printf('<td class="bL bT bB">&nbsp;</td><td  class="bL bR bT bB center" colspan="2">T O T A L</td><td class="center bT bB">%s</td><td class="bL bT bB center">%s</td><td class="bL bT bB" colspan="3"></td><td class="bL bT bB right">%s</td><td class="bL bT bB right">%s</td><td class="bL bR bT bB right">%s</td>', $lqty, $sqty, number_format($invoice->BaseAmount), number_format($invoice->PpnAmount), number_format($invoice->TotalAmount));
        print('</tr>');
        print('<tr>');
        print('<td colspan="6"><sub><u>CATATAN:</u></sub></td>');
        print('<td colspan="3">PENERIMA,</td>');
        print('<td colspan="3">HORMAT KAMI,</td>');
        print('</tr>');
        print('<tr>');
        print('<td colspan="11"><sub>- PEMBAYARAN DENGAN CHEQUE/BG DIANGGAP LUNAS SETELAH DAPAT DIUANGKAN.</sub></td>');
        print('</tr>');
        print('<tr>');
        print('<td colspan="11"><sub>- PEMBAYARAN DENGAN TRANSFER HANYA DITUJUKAN KE REK BANK PT CASULUT.</sub></td>');
        print('</tr>');
        print('<tr>');
        print('<td colspan="11"><sub>- BARANG YANG SUDAH DIBELI TIDAK DAPAT DIKEMBALIKAN.</sub></td>');
        print('</tr>');
        print('<tr>');
        print('</tr>');
        print('<tr>');
        print('<td colspan="6">&nbsp;</td>');
        print('<td colspan="3">_________________</td>');
        print('<td colspan="3">_________________</td>');
        print('</tr>');
        print('<tr>');
        printf('<td colspan="6"><sub><i>Admin: %s - Printed by: %s - %s</i></sub></td>', $invoice->AdminName, $userName, date('d-m-Y h:i:s'));
        print('<td colspan="3"><sub>(TTD + CAP TOKO)</sub></td>');
        print('<td colspan="3"><sub>(Tidak sah tanpa stempel)</sub></td>');
        print('</tr>');
        ?>
    </table>
    </font>
<?php } ?>
</div>
<script type="text/javascript">
    function printDiv(divName) {
        //if (confirm('Print Invoice ini?')) {
        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.print();

        document.body.innerHTML = originalContents;
        //}
    }
</script>
<!-- </body> -->
</html>
