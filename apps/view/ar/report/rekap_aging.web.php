<!DOCTYPE HTML>
<html>
<head>
	<title>Rekasys - Rekap Aging Piutang Debtor</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#date").customDatePicker();
		});
	</script>
</head>

<body>
<?php /** @var $date int */ /** @var $company Company */ /** @var $report null|ReaderBase */  ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold">Rekap Aging Piutang Debtor</span></legend>

	<form action="<?php print($helper->site_url("ar.report/rekap_aging")); ?>" method="get">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
			<tr>
				<td class="bold right"><label for="date">Per Tanggal :</label></td>
				<td>
					<input type="text" id="date" name="date" value="<?php print(date(JS_DATE, $date)); ?>" size="12" />
				</td>
			</tr>
			<tr>
				<td class="bold right"><label for="output">Format Report :</label></td>
				<td><select id="output" name="output">
					<option value="web">Web Browser</option>
					<option value="pdf">PDF Format (*.pdf)</option>
					<option value="xls">Excel 2003 Format (*.xls)</option>
					<option value="xlsx">Excel 2007 Format (*.xlsx)</option>
				</select></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit">Submit</button></td>
			</tr>
		</table>
	</form>
</fieldset>

<?php if ($report != null) { ?>
<br />
<div class="container">
	<div class="center bold title">
		Rekap Aging Piutang Debtor Company : <?php printf('%s - %s', $company->CompanyCode, $company->CompanyName); ?>
	</div>
	<div class="center bold subTitle">
		Per Tanggal: <?php print(date(HUMAN_DATE, $date)); ?>
	</div>
	<br />

	<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto;">
		<tr class="bold center">
			<td class="bT bL bB">No.</td>
			<td class="bT bL bB">Kode</td>
			<td class="bT bL bB">Nama Debtor/Customer</td>
            <td class="bT bL bB">Terms</td>
			<td class="bT bL bB">1 - 30</td>
			<td class="bT bL bB">31 - 60</td>
			<td class="bT bL bB">61 - 90</td>
			<td class="bT bL bB">91 - 120</td>
			<td class="bT bL bB">121 - 150</td>
			<td class="bT bL bB">&gt; 150</td>
            <td class="bT bL bB">Total</td>
            <td class="bT bL bB">Credit Limit</td>
            <td class="bT bL bB bR">Over Limit</td>
		</tr>
		<?php
		$counter = 0;
		$sums = array(
			"piutang_1" => 0
			, "piutang_2" => 0
			, "piutang_3" => 0
			, "piutang_4" => 0
			, "piutang_5" => 0
			, "piutang_6" => 0
		);
		$subtotal = 0;
		$balance = 0;
		while ($row = $report->FetchAssoc()) {
		    $subtotal = $row["sum_piutang_1"]+$row["sum_piutang_2"]+$row["sum_piutang_3"]+$row["sum_piutang_4"]+$row["sum_piutang_5"]+$row["sum_piutang_6"];
            $balance = $subtotal - $row["credit_limit"];
		    if ($subtotal == 0){
		        continue;
            }
			$counter++;
			$className = $counter % 2 == 0 ? "itemRow evenRow" : "itemRow oddRow";
			$sums["piutang_1"] += $row["sum_piutang_1"];
			$sums["piutang_2"] += $row["sum_piutang_2"];
			$sums["piutang_3"] += $row["sum_piutang_3"];
			$sums["piutang_4"] += $row["sum_piutang_4"];
			$sums["piutang_5"] += $row["sum_piutang_5"];
			$sums["piutang_6"] += $row["sum_piutang_6"];
			$link = $helper->site_url(sprintf("ar.report/detail_aging?date=%s&debtorId=%d&output=web", date(SQL_DATEONLY, $date), $row["id"]));
		?>
		<tr class="<?php print($className); ?>">
			<td class="right bL bB"><?php print($counter); ?>.</td>
			<td class="bL bB"><?php print($row["cus_code"]); ?></td>
            <td class="bL bB" nowrap><a href="<?php print($link); ?>" target="_blank"><?php print($row["cus_name"]); ?></a></td>
            <td class="bL bB right"><?php print($row["term"]); ?></td>
			<td class="right bL bB"><?php print(number_format($row["sum_piutang_1"], 2)); ?></td>
			<td class="right bL bB"><?php print(number_format($row["sum_piutang_2"], 2)); ?></td>
			<td class="right bL bB"><?php print(number_format($row["sum_piutang_3"], 2)); ?></td>
			<td class="right bL bB"><?php print(number_format($row["sum_piutang_4"], 2)); ?></td>
			<td class="right bL bB"><?php print(number_format($row["sum_piutang_5"], 2)); ?></td>
			<td class="right bL bB"><?php print(number_format($row["sum_piutang_6"], 2)); ?></td>
            <td class="right bL bB"><?php print(number_format($subtotal, 2)); ?></td>
            <td class="right bL bB"><?php print(number_format($row["credit_limit"], 2)); ?></td>
            <td class="right bL bB bR" <?php print($balance > 0 ? 'style="color:red"' : '');?>><b><?php print($balance > 0 ? number_format($balance, 2) : ''); ?></b></td>
		</tr>
		<?php } ?>
		<tr class="bold">
			<td colspan="4" class="right bL bB bR">GRAND TOTAL : </td>
			<td class="right bL bB"><?php print(number_format($sums["piutang_1"], 2)); ?></td>
			<td class="right bL bB"><?php print(number_format($sums["piutang_2"], 2)); ?></td>
			<td class="right bL bB"><?php print(number_format($sums["piutang_3"], 2)); ?></td>
			<td class="right bL bB"><?php print(number_format($sums["piutang_4"], 2)); ?></td>
			<td class="right bL bB"><?php print(number_format($sums["piutang_5"], 2)); ?></td>
			<td class="right bL bB"><?php print(number_format($sums["piutang_6"], 2)); ?></td>
            <td class="right bL bB"><?php print(number_format($sums["piutang_1"]+$sums["piutang_2"]+$sums["piutang_3"]+$sums["piutang_4"]+$sums["piutang_5"]+$sums["piutang_6"], 2)); ?></td>
            <td class="right bL bB bR" colspan="2">&nbsp;</td>
		</tr>
	</table>

</div>
	<?php } ?>

</body>
</html>
