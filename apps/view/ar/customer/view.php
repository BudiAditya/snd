<!DOCTYPE HTML>
<html>
<head>
    <?php
    /** @var $customer Customer  */
    /** @var $ctypes CusType[] */
    /** @var $sareas SalesArea[] */
	$jdl = "View Data Customer";
	$dft = "Daftar Customer";
	$burl = $helper->site_url("ar.customer");
    ?>
	<title>SND System - <?php print($jdl);?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>" />
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			//var elements = ["CusName", "ContactTypeId", "Address", "City", "PostCd", "MailAddr", "MailCity", "MailPostCd","TelNo", "FaxNo", "ContactPerson", "Position", "HandPhone", "IdCard", "Nationality", "DateOfBirth", "MaritalStatus", "Npwp", "EmailAdd", "WebSite", "Gender","Remark","Status","ContactLevel","CreditTerms", "Reminder", "Interest","CreditLimit","CreditToDate","MaxInvOutstanding","PointSum","PointRedem","Submit"];
			//BatchFocusRegister(elements);

			//$("#DateOfBirth").datepicker({dateFormat:'yy-mm-dd', altFormat:'dd-mm-yy'});
		});
	</script>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div asuransi="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div asuransi="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
	<legend class="bold"><?php print($jdl);?></legend>
    <table cellpadding="2" cellspacing="1" style="tablePadding">
        <tr>
            <td class="bold right"><label for="AreaId1">Sales Area :</label></td>
            <td><select id="AreaId1" name="AreaId1" disabled>
                    <option value="">--Pilih Sales Area--</option>
                    <?php
                    foreach ($sareas as $area) {
                        if ($area->Id == $customer->AreaId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $area->Id, $area->AreaCode, $area->AreaName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $area->Id, $area->AreaCode, $area->AreaName);
                        }
                    }
                    ?>
                </select>
                <input type="hidden" name="AreaId" id="AreaId" value="<?php print($customer->AreaId); ?>"/>
            </td>
            <td class="bold right"><label for="CusTypeId">Kategori :</label></td>
            <td><select id="CusTypeId" name="CusTypeId" disabled>
                    <option value="">--Pilih Kategori--</option>
                    <?php
                    foreach ($ctypes as $type) {
                        if ($type->Id == $customer->CusTypeId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $type->Id, $type->TypeCode,$type->TypeName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $type->Id, $type->TypeCode,$type->TypeName);
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="bold right"><label for="CusName">Customer/Outlet :</label></td>
            <td colspan="2">
                <input type="text" id="CusName" name="CusName" value="<?php print($customer->CusName); ?>" size="30" disabled/>
                <label for="CusCode"><b>Kode :</b></label>
            </td>
            <td><input type="text" id="CusCode" name="CusCode" value="<?php print($customer->CusCode); ?>" size="15" disabled/></td>
        </tr>
        <tr>
            <td class="bold right"><label for="Addr1">Alamat :</label></td>
            <td colspan="3"><input type="text" id="Addr1" name="Addr1" value="<?php print($customer->Addr1); ?>" size="63" disabled/></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="3"><input type="text" id="Addr2" name="Addr2" value="<?php print($customer->Addr2); ?>" size="63" disabled/></td>
        </tr>
        <tr>
            <td class="bold right"><label for="Phone">Telephone :</label></td>
            <td><input type="text" id="Phone" name="Phone" value="<?php print($customer->Phone); ?>" size="20" disabled/></td>
            <td class="bold right"><label for="Fax">Facsimile :</label></td>
            <td><input type="text" id="Fax" name="Fax" value="<?php print($customer->Fax); ?>" size="20" disabled/></td>
        </tr>
        <tr>
            <td class="bold right"><label for="Contact">Contact Person :</label></td>
            <td><input type="text" id="Contact" name="Contact" value="<?php print($customer->Contact); ?>" size="20" disabled/></td>
            <td class="bold right"><label for="Npwp">NPWP :</label></td>
            <td><input type="text" id="Npwp" name="Npwp" value="<?php print($customer->Npwp); ?>" size="20" disabled/></td>
        </tr>
        <tr>
            <td class="bold right"><label for="CreditLimit">Credit Limit :</label></td>
            <td><input type="text" class="bold right" id="CreditLimit" name="CreditLimit" value="<?php print($customer->CreditLimit); ?>" size="17" disabled/></td>
            <td class="bold right"><label for="Term">Lama Kredit :</label></td>
            <td><input type="text" class="bold right" id="Term" name="Term" value="<?php print($customer->Term); ?>" size="3" disabled/>hari</td>
        </tr>
        <tr>
            <td class="bold right"><label for="IsPkp">Status Pajak :</label></td>
            <td><select name="IsPkp" id="IsPkp" disabled>
                    <option value="1" <?php print($customer->IsPkp == 1 ? 'selected="selected"' : '');?>>1 - PKP</option>
                    <option value="0" <?php print($customer->IsPkp == 0 ? 'selected="selected"' : '');?>>0 - Non-PKP</option>
                </select>
            </td>
            <td class="bold right"><label for="IsAktif">Status :</label></td>
            <td><select name="IsAktif" id="IsAktif" disabled>
                    <option value="1" <?php print($customer->IsAktif == 1 ? 'selected="selected"' : '');?>>1 - Aktif</option>
                    <option value="0" <?php print($customer->IsAktif == 0 ? 'selected="selected"' : '');?>>0 - Non-Aktif</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="3">
                <a href="<?php print($burl); ?>" type="button"><?php print($dft);?></a>
            </td>
        </tr>
    </table>
</fieldset>
<!-- </body> -->
</html>
