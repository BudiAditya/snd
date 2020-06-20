<!DOCTYPE HTML>
<html>
<?php
/** @var $payment Payment */ /** @var $kasbanks KasBank[] */
?>
<head>
	<title>SND System - Entry Pembayaran Hutang</title>
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
    <script type="text/javascript">
        var cabangId = "<?=$payment->CabangId;?>";
        var creditorId = "<?=$payment->CreditorId;?>";
        var paymentId = "<?=$payment->Id;?>";
        $(document).ready(function() {

            $('#CreditorId').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("ap.supplier/getJsonSupplier"));?>",
                idField:'id',
                textField:'sup_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'sup_code',title:'Kode',width:30},
                    {field:'sup_name',title:'Nama Supplier',width:100},
                    {field:'addr1',title:'Alamat',width:100},
                    {field:'city',title:'Kota',width:60}
                ]],
                onSelect: function(index,row){
                    var id = row.id;
                    console.log(id);
                    creditorId = id;
                }
            });

            $('#aGrnSearch').combogrid({
                panelWidth:600,
                url: '<?php print($helper->site_url("ap.payment/getoutstandinggrns_json/"));?>'+cabangId+'/'+creditorId,
                idField:'id',
                textField:'id',
                mode:'get',
                fitColumns:true,
                columns:[[
                    {field:'grn_no',title:'No. GRN',width:75},
                    {field:'sup_inv_no',title:'No. Reff',width:75},
                    {field:'grn_date',title:'Tanggal',width:50},
                    {field:'due_date',title:'J T P',width:50},
                    {field:'balance_amount',title:'Outstanding',width:50,align:'right'}
                ]],
                onSelect: function(index,row){
                    var id = row.id;
                    console.log(id);
                    var gno = row.grn_no;
                    console.log(gno);
                    var rno = row.sup_inv_no;
                    console.log(rno);
                    var ivd = row.grn_date;
                    console.log(ivd);
                    var due = row.due_date;
                    console.log(due);
                    var bal = row.balance_amount;
                    console.log(bal);
                    $('#aGrnId').val(id);
                    $('#aGrnNo').val(gno);
                    $('#aGrnDate').val(ivd);
                    $('#aDueDate').val(due);
                    $('#aKeterangan').val(rno);
                    $('#aGrnOutStanding').val(bal);
                    if (Number($('#BalanceAmount').val()) >= $('#aGrnOutStanding').val()) {
                        $('#aAllocateAmount').val(bal);
                        $('#aBalanceAmount').val(0);
                    }else{
                        $('#aAllocateAmount').val($('#BalanceAmount').val());
                        $('#aBalanceAmount').val(bal - $('#aAllocateAmount').val());
                    }
                }
            });

            $("#aAllocateAmount").change(function(e){
                var out = Number($('#aGrnOutStanding').val());
                var alo = Number($("#aAllocateAmount").val());
                $('#aBalanceAmount').val(out-alo);
            });

            $('#PaymentAmount').numberbox({
                onChange: function(rvalue){
                    var rca = Number(rvalue);
                    var alm = Number($('#AllocateAmount').textbox('getValue'));
                    var bam = rca - alm;
                    $('#BalanceAmount').textbox('setValue',bam);
                }
            });

            $("#bAdDetail").click(function(e){
                if (validMaster()) {
                    $('#aGrnId').val('');
                    $('#aGrnNo').val('');
                    $('#aGrnDate').val('');
                    $('#aDueDate').val('');
                    $('#aGrnOutStanding').val(0);
                    $('#aAllocateAmount').val(0);
                    $('#aBalanceAmount').val(0);
                    newItem();
                }
            });

            $("#bUpdate1").click(function(){
                if (confirm('**Proses ini hanya meng-Update Cara Bayar & Kas/Bank** \nTetap dilanjutkan?')){
                    var wti = $("#WarkatTypeId").combobox('getValue');
                    var wbi = $("#WarkatBankId").combobox('getValue');
                    var urz = '<?php print($helper->site_url("ap.payment/updatecarabayar/".$payment->Id."/")); ?>'+wti+'/'+wbi;
                    //alert(urz);
                    $.get(urz, function(data, status){
                        if (data == 1){
                            alert('Update Data Master berhasil!');
                        }else{
                            alert('Update Data Master gagal!');
                        }
                        location.reload();
                    });
                }
            });

            $("#bUpdate").click(function(){
                if (validMaster()) {
                    if (confirm('Update data master?')) {
                        var url = "<?php print($helper->site_url("ap.payment/update/" . $payment->Id)); ?>";
                        $('#frmMaster').form('submit', {
                            url: url,
                            onSubmit: function () {
                                return $(this).form('validate');
                            },
                            success: function (result) {
                                var dtx = result.split('|');
                                if (dtx[0] == 'OK') {
                                    paymentId = dtx[1];
                                    location.href = "<?php print($helper->site_url("ap.payment/add/")); ?>" + dtx[1];
                                } else {
                                    $.messager.alert('Warning!', result);
                                }
                            }
                        });
                    }
                }
            });

            $("#bTambah").click(function(){
                if (confirm('Buat Payment baru?')){
                    location.href="<?php print($helper->site_url("ap.payment/add/0")); ?>";
                }
            });

            $("#bHapus").click(function(){
                if (confirm('Anda yakin akan membatalkan data payment ini?')){
                    location.href="<?php print($helper->site_url("ap.payment/void/").$payment->Id); ?>";
                }
            });

            $("#bCetak").click(function(){
                if (confirm('Cetak payment ini?')){
                    //location.href="<?php //print($helper->site_url("ap.payment/print_pdf/").$payment->Id); ?>";
                    alert('Proses cetak belum siap..');
                }
            });

            $("#bKembali").click(function(){
                location.href="<?php print($helper->site_url("ap.payment")); ?>";
            });

        });

        function validMaster() {
            var pya = Number($("#PaymentAmount").numberbox('getValue'));
            var bam = Number($("#BalanceAmount").numberbox('getValue'));
            var kbi = Number($("#KasbankId").combobox('getValue'));
            if (creditorId == 0 || creditorId == null || creditorId == ''){
                $.messager.alert('Warning','Supplier belum dipilih!');
                return false;
            }
            if (pya == 0 || pya == null || pya == ''){
                $.messager.alert('Warning','Nilai pembayaran belum diisi!');
                return false;
            }
            if (kbi == 0 || kbi == null || kbi == ''){
                $.messager.alert('Warning','Kas/Bank Pembayaran belum dipilih!');
                return false;
            }
            if (bam == 0 || bam == null || bam == ''){
                $.messager.alert('Warning','Pembayaran sudah dialokasikan semua!');
                return false;
            }
            return true;
        }

        function fdeldetail(dta){
            var dtz = dta.replace(/\"/g,"\\\"")
            var dtx = dtz.split('|');
            var id = dtx[0];
            var gno = dtx[1];
            var bal = dtx[2];
            var urx = '<?php print($helper->site_url("ap.payment/delete_detail/"));?>'+id;
            if (confirm('Hapus Detail Pembayaran \nGRN No: '+gno+ '\nNilai: '+bal+' ?')) {
                $.get(urx, function(data){
                    //alert(data);
                    location.reload();
                });
            }
        }

        function newItem(){
            $('#dlg').dialog('open').dialog('setTitle','Tambah Detail Pembayaran');
            //$('#fm').form('clear');
            creditorId = Number($("#CreditorId").combogrid('getValue'));
            var urz = '<?php print($helper->site_url("ap.payment/getoutstandinggrns_json/"));?>'+cabangId+'/'+creditorId;
            $('#aGrnSearch').combogrid('grid').datagrid('load',urz);
            $('#aGrnNo').focus();
        }

        function saveDetail(){
            var aivi = Number($('#aGrnId').val());
            var aalo = Number($('#aAllocateAmount').val());
            if (paymentId > 0) {
                var urz = "<?php print($helper->site_url("ap.payment/add_detail/"));?>"+paymentId;
                if (aivi > 0 && aalo > 0) {
                    $('#fm').form('submit', {
                        url: urz,
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
                                location.href = "<?php print($helper->site_url("ap.payment/add/")); ?>" + paymentId;
                                $('#dlg').dialog('close');		// close the dialog
                            }
                        }
                    });
                } else {
                    alert('Maaf, Data input tidak valid!');
                }
            }else{
                if (validMaster()){
                    var urx = "<?php print($helper->site_url("ap.payment/update/0")); ?>";
                    $('#frmMaster').form('submit', {
                        url: urx,
                        onSubmit: function () {
                            return $(this).form('validate');
                        },
                        success: function (result) {
                            var dtx = result.split('|');
                            if (dtx[0] == 'OK') {
                                paymentId = dtx[1];
                                var urz = "<?php print($helper->site_url("ap.payment/add_detail/"));?>"+paymentId;
                                if (aivi > 0 && aalo > 0) {
                                    $('#fm').form('submit', {
                                        url: urz,
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
                                                location.href = "<?php print($helper->site_url("ap.payment/add/")); ?>" + paymentId;
                                                $('#dlg').dialog('close');		// close the dialog
                                            }
                                        }
                                    });
                                } else {
                                    alert('Maaf, Data input tidak valid!');
                                }
                            } else {
                                $.messager.alert('Warning!', result);
                            }
                        }
                    });
                }
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
<div id="p" class="easyui-panel" title="Entry Pembayaran Hutang" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <form id="frmMaster" action="<?php print($helper->site_url("ap.payment/add")); ?>" method="post">
        <input type="hidden" name="PaymentMode" id="PaymentMode" value="<?php print($payment->PaymentMode);?>">
        <input type="hidden" name="BankId" id="BankId" value="<?php print($payment->KasbankId);?>">
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($payment->CabangCode != null ? $payment->CabangCode : $userCabCode); ?>" disabled/>
                    <input type="hidden" id="CabangId" name="CabangId" value="<?php print($payment->CabangId == null ? $userCabId : $payment->CabangId);?>"/>
                </td>
                <td>Tanggal</td>
                <td><input type="text" class="easyui-datebox" id="PaymentDate" name="PaymentDate" style="width: 100px" value="<?php print($payment->FormatPaymentDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser" required/></td>
                <td>No. Payment</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="PaymentNo" name="PaymentNo" value="<?php print($payment->PaymentNo != null ? $payment->PaymentNo : '-'); ?>" readonly/></td>
                <td>Status</td>
                <td><select class="easyui-combobox" id="PaymentStatus" name="PaymentStatus" style="width: 110px" disabled>
                        <option value="0" <?php print($payment->PaymentStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                        <option value="1" <?php print($payment->PaymentStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                        <option value="2" <?php print($payment->PaymentStatus == 2 ? 'selected="selected"' : '');?>>2 - Batal</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Supplier</td>
                <td><input class="easyui-combogrid" id="CreditorId" name="CreditorId" style="width: 250px" value="<?php print($payment->CreditorId);?>" required/></td>
                <td>Cara Bayar</td>
                <td><select class="easyui-combobox" id="PaymentTypeId" name="PaymentTypeId" style="width: 100px" required>
                        <?php
                        foreach ($paymenttypes as $pty) {
                            if ($pty->Id == $payment->PaymentTypeId) {
                                printf('<option value="%d" selected="selected"> %s - %s </option>',$pty->Id, $pty->Id, $pty->Type);
                            } else {
                                printf('<option value="%d"> %s - %s </option>',$pty->Id, $pty->Id, $pty->Type);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Kas/Bank</td>
                <td><select class="easyui-combobox" id="KasbankId" name="KasbankId" style="width: 150px" required>
                        <option value=""></option>
                        <?php
                        foreach ($kasbanks as $bank) {
                            if ($bank->Id == $payment->KasbankId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $bank->Id, $bank->Id, $bank->BankName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $bank->Id, $bank->Id, $bank->BankName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Jumlah</td>
                <td><input type="text" class="easyui-numberbox" style="width: 110px;text-align: right;" id="PaymentAmount" name="PaymentAmount" value="<?php print($payment->PaymentAmount != null ? $payment->PaymentAmount : 0); ?>" data-options="min:0,groupSeparator:','"/></td>
            </tr>
            <tr>
                <td>No. Warkat</td>
                <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 250px" id="WarkatNo" name="WarkatNo" value="<?php print($payment->WarkatNo); ?>"/></td>
                <td>Tgl. Warkat</td>
                <td><input type="text" class="easyui-datebox" id="WarkatDate" name="WarkatDate" style="width: 100px" value="<?php print($payment->FormatWarkatDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser"/></td>
                <td>Bank Warkat</td>
                <td><select class="easyui-combobox" id="WarkatBankId" name="WarkatBankId" style="width: 150px">
                        <option value="0"></option>
                        <?php
                        /** @var $banks Bank[] */
                        foreach ($banks as $bank) {
                            if ($bank->Id == $payment->WarkatBankId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $bank->Id, $bank->Id, $bank->BankName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $bank->Id, $bank->Id, $bank->BankName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Alokasi</td>
                <td><input type="text" class="easyui-numberbox" style="width: 110px" id="AllocateAmount" name="AllocateAmount" value="<?php print($payment->AllocateAmount != null ? $payment->AllocateAmount : 0); ?>" readonly data-options="min:0,groupSeparator:','"/></td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td colspan="3"><b><input type="text" class="easyui-textbox" id="PaymentDescs" name="PaymentDescs" style="width: 450px" value="<?php print($payment->PaymentDescs != null ? $payment->PaymentDescs : '-'); ?>"/></b></td>
                <td colspan="2">
                    <?php
                    if ($acl->CheckUserAccess("ap.payment", "edit") && $payment->AllocateAmount == 0) {
                        printf('<img src="%s" alt="Update Data" title="Update data master" id="bUpdate" style="cursor: pointer;"/>',$bsubmit);
                    }
                    ?>
                </td>
                <td>S i s a</td>
                <td><input type="text" class="easyui-numberbox" style="width: 110px" id="BalanceAmount" name="BalanceAmount" value="<?php print($payment->BalanceAmount != null ? $payment->BalanceAmount : 0); ?>" readonly data-options="min:0,groupSeparator:','"/></td>
            </tr>
            <tr>
                <td colspan="5">Detail Pembayaran Hutang :</td>
            </tr>
            <tr>
                <td colspan="7">
                    <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                        <tr>
                            <th>No.</th>
                            <th>Jenis</th>
                            <th>No. GRN</th>
                            <th>Keterangan</th>
                            <th>Tanggal</th>
                            <th>J T P</th>
                            <th>Nilai Hutang</th>
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
                        foreach($payment->Details as $idx => $detail) {
                            $url = $helper->site_url("ap.purchase/view/".$detail->GrnId);
                            $counter++;
                            print("<tr>");
                            printf('<td class="right">%s.</td>', $counter);
                            printf('<td>%s</td>', $detail->ApType == 1 ? 'Opening' : 'Invoice');
                            printf("<td><a href= '%s' target='_blank'>%s</a></td>",$url ,$detail->GrnNo);
                            printf('<td>%s</td>', $detail->Keterangan);
                            printf('<td>%s</td>', $detail->GrnDate);
                            printf('<td>%s</td>', $detail->DueDate);
                            printf('<td class="right">%s</td>', number_format($detail->GrnOutstanding,0));
                            printf('<td class="right">%s</td>', number_format($detail->AllocateAmount,0));
                            printf('<td class="right">%s</td>', number_format(($detail->GrnOutstanding - $detail->AllocateAmount),0));
                            print("<td class='center'>");
                            $dta = addslashes($detail->Id.'|'.$detail->GrnNo.'|'.$detail->GrnOutstanding);
                            printf('&nbsp<img src="%s" alt="Hapus Detail" title="Hapus Detail" style="cursor: pointer" onclick="return fdeldetail(%s);"/>',$bclose,"'".$dta."'");
                            print("</td>");
                            print("</tr>");
                            $tall += $detail->AllocateAmount;
                            $tout += $detail->GrnOutstanding;
                            $tbal += ($detail->GrnOutstanding - $detail->AllocateAmount);
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
                                if ($acl->CheckUserAccess("ap.payment", "add")) {
                                    printf('<img src="%s" alt="Data Baru" title="Buat Data Baru" id="bTambah" style="cursor: pointer;"/> &nbsp',$baddnew);
                                }
                                if ($acl->CheckUserAccess("ap.payment", "delete")) {
                                    printf('<img src="%s" alt="Hapus Data" title="Hapus Data" id="bHapus" style="cursor: pointer;"/> &nbsp',$bdelete);
                                }
                                if ($acl->CheckUserAccess("ap.payment", "print")) {
                                    printf('<img src="%s" alt="Cetak Payment" title="Cetak Payment" id="bCetak" style="cursor: pointer;"/> &nbsp',$bcetak);
                                }
                                printf('<img src="%s" id="bKembali" alt="Daftar Pembayaran" title="Kembali ke daftar penerimaan" style="cursor: pointer;"/>',$bkembali);
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>
<!-- Form Add Payment Detail -->
<div id="dlg" class="easyui-dialog" style="width:950px;height:170px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <form id="fm" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="font-size: 12px;font-family: tahoma">
            <tr>
                <th>Jenis</th>
                <th>No. GRN</th>
                <th>Tanggal</th>
                <th>J T P</th>
                <th>Outstanding</th>
                <th>Dibayar</th>
                <th>Sisa</th>
            </tr>
            <tr>
                <td><select name="aApType" id="aApType">
                        <option value="0"> 0 - Invoice</option>
                        <option value="1"> 1 - Opening</option>
                    </select>
                </td>
                <td>
                    <input type="text" id="aGrnNo" name="aGrnNo" size="15" value="" required/>
                    <input id="aGrnSearch" name="aGrnSearch" style="width: 20px"/>
                    <input type="hidden" id="aGrnId" name="aGrnId" value="0"/>
                </td>
                <td>
                    <input type="text" id="aGrnDate" name="aGrnDate" size="10" value="" disabled/>
                </td>
                <td>
                    <input type="text" id="aDueDate" name="aDueDate" size="10" value="" disabled/>
                </td>
                <td>
                    <input class="right" type="text" id="aGrnOutStanding" name="aGrnOutStanding" size="15" value="0" readonly/>
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
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- </body> -->
</html>
