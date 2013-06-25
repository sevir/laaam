<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH.'../sparks/MY_Model/1.0.0/core/MY_Model.php');

class gp_user_model extends MY_Model
{
	protected $_table = "gp_users";
	protected $primary_key = "ID";
}

