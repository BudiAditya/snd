<!DOCTYPE HTML>
<html>
<head>
    <title>SND System - A/R & Sales Statistic</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.idletimer.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>
    <!-- ChartJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>

    <style type="text/css">
        .chart-container-100 {
            width: 100%;
            height:250px
        }
        .chart-container-70 {
            width: 70%;
            height:250px
        }

        .chart-container-50 {
            width: 50%;
            height:250px
        }
    </style>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<br />
<div id="mainPanel" class="easyui-panel" title="A/R & Sales Statistic" style="width:100%;height:100%;padding:5px;" data-options="footer:'#ft'">
    <div>
        <form action="<?php print($helper->site_url("ar.statistic")); ?>" method="POST">
            <label for="Type">Jenis Statistik : </label>
            <select id="Type" name="type">
                <option value="1" <?php print($type == 1 ? 'selected="selected"' : '');?>>1 - Per Tahun</option>
                <option value="2" <?php print($type == 2 ? 'selected="selected"' : '');?>>2 - Per Bulan</option>
                <option value="3" <?php print($type == 3 ? 'selected="selected"' : '');?>>3 - Sampai Bulan</option>
            </select>
            <label for="Month">Bulan : </label>
            <select id="Month" name="month">
                <option value="1" <?php print($month == 1 ? 'selected="selected"' : '');?>>1 - Januari</option>
                <option value="2" <?php print($month == 2 ? 'selected="selected"' : '');?>>2 - Februari</option>
                <option value="3" <?php print($month == 3 ? 'selected="selected"' : '');?>>3 - Maret</option>
                <option value="4" <?php print($month == 4 ? 'selected="selected"' : '');?>>4 - April</option>
                <option value="5" <?php print($month == 5 ? 'selected="selected"' : '');?>>5 - Mei</option>
                <option value="6" <?php print($month == 6 ? 'selected="selected"' : '');?>>6 - Juni</option>
                <option value="7" <?php print($month == 7 ? 'selected="selected"' : '');?>>7 - Juli</option>
                <option value="8" <?php print($month == 8 ? 'selected="selected"' : '');?>>8 - Agustus</option>
                <option value="9" <?php print($month == 9 ? 'selected="selected"' : '');?>>9 - September</option>
                <option value="10" <?php print($month == 10 ? 'selected="selected"' : '');?>>10 - Oktober</option>
                <option value="11" <?php print($month == 11 ? 'selected="selected"' : '');?>>11 - Nopember</option>
                <option value="12" <?php print($month == 12 ? 'selected="selected"' : '');?>>12 - Desember</option>
            </select>
            <label for="Year">Tahun : </label>
            <select id="Year" name="year">
                <?php
                for ($i = date("Y"); $i >= 2019; $i--) {
                    if ($i == $year) {
                        printf('<option value="%d" selected="selected">%s</option>', $i, $i);
                    } else {
                        printf('<option value="%d">%s</option>', $i, $i);
                    }
                }
                ?>
            </select>
            <button type="submit">Generate</button>
        </form>
    </div>
    <br>
    <table border="1" cellspacing="1" style="width: 100%">
        <tr>
            <td colspan="2" style="width: 100%;height: 250px">
                <canvas id="myLineChart"></canvas>
            </td>
        </tr>
        <tr>
            <td style="width: 60%;height: 250px">
                <canvas id="myBarChart"></canvas>
            </td>
            <!--
            <td style="width: 40%;height: 250px">
                <canvas id="myPieChart"></canvas>
            </td>
            -->
            <td align="center">
                <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
                    <tr>
                        <th>No.</th>
                        <th>KODE</th>
                        <th>ENTITAS</th>
                        <th>OMSET (Rp)</th>
                    </tr>
                    <?php
                    $nmr = 1;
                    $tom = 0;
                    while ($row = $dataEntityOmset->FetchAssoc()) {
                        print("<tr>");
                        printf("<td>%s</td>",$nmr++);
                        printf("<td>%s</td>",$row["entity_code"]);
                        printf("<td>%s</td>",$row["entity_name"]);
                        printf("<td align='right'>%s</td>",number_format($row["omset"],0));
                        print("</tr>");
                        $tom+= $row["omset"];
                    }
                    print("<tr>");
                    print("<td colspan='3'>T O T A L</td>");
                    printf("<td align='right'>%s</td>",number_format($tom,0));
                    print("</tr>");
                    ?>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 60%;height: 350px">
                <canvas id="myPrincipalBarChart"></canvas>
            </td>
            <!--
            <td style="width: 40%;height: 350px">
                <canvas id="myPrincipalPieChart"></canvas>
            </td>
            -->
            <td align="center">
                <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
                    <tr>
                        <th>No.</th>
                        <th>KODE</th>
                        <th>PRINCIPAL</th>
                        <th>OMSET (Rp)</th>
                    </tr>
                    <?php
                    $nmr = 1;
                    $tom = 0;
                    while ($row = $dataPrincipalOmset->FetchAssoc()) {
                        print("<tr>");
                        printf("<td>%s</td>",$nmr++);
                        printf("<td>%s</td>",$row["principal_code"]);
                        printf("<td>%s</td>",$row["principal_name"]);
                        printf("<td align='right'>%s</td>",number_format($row["omset"],0));
                        print("</tr>");
                        $tom+= $row["omset"];
                    }
                    print("<tr>");
                    print("<td colspan='3'>T O T A L</td>");
                    printf("<td align='right'>%s</td>",number_format($tom,0));
                    print("</tr>");
                    ?>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 60%;height: 250px">
                <canvas id="mySalesChart"></canvas>
            </td>
            <td align="center">
                <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
                    <tr>
                        <th>No.</th>
                        <th>NAMA SALESMAN</th>
                        <th>OMSET (Rp)</th>
                    </tr>
                    <?php
                    $nmr = 1;
                    $tom = 0;
                    while ($row = $dataSalesOmset->FetchAssoc()) {
                        print("<tr>");
                        printf("<td>%s</td>",$nmr++);
                        printf("<td>%s</td>",$row["sales_name"]);
                        printf("<td align='right'>%s</td>",number_format($row["omset"],0));
                        print("</tr>");
                        $tom+= $row["omset"];
                    }
                    print("<tr>");
                    print("<td colspan='2'>T O T A L</td>");
                    printf("<td align='right'>%s</td>",number_format($tom,0));
                    print("</tr>");
                    ?>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 60%;height: 350px">
                <canvas id="myCityBarChart"></canvas>
            </td>
            <!--
            <td style="width: 40%;height: 350px">
                <canvas id="myCityPieChart"></canvas>
            </td>
            -->
            <td align="center">
                <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
                    <tr>
                        <th>No.</th>
                        <th>KODE</th>
                        <th>AREA</th>
                        <th>OMSET (Rp)</th>
                    </tr>
                    <?php
                    $nmr = 1;
                    $tom = 0;
                    while ($row = $dataAreaOmset->FetchAssoc()) {
                        print("<tr>");
                        printf("<td>%s</td>",$nmr++);
                        printf("<td>%s</td>",$row["area_code"]);
                        printf("<td>%s</td>",$row["area_name"]);
                        printf("<td align='right'>%s</td>",number_format($row["omset"],0));
                        print("</tr>");
                        $tom+= $row["omset"];
                    }
                    print("<tr>");
                    print("<td colspan='3'>T O T A L</td>");
                    printf("<td align='right'>%s</td>",number_format($tom,0));
                    print("</tr>");
                    ?>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 70%;height: 300px">
                <canvas id="myCustomerChart"></canvas>
            </td>
            <td align="center">
                <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
                    <tr>
                        <th>No.</th>
                        <th>TOP 10 CUSTOMER</th>
                        <th>NILAI (Rp)</th>
                    </tr>
                    <?php
                    $nmr = 1;
                    while ($row = $dataCustomer->FetchAssoc()) {
                        print("<tr>");
                        printf("<td>%s</td>",$nmr++);
                        printf("<td>%s</td>",$row["customer_name"]);
                        printf("<td align='right'>%s</td>",number_format($row["omset"],0));
                        print("</tr>");
                    }
                    ?>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 70%;height: 300px">
                <canvas id="myItemChart"></canvas>
            </td>
            <td align="center">
                <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
                    <tr>
                        <th>No.</th>
                        <th>KODE</th>
                        <th>TOP 10 PRODUK</th>
                        <th>NILAI (Rp)</th>
                    </tr>
                    <?php
                    $nmr = 1;
                    while ($row = $dataProduct->FetchAssoc()) {
                        print("<tr>");
                        printf("<td>%s</td>",$nmr++);
                        printf("<td>%s</td>",$row["item_code"]);
                        printf("<td>%s</td>",$row["item_name"]);
                        printf("<td align='right'>%s</td>",number_format($row["nilai"],0));
                        print("</tr>");
                    }
                    ?>
                </table>
            </td>
        </tr>
    </table>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<script>
    var ctxLine = document.getElementById('myLineChart').getContext('2d');
    var myLineChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels  : ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            datasets: [{
                label               : 'Penjualan',
                backgroundColor     : 'rgba(139, 29, 65, 0.8)',
                borderColor			: 'rgba(139, 29, 65, 0.8)',
                border              : 1,
                fill				: false,
                data                : [<?= $dataInvoices?>]
            },
                {
                    label               : 'Penerimaan Piutang',
                    backgroundColor     : 'rgba(105, 120, 12, 0.8)',
                    borderColor			: 'rgba(105, 120, 12, 0.8)',
                    border              : 1,
                    fill				: false,
                    data                : [<?= $dataReceipts?>]
                }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'GRAFIK PENJUALAN TAHUN <?=$dataTahun?>'
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero:true
                    }
                }]
            }
        }
    });

    $(document).ready(function() {
        //grafik penjualan per entitas barang
        $.ajax({
            url: "<?php print($helper->site_url("ar.statistic/salesByEntityData/".$type.'/'.$year.'/'.$month));?>",
            method: "GET",
            success: function(response) {
                console.log(response);
                data = JSON.parse(response);
                console.log(data);
                var label = [];
                var nilai = [];
                var warna = [];

                for(var i=0; i<data.length;i++) {
                    label.push(data[i].kode);
                    nilai.push(data[i].nilai);
                    warna.push(data[i].warna);
                }

                var ctx = document.getElementById('myBarChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: label,
                        datasets: [{
                            label: 'Penjualan',
                            backgroundColor : warna,
                            borderColor     : 'rgba(105, 120, 12, 0.8)',
                            data: nilai
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'OMSET PENJUALAN BY ENTITAS <?=$statPeriod?>'
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero:true
                                }
                            }]
                        }
                    }
                });
            }
        });

        //grafik penjualan per principal barang
        $.ajax({
            url: "<?php print($helper->site_url("ar.statistic/salesByPrincipalData/".$type.'/'.$year.'/'.$month));?>",
            method: "GET",
            success: function(response) {
                console.log(response);
                data = JSON.parse(response);
                console.log(data);
                var label = [];
                var nilai = [];
                var warna = [];

                for(var i=0; i<data.length;i++) {
                    label.push(data[i].kode);
                    nilai.push(data[i].nilai);
                    warna.push(data[i].warna);
                }

                var ctx = document.getElementById('myPrincipalBarChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: label,
                        datasets: [{
                            label: 'Penjualan',
                            backgroundColor : warna,
                            borderColor     : 'rgba(105, 120, 12, 0.8)',
                            data: nilai
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'OMSET PENJUALAN BY PRINCIPAL <?=$statPeriod?>'
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero:true
                                }
                            }]
                        }
                    }
                });
            }
        });

        //grafik penjualan per sales
        $.ajax({
            url: "<?php print($helper->site_url("ar.statistic/omsetSalesData/".$type.'/'.$year.'/'.$month));?>",
            method: "GET",
            success: function(response) {
                console.log(response);
                data = JSON.parse(response);
                console.log(data);
                var label = [];
                var nilai = [];
                var warna = [];

                for(var i=0; i<data.length;i++) {
                    label.push(data[i].nama);
                    nilai.push(data[i].nilai);
                    warna.push(data[i].warna);
                }

                var ctx = document.getElementById('mySalesChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: label,
                        datasets: [{
                            label: 'Omset Sales',
                            backgroundColor     : warna,
                            borderColor			: 'rgba(139, 29, 65, 0.8)',
                            data: nilai
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'OMSET PENJUALAN SALESMAN <?=$statPeriod?>'
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero:true
                                }
                            }]
                        }
                    }
                });
            }
        });

        //grafik penjualan per kota
        $.ajax({
            url: "<?php print($helper->site_url("ar.statistic/omsetByAreaData/".$type.'/'.$year.'/'.$month));?>",
            method: "GET",
            success: function(response) {
                console.log(response);
                data = JSON.parse(response);
                console.log(data);
                var label = [];
                var nilai = [];
                var warna = [];

                for(var i=0; i<data.length;i++) {
                    label.push(data[i].kode);
                    nilai.push(data[i].nilai);
                    warna.push(data[i].warna);
                }

                var ctx = document.getElementById('myCityBarChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: label,
                        datasets: [{
                            label: 'Penjualan',
                            backgroundColor : warna,
                            borderColor     : 'rgba(105, 120, 12, 0.8)',
                            data: nilai
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'OMSET PENJUALAN BY AREA <?=$statPeriod?>'
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero:true
                                }
                            }]
                        }
                    }
                });
            }
        });

        //grafik penjualan top 10 customer
        $.ajax({
            url: "<?php print($helper->site_url("ar.statistic/top10CustomerData/".$type.'/'.$year.'/'.$month));?>",
            method: "GET",
            success: function(response) {
                console.log(response);
                data = JSON.parse(response);
                console.log(data);
                var label = [];
                var nilai = [];
                var warna = [];

                for(var i=0; i<data.length;i++) {
                    label.push(data[i].kode);
                    nilai.push(data[i].nilai);
                    warna.push(data[i].warna);
                }

                var ctx = document.getElementById('myCustomerChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: label,
                        datasets: [{
                            label: 'Omset Customer',
                            backgroundColor     : warna,
                            borderColor			: 'rgba(139, 29, 65, 0.8)',
                            data: nilai
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'TOP 10 CUSTOMER <?=$statPeriod?>'
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero:true
                                }
                            }]
                        }
                    }
                });
            }
        });

        //grafik penjualan top 10 customer
        $.ajax({
            url: "<?php print($helper->site_url("ar.statistic/top10ItemData/".$type.'/'.$year.'/'.$month));?>",
            method: "GET",
            success: function(response) {
                console.log(response);
                data = JSON.parse(response);
                console.log(data);
                var label = [];
                var nilai = [];
                var warna = [];

                for(var i=0; i<data.length;i++) {
                    label.push(data[i].kode);
                    nilai.push(data[i].nilai);
                    warna.push(data[i].warna);
                }

                var ctx = document.getElementById('myItemChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: label,
                        datasets: [{
                            label: 'Omset Produk',
                            backgroundColor     : warna,
                            borderColor			: 'rgba(139, 29, 65, 0.8)',
                            data: nilai
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'TOP 10 PRODUCT <?=$statPeriod?>'
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero:true
                                }
                            }]
                        }
                    }
                });
            }
        });
    });
</script>
<!-- </body> -->
</html>
