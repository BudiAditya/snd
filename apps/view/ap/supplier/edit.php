<!DOCTYPE HTML>
<html>
<head>
    <?php
    /** @var $supplier Supplier  */
    /** @var $stypes SupType[] */
	$jdl = "Ubah Data Supplier";
	$dft = "Daftar Supplier";
	$burl = $helper->site_url("ap.supplier");
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
			//var elements = ["SupName", "ContactTypeId", "Address", "City", "PostCd", "MailAddr", "MailCity", "MailPostCd","TelNo", "FaxNo", "ContactPerson", "Position", "HandPhone", "IdCard", "Nationality", "DateOfBirth", "MaritalStatus", "Npwp", "EmailAdd", "WebSite", "Gender","Remark","Status","ContactLevel","CreditTerms", "Reminder", "Interest","CreditLimit","CreditToDate","MaxInvOutstanding","PointSum","PointRedem","Submit"];
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
	<form id="frm" action="<?php print($helper->site_url("ap.supplier/edit/".$supplier->Id)); ?>" method="post">
		<table cellpadding="2" cellspacing="1" style="tablePadding">
            <tr>
                <td class="bold right"><label for="SupTypeId">Type :</label></td>
                <td colspan="2"><select id="SupTypeId" name="SupTypeId" required>
                        <option value="0">--Pilih Type Supplier--</option>
                        <?php
                        foreach ($stypes as $type) {
                            if ($type->Id == $supplier->SupTypeId) {
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
                <td class="bold right"><label for="SupCode">Kode :</label></td>
                <td><input type="text" id="SupCode" name="SupCode" value="<?php print($supplier->SupCode); ?>" size="10" required/></td>
                <td class="bold right"><label for="SupName">Supplier :</label></td>
                <td><input type="text" id="SupName" name="SupName" value="<?php print($supplier->SupName); ?>" size="30" required/></td>
                <td class="bold right"><label for="Npwp">NPWP :</label></td>
                <td><input type="text" id="Npwp" name="Npwp" value="<?php print($supplier->Npwp); ?>" size="20"/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Addr1">Alamat1 :</label></td>
                <td colspan="3"><input type="text" id="Addr1" name="Addr1" value="<?php print($supplier->Addr1); ?>" size="66" required/></td>
                <td class="bold right"><label for="City">Kota :</label></td>
                <td><input type="text" id="City" name="City" value="<?php print($supplier->City); ?>" size="20" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Addr2">Alamat2 :</label></td>
                <td colspan="3"><input type="text" id="Addr2" name="Addr2" value="<?php print($supplier->Addr2); ?>" size="66" required/></td>
                <td class="bold right"><label for="PostCode">Kode Pos :</label></td>
                <td><input type="text" id="PostCode" name="PostCode" value="<?php print($supplier->PostCode); ?>" size="20"/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Phone">Telephone :</label></td>
                <td><input type="text" id="Phone" name="Phone" value="<?php print($supplier->Phone); ?>" size="20"/></td>
                <td class="bold right"><label for="Fax">Fax :</label></td>
                <td><input type="text" id="Fax" name="Fax" value="<?php print($supplier->Fax); ?>" size="30"/></td>
                <td class="bold right"><label for="Hp">HP :</label></td>
                <td><input type="text" id="Hp" name="Hp" value="<?php print($supplier->Hp); ?>" size="20"/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Contact">P I C :</label></td>
                <td><input type="text" id="Contact" name="Contact" value="<?php print($supplier->Contact); ?>" size="20"/></td>
                <td class="bold right"><label for="Manager">Manager :</label></td>
                <td><input type="text" id="Manager" name="Manager" value="<?php print($supplier->Manager); ?>" size="30"/></td>
                <td class="bold right"><label for="IsPkp">Pajak :</label></td>
                <td><select name="IsPkp" id="IsPkp" required>
                        <option value="1" <?php print($supplier->IsPkp == 1 ? 'selected="selected"' : '');?>>1 - PKP</option>
                        <option value="0" <?php print($supplier->IsPkp == 0 ? 'selected="selected"' : '');?>>0 - Non-PKP</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="Bank">Bank :</label></td>
                <td><input type="text" id="Bank" name="Bank" value="<?php print($supplier->Bank); ?>" size="20"/></td>
                <td class="bold right"><label for="AccountNo">No.Rek :</label></td>
                <td><input type="text" id="AccountNo" name="AccountNo" value="<?php print($supplier->AccountNo); ?>" size="30"/></td>
                <td class="bold right"><label for="IsAktif">Status :</label></td>
                <td><select name="IsAktif" id="IsAktif" required>
                        <option value="1" <?php print($supplier->IsAktif == 1 ? 'selected="selected"' : '');?>>1 - Aktif</option>
                        <option value="0" <?php print($supplier->IsAktif == 0 ? 'selected="selected"' : '');?>>0 - Non-Aktif</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="bold right"><label for="IsPrincipal">Principal :</label></td>
                <td><select name="IsPrincipal" id="IsPrincipal" required>
                        <option value="1" <?php print($supplier->IsPrincipal == 1 ? 'selected="selected"' : '');?>>1 - Ya</option>
                        <option value="0" <?php print($supplier->IsPrincipal == 0 ? 'selected="selected"' : '');?>>0 - Tidak</option>
                    </select>
                </td>
                <td class="bold right"><label for="CreditLimit">Limit :</label></td>
                <td><input type="text" class="right" id="CreditLimit" name="CreditLimit" value="<?php print($supplier->CreditLimit); ?>" size="15" required/></td>
                <td class="bold right"><label for="Term">Terms :</label></td>
                <td><input type="text" class="right" id="Term" name="Term" value="<?php print($supplier->Term); ?>" size="3" required/>&nbsp;hari</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="3">
                    <button type="Submit" id="Submit">Update Data</button>
                    &nbsp;
                    <a href="<?php print($burl); ?>" type="button"><?php print($dft);?></a>
                </td>
            </tr>
        </table>
	</form>
</fieldset>
<!-- </body> -->
</html>
