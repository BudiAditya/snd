<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Entry Merk Barang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $itembrand ItemBrand */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Entry Data Brand Barang</span></legend>
	<form action="<?php print($helper->site_url("inventory.itembrand/add")); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
            <tr>
                <td class="bold right"><label for="EntityId">Entitas :</label></td>
                <td><select id="EntityId" name="EntityId" required>
                        <option value="0">--Pilih Entitas--</option>
                        <?php
                        /** @var $entities ItemEntity[] */
                        foreach ($entities as $eti) {
                            if ($eti->Id == $itembrand->EntityId) {
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
                <td class="bold right"><label for="SupplierId">Principal :</label></td>
                <td><select id="SupplierId" name="SupplierId" required>
                        <option value="0">--Pilih Principal--</option>
                        <?php
                        /** @var $principals Supplier[] */
                        foreach ($principals as $principal) {
                            if ($principal->Id == $itembrand->SupplierId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $principal->Id, $principal->SupCode,$principal->SupName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $principal->Id, $principal->SupCode,$principal->SupName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="bold right"><label for="BrandCode">Kode Brand :</label></td>
				<td><input type="text" id="BrandCode" name="BrandCode" value="<?php print($itembrand->BrandCode); ?>" size="30" required/></td>
			</tr>
			<tr>
				<td class="bold right"><label for="BrandName">Nama Brand :</label></td>
				<td><input type="text" id="BrandName" name="BrandName" value="<?php print($itembrand->BrandName); ?>" size="50" required/></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit" class="button">SIMPAN</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("inventory.itembrand")); ?>" class="button">Batal</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
