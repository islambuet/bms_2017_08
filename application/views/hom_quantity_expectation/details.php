<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
if((isset($CI->permissions['action1']) && ($CI->permissions['action1']==1))||(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))||(isset($CI->permissions['action3']) && ($CI->permissions['action3']==1)))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line("ACTION_FORWARD"),
        'id'=>'button_action_forward'
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
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url.'/index/search')

);
$CI->load->view('action_buttons',array('action_buttons'=>$action_buttons));
?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function ()
    {
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

        var url = "<?php echo site_url($CI->controller_url.'/index/get_detail_items');?>";

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
                { name: 'quantity_expected', type: 'string' }
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
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',pinned:true, dataField: 'sl_no',width:'50',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center'},
                    { text: 'HOM BUD',dataField: 'quantity_budget',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { text: 'Warehouse Stock',dataField: 'stock_warehouse',width:'130',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { text: 'Outlet Stock',dataField: 'stock_outlet',width:'130',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { text: 'Total Stock',dataField: 'stock_total',width:'130',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { text: 'Minimum Stock',dataField: 'stock_minimum',width:'130',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { text: 'Quantity Expectation',dataField: 'quantity_expected',width:'170',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'}
                ]
            });
    });
</script>
