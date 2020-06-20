<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Ubah Kategori Barang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $itemcategory ItemCategory */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Ubah Data Kategori Barang</span></legend>
	<form action="<?php print($helper->site_url("inventory.itemcategory/edit/".$itemcategory->Id)); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
            <tr>
                <td class="bold right"><label for="DivisionId">Divisi :</label></td>
                <td><select id="DivisionId" name="DivisionId" required>
                        <option value="0">--Pilih Divisi--</option>
                        <?php
                        /** @var $divisions ItemDivision[] */
                        foreach ($divisions as $divisi) {
                            if ($divisi->Id == $itemcategory->DivisionId) {
                                printf('<option value="%d" selected="selected">%s - %s - (%s)</option>', $divisi->Id, $divisi->EntityCode,$divisi->DivisionName,$divisi->DivisionCode);
                            } else {
                                printf('<option value="%d">%s - %s - (%s)</option>', $divisi->Id, $divisi->EntityCode,$divisi->DivisionName,$divisi->DivisionCode);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="bold right"><label for="CategoryCode">Kode :</label></td>
				<td><input type="text" id="CategoryCode" name="CategoryCode" value="<?php print($itemcategory->CategoryCode); ?>" size="30" required/></td>
			</tr>
			<tr>
				<td class="bold right"><label for="CategoryName">Kategori :</label></td>
				<td><input type="text" id="CategoryName" name="CategoryName" value="<?php print($itemcategory->CategoryName); ?>" size="50" required/></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit" class="button">UPDATE</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("inventory.itemcategory")); ?>" class="button">Batal</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
