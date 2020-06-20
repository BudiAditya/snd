<!DOCTYPE HTML>
<html>
<?php /** @var $customers Customer[] */ ?>
<head>
	<title>SND System - Rekapitulasi Sales Order</title>
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
            <th colspan="4"><b>Rekapitulasi Sales Order (SO)</b></th>
            <th>Jenis Laporan:</th>
            <th colspan="2"><select name="JnsLaporan" id="JnsLaporan">
                    <option value="1" <?php print($JnsLaporan == 1 ? 'selected="selected"' : '');?>>1 - Rekap Per Bukti</option>
                    <option value="2" <?php print($JnsLaporan == 2 ? 'selected="selected"' : '');?>>2 - Rekap Order Detail</option>
                    <option value="3" <?php print($JnsLaporan == 3 ? 'selected="selected"' : '');?>>3 - Rekap Order Per Item</option>
                </select>
            </th>
        </tr>
        <tr class="center">
            <th>Cabang</th>
            <th>Customer</th>
            <th>Status</th>
            <th>Dari Tanggal</th>
            <th>Sampai Tanggal</th>
            <th>Output</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select name="CabangId" class="text2" id="CabangId" style="width: 150px" required>
                <?php if($userLevel > 3){ ?>
                    <option value="0">- Semua Cabang -</option>
                    <?php
                    foreach ($cabangs as $cab) {
                        if ($cab->Id == $CabangId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                        }
                    }
                    ?>
                <?php }else{
                        printf('<option value="%d">%s - %s</option>', $userCabId, $userCabCode, $userCabName);
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
                <select id="SoStatus" name="SoStatus" required>
                    <option value="-1" <?php print($Status == -1 ? 'selected="selected"' : '');?>> - Semua Status -</option>
                    <option value="1" <?php print($Status == 1 ? 'selected="selected"' : '');?>> 1 - Open </option>
                    <option value="2" <?php print($Status == 2 ? 'selected="selected"' : '');?>> 2 - Close </option>
                    <option value="3" <?php print($Status == 3 ? 'selected="selected"' : '');?>> 3 - Batal </option>
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
            <td><button type="submit" formaction="<?php print($helper->site_url("ar.order/report")); ?>"><b>Proses</b></button></td>
        </tr>
    </table>
</form>
<!-- start web report -->
<?php  if ($Reports != null){
    if ($JnsLaporan < 3){
    ?>
    <h3>Rekapitulasi Sales Order</h3>
    <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Cabang</th>
            <th>Tanggal</th>
            <th>No. Bukti</th>
            <th>Nama Customer</th>
            <th>Keterangan</th>
            <th>Nilai Order</th>
            <th>Status</th>
            <?php
            if ($JnsLaporan == 2){
                print("<th nowrap='nowrap'>Kode Barang</th>");
                print("<th nowrap='nowrap'>Nama Barang</th>");
                print("<th>Order</th>");
                print("<th>Diterima</th>");
                print("<th>Outstanding</th>");
                print("<th>Harga</th>");
                print("<th>Jumlah</th>");
            }
            ?>
        </tr>
        <?php
            $nmr = 1;
            $total = 0;
            $subtotal = 0;
            $url = null;
            $sts = null;
            $ivn = null;
            $sma = false;
            while ($row = $Reports->FetchAssoc()) {
                if ($ivn <> $row["so_no"]){
                    $nmr++;
                    $sma = false;
                }else{
                    $sma = true;
                }
                if (!$sma) {
                    $url = $helper->site_url("ap.order/view/".$row["id"]);
                    print("<tr valign='Top'>");
                    printf("<td>%s</td>",$nmr);
                    printf("<td nowrap='nowrap'>%s</td>",$row["cabang_code"]);
                    printf("<td nowrap='nowrap'>%s</td>",date('d-m-Y',strtotime($row["so_date"])));
                    printf("<td><a href= '%s' target='_blank'>%s</a></td>",$url ,$row["so_no"]);
                    printf("<td nowrap='nowrap'>%s</td>",$row["customer_name"].' ('.$row["customer_code"].')');
                    printf("<td nowrap='nowrap'>%s</td>",$row["so_descs"]);
                    printf("<td align='right'>%s</td>",number_format($row["total_amount"],0));
                    if ($row["so_status"] == 1){
                        print("<td>Open</td>");
                    }else{
                        print("<td>Closed</td>");
                    }
                    if ($JnsLaporan == 1){
                        print("</tr>");
                    };
                    $nmr++;
                    $total+= $row["total_amount"];
                }
                if ($JnsLaporan == 2){
                    if ($sma) {
                        print("</tr>");
                        print("<td colspan='8'>&nbsp;</td>");
                    }
                    printf("<td nowrap='nowrap'>%s</td>", $row['item_code']);
                    printf("<td nowrap='nowrap'>%s</td>", $row['item_descs']);
                    printf("<td align='right'>%s</td>", number_format($row['order_qty'], 0));
                    printf("<td align='right'>%s</td>", number_format($row['send_qty'], 0));
                    printf("<td align='right'>%s</td>", number_format($row['order_qty'] - $row['send_qty'], 0));
                    printf("<td align='right' >%s</td>", number_format($row['price'], 0));
                    printf("<td align='right'>%s</td>", number_format($row['sub_total'], 0));
                    print("</tr>");
                    $subtotal+= $row['sub_total'];
                }
                $ivn = $row["so_no"];
            }
        print("<tr>");
        print("<td colspan='6' align='right'>Total Order</td>");
        printf("<td align='right'>%s</td>",number_format($total,0));
        print("<td>&nbsp;</td>");
        if ($JnsLaporan == 2) {
            print("<td colspan='6'>&nbsp;</td>");
            printf("<td align='right'>%s</td>", number_format($subtotal, 0));
        }
        print("</tr>");
        ?>
    </table>
    <?php }else{ ?>
        <h3>Rekapitulasi Item Sales Order</h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Customer</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                <th>Order</th>
                <th>Dikirim</th>
                <th>Sisa</th>
            </tr>
            <?php
            $nmr = 0;
            $oqty = 0;
            $sqty = 0;
            $tqty = 0;
            $snilai = 0;
            while ($row = $Reports->FetchAssoc()) {
                $nmr++;
                print("<tr valign='Top'>");
                printf("<td>%s</td>",$nmr);
                printf("<td>%s - %s</td>",$row['customer_code'],$row['customer_name']);
                printf("<td>%s</td>",$row['item_code']);
                printf("<td>%s</td>",$row['item_descs']);
                printf("<td>%s</td>",$row['satuan']);
                printf("<td align='right'>%s</td>",number_format($row['sum_orderqty'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_sendqty'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_outstandqty'],0));
                print("</tr>");
                $oqty+= $row['sum_orderqty'];
                $sqty+= $row['sum_sendqty'];
                $tqty+= $row['sum_outstandqty'];
            }
            print("<tr>");
            print("<td colspan='5' align='right'>Total.....</td>");
            printf("<td align='right'>%s</td>",number_format($oqty,0));
            printf("<td align='right'>%s</td>",number_format($sqty,0));
            printf("<td align='right'>%s</td>",number_format($tqty,0));
            print("</tr>");
            ?>
        </table>
        <!-- end web report -->
<?php }} ?>
<!-- </body> -->
</html>
