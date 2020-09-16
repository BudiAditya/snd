<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Rekapitulasi Transfer Stock</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#StartDate").customDatePicker({ showOn: "focus" });
            $("#EndDate").customDatePicker({ showOn: "focus" });
        });
    </script>

</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br/>
<form id="frm" name="frmReport" method="post">
    <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
        <tr class="center">
            <th colspan="3"><b>Rekapitulasi Transfer Stock Barang</b></th>
            <th>Jenis Laporan:</th>
            <th colspan="2"><select name="JnsLaporan" id="JnsLaporan">
                    <option value="1" <?php print($JnsLaporan == 1 ? 'selected="selected"' : '');?>>1 - Laporan Transfer</option>
                    <option value="2" <?php print($JnsLaporan == 2 ? 'selected="selected"' : '');?>>2 - Rekap Barang Transfer</option>
                </select>
            </th>
        </tr>
        <tr class="center">
            <th>Dari Gudang</th>
            <th>Status</th>
            <th>Dari Tanggal</th>
            <th>Sampai Tanggal</th>
            <th>Output</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select name="GudangId" class="text2" id="GudangId" required>
                   <option value="0">- Semua Gudang -</option>
                    <?php
                    /** @var $cab Warehouse[]*/
                    foreach ($gudangs as $cab) {
                        if ($cab->Id == $GudangId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $cab->Id, $cab->CabCode, $cab->WhCode);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $cab->Id, $cab->CabCode, $cab->WhCode);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select name="NpbStatus" class="text2" id="NpbStatus" required>
                    <option value="-1" <?php print($NpbStatus == -1 ? 'selected="selected"' : '');?>>- Semua -</option>
                    <option value="0" <?php print($NpbStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                    <option value="1" <?php print($NpbStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                    <option value="2" <?php print($NpbStatus == 2 ? 'selected="selected"' : '');?>>2 - Approved</option>
                    <option value="3" <?php print($NpbStatus == 3 ? 'selected="selected"' : '');?>>3 - Vois</option>

                </select>
            </td>
            <td><input type="text" class="text2" maxlength="10" size="10" id="StartDate" name="StartDate" value="<?php printf(date('d-m-Y',$StartDate));?>"/></td>
            <td><input type="text" class="text2" maxlength="10" size="10" id="EndDate" name="EndDate" value="<?php printf(date('d-m-Y',$EndDate));?>"/></td>
            <td>
                <select id="Output" name="Output" required>
                    <option value="0" <?php print($Output == 0 ? 'selected="selected"' : '');?>>0 - Web Html</option>
                    <option value="1" <?php print($Output == 1 ? 'selected="selected"' : '');?>>1 - Excel</option>
                </select>
            </td>
            <td><button type="submit" formaction="<?php print($helper->site_url("inventory.transfer/report")); ?>"><b>Proses</b></button></td>
        </tr>
    </table>
</form>
<!-- start web report -->
<?php  if ($Reports != null){
    if ($JnsLaporan == 1){
    ?>
    <h3>Laporan Transfer Stock Barang</h3>
    <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Dari Gudang</th>
            <th>Ke Gudang</th>
            <th>Tanggal</th>
            <th>No.Transfer</th>
            <th>Status</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Satuan</th>
            <th>Q T Y</th>
        </tr>
        <?php
        $nmr = 0;
        $tQty = 0;
        $npn = null;
        while ($row = $Reports->FetchAssoc()) {
            if ($npn <> $row["npb_no"]) {
                $nmr++;
                $url = $helper->site_url("inventory.transfer/view/" . $row["id"]);
                print("<tr valign='Top'>");
                printf("<td>%s</td>", $nmr);
                printf("<td>%s</td>", $row["fr_wh_code"]);
                printf("<td>%s</td>", $row["to_wh_code"]);
                printf("<td>%s</td>", date('d-m-Y', strtotime($row["npb_date"])));
                printf("<td><a href= '%s' target='_blank'>%s</a></td>", $url, $row["npb_no"]);
                if ($row["npb_status"] == 0) {
                    print("<td nowrap='nowrap'>Draft</td>");
                }elseif ($row["npb_status"] == 1) {
                    print("<td nowrap='nowrap'>Posted</td>");
                }elseif ($row["npb_status"] == 2) {
                    print("<td nowrap='nowrap'>Approved</td>");
                }else{
                    print("<td nowrap='nowrap'>Void</td>");
                }
            }else{
                print("<td>&nbsp;</td>");
                print("<td>&nbsp;</td>");
                print("<td>&nbsp;</td>");
                print("<td>&nbsp;</td>");
                print("<td>&nbsp;</td>");
                print("<td>&nbsp;</td>");
            }
            printf("<td nowrap='nowrap'>%s</td>", $row["item_code"]);
            printf("<td nowrap='nowrap'>%s</td>", $row["item_name"]);
            printf("<td nowrap='nowrap'>%s</td>", $row["satuan"]);
            printf("<td align='right'>%s</td>", number_format($row["qty"], 0));
            print("</tr>");
            $tQty+= $row["qty"];
            $npn = $row["npb_no"];
        }
        print("<tr>");
        print("<td colspan='9' align='right'>Total</td>");
        printf("<td align='right'>%s</td>",number_format($tQty,0));
        print("</tr>");
        ?>
    </table>
<?php }else{ ?>
        <h3>Rekapitulasi Transfer Stock Barang</h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Dari Gudang</th>
                <th>Ke Gudang</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th>Qty</th>
            </tr>
            <?php
            $nmr = 1;
            $sqty = 0;
            $cbs = null;
            while ($row = $Reports->FetchAssoc()) {
                print("<tr valign='Top'>");
                printf("<td>%s</td>", $nmr++);
                printf("<td>%s</td>", $row['fr_wh_code']);
                printf("<td>%s</td>", $row['to_wh_code']);
                printf("<td>%s</td>",$row['item_code']);
                printf("<td>%s</td>",$row['item_name']);
                printf("<td>%s</td>",$row['satuan']);
                printf("<td align='right'>%s</td>",number_format($row['sum_qty'],0));
                print("</tr>");
                $sqty+= $row['sum_qty'];
            }
            print("<tr>");
            print("<td colspan='6' align='right'>Total.....</td>");
            printf("<td align='right'>%s</td>",number_format($sqty,0));
            print("</tr>");
            ?>
        </table>
<?php }} ?>
<!-- </body> -->
</html>
