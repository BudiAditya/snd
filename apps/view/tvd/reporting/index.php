<!DOCTYPE HTML>
<?php
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
?>
<html>
<head>
    <title>Rekasys - Castrol Reporting</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/auto-numeric.js")); ?>"></script>
    <script type="text/javascript">
        var urc = "<?php print($helper->site_url("tvd.reporting/create")); ?>";
        $(document).ready(function() {
            $("#startDate").customDatePicker({ showOn: "focus" });
            $("#endDate").customDatePicker({ showOn: "focus" });
            $("#cbAll").change(function(e) { cbAll_Change(this, e);	});

            $("#btnGenerate").click(function() {
                var test = $(".cbox:checked");
                if (test.length == 0) {
                    alert("Belum ada data yang dipilih!");
                }else {
                    $("#frm").attr('action', urc).submit();
                }
            });
        });

        function cbAll_Change(sender, e) {
            $(":checkbox.cbox").each(function(idx, ele) {
                ele.checked = sender.checked;
            });
        }
    </script>
</head>
<body>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
    <legend><b>PELAPORAN CASTROL</b></legend>
    <form id="frm" method="post">
        <table cellpadding="1" cellspacing="1" class="tablePadding">
            <tr>
                <td align="right">From :</td>
                <td><input type="text" class="text2" maxlength="10" size="10" id="startDate" name="startDate" value="<?php print(is_int($startDate) ? date(JS_DATE,$startDate) : null);?>" /></td>
            </tr>
            <tr>
                <td align="right">To :</td>
                <td><input type="text" class="text2" maxlength="10" size="10" id="endDate" name="endDate" value="<?php print(is_int($endDate) ? date(JS_DATE,$endDate) : null);?>" /></td>
            </tr>
            <tr class="bold">
                <td>&nbsp;</td>
                <td><input type="checkbox" name="cbAll" id="cbAll">Check All</td>
            </tr>
            <tr class="bold">
                <td>&nbsp;</td>
                <td><input type="checkbox" class="cbox" name="cbEmployee" id="cbEmployee" value="1" <?php print($cbEmployee == 1 ? 'checked="checked"' : '');?>/>Employee</td>
            </tr>
            <tr class="bold">
                <td>&nbsp;</td>
                <td><input type="checkbox" class="cbox" name="cbCustomer" id="cbCustomer" value="1" <?php print($cbCustomer == 1 ? 'checked="checked"' : '');?>/>Customer</td>
            </tr>
            <tr class="bold">
                <td>&nbsp;</td>
                <td><input type="checkbox" class="cbox" name="cbInvoice" id="cbInvoice" value="1" <?php print($cbInvoice == 1 ? 'checked="checked"' : '');?>/>Invoice</td>
            </tr>
            <tr class="bold">
                <td>&nbsp;</td>
                <td><input type="checkbox" class="cbox" name="cbWarehouse" id="cbWarehouse" value="1" <?php print($cbWarehouse == 1 ? 'checked="checked"' : '');?>/>Warehouse</td>
            </tr>
            <tr class="bold">
                <td>&nbsp;</td>
                <td><input type="checkbox" class="cbox" name="cbStock" id="cbStock" value="1" <?php print($cbStock == 1 ? 'checked="checked"' : '');?>/>Stock</td>
                <td class="left">
                    <button id="btnGenerate">GENERATE</button>
                </td>
            </tr>
        </table>
    </form>
</fieldset>
<!-- </body> -->
</html>
