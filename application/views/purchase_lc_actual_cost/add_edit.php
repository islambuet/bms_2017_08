<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
$show_save_button=false;
if($item['revision_received']>0)
{
    if(isset($CI->permissions['action3']) && ($CI->permissions['action3']==1))
    {
        $show_save_button=true;
    }
    elseif(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1) && $item['revision_cost']>=0 && $item['status_closed']==$CI->config->item('system_status_no'))
    {
        $show_save_button=true;
    }
    elseif(isset($CI->permissions['action1']) && ($CI->permissions['action1']==1) && $item['revision_cost']==0 && $item['status_closed']==$CI->config->item('system_status_no'))
    {
        $show_save_button=true;
    }
}
if($show_save_button)
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_SAVE"),
        'id'=>'button_action_save',
        'data-form'=>'#save_form'
    );
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_CLEAR"),
        'id'=>'button_action_clear',
        'data-form'=>'#save_form'
    );
}
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
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
                            <?php
                            if($show_save_button)
                            {
                                ?>
                                <input type="text" id="direct_cost_<?php echo $direct_cost['id']; ?>" name="direct_cost[<?php echo $direct_cost['id']; ?>]" class="form-control float_type_positive" value="<?php echo $direct_cost['amount_cost']; ?>">
                                <?php
                            }
                            else
                            {
                                ?>
                                <label>N/A</label>
                                <?php
                            }
                            ?>
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
                                    <label><?php echo $variety_cost['variety_name']; ?></label>
                                </td>
                                <td>
                                    <?php
                                    if($variety_cost['quantity_type_id']>0)
                                    {
                                        ?>
                                        <label><?php echo $variety_cost['pack_name']; ?></label>
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <label>Bulk</label>
                                        <?php
                                    }
                                    ?>
                                </td>
                                <td>
                                    <label><?php echo $variety_cost['quantity_order']; ?></label>
                                </td>
                                <td>
                                    <?php
                                    if($item['revision_received']==0)
                                    {
                                        ?>
                                        <label>Not Received</label>
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <label><?php echo $variety_cost['quantity_actual']; ?></label>
                                        <?php
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if($show_save_button)
                                    {
                                        ?>
                                        <input type="text" class="form-control float_type_positive" name="items[<?php echo $index; ?>][amount_price_total_actual]" value="<?php echo $variety_cost['amount_price_total_actual']; ?>">
                                        <input type="hidden" name="items[<?php echo $index; ?>][variety_id]" value="<?php echo $variety_cost['variety_id']; ?>">
                                        <input type="hidden" name="items[<?php echo $index; ?>][quantity_type_id]" value="<?php echo $variety_cost['quantity_type_id']; ?>">
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <label>N/A</label>
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
        <?php
        if($show_save_button)
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right">Closed Status</label>
                </div>
                <div class="col-sm-2 col-xs-8">
                    <select id="status_closed" name="item[status_closed]" class="form-control">
                        <option value="<?php echo $CI->config->item('system_status_no'); ?>"
                            <?php
                            if ($item['status_closed']==$CI->config->item('system_status_no'))
                            {
                                echo ' selected';
                            }
                            ?>>Open
                        </option>
                        <option value="<?php echo $CI->config->item('system_status_yes'); ?>"
                            <?php
                            if ($item['status_closed']==$CI->config->item('system_status_yes'))
                            {
                                echo ' selected';
                            }
                            ?>>Closed
                        </option>
                    </select>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="clearfix"></div>
</form>

<script type="text/javascript">
    jQuery(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
    });
</script>
