<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Download extends MY_Controller
{
    var $current_page = "download";
    var $logged_in_user = false;
    var $error_message;

    function __construct()
    {
        parent::__construct();
        $this->load->helper('file');
        $this->load->helper('download');

        $this->logged_in_user = $this->session->userdata('logged_in_user');


        // check if already logged in
        if ( ! $this->logged_in_user) {
            redirect('login');
        } else {
            /*if ($this->logged_in_user->user_type == 'Administrator') {
                redirect('administrator/dashboard');
            }*/
            if ($this->logged_in_user->user_type == 'User') {
                if ((int)$this->logged_in_user->user_is_default_password == 1) {
                    redirect('profile/password');
                }
            }
        }
    }

    public function index($file_name = '')
    {
        $upload_path = str_replace('system/', 'uploads/', BASEPATH);

        if ($file_name != '') {

            $file_data = read_file($upload_path . $file_name);

            if ($file_data) {
                force_download($file_name, $file_data);
                exit(0);
            } else {
                $this->error_message = 'File not found.';
                echo 'File not found.';
                return FALSE;
            }
        } else {
            echo 'File not found.';
        }
    }
}

/* End of file download.php */
/* Location: ./application/controllers/download.php */