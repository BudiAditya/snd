<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Ubah Saldo Awal Piutang</title>
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
	<legend><span class="bold">Ubah Saldo Awal Piutang</span></legend>
	<form action="<?php print($helper->site_url("ar.saldoawal/edit/".$saldoawal->Id)); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
            <tr>
                <td class="right"><label for="OpDate">Per Tanggal : </label></td>
                <td><input class="bold" type="text" name="OpDate" id="OpDate" size="12" value="<?=$saldoawal->OpDate;?>" readonly/></td>
            </tr>
            <tr>
                <td class="right"><label for="CustomerId">Customer : </label></td>
                <td><select id="CustomerId" name="CustomerId" required>
                        <option value="">-- PILIH CUSTOMER --</option>
                        <?php
                        /** @var $customers Customer[] */
                        foreach ($customers as $customer) {
                            if ($customer->Id == $saldoawal->CustomerId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $customer->Id, $customer->CusCode, $customer->CusName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $customer->Id, $customer->CusCode, $customer->CusName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="right"><label for="OpAmount">Jumlah Piutang :</label></td>
                <td><input type="text" class="bold right" id="OpAmount" name="OpAmount" value="<?php print($saldoawal->OpAmount); ?>" size="12" required/></td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit" class="button">UPDATE</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("ar.saldoawal")); ?>" class="button">Kembali</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
