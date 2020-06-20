<?php
if ($outPut == "1") {
    require_once(LIBRARY . "PHPExcel.php");
    include("stkdetail_xls.php");
} elseif ($outPut == 2){
    require_once(LIBRARY . "tabular_pdf.php");
    include("stkdetail.pdf.php");
} else {
    include("stkdetail.web.php");
}