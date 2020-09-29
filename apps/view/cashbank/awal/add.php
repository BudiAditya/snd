<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Entry Saldo Awal Kas/Bank</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // autoNumeric
            $(".num").autoNumeric({mDec: '2'});
        });
    </script>
</head>

<body>
<?php /** @var $saldoawal CbAwal */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Entry Saldo Awal Kas/Bank</span></legend>
	<form action="<?php print($helper->site_url("cashbank.awal/add")); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
            <tr>
                <td class="right"><label for="OpDate">Per Tanggal : </label></td>
                <td><input class="bold" type="text" name="OpDate" id="OpDate" size="12" value="<?=$saldoawal->OpDate;?>" readonly/></td>
            </tr>
            <tr>
                <td class="right"><label for="BankId">Kas/Bank : </label></td>
                <td><select id="BankId" name="BankId" required>
                        <option value="">-- PILIH KAS/BANK --</option>
                        <?php
                        /** @var $banks KasBank[] */
                        foreach ($banks as $bank) {
                            if ($bank->Id == $saldoawal->BankId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $bank->Id, $bank->BankName, $bank->TrxAccNo);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $bank->Id, $bank->BankName, $bank->TrxAccNo);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="right"><label for="OpAmount">Saldo Awal :</label></td>
                <td><input type="text" class="bold right num" id="OpAmount" name="OpAmount" value="<?php print($saldoawal->OpAmount); ?>" size="12" required/></td>
            </tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit" class="button">SIMPAN</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("cashbank.awal")); ?>" class="button">Kembali</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
