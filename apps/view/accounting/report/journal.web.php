<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php
/** @var $start int */ /** @var $end int */ /** @var $docTypes DocType[] */ /** @var $showNo bool */ /** @var $showCol bool */ /** @var $docIds int[] */ /** @var $vocTypes VoucherType[] */
/** @var $report ReaderBase */ /** @var $output string */ /** @var $company Company */ /** @var $orientation string */ /** @var $status int */ /** @var $cabangId int */ /** @var $cabangList Cabang[] */
?>
<head>
	<title>Rekasys - Laporan Jurnal Akuntansi</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<style type="text/css">
		#list {
			margin: 0;
			padding: 0;
		}
		#list li {
			display: inline-block;
			padding: 0 2px;
		}
		#list li label {
			position: relative;
			top: 1px;
			display: inline-block;
			width: 150px;
			overflow: hidden;
		}
		.nowrap { white-space: nowrap; }
	</style>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#Start").customDatePicker({ phpDate: <?php print(is_int($start) ? $start : "null"); ?> });
			$("#End").customDatePicker({ phpDate: <?php print(is_int($end) ? $end : "NULL"); ?> });
		});

		function CheckAll(type) {
			$("#list").find(":checkbox").each(function(idx, ele) {
				var voucher = $(ele).attr("voucher");
				if (voucher == type) {
					ele.checked = "checked";
				} else {
					ele.checked = "";
				}
			});
		}
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
	<legend><span class="bold">Laporan Jurnal Akuntansi</span></legend>

	<form action="<?php print($helper->site_url("accounting.report/journal")); ?>" method="GET">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto; width: 80%;">
			<tr>
				<td class="nowrap right"><label for="Start">Periode : </label></td>
				<td>
					<input type="text" id="Start" name="start" required="requred"/>
					<label for="End"> s.d. </label>
					<input type="text" id="End" name="end" required="requred"/>
				</td>
			</tr>
			<tr>
				<td class="nowrap right" valign="top"><label style="margin-top: 2px; display: inline-block;">Jenis Dokumen :</label></td>
				<td>
					<ul id="list">
						<?php
						$buff = array();
						foreach ($docTypes as $docType) {
							if (in_array($docType->Id, $docIds)) {
								$checkbox = sprintf('<input type="checkbox" id="cb_%d" name="docType[]" value="%d" voucher="%s" checked="checked" />', $docType->Id, $docType->Id, strtoupper($docType->VoucherCd));
								$buff[] = strtoupper($docType->DocCode);
							} else {
								$checkbox = sprintf('<input type="checkbox" id="cb_%d" name="docType[]" value="%d" voucher="%s" />', $docType->Id, $docType->Id, strtoupper($docType->VoucherCd));
							}
							$label = sprintf('<label for="cb_%d" class="nowrap">%s - %s</label>', $docType->Id, $docType->DocCode, $docType->Description);
							printf("<li>%s %s</li>", $checkbox, $label);
						}
						?>
					</ul>
				</td>
			</tr>
			<tr>
				<td class="nowrap right">Centang Semua :</td>
				<td>
					<?php
					foreach ($vocTypes as $vocType) {
						printf('<button type="button" onclick="CheckAll(\'%s\')">%s</button>', strtoupper($vocType->VoucherCd), $vocType->VoucherCd);
					}
					?>
				</td>
			</tr>
            <tr>
                <td class="right"><label for="Cabang">Pilih Cabang : </label></td>
                <td>
                    <select id="Cabang" name="idCabang">
                        <option value="0">-- Not Filtered --</option>
                        <?php
                        $selectedCabang = null;
                        foreach ($cabangList as $cabang) {
                            if($cabang->Id == $idCabang){
                                $selectedCabang = $cabang;
                                printf('<option value="%d" selected="selected">%s - %s</option>', $cabang->Id, $cabang->Kode, $cabang->Cabang);
                            }else{
                                printf('<option value="%d">%s - %s</option>', $cabang->Id, $cabang->Kode, $cabang->Cabang);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="nowrap right"><label for="Status">Status Voucher :</label></td>
				<td>
					<select id="Status" name="status">
						<option value="-1">SEMUA STATUS</option>
						<option value="0" <?php print($status == 0 ? 'selected="selected"' : ''); ?>>DRAFT</option>
						<option value="1" <?php print($status == 1 ? 'selected="selected"' : ''); ?>>APPROVED</option>
						<option value="2" <?php print($status == 2 ? 'selected="selected"' : ''); ?>>VERIFIED</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="nowrap right"><label for="Output">Output : </label></td>
				<td>
					<select id="Output" name="output">
						<option value="web" <?php print($output == "web" ? 'selected="selected"' : '') ?>>Web Browser</option>
						<option value="xls" <?php print($output == "xls" ? 'selected="selected"' : '') ?>>MS Excel</option>
					</select>
					<label for="Orientation"> Posisi Cetak : </label>
					<select id="Orientation" name="orientation">
						<option value="p">Portrait</option>
						<option value="l">Landscape</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="nowrap right">Opsi :</td>
				<td>
					<input type="checkbox" id="ShowNo" name="showNo" value="1" <?php print($showNo ? 'checked="checked"' : '') ?> />
					<label for="ShowNo">Tampilkan No Akun saja.</label><br />
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Generate</button>
				</td>
			</tr>
		</table>
	</form>
</fieldset>


<!-- REGION: LAPORAN -->
<?php
if ($report != null) {
	switch ($status) {
		case 0:
			$subTitle = "JURNAL: " . implode(", ", $buff) . " status: DRAFT";
			break;
		case 1:
			$subTitle = "JURNAL: " . implode(", ", $buff) . " status: APPROVED";
			break;
		case 2:
			$subTitle = "JURNAL: " . implode(", ", $buff) . " status: VERIFIED";
			break;
		default:
			$subTitle = "JURNAL: " . implode(", ", $buff) . " status: SEMUA";
			break;
	}
?>
<br />
<div class="container">
	<div class="title bold">
		<?php printf("%s - %s", $company->CompanyCode, $company->CompanyName); ?><br />
	</div>
	<div class="subTitle">
		<?php print($subTitle); ?><br />
		Periode: <?php printf("%s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)); ?>
        <?php
        if($selectedCabang != null){
            print("<br/>");
            printf('Cabang : %s - %s', $selectedCabang->Kode, $selectedCabang->Cabang);
        }
        ?>
	</div><br /><br />

	<table cellpadding="0" cellspacing="0" class="tablePadding tableBorder">
		<tr class="bold center">
			<th>Tanggal</th>
			<th>No. Jurnal</th>
			<th>Uraian Transaksi</th>
            <th>Akun - Perkiraan</th>
			<th>Debet</th>
			<th>Kredit</th>
		</tr>
		<?php
		$counter = 0;
		$prevDate = null;
		$prevVoucherNo = null;
		$prevSbu = null;
        $prevCst = null;
        $prevVcd = null;

		$flagDate = true;
		$flagVoucherNo = true;
		$flagSbu = true;
        $flagCst = true;
        $flagVcd = true;
		$dsums = 0;
        $csums = 0;
		while ($row = $report->FetchAssoc()) {
			// Convert datetime jadi native format
			//$row["journal_date"] = strtotime($row["journal_date"]);
			$counter++;
			$className = $counter % 2 == 0 ? "itemRow evenRow" : "itemRow oddRow";
			if ($prevDate != $row["journal_date"]) {
				$prevDate = $row["journal_date"];
				$flagDate = true;
			} else {
				$flagDate = false;
			}
			if ($prevVoucherNo != $row["journal_no"]) {
				$prevVoucherNo = $row["journal_no"];
				$flagVoucherNo = true;
			} else {
				$flagVoucherNo = false;
			}
			if ($flagVoucherNo) {
				$link = sprintf('<a href="%s" target="_blank">%s</a>', $helper->site_url("accounting.journal/view/" . $row["id"]), $prevVoucherNo);
			} else {
				$link = "&nbsp;";
			}

			$dsums += $row["db_amount"];
            $csums += $row["cr_amount"];

			printf('<tr class="%s">', $className);
			printf('<td>%s</td>', $flagDate ? $prevDate : "&nbsp;");
			printf('<td>%s</td>', $link);
			printf('<td>%s</td>', $row["keterangan"]);
			printf('<td>%s</td>', $row["acc_code"].' - '.$row["acc_name"]);
			printf('<td class="right">%s</td>', number_format($row["db_amount"], 2));
			printf('<td class="right">%s</td>', number_format($row["cr_amount"], 2));
			print("</tr>");
		} ?>
		<tr class="bold right">
			<td colspan="4">GRAND TOTAL :</td>
			<td><?php print(number_format($dsums, 2)); ?></td>
			<td><?php print(number_format($csums, 2)); ?></td>
		</tr>
	</table>
</div>
<?php } ?>
<!-- END REGION: LAPORAN-->


<!-- </body> -->
</html>
