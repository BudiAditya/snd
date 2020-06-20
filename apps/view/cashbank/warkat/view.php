<!DOCTYPE HTML>
<html>
<?php
/** @var $warkat Warkat */  /** @var $accounts CoaDetail[] */
?>
<head>
    <title>SND System - View Data Warkat</title>
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

        $(document).ready(function() {

            $("#WarkatDate").customDatePicker({ showOn: "focus" });

            $('#CustomerId').combogrid({
                panelWidth:620,
                url: "<?php print($helper->site_url("ar.customer/getJsonCustomer"));?>",
                idField:'id',
                textField:'cus_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'cus_code',title:'Kode',width:50},
                    {field:'cus_name',title:'Nama Customer',width:150},
                    {field:'addr1',title:'Alamat',width:150},
                    {field:'area_name',title:'Area',width:60}
                ]]
            });

            $("#WarkatNo").change(function(){
                var url = "<?php print($helper->site_url("cashbank.warkat/checkIfExistWarkat/"));?>"+this.value;
                $.get(url, function(data) {
                    //alert(data);
                    if (data != 'OK'){
                        var dtx = data.split('|');
                        var wkn = $("#WarkatNo").val();
                        alert('Warkat No: '+wkn+' sudah pernah diinput!'+'\nTanggal: '+dtx[1]+'\nAtas Nama: '+dtx[2]+'\nSenilai: '+dtx[3]);
                        $("#WarkatNo").val('');
                    }
                });
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
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<div id="p" class="easyui-panel" title="View Data Warkat (BG/Cheque)" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td>Cabang</td>
            <td colspan="3"><select name="CabangId" class="easyui-combobox" id="CabangId" style="width: 250px" readonly>
                    <?php
                    if($userLevel > 3){
                        print('<option value="0"></option>');
                        foreach ($cabangs as $cab) {
                            if ($cab->Id == $warkat->CabangId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $cab->Id, $cab->Kode, $cab->Cabang);
                            }
                        }
                    }else{
                        printf('<option value="%d">%s - %s</option>', $userCabId, $userCabCode, $userCabName);
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Tgl. Warkat</td>
            <td><input class="easyui-datebox" type="text" style="width: 95px" id="WarkatDate" name="WarkatDate" value="<?php print($warkat->FormatWarkatDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser" readonly/>
                &nbspMode
                <select class="easyui-combobox" id="WarkatMode" name="WarkatMode" style="width: 110px" readonly>
                    <option value="1" <?php print($warkat->WarkatMode == 1 ? 'selected="selected"' : '');?>>1 - Masuk</option>
                    <option value="2" <?php print($warkat->WarkatMode == 2 ? 'selected="selected"' : '');?>>2 - Keluar</option>
                </select>
            </td>
            <td>No. Warkat</td>
            <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 145px" id="WarkatNo" name="WarkatNo" value="<?php print($warkat->WarkatNo); ?>" readonly/></td>
            <td>Status</td>
            <td><select class="easyui-combobox" id="WarkatStatus" name="WarkatStatus" style="width: 150px" readonly>
                    <option value="0" <?php print($warkat->WarkatStatus == 0 ? 'selected="selected"' : '');?>>0 - Baru</option>
                    <option value="1" <?php print($warkat->WarkatStatus == 1 ? 'selected="selected"' : '');?>>1 - Cair</option>
                    <option value="2" <?php print($warkat->WarkatStatus == 2 ? 'selected="selected"' : '');?>>2 - Batal</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Relasi</td>
            <td colspan="2"><input class="easyui-combogrid" id="CustomerId" name="CustomerId" value="<?php print($warkat->CustomerId); ?>" style="width: 250px" readonly/>
                &nbsp&nbspWarkat Bank </td>
            <td><select class="easyui-combobox" id="WarkatBankId" name="WarkatBankId" style="width: 150px" readonly>
                    <?php
                    /** @var $banks Bank[] */
                    foreach ($banks as $bank) {
                        if ($bank->Id == $warkat->WarkatBankId) {
                            printf('<option value="%d" selected="selected">%s</option>', $bank->Id, $bank->BankName);
                        } else {
                            printf('<option value="%d">%s</option>', $bank->Id, $bank->BankName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>No. Reff</td>
            <td><input type="text" class="f1 easyui-textbox" maxlength="50" style="width: 150px" id="ReffNo" name="ReffNo" value="<?php print($warkat->ReffNo != null ? $warkat->ReffNo : '-'); ?>" readonly/></td>
        </tr>
        <tr>
            <td>Cair Ke</td>
            <td><select class="easyui-combobox" id="ReffAccId" name="ReffAccId" style="width: 250px" readonly>
                    <?php
                    /** @var $kasbanks KasBank[] */
                    foreach ($kasbanks as $bank) {
                        if ($bank->Id == $warkat->ReffAccId) {
                            printf('<option value="%d" selected="selected">%s</option>', $bank->Id, $bank->BankName);
                        } else {
                            printf('<option value="%d">%s</option>', $bank->Id, $bank->BankName);
                        }
                    }
                    ?>
                </select>
            </td>
            <td>Nilai Warkat</td>
            <td><input type="text" class="easyui-textbox" maxlength="20" style="width: 145px;text-align: right" id="WarkatAmount" name="WarkatAmount" value="<?php print($warkat->WarkatAmount != null ? number_format($warkat->WarkatAmount,0) : 0); ?>" readonly/></td>
            <td>Tgl. Proses</td>
            <td><input class="easyui-datebox" type="text" style="width: 100px" id="ProcessDate" name="ProcessDate" value="<?php print($warkat->FormatProcessDate(SQL_DATEONLY));?>" data-options="formatter:myformatter,parser:myparser" readonly/>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td><b><input type="text" class="easyui-textbox" id="WarkatDescs" name="WarkatDescs" style="width: 250px" maxlength="250" value="<?php print($warkat->WarkatDescs != null ? $warkat->WarkatDescs : '-'); ?>" readonly/></b></td>
            <td colspan="2" align="right">
                <a id="btDaftar" href="<?php print($helper->site_url("cashbank.warkat")); ?>">Daftar Warkat</a>
            </td>
        </tr>
    </table>
</div>

<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- </body> -->
</html>
