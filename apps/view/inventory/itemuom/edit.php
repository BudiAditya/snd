<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Ubah Satuan Barang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $itemuom ItemUom */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Ubah Data Satuan Barang</span></legend>
	<form action="<?php print($helper->site_url("inventory.itemuom/edit/".$itemuom->Id)); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="bold right"><label for="UomCode">Kode :</label></td>
				<td><input type="text" id="UomCode" name="UomCode" value="<?php print($itemuom->UomCode); ?>" size="5" required/></td>
			</tr>
			<tr>
				<td class="bold right"><label for="UomName">Satuan :</label></td>
				<td><input type="text" id="UomName" name="UomName" value="<?php print($itemuom->UomName); ?>" size="10" required/></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
                <td><button type="submit" class="button">UPDATE</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("inventory.itemuom")); ?>" class="button">Batal</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
