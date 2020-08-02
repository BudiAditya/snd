<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Create Faktur PPN</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        var urc = "<?php print($helper->site_url("tax.faktur/create")); ?>";
        var urv = "<?php print($helper->site_url("tax.faktur/void")); ?>";
        $(document).ready(function() {
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
            <th class="bold" colspan="7">CREATE FAKTUR PPN KELUARAN</th>
            <th class="bold">Action</th>
        </tr>
        <tr>
            <td><label for="Tahun">Tahun Pajak :</label></td>
            <td><select name="Tahun" id="Tahun" required>
                    <?php
                    $thl = 2019;
                    $ths = date('Y');
                    for ($thn = $thl; $thn <= $ths; $thn++){
                        if ($thn == $tahun) {
                            printf('<option value="%d" selected="selected">%s</option>', $thn, $thn);
                        }else{
                            printf('<option value="%d">%s</option>',$thn,$thn);
                        }
                    }
                    ?></select>
            </td>
            <td><label for="Bulan">Masa Pajak :</label></td>
            <td><select name="Bulan" id="Bulan" required>
                    <option value="1" <?php print($bulan == 1 ? 'selected="selected"' : '');?>> 1 - Januari</option>
                    <option value="2" <?php print($bulan == 2 ? 'selected="selected"' : '');?>> 2 - Februari</option>
                    <option value="3" <?php print($bulan == 3 ? 'selected="selected"' : '');?>> 3 - Maret</option>
                    <option value="4" <?php print($bulan == 4 ? 'selected="selected"' : '');?>> 4 - April</option>
                    <option value="5" <?php print($bulan == 5 ? 'selected="selected"' : '');?>> 5 - Mei</option>
                    <option value="6" <?php print($bulan == 6 ? 'selected="selected"' : '');?>> 6 - Juni</option>
                    <option value="7" <?php print($bulan == 7 ? 'selected="selected"' : '');?>> 7 - Juli</option>
                    <option value="8" <?php print($bulan == 8 ? 'selected="selected"' : '');?>> 8 - Agustus</option>
                    <option value="9" <?php print($bulan == 9 ? 'selected="selected"' : '');?>> 9 - September</option>
                    <option value="10" <?php print($bulan == 10 ? 'selected="selected"' : '');?>>10 - Oktober</option>
                    <option value="11" <?php print($bulan == 11 ? 'selected="selected"' : '');?>>11 - Nopember</option>
                    <option value="12" <?php print($bulan == 12 ? 'selected="selected"' : '');?>>12 - Desember</option>
                </select>
            </td>
            <td><label for="Output">Output :</label></td>
            <td>
                <select id="Output" name="Output" required>
                    <option value="0" <?php print($output == 0 ? 'selected="selected"' : '');?>>0 - Web Html</option>
                    <option value="1" <?php print($output == 1 ? 'selected="selected"' : '');?>>1 - Excel</option>
                </select>
            </td>
            <td>
                <button type="submit" formaction="<?php print($helper->site_url("tax.faktur/add")); ?>"><b>Tampilkan</b></button>
                &nbsp;
                <input type="button" class="button" onclick="printDiv('printArea')" value="Print"/>
            </td>
            <td>
                <input type="button" id="btnGenerate" class="button" value="Create Faktur PPN"/>
                <input type="button" id="btnVoid" class="button" value="Batalkan Faktur PPN"/>
            </td>
        </tr>
    </table>
</form>
<!-- start web report -->
<div id="printArea">
<?php
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
/** @var $invoices Invoice[] */
if ($invoices != null) {
    ?>
    <br>
    <form id="frd" name="frmDetail" method="post">
        <input type="hidden" name="rBulan" value="<?=$bulan?>"/>
        <input type="hidden" name="rTahun" value="<?=$tahun?>"/>
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>No. Faktur</th>
            <th>Tanggal</th>
            <th>Nama PKP</th>
            <!--<th>Alamat Lengkap</th>-->
            <th>NPWP</th>
            <th>D P P</th>
            <th>P P N</th>
            <th>Jumlah</th>
            <th>Ex Invoice</th>
            <th>Tgl Invoice</th>
            <th>Outlet</th>
            <th>Pilih <input type="checkbox" id="cbAll" checked="checked"></th>
        </tr>
    <?php
    $nmr = 1;
    $dpp = 0;
    $ppn = 0;
    While ($data = $invoices->FetchAssoc()){
        print('<tr>');
        printf('<td>%d</td>',$nmr++);
        printf('<td nowrap="nowrap">%s</td>',$data["nsf_pajak"]);
        printf('<td nowrap="nowrap">%s</td>',$data["fp_date"] == null ? '&nbsp;' : date('d-m-Y', strtotime($data["fp_date"])));
        printf('<td nowrap="nowrap">%s</td>',$data["cus_name"]);
        //printf('<td>%s</td>',$row["alamat_lengkap"]);
        printf('<td nowrap="nowrap">%s</td>',$data["npwp"]);
        printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["dpp_amount"],0));
        printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["ppn_amount"],0));
        printf('<td nowrap="nowrap" align="right">%s</td>',number_format($data["dpp_amount"]+$data["ppn_amount"],0));
        printf('<td nowrap="nowrap">%s</td>',$data["invoice_no"]);
        printf('<td nowrap="nowrap">%s</td>',$data["invoice_date"] == null ? '&nbsp;' : date('d-m-Y', strtotime($data["invoice_date"])));
        printf('<td nowrap="nowrap">%s</td>',$data["cus_code"]);
        printf('<td class="center"><input type="checkbox" class="cbIds" name="ids[]" value="%d" checked="checked"/></td>',$data["id"]);
        print('</tr>');
        $dpp+= $data["dpp_amount"];
        $ppn+= $data["ppn_amount"];
    }
    print('<tr class="bold">');
    print('<td colspan="5">TOTAL</td>');
    printf('<td nowrap="nowrap" align="right">%s</td>',number_format($dpp,0));
    printf('<td nowrap="nowrap" align="right">%s</td>',number_format($ppn,0));
    printf('<td nowrap="nowrap" align="right">%s</td>',number_format($dpp+$ppn,0));
    print('<td colspan="4">&nbsp;</td>');
    print('</tr>');
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
