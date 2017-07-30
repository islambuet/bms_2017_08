<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setup_market_size extends Root_Controller
{
    private $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Setup_market_size');
        $this->controller_url='setup_market_size';
        $this->locations=User_helper::get_locations();
        if(!($this->locations))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('MSG_LOCATION_NOT_ASSIGNED_OR_INVALID');
            $this->json_return($ajax);
        }

        //$this->load->model("sys_module_task_model");
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
        elseif($action=='details')
        {
            $this->system_details($id);
        }
        elseif($action=='get_details_items')
        {
            $this->system_get_details_items($id);
        }
        elseif($action=="edit")
        {
            $this->system_edit($id,$id1);
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
        if(isset($this->permissions['action0']) && ($this->permissions['action0']==1))
        {
            $data['title']='Market Size Info';
            $ajax['status']=true;
            $ajax['system_content'][]=array('id'=>'#system_content','html'=>$this->load->view($this->controller_url.'/list',$data,true));
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
    public function system_get_items()
    {
        //get my outlet ids
        $this->db->from($this->config->item('table_login_csetup_customer').' outlet');
        $this->db->where('outlet.status',$this->config->item('system_status_active'));
        $this->db->select('outlet.id');
        $this->db->join($this->config->item('table_login_csetup_cus_info').' out_info','out_info.customer_id = outlet.id and out_info.revision = 1','INNER');
        $this->db->where('out_info.type',$this->config->item('system_customer_type_outlet_id'));

        $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = out_info.district_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_territories').' t','t.id = d.territory_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_divisions').' division','division.id = zone.division_id','INNER');
        if($this->locations['division_id']>0)
        {
            $this->db->where('division.id',$this->locations['division_id']);
            if($this->locations['zone_id']>0)
            {
                $this->db->where('zone.id',$this->locations['zone_id']);
                if($this->locations['territory_id']>0)
                {
                    $this->db->where('t.id',$this->locations['territory_id']);
                    if($this->locations['district_id']>0)
                    {
                        $this->db->where('d.id',$this->locations['district_id']);
                    }
                }
            }
        }
        $results=$this->db->get()->result_array();
        $outlet_ids='0';
        foreach($results as $result)
        {
            $outlet_ids.=(','.$result['id']);
        }
        $this->db->from($this->config->item('table_login_setup_classification_crop_types').' ct');
        $this->db->select('ct.id,ct.name crop_type_name');
        $this->db->select('crop.name crop_name');
        $this->db->select('SUM(ms.size_total) size_total,SUM(ms.size_arm) size_arm,COUNT(ms.outlet_id) num_outlet');
        $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = ct.crop_id','INNER');
        $this->db->join($this->config->item('table_bms_setup_market_size').' ms','ms.crop_type_id = ct.id and ms.revision = 1 and ms.outlet_id IN('.$outlet_ids.')','LEFT');
        //filter with location



        $this->db->where('ct.status',$this->config->item('system_status_active'));

        $this->db->group_by('ct.id');
        $this->db->order_by('crop.ordering','ASC');
        $this->db->order_by('ct.ordering','ASC');
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            if(!$item['size_total'])
            {
                $item['size_total']=0;
            }
            if(!$item['size_arm'])
            {
                $item['size_arm']=0;
            }
            $item['size_competitor']=number_format($item['size_total']-$item['size_arm'],3,'.','');
            $item['size_total']=number_format($item['size_total'],3,'.','');
            $item['size_arm']=number_format($item['size_arm'],3,'.','');
        }
        $this->json_return($items);
    }
    private function system_details($id)
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
            $this->db->from($this->config->item('table_login_setup_classification_crop_types').' ct');
            $this->db->select('ct.name crop_type_name');
            $this->db->select('crop.name crop_name');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = ct.crop_id','INNER');
            $this->db->where('ct.id',$item_id);
            $item=$this->db->get()->row_array();
            if(!$item)
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Crop Type.';
                $this->json_return($ajax);
            }

            $data['title']='Detail Market Size of('.$item['crop_name'].'-'.$item['crop_type_name'].')';
            $data['options']=array('crop_type_id'=>$item_id);
            $ajax['status']=true;
            $ajax['system_content'][]=array('id'=>'#system_content','html'=>$this->load->view($this->controller_url.'/list_details',$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line('YOU_DONT_HAVE_ACCESS');
            $this->json_return($ajax);
        }
    }
    public function system_get_details_items()
    {
        $item_id=$this->input->post('crop_type_id');
        $this->db->from($this->config->item('table_login_csetup_customer').' outlet');
        $this->db->where('outlet.status',$this->config->item('system_status_active'));

        $this->db->join($this->config->item('table_login_csetup_cus_info').' out_info','out_info.customer_id = outlet.id and out_info.revision = 1','INNER');
        $this->db->where('out_info.type',$this->config->item('system_customer_type_outlet_id'));
        $this->db->select('out_info.customer_id id,out_info.name outlet_name');
        //filter with location

        $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = out_info.district_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_territories').' t','t.id = d.territory_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
        $this->db->join($this->config->item('table_login_setup_location_divisions').' division','division.id = zone.division_id','INNER');
        if($this->locations['division_id']>0)
        {
            $this->db->where('division.id',$this->locations['division_id']);
            if($this->locations['zone_id']>0)
            {
                $this->db->where('zone.id',$this->locations['zone_id']);
                if($this->locations['territory_id']>0)
                {
                    $this->db->where('t.id',$this->locations['territory_id']);
                    if($this->locations['district_id']>0)
                    {
                        $this->db->where('d.id',$this->locations['district_id']);
                    }
                }
            }
        }

        $this->db->select('ms.size_total,ms.size_arm,ms.date_created,ms.remarks');
        $this->db->join($this->config->item('table_bms_setup_market_size').' ms','ms.outlet_id = outlet.id and ms.revision = 1 and ms.crop_type_id ='.$item_id,'LEFT');


        $items=$this->db->get()->result_array();
        /*echo '<PRE>';
        print_r($items);
        echo $this->db->last_query();
        echo '</PRE>';
        die();*/
        foreach($items as &$item)
        {
            if(!$item['size_total'])
            {
                $item['size_total']=0;
            }
            if(!$item['size_arm'])
            {
                $item['size_arm']=0;
            }
            if(!$item['date_created'])
            {
                $item['date_created']='Not Done';
            }
            else
            {
                $item['date_created']=System_helper::display_date($item['date_created']);
            }
            $item['size_competitor']=number_format($item['size_total']-$item['size_arm'],3,'.','');
            $item['size_total']=number_format($item['size_total'],3,'.','');
            $item['size_arm']=number_format($item['size_arm'],3,'.','');
        }
        $this->json_return($items);
    }
    private function system_edit($crop_type_id,$id)
    {
        if(isset($this->permissions['action2']) && ($this->permissions['action2']==1))
        {
            if($id>0)
            {
                $outlet_id=$id;
            }
            else
            {
                $outlet_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_login_setup_classification_crop_types').' ct');
            $this->db->select('ct.id crop_type_id,ct.name crop_type_name');
            $this->db->select('crop.name crop_name');
            $this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id = ct.crop_id','INNER');
            $this->db->where('ct.id',$crop_type_id);
            $data['crop_type_info']=$this->db->get()->row_array();
            if(!$data['crop_type_info'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Crop Type.';
                $this->json_return($ajax);
            }

            //getting outlet info and checking in my area

            $this->db->from($this->config->item('table_login_csetup_customer').' outlet');

            $this->db->select('outlet.id outlet_id');
            $this->db->select('out_info.name outlet_name');
            $this->db->select('d.name district_name');
            $this->db->select('t.name territory_name');
            $this->db->select('zone.name zone_name');
            $this->db->select('division.name division_name');
            $this->db->join($this->config->item('table_login_csetup_cus_info').' out_info','out_info.customer_id = outlet.id','INNER');
            $this->db->join($this->config->item('table_login_setup_location_districts').' d','d.id = out_info.district_id','INNER');
            $this->db->join($this->config->item('table_login_setup_location_territories').' t','t.id = d.territory_id','INNER');
            $this->db->join($this->config->item('table_login_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
            $this->db->join($this->config->item('table_login_setup_location_divisions').' division','division.id = zone.division_id','INNER');
            $this->db->where('out_info.revision',1);
            $this->db->where('outlet.status',$this->config->item('system_status_active'));
            if($this->locations['division_id']>0)
            {
                $this->db->where('division.id',$this->locations['division_id']);
                if($this->locations['zone_id']>0)
                {
                    $this->db->where('zone.id',$this->locations['zone_id']);
                    if($this->locations['territory_id']>0)
                    {
                        $this->db->where('t.id',$this->locations['territory_id']);
                        if($this->locations['district_id']>0)
                        {
                            $this->db->where('d.id',$this->locations['district_id']);
                        }
                    }
                }
            }
            $this->db->where('outlet.id',$outlet_id);
            $data['outlet_info']=$this->db->get()->row_array();
            if(!$data['outlet_info'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Outlet.';
                $this->json_return($ajax);
            }
            $data['item']=Query_helper::get_info($this->config->item('table_bms_setup_market_size'),array('size_total','size_arm','remarks'),array('crop_type_id ='.$crop_type_id,'outlet_id ='.$outlet_id,'revision =1'),1);
            if(!$data['item'])
            {
                $data['item']['size_total']=0;
                $data['item']['size_arm']=0;
                $data['item']['remarks']='';
            }
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url.'/edit',$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$crop_type_id.'/'.$outlet_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_save()
    {
        if(!(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        $user = User_helper::get_user();
        $time=time();
        $data=$this->input->post('item');



        $this->db->trans_start();  //DB Transaction Handle START
        $revision_history_data=array();
        $revision_history_data['date_updated']=$time;
        $revision_history_data['user_updated']=$user->user_id;
        Query_helper::update($this->config->item('table_bms_setup_market_size'),$revision_history_data,array('revision=1','crop_type_id='.$data['crop_type_id'],'outlet_id='.$data['outlet_id']));

        $this->db->where('crop_type_id',$data['crop_type_id']);
        $this->db->where('outlet_id',$data['outlet_id']);
        $this->db->set('revision','revision+1',false);
        $this->db->update($this->config->item('table_bms_setup_market_size'));
        $data['user_created'] = $user->user_id;
        $data['date_created'] = $time;
        $data['revision'] = 1;
        Query_helper::add($this->config->item('table_bms_setup_market_size'),$data);
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_details($data['crop_type_id']);

        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }

}
