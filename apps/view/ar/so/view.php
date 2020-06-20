<!DOCTYPE HTML>
<html>
<?php
/** @var $order Order */ 
?>
<head>
<title>SND System - View Sales Order (SO)</title>
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
        $('#CustomerId').combogrid({
            panelWidth:600,
            url: "<?php print($helper->site_url("ar.customer/getJsonCustomer"));?>",
            idField:'id',
            textField:'cus_name',
            mode:'remote',
            fitColumns:true,
            columns:[[
                {field:'cus_code',title:'Kode',width:30},
                {field:'cus_name',title:'Nama Customer',width:100},
                {field:'addr1',title:'Alamat',width:100},
                {field:'area_name',title:'Kota',width:60}
            ]]
        });

        $("#bTambah").click(function(){
            if (confirm('Buat SO baru?')){
                location.href="<?php print($helper->site_url("ar.order/add")); ?>";
            }
        });

        $("#bEdit").click(function(){
            if (confirm('Anda yakin akan mengubah SO ini?')){
                location.href="<?php print($helper->site_url("ar.order/edit/").$order->Id); ?>";
            }
        });

        $("#bHapus").click(function(){
            if (confirm('Anda yakin akan menghapus SO ini?')){
                location.href="<?php print($helper->site_url("ar.order/delete/").$order->Id); ?>";
            }
        });

        $("#bCetak").click(function(){
            if (confirm('Cetak SO ini?')){
                alert('Proses cetak..');
            }
        });

        $("#bKembali").click(function(){
            location.href="<?php print($helper->site_url("ar.order")); ?>";
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
<div id="p" class="easyui-panel" title="View Sales Order (SO)" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td>Cabang</td>
            <td><select name="CabangId" class="easyui-combobox" id="CabangId" style="width: 250px" disabled>
                    <option value=""></option>
                    <?php
                    foreach ($cabangs as $cab) {
                        if ($cab->Id == $order->CabangId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>Tanggal</td>
            <td><input type="text" size="12" id="SoDate" name="SoDate" value="<?php print($order->FormatSoDate(JS_DATE));?>" disabled/></td>
            <td>Dibutuhkan</td>
            <td><input type="text" size="12" id="RequestDate" name="RequestDate" value="<?php print($order->FormatRequestDate(JS_DATE));?>" disabled /></td>
            <td>No. Order</td>
            <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="SoNo" name="SoNo" value="<?php print($order->SoNo != null ? $order->SoNo : '-'); ?>" disabled/></td>
        </tr>
        <tr>
            <td>Customer</td>
            <td><input class="easyui-combogrid" id="CustomerId" name="CustomerId" style="width: 250px" value="<?php print($order->CustomerId);?>" disabled/></td>
            <td>Salesman</td>
            <td><select class="easyui-combobox" id="SalesId" name="SalesId" style="width: 150px" disabled>
                    <option value=""></option>
                    <?php
                    /** @var $sales Salesman[]*/
                    foreach ($sales as $staf) {
                        if ($staf->Id == $order->SalesId) {
                            printf('<option value="%d" selected="selected">%s</option>', $staf->Id, $staf->SalesName);
                        }else{
                            printf('<option value="%d">%s</option>', $staf->Id, $staf->SalesName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>Status</td>
            <td><select class="easyui-combobox" id="SoStatus" name="SoStatus" style="width: 100px" disabled>
                    <option value="0" <?php print($order->SoStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                    <option value="1" <?php print($order->SoStatus == 1 ? 'selected="selected"' : '');?>>1 - Open</option>
                    <option value="2" <?php print($order->SoStatus == 2 ? 'selected="selected"' : '');?>>2 - Closed</option>
                    <option value="3" <?php print($order->SoStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td><b><input type="text" class="f1 easyui-textbox" id="SoDescs" name="SoDescs" style="width: 250px" maxlength="150" value="<?php print($order->SoDescs != null ? $order->SoDescs : '-'); ?>" disabled/></b></td>
            <td>Cara Bayar</td>
            <td><select id="PaymentType" name="PaymentType" disabled>
                    <option value="1" <?php print($order->PaymentType == 1 ? 'selected="selected"' : '');?>>Kredit</option>
                    <option value="0" <?php print($order->PaymentType == 0 ? 'selected="selected"' : '');?>>Tunai</option>
                </select>
                &nbsp
                Kredit
                <input type="text" id="CreditTerms" name="CreditTerms" size="2" maxlength="5" value="<?php print($order->CreditTerms != null ? $order->CreditTerms : 0); ?>" style="text-align: right" disabled/>&nbsphr</td>
        </tr>
        <tr>
            <td colspan="7">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                    <tr>
                        <th colspan="10">DETAIL BARANG YANG DIPESAN</th>
                        <th rowspan="2">Action</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Order</th>
                        <th>Terkirim</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Disc (%)</th>
                        <th>Diskon</th>
                        <th>Jumlah</th>
                    </tr>
                    <?php
                    $counter = 0;
                    $total = 0;
                    $dta = null;
                    $dtx = null;
                    foreach($order->Details as $idx => $detail) {
                        $counter++;
                        print("<tr>");
                        printf('<td class="right">%s.</td>', $counter);
                        printf('<td>%s</td>', $detail->ItemCode);
                        printf('<td>%s</td>', $detail->ItemDescs);
                        printf('<td class="right">%s</td>', number_format($detail->OrderQty,0));
                        printf('<td class="right">%s</td>', number_format($detail->SendQty,0));
                        printf('<td>%s</td>', $detail->SatKecil);
                        printf('<td class="right">%s</td>', number_format($detail->Price,0));
                        printf('<td class="right">%s</td>', $detail->DiscFormula);
                        printf('<td class="right">%s</td>', number_format($detail->DiscAmount,0));
                        printf('<td class="right">%s</td>', number_format($detail->SubTotal,0));
                        print("<td class='center'>&nbsp</td>");
                        print("</tr>");
                        $total += $detail->SubTotal;
                    }
                    ?>
                    <tr>
                        <td colspan="9" align="right">Sub Total :</td>
                        <td><input type="text" class="right bold" style="width: 150px" id="BaseAmount" name="BaseMount" value="<?php print($order->BaseAmount != null ? number_format($order->BaseAmount,0) : 0); ?>" disabled/></td>
                        <td class='center'>&nbsp</td>
                    </tr>
                    <tr>
                        <td colspan="9" align="right">Diskon (%) :</td>
                        <td><input type="text" class="right bold" style="width: 30px" id="Disc1Pct" name="Disc1Pct" value="<?php print($order->Disc1Pct != null ? number_format($order->Disc1Pct,0) : 0); ?>" disabled/>
                            <input type="text" class="right bold" style="width: 110px" id="Disc1Amount" name="Disc1Amount" value="<?php print($order->Disc1Amount != null ? number_format($order->Disc1Amount,0) : 0); ?>" disabled/></td>
                        <td class='center'><?php printf('<img src="%s" alt="Edit So" title="Proses edit P/O" id="bEdit" style="cursor: pointer;"/>',$bedit);?></td>
                    </tr>
                    <tr>
                        <td colspan="9" align="right">D P P :</td>
                        <td><input type="text" class="right bold" style="width: 150px" id="DppAmount" name="DppAmount" value="<?php print(number_format($order->BaseAmount - $order->Disc1Amount,0)); ?>" disabled/></td>
                        <td class='center'><?php printf('<img src="%s" alt="Invoie Baru" title="Buat P/O baru" id="bTambah" style="cursor: pointer;"/>',$baddnew);?></td>
                    </tr>
                    <tr>
                        <td colspan="9" align="right">Pajak (%) :</td>
                        <td><input type="text" class="right bold" style="width: 30px" id="TaxPct" name="TaxPct" value="<?php print($order->TaxPct != null ? $order->TaxPct : 0); ?>"/>
                            <input type="text" class="right bold" style="width: 110px" id="TaxAmount" name="TaxAmount" value="<?php print($order->TaxAmount != null ? number_format($order->TaxAmount,0) : 0); ?>" disabled/></td>
                        <td class='center'><?php printf('<img src="%s" alt="Hapus So" title="Proses hapus P/O" id="bHapus" style="cursor: pointer;"/>',$bdelete);?></td>
                    </tr>
                    <tr>
                        <td colspan="2" align="right">Biaya Lain :</td>
                        <td colspan="7"><b><input type="text" class="bold" id="OtherCosts" name="OtherCosts" size="60" maxlength="150" value="<?php print($order->OtherCosts != null ? $order->OtherCosts : '-'); ?>" disabled/></b></td>
                        <td><input type="text" class="right bold" style="width: 150px" id="OtherCostsAmount" name="OtherCostsAmount" value="<?php print($order->OtherCostsAmount != null ? number_format($order->OtherCostsAmount,0) : 0); ?>" disabled/></td>
                        <td class='center'><?php printf('<img src="%s" id="bCetak" alt="Cetak So" title="Proses cetak P/O" style="cursor: pointer;"/>',$bcetak);?></td>
                    </tr>
                    <tr>
                        <td colspan="9" align="right">Grand Total :</td>
                        <td><input type="text" class="right bold" style="width: 150px;" id="TotalAmount" name="TotalAmount" value="<?php print($order->TotalAmount != null ? number_format($order->TotalAmount,0) : 0); ?>" disabled/></td>
                        <td class='center'><?php printf('<img src="%s" id="bKembali" alt="Daftar So" title="Kembali ke daftar P/O" style="cursor: pointer;"/>',$bkembali);?></td>
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
