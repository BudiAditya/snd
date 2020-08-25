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
            <th>
                <select id="ReportType" name="ReportType" required>
                    <option value="0" <?php print($userReportType == 0 ? 'selected="selected"' : '');?>>Tanpa PO & SO</option>
                    <option value="1" <?php print($userReportType == 1 ? 'selected="selected"' : '');?>>Termasuk PO saja</option>
                    <option value="2" <?php print($userReportType == 2 ? 'selected="selected"' : '');?>>Termasuk SO saja</option>
                    <option value="3" <?php print($userReportType == 3 ? 'selected="selected"' : '');?>>Termasuk PO & SO</option>
                </select>
            </th>
        </tr>
        <tr class="center">
            <th>Gudang</th>
            <th>Jenis Produk</th>
            <th>Type Harga</th>
            <th>Output</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select name="GudangId" class="text2" id="GudangId" required>
                    <option value="0">-All Gudang-</option>
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
                <select id="TypeHarga" name="TypeHarga" required>
                    <option value="0" <?php print($userTypeHarga == 0 ? 'selected="selected"' : '');?>>Tanpa Harga</option>
                    <option value="1" <?php print($userTypeHarga == 1 ? 'selected="selected"' : '');?>>Harga Beli/HPP</option>
                    <option value="2" <?php print($userTypeHarga == 2 ? 'selected="selected"' : '');?>>Harga Jual</option>
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
            <th>Gudang</th>
            <th>Kode</th>
            <th>Nama Produk</th>
            <th>Satuan</th>
            <th>Stock</th>
            <?php
            if($userTypeHarga == 1){
                print('<th>Harga Beli</th>');
                print('<th>Nilai Stock</th>');
            }elseif($userTypeHarga == 2){
                print('<th>Harga Jual</th>');
                print('<th>Nilai Stock</th>');
            }
            if($userReportType == 1){
                print('<th>PO QTY</th>');
                print('<th>Stock + PO</th>');
            }elseif ($userReportType == 2){
                print('<th>SO QTY</th>');
                print('<th>Stock - SO</th>');
            }elseif ($userReportType == 3) {
                print('<th>PO QTY</th>');
                print('<th>SO QTY</th>');
                print('<th>Stock + PO - SO</th>');
            }
            ?>
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
                printf("<td nowrap='nowrap'>%s</td>",$row["wh_code"]);
                printf("<td nowrap='nowrap'>%s</td>",$row["item_code"]);
                printf("<td nowrap='nowrap'>%s</td>",$row["item_name"]);
                printf("<td nowrap='nowrap'>%s</td>",$row["s_uom_code"]);
                printf("<td align='right'>%s</td>",decFormat($row["qty_stock"],2));
                $sld = $row["qty_stock"];
                if ($userTypeHarga == 1) {
                    printf("<td align='right'>%s</td>", decFormat($row["hrg_beli"], 0));
                    printf("<td align='right'>%s</td>", decFormat(round($row["qty_stock"] * $row["hrg_beli"], 0), 0));
                    $tOtal+= round($row["qty_stock"] * $row["hrg_beli"],0);
                }elseif($userTypeHarga == 2){
                    printf("<td align='right'>%s</td>", decFormat($row["hrg_jual"], 0));
                    printf("<td align='right'>%s</td>", decFormat(round($row["qty_stock"] * $row["hrg_jual"], 0), 0));
                    $tOtal+= round($row["qty_stock"] * $row["hrg_jual"],0);
                }
                if($userReportType == 1){
                    printf("<td align='right'>%s</td>",decFormat($row["po_qty"],2));
                    printf("<td align='right'>%s</td>",decFormat($row["qty_stock"] + $row["po_qty"],2));
                    $sld = $row["qty_stock"] + $row["po_qty"];
                }elseif ($userReportType == 2){
                    printf("<td align='right'>%s</td>",decFormat($row["so_qty"],2));
                    printf("<td align='right'>%s</td>",decFormat($row["qty_stock"] - $row["so_qty"],2));
                    $sld = $row["qty_stock"] - $row["so_qty"];
                }elseif ($userReportType == 3) {
                    printf("<td align='right'>%s</td>",decFormat($row["po_qty"],2));
                    printf("<td align='right'>%s</td>",decFormat($row["so_qty"],2));
                    printf("<td align='right'>%s</td>",decFormat($row["qty_stock"] + $row["po_qty"] - $row["so_qty"],2));
                    $sld = $row["qty_stock"] + $row["po_qty"] - $row["so_qty"];
                }
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
        print("<tr>");
        if ($userTypeHarga > 0) {
            print("<td colspan='7' align='right'>Total Nilai Stock&nbsp;</td>");
            printf("<td align='right'>%s</td>", decFormat($tOtal, 0));
            if ($userReportType == 1) {
                print('<td colspan="2">&nbsp;</td>');
            } elseif ($userReportType == 2) {
                print('<td colspan="2">&nbsp;</td>');
            } elseif ($userReportType == 3) {
                print('<td colspan="3">&nbsp;</td>');
            }
        }
        print("</tr>");
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
