<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hom_quantity_expectation extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Hom_quantity_expectation');
        $this->controller_url='hom_quantity_expectation';
        $this->locations=User_helper::get_locations();
        if(!($this->locations))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('MSG_LOCATION_NOT_ASSIGNED_OR_INVALID');
            $this->json_return($ajax);
        }
    }
    public function index($action="search",$id=0)
    {
        if($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="edit")
        {
            $this->system_edit($id);
        }
        elseif($action=="get_edit_items")
        {
            $this->system_get_edit_items();
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="forward")
        {
            $this->system_forward();
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
            $data['title']="Quantity Expectation Search";
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
    private function system_edit($id)
    {
        $reports=$this->input->post('report');

        $data['options']=$reports;
        if(!($reports['year_id']>0))
        {
            $ajax['status']=false;
            $ajax['system_message']='Please Select a Fiscal year';
            $this->json_return($ajax);
        }
        if(!($reports['crop_type_id']>0))
        {
            $ajax['status']=false;
            $ajax['system_message']='Please Select a Crop Type';
            $this->json_return($ajax);
        }
        //budget status
        $this->db->from($this->config->item('table_bms_hom_budget_hom').' bud');
        $this->db->select('bud.*');
        $this->db->select('forward.status_forward_quantity_expectation,forward.date_forward_quantity_expectation,forward.user_forward_quantity_expectation');
        $this->db->where('bud.year_id',$reports['year_id']);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$reports['crop_type_id']);
        $this->db->join($this->config->item('table_bms_hom_forward').' forward','forward.year_id = '.$reports['year_id'].' and forward.crop_type_id = '.$reports['crop_type_id'],'LEFT');
        $this->db->order_by('bud.revision_quantity_expected','DESC');
        $result=$this->db->get()->row_array();
        //print_r($result);exit;
        $data['quantity_expectation_info']['status_quantity_expectation']='Not Done';
        $data['quantity_expectation_info']['date_quantity_expected']='N/A';
        $data['quantity_expectation_info']['user_quantity_expected']='N/A';
        $data['quantity_expectation_info']['date_forward_quantity_expectation']='N/A';
        $data['quantity_expectation_info']['user_forward_quantity_expectation']='N/A';
        if($result)
        {
            //$data['budget_info']=$result;
            if($result['user_updated_quantity_expected']>0)
            {
                $result['user_quantity_expected']=$result['user_updated_quantity_expected'];
                $result['date_quantity_expected']=$result['date_updated_quantity_expected'];
            }
            $user_ids=array();
            $user_ids[$result['user_quantity_expected']]=$result['user_quantity_expected'];
            if($result['user_forward_quantity_expectation']>0)
            {
                $user_ids[$result['user_forward_quantity_expectation']]=$result['user_forward_quantity_expectation'];
            }
            $this->db->from($this->config->item('table_login_setup_user_info').' ui');
            $this->db->select('ui.name,ui.user_id');
            $this->db->where('ui.revision',1);
            $users=$this->db->get()->result_array();
            foreach($users as $u)
            {
                if($u['user_id']==$result['user_quantity_expected'])
                {
                    $data['quantity_expectation_info']['user_quantity_expected']=$u['name'];
                }
                if($u['user_id']==$result['user_forward_quantity_expectation'])
                {
                    $data['quantity_expectation_info']['user_forward_quantity_expectation']=$u['name'];
                }
            }
            if($result['status_forward_quantity_expectation']&& $result['status_forward_quantity_expectation']==$this->config->item('system_status_yes'))
            {
                $data['quantity_expectation_info']['status_quantity_expectation']='Forwarded';
                $data['quantity_expectation_info']['date_forward']=System_helper::display_date_time($result['date_forward_quantity_expectation']);
            }
            elseif($result['revision_quantity_expected']!=0)
            {
                $data['quantity_expectation_info']['status_quantity_expectation']='Not Forwarded';
            }
            $data['quantity_expectation_info']['date_quantity_expected']=System_helper::display_date_time($result['date_quantity_expected']);
        }
        $data['title']='HOM Budget';
        $data['years_previous']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id <'.$reports['year_id']),$this->config->item('num_year_previous_sell'),0,array('id DESC'));
        $data['year_current']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id ='.$reports['year_id']),1,0,array('id ASC'));
        $data['years_next']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id >'.$reports['year_id']),$this->config->item('num_year_budget_prediction'),0,array('id ASC'));
        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->json_return($ajax);
    }
    private function system_get_edit_items()
    {
        $items=array();
        $year_id=$this->input->post('year_id');
        $crop_type_id=$this->input->post('crop_type_id');

        $forwarded=false;
        $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),'*',array('year_id ='.$year_id,'crop_type_id ='.$crop_type_id),1);
        if($result && $result['status_forward_quantity_expectation']==$this->config->item('system_status_yes'))
        {
            $forwarded=true;
        }


        //currently saved budget
        $results=Query_helper::get_info($this->config->item('table_bms_hom_budget_hom'),'*',array('year_id ='.$year_id,'year_index =0'));
        $items_current=array();//hom budget
        foreach($results as $result)
        {
            $items_current[$result['variety_id']]=$result;
        }
        //TODO get warehouse stock
        $stock_warehouse=array();

        //TODO get outlet stock
        $stock_outlet=array();

        $results=Query_helper::get_info($this->config->item('table_bms_setup_bud_stock_minimum'),'*',array('revision =1'));
        $stock_minimum=array();//minimum stock
        foreach($results as $result)
        {
            $stock_minimum[$result['variety_id']]=$result['quantity'];
        }
        //getting varieties
        $results=Query_helper::get_info($this->config->item('table_login_setup_classification_varieties'),array('id','name'),array('crop_type_id ='.$crop_type_id,'status ="'.$this->config->item('system_status_active').'"','whose ="ARM"'),0,0,array('ordering ASC'));

        $count=0;
        foreach($results as $result)
        {
            $count++;
            $item=array();
            $item['sl_no']=$count;
            $item['variety_id']=$result['id'];
            $item['variety_name']=$result['name'];

            if(isset($stock_warehouse[$item['variety_id']]))
            {
                $item['stock_warehouse']=$stock_warehouse[$item['variety_id']];
            }
            else
            {
                $item['stock_warehouse']=0;
            }
            if(isset($stock_outlet[$item['variety_id']]))
            {
                $item['stock_outlet']=$stock_outlet[$item['variety_id']];
            }
            else
            {
                $item['stock_outlet']=0;
            }
            if(isset($stock_minimum[$item['variety_id']]))
            {
                $item['stock_minimum']=$stock_minimum[$item['variety_id']];
            }
            else
            {
                $item['stock_minimum']=0;
            }
            $item['quantity_expected']=0;
            if((isset($items_current[$item['variety_id']]['quantity_budget'])))
            {
                $item['quantity_budget']=$items_current[$item['variety_id']]['quantity_budget'];
                if($items_current[$item['variety_id']]['revision_quantity_expected']!=0)
                {
                    //in case null
                    if($items_current[$item['variety_id']]['stock_warehouse'])
                    {
                        $item['stock_warehouse']=$items_current[$item['variety_id']]['stock_warehouse'];
                    }
                    else
                    {
                        $item['stock_warehouse']=0;
                    }
                    if($items_current[$item['variety_id']]['stock_outlet'])
                    {
                        $item['stock_outlet']=$items_current[$item['variety_id']]['stock_outlet'];
                    }
                    else
                    {
                        $item['stock_outlet']=0;
                    }
                    if($items_current[$item['variety_id']]['stock_minimum'])
                    {
                        $item['stock_minimum']=$items_current[$item['variety_id']]['stock_minimum'];
                    }
                    else
                    {
                        $item['stock_minimum']=0;
                    }

                    if($items_current[$item['variety_id']]['quantity_expected'])
                    {
                        $item['quantity_expected']=$items_current[$item['variety_id']]['quantity_expected'];
                    }
                    else
                    {
                        $item['quantity_expected']=0;
                    }

                }

            }
            else
            {
                $item['quantity_budget']='N/D';
                $item['quantity_expected']=0;
            }
            $item['stock_total']=$item['stock_warehouse']+$item['stock_outlet'];;
            if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
            {
                $item['quantity_expected_editable']=true;
            }
            else if(!$forwarded)
            {
                if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
                {
                    $item['quantity_expected_editable']=true;
                }
                else if(isset($this->permissions['action1']) && ($this->permissions['action1']==1) && ($item['quantity_expected']==0))
                {
                    $item['quantity_expected_editable']=true;

                }
                else
                {
                    $item['quantity_expected_editable']=false;
                }
            }
            else//not delete and forwarded
            {
                $item['quantity_expected_editable']=false;
            }
            $items[]=$item;
        }
        $this->json_return($items);
    }
    private function system_save()
    {
        if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1))||(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            $user = User_helper::get_user();
            $time=time();
            $year_id=$this->input->post('year_id');
            $crop_type_id=$this->input->post('crop_type_id');
            $items=$this->input->post('items');
            $forwarded=false;
            $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),'*',array('year_id ='.$year_id,'crop_type_id ='.$crop_type_id),1);

            if($result)
            {
                if($result['status_forward_budget']!=$this->config->item('system_status_yes'))
                {
                    $ajax['status']=false;
                    $ajax['system_message']='HOM Budget Not Forwarded Yet';
                    $this->json_return($ajax);
                    die();
                }
                if($result['status_forward_quantity_expectation']==$this->config->item('system_status_yes'))
                {
                    $forwarded=true;
                }
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']='HOM Budget Not Forwarded Yet';
                $this->json_return($ajax);
                die();
            }

            //getting current year data
            $results=Query_helper::get_info($this->config->item('table_bms_hom_budget_hom'),'*',array('year_id ='.$year_id,'year_index =0'));
            $items_current=array();//hom budget
            foreach($results as $result)
            {
                $items_current[$result['variety_id']]=$result;
            }
            $this->db->trans_start();  //DB Transaction Handle START
            foreach($items as $variety_id=>$item)
            {
                if(isset($items_current[$variety_id]))
                {
                    if($items_current[$variety_id]['quantity_expected']!=$item['quantity_expected'])
                    {
                        $editable=false;
                        if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
                        {
                            $editable=true;
                        }
                        else if(!$forwarded)
                        {
                            if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
                            {
                                $editable=true;
                            }
                            else if(isset($this->permissions['action1']) && ($this->permissions['action1']==1) && ($items_current[$variety_id]['quantity_expected']==0))
                            {
                                $editable=true;
                            }
                        }
                        if($editable)
                        {
                            $this->db->where('id',$items_current[$variety_id]['id']);
                            $this->db->set('revision_quantity_expected','revision_quantity_expected+1',false);
                            $this->db->set('quantity_expected',$item['quantity_expected']);
                            if($items_current[$variety_id]['revision_quantity_expected']==0)
                            {
                                $this->db->set('date_quantity_expected',$time);
                                $this->db->set('user_quantity_expected',$user->user_id);
                            }
                            else
                            {
                                $this->db->set('date_updated_quantity_expected',$time);
                                $this->db->set('user_updated_quantity_expected',$user->user_id);
                            }

                            $this->db->update($this->config->item('table_bms_hom_budget_hom'));
                        }
                    }
                    elseif($items_current[$variety_id]['quantity_expected']==$item['quantity_expected'] && $items_current[$variety_id]['revision_quantity_expected']==0)
                    {
                        $this->db->where('id',$items_current[$variety_id]['id']);
                        $this->db->set('revision_quantity_expected','revision_quantity_expected+1',false);
                        $this->db->set('quantity_expected',$item['quantity_expected']);
                        $this->db->set('date_quantity_expected',$time);
                        $this->db->set('user_quantity_expected',$user->user_id);
                        $this->db->update($this->config->item('table_bms_hom_budget_hom'));
                    }
                }
            }
            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $ajax['status']=true;
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>'');
                $ajax['system_message']=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->json_return($ajax);

            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->json_return($ajax);
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_forward()
    {
        $user = User_helper::get_user();
        $time=time();
        $year_id=$this->input->post('year_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),array('id,status_forward_budget,status_forward_quantity_expectation'),array('year_id ='.$year_id,'crop_type_id ='.$crop_type_id),1);
        if($result && $result['status_forward_quantity_expectation']==$this->config->item('system_status_yes'))
        {
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>'');
            $ajax['system_message']="Expected Quantity Budget already Forwarded";
            $this->json_return($ajax);
        }
        elseif($result && $result['status_forward_quantity_expectation']!=$this->config->item('system_status_yes'))
        {
            if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1))||(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
            {
                $data=array();
                $data['status_forward_quantity_expectation']=$this->config->item('system_status_yes');
                $data['date_forward_quantity_expectation'] = $time;
                $data['user_forward_quantity_expectation'] = $user->user_id;
                Query_helper::update($this->config->item('table_bms_hom_forward'),$data,array('id='.$result['id']));
                $ajax['status']=true;
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>'');
                $ajax['system_message']="Expected Quantity Forwarded Successfully";
                $this->json_return($ajax);
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>'');
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>'');
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
}
