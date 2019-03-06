<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Survey extends MY_Controller
{
    var $current_page = "survey";
    var $question_list = array();
    var $survey_status_list_filter = array();
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();

        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('survey_model');
        $this->load->model('survey_question_model');
        $this->load->model('global/select_global_model');

        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');

        $open_questions = $this->survey_question_model->get_available_questions();

        $this->question_list[] = 'Select Questions';
        if ($open_questions) {
            for ($i=0; $i<count($open_questions); $i++) {
                $this->question_list[$open_questions[$i]->id] = $open_questions[$i]->ques_text;
            }
        }

        $this->survey_status_list_filter[] = 'Any status';
        $this->survey_status_list_filter['open'] = 'Open';
        $this->survey_status_list_filter['closed'] = 'Closed';

        // check if logged in
        if ( ! $this->session->userdata('logged_in_user')) {
            $redirect_url = preg_replace('/(delete|update.*|(add).*)\/?[0-9]*$/', '$2', uri_string());
            $this->session->set_flashdata('redirect_url', $redirect_url);
            redirect('login');
        } else {
            $logged_in_user = $this->session->userdata('logged_in_user');
            if ($logged_in_user->user_type == 'User' && !$this->session->userdata('user_privilage_name')) {
                redirect('landing');
            }
        }
    }

    /**
     * Display paginated list of exams
     * @return void
     */
    public function index()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Surveys View'));
        // set page specific variables
        $page_info['title'] = 'Manage Surveys'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_survey_title')) {
            $this->session->keep_flashdata('filter_survey_title');
            $filter_survey_title = $this->session->flashdata('filter_survey_title');
            $this->form_data->filter_survey_title = $filter_survey_title;
            $filter['filter_survey_title']['field'] = 'survey_title';
            $filter['filter_survey_title']['value'] = $filter_survey_title;
        }
        if ($this->session->flashdata('filter_status')) {
            $this->session->keep_flashdata('filter_status');
            $filter_status = $this->session->flashdata('filter_status');
            $this->form_data->filter_status = $filter_status;
            $filter['filter_status']['field'] = 'survey_status';
            $filter['filter_status']['value'] = $filter_status;
        }
        $page_info['filter'] = $filter;


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        $record_result = $this->survey_model->get_paged_surveys($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];
   
        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/survey";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Survey Title'),
                '1' => array('data'=> 'Description', 'class' => 'center', 'width' => '120'),
                '3' => array('data'=> 'No of Questions', 'class' => 'center', 'width' => '80'),
                '4' => array('data'=> 'Status', 'class' => 'center', 'width' => '100'),
                '5' => array('data'=> 'Action', 'class' => 'center', 'width' => '80')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {
                
                $noof_questions_str = $this->survey_model->get_number_of_questions($records[$i]->id);
                $noof_questions_used_str = '';//$this->survey_question_model->get_used_question_count($records[$i]->id);

                $status_str = '';
                if ($records[$i]->survey_status == 'open') {
                    $status_str = '<span class="label label-success">OPEN</span>';
                } elseif ($records[$i]->survey_status == 'closed') {
                    $status_str = '<span class="label label-important">CLOSED</span>';
                }

                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= anchor('administrator/survey/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                
                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->survey_title),
                    '1' => array('data'=> $records[$i]->survey_description),
                    '3' => array('data'=> $noof_questions_str, 'class' => 'center', 'width' => '100'),
                    '4' => array('data'=> $status_str, 'class' => 'center', 'width' => '100'),
                    '5' => array('data'=> $action_str, 'class' => 'center', 'width' => '100', 'width' => '80')
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['records_table'] = $this->table->generate();
            $page_info['pagin_links'] = $this->pagination->create_links();
        } else {
            $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
            $page_info['pagin_links'] = '';
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



    public function filter()
    {
        $filter_survey_title = $this->input->post('filter_survey_title');
        $filter_status = $this->input->post('filter_status');
        $filter_clear = $this->input->post('filter_clear');

        if ($filter_clear == '') {
            if ($filter_survey_title != '') {
                $this->session->set_flashdata('filter_survey_title', $filter_survey_title);
            }
            if ($filter_status != '') {
                $this->session->set_flashdata('filter_status', $filter_status);
            }
        } else {
            $this->session->unset_userdata('filter_survey_title');
            $this->session->unset_userdata('filter_status');
        }

        redirect('administrator/survey');
    }

    /**
     * Display add exam form
     * @return void
     */
    public function add()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Create New Survey View'));
        // set page specific variables
        $page_info['title'] = 'Create New Survey'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();
        $page_info['catList'] = $this->select_global_model->Select_array('exm_survey_categories');
        //print_r_pre($page_info['catList']);
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

    public function add_survey()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Survey'));
        $page_info['title'] = 'Create New Survey'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $survey_title = $this->input->post('survey_title');
            $survey_description = $this->input->post('survey_description');
            $survey_anms = $this->input->post('survey_anms');
            $survey_status = $this->input->post('survey_status');
            $survey_added = date('Y-m-d H:i:s');
            $survey_expiry_date = $this->input->post('survey_expiry_date');

            $question_ids = $this->input->post('question_ids');

            if ($survey_status == '') { $survey_status = 'open'; }

            if ($survey_expiry_date == '') {
                $survey_expiry_date = '';
            } else {
                $day = substr($survey_expiry_date, 0, 2);
                $month = substr($survey_expiry_date, 3, 2);
                $year = substr($survey_expiry_date, 6, 4);
                $survey_expiry_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            }

            if (date("Y-m-d H:i:s") > $survey_expiry_date && $survey_expiry_date != '' && $survey_expiry_date != '0000-00-00 00:00:00') {
                $survey_status = 'closed';
            }
                            
            if ($question_ids) {
                $question_ids = array_unique($question_ids);
            }
            
            $data = array(
                'survey_title' => $survey_title,
                'survey_description' => $survey_description,
                'survey_status' => $survey_status,
                'survey_anms' => $survey_anms,
                'survey_added' => $survey_added,
                'survey_expired' => $survey_expiry_date,
                'question_ids' => $question_ids
            );

            $res = (int)$this->survey_model->add_survey($data);

            if ($res > 0) {                
                $this->session->set_flashdata('message_success', 'Add is successful.');
                redirect('administrator/survey/edit/'. $res);
            } else {
                $page_info['message_error'] = 'Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }

    public function edit()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Survey View'));
        // set page specific variables
        $page_info['title'] = 'Edit Survey'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;
        $page_info['catList'] = $this->select_global_model->Select_array('exm_survey_categories');
        // prefill form values
        $survey_id = (int)$this->uri->segment(4);
	   $survey = $this->survey_model->get_survey($survey_id);

        $this->_set_rules();
        //print_r($survey->survey_expired); die();
        @$this->form_data->survey_id = $survey->id;
        $this->form_data->survey_title = $survey->survey_title;
        $this->form_data->survey_description = $survey->survey_description;
        $this->form_data->survey_status = $survey->survey_status;
        $this->form_data->survey_anms = $survey->survey_anms;

        if ($survey->survey_expired == '0000-00-00 00:00:00' || $survey->survey_expired == '') {
            $this->form_data->survey_expiry_date = '';
        } else {
            $this->form_data->survey_expiry_date = date('d/m/Y', strtotime($survey->survey_expired));
        }
        $question_ids = $this->survey_model->get_survey_questions($survey->id);
        if($question_ids){
            foreach($question_ids as $k=>$v){
                $this->form_data->question_ids[] = $v->question_id;
            }
        }
        
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        // load view
	$this->load->view('administrator/layouts/default', $page_info);
    }

    public function update_survey()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update Survey'));
        // set page specific variables
        $page_info['title'] = 'Edit Survey'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $survey_id = (int)$this->input->post('survey_id');
        
        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->form_data->survey_id = $survey_id;
            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $survey_title = $this->input->post('survey_title');
            $survey_description = $this->input->post('survey_description');
            $survey_status = $this->input->post('survey_status');
            $survey_added = date('Y-m-d H:i:s');
            $survey_anms = $this->input->post('survey_anms');
            $survey_expiry_date = $this->input->post('survey_expiry_date');

            $question_ids = $this->input->post('question_ids');

            if ($survey_status == '') { $survey_status = 'open'; }

            if ($survey_expiry_date == '') {
                $survey_expiry_date = '';
            } else {
                $day = substr($survey_expiry_date, 0, 2);
                $month = substr($survey_expiry_date, 3, 2);
                $year = substr($survey_expiry_date, 6, 4);
                $survey_expiry_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            }

            if (date("Y-m-d H:i:s") > $survey_expiry_date && $survey_expiry_date != '' && $survey_expiry_date != '0000-00-00 00:00:00') {
                $survey_status = 'closed';
            }
                            
            if ($question_ids) {
                $question_ids = array_unique($question_ids);
            }

            $data = array(
                'survey_title' => $survey_title,
                'survey_description' => $survey_description,
                'survey_status' => $survey_status,
                'survey_added' => $survey_added,
                'survey_anms' => $survey_anms,
                'survey_expired' => $survey_expiry_date,
                'question_ids' => $question_ids
            );

            if ($this->survey_model->update_survey($survey_id, $data)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else  {
                $this->session->set_flashdata('message_error', $this->exam_model->error_message. ' Update is unsuccessful.');
            }

            redirect('administrator/survey/edit/'. $survey_id);
        }
    }
  
    public function getQes($value=''){
        //echo $value;
        $getData = $this->select_global_model->Select_array('exm_survey_questions',array('category_id'=>$value));
        echo json_encode($getData);
    }

    // set empty default form field values
    private function _set_fields()
    {
        @$this->form_data->survey_id = 0;
        $this->form_data->survey_title = '';
        $this->form_data->survey_description = '';
        $this->form_data->survey_status = 'open';
        $this->form_data->survey_anms = 'yes';
        $this->form_data->survey_expiry_date = '';
        $this->form_data->question_ids = array();

        $this->form_data->filter_survey_title = '';
        $this->form_data->filter_status = '';
    }

    // validation rules
    private function _set_rules()
    {
        $this->form_validation->set_rules('survey_title', 'Survey Title', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('survey_description', 'Survey Description', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('survey_status', 'Survey Status', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('question_ids[]', 'Questions', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('survey_expiry_date', 'Survey Expiry Date', 'required|trim|xss_clean|strip_tags');
    }

}

/* End of file exam.php */
/* Location: ./application/controllers/administrator/exam.php */