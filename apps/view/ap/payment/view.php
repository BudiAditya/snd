<!DOCTYPE HTML>
<html>
<?php
/** @var $payment Payment */ /** @var $kasbanks KasBank[] */
?>
<head>
    <title>SND System - View Pembayaran Hutang</title>
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
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

    <style scoped>
        .f1{
            width:200px;
        }
    </style>
    <script type="text/javascript">
        var cabangId = "<?=$payment->CabangId;?>";
        var creditorId = "<?=$payment->CreditorId;?>";
        var paymentId = "<?=$payment->Id;?>";
        $(document).ready(function() {

            $('#CreditorId').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("ap.supplier/getJsonSupplier"));?>",
                idField:'id',
                textField:'sup_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'sup_code',title:'Kode',width:30},
                    {field:'sup_name',title:'Nama Supplier',width:100},
                    {field:'addr1',title:'Alamat',width:100},
                    {field:'city',title:'Kota',width:60}
                ]]
            });

            $('#aGrnSearch').combogrid({
                panelWidth:600,
                url: '<?php print($helper->site_url("ap.payment/getoutstandinggrns_json/"));?>'+cabangId+'/'+creditorId,
                idField:'id',
                textField:'id',
                mode:'get',
                fitColumns:true,
                columns:[[
                    {field:'grn_no',title:'No. GRN',width:75},
                    {field:'sup_inv_no',title:'No. Reff',width:75},
                    {field:'grn_date',title:'Tanggal',width:50},
                    {field:'due_date',title:'J T P',width:50},
                    {field:'balance_amount',title:'Outstanding',width:50,align:'right'}
                ]]
            });

            $("#bTambah").click(function(){
                if (confirm('Buat Payment baru?')){
                    location.href="<?php print($helper->site_url("ap.payment/add/0")); ?>";
                }
            });

            $("#bHapus").click(function(){
                if (confirm('Anda yakin akan membatalkan data payment ini?')){
                    location.href="<?php print($helper->site_url("ap.payment/void/").$payment->Id); ?>";
                }
            });

            $("#bCetak").click(function(){
                if (confirm('Cetak payment ini?')){
                    //location.href="<?php //print($helper->site_url("ap.payment/print_pdf/").$payment->Id); ?>";
                    alert('Proses cetak belum siap..');
                }
            });

            $("#bKembali").click(function(){
                location.href="<?php print($helper->site_url("ap.payment")); ?>";
            });

        });

        //date formating
        function myformatter(date){
            var y = date.getFullYear();
            var m = date.getMonth()+1;
            var d = date.getDate();
            return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
        }
        function myparser(s){
            if (!s) return new Date();
            var ss = (s.split('-'));
            var y = parseInt(ss[0],10);
            var m = parseInt(ss[1],10);
            var d = parseInt(ss[2],10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
                return new Date(y,m-1,d);
            } else {
                return new Date();
            }
        }
    </script>
    <style type="text/css">
        #fd{
            margin:0;
            padding:5px 10px;
        }
        .ftitle{
            font-size:14px;
            font-weight:bold;
            padding:5px 0;
            margin-bottom:10px;
            border-bottom:1px solid #ccc;
        }
        .fitem{
            margin-bottom:5px;
        }
        .fitem label{
            display:inline-block;
            width:100px;
        }
        .numberbox .textbox-text{
            text-align: right;
            color: blue;
        }
    </style>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php }
