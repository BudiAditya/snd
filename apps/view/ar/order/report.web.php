<!DOCTYPE HTML>
<html>
<?php /** @var $customers Customer[] */
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
?>
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
            <th colspan="5"><b>Rekapitulasi Sales Order (SO)</b></th>
            <th>Jenis Laporan:</th>
            <th colspan="2"><select name="JnsLaporan" id="JnsLaporan">
                    <option value="1" <?php print($JnsLaporan == 1 ? 'selected="selected"' : '');?>>1 - Rekap Order Detail</option>
                    <option value="2" <?php print($JnsLaporan == 2 ? 'selected="selected"' : '');?>>2 - Rekap Order Per Item</option>
                </select>
            </th>
        </tr>
        <tr class="center">
            <th>Cabang</th>
            <th>Salesman</th>
            <th>Outlet/Customer</th>
            <th>Order Status</th>
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
                <select id="SalesId" name="SalesId" style="width: 150px" required>
                    <option value="0">- Semua Salesman -</option>
                    <?php
                    /** @var $salesman Salesman[]*/
                    foreach ($salesman as $sman) {
                        if ($SalesId == $sman->Id){
                            printf('<option value="%d" selected="selected">%s (%s)</option>',$sman->Id,$sman->SalesName,$sman->SalesCode);
                        }else{
                            printf('<option value="%d">%s (%s)</option>',$sman->Id,$sman->SalesName,$sman->SalesCode);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="CustomersId" name="CustomersId" style="width: 150px" required>
                    <option value="0">- Semua Customer -</option>
                    <?php
                    foreach ($customers as $customer) {
                        if ($CustomersId == $customer->Id){
                            printf('<option value="%d" selected="selected">%s (%s)</option>',$customer->Id,$customer->CusName,$customer->CusCode);
                        }else{
                            printf('<option value="%d">%s (%s)</option>',$customer->Id,$customer->CusName,$customer->CusCode);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="OrderStatus" name="OrderStatus" required>
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
            <td>
                <button type="submit" formaction="<?php print($helper->site_url("ar.order/report")); ?>"><b>Proses</b></button>
                <input type="button" class="button" onclick="printDiv('printArea')" value="Print"/>
            </td>
        </tr>
    </table>
</form>
<!-- start web report -->
<div id="printArea">
<?php  if ($Reports != null){
    if ($JnsLaporan == 1){
    ?>
    <h3>Rekapitulasi Sales Order</h3>
    <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Cabang</th>
            <th>Tanggal</th>
            <th>Salesman</th>
            <th>Outlet/Customer</th>
            <th>Brand</th>
            <th nowrap='nowrap'>Kode</th>
            <th nowrap='nowrap'>Nama Barang</th>
            <th>Satuan</th>
            <th>Order</th>
            <th>Kirim</th>
            <th>Sisa</th>
            <th>Status</th>
        </tr>
        <?php
            $nmr = 1;
            $url = null;
            $sma = false;
            $sts = null;
            while ($row = $Reports->FetchAssoc()) {
                $url = $helper->site_url("ar.order/view/".$row["id"]);
                print("<tr valign='Top'>");
                printf("<td>%s</td>",$nmr++);
                printf("<td nowrap='nowrap'>%s</td>",$row["cabang_code"]);
                printf("<td><a href= '%s' target='_blank'>%s</a></td>",$url ,date('d-m-Y',strtotime($row["order_date"])));
                printf("<td nowrap='nowrap'>%s</td>",$row["sales_name"]);
                printf("<td nowrap='nowrap'>%s</td>",$row["cus_name"]);
                printf("<td nowrap='nowrap'>%s</td>", $row['brand_name']);
                printf("<td nowrap='nowrap'>%s</td>", $row['item_code']);
                printf("<td nowrap='nowrap'>%s</td>", $row['item_name']);
                printf("<td nowrap='nowrap'>%s</td>", $row['s_uom_code']);
                printf("<td align='right'>%s</td>", number_format($row['order_qty'], 0));
                printf("<td align='right'>%s</td>", number_format($row['send_qty'], 0));
                printf("<td align='right'>%s</td>", number_format($row['order_qty'] - $row['send_qty'], 0));
                if ($row['order_qty'] - $row['send_qty'] == 0){
                    $sts = 'Close';
                }elseif ($row['order_status'] == 0){
                    $sts = 'New';
                }elseif ($row['order_status'] == 1){
                    $sts = 'Open';
                }elseif ($row['order_status'] == 2){
                    $sts = 'Close';
                }
                printf("<td nowrap='nowrap'>%s</td>", $sts);
                print("</tr>");
            }
        ?>
    </table>
    <?php }else{ ?>
        <h3>Rekapitulasi Barang Sales Order</h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Brand</th>
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
                printf("<td>%s</td>",$row['brand_name']);
                printf("<td>%s</td>",$row['item_code']);
                printf("<td>%s</td>",$row['item_name']);
                printf("<td>%s</td>",$row['s_uom_code']);
                printf("<td align='right'>%s</td>",number_format($row['sumOrderQty'],0));
                printf("<td align='right'>%s</td>",number_format($row['sumSendQty'],0));
                printf("<td align='right'>%s</td>",number_format($row['sumOrderQty']-$row['sumSendQty'],0));
                print("</tr>");
            }
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
