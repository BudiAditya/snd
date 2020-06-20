<!DOCTYPE HTML>
<html>
<head>
	<title>SND System | Profit Penjualan</title>
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
<br />
<form id="frm" name="frmReport" method="post">
    <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
        <tr class="center">
            <th colspan="2"><b>Profit Penjualan</b></th>
            <th>Jenis Laporan:</th>
            <th colspan="2"><select name="JnsLaporan" id="JnsLaporan">
                    <option value="1" <?php print($JnsLaporan == 1 ? 'selected="selected"' : '');?>>1 - Profit Per Invoice</option>
                    <option value="2" <?php print($JnsLaporan == 2 ? 'selected="selected"' : '');?>>2 - Profit Per Tanggal</option>
                    <option value="3" <?php print($JnsLaporan == 3 ? 'selected="selected"' : '');?>>3 - Profit Per Bulan</option>
                    <option value="4" <?php print($JnsLaporan == 4 ? 'selected="selected"' : '');?>>4 - Profit Per Item</option>
                </select>
            </th>
        </tr>
        <tr class="center">
            <th>Gudang</th>
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
                /** @var $gudangs Warehouse[] */
                foreach ($gudangs as $cab) {
                    if ($cab->Id == $GudangId) {
                        printf('<option value="%d" selected="selected">%s - %s</option>', $cab->Id, $cab->WhCode, $cab->WhName);
                    } else {
                        printf('<option value="%d">%s - %s</option>', $cab->Id, $cab->WhCode, $cab->WhName);
                    }
                }
                ?>
                </select>
            </td>
            <td><input type="text" class="text2" maxlength="10" size="10" id="StartDate" name="StartDate" value="<?php printf(date('d-m-Y',$StartDate));?>"/></td>
            <td><input type="text" class="text2" maxlength="10" size="10" id="EndDate" name="EndDate" value="<?php printf(date('d-m-Y',$EndDate));?>"/></td>
            <td>
                <select id="Output" name="Output" required>
                    <option value="0" <?php print($Output == 0 ? 'selected="selected"' : '');?>>0 - Web Html</option>
                </select>
            </td>
            <td><button type="submit" formaction="<?php print($helper->site_url("report.profit")); ?>"><b>Proses</b></button></td>
        </tr>
    </table>
