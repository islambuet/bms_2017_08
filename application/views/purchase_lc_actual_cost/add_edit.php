<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
if(isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE"),
        'id'=>'button_action_save',
        'data-form'=>'#save_form'
    );
}
if(isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_CLEAR"),
        'id'=>'button_action_clear',
        'data-form'=>'#save_form'
    );
}
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
echo '<pre>';
print_r($item);
print_r($items_variety_cost);
print_r($items_direct_cost);
echo '</pre>';
?>
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['id']; ?>" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_FISCAL_YEAR');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['fiscal_year_name']; ?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_MONTH');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $CI->lang->line('LABEL_MONTH_'.$item['month_id']); ?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_OPENING');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::display_date($item['date_opening']); ?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_PRINCIPAL_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['principal_name']; ?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CONSIGNMENT_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['consignment_name']; ?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_LC_NUMBER');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['lc_number']; ?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CURRENCY_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['currency_name']; ?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CURRENCY_RATE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['amount_currency_rate']; ?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_EXPECTED');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::display_date($item['date_expected']); ?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_RECEIVED_STATUS');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label">
                    <?php
                        if($item['status_received']==$this->config->item('system_status_yes'))
                        {
                            echo 'Received';
                        }
                        else
                        {
                            echo 'Pending';
                        }
                    ?>
                </label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CLOSED_STATUS');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label">
                    <?php
                        if($item['status_closed']==$this->config->item('system_status_yes'))
                        {
                            echo 'Closed';
                        }
                        else
                        {
                            echo 'Open';
                        }
                    ?>
                </label>
            </div>
        </div>
        
        <div class="row show-grid">
            <div class="widget-header">
                <div class="title">
                   Set Actual Cost to Direct Cost Items (in Tk.)
                </div>
                <div class="clearfix"></div>
            </div>
            <?php
                foreach ($items_direct_cost as $direct_cost)
                {
                    ?>
                    <div class="row show-grid">
                        <div class="col-xs-4">
                            <label for="direct_cost_<?php echo $direct_cost['id']; ?>" class="control-label pull-right"><?php echo $direct_cost['name']; ?><span style="color:#FF0000">*</span></label>
                        </div>
                        <div class="col-sm-4 col-xs-8">
                            <input type="text" id="direct_cost_<?php echo $direct_cost['id']; ?>" name="direct_cost[<?php echo $direct_cost['id']; ?>]" class="form-control" value="<?php echo $direct_cost['amount_cost']; ?>">
                        </div>
                    </div>
                    <?php
                }
            ?>  
        </div>

        <div class="row show-grid">
            <div class="widget-header">
                <div class="title">
                   Set Actual Cost to Varieties
                </div>
                <div class="clearfix"></div>
            </div>
            <div style="overflow-x: auto;" class="row show-grid">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="min-width: 150px;"><?php echo $CI->lang->line('LABEL_VARIETY'); ?></th>
                            <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?></th>
                            <th style="min-width: 100px;">Ordered Quantity</th>
                            <th style="min-width: 100px;">Received Quantity</th>
                            <th style="min-width: 150px;">Total Price (Tk.)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        foreach($items_variety_cost as $index=>$variety_cost)
                        {
                            ?>
                            <tr>
                                <td>
                                    <?php
                                    if(!(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
                                    {
                                        ?>
                                        <label><?php echo $varieties[$value['variety_id']]['text']; ?></label>
                                        <input type="hidden" name="varieties[<?php echo $index+1;?>][variety_id]" value="<?php echo $value['variety_id']; ?>">
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <select name="varieties[<?php echo $index+1;?>][variety_id]" class="form-control variety">
                                            <option value=""><?php echo $CI->lang->line('SELECT'); ?></option>
                                            <?php
                                                foreach($varieties as $variety)
                                                {
                                                    ?>
                                                    <option value="<?php echo $variety['value']; ?>"<?php if($variety['value']==$value['variety_id']){echo ' selected';} ?>><?php echo $variety['text']; ?></option>
                                                    <?php
                                                }
                                            ?>
                                        </select>
                                        <?php
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if(!(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
                                    {
                                        ?>
                                        <label><?php if($value['quantity_type_id']==0){echo 'Bulk';}else{echo $packs[$value['quantity_type_id']]['text'];} ?></label>
                                        <input type="hidden" name="varieties[<?php echo $index+1;?>][quantity_type_id]" value="<?php echo $value['quantity_type_id']; ?>">
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <select name="varieties[<?php echo $index+1;?>][quantity_type_id]" class="form-control quantity_type">
                                            <option value="-1"><?php echo $this->lang->line('SELECT'); ?></option>
                                            <option value="0"<?php if($value['quantity_type_id']==0){echo 'selected';} ?>>Bulk</option>
                                            <?php
                                                foreach($packs as $pack)
                                                {
                                                    ?>
                                                    <option value="<?php echo $pack['value']?>"<?php if($value['quantity_type_id']==$pack['value']){echo 'selected';} ?>><?php echo $pack['text'];?></option>
                                                    <?php
                                                }
                                            ?>
                                        </select>
                                        <?php
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if(!(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
                                    {
                                        ?>
                                        <label><?php if($value['quantity_type_id']==0){echo number_format($value['quantity_order'],3);}else{echo $value['quantity_order'];}  ?></label>
                                        <input type="hidden" value="<?php echo $value['quantity_order']; ?>" name="varieties[<?php echo $index+1;?>][quantity_order]">
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <input type="text" value="<?php echo $value['quantity_order']; ?>" class="form-control float_type_positive quantity" id="quantity_id_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>" name="varieties[<?php echo $index+1;?>][quantity_order]">
                                        <?php
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if(!(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1)))
                                    {
                                        ?>
                                        <label><?php echo $value['amount_price_order']; ?></label>
                                        <input type="hidden" value="<?php echo $value['amount_price_order']; ?>" name="varieties[<?php echo $index+1;?>][amount_price_order]">
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <input type="text" value="<?php echo $value['amount_price_order']; ?>" class="form-control float_type_positive price" id="price_id_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>" name="varieties[<?php echo $index+1;?>][amount_price_order]">
                                        <?php
                                    }
                                    ?>
                                </td>
                                <td class="text-right">
                                    <label class="control-label total_price" id="total_price_id_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>"><?php echo number_format($value['amount_price_total_order'],2); ?></label>
                                </td>
                                <td>
                                    <?php
                                        if(isset($CI->permissions['action3']) && ($CI->permissions['action3']==1))
                                        {
                                            ?>
                                            <button class="btn btn-danger system_button_add_delete" type="button"><?php echo $CI->lang->line('DELETE'); ?></button>
                                            <?php
                                        }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</form>

<script type="text/javascript">
    jQuery(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
    });
</script>
