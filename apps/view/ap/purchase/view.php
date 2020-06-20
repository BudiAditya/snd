<!DOCTYPE HTML>
<html>
<?php
/** @var $purchase Purchase */
?>
<head>
    <title>SND System - View Pembelian/Penerimaan Barang</title>
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
                ]]
            });

            $("#bTambah").click(function(){
                $.messager.confirm('Confirm','Buat Data Pembelian baru?',function(r) {
                    if (r) {
                        location.href="<?php print($helper->site_url("ap.purchase/add")); ?>";
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
<div id="p" class="easyui-panel" title="View Pembelian/Penerimaan Barang" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td>Cabang</td>
            <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($purchase->CabangCode != null ? $purchase->CabangCode : $userCabCode); ?>" disabled/>
                <input type="hidden" id="CabangId" name="CabangId" value="<?php print($purchase->CabangId == null ? $userCabId : $purchase->CabangId);?>"/>
            </td>
            <td>Tanggal</td>
            <td><input type="text" class="easyui-datebox" id="GrnDate" name="GrnDate" style="width: 150px" value="<?php print($purchase->FormatGrnDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser" disabled/></td>
            <td>Diterima</td>
            <td><input type="text" class="easyui-datebox" id="ReceiptDate" name="ReceiptDate" style="width: 105px" value="<?php print($purchase->FormatReceiptDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser" disabled/></td>
            <td>No. GRN</td>
            <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 150px" id="GrnNo" name="GrnNo" value="<?php print($purchase->GrnNo != null ? $purchase->GrnNo : '-'); ?>" disabled/></td>
        </tr>
        <tr>
            <td>Supplier</td>
            <td><input class="easyui-combogrid" id="SupplierId" name="SupplierId" style="width: 250px" value="<?php print($purchase->SupplierId);?>" disabled/></td>
            <td>Salesman</td>
            <td><b><input type="text" class="easyui-textbox" id="SalesName" name="SalesName" style="width: 150px" maxlength="50" value="<?php print($purchase->SalesName != null ? $purchase->SalesName : '-'); ?>" disabled/></b></td>
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
            <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 150px" id="ExPoNo" name="ExPoNo" value="<?php print($purchase->ExPoNo != null ? $purchase->ExPoNo : '-'); ?>" disabled/></td>
        </tr>
        <tr>
            <td>Expedisi</td>
            <td><select class="easyui-combobox" id="ExpeditionId" name="ExpeditionId" style="width: 250px" disabled>
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
                <select class="easyui-combobox" id="GudangId" name="GudangId" style="width: 150px" disabled>
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
            <td><select class="easyui-combobox" id="PaymentType" name="PaymentType" style="width: 70px" disabled>
                    <option value="1" <?php print($purchase->PaymentType == 1 ? 'selected="selected"' : '');?>>Kredit</option>
                    <option value="0" <?php print($purchase->PaymentType == 0 ? 'selected="selected"' : '');?>>Tunai</option>
                </select>
                <input type="text" class="easyui-numberbox" id="CreditTerms" name="CreditTerms" style="width: 30px" value="<?php print($purchase->CreditTerms != null ? $purchase->CreditTerms : 0); ?>" style="text-align: right" disabled/>
                hr
            </td>
            <td>Tgl JTP</td>
            <td><input type="text" class="easyui-datebox" id="JtpDate" name="JtpDate" style="width: 150px" value="<?php print($purchase->FormatJtpDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser" disabled/></td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td><b><input type="text" class="easyui-textbox" id="GrnDescs" name="GrnDescs" style="width: 250px" value="<?php print($purchase->GrnDescs != null ? $purchase->GrnDescs : '-'); ?>" disabled/></b></td>
            <td>No. Invoice</td>
            <td><input type="text" class="easyui-textbox" id="SupInvNo" name="SupInvNo" style="width: 150px" maxlength="50" value="<?php print($purchase->SupInvNo != null ? $purchase->SupInvNo : '-'); ?>" disabled/></td>
            <td>Tgl Invoice</td>
            <td><input type="text" class="easyui-datebox" id="SupInvDate" name="SupInvDate" style="width: 105px" value="<?php print($purchase->FormatSupInvDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser" disabled/></td>
            <td>NSF Pajak</td>
            <td><input type="text" class="easyui-textbox" id="NsfPajak" name="NsfPajak" style="width: 150px" maxlength="50" value="<?php print($purchase->NsfPajak != null ? $purchase->NsfPajak : '-'); ?>" disabled/></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="10">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma; width: 100%;">
                    <tr>
                        <th colspan="13">DETAIL BARANG YANG DIBELI/DITERIMA</th>
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
                    </tr>
                    <tr>
                        <td colspan="13" nowrap="nowrap" class="right"><?php
                            if ($acl->CheckUserAccess("ap.purchase", "add")) {
                                printf('<img src="%s" alt="GRN Baru" title="Buat invoice baru" id="bTambah" style="cursor: pointer;"/>', $baddnew);
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
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- </body> -->
</html>
