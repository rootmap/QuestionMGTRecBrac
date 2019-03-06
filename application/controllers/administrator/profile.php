<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Profile extends MY_Controller
{
    var $logged_in_user;
    var $current_page = "profile";
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
		$this->form_data = new StdClass;
        $this->load->model('user_team_model');
        $this->load->model('user_model');
        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');
        
        if($this->user_model->is_super_admin()){
            $users = $this->user_model->get_active_users();
        }else{
            $users = $this->user_model->get_user_list_for_paswword_change();
        }
        $this->user_list[] = 'Select an User';
        if ($users) {
            for ($i=0; $i<count($users); $i++) {
                $this->user_list[$users[$i]->id] = $users[$i]->user_first_name .' '. $users[$i]->user_last_name .' - '. $users[$i]->user_login;
            }
        }


        // check if logged in
        if ( ! $this->session->userdata('logged_in_user')) {
            $redirect_url = preg_replace('/(delete|update.*|(add).*)\/?[0-9]*$/', '$2', uri_string());
            $this->session->set_flashdata('redirect_url', $redirect_url);
            redirect('login');
        } else {
            $this->logged_in_user = $this->session->userdata('logged_in_user');
            if ($this->logged_in_user->user_type == 'User' && !$this->session->userdata('user_privilage_name')) {
                redirect('home');
            }
        }

    }

    public function index()
    {
         $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'View Profile'));
        $page_info['title'] = 'View Profile'. $this->site_name;
        $page_info['view_page'] = 'administrator/update_profile_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';

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
	$this->load->view('administrator/layouts/default', $page_info);
    }

    public function update_profile()
    {
         $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update Profile'));
        $user_first_name = $this->input->post('user_first_name');
        $user_last_name = $this->input->post('user_last_name');
        $user_email = $this->input->post('user_email');

        $data = array(
            'user_first_name' => $user_first_name,
            'user_last_name' => $user_last_name,
            'user_email' => $user_email,
            'user_is_lock' => 0,
            'user_is_active' => 1
        );

        if ($this->user_model->update_user($this->logged_in_user->id, $data, true)) {

            $this->session->set_flashdata('message_success', 'You profile updated successfully.');

            // reset user session data
            $user = $this->user_model->get_user($this->logged_in_user->id);
            if ($user) {
                $this->logged_in_user->user_first_name = $user_first_name;
                $this->logged_in_user->user_last_name = $user_last_name;
                $this->logged_in_user->user_email = $user_email;

                $this->session->set_userdata('logged_in_user', $this->logged_in_user);
            }

            redirect('administrator/profile');

        } else {
            $this->session->set_flashdata('message_error', $this->user_model->error_message. ' Profile Update is unsuccessful.');
            redirect('administrator/profile');
        }
    }

    public function password()
    {
         $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Change Password View'));
        $page_info['title'] = 'Change Password'. $this->site_name;
        $page_info['view_page'] = 'administrator/change_password_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';



        // determine messages
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }

        // load view
	$this->load->view('administrator/layouts/default', $page_info);
    }

    public function change_password()
    {
         $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Change Password'));
        $old_password = $this->input->post('old_password');
        $new_password = $this->input->post('new_password');
        $confirm_password = $this->input->post('confirm_password');

        if ($this->user_model->change_password($this->logged_in_user->id, $old_password, $new_password, $confirm_password, true)) {
            $this->session->set_flashdata('message_success', 'You have successfully changed your password.');
            redirect('/');
        } else {
            $this->session->set_flashdata('message_error', $this->user_model->error_message. ' Change Password is unsuccessful.');
            redirect('administrator/profile/password');
        }
    }
    
    
    public function user_password()
    {
         $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Change User Password View'));
        $page_info['title'] = 'Change Password for User'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_change_password_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';


        // determine messages
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }

        // load view
	$this->load->view('administrator/layouts/default', $page_info);
    }

    public function user_change_password()
    {
         $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Change User Password'));
        $user_id = $this->input->post('user_id');
        $new_password = $this->input->post('new_password');
        $confirm_password = $this->input->post('confirm_password');

        if ($this->user_model->user_change_password($user_id, $new_password, $confirm_password, true)) {
            $this->session->set_flashdata('message_success', 'You have successfully changed user password.');
            redirect('administrator/profile/user_password');
        } else {
            $this->session->set_flashdata('message_error', $this->user_model->error_message. ' Change Password is unsuccessful.');
            redirect('administrator/profile/user_password');
        }
    }
}

/* End of file profile.php */
/* Location: ./application/controllers/profile.php */