<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Entry Koreksi Stock Barang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
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
    <script type="text/javascript">
        $(document).ready(function() {
            var url = null;

            $("#CorrDate").customDatePicker({ showOn: "focus" });

            $("#WarehouseId").change(function() {
                url = "<?php print($helper->site_url('inventory.stock/getitemstock_json/'));?>"+this.value;
                $('#ItemSearch').combogrid('grid').datagrid('load',url);
            });

            $('#ItemSearch').combogrid({
                panelWidth:500,
                url: url,
                idField:'item_id',
                textField:'item_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'item_code',title:'Kode',width:50},
                    {field:'item_name',title:'Nama Barang',width:150},
                    {field:'s_uom_code',title:'Satuan',width:40},
                    {field:'qty_stock',title:'Stock',width:40,align:'right'}
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
                    var stock = row.qty_stock;
                    console.log(stock);
                    $('#SysQty').val(stock);
                    $('#WhsQty').val(stock);
                    $('#ItemId').val(bid);
                    $('#ItemCode').val(bkode);
                    $('#ItemUom').val(satuan);
                    $('#WhsQty').focus();
                }
            });

            $("#WhsQty").change(function() {
                var sQty = $("#SysQty").val();
                var wQty = $("#WhsQty").val();
                var cQty = wQty - sQty;
                $("#CorrQty").val(cQty);
            });
        });

    </script>
</head>

<body>
<?php /** @var $correction Correction */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Entry Data Koreksi Stock Barang</span></legend>
	<form action="<?php print($helper->site_url("inventory.correction/add")); ?>" method="post" novalidate>
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="bold right"><label for="WarehouseId">Gudang :</label></td>
				<td colspan="3"><select class="bold" id="WarehouseId" name="WarehouseId" required>
                        <option value=""></option>
                        <?php
                        /** @var $whs Warehouse[] */
                        foreach ($whs as $gudang){
                            if ($gudang->Id == $correction->WarehouseId){
                                printf('<option value="%d" selected="selected">%s - %s</option>',$gudang->Id,$gudang->WhCode,$gudang->WhName);
                            }else {
                                printf('<option value="%d">%s - %s</option>', $gudang->Id, $gudang->WhCode, $gudang->WhName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td class="bold right"><label for="CorrDate">Per Tanggal :</label></td>
                <td><input type="text" id="CorrDate" name="CorrDate" value="<?php print($correction->FormatCorrDate(JS_DATE)); ?>" size="10" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="ItemSearch">Nama Barang :</label></td>
                <td colspan="5"><select class="bold" name="ItemSearch" id="ItemSearch" style="width: 500px" required>
                        <option value=""></option>
                    </select>
                    <input type="hidden" name="ItemId" id="ItemId" value="<?php print($correction->ItemId); ?>"/>
                </td>
            </tr>
			<tr>
				<td class="bold right"><label for="ItemCode">Kode Barang :</label></td>
				<td><input type="text" class="bold" id="ItemCode" name="ItemCode" value="<?php print($correction->ItemCode); ?>" size="10" readonly/></td>
                <td class="bold right"><label for="ItemUom">Satuan :</label></td>
                <td><input type="text" class="bold" id="ItemUom" name="ItemUom" value="<?php print($correction->ItemUom); ?>" size="10" readonly/></td>
			</tr>
            <tr>
                <td class="bold right"><label for="SysQty">Stock System :</label></td>
                <td><input type="text" class="right bold" id="SysQty" name="SysQty" value="<?php print($correction->SysQty); ?>" size="10" readonly/></td>
                <td class="bold right"><label for="WhsQty">Stock Riil :</label></td>
                <td><input type="text" class="right bold" id="WhsQty" name="WhsQty" value="<?php print($correction->WhsQty); ?>" size="10" required/></td>
                <td class="bold right"><label for="CorrQty">Koreksi :</label></td>
                <td><input type="text" class="right bold" id="CorrQty" name="CorrQty" value="<?php print($correction->CorrQty); ?>" size="10" readonly/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="CorrReason">Keterangan :</label></td>
                <td colspan="5"><input type="text" name="CorrReason" id="CorrReason" style="width: 500px" value="<?=$correction->CorrReason;?>" required/></td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td colspan="3"><button type="submit" class="button">SIMPAN</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("inventory.correction")); ?>" class="button">KEMBALI</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
