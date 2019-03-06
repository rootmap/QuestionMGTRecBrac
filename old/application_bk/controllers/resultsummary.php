<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Resultsummary extends MY_Controller
{
    var $current_page = "result-summary";
    var $logged_in_user = false;

    function __construct()
    {
        parent::__construct();
        $this->load->model('result_model');
        $this->load->model('user_exam_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');


        // check if already logged in
        if ( ! $this->logged_in_user) {
            $redirect_url = preg_replace('/(delete|update.*|(add).*)\/?[0-9]*$/', '$2', uri_string());
            $this->session->set_flashdata('redirect_url', $redirect_url);
            redirect('login');
        } else {
            if ($this->logged_in_user->user_type == 'Administrator' || $this->logged_in_user->user_type == 'Super Administrator') {
                redirect('administrator/dashboard');
            }
            if ((int)$this->logged_in_user->user_is_default_password == 1) {
            	redirect('profile/password');
            }
        }


        if ($this->input->post('user_exam_id')) {

            $user_exam_id = (int)$this->input->post('user_exam_id');
            $this->session->set_flashdata('user_exam_id', $user_exam_id);

            $referer_page = $this->input->post('referer_page');
            if ($referer_page) {
                $this->session->set_flashdata('referer_page', $referer_page);
            }

            redirect('resultsummary');
        }
    }

    public function index()
    {
        $page_info['title'] = 'Summary of Result'. $this->site_name;
        $page_info['view_page'] = 'user/result_summary_view';

        $user_exam_id = (int)$this->session->flashdata('user_exam_id');
        if ($user_exam_id > 0) {

            $this->session->keep_flashdata('user_exam_id');
            $result = $this->result_model->get_result_by_user_exam_id($user_exam_id);

            $page_info['referer_page'] = '';
            if ($this->session->flashdata('referer_page')) {
                $page_info['referer_page'] = $this->session->flashdata('referer_page');
                $this->session->keep_flashdata('referer_page');
            }

            $exam = unserialize($result->result_exam_state);
            $page_info['exam'] = $exam;
            $page_info['result'] = $result;

            $log_message = $this->logged_in_user->user_login .' (User ID: '. $this->logged_in_user->id .') checked a result summary of the exam titled \''. $exam->exam_title .'\' (Exam ID: '. $exam->id .')';
            log_message("info", $log_message, false, 'checked result summary');

        } else {
            redirect('home');
        }


        // load view
		$this->load->view('user/layouts/default', $page_info);
    }
}

/* End of file resultsummary.php */
/* Location: ./application/controllers/resultsummary.php */