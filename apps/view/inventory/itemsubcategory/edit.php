<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Ubah Sub-Kategori Barang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $itemsubcategory ItemSubCategory */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Ubah Data Sub-Kategori Barang</span></legend>
	<form action="<?php print($helper->site_url("inventory.itemsubcategory/edit/".$itemsubcategory->Id)); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
            <tr>
                <td class="bold right"><label for="CategoryId">Kategori :</label></td>
                <td><select id="CategoryId" name="CategoryId" required>
                        <option value="0">--Pilih Kategori--</option>
                        <?php
                        /** @var $categories ItemCategory[] */
                        foreach ($categories as $cat) {
                            if ($cat->Id == $itemsubcategory->CategoryId) {
                                printf('<option value="%d" selected="selected">%s - %s - (%s)</option>', $cat->Id, $cat->DivisionName,$cat->CategoryName,$cat->CategoryCode);
                            } else {
                                printf('<option value="%d">%s - %s - (%s)</option>', $cat->Id, $cat->DivisionName,$cat->CategoryName,$cat->CategoryCode);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="bold right"><label for="CategoryCode">Kode :</label></td>
				<td><input type="text" id="SubCategoryCode" name="SubCategoryCode" value="<?php print($itemsubcategory->SubCategoryCode); ?>" size="30" required/></td>
			</tr>
			<tr>
				<td class="bold right"><label for="SubCategoryName">Sub-Kategori :</label></td>
				<td><input type="text" id="SubCategoryName" name="SubCategoryName" value="<?php print($itemsubcategory->SubCategoryName); ?>" size="50" required/></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit" class="button">UPDATE</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("inventory.itemsubcategory")); ?>" class="button">Batal</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
