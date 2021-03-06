<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transfer extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
        //$this->hom();

	}
    private function ti()
    {
        $source_tables=array(
            'budget'=>'arm_ems.bms_ti_bud_ti_bt',
            'forward'=>'arm_ems.bms_forward_ti'
        );
        $destination_tables=array(
            'budget'=>$this->config->item('table_bms_ti_budget_ti'),
            'forward'=>$this->config->item('table_bms_ti_forward'),
        );
        $fiscal_year_id=2;//2016-2017

        //old_budget
        $this->db->from($source_tables['budget'].' bud');
        $this->db->select('bud.*');
        $this->db->select('ct.id crop_type_id,ct.crop_id crop_id');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' ct','ct.id = v.crop_type_id','INNER');
        $this->db->where('bud.year0_id',$fiscal_year_id);
        $this->db->order_by('bud.territory_id ASC');
        $this->db->order_by('ct.crop_id ASC');
        $this->db->order_by('ct.id ASC');
        $this->db->order_by('bud.variety_id ASC');
        $results=$this->db->get()->result_array();
        $budget_crop_types=array();
        $this->db->trans_start();  //DB Transaction Handle START
        foreach($results as $result)
        {
            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['territory_id']=$result['territory_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=0;
            $data['quantity_budget']=$result['year0_budget_quantity']?$result['year0_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $data['quantity_target']=$result['year0_target_quantity']?$result['year0_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_targeted'];
            $data['user_targeted']=$result['user_targeted'];
            $this->db->insert($destination_tables['budget'],$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['territory_id']=$result['territory_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=1;
            $data['quantity_budget']=$result['year1_budget_quantity']?$result['year1_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $data['quantity_target']=$result['year1_target_quantity']?$result['year1_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_targeted'];
            $data['user_targeted']=$result['user_targeted'];
            $this->db->insert($destination_tables['budget'],$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['territory_id']=$result['territory_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=2;
            $data['quantity_budget']=$result['year2_budget_quantity']?$result['year2_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $data['quantity_target']=$result['year2_target_quantity']?$result['year2_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_targeted'];
            $data['user_targeted']=$result['user_targeted'];
            $this->db->insert($destination_tables['budget'],$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['territory_id']=$result['territory_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=3;
            $data['quantity_budget']=$result['year3_budget_quantity']?$result['year3_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $data['quantity_target']=$result['year3_target_quantity']?$result['year3_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_targeted'];
            $data['user_targeted']=$result['user_targeted'];
            $this->db->insert($destination_tables['budget'],$data);
            $budget_crop_types[$result['territory_id']][$result['crop_id']][$result['crop_type_id']]=$result['crop_type_id'];


        }
        $results=Query_helper::get_info($source_tables['forward'],'*',array('status_forward ="'.$this->config->item('system_status_yes').'"','year0_id ='.$fiscal_year_id),0,0,array('territory_id','crop_id'));
        foreach($results as $result)
        {
            if(isset($budget_crop_types[$result['territory_id']][$result['crop_id']]))
            {
                foreach($budget_crop_types[$result['territory_id']][$result['crop_id']] as $crop_type_id)
                {
                    $data=array();
                    $data['year_id']=$fiscal_year_id;
                    $data['territory_id']=$result['territory_id'];
                    $data['crop_type_id']=$crop_type_id;
                    $data['status_forward_budget']=$this->config->item('system_status_yes');
                    $data['date_forward_budget']=$result['date_created'];
                    $data['user_forward_budget']=$result['user_created'];
                    $data['status_forward_assign_target']=$result['status_assign'];
                    $data['date_forward_assign_target']=$result['date_assigned'];
                    $data['user_forward_assign_target']=$result['user_assigned'];
                    $this->db->insert($destination_tables['forward'],$data);

                }

            }
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'TI Transfer completed';

        }
        else
        {
            echo 'TI Transfer Failed';
        }

    }
    private function zi()
    {
        $source_tables=array(
            'budget'=>'arm_ems.bms_zi_bud_zi_bt',
            'forward'=>'arm_ems.bms_forward_zi'
        );
        $destination_tables=array(
            'budget'=>$this->config->item('table_bms_zi_budget_zi'),
            'forward'=>$this->config->item('table_bms_zi_forward'),
        );

        $fiscal_year_id=2;//2016-2017

        //old_budget
        $this->db->from($source_tables['budget'].' bud');
        $this->db->select('bud.*');
        $this->db->select('ct.id crop_type_id,ct.crop_id crop_id');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' ct','ct.id = v.crop_type_id','INNER');
        $this->db->where('bud.year0_id',$fiscal_year_id);
        $this->db->order_by('bud.zone_id ASC');
        $this->db->order_by('ct.crop_id ASC');
        $this->db->order_by('ct.id ASC');
        $this->db->order_by('bud.variety_id ASC');
        $results=$this->db->get()->result_array();
        $budget_crop_types=array();
        $this->db->trans_start();  //DB Transaction Handle START
        foreach($results as $result)
        {
            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['zone_id']=$result['zone_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=0;
            $data['quantity_budget']=$result['year0_budget_quantity']?$result['year0_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $data['quantity_target']=$result['year0_target_quantity']?$result['year0_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_targeted'];
            $data['user_targeted']=$result['user_targeted'];
            $this->db->insert($destination_tables['budget'],$data);


            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['zone_id']=$result['zone_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=1;
            $data['quantity_budget']=$result['year1_budget_quantity']?$result['year1_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $data['quantity_target']=$result['year1_target_quantity']?$result['year1_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_targeted'];
            $data['user_targeted']=$result['user_targeted'];
            $this->db->insert($destination_tables['budget'],$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['zone_id']=$result['zone_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=2;
            $data['quantity_budget']=$result['year2_budget_quantity']?$result['year2_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $data['quantity_target']=$result['year2_target_quantity']?$result['year2_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_targeted'];
            $data['user_targeted']=$result['user_targeted'];
            $this->db->insert($destination_tables['budget'],$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['zone_id']=$result['zone_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=3;
            $data['quantity_budget']=$result['year3_budget_quantity']?$result['year3_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $data['quantity_target']=$result['year3_target_quantity']?$result['year3_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_targeted'];
            $data['user_targeted']=$result['user_targeted'];
            $this->db->insert($destination_tables['budget'],$data);
            $budget_crop_types[$result['zone_id']][$result['crop_id']][$result['crop_type_id']]=$result['crop_type_id'];


        }
        $results=Query_helper::get_info($source_tables['forward'],'*',array('status_forward ="'.$this->config->item('system_status_yes').'"','year0_id ='.$fiscal_year_id),0,0,array('zone_id','crop_id'));
        foreach($results as $result)
        {
            if(isset($budget_crop_types[$result['zone_id']][$result['crop_id']]))
            {
                foreach($budget_crop_types[$result['zone_id']][$result['crop_id']] as $crop_type_id)
                {
                    $data=array();
                    $data['year_id']=$fiscal_year_id;
                    $data['zone_id']=$result['zone_id'];
                    $data['crop_type_id']=$crop_type_id;
                    $data['status_forward_budget']=$this->config->item('system_status_yes');
                    $data['date_forward_budget']=$result['date_created'];
                    $data['user_forward_budget']=$result['user_created'];

                    $data['status_forward_assign_target']=$result['status_assign'];
                    $data['date_forward_assign_target']=$result['date_assigned'];
                    $data['user_forward_assign_target']=$result['user_assigned'];
                    $this->db->insert($destination_tables['forward'],$data);
                }

            }
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'ZI Transfer completed';

        }
        else
        {
            echo 'ZI Transfer failed';
        }

    }
    private function di()
    {
        $source_tables=array(
            'budget'=>'arm_ems.bms_di_bud_di_bt',
            'forward'=>'arm_ems.bms_forward_di'
        );
        $destination_tables=array(
            'budget'=>$this->config->item('table_bms_di_budget_di'),
            'forward'=>$this->config->item('table_bms_di_forward'),
        );

        $fiscal_year_id=2;//2016-2017

        //old_budget
        $this->db->from($source_tables['budget'].' bud');
        $this->db->select('bud.*');
        $this->db->select('ct.id crop_type_id,ct.crop_id crop_id');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' ct','ct.id = v.crop_type_id','INNER');
        $this->db->where('bud.year0_id',$fiscal_year_id);
        $this->db->order_by('bud.division_id ASC');
        $this->db->order_by('ct.crop_id ASC');
        $this->db->order_by('ct.id ASC');
        $this->db->order_by('bud.variety_id ASC');
        $results=$this->db->get()->result_array();
        $budget_crop_types=array();
        $this->db->trans_start();  //DB Transaction Handle START
        foreach($results as $result)
        {
            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['division_id']=$result['division_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=0;
            $data['quantity_budget']=$result['year0_budget_quantity']?$result['year0_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];

            $data['quantity_target']=$result['year0_target_quantity']?$result['year0_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_targeted'];
            $data['user_targeted']=$result['user_targeted'];
            $this->db->insert($destination_tables['budget'],$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['division_id']=$result['division_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=1;
            $data['quantity_budget']=$result['year1_budget_quantity']?$result['year1_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];

            $data['quantity_target']=$result['year1_target_quantity']?$result['year1_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_targeted'];
            $data['user_targeted']=$result['user_targeted'];
            $this->db->insert($destination_tables['budget'],$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['division_id']=$result['division_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=2;
            $data['quantity_budget']=$result['year2_budget_quantity']?$result['year2_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];

            $data['quantity_target']=$result['year2_target_quantity']?$result['year2_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_targeted'];
            $data['user_targeted']=$result['user_targeted'];
            $this->db->insert($destination_tables['budget'],$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['division_id']=$result['division_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=3;
            $data['quantity_budget']=$result['year3_budget_quantity']?$result['year3_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];

            $data['quantity_target']=$result['year3_target_quantity']?$result['year3_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_targeted'];
            $data['user_targeted']=$result['user_targeted'];
            $this->db->insert($destination_tables['budget'],$data);

            $budget_crop_types[$result['division_id']][$result['crop_id']][$result['crop_type_id']]=$result['crop_type_id'];


        }
        $results=Query_helper::get_info($source_tables['forward'],'*',array('status_forward ="'.$this->config->item('system_status_yes').'"','year0_id ='.$fiscal_year_id),0,0,array('division_id','crop_id'));
        foreach($results as $result)
        {
            if(isset($budget_crop_types[$result['division_id']][$result['crop_id']]))
            {
                foreach($budget_crop_types[$result['division_id']][$result['crop_id']] as $crop_type_id)
                {
                    $data=array();
                    $data['year_id']=$fiscal_year_id;
                    $data['division_id']=$result['division_id'];
                    $data['crop_type_id']=$crop_type_id;
                    $data['status_forward_budget']=$this->config->item('system_status_yes');
                    $data['date_forward_budget']=$result['date_created'];
                    $data['user_forward_budget']=$result['user_created'];

                    $data['status_forward_assign_target']=$result['status_assign'];
                    $data['date_forward_assign_target']=$result['date_assigned'];
                    $data['user_forward_assign_target']=$result['user_assigned'];
                    $this->db->insert($destination_tables['forward'],$data);
                }

            }
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'DI Transfer completed';

        }
        else
        {
            echo 'DI Transfer failed';
        }

    }
    //required variety,crop type and crop tables
    private function hom()
    {
        $time=time();
        $source_tables=array(
            'hom_bud_variance'=>'arm_ems.bms_hom_bud_variance',
            'variety_min_stock'=>'arm_ems.bms_variety_min_stock',
            'hom_bud'=>'arm_ems.bms_hom_bud_hom_bt',
            'hom_forward'=>'arm_ems.bms_forward_hom'
        );
        $destination_tables=array(
            'hom_bud'=>$this->config->item('table_bms_hom_budget_hom'),
            'hom_forward'=>$this->config->item('table_bms_hom_forward'),
        );

        $fiscal_year_id=2;//2016-2017

        //final variances
        $results=Query_helper::get_info($source_tables['hom_bud_variance'],'*',array('year0_id ='.$fiscal_year_id));//can filter by crop id to increase runtime
        $final_variances=array();//hom variance
        foreach($results as $result)
        {
            $final_variances[$result['variety_id']]=$result;
        }
        $results=Query_helper::get_info($source_tables['variety_min_stock'],'*',array('revision =1'));//only for this crop could be done
        $min_stocks=array();//min stock
        foreach($results as $result)
        {
            $min_stocks[$result['variety_id']]=$result['quantity'];
        }

        //old_budget
        $this->db->from($source_tables['hom_bud'].' bud');
        $this->db->select('bud.*');
        $this->db->select('ct.id crop_type_id,ct.crop_id crop_id');
        $this->db->join($this->config->item('table_login_setup_classification_varieties').' v','v.id = bud.variety_id','INNER');
        $this->db->join($this->config->item('table_login_setup_classification_crop_types').' ct','ct.id = v.crop_type_id','INNER');
        $this->db->where('bud.year0_id',$fiscal_year_id);
        $this->db->order_by('ct.crop_id ASC');
        $this->db->order_by('ct.id ASC');
        $this->db->order_by('bud.variety_id ASC');
        $results=$this->db->get()->result_array();
        $budget_crop_types=array();
        $this->db->trans_start();  //DB Transaction Handle START
        foreach($results as $result)
        {
            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=0;
            $data['quantity_budget']=$result['year0_budget_quantity']?$result['year0_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            if(isset($final_variances[$data['variety_id']]))
            {
                $data['stock_warehouse']=0;
                $data['stock_outlet']=0;
                $data['stock_minimum']=0;
                if(isset($min_stocks[$data['variety_id']]))
                {
                    $data['stock_minimum']=$min_stocks[$data['variety_id']];
                }
                $data['quantity_expected']=$data['quantity_budget']-$final_variances[$data['variety_id']]['year0_variance_quantity'];
                $data['revision_quantity_expected']=($data['quantity_expected']==0)?0:1;
                $data['date_quantity_expected']=$final_variances[$data['variety_id']]['date_created'];
                $data['user_quantity_expected']=$final_variances[$data['variety_id']]['user_created'];
            }
            $data['quantity_target']=$result['year0_target_quantity']?$result['year0_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_updated']?$result['date_updated']:$time;
            $data['user_targeted']=$result['user_updated']?$result['user_updated']:21;
            $this->db->insert($destination_tables['hom_bud'],$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=1;
            $data['quantity_budget']=$result['year1_budget_quantity']?$result['year1_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $data['quantity_target']=$result['year1_target_quantity']?$result['year1_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_updated']?$result['date_updated']:$time;
            $data['user_targeted']=$result['user_updated']?$result['user_updated']:21;
            $this->db->insert($destination_tables['hom_bud'],$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=2;
            $data['quantity_budget']=$result['year2_budget_quantity']?$result['year2_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $data['quantity_target']=$result['year2_target_quantity']?$result['year2_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_updated']?$result['date_updated']:$time;
            $data['user_targeted']=$result['user_updated']?$result['user_updated']:21;
            $this->db->insert($destination_tables['hom_bud'],$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=3;
            $data['quantity_budget']=$result['year3_budget_quantity']?$result['year3_budget_quantity']:0;
            $data['revision_budget']=($data['quantity_budget']==0)?0:1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $data['quantity_target']=$result['year3_target_quantity']?$result['year3_target_quantity']:0;
            $data['revision_target']=($data['quantity_target']==0)?0:1;
            $data['date_targeted']=$result['date_updated']?$result['date_updated']:$time;
            $data['user_targeted']=$result['user_updated']?$result['user_updated']:21;
            $this->db->insert($destination_tables['hom_bud'],$data);

            $budget_crop_types[$result['crop_id']][$result['crop_type_id']]=$result['crop_type_id'];


        }
        $results=Query_helper::get_info($source_tables['hom_forward'],'*',array('status_forward ="'.$this->config->item('system_status_yes').'"','year0_id ='.$fiscal_year_id),0,0,array('crop_id'));
        foreach($results as $result)
        {
            if(isset($budget_crop_types[$result['crop_id']]))
            {
                foreach($budget_crop_types[$result['crop_id']] as $crop_type_id)
                {
                    $data=array();
                    $data['year_id']=$fiscal_year_id;
                    $data['crop_type_id']=$crop_type_id;
                    $data['status_forward_budget']=$result['status_forward'];
                    $data['date_forward_budget']=$result['date_created'];
                    $data['user_forward_budget']=$result['user_created'];

                    $data['status_forward_quantity_expectation']=$result['status_variance_finalize'];
                    $data['date_forward_quantity_expectation']=$result['date_variance_finalized'];
                    $data['user_forward_quantity_expectation']=$result['user_variance_finalized'];

                    $data['status_forward_target']=$result['status_target_finalize'];
                    $data['date_forward_target']=$result['date_target_finalized'];
                    $data['user_forward_target']=$result['user_target_finalized'];

                    $data['status_forward_assign_target']=$result['status_assign'];
                    $data['date_forward_assign_target']=$result['date_assigned'];
                    $data['user_forward_assign_target']=$result['user_assigned'];

                    $this->db->insert($destination_tables['hom_forward'],$data);
                }

            }
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'Hom Transfer completed';

        }
        else
        {
            echo 'Hom Transfer failed';
        }

    }
    private function min_stock_budget()
    {
        $source_tables=array(
            'bud_stock_minimum'=>'arm_ems.bms_variety_min_stock'
        );
        $destination_tables=array(
            'bud_stock_minimum'=>$this->config->item('table_bms_setup_bud_stock_minimum')
        );
        $results=Query_helper::get_info($source_tables['bud_stock_minimum'],'*',array('revision =1'),0,0,array('variety_id ASC'));
        $this->db->trans_start();  //DB Transaction Handle START
        foreach($results as $result)
        {
            $data=array();
            $data['variety_id']=$result['variety_id'];
            $data['quantity']=$result['quantity']?$result['quantity']:0;
            $data['revision']=1;
            $data['date_created']=$result['date_created'];
            $data['user_created']=$result['user_created'];
            $this->db->insert($destination_tables['bud_stock_minimum'],$data);
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'Min Transfer completed';

        }
        else
        {
            echo 'Min Transfer failed';
        }

    }
}
