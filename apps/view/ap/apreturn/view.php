<!DOCTYPE HTML>
<html>
<?php
/** @var $apreturn ApReturn */ ?>
<head>
<title>SND System - View Return Pembelian</title>
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
        //var addetail = ["aItemCode", "aQty","aPrice", "aDiscFormula", "aDiscAmount", "aSubTotal"];
        //BatchFocusRegister(addetail);
        //var addmaster = ["CabangId", "ArRreturnDate","SupplierId", "SalesId", "ArRreturnDescs", "PaymentType","CreditTerms","BaseAmount","Disc1Pct","Disc1Amount","TaxPct","TaxAmount","OtherCosts","OtherCostsAmount","TotalAmount","bUpdate","bKembali"];
        //BatchFocusRegister(addmaster);
        $("#RbDate").customDatePicker({ showOn: "focus" });
        var satBesar,satSedang,satKecil,isiSedang,isiKecil,isiKonversi;
        isiKonversi = 1;
        isiSedang   = 0;
        isiKecil    = 0;
        $('#SupplierId').combogrid({
            panelWidth:600,
            url: "<?php print($helper->site_url("ap.supplier/getJsonSupplier"));?>",
            idField:'id',
            textField:'sup_name',
            mode:'remote',
            fitColumns:true,
            columns:[[
                {field:'sup_code',title:'Kode',width:50},
                {field:'sup_name',title:'Nama Supplier',width:150},
                {field:'addr1',title:'Alamat',width:150},
                {field:'city',title:'Kota',width:60}
            ]]
        });

        $("#bEdit").click(function(){
            if (confirm('Ubah data retur ini?')){
                location.href="<?php print($helper->site_url("ap.apreturn/add/").$apreturn->Id); ?>";
            }
        });

        $("#bTambah").click(function(){
            if (confirm('Buat Retur Pembelian baru?')){
                location.href="<?php print($helper->site_url("ap.apreturn/add/0")); ?>";
            }
        });

        $("#bHapus").click(function(){
            if (confirm('Anda yakin akam membatalkan retur ini?')){
                location.href="<?php print($helper->site_url("ap.apreturn/void/").$apreturn->Id); ?>";
            }
        });

        $("#bCetak").click(function(){
            if (confirm('Cetak bukti retur ini?')){
                location.href="<?php print($helper->site_url("ap.apreturn/print_pdf/").$apreturn->Id); ?>";
            }
        });

        $("#bKembali").click(function(){
            location.href="<?php print($helper->site_url("ap.apreturn")); ?>";
        });
    });
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
<div id="p" class="easyui-panel" title="View Return Pembelian" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($apreturn->CabangCode != null ? $apreturn->CabangCode : $userCabCode); ?>" disabled/>
                    <input type="hidden" id="CabangId" name="CabangId" value="<?php print($apreturn->CabangId == null ? $userCabId : $apreturn->CabangId);?>"/>
                </td>
                <td>Tanggal</td>
                <td><input type="text" size="12" id="RbDate" name="RbDate" value="<?php print($apreturn->FormatRbDate(JS_DATE));?>"/></td>
                <td>No. Bukti</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="RbNo" name="RbNo" value="<?php print($apreturn->RbNo != null ? $apreturn->RbNo : '-'); ?>"/></td>
            </tr>
            <tr>
                <td>Supplier</td>
                <td><input class="easyui-combogrid" id="SupplierId" name="SupplierId" style="width: 250px" value="<?php print($apreturn->SupplierId);?>"/></td>
                <td>Gudang</td>
                <td>
                    <?php if ($itemsCount == 0){?>
                    <select class="easyui-combobox" id="GudangId" name="GudangId" style="width: 150px" required>
                        <?php }else{ ?>
                        <input type="hidden" name="GudangId1" id="GudangId1" value="<?php print($apreturn->GudangId);?>"/>
                        <select class="easyui-combobox" id="GudangId" name="GudangId" style="width: 150px" disabled>
                            <?php } ?>
                            <option value="">- Pilih Gudang -</option>
                            <?php
                            foreach ($gudangs as $gudang) {
                                if ($gudang->Id == $apreturn->GudangId) {
                                    printf('<option value="%d" selected="selected">%s</option>', $gudang->Id, $gudang->WhCode);
                                }else {
                                    printf('<option value="%d">%s</option>', $gudang->Id, $gudang->WhCode);
                                }
                            }
                            ?>
                        </select>
                </td>
                <td>Status</td>
                <td><select class="easyui-combobox" id="RbStatus" name="RbStatus" style="width: 150px" disabled>
                        <option value="0" <?php print($apreturn->RbStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                        <option value="1" <?php print($apreturn->RbStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                        <option value="2" <?php print($apreturn->RbStatus == 2 ? 'selected="selected"' : '');?>>2 - Approved</option>
                        <option value="3" <?php print($apreturn->RbStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td colspan="3"><b><input type="text" class="f1 easyui-textbox" id="RbDescs" name="RbDescs" style="width: 250px" value="<?php print($apreturn->RbDescs != null ? $apreturn->RbDescs : '-'); ?>"/></b></td>
            </tr>
            <tr>
                <td colspan="7">
                    <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                        <tr>
                            <th colspan="14">DETAIL BARANG YANG DIKEMBALIKAN</th>
                        </tr>
                        <tr>
                            <th>No.</th>
                            <th>Ex. GRN No.</th>
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
                            <th>PPh</th>
                            <th>Jumlah</th>
                        </tr>
                        <?php
                        $counter = 0;
                        $total = 0;
                        $dta = null;
                        foreach($apreturn->Details as $idx => $detail) {
                            $counter++;
                            print("<tr>");
                            printf('<td class="right">%s.</td>', $counter);
                            printf('<td>%s</td>', $detail->ExGrnNo);
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
                            printf('<td class="right">%s</td>', number_format($pph,0));
                            printf('<td class="right">%s</td>', number_format($stotal,0));
                            print("</tr>");
                            $total += $stotal;
                        }
                        ?>
                        <tr>
                            <td colspan="13" align="right">Total Nilai Retur:</td>
                            <td class="right bold"><?php print($apreturn->RbAmount != null ? number_format($apreturn->RbAmount,0) : 0);?></td>
                        </tr>
                        <tr>
                            <td colspan="14" class="right">
                                <?php
                                if ($acl->CheckUserAccess("ap.apreturn", "add")) {
                                    printf('<img src="%s" alt="Data Baru" title="Buat Data Baru" id="bTambah" style="cursor: pointer;"/> &nbsp',$baddnew);
                                }
                                if ($acl->CheckUserAccess("ap.apreturn", "delete")) {
                                    printf('<img src="%s" alt="Hapus Data" title="Hapus Data" id="bHapus" style="cursor: pointer;"/> &nbsp',$bdelete);
                                }
                                if ($acl->CheckUserAccess("ap.apreturn", "print")) {
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
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- </body> -->
</html>
