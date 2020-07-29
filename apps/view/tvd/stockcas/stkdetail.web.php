<!DOCTYPE HTML>
<html>
<head>
    <title>Rekasys - Laporan Stock Per Periode</title>
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

<?php include(VIEW . "main/menu.php");
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<div id="printArea">
<fieldset>
    <legend><b>POSISI STOCK BARANG</b></legend>
    <form id="frm" action="<?php print($helper->site_url("tvd.stockcas/stkdetail")); ?>" method="post">
        <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <td>
                    Entitas:
                    <select name="entityId" class="text2" id="entityId" required>
                        <option value="0">-- Semua --</option>
                        <?php
                        /** @var $entities ItemEntity[] */
                        foreach ($entities as $eti) {
                                if ($entityId == $eti->Id) {
                                    printf('<option value="%d" selected="selected"> %s - %s </option>', $eti->Id, $eti->Id, $eti->EntityName);
                                } else {
                                    printf('<option value="%d"> %s - %s </option>', $eti->Id, $eti->Id, $eti->EntityName);
                                }
                            }
                        ?>
                    </select>
                </td>
                <td>
                    Gudang:
                    <select name="whId" class="text2" id="whId" required>
                        <?php
                        foreach ($gudangs as $cab) {
                            if ($cab->Id == $whId) {
                                printf('<option value="%d" selected="selected"> %s </option>', $cab->Id, $cab->WhCode);
                            } else {
                                printf('<option value="%d"> %s </option>', $cab->Id, $cab->WhCode);
                            }
                        }
                        ?>

                    </select>
                </td>
                <td>
                    Periode:
                    <input type="text" class="text2" maxlength="10" size="10" id="startDate" name="startDate" value="<?php print(is_int($startDate) ? date(JS_DATE,$startDate) : null);?>" />
                    ~
                    <input type="text" class="text2" maxlength="10" size="10" id="endDate" name="endDate" value="<?php print(is_int($endDate) ? date(JS_DATE,$endDate) : null);?>" />
                </td>
                <td>
                    Output:
                    <select id="outPut" name="outPut">
                    <option value="0" <?php print($outPut == 0 ? 'Selected="Selected"' : '');?>>HTML</option>
                    <option value="1" <?php print($outPut == 1 ? 'Selected="Selected"' : '');?>>Excel</option>
                    </select>
                </td>
                <td colspan="4" class="left">
                    <button type="submit">TAMPILKAN</button>
                    <input type="button" class="button" onclick="printDiv('printArea')" value="Print"/>
                </td>
            </tr>
        </table>
        <br>
        <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">Kode Barang</th>
                <th rowspan="2">Nama Barang</th>
                <th rowspan="2">Satuan</th>
                <th rowspan="2">Awal</th>
                <th colspan="3">Masuk</th>
                <th colspan="3">Keluar</th>
                <th rowspan="2">Koreksi</th>
                <th colspan="4">Stock Akhir</th>
            </tr>
            <tr>
                <th>Pembelian</th>
                <th>Kiriman</th>
                <th>Retur</th>
                <th>Penjualan</th>
                <th>Dikirim</th>
                <th>Retur</th>
                <th>Q</th>
                <th>L</th>
                <th>S</th>
                <th>C</th>
            </tr>
            <?php
            if($mstock != null){
                $nmr = 0;
                $awl = 0;
                $mbl = 0;
                $mxi = 0;
                $mrj = 0;
                $kjl = 0;
                $kxo = 0;
                $krb = 0;
                $kor = 0;
                $ain = 0;
                $aot = 0;
                $sld = 0;
                $ssl = 0;
                $lqty = 0;
                $sqty = 0;
                $cqty = 0;
                $qqty = 0;
                $tqqty = 0;
                $tlqty = 0;
                $tsqty = 0;
                $tcqty = 0;
                while ($row = $mstock->FetchAssoc()) {
                    $nmr++;
                    print('<tr>');
                    printf('<td class="center">%d</td>',$nmr);
                    printf('<td><a href="%s" target="_blank">%s</a></td>',$helper->site_url("inventory.stock/card/".$whId."|".$row["item_id"]),$row["item_code"]);
                    printf('<td>%s</td>',$row["item_name"]);
                    printf('<td>%s</td>',$row["satuan"]);
                    printf('<td class="right">%s</td>',decFormat($row["sAwal"],0));
                    printf('<td class="right">%s</td>',$row["sBeli"] > 0 ? decFormat($row["sBeli"]) : '');
                    printf('<td class="right">%s</td>',$row["sXin"] > 0 ? decFormat($row["sXin"]) : '');
                    printf('<td class="right">%s</td>',$row["sRjual"] > 0 ? decFormat($row["sRjual"]) : '');
                    printf('<td class="right">%s</td>',$row["sJual"] > 0 ? decFormat($row["sJual"]) : '');
                    printf('<td class="right">%s</td>',$row["sXout"] > 0 ? decFormat($row["sXout"]) : '');
                    printf('<td class="right">%s</td>',$row["sRbeli"] > 0 ? decFormat($row["sRbeli"]) : '');
                    printf('<td class="right">%s</td>',$row["sKoreksi"] <> 0 ? decFormat($row["sKoreksi"]) : '');
                    $sld = ($row["sAwal"] + $row["sBeli"] + $row["sAsyin"] + $row["sXin"] + $row["sRjual"]) - ($row["sJual"] + $row["sAsyout"] + $row["sXout"] + $row["sRbeli"]) + $row["sKoreksi"];
                    if ($sld >= $row["s_uom_qty"] && $row["s_uom_qty"] > 0){
                        $aqty = array();
                        $sqty = round($sld/$row["s_uom_qty"],2);
                        $aqty = explode('.',$sqty);
                        $lqty = $aqty[0];
                        $sqty = $sld - ($lqty * $row["s_uom_qty"]);
                    }else {
                        $lqty = 0;
                        $sqty = $sld;
                    }
                    printf('<td class="right">%s</td>',decFormat($sld));
                    printf('<td class="right">%s</td>',decFormat($lqty));
                    printf('<td class="right">%s</td>',decFormat($sqty));
                    if ($row["entity_id"] == 1){
                        $cqty = round($sld * $row["qty_convert"],2);
                        printf("<td align='right'>%s</td>",number_format($cqty,2));
                    }else{
                        $cqty = 0;
                        print("<td>&nbsp;</td>");
                    }
                    print('</tr>');
                    $awl+= $row["sAwal"];
                    $mbl+= $row["sBeli"];
                    $mxi+= $row["sXin"];
                    $mrj+= $row["sRjual"];
                    $kjl+= $row["sJual"];
                    $kxo+= $row["sXout"];
                    $krb+= $row["sRbeli"];
                    $kor+= $row["sKoreksi"];
                    $ain+= $row["sAsyin"];
                    $aot+= $row["sAsyout"];
                    $ssl+= $sld;
                    $tlqty+= $lqty;
                    $tsqty+= $sqty;
                    $tcqty+= $cqty;
                }
                printf('<tr>');
                printf('<td class="bold right" colspan="4">Total Mutasi</td>');
                printf('<td class="bold right">%s</td>',decFormat($awl,2));
                printf('<td class="bold right">%s</td>',decFormat($mbl,2));
                printf('<td class="bold right">%s</td>',decFormat($mxi,2));
                printf('<td class="bold right">%s</td>',decFormat($mrj,2));
                printf('<td class="bold right">%s</td>',decFormat($kjl,2));
                printf('<td class="bold right">%s</td>',decFormat($kxo,2));
                printf('<td class="bold right">%s</td>',decFormat($krb,2));
                printf('<td class="bold right">%s</td>',decFormat($kor,2));
                printf('<td class="bold right">%s</td>',decFormat($ssl,2));
                printf('<td class="bold right">%s</td>',decFormat($tlqty,2));
                printf('<td class="bold right">%s</td>',decFormat($tsqty,2));
                printf('<td class="bold right">%s</td>',decFormat($tcqty,2));
                printf('</tr>');
            }
            ?>
        </table>
    </form>
</fieldset>
</div>
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
