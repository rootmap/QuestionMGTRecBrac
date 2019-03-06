<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Mail extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->library('email');
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
