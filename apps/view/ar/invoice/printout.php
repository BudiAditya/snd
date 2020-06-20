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
if ($doctype == 'invoice'){
    $kop = 'I N V O I C E';
}elseif ($doctype == 'suratjalan'){
    $kop = 'SURAT JALAN';
}
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
                            <b><?php print($cabang->NamaCabang);?></b>
                            <br>
                            <?php print($cabang->Alamat.' - '.$cabang->Kota);?>
							<br>
                            <?php print('Tlp: '.$cabang->Notel);?>
                            <br>
                            <?php print($doctype == 'invoice' ? 'NPWP: '.$cabang->Npwp : '');?>
                        </td>
                    </tr>
                </table>
            </td>
            <td valign="top">
                <table cellpadding="1" cellspacing="1" style="width:300px">
                    <tr>
                        <td width="25%"><b><font size="+1"><?php print($kop);?></font></b></td>
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
                        <td width="20%"><?php print($doctype == 'invoice' ? 'Nomor' : 'Reff No.');?></td>
                        <td>:</td>
                        <td><?php print($invoice->InvoiceNo);?></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>:</td>
                        <td><?php print($invoice->FormatInvoiceDate(JS_DATE));?></td>
                    </tr>
                    <tr>
                        <td><?php print($doctype == 'invoice' ? 'J T P' : 'S/O No.');?></td>
                        <td>:</td>
                        <td><?php
                            if ($doctype == 'invoice') {
                                if ($invoice->CreditTerms > 0) {
                                    print($invoice->FormatDueDate(JS_DATE) . ' (' . $invoice->CreditTerms . ' hr)');
                                } else {
                                    print('CASH');
                                }
                            }else{
                                print($invoice->ExSoNo);
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
    <?php if($doctype == 'suratjalan'){
        print('<br>Mohon diterima dengan baik barang-barang pesanan bapak/ibu sebagai berikut:<br>');
    }
    ?>
    <table cellpadding="2" cellspacing="0" style="width:850px" class="tableBorder" bgcolor="white">
        <?php if ($doctype == 'invoice'){ ?>
            <tr align="center">
                <td rowspan="2" width="3%">No.</td>
                <td rowspan="2" width="5%">BRAND</td>
                <td rowspan="2" width="30%">NAMA PRODUK</td>
                <td colspan="2" width="18%">QTY</td>
                <td rowspan="2" width="10%">HARGA SATUAN</td>
                <td colspan="2" width="10%">DISKON</td>
                <td rowspan="2" width="10%">DPP</td>
                <td rowspan="2" width="8%">PPN</td>
                <td rowspan="2" width="10%">JUMLAH</td>
            </tr>
            <tr align="center">
                <td width="5%" style="border-left: 0px;">L</td>
                <td width="5%">S</td>
                <td width="4%">%</td>
                <td width="6%">Rp</td>
            </tr>
        <?php }else { ?>
            <tr align="center">
                <td rowspan="2" width="5%">No.</td>
                <td rowspan="2" width="10%">BRAND</td>
                <td rowspan="2" width="31%">NAMA PRODUK</td>
                <td colspan="3" width="24%">QTY</td>
                <td rowspan="2" width="25%">KETERANGAN</td>
            </tr>
            <tr align="center">
                <td width="8%" style="border-left: 0px;">L</td>
                <td width="8%">S</td>
                <td width="8%">T</td>
            </tr>
        <?php
        }
        $qjns = 0;
        $qqty = 0;
        $nmr = 1;
        $brs = 14;
        $lqty = 0;
        $sqty = 0;
        foreach($invoice->Details as $idx => $detail) {
            print('<tr>');
            printf('<td align="center">%s. </td>',$nmr++);
            printf('<td> %s</td>',$detail->EntityCode);
            printf('<td> %s</td>',$detail->ItemDescs);
            if ($doctype == 'invoice') {
                if ($detail->IsFree == 0) {
                    printf('<td align="center">%s</td>',number_format($detail->Lqty));
                    printf('<td align="center">%s</td>',number_format($detail->Sqty));
                    printf('<td align="right">%s</td>', number_format($detail->Price, 2));
                    if ($detail->DiscAmount > 0) {
                        printf('<td align="right">%s</td>', $detail->DiscFormula);
                        printf('<td align="right">%s</td>', number_format($detail->DiscAmount));
                    } else {
                        print('<td>&nbsp;</td>');
                        print('<td>&nbsp;</td>');
                    }
                    printf('<td align="right">%s</td>', number_format($detail->SubTotal-$detail->DiscAmount));
                    printf('<td align="right">%s</td>', number_format($detail->PpnAmount));
                    printf('<td align="right">%s</td>', number_format($detail->SubTotal + $detail->PpnAmount - $detail->DiscAmount));
                } else {
                    printf('<td align="center">%s</td>',number_format($detail->Lqty));
                    printf('<td align="center">%s</td>',number_format($detail->Sqty));
                    print('<td align="center">*Bonus*</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                }
            }else{
                printf('<td align="right">%s %s</td>',number_format($detail->Lqty),$doctype == 'invoice' ? '' : $detail->SatBesar);
                printf('<td align="right">%s %s</td>',number_format($detail->Sqty),$doctype == 'invoice' ? '' : $detail->SatKecil);
                printf('<td align="right">%s %s</td>',number_format($detail->SalesQty),$doctype == 'invoice' ? '' : $detail->SatKecil);
                if ($detail->IsFree == 0) {
                    print('<td>&nbsp;</td>');
                }else{
                    print('<td>* Bonus *</td>');
                }
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
                if ($doctype == 'invoice') {
                    print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                }else{
                    print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                }
                print('</tr>');
            }
        }
        print('<tr>');
        if ($doctype == 'invoice') {
            printf('<td>&nbsp;</td><td class="center" colspan="2">T O T A L</td><td class="center">%s</td><td class="center">%s</td><td colspan="3"></td><td class="right">%s</td><td class="right">%s</td><td class="right">%s</td>', $lqty, $sqty, number_format($invoice->BaseAmount), number_format($invoice->PpnAmount), number_format($invoice->TotalAmount));
        }else{
            printf('<td>&nbsp;</td><td colspan="2" align="right">Total: &nbsp;</td><td class="bold center">%d</td><td class="bold center">%d</td><td class="bold center">%d</td><td>&nbsp;</td>', $lqty, $sqty, $qqty);
        }
        print('</tr>');
        if ($doctype == 'invoice') {
            print('<tr>');
            print('<td colspan="6" style="border-left: 0px;border-right: 0px;border-bottom: 0px"><sub><u>CATATAN:</u></sub></td>');
            print('<td colspan="3" style="border-left: 0px;border-right: 0px;border-bottom: 0px">PENERIMA,</td>');
            print('<td colspan="3" style="border-left: 0px;border-right: 0px;border-bottom: 0px">HORMAT KAMI,</td>');
            print('</tr>');
            print('<tr>');
            print('<td colspan="11" style="border-left: 0px;border-right: 0px;border-bottom: 0px"><sub>- PEMBAYARAN DENGAN CHEQUE/BG DIANGGAP LUNAS SETELAH DAPAT DIUANGKAN.</sub></td>');
            print('</tr>');
            print('<tr>');
            print('<td colspan="11" style="border-left: 0px;border-right: 0px;border-bottom: 0px"><sub>- BARANG YANG SUDAH DIBELI TIDAK DAPAT DIKEMBALIKAN.</sub></td>');
            print('</tr>');
            print('<tr>');
            print('<td colspan="11" style="border-left: 0px;border-right: 0px;border-bottom: 0px">&nbsp;</td>');
            print('</tr>');
            print('<tr>');
            print('<td colspan="6" style="border-left: 0px;border-right: 0px;border-bottom: 0px">&nbsp;</td>');
            print('<td colspan="3" style="border-left: 0px;border-right: 0px;border-bottom: 0px">_________________</td>');
            print('<td colspan="3" style="border-left: 0px;border-right: 0px;border-bottom: 0px">_________________</td>');
            print('</tr>');
            print('<tr>');
            printf('<td colspan="6" style="border-left: 0px;border-right: 0px;border-bottom: 0px"><sub><i>Admin: %s - Printed by: %s - %s</i></sub></td>', $invoice->AdminName, $userName, date('d-m-Y h:i:s'));
            print('<td colspan="3" style="border-left: 0px;border-right: 0px;border-bottom: 0px"><sub>(TTD + CAP TOKO)</sub></td>');
            print('<td colspan="3" style="border-left: 0px;border-right: 0px;border-bottom: 0px"><sub>(Tidak sah tanpa stempel)</sub></td>');
            print('</tr>');
        }else{
            print('<tr>');
            print('<td colspan="2" style="border-left: 0px;border-right: 0px;border-bottom: 0px">GUDANG,</td>');
            print('<td align="center" style="border-left: 0px;border-right: 0px;border-bottom: 0px">DISERAHKAN,</td>');
            print('<td colspan="2" style="border-left: 0px;border-right: 0px;border-bottom: 0px">DITERIMA,</td>');
            printf('<td colspan="2" align="right" style="border-left: 0px;border-right: 0px;border-bottom: 0px"><sub><i>Admin: %s %s</i></sub></td>',$invoice->AdminName,date('d-m-Y h:i:s'));
            print('</tr>');
            print('<tr><td colspan="7" style="border-left: 0px;border-right: 0px;border-bottom: 0px">&nbsp;</td></tr>');
            print('<tr><td colspan="7" style="border-left: 0px;border-right: 0px;border-bottom: 0px">&nbsp;</td></tr>');
            print('<tr><td colspan="7" style="border-left: 0px;border-right: 0px;border-bottom: 0px">&nbsp;</td></tr>');
            print('<tr>');
            print('<td colspan="2" style="border-left: 0px;border-right: 0px;border-bottom: 0px">_________________</td>');
            print('<td align="center" style="border-left: 0px;border-right: 0px;border-bottom: 0px">_________________</td>');
            print('<td colspan="2" style="border-left: 0px;border-right: 0px;border-bottom: 0px">_________________</td>');
            print('</tr>');
            print('<td colspan="2" style="border-left: 0px;border-right: 0px;border-bottom: 0px"><sub>(Ka. Gudang)</sub></td>');
            print('<td align="center" style="border-left: 0px;border-right: 0px;border-bottom: 0px"><sub>(Sales/Kanvaser)</sub></td>');
            print('<td colspan="2" style="border-left: 0px;border-right: 0px;border-bottom: 0px"><sub>(TTD + Stempel)</sub></td>');
            print('</tr>');
        }
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
