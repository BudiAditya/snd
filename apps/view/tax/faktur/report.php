<?php
if ($output == "1") {
    require_once(LIBRARY . "PHPExcel.php");
    include("report_xls.php");
} else {
    include("report.web.php");
}