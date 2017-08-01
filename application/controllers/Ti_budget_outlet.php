<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ti_budget_outlet extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Ti_budget_outlet');
        $this->controller_url='ti_budget_outlet';
        $this->locations=User_helper::get_locations();
        if(!($this->locations))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('MSG_LOCATION_NOT_ASSIGNED_OR_INVALID');
            $this->json_return($ajax);
        }

        //$this->load->model("sys_module_task_model");
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
        else
        {
            $this->system_search();
        }
    }
    private function system_search()
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['title']="Outlet Budget Search";
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
                    if($this->locations['territory_id']>0)
                    {
                        $data['districts']=Query_helper::get_info($this->config->item('table_login_setup_location_districts'),array('id value','name text'),array('territory_id ='.$this->locations['territory_id']));
                        if($this->locations['district_id']>0)
                        {
                            $data['outlets']=Query_helper::get_info($this->config->item('table_login_csetup_cus_info'),array('customer_id value','name text'),array('type =1','revision =1','district_id ='.$this->locations['district_id']),0,0,array('ordering ASC'));
                        }
                    }

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
        if(!($reports['outlet_id']>0))
        {
            $ajax['status']=false;
            $ajax['system_message']='Please Select a Outlet';
            $this->json_return($ajax);
        }
        $this->db->from($this->config->item('table_bms_setup_market_size').' ms');
        $this->db->select('ms.*');
        $this->db->where('ms.revision',1);
        $this->db->where('ms.outlet_id',$reports['outlet_id']);
        $this->db->where('ms.crop_type_id',$reports['crop_type_id']);
        $data['market_survey']=$this->db->get()->row_array();
        $data['title']='Outlet Budget';
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


        $outlet_id=$this->input->post('outlet_id');
        $year_id=$this->input->post('year_id');
        $crop_type_id=$this->input->post('crop_type_id');

        $years_previous=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id <'.$year_id),$this->config->item('num_year_previous_sell'),0,array('id DESC'));
        $year_current=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id ='.$year_id),1,0,array('id ASC'));
        $years_next=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id >'.$year_id),$this->config->item('num_year_budget_prediction'),0,array('id ASC'));
        //TODO get sells from pos for previous years budget

        //getting previous year data
        $this->db->from($this->config->item('table_bms_ti_budget_outlet').' bud');
        $this->db->select('bud.*');
        $this->db->where('year_id',$year_id-1);
        $this->db->where('outlet_id',$outlet_id);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$crop_type_id);
        $results=$this->db->get()->result_array();

        $previous_year_data=array();
        foreach($results as $result)
        {
            $previous_year_data[$result['variety_id']][$result['year_index']]=$result;
        }

        //getting previous data
        $this->db->from($this->config->item('table_bms_ti_budget_outlet').' bud');
        $this->db->select('bud.*');
        $this->db->where('year_id',$year_id);
        $this->db->where('outlet_id',$outlet_id);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$crop_type_id);
        $results=$this->db->get()->result_array();

        $items_old=array();
        foreach($results as $result)
        {
            $items_old[$result['variety_id']][$result['year_index']]=$result;
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

            //set year0 previous year budget
            if(isset($previous_year_data[$item['variety_id']][0]))
            {
                $item['year0_previous_quantity']=$previous_year_data[$item['variety_id']][0]['quantity_budget'];
            }
            else
            {
                $item['year0_previous_quantity']='N/A';
            }
            //set year0 previous year prediction
            if(isset($previous_year_data[$item['variety_id']][1]))
            {
                $item['year0_previous_prediction']=$previous_year_data[$item['variety_id']][1]['quantity_budget'];
            }
            else
            {
                $item['year0_previous_prediction']='N/A';
            }
            //set year 0 quantity
            if(isset($items_old[$item['variety_id']][0]))
            {
                $item['year0_budget_quantity']=$items_old[$item['variety_id']][0]['quantity_budget'];
            }
            else
            {
                $item['year0_budget_quantity']='0';
            }
            if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
            {
                $item['year0_budget_quantity_editable']=true;
            }
            elseif(isset($this->permissions['action1']) && ($this->permissions['action1']==1))
            {
                if((isset($items_old[$item['variety_id']][0]))&&($items_old[$item['variety_id']][0]['quantity_budget']!=0))
                {
                    $item['year0_budget_quantity_editable']=false;
                }
                else
                {
                    $item['year0_budget_quantity_editable']=true;
                }
            }
            else
            {
                $item['year0_budget_quantity_editable']=false;
            }
            foreach($years_next as $index=>$year_next)
            {
                if(isset($items_old[$item['variety_id']][$index+1]))
                {
                    $item['year'.($index+1).'_budget_quantity']=$items_old[$item['variety_id']][$index+1]['quantity_budget'];
                }
                else
                {
                    $item['year'.($index+1).'_budget_quantity']='0';
                }
                if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
                {
                    $item['year'.($index+1).'_budget_quantity_editable']=true;
                }
                elseif(isset($this->permissions['action1']) && ($this->permissions['action1']==1))
                {
                    if((isset($items_old[$item['variety_id']][$index+1]))&&($items_old[$item['variety_id']][$index+1]['quantity_budget']!=0))
                    {
                        $item['year'.($index+1).'_budget_quantity_editable']=false;
                    }
                    else
                    {
                        $item['year'.($index+1).'_budget_quantity_editable']=true;
                    }
                }
                else
                {
                    $item['year'.($index+1).'_budget_quantity_editable']=false;
                }

            }
            $items[]=$item;
        }
        $this->json_return($items);


    }
    private function system_save()
    {
        if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $user = User_helper::get_user();
            $time=time();
            $outlet_id=$this->input->post('outlet_id');
            $year_id=$this->input->post('year_id');
            $crop_type_id=$this->input->post('crop_type_id');
            $items=$this->input->post('items');
            //getting previous data
            $this->db->from($this->config->item('table_bms_ti_budget_outlet').' bud');
            $this->db->select('bud.*');
            $this->db->where('year_id',$year_id);
            $this->db->where('outlet_id',$outlet_id);
            $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
            $this->db->where('v.crop_type_id',$crop_type_id);
            $results=$this->db->get()->result_array();

            $items_old=array();
            foreach($results as $result)
            {
                $items_old[$result['variety_id']][$result['year_index']]=$result;
            }

            $this->db->trans_start();  //DB Transaction Handle START
            foreach($items as $variety_id=>$item)
            {
                foreach($item as $year_index=>$quantity_budget)
                {
                    if(isset($items_old[$variety_id][$year_index]))
                    {
                        if($items_old[$variety_id][$year_index]['quantity_budget']!=$quantity_budget)
                        {
                            if(($items_old[$variety_id][$year_index]['quantity_budget']==0)||(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
                            {
                                $this->db->where('id',$items_old[$variety_id][$year_index]['id']);
                                $this->db->set('revision_budget','revision_budget+1',false);
                                $this->db->set('quantity_budget',$quantity_budget);
                                $this->db->set('date_updated_budget',$time);
                                $this->db->set('user_updated_budget',$user->user_id);
                                $this->db->update($this->config->item('table_bms_ti_budget_outlet'));

                            }
                        }

                    }
                    else
                    {
                        $data=array();
                        $data['year_id']=$year_id;
                        $data['outlet_id']=$outlet_id;
                        $data['variety_id']=$variety_id;
                        $data['year_index']=$year_index;
                        $data['quantity_budget']=$quantity_budget;
                        $data['revision_budget']=1;
                        $data['date_budgeted'] = $time;
                        $data['user_budgeted'] = $user->user_id;
                        Query_helper::add($this->config->item('table_bms_ti_budget_outlet'),$data,false);
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
}
