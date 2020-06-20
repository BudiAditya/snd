<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Entry Type Supplier</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $suptype SupType */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Entry Data Type Supplier</span></legend>
	<form action="<?php print($helper->site_url("ap.suptype/add")); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="bold right"><label for="TypeCode">Kode :</label></td>
				<td><input type="text" id="TypeCode" name="TypeCode" value="<?php print($suptype->TypeCode); ?>" size="5" required/></td>
			</tr>
			<tr>
				<td class="bold right"><label for="TypeName">Type :</label></td>
				<td><input type="text" id="TypeName" name="TypeName" value="<?php print($suptype->TypeName); ?>" size="20" required/></td>
			</tr>
			<tr>
				<td class="bold right"><label for="ApAccId">Akun :</label></td>
				<td><select id="ApAccId" name="ApAccId" style="width: 300px;" required>
						<option value="0">--Pilih Akun Hutang--</option>
						<?php
						foreach ($ivtcoa as $coa) {
							if ($coa->Id == $suptype->ApAccId) {
								printf('<option value="%d" selected="selected">%s - %s</option>', $coa->Id, $coa->Kode,$coa->Perkiraan);
							} else {
								printf('<option value="%d">%s - %s</option>', $coa->Id, $coa->Kode,$coa->Perkiraan);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit" class="button">Simpan</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("ap.suptype")); ?>" class="button">Batal</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
