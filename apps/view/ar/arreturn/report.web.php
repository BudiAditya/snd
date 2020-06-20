<!DOCTYPE HTML>
<html>
<?php /** @var $customers Customer[] */
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
?>
<head>
	<title>SND System - Rekapitulasi Retur Penjualan</title>
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
            <th colspan="4"><b>Rekapitulasi Retur Penjualan</b></th>
            <th>Jenis Laporan:</th>
            <th colspan="2"><select name="JnsLaporan" id="JnsLaporan">
                    <option value="1" <?php print($JnsLaporan == 1 ? 'selected="selected"' : '');?>>1 - Rekap Per Bukti</option>
                    <option value="2" <?php print($JnsLaporan == 2 ? 'selected="selected"' : '');?>>2 - Rekap Retur Detail</option>
                    <option value="3" <?php print($JnsLaporan == 3 ? 'selected="selected"' : '');?>>3 - Rekap Item Retur</option>
                </select>
            </th>
        </tr>
        <tr class="center">
            <th>Cabang</th>
            <th>Customer</th>
            <th>Kondisi</th>
            <th>Dari Tanggal</th>
            <th>Sampai Tanggal</th>
            <th>Output</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select name="CabangId" class="text2" id="CabangId" required>
                <?php if($userLevel > 3){ ?>
                    <option value="0">- Semua Cabang -</option>
                    <?php
                    foreach ($cabangs as $cab) {
                        if ($cab->Id == $CabangId) {
                            printf('<option value="%d" selected="selected">%s</option>', $cab->Id, $cab->Kode);
                        } else {
                            printf('<option value="%d">%s</option>', $cab->Id, $cab->Kode);
                        }
                    }
                    ?>
                <?php }else{
                        printf('<option value="%d">%s</option>', $userCabId, $userCabCode);
                }?>
                </select>
            </td>
            <td>
                <select id="CustomersId" name="CustomersId" style="width: 150px" required>
                    <option value="0">- Semua Customer -</option>
                    <?php
                    foreach ($customers as $customer) {
                        if ($CustomersId == $customer->Id){
                            printf('<option value="%d" selected="selected">%s (%s)</option>',$customer->Id,$customer->CusCode,$customer->CusName);
                        }else{
                            printf('<option value="%d">%s (%s)</option>',$customer->Id,$customer->CusCode,$customer->CusName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="Kondisi" name="Kondisi" required>
                    <option value="0" <?php print($Kondisi == 0 ? 'selected="selected"' : '');?>>0 - Semua Kondisi -</option>
                    <option value="1" <?php print($Kondisi == 1 ? 'selected="selected"' : '');?>>1 - Bagus</option>
                    <option value="2" <?php print($Kondisi == 2 ? 'selected="selected"' : '');?>>2 - Rusak</option>
                    <option value="3" <?php print($Kondisi == 3 ? 'selected="selected"' : '');?>>3 - Expire</option>
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
            <td>
                <button type="submit" formaction="<?php print($helper->site_url("ar.arreturn/report")); ?>"><b>Proses</b></button>
                <input type="button" class="button" onclick="printDiv('printArea')" value="Print"/>
            </td>
        </tr>
    </table>
</form>
<!-- start web report -->
<div id="printArea">
<?php  if ($Reports != null){
    if ($JnsLaporan < 3){
    ?>
    <h3>Rekapitulasi Retur Penjualan</h3>
    <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Cabang</th>
            <th>Tanggal</th>
            <th>No. Bukti</th>
            <th>Nama Customer</th>
            <th>Keterangan</th>
            <th>Nilai Retur</th>
            <?php
            if ($JnsLaporan == 2){
                print("<th nowrap='nowrap'>Ex.Invoice No.</th>");
                print("<th nowrap='nowrap'>Kode Barang</th>");
                print("<th nowrap='nowrap'>Nama Barang</th>");
                print("<th>Kondisi</th>");
                print("<th>QTY</th>");
                print("<th>Harga</th>");
                print("<th>DPP</th>");
                print("<th>PPN</th>");
                print("<th>Jumlah</th>");
            }
            ?>
        </tr>
        <?php
            $nmr = 1;
            $total = 0;
            $subtotal = 0;
            $url = null;
            $kds = null;
            $ivn = null;
            $sma = false;
            $dpp = 0;
            $ppn = 0;
            while ($row = $Reports->FetchAssoc()) {
                if ($ivn <> $row["rj_no"]){
                    $nmr++;
                    $sma = false;
                }else{
                    $sma = true;
                }
                if (!$sma) {
                    $url = $helper->site_url("ar.arreturn/view/".$row["id"]);
                    print("<tr valign='Top'>");
                    printf("<td>%s</td>",$nmr);
                    printf("<td nowrap='nowrap'>%s</td>",$row["cabang_code"]);
                    printf("<td>%s</td>",date('d-m-Y',strtotime($row["rj_date"])));
                    printf("<td><a href= '%s' target='_blank'>%s</a></td>",$url ,$row["rj_no"]);
                    printf("<td nowrap='nowrap'>%s</td>",$row["customer_name"].' ('.$row["customer_code"].')');
                    printf("<td nowrap='nowrap'>%s</td>",$row["rj_descs"]);
                    printf("<td align='right'>%s</td>",number_format($row["rj_amount"],0));
                        if ($JnsLaporan == 1){
                            print("</tr>");
                        };
                    $nmr++;
                    $total+= $row["rj_amount"];
                }
                if ($JnsLaporan == 2){
                    if ($sma) {
                        print("</tr>");
                        print("<td colspan='7'>&nbsp;</td>");
                    }
                    if ($row['kondisi'] == 1){
                        $kds = "Bagus";
                    }elseif ($row['kondisi'] == 2){
                        $kds = "Rusak";
                    }elseif ($row['kondisi'] == 3) {
                        $kds = "Expire";
                    }else{
                        $kds = "N/A";
                    }
                    $dpp = round($row['qty_retur'] * $row['price'],0)-$row["disc_amount"];
                    $ppn = round($dpp/10,0);
                    printf("<td nowrap='nowrap'>%s</td>", $row['ex_invoice_no']);
                    printf("<td nowrap='nowrap'>%s</td>", $row['item_code']);
                    printf("<td nowrap='nowrap'>%s</td>", $row['item_descs']);
                    printf("<td nowrap='nowrap'>%s</td>", $kds);
                    printf("<td align='right'>%s</td>", number_format($row['qty_retur'], 0));
                    printf("<td align='right' >%s</td>", number_format($row['price'], 0));
                    printf("<td align='right'>%s</td>", number_format($dpp, 0));
                    printf("<td align='right'>%s</td>", number_format($ppn,0));
                    printf("<td align='right'>%s</td>", number_format($dpp+$ppn, 0));
                    print("</tr>");
                    $subtotal+= $dpp+$ppn;
                }
                $ivn = $row["rj_no"];
            }
        print("<tr>");
        print("<td colspan='6' align='right'>Total Retur</td>");
        printf("<td align='right'>%s</td>",number_format($total,0));
        if ($JnsLaporan == 2) {
            print("<td colspan='8'>&nbsp;</td>");
            printf("<td align='right'>%s</td>", number_format($subtotal, 0));
        }
        print("</tr>");
        ?>
    </table>
    <?php }else{ ?>
        <h3>Rekapitulasi Item Retur Penjualan</h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th>Bagus</th>
                <th>Rusak</th>
                <th>Expire</th>
                <th>Nilai Retur</th>
            </tr>
            <?php
            $nmr = 0;
            $bqty = 0;
            $rqty = 0;
            $eqty = 0;
            $snilai = 0;
            while ($row = $Reports->FetchAssoc()) {
                $nmr++;
                print("<tr valign='Top'>");
                printf("<td>%s</td>", $nmr);
                printf("<td>%s</td>",$row['item_code']);
                printf("<td>%s</td>",$row['item_descs']);
                printf("<td>%s</td>",$row['satuan']);
                printf("<td align='right'>%s</td>",number_format($row['qty_bagus'],0));
                printf("<td align='right'>%s</td>",number_format($row['qty_rusak'],0));
                printf("<td align='right'>%s</td>",number_format($row['qty_expire'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_total'],0));
                print("</tr>");
                $bqty+= $row['qty_bagus'];
                $rqty+= $row['qty_rusak'];
                $eqty+= $row['qty_expire'];
                $snilai+= $row['sum_total'];
            }
            print("<tr>");
            print("<td colspan='4' align='right'>Total.....</td>");
            printf("<td align='right'>%s</td>",number_format($bqty,0));
            printf("<td align='right'>%s</td>",number_format($rqty,0));
            printf("<td align='right'>%s</td>",number_format($eqty,0));
            printf("<td align='right'>%s</td>",number_format($snilai,0));
            print("</tr>");
            ?>
        </table>
        <!-- end web report -->
<?php }} ?>
     <br>
    <?php if($Reports != null){ ?>
        <?php
    print('<i>* Printed by: '.$userName.'  - Time: '.date('d-m-Y h:i:s').' *</i>');
    ?>
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
