<!DOCTYPE HTML>
<html>
<?php
/** @var $journal Journal */ ?>
<head>
    <title>SND System - Entry Journal Akuntansi</title>
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
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

    <style scoped>
        .f1{
            width:200px;
        }
    </style>
    <script type="text/javascript">
        $( function() {
            //var addmaster = ["CabangId", "JournalDate","CustomerId", "JournalDescs", "btSubmit", "btKembali"];
            //BatchFocusRegister(addmaster);
            $("#JournalDate").customDatePicker({ showOn: "focus" });

            $("#bAdDetail").click(function(e){
                if ($('#TrxCode').textbox('getValue') != '' && $('#JournalDate').val() != '') {
                    $('#aAccId').val(0);
                    $('#aKeterangan').val('-');
                    $('#aDbAmount').val(0);
                    $('#aCrAmount').val(0);
                    $('#aCabangId').val(0);
                    $('#aDeptId').val(0);
                    $('#aCustomerId').val(0);
                    $('#aSupplierId').val(0);
                    $('#aEmployeeId').val(0);
                    newItem();
                }else{
                    $.messager.alert('Warning','Data Master belum lengkap!');
                }
            });

            $("#bUpdate").click(function(){
                if (confirm('Apakah data input sudah benar?')){
                    var journalId = "<?php print($journal->Id);?>";
                    var urz = "<?php print($helper->site_url("accounting.journal/proses_master/")); ?>" + journalId;
                    $.post(urz, {
                        JournalDate: $("#JournalDate").val(),
                        JournalNo: $("#JournalNo").val(),
                        JournalDescs: $("#JournalDescs").val(),
                        TrxCode: $('#TrxCode').textbox('getValue'),
                        ReffNo: $("#ReffNo").val()
                    }).done(function (data) {
                        //alert(data);
                        location.reload();
                    });
                }
            });

            $("#bTambah").click(function(){
                if (confirm('Buat Journal baru?')){
                    location.href="<?php print($helper->site_url("accounting.journal/add")); ?>";
                }
            });

            $("#bHapus").click(function(){
                if (confirm('Anda yakin akan membatalkan journal ini?')){
                    location.href="<?php print($helper->site_url("accounting.journal/void/").$journal->Id); ?>";
                }
            });

            $("#bCetak").click(function(){
                if (confirm('Cetak bukti journal ini?')){
                    location.href="<?php print($helper->site_url("accounting.journal/print_pdf/").$journal->Id); ?>";
                }
            });

            $("#bKembali").click(function(){
                location.href="<?php print($helper->site_url("accounting.journal")); ?>";
            });
            
        });

        function newItem(){
            $('#dlg').dialog('open').dialog('setTitle','Tambah Detail Journal');
            //$('#fm').form('clear');
            url = "<?php print($helper->site_url("accounting.journal/add_detail/".$journal->Id));?>";
            $('#aAccId').focus();
        }

        function fdeldetail(dta){
            var dtz = dta.replace(/\"/g,"\\\"")
            var dtx = dtz.split('|');
            var id = dtx[0];
            var kode = dtx[1];
            var ket = dtx[3];
            var urx = '<?php print($helper->site_url("accounting.journal/delete_detail/"));?>'+id;
            if (confirm('Hapus Data Detail Journal \nKode: '+kode+' - '+dtx[2]+ '\nKeterangan: '+ket+' ?')) {
                $.get(urx, function(data){
                    $.messager.alert('Info',data);
                    location.reload();
                });
            }
        }

        function saveDetail(){
            var journalId   = "<?php print($journal->Id == null ? 0 : $journal->Id);?>";
            $.messager.confirm('Confirm','Apakah data input sudah benar ?',function(r) {
                if (r) {
                    var urz = "<?php print($helper->site_url("accounting.journal/proses_master/")); ?>" + journalId;
                    //proses simpan dan update master
                    $.post(urz, {
                        JournalDate: $("#JournalDate").val(),
                        JournalNo: $("#JournalNo").val(),
                        JournalDescs: $("#JournalDescs").val(),
                        TrxCode: $('#TrxCode').textbox('getValue'),
                        ReffNo: $("#ReffNo").val()
                    }).done(function (data) {
                        var rst = data.split('|');
                        if (rst[0] == 'OK') {
                            //validasi detail
                            var arti = rst[2];
                            var urx = "<?php print($helper->site_url("accounting.journal/add/")); ?>" + arti;
                            if (arti > 0) {
                                var url = "<?php print($helper->site_url("accounting.journal/add_detail/"));?>"+arti;
                                //proses submit detail
                                var damt = Number($('#aDbAmount').val()) + Number($('#aCrAmount').val());
                                var acci = $('#aAccId').val();
                                if (damt > 0 && acci > 0) {
                                    $('#fm').form('submit', {
                                        url: url,
                                        onSubmit: function () {
                                            return $(this).form('validate');
                                        },
                                        success: function (result) {
                                            var result = eval('(' + result + ')');
                                            if (result.errorMsg) {
                                                $.messager.show({
                                                    title: 'Error',
                                                    msg: result.errorMsg
                                                });
                                            } else {
                                                location.href = urx;
                                                $('#dlg').dialog('close');		// close the dialog
                                            }
                                        }
                                    });
                                } else {
                                    $.messager.alert('Warning','[E2] Data detail tidak valid!');
                                }
                            }else {
                                $.messager.alert('Warning','[E1] Data master tidak valid!');
                                location.href = urx;
                            }
                        }
                    });
                }
            });
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
<div id="p" class="easyui-panel" title="Entry Journal Akuntansi" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td>Cabang</td>
            <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($journal->CabangCode != null ? $journal->CabangCode : $userCabCode); ?>" disabled/>
                <input type="hidden" id="CabangId" name="CabangId" value="<?php print($journal->CabangId == null ? $userCabId : $journal->CabangId);?>"/>
            </td>
            <td>Tanggal</td>
            <td><input type="text" size="12" id="JournalDate" name="JournalDate" value="<?php print($journal->FormatJournalDate(JS_DATE));?>"/></td>
            <td>No. Journal</td>
            <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="JournalNo" name="JournalNo" value="<?php print($journal->JournalNo != null ? $journal->JournalNo : '-'); ?>" readonly/></td>
        </tr>
        <tr>
            <td>Jenis Transaksi</td>
            <td>
                <?php if ($journal->DbAmount + $journal->CrAmount == 0){?>
                <select class="easyui-combobox" id="TrxCode" name="TrxCode" style="width: 250px" required>
                    <?php }else{ ?>
                    <input type="hidden" name="TrxCode1" id="TrxCode1" value="<?php print($journal->TrxCode);?>"/>
                    <select class="easyui-combobox" id="TrxCode" name="TrxCode" style="width: 250px" disabled>
                        <?php } ?>
                        <option value="">- Pilih Jenis Transaksi -</option>
                        <?php
                        if ($trxtypes != null) {
                            while ($row = $trxtypes->FetchAssoc()) {
                                if ($row["trx_code"] == $journal->TrxCode) {
                                    printf('<option value="%s" selected="selected">%s - %s</option>', $row["trx_code"], $row["trx_code"],$row["keterangan"]);
                                } else {
                                    printf('<option value="%s">%s - %s</option>', $row["trx_code"], $row["trx_code"],$row["keterangan"]);
                                }
                            }
                        }
                        ?>
                    </select>
            </td>
            <td>Status</td>
            <td><select class="easyui-combobox" id="JournalStatus" name="JournalStatus" style="width: 100px" disabled>
                    <option value="0" <?php print($journal->JournalStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                    <option value="1" <?php print($journal->JournalStatus == 1 ? 'selected="selected"' : '');?>>1 - Verified</option>
                    <option value="2" <?php print($journal->JournalStatus == 2 ? 'selected="selected"' : '');?>>2 - Approved</option>
                    <option value="3" <?php print($journal->JournalStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                </select>
            </td>
            <td>Reff No.</td>
            <td><b><input type="text" class="f1 easyui-textbox" id="ReffNo" name="ReffNo" style="width: 150px" value="<?php print($journal->ReffNo != null ? $journal->ReffNo : '-'); ?>"/></b></td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td colspan="3"><b><input type="text" class="f1 easyui-textbox" id="JournalDescs" name="JournalDescs" style="width: 420px" value="<?php print($journal->JournalDescs != null ? $journal->JournalDescs : '-'); ?>"/></b></td>
            <td>
                <?php
                if ($acl->CheckUserAccess("accounting.journal", "edit") && $journal->Id > 0 && $journal->JournalStatus == 0) {
                    printf('<img src="%s" alt="Update Data" title="Update data master" id="bUpdate" style="cursor: pointer;"/>',$bsubmit);
                }
                ?>
            </td>

        </tr>
        <tr>
            <td colspan="8">
                <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" align="left" style="font-size: 12px;font-family: tahoma">
                    <tr>
                        <th colspan="10">DETAIL JOURNAL</th>
                        <th rowspan="2">Action</th>
                    </tr>
                    <tr>
                        <th>No.</th>
                        <th>Akun No.</th>
                        <th>Perkiraan</th>
                        <th>Keterangan</th>
                        <th>Cabang</th>
                        <th>Dept</th>
                        <th>Customer</th>
                        <th>Supplier</th>
                        <th>Debet</th>
                        <th>Kredit</th>
                    </tr>
                    <?php
                    $counter = 0;
                    $dta = null;
                    foreach($journal->Details as $idx => $detail) {
                        $counter++;
                        print("<tr>");
                        printf('<td class="right">%s.</td>', $counter);
                        printf('<td>%s</td>', $detail->AccCode);
                        printf('<td>%s</td>', $detail->AccName);
                        printf('<td>%s</td>', $detail->Keterangan);
                        printf('<td>%s</td>', $detail->CabangCode);
                        printf('<td>%s</td>', $detail->DeptName);
                        printf('<td>%s</td>', $detail->CustName);
                        printf('<td>%s</td>', $detail->SuppName);
                        printf('<td class="right">%s</td>', number_format($detail->DbAmount,2));
                        printf('<td class="right">%s</td>', number_format($detail->CrAmount,2));
                        print("<td class='center'>");
                        $dta = addslashes($detail->Id.'|'.$detail->AccCode.'|'.$detail->AccName.'|'.str_replace('"',' in',$detail->Keterangan));
                        printf('&nbsp<img src="%s" alt="Hapus Detail" title="Hapus Detail Journal" style="cursor: pointer" onclick="Journal fdeldetail(%s);"/>',$bclose,"'".$dta."'");
                        print("</td>");
                        print("</tr>");
                    }
                    ?>
                    <tr>
                        <td colspan="8" align="right">Total :</td>
                        <td class="right bold"><?php print($journal->DbAmount != null ? number_format($journal->DbAmount,0) : 0);?></td>
                        <td class="right bold"><?php print($journal->CrAmount != null ? number_format($journal->CrAmount,0) : 0);?></td>
                        <td class='center'><?php printf('<img src="%s" alt="Tambah Barang" title="Tambah barang" id="bAdDetail" style="cursor: pointer;"/>',$badd);?></td>
                    </tr>
                    <tr>
                        <td colspan="11" class="right">
                            <?php
                            if ($acl->CheckUserAccess("accounting.journal", "add")) {
                                printf('<img src="%s" alt="Data Baru" title="Buat Data Baru" id="bTambah" style="cursor: pointer;"/> &nbsp',$baddnew);
                            }
                            if ($acl->CheckUserAccess("accounting.journal", "delete") && $journal->JournalStatus == 0) {
                                printf('<img src="%s" alt="Hapus Data" title="Hapus Data" id="bHapus" style="cursor: pointer;"/> &nbsp',$bdelete);
                            }
                            if ($acl->CheckUserAccess("accounting.journal", "print") && ($journal->JournalStatus > 0 && $journal->JournalStatus < 3) ) {
                                printf('<img src="%s" alt="Cetak Bukti" title="Cetak Receipt" id="bCetak" style="cursor: pointer;"/> &nbsp',$bcetak);
                            }
                            printf('<img src="%s" id="bKembali" alt="Daftar Return" title="Kembali ke daftar Journal" style="cursor: pointer;"/>',$bkembali);
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<!-- Form Add Journal Detail -->
<div id="dlg" class="easyui-dialog" style="width:100%;height:150px;padding:5px 5px"
     closed="true" buttons="#dlg-buttons">
    <form id="fm" method="post" novalidate>
        <table cellpadding="0" cellspacing="0" class="tablePadding tableBorder" style="font-size: 12px;font-family: tahoma">
            <tr>
                <th>Kode Akun</th>
                <th>Keterangan</th>
                <th>Debet</th>
                <th>Kredit</th>
                <th>Cabang</th>
                <th>Bagian</th>
                <th>Customer</th>
                <th>Supplier</th>
                <th>Karyawan</th>
            </tr>
            <tr>
                <td><select name="aAccId" id="aAccId" style="width: 150px" required>
                        <option value=""></option>
                        <?php
                        /** @var $coas CoaDetail[] */
                        foreach ($coas as $coa){
                            printf("<option value='%d'>%s - %s</option>",$coa->Id,$coa->Kode,$coa->Perkiraan);
                        }
                        ?>
                    </select>
                    <input type="hidden" id="aJournalId" name="aJournalId" value="0"/>
                </td>
                <td>
                    <input type="text" id="aKeterangan" name="aKeterangan" size="30" value="-"/>
                </td>
                <td>
                    <input type="text" class="bold right" id="aDbAmount" name="aDbAmount" size="10" value="0" required/>
                </td>
                <td>
                    <input type="text" class="bold right" id="aCrAmount" name="aCrAmount" size="10" value="0" required/>
                </td>
                <td><select name="aCabangId" id="aCabangId" style="width: 100px" required>
                        <option value="0"></option>
                        <?php
                        /** @var $cabList Cabang[] */
                        foreach ($cabList as $cabang){
                            if ($cabang->Id == $journal->CabangId) {
                                printf("<option value='%d' selected='selected'>%s</option>", $cabang->Id, $cabang->Cabang);
                            }else{
                                printf("<option value='%d'>%s</option>",$cabang->Id,$cabang->Cabang);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td><select name="aDeptId" id="aDeptId" style="width: 100px" required>
                        <option value="0"></option>
                        <?php
                        /** @var $depts Department[] */
                        foreach ($depts as $dept){
                            printf("<option value='%d'>%s</option>",$dept->Id,$dept->DeptName);
                        }
                        ?>
                    </select>
                </td>
                <td><select name="aCustomerId" id="aCustomerId" style="width: 100px" required>
                        <option value="0"></option>
                        <?php
                        /** @var $custs Customer[] */
                        foreach ($custs as $customer){
                            printf("<option value='%d'>%s</option>",$customer->Id,$customer->CusName);
                        }
                        ?>
                    </select>
                </td>
                <td><select name="aSupplierId" id="aSupplierId" style="width: 100px" required>
                        <option value="0"></option>
                        <?php
                        /** @var $supps Supplier[] */
                        foreach ($supps as $supplier){
                            printf("<option value='%d'>%s</option>",$supplier->Id,$supplier->SupName);
                        }
                        ?>
                    </select>
                </td>
                <td><select name="aEmployeeId" id="aEmployeeId" style="width: 100px" required>
                        <option value="0"></option>
                        <?php
                        /** @var $karyas Karyawan[] */
                        foreach ($karyas as $karyawan){
                            printf("<option value='%d'>%s</option>",$karyawan->Id,$karyawan->Nama);
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
    </form>
    <br>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveDetail()" style="width:90px">Proses</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Batal</a>
</div>
<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- </body> -->
</html>
