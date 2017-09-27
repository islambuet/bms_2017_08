<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ti_target_month extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Ti_target_month');
        $this->controller_url='ti_target_month';
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
            $data['title']="TI Month Target Search";
            $data['fiscal_years']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id ASC'));
            $data['divisions']=Query_helper::get_info($this->config->item('table_login_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['zones']=array();
            $data['territories']=array();
            $data['districts']=array();
            $data['outlets']=array();
            if($this->locations['division_id']>0)
            {
                $data['zones']=Query_helper::get_info($this->config->item('table_login_setup_location_zones'),array('id value','name text'),array('division_id ='.$this->locations['division_id']));
                if($this->locations['zone_id']>0)
                {
                    $data['territories']=Query_helper::get_info($this->config->item('table_login_setup_location_territories'),array('id value','name text'),array('zone_id ='.$this->locations['zone_id']));

                }
            }
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
        if(!($reports['territory_id']>0))
        {
            $ajax['status']=false;
            $ajax['system_message']='Please Select a Territory';
            $this->json_return($ajax);
        }
        //get my outlet ids and outlet names
        $this->db->from($this->config->item('table_login_csetup_customer').' outlet');
        $this->db->where('outlet.status',$this->config->item('system_status_active'));
        $this->db->select('outlet.id value');
        $this->db->select('out_info.name text');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' out_info','out_info.customer_id = outlet.id and out_info.revision = 1','INNER');
        $this->db->where('out_info.type',$this->config->item('system_customer_type_outlet_id'));

        $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = out_info.district_id','INNER');
        $this->db->where('d.territory_id',$reports['territory_id']);
        $this->db->order_by('outlet.id','ASC');
        $results=$this->db->get()->result_array();
        $outlet_ids=array('0');
        foreach($results as $result)
        {
            $outlet_ids[]=$result['value'];
        }
        //market survey size
        $this->db->from($this->config->item('table_bms_setup_market_size').' ms');
        $this->db->select('SUM(ms.size_total) size_total,SUM(ms.size_arm) size_arm,COUNT(ms.outlet_id) num_outlet');
        $this->db->where('ms.revision',1);
        $this->db->where_in('ms.outlet_id',$outlet_ids);
        $this->db->where('ms.crop_type_id',$reports['crop_type_id']);
        $data['market_survey']=$this->db->get()->row_array();
        //print_r($data['market_survey']);exit;
        //target status
        $this->db->from($this->config->item('table_bms_ti_target_month').' mt');
        $this->db->select('mt.*');
        $this->db->select('forward.status_forward_month_target,forward.date_forward_month_target,forward.user_forward_month_target');
        $this->db->where('mt.year_id',$reports['year_id']);
        $this->db->where('mt.territory_id',$reports['territory_id']);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = mt.variety_id','INNER');
        $this->db->where('v.crop_type_id',$reports['crop_type_id']);
        $this->db->join($this->config->item('table_bms_ti_forward').' forward','forward.territory_id = '.$reports['territory_id'].' AND forward.year_id = '.$reports['year_id'].' AND forward.crop_type_id = '.$reports['crop_type_id'],'LEFT');
        $this->db->order_by('mt.revision','DESC');
        $result=$this->db->get()->row_array();

        $data['month_target_info']['status_target']='Not Done';
        $data['month_target_info']['date_target']='N/A';
        $data['month_target_info']['user_target']='N/A';
        $data['month_target_info']['date_forward']='N/A';
        $data['month_target_info']['user_forward']='N/A';
        if($result)
        {
            if($result['user_updated']>0)
            {
                $result['user_targeted']=$result['user_updated'];
                $result['date_targeted']=$result['date_updated'];
            }
            $user_ids=array();
            $user_ids[$result['user_targeted']]=$result['user_targeted'];
            if($result['user_forward_month_target']>0)
            {
                $user_ids[$result['user_forward_month_target']]=$result['user_forward_month_target'];
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
                    $data['month_target_info']['user_target']=$u['name'];
                }
                if($u['user_id']==$result['user_forward_month_target'])
                {
                    $data['month_target_info']['user_forward']=$u['name'];
                }
            }
            if($result['status_forward_month_target'] && $result['status_forward_month_target']==$this->config->item('system_status_yes'))
            {
                $data['month_target_info']['status_target']='Forwarded';
                $data['month_target_info']['date_forward']=System_helper::display_date_time($result['date_forward_month_target']);
                $data['month_target_info']['date_target']=System_helper::display_date_time($result['date_targeted']);
            }
            elseif($result['revision']!=0)
            {
                $data['month_target_info']['status_target']='Not Forwarded';
                $data['month_target_info']['date_target']=System_helper::display_date_time($result['date_targeted']);
            }
        }
        $data['title']='TI Month Target';
        $data['years_previous']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id <'.$reports['year_id']),$this->config->item('num_year_previous_sell'),0,array('id ASC'));
        $data['year_current']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"','id ='.$reports['year_id']),1,0,array('id ASC'));
        $start_month=date('n',$data['year_current']['date_start']);
        $data['starting_month']=$start_month;
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
        $territory_id=$this->input->post('territory_id');
        $zone_id=$this->input->post('zone_id');
        $years_previous=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id <'.$year_id),$this->config->item('num_year_previous_sell'),0,array('id ASC'));
        $year_current=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"','id ='.$year_id),1,0,array('id ASC'));

        //getting TI assign target current year data(When ZI assign target to TI)
        $items_current_ti_assign_target=array();
        $this->db->from($this->config->item('table_bms_ti_budget_ti').' bud');
        $this->db->select('bud.*');
        $this->db->where('year_id',$year_id);
        $this->db->where('territory_id',$territory_id);
        $this->db->where('year_index',0);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$crop_type_id);
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $items_current_ti_assign_target[$result['variety_id']]=$result;
        }

        //getting zi assign target forward data
        $this->db->select('*');
        $this->db->from($this->config->item('table_bms_zi_forward'));
        $this->db->where('year_id',$year_id);
        $this->db->where('crop_type_id',$crop_type_id);
        $this->db->where('zone_id',$zone_id);
        $result=$this->db->get()->row_array();
        $zi_forward_status_assign_target_ti=false;
        if($result && $result['status_forward_assign_target']==$this->config->item('system_status_yes'))
        {
            $zi_forward_status_assign_target_ti=true;
        }

        //getting TI current year data(From Month Target)
        $items_current_ti=array();
        $this->db->from($this->config->item('table_bms_ti_target_month').' mt');
        $this->db->select('mt.*');
        $this->db->where('year_id',$year_id);
        $this->db->where('territory_id',$territory_id);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = mt.variety_id','INNER');
        $this->db->where('v.crop_type_id',$crop_type_id);
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $items_current_ti[$result['variety_id']]=$result;
        }

        //getting TI previous years data(From Month Target)
        $items_previous_years=array();
        $this->db->from($this->config->item('table_bms_ti_target_month').' mt');
        $this->db->select('mt.*');
        $this->db->where('year_id',($year_id-1));
        $this->db->where('territory_id',$territory_id);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = mt.variety_id','INNER');
        $this->db->where('v.crop_type_id',$crop_type_id);
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $items_previous_years[$result['variety_id']][$result['year_id']]=$result;
        }

        //getting ti month target forward data(current year)
        $this->db->select('status_forward_month_target');
        $this->db->from($this->config->item('table_bms_ti_forward'));
        $this->db->where('year_id',$year_id);
        $this->db->where('crop_type_id',$crop_type_id);
        $this->db->where('territory_id',$territory_id);
        $result=$this->db->get()->row_array();
        $ti_forward_status_month_wise_target=false;
        if($result && $result['status_forward_month_target']==$this->config->item('system_status_yes'))
        {
            $ti_forward_status_month_wise_target=true;
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
            if(isset($items_current_ti_assign_target[$result['id']]))
            {
                if($zi_forward_status_assign_target_ti)
                {
                    $item['ti_quantity_assign_target']=$items_current_ti_assign_target[$result['id']]['quantity_target'];
                }
                else
                {
                    $item['ti_quantity_assign_target']='N/F';
                }
            }
            else
            {
                $item['ti_quantity_assign_target']='N/D';
            }
            for($month=1;$month<=12;$month++)
            {
                foreach($years_previous as $year)
                {
                    //TODO set sells here
                    $item['year'.$year['value'].'_month'.$month.'_sell_quantity']='TODO';

                }
                if(isset($items_current_ti[$result['id']]))
                {
                    $item['month'.$month.'_quantity_target']=$items_current_ti[$result['id']]['quantity_target_'.$month];
                }
                else
                {
                    $item['month'.$month.'_quantity_target']=0;
                }

                if(isset($this->permissions['action3']) && ($this->permissions['action3']==1))
                {
                    $item['month'.$month.'_quantity_target_editable']=true;
                }
                //else if not forwarded
                //if edit ok
                //else if add and 0 ok
                //else only view
                else if(!$ti_forward_status_month_wise_target)
                {
                    if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
                    {
                        $item['month'.$month.'_quantity_target_editable']=true;
                    }
                    else if(isset($this->permissions['action1']) && ($this->permissions['action1']==1) && ($item['month'.$month.'_quantity_target']==0))
                    {
                        $item['month'.$month.'_quantity_target_editable']=true;
                    }
                    else
                    {
                        $item['month'.$month.'_quantity_target_editable']=false;
                    }
                }
                else//not delete and forwarded
                {
                    $item['month'.$month.'_quantity_target_editable']=false;
                }
            }
            $items[]=$item;
        }
