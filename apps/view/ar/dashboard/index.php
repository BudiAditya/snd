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
            <td style="width: 40%;height: 250px">
                <canvas id="myPieChart"></canvas>
            </td>
        </tr>
        <tr>
            <td style="width: 60%;height: 350px">
                <canvas id="myPrincipalBarChart"></canvas>
            </td>
            <td style="width: 40%;height: 350px">
                <canvas id="myPrincipalPieChart"></canvas>
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
                    while ($row = $dataOmset->FetchAssoc()) {
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
            <td style="width: 40%;height: 350px">
                <canvas id="myCityPieChart"></canvas>
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
            url: "<?php print($helper->site_url("ar.dashboard/salesdata"));?>",
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
                            text: 'OMSET PENJUALAN BY ENTITAS TAHUN <?=$dataTahun?>'
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

                var ctx = document.getElementById('myPieChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'pie',
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
                        maintainAspectRatio: false
                    }
                });
            }
        });

        //grafik penjualan per principal barang
        $.ajax({
            url: "<?php print($helper->site_url("ar.dashboard/principaldata"));?>",
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
                            text: 'OMSET PENJUALAN BY PRINCIPAL TAHUN <?=$dataTahun?>'
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

                var ctx = document.getElementById('myPrincipalPieChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'pie',
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
                        maintainAspectRatio: false
                    }
                });
            }
        });

        //grafik penjualan per sales
        $.ajax({
            url: "<?php print($helper->site_url("ar.dashboard/omsetdata"));?>",
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
                            text: 'OMSET PENJUALAN SALESMAN TAHUN <?=$dataTahun?>'
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
            url: "<?php print($helper->site_url("ar.dashboard/citydata"));?>",
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
                            text: 'OMSET PENJUALAN BY AREA TAHUN <?=$dataTahun?>'
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

                var ctx = document.getElementById('myCityPieChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'pie',
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
                        maintainAspectRatio: false
                    }
                });
            }
        });

        //grafik penjualan top 10 customer
        $.ajax({
            url: "<?php print($helper->site_url("ar.dashboard/top10customerdata"));?>",
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
                            text: 'TOP 10 CUSTOMER <?=$dataTahun?>'
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
            url: "<?php print($helper->site_url("ar.dashboard/top10itemdata"));?>",
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
                            text: 'TOP 10 PRODUCT <?=$dataTahun?>'
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
