<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mgt_quantity_confirmed extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Mgt_quantity_confirmed');
        $this->controller_url='mgt_quantity_confirmed';
    }
    public function index($action="search",$id=0)
    {
        if($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="edit")
        {
            $this->system_edit();
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        else
        {
            $this->system_search();
        }
    }
    private function system_search()
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['title']="Quantity Confirmed Search";
            $data['fiscal_years']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id ASC'));
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/search",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_edit()
    {
        $reports=$this->input->post('report');
        $data=$reports;
        if(!($reports['year_id']>0))
        {
            $ajax['status']=false;
            $ajax['system_message']='Please Select a Fiscal year';
            $this->json_return($ajax);
        }
        if(!($reports['variety_id']>0))
        {
            $ajax['status']=false;
            $ajax['system_message']='Please Select a variety';
            $this->json_return($ajax);
        }

        $result=Query_helper::get_info($this->config->item('table_bms_hom_budget_hom'),'*',array('year_id ='.$reports['year_id'],'variety_id ='.$reports['variety_id'],'year_index =0'),1);
        if($result)
        {
            $data['hom_budget']=$result['quantity_budget'];
            if(isset($result['stock_warehouse']))
            {
                $data['stock_warehouse']=$result['stock_warehouse'];
            }
            else
            {
                $data['stock_warehouse']='N/A';
            }
            if(isset($result['stock_outlet']))
            {
                $data['stock_outlet']=$result['stock_outlet'];
            }
            else
            {
                $data['stock_outlet']='N/A';
            }
            if(isset($result['stock_warehouse']) && isset($result['stock_outlet']))
            {
                $data['current_stock']=$result['stock_warehouse']+$result['stock_outlet'];
            }
            elseif(isset($result['stock_warehouse']))
            {
                $data['current_stock']=$result['stock_warehouse'];
            }
            elseif(isset($result['stock_outlet']))
            {
                $data['current_stock']=$result['stock_outlet'];
            }
            else
            {
                $data['current_stock']='N/A';
            }
            $data['quantity_expected']=$result['quantity_expected'];
        }
        else
        {
            $data['hom_budget']='N/A';
            $data['stock_warehouse']='N/A';
            $data['stock_outlet']='N/A';
            $data['current_stock']='N/A';
            $data['quantity_expected']='N/A';
        }
        $data['variety']=Query_helper::get_info($this->config->item('table_login_setup_classification_varieties'),array('name'),array('id ='.$reports['variety_id']),1);
        $data['currencies']=Query_helper::get_info($this->config->item('table_bms_setup_currency'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering ASC'));
        //currency rates
        $rates=Query_helper::get_info($this->config->item('table_bms_setup_currency_rate'),'*',array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$reports['year_id']));
        $data['currency_rates']=array();
        foreach($rates as $rate)
        {
            $data['currency_rates'][$rate['currency_id']]=$rate['rate'];
        }

        //Confirmed data from principals
        $this->db->from($this->config->item('table_login_setup_variety_principals').' vp');
        $this->db->select('vp.principal_id,vp.name_import');
        $this->db->where('vp.variety_id',$reports['variety_id']);
        $this->db->where('vp.revision',1);
        $this->db->select('p.name principal_name');
        $this->db->join($this->config->item('table_login_basic_setup_principal').' p','p.id = vp.principal_id','INNER');
        $this->db->where('p.status!=',$this->config->item('system_status_delete'));
        $this->db->select('confirm.currency_id,confirm.unit_price,confirm.quantity_total,confirm.quantity_1,confirm.quantity_2,confirm.quantity_3,confirm.quantity_4,confirm.quantity_5,confirm.quantity_6,confirm.quantity_7,confirm.quantity_8,confirm.quantity_9,confirm.quantity_10,confirm.quantity_11,confirm.quantity_12,');
        $this->db->join($this->config->item('table_bms_mgt_quantity_confirm').' confirm','confirm.principal_id = vp.principal_id and confirm.variety_id = vp.variety_id and confirm.year_id = '.$reports['year_id'],'LEFT');
        $results=$this->db->get()->result_array();
        //print_r($results);exit;
        foreach($results as $result)
        {
            $data['principals'][$result['principal_id']]=$result;
        }
        //print_r($data);exit;
        $data['title']='Quantity Confirmed for '.$data['variety']['name'];
        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->json_return($ajax);
    }
}