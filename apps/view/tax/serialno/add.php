<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Entry S/N Faktur Pajak Keluaran</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $serialno SerialNo */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Entry Data S/N Faktur Pajak Keluaran</span></legend>
	<form action="<?php print($helper->site_url("tax.serialno/add")); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="bold right"><label for="TaxYear">Tahun Pajak :</label></td>
				<td><input type="text" id="TaxYear" name="TaxYear" value="<?php print($serialno->TaxYear); ?>" size="5" maxlength="4" required/></td>
				<td class="bold right"><label for="SnPrefix">Prefix :</label></td>
				<td><input type="text" id="SnPrefix" name="SnPrefix" value="<?php print($serialno->SnPrefix); ?>" size="5" maxlength="5" required/></td>
			</tr>
            <tr>
                <td class="bold right"><label for="SnStart">Mulai No. :</label></td>
                <td><input type="text" id="SnStart" name="SnStart" value="<?php print($serialno->SnStart); ?>" size="10" maxlength="8" required/></td>
                <td class="bold right"><label for="SnEnd">Sampai No. :</label></td>
                <td><input type="text" id="SnEnd" name="SnEnd" value="<?php print($serialno->SnEnd); ?>" size="10" maxlength="8" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="SnNextCounter">No. Berikut :</label></td>
                <td><input type="text" id="SnNextCounter" name="SnNextCounter" value="<?php print($serialno->SnNextCounter); ?>" size="10" maxlength="8" required/></td>
                <td class="bold right"><label for="IsAktif">Status :</label></td>
                <td><select name="IsAktif" id="IsAktif" required>
                        <option value="0" <?php print($serialno->IsAktif == 0 ? 'selected="selected"' : '');?>>0 - Non-Aktif</option>
                        <option value="1" <?php print($serialno->IsAktif == 1 ? 'selected="selected"' : '');?>>1 - Aktif</option>
                    </select>
                </td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td colspan="3"><button type="submit" class="button">SIMPAN</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("tax.serialno")); ?>" class="button">BATAL</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
