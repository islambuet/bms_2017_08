<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Di_target_finalize_zi extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Di_target_finalize_zi');
        $this->controller_url='di_target_finalize_zi';
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
            $data['title']="DI Assign Target to ZI Search";
            $data['fiscal_years']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id ASC'));
            $data['divisions']=Query_helper::get_info($this->config->item('table_login_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
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
        //get my outlet ids and Zone names
        $this->db->from($this->config->item('table_login_csetup_customer').' outlet');
        $this->db->where('outlet.status',$this->config->item('system_status_active'));
        $this->db->select('outlet.id');
        $this->db->select('zone.name text,zone.id value');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' out_info','out_info.customer_id = outlet.id and out_info.revision = 1','INNER');
        $this->db->where('out_info.type',$this->config->item('system_customer_type_outlet_id'));

        $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = out_info.district_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_territories').' t','t.id = d.territory_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_zones').' zone','zone.id = t.zone_id','INNER');

        $this->db->where('zone.division_id',$reports['division_id']);
        $this->db->order_by('zone.ordering','ASC');
        $results=$this->db->get()->result_array();
        $data['areas']=array();
        $outlet_ids=array('0');
        foreach($results as $result)
        {
            $outlet_ids[]=$result['id'];
            $data['areas'][$result['value']]=$result;
        }
        //market survey size
        $this->db->from($this->config->item('table_bms_setup_market_size').' ms');
        $this->db->select('SUM(ms.size_total) size_total,SUM(ms.size_arm) size_arm,COUNT(ms.outlet_id) num_outlet');
        $this->db->where('ms.revision',1);
        $this->db->where_in('ms.outlet_id',$outlet_ids);
        $this->db->where('ms.crop_type_id',$reports['crop_type_id']);
        $data['market_survey']=$this->db->get()->row_array();
        //print_r($data['market_survey']);exit;
        //budget status
        $this->db->from($this->config->item('table_bms_zi_budget_zi').' bud');
        $this->db->select('bud.*');
        $this->db->select('forward.status_forward_assign_target,forward.date_forward_assign_target,forward.user_forward_assign_target');
        $this->db->where('bud.year_id',$reports['year_id']);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$reports['crop_type_id']);
        $this->db->join($this->config->item('table_bms_di_forward').' forward','forward.year_id = '.$reports['year_id'].' and forward.crop_type_id = '.$reports['crop_type_id'],'LEFT');
        $this->db->order_by('bud.revision_target','DESC');
        $result=$this->db->get()->row_array();
        //print_r($result);exit;
        $data['assign_target_info']['status_assign_target']='Not Done';
        $data['assign_target_info']['date_assign_target']='N/A';
        $data['assign_target_info']['user_assign_target']='N/A';
        $data['assign_target_info']['date_forward']='N/A';
        $data['assign_target_info']['user_forward']='N/A';
        if($result)
        {
            //$data['assign_target_info']=$result;
            if($result['user_updated_target']>0)
            {
                $result['user_targeted']=$result['user_updated_target'];
                $result['date_targeted']=$result['date_updated_target'];
            }
            $user_ids=array();
            $user_ids[$result['user_targeted']]=$result['user_targeted'];
            if($result['user_forward_assign_target']>0)
            {
                $user_ids[$result['user_forward_assign_target']]=$result['user_forward_assign_target'];
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
                    $data['assign_target_info']['user_assign_target']=$u['name'];
                }
                if($u['user_id']==$result['user_forward_assign_target'])
                {
                    $data['assign_target_info']['user_forward']=$u['name'];
                }
            }


            if($result['status_forward_assign_target'] && $result['status_forward_assign_target']==$this->config->item('system_status_yes'))
            {
                $data['assign_target_info']['status_assign_target']='Forwarded';
                $data['assign_target_info']['date_forward']=System_helper::display_date_time($result['date_forward_assign_target']);
                $data['assign_target_info']['date_assign_target']=System_helper::display_date_time($result['date_targeted']);
            }
            elseif($result['revision_target']!=0)
            {
                $data['assign_target_info']['status_assign_target']='Not Forwarded';
                $data['assign_target_info']['date_assign_target']=System_helper::display_date_time($result['date_targeted']);
            }
        }

        $data['title']='DI Assign Target to ZI';
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
        $division_id=$this->input->post('division_id');
        $year_id=$this->input->post('year_id');
        $crop_type_id=$this->input->post('crop_type_id');

        $years_previous=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id <'.$year_id),$this->config->item('num_year_previous_sell'),0,array('id DESC'));
        $year_current=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id ='.$year_id),1,0,array('id ASC'));
        $years_next=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id >'.$year_id),$this->config->item('num_year_budget_prediction'),0,array('id ASC'));
        //get zones ids
        $results=Query_helper::get_info($this->config->item('table_login_setup_location_zones'),array('id value','name text'),array('division_id ='.$division_id));
        $areas=array();
        $area_ids=array('0');
        foreach($results as $result)
        {
            $area_ids[]=$result['value'];
            $areas[$result['value']]=$result;
        }

        //getting di current year data
        $items_current_di=array();
        $this->db->from($this->config->item('table_bms_di_budget_di').' bud');
        $this->db->select('bud.*');
        $this->db->where('year_id',$year_id);
        $this->db->where('division_id',$division_id);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$crop_type_id);
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $items_current_di[$result['variety_id']][$result['year_index']]=$result;
        }

        //getting di budget forward data
        $this->db->select('*');
        $this->db->from($this->config->item('table_bms_di_forward'));
        $this->db->where('year_id',$year_id);
        $this->db->where('crop_type_id',$crop_type_id);
        $this->db->where('division_id',$division_id);
        $result=$this->db->get()->row_array();

        $di_forward_status_budget=false;
        if($result && $result['status_forward_budget']==$this->config->item('system_status_yes'))
        {
            $di_forward_status_budget=true;
        }
        $di_forward_status_assign_target_zi=false;
        if($result && $result['status_forward_assign_target']==$this->config->item('system_status_yes'))
        {
            $di_forward_status_assign_target_zi=true;
        }

        //getting hom target forward data
        $this->db->select('*');
        $this->db->from($this->config->item('table_bms_hom_forward'));
        $this->db->where('year_id',$year_id);
        $this->db->where('crop_type_id',$crop_type_id);
        $result=$this->db->get()->row_array();
        $hom_forward_status_assign_target_di=false;
        if($result && $result['status_forward_assign_target']==$this->config->item('system_status_yes'))
        {
            $hom_forward_status_assign_target_di=true;
        }

        //getting areas(zones) current year data
        $this->db->from($this->config->item('table_bms_zi_budget_zi').' bud');
        $this->db->select('bud.*');
        $this->db->where('bud.year_id',$year_id);
        $this->db->where_in('bud.zone_id',$area_ids);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$crop_type_id);
        $results=$this->db->get()->result_array();
        $area_budget=array();
        foreach($results as $result)
        {
            $area_budget[$result['variety_id']][$result['year_index']][$result['zone_id']]=$result;
        }

        //get areas forward status
        $this->db->from($this->config->item('table_bms_zi_forward'));
        $this->db->select('status_forward_budget,zone_id');
        $this->db->where('year_id',$year_id);
        $this->db->where_in('zone_id',$area_ids);
        $this->db->where('crop_type_id',$crop_type_id);
        $this->db->where('status_forward_budget',$this->config->item('system_status_yes'));
        $results=$this->db->get()->result_array();
        $area_forward_status=array();
        foreach($results as $result)
        {
            $area_forward_status[$result['zone_id']]=$result['zone_id'];
        }

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

            for($i=0;$i<=count($years_next);$i++)
            {
                if(isset($items_current_di[$result['id']][$i]))
                {
                    if($di_forward_status_budget)
                    {
                        $item['year'.$i.'_di_quantity_budget']=$items_current_di[$result['id']][$i]['quantity_budget'];
                    }
                    else
                    {
                        $item['year'.$i.'_di_quantity_budget']='N/F';
                    }
                    if($hom_forward_status_assign_target_di)
                    {
                        $item['year'.$i.'_di_quantity_target']=$items_current_di[$result['id']][$i]['quantity_target'];
                    }
                    else
                    {
                        $item['year'.$i.'_di_quantity_target']='N/F';
                    }
                }
                else
                {
                    $item['year'.$i.'_di_quantity_budget']='N/D';
                    $item['year'.$i.'_di_quantity_target']='N/D';
                }

                foreach($areas as $area)
                {
                    if(isset($area_budget[$result['id']][$i][$area['value']]))
                    {
                        if(isset($area_forward_status[$area['value']]))
                        {
                            $item['year'.$i.'_area'.$area['value'].'_quantity_budget']=$area_budget[$result['id']][$i][$area['value']]['quantity_budget'];
                        }
                        else
                        {
                            $item['year'.$i.'_area'.$area['value'].'_quantity_budget']='N/F';
                        }
                        $item['year'.$i.'_area'.$area['value'].'_quantity_target']=$area_budget[$result['id']][$i][$area['value']]['quantity_target'];
                    }
                    else
                    {
                        $item['year'.$i.'_area'.$area['value'].'_quantity_budget']='N/D';
                        $item['year'.$i.'_area'.$area['value'].'_quantity_target']='0';
                    }

                    if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
                    {
                        $item['year'.$i.'_area'.$area['value'].'_quantity_target_editable']=true;
                    }
                    //else if not forwarded
                    //if edit ok
                    //else if add and 0 ok
                    //else only view
                    else if(!$di_forward_status_assign_target_zi)
                    {
                        if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
                        {
                            $item['year'.$i.'_area'.$area['value'].'_quantity_target_editable']=true;
                        }
                        else if(isset($this->permissions['action1']) && ($this->permissions['action1']==1) && ($item['year'.$i.'_area'.$area['value'].'_quantity_target']==0))
                        {
                            $item['year'.$i.'_area'.$area['value'].'_quantity_target_editable']=true;

                        }
                        else
                        {
                            $item['year'.$i.'_area'.$area['value'].'_quantity_target_editable']=false;
                        }
                    }
                    else//not delete and forwarded
                    {
                        $item['year'.$i.'_area'.$area['value'].'_quantity_target_editable']=false;
                    }
                }
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
            $division_id=$this->input->post('division_id');
            $items=$this->input->post('items');
            $forwarded=false;
            $result=Query_helper::get_info($this->config->item('table_bms_di_forward'),array('status_forward_assign_target'),array('year_id ='.$year_id,'division_id ='.$division_id,'crop_type_id ='.$crop_type_id),1);
            //print_r($result);exit;
            if($result && $result['status_forward_assign_target']==$this->config->item('system_status_yes'))
            {
                $forwarded=true;
            }

            //getting area ids
            $results=Query_helper::get_info($this->config->item('table_login_setup_location_zones'),array('id value','name text'),array('division_id ='.$division_id));
            $area_ids=array('0');
            foreach($results as $result)
            {
                $area_ids[]=$result['value'];
            }

            //getting current year data
            $items_current=array();
            $this->db->from($this->config->item('table_bms_zi_budget_zi').' bud');
            $this->db->select('bud.*');
            $this->db->where('year_id',$year_id);
            $this->db->where_in('bud.zone_id',$area_ids);
            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
            $this->db->where('v.crop_type_id',$crop_type_id);
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $items_current[$result['variety_id']][$result['year_index']][$result['zone_id']]=$result;
            }
            $this->db->trans_start();  //DB Transaction Handle START

            foreach($items as $variety_id=>$year_indexes)
            {
                foreach($year_indexes as $year_index=>$areas)
                {
                    foreach($areas as $zone_id=>$quantity_target)
                    {
                        if(isset($items_current[$variety_id][$year_index][$zone_id]))
                        {
                            if($items_current[$variety_id][$year_index][$zone_id]['quantity_target']!=$quantity_target)
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
                                    else if(isset($this->permissions['action1']) && ($this->permissions['action1']==1) && ($items_current[$variety_id][$year_index][$zone_id]['quantity_target']==0))
                                    {
                                        $editable=true;
                                    }
                                }
                                if($editable)
                                {
                                    $this->db->where('id',$items_current[$variety_id][$year_index][$zone_id]['id']);
                                    $this->db->set('revision_target','revision_target+1',false);
                                    $this->db->set('quantity_target',$quantity_target);
                                    $this->db->set('date_updated_target',$time);
                                    $this->db->set('user_updated_target',$user->user_id);
                                    $this->db->update($this->config->item('table_bms_zi_budget_zi'));
                                }
                            }
                        }
                        else
                        {
                            if((isset($this->permissions['action3']) && ($this->permissions['action3']==1))||(!$forwarded))
                            {
                                $data=array();
                                $data['year_id']=$year_id;
                                $data['zone_id']=$zone_id;
                                $data['variety_id']=$variety_id;
                                $data['year_index']=$year_index;
                                $data['quantity_budget']=0;
                                $data['revision_budget']=0;
                                $data['date_budgeted'] = $time;
                                $data['user_budgeted'] = $user->user_id;
                                $data['quantity_target']=$quantity_target;
                                $data['revision_target']=1;
                                $data['date_targeted'] = $time;
                                $data['user_targeted'] = $user->user_id;
                                Query_helper::add($this->config->item('table_bms_zi_budget_zi'),$data,false);
                            }
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
        $division_id=$this->input->post('division_id');
        $forwarded=false;
        $result=Query_helper::get_info($this->config->item('table_bms_di_forward'),array('id','status_forward_assign_target'),array('year_id ='.$year_id,'division_id ='.$division_id,'crop_type_id ='.$crop_type_id),1);
        if($result && $result['status_forward_assign_target']==$this->config->item('system_status_yes'))
        {
            $forwarded=true;
        }
        if($forwarded)
        {
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>'');
            $ajax['system_message']="This Target Assigned to ZI already Forwarded";
            $this->json_return($ajax);
        }
        else if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1))||(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            if($result)//not possible
            {
                $data=array();
                $data['status_forward_assign_target']=$this->config->item('system_status_yes');
                $data['date_forward_assign_target'] = $time;
                $data['user_forward_assign_target'] = $user->user_id;
                Query_helper::update($this->config->item('table_bms_di_forward'),$data,array('id='.$result['id']));
            }
            else
            {
                $data=array();
                $data['year_id']=$year_id;
                $data['crop_type_id']=$crop_type_id;
                $data['division_id']=$division_id;
                $data['status_forward_assign_target']=$this->config->item('system_status_yes');
                $data['date_forward_assign_target'] = $time;
                $data['user_forward_assign_target'] = $user->user_id;
                Query_helper::add($this->config->item('table_bms_di_forward'),$data,false);
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
