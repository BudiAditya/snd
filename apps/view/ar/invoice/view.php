<!DOCTYPE HTML>
<html>
<?php
/** @var $invoice Invoice */
?>
<head>
    <title>SND System - View Invoice Penjualan</title>
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
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

    <style scoped>
        .f1{
            width:200px;
        }
    </style>

    <style type="text/css">
        #fd{
            margin:0;
            padding:5px 10px;
        }
        .ftitle{
            font-size:14px;
            font-weight:bold;
            padding:5px 0;
            margin-bottom:10px;
            binvoice-bottom:1px solid #ccc;
        }
        .fitem{
            margin-bottom:5px;
        }
        .fitem label{
            display:inline-block;
            width:100px;
        }
        .numberbox .textbox-text{
            text-align: right;
            color: blue;
        }
    </style>
    <script type="text/javascript">
        $( function() {
            var custId,gudangId;
            custId = "<?php print($invoice->CustomerId) ?>";
            gudangId = "<?php print($invoice->GudangId) ?>";

            $("#InvoiceDate").customDatePicker({ showOn: "focus" });
            $("#FpDate").customDatePicker({ showOn: "focus" });

            $("#bTambah").click(function(){
                if (confirm('Buat Invoice baru?')){
                    location.href="<?php print($helper->site_url("ar.invoice/add")); ?>";
                }
            });

            $("#bHapus").click(function(){
                if (confirm('Anda yakin akam membatalkan Invoice ini?')){
                    location.href="<?php print($helper->site_url("ar.invoice/void/").$invoice->Id); ?>";
                }
            });

            $("#bCetakInvoice").click(function(){
                if (confirm('Cetak Invoice ini?')){
                    window.open("<?php print($helper->site_url("ar.invoice/ivcprint")); ?>");
                }
            });

            $("#bKembali").click(function(){
                location.href="<?php print($helper->site_url("ar.invoice")); ?>";
            });
        });

        //date formating
        function myformatter(date){
            var y = date.getFullYear();
            var m = date.getMonth()+1;
            var d = date.getDate();
            return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
        }
        function myparser(s){
            if (!s) return new Date();
            var ss = (s.split('-'));
            var y = parseInt(ss[0],10);
            var m = parseInt(ss[1],10);
            var d = parseInt(ss[2],10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
                return new Date(y,m-1,d);
            } else {
                return new Date();
            }
        }
    </script>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php }
