<!DOCTYPE HTML>
<html>
<head>
	<title>SND System | Rekapitulasi Stock Produk</title>
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
    <script type="text/javascript">

        $(document).ready(function() {

        });

    </script>
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
            bpurchase-bottom:1px solid #ccc;
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
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<form id="frm" name="frmReport" method="post">
    <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
        <tr class="left">
            <th colspan="4"><b>REKAPITULASI STOCK BARANG: <?php print($company_name);?></b></th>
        </tr>
        <tr class="center">
            <th>Gudang</th>
            <th>Jenis Produk</th>
            <th>Output</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select name="GudangId" class="text2" id="GudangId" required>
                    <option value="0">GABUNGAN (MDO + GTO)</option>
                    <?php
                    /** @var $gudangs Warehouse[] */
                    foreach ($gudangs as $gudang) {
                        if ($gudangId == $gudang->Id) {
                            printf('<option value="%s" selected="selected">%s</option>', $gudang->Id, $gudang->WhCode);
                        } else {
                            printf('<option value="%s">%s</option>', $gudang->Id, $gudang->WhCode);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select name="EntityId" class="text2" id="EntityId" required>
                   <option value="-">- Semua Jenis Produk-</option>
                    <?php
                    /** @var $jenis ItemEntity[] */
                    foreach ($jenis as $jns) {
                        if ($jns->Id == $userEntityId) {
                            printf('<option value="%d" selected="selected">%s</option>', $jns->Id, $jns->EntityName);
                        } else {
                            printf('<option value="%d">%s</option>', $jns->Id, $jns->EntityName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="Output" name="Output" required>
                    <option value="0" <?php print($output == 0 ? 'selected="selected"' : '');?>>0 - Web Html</option>
                    <option value="1" <?php print($output == 1 ? 'selected="selected"' : '');?>>1 - Excel</option>
                </select>
            </td>
            <td>
                <button type="submit" formaction="<?php print($helper->site_url("inventory.stock/report")); ?>"><b>TAMPILKAN</b></button>
                <input type="button" class="button" onclick="printDiv('printArea')" value="Print"/>
            </td>
        </tr>
    </table>
</form>
<!-- start web report -->
<div id="printArea">
<?php  if ($reports != null){
    $userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
    print('<h2>Rekapitulasi Stock Produk</h2>');
?>
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Kode</th>
            <th>Nama Produk</th>
            <th>Satuan</th>
            <th>Stock</th>
            <th>L</th>
            <th>S</th>
            <th>C</th>
        </tr>
        <?php
        $nmr = 1;
        $tOtal = 0;
        while ($row = $reports->FetchAssoc()) {
            print("<tr valign='Top'>");
            printf("<td>%s</td>",$nmr);
            if ($gudangId > 0) {
                printf("<td nowrap='nowrap'><a href='%s' target='_blank'>%s</a></td>", $helper->site_url("inventory.stock/card/" . $gudangId . "|" . $row["item_id"]), $row["item_code"]);
            }else{
                printf("<td nowrap='nowrap'>%s</td>", $row["item_code"]);
            }
            printf("<td nowrap='nowrap'>%s</td>",$row["item_name"]);
            printf("<td nowrap='nowrap'>%s</td>",$row["s_uom_code"]);
            printf("<td align='right'>%s</td>",decFormat($row["qty_stock"],2));
            $sld = $row["qty_stock"];
            if ($sld >= $row["s_uom_qty"] && $row["s_uom_qty"] > 0){
                $aqty = array();
                $sqty = round($sld/$row["s_uom_qty"],2);
                $aqty = explode('.',$sqty);
                $lqty = $aqty[0];
                $sqty = $sld - ($lqty * $row["s_uom_qty"]);
            }else {
                $lqty = 0;
                $sqty = $sld;
            }
            printf('<td class="right">%s</td>',decFormat($lqty));
            printf('<td class="right">%s</td>',decFormat($sqty));
            if ($row["entity_id"] == 1){
                $cqty = round($sld * $row["qty_convert"],2);
                printf("<td align='right'>%s</td>",number_format($cqty,2));
            }else{
                $cqty = 0;
                print("<td>&nbsp;</td>");
            }
            print("</tr>");
            $nmr++;
        }
        ?>
    </table>
<!-- end web report -->
    <br>
    <?php
    print('<i>* Printed by: ' . $userName . '  - Time: ' . date('d-m-Y h:i:s') . ' *</i>');
    ?>
</div>
<?php } ?>
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
