<!DOCTYPE HTML>
<html>
<head>
	<title>SND System - Ubah Master Barang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            //var elements = ["ItemCode","BarCode","ItemName","Submit"];
            //BatchFocusRegister(elements);
        });
    </script>
</head>

<body>
<?php /** @var $items Items */ /** @var $itemuoms ItemUom[] */?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend><span class="bold">Ubah Data Master Barang</span></legend>
	<form action="<?php print($helper->site_url("inventory.items/edit/".$items->Id)); ?>" method="post">
		<table cellspacing="0" cellpadding="0" class="tablePadding" style="margin: 0 auto;" align="left">
            <tr>
                <td class="bold right"><label for="PrincipalId">Principal :</label></td>
                <td><select id="PrincipalId" name="PrincipalId" required style="width: 150px">
                        <option value="">-- Pilih Principal --</option>
                        <?php
                        /** @var $principals Supplier[] */
                        foreach ($principals as $principal){
                            if ($items->PrincipalId == $principal->Id) {
                                printf('<option value="%d" selected="selected">%s</option>', $principal->Id, $principal->SupName);
                            } else {
                                printf('<option value="%d">%s</option>', $principal->Id, $principal->SupName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td class="bold right"><label for="BrandId">Brand :</label></td>
                <td><select id="BrandId" name="BrandId" required style="width: 150px">
                        <option value="">-- Pilih Brand --</option>
                        <?php
                        /** @var $brands ItemBrand[] */
                        foreach ($brands as $brand){
                            if ($items->BrandId == $brand->Id) {
                                printf('<option value="%d" selected="selected">%s</option>', $brand->Id, $brand->BrandName);
                            } else {
                                printf('<option value="%d">%s</option>', $brand->Id, $brand->BrandName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td class="bold right"><label for="SubCategoryId1">Kategori :</label></td>
                <td><select id="SubCategoryId1" name="SubCategoryId1" disabled style="width: 150px">
                        <option value="">-- Pilih Kategori Produk --</option>
                        <?php
                        /** @var $subcategories ItemSubCategory[] */
                        foreach ($subcategories as $subcategory){
                            if ($items->SubCategoryId == $subcategory->Id) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $subcategory->Id, $subcategory->CategoryName, $subcategory->SubCategoryName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $subcategory->Id, $subcategory->CategoryName, $subcategory->SubCategoryName);
                            }
                        }
                        ?>
                    </select>
                    <input type="hidden" value="<?=$items->SubCategoryId;?>" name="SubCategoryId" id="SubCategoryId"/>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="ItemCode">Kode Barang :</label></td>
                <td><input type="text" id="ItemCode" name="ItemCode" value="<?php print($items->ItemCode); ?>" size="20" maxlength="30" readonly/></td>
                <td class="bold right"><label for="ItemName">Nama Barang :</label></td>
                <td colspan="2"><input type="text" id="ItemName" name="ItemName" value="<?php print(htmlspecialchars($items->ItemName)); ?>" size="42" maxlength="100" required/></td>
                <td class="bold right"><label for="OldCode">Principal Item Code :</label></td>
                <td><input type="text" id="OldCode" name="OldCode" value="<?php print($items->OldCode); ?>" size="15" maxlength="30"/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="LuomCode">Satuan Besar :</label></td>
                <td><select id="LuomCode" name="LuomCode" style="width: 90px" required>
                        <option value=""></option>
                        <?php
                        /** @var $itemuoms ItemUom[] */
                        foreach ($itemuoms as $satuan) {
                            if ($items->LuomCode == $satuan->UomCode) {
                                printf('<option value="%s" selected="selected">%s - %s</option>', $satuan->UomCode, $satuan->UomCode, $satuan->UomName);
                            }else {
                                printf('<option value="%s">%s - %s</option>', $satuan->UomCode, $satuan->UomCode, $satuan->UomName);
                            }
                        }
                        ?>
                    </select>
                    <label for="SuomQty">Isi :</label>
                    <input type="text" id="SuomQty" name="SuomQty" class="right" value="<?php print($items->SuomQty); ?>" size="1" maxlength="5" required/>
                </td>
                <td class="bold right"><label for="SuomCode">Satuan Kecil :</label></td>
                <td colspan="2">
                    <select id="SuomCode" name="SuomCode" style="width: 90px" required>
                        <option value=""></option>
                        <?php
                        /** @var $itemuoms ItemUom[] */
                        foreach ($itemuoms as $satuan) {
                            if ($items->SuomCode == $satuan->UomCode) {
                                printf('<option value="%s" selected="selected">%s - %s</option>', $satuan->UomCode, $satuan->UomCode, $satuan->UomName);
                            }else {
                                printf('<option value="%s">%s - %s</option>', $satuan->UomCode, $satuan->UomCode, $satuan->UomName);
                            }
                        }
                        ?>
                    </select>
                    <label for="QtyConvert">Isi Bersih :</label>
                    <input type="text" id="QtyConvert" name="QtyConvert" class="right" value="<?php print($items->QtyConvert); ?>" size="1" maxlength="5" required/>
                    <select id="CuomCode" name="CuomCode" style="width: 90px" required>
                        <option value=""></option>
                        <?php
                        /** @var $itemuoms ItemUom[] */
                        foreach ($itemuoms as $satuan) {
                            if ($items->CuomCode == $satuan->UomCode) {
                                printf('<option value="%s" selected="selected">%s - %s</option>', $satuan->UomCode, $satuan->UomCode, $satuan->UomName);
                            }else {
                                printf('<option value="%s">%s - %s</option>', $satuan->UomCode, $satuan->UomCode, $satuan->UomName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <label for="IsAktif">Status :</label>
                    <select id="IsAktif" name="IsAktif" required>
                        <option value="1" <?php print($items->IsAktif == "1" ? 'selected="selected"' : ''); ?>>Aktif</option>
                        <option value="0" <?php print($items->IsAktif == "0" ? 'selected="selected"' : ''); ?>>Non-Aktif</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="IsAllowMinus">Aturan Stock :</label></td>
                <td><select id="IsAllowMinus" name="IsAllowMinus"  style="width: 150px" required>
                        <option value="1" <?php print($items->IsAllowMinus == "0" ? 'selected="selected"' : ''); ?>>Tidak Boleh Minus</option>
                        <option value="0" <?php print($items->IsAllowMinus == "1" ? 'selected="selected"' : ''); ?>>Boleh Minus</option>
                    </select>
                </td>
                <td class="bold right"><label for="MinStock">Min Stock :</label></td>
                <td><input type="text" id="MinStock" name="MinStock" class="right" value="<?php print($items->MinStock); ?>" size="5" maxlength="5" required/>&nbsp; Satuan Kecil</td>
            </tr>
            <tr>
                <td class="bold right"><label for="CabangId">Cabang Asal :</label></td>
                <td><select id="CabangId" name="CabangId" style="width: 150px">
                        <?php
                        foreach ($cabangs as $cabang) {
                            if ($items->CabangId > 0){
                                if ($cabang->Id == $items->CabangId) {
                                    printf('<option value="%d" selected="selected">%s</option>', $cabang->Id, $cabang->Kode);
                                }
                            }else{
                                if ($cabang->Id == $cabId) {
                                    printf('<option value="%d" selected="selected">%s</option>', $cabang->Id, $cabang->Kode);
                                }else {
                                    printf('<option value="%d">%s</option>', $cabang->Id, $cabang->Kode);
                                }
                            }
                        }
                        ?>
                    </select>
                </td>
                <td class="bold right"><label for="ItemLevel">Level :</label></td>
                <td><select id="ItemLevel" name="ItemLevel" style="width: 90px;" required>
                        <option value="2" <?php print($items->ItemLevel == "2" ? 'selected="selected"' : ''); ?>>Cabang</option>
                        <option value="1" <?php print($items->ItemLevel == "1" ? 'selected="selected"' : ''); ?>>Perusahaan</option>
                        <option value="0" <?php print($items->ItemLevel == "0" ? 'selected="selected"' : ''); ?>>Global</option>
                    </select>
                    
                </td>
            </tr>

			<tr>
				<td>&nbsp;</td>
				<td colspan="3"><button type="submit" id="Submit" class="button">Update Data</button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("inventory.items")); ?>">Daftar Barang</a>
                </td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
