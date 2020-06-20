<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - View Promo Penjualan</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
</head>

<body>
<?php /** @var $promo Promo */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">View Data Promo Penjualan</span></legend>
    <table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
        <tr>
            <td>
                <table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
                    <tr>
                        <td class="bold right"><label for="PromoType">Jenis Promo :</label></td>
                        <td colspan="3"><select id="PromoType" name="PromoType" disabled>
                                <option value=""></option>
                                <?php
                                if ($typelists != null) {
                                    while ($row = $typelists->FetchAssoc()) {
                                        if ($row["promo_type"] == $promo->PromoType) {
                                            printf('<option value="%d" selected="selected">%s - %s</option>', $row["promo_type"], $row["promo_type"],$row["promo_descs"]);
                                        } else {
                                            printf('<option value="%d">%s - %s</option>', $row["promo_type"], $row["promo_type"],$row["promo_descs"]);
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="bold right"><label for="PromoDescs">Nama Promo :</label></td>
                        <td colspan="3"><input type="text" id="PromoDescs" name="PromoDescs" value="<?php print($promo->PromoDescs); ?>" style="width: 300px;" disabled/></td>
                    </tr>
                    <tr>
                        <td class="bold right"><label for="ItemId">Nama Produk :</label></td>
                        <td colspan="3"><select id="ItemId" name="ItemId" style="width: 300px;" disabled>
                                <option value="">--Pilih Nama Produk--</option>
                                <?php
                                /** @var $itemlists Items[] */
                                foreach ($itemlists as $item) {
                                    if ($item->Id == $promo->ItemId) {
                                        printf('<option value="%d" selected="selected">%s - %s</option>', $item->Id, $item->ItemCode,$item->ItemName);
                                    } else {
                                        printf('<option value="%d">%s - %s</option>', $item->Id, $item->ItemCode,$item->ItemName);
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="bold right"><label for="CustypeId">Type Customer :</label></td>
                        <td colspan="3"><select id="CustypeId" name="CustypeId" style="width: 300px;" disabled>
                                <option value="0">--Semua Customer--</option>
                                <?php
                                /** @var $custypes CusType[] */
                                foreach ($custypes as $ctype) {
                                    if ($ctype->Id == $promo->CustypeId) {
                                        printf('<option value="%d" selected="selected">%s - %s</option>', $ctype->Id, $ctype->TypeCode,$ctype->TypeName);
                                    } else {
                                        printf('<option value="%d">%s - %s</option>', $ctype->Id, $ctype->TypeCode,$ctype->TypeName);
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="bold right"><label for="ZoneId">Sales Zone :</label></td>
                        <td><select id="ZoneId" name="ZoneId" disabled>
                                <option value="0">--Semua Zone--</option>
                                <?php
                                if ($zonelists != null) {
                                    while ($row = $zonelists->FetchAssoc()) {
                                        if ($row["id"] == $promo->ZoneId) {
                                            printf('<option value="%d" selected="selected">%s - %s</option>', $row["id"], $row["code"],$row["name"]);
                                        } else {
                                            printf('<option value="%d">%s - %s</option>', $row["id"], $row["code"],$row["name"]);
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td class="bold right"><label for="AreaId">Area :</label></td>
                        <td><select id="AreaId" name="AreaId" disabled>
                                <option value="0">--Semua Area--</option>
                                <?php
                                /** @var $arealists SalesArea[] */
                                foreach ($arealists as $area) {
                                    if ($area->Id == $promo->AreaId) {
                                        printf('<option value="%d" selected="selected">%s - %s</option>', $area->Id, $area->AreaCode,$area->AreaName);
                                    } else {
                                        printf('<option value="%d">%s - %s</option>', $area->Id, $area->AreaCode,$area->AreaName);
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="bold right"><label for="StartDate">Berlaku Mulai Tgl :</label></td>
                        <td><input type="text" size="12" id="StartDate" name="StartDate" value="<?php print($promo->FormatStartDate(JS_DATE));?>" disabled/></td>
                        <td class="bold right"><label for="EndDate">S/D Tgl :</label></td>
                        <td><input type="text" size="12" id="EndDate" name="EndDate" value="<?php print($promo->FormatEndDate(JS_DATE));?>" disabled/></td>
                    </tr>
                </table>
            </td>
            <td>
                <table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0;">
                    <tr>
                        <td class="bold right"><label for="MinQty">Minimum QTY :</label></td>
                        <td><input type="text" class="bold right" size="12" id="MinQty" name="MinQty" value="<?php print($promo->MinQty);?>" disabled/></td>
                        <td class="bold right"><label for="MaxQty">Max QTY :</label></td>
                        <td><input type="text" class="bold right" size="12" id="MaxQty" name="MaxQty" value="<?php print($promo->MaxQty);?>" disabled/></td>
                    </tr>
                    <tr>
                        <td class="bold right"><label for="MinAmount">Minimum Rp :</label></td>
                        <td><input type="text" class="bold right" size="12" id="MinAmount" name="MinAmount" value="<?php print($promo->MinAmount);?>" disabled/></td>
                        <td class="bold right"><label for="MaxAmount">Max Rp :</label></td>
                        <td><input type="text" class="bold right" size="12" id="MaxAmount" name="MaxAmount" value="<?php print($promo->MaxAmount);?>" disabled/></td>
                    </tr>
                    <tr>
                        <td class="bold right"><label for="DiscPct">Discount :</label></td>
                        <td><input type="text" class="bold right" size="12" id="DiscPct" name="DiscPct" value="<?php print($promo->DiscPct);?>" disabled/>%</td>
                        <td class="bold right"><label for="Poin">Poin :</label></td>
                        <td><input type="text" class="bold right" size="12" id="Poin" name="Poin" value="<?php print($promo->Poin);?>" disabled/></td>
                    </tr>
                    <tr>
                        <td class="bold right"><label for="QtyBonus">Bonus :</label></td>
                        <td><input type="text" class="bold right" size="12" id="QtyBonus" name="QtyBonus" value="<?php print($promo->QtyBonus);?>" disabled/></td>
                        <td class="bold right"><label for="ItemIdBonus">Item Bonus :</label></td>
                        <td><select id="ItemIdBonus" name="ItemIdBonus" style="width: 300px" disabled>
                                <option value="0">--Pilih Item Bonus--</option>
                                <?php
                                /** @var $itemlists Items[] */
                                foreach ($itemlists as $item) {
                                    if ($item->Id == $promo->ItemIdBonus) {
                                        printf('<option value="%d" selected="selected">%s - %s</option>', $item->Id, $item->ItemCode,$item->ItemName);
                                    } else {
                                        printf('<option value="%d">%s - %s</option>', $item->Id, $item->ItemCode,$item->ItemName);
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="bold right"><label for="IsKelipatan">Berlaku Kelipatan :</label></td>
                        <td><select id="IsKelipatan" name="IsKelipatan" disabled>
                                <option value="0" <?php print($promo->IsKelipatan == 0 ? 'selected="selected"' : '');?>>0 - Tidak</option>
                                <option value="1" <?php print($promo->IsKelipatan == 1 ? 'selected="selected"' : '');?>>1 - Ya</option>
                            </select>
                        </td>
                        <td class="bold right"><label for="IsAktif">Masih Berlaku :</label></td>
                        <td><select id="IsAktif" name="IsAktif" disabled>
                                <option value="0" <?php print($promo->IsAktif == 0 ? 'selected="selected"' : '');?>>0 - Tidak</option>
                                <option value="1" <?php print($promo->IsAktif == 1 ? 'selected="selected"' : '');?>>1 - Ya</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center"><a href="<?php print($helper->site_url("ar.promo")); ?>" class="button">KEMBALI</a></td>
        </tr>
    </table>
</fieldset>
<!-- </body> -->
</html>
