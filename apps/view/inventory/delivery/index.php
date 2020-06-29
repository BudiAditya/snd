<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Pengiriman Barang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        var urc = "<?php print($helper->site_url("inventory.delivery/process")); ?>";
        $(document).ready(function() {
            $("#StartDate").customDatePicker({ showOn: "focus" });
            $("#EndDate").customDatePicker({ showOn: "focus" });
            $("#cbAll").change(function(e) { cbAll_Change(this, e);	});

            $("#btnGenerate").click(function() {
                var test = $(".cbIds:checked");
                if (test.length == 0) {
                    alert("Belum ada data yang dipilih!");
                }else {
                    $("#frd").attr('action', urc).submit();
                }
            });

            $("#btnVoid").click(function() {
                var test = $(".cbIds:checked");
                if (test.length == 0) {
                    alert("Belum ada data yang dipilih!");
                }else {
                    $("#frd").attr('action', urv).submit();
                }
            });

        });

        function cbAll_Change(sender, e) {
            $(":checkbox.cbIds").each(function(idx, ele) {
                ele.checked = sender.checked;
            });
        }

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
        <tr>
            <th class="bold" colspan="10">PROSES PENGIRIMAN BARANG</th>
            <th colspan="2" class="bold">Action</th>
        </tr>
        <tr>
            <td><label for="CabangId">Cabang :</label></td>
            <td><select id="CabangId" name="cabId">
                    <?php
                    printf('<option value="%d" selected="selected">%s</option>',$cabangs->Id,$cabangs->Cabang);
                    ?>
                </select>
            </td>
            <td><label for="GudangId">Gudang :</label></td>
            <td><select id="GudangId" name="whsId">
                    <option value="0">Semua</option>
                    <?php
                    /** @var $warehouses Warehouse[] */
                    foreach ($warehouses as $gudang){
                        if ($whsId == $gudang->Id){
                            printf('<option value="%d" selected="selected">%s</option>',$gudang->Id,$gudang->WhCode);
                        }else{
                            printf('<option value="%d">%s</option>',$gudang->Id,$gudang->WhCode);
                        }
                    }
                    ?>
                </select>
            </td>
            <td><label for="StartDate">Tanggal :</label></td>
            <td><input type="text" class="text2" maxlength="10" size="8" id="StartDate" name="stDate" value="<?php printf(date('d-m-Y',$stDate));?>"/></td>
            <td><label for="EndDate">S/D Tgl :</label></td>
            <td><input type="text" class="text2" maxlength="10" size="8" id="EndDate" name="enDate" value="<?php printf(date('d-m-Y',$enDate));?>"/></td>
            <td><label for="StartDate">Status :</label></td>
            <td><select id="dStatus" name="dStatus">
                    <option value="0" <?php print($dStatus == 0 ? 'selected="selected"' : '') ;?>>0 - Belum Dikirim</option>
                    <option value="1" <?php print($dStatus == 1 ? 'selected="selected"' : '') ;?>>1 - Sudah Dikirim</option>
                </select>

            </td>
            <td>
                <button type="submit" formaction="<?php print($helper->site_url("inventory.delivery")); ?>"><b>Tampilkan</b></button>
                <input type="button" class="button" onclick="printDiv('printArea')" value="Print"/>
            </td>
            <td>
                <input type="button" id="btnGenerate" class="button" value="Proses"/>
            </td>
        </tr>
    </table>
</form>
<!-- start web report -->
<div id="printArea">
<?php
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
if ($invoices != null) {
    ?>
    <h3>DAFTAR PENGIRIMAN BARANG</h3>
    <form id="frd" name="frmDetail" method="post">
        <input type="hidden" name="rstDate" value="<?=$stDate?>"/>
        <input type="hidden" name="renDate" value="<?=$enDate?>"/>
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Nama Customer</th>
            <!--<th>Alamat</th>-->
            <!--<th>Area</th>-->
            <th>Tanggal</th>
            <th>No. Invoice</th>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>L</th>
            <th>S</th>
            <th>Q</th>
            <th>Status</th>
            <th>Pilih <input type="checkbox" id="cbAll" checked="checked"></th>
        </tr>
    <?php
    $nmr = 1;
    $csc = null;
    $ivd = null;
    $ivn = null;
    $ivi = 0;
    while ($data = $invoices->FetchAssoc()) {
        $rqty = $data["qty"];
        if ($rqty >= $data["s_uom_qty"] && $data["s_uom_qty"] > 0){
            $aqty = array();
            $sqty = round($rqty/$data["s_uom_qty"],2);
            $aqty = explode('.',$sqty);
            $lqty = $aqty[0];
            $sqty = $rqty - ($lqty * $data["s_uom_qty"]);
        }else {
            $lqty = 0;
            $sqty = $rqty;
        }
        print('<tr>');
        if ($csc <> $data["cus_code"]) {
            printf('<td>%d</td>', $nmr++);
            printf('<td nowrap="nowrap">%s</td>', $data["cus_code"] . ' - ' . $data["cus_name"]);
            //printf('<td nowrap="nowrap">%s</td>', $data["addr1"]);
            //printf('<td nowrap="nowrap">%s</td>', $data["area_code"]);
        }else{
            printf('<td colspan="2">&nbsp;</td>');
        }
        if ($ivd <> $data["invoice_date"]) {
            printf('<td nowrap="nowrap">%s</td>', $data["invoice_date"]);
        }else{
            print('<td>&nbsp;</td>');
        }
        if ($ivn <> $data["invoice_no"]) {
            printf('<td nowrap="nowrap">%s</td>', $data["invoice_no"]);
        }else{
            print('<td>&nbsp;</td>');
        }
        printf('<td nowrap="nowrap">%s</td>',$data["item_code"]);
        printf('<td nowrap="nowrap">%s</td>',$data["item_name"]);
        if ($lqty > 0) {
            printf('<td nowrap="nowrap" align="right">%s</td>', number_format($lqty, 0));
        }else{
            print('<td>&nbsp;</td>');
        }
        if ($sqty > 0){
            printf('<td nowrap="nowrap" align="right">%s</td>',number_format($sqty,0));
        }else{
            print('<td>&nbsp;</td>');
        }
        if ($rqty > 0){
            printf('<td nowrap="nowrap" align="right">%s</td>',number_format($rqty,0));
        }else{
            print('<td>&nbsp;</td>');
        }
        if ($data["delivery_status"] == 1){
            print('<td>Terkirim</td>');
        }else{
            print('<td>Belum</td>');
        }
        if ($ivn <> $data["invoice_no"]) {
            printf('<td class="center"><input type="checkbox" class="cbIds" name="ids[]" value="%d" checked="checked"/></td>', $data["master_id"]);
        }else{
            print('<td>&nbsp;</td>');
        }
        print('</tr>');
        $csc = $data["cus_code"];
        $ivd = $data["invoice_date"];
        $ivn = $data["invoice_no"];
        $ivi = $data["master_id"];
    }
    print('</table>');
    print('</form>');
}
?>
</div>
<br>
<br>
<?php if($invoices != null){ ?>
    <?php
    print('<i>* Printed by: '.$userName.'  - Time: '.date('d-m-Y h:i:s').' *</i>');
    ?>
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
