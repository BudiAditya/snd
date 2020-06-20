<!DOCTYPE HTML>
<html>
<?php
/** @var $purchase Purchase */
?>
<head>
    <title>SND System - Edit Pembelian/Penerimaan Barang</title>
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
            bpurchase-bottom:1px solid #ccc;
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
        var purchaseId = "<?php print($purchase->Id == null ? 0 : $purchase->Id) ?>";
        var supId = "<?php print($purchase->SupplierId == null ? 0 : $purchase->SupplierId) ?>";
        var gudangId = "<?php print($purchase->GudangId == null ? 0 : $purchase->GudangId) ?>";
        var itemCount = "<?php print($itemsCount == null ? 0 : $itemsCount) ?>";
        itemCount = Number(itemCount);
        purchaseId = Number(purchaseId);
        supId = Number(supId);
        gudangId = Number(gudangId);
        var isiQty = 0;
        $( function() {
            $('#SupplierId').combogrid({
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
                    var spi = row.id;
                    console.log(spi);
                    var term = row.term;
                    console.log(term);
                    if (term > 0){
                        $("#PaymentType").val(1);
                        $("#CreditTerms").val(term);
                    }
                    supId = spi;
                    var urz = "<?php print($helper->site_url('ap.purchase/getjson_polists/'));?>"+supId;
                    $('#dExPoNo').combogrid('grid').datagrid('load',urz);
                    urz = "<?php print($helper->site_url("ap.purchase/getitems_json/"));?>"+supId;
                    $('#aItemSearch').combogrid('grid').datagrid('load',urz);
                }
            });

            //combogrid po sesuai supplier yg dipilih
            $('#dExPoNo').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url('ap.purchase/getjson_polists/'));?>"+supId,
                idField:'id',
                textField:'po_no',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'po_no',title:'P/O No',width:55},
                    {field:'po_date',title:'Tanggal',width:40},
                    {field:'po_descs',title:'Keterangan',width:100},
                    {field:'nilai',title:'Nilai Order',width:50,align:'right'}
                ]],
                onSelect: function(index,row){
                    var idi = row.id;
                    console.log(idi);
                    var pon = row.po_no;
                    console.log(pon);
                    $("#aExPoId").val(idi);
                    if (pon != '') {
                        var urz = "<?php print($helper->site_url('ap.purchase/getjson_poitems/'));?>"+idi;
                        $('#aItemSearch').combogrid('grid').datagrid('load',urz);
                    }
                }
            });

            $('#aItemSearch').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("ap.purchase/getitems_json/"));?>"+supId,
                idField:'item_id',
                textField:'item_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'item_code',title:'Kode',width:50},
                    {field:'item_name',title:'Nama Barang',width:150},
                    {field:'s_uom_code',title:'Satuan',width:40},
                    {field:'qty_order',title:'Order',width:40,align:'right'},
                    {field:'hrg_beli',title:'Harga',width:40,align:'right'}
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
                    var harga = row.hrg_beli;
                    console.log(harga);
                    var qty = row.qty_order;
                    console.log(qty);
                    var isi = Number(row.s_uom_qty);
                    console.log(isi);
                    $('#aItemId').val(bid);
                    $('#aItemCode').textbox('setValue',bkode);
                    $('#aItemDescs').textbox('setValue',bnama);
                    $('#aSatuan').textbox('setValue',satuan);
                    $('#aPrice').val(harga);
                    $('#aDiscFormula').val(0);
                    $('#aDiscAmount').val(0);
                    $('#aQty').val(qty);
                    $('#aPpnPct').val(10);
                    $('#aPpnAmount').val(0);
                    $('#aPphPct').val(0);
                    $('#aPphAmount').val(0);
                    $('#lUom').text(row.l_uom_code+' x '+isi);
                    $('#sUom').text(row.s_uom_code);
                    $('#qUom').text(row.s_uom_code);
                    $('#pUom').text('/'+row.l_uom_code);
                    isiQty = isi;
                    hitDetail();
                }
            });


            $("#bAdDetail").click(function(e){
                if (validMaster()) {
                    $('#aItemId').val(0);
                    $('#aItemCode').textbox('setValue', '');
                    $('#aItemDescs').textbox('setValue', '');
                    $('#aSatuan').textbox('setValue', '');
                    $('#aPrice').numberbox('setValue',0);
                    $('#aQty').numberbox('setValue',0);
                    $('#aDiscFormula').textbox('setValue',0);
                    $('#aDiscAmount').numberbox('setValue',0);
                    $('#aIsFree').combobox('setValue',0);
                    $('#aSubTotal').numberbox('setValue',0);
                    $('#aPpnPct').numberbox('setValue', 10);
                    $('#aPpnAmount').numberbox('setValue',0);
                    $('#aPphPct').numberbox('setValue',0);
                    $('#aPphAmount').numberbox('setValue',0);
                    newItem();
                }
            });

            $('#lQty').numberbox({
                onChange: function(nvalue){
                    hitQty();
                    hitDetail();
                }
            });

            $('#sQty').numberbox({
                onChange: function(nvalue){
                    hitQty();
                    hitDetail();
                }
            });

            $('#aPrice').numberbox({
                onChange: function(nvalue){
                    hitDetail();
                }
            });

            $('#aPpnPct').numberbox({
                onChange: function(nvalue){
                    hitDetail();
                }
            });

            $("#aPphPct").numberbox({
                onChange: function(nvalue){
                    hitDetail();
                }
            });

            $("#aDiscFormula").textbox({
                onChange: function(nvalue){
                    $('#aDiscAmount').numberbox('setValue',0);
                    hitDetail();
                }
            });

            $("#aDiscAmount").numberbox({
                onChange: function(nvalue){
                    //if ($('#aDiscAmount').numberbox('getValue') > 0) {
                    //    $('#aDiscFormula').textbox('setValue',0);
                    //}
                    hitDetail();
                }
            });

            $("#aIsFree").combobox({
                onChange: function(nvalue){
                    if (nvalue == 1){
                        $('#aDiscFormula').textbox('setValue',0);
                        $('#aDiscAmount').numberbox('setValue',0);
                    }
                    hitDetail();
                }
            });

            $("#bUpdate").click(function () {
                if (validMaster()) {
                    var urx = "<?php print($helper->site_url("ap.purchase/proses_master/")); ?>"+purchaseId;
                    //alert(urx);
                    $('#frmMaster').form('submit', {
                        url: urx,
                        onSubmit: function () {
                            return $(this).form('validate');
                        },
                        success: function (result) {
                            var dtx = result.split('|');
                            if (dtx[0] == 'OK') {
                                purchaseId = dtx[2];
                                location.href = "<?php print($helper->site_url("ap.purchase/edit/"));?>" + purchaseId;
                            } else {
                                $.messager.alert('Warning!', result + ' Update data master gagal!');
                            }
                        }
                    });
                }
            });


            $("#bTambah").click(function(){
                $.messager.confirm('Confirm','Buat Data Pembelian baru?',function(r) {
                    if (r) {
                        location.href="<?php print($helper->site_url("ap.purchase/add")); ?>";
                    }
                });
            });

            $("#bHapus").click(function(){
                $.messager.confirm('Confirm','Anda yakin akan membatalkan Data Pembelian ini?',function(r) {
                    if (r) {
                        location.href="<?php print($helper->site_url("ap.purchase/void/").$purchase->Id); ?>";
                    }
                });
            });

            $("#bCetakPdf").click(function(){
                $.messager.confirm('Confirm','Cetak Bukti Pembelian ini?',function(r) {
                    if (r) {
                        window.open("<?php print($helper->site_url("ap.purchase/grn_print/grn/?&id[]=").$purchase->Id); ?>");
                    }
                });
            });

            $("#bKembali").click(function(){
                location.href="<?php print($helper->site_url("ap.purchase")); ?>";
            });
        });

        function validMaster() {
            if (itemCount == 0) {
                gudangId = Number($("#GudangId").combobox('getValue'));
            }
            var pym = Number($("#PaymentType").combobox('getValue'));
            var crt = Number($("#CreditTerms").numberbox('getValue'));

            if (supId == 0){
                $.messager.alert('Warning','Supplier belum dipilih!');
                return false;
            }

            if (gudangId == 0){
                $.messager.alert('Warning','Gudang belum dipilih!');
                return false;
            }

            if (pym == 1 && crt == 0){
                $.messager.alert('Warning','Credit Terms belum diisi!');
                return false;
            }

            return true;
        }

        function hitQty() {
            var lQTy = Number($("#lQty").numberbox('getValue'));
            var sQTy = Number($("#sQty").numberbox('getValue'));
            var rQty = Number((lQTy * isiQty)) + sQTy;
            $("#aQty").numberbox('setValue',rQty);
        }

        function hitDetail(){
            var isFree = Number($("#aIsFree").combobox('getValue'));
            var tpp = Number($('#aPpnPct').numberbox('getValue',0));
            var tph = Number($('#aPphPct').numberbox('getValue',0));
            var txa = 0;
            var tha = 0;
            var isi = isiQty;
            var hrg = Number($("#aPrice").numberbox('getValue'));
            var lqt = Number($("#lQty").numberbox('getValue'));
            var sqt = Number($("#sQty").numberbox('getValue'));
            var subTotal = 0;
            var dpp = 0;
            var dfm = $("#aDiscFormula").textbox('getValue');
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
                if (dfm != null && dfm != '0' && dfm != '') {
                    discAmount = hitDiscFormula(subTotal, dfm);
                }else{
                    discAmount = Number($('#aDiscAmount').numberbox('getValue'));
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
            $('#aDiscAmount').numberbox('setValue',discAmount);
            $('#aPpnAmount').numberbox('setValue',txa);
            $('#aPphAmount').numberbox('setValue',tha);
            $('#aDpp').numberbox('setValue',dpp);
            $('#aSubTotal').numberbox('setValue',subTotal);
            $('#aTotal').numberbox('setValue',totalDetail);
        }

        function fdeldetail(dta){
            var dtz = dta.replace(/\"/g,"\\\"")
            var dtx = dtz.split('|');
            var id = dtx[0];
            var kode = dtx[1];
            var barang = dtx[2];
            var urx = '<?php print($helper->site_url("ap.purchase/delete_detail/"));?>'+id;
            $.messager.confirm('Confirm','Hapus Data Detail Barang \nKode: '+kode+ '\nNama: '+barang+' ?',function(r) {
                if (r) {
                    $.get(urx, function (data) {
                        $.messager.alert('Warning',data);
                        location.reload();
                    });
                }
            });
        }

        function newItem(){
            $('#dlg').dialog('open').dialog('setTitle','Tambah Detail Barang yang diterima');
            //$('#fm').form('clear');
            var urz = "<?php print($helper->site_url('ap.purchase/getjson_polists/'));?>"+supId;
            $('#dExPoNo').combogrid('grid').datagrid('load',urz);
            urz = "<?php print($helper->site_url("ap.purchase/getitems_json/"));?>"+supId;
            $('#aItemSearch').combogrid('grid').datagrid('load',urz);
            $('#aItemCode').focus();
        }

        function saveDetail(){
            if (validMaster()) {
                var aitd = Number($('#aItemId').val());
                var aqty = Number($('#aQty').numberbox('getValue'));
                var astt = Number($('#aSubTotal').numberbox('getValue'));
                var aisf = Number($('#aIsFree').combobox('getValue'));
                if (purchaseId > 0) {
                    var url = "<?php print($helper->site_url("ap.purchase/add_detail/"));?>" + purchaseId;
                    if ((aitd > 0 && aqty > 0 && astt > 0) || (aitd > 0 && aqty > 0 && aisf > 0)) {
                        $('#fm').form('submit', {
                            url: url,
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
                                    location.href = "<?php print($helper->site_url("ap.purchase/edit/"));?>" + purchaseId;
                                }
                            }
                        });
                    } else {
                        $.messager.alert('Warning', 'Data tidak valid!');
                    }
                }else{
                    var urx = "<?php print($helper->site_url("ap.purchase/proses_master/0")); ?>";
                    $('#frmMaster').form('submit', {
                        url: urx,
                        onSubmit: function () {
                            return $(this).form('validate');
                        },
                        success: function (result) {
                            var dtx = result.split('|');
                            if (dtx[0] == 'OK') {
                                purchaseId = dtx[2];
                                var url = "<?php print($helper->site_url("ap.purchase/add_detail/"));?>" + purchaseId;
                                if ((aitd > 0 && aqty > 0 && astt > 0) || (aitd > 0 && aqty > 0 && aisf > 0)) {
                                    $('#fm').form('submit', {
                                        url: url,
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
                                                location.href = "<?php print($helper->site_url("ap.purchase/edit/"));?>" + purchaseId;
                                            }
                                        }
                                    });
                                } else {
                                    $.messager.alert('Warning', 'Data tidak valid!');
                                }
                            } else {
                                $.messager.alert('Warning!', result + ' Insert data master gagal!');
                            }
                        }
                    });
                }
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
<div id="p" class="easyui-panel" title="Edit Pembelian/Penerimaan Barang" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <form id="frmMaster" action="<?php print($helper->site_url("ap.purchase/add/".$purchase->Id)); ?>" method="post">
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang</td>
                <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($purchase->CabangCode != null ? $purchase->CabangCode : $userCabCode); ?>" disabled/>
                    <input type="hidden" id="CabangId" name="CabangId" value="<?php print($purchase->CabangId == null ? $userCabId : $purchase->CabangId);?>"/>
                </td>
                <td>Tanggal</td>
                <td><input type="text" class="easyui-datebox" id="GrnDate" name="GrnDate" style="width: 150px" value="<?php print($purchase->FormatGrnDate(SQL_DATEONLY));?>" <?php print($itemsCount == 0 ? 'required' : 'readonly');?> data-options="formatter:myformatter,parser:myparser"/></td>
                <td>Diterima</td>
                <td><input type="text" class="easyui-datebox" id="ReceiptDate" name="ReceiptDate" style="width: 105px" value="<?php print($purchase->FormatReceiptDate(SQL_DATEONLY));?>" <?php print($itemsCount == 0 ? 'required' : 'readonly');?> data-options="formatter:myformatter,parser:myparser"/></td>
                <td>No. GRN</td>
                <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 150px" id="GrnNo" name="GrnNo" value="<?php print($purchase->GrnNo != null ? $purchase->GrnNo : '-'); ?>" readonly/></td>
            </tr>
            <tr>
                <td>Supplier</td>
                <td><input class="easyui-combogrid" id="SupplierId" name="SupplierId" style="width: 250px" value="<?php print($purchase->SupplierId);?>" required/></td>
                <td>Salesman</td>
                <td><b><input type="text" class="easyui-textbox" id="SalesName" name="SalesName" style="width: 150px" maxlength="50" value="<?php print($purchase->SalesName != null ? $purchase->SalesName : '-'); ?>"/></b></td>
                <td>Status</td>
                <td><select class="easyui-combobox" id="GrnStatus1" name="GrnStatus1" style="width: 105px" disabled>
                        <option value="0" <?php print($purchase->GrnStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                        <option value="1" <?php print($purchase->GrnStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                        <option value="2" <?php print($purchase->GrnStatus == 2 ? 'selected="selected"' : '');?>>2 - Approved</option>
                        <option value="3" <?php print($purchase->GrnStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                    </select>
                    <input type="hidden" id="GrnStatus" name="GrnStatus" value="<?php print($purchase->GrnStatus);?>"/>
                </td>
                <td>Ex PO No.</td>
                <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 150px" id="ExPoNo" name="ExPoNo" value="<?php print($purchase->ExPoNo != null ? $purchase->ExPoNo : '-'); ?>"/></td>
            </tr>
            <tr>
                <td>Expedisi</td>
                <td><select class="easyui-combobox" id="ExpeditionId" name="ExpeditionId" style="width: 250px">
                        <option value="0"></option>
                        <?php
                        /** @var $expedition Expedition[]*/
                        foreach ($expedition as $expedisi) {
                            if ($expedisi->Id == $purchase->ExpeditionId) {
                                printf('<option value="%d" selected="selected">%s</option>', $expedisi->Id, $expedisi->ExpName);
                            }else{
                                printf('<option value="%d">%s</option>', $expedisi->Id, $expedisi->ExpName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Gudang</td>
                <td>
                    <?php if ($itemsCount == 0){?>
                    <select class="easyui-combobox" id="GudangId" name="GudangId" style="width: 150px" required>
                        <?php }else{ ?>
                        <input type="hidden" name="GudangId" id="GudangId" value="<?php print($purchase->GudangId);?>"/>
                        <select class="easyui-combobox" id="GudangId1" name="GudangId1" style="width: 150px" disabled>
                            <?php } ?>
                            <option value=""></option>
                            <?php
                            /** @var $gudang Warehouse[]*/
                            foreach ($gudangs as $gudang) {
                                if ($gudang->Id == $purchase->GudangId) {
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
                        <option value="1" <?php print($purchase->PaymentType == 1 ? 'selected="selected"' : '');?>>Kredit</option>
                        <option value="0" <?php print($purchase->PaymentType == 0 ? 'selected="selected"' : '');?>>Tunai</option>
                    </select>
                    <input type="text" class="easyui-numberbox" id="CreditTerms" name="CreditTerms" style="width: 30px" value="<?php print($purchase->CreditTerms != null ? $purchase->CreditTerms : 0); ?>" style="text-align: right" required/>
                    hr
                </td>
                <td>Tgl JTP</td>
                <td><input type="text" class="easyui-datebox" id="JtpDate" name="JtpDate" style="width: 150px" value="<?php print($purchase->FormatJtpDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser"/></td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td><b><input type="text" class="easyui-textbox" id="GrnDescs" name="GrnDescs" style="width: 250px" value="<?php print($purchase->GrnDescs != null ? $purchase->GrnDescs : '-'); ?>" required/></b></td>
                <td>No. Invoice</td>
                <td><input type="text" class="easyui-textbox" id="SupInvNo" name="SupInvNo" style="width: 150px" maxlength="50" value="<?php print($purchase->SupInvNo != null ? $purchase->SupInvNo : '-'); ?>"/></td>
                <td>Tgl Invoice</td>
                <td><input type="text" class="easyui-datebox" id="SupInvDate" name="SupInvDate" style="width: 105px" value="<?php print($purchase->FormatSupInvDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser"/></td>
                <td>NSF Pajak</td>
                <td><input type="text" class="easyui-textbox" id="NsfPajak" name="NsfPajak" style="width: 150px" maxlength="50" value="<?php print($purchase->NsfPajak != null ? $purchase->NsfPajak : '-'); ?>"/></td>
                <td>
                    <?php
                    if ($acl->CheckUserAccess("ap.purchase", "edit") && $purchase->Id > 0 && $purchase->GrnStatus == 1) {
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
                    <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma; width: 100%;">
                        <tr>
                            <th colspan="13">DETAIL BARANG YANG DIBELI/DITERIMA</th>
                            <th rowspan="2">Action</th>
                        </tr>
                        <tr>
                            <th>No.</th>
                            <th>P/O</th>
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
                        foreach($purchase->Details as $idx => $detail) {
                            $counter++;
                            print("<tr>");
                            printf('<td class="right">%s.</td>', $counter);
                            printf('<td>%s</td>', $detail->ExPoNo);
                            printf('<td>%s</td>', $detail->ItemCode);
                            printf('<td nowrap="nowrap">%s</td>', $detail->ItemDescs);
                            printf('<td class="right">%s</td>', number_format($detail->Lqty,0));
                            printf('<td class="right">%s</td>', number_format($detail->Sqty,0));
                            printf('<td class="right">%s</td>', number_format($detail->Price,0));
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
                            $dtx = $detail->Id.'|'.$detail->ItemCode.'|'.str_replace('"',' in',$detail->ItemDescs).'|'.$detail->ItemId.'|'.$detail->PurchaseQty.'|'.$detail->ReturnQty.'|'.$detail->SatKecil.'|'.$detail->Price.'|'.$detail->DiscFormula.'|'.$detail->IsFree.'|'.$detail->ExPoId.'|'.$detail->PphPct.'|'.$detail->PpnPct.'|'.$detail->PpnAmount.'|'.$detail->PphAmount.'|'.$detail->ByAngkut;
                            if ($acl->CheckUserAccess("ap.purchase", "delete")) {
                                printf('&nbsp<img src="%s" alt="Hapus barang" title="Hapus barang" style="cursor: pointer" onclick="return fdeldetail(%s);"/>', $bclose, "'" . $dta . "'");
                            }else{
                                print('&nbsp;');
                            }
                            print("</td>");
                            print("</tr>");
                            $total += $detail->SubTotal;
                        }
                        ?>
                        <tr class="bold">
                            <td colspan="8" align="right">Total Rp. </td>
                            <td class="right"><?php print($purchase->BaseAmount != null ? number_format($purchase->BaseAmount,0) : 0); ?></td>
                            <td class="right"><?php print($purchase->DiscAmount != null ? number_format($purchase->DiscAmount,0) : 0); ?></td>
                            <td class="right"><?php print($purchase->PpnAmount != null ? number_format($purchase->PpnAmount,0) : 0); ?></td>
                            <td class="right"><?php print($purchase->PphAmount != null ? number_format($purchase->PphAmount,0) : 0); ?></td>
                            <td class="right"><?php print($purchase->TotalAmount != null ? number_format($purchase->TotalAmount,0) : 0); ?></td>
                            <td class='center'><?php printf('<img src="%s" alt="Tambah Barang" title="Tambah barang" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                        </tr>
                        <tr>
                            <td colspan="14" nowrap="nowrap" class="right"><?php
                                if ($acl->CheckUserAccess("ap.purchase", "add")) {
                                    printf('<img src="%s" alt="GRN Baru" title="Buat invoice baru" id="bTambah" style="cursor: pointer;"/>', $baddnew);
                                }
                                ?>
                                &nbsp;
                                <?php
                                if ($acl->CheckUserAccess("ap.purchase", "delete")) {
                                    printf('<img src="%s" alt="Hapus Grn" title="Proses hapus invoice" id="bHapus" style="cursor: pointer;"/>',$bdelete);
                                }
                                ?>
                                &nbsp;
                                <?php
                                if ($acl->CheckUserAccess("ap.purchase", "print")) {
                                    printf('<img src="%s" id="bCetakPdf" alt="Cetak Bukti Pembelian" title="Proses cetak bukti pembelian" style="cursor: pointer;"/>',$bcetak);
                                }
                                ?>
                                &nbsp;
                                <?php
                                printf('<img src="%s" id="bKembali" alt="Daftar Grn" title="Kembali ke daftar invoice" style="cursor: pointer;"/>',$bkembali);
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
<!-- Form Add Grn Detail -->
<div id="dlg" class="easyui-dialog" style="width:750px;height:310px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <form id="fm" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding" style="font-size: 12px;font-family: tahoma">
            <tr>
                <td class="right bold">Ex P/O No :</td>
                <td colspan="7"><input class="easyui-combogrid" id="dExPoNo" name="dExPoNo" style="width:600px"/></td>
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
                    <input type="hidden" id="aExPoId" name="aExPoId" value=""/>
                    <input type="hidden" id="aQtyStock" name="aQtyStock" value="0"/>
                    <input type="text" class="easyui-textbox bold" id="aItemDescs" name="aItemDescs" size="50" value="" readonly/>
                    <input type="text" class="easyui-textbox bold" id="aSatuan" name="aSatuan" size="8" value="" readonly/>
                </td>
            </tr>
            <tr>
                <td class="right bold">L - QTY :</td>
                <td><input class="easyui-numberbox bold" type="text" id="lQty" name="lQty" size="5" value="0" required/>&nbsp;<span id="lUom"></span></td>
                <td class="right bold">+ S - QTY :</td>
                <td><input class="easyui-numberbox bold" type="text" id="sQty" name="sQty" size="5" value="0" required/>&nbsp;<span id="sUom"></span></td>
                <td class="right bold">= QTY :</td>
                <td><input class="easyui-numberbox bold" type="text" id="aQty" name="aQty" size="5" value="0" readonly/>&nbsp;<span id="qUom"></span></td>
            </tr>
            <tr>
                <td class="right bold">Bonus/Free? :</td>
                <td><select class="easyui-combobox" name="aIsFree" id="aIsFree" style="width:110px">
                        <option value="0">0 - Tidak</option>
                        <option value="1">1 - Ya</option>
                    </select>
                </td>
                <td class="right bold">Harga Beli :</td>
                <td><input class="easyui-numberbox bold" type="text" id="aPrice" name="aPrice"  style="width:120px" value="0"/><span id="pUom"></span></td>
                <td class="right bold">Jumlah :</td>
                <td><input class="easyui-numberbox bold" type="text" id="aSubTotal" name="aSubTotal"  style="width:120px" value="0" readonly/></td>
            </tr>
            <tr>
                <td class="right bold">Diskon :</td>
                <td><input class="easyui-textbox bold" type="text" id="aDiscFormula" name="aDiscFormula" style="width:110px" value="0"/>&nbsp;%</td>
                <td class="right bold">Nilai Diskon :</td>
                <td><input class="easyui-numberbox bold" type="text" id="aDiscAmount" name="aDiscAmount" style="width:120px" value="0"/></td>
                <td class="right bold">D P P :</td>
                <td><input class="easyui-numberbox bold" type="text" id="aDpp" name="aDpp" style="width:120px" value="0" readonly/></td>
            </tr>
            <tr>
                <td class="right bold">Tarif PPN :</td>
                <td><input class="easyui-numberbox bold" type="text" name="aPpnPct" id="aPpnPct" value="10" style="width:110px"/>%</td>
                <td class="right bold">Nilai PPN :</td>
                <td><input class="easyui-numberbox bold" type="text" id="aPpnAmount" name="aPpnAmount" style="width:120px" value="0"/></td>
            </tr>
            <tr>
                <td class="right bold">Tarif PPh :</td>
                <td><input class="easyui-numberbox bold" type="text" name="aPphPct" id="aPphPct" value="0" style="width:110px" data-options="min:0,precision:2"/>%</td>
                <td class="right bold">Nilai PPh :</td>
                <td><input class="easyui-numberbox bold" type="text" id="aPphAmount" name="aPphAmount" style="width:120px" value="0"/></td>
                <td class="right bold">Total :</td>
                <td><input class="easyui-numberbox bold" type="text" id="aTotal" name="aTotal"  style="width:120px" value="0" readonly/></td>
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
