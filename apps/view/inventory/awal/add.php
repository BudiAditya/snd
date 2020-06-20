<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Entry Stock Awal Barang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            $("#OpDate").customDatePicker({ showOn: "focus" });

        });

    </script>
</head>

<body>
<?php /** @var $awal Awal */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Entry Data Stock Awal Barang</span></legend>
	<form action="<?php print($helper->site_url("inventory.awal/add")); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="bold right"><label for="WarehouseId">Gudang :</label></td>
				<td colspan="3"><select id="WarehouseId" name="WarehouseId" required>
                        <option value=""></option>
                        <?php
                        /** @var $whs Warehouse[] */
                        foreach ($whs as $gudang){
                            if ($gudang->Id == $awal->WarehouseId){
                                printf('<option value="%d" selected="selected">%s - %s</option>',$gudang->Id,$gudang->WhCode,$gudang->WhName);
                            }else {
                                printf('<option value="%d">%s - %s</option>', $gudang->Id, $gudang->WhCode, $gudang->WhName);
                            }
                        }
                        ?>
                    </select>
                </td>
			</tr>
            <tr>
                <td class="bold right"><label for="OpDate">Per Tanggal :</label></td>
                <td><input type="text" id="OpDate" name="OpDate" value="<?php print($awal->OpDate); ?>" size="12" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="ItemId">Nama Barang :</label></td>
                <td colspan="3"><select id="ItemId" name="ItemId" required>
                        <option value=""></option>
                        <?php
                        /** @var $items Items[] */
                        foreach ($items as $barang){
                            if ($barang->Id == $awal->ItemId){
                                printf('<option value="%d" selected="selected">%s - %s (%s)</option>',$barang->Id,$barang->ItemCode,$barang->ItemName,$barang->SuomCode);
                            }else {
                                printf('<option value="%d">%s - %s (%s)</option>', $barang->Id,$barang->ItemCode,$barang->ItemName,$barang->SuomCode);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="bold right"><label for="OpQty">Stock Awal :</label></td>
				<td><input type="text" class="right" id="OpQty" name="OpQty" value="<?php print($awal->OpQty); ?>" size="12" required/></td>
                <td class="bold right"><label for="Hpp">H P P :</label></td>
                <td><input type="text" class="right" id="Hpp" name="Hpp" value="<?php print($awal->Hpp); ?>" size="12" required/></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td colspan="3"><button type="submit" class="button">SIMPAN</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("inventory.awal")); ?>" class="button">Batal</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
