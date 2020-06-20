<!DOCTYPE HTML>
<html>
<?php
/** @var $collect Collect */ /** @var $collector Karyawan[] */
?>
<head>
	<title>SND System - Ubah Data Penagihan</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var elements = ["CabangId","CollectDate","CollectNo","CollectorId","CollectDescs","CollectAmount","PaidAmount","BalanceAmount","CollectStatus","btSubmit"];
            BatchFocusRegister(elements);
            $(".date").customDatePicker({ showOn: "focus" });

            // autoNumeric
            $(".num").autoNumeric({mDec: '0'});
            $("#frm").submit(function(e) {
                $(".num").each(function(idx, ele){
                    this.value  = $(ele).autoNumericGet({mDec: '0'});
                });
            });
            // when Base Amount change
            //eRepair = Number($("#NilEstRepair").autoNumericGet({mDec: '0'}));
            $("#CollectAmount").change(function(e){
                var bam = Number($("#CollectAmount").autoNumericGet({mDec: '0'}));
                var pam = Number($("#PaidAmount").autoNumericGet({mDec: '0'}));
                if (pyt == 0){
                    $("#PaidAmount").val(bam);
                    pam = bam;
                }
                $("#BalanceAmount").val(bam-pam);
            });
            // when Paid Amount change
            $("#PaidAmount").change(function(e){
                var bam = Number($("#CollectAmount").autoNumericGet({mDec: '0'}));
                var pam = Number($("#PaidAmount").autoNumericGet({mDec: '0'}));
                $("#BalanceAmount").val(bam-pam);
            });

            $("#bAddDetail").click(function(e){
                $("#divadddetail").show();
            });
            // save add repair

            $("#frmAddDetail").submit(function(e){
                var postData = $(this).serializeArray();
                var formURL = $(this).attr("action");
                $.ajax(
                    {
                        url : formURL,
                        type: "POST",
                        data : postData,
                        success:function(data, textStatus, jqXHR)
                        {
                            //data: return data from server
                            //alert(data);
                            location.reload();
                        },
                        error: function(jqXHR, textStatus, errorThrown)
                        {
                            //if fails
                            alert('Maaf, gagal simpan data..')
                        }
                    });
                e.preventDefault(); //STOP default action
                e.unbind(); //unbind. to stop multiple form submit.
            });

            $("#dPaidAmount").change(function(e){
                var bam = Number($("#dOutstandingAmount").autoNumericGet({mDec: '0'}));
                var pam = Number($("#dPaidAmount").autoNumericGet({mDec: '0'}));
                $("#dBalanceAmount").val(bam-pam);
            });

            $("#frmEditDetail").submit(function(e){
                var postData = $(this).serializeArray();
                var formURL = $(this).attr("action");
                $.ajax(
                    {
                        url : formURL,
                        type: "POST",
                        data : postData,
                        success:function(data, textStatus, jqXHR)
                        {
                            //data: return data from server
                            //alert(data);
                            location.reload();
                        },
                        error: function(jqXHR, textStatus, errorThrown)
                        {
                            //if fails
                            alert('Maaf, gagal update data..')
                        }
                    });
                e.preventDefault(); //STOP default action
                e.unbind(); //unbind. to stop multiple form submit.
            });

            $("#bCancelAdd").click(function(e){
                $("#divadddetail").hide();
            });

            $("#bCancelEdit").click(function(e){
                $("#diveditdetail").hide();
            });
        });

        //edit detail
        function feditdetail(dta){
            //$dta = $detail->Id.'|'.$detail->InvoiceNo.'|'.$counter.'|'.$detail->InvoiceDate.'|'.$detail->CustomerName.'|'.$detail->InvoiceDueDate.'|'.$detail->OutstandingAmount.'|'.$detail->PaidAmount.'|'.$detail->BalanceAmount.'|'.$detail->DetailStatus.'|'.$detail->RecollectDate;
            var dtx = dta.split('|');
            //if (confirm('Ubah Detail Penagihan No: '+dtx[2]+ '\nNo.Invoice: '+dtx[1]+' ?')) {
                $("#dId").val(dtx[0]);
                $("#dInvoiceNo").val(dtx[1]);
                $("#dInvoiceDate").val(dtx[3]);
                $("#dCustomerName").val(dtx[4]);
                $("#dInvoiceDueDate").val(dtx[5]);
                $("#dOutstandingAmount").val(dtx[6]);
                $("#dPaidAmount").val(dtx[7]);
                $("#dBalanceAmount").val(dtx[8]);
                $("#dDetailStatus").val(dtx[9]);
                $("#dRecollectDate").val(dtx[10]);
                $("#diveditdetail").show();
            //}
        }

        // delete detail
        function fdeldetail(dta){
            var dtx = dta.split('|');
            var id = dtx[0];
            var invoiceno = dtx[1];
            var norut = dtx[2];
            var urx = '<?php print($helper->site_url("ar.collect/delete_detail/"));?>'+id;
            if (confirm('Hapus Detail Penagihan No: '+norut+ '\nNo.Invoice: '+invoiceno+' ?')) {
                $.get(urx, function(data){
                    //alert(data);
                    location.reload();
                });
            }
        }
    </script>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<?php
