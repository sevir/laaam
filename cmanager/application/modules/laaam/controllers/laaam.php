<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Laaam extends TWIG_Controller {
	private $assets;

	function __construct()
    {
        parent::__construct();        		
    }

	public function index()
	{
		require_once '../../gp-load.php';
		if( GP::$user->logged_in() && GP::$user->current()->can( 'write', 'project' )){
			$this->display('admin_tools');
		}else{			
			redirect('http://'.$_SERVER['HTTP_HOST']);
		}
	}

	public function get_user(){
		require_once '../../gp-load.php';
		if( GP::$user->logged_in() && GP::$user->current()->can( 'write', 'project' )){
			$this->load->database();
			$this->load->model('gp_user_model');
			$this->load->spark('ajax/1.0.1');
			$this->load->library('ajax');

			$users = $this->gp_user_model->find_all();
			$this->ajax->response(array(
				'Result' => 'OK',
				'Records' => $users
			));
		}
	}

	public function delete_user(){
		require_once '../../gp-load.php';
		if( GP::$user->logged_in() && GP::$user->current()->can( 'write', 'project' )){
			$this->load->database();
			$this->load->model('gp_user_model');
			$this->load->spark('ajax/1.0.1');
			$this->load->library('ajax');

			$users = $this->gp_user_model->delete($this->input->post('ID'));
			$this->ajax->response(array(
				'Result' => 'OK'
			));
		}
	}

	public function update_user(){
		require_once '../../gp-load.php';
		if( GP::$user->logged_in() && GP::$user->current()->can( 'write', 'project' )){
			$this->load->database();
			$this->load->model('gp_user_model');
			$this->load->model('gp_permission_model');
			$this->load->spark('ajax/1.0.1');
			$this->load->library('ajax');

			$user_to_make_admin = GP::$user->by_login( $this->input->post('user_login') );
			if ($this->input->post('can_admin')){				
				GP::$permission->create( array( 'user_id' => $user_to_make_admin->id, 'action' => 'admin' ) );
			}else{
				$this->gp_permission_model->delete_by_user_id($user_to_make_admin->id);
			}

			$users = $this->gp_user_model->update_by_user_email($this->input->post('user_email'), array(
				'display_name'=> $this->input->post('display_name'),
				'user_email' => $this->input->post('user_email'),
				'user_login' => $this->input->post('user_login'),
				'user_nicename' => $this->input->post('user_nicename'),
				'user_pass' => (strpos($this->input->post('user_pass') , '$') !== 0)?WP_Pass::hash_password( $this->input->post('user_pass') ):$this->input->post('user_pass') 
				));
			$this->ajax->response(array(
				'Result' => 'OK',
				'Records' => GP::$user->by_login( $this->input->post('user_login') )
			));
		}
	}

	public function create_user(){
		require_once '../../gp-load.php';
		if( GP::$user->logged_in() && GP::$user->current()->can( 'write', 'project' )){
			$this->load->spark('ajax/1.0.1');
			$this->load->library('ajax');

			$user_by_login = GP::$user->by_login( $this->input->post('user_login') );
			if($user_by_login){
				$this->ajax->response(array(
					'Result'=>'ERROR',
					'Message'=>'User login exists!'
				));
			}

			$user_by_email = GP::$user->by_email( $this->input->post('user_email') );
			if($user_by_email){
				$this->ajax->response(array(
					'Result'=>'ERROR',
					'Message'=>'User email exists!'
				));
			}else{
				$args = array();
				$args['user_login'] = $this->input->post('user_login');
				$args['user_nicename'] = $this->input->post('user_nicename');
				$args['display_name'] = $this->input->post('display_name');
				$args['user_email'] = $this->input->post('user_email');
				$args['user_pass'] = $this->input->post('user_pass');
				$user = GP::$user->create( $args ) ;
			}
			if ($user){
				$response = array(
					'Result' => 'OK',
					'Record' => $user
				);
			}else{
				$response = array(
					'Result' => 'ERROR',
					'Message' => 'Error creating user'
				);
			}
			$this->ajax->response($response);
		}
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

			$c = preg_replace('/(\$db\[\'default\'\]\[\'hostname\'\]) = \'([^\']*)\'/', '$1 = \''.$host.'\'', $c);
			$c = preg_replace('/(\$db\[\'default\'\]\[\'username\'\]) = \'([^\']*)\'/', '$1 = \''.$username.'\'', $c);
			$c = preg_replace('/(\$db\[\'default\'\]\[\'password\'\]) = \'([^\']*)\'/', '$1 = \''.$password.'\'', $c);
			$c = preg_replace('/(\$db\[\'default\'\]\[\'database\'\]) = \'([^\']*)\'/', '$1 = \''.$database.'\'', $c);
			$c = preg_replace('/(\$db\[\'default\'\]\[\'char_set\'\]) = \'([^\']*)\'/', '$1 = \''.$charset.'\'', $c);
			$c = preg_replace('/(\$db\[\'default\'\]\[\'dbcollat\'\]) = \'([^\']*)\'/', '$1 = \''.$collate.'\'', $c);
			$c = preg_replace('/(\$db\[\'default\'\]\[\'dbprefix\'\]) = \'([^\']*)\'/', '$1 = \''.$prefix.'\'', $c);
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