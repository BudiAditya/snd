<?php
if ($output == "1") {
    require_once(LIBRARY . "PHPExcel.php");
    include("report_xls.php");
} elseif ($output == 2){
    require_once(LIBRARY . "tabular_pdf.php");
    include("report.pdf.php");
} else {
    include("report.web.php");
}