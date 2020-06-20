<!DOCTYPE HTML>
<html>
<?php
/** @var $purchase Purchase */
?>
<head>
	<title>SND System - Entry Pembelian/Penerimaan Barang</title>
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

        $(document).ready(function() {

            //var addmaster = ["CabangId", "GrnDate","ReceiptDate","SupplierId", "SalesName", "GrnDescs", "PaymentType","CreditTerms","btSubmit", "btKembali"];
            //BatchFocusRegister(addmaster);

            $("#GrnDate").customDatePicker({ showOn: "focus" });
            $("#ReceiptDate").customDatePicker({ showOn: "focus" });
            $("#SupInvDate").customDatePicker({ showOn: "focus" });
            $("#JtpDate").customDatePicker({ showOn: "focus" });

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
                    var urz = "<?php print($helper->site_url('ap.order/getjson_polists/'.$userCabId.'/'));?>"+spi;
                    $('#aExPoId').combogrid('grid').datagrid('load',urz);
                }
            });

            $('#aExPoId').combogrid({
                panelWidth:300,
                url: "<?php print($helper->site_url('ap.order/getjson_polists/'.$userCabId));?>",
                idField:'id',
                textField:'po_no',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'po_no',title:'P/O No',width:50},
                    {field:'po_date',title:'Tanggal',width:30}
                ]],
                onSelect: function(index,row){
                    var idi = row.id;
                    console.log(idi);
                    var pon = row.po_no;
                    console.log(pon);
                    $("#ExPoId").val(idi);
                }
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
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<div id="p" class="easyui-panel" title="Entry Pembelian/Penerimaan Barang" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <form id="frmMaster" action="<?php print($helper->site_url("ap.purchase/add")); ?>" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($purchase->CabangCode != null ? $purchase->CabangCode : $userCabCode); ?>" disabled/>
                    <input type="hidden" id="CabangId" name="CabangId" value="<?php print($purchase->CabangId == null ? $userCabId : $purchase->CabangId);?>"/>
                </td>
                <td>Tanggal</td>
                <td><input type="text" size="12" id="GrnDate" name="GrnDate" value="<?php print($purchase->FormatGrnDate(JS_DATE));?>" required/></td>
                <td>Diterima</td>
                <td><input type="text" size="12" id="ReceiptDate" name="ReceiptDate" value="<?php print($purchase->FormatReceiptDate(JS_DATE));?>" /></td>
                <td>No. GRN</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="GrnNo" name="GrnNo" value="<?php print($purchase->GrnNo != null ? $purchase->GrnNo : '-'); ?>" readonly/></td>
            </tr>
            <tr>
                <td>Supplier</td>
                <td><input class="easyui-combogrid" id="SupplierId" name="SupplierId" style="width: 250px" value="<?php print($purchase->SupplierId);?>" required/></td>
                <td>Salesman</td>
                <td><b><input type="text" class="f1 easyui-textbox" id="SalesName" name="SalesName" style="width: 150px"  maxlength="50" value="<?php print($purchase->SalesName != null ? $purchase->SalesName : '-'); ?>"/></b></td>
                <td>Status</td>
                <td><select class="easyui-combobox" id="GrnStatus1" name="GrnStatus1" style="width: 100px" disabled>
                        <option value="0" <?php print($purchase->GrnStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                        <option value="1" <?php print($purchase->GrnStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                        <option value="2" <?php print($purchase->GrnStatus == 2 ? 'selected="selected"' : '');?>>2 - Approved</option>
                        <option value="3" <?php print($purchase->GrnStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                    </select>
                    <input type="hidden" id="GrnStatus" name="GrnStatus" value="<?php print($purchase->GrnStatus);?>"/>
                </td>
                <td>Ex PO No.</td>
                <td><input class="easyui-combogrid" id="aExPoId" name="aExPoId" style="width: 150px"/>
                    <input type="hidden" id="ExPoId" name="ExPoId"/>
                </td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td><b><input type="text" class="f1 easyui-textbox" id="GrnDescs" name="GrnDescs" style="width: 250px" value="<?php print($purchase->GrnDescs != null ? $purchase->GrnDescs : '-'); ?>" required/></b></td>
                <td>Gudang</td>
                <td><select id="GudangId" name="GudangId" style="width: 150px" required>
                        <option value="">- Pilih Gudang -</option>
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
                <td><select id="PaymentType" name="PaymentType" required>
                        <option value="1" <?php print($purchase->PaymentType == 1 ? 'selected="selected"' : '');?>>Kredit</option>
                        <option value="0" <?php print($purchase->PaymentType == 0 ? 'selected="selected"' : '');?>>Tunai</option>
                    </select>
                    <input type="text" id="CreditTerms" name="CreditTerms" size="2" maxlength="3" value="<?php print($purchase->CreditTerms != null ? $purchase->CreditTerms : 0); ?>" style="text-align: right" required/>
                    hari
                </td>
                <td>Tgl JTP</td>
                <td><input type="text" size="12" id="JtpDate" name="JtpDate" value="<?php print($purchase->FormatJtpDate(JS_DATE));?>"/></td>
            </tr>
            <tr>
                <td>Expedisi</td>
                <td><select id="ExpeditionId" name="ExpeditionId" style="width: 250px">
                        <option value=""></option>
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
                <td>No. Invoice</td>
                <td><input type="text" class="f1 easyui-textbox" id="SupInvNo" name="SupInvNo" style="width: 150px" maxlength="50" value="<?php print($purchase->SupInvNo != null ? $purchase->SupInvNo : '-'); ?>"/></td>
                <td>Tgl Invoice</td>
                <td><input type="text" size="12" id="SupInvDate" name="SupInvDate" value="<?php print($purchase->FormatSupInvDate(JS_DATE));?>"/></td>
                <td>NSF Pajak</td>
                <td><input type="text" class="f1 easyui-textbox" id="NsfPajak" name="NsfPajak" style="width: 150px" maxlength="50" value="<?php print($purchase->NsfPajak != null ? $purchase->NsfPajak : '-'); ?>"/></td>
            </tr>
            <tr>
                <td colspan="8" align="right">
                    <a id="btKembali" href="<?php print($helper->site_url("ap.purchase")); ?>" class="button">Kembali</a>
                    <button id="btSubmit" type="submit" class="button">Berikutnya &gt;</button>
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- </body> -->
</html>
