<!DOCTYPE HTML>
<html>
<?php /** @var $customers Customer[] */ /** @var $sales Salesman[] */
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
?>
<head>
	<title>SND System - Daftar Piutang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.idletimer.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>
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
<h2>REKAPITULASI PIUTANG</h2>
<form id="frm" name="frmReport" method="post">
    <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
        <tr class="center">
            <th>Cabang</th>
            <th>Customer</th>
            <th>Salesman</th>
            <th>Status</th>
            <th>Dari Tanggal</th>
            <th>Sampai Tanggal</th>
            <th>Output</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select name="CabangId" class="text2" id="CabangId" style="width: 100px" required>
                <option value="0">- Semua Cabang -</option>
                <?php
                /** @var $cabangs Cabang[] */
                foreach ($cabangs as $cab) {
                    if ($cab->Id == $CabangId) {
                        printf('<option value="%d" selected="selected">%s</option>', $cab->Id, $cab->Cabang);
                    } else {
                        printf('<option value="%d">%s</option>', $cab->Id, $cab->Cabang);
                    }
                }
                ?>
                </select>
            </td>
            <td>
                <select id="ContactsId" name="ContactsId" style="width: 150px" required>
                    <option value="0">- Semua Customer -</option>
                    <?php
                    foreach ($customers as $customer) {
                        if ($ContactsId == $customer->Id){
                            printf('<option value="%d" selected="selected"> %s - %s </option>',$customer->Id,$customer->CusCode,$customer->CusName);
                        }else{
                            printf('<option value="%d"> %s - %s </option>',$customer->Id,$customer->CusCode,$customer->CusName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="SalesId" name="SalesId" style="width: 150px" required>
                    <option value="0">- Semua Salesman -</option>
                    <?php
                    foreach ($sales as $salesman) {
                        if ($salesman->Id == $SalesId) {
                            printf('<option value="%d" selected="selected">%s</option>', $salesman->Id, $salesman->SalesName);
                        } else {
                            printf('<option value="%d">%s</option>', $salesman->Id, $salesman->SalesName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="PaymentStatus" name="PaymentStatus" required>
                    <option value="-1" <?php print($PaymentStatus == -1 ? 'selected="selected"' : '');?>> - Semua Status -</option>
                    <option value="0" <?php print($PaymentStatus == 0 ? 'selected="selected"' : '');?>>0 - Belum Lunas</option>
                    <option value="1" <?php print($PaymentStatus == 1 ? 'selected="selected"' : '');?>>1 - Sudah Lunas</option>
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
                <button type="submit" formaction="<?php print($helper->site_url("ar.report/list_piutang")); ?>"><b>TAMPILKAN</b></button>
                <input type="button" class="button" onclick="printDiv('printArea')" value="Print"/>
            </td>
        </tr>
    </table>
</form>
<!-- start web report -->
<div id="printArea">
<?php
if ($Reports != null){
    $bsearch = base_url('public/images/button/').'search.png';
?>
<h3>DAFTAR PIUTANG</h3>
<?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
<table cellpadding="1" cellspacing="1" class="tablePadding tableBorder" style="font-size:small">
    <tr>
        <th>No.</th>
        <th>Cab</th>
        <th>Tanggal</th>
        <th>No. Invoice</th>
        <th>Customer</th>
        <th>Area</th>
        <th>Salesman</th>
        <th>JTP</th>
        <th>Jumlah</th>
        <th>Retur</th>
        <th>Terbayar</th>
        <th>Outstanding</th>
        <th>Action</th>
    </tr>
    <?php
        $nmr = 0;
        $tOtal = 0;
        $subTotal = 0;
        $tTerbayar = 0;
        $tReturn = 0;
        $tSisa = 0;
        $url = null;
        $ivn = null;
        $sma = false;
        while ($row = $Reports->FetchAssoc()) {
            $nmr++;
            $url = $helper->site_url("ar.invoice/view/" . $row["id"]);
            print("<tr valign='Top'>");
            printf("<td>%s</td>", $nmr);
            printf("<td nowrap='nowrap'>%s</td>", $row["cabang_code"]);
            printf("<td nowrap='nowrap'>%s</td>", date('d-m-Y', strtotime($row["invoice_date"])));
            printf("<td nowrap='nowrap'><a href= '%s' target='_blank'>%s</a></td>", $url, $row["invoice_no"]);
            printf("<td nowrap='nowrap'>%s</td>", $row["customer_code"].' - '.$row["customer_name"]);
            printf("<td nowrap='nowrap'>%s</td>", $row["area_code"]);
            printf("<td nowrap='nowrap'>%s</td>", $row["sales_name"]);
            printf("<td nowrap='nowrap'>%s</td>", date('d-m-Y', strtotime($row["due_date"])));
            printf("<td align='right'>%s</td>", number_format($row["total_amount"], 0));
            printf("<td align='right'>%s</td>", number_format($row["return_amount"], 0));
            printf("<td align='right'>%s</td>", number_format($row["paid_amount"], 0));
            printf("<td align='right'>%s</td>", number_format($row["balance_amount"], 0));
            printf('<td align="center"><img src="%s" alt="List" title="Perincian" id="bList" style="cursor: pointer" onclick="return fViewList(%d,%s);"/></td>', $bsearch,$row["id"],"'".$row["invoice_no"]."'");
            print("</tr>");
           $tOtal+= $row["total_amount"];
            $tReturn+= $row["return_amount"];
            $tTerbayar+= $row["paid_amount"];
            $tSisa+= $row["balance_amount"];
        }
    print("<tr class='bold'>");
    print("<td colspan='8' align='right'>Total..</td>");
    printf("<td align='right'>%s</td>", number_format($tOtal, 0));
    printf("<td align='right'>%s</td>", number_format($tReturn, 0));
    printf("<td align='right'>%s</td>", number_format($tTerbayar, 0));
    printf("<td align='right'>%s</td>", number_format($tSisa, 0));
    print('<td>&nbsp;</td>');
    print("</tr>");
    ?>
</table>
<?php } ?>
</div>
<!-- modal list -->
<div id="dList" class="easyui-dialog" style="width:400px;height:300px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <div id="htmList"></div>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dList').dialog('close')" style="width:90px">Tutup</a>
</div>
<br>
<?php if($Reports != null){ ?>
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
    function fViewList(pId,pBukti) {
        var pTitle = 'Perincian Pembayaran Invoice: '+pBukti;
        var url = "<?=base_url('ar/report/getOrList/');?>"+pId;
        $.get(url,function (data) {
            $("#htmList").html(data);
            $('#dList').dialog('open').dialog('setTitle',pTitle);
        });
    }
</script>
<!-- </body> -->
</html>
