<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Sharif Judge online judge
 * @file moss.php
 * @author Mohammad Javad Naderi <mjnaderi@gmail.com>
 */

class Moss extends CI_Controller{

	var $username;
	var $assignment;
	var $user_level;



	public function __construct(){
		parent::__construct();
		$this->load->library('session');
		if ( ! $this->session->userdata('logged_in')){ // if not logged in
			redirect('login');
		}
		$this->username = $this->session->userdata('username');
		$this->assignment = $this->assignment_model->assignment_info($this->user_model->selected_assignment($this->username));
		$this->user_level = $this->user_model->get_user_level($this->username);
		if ( $this->user_level <=1)
			show_error("You have not enough permission to access this page.");
	}







	public function index($assignment_id = FALSE) {
		if ($assignment_id===FALSE)
			show_404();
		$data = array(
			'username'=>$this->username,
			'user_level' => $this->user_level,
			'all_assignments'=>$this->assignment_model->all_assignments(),
			'assignment' => $this->assignment,
			'title'=>'Detect Similar Codes',
			'style'=>'main.css',
			'moss_userid' => $this->settings_model->get_setting('moss_userid'),
			'moss_assignment' => $this->assignment_model->assignment_info($assignment_id),
			'update_time' => $this->assignment_model->get_moss_time($assignment_id)
		);

		$data['moss_problems']=array();
		$assignments_path = rtrim($this->settings_model->get_setting('assignments_root'),'/');
		for($i=1 ; $i<=$data['moss_assignment']['problems'];$i++)
			$data['moss_problems'][$i] = file_get_contents($assignments_path."/assignment_{$assignment_id}/p{$i}/moss_link.txt");

		$this->load->view('templates/header',$data);
		$this->load->view('pages/admin/moss',$data);
		$this->load->view('templates/footer');
	}








	public function update($assignment_id = FALSE) {
		if ($assignment_id===FALSE)
			show_404();
		$userid = $this->input->post('moss_userid');
		$this->settings_model->set_setting('moss_userid', $userid);
		$moss_original = trim( file_get_contents(rtrim($this->settings_model->get_setting('tester_path'),'/').'/moss/moss_original') );
		$moss_path = rtrim($this->settings_model->get_setting('tester_path'),'/').'/moss/moss';
		file_put_contents( $moss_path , str_replace('MOSS_USER_ID',$userid,$moss_original) );
		shell_exec("chmod +x {$moss_path}");
		$this->index($assignment_id);
	}








	public function detect($assignment_id = FALSE) {
		if ($assignment_id===FALSE)
			show_404();
		$this->load->model('submit_model');
		$assignments_path = rtrim($this->settings_model->get_setting('assignments_root'),'/');
		$tester_path = rtrim($this->settings_model->get_setting('tester_path'),'/');
		shell_exec("chmod +x $tester_path/moss/moss");
		$items = $this->submit_model->get_final_submissions($assignment_id, $this->user_level, $this->username);
		$groups = array();
		foreach ($items as $item) {
			if (!isset($groups[$item['problem']]))
				$groups[$item['problem']] = array($item);
			else
				array_push($groups[$item['problem']], $item);
		}
		foreach ($groups as $problem_id => $group) {
			$list = "";
			$assignment_path = $assignments_path."/assignment_{$assignment_id}";
			foreach ($group as $item)
				if ($item['file_type']!='zip')
					$list .= "p{$problem_id}/{$item['username']}/{$item['file_name']}.{$item['file_type']}" . " ";
			$rc = shell_exec("cd $assignment_path; $tester_path/moss/moss $list | grep http >p{$problem_id}/moss_link.txt; echo $?");
			$this->assignment_model->set_moss_time($assignment_id);
		}
		$this->index($assignment_id);
	}


}