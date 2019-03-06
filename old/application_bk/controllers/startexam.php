<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Startexam extends MY_Controller
{
    var $current_page = "start-exam";
    var $logged_in_user = false;
    var $user_exam_id = 0;
    var $setmark = 0;
    var $exam_id = 0;
    var $totalqus=0;
    var $timeQus=0;
    var $QusSet=0;

    function __construct()
    {
        parent::__construct();
        $this->load->model('exam_model');
        $this->load->model('category_model');
        $this->load->model('user_exam_model');
        $this->load->model('global/select_global_model');
        $this->logged_in_user = $this->session->userdata('logged_in_user');
        if ($this->session->userdata('exam_is_started')) {
            redirect('exam');
        }

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

        if ($this->input->post('exam_id') && $this->input->post('user_exam_id')) {

            $this->user_exam_id = (int)$this->input->post('user_exam_id');
            $this->exam_id = (int)$this->input->post('exam_id');
            $this->setmark = (int)$this->input->post('setmark');
            $this->totalqus = (int)$this->input->post('totalqus');
            $this->timeQus = (int)$this->input->post('timeQus');
            $this->QusSet = (int)$this->input->post('qus_set');
            $this->immediate_result = (int)$this->input->post('immediate_result');


            if ($this->exam_id > 0 && $this->user_exam_id > 0) {
                
                $this->session->set_userdata('user_exam_id', $this->user_exam_id);
                $this->session->set_userdata('exam_id', $this->exam_id);
                $this->session->set_userdata('setmark', $this->setmark);
                $this->session->set_userdata('totalqus', $this->totalqus);
                $this->session->set_userdata('timeQus', $this->timeQus);
                $this->session->set_userdata('QusSet', $this->QusSet);
                $this->session->set_userdata('immediate_result', $this->immediate_result);

                redirect('startexam');

            } else {
                redirect('home');
            }
        }
    }

    public function index()
	{
        $page_info['title'] = 'Prepare Exam'. $this->site_name;
        $page_info['view_page'] = 'user/start_exam_view';
        $user = $this->user_model->get_user($this->logged_in_user->id);
        //print_r_pre($user); die();
        $page_info['user'] = $user;
        $this->user_exam_id = (int)$this->session->userdata('user_exam_id');
        $this->exam_id = (int)$this->session->userdata('exam_id');
        $this->setmark = (int)$this->session->userdata('setmark');
        $this->totalqus = (int)$this->session->userdata('totalqus');
        $this->timeQus = (int)$this->session->userdata('timeQus');
        $this->QusSet = (int)$this->session->userdata('QusSet');
        //print_r_pre($this->QusSet); die();
        $this->immediate_result = (int)$this->session->userdata('immediate_result');
        if ($this->exam_id <= 0) {
            redirect('home');
        } else {

            // TODO: check if this exam is valid (expired for the user id) to take
            $exam = $this->exam_model->get_exam($this->exam_id);
            $examSet = $this->exam_model->get_Sets($this->exam_id,$exam->exam_random_qus);
            $setID=$examSet->category_id;
            
            $page_info['setID'] = $setID;
            
            if($setID)
            {
                $examSetInfo = $this->exam_model->get_Set_Info($setID);
                $page_info['examSetInfo'] = $examSetInfo;
            }
            


            $examVenue = $this->exam_model->get_venue($this->exam_id);
            $venues='';
            $venuesLocation='';
            if(!empty($examVenue))
            {
                $key=0;
                foreach($examVenue as $venue):
                    if($key==0)
                    {
                        $venues .=$venue['venue_name'];
                        $venuesLocation .=$venue['venue_location'];
                    }
                    else
                    {
                        $venues .=', '.$venue['venue_name'];   
                        $venuesLocation .=', '.$venue['venue_location'];   
                    }
                $key++;                          
                endforeach;
            }
            $page_info['venues'] = $venues;
            $page_info['venue_location'] = $venuesLocation;

            //print_r_pre($page_info['venues']); die();
            
            if ($exam) {
                $exam->exam_score = $this->setmark;
                $exam->immediate_result = $this->immediate_result;
                $exam->exam_ip_address = $this->session->userdata('ip_address');
                $exam->exam_user_agent = $this->session->userdata('user_agent');
                $total_questions = $this->totalqus;

               
                $exam->exam_categories = $this->select_global_model->select_array('exm_question_set',array('id'=>$this->QusSet));
              //print_r_pre($exam->exam_categories);
                $exam->exam_total_questions = $total_questions;
                $page_info['exam'] = $exam;
                //print_r_pre($page_info['exam']);
                $this->session->set_userdata('exam', $exam);
                //var_dump($exam); die();
            } else {
                redirect('home');
            }
        }
        // load view
		$this->load->view('user/layouts/default', $page_info);
	}
}

/* End of file startexam.php */
/* Location: ./application/controllers/startexam.php */