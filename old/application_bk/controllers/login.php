<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends MY_Controller
{
    var $current_page = "login";
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();



		$this->load->library('robi_email');
        $this->load->helper('email');
        $this->load->helper('string');
        $this->load->model('group_privilage_model');
        $this->load->model('global/update_global_model');
        $this->load->model('global/insert_global_model');

        // check if already logged in
        if ($this->session->userdata('logged_in_user')) {

            $logged_in_user = $this->session->userdata('logged_in_user');

            if ($logged_in_user->user_type == 'Administrator' || $logged_in_user->user_type == 'Super Administrator') {
                redirect('administrator/dashboard');
            } else if ($logged_in_user->user_type == 'User') {
                //redirect('home');
				if($this->session->userdata('user_privilage_name')){
					redirect('administrator/dashboard');
				}else{
					redirect('home');
				}
            } else {
                redirect('logout');
            }

        }
    }

    /**
     * Display login form
     * @return void
     */
    public function index()
	{
        $page_info['title'] = 'Login'. $this->site_name;
        $page_info['url_suffix'] = $this->config->item('url_suffix');
        $page_info['message_error'] = '';
        $page_info['redirect_url'] = '';

        if ($this->session->flashdata('redirect_url')) {
            $this->session->keep_flashdata('redirect_url');
            $page_info['redirect_url'] = $this->session->flashdata('redirect_url');
        }
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        if ($this->session->flashdata('show_box')) {
            $page_info['show_box'] = $this->session->flashdata('show_box');
        } else {
            $page_info['show_box'] = '1';
        }
        
		$this->load->view('login', $page_info);
	}

    /**
     * Validate and Authenticate Username and Password then redirect to the dashboard
     * @return void
     */
    public function do_login()
    {
        $this->load->library('session');

        // check authentication
        $username = trim($this->input->post('user_login'));
        $password = trim($this->input->post('user_password'));
        $redirect_url = $this->input->post('redirect_url');

        $logged_in_user = $this->user_model->check_username_password($username, $password);


        $failed_login_message = trim($this->global_options['failed_login_message']);
        if ($failed_login_message == '') {
            $failed_login_message = 'Authentication failed.';
        }

        $locked_login_message = trim($this->global_options['locked_login_message']);
        if ($locked_login_message == '') {
            $locked_login_message = 'Your account has been locked.';
        }


        if( ! $logged_in_user) {

            $this->session->set_flashdata('message_error', $failed_login_message);
            $this->user_model->increment_failed_login_count($username);
            if ($this->user_model->error_message == 'user_locked') {

                $user_id = 0;
                $user = $this->user_model->get_user_by_login($username);
                //print_r($user); die();
                if ($user) {
                    $user_id = $user->id;
                }
                $this->session->set_flashdata('message_error', $locked_login_message);
                log_message("info", 'User locked: consecutive wrong password given', false, 'user locked', $user_id);
            }
            
            log_message("info", 'Unsuccessful login: username/password did not matched', false, 'unsuccessful login');
            redirect('login');

        } else {
            
            if($logged_in_user->user_type == 'Super Administrator') {
                $permited_privilages = $this->group_privilage_model->get_privilages();
            }else{
                $permited_privilages = $this->group_privilage_model->get_permitted_privilages($logged_in_user->id);
            }
            $privilage_name = array();
            if($permited_privilages){
                foreach($permited_privilages as $k=>$v){
                    $privilage_name[] = $v->privilage_name;
                }
            }

            $this->session->set_userdata('user_privilage_name', $privilage_name);

            if ((int)$logged_in_user->user_is_lock == 1) {
                $this->session->set_flashdata('message_error', $locked_login_message);
                log_message("info", 'Unsuccessful login: user account locked', false, 'unsuccessful login');
                redirect('login');
            }

            //print_r_pre($logged_in_user);die;
            $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$logged_in_user->id,
                'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'login'));
            if ($redirect_url != '') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \''. $redirect_url .'\' page', false, 'successful login');
                redirect($redirect_url);
            } elseif ($logged_in_user->user_type == 'Super Administrator') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            } elseif ($logged_in_user->user_type == 'Administrator') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            } elseif ($logged_in_user->user_type == 'Recruitment Manager') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            } elseif ($logged_in_user->user_type == 'Head of HR') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            }elseif ($logged_in_user->user_type == 'Subject Matter Experts') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            }
            elseif ($logged_in_user->user_type == 'System Auditor') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            }
            elseif ($logged_in_user->user_type == 'Recruitment Assistant - Question') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            }
            elseif ($logged_in_user->user_type == 'Recruitment Assistant – Result') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            }
            elseif ($logged_in_user->user_type == 'Examiner') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            }



            elseif ($logged_in_user->user_type == 'User') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'home\' page', false, 'successful login');
				
				if($this->session->userdata('user_privilage_name')){
					redirect('administrator/dashboard');
				}else{
					redirect('home');
				}
            }elseif ($logged_in_user->user_type == 'Candidate') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'home\' page', false, 'successful login');


                redirect('home');

            }

            else {
                $this->session->set_flashdata('message_error', $failed_login_message);
                $this->user_model->increment_failed_login_count($username);
                log_message("info", 'Unsuccessful login: unknown reason', false, 'unsuccessful login');
                redirect('login');
            }
        }
    }

    /**
     * Generate and send new password by email
     * @return void
     */


    public function send_new_password()
    {

        $user_id = strip_slashes(trim($this->input->post('fp_user_login')));


        $permitted=$this->user_model->get_user_password_reset_permission($user_id);
        if($permitted==0)
        {
            $this->session->set_flashdata('message_error', 'You are not elegible to reset your password, Please contact with system admin.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        if ($user_id == '') {
            $this->session->set_flashdata('message_error', 'Login ID can not be empty.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        $user = $this->user_model->get_user_by_login($user_id);

        // user not found
        if ( ! $user) {
            $this->session->set_flashdata('message_error', $this->user_model->error_message);
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        $user_email = $user->user_email;
        $user_active = (int)$user->user_is_active;
        $user_locked = (int)$user->user_is_lock;

        // user is not active
        if ($user_active == 0) {
            $this->session->set_flashdata('message_error', 'User not found with the login id.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        // user is locked
        if ($user_locked == 1) {
            $this->session->set_flashdata('message_error', 'User is locked. Please contact with an Administrator to unlock your account.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        // invalid email address
        if ( ! valid_email($user_email) ) {
            $this->session->set_flashdata('message_error', 'Invaid email address. Please contact with an Administrator to update your email address.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        // error checking complete; try to send an email
        $new_password = random_string();
        $new_password_md5 = md5($new_password);
        $mail_sent_res = $this->robi_email->forgot_password($user, $new_password);

        // unable to send email
        if ( ! $mail_sent_res) {
            $this->session->set_flashdata('message_error', 'There was a problem in sending email. Please try again later.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        // email sent successfully; update db;
        $update_user_res = $this->user_model->reset_password($user->id, $new_password_md5);
        if ($update_user_res) {
            $this->session->set_flashdata('message_success', 'Password reset is successful. Please check your email address for new password.');
            redirect('login');
        } else {
            $this->session->set_flashdata('message_error', 'Database problem occurred. Please try again later.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }
    }
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */