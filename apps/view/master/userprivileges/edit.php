<!DOCTYPE HTML>
<?php /** @var $userdata UserAdmin */  /** $privileges $userPrivileges[] */ ?>
<html>
<head>
	<title>SND System - Edit Data Hak Akses</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php include(VIEW . "main/menu.php"); ?>
<br/>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div>
<?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div>
<?php } ?>

<fieldset>
	<legend><b>Pengaturan Privileges Discount - User: <?php print(strtoupper($userdata->UserId).' ('.$userdata->UserName.') Cabang: '.$userdata->CabangKode);?></b></legend>
    <br>
	<form id="frm" action="<?php printf($helper->site_url('master.userprivileges/edit/%s'), $userdata->UserUid); ?>" method="post">
        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
            <tr>
                <th>No.</th>
                <th>Nama Module</th>
                <th>Module Path</th>
                <th>Disc1</th>
                <th>Disc2</th>
                <th>Disc3</th>
                <th>Disc4</th>
                <th>Disc5</th>
            </tr>
            <?php
                $nmr = 1;
                if ($issetup){
                    foreach($privileges as $hak){
                        print("<tr>");
                        printf("<td class='bold center'><input name='resourceId[]' type='hidden' value='%d'/>%s</td>",$hak->ResourceId,$nmr);
                        printf("<td class='bold'>%s</td>",$hak->ResourceName);
                        printf("<td class='bold'>%s</td>",$hak->ResourcePath);
                        printf("<td class='bold'><input name='mDl1[]' id='mDl1' size='5' style='text-align: right' value='%d'/></td>",$hak->Mdl1);
                        printf("<td class='bold'><input name='mDl2[]' id='mDl2' size='5' style='text-align: right' value='%d'/></td>",$hak->Mdl2);
                        printf("<td class='bold'><input name='mDl3[]' id='mDl3' size='5' style='text-align: right' value='%d'/></td>",$hak->Mdl3);
                        printf("<td class='bold'><input name='mDl4[]' id='mDl4' size='5' style='text-align: right' value='%d'/></td>",$hak->Mdl4);
                        printf("<td class='bold'><input name='mDl5[]' id='mDl5' size='5' style='text-align: right' value='%d'/></td>",$hak->Mdl5);
                        print("</tr>");
                        $nmr++;
                    }
                }else{
                    while($row = $resources->FetchAssoc()){
                        print("<tr>");
                        printf("<td class='bold center'><input name='resourceId[]' type='hidden' value='%d'/>%s</td>",$row["id"],$nmr);
                        printf("<td class='bold'>%s</td>",$row["resource_name"]);
                        printf("<td class='bold'>%s</td>",$row["resource_path"]);
                        print("<td class='bold'><input name='mDl1[]' id='mDl1' size='5' style='text-align: right' value='0'/></td>");
                        print("<td class='bold'><input name='mDl2[]' id='mDl2' size='5' style='text-align: right' value='0'/></td>");
                        print("<td class='bold'><input name='mDl3[]' id='mDl3' size='5' style='text-align: right' value='0'/></td>");
                        print("<td class='bold'><input name='mDl4[]' id='mDl4' size='5' style='text-align: right' value='0'/></td>");
                        print("<td class='bold'><input name='mDl5[]' id='mDl5' size='5' style='text-align: right' value='0'/></td>");
                        print("</tr>");
                        $nmr++;
                    }
                }
            ?>
            <tr>
                <td colspan="8" class="right bold">
                    <button type="submit">UPDATE</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("master.useradmin")); ?>" class="button">Daftar User</a>
                </td>
            </tr>
        </table>
	</form>
</fieldset>
<!-- </body> -->
</html>
