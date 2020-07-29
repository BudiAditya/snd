<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Ubah Data Salesman</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $salesman Salesman */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Ubah Data Salesman</span></legend>
	<form action="<?php print($helper->site_url("master.salesman/edit/".$salesman->Id)); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="bold right"><label for="SalesCode">Kode :</label></td>
				<td><input type="text" id="SalesCode" name="SalesCode" value="<?php print($salesman->SalesCode); ?>" size="5" required/></td>
			</tr>
            <tr>
                <td class="bold right"><label for="SalesName">Nama Lengkap :</label></td>
                <td><input type="text" id="SalesName" name="SalesName" value="<?php print($salesman->SalesName); ?>" size="20" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="FirstName">Nama Depan :</label></td>
                <td><input type="text" id="FirstName" name="FirstName" value="<?php print($salesman->FirstName); ?>" size="20" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="LastName">Nama Belakang :</label></td>
                <td><input type="text" id="LastName" name="LastName" value="<?php print($salesman->LastName); ?>" size="20" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="JobTitle">Jabatan :</label></td>
                <td><input type="text" id="JobTitle" name="JobTitle" value="<?php print($salesman->JobTitle); ?>" size="20" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="IsCastrol">Sales Castrol :</label></td>
                <td><select id="IsCastrol" name="IsCastrol" required style="width: 100px">
                        <option value="1" <?php print($salesman->IsCastrol == 1 ? 'selected="selected"' : '');?>>1 - Ya</option>
                        <option value="0" <?php print($salesman->IsCastrol == 0 ? 'selected="selected"' : '');?>>0 - Tidak</option>
                    </select>
                    <input type="hidden" id="TrxId" name="TrxId" value="<?=$salesman->TrxId;?>"/>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="IsAktif">Status :</label></td>
                <td><select id="IsAktif" name="IsAktif" required style="width: 100px">
                        <option value="1" <?php print($salesman->IsAktif == 1 ? 'selected="selected"' : '');?>>1 - Aktif</option>
                        <option value="0" <?php print($salesman->IsAktif == 0 ? 'selected="selected"' : '');?>>0 - Non-Aktif</option>
                    </select>
                </td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit" class="button">UPDATE</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("master.salesman")); ?>" class="button">Batal</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
