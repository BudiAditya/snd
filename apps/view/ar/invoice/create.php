<!DOCTYPE HTML>
<html>
<head>
    <title>SND System - Rekapitulasi Sales Order</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript">
        var urc = "<?php print($helper->site_url("ar.invoice/generate")); ?>";
        $(document).ready(function() {
            $("#cbAll").change(function(e) { cbAll_Change(this, e);	});

            $("#btnGenerate").click(function() {
                var test = $(".cbIds:checked");
                if (test.length == 0) {
                    alert("Belum ada data yang dipilih!");
                }else {
                    if (confirm("Mulai proses buat Invoice?")){
                        $("#frm").attr('action', urc).submit();
                    }
                }
            });
        });
        function cbAll_Change(sender, e) {
            $(":checkbox.cbIds").each(function(idx, ele) {
                ele.checked = sender.checked;
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
<form id="frm" name="frmReport" method="post">
    <table cellpadding="2" cellspacing="1" class="tablePadding tableBorder">
        <tr>
            <th colspan="2" class="bold left">CREATE INVOICE FROM SALES ORDER (S/O)</th>
            <th>Gudang :
                <select name="GudangId" id="GudangId" required>
                    <?php
                    /** @var $gudangs Warehouse[] */
                    foreach ($gudangs as $gdg){
                        if ($GudangId == $gdg->Id) {
                            printf('<option value="%d" selected="selected">%s</option>', $gdg->Id, $gdg->WhCode);
                        }else{
                            printf('<option value="%d">%s</option>',$gdg->Id,$gdg->WhCode);
                        }
                    }
                    ?>
                </select>
            </th>
        </tr>
        <tr class="center">
            <th>Salesman</th>
            <th>Outlet/Customer</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select id="SalesId" name="SalesId" style="width: 150px" required>
                    <option value="0">- Semua Salesman -</option>
                    <?php
                    while ($row = $sales->FetchAssoc()) {
                        if ($SalesId == $row["sales_id"]){
                            printf('<option value="%d" selected="selected">%s (%s)</option>',$row["sales_id"],$row["sales_name"],$row["sales_code"]);
                        }else{
                            printf('<option value="%d">%s (%s)</option>',$row["sales_id"],$row["sales_name"],$row["sales_code"]);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <select id="CustomersId" name="CustomersId" style="width: 150px" required>
                    <option value="0">- Semua Customer -</option>
                    <?php
                    while ($row = $customers->FetchAssoc()) {
                        if ($CustomersId == $row["customer_id"]){
                            printf('<option value="%d" selected="selected">%s (%s)</option>',$row["customer_id"],$row["cus_name"],$row["cus_code"]);
                        }else{
                            printf('<option value="%d">%s (%s)</option>',$row["customer_id"],$row["cus_name"],$row["cus_code"]);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>
                <button type="submit" formaction="<?php print($helper->site_url("ar.invoice/create")); ?>"><b>Tampilkan</b></button>
                <button id="btnGenerate" class="button"><b>Generate Invoice</b>
            </td>
        </tr>
    </table>
<!-- start web report -->
<?php  if ($Datas != null){
        ?>
        <br>
        <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
            <tr>
                <th>No.</th>
                <th>Salesman</th>
                <th>Outlet/Customer</th>
                <th>Tanggal</th>
                <th>Brand</th>
                <th nowrap='nowrap'>Kode</th>
                <th nowrap='nowrap'>Nama Barang</th>
                <th>Satuan</th>
                <th>Order</th>
                <th>Kirim</th>
                <th>Sisa</th>
                <th>Remarks</th>
                <th>Pilih <input type="checkbox" id="cbAll" checked="checked"></th>
            </tr>
            <?php
            $nmr = 1;
            $url = null;
            $sma = false;
            while ($row = $Datas->FetchAssoc()) {
                $url = $helper->site_url("ar.order/view/".$row["id"]);
                print("<tr valign='Top'>");
                printf("<td>%s</td>",$nmr++);
                printf("<td nowrap='nowrap'>%s</td>",$row["sales_name"]);
                printf("<td nowrap='nowrap'>%s</td>",$row["cus_name"]);
                printf("<td><a href= '%s' target='_blank'>%s</a></td>",$url ,date('d-m-Y',strtotime($row["order_date"])));
                printf("<td nowrap='nowrap'>%s</td>", $row['brand_name']);
                printf("<td nowrap='nowrap'>%s</td>", $row['item_code']);
                printf("<td nowrap='nowrap'>%s</td>", $row['item_name']);
                printf("<td nowrap='nowrap'>%s</td>", $row['s_uom_code']);
                printf("<td align='right'>%s</td>", number_format($row['order_qty'], 0));
                printf("<td align='right'>%s</td>", number_format($row['send_qty'], 0));
                printf("<td align='right'>%s</td>", number_format($row['order_qty'] - $row['send_qty'], 0));
                printf("<td nowrap='nowrap'>%s</td>", $row['keterangan']);
                printf('<td class="center"><input type="checkbox" class="cbIds" name="ids[]" value="%d" checked="checked"/></td>',$row["id"]);
                print("</tr>");
            }
            ?>
        </table>
    <?php } ?>
</form>
<!-- </body> -->
</html>
