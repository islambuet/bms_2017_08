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
        $this->min_stock_budget();

	}
    private function ti()
    {
        $fiscal_year_id=2;//2016-2017

        //old_budget
        $this->db->from('arm_ems.bms_ti_bud_ti_bt bud');
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
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_ti_budget_ti',$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['territory_id']=$result['territory_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=1;
            $data['quantity_budget']=$result['year1_budget_quantity']?$result['year1_budget_quantity']:0;
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_ti_budget_ti',$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['territory_id']=$result['territory_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=2;
            $data['quantity_budget']=$result['year2_budget_quantity']?$result['year2_budget_quantity']:0;
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_ti_budget_ti',$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['territory_id']=$result['territory_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=3;
            $data['quantity_budget']=$result['year3_budget_quantity']?$result['year3_budget_quantity']:0;
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_ti_budget_ti',$data);
            $budget_crop_types[$result['territory_id']][$result['crop_id']][$result['crop_type_id']]=$result['crop_type_id'];


        }
        $results=Query_helper::get_info('arm_ems.bms_forward_ti','*',array('status_forward ="'.$this->config->item('system_status_yes').'"','year0_id ='.$fiscal_year_id),0,0,array('territory_id','crop_id'));
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
                    $this->db->insert('arm_bms_2017_08.bms_ti_forward',$data);
                }

            }
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'Transfer completed';

        }
        else
        {
            echo 'Transfer finished';
        }

    }
    private function zi()
    {
        $fiscal_year_id=2;//2016-2017

        //old_budget
        $this->db->from('arm_ems.bms_zi_bud_zi_bt bud');
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
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_zi_budget_zi',$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['zone_id']=$result['zone_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=1;
            $data['quantity_budget']=$result['year1_budget_quantity']?$result['year1_budget_quantity']:0;
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_zi_budget_zi',$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['zone_id']=$result['zone_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=2;
            $data['quantity_budget']=$result['year2_budget_quantity']?$result['year2_budget_quantity']:0;
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_zi_budget_zi',$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['zone_id']=$result['zone_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=3;
            $data['quantity_budget']=$result['year3_budget_quantity']?$result['year3_budget_quantity']:0;
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_zi_budget_zi',$data);
            $budget_crop_types[$result['zone_id']][$result['crop_id']][$result['crop_type_id']]=$result['crop_type_id'];


        }
        $results=Query_helper::get_info('arm_ems.bms_forward_zi','*',array('status_forward ="'.$this->config->item('system_status_yes').'"','year0_id ='.$fiscal_year_id),0,0,array('zone_id','crop_id'));
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
                    $this->db->insert('arm_bms_2017_08.bms_zi_forward',$data);
                }

            }
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'Transfer completed';

        }
        else
        {
            echo 'Transfer finished';
        }

    }
    private function di()
    {
        $fiscal_year_id=2;//2016-2017

        //old_budget
        $this->db->from('arm_ems.bms_di_bud_di_bt bud');
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
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_di_budget_di',$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['division_id']=$result['division_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=1;
            $data['quantity_budget']=$result['year1_budget_quantity']?$result['year1_budget_quantity']:0;
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_di_budget_di',$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['division_id']=$result['division_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=2;
            $data['quantity_budget']=$result['year2_budget_quantity']?$result['year2_budget_quantity']:0;
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_di_budget_di',$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['division_id']=$result['division_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=3;
            $data['quantity_budget']=$result['year3_budget_quantity']?$result['year3_budget_quantity']:0;
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_di_budget_di',$data);
            $budget_crop_types[$result['division_id']][$result['crop_id']][$result['crop_type_id']]=$result['crop_type_id'];


        }
        $results=Query_helper::get_info('arm_ems.bms_forward_di','*',array('status_forward ="'.$this->config->item('system_status_yes').'"','year0_id ='.$fiscal_year_id),0,0,array('division_id','crop_id'));
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
                    $this->db->insert('arm_bms_2017_08.bms_di_forward',$data);
                }

            }
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            echo 'Transfer completed';

        }
        else
        {
            echo 'Transfer finished';
        }

    }
    private function hom()
    {
        $fiscal_year_id=2;//2016-2017

        //final variances
        $results=Query_helper::get_info('arm_ems.bms_hom_bud_variance','*',array('year0_id ='.$fiscal_year_id));//can filter by crop id to increase runtime
        $final_variances=array();//hom variance
        foreach($results as $result)
        {
            $final_variances[$result['variety_id']]=$result;
        }
        $results=Query_helper::get_info('arm_ems.bms_variety_min_stock','*',array('revision =1'));//only for this crop could be done
        $min_stocks=array();//min stock
        foreach($results as $result)
        {
            $min_stocks[$result['variety_id']]=$result['quantity'];
        }

        //old_budget
        $this->db->from('arm_ems.bms_hom_bud_hom_bt bud');
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
            $data['revision_budget']=1;
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
                $data['revision_quantity_expected']=1;
                $data['date_quantity_expected']=$final_variances[$data['variety_id']]['date_created'];
                $data['user_quantity_expected']=$final_variances[$data['variety_id']]['user_created'];
            }
            $this->db->insert('arm_bms_2017_08.bms_hom_budget_hom',$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=1;
            $data['quantity_budget']=$result['year1_budget_quantity']?$result['year1_budget_quantity']:0;
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_hom_budget_hom',$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=2;
            $data['quantity_budget']=$result['year2_budget_quantity']?$result['year2_budget_quantity']:0;
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_hom_budget_hom',$data);

            $data=array();
            $data['year_id']=$result['year0_id'];
            $data['variety_id']=$result['variety_id'];
            $data['year_index']=3;
            $data['quantity_budget']=$result['year3_budget_quantity']?$result['year3_budget_quantity']:0;
            $data['revision_budget']=1;
            $data['date_budgeted']=$result['date_created'];
            $data['user_budgeted']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_hom_budget_hom',$data);
            $budget_crop_types[$result['crop_id']][$result['crop_type_id']]=$result['crop_type_id'];


        }
        $results=Query_helper::get_info('arm_ems.bms_forward_hom','*',array('status_forward ="'.$this->config->item('system_status_yes').'"','year0_id ='.$fiscal_year_id),0,0,array('crop_id'));
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
                    $this->db->insert('arm_bms_2017_08.bms_hom_forward',$data);
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
        $results=Query_helper::get_info('arm_ems.bms_variety_min_stock','*',array('revision =1'),0,0,array('variety_id ASC'));
        $this->db->trans_start();  //DB Transaction Handle START
        foreach($results as $result)
        {
            $data=array();
            $data['variety_id']=$result['variety_id'];
            $data['quantity']=$result['quantity']?$result['quantity']:0;
            $data['revision']=1;
            $data['date_created']=$result['date_created'];
            $data['user_created']=$result['user_created'];
            $this->db->insert('arm_bms_2017_08.bms_setup_bud_stock_minimum',$data);
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
