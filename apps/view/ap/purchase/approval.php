<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Approval Purchase</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        var ura = "<?php print($helper->site_url("ap.purchase/approve/1")); ?>";
        var urb = "<?php print($helper->site_url("ap.purchase/approve/0")); ?>";
        $(document).ready(function() {
            $("#StartDate").customDatePicker({ showOn: "focus" });
            $("#EndDate").customDatePicker({ showOn: "focus" });
            $("#cbAll").change(function(e) { cbAll_Change(this, e);	});

            $("#btnApprove").click(function() {
                var test = $(".cbIds:checked");
                if (test.length == 0) {
                    alert("Belum ada data yang dipilih!");
                }else {
                    if (confirm("Proses Approve data yang dipilih?")) {
                        $("#frd").attr('action', ura).submit();
                    }
                }
            });

            $("#btnUnapprove").click(function() {
                var test = $(".cbIds:checked");
                if (test.length == 0) {
                    alert("Belum ada data yang dipilih!");
                }else {
                    if (confirm("Batalkan Approve data yang dipilih?")) {
                        $("#frd").attr('action', urb).submit();
                    }
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
            <th class="bold" colspan="6">PROSES APPROVAL PURCHASE (FAKTUR PEMBELIAN)</th>
            <th colspan="2" class="bold">ACTION</th>
        </tr>
        <tr>
            <td><label for="StartDate">Tanggal :</label></td>
            <td><input type="text" class="text2" maxlength="10" size="8" id="StartDate" name="stDate" value="<?php printf(date('d-m-Y',$stDate));?>"/></td>
            <td><label for="EndDate">S/D Tgl :</label></td>
            <td><input type="text" class="text2" maxlength="10" size="8" id="EndDate" name="enDate" value="<?php printf(date('d-m-Y',$enDate));?>"/></td>
            <td><label for="gnStatus">Status :</label></td>
            <td><select id="gnStatus" name="gnStatus">
                    <option value="0" <?php print($gnStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                    <option value="1" <?php print($gnStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                    <option value="2" <?php print($gnStatus == 2 ? 'selected="selected"' : '');?>>2 - Approved</option>
                    <option value="3" <?php print($gnStatus == 3 ? 'selected="selected"' : '');?>>3 - Void</option>
                </select>
            </td>
            <td>
                <button type="submit" formaction="<?php print($helper->site_url("ap.purchase/approval")); ?>"><b>TAMPILKAN</b></button>
            </td>
            <td class="bold">
                <b><input type="button" id="btnApprove" class="button" value="APPROVE"/></b>
                &nbsp;&nbsp;
                <b><input type="button" id="btnUnapprove" class="button" value="BATAL APPROVE"/></b>
            </td>
        </tr>
    </table>
</form>
<?php
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
if ($grns != null) {
?>
<br>
<form id="frd" name="frmDetail" method="get">
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Tanggal</th>
            <th>No. Purchase</th>
            <th>Nama Supplier</th>
            <th>No. Invoice</th>
            <th>Tgl Invoice</th>
            <th>Sub Total</th>
            <th>Diskon</th>
            <th>DPP</th>
            <th>PPN</th>
            <th>PPh</th>
            <th>Total</th>
            <th>Status</th>
            <th>Pilih <input type="checkbox" id="cbAll" checked="checked"></th>
        </tr>
        <?php
        $nmr = 1;
        $url = null;
        $tbam = 0;
        $tdam = 0;
        $tdpp = 0;
        $tppn = 0;
        $tpph = 0;
        $ttam = 0;
        while ($data = $grns->FetchAssoc()) {
            $url = $helper->site_url("ap.purchase/view/" . $data["id"]);
            print('<tr>');
            printf('<td>%d</td>', $nmr++);
            printf('<td nowrap="nowrap">%s</td>', $data["grn_date"]);
            printf("<td nowrap='nowrap'><a href= '%s' target='_blank'>%s</a></td>", $url, $data["grn_no"]);
            printf('<td nowrap="nowrap">%s</td>', $data["supplier_code"] . ' - ' . $data["supplier_name"]);
            printf('<td nowrap="nowrap">%s</td>', $data["sup_inv_no"]);
            printf('<td nowrap="nowrap">%s</td>', $data["sup_inv_date"]);
            printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["base_amount"],0));
            printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["disc_amount"],0));
            printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["base_amount"]-$data["disc_amount"],0));
            printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["ppn_amount"],0));
            printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["pph_amount"],0));
            printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["total_amount"],0));
            if ($data["grn_status"] == 0) {
                print('<td>DRAFT</td>');
            }elseif ($data["grn_status"] == 1){
                print('<td>POSTED</td>');
            }elseif ($data["grn_status"] == 2){
                print('<td>APPROVED</td>');
            }elseif ($data["grn_status"] == 3) {
                print('<td>VOID</td>');
            }else{
                print('<td>N/A</td>');
            }
            printf('<td class="center"><input type="checkbox" class="cbIds" name="id[]" value="%d" checked="checked"/></td>', $data["id"]);
            print('</tr>');
            $tbam += $data["base_amount"];
            $tdam += $data["disc_amount"];
            $tdpp += $data["base_amount"]-$data["disc_amount"];
            $tppn += $data["ppn_amount"];
            $tpph += $data["pph_amount"];
            $ttam += $data["total_amount"];
        }
        printf('
        <tr class="bold">
            <td colspan="6" align="right">Total..</td>
            <td align="right">%s</td>
            <td align="right">%s</td>
            <td align="right">%s</td>
            <td align="right">%s</td>
            <td align="right">%s</td>
            <td align="right">%s</td>
            <td colspan="2">&nbsp;</td>
        </tr>
        ',number_format($tbam,0),number_format($tdam,0),number_format($tdpp,0),number_format($tppn,0),number_format($tpph,0),number_format($ttam,0));
        ?>
    </table>
</form>
<?php } ?>
<!-- </body> -->
</html>
