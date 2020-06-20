<!DOCTYPE HTML>
<html>
<head>
    <title>SND System - Revenue & Costs Statistic</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>
    <!-- ChartJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>

    <style type="text/css">
        .chart-container {
            width: 100%;
            height:300px
        }
    </style>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<br />
<div id="mainPanel" class="easyui-panel" title="Revenue & Costs Statistic" style="width:100%;height:100%;padding:5px;" data-options="footer:'#ft'">
    <div class="chart-container">
        <canvas id="myLineChart"></canvas>
    </div>
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
                label               : 'Pendapatan',
                backgroundColor     : 'rgba(139, 29, 65, 0.8)',
                borderColor			: 'rgba(139, 29, 65, 0.8)',
                border              : 1,
                fill				: false,
                data                : [<?= $dataRevenues?>]
            },
                {
                    label               : 'Biaya',
                    backgroundColor     : 'rgba(105, 120, 12, 0.8)',
                    borderColor			: 'rgba(105, 120, 12, 0.8)',
                    border              : 1,
                    fill				: false,
                    data                : [<?= $dataCosts?>]
                }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'GRAFIK PENDAPATAN & BIAYA TAHUN <?=$dataTahun?>'
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
</script>
<!-- </body> -->
</html>
