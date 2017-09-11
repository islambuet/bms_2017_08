<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hom_target_finalize_hom extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Hom_target_finalize_hom');
        $this->controller_url='hom_target_finalize_hom';
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
            $data['title']="HOM Target Finalize Search";
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

        //market survey size
        $this->db->select('SUM(ms.size_total) size_total,SUM(ms.size_arm) size_arm,COUNT(ms.outlet_id) num_outlet');
        $this->db->from($this->config->item('table_bms_setup_market_size').' ms');
        $this->db->join($this->config->item('table_login_csetup_customer').' outlet','outlet.id=ms.outlet_id','INNER');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' out_info','out_info.customer_id = outlet.id and out_info.revision = 1','INNER');
        $this->db->where('ms.revision',1);
        $this->db->where('ms.crop_type_id',$reports['crop_type_id']);
        $this->db->where('outlet.status',$this->config->item('system_status_active'));
        $this->db->where('out_info.type',$this->config->item('system_customer_type_outlet_id'));
        /*
        $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = out_info.district_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_territories').' t','t.id = d.territory_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_divisions').' division','division.id = zone.division_id','INNER');
        $this->db->where('division.status',$this->config->item('system_status_active'));
        */
        $data['market_survey']=$this->db->get()->row_array();

        //budget status
        $this->db->from($this->config->item('table_bms_hom_budget_hom').' bud');
        $this->db->select('bud.*');
        $this->db->select('forward.status_forward_target,forward.date_forward_target,forward.user_forward_target');
        $this->db->where('bud.year_id',$reports['year_id']);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$reports['crop_type_id']);
        $this->db->join($this->config->item('table_bms_hom_forward').' forward','forward.year_id = '.$reports['year_id'].' and forward.crop_type_id = '.$reports['crop_type_id'],'LEFT');
        $this->db->order_by('bud.revision_budget','DESC');
        $result=$this->db->get()->row_array();
        
        $data['target_info']['status_forward_target']='Not Done';
        $data['target_info']['date_targeted']='N/A';
        $data['target_info']['user_targeted']='N/A';
        $data['target_info']['date_forward_target']='N/A';
        $data['target_info']['user_forward_target']='N/A';
        if($result && $result['revision_target']>0)
        {
            //$data['target_info']=$result;
            if($result['user_updated_target']>0)
            {
                $result['user_targeted']=$result['user_updated_target'];
                $result['date_targeted']=$result['date_updated_target'];
            }
            $user_ids=array();
            $user_ids[$result['user_targeted']]=$result['user_targeted'];
            if($result['user_forward_target']>0)
            {
                $user_ids[$result['user_forward_target']]=$result['user_forward_target'];
            }
            $this->db->from($this->config->item('table_login_setup_user_info').' ui');
            $this->db->select('ui.name,ui.user_id');
            $this->db->where('ui.revision',1);
            $this->db->where_in('ui.user_id',$user_ids);
            $users=$this->db->get()->result_array();
            foreach($users as $u)
            {
                if($u['user_id']==$result['user_targeted'])
                {
                    $data['target_info']['user_targeted']=$u['name'];
                }
                if($u['user_id']==$result['user_forward_target'])
                {
                    $data['target_info']['user_forward_target']=$u['name'];
                }
            }

            if($result['status_forward_target']&& $result['status_forward_target']==$this->config->item('system_status_yes'))
            {
                $data['target_info']['status_forward_target']='Forwarded';
                $data['target_info']['date_forward_target']=System_helper::display_date_time($result['date_forward_target']);
                $data['target_info']['date_targeted']=System_helper::display_date_time($result['date_targeted']);
            }
            elseif($result['revision_target']!=0)
            {
                $data['target_info']['status_forward_target']='Not Forwarded';
                $data['target_info']['date_targeted']=System_helper::display_date_time($result['date_targeted']);
            }
        }

        $data['title']='HOM Target Finalize';
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

        $years_previous=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id <'.$year_id),$this->config->item('num_year_previous_sell'),0,array('id DESC'));
        $year_current=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id ='.$year_id),1,0,array('id ASC'));
        $years_next=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id >'.$year_id),$this->config->item('num_year_budget_prediction'),0,array('id ASC'));

        //getting previous year data
        $items_previous_year=array();
        $this->db->from($this->config->item('table_bms_hom_budget_hom').' bud');
        $this->db->select('bud.*');
        $this->db->where('year_id',$year_id-1);
        $this->db->where('year_index',0);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$crop_type_id);
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $items_previous_year[$result['variety_id']]=$result;
        }

        //getting current year data
        $items_current=array();
        $this->db->from($this->config->item('table_bms_hom_budget_hom').' bud');
        $this->db->select('bud.*');
        $this->db->where('year_id',$year_id);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$crop_type_id);
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $items_current[$result['variety_id']][$result['year_index']]=$result;
        }

        //getting forward status
        $forwarded=false;
        $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),array('status_forward_target'),array('year_id ='.$year_id,'crop_type_id ='.$crop_type_id),1);
        if($result && $result['status_forward_target']==$this->config->item('system_status_yes'))
        {
            $forwarded=true;
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

            //TODO set sells here
            foreach($years_previous as $index=>$year_previous)
            {
                $item['year'.($index+1).'_sell_quantity']='TODO';
            }

            //TODO set quantity_confirmed here
            $item['year0_quantity_confirmed']=0;

            if(isset($items_current[$item['variety_id']][0]))
            {
                $item['year0_quantity_budget']=$items_current[$item['variety_id']][0]['quantity_budget'];
                $item['year0_quantity_target']=$items_current[$item['variety_id']][0]['quantity_target'];
                $item['year0_quantity_expected']=$items_current[$item['variety_id']][0]['quantity_expected'];
                if($item['year0_quantity_expected']==null)
                {
                    $item['year0_quantity_expected']=0;
                }
                $item['year0_stock_warehouse']=$items_current[$item['variety_id']][0]['stock_warehouse'];
                if($item['year0_stock_warehouse']==null)
                {
                    $item['year0_stock_warehouse']=0;
                }
                $item['year0_stock_outlet']=$items_current[$item['variety_id']][0]['stock_outlet'];
                if($item['year0_stock_outlet']==null)
                {
                    $item['year0_stock_outlet']=0;
                }
                $item['year0_stock_total']=$item['year0_stock_warehouse']+$item['year0_stock_outlet'];
                $item['year0_quantity_available']=$item['year0_stock_total']+$item['year0_quantity_confirmed'];
            }
            else
            {
                $item['year0_quantity_budget']=0;
                $item['year0_quantity_target']=0;
                $item['year0_quantity_expected']=0;
                $item['year0_stock_warehouse']=0;
                $item['year0_stock_outlet']=0;
                $item['year0_stock_total']=0;
                $item['year0_quantity_available']=0;
            }
            if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
            {
                $item['year0_quantity_target_editable']=true;
            }
            else if(!$forwarded)
            {
                if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
                {
                    $item['year0_quantity_target_editable']=true;
                }
                else if(isset($this->permissions['action1']) && ($this->permissions['action1']==1) && ($item['year0_quantity_target']==0))
                {
                    $item['year0_quantity_target_editable']=true;
                }
                else
                {
                    $item['year0_quantity_target_editable']=false;
                }
            }
            else
            {
                $item['year0_quantity_target_editable']=false;
            }

            //foreach($years_next as $index=>$year_next)
            for($i=1;$i<=sizeof($years_next);$i++)
            {
                if(isset($items_current[$item['variety_id']][$i]))
                {
                    $item['year'.$i.'_quantity_budget']=$items_current[$item['variety_id']][$i]['quantity_budget'];
                    $item['year'.$i.'_quantity_target']=$items_current[$item['variety_id']][$i]['quantity_target'];
                }
                else
                {
                    $item['year'.$i.'_quantity_budget']='0';
                    $item['year'.$i.'_quantity_target']='0';
                }
                if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
                {
                    $item['year'.$i.'_quantity_target_editable']=true;
                }
                //else if not forwarded
                //if edit ok
                //else if add and 0 ok
                //else only view
                else if(!$forwarded)
                {
                    if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
                    {
                        $item['year'.$i.'_quantity_target_editable']=true;
                    }
                    else if(isset($this->permissions['action1']) && ($this->permissions['action1']==1) && ($item['year'.$i.'_quantity_target']==0))
                    {
                        $item['year'.$i.'_quantity_target_editable']=true;

                    }
                    else
                    {
                        $item['year'.$i.'_quantity_target_editable']=false;
                    }
                }
                else//not delete and forwarded
                {
                    $item['year'.$i.'_quantity_target_editable']=false;
                }

            }
            
            //set year0 previous year budget
            if(isset($items_previous_year[$item['variety_id']]))
            {
                $item['year0_previous_quantity_budget']=$items_previous_year[$item['variety_id']]['quantity_budget'];
                $item['year0_previous_quantity_target']=$items_previous_year[$item['variety_id']]['quantity_target'];
            }
            else
            {
                $item['year0_previous_quantity_budget']='N/A';
                $item['year0_previous_quantity_target']='N/A';
            }
            //set year 0 quantity


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
            $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),array('status_forward_target'),array('year_id ='.$year_id,'crop_type_id ='.$crop_type_id),1);
            //print_r($result);exit;
            if($result && $result['status_forward_target']==$this->config->item('system_status_yes'))
            {
                $forwarded=true;
            }

            //getting current year data
            $items_current=array();
            $this->db->from($this->config->item('table_bms_hom_budget_hom').' bud');
            $this->db->select('bud.*');
            $this->db->where('year_id',$year_id);
            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
            $this->db->where('v.crop_type_id',$crop_type_id);
            $results=$this->db->get()->result_array();
            //print_r($items);exit;
            foreach($results as $result)
            {
                $items_current[$result['variety_id']][$result['year_index']]=$result;
            }

            $this->db->trans_start();  //DB Transaction Handle START
            foreach($items as $variety_id=>$item)
            {
                foreach($item as $year_index=>$quantity_target)
                {
                    if(isset($items_current[$variety_id][$year_index]))
                    {
                        if($items_current[$variety_id][$year_index]['quantity_target']!=$quantity_target)
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
                                else if(isset($this->permissions['action1']) && ($this->permissions['action1']==1) && ($items_current[$variety_id][$year_index]['quantity_target']==0))
                                {
                                    $editable=true;
                                }
                            }
                            if($editable)
                            {
                                $this->db->where('id',$items_current[$variety_id][$year_index]['id']);
                                $this->db->set('revision_target','revision_target+1',false);
                                $this->db->set('quantity_target',$quantity_target);
                                $this->db->set('date_updated_target',$time);
                                $this->db->set('user_updated_target',$user->user_id);
                                $this->db->update($this->config->item('table_bms_hom_budget_hom'));
                            }
                        }
                    }
                    else
                    {
                        if((isset($this->permissions['action3']) && ($this->permissions['action3']==1))||(!$forwarded))
                        {
                            $data=array();
                            $data['year_id']=$year_id;
                            $data['variety_id']=$variety_id;
                            $data['year_index']=$year_index;

                            $data['quantity_target']=$quantity_target;
                            $data['revision_target']=1;
                            $data['date_targeted'] = $time;
                            $data['user_targeted'] = $user->user_id;

                            $data['quantity_budget']=0;
                            $data['revision_budget']=0;
                            $data['date_budgeted'] = $time;
                            $data['user_budgeted'] = $user->user_id;

                            $data['quantity_expected']=0;
                            $data['revision_quantity_expected']=0;
                            $data['date_quantity_expected'] = $time;
                            $data['user_quantity_expected'] = $user->user_id;
                            Query_helper::add($this->config->item('table_bms_hom_budget_hom'),$data,false);
                        }
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
        $forwarded=false;
        $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),array('id','status_forward_target'),array('year_id ='.$year_id,'crop_type_id ='.$crop_type_id),1);
        if($result && $result['status_forward_target']==$this->config->item('system_status_yes'))
        {
            $forwarded=true;
        }
        if($forwarded)
        {
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>'');
            $ajax['system_message']="This Target already Forwarded";
            $this->json_return($ajax);
        }
        else if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1))||(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            if($result)//not possible
            {
                $data=array();
                $data['status_forward_target']=$this->config->item('system_status_yes');
                $data['date_forward_target'] = $time;
                $data['user_forward_target'] = $user->user_id;
                Query_helper::update($this->config->item('table_bms_hom_forward'),$data,array('id='.$result['id']));
            }
            else
            {
                $data=array();
                $data['year_id']=$year_id;
                $data['crop_type_id']=$crop_type_id;
                $data['status_forward_target']=$this->config->item('system_status_yes');
                $data['date_forward_target'] = $time;
                $data['user_forward_target'] = $user->user_id;
                Query_helper::add($this->config->item('table_bms_hom_forward'),$data,false);
            }
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>'');
            $ajax['system_message']=$this->lang->line("MSG_SAVED_SUCCESS");
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
}
