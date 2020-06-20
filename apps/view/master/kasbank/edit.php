<!DOCTYPE HTML>
<html>
<head>
    <title>SND System - Ubah Master Bank</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $kasbank KasBank */ /** @var $accounts CoaDetail[] */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
    <legend><span class="bold">Ubah Data Bank</span></legend>
    <form action="<?php print($helper->site_url("master.kasbank/edit/".$kasbank->Id)); ?>" method="post">
        <table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0 auto;">
            <tr>
                <td class="right bold">Cabang :</td>
                <td><?php printf('%s', $cabCode) ?></td>
            </tr>
            <tr>
                <td class="bold right"><label for="BankId">Kode Bank :</label></td>
                <td><select id="BankId" name="BankId" required>
                        <option value="0">-- PILIH BANK --</option>
                        <?php
                        /** @var $banks Bank[] */
                        foreach ($banks as $bank) {
                            if ($bank->Id == $kasbank->BankId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $bank->Id, $bank->BankCode, $bank->BankName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $bank->Id, $bank->BankCode, $bank->BankName);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Name">Nama Bank :</label></td>
                <td><input type="text" id="Name" name="Name" value="<?php print($kasbank->BankName); ?>" size="30" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Branch">Cabang Bank :</label></td>
                <td><input type="text" id="Branch" name="Branch" value="<?php print($kasbank->Branch); ?>" size="15" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Address">Alamat :</label></td>
                <td><input type="text" id="Address" name="Address" value="<?php print($kasbank->Address); ?>" size="50" /></td>
            </tr>
            <tr>
                <td class="bold right"><label for="AtsNama">Atas Nama :</label></td>
                <td><input type="text" id="AtsNama" name="AtsNama" value="<?php print($kasbank->AtsNama); ?>" size="50" /></td>
            </tr>
            <tr>
                <td class="bold right"><label for="NoRek">Nomor Rekening :</label></td>
                <td><input type="text" id="NoRek" name="NoRek" value="<?php print($kasbank->NoRekening); ?>" size="30" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="CurrencyCode">Mata Uang :</label></td>
                <td><input type="text" id="CurrencyCode" name="CurrencyCode" value="<?php print($kasbank->CurrencyCode); ?>" size="5" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="TrxAccId">Kode Akun :</label></td>
                <td><select id="TrxAccId" name="TrxAccId" required>
                        <option value="">-- PILIH AKUN --</option>
                        <?php
                        foreach ($accounts as $account) {
                            if ($account->Id == $kasbank->TrxAccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->Kode, $account->Perkiraan);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $account->Id, $account->Kode, $account->Perkiraan);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td class="bold right"><label for="CostAccId">Kode Akun Biaya :</label></td>
                <td><select id="CostAccId" name="CostAccId">
                        <option value="0">-- PILIH AKUN --</option>
                        <?php
                        foreach ($accounts as $account) {
                            if ($account->Id == $kasbank->CostAccId) {
                                printf('<option value="%s" selected="selected">%s - %s</option>', $account->Id, $account->Kode, $account->Perkiraan);
                            } else {
                                printf('<option value="%s">%s - %s</option>', $account->Id, $account->Kode, $account->Perkiraan);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td class="bold right"><label for="RevAccId">Kode Akun Pendapatan :</label></td>
                <td><select id="RevAccId" name="RevAccId">
                        <option value="0">-- PILIH AKUN --</option>
                        <?php
                        foreach ($accounts as $account) {
                            if ($account->Id == $kasbank->RevAccId) {
                                printf('<option value="%s" selected="selected">%s - %s</option>', $account->Id, $account->Kode, $account->Perkiraan);
                            } else {
                                printf('<option value="%s">%s - %s</option>', $account->Id, $account->Kode, $account->Perkiraan);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><button type="submit">UPDATE</button>
                    <a href="<?php print($helper->site_url("master.kasbank")); ?>" class="button">Daftar Bank</a>
                </td>
            </tr>
        </table>
    </form>
</fieldset>

<!-- </body> -->
</html>
