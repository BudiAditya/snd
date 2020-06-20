<!DOCTYPE HTML>
<html>
<head>
    <title>SND System - View Faktur Pajak Keluaran</title>
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
            binvoice-bottom:1px solid #ccc;
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
    </style>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div>
    <?php
}
$faktur = $mfaktur->FetchAssoc();
?>
<br />
<div id="p" class="easyui-panel" title="View Faktur Pajak Keluaran" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 14px;font-family: tahoma">
        <tr>
            <td>Tgl Faktur</td>
            <td colspan="3"><input type="text" class="f1 easyui-textbox" style="width: 150px" value="<?php print($faktur["tanggal_faktur"]);?>"/>
                &nbsp;
                No. Faktur
                &nbsp;
                <input type="text" class="f1 easyui-textbox"  style="width: 150px" value="<?php print($faktur["nomor_faktur"]); ?>"/>
            </td>
        </tr>
        <tr>
            <td>Nama PKP</td>
            <td colspan="3"><input type="text" class="f1 easyui-textbox" style="width: 390px" value="<?php print($faktur["nama"]);?>"/></td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td colspan="3"><input type="text" class="f1 easyui-textbox" style="width: 390px" value="<?php print($faktur["alamat_lengkap"]);?>"/></td>
        </tr>
        <tr>
            <td>N P W P</td>
            <td colspan="3"><input type="text" class="f1 easyui-textbox" style="width: 150px" value="<?php print($faktur["npwp"]);?>"/>
                &nbsp;
                Invoice No.
                &nbsp;
                <input type="text" class="f1 easyui-textbox"  style="width: 150px" value="<?php print($faktur["referensi"]); ?>"/>
            </td>
        </tr>
        <tr>
            <td colspan="10">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 13px;font-family: tahoma; widows: 100%;">
                    <tr>
                        <th>No.</th>
                        <th>Kode</th>
                        <th nowrap="nowrap">Nama Barang</th>
                        <th>QTY</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Diskon</th>
                        <th>DPP</th>
                        <th>PPN</th>
                        <th>Total</th>
                    </tr>
                    <?php
                    if ($dfaktur != null){
                        $nmr = 1;
                        while ($detail = $dfaktur->FetchAssoc()) {
                            print("<tr>");
                            printf("<td align='right'>%s</td>",$nmr++);
                            printf("<td>%s</td>",$detail["kode_objek"]);
                            printf("<td>%s</td>",$detail["nama"]);
                            printf("<td align='right'>%s</td>",number_format($detail["jumlah_barang"]));
                            printf("<td align='right'>%s</td>",number_format($detail["harga_satuan"]));
                            printf("<td align='right'>%s</td>",number_format($detail["harga_total"]));
                            printf("<td align='right'>%s</td>",number_format($detail["diskon"]));
                            printf("<td align='right'>%s</td>",number_format($detail["dpp"]));
                            printf("<td align='right'>%s</td>",number_format($detail["ppn"]));
                            printf("<td align='right'>%s</td>",number_format($detail["dpp"]+$detail["ppn"]));
                            print("</tr>");
                        }
                    }
                    print("<tr>");
                    printf("<td colspan='7' align='right'>%s</td>",'TOTAL');
                    printf("<td align='right'>%s</td>",number_format($faktur["jumlah_dpp"]));
                    printf("<td align='right'>%s</td>",number_format($faktur["jumlah_ppn"]));
                    printf("<td align='right'>%s</td>",number_format($faktur["jumlah_dpp"]+$faktur["jumlah_ppn"]));
                    print("</tr>");
                    ?>
                </table>
            </td>
        </tr>
    </table>
    <a href="<?php print($helper->site_url("tax.faktur")); ?>" class="button"><b>KEMBALI</b></a>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- </body> -->
</html>
