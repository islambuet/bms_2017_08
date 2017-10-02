<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mgt_quantity_confirmed extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Mgt_quantity_confirmed');
        $this->controller_url='mgt_quantity_confirmed';
    }
    public function index($action="search",$id=0)
    {
        if($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="edit")
        {
            $this->system_edit();
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
            $data['title']="Quantity Confirmed Search";
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
    private function system_edit()
    {
        $reports=$this->input->post('report');
        $data=$reports;
        if(!($reports['year_id']>0))
        {
            $ajax['status']=false;
            $ajax['system_message']='Please Select a Fiscal year';
            $this->json_return($ajax);
        }
        if(!($reports['variety_id']>0))
        {
            $ajax['status']=false;
            $ajax['system_message']='Please Select a variety';
            $this->json_return($ajax);
        }

        $result=Query_helper::get_info($this->config->item('table_bms_hom_budget_hom'),'*',array('year_id ='.$reports['year_id'],'variety_id ='.$reports['variety_id'],'year_index =0'),1);
        if($result)
        {
            $data['hom_budget']=$result['quantity_budget'];
            if(isset($result['stock_warehouse']))
            {
                $data['stock_warehouse']=$result['stock_warehouse'];
            }
            else
            {
                $data['stock_warehouse']='N/A';
            }
            if(isset($result['stock_outlet']))
            {
                $data['stock_outlet']=$result['stock_outlet'];
            }
            else
            {
                $data['stock_outlet']='N/A';
            }
            if(isset($result['stock_warehouse']) && isset($result['stock_outlet']))
            {
                $data['current_stock']=$result['stock_warehouse']+$result['stock_outlet'];
            }
            elseif(isset($result['stock_warehouse']))
            {
                $data['current_stock']=$result['stock_warehouse'];
            }
            elseif(isset($result['stock_outlet']))
            {
                $data['current_stock']=$result['stock_outlet'];
            }
            else
            {
                $data['current_stock']='N/A';
            }
            $data['quantity_expected']=$result['quantity_expected'];
        }
        else
        {
            $data['hom_budget']='N/A';
            $data['stock_warehouse']='N/A';
            $data['stock_outlet']='N/A';
            $data['current_stock']='N/A';
            $data['quantity_expected']='N/A';
        }
        $data['variety']=Query_helper::get_info($this->config->item('table_login_setup_classification_varieties'),array('name'),array('id ='.$reports['variety_id']),1);
        //currency and rates
        $data['currencies']=Query_helper::get_info($this->config->item('table_bms_setup_currency'),array('id value','name text','amount_rate_budget'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering ASC'));
        $data['currency_rates']=array();
        foreach($data['currencies'] as $rate)
        {
            $data['currency_rates'][$rate['value']]=$rate['amount_rate_budget'];
        }

        //Confirmed data from principals
        $this->db->from($this->config->item('table_login_setup_variety_principals').' vp');
        $this->db->select('vp.principal_id,vp.name_import');
        $this->db->where('vp.variety_id',$reports['variety_id']);
        $this->db->where('vp.revision',1);
        $this->db->select('p.name principal_name');
        $this->db->join($this->config->item('table_login_basic_setup_principal').' p','p.id = vp.principal_id','INNER');
        $this->db->where('p.status!=',$this->config->item('system_status_delete'));
        $this->db->select('confirm.cogs_total cogs_total_variety,confirm.quantity_confirmed_total');
        $this->db->join($this->config->item('table_bms_mgt_quantity_confirm').' confirm','confirm.variety_id = '.$reports['variety_id'].' and confirm.fiscal_year_id = '.$reports['year_id'],'LEFT');
        $this->db->select('details.currency_id,details.amount_rate_budget,details.price_unit,details.cogs_kg,details.cogs_total,details.quantity_total,details.quantity_1,details.quantity_2,details.quantity_3,details.quantity_4,details.quantity_5,details.quantity_6,details.quantity_7,details.quantity_8,details.quantity_9,details.quantity_10,details.quantity_11,details.quantity_12');
        $this->db->join($this->config->item('table_bms_mgt_quantity_confirm_details').' details','details.parent_id = confirm.id and details.principal_id = vp.principal_id','LEFT');
        $results=$this->db->get()->result_array();
        //print_r($results);exit;
        if(!$results)
        {
            $ajax['status']=false;
            $ajax['system_message']='No Principal Found Here. Please set principal first or select another variety';
            $this->json_return($ajax);
            die();
        }
        foreach($results as $result)
        {
            $data['items'][$result['principal_id']]=$result;
            $data['cogs_total']=$data['items'][$result['principal_id']]['cogs_total_variety'];
            $data['quantity_confirmed_total']=$data['items'][$result['principal_id']]['quantity_confirmed_total'];
        }
        //direct cost
        $result=$results=Query_helper::get_info($this->config->item('table_bms_setup_direct_cost_items'),array('SUM(percentage) total_percentage'),array('status !="'.$this->config->item('system_status_delete').'"'),1);
        if(isset($result['total_percentage']))
        {
            $data['direct_costs_percentage']=number_format($result['total_percentage']/100,5,'.','');
        }
        else
        {
            $data['direct_costs_percentage']=0;
        }
        //packing items cost
        $result=Query_helper::get_info($this->config->item('table_bms_setup_packing_items_cost'),array('SUM(amount_cost_budget) total_cost'),array('variety_id ='.$reports['variety_id']),1);
        if(isset($result['total_cost']))
        {
            $data['packing_cost']=$result['total_cost'];
        }
        else
        {
            $data['packing_cost']=0;
        }

        $data['title']='Quantity Confirmed for '.$data['variety']['name'];
        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view($this->controller_url."/add_edit",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->json_return($ajax);
    }
    private function system_save()
    {
        if((isset($this->permissions['action1']) && ($this->permissions['action1']==1))||(isset($this->permissions['action2']) && ($this->permissions['action2']==1)))
        {
            $user=User_helper::get_user();
            $time=time();
            $fiscal_year_id=$this->input->post('year_id');
            $variety_id=$this->input->post('variety_id');
            $items=$this->input->post('items');
            //direct cost
            $results=$results=Query_helper::get_info($this->config->item('table_bms_setup_direct_cost_items'),array('id','percentage'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0);
            if($results)
            {
                $direct_costs['direct_cost_percentage_total']=0;
                foreach($results as $result)
                {
                    $direct_costs['direct_cost_percentage_total']+=$result['percentage'];
                }
                $direct_costs['details_direct_cost']=json_encode($results);
            }
            else
            {
                $direct_costs['details_direct_cost']=null;
                $direct_costs['direct_cost_percentage_total']=0;
            }
            //packing items cost
            $results=Query_helper::get_info($this->config->item('table_bms_setup_packing_items_cost'),array('packing_item_id,amount_cost_budget'),array('variety_id ='.$variety_id),0,0);
            if($results)
            {
                $packs['pack_total_kg']=0;
                foreach($results as $result)
                {
                    $packs['pack_total_kg']+=$result['amount_cost_budget'];
                }
                $packs['details_pack']=json_encode($results);
            }
            else
            {
                $packs['details_pack']=null;
                $packs['pack_total_kg']=0;
            }
            //present currency rates
            $results=Query_helper::get_info($this->config->item('table_bms_setup_currency'),array('id value','name text','amount_rate_budget'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering ASC'));
            $currency_rates=array();
            foreach($results as $rate)
            {
                $currency_rates[$rate['value']]=$rate['amount_rate_budget'];
            }

            $items_current=array();
            $this->db->from($this->config->item('table_bms_mgt_quantity_confirm').' confirm');
            $this->db->select('*');
            $this->db->where('fiscal_year_id',$fiscal_year_id);
            $this->db->where('variety_id',$variety_id);
            $this->db->select('details.*');
            $this->db->join($this->config->item('table_bms_mgt_quantity_confirm_details').' details','details.parent_id = confirm.id','INNER');
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $items_current[$result['principal_id']]=$result;
                $parent_id=$result['parent_id'];
            }

            $new_main=array();
            $new_main['variety_id']=$variety_id;
            $new_main['fiscal_year_id']=$fiscal_year_id;
            $new_main['pack_total_kg']=$packs['pack_total_kg'];
            $new_main['details_pack']=$packs['details_pack'];
            $new_main['direct_cost_percentage_total']=$direct_costs['direct_cost_percentage_total'];
            $new_main['details_direct_cost']=$direct_costs['details_direct_cost'];
            $new_details=array();

            $new_main['cogs_total']=0;
            $new_main["quantity_confirmed_total"]=0;
            for($i=1;$i<13;$i++)
            {
                $new_main["quantity_$i"]=0;
            }
            foreach($items as $principal_id=>$item)
            {
                $new_details[$principal_id]['principal_id']=$principal_id;
                $new_details[$principal_id]['currency_id']=$item['currency_id'];
                $new_details[$principal_id]['amount_rate_budget']=$currency_rates[$item['currency_id']];
                //principal wise currency rate & Currency id && unit price
                $new_details[$principal_id]['price_unit']=$item['price_unit'];
                if(!(($new_details[$principal_id]['price_unit'])>0))
                {
                    $new_details[$principal_id]['price_unit']=0;
                }

                $new_details[$principal_id]['quantity_total']=0;
                for($i=1;$i<13;$i++)
                {
                    //principal wise quantity per month
                    $new_details[$principal_id]["quantity_$i"]=$item["quantity_$i"];
                    //principal wise total quantity
                    $new_details[$principal_id]["quantity_total"]+=$item["quantity_$i"];
                    //month wise total quantity
                    $new_main["quantity_$i"]+=$item["quantity_$i"];
                }
                //confirmed total quantity
                $new_main["quantity_confirmed_total"]+=$new_details[$principal_id]["quantity_total"];

                //cogs
                if($new_details[$principal_id]['price_unit']>0 && $new_details[$principal_id]['amount_rate_budget']>0)
                {
                    $new_details[$principal_id]['cogs_kg']=($new_details[$principal_id]['price_unit']*$new_details[$principal_id]['amount_rate_budget'])+(($new_details[$principal_id]['price_unit']*$new_details[$principal_id]['amount_rate_budget'])*($direct_costs['direct_cost_percentage_total']/100))+$packs['pack_total_kg'];
                }
                else
                {
                    $new_details[$principal_id]['cogs_kg']=0;
                }

                //total cogs
                if($new_details[$principal_id]["quantity_total"]>0)
                {
                    $new_details[$principal_id]['cogs_total']=($new_details[$principal_id]["quantity_total"]*$new_details[$principal_id]['cogs_kg']);
                }
                else
                {
                    $new_details[$principal_id]['cogs_total']=0;
                }
                $new_main['cogs_total']+=$new_details[$principal_id]["cogs_total"];
            }

            $this->db->trans_start();  //DB Transaction Handle START
            if($results)
            {
                $this->db->where('id',$parent_id);
                $this->db->set('revision','revision+1',false);
                $this->db->update($this->config->item('table_bms_mgt_quantity_confirm'));
                $new_main['user_updated'] = $user->user_id;
                $new_main['date_updated'] = $time;
                Query_helper::update($this->config->item('table_bms_mgt_quantity_confirm'),$new_main,array("id = ".$parent_id),$save_history=true);
                foreach($new_details as $principal_id=>$data)
                {
                    if(isset($items_current[$principal_id]))
                    {
                        $this->db->where('parent_id',$parent_id);
                        $this->db->where('principal_id',$principal_id);
                        $this->db->set('revision','revision+1',false);
                        $this->db->update($this->config->item('table_bms_mgt_quantity_confirm_details'));
                        $data['user_updated']=$time;
                        $data['date_updated']=$user->user_id;
                        Query_helper::update($this->config->item('table_bms_mgt_quantity_confirm_details'),$data,array('parent_id = '.$parent_id,'principal_id = '.$principal_id),$save_history=true);
                    }
                    else
                    {
                        $data['parent_id']=$parent_id;
                        $data['principal_id']=$principal_id;
                        $data['date_created']=$time;
                        $data['user_created']=$user->user_id;
                        Query_helper::add($this->config->item('table_bms_mgt_quantity_confirm_details'),$data,$save_history=true);
                    }
                }
            }
            else
            {
                $new_main['date_created']=$time;
                $new_main['user_created']=$user->user_id;
                $parent_id=Query_helper::add($this->config->item('table_bms_mgt_quantity_confirm'),$new_main,$save_history=true);
                foreach($new_details as $principal_id=>$data)
                {
                    $data['parent_id']=$parent_id;
                    $data['principal_id']=$principal_id;
                    $data['date_created']=$time;
                    $data['user_created']=$user->user_id;
                    Query_helper::add($this->config->item('table_bms_mgt_quantity_confirm_details'),$data,$save_history=true);
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