<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))||(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))||(isset($CI->permissions['action3']) && ($CI->permissions['action3']==1)))
{
    if($quantity_expectation_info['status_quantity_expectation']=='Forwarded')
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
        if($quantity_expectation_info['status_quantity_expectation']=='Not Forwarded')
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
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Quantity Expectation Status</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $quantity_expectation_info['status_quantity_expectation'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Last Quantity Expected By</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $quantity_expectation_info['user_quantity_expected'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Last Quantity Expected time</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $quantity_expectation_info['date_quantity_expected'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Quantity Expectation Forwarded By</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $quantity_expectation_info['user_forward_quantity_expectation'];?></label>
        </div>
    </div>
    <div style="" class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right">Forwarded time</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php echo $quantity_expectation_info['date_forward_quantity_expectation'];?></label>
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
            var data=$('#system_jqx_container').jqxGrid('getrows');
            for(var i=0;i<data.length;i++)
            {
                $('#save_form_jqx #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+'][stock_warehouse]" value="'+data[i]['stock_warehouse']+'">');
                $('#save_form_jqx #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+'][stock_outlet]" value="'+data[i]['stock_outlet']+'">');
                $('#save_form_jqx #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+'][stock_minimum]" value="'+data[i]['stock_minimum']+'">');
                $('#save_form_jqx #jqx_inputs').append('<input type="hidden" name="items['+data[i]['variety_id']+'][quantity_expected]" value="'+data[i]['quantity_expected']+'">');
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
//            alert('hi');
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

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'sl_no', type: 'int' },
                { name: 'variety_name', type: 'string' },
                { name: 'variety_id', type: 'string' },
                { name: 'quantity_budget', type: 'string' },
                { name: 'stock_warehouse', type: 'string' },
                { name: 'stock_outlet', type: 'string' },
                { name: 'stock_total', type: 'string' },
                { name: 'stock_minimum', type: 'string' },
                { name: 'quantity_expected', type: 'string' },
                { name: 'quantity_expected_editable', type: 'string' }

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
        // create jqxgrid.
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
                    { text: 'HOM BUD',dataField: 'quantity_budget',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Warehouse Stock',dataField: 'stock_warehouse',width:'130',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Outlet Stock',dataField: 'stock_outlet',width:'130',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Total Stock',dataField: 'stock_total',width:'130',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Minimum Stock',dataField: 'stock_minimum',width:'130',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    {
                        text: 'Quantity Expectation',dataField: 'quantity_expected',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',columntype:'custom',
                        cellbeginedit: function (row)
                        {

                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);
                            return selectedRowData['quantity_expected_editable'];
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
                    }
                ]
            });
    });
</script>