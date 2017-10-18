<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_lc_actual_cost extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Purchase_lc_actual_cost');
        $this->controller_url='purchase_lc_actual_cost';
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
        $this->db->where('lc.status',$this->config->item('system_status_active'));
        $this->db->order_by('lc.year_id','DESC');
        $this->db->order_by('lc.id','DESC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            $item['month_name']=$this->lang->line('LABEL_MONTH_'.$item['month_id']);
            $item['date_opening']=System_helper::display_date($item['date_opening']);
            $item['date_expected']=System_helper::display_date($item['date_expected']);
            if($item['status_received']==$this->config->item('system_status_yes'))
            {
                $item['status_received']='Received';
            }
            else
            {
                $item['status_received']='Pending';
            }

            if($item['status_closed']==$this->config->item('system_status_yes'))
            {
                $item['status_closed']='Closed';
            }
            else
            {
                $item['status_closed']='Open';
            }
        }
        $this->json_return($items);
    }

    private function system_edit($id)
    {
        if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }

            $this->db->select('lc.*');
            $this->db->select('fy.name fiscal_year_name');
            $this->db->select('principal.name principal_name');
            $this->db->select('currency.name currency_name');
            $this->db->from($this->config->item('table_bms_purchase_lc').' lc');
            $this->db->join($this->config->item('table_login_basic_setup_fiscal_year').' fy','fy.id=lc.year_id','INNER');
            $this->db->join($this->config->item('table_login_basic_setup_principal').' principal','principal.id=lc.principal_id','INNER');
            $this->db->join($this->config->item('table_bms_setup_currency').' currency','currency.id=lc.currency_id','INNER');
            $this->db->where('lc.id',$item_id);
            $data['item']=$this->db->get()->row_array();
            //print_r($data['item']);exit();
            if(!$data['item'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Try';
                $this->json_return($ajax);
            }

            $this->db->select('v.name variety_name');
            $this->db->select('pack.name pack_name');
            $this->db->select('lc_details.*');
            $this->db->from($this->config->item('table_bms_purchase_lc_details').' lc_details');
            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id=lc_details.variety_id','INNER');
            $this->db->join($this->config->item('table_login_setup_classification_vpack_size').' pack','pack.id=lc_details.quantity_type_id','LEFT');
            $this->db->where('lc_details.lc_id',$item_id);
            $this->db->where('lc_details.revision',1);
            $data['items_variety_cost']=$this->db->get()->result_array();
            //print_r($data['items_variety_cost']);exit();

            $this->db->select('dc.id,dc.name');
            $this->db->select('lc_dcost.amount_cost');
            $this->db->from($this->config->item('table_bms_setup_direct_cost_items').' dc');
            $this->db->join($this->config->item('table_bms_purchase_lc_cost_details').' lc_dcost','lc_dcost.direct_cost_id=dc.id AND lc_dcost.revision=1 AND lc_dcost.lc_id='.$item_id,'LEFT');
            $data['items_direct_cost']=$this->db->get()->result_array();
            //print_r($data['items_direct_cost']);exit();

            $data['title']="Set Actual Cost of LC No. (".$data['item']['lc_number'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
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
        $user = User_helper::get_user();
        if(!(isset($this->permissions['action1'])&&($this->permissions['action1']==1) || isset($this->permissions['action2'])&&($this->permissions['action2']==1) || isset($this->permissions['action3'])&&($this->permissions['action3']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
            die();
        }
        else
        {
            $time=time();
            $data=$this->input->post('item');
            if($data)
            {
                $data['date_opening']=System_helper::get_time($data['date_opening']);
                $data['date_expected']=System_helper::get_time($data['date_expected']);
            }
            $varieties=$this->input->post('varieties');
            if($varieties)
            {
                if(!$this->check_validation_for_varieties())
                {
                    $ajax['status']=false;
                    $ajax['system_message']=$this->message;
                    $this->json_return($ajax);
                    die();
                }
            }
            $this->db->trans_start();  //DB Transaction Handle START

            if($id>0)
            {
                $this->db->from($this->config->item('table_bms_purchase_lc').' lc');
                $this->db->select('lc.*');
                $this->db->select('lc_details.*');
                $this->db->join($this->config->item('table_bms_purchase_lc_details').' lc_details','lc_details.lc_id = lc.id AND lc_details.revision = 1','LEFT');
                $this->db->where('lc.id',$id);
                $this->db->where('lc.status',$this->config->item('system_status_active'));
                $result=$this->db->get()->row_array();
                if(!$result)
                {
                    $ajax['status']=false;
                    $ajax['system_message']='Invalid Try';
                    $this->json_return($ajax);
                    die();
                }
                else
                {
                    $currency_rate=$result['amount_currency_rate'];
                }
                if($result && $result['revision_receive']>0)
                {
                    if(isset($this->permissions['action3'])&&($this->permissions['action3']==1))
                    {
                        if(!$this->check_validation())
                        {
                            $ajax['status']=false;
                            $ajax['system_message']=$this->message;
                            $this->json_return($ajax);
                        }
                        $data['date_updated']=$time;
                        $data['user_updated']=$user->user_id;
                        Query_helper::update($this->config->item('table_bms_purchase_lc'),$data,array('id='.$id));
                        //varieties
                        $revision_history_data=array();
                        $revision_history_data['date_updated']=$time;
                        $revision_history_data['user_updated']=$user->user_id;
                        Query_helper::update($this->config->item('table_bms_purchase_lc_details'),$revision_history_data,array('revision=1','lc_id='.$id));

                        $this->db->where('lc_id',$id);
                        $this->db->set('revision', 'revision+1', FALSE);
                        $this->db->update($this->config->item('table_bms_purchase_lc_details'));

                        foreach($varieties as $v)
                        {
                            $v_data=array();
                            $v_data['lc_id']=$id;
                            $v_data['variety_id']=$v['variety_id'];
                            $v_data['quantity_type_id']=$v['quantity_type_id'];
                            $v_data['quantity_order']=$v['quantity_order'];
                            $v_data['amount_price_order']=$v['amount_price_order'];
                            $v_data['amount_price_total_order']=$v['quantity_order']*$v['amount_price_order']*$data['amount_currency_rate'];
                            $v_data['revision']=1;
                            $v_data['date_created'] = $time;
                            $v_data['user_created'] = $user->user_id;
                            Query_helper::add($this->config->item('table_bms_purchase_lc_details'),$v_data);
                        }
                    }
                    else
                    {
                        $ajax['status']=false;
                        $ajax['system_message']='You can not edit this LC';
                        $this->json_return($ajax);
                        die();
                    }
                }
                else
                {
                    if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)) && !(isset($this->permissions['action3']) && ($this->permissions['action3']==1)) && isset($this->permissions['action1']) && ($this->permissions['action1']==1))
                    {
                        //varieties
                        $revision_history_data=array();
                        $revision_history_data['date_updated']=$time;
                        $revision_history_data['user_updated']=$user->user_id;
                        Query_helper::update($this->config->item('table_bms_purchase_lc_details'),$revision_history_data,array('revision=1','lc_id='.$id));

                        $this->db->where('lc_id',$id);
                        $this->db->set('revision', 'revision+1', FALSE);
                        $this->db->update($this->config->item('table_bms_purchase_lc_details'));

                        foreach($varieties as $v)
                        {
                            $v_data=array();
                            $v_data['lc_id']=$id;
                            $v_data['variety_id']=$v['variety_id'];
                            $v_data['quantity_type_id']=$v['quantity_type_id'];
                            $v_data['quantity_order']=$v['quantity_order'];
                            $v_data['amount_price_order']=$v['amount_price_order'];
                            $v_data['amount_price_total_order']=$v['quantity_order']*$v['amount_price_order']*$currency_rate;
                            $v_data['revision']=1;
                            $v_data['date_created'] = $time;
                            $v_data['user_created'] = $user->user_id;
                            Query_helper::add($this->config->item('table_bms_purchase_lc_details'),$v_data);
                        }
                    }
                    if(isset($this->permissions['action2'])&&($this->permissions['action2']==1) || isset($this->permissions['action3'])&&($this->permissions['action3']==1))
                    {
                        if(!$this->check_validation())
                        {
                            $ajax['status']=false;
                            $ajax['system_message']=$this->message;
                            $this->json_return($ajax);
                            die();
                        }
                        $data['date_updated']=$time;
                        $data['user_updated']=$user->user_id;
                        Query_helper::update($this->config->item('table_bms_purchase_lc'),$data,array('id='.$id));
                        //varieties
                        $revision_history_data=array();
                        $revision_history_data['date_updated']=$time;
                        $revision_history_data['user_updated']=$user->user_id;
                        Query_helper::update($this->config->item('table_bms_purchase_lc_details'),$revision_history_data,array('revision=1','lc_id='.$id));

                        $this->db->where('lc_id',$id);
                        $this->db->set('revision', 'revision+1', FALSE);
                        $this->db->update($this->config->item('table_bms_purchase_lc_details'));

                        foreach($varieties as $v)
                        {
                            $v_data=array();
                            $v_data['lc_id']=$id;
                            $v_data['variety_id']=$v['variety_id'];
                            $v_data['quantity_type_id']=$v['quantity_type_id'];
                            $v_data['quantity_order']=$v['quantity_order'];
                            $v_data['amount_price_order']=$v['amount_price_order'];
                            $v_data['amount_price_total_order']=$v['quantity_order']*$v['amount_price_order']*$data['amount_currency_rate'];
                            $v_data['revision']=1;
                            $v_data['date_created'] = $time;
                            $v_data['user_created'] = $user->user_id;
                            Query_helper::add($this->config->item('table_bms_purchase_lc_details'),$v_data);
                        }
                    }
                }
            }
            else
            {
                if(!$this->check_validation())
                {
                    $ajax['status']=false;
                    $ajax['system_message']=$this->message;
                    $this->json_return($ajax);
                }
                if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
                {
                    $data['user_created'] = $user->user_id;
                    $data['date_created'] = time();
                    $lc_id=Query_helper::add($this->config->item('table_bms_purchase_lc'),$data);
                    //varieties
                    if($varieties)
                    {
                        foreach($varieties as $v)
                        {
                            $v_data=array();
                            $v_data['lc_id']=$lc_id;
                            $v_data['variety_id']=$v['variety_id'];
                            $v_data['quantity_type_id']=$v['quantity_type_id'];
                            $v_data['quantity_order']=$v['quantity_order'];
                            $v_data['amount_price_order']=$v['amount_price_order'];
                            $v_data['amount_price_total_order']=$v['quantity_order']*$v['amount_price_order']*$data['amount_currency_rate'];
                            $v_data['revision']=1;
                            $v_data['date_created'] = $time;
                            $v_data['user_created'] = $user->user_id;
                            Query_helper::add($this->config->item('table_bms_purchase_lc_details'),$v_data);
                        }
                    }
                }
            }

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $save_and_new=$this->input->post('system_save_new_status');
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                if($save_and_new==1)
                {
                    $this->system_add();
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
    private function check_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[year_id]',$this->lang->line('LABEL_FISCAL_YEAR'),'required');
        $this->form_validation->set_rules('item[month_id]',$this->lang->line('LABEL_MONTH'),'required');
        $this->form_validation->set_rules('item[date_opening]',$this->lang->line('LABEL_DATE_OPENING'),'required');
        $this->form_validation->set_rules('item[principal_id]',$this->lang->line('LABEL_PRINCIPAL_NAME'),'required');
        $this->form_validation->set_rules('item[consignment_name]',$this->lang->line('LABEL_CONSIGNMENT_NAME'),'required');
        $this->form_validation->set_rules('item[currency_id]',$this->lang->line('LABEL_CURRENCY_NAME'),'required');
        $this->form_validation->set_rules('item[amount_currency_rate]',$this->lang->line('LABEL_CURRENCY_RATE'),'required');
        $this->form_validation->set_rules('item[lc_number]',$this->lang->line('LABEL_LC_NUMBER'),'required');
        $this->form_validation->set_rules('item[date_expected]',$this->lang->line('LABEL_DATE_EXPECTED'),'required');
        $this->form_validation->set_rules('item[status]',$this->lang->line('STATUS'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
    private function check_validation_for_varieties()
    {
        $varieties=$this->input->post('varieties');
        if(!(sizeof($varieties)>0))
        {
            return true;
        }
        else
        {
            foreach($varieties as $variety)
            {
                if(!(($variety['variety_id']>0)&& ($variety['quantity_type_id']>=0)&& ($variety['quantity_order']>0)&& ($variety['amount_price_order']>0)))
                {
                    $this->message='Unfinished Variety Entry';
                    return false;
                }
            }
        }
        return true;
    }
    public function get_dropdown_arm_varieties_by_principal_id()
    {
        $principal_id = $this->input->post('principal_id');
        $html_container_id='#varieties_container';
        if($this->input->post('html_container_id'))
        {
            $html_container_id=$this->input->post('html_container_id');
        }

        $this->db->from($this->config->item('table_login_setup_classification_varieties').' v');
        $this->db->select('v.id value,v.name');
        $this->db->select('vp.name_import text');
        $this->db->join($this->config->item('table_login_setup_variety_principals').' vp','vp.variety_id = v.id AND vp.principal_id = '.$principal_id.' AND vp.revision = 1','INNER');
        $this->db->where('v.status',$this->config->item('system_status_active'));
        $this->db->where('v.whose','ARM');
        $this->db->order_by('v.ordering ASC');
        $data['items']=$this->db->get()->result_array();
        
        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>$html_container_id,"html"=>$this->load->view("dropdown_with_select",$data,true));

        $this->json_return($ajax);
    }
}
