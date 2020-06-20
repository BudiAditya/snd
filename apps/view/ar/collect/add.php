<!DOCTYPE HTML>
<html>
<?php
/** @var $collect Collect */ /** @var $collector Karyawan[] */
?>
<head>
	<title>SND System - Entry Data Penagihan</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var elements = ["CabangId","CollectDate","CollectNo","CollectorId","CollectDescs","CollectAmount","PaidAmount","BalanceAmount","CollectStatus","btSubmit"];
            BatchFocusRegister(elements);
            $("#CollectDate").customDatePicker({ showOn: "focus" });

            // autoNumeric
            $(".num").autoNumeric({mDec: '0'});
            $("#frm").submit(function(e) {
                $(".num").each(function(idx, ele){
                    this.value  = $(ele).autoNumericGet({mDec: '0'});
                });
            });
            // when Base Amount change
            //eRepair = Number($("#NilEstRepair").autoNumericGet({mDec: '0'}));
            $("#CollectAmount").change(function(e){
                var bam = Number($("#CollectAmount").autoNumericGet({mDec: '0'}));
                var pam = Number($("#PaidAmount").autoNumericGet({mDec: '0'}));
                if (pyt == 0){
                    $("#PaidAmount").val(bam);
                    pam = bam;
                }
                $("#BalanceAmount").val(bam-pam);
            });
            // when Paid Amount change
            $("#PaidAmount").change(function(e){
                var bam = Number($("#CollectAmount").autoNumericGet({mDec: '0'}));
                var pam = Number($("#PaidAmount").autoNumericGet({mDec: '0'}));
                $("#BalanceAmount").val(bam-pam);
            });
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
	<legend align="left"><strong>Entry Data Penagihan Baru</strong></legend>
    <form id="frm" action="<?php print($helper->site_url("ar.collect/add")); ?>" method="post">
        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang/Outlet</td>
                <td><select name="CabangId" class="text2" id="CabangId" required>
                        <option value=""></option>
                        <?php
                        foreach ($cabangs as $cab) {
                            if ($cab->Id == $collect->CabangId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Tanggal</td>
                <td><input type="text" class="text2" maxlength="10" size="10" id="CollectDate" name="CollectDate" value="<?php print($collect->FormatCollectDate(JS_DATE));?>" required/></td>
                <td>No. Collect</td>
                <td><input type="text" class="text2" maxlength="20" size="20" id="CollectNo" name="CollectNo" value="<?php print($collect->CollectNo != null ? $collect->CollectNo : '-'); ?>" /></td>
            </tr>
            <tr>
                <td>Nama Collector</td>
                <td><select class="text2" id="CollectorId" name="CollectorId" required>
                        <option value="">- Pilih Collector -</option>
                        <?php
                        foreach ($collector as $collectorman) {
                            if ($collectorman->Id == $collect->CollectorId) {
                                printf('<option value="%d" selected="selected">%s</option>', $collectorman->Id, $collectorman->Nama);
                            } else {
                                printf('<option value="%d">%s</option>', $collectorman->Id, $collectorman->Nama);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Keterangan</td>
                <td colspan="3"><input type="text" class="text2" maxlength="150" size="70" id="CollectDescs" name="CollectDescs" value="<?php print($collect->CollectDescs);?>" /></td>
            </tr>
            <tr>
                <td>Nilai Tagihan</td>
                <td><b>Rp. <input type="text" class="num" id="CollectAmount" name="CollectAmount" size="18" maxlength="20" value="<?php print(number_format($collect->CollectAmount,0)); ?>" style="text-align: right" required/></b></td>
                <td>Sudah Terbayar</td>
                <td><b>Rp. <input type="text" class="num" id="PaidAmount" name="PaidAmount" size="18" maxlength="20" value="<?php print(number_format($collect->PaidAmount,0)); ?>" style="text-align: right" required/></b></td>
                <td>Sisa</td>
                <td><b>Rp. <input type="text" class="num" id="BalanceAmount" name="BalanceAmount" size="18" maxlength="20" value="<?php print(number_format($collect->CollectAmount - $collect->PaidAmount,0)); ?>" style="text-align: right" required/></b></td>
            </tr>
            <tr>
                <td>Status Penagihan</td>
                <td><select class="text2" id="CollectStatus" name="CollectStatus" required>
                        <option value="0" <?php print($collect->CollectStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                        <option value="1" <?php print($collect->CollectStatus == 1 ? 'selected="selected"' : '');?>>1 - In Process</option>
                        <option value="2" <?php print($collect->CollectStatus == 2 ? 'selected="selected"' : '');?>>2 - Selesai</option>
                        <option value="3" <?php print($collect->CollectStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                    </select>
                </td>
                <td colspan="4" align="center">
                    <a href="<?php print($helper->site_url("ar.collect")); ?>" class="button">Daftar Penagihan</a>
                    &nbsp&nbsp
                    <button id="btSubmit" type="submit"><b>Berikutnya &gt</b></button>
                </td>
            </tr>
        </table>
    </form>
</fieldset>
<!-- </body> -->
</html>
