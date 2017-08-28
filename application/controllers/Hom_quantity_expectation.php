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

    public function index($action="search",$id1=0,$id2=0)
    {
        if($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="edit")
        {
            $this->system_edit($id1,$id2);
        }
        elseif($action=="get_edit_items")
        {
            $this->system_get_edit_items();
        }
        elseif($action=="get_detail_items")
        {
            $this->system_get_edit_items('details');
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="details")
        {
            $this->system_details($id1,$id2);
        }
        elseif($action=="forward")
        {
            $this->system_forward($id1,$id2);
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
            $data['fiscal_years']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id ASC'));
            $data['title']="Quantity Expectation";
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
        $crop_type=Query_helper::get_info($this->config->item('table_login_setup_classification_crop_types'),array('id value','name text'),array('id ='.$reports['crop_type_id']),1);
        $data['years']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id ASC'));
        $data['year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id ='.$reports['year_id']),1);
        $this->db->from($this->config->item('table_bms_hom_budget_hom').' bud');
        $this->db->select('bud.*');
        $this->db->select('forward.status_forward_quantity_expectation,forward.date_forward_quantity_expectation,forward.user_forward_quantity_expectation');
        $this->db->where('bud.year_id',$reports['year_id']);
        $this->db->where('bud.year_index',0);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$reports['crop_type_id']);
        $this->db->join($this->config->item('table_bms_hom_forward').' forward','forward.year_id = '.$reports['year_id'].' and forward.crop_type_id = '.$reports['crop_type_id'],'LEFT');
        $this->db->order_by('bud.revision_budget','DESC');
        $result=$this->db->get()->row_array();
        $data['quantity_expectation_info']['quantity_expected']=0;
        $data['quantity_expectation_info']['date_quantity_expected']='N/A';
        $data['quantity_expectation_info']['user_quantity_expected']='N/A';
        $data['quantity_expectation_info']['date_forward_quantity_expectation']='N/A';
        $data['quantity_expectation_info']['user_forward_quantity_expectation']='N/A';
        if($result)
        {
            $data['quantity_expectation_info']['quantity_expected']=$result['quantity_expected'];
            $user_ids=array();
            $user_ids[$result['user_quantity_expected']]=$result['user_quantity_expected'];
            $user_ids[$result['user_forward_quantity_expectation']]=$result['user_forward_quantity_expectation'];
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
                $data['quantity_expectation_info']['date_forward_quantity_expectation']=System_helper::display_date_time($result['date_forward_quantity_expectation']);
            }
            elseif($result['revision_quantity_expected']>0 && $result['status_forward_quantity_expectation']!=$this->config->item('system_status_yes'))
            {
                $data['quantity_expectation_info']['status_quantity_expectation']='Not Forwarded';
            }
            else
            {
                $data['quantity_expectation_info']['status_quantity_expectation']='Not Done';
            }
            $data['quantity_expectation_info']['date_quantity_expected']=System_helper::display_date_time($result['date_quantity_expected']);
        }
