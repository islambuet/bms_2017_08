<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_product_receive extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Purchase_product_receive');
        $this->controller_url='purchase_product_receive';
    }

    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="get_items")
        {
            $this->system_get_items();
        }
        elseif($action=="edit")
        {
            $this->system_edit($id);
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        else
        {
            $this->system_list();
        }
    }

    private function system_list()
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            $data['title']="LC List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items()
    {
        $this->db->from($this->config->item('table_bms_purchase_lc').' lc');
        $this->db->select('lc.*');
        $this->db->select('fy.name fiscal_year_name');
        $this->db->select('principal.name principal_name');
        $this->db->join($this->config->item('table_login_basic_setup_fiscal_year').' fy','fy.id = lc.year_id','INNER');
        $this->db->join($this->config->item('table_login_basic_setup_principal').' principal','principal.id = lc.principal_id','INNER');
        $this->db->order_by('lc.year_id','DESC');
        $this->db->order_by('lc.id','DESC');
        $results=$this->db->get()->result_array();
        $items=array();
        foreach($results as $result)
        {
            $item=array();
            $item['id']=$result['id'];
            $item['fiscal_year_name']=$result['fiscal_year_name'];
            $item['principal_name']=$result['principal_name'];
            $item['lc_number']=$result['lc_number'];
            $item['status']=$result['status'];
            $item['status_received']=$result['status_received'];
            $item['consignment_name']=$result['consignment_name'];
            $item['month_name']=$this->lang->line("LABEL_MONTH_$result[month_id]");
            $item['date_opening']=System_helper::display_date($result['date_opening']);
            $item['date_expected']=System_helper::display_date($result['date_expected']);
            $items[]=$item;
        }
        $this->json_return($items);
    }
    private function system_edit($id)
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }

            $this->db->from($this->config->item('table_bms_purchase_lc').' lc');
            $this->db->select('lc.*');
            $this->db->select('fy.name fiscal_year_name');
            $this->db->select('principal.name principal_name');
            $this->db->select('currency.name currency_name');
            $this->db->join($this->config->item('table_login_basic_setup_fiscal_year').' fy','fy.id = lc.year_id','INNER');
            $this->db->join($this->config->item('table_login_basic_setup_principal').' principal','principal.id = lc.principal_id','INNER');
            $this->db->join($this->config->item('table_bms_setup_currency').' currency','currency.id = lc.currency_id','INNER');
            $this->db->where('lc.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            if(!$data['item'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try';
                $this->json_return($ajax);
            }
            if($data['item']['status_received']==$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']='Already product received, you can not edit this LC';
                $this->json_return($ajax);
            }

            $results=Query_helper::get_info($this->config->item('table_bms_purchase_lc_details'),'*',array('lc_id='.$item_id,'revision=1'));
            $data['items']=array();
            foreach($results as $result)
            {
                $data['items'][$result['id']]=$result;
            }

            $results=Query_helper::get_info($this->config->item('table_login_setup_classification_vpack_size'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id ASC'));
            $data['packs']=array();
            foreach($results as $result)
            {
                $data['packs'][$result['value']]=$result;
            }

            $this->db->from($this->config->item('table_login_setup_classification_varieties').' v');
            $this->db->select('v.id value,v.name');
            $this->db->select('vp.name_import text');
            $this->db->join($this->config->item('table_login_setup_variety_principals').' vp','vp.variety_id = v.id AND vp.principal_id = '.$data['item']['principal_id'].' AND vp.revision = 1','INNER');
            $this->db->where('v.status',$this->config->item('system_status_active'));
            $this->db->where('v.whose','ARM');
            $this->db->order_by('v.ordering ASC');
            $results=$this->db->get()->result_array();
            $data['varieties']=array();
            foreach($results as $result)
            {
                $data['varieties'][$result['value']]=$result;
            }

            $data['title']="Edit LC (".$data['item']['lc_number'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save()
    {
        $id = $this->input->post("id");
        if(!(isset($this->permissions['action1'])&&($this->permissions['action1']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        else
        {
            $status=$this->input->post('item');
            $varieties=$this->input->post('varieties');
            $this->db->trans_start();  //DB Transaction Handle START
            if($varieties)
            {
                if($status['status_received']==$this->config->item('system_status_yes'))
                {
                    Query_helper::update($this->config->item('table_bms_purchase_lc'),$status,array('id='.$id));
                }
                foreach($varieties as $index=>$v_data)
                {
                    if($v_data['quantity_actual'])
                    {
                        Query_helper::update($this->config->item('table_bms_purchase_lc_details'),$v_data,array('id='.$index));
                    }
                }
            }
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_list();
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }
}