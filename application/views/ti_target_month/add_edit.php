<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
$action_buttons=array();
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))||(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))||(isset($CI->permissions['action3']) && ($CI->permissions['action3']==1)))
{
    if($month_target_info['status_target']=='Forwarded')
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
        if($month_target_info['status_target']=='Not Forwarded')
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
    <input type="hidden" name="territory_id" value="<?php echo $options['territory_id']; ?>" />

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
            <label class="control-label pull-right">Month Target Status</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $month_target_info['status_target'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Last Targeted By</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $month_target_info['user_target'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Last Targeted time</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $month_target_info['date_target'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Month Target Forwarded By</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $month_target_info['user_forward'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Target time</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $month_target_info['date_forward'];?></label>
        </div>
    </div>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function ()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        $(document).off('click', '#button_action_save_jqx');
        $(document).on("click", "#button_action_save_jqx", function(event)
        {
            $('#save_form_jqx #jqx_inputs').html('');
            var data=$('#system_jqx_container').jqxGrid('getrows');
            for(var i=0;i<data.length;i++)
            {
                <?php
                for($i=1;$i<=12;$i++)
                {
                    ?>
                $('#save_form_jqx  #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+'][quantity_target_<?php echo $i; ?>]" value="'+data[i]['month<?php echo $i; ?>_quantity_target']+'">');
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
                    data:{year_id:'<?php echo $options['year_id'];?>',territory_id:'<?php echo $options['territory_id'];?>',crop_type_id:'<?php echo $options['crop_type_id'];?>'},
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
                { name: 'sl_no', type: 'int' },
                { name: 'variety_id', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'ti_quantity_assign_target', type: 'string' },

                <?php
                for($i=1;$i<=12;$i++)
                {
                    foreach($years_previous as $year)
                    {
                        ?>
                        { name: 'year<?php echo $year['value']; ?>_month<?php echo $i; ?>_sell_quantity', type: 'string' },
                        <?php
                    }
                    ?>
                    { name: 'month<?php echo $i; ?>_quantity_target', type: 'string' },
                    { name: 'month<?php echo $i; ?>_quantity_target_editable', type: 'string' },
                    <?php
                }
                ?>
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
                enablebrowserselection: true,
                altrows: true,
                rowsheight: 35,
                editable:true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',pinned:true, dataField: 'sl_no',width:'40',align:'center',cellsAlign:'right',cellsrenderer: cellsrenderer,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY'); ?>',pinned:true, dataField: 'variety_name',width:'150',align:'center',cellsrenderer: cellsrenderer,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_TI_TARGET'); ?>',pinned:true, dataField: 'ti_quantity_assign_target',width:'75',align:'center',cellsrenderer: cellsrenderer,editable:false},
                    <?php
                        for($i=0;$i<12;$i++)
                        {
                            if(($starting_month+$i)>12)
                            {
                                $current_month=$starting_month+$i-12;
                            }
                            else
                            {
                                $current_month=$starting_month+$i;
                            }
                            foreach($years_previous as $year)
                            {
                                ?>
                                { columngroup: 'previous_year_month<?php echo $current_month; ?>',text: '<?php echo $year['text']; ?>', dataField: 'year<?php echo $year['value']; ?>_month<?php echo $current_month; ?>_sell_quantity',width:'65',align:'center',cellsAlign:'right',cellsrenderer: cellsrenderer,editable:false},
                                <?php
                            }
                            ?>
                            { columngroup: 'month<?php echo $current_month; ?>',text: 'Curr. Yr. Target', dataField: 'month<?php echo $current_month; ?>_quantity_target',width:'100',align:'center',cellsAlign:'right',cellsrenderer: cellsrenderer,columntype:'custom',
                                cellbeginedit: function (row) {
                                    var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);//only last selected
                                    return selectedRowData['month<?php echo $current_month; ?>_quantity_target_editable'];
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
                columngroups:[
                    <?php
                    for($i=0;$i<12;$i++)
                    {
                        if(($starting_month+$i)>12)
                        {
                            $current_month=$starting_month+$i-12;
                        }
                        else
                        {
                            $current_month=$starting_month+$i;
                        }
                        $month_name=$this->lang->line('LABEL_MONTH_'.$current_month);
                        ?>
                        { parentgroup: 'month<?php echo $current_month; ?>',text: 'Prev. Yr. Achieved', align: 'center', name: 'previous_year_month<?php echo $current_month; ?>' },
                        { text: '<?php echo $month_name; ?>', align: 'center', name: 'month<?php echo $current_month; ?>' },
                        <?php
                    }
                    ?>
                ]
            });
    });
</script>
