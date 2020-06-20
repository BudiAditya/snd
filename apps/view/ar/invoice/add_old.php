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
    <script type="text/javascript">

        $(document).ready(function() {

            //var addmaster = ["CabangId", "InvoiceDate","ReceiptDate","CustomerId", "SalesName", "InvoiceDescs", "PaymentType","CreditTerms","btSubmit", "btKembali"];
            //BatchFocusRegister(addmaster);

            $("#InvoiceDate").customDatePicker({ showOn: "focus" });
            $("#FpDate").customDatePicker({ showOn: "focus" });

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
                ]],
                onSelect: function(index,row){
                    var csi = row.id;
                    console.log(csi);
                    var term = row.term;
                    console.log(term);
                    if (term > 0){
                        $("#PaymentType").val(1);
                        $("#CreditTerms").val(term);
                    }
                    var urz = "<?php print($helper->site_url('ar.order/getjson_solists/'.$userCabId.'/'));?>"+csi;
                    $('#aExSoId').combogrid('grid').datagrid('load',urz);
                }
            });

            $('#aExSoId').combogrid({
                panelWidth:300,
                url: "<?php print($helper->site_url('ar.order/getjson_solists/'.$userCabId));?>",
                idField:'id',
                textField:'so_no',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'so_no',title:'S/O No',width:50},
                    {field:'so_date',title:'Tanggal',width:30}
                ]],
                onSelect: function(index,row){
                    var idi = row.id;
                    console.log(idi);
                    var son = row.so_no;
                    console.log(son);
                    $("#ExSoId").val(idi);
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
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<div id="p" class="easyui-panel" title="Entry Invoice Penjualan" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <form id="frmMaster" action="<?php print($helper->site_url("ar.invoice/add")); ?>" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($invoice->CabangCode != null ? $invoice->CabangCode : $userCabCode); ?>" disabled/>
                    <input type="hidden" id="CabangId" name="CabangId" value="<?php print($invoice->CabangId == null ? $userCabId : $invoice->CabangId);?>"/>
                </td>
                <td>Tanggal</td>
                <td><input type="text" size="12" id="InvoiceDate" name="InvoiceDate" value="<?php print($invoice->FormatInvoiceDate(JS_DATE));?>" required/></td>
                <td>No. INV</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="InvoiceNo" name="InvoiceNo" value="<?php print($invoice->InvoiceNo != null ? $invoice->InvoiceNo : '-'); ?>" readonly/></td>
            </tr>
            <tr>
                <td>Customer</td>
                <td><input class="easyui-combogrid" id="CustomerId" name="CustomerId" style="width: 250px" value="<?php print($invoice->CustomerId);?>" required/></td>
                <td>Salesman</td>
                <td><select class="easyui-combobox" id="SalesId" name="SalesId" style="width: 150px">
                        <option value=""></option>
                        <?php
                        /** @var $sales Karyawan[]*/
                        foreach ($sales as $staf) {
                            if ($staf->Id == $invoice->SalesId) {
                                printf('<option value="%d" selected="selected">%s</option>', $staf->Id, $staf->Nama);
                            }else{
                                printf('<option value="%d">%s</option>', $staf->Id, $staf->Nama);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Status</td>
                <td><select class="easyui-combobox" id="InvoiceStatus1" name="InvoiceStatus1" style="width: 100px" disabled>
                        <option value="0" <?php print($invoice->InvoiceStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                        <option value="1" <?php print($invoice->InvoiceStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                        <option value="2" <?php print($invoice->InvoiceStatus == 2 ? 'selected="selected"' : '');?>>2 - Approved</option>
                        <option value="3" <?php print($invoice->InvoiceStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                    </select>
                    <input type="hidden" id="InvoiceStatus" name="InvoiceStatus" value="<?php print($invoice->InvoiceStatus);?>"/>
                </td>
                <td>Ex SO No.</td>
                <td><input class="easyui-combogrid" id="aExSoId" name="aExSoId" style="width: 150px"/>
                    <input type="hidden" id="ExSoId" name="ExSoId"/>
                </td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td><b><input type="text" class="f1 easyui-textbox" id="InvoiceDescs" name="InvoiceDescs" style="width: 250px" value="<?php print($invoice->InvoiceDescs != null ? $invoice->InvoiceDescs : '-'); ?>" required/></b></td>
                <td>Gudang</td>
                <td><select class="easyui-combobox" id="GudangId" name="GudangId" style="width: 150px" required>
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
                <td><select id="PaymentType" name="PaymentType" required>
                        <option value="1" <?php print($invoice->PaymentType == 1 ? 'selected="selected"' : '');?>>Kredit</option>
                        <option value="0" <?php print($invoice->PaymentType == 0 ? 'selected="selected"' : '');?>>Tunai</option>
                    </select>
                    <input type="text" id="CreditTerms" name="CreditTerms" size="2" maxlength="3" value="<?php print($invoice->CreditTerms != null ? $invoice->CreditTerms : 0); ?>" style="text-align: right" required/>
                    hari
                </td>
            </tr>
            <tr>
                <td>Expedisi</td>
                <td><select class="easyui-combobox" id="ExpeditionId" name="ExpeditionId" style="width: 250px">
                        <option value=""></option>
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
                <td><input type="text" size="12" id="FpDate" name="FpDate" value="<?php print($invoice->FormatFpDate(JS_DATE));?>"/></td>
                <td>NSF Pajak</td>
                <td><input type="text" class="f1 easyui-textbox" id="NsfPajak" name="NsfPajak" style="width: 150px" maxlength="50" value="<?php print($invoice->NsfPajak != null ? $invoice->NsfPajak : '-'); ?>"/></td>
            </tr>
            <tr>
                <td colspan="8" align="right">
                    <a id="btKembali" href="<?php print($helper->site_url("ar.invoice")); ?>" class="button">Kembali</a>
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
