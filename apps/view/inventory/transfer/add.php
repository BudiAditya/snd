<!DOCTYPE HTML>
<html>
<?php
/** @var $transfer Transfer */
?>
<head>
	<title>SND System - Entry Pengiriman Barang Antar Gudang</title>
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

            var addmaster = ["FrWhId", "NpbDate","ToWhId","NpbStatus","NpbDescs", "btSubmit", "btKembali"];
            BatchFocusRegister(addmaster);

            $("#NpbDate").customDatePicker({ showOn: "focus" });
            
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
            btransfer-bottom:1px solid #ccc;
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
<div id="p" class="easyui-panel" title="Entry Pengiriman Barang Antar Gudang" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <form id="frmMaster" action="<?php print($helper->site_url("inventory.transfer/add")); ?>" method="post">
        <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
            <tr>
                <td>Dari Gudang</td>
                <td><select name="FrWhId" class="easyui-combobox" id="FrWhId" style="width: 250px">
                        <option value=""></option>
                        <?php
                        foreach ($whfrom as $gdg) {
                            if ($gdg->Id == $transfer->FrWhId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $gdg->Id, $gdg->CabCode, $gdg->WhCode);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $gdg->Id, $gdg->CabCode, $gdg->WhCode);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Tanggal</td>
                <td><input type="text" size="12" id="NpbDate" name="NpbDate" value="<?php print($transfer->FormatNpbDate(JS_DATE));?>" required/></td>
                <td>No. NPB</td>
                <td><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 150px" id="NpbNo" name="NpbNo" value="<?php print($transfer->NpbNo != null ? $transfer->NpbNo : '-'); ?>" readonly/></td>
            </tr>
            <tr>
                <td>Ke Gudang</td>
                <td><select name="ToWhId" class="easyui-combobox" id="ToWhId" style="width: 250px">
                        <option value=""></option>
                        <?php
                        foreach ($whdest as $gdg) {
                            if ($gdg->Id == $transfer->ToWhId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $gdg->Id, $gdg->CabCode, $gdg->WhCode);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $gdg->Id, $gdg->CabCode, $gdg->WhCode);
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>Status</td>
                <td><select class="easyui-combobox" id="NpbStatus" name="NpbStatus" style="width: 100px">
                        <option value="0" <?php print($transfer->NpbStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                        <option value="1" <?php print($transfer->NpbStatus == 1 ? 'selected="selected"' : '');?>>1 - Posted</option>
                        <option value="2" <?php print($transfer->NpbStatus == 2 ? 'selected="selected"' : '');?>>2 - Closed</option>
                        <option value="3" <?php print($transfer->NpbStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td colspan="3"><b><input type="text" class="f1 easyui-textbox" id="NpbDescs" name="NpbDescs" style="width: 250px" maxlength="150" value="<?php print($transfer->NpbDescs != null ? $transfer->NpbDescs : '-'); ?>" /></b></td>
                <td colspan="2" align="center">
                    <a id="btKembali" href="<?php print($helper->site_url("inventory.transfer")); ?>" class="button">Kembali</a>
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
