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
        elseif($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="get_items")
        {
            $this->system_get_items($id1);
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
            $data['years']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id ASC'));
            $data['budget']=array();
            $data['budget']['division_id']=$this->locations['division_id'];
            $data['budget']['zone_id']=$this->locations['zone_id'];
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
    private function system_list()
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['year0_id']=$this->input->post('year0_id');
            $data['crop_id']=$this->input->post('crop_id');
            $data['options']=$data;
            $data['title']="Crop List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/list",$data,true));
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
    private function system_get_items()
    {
        $year0_id=$this->input->post('year0_id');
        $crop_id=$this->input->post('crop_id');
        $this->db->from($this->config->item('table_login_setup_classification_crop_types').' ctype');
        $this->db->select('ctype.id,ctype.name type_name');
        $this->db->select('fhom.status_forward_quantity_expectation');
        $this->db->join($this->config->item('table_bms_hom_forward').' fhom','fhom.crop_type_id = ctype.id and year_id ='.$year0_id,'LEFT');
        $this->db->order_by('ctype.ordering','ASC');
        $this->db->where('ctype.status',$this->config->item('system_status_active'));
        $this->db->where('ctype.crop_id',$crop_id);
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            if(!$item['status_forward_quantity_expectation'])
            {
                $item['status_forward_quantity_expectation']=$this->config->item('system_status_no');
            }
        }
        $this->json_return($items);
    }

    private function system_edit($year0_id,$crop_type_id)
    {
        if((isset($this->permissions['action1']) && ($this->permissions['action1']==1)) || (isset($this->permissions['action2']) && ($this->permissions['action2']==1)) || (isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            if(($this->input->post('id')))
            {
                $crop_type_id=$this->input->post('id');
            }
            $crop_type=Query_helper::get_info($this->config->item('table_login_setup_classification_crop_types'),array('id value','name text'),array('id ='.$crop_type_id),1);
            $data['years']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id ASC'));
            $data['year0_id']=$year0_id;
            $data['crop_type_id']=$crop_type_id;
            $data['options']=$data;

            $data['year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id ='.$year0_id),1);
            $data['title']="Quantity Expectation For ".$crop_type['text'].' ('.$data['year']['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/edit/".$year0_id.'/'.$crop_type_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_edit_items()
    {
        $year0_id=$this->input->post('year0_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $forwarded=false;
        $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),'*',array('year_id ='.$year0_id,'crop_type_id ='.$crop_type_id),1);
        if($result && $result['status_forward_quantity_expectation']==$this->config->item('system_status_yes'))
        {
            $forwarded=true;
        }
        $results=Query_helper::get_info($this->config->item('table_bms_hom_budget_hom'),'*',array('year_id ='.$year0_id,'year_index =0'));
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
        $year0_id=$this->input->post('year0_id');
        $crop_type_id=$this->input->post('crop_type_id');
        if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1))||(isset($this->permissions['action3']) && ($this->permissions['action3']==1)))
        {
            $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),'*',array('year_id ='.$year0_id,'crop_type_id ='.$crop_type_id),1);

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
            $results=Query_helper::get_info($this->config->item('table_bms_hom_budget_hom'),'*',array('year_id ='.$year0_id,'year_index =0'));
            $old_items=array();//hom budget
            foreach($results as $result)
            {
                $old_items[$result['variety_id']]=$result;
            }
            $forwarded=false;
            $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),'*',array('year_id ='.$year0_id,'crop_type_id ='.$crop_type_id),1);
            if($result && $result['status_forward_quantity_expectation']==$this->config->item('system_status_yes'))
            {
                $forwarded=true;
            }

            foreach($items as $variety_id=>$variety)
            {
                $data=array();
                $year_id=$year0_id;
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
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_details($year0_id,$crop_type_id);
            die();
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }
    private function system_details($year0_id,$crop_type_id)
    {
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            if(($this->input->post('id')))
            {
                $crop_type_id=$this->input->post('id');
            }
            $data['year0_id']=$year0_id;
            $data['crop_type_id']=$crop_type_id;
            $data['options']=$data;
            $crop_type=Query_helper::get_info($this->config->item('table_login_setup_classification_crop_types'),array('id value','name text'),array('id ='.$crop_type_id),1);
            $data['hom_forward_info']=Query_helper::get_info($this->config->item('table_bms_hom_forward'),'*',array('year_id ='.$year0_id,'crop_type_id ='.$crop_type_id),1);

            $data['years']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id ASC'));
            $data['year0_id']=$year0_id;
            $data['crop_type_id']=$crop_type_id;
            $data['options']=$data;
            $data['year']=Query_helper::get_info($this->config->item('table_login_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id ='.$year0_id),1);
            $data['title']="Quantity Expectation For ".$crop_type['text'].' ('.$data['year']['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/details/".$year0_id.'/'.$crop_type_id);
            $this->json_return($ajax);
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
        $year0_id=$this->input->post('year0_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $user = User_helper::get_user();
        $time=time();
        $result=Query_helper::get_info($this->config->item('table_bms_hom_forward'),'*',array('year_id ='.$year0_id,'crop_type_id ='.$crop_type_id),1);
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
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $ajax['status']=true;
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
