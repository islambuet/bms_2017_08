<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url)
);
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
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save_coworker');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $user['id']; ?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div style="" class="row show-grid">
            <div style="" class="row show-grid">
                <div class="col-xs-12">
                    <?php
                    foreach($office_staffs as $offices_staff)
                    {
                        if(is_array($offices_staff))
                        {
                            ?>
                            <div class="checkbox">
                                <?php
                                $dept_name=false;
                                foreach($offices_staff as $office_staff)
                                {
                                    if(!$dept_name)
                                    {
                                        echo '<b>'.$office_staff['department_name'].'</b>';
                                    }
                                    $dept_name=true;
                                }
                                ?>
                            </div>

                            <?php foreach($offices_staff as $office_staff){?>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="coworkers[]" value="<?php echo $office_staff['id']; ?>" <?php if(in_array($office_staff['id'],$assigned_coworker)){echo 'checked';} ?>><?php echo $office_staff['name'].' - '.$office_staff['designation_name']; ?>
                                </label>
                            </div>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</form>
