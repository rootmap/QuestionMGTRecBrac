<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Exam extends MY_Controller
{
    var $current_page = "exam";
    var $cat_list = array();
    var $cat_lists = 0;
    var $exam_type_list_filter = array();
    var $exam_status_list_filter = array();
    var $user_exam_id = 0;
    var $setmark = 0;
    var $exam_id = 0;
    var $totalqus=0;
    var $timeQus=0;
    var $QusSet=0;

    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
        $this->form_data = new StdClass;
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('category_model');
        $this->load->model('exam_model');
        $this->load->model('question_model');
        $this->load->model('question_set_model');
        $this->load->model('global/select_global_model');
        $this->load->model('global/insert_global_model');
        $this->load->model('global/delete_global_model');
        $this->load->model('global/update_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');

        $all_categories_tree = $this->category_model->get_categories_recursive();
        $all_categories = $this->category_model->get_padded_categories($all_categories_tree);
        $this->cat_lists=$all_categories;



        $this->cat_list[] = 'Select a Category';
        if ($all_categories) {
            for ($i=0; $i<count($all_categories); $i++) {
                $this->cat_list[$all_categories[$i]->id] = $all_categories[$i]->cat_name;
            }
        }

        $this->exam_type_list_filter[] = 'All types';
        $this->exam_type_list_filter['mcq'] = 'MCQ';
        $this->exam_type_list_filter['descriptive'] = 'Descriptive';

        $this->exam_status_list_filter[] = 'Any status';
        $this->exam_status_list_filter['open'] = 'Open';
        $this->exam_status_list_filter['closed'] = 'Closed';

        // check if logged in
        if ( ! $this->session->userdata('logged_in_user')) {
            $redirect_url = preg_replace('/(delete|update.*|(add).*)\/?[0-9]*$/', '$2', uri_string());
            $this->session->set_flashdata('redirect_url', $redirect_url);
            redirect('login');
        } else {
            $logged_in_user = $this->session->userdata('logged_in_user');
            if ($logged_in_user->user_type == 'User' && !$this->session->userdata('user_privilage_name')) {
                redirect('home');
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
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Exam- List View'));
        // set page specific variables
        $page_info['title'] = 'Manage Exams'. $this->site_name;
        $page_info['view_page'] = 'administrator/exam_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_exam_title')) {
            $this->session->keep_flashdata('filter_exam_title');
            $filter_exam_title = $this->session->flashdata('filter_exam_title');
            $this->form_data->filter_exam_title = $filter_exam_title;
            $filter['filter_exam_title']['field'] = 'exam_title';
            $filter['filter_exam_title']['value'] = $filter_exam_title;
        }
        if ($this->session->flashdata('filter_exam_type')) {
            $this->session->keep_flashdata('filter_exam_type');
            $filter_exam_type = $this->session->flashdata('filter_exam_type');
            $this->form_data->filter_exam_type = $filter_exam_type;
            $filter['filter_exam_type']['field'] = 'exam_type';
            $filter['filter_exam_type']['value'] = $filter_exam_type;
        }
        if ($this->session->flashdata('filter_status')) {
            $this->session->keep_flashdata('filter_status');
            $filter_status = $this->session->flashdata('filter_status');
            $this->form_data->filter_status = $filter_status;
            $filter['filter_status']['field'] = 'exam_status';
            $filter['filter_status']['value'] = $filter_status;
        }
        $page_info['filter'] = $filter;


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        $record_result = $this->exam_model->get_paged_exams($per_page, $page_offset, $filter);

        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];


        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/exam";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;
 
        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'ID'),
                '1' => array('data'=> 'Exam Title'),
                '2' => array('data'=> 'Exam Type', 'class' => 'center', 'width' => '120'),
                '3' => array('data'=> 'Duration', 'class' => 'center', 'width' => '100'),
                '4' => array('data'=> 'No of Set', 'class' => 'center', 'width' => '80'),
                '5' => array('data'=> 'Questions Used', 'class' => 'center', 'width' => '80'),
                '6' => array('data'=> 'Score', 'class' => 'center', 'width' => '100'),
                '7' => array('data'=> 'Status', 'class' => 'center', 'width' => '100'),
                '8' => array('data'=> 'Action', 'class' => 'center', 'width' => '200')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {
                $title_str = '';
                if ($records[$i]->exam_type == 'mcq') {
                    $title_str = '<span class="label label-info">MCQ</span>';
                } elseif ($records[$i]->exam_type == 'descriptive') {
                    $title_str = '<span class="label label-info">Descriptive</span>';
                }

                $duration_str = '';
                if ($records[$i]->exam_time == 0) {
                    $duration_str = 'Unlimited';
                } elseif ($records[$i]->exam_time == 1) {
                    $duration_str = $records[$i]->exam_time .' min.';
                } else {
                    $duration_str = $records[$i]->exam_time .' mins.';
                }

                $noof_questions_str = count($this->select_global_model->Select_array('exm_exam_category',array('exam_id'=>$records[$i]->id)));
                $noof_questions_used_str = $this->question_model->get_used_question_count($records[$i]->id);

                $status_str = '';
                if ($records[$i]->exam_status == 'open') {
                    $status_str = '<span class="label label-success">OPEN</span>';
                } elseif ($records[$i]->exam_status == 'closed') {
                    $status_str = '<span class="label label-important">CLOSED</span>';
                }

                $action_str = '';
                $action_str .= anchor('administrator/exam/preview/'. $records[$i]->id, '<span class="btn btn-success"><i class="icon-print"></i>', 'title="Print"');
                $action_str .= '&nbsp;&nbsp;&nbsp;';
                if(!isSystemAuditor())
                $action_str .= anchor('administrator/exam/edit/'. $records[$i]->id, '<span class="btn btn-success"><i class="icon-edit"></i></span>', 'title="Edit"');
                $action_str .= '&nbsp;&nbsp;&nbsp;';
                //if(!isSystemAuditor())
                //$action_str .= anchor('administrator/print_exam/assign/'. encrypt($records[$i]->id), '<span class="btn btn-success"><i class="icon-print"></i></span>', 'title="Print"');
                /*$action_str .= anchor('administrator/exam/delete/'. $records[$i]->id, '<i class="icon-trash"></i>', array('title'=>'Delete', 'onclick'=>'return confirm(\'Do you really want to delete this record?\')'));*/

                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->id),
                    '1' => array('data'=> $records[$i]->exam_title),
                    '2' => array('data'=> $title_str, 'class' => 'center', 'width' => '120'),
                    '3' => array('data'=> $duration_str, 'class' => 'right', 'width' => '100'),
                    '4' => array('data'=> $noof_questions_str, 'class' => 'center', 'width' => '100'),
                    '5' => array('data'=> $noof_questions_used_str, 'class' => 'center', 'width' => '100'),
                    '6' => array('data'=> $records[$i]->exam_score, 'class' => 'center', 'width' => '100'),
                    '7' => array('data'=> $status_str, 'class' => 'center', 'width' => '100'),
                    '8' => array('data'=> $action_str, 'class' => 'center',  'width' => '200')
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
        $filter_exam_title = $this->input->post('filter_exam_title');
        $filter_exam_type = $this->input->post('filter_exam_type');
        $filter_status = $this->input->post('filter_status');
        $filter_clear = $this->input->post('filter_clear');

        if ($filter_clear == '') {
            if ($filter_exam_title != '') {
                $this->session->set_flashdata('filter_exam_title', $filter_exam_title);
            }
            if ($filter_exam_type != '') {
                $this->session->set_flashdata('filter_exam_type', $filter_exam_type);
            }
            if ($filter_status != '') {
                $this->session->set_flashdata('filter_status', $filter_status);
            }
        } else {
            $this->session->unset_userdata('filter_exam_title');
            $this->session->unset_userdata('filter_exam_type');
            $this->session->unset_userdata('filter_status');
        }

        redirect('administrator/exam');
    }

    /**
     * Display add exam form
     * @return void
     */
    public function add()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Create Exam View'));
        // set page specific variables
        $page_info['title'] = 'Create New Exam'. $this->site_name;
        $page_info['view_page'] = 'administrator/exam_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();

        // determine messages

        $page_info['qSet']  = $this->select_global_model->FlyQuery(array('set_status'=>1),'exm_question_set','select','*',array('id'=>'DESC'));

        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }

        // load view
		$this->load->view('administrator/layouts/default', $page_info);
    }

    public function add_exam()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Create Exam'));
        $page_info['title'] = 'Create New Exam'. $this->site_name;
        $page_info['view_page'] = 'administrator/exam_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;
        $this->_set_fields();
        $this->_set_rules();
        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {
            
            $exam_title = $this->input->post('exam_title');
            $exam_description = $this->input->post('exam_description');
            $exam_type = $this->input->post('exam_type');
            $exam_time = (int)$this->input->post('exam_time');
            $exam_score = (int)$this->input->post('exam_score');
            //$exam_per_page = (int)$this->input->post('exam_per_page');
            $exam_allow_previous = (int)$this->input->post('exam_allow_previous');
            $exam_allow_dontknow = (int)$this->input->post('exam_allow_dontknow');
            //$exam_allow_pause = (int)$this->input->post('exam_allow_pause');
            $exam_allow_negative_marking = (int)$this->input->post('exam_allow_negative_marking');
            $exam_negative_mark_weight = (int)$this->input->post('exam_negative_mark_weight');
            $exam_instructions = $this->input->post('exam_instructions');
            $exam_nop = $this->input->post('exam_nop');


            

            $random = $this->input->post('random');
            $exam_status = $this->input->post('exam_status');
            $exam_allow_result_mail = (int)$this->input->post('exam_allow_result_mail');
            $exam_added = date('Y-m-d H:i:s');
            $exam_expiry_date = $this->input->post('exam_expiry_date');

            $exam_category = $this->input->post('exam_category');
            $noof_questions = $this->input->post('noof_questions');

            //$setNegativeMarking=0;
            if($exam_allow_negative_marking==0)
            {
                
                if (isset($exam_category)) {

                    foreach($exam_category as $setID):
                        $setInfo = $this->exam_model->get_Set($setID);
                        if(!empty($setInfo->neg_mark_per_ques))
                        {
                            $exam_allow_negative_marking= (int)$this->input->post('exam_allow_negative_marking');
                            $exam_negative_mark_weight=$setInfo->neg_mark_per_ques;
                        }
                        //exam_allow_negative_marking
                        //print_r_pre($setInfo); die();
                    endforeach;
                }
            }

            //print_r_pre($this->input->post('exam_category')); die();

            /* validation */
            if ($exam_type == '') { $exam_type = 'mcq'; }
            if ($exam_time < 0) { $exam_time = 0; }
            if ($exam_score <= 0) { $exam_score = 100; }
            if ($exam_negative_mark_weight < 0) { $exam_negative_mark_weight = 0; }
            if ($exam_allow_negative_marking == 0) { $exam_negative_mark_weight = 0; }
            if ($exam_status == '') { $exam_status = 'open'; }

            if ($exam_expiry_date == '') {
                $exam_expiry_date = '';
            } else {
                $day = substr($exam_expiry_date, 0, 2);
                $month = substr($exam_expiry_date, 3, 2);
                $year = substr($exam_expiry_date, 6, 4);
                $exam_expiry_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            }

            if (date("Y-m-d H:i:s") > $exam_expiry_date && $exam_expiry_date != '' && $exam_expiry_date != '0000-00-00 00:00:00') {
                $exam_status = 'closed';
            }

            $exam_questions_set = array();
            $j = 0;
            
           
            $data = array(
                'exam_title' => $exam_title,
                'exam_description' => $exam_description,
                'exam_type' => $exam_type,
                'exam_time' => $exam_time,
                'exam_score' => $exam_score,
                'exam_instructions' => $exam_instructions,
                'exam_nop' => $exam_nop,
                //'exam_per_page' => $exam_per_page,
                'exam_allow_previous' => $exam_allow_previous,
                'exam_allow_dontknow' => $exam_allow_dontknow,
                //'exam_allow_pause' => $exam_allow_pause,
                'exam_allow_negative_marking' => $exam_allow_negative_marking,
                'exam_negative_mark_weight' => $exam_negative_mark_weight,
                'exam_status' => $exam_status,
                'exam_allow_result_mail' => $exam_allow_result_mail,
                'exam_added' => $exam_added,
                'exam_expiry_date' => $exam_expiry_date,
                'exam_questions_set' => $exam_category,
                'exam_random_qus' => $random
            );

            $res = (int)$this->exam_model->add_exam($data);

            if ($res > 0) {
                $this->session->set_flashdata('message_success', 'Add is successful.');
                redirect('administrator/exam/add');
            } else {
                $page_info['message_error'] = 'Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }

    public function edit()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Exam View'));
        // set page specific variables
        $page_info['title'] = 'Edit Exam'. $this->site_name;
        $page_info['view_page'] = 'administrator/exam_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        // prefill form values
        $exam_id = (int)$this->uri->segment(4);
		$exam = $this->exam_model->get_exam($exam_id);

        $this->_set_rules();

		$this->form_data->exam_id = $exam->id;
		$this->form_data->exam_title = $exam->exam_title;
		$this->form_data->exam_description = $exam->exam_description;
		$this->form_data->exam_type = $exam->exam_type;
		$this->form_data->exam_time = $exam->exam_time;
        $this->form_data->exam_score = $exam->exam_score;
        $this->form_data->exam_instructions = $exam->exam_instructions;
		$this->form_data->exam_nop = $exam->exam_nop;
		//$this->form_data->exam_per_page = $exam->exam_per_page;
		$this->form_data->exam_allow_previous = (int)$exam->exam_allow_previous;
		$this->form_data->exam_allow_dontknow = (int)$exam->exam_allow_dontknow;
		//$this->form_data->exam_allow_pause = $exam->exam_allow_pause;
		$this->form_data->exam_allow_negative_marking = (int)$exam->exam_allow_negative_marking;
        $this->form_data->random = $exam->exam_random_qus;
		$this->form_data->exam_negative_mark_weight = $exam->exam_negative_mark_weight;
		$this->form_data->exam_status = $exam->exam_status;
		$this->form_data->exam_allow_result_mail = (int)$exam->exam_allow_result_mail;

        if ($exam->exam_expiry_date == '0000-00-00 00:00:00' || $exam->exam_expiry_date == '') {
            $this->form_data->exam_expiry_date = '';
        } else {
            $this->form_data->exam_expiry_date = date('d/m/Y', strtotime($exam->exam_expiry_date));
        }
        
        $page_info['selected_cat'] = $this->exam_model->get_exam_categories_name($exam->id);
        $data = array();
        foreach ($page_info['selected_cat'] as $key => $value) {
            $data[$key] = $value['category_id'];
        }
        $page_info['qSet'] = $this->select_global_model->select_where_not_in('exm_question_set','id',$data,array('set_status'=>1));
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        // load view
		$this->load->view('administrator/layouts/default', $page_info);
    }

    public function update_exam()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update Exam'));
        // set page specific variables
        $page_info['title'] = 'Edit Exam'. $this->site_name;
        $page_info['view_page'] = 'administrator/exam_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $exam_id = (int)$this->input->post('exam_id');
        
        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->form_data->exam_id = $exam_id;
            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $exam_title = $this->input->post('exam_title');
            $exam_description = $this->input->post('exam_description');
            $exam_type = $this->input->post('exam_type');
            $exam_time = (int)$this->input->post('exam_time');
            $exam_score = (int)$this->input->post('exam_score');
            //$exam_per_page = (int)$this->input->post('exam_per_page');
            $exam_allow_previous = (int)$this->input->post('exam_allow_previous');
            $exam_allow_dontknow = (int)$this->input->post('exam_allow_dontknow');
            //$exam_allow_pause = (int)$this->input->post('exam_allow_pause');
            $exam_allow_negative_marking = (int)$this->input->post('exam_allow_negative_marking');
            $exam_instructions =$this->input->post('exam_instructions');
            $exam_nop =$this->input->post('exam_nop');
            $random = $this->input->post('random');
            $exam_negative_mark_weight = (int)$this->input->post('exam_negative_mark_weight');
            $exam_status = $this->input->post('exam_status');
            $exam_allow_result_mail = (int)$this->input->post('exam_allow_result_mail');
            $exam_expiry_date = $this->input->post('exam_expiry_date');

            $exam_category = $this->input->post('exam_category');
            $noof_questions = $this->input->post('noof_questions');
            $randomSET = $this->input->post('random');
            //echo $randomSET;
            //exit();
            /* validation */
            if ($exam_type == '') { $exam_type = 'mcq'; }
            if ($exam_time < 0) { $exam_time = 0; }
            if ($exam_score <= 0) { $exam_score = 100; }
            if ($exam_negative_mark_weight < 0) { $exam_negative_mark_weight = 0; }
            if ($exam_allow_negative_marking == 0) { $exam_negative_mark_weight = 0; }
            if ($exam_status == '') { $exam_status = 'open'; }

            if ($exam_expiry_date == '') {
                $exam_expiry_date = '';
            } else {
                $day = substr($exam_expiry_date, 0, 2);
                $month = substr($exam_expiry_date, 3, 2);
                $year = substr($exam_expiry_date, 6, 4);
                $exam_expiry_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            }

            if (date("Y-m-d H:i:s") > $exam_expiry_date && $exam_expiry_date != '' && $exam_expiry_date != '0000-00-00 00:00:00') {
                $exam_status = 'closed';
            }

            $exam_questions_set = array();
            $j = 0;
            

            $data = array(
                'exam_title' => $exam_title,
                'exam_description' => $exam_description,
                'exam_type' => $exam_type,
                'exam_time' => $exam_time,
                'exam_score' => $exam_score,
                'exam_instructions' => $exam_instructions,
                'exam_nop' => $exam_nop,
                //'exam_per_page' => $exam_per_page,
                'exam_allow_previous' => $exam_allow_previous,
                'exam_allow_dontknow' => $exam_allow_dontknow,
                //'exam_allow_pause' => $exam_allow_pause,
                'exam_allow_negative_marking' => $exam_allow_negative_marking,
                'exam_negative_mark_weight' => $exam_negative_mark_weight,
                'exam_status' => $exam_status,
                'exam_allow_result_mail' => $exam_allow_result_mail,
                'exam_expiry_date' => $exam_expiry_date,
                'exam_questions_set' => $exam_category,
                'exam_random_qus' => $randomSET,
            );

            if ($this->exam_model->update_exam($exam_id, $data)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else  {
                $this->session->set_flashdata('message_error', $this->exam_model->error_message. ' Update is unsuccessful.');
            }

            redirect('administrator/exam/edit/'. $exam_id);
        }
    }
    
    public function print_question($id)
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Print Question'));
    	// set page specific variables
    	$page_info['title'] = 'Edit Exam'. $this->site_name;
    	$page_info['view_page'] = 'administrator/pdfreport';
    	$page_info['message_error'] = '';
    	$page_info['message_success'] = '';
    	$page_info['message_info'] = '';
    	$page_info['is_edit'] = true;
    	$this->load->model('user_exam_question_model');
    	
    	
    	$exam_id = dencrypt($this->uri->segment(4));
    	$exam_id=(int)($exam_id); 
    	
    	//$this->exam_model->print_question($exam_id); 
     	 
    	$this->load->helper('pdf_helper');
    	
    	tcpdf();
    	$obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    	$obj_pdf->SetCreator(PDF_CREATOR);
    	$title = '';
    	$obj_pdf->SetTitle($title);
    	$obj_pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $title);
    	$obj_pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    	$obj_pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    	$obj_pdf->SetDefaultMonospacedFont('helvetica');
    	$obj_pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    	$obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    	$obj_pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    	$obj_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    	$obj_pdf->SetFont('helvetica', '', 9);
    	$obj_pdf->setFontSubsetting(false);
    	$obj_pdf->AddPage();
    	ob_start();
    	// we can have any view part here like HTML, PHP etc
    	$exam_details=''; 
		echo $exam_details;
    	
    	$content = ob_get_contents();
    	ob_end_clean();
    	$obj_pdf->writeHTML($content, true, false, true, false, '');
    	$obj_pdf->Output('output.pdf', 'I');
    }



    public function preview(){
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Qustion Preview'));

        $page_info['title'] = 'Preview Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/exam/question_preview_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;


        $exam_id = (int)$this->uri->segment(4);

        $question_sets = $this->question_set_model->get_question_set_by_examid($exam_id);
        $page_info['exam_id'] = $exam_id;


        $page_info['question_set'] = $question_sets;

        $this->load->view('administrator/layouts/default', $page_info);


    }

    public function questionsetpreview(){
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Question Set View'));

        $page_info['title'] = 'Question Paper Set '. $this->site_name;
        $page_info['view_page'] = 'administrator/exam/exam_question_set_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;
        $this->logged_in_user = $this->session->userdata('logged_in_user');

        $user = $this->user_model->get_user($this->logged_in_user->id);
        //print_r_pre($user); die();
        $page_info['user'] = $user;


        $exam_id = (int)$this->uri->segment(4);
        $set_id = (int)$this->uri->segment(5);

        $getExamInfo=$this->select_global_model->FlyQuery(array('id'=>$exam_id),'exams','first');
        $geSetInfo=$this->select_global_model->FlyQuery(array('id'=>$set_id),'question_set','first');
        //var_dump($geSetInfo);die;
        $geSetListInfo=$this->select_global_model->FlyQuery(
            array('SELECT qsm.question_id,qsm.question_mark,qsm.is_mandatory,q.ques_text,q.ques_choices,q.ques_type
  FROM exm_question_set_question_map qsm 
  LEFT JOIN exm_questions q ON qsm.question_id=q.id
  WHERE qsm.question_set_id='.$set_id)
        );

        if($set_id)
        {
            $examSetInfo = $this->exam_model->get_Set_Info($set_id);
            $page_info['examSetInfo'] = $examSetInfo;
        }

        $examVenue = $this->exam_model->get_venue($exam_id);
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

        //print_r_pre($geSetListInfo); die();

        $question_sets = $this->question_set_model->get_question_set_by_examid($exam_id);

        $this->setmark = (int)$this->session->userdata('setmark');
        $this->totalqus = (int)$this->session->userdata('totalqus');



        $getExamInfo->exam_score = $this->setmark;

        $page_info['exam_id'] = $exam_id;
        $page_info['exam'] = $getExamInfo;
        $page_info['set_id'] = $set_id;
        $page_info['set_info'] = $geSetInfo;
        $page_info['SetQListInfo'] = $geSetListInfo;
        //var_dump($geSetInfo);die;
        $page_info['setmark'] = (int)$this->session->userdata('setmark');



        $page_info['question_set'] = $question_sets;

        $this->load->view('administrator/layouts/default', $page_info);


    }

    public function add_question_pool()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Category Form View'));
        $page_info['title'] = 'Add Question Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $cat_name = $this->input->post('cat_name');
            $cat_parent = (int)$this->input->post('cat_parent');

            $data = array(
                'pool_name' => $cat_name
               
            );

            $res = (int)$this->exam_model->add_question_pool($data);
                //print_r('dd'); die();
            if ($res > 0) {
                $this->session->set_flashdata('message_success', 'Add is successful.');
                redirect('administrator/exam/edit_pool/'. $res);
            } else {
                $page_info['message_error'] = 'Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }
  

    /* exam delete is not allowed */
    /**
     * Delete a exam
     * @return void
     */
    /*public function delete()
    {
        $exam_id = (int)$this->uri->segment(4);
        $res = $this->exam_model->delete_exam($exam_id);

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Delete is successful.');
        } else {
            $this->session->set_flashdata('message_error', $this->exam_model->error_message .' Delete is unsuccessful.');
        }
        
        redirect('administrator/exam');
    }*/


    // set empty default form field values
	private function _set_fields()
	{
		$this->form_data = new StdClass;
		$this->form_data->exam_id = 0;
        $this->form_data->exam_title = '';
        $this->form_data->exam_description = '';
        $this->form_data->exam_type = '';
        $this->form_data->exam_time = '0';
        $this->form_data->exam_instructions = '->Write your name, contact no. and signature on the space given and check all your details carefully
->Use of Calculator is strictly prohibited
->for incorrect MCQ
->Use of cell phones are strictly prohibited during the time of exam
->The mark allocation is indicated at the beginning of each segment
->You are not permitted to leave the examination room early without the prior consent of the invigilator';
        $this->form_data->exam_nop = 'Internship/Accounts';
        $this->form_data->exam_score = '100';
        //$this->form_data->exam_per_page = '1';
        $this->form_data->exam_allow_previous = '0';
        $this->form_data->exam_allow_dontknow = '0';
        //$this->form_data->exam_allow_pause = '0';
        $this->form_data->exam_allow_negative_marking = '0';
        $this->form_data->exam_negative_mark_weight = '100';
        $this->form_data->random = '';
        $this->form_data->exam_status = 'open';
        $this->form_data->exam_allow_result_mail = '1';
        $this->form_data->exam_expiry_date = '';
        $this->form_data->exam_questions_set = array();

        $this->form_data->filter_exam_title = '';
        $this->form_data->filter_exam_type = '';
        $this->form_data->filter_status = '';
	}

	// validation rules
	private function _set_rules()
	{
		$this->form_validation->set_rules('exam_title', 'Exam Title', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('random', 'Question Random', 'required|trim|xss_clean|strip_tags');

		$this->form_validation->set_rules('exam_description', 'Exam Description', 'trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('exam_type', 'Exam Type', 'trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('exam_time', 'Exam Duration', 'trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('exam_score', 'Exam Score', 'trim|xss_clean|strip_tags');
		//$this->form_validation->set_rules('exam_per_page', 'Questions per page', 'trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('exam_allow_previous', 'Allow Back Button', 'trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('exam_allow_dontknow', 'Allow "Don\'t Know" Choice?', 'trim|xss_clean|strip_tags');
		//$this->form_validation->set_rules('exam_allow_pause', 'Allow Pause', 'trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('exam_allow_negative_marking', 'Allow Negative Marking', 'trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('exam_negative_mark_weight', 'Negative Marking Weight', 'trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('exam_status', 'Exam Status', 'trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('exam_allow_result_mail', 'Allow Sending Result Mail?', 'trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('exam_expiry_date', 'Exam Expiry Date', 'trim|xss_clean|strip_tags');
	}


/** new line here start 23-12-2017**/


     public function create_question_pool()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Create Question Set View'));
        if(!in_array('create_question_pool', $this->session->userdata('user_privilage_name'))){
             redirect('administrator/dashboard');
        }
        $page_info['title'] = 'Add  New Question Set'. $this->site_name;
        $page_info['view_page'] = 'administrator/create_question_set_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;


        $this->_set_fields();
        $this->_set_rules();
        $getData = $this->uri->segment(2);
        if ($getData) {
            $page_info['questionSet'] = $this->select_global_model->select_array('question_set',array('id'=>$getData));

            $page_info['setData'] = $this->question_set_model->mappedSetPool(array('TB.question_set_id'=>$getData));
        }


        //print_r_pre($this->select_global_model->select_array('exm_categories'));

        //$page_info['exaCategory'] = $this->select_global_model->select_array('exm_categories');
        $page_info['exaCategory'] = $this->cat_lists;


        //print_r_pre($page_info['exaCategory']);

        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        // load view
        $this->load->view('administrator/layouts/default', $page_info);

    }

    public function mappingquestion()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Mappaing Question'));

        if(!in_array('create_question_pool', $this->session->userdata('user_privilage_name'))){
             redirect('administrator/dashboard');
        }
        $this->_set_fields();
        $this->_set_rules();
        if (!$this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {
        $sessdata = $this->session->userdata('logged_in_user');
        $fechData = array();
        $ids = $this->input->post('id');
        $set_name = $this->input->post('set_name');
        $set_limit = $this->input->post('set_limit');
        $total_mark = $this->input->post('total_mark');
        //$neg_mark_per_ques = $this->input->post('neg_mark');
        $random = $this->input->post('random');

        if(empty($ids) < 0 || empty($set_name) || empty($set_limit) || empty($ids) || empty($total_mark) || empty($random)){
            $this->session->set_flashdata('message_error', 'Some field are empty');
            redirect('create_question_pool');
        }

        $update_data = array();
        $setcheck = $this->select_global_model->select_array('exm_question_set',array('name'=>$set_name));
        if(!$setcheck){
            $setid=$this->insert_global_model->globalinsert('exm_question_set',array('name'=>$set_name,'set_limit'=>$set_limit,'total_mark'=>$total_mark,'created_by'=>$sessdata->id,'random_qus'=>$random));
            if($ids){
                foreach ($ids as $key => $value) {
                        $fechData[$key]['question_set_id'] = $setid;
                        $fechData[$key]['question_id'] = $value;
                        $fechData[$key]['created_by'] = $sessdata->id;
                    if($this->input->post('is_mandatory_'.$value))
                    {
                        $fechData[$key]['is_mandatory'] = 1;
                    }
                    else{
                        $fechData[$key]['is_mandatory'] = 0;
                    }
                    $fechData[$key]['question_mark'] = $this->input->post('mark_'.$value);
                }
            }


            if($this->insert_global_model->globalinsertbatch('exm_question_set_question_map',$fechData)){
                $this->session->set_flashdata('message_success', 'Mapping successful.');
                redirect('create_question_pool');
            }else{
                $this->session->set_flashdata('message_error', 'Mapping failed!');
                redirect('create_question_pool');
            }

            
        }else{
            $this->session->set_flashdata('message_error', 'Set name already exists!');
            redirect('create_question_pool');
        }
        }
        
    }

    public function questionsetlist($val=0)
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Question - List View'));
        if(!in_array('questionpoollist', $this->session->userdata('user_privilage_name'))){
             redirect('administrator/dashboard');
        }
        $page_info['title'] = 'Question Set List'. $this->site_name;
        $page_info['view_page'] = 'administrator/manage_ques_set_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $records = $this->select_global_model->Select_array('exm_question_set');
        $per_page = $this->config->item('per_page');
        $uri_segment = 2;
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;
        if(isset($_POST['filter'])){
            $setname = $this->input->post('filter_question');
            $questionSets = $this->select_global_model->pool_search($setname,$per_page,$page_offset);
        }else{
            $questionSets = $this->select_global_model->Select_array_limit(array(),$per_page,$page_offset);
        }

        var_dump($questionSets);die;
        $config["base_url"] = base_url() . "questionsetlist";
        $config["total_rows"] = count($records);
        $config['per_page'] = $per_page;
        $this->pagination->initialize($config);
        if ($questionSets) {
            $tbl_heading = array(
                '0' => array('data'=> 'Set Name'),
                '1' => array('data'=> 'No. of Questions', 'class' => 'center', 'width' => '120'),
                '2' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);
            for ($i = 0; $i<count($questionSets); $i++) {
                $action_str = '';
                $action_str .= anchor('create_question_pool/'.$questionSets[$i]['id'], '<i class="icon-edit"></i>', 'title="Edit"');
                $no_of_questions = 10;

                $tbl_row = array(
                    '0' => array('data'=> $questionSets[$i]['name']),
                    '1' => array('data'=> $questionSets[$i]['total'], 'class' => 'center'),
                    '2' => array('data'=> $action_str, 'class' => 'center', 'width' => '100', 'width' => '120')
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['records_table'] = $this->table->generate();
            $page_info['pagin_links'] = $this->pagination->create_links();
        } else {
            $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
            $page_info['pagin_links'] = '';
        }



        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        // load view
        $this->load->view('administrator/layouts/default', $page_info);

    }


    public function updatepool()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update Pool'));
        if(!in_array('create_question_pool', $this->session->userdata('user_privilage_name'))){
             redirect('administrator/dashboard');
        }
        $sessdata = $this->session->userdata('logged_in_user');
        $fechData = array();
        $pool_name = $this->input->post('pool_name');
        $selectedqus = $this->input->post('selectedqus');
        $poolid = $this->input->post('poolid');
        
        if(empty($pool_name) || empty($selectedqus)){
            $this->session->set_flashdata('message_error', 'Some fields are empty!');
            redirect('create_question_pool');
        }
        $pullids = $this->select_global_model->select_array('exm_question_pull',array('id !='=>$poolid,'pull_name'=>$pool_name));
        
        if(!$pullids){

            $this->update_global_model->globalupdate('exm_question_pull',array('id'=>$poolid),array('pull_name'=>$pool_name,'updated_by'=>$sessdata->id,'updated_at'=>date('Y-m-d H:i:s')));
           
            $this->delete_global_model->globaldelete('exm_question_pull_data',array('pull_id'=>$poolid));

            if($selectedqus){
                foreach ($selectedqus as $key => $value) {
                        $fechData[$key]['pull_id'] = $poolid;
                        $fechData[$key]['question_id'] = $value;
                        $fechData[$key]['created_by'] = $sessdata->id;
                }
            }
            //print_r_pre($fechData);
            if($this->insert_global_model->globalinsertbatch('exm_question_pull_data',$fechData)){
                $this->session->set_flashdata('message_success', 'Mapping successful.');
                redirect('create_question_pool/'.$poolid);
            }else{
                $this->session->set_flashdata('message_error', 'Mapping failed!');
                redirect('create_question_pool/'.$poolid);
            }
        }else{
            $this->session->set_flashdata('message_error', 'Pull name already exists!');
            redirect('create_question_pool/'.$poolid);
        }
    }

}

/* End of file exam.php */
/* Location: ./application/controllers/administrator/exam.php */