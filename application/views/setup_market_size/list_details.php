<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
$action_buttons[]=array(
    'label'=>$CI->lang->line("ACTION_BACK"),
    'href'=>site_url($CI->controller_url.'/index/list')

);
if(isset($CI->permissions['action2']) && ($CI->permissions['action2']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line('ACTION_EDIT'),
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/edit/'.$options['crop_type_id'])
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
    'label'=>$CI->lang->line("ACTION_REFRESH"),
    'href'=>site_url($CI->controller_url.'/index/details/'.$options['crop_type_id'])

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
    <?php
    if(isset($CI->permissions['action6']) && ($CI->permissions['action6']==1))
    {

        ?>
        <div class="col-xs-12" style="margin-bottom: 20px;">
            <div class="col-xs-12" style="margin-bottom: 20px;">
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  value="id"><?php echo $CI->lang->line('ID'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="outlet_name">Outlet</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="date_created">Last Survey</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="size_total">Total Size</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="size_arm">Arm Size</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="size_competitor">Competitor Size</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="remarks"><?php echo $CI->lang->line('LABEL_REMARKS'); ?></label>
            </div>
        </div>
    <?php
    }
    ?>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        var url = "<?php echo site_url($CI->controller_url.'/index/get_details_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'outlet_name', type: 'string' },
                { name: 'date_created', type: 'string' },
                { name: 'size_total', type: 'string' },
                { name: 'size_arm', type: 'string' },
                { name: 'size_competitor', type: 'string' },
                { name: 'remarks', type: 'string' }
            ],
            id: 'id',
            url: url,
            type: 'POST',
            data:JSON.parse('<?php echo json_encode($options);?>')
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,
                pageable: true,
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                pagesize:50,
                pagesizeoptions: ['20', '50', '100', '200','300','500'],
                selectionmode: 'singlerow',
                columnsreorder: true,
                enablebrowserselection: true,
                altrows: true,
                autoheight: true,
                columns: [
                    { text: '<?php echo $CI->lang->line('ID'); ?>', dataField: 'id',hidden:true, width:'40',cellsalign: 'right'},
                    { text: 'Outlet', dataField: 'outlet_name'},
                    { text: 'Last Survey', dataField: 'date_created',width:'200'},
                    { text: 'Total Size', dataField: 'size_total',cellsalign: 'right',width:'150'},
                    { text: 'Arm Size', dataField: 'size_arm',cellsalign: 'right',width:'150'},
                    { text: 'Competitor Size', dataField: 'size_competitor',cellsalign: 'right',width:'150'},
                    { text: '<?php echo $CI->lang->line('LABEL_REMARKS'); ?>', dataField: 'remarks'}
                ]
            });
    });
</script>
