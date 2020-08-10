<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Entry Pemakaian Barang</title>
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

            $("#IssueDate").customDatePicker({ showOn: "focus" });

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
                    $('#qStock').val(stock);
                    $('#ItemId').val(bid);
                    $('#ItemCode').val(bkode);
                    $('#ItemUom').val(satuan);
                    $('#Uom').html(satuan);
                    $('#Qty').focus();
                }
            });
        });

    </script>
</head>

<body>
<?php /** @var $issue Issue */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Entry Data Pemakaian Barang</span></legend>
	<form name="iForm" id="iForm" action="<?php print($helper->site_url("inventory.issue/add")); ?>" method="post" novalidate>
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
            <tr>
                <td class="bold right"><label for="IssueDate">Per Tanggal :</label></td>
                <td><input type="text" id="IssueDate" name="IssueDate" value="<?php print($issue->FormatIssueDate(JS_DATE)); ?>" size="10" required/></td>
            </tr>
			<tr>
				<td class="bold right"><label for="WarehouseId">Ex. Gudang :</label></td>
				<td colspan="2"><select class="bold" id="WarehouseId" name="WarehouseId" required>
                        <option value=""></option>
                        <?php
                        /** @var $whs Warehouse[] */
                        foreach ($whs as $gudang){
                            if ($gudang->Id == $issue->WarehouseId){
                                printf('<option value="%d" selected="selected">%s - %s</option>',$gudang->Id,$gudang->WhCode,$gudang->WhName);
                            }else {
                                printf('<option value="%d">%s - %s</option>', $gudang->Id, $gudang->WhCode, $gudang->WhName);
                            }
                        }
                        ?>
                        <input type="hidden" name="qStock" id="qStock" value="0"/>
                        <input type="hidden" name="ItemId" id="ItemId" value="<?php print($issue->ItemId); ?>"/>
                        <input type="hidden" name="ItemUom" id="ItemUom" value="<?php print($issue->ItemUom); ?>"/>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="ItemSearch">Nama Barang :</label></td>
                <td colspan="3"><input type="text" class="bold" name="ItemSearch" id="ItemSearch" style="width: 500px" value="<?=$issue->ItemId;?>" required/></td>
            </tr>
			<tr>
				<td class="bold right"><label for="ItemCode">Kode Barang :</label></td>
				<td><input type="text" class="bold" id="ItemCode" name="ItemCode" value="<?php print($issue->ItemCode); ?>" size="10" readonly/>
                    &nbsp;
                    <label for="Qty"><b>Qty :</b></label>
                    &nbsp;
                    <input type="text" class="right bold" id="Qty" name="Qty" value="<?php print($issue->Qty); ?>" size="5" required/>
                    &nbsp;
                    <span id="Uom"></span>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="DebetAccId">Beban Akun :</label></td>
                <td colspan="3"><select class="bold" id="DebetAccId" name="DebetAccId" required style="width: 500px">
                        <option value=""></option>
                        <?php
                        /** @var $coas CoaDetail[] */
                        foreach ($coas as $coa){
                            if ($coa->Id == $issue->DebetAccId){
                                printf('<option value="%d" selected="selected">%s - %s</option>',$coa->Id,$coa->Kode,$coa->Perkiraan);
                            }else {
                                printf('<option value="%d">%s - %s</option>', $coa->Id,$coa->Kode,$coa->Perkiraan);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="Keterangan">Keterangan :</label></td>
                <td><input type="text" name="Keterangan" id="Keterangan" style="width: 500px" value="<?=$issue->Keterangan;?>" required/></td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td colspan="3"><button type="submit" class="button">SIMPAN</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("inventory.issue")); ?>" class="button">KEMBALI</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
