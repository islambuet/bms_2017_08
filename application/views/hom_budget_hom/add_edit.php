<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
$action_buttons=array();
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))||(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))||(isset($CI->permissions['action3']) && ($CI->permissions['action3']==1)))
{
    if($budget_info['status_budget']=='Forwarded')
    {
        if((isset($CI->permissions['action3']) && ($CI->permissions['action3']==1)))
        {
            $action_buttons[]=array(
                'type'=>'button',
                'label'=>$CI->lang->line("ACTION_SAVE"),
                'id'=>'button_action_save_jqx'
            );
        }
    }
    else
    {
        $action_buttons[]=array(
            'type'=>'button',
            'label'=>$CI->lang->line("ACTION_SAVE"),
            'id'=>'button_action_save_jqx'
        );
        if($budget_info['status_budget']=='Not Forwarded')
        {
            $action_buttons[]=array(
                'type'=>'button',
                'label'=>$CI->lang->line("ACTION_FORWARD"),
                'id'=>'button_action_forward'
            );
        }

    }
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
<form class="form_valid" id="save_form_jqx" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="year_id" value="<?php echo $options['year_id']; ?>" />
    <input type="hidden" name="crop_type_id" value="<?php echo $options['crop_type_id']; ?>" />

    <div id="jqx_inputs">
    </div>
</form>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php
    if($market_survey['num_outlet']>0)
    {
        ?>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Total Market Size</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo number_format($market_survey['size_total'],3,'.','');?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Arm Market Size</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo number_format($market_survey['size_arm'],3,'.','');?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Competitor Market Size</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo number_format($market_survey['size_total']-$market_survey['size_arm'],3,'.','');?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">#outlet Survey Done</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $market_survey['num_outlet'];?></label>
            </div>
        </div>
    <?php
    }
    else
    {
        ?>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Market Survey</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label">Not Done Yet</label>
            </div>
        </div>
    <?php
    }
    ?>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Budget Status</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $budget_info['status_budget'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Last Budgeted By</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $budget_info['user_budget'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Last Budgeted time</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $budget_info['date_budget'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Budget Forwarded By</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $budget_info['user_forward'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Forwarded time</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $budget_info['date_forward'];?></label>
        </div>
    </div>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function ()
    {
        $(document).off('click', '#button_action_save_jqx');
        $(document).on("click", "#button_action_save_jqx", function(event)
        {
            $('#save_form_jqx #jqx_inputs').html('');
            var data=$('#system_jqx_container').jqxGrid('getrows');
            for(var i=0;i<data.length;i++)
            {
                <?php
                for($i=0;$i<=sizeof($years_next);$i++)
                {
                    ?>
                    $('#save_form_jqx  #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+'][<?php echo $i; ?>]" value="'+data[i]['year<?php echo $i; ?>_budget_quantity']+'">');
                    <?php
                }
                ?>
            }
            var sure = confirm('<?php echo $CI->lang->line('MSG_CONFIRM_SAVE'); ?>');
            if(sure)
            {
                $("#save_form_jqx").submit();
            }

        });
        $(document).off('click', '#button_action_forward');
        $(document).on("click", "#button_action_forward", function(event)
        {
            var sure = confirm('Are Your Sure to Forward?');
            if(sure)
            {
                $.ajax({
                    url: '<?php echo site_url($CI->controller_url.'/index/forward');?>',
                    type: 'POST',
                    datatype: "JSON",
                    data:{year_id:'<?php echo $options['year_id'];?>',crop_type_id:'<?php echo $options['crop_type_id'];?>'},
                    success: function (data, status)
                    {

                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");

                    }
                });
            }

        });

        var url = "<?php echo site_url($CI->controller_url.'/index/get_edit_items');?>";
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'variety_id', type: 'string' },
                { name: 'variety_name', type: 'string' },
                <?php
                    foreach($areas as $area)
                    {?>{ name: '<?php echo 'area_quantity_'.$area['value'];?>', type: 'text' },
                        <?php
                    }
                ?>
                <?php
                    for($i=0;$i<sizeof($years_previous);$i++)
                    {
                        ?>{ name: '<?php echo 'year'.($i+1).'_sell_quantity';?>', type: 'string' },
                        <?php
                    }
                ?>
                <?php
                    for($i=0;$i<=sizeof($years_next);$i++)
                    {
                        ?>{ name: '<?php echo 'year'.$i.'_area_quantity';?>', type: 'string' },
                        { name: '<?php echo 'year'.$i.'_budget_quantity';?>', type: 'string' },
                        { name: '<?php echo 'year'.$i.'_budget_quantity_editable';?>', type: 'string' },
                        <?php
                    }
                ?>
                { name: '<?php echo 'year0_previous_quantity';?>', type: 'string' },
                { name: '<?php echo 'year0_previous_prediction';?>', type: 'string' },
                { name: 'sl_no', type: 'int' }
            ],
            id: 'id',
            url: url,
            type: 'POST',
            data:JSON.parse('<?php echo json_encode($options);?>')
        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            if(record[column+'_editable'])
            {
                element.html('<div class="jqxgrid_input">'+value+'</div>');
            }

            return element[0].outerHTML;

        };
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height:'300',
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                altrows: true,
                rowsheight: 35,
                editable:true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',pinned:true, dataField: 'sl_no',width:'50',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center',editable:false},
                    <?php
                        foreach($areas as $area)
                        {?>{ columngroup: 'areas',text: '<?php echo $area['text'];?>', dataField: '<?php echo 'area_quantity_'.$area['value'];?>',align:'center',width:'100',cellsrenderer: cellsrenderer,cellsAlign:'right',editable:false},
                            <?php
                        }
                    ?>
                    <?php
                        for($i=0;$i<sizeof($years_previous);$i++)
                        {?>{columngroup: 'previous_years',text: '<?php echo $years_previous[$i]['text']; ?>', dataField: '<?php echo 'year'.($i+1).'_sell_quantity';?>',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                            <?php
                        }
                    ?>
                    { columngroup: 'year0',text: 'Prev.Budget',dataField: 'year0_previous_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { columngroup: 'year0',text: 'Prev.Prediction',dataField: 'year0_previous_prediction',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                        <?php
                            for($i=0;$i<=sizeof($years_next);$i++)
                            {?>{ columngroup: '<?php echo 'year'.($i); ?>',text: 'Divisions', dataField: '<?php echo 'year'.($i).'_area_quantity';?>',align:'center',width:'80',cellsrenderer: cellsrenderer,cellsAlign:'right',editable:false},
                        { columngroup: '<?php echo 'year'.($i); ?>',text: 'HOM', dataField: '<?php echo 'year'.($i).'_budget_quantity';?>',align:'center',width:'100',cellsrenderer: cellsrenderer,cellsAlign:'right',columntype:'custom',
                        cellbeginedit: function (row)
                        {
                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);//only last selected
                            return selectedRowData['<?php echo 'year'.($i).'_budget_quantity_editable';?>'];
                        },
                        initeditor: function (row, cellvalue, editor, celltext, pressedkey) {
                            editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input float_type_positive"><div>');
                        },
                        geteditorvalue: function (row, cellvalue, editor) {
                            // return the editor's value.
                            var value=editor.find('input').val();
                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);
                            return editor.find('input').val();
                        }
                    },
                    <?php
                        }
                    ?>
                ],
                columngroups:
                    [
                        { text: '<?php echo $CI->lang->line('LABEL_PREVIOUS_YEARS'); ?> Achieved', align: 'center', name: 'previous_years' },
                        { text: '<?php echo $CI->lang->line('LABEL_BUDGETED_YEAR').'('.$year_current['text'].')'; ?>', align: 'center', name: 'year0' },
                        { text: '<?php echo $CI->lang->line('LABEL_NEXT_YEARS'); ?> Prediction', align: 'center', name: 'next_years' },
                        <?php
                            for($i=0;$i<sizeof($years_next);$i++)
                            {?>{ text: '<?php echo $years_next[$i]['text']; ?>', align: 'center',parentgroup:'next_years', name: '<?php echo 'year'.($i+1); ?>' },
                                <?php
                            }
                        ?>
                        { text: 'Divisions', align: 'center', name: 'areas' }

                    ]
            });

    });
</script>