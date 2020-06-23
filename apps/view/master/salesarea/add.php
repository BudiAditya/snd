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
				<td class="bold right"><label for="AreaCode">Kode :</label></td>
				<td><input type="text" id="AreaCode" name="AreaCode" value="<?php print($salesarea->AreaCode); ?>" style="width: 100px" required/></td>
			</tr>
			<tr>
				<td class="bold right"><label for="AreaName">Kota/Area :</label></td>
				<td><input type="text" id="AreaName" name="AreaName" value="<?php print($salesarea->AreaName); ?>" style="width: 200px" required/></td>
			</tr>
			<tr>
				<td class="bold right"><label for="PropId">Propinsi :</label></td>
				<td><select id="PropId" name="PropId" required style="width: 200px">
						<option value="0"></option>
						<?php
                        if ($proplist != null) {
                            while ($row = $proplist->FetchAssoc()) {
                                if ($row["id"] == $salesarea->PropId) {
                                    printf('<option value="%d" selected="selected">%s - %s</option>', $row["id"], $row["prop_code"],$row["prop_name"]);
                                } else {
                                    printf('<option value="%d">%s - %s</option>', $row["id"], $row["prop_code"],$row["prop_name"]);
                                }
                            }
                        }
						?>
					</select>
				</td>
			</tr>
            <tr>
                <td class="bold right"><label for="CabangId">Cabang :</label></td>
                <td><select id="CabangId" name="CabangId" required style="width: 200px">
                        <option value="0"></option>
                        <?php
                        /** @var $cabangs Cabang[] */
                        if ($cabangs != null) {
                            foreach ($cabangs as $cab){
                                if ($cab->Id == $salesarea->CabangId) {
                                    printf('<option value="%d" selected="selected">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                                } else {
                                    printf('<option value="%d">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                                }
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="ZoneId">Zona Harga :</label></td>
                <td><select id="ZoneId" name="ZoneId" required style="width: 200px">
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
				<td><button type="submit" class="button">SIMPAN</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("master.salesarea")); ?>" class="button">KEMBALI</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
