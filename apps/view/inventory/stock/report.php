<?php
if ($output == "1") {
    require_once(LIBRARY . "PHPExcel.php");
    if ($userReportType == 1) {
        include("report_xls1.php");
    }elseif ($userReportType == 2){
        include("report_xls2.php");
    }elseif ($userReportType == 3) {
        include("report_xls3.php");
    }else{
        include("report_xls0.php");
    }
} elseif ($output == 2){
    require_once(LIBRARY . "tabular_pdf.php");
    include("report.pdf.php");
} else {
    include("report.web.php");
}