$badd = base_url('public/images/button/').'add.png';
$bsave = base_url('public/images/button/').'accept.png';
$bcancel = base_url('public/images/button/').'cancel.png';
$bview = base_url('public/images/button/').'view.png';
$bedit = base_url('public/images/button/').'edit.png';
$bdelete = base_url('public/images/button/').'delete.png';
$bclose = base_url('public/images/button/').'close.png';
$bsearch = base_url('public/images/button/').'search.png';
$bkembali = base_url('public/images/button/').'back.png';
$bcetak = base_url('public/images/button/').'printer.png';
$bsubmit = base_url('public/images/button/').'ok.png';
$baddnew = base_url('public/images/button/').'create_new.png';
?>
<br />
<div id="p" class="easyui-panel" title="View Pembayaran Hutang" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td>Cabang</td>
            <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($payment->CabangCode != null ? $payment->CabangCode : $userCabCode); ?>" disabled/>
                <input type="hidden" id="CabangId" name="CabangId" value="<?php print($payment->CabangId == null ? $userCabId : $payment->CabangId);?>"/>
            </td>
            <td>Tanggal</td>
            <td><input type="text" class="easyui-datebox" id="PaymentDate" name="PaymentDate" style="width: 100px" value="<?php print($payment->FormatPaymentDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser" disabled/></td>
            <td>No. Payment</td>
            <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="PaymentNo" name="PaymentNo" value="<?php print($payment->PaymentNo != null ? $payment->PaymentNo : '-'); ?>" disabled/></td>
            <td>Status</td>
            <td><select class="easyui-combobox" id="PaymentStatus" name="PaymentStatus" style="width: 110px" disabled>
                    <option value="0" <?php print($payment->PaymentStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                    <option value="1" <?php print($payment->PaymentStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                    <option value="2" <?php print($payment->PaymentStatus == 2 ? 'selected="selected"' : '');?>>2 - Batal</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Supplier</td>
            <td><input class="easyui-combogrid" id="CreditorId" name="CreditorId" style="width: 250px" value="<?php print($payment->CreditorId);?>" disabled/></td>
            <td>Cara Bayar</td>
            <td><select class="easyui-combobox" id="PaymentTypeId" name="PaymentTypeId" style="width: 100px" disabled>
                    <?php
                    foreach ($paymenttypes as $pty) {
                        if ($pty->Id == $payment->PaymentTypeId) {
                            printf('<option value="%d" selected="selected"> %s - %s </option>',$pty->Id, $pty->Id, $pty->Type);
                        } else {
                            printf('<option value="%d"> %s - %s </option>',$pty->Id, $pty->Id, $pty->Type);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>Kas/Bank</td>
            <td><select class="easyui-combobox" id="KasbankId" name="KasbankId" style="width: 150px" disabled>
                    <option value=""></option>
                    <?php
                    foreach ($kasbanks as $bank) {
                        if ($bank->Id == $payment->KasbankId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $bank->Id, $bank->Id, $bank->BankName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $bank->Id, $bank->Id, $bank->BankName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>Jumlah</td>
            <td><input type="text" class="easyui-numberbox" style="width: 110px;text-align: right;" id="PaymentAmount" name="PaymentAmount" value="<?php print($payment->PaymentAmount != null ? $payment->PaymentAmount : 0); ?>" disabled data-options="min:0,groupSeparator:','"/></td>
        </tr>
        <tr>
            <td>No. Warkat</td>
            <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 250px" id="WarkatNo" name="WarkatNo" value="<?php print($payment->WarkatNo); ?>" disabled/></td>
            <td>Tgl. Warkat</td>
            <td><input type="text" class="easyui-datebox" id="WarkatDate" name="WarkatDate" style="width: 100px" value="<?php print($payment->FormatWarkatDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser" disabled/></td>
            <td>Bank Warkat</td>
            <td><select class="easyui-combobox" id="WarkatBankId" name="WarkatBankId" style="width: 150px" disabled>
                    <option value="0"></option>
                    <?php
                    /** @var $banks Bank[] */
                    foreach ($banks as $bank) {
                        if ($bank->Id == $payment->WarkatBankId) {
                            printf('<option value="%d" selected="selected">%s - %s</option>', $bank->Id, $bank->Id, $bank->BankName);
                        } else {
                            printf('<option value="%d">%s - %s</option>', $bank->Id, $bank->Id, $bank->BankName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>Alokasi</td>
            <td><input type="text" class="easyui-numberbox" style="width: 110px" id="AllocateAmount" name="AllocateAmount" value="<?php print($payment->AllocateAmount != null ? $payment->AllocateAmount : 0); ?>" disabled data-options="min:0,groupSeparator:','"/></td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td colspan="3"><b><input type="text" class="easyui-textbox" id="PaymentDescs" name="PaymentDescs" style="width: 450px" value="<?php print($payment->PaymentDescs != null ? $payment->PaymentDescs : '-'); ?>" disabled/></b></td>
            <td colspan="2">
                &nbsp;
            </td>
            <td>S i s a</td>
            <td><input type="text" class="easyui-numberbox" style="width: 110px" id="BalanceAmount" name="BalanceAmount" value="<?php print($payment->BalanceAmount != null ? $payment->BalanceAmount : 0); ?>" disabled data-options="min:0,groupSeparator:','"/></td>
        </tr>
        <tr>
            <td colspan="5">Detail Pembayaran Hutang :</td>
        </tr>
        <tr>
            <td colspan="6">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                    <tr>
                        <th>No.</th>
                        <th>Jenis</th>
                        <th>No. GRN</th>
                        <th>Keterangan</th>
                        <th>Tanggal</th>
                        <th>J T P</th>
                        <th>Nilai Hutang</th>
                        <th>Dibayar</th>
                        <th>Sisa</th>
                    </tr>
                    <?php
                    $counter = 0;
                    $tout = 0;
                    $tall = 0;
                    $tbal = 0;
                    $dta = null;
                    $url = null;
                    foreach($payment->Details as $idx => $detail) {
                        $url = $helper->site_url("ap.purchase/view/".$detail->GrnId);
                        $counter++;
                        print("<tr>");
                        printf('<td class="right">%s.</td>', $counter);
                        printf('<td>%s</td>', $detail->ApType == 1 ? 'Opening' : 'Invoice');
                        printf("<td><a href= '%s' target='_blank'>%s</a></td>",$url ,$detail->GrnNo);
                        printf('<td>%s</td>', $detail->Keterangan);
                        printf('<td>%s</td>', $detail->GrnDate);
                        printf('<td>%s</td>', $detail->DueDate);
                        printf('<td class="right">%s</td>', number_format($detail->GrnOutstanding,0));
                        printf('<td class="right">%s</td>', number_format($detail->AllocateAmount,0));
                        printf('<td class="right">%s</td>', number_format(($detail->GrnOutstanding - $detail->AllocateAmount),0));
                        print("</tr>");
                        $tall += $detail->AllocateAmount;
                        $tout += $detail->GrnOutstanding;
                        $tbal += ($detail->GrnOutstanding - $detail->AllocateAmount);
                    }
                    ?>
                    <tr>
                        <td colspan="6" class="bold right">Sub Total :</td>
                        <td class="bold right"><?php print(number_format($tout,0));?></td>
                        <td class="bold right"><?php print(number_format($tall,0));?></td>
                        <td class="bold right"><?php print(number_format($tbal,0));?></td>
                    </tr>
                    <tr>
                        <td colspan="9" class="right">
                            <?php
                            if ($acl->CheckUserAccess("ap.payment", "add")) {
                                printf('<img src="%s" alt="Data Baru" title="Buat Data Baru" id="bTambah" style="cursor: pointer;"/> &nbsp',$baddnew);
                            }
                            if ($acl->CheckUserAccess("ap.payment", "print")) {
                                printf('<img src="%s" alt="Cetak Payment" title="Cetak Payment" id="bCetak" style="cursor: pointer;"/> &nbsp',$bcetak);
                            }
                            printf('<img src="%s" id="bKembali" alt="Daftar Pembayaran" title="Kembali ke daftar penerimaan" style="cursor: pointer;"/>',$bkembali);
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- </body> -->
</html>
