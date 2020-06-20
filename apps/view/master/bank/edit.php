<!DOCTYPE HTML>
<html>
<head>
    <title>SND System - Ubah Data Bank</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $bank Bank */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
    <legend><span class="bold">Ubah Data Bank</span></legend>
    <form action="<?php print($helper->site_url("master.bank/edit/".$bank->Id)); ?>" method="post">
        <table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
            <tr>
                <td class="bold right"><label for="BankCode">Kode :</label></td>
                <td><input type="text" id="BankCode" name="BankCode" value="<?php print($bank->BankCode); ?>" size="5" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="BankName">Nama Bank :</label></td>
                <td><input type="text" id="BankName" name="BankName" value="<?php print($bank->BankName); ?>" size="20" required/></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><button type="submit" class="button">UPDATE</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("master.bank")); ?>" class="button">Batal</a>
                </td>
            </tr>
        </table>
    </form>
</fieldset>
<!-- </body> -->
</html>
