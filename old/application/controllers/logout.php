<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Logout extends MY_Controller
{
    var $logged_in_user = false;
    var $tbl_exam_users_activity    = "exm_user_activity";
    var $tbl_exam_device_tracking   = "exm_device_tracking";

    function __construct()
    {
        parent::__construct();
        $this->logged_in_user = $this->session->userdata('logged_in_user');
        $this->load->model('global/insert_global_model');


    }

    /**
     * Logging out user; destroy all session data.
     * @return void
     */
    function index()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'logout'));
        $insert_data = $this->insert_global_model->globalinsert($this->tbl_exam_device_tracking,array('ip_address'=>$this->input->ip_address(),'user_id'=>$logged_in_user->id,'activity'=>'login'));
        $this->session->sess_destroy();

        $log_message = 'Logout';
        if ($this->logged_in_user) {
            $log_message = $this->logged_in_user->user_login .' (User ID: '. $this->logged_in_user->id .') logged out';
        }

        log_message("info", $log_message, false, 'logout');
        redirect('login');
    }

}

/* End of file logout.php */
/* Location: ./application/controllers/logout.php */