//        print_r($items);
//        exit;
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
            $territory_id=$this->input->post('territory_id');
            $items=$this->input->post('items');
            $forwarded=false;
            $result=Query_helper::get_info($this->config->item('table_bms_ti_forward'),array('status_forward_month_target'),array('year_id ='.$year_id,'crop_type_id ='.$crop_type_id,'territory_id ='.$territory_id),1);
            if($result && $result['status_forward_month_target']==$this->config->item('system_status_yes'))
            {
                $forwarded=true;
            }

            //getting TI current year data(From Month Target)
            $items_current=array();
            $this->db->from($this->config->item('table_bms_ti_target_month').' mt');
            $this->db->select('mt.*');
            $this->db->where('year_id',$year_id);
            $this->db->where('territory_id',$territory_id);
            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = mt.variety_id','INNER');
            $this->db->where('v.crop_type_id',$crop_type_id);
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $items_current[$result['variety_id']]=$result;
            }
            $this->db->trans_start();  //DB Transaction Handle START
            foreach($items as $variety_id=>$item)
            {
                if(isset($items_current[$variety_id]))
                {
                    $changed=false;
                    for($i=1;$i<=12;$i++)
                    {
                        if($items_current[$variety_id]['quantity_target_'.$i]!=$item['quantity_target_'.$i])
                        {
                            $changed=true;
                            break;
                        }
                    }
                    if($changed)
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
                            else if(isset($this->permissions['action1']) && ($this->permissions['action1']==1) && ($items_current[$variety_id]['revision']==0))
                            {
                                $editable=true;
                            }
                        }
                        if($editable)
                        {
                            $this->db->where('id',$items_current[$variety_id]['id']);
                            $this->db->set('revision','revision+1',false);

                            for($i=1;$i<=12;$i++)
                            {
                                $this->db->set('quantity_target_'.$i,$item['quantity_target_'.$i]);
                            }
                            $this->db->set('date_targeted',$time);
                            $this->db->set('user_targeted',$user->user_id);
                            $this->db->set('date_updated',$time);
                            $this->db->set('user_updated',$user->user_id);

                            $this->db->update($this->config->item('table_bms_ti_target_month'));
                        }
                    }
                }
                else
                {
                    if((isset($this->permissions['action3']) && ($this->permissions['action3']==1))||(!$forwarded))
                    {
                        $item['year_id']=$year_id;
                        $item['territory_id']=$territory_id;
                        $item['variety_id']=$variety_id;
                        $item['revision']=1;
                        $item['date_created'] = $time;
                        $item['user_created'] = $user->user_id;
                        $item['date_targeted'] = $time;
                        $item['user_targeted'] = $user->user_id;
                        Query_helper::add($this->config->item('table_bms_ti_target_month'),$item,false);
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
        $territory_id=$this->input->post('territory_id');
        $forwarded=false;
        $result=Query_helper::get_info($this->config->item('table_bms_ti_forward'),array('id','status_forward_month_target'),array('territory_id ='.$territory_id,'year_id ='.$year_id,'crop_type_id ='.$crop_type_id),1);
        if($result && $result['status_forward_month_target']==$this->config->item('system_status_yes'))
        {
            $forwarded=true;
        }
        if($forwarded)
        {
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>'');
            $ajax['system_message']="This Month Target already Forwarded";
            $this->json_return($ajax);
        }
        else if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1))||(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            if($result)//not possible
            {
                $data=array();
                $data['status_forward_month_target']=$this->config->item('system_status_yes');
                $data['date_forward_month_target'] = $time;
                $data['user_forward_month_target'] = $user->user_id;
                Query_helper::update($this->config->item('table_bms_ti_forward'),$data,array('id='.$result['id']));
            }
            else
            {
                $data=array();
                $data['territory_id']=$territory_id;
                $data['year_id']=$year_id;
                $data['crop_type_id']=$crop_type_id;
                $data['status_forward_month_target']=$this->config->item('system_status_yes');
                $data['date_forward_month_target'] = $time;
                $data['user_forward_month_target'] = $user->user_id;
                Query_helper::add($this->config->item('table_bms_ti_forward'),$data,false);
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