//        print_r($data['quantity_expectation_info']);
//        exit;
        $data['title']="Quantity Expectation For ".$crop_type['text'].' ('.$data['year']['text'].')';
        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->json_return($ajax);
    }
    private function system_get_edit_items()
    {
        $year_id=$this->input->post('year_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $data['year_id']=$this->input->post('year_id');
        $data['crop_type_id']=$this->input->post('crop_type_id');
        $data['options']=$data;
        $forwarded=false;
        $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),'*',array('year_id ='.$year_id,'crop_type_id ='.$crop_type_id),1);
        if($result && $result['status_forward_quantity_expectation']==$this->config->item('system_status_yes'))
        {
            $forwarded=true;
        }
        $results=Query_helper::get_info($this->config->item('table_bms_hom_budget_hom'),'*',array('year_id ='.$year_id,'year_index =0'));
        $old_items=array();//hom budget
        foreach($results as $result)
        {
            $old_items[$result['variety_id']]=$result;
        }
        $results=Query_helper::get_info($this->config->item('table_bms_setup_bud_stock_minimum'),'*',array('revision =1'));
        $min_stocks=array();//minimum stock
        foreach($results as $result)
        {
            $min_stocks[$result['variety_id']]=$result['quantity'];
        }
        $this->db->from($this->config->item('table_login_setup_classification_varieties').' v');
        $this->db->select('v.id,v.name');
        $this->db->select('type.name type_name');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->where('v.whose','ARM');
        $this->db->where('v.status =',$this->config->item('system_status_active'));
        $this->db->where('type.id',$crop_type_id);
        $this->db->order_by('type.ordering','ASC');
        $this->db->order_by('v.ordering','ASC');
        $results=$this->db->get()->result_array();
        $count=0;
        $items=array();
        foreach($results as $result)
        {
            $item=array();
            $count++;
            $item['sl_no']=$count;
            $item['variety_id']=$result['id'];
            $item['variety_name']=$result['name'];
            if((isset($old_items[$result['id']]['quantity_budget']))&&(($old_items[$result['id']]['quantity_budget'])>0))
            {
                $item['quantity_budget']=$old_items[$result['id']]['quantity_budget'];
            }
            else
            {
                $item['quantity_budget']='N/D';
            }

            //TODO check when stock warehouse task will complete

            if((isset($old_items[$result['id']]['stock_warehouse']))&&(($old_items[$result['id']]['stock_warehouse'])>0))
            {
                $item['stock_warehouse']=$old_items[$result['id']]['stock_warehouse'];
            }else
            {
                $item['stock_warehouse']=0;
            }
            //TODO check when stock outlet task will complete

            if((isset($old_items[$result['id']]['stock_outlet']))&&(($old_items[$result['id']]['stock_outlet'])>0))
            {
                $item['stock_outlet']=$old_items[$result['id']]['stock_outlet'];
            }else
            {
                $item['stock_outlet']=0;
            }

            //TODO check when stock warehouse and stock outlet task will complete

            if($item['stock_warehouse']>0 || $item['stock_outlet']>0)
            {
                $item['stock_total']=$item['stock_warehouse']+$item['stock_outlet'];
            }else
            {
                $item['stock_total']=0;
            }

            if((isset($old_items[$result['id']]['stock_minimum']))&&(($old_items[$result['id']]['stock_minimum'])>0))
            {
                $item['stock_minimum']=$old_items[$result['id']]['stock_minimum'];
            }elseif((isset($min_stocks[$result['id']]))&&(($min_stocks[$result['id']])>0))
            {
                $item['stock_minimum']=$min_stocks[$result['id']];
            }
            else
            {
                $item['stock_minimum']=0;
            }
            if(isset($old_items[$item['variety_id']]['quantity_expected']))
            {
                $item['quantity_expected']=$old_items[$item['variety_id']]['quantity_expected'];
            }else
            {
                $item['quantity_expected']=0;
            }
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
        $year_id=$this->input->post('year_id');
        $crop_type_id=$this->input->post('crop_type_id');
        if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1))||(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),'*',array('year_id ='.$year_id,'crop_type_id ='.$crop_type_id),1);

            if($result)
            {
                if($result['status_forward_quantity_expectation']===$this->config->item('system_status_yes'))
                {
                    $ajax['status']=false;
                    $ajax['system_message']=$this->lang->line("MSG_ALREADY_FINALIZED");
                    $this->json_return($ajax);
                    die();
                }
            }
            if(!$result)
            {
                $ajax['status']=false;
                $ajax['system_message']='HOM Budget Not Forwarded Yet';
                $this->json_return($ajax);
                die();
            }
        }
        $user = User_helper::get_user();
        $time=time();
        $items=$this->input->post('items');
        $this->db->trans_start();
        if(sizeof($items)>0)
        {
            $results=Query_helper::get_info($this->config->item('table_bms_hom_budget_hom'),'*',array('year_id ='.$year_id,'year_index =0'));
            $old_items=array();//hom budget
            foreach($results as $result)
            {
                $old_items[$result['variety_id']]=$result;
            }
            $forwarded=false;
            $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),'*',array('year_id ='.$year_id,'crop_type_id ='.$crop_type_id),1);
            if($result && $result['status_forward_quantity_expectation']==$this->config->item('system_status_yes'))
            {
                $forwarded=true;
            }

            foreach($items as $variety_id=>$variety)
            {
                $data=array();
                $year_id=$year_id;
                $data['stock_warehouse']=$variety['stock_warehouse'];
                $data['stock_outlet']=$variety['stock_outlet'];
                $data['stock_minimum']=$variety['stock_minimum'];
                $quantity_expected=$variety['quantity_expected'];
                if(isset($old_items[$variety_id]))
                {
                    if($old_items[$variety_id]['quantity_expected']!=$quantity_expected)
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
                            else if(isset($this->permissions['action1']) && ($this->permissions['action1']==1) && ($old_items[$variety_id]['quantity_expected']==0))
                            {
                                $editable=true;
                            }
                        }
                        if($editable)
                        {
                            $this->db->where('variety_id',$variety_id);
                            $this->db->where('year_id',$year_id);
                            $this->db->where('year_index',0);
                            $this->db->set('revision_quantity_expected','revision_quantity_expected+1',false);
                            $this->db->set('quantity_expected',$quantity_expected);
                            $this->db->set('date_quantity_expected',$time);
                            $this->db->set('user_quantity_expected',$user->user_id);
                            $this->db->update($this->config->item('table_bms_hom_budget_hom'));
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
    private function system_details($year_id,$crop_type_id)
    {
        $data['year_id']=$year_id;
        $data['crop_type_id']=$crop_type_id;
        $data['options']=$data;
        $crop_type=Query_helper::get_info($this->config->item('table_login_setup_classification_crop_types'),array('id value','name text'),array('id ='.$year_id),1);
        $data['hom_forward_info']=Query_helper::get_info($this->config->item('table_bms_hom_forward'),'*',array('year_id ='.$year_id,'crop_type_id ='.$crop_type_id),1);
        $data['years']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id ASC'));
        $data['year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id ='.$year_id),1);
        $data['title']="Quantity Expectation For ".$crop_type['text'].' ('.$data['year']['text'].')';
        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/details",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $ajax['system_page_url']=site_url($this->controller_url."/index/details/".$year_id.'/'.$crop_type_id);
        $this->json_return($ajax);
    }

    private function system_forward()
    {
        $year_id=$this->input->post('year_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $user = User_helper::get_user();
        $time=time();

        $this->db->from($this->config->item('table_bms_hom_budget_hom').' bud');
        $this->db->select('bud.*');
        $this->db->select('forward.status_forward_quantity_expectation,forward.date_forward_quantity_expectation,forward.user_forward_quantity_expectation');
        $this->db->where('bud.year_id',$year_id);
        $this->db->where('bud.year_index',0);
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->where('v.crop_type_id',$crop_type_id);
        $this->db->join($this->config->item('table_bms_hom_forward').' forward','forward.year_id = '.$year_id.' and forward.crop_type_id = '.$crop_type_id,'LEFT');
        $this->db->order_by('bud.revision_budget','DESC');
        $result=$this->db->get()->row_array();
        if($result['quantity_expected']>0)
        {
            $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),'*',array('year_id ='.$year_id,'crop_type_id ='.$crop_type_id),1);
            $this->db->trans_start();
            if($result)
            {
                if($result['status_forward_quantity_expectation']===$this->config->item('system_status_yes'))
                {
                    $ajax['status']=false;
                    $ajax['system_message']=$this->lang->line("MSG_ALREADY_FORWARDED");
                    $this->json_return($ajax);
                    die();
                }
                else
                {
                    $data=array();
                    $data['status_forward_quantity_expectation']=$this->config->item('system_status_yes');
                    $data['user_forward_quantity_expectation'] = $user->user_id;
                    $data['date_forward_quantity_expectation'] = $time;
                    Query_helper::update($this->config->item('table_bms_hom_forward'),$data,array("id = ".$result['id']));
                }
            }
            else
            {
                $ajax['status']=true;
                $ajax['system_message']='HOM Budget Not Forwarded Yet!';
                $this->json_return($ajax);
            }
        }else
        {
            $ajax['status']=true;
            $ajax['system_message']='Quantity Expectation Not Done Yet';
            $this->json_return($ajax);
        }

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>'');
            $ajax['system_message']=$this->lang->line("MSG_SUCCESSFULLY_FORWARDED");
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }
}
