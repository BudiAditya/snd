<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Entry Divisi Barang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $itemdivisi ItemDivision */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Entry Data Divisi Barang</span></legend>
	<form action="<?php print($helper->site_url("inventory.itemdivision/add")); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
            <tr>
                <td class="bold right"><label for="EntityId">Entitas :</label></td>
                <td><select id="EntityId" name="EntityId" required>
                        <option value="0">--Pilih Entitas--</option>
                        <?php
                        /** @var $entities ItemEntity[] */
                        foreach ($entities as $eti) {
                            if ($eti->Id == $itemdivisi->EntityId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $eti->Id, $eti->EntityCode,$eti->EntityName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $eti->Id, $eti->EntityCode,$eti->EntityName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="bold right"><label for="DivisionCode">Kode Divisi :</label></td>
				<td><input type="text" id="DivisionCode" name="DivisionCode" value="<?php print($itemdivisi->DivisionCode); ?>" size="30" required/></td>
			</tr>
			<tr>
				<td class="bold right"><label for="DivisionName">Nama Divisi :</label></td>
				<td><input type="text" id="DivisionName" name="DivisionName" value="<?php print($itemdivisi->DivisionName); ?>" size="50" required/></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit" class="button">SIMPAN</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("inventory.itemdivision")); ?>" class="button">Batal</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
