<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Laaam extends TWIG_Controller {
	private $assets;

	function __construct()
    {
        parent::__construct();

		
    }

	public function index()
	{
		
	}

	public function login(){
		$this->load->spark('ajax/1.0.1');
		$this->load->library('ajax');

		$username = $this->input->post('username');
		$password = $this->input->post('password');

		if ($username && $password){
			$this->load->spark('curl/1.2.1');
			$this->load->library('curl');

			//check DIGIO/ACCESIUM accounts
			$server_response = $this->curl->simple_post(
				'https://gestion.digio.es/checklogin.php',
				array(
					'username' => $username,
					'password' => $password
				),
				array(
					CURLOPT_SSL_VERIFYPEER => false
				)
			);

			if($server_response){
				require_once '../../gp-load.php';

				$user_by_email = GP::$user->by_email( $username );
				$gp_user = str_replace('@digio.es', '', $username);
				if (!$user_by_email){
					$args = array();
					$args['user_login'] = $gp_user;
					$args['user_nicename'] = $gp_user;
					$args['display_name'] = $gp_user;
					$args['user_email'] = $username;
					$args['user_pass'] = $password;

					$user = GP::$user->create( $args ) ;
				}
				$response = array(
					'stat' => ($user || $user_by_email)?'OK':'ERROR',
					'user_login' => $gp_user,
					'user_password' => $password
				);
			}else{
				$response = array(
					'stat' => 'ERROR',
					'msg' => _('Invalid authentication')
				);
			}
		}else{
			$response = array(
				'stat' => 'ERROR',
				'msg' => _('Please check username or password')
			);
		}
		$this->ajax->response($response);
	}

	public function install(){
		$this->display('install_view', array(
    		'img_path'=>auto_link($this->config->item('index_page').'/../../laaam/img/get/'),
    		'install_path'=>auto_link($this->config->item('index_page').'/../../laaam')
		) );;
	}

	public function install_save(){
		$host = $this->input->get_post('host');
		$database = $this->input->get_post('database');
		$username = $this->input->get_post('username');
		$password = $this->input->get_post('password');
		$charset = ($this->input->get_post('charset'))?$this->input->get_post('charset'):'utf8';
		$collate = ($this->input->get_post('collate'))?$this->input->get_post('collate'):'utf8_general_ci';
		$prefix = ($this->input->get_post('prefix'))?$this->input->get_post('prefix'):'gp_';

		if( $host && $database && $username && $password && $charset && $collate){
			$this->load->helper(array('file','url'));

			$c = read_file('../application/config/database.php');

			$c = str_replace('$db[\'default\'][\'hostname\'] = \'localhost\'', '$db[\'default\'][\'hostname\'] = \''.$host.'\'', $c);
			$c = str_replace('$db[\'default\'][\'username\'] = \'\'', '$db[\'default\'][\'username\'] = \''.$username.'\'', $c);
			$c = str_replace('$db[\'default\'][\'password\'] = \'\'', '$db[\'default\'][\'password\'] = \''.$password.'\'', $c);
			$c = str_replace('$db[\'default\'][\'database\'] = \'\'', '$db[\'default\'][\'database\'] = \''.$database.'\'', $c);
			$c = str_replace('$db[\'default\'][\'char_set\'] = \'utf8\'', '$db[\'default\'][\'char_set\'] = \''.$charset.'\'', $c);
			$c = str_replace('$db[\'default\'][\'dbcollat\'] = \'utf8_general_ci\'', '$db[\'default\'][\'dbcollat\'] = \''.$collate.'\'', $c);
			$c = str_replace('$db[\'default\'][\'dbprefix\'] = \'\'', '$db[\'default\'][\'dbprefix\'] = \''.$prefix.'\'', $c);
			if(write_file('../application/config/database.php' ,$c)){
				$c = read_file('../../gp-config-sample.php');

				write_file('../../gp-config.php', $c);
				redirect('http://'.$_SERVER['HTTP_HOST']);
				exit;
			}else{
				redirect('laaam/install');
			}
		}else{
			redirect('laaam/install');
		}
	}
}

/* End of file laaam.php */
/* Location: ./application/modules/laaam/controllers/laaam.php */