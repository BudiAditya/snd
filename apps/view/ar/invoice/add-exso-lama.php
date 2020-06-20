<!DOCTYPE HTML>
<html>
<?php
/** @var $invoice Invoice */
?>
<head>
    <title>SND System - Entry Invoice Penjualan</title>
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
        var invoiceId  = "<?php print($invoice->Id == null ? 0 : $invoice->Id);?>";
        var detailCount = "<?=$itemsCount;?>";
        var custId = "<?=$invoice->CustomerId;?>";
        var gudangId = "<?=$invoice->GudangId;?>";
        var areaId = "<?=$invoice->AreaId;?>";
        $( function() {
            //var addetail = ["aItemCode", "aQty","aPrice", "aDiscFormula", "aDiscAmount", "aIsFree", "aSubTotal"];
            //BatchFocusRegister(addetail);
            //var addmaster = ["CabangId", "InvoiceDate","ReceiptDate","CustomerId", "SalesName", "InvoiceDescs", "PaymentType","CreditTerms","bUpdate", "bKembali"];
            //BatchFocusRegister(addmaster);

            $('#CustomerId').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("ar.customer/getJsonCustomer"));?>",
                idField:'id',
                textField:'cus_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'cus_name',title:'Nama Customer',width:150},
                    {field:'cus_code',title:'Kode',width:50},
                    {field:'addr1',title:'Alamat',width:150},
                    {field:'area_name',title:'Area',width:60}
                ]],
                onSelect: function(index,row){
                    var csi = row.id;
                    console.log(csi);
                    var term = row.term;
                    console.log(term);
                    if (term > 0){
                        $("#PaymentType").combobox("setValue",1);
                        $("#CreditTerms").textbox("setValue",term);
                    }else{
                        $("#PaymentType").combobox("setValue",0);
                        $("#CreditTerms").textbox("setValue",0);
                    }
                }
            });

            $('#aItemSearch').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("ar.invoice/getitems_json/0"));?>",
                idField:'item_id',
                textField:'item_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'item_code',title:'Kode',width:50},
                    {field:'item_name',title:'Nama Barang',width:150},
                    {field:'s_uom_code',title:'Satuan',width:40},
                    {field:'qty_order',title:'Order',width:40,align:'right'},
                    {field:'hrg_jual',title:'Harga',width:40,align:'right'}
                ]],
                onSelect: function(index,row){
                    var bid = row.item_id;
                    console.log(bid);
                    var bkode = row.item_code;
                    console.log(bkode);
                    var bnama = row.item_name;
                    console.log(bnama);
                    var satuan = row.s_uom_code;
                    console.log(satuan);
                    var harga = row.hrg_jual;
                    console.log(harga);
                    var qty = row.qty_order;
                    console.log(qty);
                    var isi = row.s_uom_qty;
                    console.log(isi);
                    var qst = 0;
                    var dtz = 0;
                    var adt = new Array();
                    $('#aItemId').val(bid);
                    $('#aItemCode').textbox('setValue',bkode);
                    $('#aItemDescs').textbox('setValue',bnama);
                    $('#aSatuan').textbox('setValue',satuan);
                    $('#aPrice').val(harga);
                    $('#aDiscFormula').val(0);
                    $('#aDiscAmount').val(0);
                    if (isi > 0 && qty >= isi) {
                        dtz = round(qty/isi,2);
                        dtz = dtz.toString();
                        adt = dtz.split('.');
                        $("#lQty").val(adt[0]);
                        $("#sQty").val(qty - (Number(adt[0]) * isi));
                    }else{
                        $("#lQty").val(0);
                        $("#sQty").val(qty);
                    }
                    $('#aQty').val(qty);
                    $('#aPpnPct').val(10);
                    $('#aPpnAmount').val(0);
                    $('#aPphPct').val(0);
                    $('#aPphAmount').val(0);
                    $('#aIsiSatKecil').val(row.s_uom_qty);
                    $('#lUom').text(row.l_uom_code+' x '+isi);
                    $('#sUom').text(row.s_uom_code);
                    $('#qUom').text(row.s_uom_code);
                    $('#pUom').text('/'+row.l_uom_code);
                    //check stock
                    if (invoiceId == 0) {
                        gudangId = $('#GudangId').combobox('getValue');
                    }
                    $.get("<?php print($helper->site_url("ar.invoice/checkStock/"));?>"+gudangId+'/'+bid, function(data){
                        qst = Number(data);
                        $('#aQtyStock').val(qst);
                        $('#xQtyStock').textbox('setValue',qst);
                        if (qst > 0) {
                            $('#lQty').prop('disabled',false);
                            $('#sQty').prop('disabled',false);
                            fillSalePrice(areaId, bid);
                            hitDetail();
                        }else{
                            $('#lQty').prop('disabled',true);
                            $('#sQty').prop('disabled',true);
                            $.messager.alert('Warning','Maaf Stock produk tidak cukup!');
                        }
                    });
                }
            });

            $("#bAdDetail").click(function(e){
                if (validasiMaster()) {
                    $('#aItemId').val(0);
                    $('#aItemCode').textbox('setValue', '');
                    $('#aItemDescs').textbox('setValue', '');
                    $('#aSatuan').textbox('setValue', '');
                    $('#aPrice').val(0);
                    $('#aQty').val(0);
                    $('#aDiscFormula').val('0');
                    $('#aDiscAmount').val(0);
                    $('#aIsFree').val(0);
                    $('#aSubTotal').val(0);
                    $('#aPpnPct').val(10);
                    $('#aPpnAmount').val(0);
                    $('#aPphPct').val(0);
                    $('#aPphAmount').val(0);
                    $('#lQty').val(0);
                    $('#sQty').val(0);
                    $('#aQtyStock').val(0);
                    $('#xQtyStock').textbox('setValue', 0);
                    newItem();
                }else{
                    $.messager.alert('Warning','Data Master tidak valid!');
                }
            });

            $("#lQty").change(function(e){
                hitQty();
                hitDetail();
            });

            $("#sQty").change(function(e){
                hitQty();
                hitDetail();
            });

            $("#aPrice").change(function(e){
                hitDetail();
            });

            $("#aPpnPct").change(function(e){
                hitDetail();
            });

            $("#aPphPct").change(function(e){
                hitDetail();
            });

            $("#aDiscFormula").change(function(e){
                hitDetail();
            });

            $("#aDiscAmount").change(function(e){
                if ($('#aDiscAmount').val() > 0) {
                    $('#aDiscFormula').val('0');
                    hitDetail();
                }
            });

            $('#aIsFree').change(function () {
                if (this.value == 1){
                    $('#aDiscFormula').val('0');
                    $('#aDiscAmount').val(0);
                }
                hitDetail();
            });

            $("#bUpdate").click(function(){
                $.messager.confirm('Confirm','Update data master?',function(r) {
                    if (r) {
                        saveMaster();
                    }
                });
            });

            $("#bTambah").click(function(){
                $.messager.confirm('Confirm','Buat Invoice baru?',function(r){
                    if (r){
                        location.href="<?php print($helper->site_url("ar.invoice/add/0")); ?>";
                    }
                });
            });

            $("#bHapus").click(function(){
                $.messager.confirm('Confirm','Batalkan Invoice ini?',function(r){
                    if (r){
                        location.href="<?php print($helper->site_url("ar.invoice/void/").$invoice->Id); ?>";
                    }
                });
            });

            $("#bKembali").click(function(){
                location.href="<?php print($helper->site_url("ar.invoice")); ?>";
            });
        });

        function hitQty() {
            var iQty = Number($("#aIsiSatKecil").val());
            var lQTy = Number($("#lQty").val());
            var sQTy = Number($("#sQty").val());
            var tQty = Number($("#aQtyStock").val());
            var rQty = Number((lQTy * iQty)) + sQTy;
            if (tQty >= rQty) {
                $("#aQty").val(rQty);
            }else{
                $("#lQty").val(0);
                $("#sQty").val(0);
                $("#aQty").val(0);
                $.messager.alert('Warning','Maaf Stock produk tidak cukup!');
            }
        }

        function hitDetail(){
            var isFree = Number($("#aIsFree").val());
            var tpp = Number($('#aPpnPct').val());
            var tph = Number($('#aPphPct').val());
            var txa = 0;
            var tha = 0;
            var isi = Number($("#aIsiSatKecil").val());
            var hrg = Number($("#aPrice").val());
            var lqt = Number($("#lQty").val());
            var sqt = Number($("#sQty").val());
            var subTotal = 0;
            var dpp = 0;
            var discAmount = 0;
            var totalDetail = 0;
            if (isFree == 0 && hrg > 0 && isi > 0){
                if (lqt > 0){
                    subTotal+= round(lqt * hrg,0);
                }
                if (sqt > 0){
                    hrg = Number(round(hrg/isi,2));
                    subTotal+= round(sqt * hrg,0);
                }
                //alert(hrg);
                if ($("#aDiscFormula").val() != null && $("#aDiscFormula").val() != '0' && $("#aDiscFormula").val() != '') {
                    discAmount = hitDiscFormula(subTotal, $("#aDiscFormula").val());
                }else{
                    discAmount = Number($('#aDiscAmount').val());
                }
                dpp = subTotal - discAmount;
                if (dpp > 0 && tpp > 0){
                    txa = round(dpp * (tpp/100),0);
                }
                if (dpp > 0 && tph > 0){
                    tha = round(dpp * (tph/100),0);
                }
                totalDetail = dpp + txa + tha;
            }
            $('#aDiscAmount').val(discAmount);
            $('#aPpnAmount').val(txa);
            $('#aPphAmount').val(tha);
            $('#aDpp').val(dpp);
            $('#aSubTotal').val(subTotal);
            $('#aTotal').val(totalDetail);
        }

        function fdeldetail(dta){
            var dtz = dta.replace(/\"/g,"\\\"")
            var dtx = dtz.split('|');
            var id = dtx[0];
            var kode = dtx[1];
            var barang = dtx[2];
            var urx = '<?php print($helper->site_url("ar.invoice/delete_detail/"));?>'+id;
            $.messager.confirm('Confirm','Hapus Data Detail Barang \nKode: '+kode+ '\nNama: '+barang+' ?',function(r) {
                if (r) {
                    $.get(urx, function (data) {
                        location.reload();
                    });
                }
            });
        }

        function newItem(){
            $('#dlg').dialog('open').dialog('setTitle','Tambah Detail Barang yang diterima');
            //$('#fm').form('clear');
            var urz = "<?php print($helper->site_url('ar.invoice/getjson_solists/'.$invoice->CustomerId));?>";
            $('#dExSoNo').combogrid('grid').datagrid('load',urz);
            url= "<?php print($helper->site_url('ar.invoice/add_detail/'));?>"+invoiceId;
            $('#aItemCode').focus();
        }

        function validasiMaster(){
            var csi = $('#CustomerId').combogrid("getValue");
            var ivd = $('#InvoiceDate').datebox("getValue");
            var sli = $('#SalesId').combobox("getValue");
            if (invoiceId == 0) {
                var whi = $('#GudangId').combobox("getValue");
            }else{
                var whi = "<?php print($invoice->GudangId);?>";
            }
            var pty = $('#PaymentType').combobox("getValue");
            var crt = $('#CreditTerms').textbox("getValue");
            if (csi == 0 || csi == '' || csi == null){
                $.messager.alert('Warning','Customer belum dipilih!');
                $('#CustomerId').focus();
                return false;
            }
            if (ivd == 0 || ivd == '' || ivd == null){
                $.messager.alert('Warning','Tanggal Invoice belum diisi!');
                $('#InvoiceDate').focus();
                return false;
            }
            if (sli == 0 || sli == '' || sli == null){
                $.messager.alert('Warning','Salesman belum dipilih!');
                $('#SalesId').focus();
                return false;
            }
            if (whi == 0 || whi == '' || whi == null){
                $.messager.alert('Warning','Gudang belum dipilih!');
                $('#GudangId').focus();
                return false;
            }
            if (pty == 1 && crt == 0){
                $.messager.alert('Warning','Lama Kredit belum diisi!');
                $('#CreditTerms').focus();
                return false;
            }
            return true;
        }

        function saveMaster() {
            if (validasiMaster()){
                if (detailCount > 0){
                    var whi = gudangId;
                }else{
                    var whi = $('#GudangId').combobox("getValue");
                }
                var data = {
                    CustomerId: $('#CustomerId').combobox("getValue"),
                    InvoiceNo: $('#InvoiceNo').textbox("getValue"),
                    InvoiceDate: $('#InvoiceDate').datebox("getValue"),
                    GudangId: whi,
                    SalesId: $('#SalesId').combobox("getValue"),
                    DbAccId: $('#DbAccId').combobox("getValue"),
                    InvoiceDescs: $("#InvoiceDescs").textbox("getValue"),
                    PaymentType: $('#PaymentType').combobox("getValue"),
                    CreditTerms: $('#CreditTerms').textbox("getValue"),
                    ExpeditionId: $('#ExpeditionId').combobox("getValue")
                };
                var urz = "<?php print($helper->site_url("ar.invoice/proses_master/")); ?>" + invoiceId;
                $.post(urz, data).done(function (dtx) {
                    var rst = dtx.split('|');
                    if (rst[0] == 'OK') {
                        invoiceId = rst[2];
                        location.reload();
                    }
                });
            }
        }

        function saveDetail(){
            /*
            if (invoiceId == 0){
                saveMaster();
            }
            */
            var aitd = Number($('#aItemId').val());
            var aqty = Number($('#aQty').val());
            var astt = Number($('#aSubTotal').val());
            var aisf = $('#aIsFree').val();
            if ((aitd > 0 && aqty > 0 && astt > 0) || (aitd > 0 && aqty > 0 && aisf > 0) && validasiMaster()){
                if (invoiceId == 0) {
                    var data = {
                        CustomerId: $('#CustomerId').combobox("getValue"),
                        InvoiceNo: $('#InvoiceNo').textbox("getValue"),
                        InvoiceDate: $('#InvoiceDate').datebox("getValue"),
                        GudangId: $('#GudangId').combobox("getValue"),
                        SalesId: $('#SalesId').combobox("getValue"),
                        DbAccId: $('#DbAccId').combobox("getValue"),
                        InvoiceDescs: $("#InvoiceDescs").textbox("getValue"),
                        PaymentType: $('#PaymentType').combobox("getValue"),
                        CreditTerms: $('#CreditTerms').textbox("getValue"),
                        ExpeditionId: $('#ExpeditionId').combobox("getValue")
                    };
                    var urz = "<?php print($helper->site_url("ar.invoice/proses_master/")); ?>" + invoiceId;
                    $.post(urz, data).done(function (dtx) {
                        var rst = dtx.split('|');
                        if (rst[0] == 'OK') {
                            invoiceId = rst[2];
                            var url= "<?php print($helper->site_url('ar.invoice/add_detail/'));?>"+invoiceId;
                            var urx = "<?php print($helper->site_url("ar.invoice/add/")); ?>" + invoiceId;
                            $('#frmDetail').form('submit', {
                                url: url,
                                onSubmit: function () {
                                    return $(this).form('validate');
                                },
                                success: function (result) {
                                    var result = eval('(' + result + ')');
                                    if (result.errorMsg) {
                                        $.messager.alert('Error',result.errorMsg);
                                    } else {
                                        location.href = urx;
                                        $('#dlg').dialog('close');
                                    }
                                }
                            });
                        }else{
                            $.messager.alert('Warning','Data Master gagal disimpan!');
                        }
                    });
                }else {
                    var url= "<?php print($helper->site_url('ar.invoice/add_detail/'));?>"+invoiceId;
                    var urx = "<?php print($helper->site_url("ar.invoice/add/")); ?>" + invoiceId;
                    $('#frmDetail').form('submit', {
                        url: url,
                        onSubmit: function () {
                            return $(this).form('validate');
                        },
                        success: function (result) {
                            var result = eval('(' + result + ')');
                            if (result.errorMsg) {
                                $.messager.alert('Error',result.errorMsg);
                            } else {
                                location.href = urx;
                                $('#dlg').dialog('close');
                            }
                        }
                    });
                }
            }else{
                $.messager.alert('Warning','Data tidak valid!');
            }
        }

        function hitDiscFormula(nAmount,dFormula) {
            nAmount = Number(nAmount);
            if (nAmount > 0 && dFormula != '' && dFormula != '0') {
                var aFormula = dFormula.split('+');
                var nDiscount = 0;
                var pDiscount = 0;
                var retVal = 0;
                for (var i = 0; i < aFormula.length; i++) {
                    pDiscount = aFormula[i];
                    nAmount -= nDiscount;
                    nDiscount = round(nAmount * (pDiscount / 100), 0);
                    retVal += nDiscount;
                }
            }
            return retVal;
        }

        function fillSalePrice(areaId,itemId) {
            var csi = $('#CustomerId').combogrid("getValue");
            var urs = "<?php print($helper->site_url("ar.invoice/getItemSalePriceBySalesArea/"));?>"+csi+'/'+itemId;
            $.get(urs, function(data, status){
                var dta = data.split('|');
                if(dta[0] == 'ERR'){
                    $.messager.alert('Warning','Harga Produk ini belum disetting!');
                }else{
                    $('#aPrice').val(dta[1]);
                    $('#pUom').text('/'+dta[0]);
                }
            });
        }

        function checkStock(whId,itemId) {
            var urs = "<?php print($helper->site_url("ar.invoice/checkStock/"));?>"+whId+'/'+itemId;
            $.get(urs, function(data){
                var qty = Number(data);
                return qty;
            });
        }

        //fungsi pembulatan
        function round(value, decimals) {
            return Number(Math.round(value+'e'+decimals)+'e-'+decimals);
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
$bpdf = base_url('public/images/button/').'pdf.png';
?>
<br />
<div id="p" class="easyui-panel" title="Entry Invoice Penjualan" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <form id="frmMaster">
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($invoice->CabangCode != null ? $invoice->CabangCode : $userCabCode); ?>" disabled/>
                    <input type="hidden" id="CabangId" name="CabangId" value="<?php print($invoice->CabangId == null ? $userCabId : $invoice->CabangId);?>"/>
                </td>
                <td>Tanggal</td>
                <td><input type="text" class="f1 easyui-datebox" id="InvoiceDate" name="InvoiceDate" style="width: 150px" value="<?php print($invoice->FormatInvoiceDate(SQL_DATEONLY));?>" <?php print($itemsCount == 0 ? 'required' : 'readonly');?> data-options="formatter:myformatter,parser:myparser"/></td>
                <td>No. Invoice</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="InvoiceNo" name="InvoiceNo" value="<?php print($invoice->InvoiceNo != null ? $invoice->InvoiceNo : '-'); ?>" readonly/></td>
            </tr>
            <tr>
                <td>Customer</td>
                <td><input class="easyui-combogrid" id="CustomerId" name="CustomerId" style="width: 250px" value="<?php print($invoice->CustomerId);?>" <?php print($itemsCount == 0 ? 'required' : 'readonly');?>/></td>
                <td>Salesman</td>
                <td><select class="easyui-combobox" id="SalesId" name="SalesId" style="width: 150px" required>
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
                </td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td><b><input type="text" class="f1 easyui-textbox" id="InvoiceDescs" name="InvoiceDescs" style="width: 250px" value="<?php print($invoice->InvoiceDescs != null ? $invoice->InvoiceDescs : '-'); ?>" required/></b></td>
                <td>Gudang *</td>
                <td>
                    <?php if ($itemsCount == 0){?>
                    <select class="easyui-combobox" id="GudangId" name="GudangId" style="width: 150px" required>
                    <?php }else{ ?>
                    <input type="hidden" name="GudangId" id="GudangId" value="<?php print($invoice->GudangId);?>"/>
                    <select class="easyui-combobox" id="GudangId1" name="GudangId1" style="width: 150px" disabled>
                    <?php } ?>
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
                <td><select class="easyui-combobox" id="PaymentType" name="PaymentType" style="width: 70px" required>
                        <option value="1" <?php print($invoice->PaymentType == 1 ? 'selected="selected"' : '');?>>Kredit</option>
                        <option value="0" <?php print($invoice->PaymentType == 0 ? 'selected="selected"' : '');?>>Tunai</option>
                    </select>
                    &nbsp
                    <input type="text" class="easyui-textbox" id="CreditTerms" name="CreditTerms" style="width: 40px" value="<?php print($invoice->CreditTerms != null ? $invoice->CreditTerms : 0); ?>" style="text-align: right" required/>
                    hari
                </td>
                <td>Kas/Bank</td>
                <td><select class="easyui-combobox" id="DbAccId" name="DbAccId" style="width: 150px" required>
                        <option value="0"></option>
                        <?php
                        /** @var $coakas KasBank[]*/
                        foreach ($coakas as $kas) {
                            if ($kas->TrxAccId == $invoice->DbAccId) {
                                printf('<option value="%d" selected="selected">%s</option>', $kas->TrxAccId, $kas->BankName);
                            }else{
                                printf('<option value="%d">%s</option>', $kas->TrxAccId, $kas->BankName);
                            }
                        }
                        ?>
                    </select>
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
                <td>
                    <?php
                    if ($acl->CheckUserAccess("ar.invoice", "edit") && $invoice->Id > 0 && $invoice->InvoiceStatus == 1) {
                        printf('<img src="%s" alt="Update Data" title="Update data master" id="bUpdate" style="cursor: pointer;"/>',$bsubmit);
                    }else{
                        print("&nbsp;");
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="10">
                    <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma; widows: 100%;">
                        <tr>
                            <th colspan="13">DETAIL BARANG YANG DIJUAL</th>
                            <th rowspan="2">Action</th>
                        </tr>
                        <tr>
                            <th>No.</th>
                            <th>S/O</th>
                            <th>Kode</th>
                            <th nowrap="nowrap">Nama Barang</th>
                            <th>L</th>
                            <th>S</th>
                            <th>Harga</th>
                            <th>Bonus</th>
                            <th>Jumlah</th>
                            <th>Diskon</th>
                            <th>PPN</th>
                            <th>PPh</th>
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
                            printf('<td>%s</td>', $detail->ExSoNo);
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
                            printf('<td class="right">%s</td>', number_format($detail->PpnAmount,0));
                            printf('<td class="right">%s</td>', number_format($detail->PphAmount,0));
                            printf('<td class="right">%s</td>', number_format($detail->SubTotal + $detail->PpnAmount + $detail->PphAmount - $detail->DiscAmount,0));
                            print("<td class='center' nowrap='nowrap'>");
                            $dta = addslashes($detail->Id.'|'.$detail->ItemCode.'|'.str_replace('"',' in',$detail->ItemDescs));
                            $dtx = $detail->Id.'|'.$detail->ItemCode.'|'.str_replace('"',' in',$detail->ItemDescs).'|'.$detail->ItemId.'|'.$detail->SalesQty.'|'.$detail->ReturnQty.'|'.$detail->SatKecil.'|'.$detail->Price.'|'.$detail->DiscFormula.'|'.$detail->IsFree.'|'.$detail->ExSoId.'|'.$detail->PphPct.'|'.$detail->PpnPct.'|'.$detail->PpnAmount.'|'.$detail->PphAmount.'|'.$detail->Lqty.'|'.$detail->Sqty.'|'.$detail->IsiSatKecil.'|'.$detail->SatBesar;
                            //printf('&nbsp<img src="%s" alt="Edit barang" title="Edit barang" style="cursor: pointer" onclick="return feditdetail(%s);"/>',$bedit,"'".$dtx."'");
                            printf('&nbsp<img src="%s" alt="Hapus barang" title="Hapus barang" style="cursor: pointer" onclick="return fdeldetail(%s);"/>',$bclose,"'".$dta."'");
                            print("</td>");
                            print("</tr>");
                            $total += $detail->SubTotal;
                        }
                        ?>
                        <tr class="bold">
                            <td colspan="8" align="right">Total Rp. </td>
                            <td class="right"><?php print($invoice->BaseAmount != null ? number_format($invoice->BaseAmount,0) : 0); ?></td>
                            <td class="right"><?php print($invoice->DiscAmount != null ? number_format($invoice->DiscAmount,0) : 0); ?></td>
                            <td class="right"><?php print($invoice->PpnAmount != null ? number_format($invoice->PpnAmount,0) : 0); ?></td>
                            <td class="right"><?php print($invoice->PphAmount != null ? number_format($invoice->PphAmount,0) : 0); ?></td>
                            <td class="right"><?php print($invoice->TotalAmount != null ? number_format($invoice->TotalAmount,0) : 0); ?></td>
                            <td class='center'><?php printf('<img src="%s" alt="Tambah Barang" title="Tambah barang" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                        </tr>
                        <tr>
                            <td colspan="15" nowrap="nowrap" class="right"><?php
                                if ($acl->CheckUserAccess("ar.invoice", "add")) {
                                    printf('<img src="%s" alt="Invoice Baru" title="Buat invoice baru" id="bTambah" style="cursor: pointer;"/>', $baddnew);
                                }
                                ?>
                                &nbsp;
                                <?php
                                if ($acl->CheckUserAccess("ar.invoice", "delete")) {
                                    printf('<img src="%s" alt="Hapus Invoice" title="Proses hapus invoice" id="bHapus" style="cursor: pointer;"/>',$bdelete);
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
    </form>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- Form Add Invoice Detail -->
<div id="dlg" class="easyui-dialog" style="width:750px;height:300px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <form id="frmDetail" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding" style="font-size: 12px;font-family: tahoma">
            <tr>
                <td class="right bold">Ex S/O No:</td>
                <td colspan="7"><input class="easyui-combogrid" id="dExSoNo" name="dExSoNo" style="width:600px"/></td>
            </tr>
            <tr>
                <td class="right bold">Search :</td>
                <td colspan="7"><input id="aItemSearch" name="aItemSearch" style="width: 600px"/></td>
            </tr>
            <tr>
                <td class="right bold">Kode Barang :</td>
                <td colspan="5"><input type="text" class="easyui-textbox bold" id="aItemCode" name="aItemCode" size="12" value="" readonly/>
                    <input type="hidden" id="aItemId" name="aItemId" value="0"/>
                    <input type="hidden" id="aId" name="aId" value="0"/>
                    <input type="hidden" id="aExSoId" name="aExSoId" value=""/>
                    <input type="hidden" id="aQtyStock" name="aQtyStock" value="0"/>
                    <input type="hidden" id="aIsiSatKecil" name="aIsiSatKecil" value="0"/>
                    <input type="text" class="easyui-textbox bold" id="aItemDescs" name="aItemDescs" size="40" value="" readonly/>
                    &nbsp;
                    <b>Stock :</b>
                    &nbsp;
                    <input type="text" class="easyui-numberbox bold right" id="xQtyStock" name="xQtyStock" size="4" value="" readonly/>
                    <input type="text" class="easyui-textbox bold" id="aSatuan" name="aSatuan" size="3" value="" readonly/>
                </td>
            </tr>
            <tr>
                <td class="right bold">L - QTY :</td>
                <td><input class="right bold" type="text" id="lQty" name="lQty" size="5" value="0" required/>&nbsp;<span id="lUom"></span></td>
                <td class="right bold">+ S - QTY :</td>
                <td><input class="right bold" type="text" id="sQty" name="sQty" size="5" value="0" required/>&nbsp;<span id="sUom"></span></td>
                <td class="right bold">= QTY :</td>
                <td><input class="right bold" type="text" id="aQty" name="aQty" size="5" value="0" readonly/>&nbsp;<span id="qUom"></span></td>
            </tr>
            <tr>
                <td class="right bold">Bonus/Free? :</td>
                <td><select name="aIsFree" id="aIsFree" style="width:100px">
                        <option value="0">0 - Tidak</option>
                        <option value="1">1 - Ya</option>
                    </select>
                </td>
                <td class="right bold">Harga :</td>
                <td><input class="right bold" type="text" id="aPrice" name="aPrice" size="8" value="0" readonly/><span id="pUom"></span></td>
                <td class="right bold">Jumlah :</td>
                <td><input class="right bold" type="text" id="aSubTotal" name="aSubTotal" size="15" value="0" readonly/></td>
            </tr>
            <tr>
                <td class="right bold">Diskon :</td>
                <td><input class="right bold" type="text" id="aDiscFormula" name="aDiscFormula" size="12" value="0"/>%</td>
                <td class="right bold">Nilai Diskon :</td>
                <td><input class="right bold" type="text" id="aDiscAmount" name="aDiscAmount" size="12" value="0"/></td>
                <td class="right bold">D P P :</td>
                <td><input class="right bold" type="text" id="aDpp" name="aDpp" size="15" value="0" readonly/></td>
            </tr>
            <tr>
                <td class="right bold">P P N :</td>
                <td><input class="right bold" type="text" name="aPpnPct" id="aPpnPct" value="10" size="12"/>%</td>
                <td class="right bold">Nilai PPN :</td>
                <td><input class="right bold" type="text" id="aPpnAmount" name="aPpnAmount" size="12" value="0"/></td>
            </tr>
            <tr>
                <td class="right bold">P P h :</td>
                <td><input class="right bold" type="text" name="aPphPct" id="aPphPct" value="0" size="12"/>%</td>
                <td class="right bold">Nilai PPh :</td>
                <td><input class="right bold" type="text" id="aPphAmount" name="aPphAmount" size="12" value="0"/></td>
                <td class="right bold">Total :</td>
                <td><input class="right bold" type="text" id="aTotal" name="aTotal" size="15" value="0" readonly/></td>
            </tr>
        </table>
    </form>

</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveDetail()" style="width:90px">Simpan</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Batal</a>
</div>
<!-- </body> -->
</html>
