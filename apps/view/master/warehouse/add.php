<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Entry Data Gudang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function () {
                var elements = ["CabangId","WhCode", "WhName", "WhPic", "IsTrx", "WhStatus","Simpan"];
                BatchFocusRegister(elements);
        });
    </script>
</head>

<body>
<?php /** @var $warehouse Warehouse */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Entry Data Gudang</span></legend>
	<form action="<?php print($helper->site_url("master.warehouse/add")); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
            <tr>
                <td class="bold right"><label for="CabangId">Cabang :</label></td>
                <td>
                    <?php
                    if ($userLevel < 4){
                        printf('<input type="hidden" name="CabangId" id="CabangId" value="%d"/>',$cabangs->Id);
                        printf('<input type="text" name="CabName" id="CabName" value="%s" readonly/>',$cabangs->Kode);
                    }else{
                        /** @var $cabangs Cabang[] */
                        print('<select name="CabangId" id="CabangId" required>');
                        print('<option value=""></option>');
                        foreach ($cabangs as $CabList){
                            if ($CabList->Id == $warehouse->CabangId) {
                                printf('<option value="%d" selected="selected">%s</option>', $CabList->Id, $CabList->Kode);
                            }else{
                                printf('<option value="%d">%s</option>', $CabList->Id, $CabList->Kode);
                            }
                        }
                        print('</select>');
                    }
                    ?>
                </td>
            </tr>
			<tr>
				<td class="bold right"><label for="WhCode">Kode :</label></td>
				<td><input type="text" id="WhCode" name="WhCode" value="<?php print($warehouse->WhCode); ?>" size="15" required/></td>
			</tr>
            <tr>
                <td class="bold right"><label for="WhName">Gudang :</label></td>
                <td><input type="text" id="WhName" name="WhName" value="<?php print($warehouse->WhName); ?>" size="30" required/></td>
            </tr>
			<tr>
				<td class="bold right"><label for="WhPic">P I C :</label></td>
				<td><input type="text" id="WhPic" name="WhPic" value="<?php print($warehouse->WhPic); ?>" size="30" required/></td>
			</tr>
            <tr>
                <td class="bold right"><label for="IsTrx">Transaksional :</label></td>
                <td><select name="IsTrx" id="IsTrx" required>
                        <option value="1" <?php print($warehouse->IsTrx == 1 ? 'selected="selected"' : '');?>> 1 - Ya </option>
                        <option value="0" <?php print($warehouse->IsTrx == 0 ? 'selected="selected"' : '');?>> 0 - Tidak </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="WhStatus">Status :</label></td>
                <td><select name="WhStatus" id="WhStatus" required>
                        <option value="1" <?php print($warehouse->WhStatus == 1 ? 'selected="selected"' : '');?>> 1 - Aktif </option>
                        <option value="0" <?php print($warehouse->WhStatus == 0 ? 'selected="selected"' : '');?>> 0 - Non-Aktif </option>
                    </select>
                </td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td><button id="Simpan" type="submit" class="button">Simpan</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("master.warehouse")); ?>" class="button">Batal</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
