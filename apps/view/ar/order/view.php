<!DOCTYPE HTML>
<html>
<?php
/** @var $order Order */
?>
<head>
    <title>SND System - View Sales Order (SO)</title>
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
        var iQty = 0;
        $(document).ready(function() {

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
                ]]
            });

            $('#ItemId').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("ar.order/getitems_json/0"));?>",
                idField:'item_id',
                textField:'item_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'item_name',title:'Nama Barang',width:200},
                    {field:'item_code',title:'Kode',width:50},
                    {field:'l_uom_code',title:'Large',width:40},
                    {field:'s_uom_qty',title:'Isi',width:40},
                    {field:'s_uom_code',title:'Small',width:40}
                ]],
                onSelect: function(index,row){
                    var luom = row.l_uom_code;
                    console.log(luom);
                    var suom = row.s_uom_code;
                    console.log(suom);
                    var sqty = row.s_uom_qty;
                    console.log(sqty);
                    $('#lUom').text(luom+' x '+sqty);
                    $('#sUom').text(suom);
                    $('#qUom').text(suom);
                    iQty = sqty;
                }
            });

            $("#lQty").change(function(e){
                hitQty();
            });

            $("#sQty").change(function(e){
                hitQty();
            });
        });
        //hitung isi
        function hitQty() {
            var lQTy = Number($("#lQty").val());
            var sQTy = Number($("#sQty").val());
            var rQty = Number((lQTy * iQty)) + sQTy;
            $("#aQty").val(rQty);
        }
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
<div id="p" class="easyui-panel" title="View Sales Order (SO)" style="width:100%;height:100%;padding:10px;" data-options="footer:'#ft'">
    <table cellpadding="0" cellspacing="0" class="tablePadding" align="left" style="font-size: 13px;font-family: tahoma">
        <tr>
            <td class="right bold">Tanggal Order :</td>
            <td><input type="text" class="easyui-datebox" size="10" id="OrderDate" name="OrderDate" value="<?php print($order->FormatOrderDate(SQL_DATEONLY));?>" disabled data-options="formatter:myformatter,parser:myparser"/></td>
            <td class="right bold">Prioritas :</td>
            <td><select class="easyui-combobox" id="PriorityId" name="PriorityId" style="width: 110px" disabled>
                    <option value="0" <?php print($order->PriorityId == 0 ? 'selected="selected"' : '');?>>0 - Normal</option>
                    <option value="1" <?php print($order->PriorityId == 1 ? 'selected="selected"' : '');?>>1 - Utama</option>
                </select>
            </td>
            <td class="right bold">Tgl. Faktur :</td>
            <td><input type="text" class="easyui-datebox" size="10" id="RequestDate" name="RequestDate" value="<?php print($order->FormatRequestDate(SQL_DATEONLY));?>" disabled data-options="formatter:myformatter,parser:myparser"/></td>
        </tr>
        <tr>
            <td class="right bold">Customer/Outlet :</td>
            <td colspan="3"><input class="easyui-combogrid" id="CustomerId" name="CustomerId" value="<?=$order->CustomerId;?>" style="width: 290px" disabled/></td>
            <td class="right bold">Salesman :</td>
            <td><select class="easyui-combobox" id="SalesId" name="SalesId" style="width: 150px" disabled>
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
        </tr>
        <tr>
            <td class="right bold">Nama Produk :</td>
            <td colspan="3"><input class="easyui-combogrid" id="ItemId" name="ItemId" value="<?=$order->ItemId;?>" style="width: 290px" disabled/></td>
            <td class="right bold">Status :</td>
            <td><select class="easyui-combobox" id="OrderStatus" name="OrderStatus" style="width: 150px" disabled>
                    <option value="0" <?php print($order->OrderStatus == 0 ? 'selected="selected"' : '');?>>0 - Draft</option>
                    <option value="1" <?php print($order->OrderStatus == 1 ? 'selected="selected"' : '');?>>1 - Open</option>
                    <option value="2" <?php print($order->OrderStatus == 2 ? 'selected="selected"' : '');?>>2 - Closed</option>
                    <option value="3" <?php print($order->OrderStatus == 3 ? 'selected="selected"' : '');?>>3 - Batal</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="right bold">L - QTY :</td>
            <td colspan="5">
                <input class="right bold" type="text" id="lQty" name="lQty" size="5" value="<?=$order->Lqty;?>" disabled/>&nbsp;<span id="lUom"></span>
                &nbsp;
                <b>+ S - QTY :</b>
                &nbsp;
                <input class="right bold" type="text" id="sQty" name="sQty" size="5" value="<?=$order->Sqty;?>" disabled/>&nbsp;<span id="sUom"></span>
                &nbsp;
                <b>= QTY :</b>
                &nbsp;
                <input class="right bold" type="text" id="aQty" name="aQty" size="5" value="<?=$order->OrderQty;?>" disabled/>&nbsp;<span id="qUom"></span>
            </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="3"><a href="<?php print($helper->site_url("ar.order")); ?>" class="button">Kembali</a></td>
        </tr>
    </table>
</div>

<div id="ft" style="padding:5px; text-align: center; font-family: verdana; font-size: 9px" >
    Copyright &copy; 2018 - <?=date('Y');?> <a href="https://rekasys.com" target="_blank">Rekasys Inc</a>
</div>
<!-- </body> -->
</html>
