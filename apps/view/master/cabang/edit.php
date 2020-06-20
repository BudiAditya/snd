<!DOCTYPE HTML>
<html>
<head>
    <title>SND System - Ubah Data Informasi Cabang</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var elements = ["Kode", "Cabang","Alamat", "Pic"];
            BatchFocusRegister(elements);
        });
    </script>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>

<br/>
<fieldset>
    <legend><b>Ubah Data Cabang</b></legend>
    <form id="frm" action="<?php print($helper->site_url("master.cabang/edit/".$cabang->Id)); ?>" method="post" enctype="multipart/form-data">
        <table cellpadding="2" cellspacing="1">
            <tr>
                <th>Data Umum</th>
                <th>Jam Kerja</th>
            </tr>
            <tr>
                <td valign="top">
                    <table cellpadding="2" cellspacing="1">
                        <tr>
                            <td>Company</td>
                            <td><select name="CompanyId" class="text2" id="CompanyId">
                                    <option value=""></option>
                                    <?php
                                    foreach ($companies as $sbu) {
                                        if ($sbu->Id == $userCompanyId) {
                                            printf('<option value="%d" selected="selected">%s - %s</option>', $sbu->Id, $sbu->CompanyCode, $sbu->CompanyName);
                                        } else {
                                            printf('<option value="%d">%s - %s</option>', $sbu->Id, $sbu->CompanyCode, $sbu->CompanyName);
                                        }
                                    }
                                    ?>
                                </select></td>
                        </tr>
                        <tr>
                            <td>Area</td>
                            <td><select name="AreaId" class="text2" id="AreaId" required>
                                    <option value=""></option>
                                    <?php
                                    foreach ($areas as $area) {
                                        if ($area->Id == $cabang->AreaId) {
                                            printf('<option value="%d" selected="selected">%s</option>', $area->Id, $area->AreaName);
                                        } else {
                                            printf('<option value="%d">%s</option>', $area->Id, $area->AreaName);
                                        }
                                    }
                                    ?>
                                </select></td>
                        </tr>
                        <tr>
                            <td>Jenis Cabang</td>
                            <td><select id="CabType" name="CabType" >
                                    <option value="0" <?php print($cabang->CabType == 0 ? 'selected="selected"' : '');?>>Outlet + Gudang</option>
                                    <option value="1" <?php print($cabang->CabType == 1 ? 'selected="selected"' : '');?>>Outlet Saja</option>
                                    <option value="2" <?php print($cabang->CabType == 2 ? 'selected="selected"' : '');?>>Gudang Saja</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Kode Cabang</td>
                            <td><input type="text" class="text2" name="Kode" id="Kode" maxlength="50" size="50" value="<?php print($cabang->Kode); ?>" required/></td>
                        </tr>
                        <tr>
                            <td>Lokasi/Cabang</td>
                            <td><input type="text" class="text2" name="Cabang" id="Cabang" maxlength="50" size="50" value="<?php print($cabang->Cabang); ?>" required/></td>
                        </tr>
                        <tr>
                            <td>Nama Outlet</td>
                            <td><input type="text" class="text2" name="NamaCabang" id="NamaCabang" maxlength="50" size="50" value="<?php print($cabang->NamaCabang); ?>"/></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td><input type="text" class="text2" name="Alamat" id="Alamat" maxlength="250" size="50" value="<?php print($cabang->Alamat); ?>" /></td>
                        </tr>
                        <tr>
                            <td>Kota</td>
                            <td><input type="text" class="text2" name="Kota" id="Kota" maxlength="50" size="50" value="<?php print($cabang->Kota); ?>" /></td>
                        </tr>
                        <tr>
                            <td>No. Telepon</td>
                            <td><input type="text" class="text2" name="Notel" id="Notel" maxlength="50" size="50" value="<?php print($cabang->Notel); ?>" /></td>
                        </tr>
                        <tr>
                            <td>N P W P</td>
                            <td><input type="text" class="text2" name="Npwp" id="Npwp" maxlength="50" size="50" value="<?php print($cabang->Npwp); ?>" /></td>
                        </tr>
                        <tr>
                            <td>No. Rekening</td>
                            <td><input type="text" class="text2" name="Norek" id="Norek" maxlength="150" size="50" value="<?php print($cabang->Norek); ?>" /></td>
                        </tr>
                        <tr>
                            <td>P I C</td>
                            <td><input type="text" class="text2" name="Pic" id="Pic" maxlength="50" size="50" value="<?php print($cabang->Pic); ?>" /></td>
                        </tr>
                        <tr>
                            <td>Harga Jual</td>
                            <td><select id="PriceIncPpn" name="PriceIncPpn" >
                                    <option value="0" <?php print($cabang->PriceIncPpn == 0 ? 'selected="selected"' : '');?>>0 - Belum Termasuk PPN</option>
                                    <option value="1" <?php print($cabang->PriceIncPpn == 1 ? 'selected="selected"' : '');?>>1 - Sudah Termasuk PPN</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Aturan Stock</td>
                            <td><select id="AllowMinus" name="AllowMinus" >
                                    <option value="0" <?php print($cabang->AllowMinus == 0 ? 'selected="selected"' : '');?>>0 - Tidak Boleh Minus</option>
                                    <option value="1" <?php print($cabang->AllowMinus == 1 ? 'selected="selected"' : '');?>>1 - Boleh Minus</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Raw Printing Mode</td>
                            <td><select id="RawPrintMode" name="RawPrintMode" >
                                    <option value="0">--Pilih Print Mode--</option>
                                    <option value="1" <?php print($cabang->RawPrintMode == 1 ? 'selected="selected"' : '');?>>1 - Plain Paper</option>
                                    <option value="2" <?php print($cabang->RawPrintMode == 2 ? 'selected="selected"' : '');?>>2 - Form Paper</option>
                                    <option value="3" <?php print($cabang->RawPrintMode == 3 ? 'selected="selected"' : '');?>>3 - P D F</option>
                                    <option value="4" <?php print($cabang->RawPrintMode == 4 ? 'selected="selected"' : '');?>>4 - POS Struk</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Raw Printer Name</td>
                            <td><input type="text" class="text2" name="RawPrinterName" id="RawPrinterName" maxlength="50" size="50" value="<?php print($cabang->RawPrinterName); ?>" /></td>
                        </tr>
                        <tr>
                            <td>File Logo</td>
                            <td><input type="file" class="text2" name="FileName" id="FileName" accept="image/*" /></td>
                        </tr>
                    </table>
                </td>
                <td valign="top">
                    <table cellpadding="2" cellspacing="1">
                        <tr>
                            <td colspan="2" align="center"><b>SENIN - JUMAT</b></td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>S A B T U</b></td>
                            <td><b>Aturan Login ke System:</b></td>
                        </tr>
                        <tr>
                            <td align="center"><input type="text" class="bold center" name="Jk1Mulai" id="Jk1Mulai" maxlength="5" size="3" value="<?php print($cabang->Jk1Mulai); ?>"/></td>
                            <td align="center"><input type="text" class="bold center" name="Jk1Akhir" id="Jk1Akhir" maxlength="5" size="3" value="<?php print($cabang->Jk1Akhir); ?>"/></td>
                            <td><input type="text" class="bold center" name="Jk2Mulai" id="Jk2Mulai" maxlength="5" size="3" value="<?php print($cabang->Jk2Mulai); ?>"/>
                                <input type="text" class="bold center" name="Jk2Akhir" id="Jk2Akhir" maxlength="5" size="3" value="<?php print($cabang->Jk2Akhir); ?>"/>
                            </td>
                            <td><select name="WorkMode" id="WorkMode" required>
                                    <option value="0" <?php print($cabang->WorkMode == 0 ? 'selected="selected"' : '');?>>0 - Bebas Login (Not Recomended)</option>
                                    <option value="1" <?php print($cabang->WorkMode == 1 ? 'selected="selected"' : '');?>>1 - Login Sesuai Jam Kerja</option>
                                    <option value="2" <?php print($cabang->WorkMode == 2 ? 'selected="selected"' : '');?>>2 - Login Sesuai Jam Kerja + Kehadiran</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="2" align="right">Akun Kas Besar :</td>
                            <td colspan="2">
                                <select name="KasAccId" id="KasAccId" required>
                                    <option value="0"></option>
                                    <?php
                                    /** @var $akuns CoaDetail[] */
                                    foreach ($akuns as $akun){
                                        if ($akun->Id == $cabang->KasAccId) {
                                            printf('<option value="%d" selected="selected">%s - %s</option>', $akun->Id, $akun->Kode, $akun->Perkiraan);
                                        }else{
                                            printf('<option value="%d">%s - %s</option>',$akun->Id,$akun->Kode,$akun->Perkiraan);
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="right">Akun Kas Kecil :</td>
                            <td colspan="2">
                                <select name="PtyAccId" id="PtyAccId" required>
                                    <option value="0"></option>
                                    <?php
                                    /** @var $akuns CoaDetail[] */
                                    foreach ($akuns as $akun){
                                        if ($akun->Id == $cabang->PtyAccId) {
                                            printf('<option value="%d" selected="selected">%s - %s</option>', $akun->Id, $akun->Kode, $akun->Perkiraan);
                                        }else{
                                            printf('<option value="%d">%s - %s</option>',$akun->Id,$akun->Kode,$akun->Perkiraan);
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="right">Akun Warkat :</td>
                            <td colspan="2">
                                <select name="WktAccId" id="WktAccId" required>
                                    <option value="0"></option>
                                    <?php
                                    /** @var $akuns CoaDetail[] */
                                    foreach ($akuns as $akun){
                                        if ($akun->Id == $cabang->WktAccId) {
                                            printf('<option value="%d" selected="selected">%s - %s</option>', $akun->Id, $akun->Kode, $akun->Perkiraan);
                                        }else{
                                            printf('<option value="%d">%s - %s</option>',$akun->Id,$akun->Kode,$akun->Perkiraan);
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <button type="submit">UPDATE</button>
                    <a href="<?php print($helper->site_url("master.cabang")); ?>" class="button">Daftar Cabang</a>
                </td>
            </tr>
        </table>

    </form>
</fieldset>
<!-- </body> -->
</html>
