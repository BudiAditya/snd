<!DOCTYPE HTML>
<html>
<head>
    <title>Rekasys - Ubah Saldo Awal Akun</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $("#Debit").autoNumeric({ vMax: "99999999999999.99" });
            $("#Credit").autoNumeric({ vMax: "99999999999999.99" });
        });

    </script>
</head>

<body>

<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
    <legend><span class="bold">Ubah Saldo Awal Akuntansi</span></legend>

    <form action="<?php print($helper->site_url("accounting.obal/edit/".$openingBalance->Id)) ?>" method="post">
        <table cellpadding="0" cellspacing="0" class="tablePadding">
            <tr>
                <td class="right"><label for="AccId">Akun : </label></td>
                <td><select id="AccId" name="AccId" required>
                        <option value="">-- PILIH AKUN --</option>
                        <?php
                        $prevParentId = null;
                        foreach ($accounts as $account) {
                            /*
                                                    if ($prevParentId != $account->ParentId) {
                                                        $prevParentId = $account->ParentId;
                                                        $parent = $parentAccounts[$prevParentId];
                                                        printf('<optgroup label="%s - %s"></optgroup>', $parent->AccNo, $parent->AccName);
                                                    }
                            */
                            if ($account->Id == $openingBalance->AccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->Kode, $account->Perkiraan);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $account->Id, $account->Kode, $account->Perkiraan);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td class="right"><label for="Year">Tahun : </label></td>
                <td>
                    <select id="Year" name="Year" required>
                        <?php
                        $year = $openingBalance->FormatDate("Y");
                        for ($i = date("Y"); $i >= 2020; $i--) {
                            if ($i == $year) {
                                printf('<option value="%d" selected="selected">%s</option>', $i, $i);
                            } else {
                                printf('<option value="%d">%s</option>', $i, $i);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="right"><label for="DbAmount">Jumlah Debet :</label></td>
                <td><input type="text" id="DbAmount" name="DbAmount" value="<?php print($openingBalance->DbAmount); ?>" style="text-align: right;" required/></td>
            </tr>
            <tr>
                <td class="right"><label for="CrAmount">Jumlah Kredit :</label></td>
                <td><input type="text" id="CrAmount" name="CrAmount" value="<?php print($openingBalance->CrAmount); ?>" style="text-align: right" required/></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <button type="submit">UPDATE</button>
                    &nbsp;
                    <a href="<?php print($helper->site_url("accounting.obal")) ?>" class="button">KEMBALI</a>
                </td>
            </tr>
        </table>
    </form>
</fieldset>

<!-- </body> -->
</html>
