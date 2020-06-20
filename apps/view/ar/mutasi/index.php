<!DOCTYPE HTML>
<html>
<?php /** @var $customers Customer[] */
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
?>
<head>
	<title>SND System - Rekapitulasi Piutang</title>
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
            <th colspan="6"><b>REKAPITULASI PIUTANG</b></th>
        </tr>
        <tr class="center">
            <th>Cabang</th>
            <th>Customer/Outlet</th>
            <th>Dari Tanggal</th>
            <th>Sampai Tanggal</th>
            <th>Output</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select name="CabangId" class="text2" id="CabangId" required>
                    <option value="0">- Semua Cabang -</option>
                    <?php
                    /** @var $cabangs Cabang[] */
                    foreach ($cabangs as $cab) {
                        if ($cab->Id == $CabangId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
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
                            printf('<option value="%d" selected="selected"> %s - %s </option>',$customer->Id,$customer->CusCode,$customer->CusName);
                        }else{
                            printf('<option value="%d"> %s - %s </option>',$customer->Id,$customer->CusCode,$customer->CusName);
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
                    <option value="1" <?php print($Output == 1 ? 'selected="selected"' : '');?>>1 - Excel</option>
                </select>
            </td>
            <td>
                <button type="submit" formaction="<?php print($helper->site_url("ar.mutasi")); ?>"><b>Proses</b></button>
                <input type="button" class="button" onclick="printDiv('printArea')" value="Print"/>
            </td>
        </tr>
    </table>
</form>
<!-- start web report -->
<div id="printArea">
<?php  if ($Reports != null){ ?>
        <h3>REKAPITULASI PIUTANG</h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>No. Bukti</th>
                <th>Customer</th>
                <th>Penjualan</th>
                <th>Retur</th>
                <th>Pembayaran</th>
                <th>Saldo</th>
            </tr>
            <?php
                $nmr = 1;
                $saldo = 0;
                $invoice = 0;
                $retur = 0;
                $receipt = 0;
                while ($row = $Reports->FetchAssoc()) {
                    if ($nmr == 1){
                        $saldo = $row["saldo"];
                    }else{
                        $saldo = $saldo + ($row["invoice"] - ($row["retur"] + $row["receipt"]));
                    }
                    print("<tr valign='Midle'>");
                    printf("<td>%s</td>", $nmr++);
                    printf("<td nowrap='nowrap'>%s</td>", date('d-m-Y', strtotime($row["trx_date"])));
                    printf("<td nowrap='nowrap'>%s</td>", $row["no_bukti"]);
                    printf("<td nowrap='nowrap'>%s</td>", $row["customer"]);
                    printf("<td align='right'>%s</td>", number_format($row["invoice"], 0));
                    printf("<td align='right'>%s</td>", number_format($row["retur"], 0));
                    printf("<td align='right'>%s</td>", number_format($row["receipt"], 0));
                    printf("<td align='right'>%s</td>", number_format($saldo, 0));
                    print("</tr>");
                    $invoice+= $row["invoice"];
                    $receipt+= $row["receipt"];
                    $retur+= $row["retur"];
                }
                print("<tr class='bold'>");
                print("<td colspan='4'>T o t a l </td>");
                printf("<td align='right'>%s</td>", number_format($invoice, 0));
                printf("<td align='right'>%s</td>", number_format($retur, 0));
                printf("<td align='right'>%s</td>", number_format($receipt, 0));
                print("<td>&nbsp;</td>");
                print("</tr>");
            ?>
        </table>
<?php } ?>
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
