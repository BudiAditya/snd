<!DOCTYPE HTML>
<html>
<head>
    <title>SND System - Entry Saldo Awal Hutang</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $saldoawal SaldoAwal */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
    <legend><span class="bold">Entry Saldo Awal Hutang</span></legend>
    <form action="<?php print($helper->site_url("ap.saldoawal/edit/".$saldoawal->Id)); ?>" method="post">
        <table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
            <tr>
                <td class="right"><label for="OpDate">Per Tanggal : </label></td>
                <td><input class="bold" type="text" name="OpDate" id="OpDate" size="12" value="<?=$saldoawal->OpDate;?>" readonly/></td>
            </tr>
            <tr>
                <td class="right"><label for="SupplierId">Supplier : </label></td>
                <td><select id="SupplierId" name="SupplierId" required>
                        <option value="">-- PILIH SUPPLIER --</option>
                        <?php
                        /** @var $suppliers Supplier[] */
                        foreach ($suppliers as $supplier) {
                            if ($supplier->Id == $saldoawal->SupplierId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $supplier->Id, $supplier->SupCode, $supplier->SupName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $supplier->Id, $supplier->SupCode, $supplier->SupName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="right"><label for="OpAmount">Jumlah Hutang :</label></td>
                <td><input type="text" class="bold right" id="OpAmount" name="OpAmount" value="<?php print($saldoawal->OpAmount); ?>" size="12" required/></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><button type="submit" class="button">UPDATE</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("ap.saldoawal")); ?>" class="button">Kembali</a>
                </td>
            </tr>
        </table>
    </form>
</fieldset>
<!-- </body> -->
</html>
