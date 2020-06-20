<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>SND System - Ganti Password</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var elements = ["Old","New", "Retype", "Proses"];
            BatchFocusRegister(elements);
        });
    </script>
</head>

<body>

<?php
 if ($isFirst == 0) {
     include(VIEW . "main/menu.php");
 }
?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold">GANTI PASSWORD</span></legend>

	<form action="<?php print($helper->site_url("main/change_password/".$isFirst)); ?>" method="post">
		<table cellpadding="0" cellspacing="0" class="tablePadding">
			<tr>
				<td><label for="Old">Password Lama :</label></td>
				<td><input type="password" id="Old" name="Old" required /></td>
			</tr>
			<tr>
				<td><label for="New">Password Baru :</label></td>
				<td><input type="password" id="New" name="New" /></td>
			</tr>
			<tr>
				<td><label for="Retype">Password Baru :</label></td>
				<td><input type="password" id="Retype" name="Retype"></td>
			</tr>
			<tr>
                <td>&nbsp;</td>
				<td>
					<button type="submit" id="Proses">PROSES</button>
                    &nbsp;
                    <?php
                    if ($isFirst == 1) {
                        printf('<a href = "%s" type = "button" > BATAL</a >',$helper->site_url("main/batalgantipassword/1"));
                    }else{
                        printf('<a href = "%s" type = "button" > BATAL</a >',$helper->site_url("main"));
                    }
                    ?>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

<!-- </body> -->
</html>
