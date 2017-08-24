<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Setup
//Market Size
$config['table_bms_setup_market_size'] = 'arm_bms_2017_08.bms_setup_market_size';
//Minimum Stock
$config['table_bms_setup_bud_stock_minimum'] = 'arm_bms_2017_08.bms_setup_bud_stock_minimum';


//TI budget
$config['table_bms_ti_budget_outlet'] = 'arm_bms_2017_08.bms_ti_budget_outlet';//outlet budget and target table
$config['table_bms_ti_budget_ti'] = 'arm_bms_2017_08.bms_ti_budget_ti';//ti budget and target table
$config['table_bms_ti_forward'] = 'arm_bms_2017_08.bms_ti_forward';

//ZI budget
$config['table_bms_zi_budget_zi'] = 'arm_bms_2017_08.bms_zi_budget_zi';//zi budget and target table
$config['table_bms_zi_forward'] = 'arm_bms_2017_08.bms_zi_forward';

//DI budget
$config['table_bms_di_budget_di'] = 'arm_bms_2017_08.bms_di_budget_di';//zi budget and target table
$config['table_bms_di_forward'] = 'arm_bms_2017_08.bms_di_forward';

//HOM budget
$config['table_bms_hom_budget_hom'] = 'arm_bms_2017_08.bms_hom_budget_hom';//zi budget and target table
$config['table_bms_hom_forward'] = 'arm_bms_2017_08.bms_hom_forward';