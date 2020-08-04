<!DOCTYPE HTML>
<html>
<?php /** @var $customers Customer[] */ /** @var $sales Salesman[] */
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
?>
<head>
	<title>SND System - Rekapitulasi Nota/Invoice/Piutang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#StartDate").customDatePicker({ showOn: "focus" });
            $("#EndDate").customDatePicker({ showOn: "focus" });
        });
    </script>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<h2>REKAPITULASI PENJUALAN</h2>
<form id="frm" name="frmReport" method="post">
    <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
        <tr class="left">
            <th colspan="6">
                Sales Area :
                <select name="SalesAreaId" id="SalesAreaId" style="width: 130px">
                    <option value="0">-- Semua --</option>
                    <?php
                    /** @var $areaList SalesArea[] **/
                    foreach ($areaList as $area){
                        if ($area->Id == $SalesAreaId){
                            printf('<option value="%d" selected="selected">%s</option>',$area->Id,$area->AreaName);
                        }else {
                            printf('<option value="%d">%s</option>', $area->Id, $area->AreaName);
                        }
                    }
                    ?>
                </select>
                Entitas :
                <select name="EntityId" id="EntityId">
                    <option value="0">-- Semua --</option>
                    <?php
                    /** @var $entities ItemEntity[] **/
                    foreach ($entities as $eti){
                        if ($eti->Id == $EntityId){
                            printf('<option value="%d" selected="selected">%s</option>',$eti->Id,$eti->EntityName);
                        }else {
                            printf('<option value="%d">%s</option>', $eti->Id, $eti->EntityName);
                        }
                    }
                    ?>
                </select>
                Principal :
                <select name="PrincipalId" id="PrincipalId" style="width: 200px">
                    <option value="0">-- Semua --</option>
                    <?php
                    /** @var $principaList ItemPrincipal[] **/
                    foreach ($principaList as $eti){
                        if ($eti->Id == $PrincipalId){
                            printf('<option value="%d" selected="selected">%s</option>',$eti->Id,$eti->PrincipalName);
                        }else {
                            printf('<option value="%d">%s</option>', $eti->Id, $eti->PrincipalName);
                        }
                    }
                    ?>
                </select>
                Brand :
                <select name="BrandId" id="BrandId" style="width: 130px">
                    <option value="0">-- Semua --</option>
                    <?php
                    /** @var $brandList ItemBrand[] **/
                    foreach ($brandList as $eti){
                        if ($eti->Id == $BrandId){
                            printf('<option value="%d" selected="selected">%s</option>',$eti->Id,$eti->BrandName);
                        }else {
                            printf('<option value="%d">%s</option>', $eti->Id, $eti->BrandName);
                        }
                    }
                    ?>
                </select>
            </th>
            <th>Jenis Laporan :</th>
            <th colspan="2"><select name="JnsLaporan" id="JnsLaporan">
                    <option value="1" <?php print($JnsLaporan == 1 ? 'selected="selected"' : '');?>>1 - Rekap Per Invoice</option>
                    <option value="2" <?php print($JnsLaporan == 2 ? 'selected="selected"' : '');?>>2 - Rekap Invoice Detail</option>
                    <option value="3" <?php print($JnsLaporan == 3 ? 'selected="selected"' : '');?>>3 - Rekap Item Terjual</option>
                    <option value="4" <?php print($JnsLaporan == 4 ? 'selected="selected"' : '');?>>4 - Rekap Per Outlet</option>
                    <option value="5" <?php print($JnsLaporan == 5 ? 'selected="selected"' : '');?>>5 - Rekap Per Produk</option>
                    <option value="6" <?php print($JnsLaporan == 6 ? 'selected="selected"' : '');?>>6 - Rekap Omset Salesman</option>
                    <option value="7" <?php print($JnsLaporan == 7 ? 'selected="selected"' : '');?>>7 - Rekap Omset Per Entitas</option>
                    <option value="8" <?php print($JnsLaporan == 8 ? 'selected="selected"' : '');?>>8 - Omset Salesman Detail</option>
                    <option value="9" <?php print($JnsLaporan == 9 ? 'selected="selected"' : '');?>>9 - Rekap Omset Per Principal</option>
                </select>
            </th>
        </tr>
        <tr class="center">
            <th>Cabang</th>
            <th>Customer</th>
            <th>Salesman</th>
            <th>Status Invoice</th>
            <th>Status Lunas</th>
            <th>Dari Tanggal</th>
            <th>Sampai Tanggal</th>
            <th>Output</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select name="CabangId" class="text2" id="CabangId" style="width: 100px" required>
                <option value="0">- Semua Cabang -</option>
                <?php
                /** @var $cabangs Cabang[] */
                foreach ($cabangs as $cab) {
                    if ($cab->Id == $CabangId) {
                        printf('<option value="%d" selected="selected">%s</option>', $cab->Id, $cab->Cabang);
                    } else {
                        printf('<option value="%d">%s</option>', $cab->Id, $cab->Cabang);
                    }
                }
                ?>
                </select>
            </td>
            <td>
                <select id="ContactsId" name="ContactsId" style="width: 150px" required>
                    <option value="0">- Semua Customer -</option>
                    <?php
                    foreach ($customers as $customer) {
                        if ($ContactsId == $customer->Id){
                            printf('<option value="%d" selected="selected"> %s - %s </option>',$customer->Id,$customer->CusCode,$customer->CusName);
                        }else{
                            printf('<option value="%d"> %s - %s </option>',$customer->Id,$customer->CusCode,$customer->CusName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="SalesId" name="SalesId" style="width: 150px" required>
                    <option value="0">- Semua Salesman -</option>
                    <?php
                    foreach ($sales as $salesman) {
                        if ($salesman->Id == $SalesId) {
                            printf('<option value="%d" selected="selected">%s</option>', $salesman->Id, $salesman->SalesName);
                        } else {
                            printf('<option value="%d">%s</option>', $salesman->Id, $salesman->SalesName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="Status" name="Status" required>
                    <option value="-1" <?php print($Status == -1 ? 'selected="selected"' : '');?>> - Semua Status -</option>
                    <option value="0" <?php print($Status == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                    <option value="1" <?php print($Status == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                    <option value="2" <?php print($Status == 2 ? 'selected="selected"' : '');?>>2 - Approved</option>
                    <option value="3" <?php print($Status == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                </select>
            </td>
            <td>
                <select id="PaymentStatus" name="PaymentStatus" required>
                    <option value="-1" <?php print($PaymentStatus == -1 ? 'selected="selected"' : '');?>> - Semua Status -</option>
                    <option value="0" <?php print($PaymentStatus == 0 ? 'selected="selected"' : '');?>>0 - Belum Lunas</option>
                    <option value="1" <?php print($PaymentStatus == 1 ? 'selected="selected"' : '');?>>1 - Lunas</option>
                </select>
            </td>
            <td><input type="text" class="text2" maxlength="10" size="10" id="StartDate" name="StartDate" value="<?php printf(date('d-m-Y',$StartDate));?>"/></td>
            <td><input type="text" class="text2" maxlength="10" size="10" id="EndDate" name="EndDate" value="<?php printf(date('d-m-Y',$EndDate));?>"/></td>
            <td>
                <select id="Output" name="Output" required>
                    <option value="0" <?php print($Output == 0 ? 'selected="selected"' : '');?>>0 - Web Html</option>
                    <option value="1" <?php print($Output == 1 ? 'selected="selected"' : '');?>>1 - Excel</option>
                </select>
            </td>
            <td>
                <button type="submit" formaction="<?php print($helper->site_url("ar.invoice/report")); ?>"><b>TAMPILKAN</b></button>
                <input type="button" class="button" onclick="printDiv('printArea')" value="Print"/>
            </td>
        </tr>
    </table>
</form>
<!-- start web report -->
<div id="printArea">
<?php  if ($Reports != null){
    if ($JnsLaporan < 3){
    ?>
        <h3>Rekapitulasi A/R Invoice</h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Cab</th>
                <th>Tanggal</th>
                <th>No. Invoice</th>
                <th>Customer</th>
                <th nowrap="nowrap">Cs Code</th>
                <th>Area</th>
                <th>Salesman</th>
                <?php
                if ($JnsLaporan == 1){
                    print("<th>JTP</th>");
                    print("<th>D P P</th>");
                    print("<th>P P N</th>");
                    print("<th>Jumlah</th>");
                    print("<th>Retur</th>");
                    print("<th>Terbayar</th>");
                    print("<th>Outstanding</th>");
                }elseif ($JnsLaporan == 2){
                    print("<th nowrap='nowrap'>Brand</th>");
                    print("<th nowrap='nowrap'>Kode</th>");
                    print("<th nowrap='nowrap'>Nama Barang</th>");
                    print("<th>QTY</th>");
                    print("<th>Harga</th>");
                    print("<th>Jumlah</th>");
                    print("<th>Discount</th>");
                    print("<th>DPP</th>");
                    print("<th>PPN</th>");
                    print("<th>Total</th>");
                }
                ?>
            </tr>
            <?php
                $nmr = 0;
                $tDsc = 0;
                $tDpp = 0;
                $tPpn = 0;
                $tOtal = 0;
                $subTotal = 0;
                $tTerbayar = 0;
                $tReturn = 0;
                $tSisa = 0;
                $url = null;
                $ivn = null;
                $sma = false;
                while ($row = $Reports->FetchAssoc()) {
                    $nmr++;
                    $url = $helper->site_url("ar.invoice/view/" . $row["id"]);
                    print("<tr valign='Top'>");
                    printf("<td>%s</td>", $nmr);
                    printf("<td nowrap='nowrap'>%s</td>", $row["cabang_code"]);
                    printf("<td nowrap='nowrap'>%s</td>", date('d-m-Y', strtotime($row["invoice_date"])));
                    printf("<td nowrap='nowrap'><a href= '%s' target='_blank'>%s</a></td>", $url, $row["invoice_no"]);
                    printf("<td nowrap='nowrap'>%s</td>", $row["customer_name"]);
                    printf("<td nowrap='nowrap'>%s</td>", $row["customer_code"]);
                    printf("<td nowrap='nowrap'>%s</td>", $row["area_code"]);
                    printf("<td nowrap='nowrap'>%s</td>", $row["sales_name"]);
                    if ($JnsLaporan == 1){
                        printf("<td nowrap='nowrap'>%s</td>", date('d-m-Y', strtotime($row["due_date"])));
                        printf("<td align='right'>%s</td>", number_format($row["base_amount"] - $row["disc_amount"], 0));
                        printf("<td align='right'>%s</td>", number_format($row["ppn_amount"], 0));
                        printf("<td align='right'>%s</td>", number_format($row["total_amount"], 0));
                        printf("<td align='right'>%s</td>", number_format($row["return_amount"], 0));
                        printf("<td align='right'>%s</td>", number_format($row["paid_amount"], 0));
                        printf("<td align='right'>%s</td>", number_format($row["balance_amount"], 0));
                        print("</tr>");
                        $tDpp+= $row["base_amount"] - $row["disc_amount"];
                        $tPpn+= $row["ppn_amount"];
                        $tOtal+= $row["total_amount"];
                        $tReturn+= $row["return_amount"];
                        $tTerbayar+= $row["paid_amount"];
                        $tSisa+= $row["balance_amount"];
                    }elseif ($JnsLaporan == 2){
                        printf("<td nowrap='nowrap'>%s</td>", $row['brand_name']);
                        printf("<td nowrap='nowrap'>%s</td>", $row['item_code']);
                        printf("<td nowrap='nowrap'>%s</td>", $row['item_name']);
                        printf("<td align='right'>%s</td>", number_format($row['sales_qty'], 0));
                        printf("<td align='right' >%s</td>", number_format($row['price'], 0));
                        printf("<td align='right'>%s</td>", number_format($row['sub_total'], 0));
                        printf("<td align='right'>%s</td>", number_format($row['disc_amount'], 0));
                        printf("<td align='right'>%s</td>", number_format($row['sub_total']-$row['disc_amount'],0));
                        printf("<td align='right'>%s</td>", number_format($row['ppn_amount'], 0));
                        printf("<td align='right'>%s</td>", number_format(($row['sub_total']-$row['disc_amount']) + $row['ppn_amount'], 0));
                        print("</tr>");
                        $subTotal+= $row['sub_total'];
                        $tDsc+= $row['disc_amount'];
                        $tDpp+= $row['sub_total']-$row['disc_amount'];
                        $tPpn+= $row['ppn_amount'];
                    }
                    $ivn = $row["invoice_no"];
                }
            print("<tr class='bold'>");
            print("<td colspan='10' align='right'>Total Invoice</td>");
            if ($JnsLaporan == 1) {
                printf("<td align='right'>%s</td>", number_format($tDpp, 0));
                printf("<td align='right'>%s</td>", number_format($tPpn, 0));
                printf("<td align='right'>%s</td>", number_format($tOtal, 0));
                printf("<td align='right'>%s</td>", number_format($tReturn, 0));
                printf("<td align='right'>%s</td>", number_format($tTerbayar, 0));
                printf("<td align='right'>%s</td>", number_format($tSisa, 0));
            }
            if ($JnsLaporan == 2) {
                print("<td colspan='3'>&nbsp;</td>");
                printf("<td align='right'>%s</td>", number_format($subTotal, 0));
                printf("<td align='right'>%s</td>", number_format($tDsc, 0));
                printf("<td align='right'>%s</td>", number_format($tDpp, 0));
                printf("<td align='right'>%s</td>", number_format($tPpn, 0));
                printf("<td align='right'>%s</td>", number_format($tDpp+$tPpn, 0));
            }
            print("</tr>");
            ?>
        </table>
<?php }elseif ($JnsLaporan == 3){ ?>
        <h3>Rekapitulasi Item Terjual</h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Brand</th>
                <th>Kode</th>
                <th>Nama Produk</th>
                <th>L</th>
                <th>S</th>
                <th>PCS</th>
                <th>LTR</th>
                <th>DPP</th>
                <th>PPN</th>
                <th>Nilai</th>
            </tr>
            <?php
            $nmr = 0;
            $lqty = 0;
            $sqty = 0;
            $cqty = 0;
            $tlqty = 0;
            $tsqty = 0;
            $tcqty = 0;
            $tqty  = 0;
            $tdpp = 0;
            $tppn = 0;
            $stotal = 0;
            while ($row = $Reports->FetchAssoc()) {
                $nmr++;
                if ($row["entity_id"] == 1) {
                    $cqty = round($row["sum_qty"] * $row["qty_convert"], 2);
                }else{
                    $cqty = 0;
                }
                print("<tr valign='Top'>");
                printf("<td>%s</td>", $nmr);
                printf("<td>%s</td>",$row['brand_name']);
                printf("<td>%s</td>",$row['item_code']);
                printf("<td>%s</td>",$row['item_name']);
                printf("<td align='right'>%s</td>", $row['sum_lqty'] == 0 ? '' : number_format($row['sum_lqty'],0));
                printf("<td align='right'>%s</td>", $row['sum_sqty'] == 0 ? '' : number_format($row['sum_sqty'],0));
                printf("<td align='right'>%s</td>", $row['sum_qty'] == 0 ? '' : number_format($row['sum_qty'],0));
                printf("<td align='right'>%s</td>",$cqty == 0 ? '' : number_format($cqty,2));
                printf("<td align='right'>%s</td>",number_format($row['sum_dpp'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_ppn'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_dpp']+$row['sum_ppn'],0));
                print("</tr>");
                $tlqty+= $row['sum_lqty'];
                $tsqty+= $row['sum_sqty'];
                $tqty+= $row['sum_qty'];
                $tcqty+= $cqty;
                $tdpp+= $row['sum_dpp'];
                $tppn+= $row['sum_ppn'];
                $stotal+= $row['sum_dpp']+$row['sum_ppn'];
            }
            print("<tr class='bold'>");
            print("<td colspan='4' align='right'>Total.....</td>");
            printf("<td align='right'>%s</td>",number_format($tlqty,0));
            printf("<td align='right'>%s</td>",number_format($tsqty,0));
            printf("<td align='right'>%s</td>",number_format($tqty,0));
            printf("<td align='right'>%s</td>",number_format($tcqty,2));
            printf("<td align='right'>%s</td>",number_format($tdpp,0));
            printf("<td align='right'>%s</td>",number_format($tppn,0));
            printf("<td align='right'>%s</td>",number_format($stotal,0));
            print("</tr>");
            ?>
        </table>
<!-- end web report -->
<?php }elseif ($JnsLaporan == 4){ ?>
        <h3>REKAPITULASI PER OUTLET - <?php print($userCabName);?></h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>No. Invoice</th>
                <th>Outlet</th>
                <th>Nama Outlet</th>
                <th>Alamat</th>
                <th>Salesman</th>
                <th>QTY</th>
                <th>Jumlah</th>
            </tr>
            <?php
            $nmr = 1;
            $tDpp = 0;
            $tPpn = 0;
            $tOtal = 0;
            $subTotal = 0;
            $tTerbayar = 0;
            $tSisa = 0;
            $tQty = 0;
            $url = null;
            $ivn = null;
            $sma = false;
            while ($row = $Reports->FetchAssoc()) {
                $url = $helper->site_url("ar.invoice/view/" . $row["id"]);
                print("<tr valign='Top'>");
                printf("<td>%s</td>", $nmr);
                printf("<td nowrap='nowrap'>%s</td>", date('d-m-Y', strtotime($row["invoice_date"])));
                printf("<td nowrap='nowrap'><a href= '%s' target='_blank'>%s</a></td>", $url, $row["invoice_no"]);
                printf("<td nowrap='nowrap'>%s</td>", $row["customer_code"]);
                printf("<td nowrap='nowrap'>%s</td>", $row["customer_name"]);
                printf("<td nowrap='nowrap'>%s</td>", $row["customer_address"]);
                printf("<td nowrap='nowrap'>%s</td>", $row["sales_name"]);
                printf("<td align='right'>%s</td>", number_format($row["sum_qty"], 0));
                printf("<td align='right'>%s</td>", number_format($row["total_amount"], 0));
                print("</tr>");
                $tDpp+= $row["base_amount"];
                $tPpn+= $row["ppn_amount"];
                $tOtal+= $row["total_amount"];
                $tTerbayar+= $row["paid_amount"];
                $tSisa+= $row["balance_amount"];
                $tQty+= $row["sum_qty"];
                $nmr++;
            }
            print("<tr class='bold'>");
            print("<td colspan='7' align='right'>Total </td>");
            printf("<td align='right'>%s</td>",number_format($tQty,0));
            printf("<td align='right'>%s</td>",number_format($tOtal,0));
            print("</tr>");
            ?>
        </table>
    <?php }elseif ($JnsLaporan == 5){ ?>
        <h3>REKAPITULASI PER PRODUK - <?php print($userCabName);?></h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Kode</th>
                <th>Brand</th>
                <th>Nama Produk</th>
                <th>L</th>
                <th>S</th>
                <th>Q</th>
                <th>C</th>
            </tr>
            <?php
            $lqty = 0;
            $sqty = 0;
            $cqty = 0;
            $qqty = 0;
            $tqqty = 0;
            $tlqty = 0;
            $tsqty = 0;
            $tcqty = 0;
            $nmr = 0;
            while ($row = $Reports->FetchAssoc()) {
                $nmr++;
                $qqty = $row["sum_qty"];
                if ($qqty >= $row["s_uom_qty"] && $row["s_uom_qty"] > 0){
                    $aqty = array();
                    $sqty = round($qqty/$row["s_uom_qty"],2);
                    $aqty = explode('.',$sqty);
                    $lqty = $aqty[0];
                    $sqty = $qqty - ($lqty * $row["s_uom_qty"]);
                }else {
                    $lqty = 0;
                    $sqty = $qqty;
                }
                print("<tr valign='Top'>");
                printf("<td>%s</td>", $nmr);
                printf("<td>%s</td>",$row['item_code']);
                printf("<td>%s</td>",$row['brand_name']);
                printf("<td>%s</td>",$row['item_name']);
                printf("<td align='right'>%s</td>",number_format($lqty,0));
                printf("<td align='right'>%s</td>",number_format($sqty,0));
                printf("<td align='right'>%s</td>",number_format($qqty,0));
                if ($row["entity_id"] == 1){
                    $cqty = round($qqty * $row["qty_convert"],2);
                    printf("<td align='right'>%s</td>",number_format($cqty,2));
                }else{
                    $cqty = 0;
                    print("<td>&nbsp;</td>");
                }

                print("</tr>");
                $tlqty+= $lqty;
                $tsqty+= $sqty;
                $tcqty+= $cqty;
                $tqqty+= $qqty;
            }
            print("<tr class='bold'>");
            print("<td colspan='6' align='right'>Total.....</td>");
            printf("<td align='right'>%s</td>",number_format($tqqty,0));
            printf("<td align='right'>%s</td>",number_format($tcqty,2));
            print("</tr>");
            ?>
        </table>
    <?php }elseif ($JnsLaporan == 6){ ?>
        <h3>REKAPITULASI OMSET PENJUALAN SALESMAN - <?php print($userCabName);?></h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>NAMA SALESMAN</th>
                <th>D P P</th>
                <th>P P N</th>
                <th>TOTAL</th>
                <th>RETUR</th>
                <th>TERBAYAR</th>
                <th>OUTSTANDING</th>
            </tr>
            <?php
            $nmr = 0;
            $sdpp = 0;
            $sppn = 0;
            $sram = 0;
            $spam = 0;
            while ($row = $Reports->FetchAssoc()) {
                $nmr++;
                print("<tr valign='Top'>");
                printf("<td>%s</td>", $nmr);
                printf("<td>%s</td>",$row['sales_name']);
                printf("<td align='right'>%s</td>",number_format($row['sum_dpp'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_ppn'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_dpp']+$row['sum_ppn'],0));
                printf("<td align='right'>%s</td>",number_format($row['return_amount'],0));
                printf("<td align='right'>%s</td>",number_format($row['paid_amount'],0));
                printf("<td align='right'>%s</td>",number_format(($row['sum_dpp']+$row['sum_ppn']) - ($row['paid_amount']+$row['return_amount']),0));
                print("</tr>");
                $sdpp+= $row['sum_dpp'];
                $sppn+= $row['sum_ppn'];
                $sram+= $row['return_amount'];
                $spam+= $row['paid_amount'];
            }
            print("<tr class='bold'>");
            print("<td colspan='2' align='right'>T O T A L .....</td>");
            printf("<td align='right'>%s</td>",number_format($sdpp,0));
            printf("<td align='right'>%s</td>",number_format($sppn,0));
            printf("<td align='right'>%s</td>",number_format($sdpp+$sppn,0));
            printf("<td align='right'>%s</td>",number_format($sram,0));
            printf("<td align='right'>%s</td>",number_format($spam,0));
            printf("<td align='right'>%s</td>",number_format(($sdpp+$sppn)-($sram+$spam),0));
            print("</tr>");
            ?>
        </table>
    <?php }elseif ($JnsLaporan == 7){ ?>
        <h3>REKAPITULASI OMSET PENJUALAN PER ENTITAS - <?php print($userCabName);?></h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>KODE</th>
                <th>ENTITAS</th>
                <th>QTY</th>
                <th>LITER</th>
                <th>DPP</th>
                <th>PPN</th>
                <th>TOTAL</th>
            </tr>
            <?php
            $nmr = 0;
            $sdpp = 0;
            $sppn = 0;
            $sqty = 0;
            $sltr = 0;
            while ($row = $Reports->FetchAssoc()) {
                $nmr++;
                print("<tr valign='Top'>");
                printf("<td>%s</td>", $nmr);
                printf("<td>%s</td>",$row['entity_code']);
                printf("<td>%s</td>",$row['entity_name']);
                printf("<td align='right'>%s</td>",number_format($row['sum_qty'],0));
                if ($row["entity_code"] == 'CAS') {
                    printf("<td align='right'>%s</td>", number_format($row['sum_liter'], 2));
                    $sltr+= $row['sum_liter'];
                }else{
                    print("<td>&nbsp;</td>");
                }
                printf("<td align='right'>%s</td>",number_format($row['sum_dpp'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_ppn'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_dpp']+$row['sum_ppn'],0));
                print("</tr>");
                $sdpp+= $row['sum_dpp'];
                $sppn+= $row['sum_ppn'];
                $sqty+= $row['sum_qty'];
            }
            print("<tr class='bold'>");
            print("<td colspan='3' align='right'>T O T A L .....</td>");
            printf("<td align='right'>%s</td>",number_format($sqty,0));
            printf("<td align='right'>%s</td>",number_format($sltr,2));
            printf("<td align='right'>%s</td>",number_format($sdpp,0));
            printf("<td align='right'>%s</td>",number_format($sppn,0));
            printf("<td align='right'>%s</td>",number_format($sdpp+$sppn,0));
            print("</tr>");
            ?>
        </table>
    <?php }elseif ($JnsLaporan == 8){ ?>
        <h3>REKAPITULASI OMSET PENJUALAN PER SALES DETAIL - <?php print($userCabName);?></h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>SALES</th>
                <th>ENTITAS</th>
                <th>QTY</th>
                <th>LITER</th>
                <th>DPP</th>
                <th>PPN</th>
                <th>TOTAL</th>
            </tr>
            <?php
            $nmr = 0;
            $sdpp = 0;
            $sppn = 0;
            $sqty = 0;
            $sltr = 0;
            $snm = null;
            while ($row = $Reports->FetchAssoc()) {
                print("<tr valign='Top'>");
                if ($snm <> $row['sales_name']){
                    $nmr++;
                    printf("<td>%s</td>", $nmr);
                    printf("<td>%s</td>",$row['sales_name']);
                }else{
                    print("<td>&nbsp;</td><td>&nbsp;</td>");
                }
                printf("<td>%s</td>",$row['entity_name']);
                printf("<td align='right'>%s</td>",number_format($row['sum_qty'],0));
                if ($row["entity_code"] == 'CAS') {
                    printf("<td align='right'>%s</td>", number_format($row['sum_liter'], 2));
                    $sltr+= $row['sum_liter'];
                }else{
                    print("<td>&nbsp;</td>");
                }
                printf("<td align='right'>%s</td>",number_format($row['sum_dpp'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_ppn'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_dpp']+$row['sum_ppn'],0));
                print("</tr>");
                $sdpp+= $row['sum_dpp'];
                $sppn+= $row['sum_ppn'];
                $sqty+= $row['sum_qty'];
                $snm = $row["sales_name"];
            }
            print("<tr class='bold'>");
            print("<td colspan='3' align='right'>T O T A L .....</td>");
            printf("<td align='right'>%s</td>",number_format($sqty,0));
            printf("<td align='right'>%s</td>",number_format($sltr,2));
            printf("<td align='right'>%s</td>",number_format($sdpp,0));
            printf("<td align='right'>%s</td>",number_format($sppn,0));
            printf("<td align='right'>%s</td>",number_format($sdpp+$sppn,0));
            print("</tr>");
            ?>
        </table>
    <?php }elseif ($JnsLaporan == 9){ ?>
        <h3>REKAPITULASI OMSET PENJUALAN PER PRINCIPAL - <?php print($userCabName);?></h3>
        <?php printf("Dari Tgl. %s - %s",date('d-m-Y',$StartDate),date('d-m-Y',$EndDate));?>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>KODE</th>
                <th>NAMA PRINCIPAL</th>
                <th>QTY</th>
                <th>LITER</th>
                <th>DPP</th>
                <th>PPN</th>
                <th>TOTAL</th>
            </tr>
            <?php
            $nmr = 0;
            $sdpp = 0;
            $sppn = 0;
            $sqty = 0;
            $sltr = 0;
            while ($row = $Reports->FetchAssoc()) {
                $nmr++;
                print("<tr valign='Top'>");
                printf("<td>%s</td>", $nmr);
                printf("<td>%s</td>",$row['principal_code']);
                printf("<td>%s</td>",$row['principal_name']);
                printf("<td align='right'>%s</td>",number_format($row['sum_qty'],0));
                if ($row["principal_code"] == '001') {
                    printf("<td align='right'>%s</td>", number_format($row['sum_liter'], 2));
                    $sltr+= $row['sum_liter'];
                }else{
                    print("<td>&nbsp;</td>");
                }
                printf("<td align='right'>%s</td>",number_format($row['sum_dpp'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_ppn'],0));
                printf("<td align='right'>%s</td>",number_format($row['sum_dpp']+$row['sum_ppn'],0));
                print("</tr>");
                $sdpp+= $row['sum_dpp'];
                $sppn+= $row['sum_ppn'];
                $sqty+= $row['sum_qty'];
            }
            print("<tr class='bold'>");
            print("<td colspan='3' align='right'>T O T A L .....</td>");
            printf("<td align='right'>%s</td>",number_format($sqty,0));
            printf("<td align='right'>%s</td>",number_format($sltr,2));
            printf("<td align='right'>%s</td>",number_format($sdpp,0));
            printf("<td align='right'>%s</td>",number_format($sppn,0));
            printf("<td align='right'>%s</td>",number_format($sdpp+$sppn,0));
            print("</tr>");
            ?>
        </table>
    <?php }} ?>
</div>
<br>
<?php if($Reports != null){ ?>
    <?php
    print('<i>* Printed by: '.$userName.'  - Time: '.date('d-m-Y h:i:s').' *</i>');
    ?>
<?php } ?>
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
