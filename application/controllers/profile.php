<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Profile extends MY_Controller
{
    var $current_page = "profile";
    var $logged_in_user = false;
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
        $this->load->model('user_team_model');

       
        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');


        if ($this->session->userdata('exam_is_started')) {
            redirect('exam');
        } else {
            $this->session->unset_userdata('exam_id');
            $this->session->unset_userdata('user_exam_id');
            $this->session->unset_userdata('exam');
            $this->session->unset_userdata('exam_is_started');
            //$this->session->unset_userdata('exam_is_completed');
        }
        
        // check if already logged in
        if ( ! $this->logged_in_user) {
            $redirect_url = preg_replace('/(delete|update.*|(add).*)\/?[0-9]*$/', '$2', uri_string());
            $this->session->set_flashdata('redirect_url', $redirect_url);
            redirect('login');
        } else {
            if ($this->logged_in_user->user_type == 'Administrator' || $this->logged_in_user->user_type == 'Super Administrator') {
                redirect('administrator/profile');
            }
        }

    }

    public function index()
	{

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'View Profile'));
        $page_info['title'] = 'View Profile'. $this->site_name;
        $page_info['view_page'] = 'user/update_profile_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';

        if ((int)$this->logged_in_user->user_is_default_password == 1) {
            redirect('profile/password');
        }

        $log_message = $this->logged_in_user->user_login .' (User ID: '. $this->logged_in_user->id .') visited profile page.';
        log_message("info", $log_message, false, 'view profile');

        $user = $this->user_model->get_user($this->logged_in_user->id);

        if ($user) {
            $team_name = $this->user_team_model->get_team_name($user->user_team_id);
            if ($team_name) {
                $user->user_team_name = $team_name;
            } else {
                $user->user_team_name = '';
            }
            $page_info['user'] = $user;
        }

        // determine messages
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }

        // load view
		$this->load->view('user/layouts/default', $page_info);
	}

    private function update_profile()
    {
        /*$user_first_name = $this->input->post('user_first_name');
        $user_last_name = $this->input->post('user_last_name');
        $user_email = $this->input->post('user_email');

        if ((int)$this->logged_in_user->user_is_default_password == 1) {
        	redirect('profile/password');
        }

        $data = array(
            'user_first_name' => $user_first_name,
            'user_last_name' => $user_last_name,
            'user_email' => $user_email
        );

        if ($this->user_model->update_user($this->logged_in_user->id, $data)) {

            $this->session->set_flashdata('message_success', 'You profile updated successfully.');

            // reset user session data
            $user = $this->user_model->get_user($this->logged_in_user->id);
            if ($user) {
                $this->logged_in_user->user_first_name = $user_first_name;
                $this->logged_in_user->user_last_name = $user_last_name;
                $this->logged_in_user->user_email = $user_email;

                $this->session->set_userdata('logged_in_user', $this->logged_in_user);
            }

            redirect('profile');

        } else {
            $this->session->set_flashdata('message_error', $this->user_model->error_message. ' Profile Update is unsuccessful.');
            redirect('profile');
        }*/
    }

    public function password()
	{

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Change Password View'));

        $page_info['title'] = 'Change Password'. $this->site_name;
        $page_info['view_page'] = 'user/change_password_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';

        $user = $this->user_model->get_user($this->logged_in_user->id);
        if ($user) {
            $page_info['user'] = $user;
        }

        // determine messages
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }

        // load view
		$this->load->view('user/layouts/default', $page_info);
	}

    public function change_password()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Password Change'));


        $old_password = $this->input->post('old_password');
        $new_password = $this->input->post('new_password');
        $confirm_password = $this->input->post('confirm_password');

        if ($this->user_model->change_password($this->logged_in_user->id, $old_password, $new_password, $confirm_password, true)) {

            $this->session->set_flashdata('message_success', 'You have successfully changed your password.');

            $log_message = $this->logged_in_user->user_login .' (User ID: '. $this->logged_in_user->id .') changed own password.';
            log_message("info", $log_message, false, 'changed password');

            // recreate user session
            $user = $this->user_model->get_user($this->logged_in_user->id);
            if ($user) {
                $this->session->set_userdata('logged_in_user', $user);
            }

            redirect('/');

        } else {
            $this->session->set_flashdata('message_error', $this->user_model->error_message. ' Change Password is unsuccessful.');
            redirect('profile/password');
        }
    }
}

/* End of file profile.php */
/* Location: ./application/controllers/profile.php */