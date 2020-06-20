<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Entry Data Hari Libur</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

    <script type="text/javascript">
        $(document).ready(function () {
                var elements = ["TglLibur", "JnsLibur", "Keterangan", "Simpan"];
                BatchFocusRegister(elements);
        });

        //date formating
        function myformatter(date){
            var y = date.getFullYear();
            var m = date.getMonth()+1;
            var d = date.getDate();
            return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
        }
        function myparser(s){
            if (!s) return new Date();
            var ss = (s.split('-'));
            var y = parseInt(ss[0],10);
            var m = parseInt(ss[1],10);
            var d = parseInt(ss[2],10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
                return new Date(y,m-1,d);
            } else {
                return new Date();
            }
        }
    </script>
</head>

<body>
<?php /** @var $libur Libur */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Entry Data Hari Libur</span></legend>
	<form action="<?php print($helper->site_url("master.libur/add")); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
			<tr>
				<td class="bold right"><label for="TglLibur">Tanggal :</label></td>
                <td><input type="text" class="easyui-datebox" size="10" id="TglLibur" name="TglLibur" value="<?php print($libur->FormatTglLibur(SQL_DATEONLY));?>" required data-options="formatter:myformatter,parser:myparser"/></td>
			</tr>
            <tr>
                <td class="bold right"><label for="JnsLibur">Jenis Libur :</label></td>
                <td><select name="JnsLibur" id="JnsLibur" required>
                        <option value="1" <?php print($libur->JnsLibur == 1 ? 'selected="selected"' : '');?>> 1 - Nasional </option>
                        <option value="2" <?php print($libur->JnsLibur == 2 ? 'selected="selected"' : '');?>> 2 - Perusahaan </option>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="bold right"><label for="Keterangan">Keterangan :</label></td>
				<td><input type="text" id="Keterangan" name="Keterangan" value="<?php print($libur->Keterangan); ?>" size="50" required/></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button id="Simpan" type="submit" class="button">SIMPAN</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("master.libur")); ?>" class="button">Batal</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
