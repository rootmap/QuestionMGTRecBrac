<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Mail extends MY_Controller
{
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
        $this->load->library('email');
        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');
    }

    function index()
    {

        $this->email->from('mail2rupok@gmail.com', 'Arif Uddin');
        $this->email->to('arif@mirtechbd.com');

        $this->email->subject('Email Test');
        $this->email->message('Testing the email class.');


        $app_path = str_replace('system/', '', BASEPATH);
        $this->email->attach($app_path. 'index.php');
        //echo '<pre>'; print_r( $app_path ); echo '</pre>'; die();

        $this->email->send();

        echo $this->email->print_debugger();

    }

}
