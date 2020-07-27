<?php
if ($outPut == "1") {
    require_once(LIBRARY . "PHPExcel.php");
    include("card_xls.php");
} elseif ($outPut == 2){
    require_once(LIBRARY . "tabular_pdf.php");
    include("card.pdf.php");
} else {
    include("card.web.php");
}