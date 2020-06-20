<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Edit Data Informasi Departemen</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			var elements = ["DeptCd", "DeptName"];
			BatchFocusRegister(elements);
		});
	</script>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>

<br />
<fieldset>
	<legend><b>Ubah Data Departemen</b></legend>
	<form id="frm" action="<?php print($helper->site_url("master.department/edit")); ?>" method="post">
		<input type="hidden" id="Id" name="Id" value="<?php print($dept->Id); ?>"/>
		<table cellpadding="2" cellspacing="1">
			<tr>
				<td>Company</td>
				<td><select name="CompanyId" class="text2" id="CompanyId">
					<option value=""></option>
					<?php
					foreach ($companies as $sbu) {
						if ($sbu->Id == $dept->CompanyId) {
							printf('<option value="%d" selected="selected">%s - %s</option>', $sbu->Id, $sbu->CompanyCode, $sbu->CompanyName);
						} else {
							printf('<option value="%d">%s - %s</option>', $sbu->Id, $sbu->CompanyCode, $sbu->CompanyName);
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td>Kode</td>
				<td><input type="text" class="text2" name="DeptCd" id="DeptCd" maxlength="5" size="5" value="<?php print($dept->DeptCd); ?>" /></td>
			</tr>
			<tr>
				<td>Nama Departemen</td>
				<td><input type="text" class="text2" name="DeptName" id="DeptName" maxlength="50" size="50" value="<?php print($dept->DeptName); ?>" /></td>
			</tr>
			<tr>
                <td>&nbsp;</td>
				<td>
					<button type="submit">Submit</button>
					<a href="<?php print($helper->site_url("master.department")); ?>" class="button">Daftar Departemen</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
