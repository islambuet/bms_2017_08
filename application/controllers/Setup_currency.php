<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setup_currency extends Root_Controller
{
    private $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Setup_currency');
        $this->controller_url='setup_currency';
        $this->locations=User_helper::get_locations();
        if(!($this->locations))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('MSG_LOCATION_NOT_ASSIGNED_OR_INVALID');
            $this->json_return($ajax);
        }
    }

    public function index($action="list",$id=0,$id1=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=='get_items')
        {
            $this->system_get_items();
        }
        elseif($action=='add')
        {
            $this->system_add_currency();
        }
        elseif($action=='edit')
        {
            $this->system_edit_currency($id);
        }
        elseif($action=="save")
        {
            $this->system_save_currency();
        }
        elseif($action=='list_rate')
        {
            $this->system_list_rate($id);
        }
        elseif($action=='get_rate_items')
        {
            $this->system_get_rate_items($id);
        }
        elseif($action=="edit_rate")
        {
            $this->system_edit_rate($id,$id1);
        }
        elseif($action=="save_rate")
        {
            $this->system_save_currency_rate();
        }
        else
        {
            $this->system_list();
        }
    }

    private function system_list()
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['title']='Currency List';
            $ajax['status']=true;
            $ajax['system_content'][]=array('id'=>'#system_content','html'=>$this->load->view($this->controller_url.'/list_currency',$data,true));
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
            $ajax['system_message']=$this->lang->line('YOU_DONT_HAVE_ACCESS');
            $this->json_return($ajax);
        }
    }
    private function system_get_items()
    {
        $this->db->from($this->config->item('table_bms_setup_currency'));
        $this->db->select('id,name,symbol,status,ordering');
        $this->db->order_by('ordering','ASC');
        $this->db->where('status !=',$this->config->item('system_status_delete'));
        $items=$this->db->get()->result_array();
        $this->json_return($items);
    }
    private function system_add_currency()
    {
        if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
        {
            $data['title']="Create Currency";
            $data["currency"] = Array(
                'id' => 0,
                'name' => '',
                'symbol' => '',
                'description' => '',
                'ordering' => 99,
                'status' => $this->config->item('system_status_active')
            );
            $ajax['system_page_url']=site_url($this->controller_url."/index/add");
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("setup_currency/add_edit_currency",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_list_rate($id)
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_bms_setup_currency').' currency');
            $this->db->select('currency.name');
            $this->db->where('currency.id',$item_id);
            $item=$this->db->get()->row_array();
            if(!$item)
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Currency.';
                $this->json_return($ajax);
            }
            $data['title']='Currency Rate of '.$item['name'];
            $data['options']=array('currency_id'=>$item_id);
            $ajax['status']=true;
            $ajax['system_content'][]=array('id'=>'#system_content','html'=>$this->load->view($this->controller_url.'/list_rate',$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/list_rate/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('YOU_DONT_HAVE_ACCESS');
            $this->json_return($ajax);
        }
    }
    private function system_get_rate_items()
    {
        $items=array();
        $item_id=$this->input->post('currency_id');
        $this->db->from($this->config->item('table_login_basic_setup_fiscal_year').' year');
        $this->db->select('year.id,year.name');
        $this->db->select('currency_rate.rate');
        $this->db->join($this->config->item('table_bms_setup_currency_rate').' currency_rate','currency_rate.fiscal_year_id = year.id and currency_rate.currency_id = '.$item_id.'','LEFT');
        $this->db->where('year.status !=',$this->config->item('system_status_delete'));
        $this->db->order_by('year.id','ASC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            if($item['rate']=='')
            {
                $item['rate']='Not Assigned';
            }
        }

        $this->json_return($items);
    }
    private function system_edit_currency($id)
    {
        if(isset($this->permissions['action2'])&&($this->permissions['action2']==1))
        {
            if(($this->input->post('id')))
            {
                $currency_id=$this->input->post('id');
            }
            else
            {
                $currency_id=$id;
            }

            $data['currency']=Query_helper::get_info($this->config->item('table_bms_setup_currency'),'*',array('id ='.$currency_id),1);
            $data['title']="Edit Currency (".$data['currency']['name'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("setup_currency/add_edit_currency",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$currency_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_edit_rate($currency_id,$id)
    {
        if($currency_id<1)
        {
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try';
            $this->json_return($ajax);
        }
        if(isset($this->permissions['action2'])&&($this->permissions['action2']==1))
        {
            if(($this->input->post('id')))
            {
                $year_id=$this->input->post('id');
            }
            else
            {
                $year_id=$id;
            }

            $this->db->from($this->config->item('table_bms_setup_currency').' currency');
            $this->db->select('currency.id,currency.name');
            $this->db->select('currency_rate.rate');
            $this->db->join($this->config->item('table_bms_setup_currency_rate').' currency_rate','currency_rate.currency_id = currency.id and currency_rate.fiscal_year_id = '.$year_id.'','LEFT');
            $this->db->where('currency.id',$currency_id);
            $data['currency']=$this->db->get()->row_array();
            $data['currency']['fiscal_year_id']=$year_id;
            //print_r($data['currency']);exit;
            $data['title']="Edit ".$data['currency']['name']." Rate";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("setup_currency/edit_rate",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit_rate/'.$currency_id.'/'.$year_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save_currency()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        if($id>0)
        {
            if(!(isset($this->permissions['action2'])&&($this->permissions['action2']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
        }
        else
        {
            if(!(isset($this->permissions['action1'])&&($this->permissions['action1']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();

            }
        }
        if(!$this->check_validation_for_currency())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $data=$this->input->post('currency');
            $this->db->trans_start();  //DB Transaction Handle START
            if($id>0)
            {
                $data['user_updated'] = $user->user_id;
                $data['date_updated'] = time();
                Query_helper::update($this->config->item('table_bms_setup_currency'),$data,array("id = ".$id));
            }
            else
            {
                $data['user_created'] = $user->user_id;
                $data['date_created'] = time();
                Query_helper::add($this->config->item('table_bms_setup_currency'),$data);
            }
            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $save_and_new=$this->input->post('system_save_new_status');
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                if($save_and_new==1)
                {
                    $this->system_add_currency();
                }
                else
                {
                    $this->system_list();
                }
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function system_save_currency_rate()
    {
        $id = $this->input->post("id");
        $fiscal_year_id = $this->input->post("fiscal_year_id");
        $user = User_helper::get_user();
        if($id>0 && $fiscal_year_id>0)
        {
            if(!(isset($this->permissions['action2'])&&($this->permissions['action2']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
                die();
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']='Invalid Try';
            $this->json_return($ajax);
            die();
        }
        if(!$this->check_validation_for_currency_rate())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $data=$this->input->post('currency');
            $info=Query_helper::get_info($this->config->item('table_bms_setup_currency_rate'),'*',array('currency_id ='.$id,'fiscal_year_id ='.$fiscal_year_id),1);
            //print_r($info);exit;
            $this->db->trans_start();  //DB Transaction Handle START
            if($info)
            {
                $data['user_updated'] = $user->user_id;
                $data['date_updated'] = time();
                Query_helper::update($this->config->item('table_bms_setup_currency_rate'),$data,array("currency_id = ".$id,"fiscal_year_id = ".$fiscal_year_id));
            }
            else
            {
                $data['fiscal_year_id'] = $fiscal_year_id;
                $data['currency_id'] = $id;
                $data['user_created'] = $user->user_id;
                $data['date_created'] = time();
                Query_helper::add($this->config->item('table_bms_setup_currency_rate'),$data);
            }
            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_list_rate($id);
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
    }
    private function check_validation_for_currency()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('currency[name]',$this->lang->line('LABEL_NAME'),'required');
        $this->form_validation->set_rules('currency[status]',$this->lang->line('STATUS'),'required');

        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function check_validation_for_currency_rate()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('currency[rate]','Currency Rate','required');

        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }













}
