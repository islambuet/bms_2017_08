<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setup_packing_item extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Setup_packing_item');
        $this->controller_url='setup_packing_item';
    }

    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=='get_items')
        {
            $this->system_get_items();
        }
        elseif($action=="add")
        {
            $this->system_add();
        }
        elseif($action=="edit")
        {
            $this->system_edit($id);
        }
        elseif($action=="list_variety_packing_cost")
        {
            $this->system_list_variety_packing_cost($id);
        }
        elseif($action=='get_variety_packing_cost_items')
        {
            $this->system_get_variety_packing_cost_items();
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="save_variety_packing_cost")
        {
            $this->system_save_variety_packing_cost();
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
            $data['title']="Packing Items";
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
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_items()
    {
    	$this->db->select('pt.*');
    	$this->db->select('COUNT(ptc.variety_id) no_of_variety');
    	$this->db->from($this->config->item('table_bms_setup_packing_items').' pt');
    	$this->db->join($this->config->item('table_bms_setup_packing_items_cost').' ptc','ptc.packing_item_id=pt.id','LEFT');
    	$this->db->where('pt.status!=',$this->config->item('system_status_delete'));
    	$this->db->order_by('pt.ordering','ASC');
    	$this->db->group_by('pt.id');
    	$items=$this->db->get()->result_array();

    	#$items=Query_helper::get_info($this->config->item('table_bms_setup_packing_items'),'*',array('status!="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering ASC'));
        $this->json_return($items);
    }
    private function system_add()
    {
        if(isset($this->permissions['action1'])&&($this->permissions['action1']==1))
        {
            $data['title']="Create New Packing Item";
            $data["item"] = Array(
                'id' => 0,
                'name' => '',
                'description' => '',
                'ordering' => 99,
                'status' => $this->config->item('system_status_active')
            );
            $ajax['system_page_url']=site_url($this->controller_url."/index/add");

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
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
    private function system_edit($id)
    {
        if(isset($this->permissions['action2'])&&($this->permissions['action2']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }

            $data['item']=Query_helper::get_info($this->config->item('table_bms_setup_packing_items'),'*',array('id ='.$item_id),1);
            if(!$data['item'])
	        {
	            $ajax['status']=false;
	            $ajax['system_message']='Invalid Packing Item';
	            $this->json_return($ajax);
	        }

            $data['title']="Edit Packing Item (".$data['item']['name'].')';
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
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_list_variety_packing_cost($id)
    {
        if(isset($this->permissions['action0'])&&($this->permissions['action0']==1))
        {
            if($id>0)
            {
                $item_id=$id;
            }
            else
            {
                $item_id=$this->input->post('id');
            }
            $data['item']=Query_helper::get_info($this->config->item('table_bms_setup_packing_items'),'*',array('id ='.$item_id),1);
            if(!$data['item'])
	        {
	            $ajax['status']=false;
	            $ajax['system_message']='Invalid Packing Item';
	            $this->json_return($ajax);
	        }

            $data['title']='Variety List for Packing Cost of ('.$data['item']['name'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view($this->controller_url."/list_variety_packing_cost",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/list_variety_packing_cost/'.$item_id);
            $this->json_return($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
    }
    private function system_get_variety_packing_cost_items()
    {
    	$id=$this->input->post('id');

    	$this->db->select('v.id,v.name variety_name');
    	$this->db->select('cost.cost');
    	$this->db->select('crop.name crop_name');
    	$this->db->select('type.name crop_type_name');

    	$this->db->from($this->config->item('table_login_setup_classification_varieties').' v');
    	$this->db->join($this->config->item('table_bms_setup_packing_items_cost').' cost','cost.variety_id=v.id AND cost.packing_item_id='.$id,'LEFT');
    	$this->db->join($this->config->item('table_login_setup_classification_crop_types').' type','type.id=v.crop_type_id','INNER');
    	$this->db->join($this->config->item('table_login_setup_classification_crops').' crop','crop.id=type.crop_id','INNER');

    	$this->db->where('v.status',$this->config->item('system_status_active'));
    	$this->db->where('v.whose','ARM');

    	$this->db->order_by('crop.ordering','ASC');
    	$this->db->order_by('type.ordering','ASC');
    	$this->db->order_by('v.ordering','ASC');

    	$items=$this->db->get()->result_array();
    	foreach($items as &$item)
    	{
    		if($item['cost']==0)
    		{
    			$item['cost']=0;
    		}
    	}
        $this->json_return($items);
    }
    private function system_save()
    {
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        if($id>0)
        {
            if(!(isset($this->permissions['action2'])&&($this->permissions['action2']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
        }
        else
        {
            if(!(isset($this->permissions['action1'])&&($this->permissions['action1']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->json_return($ajax);
            }
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->json_return($ajax);
        }
        else
        {
            $data=$this->input->post('item');
            $this->db->trans_start();  //DB Transaction Handle START
            if($id>0)
            {
                $data['user_updated'] = $user->user_id;
                $data['date_updated'] = time();

                Query_helper::update($this->config->item('table_bms_setup_packing_items'),$data,array("id = ".$id));
            }
            else
            {
                $data['user_created'] = $user->user_id;
                $data['date_created'] = time();
                Query_helper::add($this->config->item('table_bms_setup_packing_items'),$data);
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
    private function system_save_variety_packing_cost()
    {
    	$id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();

        if(!(isset($this->permissions['action2'])&&($this->permissions['action2']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->json_return($ajax);
        }
        if($id<1)
        {
            $ajax['status']=false;
            $ajax['system_message']='Invalid try';
            $this->json_return($ajax);
        }
        $items=$this->input->post('items');
        $results=Query_helper::get_info($this->config->item('table_bms_setup_packing_items_cost'),array('variety_id','cost'),array('packing_item_id='.$id));
        $items_current=array();
        foreach($results as $result)
        {
        	$items_current[$result['variety_id']]=$result['cost'];
        }

        $data_add=array(
        	'packing_item_id'=>$id,
        	'revision_cost'=>1,
        	'date_created'=>$time,
        	'user_created'=>$user->user_id
        );
        $this->db->trans_start();  //DB Transaction Handle START

        foreach($items as $variety_id=>$cost)
        {
        	if(isset($items_current[$variety_id]))
        	{
        		if($items_current[$variety_id]!=$cost)
        		{
        			$this->db->set('cost',$cost);
        			$this->db->set('revision_cost','revision_cost+1',false);
        			$this->db->set('date_updated',$time);
        			$this->db->set('user_updated',$user->user_id);

        			$this->db->where('variety_id',$variety_id);
        			$this->db->where('packing_item_id',$packing_item_id);

        			$this->db->update($this->config->item('table_bms_setup_packing_items_cost'));
        		}
        	}
        	else
        	{
        		$data_add['variety_id']=$variety_id;
        		$data_add['cost']=$cost;
        		Query_helper::add($this->config->item('table_bms_setup_packing_items_cost'),$data_add);
        	}
        }

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_list();
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->json_return($ajax);
        }
    }
    private function check_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('item[name]',$this->lang->line('LABEL_NAME'),'required');
        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
}
