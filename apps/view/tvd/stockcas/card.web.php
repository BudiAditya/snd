<!DOCTYPE HTML>
<?php
/** @var $stock Stock */
/** @var $whs Warehouse */
/** @var $its Items */
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
?>
<html>
<head>
    <title>Rekasys - Kartu Stock Barang</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            //var elements = ["CabangId", "OpDate","ItemType", "ItemId", "PartId", "OpQty", "OpPrice"];
            //BatchFocusRegister(elements);
            $("#startDate").customDatePicker({ showOn: "focus" });
            $("#endDate").customDatePicker({ showOn: "focus" });
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
    <div id="printArea">
    <h3>Kartu Stock Barang</h3>
    <form id="frm" action="<?php print($helper->site_url("inventory.stock/card/".$stock->WarehouseId.'|'.$stock->ItemId)); ?>" method="post">
        <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <td>Gudang :</td>
                <td class="bold"><?php printf('%s - %s',$whs->WhCode,strtoupper($whs->WhName)) ?></td>
                <td>Nama Barang :</td>
                <td class="bold"><?php print($its->ItemName); ?></td>
                <td align="right">Kode :</td>
                <td class="bold"><?php print($its->ItemCode); ?></td>
                <td>Satuan :</td>
                <td class="bold"><?php print($its->SuomCode); ?></td>
            </tr>
            <tr>
                <td>Dari Tgl :</td>
                <td><input type="text" class="text2" maxlength="10" size="10" id="startDate" name="startDate" value="<?php print(is_int($startDate) ? date(JS_DATE,$startDate) : null);?>" /></td>
                <td>Sampai Tgl :</td>
                <td><input type="text" class="text2" maxlength="10" size="10" id="endDate" name="endDate" value="<?php print(is_int($endDate) ? date(JS_DATE,$endDate) : null);?>" /></td>
                <td>Output :</td>
                <td><select id="outPut" name="outPut">
                        <option value="0" <?php print($outPut == 0 ? 'Selected="Selected"' : '');?>> HTML</option>
                        <option value="1" <?php print($outPut == 1 ? 'Selected="Selected"' : '');?>> Excel</option>
                    </select>
                </td>
                <td colspan="4" class="left">
                    <button type="submit">Tampilkan</button>
                    <input type="button" class="button" onclick="printDiv('printArea')" value="Print"/>
                </td>
            </tr>
        </table>
        <br>
        <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Transaksi</th>
                <th>Relasi</th>
                <th>Keterangan</th>
                <th>Awal</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Koreksi</th>
                <th>Saldo</th>
            </tr>
            <?php
            if($stkcard != null){
                $saldo = 0;
                $trxdate = null;
                $nmr = 0;
                while ($row = $stkcard->FetchAssoc()) {
                    $nmr++;
                    print('<tr>');
                    printf('<td class="center">%d</td>',$nmr);
                    if($trxdate <> $row["trx_date"]){
                        printf('<td>%s</td>',$row["trx_date"]);
                    }else{
                        print('<td>-</td>');
                    }
                    if ($nmr == 1){
                        $saldo = $row["saldo"];
                        printf('<td>%s</td>',$row["trx_type"]);
                    }else{
                        $saldo = ($saldo + $row["awalcas"] + $row["masuk"] + $row["koreksi"]) - $row["keluar"];
                        printf('<td><a href="%s" target="_blank">%s</a></td>',$helper->site_url($row["trx_url"]),$row["trx_type"]);
                    }
                    printf('<td>%s</td>',$row["relasi"]);
                    printf('<td>%s</td>',$row["notes"]);
                    printf('<td class="right">%s</td>', $row["awalcas"] > 0 ? decFormat($row["awalcas"]) : '');
                    printf('<td class="right">%s</td>', $row["masuk"] > 0 ? decFormat($row["masuk"]) : '');
                    printf('<td class="right">%s</td>', $row["keluar"] > 0 ? decFormat($row["keluar"]) : '');
                    printf('<td class="right">%s%s</td>', $row["koreksi"] > 0 ? '+' : '', $row["koreksi"] <> 0 ? decFormat($row["koreksi"]) : '');
                    printf('<td class="right">%s</td>', decFormat($saldo));
                    print('</tr>');
                    $trxdate = $row["trx_date"];
                }
            }
            ?>
        </table>
    </form>
    </div>
</fieldset>
<br>
<?php
print('<i>* Printed by: ' . $userName . '  - Time: ' . date('d-m-Y h:i:s') . ' *</i>');
?>
<script type="text/javascript">
    function printDiv(divName) {
        //if (confirm('Print Invoice ini?')) {
        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.print();

        document.body.innerHTML = originalContents;
        //}
    }
</script>
<!-- </body> -->
</html>
