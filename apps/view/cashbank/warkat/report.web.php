<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Laporan Warkat</title>
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
            <th colspan="9"><b>Laporan Warkat</b></th>
        </tr>
        <tr class="center">
            <th>Cabang</th>
            <th>Mode</th>
            <th>Warkat Bank</th>
            <th>Akun Pencairan</th>
            <th>Status</th>
            <th>Dari Tgl</th>
            <th>Sampai Tgl</th>
            <th>Output</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select name="CabangId" class="text2" id="CabangId" style="width: 150px" required>
                    <option value="0">- Semua Cabang -</option>
                    <?php
                    foreach ($Cabangs as $cab) {
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
                <select id="TrxMode" name="TrxMode" required>
                    <option value="1" <?php print($TrxMode == 1 ? 'selected="selected"' : '');?>>1 - Masuk</option>
                    <option value="2" <?php print($TrxMode == 2 ? 'selected="selected"' : '');?>>2 - Keluar</option>
                </select>
            </td>
            <td>
                <select id="BankId" name="BankId" style="width: 150px" required>
                    <option value="0">- Semua Bank -</option>
                    <?php
                    /** @var $Banks Bank[]*/
                    foreach ($Banks as $bank) {
                        if ($bank->Id == $BankId) {
                            printf('<option value="%d" selected="selected">%s</option>', $bank->Id, $bank->BankName);
                        } else {
                            printf('<option value="%d">%s</option>', $bank->Id, $bank->BankName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="KasBankId" name="KasBankId" style="width: 150px" required>
                    <option value="0">- Semua Akun -</option>
                    <?php
                    /** @var $KasBanks KasBank[]*/
                    foreach ($KasBanks as $bank) {
                        if ($bank->Id == $KasBankId) {
                            printf('<option value="%d" selected="selected">%s</option>', $bank->Id, $bank->BankName);
                        } else {
                            printf('<option value="%d">%s</option>', $bank->Id, $bank->BankName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="WarkatStatus" name="WarkatStatus" required>
                    <option value="-1" <?php print($WarkatStatus == -1 ? 'selected="selected"' : '');?>> - Semua Status -</option>
                    <option value="0" <?php print($WarkatStatus == 0 ? 'selected="selected"' : '');?>>0 - Baru</option>
                    <option value="1" <?php print($WarkatStatus == 1 ? 'selected="selected"' : '');?>>1 - Cair</option>
                    <option value="2" <?php print($WarkatStatus == 2 ? 'selected="selected"' : '');?>>2 - Batal</option>
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
                <button type="submit" formaction="<?php print($helper->site_url("cashbank.warkat/report")); ?>"><b>Proses</b></button>
                <input type="button" class="button" onclick="printDiv('printArea')" value="Print"/>
            </td>
        </tr>
    </table>
</form>
<!-- start web report -->
<div id="printArea">
<?php  if ($Reports != null){
    $userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
    ?>
    <h3>Laporan Transaksi Warkat</h3>
    <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Cabang</th>
            <th>Tanggal</th>
            <th nowrap>No. Warkat</th>
            <th>Mode</th>
            <th>Bank</th>
            <th>Relasi</th>
            <th>Pencairan</th>
            <th>Refferensi</th>
            <?php
            if($TrxMode == 0){
               print('<th>Debet</th>');
               print('<th>Kredit</th>');
               print('<th>Saldo</th>');
            }else{
                print('<th>Jumlah</th>');
            }
            ?>
            <th nowrap>Tgl. Proses</th>
            <th>Status</th>
        </tr>
        <?php
            $nmr = 1;
            $tdebet = 0;
            $tkredit = 0;
            $saldo = 0;
            $url = null;
            $xmode = null;
            while ($row = $Reports->FetchAssoc()) {
                $url = $helper->site_url("cashbank.warkat/view/".$row["id"]);
                $debet = 0;
                $kredit = 0;
                if ($TrxMode == 0){
                    if ($row["warkat_mode"] == 1){
                        $debet = $row["warkat_amount"];
                        $tdebet+= $debet;
                    }else{
                        $kredit = $row["warkat_amount"];
                        $tkredit+= $kredit;
                    }
                    $saldo = $saldo + $debet - $kredit;
                }else{
                    $saldo+= $row["warkat_amount"];
                }
                if ($row["warkat_mode"] == 1){
                    $xmode = "Masuk";
                }else{
                    $xmode = "Masuk";
                }
                print("<tr valign='Top'>");
                printf("<td>%s</td>",$nmr);
                printf("<td nowrap>%s</td>",$row["kd_cabang"]);
                printf("<td nowrap>%s</td>",date('d-m-Y',strtotime($row["warkat_date"])));
                printf("<td nowrap><a href= '%s' target='_blank'>%s</a></td>",$url ,$row["warkat_no"]);
                printf("<td nowrap>%s</td>",$xmode);
                printf("<td nowrap>%s</td>",$row["bank_name"]);
                printf("<td nowrap>%s</td>",$row["cust_name"]);
                printf("<td nowrap>%s</td>",$row["kontra_perkiraan"]);
                printf("<td nowrap>%s</td>",$row["reff_no"]);
                if ($TrxMode == 0){
                    printf("<td align='right'>%s</td>",number_format($debet,0));
                    printf("<td align='right'>%s</td>",number_format($kredit,0));
                    printf("<td align='right'>%s</td>",number_format($saldo,0));
                }else{
                    printf("<td align='right'>%s</td>",number_format($row["warkat_amount"],0));
                }
                printf("<td nowrap>%s</td>",date('d-m-Y',strtotime($row["process_date"])));
                printf("<td nowrap>%s</td>",$row["warkat_reason"]);
                print("</tr>");
                $nmr++;
            }
        print("<tr>");
        print("<td colspan='9' align='right'>Total Transaksi</td>");
        if ($TrxMode == 0){
            printf("<td align='right'>%s</td>",number_format($tdebet,0));
            printf("<td align='right'>%s</td>",number_format($tkredit,0));
            printf("<td align='right'>%s</td>",number_format($tdebet-$tkredit,0));
        }else{
            printf("<td align='right'>%s</td>",number_format($saldo,0));
        }
        printf("<td colspan='2'>&nbsp</td>");
        print("</tr>");
        ?>
    </table>
    <?='<br><i>* Printed by: ' . $userName . '  - Time: ' . date('d-m-Y h:i:s') . ' *</i>';?>
<!-- end web report -->
<?php } ?>
</div>
<script type="text/javascript">
    function printDiv(divName) {
        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>
<!-- </body> -->
</html>
