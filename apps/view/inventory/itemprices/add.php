<!DOCTYPE HTML>
<?php /** @var $itemprices ItemPrices */
$crDate = date(JS_DATE, strtotime(date('Y-m-d')));
?>
<html>
<head>
	<title>SND System - Entry Harga Barang</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/default/easyui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/icon.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-themes/color.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/easyui-demo/demo.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.easyui.min.js")); ?>"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            //var elements = ["ItemCode","PriceDate","MaxDisc","PurchasePrice","Markup1","HrgJual11","Markup2","HrgJual12","Markup3","HrgJual13","Markup4","HrgJual14","Markup5","HrgJual15","Markup6","HrgJual16","Submit"];
            //BatchFocusRegister(elements);
            $("#PriceDate").customDatePicker({ showOn: "focus" });

            var bid = '<?php print($itemprices->ItemId);?>';
            var bkode = '<?php print($itemprices->ItemCode);?>';
            var bnama = '<?php print($itemprices->ItemName);?>';
            var satbesar = '<?php print($itemprices->LuomCode);?>';
            var satkecil = '<?php print($itemprices->SuomCode);?>';
            var isikecil = '<?php print($itemprices->SuomQty);?>';
            var satjual = '<?php print($itemprices->UomCode);?>';
            //add satuan option
            $("#UomCode").empty();
            $("#UomCode").append('<option value=""></option>');
            if (satbesar != '' && satbesar != null) {
                $("#UomCode").append('<option value="'+satbesar+'">'+satbesar+'</option>');
            }
            if (satkecil != '' && satkecil != null) {
                $("#UomCode").append('<option value="'+satkecil+'">'+satkecil+'</option>');
            }
            $('#UomCode').val(satjual);

            $('#ItemSearch').combogrid({
                panelWidth:600,
                url: "<?php print($helper->site_url("inventory.items/getitems_json/0"));?>",
                idField:'item_id',
                textField:'item_name',
                mode:'remote',
                fitColumns:true,
                columns:[[
                    {field:'item_code',title:'Kode',width:50,sortable:true},
                    {field:'item_name',title:'Nama Barang',sortable:true,width:200},
                    {field:'l_uom_code',title:'Besar',width:50},
                    {field:'s_uom_qty',title:'Isi',width:50},
                    {field:'s_uom_code',title:'Kecil',width:50}
                ]],
                onSelect: function(index,row){
                    bid = row.item_id;
                    console.log(bid);
                    bkode = row.item_code;
                    console.log(bkode);
                    bnama = row.item_name;
                    console.log(bnama);
                    satbesar = row.l_uom_code;
                    console.log(satbesar);
                    satkecil = row.s_uom_code;
                    console.log(satkecil);
                    isikecil = row.s_uom_qty;
                    console.log(isikecil);
                    $('#ItemId').val(bid);
                    $('#ItemCode').val(bkode);
                    $('#ItemName').val(bnama);
                    $('#LuomCode').val(satbesar);
                    $('#SuomCode').val(satkecil);
                    $('#SuomQty').val(isikecil);
                    //add satuan option
                    $("#UomCode").empty();
                    $("#UomCode").append('<option value=""></option>');
                    if (satbesar != '' && satbesar != null) {
                        $("#UomCode").append('<option value="'+satbesar+'">'+satbesar+'</option>');
                    }
                    if (satkecil != '' && satkecil != null) {
                        $("#UomCode").append('<option value="'+satkecil+'">'+satkecil+'</option>');
                    }
                    $('#UomCode').val(satbesar);
                }
            });

            $("#PurchasePrice").change(function(e){
                $("#Hpp").val(this.value);
            });

        });

        function formatPrice(num,row){
            return Number(num).toLocaleString();
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
	<legend><span class="bold">Entry Data Harga Barang</span></legend>
	<form action="<?php print($helper->site_url("inventory.itemprices/add/".$itemprices->Id)); ?>" method="post">
        <table cellpadding="0" cellspacing="0" class="tablePadding" style="font-size: 12px;font-family: tahoma">
            <tr>
                <td class="bold right">Cabang :</td>
                <td colspan="3"><input type="text" class="f1 easyui-textbox" maxlength="20" style="width: 130px" id="CabangCode" name="CabangCode" value="<?php print($itemprices->CabangCode != null ? $itemprices->CabangCode : $userCabCode); ?>" disabled/>
                    <input type="hidden" id="CabangId" name="CabangId" value="<?php print($itemprices->CabangId == null ? $userCabId : $itemprices->CabangId);?>"/>
                </td>
            </tr>
            <tr>
                <td class="bold right">Cari Data :</td>
                <td colspan="6"><input id="ItemSearch" name="ItemSearch" style="width: 500px"/></td>
            </tr>
            <tr>
                <td class="bold right">Kode Barang :</td>
                <td>
                    <input type="text" class="bold" id="ItemCode" name="ItemCode" size="15" value="<?php print($itemprices->ItemCode);?>" readonly required/>
                    <input type="hidden" id="ItemId" name="ItemId" value="<?php print($itemprices->ItemId);?>"/>
                    <input type="hidden" id="Id" name="Id" value="<?php print($itemprices->Id);?>"/>
                    &nbsp;
                    <b>Per Tanggal :</b>
                    <input type="text" class="bold" size="10" id="PriceDate" name="PriceDate" value="<?php print($itemprices->FormatPriceDate(JS_DATE));?>" required/>
                </td>
            </tr>
            <tr>
                <td class="bold right">Nama Barang :</td>
                <td colspan="6">
                    <input type="text" class="bold" id="ItemName" name="ItemName" style="width:490px" value="<?php print(htmlspecialchars($itemprices->ItemName));?>" readonly/>
                </td>
            </tr>
            <tr>
                <td class="bold right">Satuan Besar :</td>
                <td colspan="5"><input type="text" class="bold" id="LuomCode" name="LuomCode" size="10" value="<?php print($itemprices->LuomCode);?>" readonly/>
                    &nbsp;
                    <b>Isi :</b>
                    <input type="text" class="bold right" id="SuomQty" name="SuomQty" size="3" value="<?php print($itemprices->SuomQty);?>" readonly/>
                    <input type="text" class="bold" id="SuomCode" name="SuomCode" size="10" value="<?php print($itemprices->SuomCode);?>" readonly/>
                </td>
            </tr>
            <tr>
                <td class="bold right" valign="top">Satuan Jual :</td>
                <td class="bold" valign="top">
                    <select id="UomCode" name="UomCode" style="width:100px" required>
                        <option value=""></option>
                    </select>
                </td>
            </tr>
            <?php if ($ulevel > 1){?>
            <tr>
                <td class="bold right">Harga Beli :</td>
                <td><input class="bold right" type="text" id="PurchasePrice" name="PurchasePrice" size="10" value="<?php print($itemprices->PurchasePrice == null ? 0 : $itemprices->PurchasePrice);?>"/>
                    <b>HPP :</b>
                    <input class="bold right" type="text" id="Hpp" name="Hpp" size="10" value="<?php print($itemprices->Hpp == null ? 0 : $itemprices->Hpp);?>"/>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td class="bold right" valign="top">Harga Jual :</td>
                <td colspan="6">
                    <table cellpadding="1" cellspacing="1" class="tablePadding tableBorder">
                        <tr>
                            <th>Zone 1</th>
                            <th>Zone 2</th>
                            <th>Zone 3</th>
                            <th>Zone 4</th>
                            <th>Zone 5</th>
                        </tr>
                        <tr>
                            <td><input class="bold right" type="text" id="pZone1" name="pZone1" size="10" value="<?php print($itemprices->pZone1 == null ? 0 : $itemprices->pZone1);?>"/></td>
                            <td><input class="bold right" type="text" id="pZone2" name="pZone2" size="10" value="<?php print($itemprices->pZone2 == null ? 0 : $itemprices->pZone2);?>"/></td>
                            <td><input class="bold right" type="text" id="pZone3" name="pZone3" size="10" value="<?php print($itemprices->pZone3 == null ? 0 : $itemprices->pZone3);?>"/></td>
                            <td><input class="bold right" type="text" id="pZone4" name="pZone4" size="10" value="<?php print($itemprices->pZone4 == null ? 0 : $itemprices->pZone4);?>"/></td>
                            <td><input class="bold right" type="text" id="pZone5" name="pZone5" size="10" value="<?php print($itemprices->pZone5 == null ? 0 : $itemprices->pZone5);?>"/></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="3"><button type="submit" id="Submit" class="button"><b>Update Data</b></button>
                    &nbsp&nbsp
                    <a href="<?php print($helper->site_url("inventory.itemprices")); ?>">Daftar Harga Barang</a>
                </td>
            </tr>
        </table>
    </form>
</fieldset>
<!-- </body> -->
</html>