$badd = base_url('public/images/button/').'add.png';
$bsave = base_url('public/images/button/').'accept.png';
$bcancel = base_url('public/images/button/').'cancel.png';
$bview = base_url('public/images/button/').'view.png';
$bedit = base_url('public/images/button/').'edit.png';
$bdelete = base_url('public/images/button/').'delete.png';
$bclose = base_url('public/images/button/').'close.png';
$bsearch = base_url('public/images/button/').'search.png';
$bkembali = base_url('public/images/button/').'back.png';
$bcetak = base_url('public/images/button/').'printer.png';
$bsubmit = base_url('public/images/button/').'ok.png';
$baddnew = base_url('public/images/button/').'create_new.png';
$bpdf = base_url('public/images/button/').'pdf.png';
?>
<br />
<div id="p" class="easyui-panel" title="View Invoice Penjualan" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td>Cabang</td>
            <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($invoice->CabangCode != null ? $invoice->CabangCode : $userCabCode); ?>" disabled/>
                <input type="hidden" id="CabangId" name="CabangId" value="<?php print($invoice->CabangId == null ? $userCabId : $invoice->CabangId);?>"/>
            </td>
            <td>Tanggal</td>
            <td><input type="text" class="f1 easyui-datebox" id="InvoiceDate" name="InvoiceDate" style="width: 150px" value="<?php print($invoice->FormatInvoiceDate(SQL_DATEONLY));?>" disabled data-options="formatter:myformatter,parser:myparser"/></td>
            <td>No. Invoice</td>
            <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="InvoiceNo" name="InvoiceNo" value="<?php print($invoice->InvoiceNo != null ? $invoice->InvoiceNo : '-'); ?>" disabled/></td>
        </tr>
        <tr>
            <td>Customer</td>
            <td><input class="easyui-textbox" id="CustomerId" name="CustomerId" style="width: 250px" value="<?php print($custdata->CusCode .' - '.$custdata->CusName);?>" disabled/></td>
            <td>Salesman</td>
            <td><select class="easyui-combobox" id="SalesId" name="SalesId" style="width: 150px" disabled>
                    <option value=""></option>
                    <?php
                    /** @var $sales Salesman[]*/
                    foreach ($sales as $staf) {
                        if ($staf->Id == $invoice->SalesId) {
                            printf('<option value="%d" selected="selected">%s</option>', $staf->Id, $staf->SalesName);
                        }else{
                            printf('<option value="%d">%s</option>', $staf->Id, $staf->SalesName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>Status</td>
            <td><select class="easyui-combobox" id="InvoiceStatus1" name="InvoiceStatus1" style="width: 150px" disabled>
                    <option value="0" <?php print($invoice->InvoiceStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                    <option value="1" <?php print($invoice->InvoiceStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                    <option value="2" <?php print($invoice->InvoiceStatus == 2 ? 'selected="selected"' : '');?>>2 - Approved</option>
                    <option value="3" <?php print($invoice->InvoiceStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                </select>
                <input type="hidden" id="InvoiceStatus" name="InvoiceStatus" value="<?php print($invoice->InvoiceStatus);?>"/>
                <input type="hidden" id="DbAccId" name="DbAccId" value="<?=$invoice->DbAccId;?>"/>
            </td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td><b><input type="text" class="f1 easyui-textbox" id="InvoiceDescs" name="InvoiceDescs" style="width: 250px" value="<?php print($invoice->InvoiceDescs != null ? $invoice->InvoiceDescs : '-'); ?>" disabled/></b></td>
            <td>Gudang</td>
            <td>
                <select class="easyui-combobox" id="GudangId" name="GudangId" style="width: 150px" disabled>
                    <option value="">- Pilih Gudang -</option>
                    <?php
                    /** @var $gudang Warehouse[]*/
                    foreach ($gudangs as $gudang) {
                        if ($gudang->Id == $invoice->GudangId) {
                            printf('<option value="%d" selected="selected">%s</option>', $gudang->Id, $gudang->WhCode);
                        }else{
                            printf('<option value="%d">%s</option>', $gudang->Id, $gudang->WhCode);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>Cara Bayar</td>
            <td><select class="easyui-combobox" id="PaymentType" name="PaymentType" style="width: 70px" disabled>
                    <option value="1" <?php print($invoice->PaymentType == 1 ? 'selected="selected"' : '');?>>Kredit</option>
                    <option value="0" <?php print($invoice->PaymentType == 0 ? 'selected="selected"' : '');?>>Tunai</option>
                </select>
                &nbsp
                <input type="text" class="easyui-textbox" id="CreditTerms" name="CreditTerms" style="width: 40px" value="<?php print($invoice->CreditTerms != null ? $invoice->CreditTerms : 0); ?>" style="text-align: right" disabled/>
                hari
            </td>
        </tr>
        <tr>
            <td>Expedisi</td>
            <td><select class="easyui-combobox" id="ExpeditionId" name="ExpeditionId" style="width: 250px">
                    <option value="0"></option>
                    <?php
                    /** @var $expedition Expedition[]*/
                    foreach ($expedition as $expedisi) {
                        if ($expedisi->Id == $invoice->ExpeditionId) {
                            printf('<option value="%d" selected="selected">%s</option>', $expedisi->Id, $expedisi->ExpName);
                        }else{
                            printf('<option value="%d">%s</option>', $expedisi->Id, $expedisi->ExpName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>Tgl Faktur</td>
            <td><input type="text" class="f1 easyui-datebox" style="width: 150px" id="FpDate" name="FpDate" value="<?php print($invoice->FormatFpDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser" readonly/></td>
            <td>NSF Pajak</td>
            <td><input type="text" class="f1 easyui-textbox" id="NsfPajak" name="NsfPajak" style="width: 150px" maxlength="50" value="<?php print($invoice->NsfPajak != null ? $invoice->NsfPajak : '-'); ?>" readonly/></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="10">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma; widows: 100%;">
                    <tr>
                        <th colspan="13">DETAIL BARANG YANG DIJUAL</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>Brand</th>
                        <th>Kode</th>
                        <th nowrap="nowrap">Nama Barang</th>
                        <th>L</th>
                        <th>S</th>
                        <th>Harga</th>
                        <th>Bonus</th>
                        <th>Jumlah</th>
                        <th>Diskon</th>
                        <th>DPP</th>
                        <th>PPN</th>
                        <th>Total</th>
                    </tr>
                    <?php
                    $counter = 0;
                    $total = 0;
                    $dta = null;
                    $dtx = null;
                    foreach($invoice->Details as $idx => $detail) {
                        $counter++;
                        print("<tr>");
                        printf('<td class="right">%s.</td>', $counter);
                        printf('<td>%s</td>', $detail->EntityCode);
                        printf('<td>%s</td>', $detail->ItemCode);
                        printf('<td nowrap="nowrap">%s</td>', $detail->ItemDescs);
                        printf('<td class="right">%s</td>', number_format($detail->Lqty,0));
                        printf('<td class="right">%s</td>', number_format($detail->Sqty,0));
                        printf('<td class="right">%s</td>', number_format($detail->Price,2));
                        if($detail->IsFree == 0){
                            print("<td class='center'><input type='checkbox' disabled></td>");
                            printf('<td class="right">%s</td>', number_format($detail->SubTotal,0));
                        }else{
                            print("<td class='center'><input type='checkbox' checked='checked' disabled></td>");
                            print("<td class='right'>0</td>");
                        }
                        printf('<td class="right">%s</td>', number_format($detail->DiscAmount,0));
                        printf('<td class="right">%s</td>', number_format($detail->SubTotal - $detail->DiscAmount,0));
                        printf('<td class="right">%s</td>', number_format($detail->PpnAmount,0));
                        printf('<td class="right">%s</td>', number_format($detail->SubTotal + $detail->PpnAmount + $detail->PphAmount - $detail->DiscAmount,0));
                        print("</tr>");
                        $total += $detail->SubTotal;
                    }
                    ?>
                    <tr class="bold">
                        <td colspan="8" align="right">Total Rp. </td>
                        <td class="right"><?php print($invoice->BaseAmount != null ? number_format($invoice->BaseAmount,0) : 0); ?></td>
                        <td class="right"><?php print($invoice->DiscAmount != null ? number_format($invoice->DiscAmount,0) : 0); ?></td>
                        <td class="right"><?php print($invoice->PpnAmount != null ? number_format($invoice->BaseAmount - $invoice->DiscAmount,0) : 0); ?></td>
                        <td class="right"><?php print($invoice->PphAmount != null ? number_format($invoice->PpnAmount,0) : 0); ?></td>
                        <td class="right"><?php print($invoice->TotalAmount != null ? number_format($invoice->TotalAmount,0) : 0); ?></td>
                    </tr>
                    <tr>
                        <td colspan="13" class="right"><?php
                            if ($acl->CheckUserAccess("ar.invoice", "add")) {
                                printf('<img src="%s" alt="Invoice Baru" title="Buat invoice baru" id="bTambah" style="cursor: pointer;"/>', $baddnew);
                            }
                            ?>
                            &nbsp;
                            <?php
                            if ($acl->CheckUserAccess("ar.invoice", "print")) {
                                printf('<img src="%s" id="bCetakInvoice" alt="Cetak Bukti Invoice" title="Proses cetak bukti Invoice" style="cursor: pointer;"/>',$bcetak);
                            }
                            ?>
                            &nbsp;
                            <?php
                            printf('<img src="%s" id="bKembali" alt="Daftar Invoice" title="Kembali ke daftar invoice" style="cursor: pointer;"/>',$bkembali);
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- </body> -->
</html>
