<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();

?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <form id="search_form" action="<?php echo site_url($CI->controller_url.'/index/edit');?>" method="post">
        <div class="row show-grid">
            <div class="col-xs-6">
                <div class="row show-grid">
                    <div class="col-xs-6">
                        <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_FISCAL_YEAR');?><span style="color:#FF0000">*</span></label>
                    </div>
                    <div class="col-xs-6">
                        <select id="year_id" name="report[year_id]" class="form-control">
                            <option value=""><?php echo $this->lang->line('SELECT');?></option>
                            <?php
                            foreach($fiscal_years as $year)
                            {?>
                                <option value="<?php echo $year['value']?>"><?php echo $year['text'];?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div style="" class="row show-grid" id="crop_id_container">
                    <div class="col-xs-6">
                        <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CROP_NAME');?><span style="color:#FF0000">*</span></label>
                    </div>
                    <div class="col-xs-6">
                        <select id="crop_id" name="report[crop_id]" class="form-control">
                            <option value=""><?php echo $this->lang->line('SELECT');?></option>
                        </select>
                    </div>
                </div>
                <div style="display: none;" class="row show-grid" id="crop_type_id_container">
                    <div class="col-xs-6">
                        <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CROP_TYPE');?><span style="color:#FF0000">*</span></label>
                    </div>
                    <div class="col-xs-6">
                        <select id="crop_type_id" name="report[crop_type_id]" class="form-control">
                            <option value=""><?php echo $this->lang->line('SELECT');?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
                <div style="" class="row show-grid">
                    <div class="col-xs-6">
                        <?php
                        if($CI->locations['division_id']>0)
                        {
                            ?>
                            <label class="control-label"><?php echo $CI->locations['division_name'];?></label>
                            <input type="hidden" name="report[division_id]" value="<?php echo $CI->locations['division_id'];?>">
                        <?php
                        }
                        else
                        {
                            ?>
                            <select id="division_id" name="report[division_id]" class="form-control">
                                <option value=""><?php echo $this->lang->line('SELECT');?></option>
                                <?php
                                foreach($divisions as $division)
                                {?>
                                    <option value="<?php echo $division['value']?>"><?php echo $division['text'];?></option>
                                <?php
                                }
                                ?>
                            </select>
                        <?php
                        }
                        ?>
                    </div>
                    <div class="col-xs-6">
                        <label class="control-label"><?php echo $CI->lang->line('LABEL_DIVISION_NAME');?><span style="color:#FF0000">*</span></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-xs-4">
                <div class="action_button pull-right">
                    <button id="button_action_report" type="button" class="btn" data-form="#search_form">Load Form</button>
                </div>

            </div>
            <div class="col-xs-4">

            </div>
        </div>
    </form>
</div>
<div class="clearfix"></div>


<div id="system_report_container">

</div>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        $(document).off('change', '#year_id');
        $('#crop_id').html(get_dropdown_with_select(system_crops));
        $(document).off('change', '#crop_id');
        $(document).on('change','#crop_id',function()
        {
            $('#system_report_container').html('');
            $('#crop_type_id').val('');
            $('#crop_type_id_container').hide();
            var crop_id=$('#crop_id').val();
            if(crop_id>0)
            {
                if(system_types[crop_id]!==undefined)
                {
                    $('#crop_type_id_container').show();
                    $('#crop_type_id').html(get_dropdown_with_select(system_types[crop_id]));
                }
            }

        });
        $(document).off('change', '#crop_type_id');

    });
</script>
