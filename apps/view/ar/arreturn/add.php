<!DOCTYPE HTML>
<html>
<?php
/** @var $arreturn ArReturn */ ?>
<head>
    <title>SND System - Entry Return Penjualan</title>
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
        $( function() {
            //var addmaster = ["CabangId", "RjDate","CustomerId", "RjDescs", "btSubmit", "btKembali"];
            //BatchFocusRegister(addmaster);
            $("#RjDate").customDatePicker({ showOn: "focus" });
            var satBesar,satSedang,satKecil,isiSedang,isiKecil,isiKonversi;
            isiKonversi = 1;
            isiSedang   = 0;
            isiKecil    = 0;
            $('#CustomerId').combogrid({
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
                ]]
            });

            $('#aExInvoiceNo').combogrid({
                panelWidth:650,
                url: "<?php print($helper->site_url("ar.arreturn/getjson_invoicelists/".$arreturn->GudangId.'/'.$arreturn->CustomerId));?>",
                idField:'invoice_no',
                textField:'invoice_no',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'invoice_no',title:'No. Invoice',width:50},
                    {field:'invoice_date',title:'Tanggal',width:40},
                    {field:'base_amount',title:'Sub Total',width:20,align:'right'},
                    {field:'disc_amount',title:'Discount',width:20,align:'right'},
                    {field:'ppn_amount',title:'PPN',width:20,align:'right'}
                ]],
                onSelect: function(index,row){
                    var ivi = row.id;
                    console.log(ivi);
                    $("#aExInvoiceId").val(ivi);
                    var urz = "<?php print($helper->site_url("ar.arreturn/getjson_invoiceitems/"));?>"+ivi;
                    $('#aItemSearch').combogrid('grid').datagrid('load',urz);
                }
            });

            $('#aItemSearch').combogrid({
                panelWidth:650,
                url: "<?php print($helper->site_url("ar.arreturn/getjson_invoiceitems/0"));?>",
                idField:'item_id',
                textField:'item_id',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'item_code',title:'Kode Barang',width:30},
                    {field:'item_descs',title:'Nama Barang',width:70},
                    {field:'qty_jual',title:'Qty',width:20,align:'right'},
                    {field:'satuan',title:'Satuan',width:20},
                    {field:'price',title:'Harga',width:30,align:'right'},
                    {field:'disc_formula',title:'Disc %',width:25,align:'right'},
                    {field:'ppn_pct',title:'PPN %',width:20,align:'right'}
                ]],
                onSelect: function(index,row){
                    var idi = row.id;
                    console.log(idi);
                    var iti = row.item_id;
                    console.log(iti);
                    var itc = row.item_code;
                    console.log(itc);
                    var itd = row.item_descs;
                    console.log(itd);
                    var qtj = row.qty_jual;
                    console.log(qtj);
                    var sat = row.satuan;
                    console.log(sat);
                    var prc = row.price;
                    console.log(prc);
                    var ppn = row.ppn_pct;
                    console.log(ppn);
                    var pph = row.pph_pct;
                    console.log(pph);
                    var dfo = row.disc_formula;
                    console.log(dfo);
                    var dia = row.disc_amount;
                    console.log(dia);
                    var ifr = row.is_free;
                    console.log(ifr);
                    var hpp = row.item_hpp;
                    console.log(hpp);
                    $('#aExInvDetailId').val(idi);
                    $('#aItemId').val(iti);
                    $('#aItemCode').val(itc);
                    $('#aItemDescs').val(itd);
                    $('#aSatuan').val(sat);
                    $('#aPrice').val(prc);
                    $('#aPrice1').val(prc);
                    $('#aQtyJual').val(qtj);
                    $('#aQtyRetur').val(0);
                    $('#aSubTotal').val(0);
                    $('#aPpnPct').val(ppn);
                    $('#aPphPct').val(pph);
                    $('#aItemHpp').val(hpp);
                    $("#aDiscAmount").val(dia);
                    $("#aDiscFormula").val(dfo);
                    $("#aIsFree").val(ifr);
                    $("#xIsFree").val(ifr);
                    //isi variable global
                    satBesar    = row.bsatbesar;
                    satKecil    = row.bsatkecil;
                    isiKecil    = row.bisisatkecil;
                    isiKonversi = 0;
                }
            });

            $("#bAdDetail").click(function(e){
                var itemCount  = "<?php print($itemsCount == null ? 0 : $itemsCount);?>";
                var returnId   = "<?php print($arreturn->Id == null ? 0 : $arreturn->Id);?>";
                var customerId = $('#CustomerId').combogrid('getValue');
                if (itemCount > 0) {
                    var gudangId = $('#GudangId1').val();
                }else{
                    var gudangId = $('#GudangId').combogrid('getValue');
                }
                if (customerId > 0 && gudangId >0) {
                    var urz = "<?php print($helper->site_url("ar.arreturn/getjson_invoicelists/"));?>" + gudangId + "/" + customerId;
                    $('#aExInvDetailId').val(0);
                    $('#aItemId').val('');
                    $('#aItemCode').val('');
                    $('#aItemDescs').val('');
                    $('#aSatuan').val('');
                    $('#aPrice').val(0);
                    $('#aQtyJual').val(0);
                    $('#aQtyReturn').val('0');
                    $('#aExInvoiceNo').combogrid("setValue", '');
                    $('#aSubTotal').val(0);
                    $('#aPpnPct').val(10);
                    $('#aPpnAmount').val(0);
                    $('#aPphPct').val(10);
                    $('#aPphAmount').val(0);
                    $('#aDiscFormula').val(0);
                    $('#aDiscAmount').val(0);
                    $('#aKondisi').val(1);
                    $('#aIsFree').val(0);
                    $('#xIsFree').val(0);
                    $('#aItemHpp').val(0);
                    $('#aExInvoiceNo').combogrid('grid').datagrid('load', urz);
                    newItem();
                }else{
                    $('#CustomerId').focus();
                    $.messager.alert('Warning','Data input belum lengkap!');
                }
            });                        

            $("#bUpdate").click(function(){
                $.messager.confirm('Confirm','Apakah data input sudah benar?',function(r) {
                    if (r) {
                        $('#frmMaster').submit();
                    }
                });
            });

            $("#bTambah").click(function(){
                $.messager.confirm('Confirm','Buat Retur Penjualan baru?',function(r) {
                    if (r) {
                        location.href="<?php print($helper->site_url("ar.arreturn/add")); ?>";
                    }
                });
            });

            $("#bHapus").click(function(){
                $.messager.confirm('Confirm','Anda yakin akan membatalkan return ini?',function(r) {
                    if (r) {
                        location.href="<?php print($helper->site_url("ar.arreturn/void/").$arreturn->Id); ?>";
                    }
                });
            });

            $("#bCetak").click(function(){
                $.messager.confirm('Confirm','Cetak bukti retur ini?',function(r) {
                    if (r) {
                        location.href="<?php print($helper->site_url("ar.arreturn/print_pdf/").$arreturn->Id); ?>";
                    }
                });
            });

            $("#bKembali").click(function(){
                location.href="<?php print($helper->site_url("ar.arreturn")); ?>";
            });

            //hitung nilai retur
            $("#aQtyRetur").change(function(e){
                hiDetail();
            });

            $("#aIsFree").change(function(e){
                hiDetail();
            });

            $("#aDiscFormula").change(function(e){
                hiDetail();
            });
        });

        function hiDetail() {
            var qty = Number($('#aQtyJual').val());
            var qtr = Number($('#aQtyRetur').val());
            var prc = Number($('#aPrice').val());
            var ppn = Number($('#aPpnPct').val());
            var pph = Number($('#aPphPct').val());
            var dfo = $('#aDiscFormula').val();
            var ifr = $('#aIsFree').val();
            var dam = 0;
            var sbt = 0;
            var jum = 0;
            if (qtr > 0 && ifr == 0){
                if (qtr > qty){
                    $.messager.alert('Warning','Qty Retur tidak boleh melebihi Qty penjualan!');
                    $('#aQtyRetur').val(qty);
                    jum = round(qty * prc,0);
                }else{
                    jum = round(qtr * prc,0);
                }
                sbt = jum;
                //hitung discount
                if (sbt > 0) {
                    dam = hitDiscFormula(sbt, dfo);
                    sbt = sbt - dam;
                }
                //hitung ppn
                if (ppn > 0){
                    ppn = round(sbt * (ppn/100),0);
                }
                //hitung pph
                if (pph > 0){
                    pph = round(sbt * (pph/100),0);
                }
                $('#aSubTotal').val(jum);
                $('#aDiscAmount').val(dam);
                $('#aPpnAmount').val(ppn);
                $('#aPphAmount').val(pph);
                $('#aDpp').val(sbt-dam);
                $('#aTotal').val(sbt+ppn+pph);
            }else{
                $('#aSubTotal').val(0);
                $('#aDiscAmount').val(0);
                $('#aPpnAmount').val(0);
                $('#aPphAmount').val(0);
                $('#aDpp').val(0);
                $('#aTotal').val(0);
            }
        }
       
        function fdeldetail(dta){
            var dtz = dta.replace(/\"/g,"\\\"")
            var dtx = dtz.split('|');
            var id = dtx[0];
            var kode = dtx[2];
            var barang = dtx[3];
            var urx = '<?php print($helper->site_url("ar.arreturn/delete_detail/"));?>'+id;
            $.messager.confirm('Confirm','Hapus Data Detail Barang \nKode: '+kode+ '\nNama: '+barang+' ?',function(r) {
                if (r) {
                    $.get(urx, function(data){
                        $.messager.alert('Info',data);
                        location.reload();
                    });
                }
            });
        }        

        function newItem(){
            $('#dlg').dialog('open').dialog('setTitle','Tambah Detail Barang yang dikembalikan');
            //$('#fm').form('clear');
            url = "<?php print($helper->site_url("ar.arreturn/add_detail/".$arreturn->Id));?>";
            $('#aItemCode').focus();
        }

        function saveDetail(){
            var itemCount  = "<?php print($itemsCount == null ? 0 : $itemsCount);?>";
            var returnId   = "<?php print($arreturn->Id == null ? 0 : $arreturn->Id);?>";
            var customerId = $('#CustomerId').combogrid('getValue');
            if (itemCount > 0) {
                var gudangId = $('#GudangId1').val();
            }else{
                var gudangId = $('#GudangId').combogrid('getValue');
            }
            $.messager.confirm('Confirm','Apakah data input sudah benar?',function(r){
                if (r){
                    var urz = "<?php print($helper->site_url("ar.arreturn/proses_master/")); ?>" + returnId;
                    //proses simpan dan update master
                    $.post(urz, {
                        GudangId: gudangId,
                        RjDate: $("#RjDate").val(),
                        RjNo: $("#RjNo").val(),
                        RjDescs: $("#RjDescs").val(),
                        CustomerId: customerId
                    }).done(function (data) {
                        var rst = data.split('|');
                        if (rst[0] == 'OK') {
                            //validasi detail
                            var arti = rst[2];
                            if (arti > 0) {
                                var url = "<?php print($helper->site_url("ar.arreturn/add_detail/"));?>"+arti;
                                urz = "<?php print($helper->site_url("ar.arreturn/add/")); ?>" + arti;
                                //proses submit detail
                                var rqty = Number($('#aQtyRetur').val());
                                if (rqty > 0) {
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
                                                $('#dlg').dialog('close');		// close the dialog
                                                location.href = urz;
                                            }
                                        }
                                    });
                                } else {
                                    $.messager.alert('Warning','[E2] Data detail tidak valid!');
                                }
                            }else {
                                $.messager.alert('Warning','[E1] Data master tidak valid!');
                                location.reload();
                            }
                        }
                    });
                }
            });
        }

        function hitDiscFormula(nAmount,dFormula) {
            nAmount = Number(nAmount);
            var retVal = 0;
            if (nAmount > 0 && dFormula != '' && dFormula != '0') {
                var aFormula = dFormula.split('+');
                var nDiscount = 0;
                var pDiscount = 0;
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
<div id="p" class="easyui-panel" title="Entry Return Penjualan" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <form id="frmMaster" action="<?php print($helper->site_url("ar.arreturn/add/".$arreturn->Id)); ?>" method="post">
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($arreturn->CabangCode != null ? $arreturn->CabangCode : $userCabCode); ?>" disabled/>
                    <input type="hidden" id="CabangId" name="CabangId" value="<?php print($arreturn->CabangId == null ? $userCabId : $arreturn->CabangId);?>"/>
                </td>
                <td>Tanggal</td>
                <td><input type="text" size="12" id="RjDate" name="RjDate" value="<?php print($arreturn->FormatRjDate(JS_DATE));?>"/></td>
                <td>No. Bukti</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="RjNo" name="RjNo" value="<?php print($arreturn->RjNo != null ? $arreturn->RjNo : '-'); ?>"/></td>
            </tr>
            <tr>
                <td>Customer</td>
                <td><input class="easyui-combogrid" id="CustomerId" name="CustomerId" style="width: 250px" value="<?php print($arreturn->CustomerId);?>" required/></td>
                <td>Gudang</td>
                <td>
                    <?php if ($itemsCount == 0){?>
                    <select class="easyui-combobox" id="GudangId" name="GudangId" style="width: 150px" required>
                        <?php }else{ ?>
                        <input type="hidden" name="GudangId1" id="GudangId1" value="<?php print($arreturn->GudangId);?>"/>
                        <select class="easyui-combobox" id="GudangId" name="GudangId" style="width: 150px" disabled>
                            <?php } ?>
                            <option value="">- Pilih Gudang -</option>
                            <?php
                            foreach ($gudangs as $gudang) {
                                if ($gudang->Id == $arreturn->GudangId) {
                                    printf('<option value="%d" selected="selected">%s</option>', $gudang->Id, $gudang->WhCode);
                                }else {
                                    printf('<option value="%d">%s</option>', $gudang->Id, $gudang->WhCode);
                                }
                            }
                            ?>
                        </select>
                </td>
                <td>Status</td>
                <td><select class="easyui-combobox" id="RjStatus" name="RjStatus" style="width: 150px" disabled>
                        <option value="0" <?php print($arreturn->RjStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                        <option value="1" <?php print($arreturn->RjStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                        <option value="2" <?php print($arreturn->RjStatus == 2 ? 'selected="selected"' : '');?>>2 - Approved</option>
                        <option value="3" <?php print($arreturn->RjStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td colspan="3"><b><input type="text" class="f1 easyui-textbox" id="RjDescs" name="RjDescs" style="width: 250px" value="<?php print($arreturn->RjDescs != null ? $arreturn->RjDescs : '-'); ?>"/></b></td>
            </tr>
            <tr>
                <td colspan="7">
                    <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                        <tr>
                            <th colspan="13">DETAIL BARANG YANG DIKEMBALIKAN</th>
                            <th rowspan="2">Action</th>
                        </tr>
                        <tr>
                            <th>No.</th>
                            <th>Ex. Invoice No.</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Kondisi</th>
                            <th>Harga</th>
                            <th>SubTotal</th>
                            <th>Diskon</th>
                            <th>DPP</th>
                            <th>PPN</th>
                            <th>Jumlah</th>
                        </tr>
                        <?php
                        $counter = 0;
                        $total = 0;
                        $dta = null;
                        foreach($arreturn->Details as $idx => $detail) {
                            $counter++;
                            print("<tr>");
                            printf('<td class="right">%s.</td>', $counter);
                            printf('<td>%s</td>', $detail->ExInvoiceNo);
                            printf('<td>%s</td>', $detail->ItemCode);
                            printf('<td>%s</td>', $detail->ItemDescs);
                            printf('<td class="right">%s</td>', number_format($detail->QtyRetur,0));
                            printf('<td>%s</td>', $detail->SatKecil);
                            $kds = null;
                            if ($detail->Kondisi == 1){
                                $kds = "Bagus";
                            }elseif ($detail->Kondisi == 2){
                                $kds = "Rusak";
                            }elseif ($detail->Kondisi == 3) {
                                $kds = "Expire";
                            }else{
                                $kds = "N/A";
                            }
                            $jumlah = round($detail->QtyRetur * $detail->Price,0);
                            $dpp    = $jumlah - $detail->DiscAmount;
                            $ppn    = round($dpp * ($detail->PpnPct/100),0);
                            $pph    = round($dpp * ($detail->PphPct/100),0);
                            $stotal = $dpp + $ppn + $pph;
                            printf('<td>%s</td>', $kds);
                            printf('<td class="right">%s</td>', number_format($detail->Price,2));
                            printf('<td class="right">%s</td>', number_format($jumlah,0));
                            printf('<td class="right">%s</td>', number_format($detail->DiscAmount,0));
                            printf('<td class="right">%s</td>', number_format($dpp,0));
                            printf('<td class="right">%s</td>', number_format($ppn,0));
                            //printf('<td class="right">%s</td>', number_format($pph,0));
                            printf('<td class="right">%s</td>', number_format($stotal,0));
                            print("<td class='center'>");
                            $dta = addslashes($detail->Id.'|'.$detail->ExInvoiceNo.'|'.$detail->ItemCode.'|'.str_replace('"',' in',$detail->ItemDescs));
                            printf('&nbsp<img src="%s" alt="Hapus barang" title="Hapus barang" style="cursor: pointer" onclick="return fdeldetail(%s);"/>',$bclose,"'".$dta."'");
                            print("</td>");
                            print("</tr>");
                            $total += $stotal;
                        }
                        ?>
                        <tr>
                            <td colspan="12" align="right">Total Nilai Retur:</td>
                            <td class="right bold"><?php print($arreturn->RjAmount != null ? number_format($arreturn->RjAmount,0) : 0);?></td>
                            <td class='center'><?php printf('<img src="%s" alt="Tambah Barang" title="Tambah barang" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                        </tr>
                        <tr>
                            <td colspan="14" class="right">
                                <?php
                                if ($acl->CheckUserAccess("ar.arreturn", "edit")) {
                                    printf('<img src="%s" alt="Simpan Data" title="Simpan data master" id="bUpdate" style="cursor: pointer;"/> &nbsp',$bsubmit);
                                }
                                if ($acl->CheckUserAccess("ar.arreturn", "add")) {
                                    printf('<img src="%s" alt="Data Baru" title="Buat Data Baru" id="bTambah" style="cursor: pointer;"/> &nbsp',$baddnew);
                                }
                                if ($acl->CheckUserAccess("ar.arreturn", "delete")) {
                                    printf('<img src="%s" alt="Hapus Data" title="Hapus Data" id="bHapus" style="cursor: pointer;"/> &nbsp',$bdelete);
                                }
                                if ($acl->CheckUserAccess("ar.arreturn", "print")) {
                                    printf('<img src="%s" alt="Cetak Bukti" title="Cetak Receipt" id="bCetak" style="cursor: pointer;"/> &nbsp',$bcetak);
                                }
                                printf('<img src="%s" id="bKembali" alt="Daftar Return" title="Kembali ke daftar return" style="cursor: pointer;"/>',$bkembali);
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
<!-- Form Add ArRreturn Detail -->
<div id="dlg" class="easyui-dialog" style="width:750px;height:300px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <form id="fm" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding" style="font-size: 12px;font-family: tahoma">
            <tr>
                <td class="right bold">Ex Invoice No:</td>
                <td colspan="7">
                    <input type="text" id="aExInvoiceNo" name="aExInvoiceNo" style="width: 580px;" value="" required/>
                    <input type="hidden" id="aExInvoiceId" name="aExInvoiceId" value="0"/>
                    <input type="hidden" id="aExInvDetailId" name="aExInvDetailId" value="0"/>
                    <input type="hidden" id="aItemId" name="aItemId" value="0"/>
                    <input type="hidden" id="aId" name="aId" value="0"/>
                    <input type="hidden" id="aItemHpp" name="aItemHpp" value="0"/>
                    <input type="hidden" id="aIsFree" name="aIsFree" value="0"/>
                    <input type="hidden" name="aPphPct" id="aPphPct" value="0"/>
                    <input type="hidden" id="aPphAmount" name="aPphAmount" value="0"/>
                </td>
            </tr>
            <tr>
                <td class="right bold">Cari Produk:</td>
                <td colspan="7">
                    <input type="text" class="bold" id="aItemCode" name="aItemCode" size="10" value="" required/>
                    <input class="easyui-combogrid" id="aItemSearch" name="aItemSearch" style="width:20px"/>
                    <input type="text" id="aItemDescs" name="aItemDescs" size="44" value="" readonly/>
                    QTY :
                    <input class="right" type="text" id="aQtyJual" name="aQtyJual" size="3" value="0" readonly/>
                    <input type="text" id="aSatuan" name="aSatuan" value="" size="7" readonly/>
                </td>
            </tr>
            <tr>
                <td class="right bold">Qty Retur:</td>
                <td colspan="7">
                    <input class="right bold" type="text" id="aQtyRetur" name="aQtyRetur" size="5" value="0" required/>
                    &nbsp;
                    Kondisi:
                    &nbsp;
                    <select name="aKondisi" id="aKondisi" required>
                        <option value="1"> 1 - Bagus </option>
                        <option value="2"> 2 - Rusak </option>
                        <option value="3"> 3 - Expire </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="right bold">Bonus/Free? :</td>
                <td><select name="xIsFree" id="xIsFree" style="width:100px" disabled>
                        <option value="0">0 - Tidak</option>
                        <option value="1">1 - Ya</option>
                    </select>
                </td>
                <td class="right bold">Harga :</td>
                <td><input class="right bold" type="text" id="aPrice" name="aPrice" size="8" value="0" readonly/></td>
                <td class="right bold">Jumlah :</td>
                <td><input class="right bold" type="text" id="aSubTotal" name="aSubTotal" size="15" value="0" readonly/></td>
            </tr>
            <tr>
                <td class="right bold">Diskon :</td>
                <td><input class="right bold" type="text" id="aDiscFormula" name="aDiscFormula" size="12" value="0" readonly/>%</td>
                <td class="right bold">Nilai Diskon :</td>
                <td><input class="right bold" type="text" id="aDiscAmount" name="aDiscAmount" size="12" value="0" readonly/></td>
                <td class="right bold">D P P :</td>
                <td><input class="right bold" type="text" id="aDpp" name="aDpp" size="15" value="0" readonly/></td>
            </tr>
            <tr>
                <td class="right bold">P P N :</td>
                <td><input class="right bold" type="text" name="aPpnPct" id="aPpnPct" value="10" size="12" readonly/>%</td>
                <td class="right bold">Nilai PPN :</td>
                <td><input class="right bold" type="text" id="aPpnAmount" name="aPpnAmount" size="12" value="0" readonly/></td>
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
