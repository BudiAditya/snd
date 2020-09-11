<!DOCTYPE HTML>
<html>
<?php
/** @var $receipt Receipt */ /** @var $banks KasBank[] */ /** @var $paymenttypes PaymentType[] */
?>
<head>
	<title>SND System - Entry Penerimaan Piutang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
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
            border-bottom:1px solid #ccc;
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
        var cabangId = "<?=$receipt->CabangId;?>";
        var debtorId = "<?=$receipt->DebtorId;?>";
        var receiptId = "<?=$receipt->Id;?>";
        $(document).ready(function() {

            //var addmaster = ["CabangId", "ReceiptDate","DebtorId", "SalesId", "ReceiptDescs", "PaymentType","CreditTerms","btSubmit", "btKembali"];
            //BatchFocusRegister(addmaster);
            $('#DebtorId').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("ar.customer/getJsonCustomer"));?>",
                idField:'id',
                textField:'cus_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'cus_code',title:'Kode',width:50},
                    {field:'cus_name',title:'Nama Customer',width:150},
                    {field:'addr1',title:'Alamat',width:150},
                    {field:'area_name',title:'Area',width:60}
                ]],
                onSelect: function(index,row){
                    var id = row.id;
                    console.log(id);
                    debtorId = id;
                }
            });

            // getoutstandinginvoice_plain($cabangId,$customerId,$invoiceNo){
            // $ret = 'OK|'.$invoice->Id.'|'.$invoice->InvoiceDate.'|'.$invoice->DueDate.'|'.$invoice->BalanceAmount;
            $("#aInvoiceNo").change(function(e){
                var ivn = $("#aInvoiceNo").val();
                var dbi = "<?php print($receipt->DebtorId);?>";
                var cbi = "<?php print($receipt->CabangId);?>";
                var url = "<?php print($helper->site_url("ar.receipt/getoutstandinginvoices_plain/"));?>"+cbi+"/"+dbi+"/"+ivn;
                if (ivn != ''){
                    $.get(url, function(data, status){
                        //alert("Data: " + data + "\nStatus: " + status);
                        if (status == 'success'){
                            var dtx = data.split('|');
                            if (dtx[0] == 'OK'){
                                $('#aInvoiceId').val(dtx[1]);
                                $('#aInvoiceSearch').val(dtx[1]);
                                $('#aInvoiceDate').val(dtx[2]);
                                $('#aDueDate').val(dtx[3]);
                                $('#aInvoiceOutStanding').val(Number(dtx[4]));
                                if (Number($('#BalanceAmount').val()) >= $('#aInvoiceOutStanding').val()) {
                                    $('#aAllocateAmount').val(Number(dtx[4]));
                                    $('#aBalanceAmount').val(0);
                                }else{
                                    $('#aAllocateAmount').val($('#BalanceAmount').val());
                                    $('#aBalanceAmount').val(Number(dtx[4])-$('#BalanceAmount').val());
                                }
                            }else{
                                alert('Data Invoice Piutang tidak ditemukan!');
                            }
                        }else{
                            alert('Data Invoice Piutang tidak ditemukan!');
                        }
                    });
                }
            });

            $("#aArType").change(function(e){
               alert('Test..');
            });

            $('#aInvoiceSearch').combogrid({
                panelWidth:500,
                url: "<?php print($helper->site_url('ar.receipt/getoutstandinginvoices_json/'));?>"+cabangId+'/'+debtorId,
                idField:'id',
                textField:'id',
                mode:'get',
                fitColumns:true,
                columns:[[
                    {field:'invoice_no',title:'No. Invoice',width:50},
                    {field:'invoice_date',title:'Tanggal',width:50},
                    {field:'due_date',title:'J T P',width:50},
                    {field:'balance_amount',title:'Outstanding',width:50,align:'right'}
                ]],
                onSelect: function(index,row){
                    var id = row.id;
                    console.log(id);
                    var ivn = row.invoice_no;
                    console.log(ivn);
                    var ivd = row.invoice_date;
                    console.log(ivd);
                    var due = row.due_date;
                    console.log(due);
                    var bal = row.balance_amount;
                    console.log(bal);
                    $('#aInvoiceId').val(id);
                    $('#aInvoiceNo').val(ivn);
                    $('#aInvoiceDate').val(ivd);
                    $('#aDueDate').val(due);
                    $('#aInvoiceOutStanding').val(bal);
                    if (Number($('#BalanceAmount').val()) >= $('#aInvoiceOutStanding').val()) {
                        $('#aAllocateAmount').val(bal);
                        $('#aBalanceAmount').val(0);
                    }else{
                        $('#aAllocateAmount').val($('#BalanceAmount').val());
                        $('#aBalanceAmount').val(bal - $('#aAllocateAmount').val());
                    }
                }
            });

            $("#aAllocateAmount").change(function(e){
                var out = Number($('#aInvoiceOutStanding').val());
                var alo = Number($("#aAllocateAmount").val());
                $('#aBalanceAmount').val(out-alo);
            });

            $('#ReceiptAmount').numberbox({
                onChange: function(rvalue){
                    var rca = Number(rvalue);
                    var alm = Number($('#AllocateAmount').textbox('getValue'));
                    var bam = rca - alm;
                    $('#BalanceAmount').textbox('setValue',bam);
                }
            });

            $("#bAdDetail").click(function(e){
                if (validMaster()) {
                    $('#aInvoiceId').val('');
                    $('#aInvoiceNo').val('');
                    $('#aInvoiceDate').val('');
                    $('#aDueDate').val('');
                    $('#aInvoiceOutStanding').val(0);
                    $('#aAllocateAmount').val(0);
                    $('#aBalanceAmount').val(0);
                    newItem();
                }
            });

            $("#bTambah").click(function(){
                if (confirm('Buat Receipt baru?')){
                    location.href="<?php print($helper->site_url("ar.receipt/add/0")); ?>";
                }
            });

            $("#bUpdate").click(function(){
                if (confirm('Update data master?')){
                    var url = "<?php print($helper->site_url("ar.receipt/update/".$receipt->Id)); ?>";
                    $('#frmMaster').form('submit', {
                        url: url,
                        onSubmit: function () {
                            return $(this).form('validate');
                        },
                        success: function (result) {
                            var dtx = result.split('|');
                            if (dtx[0] == 'OK'){
                                receiptId = dtx[1];
                                location.href = "<?php print($helper->site_url("ar.receipt/add/")); ?>"+dtx[1];
                            }else{
                                $.messager.alert('Warning!',result);
                            }
                        }
                    });
                }
            });

            $("#bHapus").click(function(){
                if (confirm('Anda yakin akan membatalkan data receipt ini?')){
                    location.href="<?php print($helper->site_url("ar.receipt/void/").$receipt->Id); ?>";
                }
            });

            $("#bCetak").click(function(){
                if (confirm('Cetak receipt ini?')){
                    //location.href="<?php //print($helper->site_url("ar.receipt/print_pdf/").$receipt->Id); ?>";
                    alert('Proses cetak belum siap..');
                }
            });

            $("#bKembali").click(function(){
                location.href="<?php print($helper->site_url("ar.receipt")); ?>";
            });
        });

        function validMaster() {
            var rca = Number($("#ReceiptAmount").numberbox('getValue'));
            var bam = Number($("#BalanceAmount").numberbox('getValue'));
            var kbi = Number($("#KasbankId").combobox('getValue'));
            if (debtorId == 0 || debtorId == null || debtorId == ''){
                $.messager.alert('Warning','Customer belum dipilih!');
                return false;
            }
            if (rca == 0 || rca == null || rca == ''){
                $.messager.alert('Warning','Nilai penerimaan belum diisi!');
                return false;
            }
            if (kbi == 0 || kbi == null || kbi == ''){
                $.messager.alert('Warning','Kas/Bank penerimaan belum dipilih!');
                return false;
            }
            if (bam == 0 || bam == null || bam == ''){
                $.messager.alert('Warning','Penerimaan sudah dialokasikan semua!');
                return false;
            }
            return true;
        }

        function fdeldetail(dta){
            var dtz = dta.replace(/\"/g,"\\\"")
            var dtx = dtz.split('|');
            var id = dtx[0];
            var ivn = dtx[1];
            var bal = dtx[2];
            var urx = '<?php print($helper->site_url("ar.receipt/delete_detail/"));?>'+id;
            if (confirm('Hapus Detail penerimaan \nInvoice No: '+ivn+ '\nNilai: '+bal+' ?')) {
                $.get(urx, function(data){
                    //alert(data);
                    location.reload();
                });
            }
        }

        function newItem(){
            $('#dlg').dialog('open').dialog('setTitle','Tambah Detail Penerimaan');
            //$('#fm').form('clear');
            debtorId = Number($("#DebtorId").combogrid('getValue'));
            var urz = "<?php print($helper->site_url('ar.receipt/getoutstandinginvoices_json/'));?>"+cabangId+'/'+debtorId;
            $('#aInvoiceSearch').combogrid('grid').datagrid('load',urz);
            $('#aInvoiceNo').focus();
        }

        function saveDetail(){
            var aivi = Number($('#aInvoiceId').val());
            var aalo = Number($('#aAllocateAmount').val());
            if (receiptId > 0) {
                if (aivi > 0 && aalo > 0) {
                    var urx = "<?php print($helper->site_url("ar.receipt/add_detail/"));?>"+receiptId;
                    $('#fm').form('submit', {
                        url: urx,
                        onSubmit: function () {
                            return $(this).form('validate');
                        },
                        success: function (result) {
                            var result = eval('(' + result + ')');
                            if (result.errorMsg) {
                                $.messager.show({
                                    title: 'Error',
                                    msg: result.errorMsg
                                });
                            } else {
                                location.href = "<?php print($helper->site_url("ar.receipt/add/")); ?>"+receiptId;
                                $('#dlg').dialog('close');		// close the dialog
                            }
                        }
                    });
                } else {
                    alert('Maaf, Data input tidak valid!');
                }
            }else{
                var urz = "<?php print($helper->site_url("ar.receipt/update/")); ?>"+receiptId;
                $('#frmMaster').form('submit', {
                    url: urz,
                    onSubmit: function () {
                        return $(this).form('validate');
                    },
                    success: function (result) {
                        var dtx = result.split('|');
                        if (dtx[0] == 'OK'){
                            receiptId = Number(dtx[1]);
                            var urx = "<?php print($helper->site_url("ar.receipt/add_detail/"));?>"+receiptId;
                            if (aivi > 0 && aalo > 0) {
                                $('#fm').form('submit', {
                                    url: urx,
                                    onSubmit: function () {
                                        return $(this).form('validate');
                                    },
                                    success: function (result) {
                                        var result = eval('(' + result + ')');
                                        if (result.errorMsg) {
                                            $.messager.show({
                                                title: 'Error',
                                                msg: result.errorMsg
                                            });
                                        } else {
                                            //location.reload();
                                            location.href = "<?php print($helper->site_url("ar.receipt/add/")); ?>"+receiptId;
                                            $('#dlg').dialog('close');		// close the dialog
                                        }
                                    }
                                });
                            } else {
                                alert('Maaf, Data input tidak valid!');
                            }
                        }else{
                            $.messager.alert('Warning!',result);
                        }
                    }
                });
            }
        }

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
?>
<br />
<div id="p" class="easyui-panel" title="Entry Penerimaan Piutang" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <form id="frmMaster" action="<?php print($helper->site_url("ar.receipt/add")); ?>" method="post">
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($receipt->CabangCode != null ? $receipt->CabangCode : $userCabCode); ?>" disabled/>
                    <input type="hidden" id="CabangId" name="CabangId" value="<?php print($receipt->CabangId == null ? $userCabId : $receipt->CabangId);?>"/>
                </td>
                <td>Tanggal</td>
                <td><input type="text" class="easyui-datebox" id="ReceiptDate" name="ReceiptDate" style="width: 100px" value="<?php print($receipt->FormatReceiptDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser"/></td>
                <td>No. Receipt</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="ReceiptNo" name="ReceiptNo" value="<?php print($receipt->ReceiptNo != null ? $receipt->ReceiptNo : '-'); ?>" readonly/></td>
                <td>Status</td>
                <td><select class="easyui-combobox" id="ReceiptStatus" name="ReceiptStatus" style="width: 110px" readonly>
                        <option value="0" <?php print($receipt->ReceiptStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                        <option value="1" <?php print($receipt->ReceiptStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                        <option value="2" <?php print($receipt->ReceiptStatus == 2 ? 'selected="selected"' : '');?>>2 - Approved</option>
                        <option value="3" <?php print($receipt->ReceiptStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Customer</td>
                <td><input class="easyui-combogrid" id="DebtorId" name="DebtorId" style="width: 250px" value="<?php print($receipt->DebtorId);?>" required/></td>
                <td>Cara Bayar</td>
                <td><select class="easyui-combobox" id="PaymentTypeId" name="PaymentTypeId" style="width: 100px">
                        <?php
                        foreach ($paymenttypes as $wti) {
                            if ($wti->Id == $receipt->PaymentTypeId) {
                                printf('<option value="%d" selected="selected"> %s - %s </option>',$wti->Id, $wti->Id, $wti->Type);
                            } else {
                                printf('<option value="%d"> %s - %s </option>',$wti->Id, $wti->Id, $wti->Type);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Kas/Bank</td>
                <td><select class="easyui-combobox" id="KasbankId" name="KasbankId" style="width: 150px" required>
                        <option value="0"></option>
                        <?php
                        foreach ($kasbanks as $bank) {
                            if ($bank->Id == $receipt->KasbankId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $bank->Id, $bank->Id, $bank->BankName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $bank->Id, $bank->Id, $bank->BankName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Jumlah</td>
                <td><input type="text" class="easyui-numberbox" style="width: 110px;text-align: right;" id="ReceiptAmount" name="ReceiptAmount" value="<?php print($receipt->ReceiptAmount != null ? $receipt->ReceiptAmount : 0); ?>" data-options="min:0,groupSeparator:','"/></td>
            </tr>
            <tr>
                <td>No. Warkat</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="WarkatNo" name="WarkatNo" value="<?php print($receipt->WarkatNo); ?>"/></td>
                <td>Tgl. Warkat</td>
                <td><input type="text" class="easyui-datebox" id="WarkatDate" name="WarkatDate" style="width: 100px" value="<?php print($receipt->FormatWarkatDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser"/></td>
                <td>Bank Warkat</td>
                <td><select class="easyui-combobox" id="WarkatBankId" name="WarkatBankId" style="width: 150px">
                        <option value="0"></option>
                        <?php
                        foreach ($banks as $bank) {
                            if ($bank->Id == $receipt->WarkatBankId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $bank->Id, $bank->Id, $bank->BankName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $bank->Id, $bank->Id, $bank->BankName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Alokasi</td>
                <td><input type="text" class="easyui-numberbox" style="width: 110px;text-align: right;" id="AllocateAmount" name="AllocateAmount" value="<?php print($receipt->AllocateAmount != null ? $receipt->AllocateAmount : 0); ?>" readonly data-options="min:0,groupSeparator:','"/></td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td colspan="3"><b><input type="text" class="easyui-textbox" id="ReceiptDescs" name="ReceiptDescs" style="width: 450px" value="<?php print($receipt->ReceiptDescs != null ? $receipt->ReceiptDescs : '-'); ?>" required/></b></td>
                <td colspan="2">
                <?php
                    if ($acl->CheckUserAccess("ar.receipt", "edit") && $receipt->AllocateAmount == 0) {
                    printf('<img src="%s" alt="Update Data" title="Update data master" id="bUpdate" style="cursor: pointer;"/>',$bsubmit);
                    }
                    ?>
                </td>
                <td>S i s a</td>
                <td><input type="text" class="easyui-numberbox" style="width: 110px;text-align: right;" id="BalanceAmount" name="BalanceAmount" value="<?php print($receipt->BalanceAmount != null ? $receipt->BalanceAmount : 0); ?>" readonly data-options="min:0,groupSeparator:','"/></td>
            </tr>
            <tr>
                <td colspan="5">Detail Penerimaan Piutang :</td>
            </tr>
            <tr>
                <td colspan="7">
                    <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                        <tr>
                            <th>No.</th>
                            <th>Jenis</th>
                            <th>No. Invoice</th>
                            <th>Keterangan</th>
                            <th>Tanggal</th>
                            <th>J T P</th>
                            <th>Nilai Piutang</th>
                            <th>Dibayar</th>
                            <th>Sisa</th>
                            <th>Action</th>
                        </tr>
                        <?php
                        $counter = 0;
                        $tout = 0;
                        $tall = 0;
                        $tbal = 0;
                        $dta = null;
                        $url = null;
                        foreach($receipt->Details as $idx => $detail) {
                            $url = $helper->site_url("ar.invoice/view/".$detail->InvoiceId);
                            $counter++;
                            print("<tr>");
                            printf('<td class="right">%s.</td>', $counter);
                            printf('<td>%s</td>', $detail->ArType == 1 ? 'Opening' : 'Invoice');
                            printf("<td><a href= '%s' target='_blank'>%s</a></td>",$url ,$detail->InvoiceNo);
                            printf('<td>%s</td>', $detail->Keterangan);
                            printf('<td>%s</td>', $detail->InvoiceDate);
                            printf('<td>%s</td>', $detail->DueDate);
                            printf('<td class="right">%s</td>', number_format($detail->InvoiceOutstanding,0));
                            printf('<td class="right">%s</td>', number_format($detail->AllocateAmount,0));
                            printf('<td class="right">%s</td>', number_format(($detail->InvoiceOutstanding - $detail->AllocateAmount),0));
                            print("<td class='center'>");
                            $dta = addslashes($detail->Id.'|'.$detail->InvoiceNo.'|'.$detail->InvoiceOutstanding);
                            printf('&nbsp<img src="%s" alt="Hapus Detail" title="Hapus Detail" style="cursor: pointer" onclick="return fdeldetail(%s);"/>',$bclose,"'".$dta."'");
                            print("</td>");
                            print("</tr>");
                            $tall += $detail->AllocateAmount;
                            $tout += $detail->InvoiceOutstanding;
                            $tbal += ($detail->InvoiceOutstanding - $detail->AllocateAmount);
                        }
                        ?>
                        <tr>
                            <td colspan="6" class="bold right">Sub Total :</td>
                            <td class="bold right"><?php print(number_format($tout,0));?></td>
                            <td class="bold right"><?php print(number_format($tall,0));?></td>
                            <td class="bold right"><?php print(number_format($tbal,0));?></td>
                            <td class='center'><?php printf('<img src="%s" alt="Tambah Detail" title="Tambah Detail" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                        </tr>
                        <tr>
                            <td colspan="10" class="right">
                                <?php
                                if ($acl->CheckUserAccess("ar.receipt", "add")) {
                                    printf('<img src="%s" alt="Data Baru" title="Buat Data Baru" id="bTambah" style="cursor: pointer;"/> &nbsp',$baddnew);
                                }
                                if ($acl->CheckUserAccess("ar.receipt", "delete")) {
                                    printf('<img src="%s" alt="Hapus Data" title="Hapus Data" id="bHapus" style="cursor: pointer;"/> &nbsp',$bdelete);
                                }
                                if ($acl->CheckUserAccess("ar.receipt", "print")) {
                                    printf('<img src="%s" alt="Cetak Receipt" title="Cetak Receipt" id="bCetak" style="cursor: pointer;"/> &nbsp',$bcetak);
                                }
                                printf('<img src="%s" id="bKembali" alt="Daftar Penerimaan" title="Kembali ke daftar penerimaan" style="cursor: pointer;"/>',$bkembali);
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>
<!-- Form Add Receipt Detail -->
<div id="dlg" class="easyui-dialog" style="width:950px;height:170px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <form id="fm" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="font-size: 12px;font-family: tahoma">
            <tr>
                <th>Jenis</th>
                <th>No.Invoice</th>
                <th>Tgl.Invoice</th>
                <th>J T P</th>
                <th>Outstanding</th>
                <th>Dibayar</th>
                <th>Sisa</th>
            </tr>
            <tr>
                <td><select name="aArType" id="aArType">
                        <option value="0"> 0 - Invoice</option>
                        <option value="1"> 1 - Opening</option>
                    </select>
                </td>
                <td>
                    <input type="text" id="aInvoiceNo" name="aInvoiceNo" size="15" value="" required/>
                    <input id="aInvoiceSearch" name="aInvoiceSearch" style="width: 20px"/>
                    <input type="hidden" id="aInvoiceId" name="aInvoiceId" value="0"/>
                </td>
                <td>
                    <input type="text" id="aInvoiceDate" name="aInvoiceDate" size="10" value="" disabled/>
                </td>
                <td>
                    <input type="text" id="aDueDate" name="aDueDate" size="10" value="" disabled/>
                </td>
                <td>
                    <input class="right" type="text" id="aInvoiceOutStanding" name="aInvoiceOutStanding" size="15" value="0" readonly/>
                </td>
                <td>
                    <input class="right" type="text" id="aAllocateAmount" name="aAllocateAmount" size="15" value="0"/>
                </td>
                <td>
                    <input class="right" type="text" id="aBalanceAmount" name="aBalanceAmount" size="15" value="0" readonly/>
                </td>
            </tr>
            <tr>
                <td class="bold right">Keterangan</td>
                <td colspan="6"><input type="text" name="aKeterangan" id="aKeterangan" value="-" size="120"/> </td>
            </tr>
        </table>
    </form>
    <br>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveDetail()" style="width:90px">Proses</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Batal</a>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px">
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- </body> -->
</html>
