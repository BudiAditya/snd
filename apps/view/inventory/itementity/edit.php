<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Ubah Entitas Barang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $itementity ItemEntity */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Ubah Data Entitas Barang</span></legend>
	<form action="<?php print($helper->site_url("inventory.itementity/edit/".$itementity->Id)); ?>" method="post">
        <table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
            <tr>
                <td class="bold right"><label for="EntityCode">Kode Entitas :</label></td>
                <td><input type="text" id="EntityCode" name="EntityCode" value="<?php print($itementity->EntityCode); ?>" size="5" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="EntityName">Nama Entitas :</label></td>
                <td><input type="text" id="EntityName" name="EntityName" value="<?php print($itementity->EntityName); ?>" size="20" required/></td>
                <td class="bold right"><label for="ChartColor">Chart Color :</label></td>
                <td><input type="color" id="ChartColor" name="ChartColor" value="<?php print($itementity->ChartColor); ?>"/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="IvtAccId">Akun Persediaan :</label></td>
                <td colspan="3"><select id="IvtAccId" name="IvtAccId" style="width: 300px;" required>
                        <option value="0">--Pilih Akun Persediaan--</option>
                        <?php
                        foreach ($ivtcoa as $coakredit) {
                            if ($coakredit->Id == $itementity->IvtAccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="RevAccId">Akun Pendapatan :</label></td>
                <td colspan="3"><select id="RevAccId" name="RevAccId" style="width: 300px;" required>
                        <option value="0">--Pilih Akun Pendapatan--</option>
                        <?php
                        foreach ($ivtcoa as $coakredit) {
                            if ($coakredit->Id == $itementity->RevAccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="ArAccId">Akun Piutang :</label></td>
                <td colspan="3"><select id="ArAccId" name="ArAccId" style="width: 300px;" required>
                        <option value="0">--Pilih Akun Piutang--</option>
                        <?php
                        foreach ($ivtcoa as $coakredit) {
                            if ($coakredit->Id == $itementity->ArAccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="ApAccId">Akun Hutang :</label></td>
                <td colspan="3"><select id="ApAccId" name="ApAccId" style="width: 300px;" required>
                        <option value="0">--Pilih Akun Hutang--</option>
                        <?php
                        foreach ($ivtcoa as $coakredit) {
                            if ($coakredit->Id == $itementity->ApAccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="HppAccId">Akun HPP :</label></td>
                <td colspan="3"><select id="HppAccId" name="HppAccId" style="width: 300px;" required>
                        <option value="0">--Pilih Akun HPP--</option>
                        <?php
                        foreach ($ivtcoa as $coakredit) {
                            if ($coakredit->Id == $itementity->HppAccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="SlsDiscAccId">Akun Diskon Penjualan :</label></td>
                <td colspan="3"><select id="SlsDiscAccId" name="SlsDiscAccId" style="width: 300px;" required>
                        <option value="0">--Pilih Akun Diskon--</option>
                        <?php
                        foreach ($ivtcoa as $coakredit) {
                            if ($coakredit->Id == $itementity->SlsDiscAccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="PrcDiscAccId">Akun Diskon Pembelian :</label></td>
                <td colspan="3"><select id="PrcDiscAccId" name="PrcDiscAccId" style="width: 300px;" required>
                        <option value="0">--Pilih Akun Diskon--</option>
                        <?php
                        foreach ($ivtcoa as $coakredit) {
                            if ($coakredit->Id == $itementity->PrcDiscAccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="RetSlsAccId">Akun Retur Penjualan :</label></td>
                <td colspan="3"><select id="RetSlsAccId" name="RetSlsAccId" style="width: 300px;" required>
                        <option value="0">--Pilih Akun Retur--</option>
                        <?php
                        foreach ($ivtcoa as $coakredit) {
                            if ($coakredit->Id == $itementity->RetSlsAccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="RetSlsAccId">Akun Retur Pembelian :</label></td>
                <td colspan="3"><select id="RetPrcAccId" name="RetPrcAccId" style="width: 300px;" required>
                        <option value="0">--Pilih Akun Retur--</option>
                        <?php
                        foreach ($ivtcoa as $coakredit) {
                            if ($coakredit->Id == $itementity->RetPrcAccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $coakredit->Id, $coakredit->Kode,$coakredit->Perkiraan);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><button type="submit" class="button">Update</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("inventory.itementity")); ?>" class="button">Batal</a>
                </td>
            </tr>
        </table>
	</form>
</fieldset>
<!-- </body> -->
</html>