</form>
<!-- start web report -->
<?php  if ($Reports != null){
    if ($JnsLaporan == 1){
    ?>
        <h3>Profit Per Transsaksi/Invoice</h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Gudang</th>
                <th>Tanggal</th>
                <th>No. Invoice</th>
                <th>Customer</th>
                <th>Keterangan</th>
                <th>Penjualan (+)</th>
                <th>Retur (-)</th>
                <th>Pokok (-)</th>
                <th>Profit</th>
                <th>%</th>
            </tr>
            <?php
                $nmr = 0;
                $sumSale = 0;
                $sumHpp = 0;
                $sumReturn = 0;
                $sumProfit = 0;
                while ($row = $Reports->FetchAssoc()) {
                    $nmr++;
                    print("<tr valign='Top'>");
                    printf("<td>%s</td>", $nmr);
                    printf("<td nowrap='nowrap'>%s</td>", $row["gudang_code"]);
                    printf("<td>%s</td>", date('d-m-Y', strtotime($row["invoice_date"])));
                    printf("<td>%s</td>", $row["invoice_no"]);
                    printf("<td nowrap='nowrap'>%s</td>", $row["customer_name"]);
                    printf("<td nowrap='nowrap'>%s</td>", $row["invoice_descs"]);
                    printf("<td align='right'>%s</td>", number_format($row["total_amount"], 0));
                    printf("<td align='right'>%s</td>", number_format($row["total_return"], 0));
                    printf("<td align='right'>%s</td>", number_format($row["total_hpp"], 0));
                    printf("<td align='right'>%s</td>", number_format($row["total_amount"] - $row["total_return"] - $row["total_hpp"], 0));
                    if ($row["total_hpp"] > 0) {
                        printf("<td align='right'>%s</td>", number_format((($row["total_amount"] - $row["total_return"] - $row["total_hpp"]) / $row["total_hpp"]) * 100, 0));
                    }else{
                        if (($row["total_amount"] - $row["total_return"] - $row["total_hpp"]) >0) {
                            printf("<td align='right'>%s</td>", 100);
                        }else{
                            printf("<td align='right'>%s</td>", 0);
                        }
                    }
                    print("</tr>");
                    $sumSale+= $row["total_amount"];
                    $sumHpp+= $row["total_hpp"];
                    $sumProfit+= $row["total_amount"] - $row["total_return"] - $row["total_hpp"];
                }
            print("<tr>");
            print("<td colspan='6' align='right'>T o t a l</td>");
            printf("<td align='right'>%s</td>",number_format($sumSale,0));
            printf("<td align='right'>%s</td>",number_format($sumReturn,0));
            printf("<td align='right'>%s</td>",number_format($sumHpp,0));
            printf("<td align='right'>%s</td>",number_format($sumProfit,0));
            if ($sumHpp > 0) {
                printf("<td align='right'>%s</td>", number_format(($sumProfit / $sumHpp) * 100, 0));
            }else{
                if ($sumProfit > 0) {
                    printf("<td align='right'>%s</td>", 100);
                }else{
                    printf("<td align='right'>%s</td>", 0);
                }
            }
            print("</tr>");
            ?>
        </table>
<?php } elseif ($JnsLaporan == 2){
        ?>
        <h3>Profit Per Tanggal</h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Cabang</th>
                <th>Tanggal</th>
                <th>Penjualan (+)</th>
                <th>Retur (-)</th>
                <th>Pokok (-)</th>
                <th>Profit</th>
            </tr>
            <?php
            $nmr = 0;
            $sumSale = 0;
            $sumHpp = 0;
            $sumReturn = 0;
            $sumProfit = 0;
            while ($row = $Reports->FetchAssoc()) {
                $nmr++;
                print("<tr valign='Top'>");
                printf("<td>%s</td>", $nmr);
                printf("<td nowrap='nowrap'>%s</td>", $row["cabang_code"]);
                printf("<td>%s</td>", date('d-m-Y', strtotime($row["invoice_date"])));
                printf("<td align='right'>%s</td>", number_format($row["sumSale"], 0));
                printf("<td align='right'>%s</td>", number_format($row["sumReturn"], 0));
                printf("<td align='right'>%s</td>", number_format($row["sumHpp"], 0));
                printf("<td align='right'>%s</td>", number_format($row["sumSale"] - $row["sumReturn"] - $row["sumHpp"], 0));
                print("</tr>");
                $sumSale+= $row["sumSale"];
                $sumReturn+= $row["sumReturn"];
                $sumHpp+= $row["sumHpp"];
                $sumProfit+= $row["sumSale"] - $row["sumHpp"];
            }
            print("<tr>");
            print("<td colspan='3' align='right'>T o t a l</td>");
            printf("<td align='right'>%s</td>",number_format($sumSale,0));
            printf("<td align='right'>%s</td>",number_format($sumReturn,0));
            printf("<td align='right'>%s</td>",number_format($sumHpp,0));
            printf("<td align='right'>%s</td>",number_format($sumProfit,0));
            print("</tr>");
            ?>
        </table>
<?php } elseif ($JnsLaporan == 3){
        ?>
        <h3>Profit Per Bulan</h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Cabang</th>
                <th>Bulan</th>
                <th>Penjualan (+)</th>
                <th>Retur (-)</th>
                <th>Pokok (-)</th>
                <th>Profit</th>
            </tr>
            <?php
            $nmr = 0;
            $sumSale = 0;
            $sumReturn = 0;
            $sumHpp = 0;
            $sumProfit = 0;
            while ($row = $Reports->FetchAssoc()) {
                $nmr++;
                print("<tr valign='Top'>");
                printf("<td>%s</td>", $nmr);
                printf("<td nowrap='nowrap'>%s</td>", $row["cabang_code"]);
                printf("<td>%s - %s</td>", $row["tahun"],$row["bulan"]);
                printf("<td align='right'>%s</td>", number_format($row["sumSale"], 0));
                printf("<td align='right'>%s</td>", number_format($row["sumReturn"], 0));
                printf("<td align='right'>%s</td>", number_format($row["sumHpp"], 0));
                printf("<td align='right'>%s</td>", number_format($row["sumSale"] - $row["sumReturn"] - $row["sumHpp"], 0));
                print("</tr>");
                $sumSale+= $row["sumSale"];
                $sumReturn+= $row["sumReturn"];
                $sumHpp+= $row["sumHpp"];
                $sumProfit+= $row["sumSale"] - $row["sumReturn"] - $row["sumHpp"];
            }
            print("<tr>");
            print("<td colspan='3' align='right'>T o t a l</td>");
            printf("<td align='right'>%s</td>",number_format($sumSale,0));
            printf("<td align='right'>%s</td>",number_format($sumReturn,0));
            printf("<td align='right'>%s</td>",number_format($sumHpp,0));
            printf("<td align='right'>%s</td>",number_format($sumProfit,0));
            print("</tr>");
            ?>
        </table>
<?php }else{ ?>
        <h3>Profit Per Item</h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>QTY Jual</th>
				<th>QTY Retur</th>
				<th>QTY Nett</th>
                <th>Penjualan (+)</th>
                <th>Retur (-)</th>
                <th>Pokok (-)</th>
                <th>Profit</th>
            </tr>
            <?php
            $nmr = 0;
            $sqty = 0;
			$rqty = 0;
            $snilai = 0;
            $sreturn = 0;
            $shpp = 0;
            $sprofit = 0;
            while ($row = $Reports->FetchAssoc()) {
                $nmr++;
                print("<tr valign='Top'>");
                printf("<td>%s</td>", $nmr);
                printf("<td>%s</td>",$row['item_code']);
                printf("<td>%s</td>",$row['item_descs']);
                printf("<td>%s</td>",$row['satuan']);
                printf("<td align='right'>%s</td>",number_format($row['sum_qty'],0));
				printf("<td align='right'>%s</td>",number_format($row['sum_qty_return'],0));
				printf("<td align='right'>%s</td>",number_format($row['sum_qty']-$row['sum_qty_return'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_total'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_return'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_hpp'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_total'] - $row['sum_return'] - $row['sum_hpp'],0));
                print("</tr>");
                $sqty+= $row['sum_qty'];
				$rqty+= $row['sum_qty_return'];
                $snilai+= $row['sum_total'];
                $sreturn+= $row['sum_return'];
                $shpp+= $row['sum_hpp'];
                $sprofit+= $row['sum_total'] - $row['sum_return'] - $row['sum_hpp'];
            }
            print("<tr>");
            print("<td colspan='4' align='right'>Total</td>");
            printf("<td align='right'>%s</td>",number_format($sqty,0));
			printf("<td align='right'>%s</td>",number_format($rqty,0));
			printf("<td align='right'>%s</td>",number_format($sqty - $rqty,0));
            printf("<td align='right'>%s</td>",number_format($snilai,0));
            printf("<td align='right'>%s</td>",number_format($sreturn,0));
            printf("<td align='right'>%s</td>",number_format($shpp,0));
            printf("<td align='right'>%s</td>",number_format($sprofit,0));
            print("</tr>");
            ?>
        </table>
<!-- end web report -->
<?php }} ?>
<!-- </body> -->
</html>
