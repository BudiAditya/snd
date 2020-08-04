<!DOCTYPE HTML>
<html>
<?php
/** @var $invoice Invoice */
?>
<head>
    <title>SND System - Edit Invoice Penjualan</title>
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
        var invoiceId  = "<?php print($invoice->Id == null ? 0 : $invoice->Id);?>";
        var detailCount = "<?=$itemsCount;?>";
        var custId = "<?=$invoice->CustomerId;?>";
        var salesId = "<?=$invoice->SalesId;?>";
        var gudangId = "<?=$invoice->GudangId;?>";
        var areaId = "<?=$invoice->AreaId;?>";
        var disPrev = "<?=$discPrev;?>";
        var aDiscPrevileges = disPrev.split('|');
        var msgText = null;
        var dMode = null;
        var detailId = 0;
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
                        $("#CreditTerms").numberbox("setValue",term);
                    }else{
                        $("#PaymentType").combobox("setValue",0);
                        $("#CreditTerms").numberbox("setValue",0);
                    }
                }
            });

            $('#dExOrder').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("ar.invoice/getjson_orderitems/"));?>"+custId+'/'+salesId,
                idField:'item_id',
                textField:'item_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'item_name',title:'Nama Barang',width:150},
                    {field:'item_code',title:'Kode',width:50},
                    {field:'s_uom_code',title:'Satuan',width:35},
                    {field:'qty_order',title:'Order',width:35,align:'right'},
                    {field:'hrg_jual',title:'Harga',width:50,align:'right'}
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
                    var qty = Number(row.qty_order);
                    console.log(qty);
                    var isi = Number(row.s_uom_qty);
                    console.log(isi);
                    var qst = 0;
                    var dtz = 0;
                    var adt = new Array();
                    $('#aItemId').val(bid);
                    $('#aExSoId').val(row.id);
                    $('#aItemCode').textbox('setValue',bkode);
                    $('#aItemDescs').textbox('setValue',bnama);
                    $('#aSatuan').textbox('setValue',satuan);
                    $('#aPrice').numberbox('setValue',harga);
                    $('#aDiscFormula').textbox('setValue',0);
                    $('#aDiscAmount').numberbox('setValue',0);
                    if (isi > 0 && qty >= isi) {
                        dtz = round(qty/isi,2);
                        dtz = dtz.toString();
                        adt = dtz.split('.');
                        $("#lQty").numberbox('setValue',adt[0]);
                        $("#sQty").numberbox('setValue',qty - (Number(adt[0]) * isi));
                    }else{
                        $("#lQty").numberbox('setValue',0);
                        $("#sQty").numberbox('setValue',qty);
                    }
                    $('#aQty').numberbox('setValue',qty);
                    $('#aPpnPct').numberbox('setValue',10);
                    $('#aPpnAmount').numberbox('setValue',0);
                    $('#aIsiSatKecil').val(row.s_uom_qty);
                    $('#lUom').text(row.l_uom_code+' x '+isi);
                    $('#sUom').text(row.s_uom_code);
                    $('#qUom').text(row.s_uom_code);
                    $('#pUom').text('/'+row.l_uom_code);
                    //check stock
                    //gudangId = $('#GudangId').combobox('getValue');
                    $.get("<?php print($helper->site_url("ar.invoice/checkStock/"));?>"+gudangId+'/'+bid, function(data){
                        qst = Number(data);
                        $('#aQtyStock').val(qst);
                        $('#xQtyStock').numberbox('setValue',qst);
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

            $('#dExStock').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("ar.invoice/getjson_stockitems/"));?>"+gudangId,
                idField:'item_id',
                textField:'item_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'item_name',title:'Nama Barang',width:150},
                    {field:'item_code',title:'Kode',width:50},
                    {field:'s_uom_code',title:'Satuan',width:35},
                    {field:'qty_stock',title:'Stock',width:35,align:'right'},
                    {field:'hrg_jual',title:'Harga',width:50,align:'right'}
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
                    var qty = Number(row.qty_stock);
                    console.log(qty);
                    var isi = Number(row.s_uom_qty);
                    console.log(isi);
                    var qst = 0;
                    var dtz = 0;
                    var adt = new Array();
                    $('#aItemId').val(bid);
                    $('#aExSoId').val(0);
                    $('#aItemCode').textbox('setValue',bkode);
                    $('#aItemDescs').textbox('setValue',bnama);
                    $('#aSatuan').textbox('setValue',satuan);
                    $('#aPrice').numberbox('setValue',harga);
                    $('#aDiscFormula').textbox('setValue',0);
                    $('#aDiscAmount').numberbox('setValue',0);
                    $('#aPpnPct').numberbox('setValue',10);
                    $('#aPpnAmount').numberbox('setValue',0);
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
                        $('#xQtyStock').numberbox('setValue',qst);
                        if (qst > 0) {
                            $('#lQty').prop('disabled',false);
                            $('#sQty').prop('disabled',false);
                            fillSalePrice(areaId, bid);
                            hitDetail();
                        }else{
                            $('#lQty').prop('disabled',true);
                            $('#sQty').prop('disabled',true);
                            $.messager.alert('Warning','[ER1] - Maaf Stock produk tidak cukup!');
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
                    $('#aPrice').numberbox('setValue',0);
                    $('#aQty').numberbox('setValue',0);
                    $('#aDiscFormula').textbox('setValue','0');
                    $('#aDiscAmount').numberbox('setValue',0);
                    $('#aIsFree').combobox('setValue',0);
                    $('#aSubTotal').numberbox('setValue',0);
                    $('#aPpnPct').numberbox('setValue',10);
                    $('#aPpnAmount').numberbox('setValue',0);
                    $('#lQty').numberbox('setValue',0);
                    $('#sQty').numberbox('setValue',0);
                    $('#aQtyStock').val(0);
                    $('#xQtyStock').numberbox('setValue', 0);
                    newItem();
                }
            });

            $('#lQty').numberbox({
                onChange: function(rvalue){
                    hitQty();
                    hitDetail();
                }
            });

            $('#sQty').numberbox({
                onChange: function(rvalue){
                    hitQty();
                    hitDetail();
                }
            });

            $('#aPrice').numberbox({
                onChange: function(rvalue){
                    hitDetail();
                }
            });

            $('#aPpnPct').numberbox({
                onChange: function(rvalue){
                    hitDetail();
                }
            });

            $('#aDiscFormula').textbox({
                onChange: function(rvalue){
                    if (validateDiscLevel(rvalue)){
                        hitDetail();
                        //$.messager.alert('Validasi',msgText);
                    }else{
                        $("#aDiscFormula").textbox('setValue','0');
                        $.messager.alert('Validasi',msgText);
                    }
                }
            });

            $('#aDiscAmount').numberbox({
                onChange: function(rvalue){
                    /*
                    if (rvalue > 0) {
                        $('#aDiscFormula').textbox('setValue','0');
                    }
                    */
                    hitDetail();
                }
            });

            $('#aIsFree').combobox({
                onChange: function(rvalue){
                    if (rvalue == 1){
                        $('#aDiscFormula').textbox('setValue','0');
                        $('#aDiscAmount').numberbox('setValue',0);
                    }
                    hitDetail();
                }
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
            var lQTy = Number($("#lQty").numberbox('getValue'));
            var sQTy = Number($("#sQty").numberbox('getValue'));
            var tQty = 0;
            var rQty = Number((lQTy * iQty)) + sQTy;
            var xQty = Number($("#xQtyLalu").val());
            var bid  = $("#aItemId").val();
            var ur1  = "<?php print($helper->site_url("ar.invoice/checkStock/"));?>"+gudangId+'/'+bid;
            $.get(ur1, function(data){
                tQty = Number(data);
                $("#xQtyStock").numberbox('setValue',tQty);
                $("#aQtyStock").val(tQty);
                if (dMode == 'A') {
                    if (tQty >= rQty) {
                        $("#aQty").numberbox('setValue', rQty);
                    } else {
                        $("#lQty").numberbox('setValue', 0);
                        $("#sQty").numberbox('setValue', 0);
                        $("#aQty").numberbox('setValue', 0);
                        $.messager.alert('Warning', '[ER2] - Maaf Stock produk tidak cukup! (Stok: ' + tQty + ', Dibutuhkan: ' + rQty + ')');
                    }
                }else{
                    tQty = tQty + xQty;
                    if (tQty >= rQty) {
                        $("#aQty").numberbox('setValue', rQty);
                    } else {
                        $("#lQty").numberbox('setValue', 0);
                        $("#sQty").numberbox('setValue', 0);
                        $("#aQty").numberbox('setValue', 0);
                        $.messager.alert('Warning', '[ER2] - Maaf Stock produk tidak cukup! (Stok: ' + tQty  + ', Dibutuhkan: ' + rQty + ')');
                    }
                }
            });
        }

        function hitDetail(){
            var isFree = Number($("#aIsFree").combobox('getValue'));
            var tpp = Number($('#aPpnPct').numberbox('getValue'));
            var txa = 0;
            var isi = Number($("#aIsiSatKecil").val());
            var hrg = Number($("#aPrice").numberbox('getValue'));
            var lqt = Number($("#lQty").numberbox('getValue'));
            var sqt = Number($("#sQty").numberbox('getValue'));
            var dfm = $("#aDiscFormula").textbox('getValue');
            var dam = Number($('#aDiscAmount').numberbox('getValue'));
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
                if (dfm != null && dfm != '0' && dfm != '') {
                    discAmount = hitDiscFormula(subTotal, dfm);
                }else{
                    discAmount = Number(dam);
                    //discAmount = 0;
                }
                dpp = subTotal - discAmount;
                if (dpp > 0 && tpp > 0){
                    txa = round(dpp * (tpp/100),0);
                }
                totalDetail = dpp + txa;
            }
            $('#aDiscAmount').numberbox('setValue',discAmount);
            $('#aPpnAmount').numberbox('setValue',txa);
            $('#aDpp').numberbox('setValue',dpp);
            $('#aSubTotal').numberbox('setValue',subTotal);
            $('#aTotal').numberbox('setValue',totalDetail);
        }

        function feditdetail(dta){
            //$dtx = $detail->Id.'|'.$detail->ItemCode.'|'.str_replace('"',' in',$detail->ItemDescs).'|'.$detail->ItemId.'|'.$detail->SalesQty.'|'.$detail->ReturnQty.'|'.$detail->SatKecil.'|'.$detail->Price.'|'.$detail->DiscFormula.'|'.$detail->IsFree.'|'.$detail->ExSoId.'|'.$detail->PphPct.'|'.$detail->PpnPct.'|'.$detail->PpnAmount.'|'.$detail->PphAmount;
            var dtx = dta.split('|');
            var bid = Number(dtx[3]);
            var qst = 0;
            $('#aId').val(dtx[0]);
            detailId = Number(dtx[0]);
            $('#aItemId').val(dtx[3]);
            $('#aItemCode').textbox('setValue',dtx[1]);
            $('#aItemDescs').textbox('setValue',dtx[2]);
            $('#aSatuan').textbox('setValue',dtx[6]);
            $('#aPrice').numberbox('setValue',dtx[7]);
            $('#aDiscFormula').textbox('setValue',dtx[8]);
            $('#aIsFree').combobox('setValue',dtx[9]);
            $('#aExSoId').val(dtx[10]);
            $('#aPphPct').val(dtx[11]);
            $('#aPpnPct').numberbox('setValue',dtx[12]);
            $('#lQty').numberbox('setValue',dtx[15]);
            $('#sQty').numberbox('setValue',dtx[16]);
            $('#lUom').text(dtx[18]+' x ' + dtx[17]);
            $('#sUom').text(dtx[6]);
            $('#qUom').text(dtx[6]);
            $('#aIsiSatKecil').val(dtx[17]);
            $('#aQty').numberbox('setValue',dtx[4]);
            $('#xQtyLalu').val(dtx[4]);
            //set dialog
            $('#dlg').dialog('open').dialog('setTitle','Edit Detail Barang yang dijual');
            $('#cSubmit').text('UPDATE');
            url= "<?php print($helper->site_url('ar.invoice/edit_detail/'));?>"+invoiceId;
            dMode = 'E';
            $('#dExOrder').combogrid({disabled: true});
            $('#dExStock').combogrid({disabled: true});
            hitDetail();
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
            custId  = $("#CustomerId").combogrid("getValue");
            salesId = $("#SalesId").combobox("getValue");
            $('#dlg').dialog('open').dialog('setTitle','Tambah Detail Barang yang dijual');
            $('#cSubmit').text('SIMPAN');
            //$('#fm').form('clear');
            $('#dExOrder').combogrid({disabled: false});
            $('#dExStock').combogrid({disabled: false});
            var urz = "<?php print($helper->site_url('ar.invoice/getjson_orderitems/'));?>"+custId+'/'+salesId;
            $('#dExOrder').combogrid('grid').datagrid('load',urz);
            if (invoiceId == 0) {
                gudangId = $('#GudangId').combobox("getValue");
            }
            urz = "<?php print($helper->site_url("ar.invoice/getjson_stockitems/"));?>"+gudangId,
                $('#dExStock').combogrid('grid').datagrid('load',urz);
            url= "<?php print($helper->site_url('ar.invoice/add_detail/'));?>"+invoiceId;
            dMode = 'A';
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
            var crt = $('#CreditTerms').numberbox("getValue");
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
                        salesId = $('#SalesId').combobox("getValue");
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
            var aqty = Number($('#aQty').numberbox('getValue'));
            var astt = Number($('#aSubTotal').numberbox('getValue'));
            var aisf = $('#aIsFree').combobox('getValue');
            if ((aitd > 0 && aqty > 0 && astt > 0) || (aitd > 0 && aqty > 0 && aisf > 0) && validasiMaster()){
                if (invoiceId == 0) {
                    var data = {
                        CustomerId: $('#CustomerId').combobox("getValue"),
                        InvoiceNo: $('#InvoiceNo').textbox("getValue"),
                        InvoiceDate: $('#InvoiceDate').datebox("getValue"),
                        GudangId: $('#GudangId').combobox("getValue"),
                        SalesId: $('#SalesId').combobox("getValue"),
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
                            salesId = $('#SalesId').combobox("getValue");
                            if (dMode == 'E') {
                                var url = "<?php print($helper->site_url('ar.invoice/edit_detail/'));?>" + invoiceId +'/'+ detailId;
                            }else{
                                var url = "<?php print($helper->site_url('ar.invoice/add_detail/'));?>" + invoiceId;
                            }
                            var urx = "<?php print($helper->site_url("ar.invoice/edit/")); ?>" + invoiceId;
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
                    if (dMode == 'E') {
                        var url = "<?php print($helper->site_url('ar.invoice/edit_detail/'));?>" + invoiceId +'/'+ detailId;
                    }else{
                        var url = "<?php print($helper->site_url('ar.invoice/add_detail/'));?>" + invoiceId;
                    }
                    var urx = "<?php print($helper->site_url("ar.invoice/edit/")); ?>" + invoiceId;
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

        function validateDiscLevel(dFormula) {
            var aFormula = dFormula.split('+');
            var mDiscount = 0;
            var pDiscount = 0;
            var dLevel = aFormula.length;
            if (dLevel > 5){dLevel = 5;}
            var retVal = true;
            var z = 0;
            for (var i = 0; i < dLevel; i++) {
                z++;
                pDiscount = Number(aFormula[i]);
                mDiscount = Number(aDiscPrevileges[i]);
                if (pDiscount > mDiscount){
                    retVal = false;
                    msgText = 'Max Discount['+z+'] Allowed: '+mDiscount+'%';
                    break;
                }
            }
            return retVal;
        }

        function hitDiscFormula(nAmount,dFormula) {
            nAmount = Number(nAmount);
            if (nAmount > 0 && dFormula != '' && dFormula != '0') {
                var aFormula = dFormula.split('+');
                var nDiscount = 0;
                var pDiscount = 0;
                var dLevel = aFormula.length;
                var retVal = 0;
                if (dLevel > 5){dLevel = 5;}
                for (var i = 0; i < dLevel; i++) {
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
                    $.messager.alert('Warning',data+' Harga Produk ini belum disetting!');
                }else{
                    $('#aPrice').numberbox('setValue',dta[1]);
                    $('#pUom').text('/'+dta[0]);
                    hitDetail();
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
<div id="p" class="easyui-panel" title="Edit Invoice Penjualan" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <form id="frmMaster">
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang</td>
                <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($invoice->CabangCode != null ? $invoice->CabangCode : $userCabCode); ?>" disabled/>
                    <input type="hidden" id="CabangId" name="CabangId" value="<?php print($invoice->CabangId == null ? $userCabId : $invoice->CabangId);?>"/>
                </td>
                <td>Tanggal</td>
                <td><input type="text" class="easyui-datebox" id="InvoiceDate" name="InvoiceDate" style="width: 150px" value="<?php print($invoice->FormatInvoiceDate(SQL_DATEONLY));?>" <?php print($itemsCount == 0 ? 'required' : 'readonly');?> data-options="formatter:myformatter,parser:myparser"/></td>
                <td>No. Invoice</td>
                <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 150px" id="InvoiceNo" name="InvoiceNo" value="<?php print($invoice->InvoiceNo != null ? $invoice->InvoiceNo : '-'); ?>" readonly/></td>
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
                    <input type="hidden" id="DbAccId" name="DbAccId" value="<?=$invoice->DbAccId;?>"/>
                </td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td><b><input type="text" class="f1 easyui-textbox" id="InvoiceDescs" name="InvoiceDescs" style="width: 250px" value="<?php print($invoice->InvoiceDescs != null ? $invoice->InvoiceDescs : '-'); ?>" required/></b></td>
                <td>Gudang *</td>
                <td>
                    <select class="easyui-combobox" id="GudangId" name="GudangId" style="width: 150px" required>
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
                    <input type="text" class="easyui-numberbox" id="CreditTerms" name="CreditTerms" style="width: 40px" value="<?php print($invoice->CreditTerms != null ? $invoice->CreditTerms : 0); ?>" style="text-align: right" data-options="min:0" required/>
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
                            if ($detail->Lqty == 0 && $detail->Sqty == 0){
                                print('<td>&nbsp;</td>');
                                printf('<td class="right">%s</td>', number_format($detail->SalesQty, 0));
                            }else {
                                printf('<td class="right">%s</td>', number_format($detail->Lqty, 0));
                                printf('<td class="right">%s</td>', number_format($detail->Sqty, 0));
                            }
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
                            print("<td class='center' nowrap='nowrap'>");
                            $dta = addslashes($detail->Id.'|'.$detail->ItemCode.'|'.str_replace('"',' in',$detail->ItemDescs));
                            $dtx = $detail->Id.'|'.$detail->ItemCode.'|'.str_replace('"',' in',$detail->ItemDescs).'|'.$detail->ItemId.'|'.$detail->SalesQty.'|'.$detail->ReturnQty.'|'.$detail->SatKecil.'|'.$detail->Price.'|'.$detail->DiscFormula.'|'.$detail->IsFree.'|'.$detail->ExSoId.'|'.$detail->PphPct.'|'.$detail->PpnPct.'|'.$detail->PpnAmount.'|'.$detail->PphAmount.'|'.$detail->Lqty.'|'.$detail->Sqty.'|'.$detail->IsiSatKecil.'|'.$detail->SatBesar;
                            if ($acl->CheckUserAccess("ar.invoice", "delete")) {
                                printf('&nbsp<img src="%s" alt="Edit barang" title="Edit barang" style="cursor: pointer" onclick="return feditdetail(%s);"/>',$bedit,"'".$dtx."'");
                            }
                            if ($acl->CheckUserAccess("ar.invoice", "delete")) {
                                printf('&nbsp<img src="%s" alt="Hapus barang" title="Hapus barang" style="cursor: pointer" onclick="return fdeldetail(%s);"/>', $bclose, "'" . $dta . "'");
                            }
                            print("</td>");
                            print("</tr>");
                            $total += $detail->SubTotal;
                        }
                        ?>
                        <tr class="bold">
                            <td colspan="8" align="right">Total Rp. </td>
                            <td class="right"><?php print($invoice->BaseAmount != null ? number_format($invoice->BaseAmount,0) : 0); ?></td>
                            <td class="right"><?php print($invoice->DiscAmount != null ? number_format($invoice->DiscAmount,0) : 0); ?></td>
                            <td class="right"><?php print($invoice->PpnAmount != null ? number_format($invoice->BaseAmount - $invoice->DiscAmount,0) : 0); ?></td>
                            <td class="right"><?php print($invoice->PpnAmount != null ? number_format($invoice->PpnAmount,0) : 0); ?></td>
                            <td class="right"><?php print($invoice->TotalAmount != null ? number_format($invoice->TotalAmount,0) : 0); ?></td>
                            <td class='center'>
                                <?php
                                if ($acl->CheckUserAccess("ar.invoice", "add")) {
                                    printf('<img src="%s" alt="Tambah Barang" title="Tambah barang" id="bAdDetail" style="cursor: pointer;"/>', $badd);
                                }else{
                                    print('&nbsp;');
                                }
                                ?>
                            </td>
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
                <td class="right bold">Ex Order :</td>
                <td colspan="7"><input class="easyui-combogrid" id="dExOrder" name="dExOrder" style="width:600px"/></td>
            </tr>
            <tr>
                <td class="right bold">Ex Stock :</td>
                <td colspan="7"><input class="easyui-combogrid" id="dExStock" name="dExStock" style="width:600px"/></td>
            </tr>
            <tr>
                <td class="right bold">Kode Barang :</td>
                <td colspan="5"><input type="text" class="easyui-textbox bold" id="aItemCode" name="aItemCode" size="12" value="" readonly/>
                    <input type="hidden" id="aItemId" name="aItemId" value="0"/>
                    <input type="hidden" id="aId" name="aId" value="0"/>
                    <input type="hidden" id="aExSoId" name="aExSoId" value="0"/>
                    <input type="hidden" id="aQtyStock" name="aQtyStock" value="0"/>
                    <input type="hidden" id="xQtyLalu" name="xQtyLalu" value="0"/>
                    <input type="hidden" id="aIsiSatKecil" name="aIsiSatKecil" value="0"/>
                    <input type="text" class="easyui-textbox" id="aItemDescs" name="aItemDescs" style="width:300px" value="" readonly/>
                    <input type="hidden" name="aPphPct" id="aPphPct" value="0"/>
                    <input type="hidden" name="aPphAmount" id="aPphAmount" value="0"/>
                    &nbsp;
                    <b>Stock :</b>
                    &nbsp;
                    <input type="text" class="easyui-numberbox"  data-options="min:0,groupSeparator:','" id="xQtyStock" name="xQtyStock" style="width:60px" value="" readonly/>
                    <input type="text" class="easyui-textbox" id="aSatuan" name="aSatuan" style="width:50px" value="" readonly/>
                </td>
            </tr>
            <tr>
                <td class="right bold">L - QTY :</td>
                <td><input class="easyui-numberbox"  data-options="min:0,groupSeparator:','" type="text" id="lQty" name="lQty" style="width:60px" value="0" required/>&nbsp;<span id="lUom"></span></td>
                <td class="right bold">+ S - QTY :</td>
                <td><input class="easyui-numberbox"  data-options="min:0,groupSeparator:','" type="text" id="sQty" name="sQty" style="width:60px" value="0" required/>&nbsp;<span id="sUom"></span></td>
                <td class="right bold">= QTY :</td>
                <td><input class="easyui-numberbox"  data-options="min:0,groupSeparator:','" type="text" id="aQty" name="aQty" style="width:60px" value="0" readonly/>&nbsp;<span id="qUom"></span></td>
            </tr>
            <tr>
                <td class="right bold">Bonus/Free? :</td>
                <td><select class="easyui-combobox" name="aIsFree" id="aIsFree" style="width:100px">
                        <option value="0">0 - Tidak</option>
                        <option value="1">1 - Ya</option>
                    </select>
                </td>
                <td class="right bold">Harga :</td>
                <td><input class="easyui-numberbox"  data-options="min:0,groupSeparator:',',precision:2" type="text" id="aPrice" name="aPrice" style="width:100px" value="0"/><span id="pUom"></span></td>
                <td class="right bold">Jumlah :</td>
                <td><input class="easyui-numberbox"  data-options="min:0,groupSeparator:','" type="text" id="aSubTotal" name="aSubTotal" style="width:130px" value="0" readonly/></td>
            </tr>
            <tr>
                <td class="right bold">Diskon :</td>
                <td><input class="easyui-textbox" type="text" id="aDiscFormula" name="aDiscFormula" style="width:100px" value="0"/>%</td>
                <td class="right bold">Nilai Diskon :</td>
                <td><input class="easyui-numberbox"  data-options="min:0,groupSeparator:','" type="text" id="aDiscAmount" name="aDiscAmount" style="width:100px" value="0"/></td>
                <td class="right bold">D P P :</td>
                <td><input class="easyui-numberbox"  data-options="min:0,groupSeparator:','" type="text" id="aDpp" name="aDpp" style="width:130px" value="0" readonly/></td>
            </tr>
            <tr>
                <td class="right bold">P P N :</td>
                <td><input class="easyui-numberbox"  data-options="min:0,groupSeparator:','" type="text" name="aPpnPct" id="aPpnPct" value="10" style="width:100px"/>%</td>
                <td class="right bold">Nilai PPN :</td>
                <td><input class="easyui-numberbox"  data-options="min:0,groupSeparator:','" type="text" id="aPpnAmount" name="aPpnAmount" style="width:100px" value="0"/></td>
                <td class="right bold">Total :</td>
                <td><input class="easyui-numberbox"  data-options="min:0,groupSeparator:','" type="text" id="aTotal" name="aTotal" style="width:130px" value="0" readonly/></td>
            </tr>
        </table>
    </form>

</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveDetail()" style="width:90px"><span id="cSubmit">SIMPAN</span></a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Batal</a>
</div>
<!-- </body> -->
</html>
