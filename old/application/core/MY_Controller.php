<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class MY_Controller extends CI_Controller
{
    var $global_options = array();

    var $site_name = '';
    var $site_name_raw = '';

    public function __construct()
    {
		
        parent::__construct();

        if( ! ini_get('date.timezone') ) {
            date_default_timezone_set('ASIA/Dacca');
        }

        //log_message("ERROR", 'test message', false, 'test action');
        /*log_message("DEBUG", 'test DEBUG');
        log_message("INFO", 'test INFO');*/

        $this->load->library('output');
        $this->output->nocache();
        //$this->output->enable_profiler(TRUE);

        $this->load->model('user_model');
        $this->load->model('option_model');


        $logged_in_user = $this->session->userdata('logged_in_user');
        //print_r($logged_in_user); die();
        if ($logged_in_user) {
           //print_r($logged_in_user); die();
            $this->user_model->update_user_activity_time($logged_in_user->id);
        }

//print_r($logged_in_user); die();
        // create global options variable
        $this->global_options = $this->option_model->get_all_options();
        $this->site_name = $this->global_options['site_name'];
        $this->site_name_raw = $this->site_name;

        if ($this->site_name != '') {
            $this->site_name = ' :: '. $this->site_name;
        }
    }
    
}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */