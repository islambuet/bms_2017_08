<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Di_budget_di extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Di_budget_di');
        $this->controller_url='di_budget_di';
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
            $data['title']="DI Budget Search";
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
        if(!($reports['division_id']>0))
        {
            $ajax['status']=false;
            $ajax['system_message']='Please Select a Zone';
            $this->json_return($ajax);
        }
        //get my outlet ids and territory names
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
        //budget status
        $this->db->from($this->config->item('table_bms_di_budget_di').' bud');
        $this->db->select('bud.*');
        $this->db->select('forward.status_forward_budget,forward.date_forward_budget,forward.user_forward_budget');
        $this->db->where('bud.year_id',$reports['year_id']);
        $this->db->where('bud.division_id',$reports['division_id']);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$reports['crop_type_id']);
        $this->db->join($this->config->item('table_bms_di_forward').' forward','forward.year_id = '.$reports['year_id'].' and forward.division_id = '.$reports['division_id'].' and forward.crop_type_id = '.$reports['crop_type_id'],'LEFT');
        $this->db->order_by('bud.revision_budget','DESC');
        $result=$this->db->get()->row_array();
        $data['budget_info']['status_budget']='Not Done';
        $data['budget_info']['date_budget']='N/A';
        $data['budget_info']['user_budget']='N/A';
        $data['budget_info']['date_forward']='N/A';
        $data['budget_info']['user_forward']='N/A';
        if($result)
        {
            //$data['budget_info']=$result;
            if($result['user_updated_budget']>0)
            {
                $result['user_budgeted']=$result['user_updated_budget'];
                $result['date_budget']=$result['date_updated_budget'];
            }
            $user_ids=array();
            $user_ids[$result['user_budgeted']]=$result['user_budgeted'];
            if($result['user_forward_budget']>0)
            {
                $user_ids[$result['user_forward_budget']]=$result['user_forward_budget'];
            }
            $this->db->from($this->config->item('table_login_setup_user_info').' ui');
            $this->db->select('ui.name,ui.user_id');
            $this->db->where('ui.revision',1);
            $this->db->where_in('ui.user_id',$user_ids);
            $users=$this->db->get()->result_array();
            foreach($users as $u)
            {
                if($u['user_id']==$result['user_budgeted'])
                {
                    $data['budget_info']['user_budget']=$u['name'];
                }
                if($u['user_id']==$result['user_forward_budget'])
                {
                    $data['budget_info']['user_forward']=$u['name'];
                }
            }


            if($result['status_forward_budget']&& $result['status_forward_budget']==$this->config->item('system_status_yes'))
            {
                $data['budget_info']['status_budget']='Forwarded';
                $data['budget_info']['date_forward']=System_helper::display_date_time($result['date_forward_budget']);
            }
            else
            {
                $data['budget_info']['status_budget']='Not Forwarded';
            }
            $data['budget_info']['date_budget']=System_helper::display_date_time($result['date_budgeted']);
        }

        $data['title']='DI Budget';
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


        //get zone ids
        $results=Query_helper::get_info($this->config->item('table_login_setup_location_zones'),array('id value','name text'),array('division_id ='.$division_id));
        $areas=array();
        $area_ids=array('0');
        foreach($results as $result)
        {
            $area_ids[]=$result['value'];
            $areas[$result['value']]=$result;
        }
        //get areas forward status
        $this->db->from($this->config->item('table_bms_zi_forward').' area_forward');
        $this->db->select('area_forward.status_forward_budget,area_forward.zone_id');
        $this->db->where('area_forward.year_id',$year_id);
        $this->db->where_in('area_forward.zone_id',$area_ids);
        $this->db->where('area_forward.crop_type_id',$crop_type_id);
        $this->db->where('area_forward.status_forward_budget',$this->config->item('system_status_yes'));
        $results=$this->db->get()->result_array();
        $area_forward_status=array();
        foreach($results as $result)
        {
            $area_forward_status[$result['zone_id']]=$result['zone_id'];
        }
        //get areas current budget
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
            $area_budget[$result['variety_id']][$result['zone_id']][$result['year_index']]=$result['quantity_budget'];
        }

        //TODO get sells from pos for previous years budget

        //getting previous year data
        $items_previous_year=array();
        $this->db->from($this->config->item('table_bms_di_budget_di').' bud');
        $this->db->select('bud.*');
        $this->db->where('year_id',$year_id-1);
        $this->db->where('division_id',$division_id);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$crop_type_id);
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $items_previous_year[$result['variety_id']][$result['year_index']]=$result;
        }

        //getting current year data
        $items_current=array();
        $this->db->from($this->config->item('table_bms_di_budget_di').' bud');
        $this->db->select('bud.*');
        $this->db->where('year_id',$year_id);
        $this->db->where('division_id',$division_id);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$crop_type_id);
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $items_current[$result['variety_id']][$result['year_index']]=$result;
        }
        //getting forward status
        $forwarded=false;
        $result=Query_helper::get_info($this->config->item('table_bms_di_forward'),array('status_forward_budget'),array('year_id ='.$year_id,'division_id ='.$division_id,'crop_type_id ='.$crop_type_id),1);
        if($result && $result['status_forward_budget']==$this->config->item('system_status_yes'))
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

            //foreach($years_next as $index=>$year_next)
            for($i=0;$i<=sizeof($years_next);$i++)
            {
                $item['year'.$i.'_area_quantity']=0;

                if(isset($items_current[$item['variety_id']][$i]))
                {
                    $item['year'.$i.'_budget_quantity']=$items_current[$item['variety_id']][$i]['quantity_budget'];
                }
                else
                {
                    $item['year'.$i.'_budget_quantity']='0';
                }
                if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
                {
                    $item['year'.$i.'_budget_quantity_editable']=true;
                }
                //else if not forwarded
                //if edit ok
                //else if add and 0 ok
                //else only view
                else if(!$forwarded)
                {
                    if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
                    {
                        $item['year'.$i.'_budget_quantity_editable']=true;
                    }
                    else if(isset($this->permissions['action1']) && ($this->permissions['action1']==1) && ($item['year'.$i.'_budget_quantity']==0))
                    {
                        $item['year'.$i.'_budget_quantity_editable']=true;

                    }
                    else
                    {
                        $item['year'.$i.'_budget_quantity_editable']=false;
                    }
                }
                else//not delete and forwarded
                {
                    $item['year'.$i.'_budget_quantity_editable']=false;
                }

            }
            foreach($areas as $area_id=>$area)
            {
                if(isset($area_budget[$item['variety_id']][$area_id]))
                {
                    if(isset($area_forward_status[$area_id]))
                    {
                        foreach($area_budget[$item['variety_id']][$area_id] as $year_index=>$quantity)
                        {
                            if($year_index==0)
                            {
                                $item['area_quantity_'.$area_id]=$quantity;
                            }
                            if(isset($item['year'.$year_index.'_area_quantity']))//for safety if next years increase
                            {
                                $item['year'.$year_index.'_area_quantity']+=$quantity;
                            }
                        }
                    }
                    else
                    {
                        $item['area_quantity_'.$area_id]='NF';
                    }

                }
                else
                {
                    $item['area_quantity_'.$area_id]='ND';
                }
            }

            //set year0 previous year budget
            if(isset($items_previous_year[$item['variety_id']][0]))
            {
                $item['year0_previous_quantity']=$items_previous_year[$item['variety_id']][0]['quantity_budget'];
            }
            else
            {
                $item['year0_previous_quantity']='N/A';
            }
            //set year0 previous year prediction
            if(isset($items_previous_year[$item['variety_id']][1]))
            {
                $item['year0_previous_prediction']=$items_previous_year[$item['variety_id']][1]['quantity_budget'];
            }
            else
            {
                $item['year0_previous_prediction']='N/A';
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
            $division_id=$this->input->post('division_id');
            $year_id=$this->input->post('year_id');
            $crop_type_id=$this->input->post('crop_type_id');
            $items=$this->input->post('items');
            $forwarded=false;
            $result=Query_helper::get_info($this->config->item('table_bms_di_forward'),array('status_forward_budget'),array('year_id ='.$year_id,'division_id ='.$division_id,'crop_type_id ='.$crop_type_id),1);
            if($result && $result['status_forward_budget']==$this->config->item('system_status_yes'))
            {
                $forwarded=true;
            }

            //getting current year data
            $items_current=array();
            $this->db->from($this->config->item('table_bms_di_budget_di').' bud');
            $this->db->select('bud.*');
            $this->db->where('year_id',$year_id);
            $this->db->where('division_id',$division_id);
            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
            $this->db->where('v.crop_type_id',$crop_type_id);
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $items_current[$result['variety_id']][$result['year_index']]=$result;
            }

            $this->db->trans_start();  //DB Transaction Handle START
            foreach($items as $variety_id=>$item)
            {
                foreach($item as $year_index=>$quantity_budget)
                {
                    if(isset($items_current[$variety_id][$year_index]))
                    {
                        if($items_current[$variety_id][$year_index]['quantity_budget']!=$quantity_budget)
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
                                else if(isset($this->permissions['action1']) && ($this->permissions['action1']==1) && ($items_current[$variety_id][$year_index]['quantity_budget']==0))
                                {
                                    $editable=true;

                                }
                            }
                            if($editable)
                            {
                                $this->db->where('id',$items_current[$variety_id][$year_index]['id']);
                                $this->db->set('revision_budget','revision_budget+1',false);
                                $this->db->set('quantity_budget',$quantity_budget);
                                $this->db->set('date_updated_budget',$time);
                                $this->db->set('user_updated_budget',$user->user_id);
                                $this->db->update($this->config->item('table_bms_di_budget_di'));
                            }
                        }

                    }
                    else
                    {
                        if((isset($this->permissions['action3']) && ($this->permissions['action3']==1))||(!$forwarded))
                        {
                            $data=array();
                            $data['year_id']=$year_id;
                            $data['division_id']=$division_id;
                            $data['variety_id']=$variety_id;
                            $data['year_index']=$year_index;
                            $data['quantity_budget']=$quantity_budget;
                            $data['revision_budget']=1;
                            $data['date_budgeted'] = $time;
                            $data['user_budgeted'] = $user->user_id;
                            Query_helper::add($this->config->item('table_bms_di_budget_di'),$data,false);
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
        $division_id=$this->input->post('division_id');
        $year_id=$this->input->post('year_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $forwarded=false;
        $result=Query_helper::get_info($this->config->item('table_bms_di_forward'),array('status_forward_budget'),array('year_id ='.$year_id,'division_id ='.$division_id,'crop_type_id ='.$crop_type_id),1);
        if($result && $result['status_forward_budget']==$this->config->item('system_status_yes'))
        {
            $forwarded=true;
        }
        if($forwarded)
        {
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>'');
            $ajax['system_message']="This Budget already Forwarded";
            $this->json_return($ajax);
        }
        else if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1))||(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            if($result)//not possible
            {
                $data=array();
                $data['status_forward_budget']=$this->config->item('system_status_yes');
                $data['date_forward_budget'] = $time;
                $data['user_forward_budget'] = $user->user_id;
                Query_helper::update($this->config->item('table_bms_di_forward'),$data,array('id='.$result['id']));
            }
            else
            {
                $data=array();
                $data['year_id']=$year_id;
                $data['division_id']=$division_id;
                $data['crop_type_id']=$crop_type_id;
                $data['status_forward_budget']=$this->config->item('system_status_yes');
                $data['date_forward_budget'] = $time;
                $data['user_forward_budget'] = $user->user_id;
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
