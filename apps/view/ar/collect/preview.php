<!DOCTYPE HTML>
<html>
<?php
require_once (LIBRARY . "gen_functions.php");
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
/** @var $collect Collect */
/** @var $cabang Cabang */
?>
<head>
    <title>SND System | Print Slip Panegihan)</title>
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
    <input type="button" class="button" onclick="printDiv('printSlip')" value="Print Slip" />
    <a href="<?php print($helper->site_url("ar.collect")); ?>">Daftar Slip Penagihan</a>
</div>
<div id="printSlip">
<div class="pagebreak"> </div>
<table cellpadding="1" cellspacing="1" width="750" bgcolor="white">
    <tr>
        <td valign="top">
            <table cellpadding="1" cellspacing="1" width="400">
                <tr>
                    <td colspan="3">
                        <b><?php print($cabang->NamaCabang);?></b>
                        <br>
                        <?php print($cabang->Alamat.' - '.$cabang->Kota);?>
                        <br>
                        <?php print('Tlp: '.$cabang->Notel);?>
                        <br>
                        NPWP: <?php print($cabang->Npwp);?>
                    </td>
                </tr>
            </table>
        </td>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td valign="top">
            <table cellpadding="1" cellspacing="1" width="350">
                <tr>
                    <td colspan="3"><b>SLIP PENAGIHAN</b></td>
                </tr>
                <tr>
                    <td width="20%">Nomor</td>
                    <td>:</td>
                    <td><?php print($collect->CollectNo);?></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td><?php print($collect->FormatCollectDate(JS_DATE));?></td>
                </tr>
                <tr>
                    <td>Sales/Collector</td>
                    <td>:</td>
                    <td><?php print($collector->Nama);?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<table cellpadding="2" cellspacing="2" width="750" class="tableBorder" bgcolor="white">
    <tr align="center">
        <th width="3%">No.</th>
        <th width="10%">No. Invoice</th>
        <th width="10%">Tanggal</th>
        <th width="20%">Nama Customer</th>
        <th width="10%">JTP</th>
        <th width="15%">Tagihan</th>
        <th width="15%">Terbayar</th>
        <th width="17%">Keterangan</th>
    </tr>
    <?php
    $counter = 0;
    $total = 0;
    $totOut = 0;
    $dtStatus = null;
    foreach($collect->Details as $idx => $detail) {
        $counter++;
        print("<tr>");
        printf('<td class="right">%s.</td>', $counter);
        printf('<td nowrap="nowrap">%s</td>', $detail->InvoiceNo);
        printf('<td nowrap="nowrap">%s</td>', $detail->InvoiceDate);
        printf('<td nowrap="nowrap">%s</td>', $detail->CustomerName);
        printf('<td nowrap="nowrap">%s</td>', $detail->InvoiceDueDate);
        printf('<td nowrap="nowrap" class="right">%s</td>', number_format($detail->OutstandingAmount,0));
        print('<td>&nbsp;</td>');
        print('<td>&nbsp;</td>');
        print("</tr>");
        $totOut += $detail->OutstandingAmount;
    }
    ?>
    <tr>
        <td colspan="5" align="right">Total -<b><?= $counter.'</b>- Faktur, senilai Rp. '?></td>
        <td align="right"><b><?= number_format($totOut,0)?></b></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <?php
    print('</tr>');
        print('<td colspan="5">
            Cap dan Tanda Tangan
            <br>
            <br>
            <br>
            <br>
            ------------------ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ----------------- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -----------------
            <br>
            &nbsp;&nbsp;Sales/Collector &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; AR/Finance &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Manager
        </td>');
        print('<td colspan="3" valign="top">Catatan:</td>');
    print('</tr>');
    print('<tr>');
    printf('<td colspan="8" align="right" style="border-left: 0px;border-right: 0px;border-bottom: 0px"><sub><i>Printed by: %s - Time: %s</i></sub></td>',$userName,date('d-m-Y h:i:s'));
    print('</tr>');
    ?>
</table>
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
