<!DOCTYPE HTML>
<html>
<?php /** @var $suppliers Supplier[] */ /** @var $banks KasBank[] */
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
?>
<head>
	<title>SND System - Rekapitulasi Pembayaran Hutang</title>
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
            <th colspan="8"><b>Rekapitulasi Pembayaran Hutang</b></th>
            <th>
                Jenis :
                &nbsp;
                <select class="text2" id="RptType" name="RptType">
                    <option value="1" <?php print($RptType == 1 ? 'selected="selected"' : '');?>>1 - Rekapitulasi</option>
                    <option value="2" <?php print($RptType == 2 ? 'selected="selected"' : '');?>>2 - Detail</option>
                </select>
            </th>
        </tr>
        <tr class="center">
            <th>Cabang</th>
            <th>Supplier</th>
            <th>Cara Bayar</th>
            <th>Kas/Bank</th>
            <th>Status</th>
            <th>Dari Tanggal</th>
            <th>Sampai Tanggal</th>
            <th>Output</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select name="CabangId" class="text2" id="CabangId" style="width: 100px" required>
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
                <select id="SuppliersId" name="SuppliersId" style="width: 100px" required>
                    <option value="0">- Semua Supplier -</option>
                    <?php
                    foreach ($suppliers as $supplier) {
                        if ($SuppliersId == $supplier->Id){
                            printf('<option value="%d" selected="selected">%s (%s)</option>',$supplier->Id,$supplier->SupName,$supplier->SupCode);
                        }else{
                            printf('<option value="%d">%s (%s)</option>',$supplier->Id,$supplier->SupName,$supplier->SupCode);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="PaymentMode" name="PaymentMode" style="width: 100px" required>
                    <option value="-1">- Semua Cara Bayar -</option>
                    <?php
                    /** @var $paytypes PaymentType[] */
                    foreach ($paytypes as $type) {
                        if ($PaymentMode == $type->Id){
                            printf('<option value="%d" selected="selected">%s - %s</option>',$type->Id,$type->Id,$type->Type);
                        }else{
                            printf('<option value="%d">%s - %s</option>',$type->Id,$type->Id,$type->Type);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="KasbankId" name="KasbankId" style="width: 100px" required>
                    <option value="0">- Semua Kas/Bank -</option>
                    <?php
                    foreach ($banks as $bank) {
                        if ($KasbankId == $bank->Id){
                            printf('<option value="%d" selected="selected">%s - %s</option>',$bank->Id,$bank->Id,$bank->BankName);
                        }else{
                            printf('<option value="%d">%s - %s</option>',$bank->Id,$bank->Id,$bank->BankName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="PaymentStatus" name="PaymentStatus" style="100px" required>
                    <option value="-1" <?php print($PaymentStatus == -1 ? 'selected="selected"' : '');?>> - Semua Status -</option>
                    <option value="0" <?php print($PaymentStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                    <option value="1" <?php print($PaymentStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                    <option value="2" <?php print($PaymentStatus == 2 ? 'selected="selected"' : '');?>>2 - Batal</option>
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
                <button type="submit" formaction="<?php print($helper->site_url("ap.payment/report")); ?>"><b>TAMPILKAN</b></button>
                <input type="button" class="button" onclick="printDiv('printArea')" value="Print"/>
            </td>
        </tr>
    </table>
</form>
<!-- start web report -->
<div id="printArea">
<?php  if ($Reports != null){ ?>
    <h3>Rekapitulasi Pembayaran Hutang</h3>
    <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th>No.</th>
            <th>Cabang</th>
            <th>Tanggal</th>
            <th>No. Payment</th>
            <th>Nama Supplier</th>
            <th>Cara Bayar</th>
            <th>Kas / Bank</th>
            <th>Jumlah</th>
            <?php if ($RptType == 1){?>
            <th>Status</th>
            <?php }else{ ?>
                <th>No. GRN</th>
                <th>Tgl GRN</th>
                <th>Alokasi</th>
            <?php } ?>
        </tr>
        <?php
            $nmr = 1;
            $total = 0;
            $tgrn = 0;
            $pvn = null;
            $url = null;
            while ($row = $Reports->FetchAssoc()) {
                $url = $helper->site_url("ap.payment/view/".$row["id"]);
                if ($pvn != $row["payment_no"]) {
                    print("<tr valign='Top'>");
                    printf("<td>%s</td>", $nmr);
                    printf("<td>%s</td>", $row["cabang_code"]);
                    printf("<td nowrap='nowrap'>%s</td>", date('d-m-Y', strtotime($row["payment_date"])));
                    printf("<td nowrap='nowrap'><a href= '%s' target='_blank'>%s</a></td>", $url, $row["payment_no"]);
                    printf("<td nowrap='nowrap'>%s</td>", $row["supplier_name"] . ' (' . $row["supplier_code"] . ')');
                    printf("<td nowrap='nowrap'>%s</td>", $row["cara_bayar"]);
                    printf("<td nowrap='nowrap'>%s</td>", $row["bank_name"]);
                    printf("<td align='right'>%s</td>", number_format($row["payment_amount"], 0));
                    if ($RptType == 1) {
                        printf("<td>%s</td>", $row["status_desc"]);
                    } else {
                        printf("<td nowrap='nowrap'>%s</td>", $row["grn_no"]);
                        printf("<td nowrap='nowrap'>%s</td>", date('d-m-Y', strtotime($row["grn_date"])));
                        printf("<td align='right'>%s</td>", number_format($row["allocate_amount"], 0));
                    }
                    print("</tr>");
                    $nmr++;
                }else{
                    print("<tr valign='Top'>");
                    print("<td colspan='8'>&nbsp;</td>");
                    printf("<td nowrap='nowrap'>%s</td>", $row["grn_no"]);
                    printf("<td nowrap='nowrap'>%s</td>", date('d-m-Y', strtotime($row["grn_date"])));
                    printf("<td align='right'>%s</td>", number_format($row["allocate_amount"], 0));
                    print("</tr>");
                }
                $pvn = $row["payment_no"];
                $total+= $row["payment_amount"];
                $tgrn+= $row["allocate_amount"];
            }
        print("<tr>");
        print("<td colspan='7' align='right'>Total Pembayaran</td>");
        printf("<td align='right'>%s</td>",number_format($total,0));
        if ($RptType == 1) {
            print("<td>&nbsp;</td>");
        }else{
            printf("<td colspan='2' align='right'>Total Alokasi</td>");
            printf("<td align='right'>%s</td>", number_format($tgrn, 0));
        }
        print("</tr>");
        ?>
    </table>
    <br>
    <?php
    print('<i>* Printed by: ' . $userName . '  - Time: ' . date('d-m-Y h:i:s') . ' *</i>');
    ?>
</div>
<!-- end web report -->
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