$badd = base_url('public/images/button/').'add.png';
$bsave = base_url('public/images/button/').'accept.png';
$bcancel = base_url('public/images/button/').'reject.png';
$bview = base_url('public/images/button/').'view.png';
$bedit = base_url('public/images/button/').'edit.png';
$bdelete = base_url('public/images/button/').'delete.png';
$sts = null;
?>
<br />
<fieldset>
	<legend align="center"><strong>Ubah Data Penagihan No. <?php print($collect->CollectNo);?></strong></legend>
    <form id="frm" action="<?php print($helper->site_url("ar.collect/edit/".$collect->Id)); ?>" method="post">
        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="center" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang/Outlet</td>
                <td><select name="CabangId" class="text2" id="CabangId" required>
                        <option value=""></option>
                        <?php
                        foreach ($cabangs as $cab) {
                            if ($cab->Id == $collect->CabangId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Tanggal</td>
                <td><input type="text" class="date" maxlength="10" size="10" id="CollectDate" name="CollectDate" value="<?php print($collect->FormatCollectDate(JS_DATE));?>" required/></td>
                <td>No. Collect</td>
                <td><input type="text" class="text2" maxlength="20" size="20" id="CollectNo" name="CollectNo" value="<?php print($collect->CollectNo != null ? $collect->CollectNo : '-'); ?>" /></td>
            </tr>
            <tr>
                <td>Nama Collector</td>
                <td><select class="text2" id="CollectorId" name="CollectorId" required>
                        <option value="">- Pilih Collector -</option>
                        <?php
                        foreach ($collector as $collectorman) {
                            if ($collectorman->Id == $collect->CollectorId) {
                                printf('<option value="%d" selected="selected">%s</option>', $collectorman->Id, $collectorman->Nama);
                            } else {
                                printf('<option value="%d">%s</option>', $collectorman->Id, $collectorman->Nama);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Keterangan</td>
                <td colspan="3"><input type="text" class="text2" maxlength="150" size="70" id="CollectDescs" name="CollectDescs" value="<?php print($collect->CollectDescs);?>" /></td>
            </tr>
            <tr>
                <td>Nilai Tagihan</td>
                <td><b>Rp. <input type="text" class="num" id="CollectAmount" name="CollectAmount" size="18" maxlength="20" value="<?php print(number_format($collect->CollectAmount,0)); ?>" style="text-align: right" required/></b></td>
                <td>Sudah Terbayar</td>
                <td><b>Rp. <input type="text" class="num" id="PaidAmount" name="PaidAmount" size="18" maxlength="20" value="<?php print(number_format($collect->PaidAmount,0)); ?>" style="text-align: right" required/></b></td>
                <td>Sisa</td>
                <td><b>Rp. <input type="text" class="num" id="BalanceAmount" name="BalanceAmount" size="18" maxlength="20" value="<?php print(number_format($collect->CollectAmount - $collect->PaidAmount,0)); ?>" style="text-align: right" required/></b></td>
            </tr>
            <tr>
                <td>Status Penagihan</td>
                <td><select class="text2" id="CollectStatus" name="CollectStatus" required>
                        <option value="0" <?php print($collect->CollectStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                        <option value="1" <?php print($collect->CollectStatus == 1 ? 'selected="selected"' : '');?>>1 - In Process</option>
                        <option value="2" <?php print($collect->CollectStatus == 2 ? 'selected="selected"' : '');?>>2 - Selesai</option>
                        <option value="3" <?php print($collect->CollectStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                    </select>
                </td>
                <td colspan="4" align="center">
                    <a href="<?php print($helper->site_url("ar.collect")); ?>" class="button">Daftar Penagihan</a>
                    &nbsp&nbsp
                    <button id="btSubmit" type="submit"><b>Update</b></button>
                </td>
            </tr>
        </table>
    </form>
    <div id="divadddetail" style="display: none">
        <br>
        <form id="frmAddDetail" action="<?php print($helper->site_url("ar.collect/add_detail/".$collect->Id)); ?>" method="post">
            <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="center">
                <tr>
                    <th colspan="11"><strong>TAMBAH DETAIL PENAGIHAN PIUTANG</strong></th>
                </tr>
                <tr>
                    <th>No.</th>
                    <th>No. Invoice</th>
                    <th>Tanggal</th>
                    <th>Nama Customer</th>
                    <th>Salesman</th>
                    <th>JTP</th>
                    <th>Nilai</th>
                    <th>Retur</th>
                    <th>Terbayar</th>
                    <th>Sisa</th>
                    <th>Pilih</th>
                </tr>
                <?php
                $nmr = 0;
                $tBase = 0;
                $tPaid = 0;
                $tReturn = 0;
                $dtx = null;
                while($row = $outinvoices->FetchAssoc()){
                    $nmr++;
                    $dtx = $row["id"]."|".$row["balance_amount"]."|0";
                    print("<tr>");
                    printf("<td>%d</td>",$nmr);
                    printf("<td>%s</td>",$row["invoice_no"]);
                    printf("<td>%s</td>",$row["invoice_date"]);
                    printf("<td>%s</td>",$row["customer_name"]);
                    printf("<td>%s</td>",$row["sales_name"]);
                    printf("<td>%s</td>",$row["due_date"]);
                    printf("<td align='right'>%s</td>",number_format($row["base_amount"],0));
                    printf("<td align='right'>%s</td>",number_format($row["return_amount"],0));
                    printf("<td align='right'>%s</td>",number_format($row["paid_amount"],0));
                    printf("<td align='right'>%s</td>",number_format($row["balance_amount"],0));
                    printf("<td><input type='checkbox' name='pilihInvoices[]' value='%s'/></td>",$dtx);
                    print("</tr>");
                    $tBase += $row["base_amount"];
                    $tPaid += $row["paid_amount"];
                    $tReturn += $row["return_amount"];
                }
                print("<tr>");
                print("<td colspan='6' align='right'>T o t a l</td>");
                printf("<td align='right'>%s</td>",number_format($tBase,0));
                printf("<td align='right'>%s</td>",number_format($tReturn,0));
                printf("<td align='right'>%s</td>",number_format($tPaid,0));
                printf("<td align='right'>%s</td>",number_format($tBase - $tPaid,0));
                printf('<td><input type="image" id="bSaveDetail" height="15px" width="15px" src="%s" style="cursor: pointer" value="Submit" name="submit"/>',$bsave);
                printf('&nbsp<img id="bCancelAdd" src="%s" style="cursor: pointer"/></td>',$bcancel);
                print("</tr>");
                ?>
            </table>
        </form>
    </div>
    <div id="diveditdetail" style="display: none">
        <br>
        <form id="frmEditDetail" action="<?php print($helper->site_url("ar.collect/edit_detail/".$collect->Id)); ?>" method="post">
            <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="center">
                <tr>
                    <th colspan="11"><strong>EDIT DETAIL PENAGIHAN PIUTANG</strong></th>
                </tr>
                <tr>
                    <th>No. Invoice</th>
                    <th>Tanggal</th>
                    <th>Nama Customer</th>
                    <th>JTP</th>
                    <th>Outstanding</th>
                    <th>Terbayar</th>
                    <th>Sisa</th>
                    <th>Status</th>
                    <th>Tgl. Kembali</th>
                    <th>Action</th>
                </tr>
                <tr>
                    <td><input type="hidden" id="dId" name="dId" value="0"/>
                        <input type="text" class="text2" size="13" id="dInvoiceNo" name="dInvoiceNo" value="" readonly/>
                    </td>
                    <td><input type="text" class="text2" size="10" id="dInvoiceDate" name="dInvoiceDate" value="" readonly/></td>
                    <td><input type="text" class="text2" size="30" id="dCustomerName" name="dCustomerName" value="" readonly/></td>
                    <td><input type="text" class="text2" size="10" id="dInvoiceDueDate" name="dInvoiceDueDate" value="" readonly/></td>
                    <td><input type="text" class="text2" size="10" id="dOutstandingAmount" name="dOutstandingAmount" value="0" style="text-align: right" readonly/></td>
                    <td><input type="text" class="text2" size="10" id="dPaidAmount" name="dPaidAmount" value="0" style="text-align: right" required/></td>
                    <td><input type="text" class="text2" size="10" id="dBalanceAmount" name="dBalanceAmount" value="0" style="text-align: right" readonly/></td>
                    <td><select class="text2" id="dDetailtatus" name="dDetailStatus" required>
                        <option value="0">0 - Draft</option>
                        <option value="1">1 - In Process</option>
                        <option value="2">2 - Terbayar</option>
                        <option value="3">3 - Ditunda</option>
                        </select>
                    </td>
                    <td><input type="text" class="date" size="10" id="dRecollectDate" name="dRecollectDate" value=""/></td>
                    <td>
                        <?php
                        printf('<input type="image" id="bUpdateDetail" height="15px" width="15px" src="%s" style="cursor: pointer" value="Submit" name="submit"/>',$bsave);
                        printf('&nbsp<img id="bCancelEdit" src="%s" style="cursor: pointer"/>',$bcancel);
                        ?>
                    </td>
                </tr>

            </table>
        </form>
    </div>
    <br>
    <div>
        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="center">
            <tr>
                <th colspan="11"><strong>DETAIL PENAGIHAN PIUTANG</strong></th>
            </tr>
            <tr>
                <th>No.</th>
                <th>No. Invoice</th>
                <th>Tanggal</th>
                <th>Nama Customer</th>
                <th>JTP</th>
                <th>Outstanding</th>
                <th>Terbayar</th>
                <th>Sisa</th>
                <th>Status</th>
                <th>Tgl. Kembali</th>
                <th>Action</th>
            </tr>
            <?php
            $counter = 0;
            $total = 0;
            $totOut = 0;
            $totPaid = 0;
            $totSisa = 0;
            $dtStatus = null;
            foreach($collect->Details as $idx => $detail) {
                $counter++;
                if ($detail->DetailStatus == 0){
                    $dtStatus = "Draft";
                }elseif ($detail->DetailStatus == 1){
                    $dtStatus = "In Process";
                }elseif ($detail->DetailStatus == 2){
                    $dtStatus = "Terbayar";
                }elseif ($detail->DetailStatus == 3){
                    $dtStatus = "Ditunda";
                }else{
                    $dtStatus = "Void";
                }
                print("<tr>");
                printf('<td class="right">%s.</td>', $counter);
                printf('<td>%s</td>', $detail->InvoiceNo);
                printf('<td>%s</td>', $detail->InvoiceDate);
                printf('<td>%s</td>', $detail->CustomerName);
                printf('<td>%s</td>', $detail->InvoiceDueDate);
                printf('<td class="right">%s</td>', number_format($detail->OutstandingAmount,0));
                printf('<td class="right">%s</td>', number_format($detail->PaidAmount,0));
                printf('<td class="right">%s</td>', number_format($detail->BalanceAmount,0));
                printf('<td>%s</td>', $dtStatus);
                printf('<td>%s</td>', $detail->RecollectDate);
                print("<td class='center'>");
                $dta = $detail->Id.'|'.$detail->InvoiceNo.'|'.$counter.'|'.$detail->InvoiceDate.'|'.$detail->CustomerName.'|'.$detail->InvoiceDueDate.'|'.$detail->OutstandingAmount.'|'.$detail->PaidAmount.'|'.$detail->BalanceAmount.'|'.$detail->DetailStatus.'|'.$detail->RecollectDate;
                printf('<img src="%s" style="cursor: pointer" onclick="return feditdetail(%s);"/>',$bedit,"'".$dta."'");
                printf('&nbsp&nbsp<img src="%s" style="cursor: pointer" onclick="return fdeldetail(%s);"/>',$bdelete,"'".$dta."'");
                print("</td>");
                print("</tr>");
                $totOut += $detail->OutstandingAmount;
                $totPaid += $detail->PaidAmount;
                $totSisa += $detail->BalanceAmount;
            }
            print("<tr>");
            print("<td colspan='5' class='right'>T o t a l</td>");
            printf('<td class="right">%s</td>', number_format($totOut,0));
            printf('<td class="right">%s</td>', number_format($totPaid,0));
            printf('<td class="right">%s</td>', number_format($totSisa,0));
            print("<td colspan='3' class='center'>");
            printf('<img src="%s" id="bAddDetail" style="cursor: pointer;"/>',$badd);
            print("</td>");
            print("</tr>");
            ?>
        </table>
    </div>
</fieldset>
<!-- </body> -->
</html>
