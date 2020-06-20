<!DOCTYPE HTML>
<html>
<?php
/** @var $transfer Transfer */
?>
<head>
<title>SND System - Edit Pengiriman Barang Antar Cabang</title>
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
        $("#bEdit").click(function(){
            if (confirm('Ubah data NPB ini?')){
                location.href="<?php print($helper->site_url("inventory.transfer/edit/".$transfer->Id)); ?>";
            }
        });

        $("#bTambah").click(function(){
            if (confirm('Buat NPB baru?')){
                location.href="<?php print($helper->site_url("inventory.transfer/add")); ?>";
            }
        });

        $("#bHapus").click(function(){
            if (confirm('Anda yakin akam menghapus NPB ini?')){
                location.href="<?php print($helper->site_url("inventory.transfer/delete/").$transfer->Id); ?>";
            }
        });

        $("#bCetak").click(function(){
            if (confirm('Cetak PDF Bukti Transfer ini?')){
                window.open("<?php print($helper->site_url("inventory.transfer/transfer_print/?&id[]=").$transfer->Id); ?>");
            }
        });

        $("#bKembali").click(function(){
            location.href="<?php print($helper->site_url("inventory.transfer")); ?>";
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
        btransfer-bottom:1px solid #ccc;
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
<div id="p" class="easyui-panel" title="View Pengiriman Barang Antar Cabang" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td>Dari Gudang</td>
            <td><select name="FrWhId" class="easyui-combobox" id="FrWhId" style="width: 250px" disabled>
                    <option value=""></option>
                    <?php
                    foreach ($whfrom as $gdg) {
                        if ($gdg->Id == $transfer->FrWhId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $gdg->Id, $gdg->WhCode, $gdg->WhName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $gdg->Id, $gdg->WhCode, $gdg->WhName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>Tanggal</td>
            <td><input type="text" size="12" id="NpbDate" name="NpbDate" value="<?php print($transfer->FormatNpbDate(JS_DATE));?>" disabled/></td>
            <td>No. NPB</td>
            <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="NpbNo" name="NpbNo" value="<?php print($transfer->NpbNo != null ? $transfer->NpbNo : '-'); ?>" disabled/></td>
        </tr>
        <tr>
            <td>Ke Gudang</td>
            <td><select name="ToWhId" class="easyui-combobox" id="ToWhId" style="width: 250px" disabled>
                    <option value=""></option>
                    <?php
                    foreach ($whdest as $gdg) {
                        if ($gdg->Id == $transfer->ToWhId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $gdg->Id, $gdg->WhCode, $gdg->WhName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $gdg->Id, $gdg->WhCode, $gdg->WhName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>Status</td>
            <td><select class="easyui-combobox" id="NpbStatus" name="NpbStatus" style="width: 100px" disabled>
                    <option value="0" <?php print($transfer->NpbStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                    <option value="1" <?php print($transfer->NpbStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                    <option value="2" <?php print($transfer->NpbStatus == 2 ? 'selected="selected"' : '');?>>2 - Closed</option>
                    <option value="3" <?php print($transfer->NpbStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td colspan="3"><b><input type="text" class="f1 easyui-textbox" id="NpbDescs" name="NpbDescs" style="width: 250px" maxlength="150" value="<?php print($transfer->NpbDescs != null ? $transfer->NpbDescs : '-'); ?>" disabled/></b></td>
        </tr>
        <tr>
            <td colspan="7">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                    <tr>
                        <th colspan="5">DETAIL BARANG YANG DIKIRIM</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                    </tr>
                    <?php
                    $counter = 0;
                    $dta = null;
                    $dtx = null;
                    $tqy = 0;
                    foreach($transfer->Details as $idx => $detail) {
                        $counter++;
                        print("<tr>");
                        printf('<td class="right">%s.</td>', $counter);
                        printf('<td>%s</td>', $detail->ItemCode);
                        printf('<td>%s</td>', $detail->ItemName);
                        printf('<td class="right">%s</td>', number_format($detail->Qty,0));
                        printf('<td>%s</td>', $detail->SatKecil);
                        print("</tr>");
                        $tqy+= $detail->Qty;
                    }
                    ?>
                    <tr>
                        <td colspan="3" align="right">Total :</td>
                        <td class="right bold"><?php print(number_format($tqy,0));?></td>
                        <td>item(s)</td>
                    </tr>
                    <tr>
                        <td colspan="5" align="right">
                            <?php printf('<img src="%s" id="bCetak" alt="Cetak Npb" title="Proses cetak NPB" style="cursor: pointer;"/>',$bcetak);?>
                            &nbsp&nbsp
                            <?php printf('<img src="%s" id="bKembali" alt="Daftar Npb" title="Kembali ke daftar NPB" style="cursor: pointer;"/>',$bkembali);?>
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
