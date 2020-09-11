<!DOCTYPE HTML>
<html>
<?php
/** @var $cbtrx CbTrx */ /** @var $accounts CoaDetail[] */ /** /** @var $trxtypes TrxType[] */ /** @var $cabangs Cabang[] */ /** @var $coabanks CoaDetail[] */
?>
<head>
	<title>SND System - Ubah Data Transaksi Cash/Bank</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            //var elements = ["TrxDate","ContactId","ReffNo","xTrxTypeId","TrxDescs","DbAccId","CrAccId","TrxAmount","Simpan"];
            //BatchFocusRegister(elements);
            $("#TrxDate").customDatePicker({ showOn: "focus" });
            // autoNumeric
            $(".num").autoNumeric({mDec: '2'});
            $("#frm").submit(function(e) {
                $(".num").each(function(idx, ele){
                    this.value  = $(ele).autoNumericGet({mDec: '2'});
                });
            });
            // when xTrxTypeId change
            $("#xTrxTypeId").change(function(e){
                var txd = $("#xTrxTypeId").val().split('|');
                var txi = txd[0];
                var txm = txd[1];
                var tx1 = txd[2];
                var tx2 = txd[3];
                var txu = txd[4];
                var tri = Number(txd[5]);
                $("#TrxMode").val(txm);
                $("#TrxTypeId").val(txi);
                if (txm == 1){
                    $("#DbAccId").val(tx1);
                    $("#CrAccId").val(tx2);
                }else if (txm == 2){
                    $("#DbAccId").val(tx2);
                    $("#CrAccId").val(tx1);
                }else{
                    $("#DbAccId").val(0);
                    $("#CrAccId").val(0);
                }
                $("#TrxDescs").val(txu);
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
	<legend><b>Ubah Transaksi Cash/Bank</b></legend>
	<form id="frm" method="post">
        <input type="hidden" id="TrxMode" name="TrxMode" value="<?php print($cbtrx->TrxMode);?>"/>
        <input type="hidden" id="TrxTypeId" name="TrxTypeId" value="<?php print($cbtrx->TrxTypeId);?>"/>
        <input type="hidden" id="CreateMode" name="CreateMode" value="<?php print($cbtrx->CreateMode);?>"/>
		<table cellpadding="2" cellspacing="2">
            <tr>
                <td>Tanggal</td>
                <td><input type="text" class="text2" maxlength="10" size="10" id="TrxDate" name="TrxDate" value="<?php print($cbtrx->FormatTrxDate(JS_DATE)); ?>" required/></td>
                <td>No. Bukti</td>
                <td><input type="text" class="text2" size="18" id="TrxNo" name="TrxNo" value="<?php print($cbtrx->TrxNo == null ? 'Auto' : $cbtrx->TrxNo); ?>" readonly/></td>
            </tr>
            <tr>
                <td>Jenis Transaksi</td>
                <td><select id="xTrxTypeId" name="xTrxTypeId" style="width: 250px"  required>
                        <option value="">--Pilih Jenis Transaksi--</option>
                        <?php
                        foreach ($trxtypes as $trxtype) {
                            $txd = $trxtype->Id.'|'.$trxtype->TrxMode.'|'.$trxtype->DefAccId.'|'.$trxtype->TrxAccId.'|'.$trxtype->TrxDescs.'|'.$trxtype->RefftypeId;
                            if ($trxtype->Id == $cbtrx->TrxTypeId) {
                                printf('<option value="%s" selected="selected">%s</option>', $txd, $trxtype->TrxDescs);
                            } else {
                                printf('<option value="%s">%s</option>', $txd, $trxtype->TrxDescs);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Status </td>
                <td><select id="TrxStatus1" name="TrxStatus1" disabled>
                        <option value="0" <?php print($cbtrx->TrxStatus == 0 ? 'selected="selected"' : '');?>>Draft</option>
                        <option value="1" <?php print($cbtrx->TrxStatus == 1 ? 'selected="selected"' : '');?>>Posted</option>
                        <option value="2" <?php print($cbtrx->TrxStatus == 2 ? 'selected="selected"' : '');?>>Approved</option>
                        <option value="3" <?php print($cbtrx->TrxStatus == 3 ? 'selected="selected"' : '');?>>Void</option>
                    </select>
                    <input type="hidden" name="TrxStatus" id="TrxStatus" value="<?=$cbtrx->TrxStatus?>"/>
                </td>
            </tr>
            <tr>
                <td>Debet Akun</td>
                <td><select id="DbAccId" name="DbAccId" style="width: 250px;">
                        <option value="">--Pilih Akun Debet--</option>
                        <?php
                        foreach ($accounts as $coadebet) {
                            if ($coadebet->Id == $cbtrx->DbAccId) {
                                printf('<option value="%d" selected="selected">%s</option>', $coadebet->Id, $coadebet->Kode.' - '.$coadebet->Perkiraan);
                            } else {
                                printf('<option value="%d">%s</option>', $coadebet->Id, $coadebet->Kode.' - '.$coadebet->Perkiraan);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Kredit Akun</td>
                <td colspan="3"><select id="CrAccId" name="CrAccId" style="width: 250px;">
                        <option value="">--Pilih Akun Kredit--</option>
                        <?php
                        foreach ($accounts as $coakredit) {
                            if ($coakredit->Id == $cbtrx->CrAccId) {
                                printf('<option value="%d" selected="selected">%s</option>', $coakredit->Id, $coakredit->Kode.' - '.$coakredit->Perkiraan);
                            } else {
                                printf('<option value="%d">%s</option>', $coakredit->Id, $coakredit->Kode.' - '.$coakredit->Perkiraan);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td colspan="4"><input type="text" class="text2" maxlength="150" size="75" id="TrxDescs" name="TrxDescs" value="<?php print($cbtrx->TrxDescs); ?>" required/></td>
            </tr>
            <tr>
                <td>Atas Nama</td>
                <td><input type="text" class="text2" style="width: 250px;" id="RelasiName" name="RelasiName" value="<?php print($cbtrx->RelasiName == null ? '-' : $cbtrx->RelasiName); ?>"/></td>
                <td>No. Reff. </td>
                <td><input type="text" class="text2" maxlength="150" size="22" id="ReffNo" name="ReffNo" value="<?php print($cbtrx->ReffNo == null ? '-' : $cbtrx->ReffNo); ?>"/></td>
            </tr>
            <tr>
                <td>Jumlah Uang</td>
                <td colspan="2"><b>Rp. <input type="text" class="bold num" id="TrxAmount" name="TrxAmount" size="20" maxlength="20" value="<?php print($cbtrx->TrxAmount == null ? 0 : $cbtrx->TrxAmount); ?>" style="text-align: right" required/></b></td>
            </tr>
			<tr>
                <td>&nbsp;</td>
				<td colspan="3">
					<button type="submit" id="Simpan" formaction="<?php print($helper->site_url("cashbank.cbtrx/edit/".$cbtrx->Id)); ?>">Update</button>
					<a href="<?php print($helper->site_url("cashbank.cbtrx")); ?>" class="button">Daftar Transaksi</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>
<!-- </body> -->
</html>
