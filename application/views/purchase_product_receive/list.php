<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI=& get_instance();
$action_buttons=array();
if(isset($CI->permissions['action0']) && ($CI->permissions['action0']==1))
{
    $action_buttons[]=array(
        'type'=>'button',
        'label'=>$CI->lang->line('ACTION_EDIT'),
        'class'=>'button_jqx_action',
        'data-action-link'=>site_url($CI->controller_url.'/index/edit')
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
    'href'=>site_url($CI->controller_url.'/index/list')
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
    /*if(isset($CI->permissions['column_headers'])&&($CI->permissions['column_headers']==1))
    {
        ?>
        <div class="col-xs-12" style="margin-bottom: 20px;">
            <div class="col-xs-12" style="margin-bottom: 20px;">
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="fiscal_year_name"><?php echo $CI->lang->line('LABEL_FISCAL_YEAR'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="principal_name"><?php echo $CI->lang->line('LABEL_PRINCIPAL_NAME'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="name"><?php echo $CI->lang->line('LABEL_CONSIGNMENT_NAME'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="month"><?php echo $CI->lang->line('LABEL_MONTH_PURCHASE'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="date_purchase"><?php echo $CI->lang->line('LABEL_DATE_PURCHASE'); ?></label>
            </div>
        </div>
    <?php
    }*/
    ?>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        system_preset({controller:'<?php echo $CI->router->class; ?>'});
        var url = "<?php echo site_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'fiscal_year_name', type: 'string' },
                { name: 'month_name', type: 'string' },
                { name: 'date_opening', type: 'string' },
                { name: 'principal_name', type: 'string' },
                { name: 'consignment_name', type: 'string' },
                { name: 'lc_number', type: 'string' },
                { name: 'date_expected', type: 'string' },
                { name: 'status', type: 'string' },
                { name: 'status_received', type: 'string' }
            ],
            id: 'id',
            url: url
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
                pagesize:20,
                pagesizeoptions: ['20', '50', '100', '200','300','500'],
                selectionmode: 'singlerow',
                altrows: true,
                autoheight: true,
                autorowheight: true,
                columnsreorder: true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_FISCAL_YEAR'); ?>', dataField: 'fiscal_year_name',filtertype: 'list'},
                    { text: '<?php echo $CI->lang->line('LABEL_LC_NUMBER'); ?>', dataField: 'lc_number'},
                    { text: '<?php echo $CI->lang->line('LABEL_CONSIGNMENT_NAME'); ?>', dataField: 'consignment_name'},
                    { text: '<?php echo $CI->lang->line('LABEL_PRINCIPAL_NAME'); ?>', dataField: 'principal_name',filtertype: 'list'},
                    { text: '<?php echo $CI->lang->line('LABEL_MONTH'); ?>', dataField: 'month_name',filtertype: 'list'},
                    { text: '<?php echo $CI->lang->line('LABEL_DATE_OPENING'); ?>', dataField: 'date_opening'},
                    { text: '<?php echo $CI->lang->line('LABEL_DATE_EXPECTED'); ?>', dataField: 'date_expected'},
                    { text: '<?php echo $CI->lang->line('STATUS'); ?>', dataField: 'status',cellsalign: 'right',filtertype: 'list'},
                    { text: 'Receive Status', dataField: 'status_received',cellsalign: 'right',filtertype: 'list'}
                ]
            });
    });
</script>
