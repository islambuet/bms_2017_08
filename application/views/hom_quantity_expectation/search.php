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
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_FISCAL_YEAR');?></label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <select id="year0_id" class="form-control">
                <option value=""><?php echo $this->lang->line('SELECT');?></option>
                <?php
                foreach($years as $year)
                {?>
                    <option value="<?php echo $year['value']?>"><?php echo $year['text'];?></option>
                <?php
                }
                ?>
            </select>
        </div>
    </div>
    <div style="" class="row show-grid" id="crop_id_container">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_CROP_NAME');?><span style="color:#FF0000">*</span></label>
        </div>
        <div class="col-xs-4">
            <select id="crop_id" name="report[crop_id]" class="form-control">
                <option value=""><?php echo $this->lang->line('SELECT');?></option>
            </select>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">

        </div>
        <div class="col-xs-4">
            <div class="action_button pull-right">
                <button id="load_form" class="btn">Load Form</button>
            </div>

        </div>
        <div class="col-xs-4">

        </div>
    </div>
</div>
<div id="system_report_container">

</div>

<div class="clearfix"></div>
<script type="text/javascript">
    function load_crop_types()
    {
        var year0_id=$('#year0_id').val();
        var crop_id=$('#crop_id').val();
        if(year0_id>0 && crop_id>0)
        {
            $.ajax({
                url: '<?php echo site_url($CI->controller_url.'/index/list');?>',
                type: 'POST',
                datatype: "JSON",
                data:{
                    year0_id:year0_id,
                    crop_id:crop_id
                },
                success: function (data, status)
                {

                },
                error: function (xhr, desc, err)
                {
                    console.log("error");

                }
            });
        }
        else if(year0_id==0)
        {
            animate_message('Please Select a Fiscal Year');
        }
        else if(crop_id==0)
        {
            animate_message('Please Select a Crop');
        }
    }
    jQuery(document).ready(function()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        $(document).off('click', '#load_form');
        //load_crop_types();
        $(document).off("change","#year0_id");
        $(document).off('change', '#crop_id');
        $('#crop_id').html(get_dropdown_with_select(system_crops));
        $(document).on("change","#crop_id,#year0_id",function()
        {
            $('#system_report_container').html('');
        });
        $(document).on('click','#load_form',function(event)
        {
            load_crop_types();
        });
    });
</script>
