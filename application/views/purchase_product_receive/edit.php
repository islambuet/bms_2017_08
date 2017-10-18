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
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_FISCAL_YEAR');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['fiscal_year_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_MONTH');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo date("F", mktime(0, 0, 0,  $item['month_id'],1, 2000));?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_OPENING');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::display_date($item['date_opening']);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_PRINCIPAL_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['principal_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CONSIGNMENT_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['consignment_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CURRENCY_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['currency_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CURRENCY_RATE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo number_format($item['amount_currency_rate'],2);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_LC_NUMBER');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['lc_number'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_EXPECTED');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::display_date($item['date_expected']);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('STATUS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $item['status'];?></label>
            </div>
        </div>

        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Receive Status</label>
            </div>
            <div class="col-sm-2 col-xs-8">
                <select id="status_received" name="item[status_received]" class="form-control">
                    <option value="<?php echo $CI->config->item('system_status_yes'); ?>"
                        <?php
                        if ($item['status_received'] == $CI->config->item('system_status_yes')) {
                            echo "selected='selected'";
                        }
                        ?> ><?php echo $CI->lang->line('YES') ?>
                    </option>
                    <option value="<?php echo $CI->config->item('system_status_no'); ?>"
                        <?php
                        if ($item['status_received'] == $CI->config->item('system_status_no')) {
                            echo "selected='selected'";
                        }
                        ?> ><?php echo $CI->lang->line('NO') ?></option>
                </select>
            </div>
        </div>

        <div class="row show-grid">
            <div class="widget-header">
                <div class="title">
                    Add Actual Quantity
                </div>
                <div class="clearfix"></div>
            </div>
            <div style="overflow-x: auto;" class="row show-grid">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th style="min-width: 150px;"><?php echo $CI->lang->line('LABEL_VARIETY'); ?></th>
                        <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_PACK_SIZE'); ?></th>
                        <th style="min-width: 100px;"><?php echo $CI->lang->line('LABEL_QUANTITY'); ?></th>
                        <th style="min-width: 100px;">Received Quantity</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($items as $index=>$value)
                    {
                        ?>
                        <tr>
                            <td>
                                <label><?php echo $varieties[$value['variety_id']]['text']; ?></label>
                            </td>
                            <td>
                                <label><?php if($value['quantity_type_id']==0){echo 'Bulk';}else{echo $packs[$value['quantity_type_id']]['text'];} ?></label>
                            </td>
                            <td>
                                <label><?php if($value['quantity_type_id']==0){echo number_format($value['quantity_order'],3).' Kg';}else{echo $value['quantity_order'].'  Pack';}  ?></label>
                            </td>
                            <td>
                                <input type="text" class="form-control float_type_positive" name="varieties[<?php echo $index;?>][quantity_actual]" value="<?php echo $value['quantity_actual']; ?>">
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
</form>