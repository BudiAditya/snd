<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Printing Invoice</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        var urc = "<?php print($helper->site_url("ar.invoice/printout")); ?>";
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
            <th class="bold" colspan="8">PROSES PENCETAKAN INVOICE</th>
            <th colspan="2" class="bold">Action</th>
        </tr>
        <tr>
            <td><label for="AreaId">Area :</label></td>
            <td><select id="AreaId" name="areaId">
                    <option value="0">Semua</option>
                    <?php
                    /** @var $areas SalesArea[] */
                    foreach ($areas as $area){
                        if ($areaId == $area->Id){
                            printf('<option value="%d" selected="selected">%s - %s</option>',$area->Id,$area->AreaCode,$area->AreaName);
                        }else{
                            printf('<option value="%d">%s - %s</option>',$area->Id,$area->AreaCode,$area->AreaName);
                        }
                    }
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
            <td>
                <button type="submit" formaction="<?php print($helper->site_url("ar.invoice/ivcprint")); ?>"><b>Tampilkan</b></button>
            </td>
            <td>
                <b><input type="button" id="btnGenerate" class="button" value="Print"/></b>
            </td>
        </tr>
    </table>
</form>
<?php
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
if ($invoices != null) {
    ?>
    <br>
    <form id="frd" name="frmDetail" method="post">
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Tanggal</th>
            <th>No. Invoice</th>
            <th>Nama Customer</th>
            <th>Area</th>
            <th>Sub Total</th>
            <th>Diskon</th>
            <th>DPP</th>
            <th>PPN</th>
            <th>Total</th>
            <th>Items</th>
            <th>Kertas</th>
            <th>Pilih <input type="checkbox" id="cbAll" checked="checked"></th>
        </tr>
    <?php
    $nmr = 1;
    $url = null;
    while ($data = $invoices->FetchAssoc()) {
        $url = $helper->site_url("ar.invoice/view/" . $data["id"]);
        print('<tr>');
        printf('<td>%d</td>', $nmr++);
        printf('<td nowrap="nowrap">%s</td>', $data["invoice_date"]);
        printf("<td nowrap='nowrap'><a href= '%s' target='_blank'>%s</a></td>", $url, $data["invoice_no"]);
        printf('<td nowrap="nowrap">%s</td>', $data["cus_code"] . ' - ' . $data["cus_name"]);
        printf('<td nowrap="nowrap">%s</td>', $data["area_code"]);
        printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["sub_total"],0));
        printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["discount"],0));
        printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["sub_total"]-$data["discount"],0));
        printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["ppn"],0));
        printf('<td nowrap="nowrap" align="right">%s</td>',number_format(($data["sub_total"]-$data["discount"])-$data["ppn"],0));
        printf('<td nowrap="nowrap" align="center">%s</td>', $data["baris"]);
        if ($data["baris"] > 40) {
            print('<td>Panjang</td>');
        }else{
            print('<td>Pendek</td>');
        }
        printf('<td class="center"><input type="checkbox" class="cbIds" name="ids[]" value="%d" checked="checked"/></td>', $data["id"]);
        print('</tr>');
    }
    print('</table>');
    print('</form>');
}
?>
<!-- </body> -->
</html>
