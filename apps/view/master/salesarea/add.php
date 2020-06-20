<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Entry Sales Area</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $salesarea SalesArea */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Entry Data Sales Area</span></legend>
	<form action="<?php print($helper->site_url("master.salesarea/add")); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="bold right"><label for="AreaCode">Kode Area :</label></td>
				<td><input type="text" id="AreaCode" name="AreaCode" value="<?php print($salesarea->AreaCode); ?>" size="5" required/></td>
			</tr>
			<tr>
				<td class="bold right"><label for="AreaName">Nama Area :</label></td>
				<td><input type="text" id="AreaName" name="AreaName" value="<?php print($salesarea->AreaName); ?>" size="20" required/></td>
			</tr>
			<tr>
				<td class="bold right"><label for="CityId">Nama Kota :</label></td>
				<td><select id="CityId" name="CityId" required>
						<option value="0"></option>
						<?php
                        if ($citylist != null) {
                            while ($row = $citylist->FetchAssoc()) {
                                if ($row["id"] == $salesarea->CityId) {
                                    printf('<option value="%d" selected="selected">%s - %s</option>', $row["id"], $row["city_name"],$row["prop_name"]);
                                } else {
                                    printf('<option value="%d">%s - %s</option>', $row["id"], $row["city_name"],$row["prop_name"]);
                                }
                            }
                        }
						?>
					</select>
				</td>
			</tr>
            <tr>
                <td class="bold right"><label for="ZoneId">Zona Harga :</label></td>
                <td><select id="ZoneId" name="ZoneId" required>
                        <option value="0"></option>
                        <?php
                        if ($zonelist != null) {
                            while ($row = $zonelist->FetchAssoc()) {
                                if ($row["id"] == $salesarea->ZoneId) {
                                    printf('<option value="%d" selected="selected">%s - %s</option>', $row["id"], $row["code"],$row["name"]);
                                } else {
                                    printf('<option value="%d">%s - %s</option>', $row["id"], $row["code"],$row["name"]);
                                }
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
                    <a href="<?php print($helper->site_url("master.salesarea")); ?>" class="button">Batal</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
