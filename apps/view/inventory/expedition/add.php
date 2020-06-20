<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Entry Data Expedisi</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $expedition Expedition */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Entry Data Data Expedisi</span></legend>
	<form action="<?php print($helper->site_url("inventory.expedition/add")); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="bold right"><label for="ExpCode">Kode :</label></td>
				<td><input type="text" id="ExpCode" name="ExpCode" value="<?php print($expedition->ExpCode); ?>" size="5" required/></td>
			</tr>
			<tr>
				<td class="bold right"><label for="ExpName">Nama :</label></td>
				<td><input type="text" id="ExpName" name="ExpName" value="<?php print($expedition->ExpName); ?>" size="50" required/></td>
			</tr>
            <tr>
                <td class="bold right"><label for="Address">Alamat :</label></td>
                <td><input type="text" id="Address" name="Address" value="<?php print($expedition->Address); ?>" size="50" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Cperson">P I C :</label></td>
                <td><input type="text" id="Cperson" name="Cperson" value="<?php print($expedition->Cperson); ?>" size="20" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Phone">Telephone :</label></td>
                <td><input type="text" id="Phone" name="Phone" value="<?php print($expedition->Phone); ?>" size="20" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Fax">Facsimile :</label></td>
                <td><input type="text" id="Fax" name="Fax" value="<?php print($expedition->Fax); ?>" size="20" required/></td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit" class="button">SIMPAN</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("inventory.expedition")); ?>" class="button">Batal</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
