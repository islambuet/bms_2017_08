<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['system_site_short_name']='bms';
$config['offline_controllers']=array('home','sys_site_offline');
$config['external_controllers']=array('home');//user can use them without login
$config['system_max_actions']=7;

$config['system_status_active']='Active';
$config['system_status_inactive']='In-Active';
$config['system_status_delete']='Deleted';
$config['system_image_base_url']='http://localhost/tms_2017_07/';//depended on sites base url

$config['system_customer_type_outlet_id']=1;//depended on sites base url
$config['num_year_budget_prediction']=3;//number of year predict budget
$config['num_year_previous_sell']=2;//number of year display previous year sells