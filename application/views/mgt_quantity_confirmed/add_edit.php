<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
if(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE"),
        'id'=>'button_action_save',
        'data-form'=>'#save_form'
    );
}
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<?php if($cogs_total>0){?>
<div class="alert alert-warning">
    <?php echo $CI->lang->line('MSG_QC_EDIT_WARNING'); ?>
</div>
<?php
}
?>
<form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="year_id" value="<?php echo $year_id; ?>" />
    <input type="hidden" name="variety_id" value="<?php echo $variety_id; ?>" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">HOM Budget</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $hom_budget;?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Stock Warehouse</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $stock_warehouse;?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Stock outlet</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $stock_outlet;?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Current Stock</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $current_stock;?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Quantity Expected</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $quantity_expected;?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Variety Name</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $variety['name'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Confirmed Total Quantity</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label id="total_quantity_confirmed" class="control-label"><?php if(isset($quantity_confirmed_total)){echo number_format($quantity_confirmed_total,3);} else{echo '0.000';}?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Variety Wise Total COGS</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label id="variety_total_cogs" class="control-label"><?php if(isset($cogs_total)){echo number_format($cogs_total,2);} else{echo '0.00';}?></label>
            </div>
        </div>
        <div class="panel-group" id="accordion">
            <?php
            $count=0;
            foreach($items as $id=>$item)
            {
                $count++;
                ?>
                <div class="panel panel-default" id="principal_container_<?php echo $id;?>">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a class="accordion-toggle external" data-toggle="collapse"  data-target="#collapse_<?php echo $id;?>" href="#">
                                <?php echo "Principal No. $count : ".$item['principal_name'];?>
                            </a>
                        </h4>
                    </div>
                    <div id="collapse_<?php echo $id;?>" class="panel-collapse collapse">

                        <div style="" class="row show-grid">
                            <div class="col-xs-4">
                                <label class="control-label pull-right">Principal Name</label>
                            </div>
                            <div class="col-sm-4 col-xs-8">
                                <label class="control-label"><?php echo $item['principal_name'];?></label>
                            </div>
                        </div>
                        <div style="" class="row show-grid">
                            <div class="col-xs-4">
                                <label class="control-label pull-right">Import Name</label>
                            </div>
                            <div class="col-sm-4 col-xs-8">
                                <label class="control-label"><?php echo $item['name_import'];?></label>
                            </div>
                        </div>
                        <div style="" class="row show-grid">
                            <div class="col-xs-4">
                                <label class="control-label pull-right">Quantity Confirm(kg)</label>
                            </div>
                            <div class="col-sm-4 col-xs-8">
                                <label class="control-label sub_total_quantity" id="quantity_confirmed_<?php echo $id;?>"><?php if(isset($item['quantity_total'])){echo $item['quantity_total'];} else{echo '0.000';}?></label>
                            </div>
                        </div>

                        <div style="" class="row show-grid">
                            <div class="col-xs-4">
                                <label class="control-label pull-right">Months</label>
                            </div>
                            <div class="col-xs-8">
                                <div class="row">
                                    <?php
                                    for($i=1;$i<13;$i++)
                                    {
                                        ?>
                                        <div class="col-xs-1">
                                            <label class="control-label pull-right"><?php echo date("M", mktime(0, 0, 0,  ($i),1, 2000));?></label>
                                        </div>
                                        <div class="col-xs-2">
                                            <input name="items[<?php echo $id;?>][quantity_<?php echo ($i);?>]" type="text" class="form-control float_type_positive months quantity_month_<?php echo $id;?>" data-principal-id="<?php echo $id;?>" style="float: left;margin-bottom: 5px;" value="<?php if(isset($item['quantity_'.$i])){echo $item['quantity_'.$i];}?>">
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div style="" class="row show-grid">
                            <div class="col-xs-4">
                                <label class="control-label pull-right">Price/KG</label>
                            </div>
                            <div class="col-xs-2">
                                <input id="price_<?php echo $id;?>" name="items[<?php echo $id;?>][price_unit]" type="text" class="form-control float_type_positive price" data-principal-id="<?php echo $id;?>" style="float: left;" value="<?php if(isset($item['price_unit'])){echo $item['price_unit'];}?>">
                            </div>
                            <div class="col-xs-2">
                                <select id="currency_id_<?php echo $id;?>" name="items[<?php echo $id;?>][currency_id]" class="form-control currency_id" data-principal-id="<?php echo $id;?>">
                                    <?php
                                    foreach($currencies as $currency)
                                    {?>
                                        <option value="<?php echo $currency['value']?>" <?php if(isset($item['currency_id'])&&($currency['value']==$item['currency_id'])){ echo "selected";}?>><?php echo $currency['text'];?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div style="" class="row show-grid">
                            <div class="col-xs-4">
                                <label class="control-label pull-right">COGS</label>
                            </div>
                            <div class="col-sm-4 col-xs-8">
                                <label id="cogs_<?php echo $id;?>" class="control-label"><?php if(isset($item['cogs_kg'])){echo number_format($item['cogs_kg'],2);} else{echo '0.00';}?></label>
                            </div>
                        </div>
                        <div style="" class="row show-grid">
                            <div class="col-xs-4">
                                <label class="control-label pull-right">Total COGS</label>
                            </div>
                            <div class="col-sm-4 col-xs-8">
                                <label id="total_cogs_<?php echo $id;?>" class="control-label sub_total_cogs"><?php if(isset($item['cogs_total'])){echo number_format($item['cogs_total'],2);} else{echo '0.00';}?></label>
                            </div>
                        </div>
                    </div>
                </div>

            <?php
            }
            ?>
        </div>
    </div>
</form>

<script type="text/javascript">

    var direct_costs_percentage=<?php echo $direct_costs_percentage; ?>;
    var packing_cost=<?php echo $packing_cost; ?>;
    var currencies=JSON.parse('<?php echo json_encode($currency_rates);?>');
    function calculate_total(principal_id)
    {
        var principal_id = principal_id;
        var total_quantity=0;
        var total_quantity_confirmed=0;
        var cogs=0;
        var main_total_cogs=0;
        $("#quantity_confirmed_"+principal_id).html(0.000);
        $("#total_quantity_confirmed").html(0.000);
        $("#cogs_"+principal_id).html(0.00);
        $("#total_cogs_"+principal_id).html(0.00);
        $('#variety_total_cogs').html(0.00);

        $(".quantity_month_"+principal_id).each( function( index, element )
        {
            var month_quantity=parseFloat($(this).val());
            if(month_quantity>0)
            {
                total_quantity+=month_quantity;
            }
        });
        if(total_quantity>0)
        {
            $("#quantity_confirmed_"+principal_id).html(number_format(total_quantity,3,'.',''));
            $('.sub_total_quantity').each(function(index,element)
            {
                var st_quantity = Number($(element).text().replace(/[^0-9\.-]+/g,""));
                if(st_quantity==0)
                {
                    st_quantity=0;
                }
                total_quantity_confirmed+=parseFloat(st_quantity);
            });
            $('#total_quantity_confirmed').html(number_format(total_quantity_confirmed,3,'.',','));
        }

        var price=parseFloat($("#price_"+principal_id).val());
        var currency_id=$("#currency_id_"+principal_id).val();
        if(price>0)
        {
            var unit_price=price*currencies[currency_id];
            var cogs=unit_price+unit_price*direct_costs_percentage+packing_cost;
            $("#cogs_"+principal_id).html(number_format(cogs,2));
            if(total_quantity>0)
            {
                $("#total_cogs_"+principal_id).html(number_format(cogs*total_quantity,2));
            }
        }

        $('.sub_total_cogs').each(function(index,element)
        {
            var scogs = Number($(element).text().replace(/[^0-9\.-]+/g,""));
            if(scogs==0)
            {
                scogs=0;
            }
            main_total_cogs+=parseFloat(scogs);
        });
        $('#variety_total_cogs').html(number_format(main_total_cogs,2,'.',','));
    }

    jQuery(document).ready(function()
    {
        $(document).off("change", ".price");
        $(document).off("change", ".currency_id");
        $(document).off("change", ".months");
        $(document).on("change",".months",function(){
            var principal_id = $(this).attr("data-principal-id");
            calculate_total(principal_id);
        });
        $(document).on("change",".price",function(){
            var principal_id = $(this).attr("data-principal-id");
            calculate_total(principal_id);
        });
        $(document).on("change",".currency_id",function(){
            var principal_id = $(this).attr("data-principal-id");
            calculate_total(principal_id);
        });
    });

</script>
