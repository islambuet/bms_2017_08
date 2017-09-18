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
if(isset($CI->permissions['action4']) && ($CI->permissions['action4']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_PRINT"),
        'class'=>'button_action_download',
        'data-title'=>"Print",
        'data-print'=>true
    );
}
if(isset($CI->permissions['action5']) && ($CI->permissions['action5']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_DOWNLOAD"),
        'class'=>'button_action_download',
        'data-title'=>"Download"
    );
}
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
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
    <div class="panel-group" id="accordion">
    <?php
        $count=0;
        foreach($principals as $id=>$principal)
        {
            $count++;
    ?>
            <div class="panel panel-default" id="principal_container_<?php echo $id;?>">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a class="accordion-toggle external" data-toggle="collapse"  data-target="#collapse_<?php echo $id;?>" href="#">
                            <?php echo "Principal No. $count : ".$principal['principal_name'];?>
                        </a>
                    </h4>
                </div>
                <div id="collapse_<?php echo $id;?>" class="panel-collapse collapse">

                    <div style="" class="row show-grid">
                        <div class="col-xs-4">
                            <label class="control-label pull-right">Principal Name</label>
                        </div>
                        <div class="col-sm-4 col-xs-8">
                            <label class="control-label"><?php echo $principal['principal_name'];?></label>
                        </div>
                    </div>
                    <div style="" class="row show-grid">
                        <div class="col-xs-4">
                            <label class="control-label pull-right">Import Name</label>
                        </div>
                        <div class="col-sm-4 col-xs-8">
                            <label class="control-label"><?php echo $principal['name_import'];?></label>
                        </div>
                    </div>
                    <div style="" class="row show-grid">
                        <div class="col-xs-4">
                            <label class="control-label pull-right">Purchase Quantity(kg)</label>
                        </div>
                        <div class="col-sm-4 col-xs-8">
                            <label class="control-label" id="quantity_purchased"><?php if(isset($principal['quantity_total'])){echo $principal['quantity_total'];}else{echo 'Not Assigned';}?></label>
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
                                        <input id="quantity_<?php echo ($i);?>" name="purchase[quantity_<?php echo ($i);?>]" type="text" class="form-control float_type_positive quantity_month" style="float: left;margin-bottom: 5px;" value="<?php if(isset($principal['quantity_'.$i])){echo $principal['quantity_'.$i];}?>">
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
                            <input id="price" name="purchase[unit_price]" type="text" class="form-control float_type_positive" style="float: left;" value="<?php if(isset($principal['unit_price'])){echo $principal['unit_price'];} ?>">
                        </div>
                        <div class="col-xs-2">
                            <select id="currency_id" name="purchase[currency_id]" class="form-control">
                                <?php
                                foreach($currencies as $currency)
                                {?>
                                    <option value="<?php echo $currency['value']?>" <?php if(isset($principal['currency_id'])&&($currency['value']==$principal['currency_id'])){ echo "selected";}?>><?php echo $currency['text'];?></option>
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
                            <label id="cogs" class="control-label"></label>
                        </div>
                    </div>
                    <div style="" class="row show-grid">
                        <div class="col-xs-4">
                            <label class="control-label pull-right">Total COGS</label>
                        </div>
                        <div class="col-sm-4 col-xs-8">
                            <label id="total_cogs" class="control-label"></label>
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
<!--
<script type="text/javascript">
    <?php
        foreach($currencies as $currency)
        {
            $value=0;
            if(isset($currency_rates[$currency['value']]))
            {
                $value=$currency_rates[$currency['value']];
            }
        ?>
    var currency_<?php echo $currency['value'];?>=<?php echo $value;?>;
    <?php
    }
?>
    var direct_costs_percentage=<?php echo $direct_costs_percentage; ?>;
    var packing_cost=<?php echo $packing_cost; ?>;
    function calculate_total()
    {
        var quantity_purchased=0;
        var total_cogs=0;
        var cogs=0;
        $("#quantity_purchased").html("-");
        $("#cogs").html("-");
        $("#total_cogs").html("-");

        $(".quantity_month").each( function( index, element )
        {
            var month_quantity=parseFloat($(this).val());
            if(month_quantity>0)
            {
                quantity_purchased+=month_quantity;
            }
        });
        if(quantity_purchased>0)
        {
            $("#quantity_purchased").html(number_format(quantity_purchased,3,'.',''));
        }
        var price=parseFloat($("#price").val());
        var currency_id=$("#currency_id").val();
        if(price>0)
        {
            var unit_price=price*window['currency_'+currency_id];
            var total_unit_price=unit_price+unit_price*direct_costs_percentage+packing_cost;
            $("#cogs").html(number_format(total_unit_price,2));
            if(quantity_purchased>0)
            {
                $("#total_cogs").html(number_format(total_unit_price*quantity_purchased,2));
            }

        }

    }
    jQuery(document).ready(function()
    {
        $(document).off("change", ".quantity_month");
        $(document).off("change", "#price");
        $(document).off("change", "#currency_id");
        calculate_total();
        $(document).on("change",".quantity_month",function(){
            calculate_total();
        });
        $(document).on("change","#price",function(){
            calculate_total();
        });
        $(document).on("change","#currency_id",function(){
            calculate_total();
        });


    });
</script>
-->