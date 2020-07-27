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
    <style type="text/css">
        @page {
            margin: 2cm;
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
    <table cellpadding="2" cellspacing="0" style="width:850px;" bgcolor="white" class="tableBorderSlim">
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
            <td width="5%" style="border-left: 0px; border-bottom: 1px solid !important;">L</td>
            <td width="5%" style="border-bottom: 1px solid !important;">S</td>
            <td width="4%" style="border-bottom: 1px solid !important;">%</td>
            <td width="6%" style="border-bottom: 1px solid !important;">Rp</td>
        </tr>
        <?php
        $qjns = 0;
        $qqty = 0;
        $nmr = 1;
        $lqty = 0;
        $sqty = 0;
        if ($invoice->Jbaris <= 13){
            $brs = 13;
        }else{
            $brs = 35;
        }
        foreach($invoice->Details as $idx => $detail) {
            print('<tr valign="top">');
            printf('<td align="center">%s. </td>',$nmr++);
            printf('<td> %s</td>',$detail->EntityCode);
            printf('<td> %s</td>',$detail->ItemDescs);
            printf('<td align="center">%s</td>',$detail->Lqty > 0 ? number_format($detail->Lqty) : '');
            printf('<td align="center">%s</td>', $detail->Sqty > 0 ? number_format($detail->Sqty) : '');
            if ($detail->IsFree == 0) {
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
                print('<td align="center">*Bonus*</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
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
                print('<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>');
                print('</tr>');
            }
        }
        print('<tr style="outline: thin solid">');
        printf('<td style="border-left: 0px !important;">&nbsp;</td><td class="center" colspan="2">T O T A L</td><td class="center">%s</td><td class="center">%s</td><td colspan="3"></td><td class="right">%s</td><td class="right">%s</td><td class="right" style="border-right: 0px !important;"><b>%s</b></td>', $lqty, $sqty, number_format($invoice->BaseAmount), number_format($invoice->PpnAmount), number_format($invoice->TotalAmount));
        print('</tr>');
        print('</table>');
        print('<br>');
        print('<table cellpadding="0" cellspacing="0" style="width:850px;" bgcolor="white">');
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
