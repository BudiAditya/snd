<!DOCTYPE HTML>
<html>
<?php
/** @var $order Order */ 
?>
<head>
	<title>SND System - Entry Sales Order (SO)</title>
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

            //var addmaster = ["CabangId", "SoDate","RequestDate","CustomerId", "SalesName", "SoDescs", "PaymentType","CreditTerms","btSubmit", "btKembali"];
            //BatchFocusRegister(addmaster);

            $("#SoDate").customDatePicker({ showOn: "focus" });
            $("#RequestDate").customDatePicker({ showOn: "focus" });

            $('#CustomerId').combogrid({
                panelWidth:600,
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
                ]],
                onSelect: function(index,row){
                    var term = row.term;
                    console.log(term);
                    if (term > 0){
                        $("#PaymentType").val(1);
                        $("#CreditTerms").val(term);
                    }
                }
            });
        });

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
<div id="p" class="easyui-panel" title="Entry Sales Order (SO)" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <form id="frmMaster" action="<?php print($helper->site_url("ar.order/add")); ?>" method="post">
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Cabang</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 250px" id="CabangCode" name="CabangCode" value="<?php print($order->CabangCode != null ? $order->CabangCode : $userCabCode); ?>" disabled/>
                    <input type="hidden" id="CabangId" name="CabangId" value="<?php print($order->CabangId == null ? $userCabId : $order->CabangId);?>"/>
                </td>
                <td>Tanggal</td>
                <td><input type="text" size="12" id="SoDate" name="SoDate" value="<?php print($order->FormatSoDate(JS_DATE));?>" required/></td>
                <td>Dibutuhkan</td>
                <td><input type="text" size="12" id="RequestDate" name="RequestDate" value="<?php print($order->FormatRequestDate(JS_DATE));?>" /></td>
                <td>No. Order</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="SoNo" name="SoNo" value="<?php print($order->SoNo != null ? $order->SoNo : '-'); ?>" readonly/></td>
            </tr>
            <tr>
                <td>Customer</td>
                <td><input class="easyui-combogrid" id="CustomerId" name="CustomerId" style="width: 250px"/></td>
                <td>Salesman</td>
                <td><select class="easyui-combobox" id="SalesId" name="SalesId" style="width: 150px">
                        <option value=""></option>
                        <?php
                        /** @var $sales Salesman[]*/
                        foreach ($sales as $staf) {
                            if ($staf->Id == $order->SalesId) {
                                printf('<option value="%d" selected="selected">%s</option>', $staf->Id, $staf->SalesName);
                            }else{
                                printf('<option value="%d">%s</option>', $staf->Id, $staf->SalesName);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Status</td>
                <td><select class="easyui-combobox" id="SoStatus" name="SoStatus" style="width: 100px" disabled>
                        <option value="0" <?php print($order->SoStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                        <option value="1" <?php print($order->SoStatus == 1 ? 'selected="selected"' : '');?>>1 - Open</option>
                        <option value="2" <?php print($order->SoStatus == 2 ? 'selected="selected"' : '');?>>2 - Closed</option>
                        <option value="3" <?php print($order->SoStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td><b><input type="text" class="f1 easyui-textbox" id="SoDescs" name="SoDescs" style="width: 250px" maxlength="150" value="<?php print($order->SoDescs != null ? $order->SoDescs : '-'); ?>" /></b></td>
                <td>Cara Bayar</td>
                <td><select id="PaymentType" name="PaymentType" required>
                        <option value="1" <?php print($order->PaymentType == 1 ? 'selected="selected"' : '');?>>Kredit</option>
                        <option value="0" <?php print($order->PaymentType == 0 ? 'selected="selected"' : '');?>>Tunai</option>
                    </select>
                    &nbsp
                    Kredit
                    <input type="text" id="CreditTerms" name="CreditTerms" size="2" maxlength="5" value="<?php print($order->CreditTerms != null ? $order->CreditTerms : 0); ?>" style="text-align: right" required/>&nbsphr</td>
            </tr>
            <tr>
                <td colspan="6" align="right">
                    <a id="btKembali" href="<?php print($helper->site_url("ar.order")); ?>" class="button">Kembali</a>
                    <button id="btSubmit" type="submit">Berikutnya &gt;</button>
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- </body> -->
</html>
