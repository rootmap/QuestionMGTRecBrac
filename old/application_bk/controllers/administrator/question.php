<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Question extends MY_Controller
{
    private $table_name = 'categories';
    var $current_page = "question";
    var $cat_list = array();
    var $cat_list_filter = array();
    var $type_list_filter = array();
    var $expired_list_filter = array();
    private $default_category = 0;

    function __construct()
    {
        parent::__construct();
        $this->form_data = new StdClass;
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->helper('serialize');
        $this->load->library("pagination");
        $this->load->library('excel');
        $this->load->library('table');
        $this->load->library('upload');
        $this->load->library('form_validation');
        $this->load->model('category_model');
        $this->load->model('question_model');
        $this->load->model('global/Select_global_model');


        // prefill dropdowns
        $all_categories_tree = $this->category_model->get_categories_recursive();
        $all_categories = $this->category_model->get_padded_categories($all_categories_tree);

        $this->cat_list[] = 'Select a Category';
        $this->cat_list_filter[] = 'All categories';

        if ($all_categories) {
            for ($i=0; $i<count($all_categories); $i++) {
                $this->cat_list[$all_categories[$i]->id] = $all_categories[$i]->cat_name;
                $this->cat_list_filter[$all_categories[$i]->id] = $all_categories[$i]->cat_name;
            }
        }

        $this->type_list_filter[''] = 'All types';
        $this->type_list_filter['mcq'] = 'MCQ';
        $this->type_list_filter['descriptive'] = 'Descriptive';

        $this->expired_list_filter[''] = 'Any ';
        $this->expired_list_filter['available'] = 'Available';
        $this->expired_list_filter['expired'] = 'Expired';


        $this->default_category = $this->global_options['default_category'];

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

    public function get_categories_recursive($parent_id = 0)
    {
        $parent_id = (int)$parent_id;
        $child_list = $this->get_child_categories($parent_id);
        //$child_list[0]['cat_name'];
        $makePulseCat='';
        for ($i=0; $i < count($child_list); $i++) {
            $child_list[$i]['cat_parent'] = $this->get_categories_recursive($child_list[$i]['cat_parent']);
        }
        return $child_list;
    }

    public function get_child_categories($parent_id = 0)
    {
        $parent_id = (int)$parent_id;
        $sql = 'SELECT * FROM '. $this->db->dbprefix($this->table_name) .' 
                WHERE id = '. $parent_id;
        $res = $this->db->query($sql);
        return $res->result_array();
    }

    public function resolveRecursion(array $result, array $array) {
        if(!empty($array[0]['cat_name']))
        {
            $result[] = $array[0]['cat_name']; 
        }
        if(!empty($array[0]['cat_parent']))
        {
            $result=$this->resolveRecursion($result, $array[0]['cat_parent']); 
        }


        return $result;
    }

    public function concateDesendArray($array=array())
    {
        //print_r_pre($array);
        $returnString=''; 
        if(count($array)!=0)
        {
            for($i=count($array)-1; $i>=0; $i--)
            {

                $returnString.=$array[$i];
                if($i!=0)
                    $returnString.="-";
                
            }
        }

        return $returnString;
    }


    /**
     * Display paginated list of questions
     * @return void
     */
    public function index()
    {
        // set page specific variables
        $page_info['title'] = 'Manage Questions'. $this->site_name;
        $page_info['view_page'] = 'administrator/question_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();

        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_question')) {
            $this->session->keep_flashdata('filter_question');
            $filter_question = $this->session->flashdata('filter_question');
            $this->form_data->filter_question = $filter_question;
            $filter['filter_question']['field'] = 'ques_text';
            $filter['filter_question']['value'] = $filter_question;
        }
        if ($this->session->flashdata('filter_category')) {
            $this->session->keep_flashdata('filter_category');
            $filter_category = (int)$this->session->flashdata('filter_category');
            $this->form_data->filter_category = $filter_category;
            $filter['filter_category']['field'] = 'category_id';
            $filter['filter_category']['value'] = $filter_category;
        }
        if ($this->session->flashdata('filter_type')) {
            $this->session->keep_flashdata('filter_type');
            $filter_type = $this->session->flashdata('filter_type');
            $this->form_data->filter_type = $filter_type;
            $filter['filter_type']['field'] = 'ques_type';
            $filter['filter_type']['value'] = $filter_type;
        }
        if ($this->session->flashdata('filter_expired')) {
            $this->session->keep_flashdata('filter_expired');
            $filter_expired = $this->session->flashdata('filter_expired');
            $this->form_data->filter_expired = $filter_expired;
            $filter['filter_expired']['field'] = 'ques_expiry_date';
            $filter['filter_expired']['value'] = $filter_expired;
        }
        if ($this->session->flashdata('filter_status')) {
            $this->session->keep_flashdata('filter_status');
            $filter_status = $this->session->flashdata('filter_status');
            $this->form_data->filter_status = $filter_status;
            $filter['filter_status']['field'] = 'status';
            $filter['filter_status']['value'] = $filter_status;
        }
        $page_info['filter'] = $filter;

        //print_r_pre($filter);


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->question_model->get_paged_questions($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];


        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/question";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        //print_r_pre($records);
        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'ID', 'min-width' => '30%'),
                '1' => array('data'=> 'Question', 'min-width' => '30%'),
                '2' => array('data'=> 'Category'),
                '3' => array('data'=> 'Details', 'class' => 'center', 'width' => '130'),
                '4' => array('data'=> 'Uses Stat', 'class' => 'center', 'width' => '120'),
                '5' => array('data'=> 'Type', 'class' => 'center', 'width' => '120'),
                '6' => array('data'=> 'Is Expired?', 'class' => 'center', 'width' => '80'),
                '7' => array('data'=> 'Status', 'class' => 'center', 'width' => '120'),
                '8' => array('data'=> 'Created By', 'class' => 'center', 'width' => '80'),
                '9' => array('data'=> 'Action', 'class' => 'center', 'width' => '50')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);


            


            $total_used_question_count = $this->question_model->get_total_used_question_count();

            for ($i = 0; $i<count($records); $i++) {

                $category_str = '';
                if ($records[$i]->category_id > 0) {
                    $category_Array = $this->get_categories_recursive($records[$i]->category_id);
                    //print_r_pre($category_Array);die;

                    $result = $this->resolveRecursion([], $category_Array);
                    $categoryStringCaoncated=$this->concateDesendArray($result); 
                    $category_str = $categoryStringCaoncated;

                    //$this->category_model->get_category($records[$i]->category_id)->cat_name;
                }

                $expired_str = '<span class="label label-success">AVAILABLE</span>';
                if ($records[$i]->ques_expiry_date != '' && $records[$i]->ques_expiry_date != '0000-00-00 00:00:00' && $records[$i]->ques_expiry_date <= date('Y-m-d H:i:s')) {
                    $expired_str = '<span class="label label-important">EXPIRED</span>';
                }

                /*$added_date_str = '';
                if ($records[$i]->ques_added != '' && $records[$i]->ques_added != '0000-00-00 00:00:00') {
                    $added_date_str = date('jS F, Y', strtotime($records[$i]->ques_added));
                }*/

                $question_type = $records[$i]->ques_type;
                if ($question_type == 'mcq') {
                    $number_of_choices = $this->get_number_of_choices($records[$i]->ques_choices);
                    $number_of_right_choices = $this->get_number_of_right_choices($records[$i]->ques_choices);
                    $question_type = '<a href="javascript:void(0)" title="Total choices = '. $number_of_choices .', Right choices = '. $number_of_right_choices .'"><span class="label label-info">MCQ</span></a>';
                } elseif ($question_type = 'descriptive') {
                    $question_type = '<span class="label label-info">DESCRIPTIVE</span>';
                }

                $stat_str = '';
                $uses_stat_str = '';
                if ($records[$i]->ques_type == 'mcq') {

                    $correct = $this->question_model->get_correct_answer_count($records[$i]->id);
                    $wrong = $this->question_model->get_wrong_answer_count($records[$i]->id);
                    $dontknow = $this->question_model->get_dontknow_answer_count($records[$i]->id);
                    $unanswered = $this->question_model->get_unanswered_answer_count($records[$i]->id);
                    $user_count = $this->question_model->get_user_count($records[$i]->id);

                    $total_used_question_in_category_count = $this->question_model->get_total_used_question_in_category_count($records[$i]->category_id);
                    $total_count = $correct + $wrong + $dontknow + $unanswered;


                    $stat_str .= '<a href="'. base_url('administrator/question/stats/correct/'. $records[$i]->id) .'" title="'. $correct .' correct answer(s)"><span class="label label-success">'. $correct .'</span></a>&nbsp;&nbsp;';
                    $stat_str .= '<a href="'. base_url('administrator/question/stats/wrong/'. $records[$i]->id) .'" title="'. $wrong .' wrong answer(s)"><span class="label label-important">'. $wrong .'</span></a>&nbsp;&nbsp;';
                    $stat_str .= '<a href="'. base_url('administrator/question/stats/dontknow/'. $records[$i]->id) .'" title="'. $dontknow .' dont know answer(s)"><span class="label label-info">'. $dontknow .'</span></a>&nbsp;&nbsp;';
                    $stat_str .= '<a href="'. base_url('administrator/question/stats/unanswered/'. $records[$i]->id) .'" title="'. $unanswered .' not answered"><span class="label">'. $unanswered .'</span></a>';
                    $stat_str .= ' | <a href="javascript:void(0)" title="appeared to '. $user_count .' user(s)"><span class="label label-warning">'. $user_count .'</span>';

                    $uses_stat_str .= '<span class="mark" title="question appeared '. $total_count .' times / total '. $total_used_question_in_category_count .' questions appeared from question\'s category">'. $total_count .'/'. $total_used_question_in_category_count .'</span>, ';
                    $uses_stat_str .= '<span class="mark" title="question appeared '. $total_count .' times / total '. $total_used_question_count .' questions appeared overall">'. $total_count .'/'. $total_used_question_count .'</span>';
                }

                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= anchor('administrator/question/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                

                /*if(!isSystemAuditor())
                $status = '<a href="'. base_url('changeQuestionstatus/2:'. $records[$i]->id) .'" title="click here to Approve"><span class="label label-info">Pending</span></a>&nbsp;&nbsp;';
                else{
                    $status = '<span class="label label-info">Pending</span>';
                }

                if($records[$i]->status==2){
                    if(!isSystemAuditor())
                    $status = '<a href="'. base_url('changeQuestionstatus/1:'. $records[$i]->id) .'" title="click here to pending"><span class="label label-success">Approved</span></a>&nbsp;&nbsp;';
                    else
                        $status = '<span class="label label-info">Approved</span>';
                }*/
                if(!isSystemAuditor())
                {
                    if($records[$i]->status==1 || $records[$i]->status==0){
                    $status = '<a href="'. base_url('changeQuestionstatus/2:'. $records[$i]->id) .'" title="click here to Approve"><span class="label label-success">Approve</span></a>&nbsp;&nbsp;<a href="'. base_url('changeQuestionstatus/3:'. $records[$i]->id) .'" title="click here to Reject"><span class="label label-important">Reject</span></a>';
                    }
                    elseif($records[$i]->status==2){
                    $status = '<span class="label label-success">Approve</span>';
                    }elseif($records[$i]->status==3){
                    $status = '<span class="label label-important">Reject</span>';
                    }
                }
                else
                {
                    if($records[$i]->status==1 || $records[$i]->status==0){
                    $status = '<span class="label label-default">Pending</span>';
                    }
                    elseif($records[$i]->status==2){
                    $status = '<span class="label label-success">Approve</span>';
                    }elseif($records[$i]->status==3){
                    $status = '<span class="label label-important">Reject</span>';
                    }
                }
                

                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->id, 'min-width' => '30%'),
                    '1' => array('data'=> $records[$i]->ques_text, 'min-width' => '30%'),
                    '2' => array('data'=> $category_str),
                    '3' => array('data'=> $stat_str, 'class' => 'center', 'width' => '130'),
                    '4' => array('data'=> $uses_stat_str, 'class' => 'center', 'width' => '120'),
                    '5' => array('data'=> $question_type, 'class' => 'center', 'width' => '120'),
                    '6' => array('data'=> $expired_str, 'class' => 'center', 'width' => '80'),
                    '7' => array('data'=> $status, 'class' => 'center', 'width' => '50'),
                    '8' => array('data'=>$records[$i]->created_by_name, 'class' => 'center', 'width' => '50'),
                    '9' => array('data'=> $action_str, 'class' => 'center', 'width' => '50')
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

    
    public function stats($stat_type='', $question_id=0)
    {
        // set page specific variables
        $page_info['title'] = 'Question Statistics'. $this->site_name;
        $page_info['view_page'] = 'administrator/question_stat_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();

        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_question')) {
            $this->session->keep_flashdata('filter_question');
            $filter_question = $this->session->flashdata('filter_question');
            $this->form_data->filter_question = $filter_question;
            $filter['filter_question']['field'] = 'ques_text';
            $filter['filter_question']['value'] = $filter_question;
        }
        if ($this->session->flashdata('filter_category')) {
            $this->session->keep_flashdata('filter_category');
        }
        if ($this->session->flashdata('filter_type')) {
            $this->session->keep_flashdata('filter_type');
        }
        if ($this->session->flashdata('filter_expired')) {
            $this->session->keep_flashdata('filter_expired');
        }
        $page_info['filter'] = $filter;


        $stat_type = trim($stat_type);
        $question_id = (int)$question_id;

        if ($stat_type != 'correct' && $stat_type != 'wrong' && $stat_type != 'dontknow' && $stat_type != 'unanswered') {
            redirect('administrator/question');
        } elseif ($question_id <= 0) {
            redirect('administrator/question');
        }


        $question = $this->question_model->get_question($question_id);
        if ($question) {

            $page_info['question_id'] = $question_id;
            $page_info['correct_count'] = (int)$this->question_model->get_correct_answer_count($question_id);
            $page_info['wrong_count'] = (int)$this->question_model->get_wrong_answer_count($question_id);
            $page_info['dontknow_count'] = (int)$this->question_model->get_dontknow_answer_count($question_id);
            $page_info['unanswered_count'] = (int)$this->question_model->get_unanswered_answer_count($question_id);
            $page_info['total_used_question_count'] = $this->question_model->get_total_used_question_count();
            $page_info['total_used_question_in_category_count'] = $this->question_model->get_total_used_question_in_category_count($question->category_id);
            $page_info['user_count'] = (int)$this->question_model->get_user_count($question_id);
            $page_info['exam_count'] = (int)$this->question_model->get_exam_count($question_id);


            $per_page = $this->config->item('per_page');
            $uri_segment = 6;
            $page_offset = $this->uri->segment($uri_segment);
            $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

            //$record_result = $this->question_model->get_answer_details($per_page, $page_offset, $filter);
            $record_result = $this->question_model->get_answer_details($question_id, $stat_type, $per_page, $page_offset, $filter);
            $page_info['records'] = $record_result['result'];
            $records = $record_result['result'];

            // build paginated list
            $config = array();
            $config["base_url"] = base_url() . "administrator/question/stats/". $stat_type .'/'. $question_id;
            $config['uri_segment'] = $uri_segment;
            $config["total_rows"] = $record_result['count'];
            $this->pagination->initialize($config);


            if ($records && count($records) > 0) {
                // customize and generate records table
                $tbl_heading = array(
                    '0' => array('data'=> 'Exam Title'),
                    '1' => array('data'=> 'Start Date', 'width'=> '120px'),
                    '2' => array('data'=> 'End Date', 'width'=> '120px'),
                    '3' => array('data'=> 'Login ID'),
                    '4' => array('data'=> 'User Name')
                );
                $this->table->set_heading($tbl_heading);

                $tbl_template = array (
                    'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                    'table_close'         => '</table>'
                );
                $this->table->set_template($tbl_template);

                for ($i = 0; $i<count($records); $i++) {

                    $name_str = trim($records[$i]->user_first_name) .' '. trim($records[$i]->user_last_name);
                    $tbl_row = array(
                        '0' => array('data'=> $records[$i]->exam_title),
                        '1' => array('data'=> date('jS M, Y', strtotime($records[$i]->ue_start_date)), 'width'=> '120px'),
                        '2' => array('data'=> date('jS M, Y', strtotime($records[$i]->ue_end_date)), 'width'=> '120px'),
                        '3' => array('data'=> $records[$i]->user_login),
                        '4' => array('data'=> $name_str)
                    );
                    $this->table->add_row($tbl_row);
                }

                $page_info['records_table'] = $this->table->generate();
                $page_info['pagin_links'] = $this->pagination->create_links();

            } else {
                $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
                $page_info['pagin_links'] = '';
            }
        } else {
            redirect('administrator/question');
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
        $filter_question = $this->input->post('filter_question');
        $filter_category = (int)$this->input->post('filter_category');
        $filter_type = $this->input->post('filter_type');
        $filter_expired = $this->input->post('filter_expired');
        $filter_clear = $this->input->post('filter_clear');
        $filter_status = $this->input->post('status');

        if ($filter_clear == '') {
            if ($filter_question != '') {
                $this->session->set_flashdata('filter_question', $filter_question);
            }
            if ($filter_category > 0) {
                $this->session->set_flashdata('filter_category', $filter_category);
            }
            if ($filter_type == 'mcq' || $filter_type == 'descriptive') {
                $this->session->set_flashdata('filter_type', $filter_type);
            }
            if ($filter_expired == 'available' || $filter_expired == 'expired') {
                $this->session->set_flashdata('filter_expired', $filter_expired);
            }
            if ($filter_status !=''){
                $this->session->set_flashdata('filter_status', $filter_status);
            }
        } else {
            $this->session->unset_userdata('filter_question');
            $this->session->unset_userdata('filter_category');
            $this->session->unset_userdata('filter_type');
            $this->session->unset_userdata('filter_expired');
        }

        redirect('administrator/question');
    }

    private function get_number_of_choices($choice_str = '')
    {
        $choice_num = 0;

        if ($choice_str != '') {
            $choice_num = (int)count(maybe_unserialize($choice_str));
        }

        return $choice_num;
    }

    private function get_number_of_right_choices($choice_str = '')
    {
        $right_choice_num = 0;

        if ($choice_str != '') {
            $choices = maybe_unserialize($choice_str);
            for ($i=0; $i<count($choices); $i++) {
                if ((int)$choices[$i]['is_answer'] == 1) {
                    $right_choice_num++;
                }
            }
        }

        return $right_choice_num;
    }

    /**
     * Display add question form
     * @return void
     */
    public function add()
    {
        // set page specific variables
        $page_info['title'] = 'Add New Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/question_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;
        
        $this->_set_fields();
        $this->_set_rules();

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

    public function add_question()
    {
        $page_info['title'] = 'Add New Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/question_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;
        $createdid = $this->session->userdata('logged_in_user');
       // print_r_pre($t->id);

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {



            $category_id = (int)$this->input->post('category_id');
            $ques_text = htmlspecialchars($this->input->post('ques_text'));
            $ques_type = $this->input->post('ques_type');
            $mcq_options = $this->input->post('mcq_options');
            $ques_mandatory = $this->input->post('ques_mandatory');
            $mark = $this->input->post('marks');
            $neg_marks = $this->input->post('neg_marks');
            $mcq_options_right = $this->input->post('mcq_options_right');
            $ques_expiry_date = $this->input->post('ques_expiry_date');
            $ques_added = date('Y-m-d H:i:s');

            $ex=0;
            $testQED =explode('/', $ques_expiry_date); //@Saiful BBL
			$ques_expiry_date =$testQED[2].'-'.$testQED[1].'-'.$testQED[0];//@Saiful BBL



            //echo $ex; die();

            if($ex==0)
            {

                
                 
                if ($category_id <= 0) {
                    $category_id = $this->default_category;
                }

                if ($ques_type == 'mcq') {
                    // building choices array
                    $ques_choices = array();
                    $j = 0;

                    for($i=0; $i<count($mcq_options); $i++) {
                        if (trim($mcq_options[$i]) != '') {

                            $ques_choices[$j]['text'] = htmlspecialchars($mcq_options[$i]);
                            $is_answer = 0;

                            for($k=0; $k<count($mcq_options_right); $k++) {
                                if ($mcq_options_right[$k] == $i) {
                                    $is_answer = 1;
                                    break;
                                }
                            }

                            $ques_choices[$j]['is_answer'] = $is_answer;
                            $j++;
                        }
                    }

                } else  {
                    $ques_choices = '';
                }

                $ques_choices = maybe_serialize($ques_choices);

                // if question type is not selected; set a default one based on the question choice field
                if ($ques_type == '' || ($ques_type != 'mcq' && $ques_type != 'descriptive')) {
                    if ($ques_choices == '') {
                        $ques_type = 'descriptive';
                    } else {
                        $ques_type = 'mcq';
                    }
                }

                if ($ques_expiry_date == '') {
                    $ques_expiry_date = '';
                } else {
                    $day = substr($ques_expiry_date, 0, 2);
                    $month = substr($ques_expiry_date, 3, 2);
                    $year = substr($ques_expiry_date, 6, 4);
                    //$ques_expiry_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
                }
      
                $data = array(
                    'category_id' => $category_id,
                    'ques_text' => $ques_text,
                    'ques_type' => $ques_type,
                    'ques_choices' => $ques_choices,
                    'is_mandatory' => $ques_mandatory,
                    'ques_added' => $ques_added,
                    'created_by' => $createdid->id,
                    'mark' => $mark,
                    'ques_expiry_date'=>$ques_expiry_date, //@Saiful BBL
                    'neg_mark' => $neg_marks,
                    'admin_group' => $this->session->userdata('logged_in_user')->admin_group
                );
               // print_r_pre($data);
                $res = (int)$this->question_model->add_question($data);

                if ($res > 0) {
                    $this->session->set_flashdata('message_success', 'Add is successful.');
                    redirect('administrator/question/add');
                } else {
                    $page_info['message_error'] = $this->question_model->error_message .'Add is unsuccessful.';
                    $this->load->view('administrator/layouts/default', $page_info);
                }
            }
            else
            {
                $page_info['message_error'] = ' Question already exists.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }

    public function bulk()
    {
        // set page specific variables
        $page_info['title'] = 'Add Bulk Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/question_bulk_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';


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

    public function bulk_upload()
    {
        $questions = array();
        $invalid_questions = array();
        $error_messages = array();

        $file_path = '';
        $default_category_id = (int)$this->global_options['default_category'];
        $has_column_header = (int)$this->input->post('question_file_has_column_header');

        // uploading file
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xls|xlsx';

        if ($_FILES['question_file']['tmp_name'] != '' && $_FILES['question_file']['error'] == 0) {

            $this->upload->initialize($config);
            $this->upload->do_upload('question_file');

            $file_error = $this->upload->display_errors();
            $file_data = $this->upload->data();

            if ($file_error == '') {

                $file_path = $file_data['full_path'];

                $objPHPExcel = PHPExcel_IOFactory::load($file_path);
                @unlink($file_path);

                $objPHPExcel->setActiveSheetIndex(0);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                $max_column_name = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $max_column_number = PHPExcel_Cell::columnIndexFromString($max_column_name);

                if ($max_column_number < 6) {
                    $this->session->set_flashdata('message_error', 'File format does not match.');
                    redirect('administrator/question/bulk');
                }

                // remove first row (if $has_column_header == 1)
                // remove empty rows
                $start = 1;
                if ($has_column_header) {
                    $start = 2;
                }

                for ($i=$start; $i<=count($sheetData); $i++) {

                    $question_category = trim($sheetData[$i]['A']);
                    $question_text = trim($sheetData[$i]['F']);
                    $question_type = trim($sheetData[$i]['G']);
                    $question_mark = trim($sheetData[$i]['H']);
                    $question_neg_mark = trim($sheetData[$i]['I']);
                    $is_mandatory = trim($sheetData[$i]['J']);
                    $question_choices = $sheetData[$i]['K'];
                    $right_choices = trim($sheetData[$i]['L']);
                    $question_expiry_date = trim($sheetData[$i]['M']);

                    if ($question_category == '' && $question_text == '' && $question_type == '' && $question_choices == '' && $right_choices == '' && $question_expiry_date == '') {
                        continue;
                    } else {
                        $questions[$i]['category_id'] = $question_category;
                        $questions[$i]['ques_text'] = $question_text;
                        $questions[$i]['ques_type'] = $question_type;
                        $questions[$i]['mark'] = $question_mark;
                        $questions[$i]['neg_mark'] = $question_neg_mark;
                        $questions[$i]['is_mandatory'] = $is_mandatory;
                        $questions[$i]['ques_choices'] = $question_choices;
                        $questions[$i]['ques_expiry_date'] = $question_expiry_date;
                        $questions[$i]['right_choices'] = $right_choices;
                    }
                }

                // check for valid data
                if (count($questions) > 0) {
                    foreach($questions as $row => $question) {

                        $row_has_error = false;

                        $question_category = $question['category_id'];
                        $question_text = $question['ques_text'];
                        $question_type = $question['ques_type'];
                        $question_mark = $question['mark'];
                        $question_neg_mark = $question['neg_mark'];
                        $is_mandatory = $question['is_mandatory'];
                        $question_choices = $question['ques_choices'];
                        $question_expiry_date = $question['ques_expiry_date'];
                        $right_choices = $question['right_choices'];

                        // Question Text is required
                        if ($question_text == '') {
                            $error_messages[$row][] = 'Question is required';
                            $row_has_error = true;
                        } else {
                            // TODO: workaround to allow \ char. may be use &#92; or use
                            $question_text = str_replace('\\', '', $question_text);
                        }

                        // Question Type required; could be 'mcq' or 'descriptive'
                        $question_type = strtolower($question_type);
                        if ($question_type != 'mcq' && $question_type != 'descriptive') {
                            $question_type = '';
                            $error_messages[$row][] = 'Question Type should be \'mcq\' or \'descriptive\'';
                            $row_has_error = true;
                        }

                        // question choices are only required when question type is mcq
                        if ($question_type == 'mcq') {

                            $question_choices_arr = explode(chr(10), $question_choices);
                            for ($i=0; $i<count($question_choices_arr); $i++) {
                                $question_choices_arr[$i] = trim(str_replace('\\', '', $question_choices_arr[$i]));
                                if ($question_choices_arr[$i] == '') {
                                    unset($question_choices_arr[$i]);
                                }
                            }
                            if (count($question_choices_arr) < 2) {
                                $error_messages[$row][] = 'There should be at least 2 Question Choices';
                                $row_has_error = true;
                            } else {
                                unset($question_choices);
                                $question_choices = array();
                                $i = 0;
                                foreach ($question_choices_arr as $key => $value) {
                                    $question_choices[$i] =  array();
                                    $question_choices[$i]['text'] = $value;
                                    $question_choices[$i]['is_answer'] = 0;
                                    $i++;
                                }
                            }

                            // extract right choices
                            $right_choices = explode(',', $right_choices);
                            $no_of_right_choices = 0;
                            for ($i=0; $i<count($right_choices); $i++) {
                                $choice_number = (int)$right_choices[$i];
                                if ($choice_number > 0) {
                                    $choice_number--;
                                    if (isset($question_choices[$choice_number])) {
                                        $question_choices[$choice_number]['is_answer'] = 1;
                                        $no_of_right_choices++;
                                    }
                                }
                            }
                                                       
                            $question_choices = maybe_serialize($question_choices);

                            if ($no_of_right_choices < 1) {
                                $error_messages[$row][] = 'There should be at least 1 right choice of a question';
                                $row_has_error = true;
                            }
                        } else {
                            $question_choices = '';
                        }

                        // question expiry date is optional. if provided, it should be a valid date
                        if ($question_expiry_date != '') {
                            $question_expiry_date = str_replace('-', '/', $question_expiry_date);
                            $question_expiry_date = date('Y-m-d', strtotime($question_expiry_date));
                        }

                        if ($row_has_error) {
                            $invalid_questions[$row] = $question;
                            unset($questions[$row]);
                        } else {
                            
                            // If left empty then Question will be under Default Category.
                            // If entered Category not found, then a new Question Category will be created.
                            $question_category_id = 0;
                            if ($question_category == '') {
                                $question_category_id = 0;
                            } else {
                                $old_category = $this->category_model->get_category($question_category);
                                if ($old_category) {
                                    $question_category_id = (int)$question_category;
                                } else {
                                    $new_category = array();
                                    //$new_category['cat_name'] = $question_category;
                                    //$question_category_id = $this->category_model->add_category($new_category);
                                }

                                //previous version
                                /*
                                $old_category = $this->category_model->get_category_by_name($question_category);
                                if ($old_category) {
                                    $question_category_id = (int)$old_category->id;
                                } else {
                                    $new_category = array();
                                    $new_category['cat_name'] = $question_category;
                                    $question_category_id = $this->category_model->add_category($new_category);
                                }
                                */
                            }
                            $questions[$row]['category_id'] = $question_category_id;
                            $questions[$row]['ques_text'] = $question_text;
                            $questions[$row]['ques_type'] = $question_type;
                            $questions[$row]['mark'] = $question_mark;
                            $questions[$row]['neg_mark'] = $question_neg_mark;
                            $questions[$row]['is_mandatory'] = $is_mandatory;
                            $questions[$row]['ques_choices'] = $question_choices;
                            $questions[$row]['ques_expiry_date'] = $question_expiry_date;
                            $questions[$row]['admin_group'] = $this->session->userdata('logged_in_user')->admin_group;
                            $questions[$row]['created_by'] = $this->session->userdata('logged_in_user')->id;
                        }
                    }
                }

                //print_r_pre($questions);die;

                foreach($questions as $key => $value) {
                    unset($questions[$key]['right_choices']);
                }

                if (count($questions) <= 0 && count($invalid_questions) <= 0) {
                    $this->session->set_flashdata('message_error', 'File does not contain any row.');
                    redirect('administrator/question/bulk');
                }

                $this->session->set_flashdata('bulk_questions', $questions);
                $this->session->set_flashdata('bulk_invalid_questions', $invalid_questions);
                $this->session->set_flashdata('bulk_error_messages', $error_messages);

            } else {
                $this->session->set_flashdata('message_error', $file_error);
                redirect('administrator/question/bulk');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Please upload an Excel file.');
            redirect('administrator/question/bulk');
        }

        $this->session->set_flashdata('bulk_action', 1);
        redirect('administrator/question/bulk_upload_action');
    }

    public function bulk_upload_action()
    {
        // set page specific variables
        $page_info['title'] = 'Take an Action'. $this->site_name;
        $page_info['view_page'] = 'administrator/question_bulk_action_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $page_info['bulk_questions'] = array();
        $page_info['bulk_invalid_questions'] = array();
        $page_info['bulk_error_messages'] = array();


        if ($this->session->flashdata('bulk_action')) {
            $this->session->keep_flashdata('bulk_action');
        }
        if ( (int)$this->session->flashdata('bulk_action') == 0 ) {
            redirect('administrator/question/bulk');
        }


        if ($this->session->flashdata('bulk_questions')) {
            $page_info['bulk_questions'] = $this->session->flashdata('bulk_questions');
            $this->session->keep_flashdata('bulk_questions');
        }
        if ($this->session->flashdata('bulk_invalid_questions')) {
            $page_info['bulk_invalid_questions'] = $this->session->flashdata('bulk_invalid_questions');
            $this->session->keep_flashdata('bulk_invalid_questions');
        }
        if ($this->session->flashdata('bulk_error_messages')) {
            $page_info['bulk_error_messages'] = $this->session->flashdata('bulk_error_messages');
            $this->session->keep_flashdata('bulk_error_messages');
        }


        $bulk_invalid_questions = $page_info['bulk_invalid_questions'];
        $bulk_error_messages = $page_info['bulk_error_messages'];

        if ($bulk_invalid_questions && count($bulk_invalid_questions) < 250) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Question'),
                '1' => array('data'=> 'Error')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            foreach($bulk_invalid_questions as $row => $record) {

                $error_message = '';
                for ($i=0; $i<count($bulk_error_messages[$row]); $i++) {
                    if ($i>0) { $error_message .= '<br />'; }
                    $error_message .= $bulk_error_messages[$row][$i];
                }

                $tbl_row = array(
                    '0' => array('data'=> $record['ques_text']),
                    '1' => array('data'=> $error_message)
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['bulk_invalid_questions_table'] = $this->table->generate();
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

    public function bulk_upload_do_action()
    {
        $bulk_questions = array();

        if ($this->session->flashdata('bulk_questions')) {
            $bulk_questions = $this->session->flashdata('bulk_questions');
        }
        //print_r($bulk_questions);
        // bulk insert
        $this->question_model->add_bulk_questions($bulk_questions);
        $this->session->set_flashdata('message_success', 'Record(s) inserted successfully.');

        redirect('administrator/question/bulk');
    }

    public function edit()
    {
        if(!in_array('questionedit', $this->session->userdata('user_privilage_name')))
        {
           redirect('administrator/dashboard'); 
        }
        // set page specific variables
        $page_info['title'] = 'Edit Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/question_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $this->_set_rules();
        
        // prefill form values
        $question_id = (int)$this->uri->segment(4);
		$question = $this->question_model->get_question($question_id, true);

		$this->form_data->question_id = $question->id;
		$this->form_data->category_id = $question->category_id;
		$this->form_data->ques_text = $question->ques_text;
		$this->form_data->ques_type = $question->ques_type;
        $this->form_data->marks = $question->mark;
        $this->form_data->neg_marks = $question->neg_mark;
        $this->form_data->ques_mandatory = $question->is_mandatory;
		$this->form_data->ques_choices = maybe_unserialize($question->ques_choices);
        $this->form_data->ques_added = date('d/m/Y', strtotime($question->ques_added));
        if ($question->ques_expiry_date == '0000-00-00 00:00:00') {
            $this->form_data->ques_expiry_date = '';
        } else {
            $this->form_data->ques_expiry_date = date('d/m/Y', strtotime($question->ques_expiry_date));
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

    public function update_question()
    {
        // set page specific variables
        $page_info['title'] = 'Edit Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/question_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $question_id = (int)$this->input->post('question_id');

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->form_data->question_id = $question_id;
            $this->load->view('administrator/layouts/default', $page_info);

        } else {
            
            $category_id = (int)$this->input->post('category_id');
            $ques_text = htmlspecialchars($this->input->post('ques_text'));
            $ques_type = $this->input->post('ques_type');
            $marks = $this->input->post('marks');
            $neg_marks = $this->input->post('neg_marks');
            $mcq_options = $this->input->post('mcq_options');
            $ques_mandatory = $this->input->post('ques_mandatory');
            $mcq_options_right = $this->input->post('mcq_options_right');
            $ques_expiry_date = $this->input->post('ques_expiry_date');
            // Added line 1136 and 1137 as requested by Fahad Bhai	@Saiful BBL
			$testQED =explode('/', $ques_expiry_date);
			$ques_expiry_date =$testQED[2].'-'.$testQED[1].'-'.$testQED[0];

            // validation
            if ($category_id <= 0) {
                $category_id = $this->default_category;
            }

            if ($ques_type == 'mcq') {

                // building choices array
                $ques_choices = array();
                $j = 0;

                for($i=0; $i<count($mcq_options); $i++) {
                    if (trim($mcq_options[$i]) != '') {

                        $ques_choices[$j]['text'] = htmlspecialchars($mcq_options[$i]);
                        $is_answer = 0;

                        for($k=0; $k<count($mcq_options_right); $k++) {
                            if ($mcq_options_right[$k] == $i) {
                                $is_answer = 1;
                                break;
                            }
                        }

                        $ques_choices[$j]['is_answer'] = $is_answer;
                        $j++;
                    }
                }

            } else  {
                $ques_choices = '';
            }

            $ques_choices = maybe_serialize($ques_choices);

            // if question type is not selected; set a default one based on the question choice field
            if ($ques_type == '' || ($ques_type != 'mcq' && $ques_type != 'descriptive')) {
                if ($ques_choices == '') {
                    $ques_type = 'descriptive';
                } else {
                    $ques_type = 'mcq';
                }
            }

            if ($ques_expiry_date == '') {
                $ques_expiry_date = '';
            } else {
                $day = substr($ques_expiry_date, 0, 2);
                $month = substr($ques_expiry_date, 3, 2);
                $year = substr($ques_expiry_date, 6, 4);
                //$ques_expiry_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            }

            $data = array(
                'category_id' => $category_id,
                'ques_text' => $ques_text,
                'ques_type' => $ques_type,
                'ques_choices' => $ques_choices,
                'is_mandatory' => $ques_mandatory,
                'ques_expiry_date'=>$ques_expiry_date, //@Saiful BBL
                'mark' => $marks,
                'neg_mark' => $neg_marks,
                'admin_group' => $this->session->userdata('logged_in_user')->admin_group
            );

            if ($this->question_model->update_question($question_id, $data)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else  {
                $this->session->set_flashdata('message_error', 'Update is unsuccessful.');
            }

            redirect('administrator/question/edit/'. $question_id);
        }
    }
    
    public function edit_bulk()
    {
        // set page specific variables
        $page_info['title'] = 'Edit Bulk Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/question_edit_bulk_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';


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

    public function edit_bulk_upload()
    {
        $questions = array();
        $invalid_questions = array();
        $error_messages = array();

        $file_path = '';
        $default_category_id = (int)$this->global_options['default_category'];
        $has_column_header = (int)$this->input->post('question_file_has_column_header');

        // uploading file
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xls|xlsx';

        if ($_FILES['question_file']['tmp_name'] != '' && $_FILES['question_file']['error'] == 0) {

            $this->upload->initialize($config);
            $this->upload->do_upload('question_file');

            $file_error = $this->upload->display_errors();
            $file_data = $this->upload->data();

            if ($file_error == '') {

                $file_path = $file_data['full_path'];

                $objPHPExcel = PHPExcel_IOFactory::load($file_path);
                @unlink($file_path);

                $objPHPExcel->setActiveSheetIndex(0);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                $max_column_name = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $max_column_number = PHPExcel_Cell::columnIndexFromString($max_column_name);

                if ($max_column_number < 7) {
                    $this->session->set_flashdata('message_error', 'File format does not match.');
                    redirect('administrator/question/edit_bulk');
                }

                // remove first row (if $has_column_header == 1)
                // remove empty rows
                $start = 1;
                if ($has_column_header) {
                    $start = 2;
                }

                for ($i=$start; $i<=count($sheetData); $i++) {
                    $question_category = trim($sheetData[$i]['A']);
                    $question_text = trim($sheetData[$i]['F']);
                    $question_text_new = trim($sheetData[$i]['G']);
                    $question_type = trim($sheetData[$i]['H']);
                    $question_mark = trim($sheetData[$i]['I']);
                    $question_neg_mark = trim($sheetData[$i]['J']);
                    $is_mandatory = trim($sheetData[$i]['K']);
                    $question_choices = $sheetData[$i]['L'];
                    $right_choices = trim($sheetData[$i]['M']);
                    $question_expiry_date = trim($sheetData[$i]['N']);

                    if ($question_category == '' && $question_text == '' && $question_text_new == '' && $question_type == '' && $question_choices == '' && $right_choices == '' && $question_expiry_date == '') {
                        continue;
                    } else {
                        $questions[$i]['category_id'] = $question_category;
                        $questions[$i]['ques_text'] = $question_text;
                        $questions[$i]['ques_text_new'] = $question_text_new;
                        $questions[$i]['ques_type'] = $question_type;
                        $questions[$i]['mark'] = $question_mark;
                        $questions[$i]['neg_mark'] = $question_neg_mark;
                        $questions[$i]['is_mandatory'] = $is_mandatory;
                        $questions[$i]['ques_choices'] = $question_choices;
                        $questions[$i]['ques_expiry_date'] = $question_expiry_date;
                        $questions[$i]['right_choices'] = $right_choices;
                    }
                }

                // check for valid data
                if (count($questions) > 0) {
                    foreach($questions as $row => $question) {

                        $row_has_error = false;

                        $question_category = $question['category_id'];
                        $question_text = $question['ques_text'];
                        $question_text_new = $question['ques_text_new'];
                        $question_type = $question['ques_type'];
                        $question_mark = $question['mark'];
                        $question_neg_mark = $question['neg_mark'];
                        $is_mandatory = $question['is_mandatory'];
                        $question_choices = $question['ques_choices'];
                        $question_expiry_date = $question['ques_expiry_date'];
                        $right_choices = $question['right_choices'];

                        // Question Text is required
                        if ($question_text == '') {
                            $error_messages[$row][] = 'Existing Question is required';
                            $row_has_error = true;
                        } else {
                            // TODO: workaround to allow \ char. may be use &#92; or use
                            $question_text = str_replace('\\', '', $question_text);
                        }
                        
                        
                        // If new Question Text is empty then it gets existing question
                        if ($question_text_new == '') {
                            $question_text_new = str_replace('\\', '', $question_text);
                        } else {
                            // TODO: workaround to allow \ char. may be use &#92; or use
                            $question_text_new = str_replace('\\', '', $question_text_new);
                        }

                        // Question Type required; could be 'mcq' or 'descriptive'
                        $question_type = strtolower($question_type);
                        if ($question_type != 'mcq' && $question_type != 'descriptive') {
                            $question_type = '';
                            $error_messages[$row][] = 'Question Type should be \'mcq\' or \'descriptive\'';
                            $row_has_error = true;
                        }

                        // question choices are only required when question type is mcq
                        if ($question_type == 'mcq') {

                            $question_choices_arr = explode(chr(10), $question_choices);
                            for ($i=0; $i<count($question_choices_arr); $i++) {
                                $question_choices_arr[$i] = trim(str_replace('\\', '', $question_choices_arr[$i]));
                                if ($question_choices_arr[$i] == '') {
                                    unset($question_choices_arr[$i]);
                                }
                            }
                            if (count($question_choices_arr) < 2) {
                                $error_messages[$row][] = 'There should be at least 2 Question Choices';
                                $row_has_error = true;
                            } else {
                                unset($question_choices);
                                $question_choices = array();
                                $i = 0;
                                foreach ($question_choices_arr as $key => $value) {
                                    $question_choices[$i] =  array();
                                    $question_choices[$i]['text'] = $value;
                                    $question_choices[$i]['is_answer'] = 0;
                                    $i++;
                                }
                            }

                            // extract right choices
                            $right_choices = explode(',', $right_choices);
                            $no_of_right_choices = 0;
                            for ($i=0; $i<count($right_choices); $i++) {
                                $choice_number = (int)$right_choices[$i];
                                if ($choice_number > 0) {
                                    $choice_number--;
                                    if (isset($question_choices[$choice_number])) {
                                        $question_choices[$choice_number]['is_answer'] = 1;
                                        $no_of_right_choices++;
                                    }
                                }
                            }
                                                       
                            $question_choices = maybe_serialize($question_choices);

                            if ($no_of_right_choices < 1) {
                                $error_messages[$row][] = 'There should be at least 1 right choice of a question';
                                $row_has_error = true;
                            }
                        } else {
                            $question_choices = '';
                        }

                        // question expiry date is optional. if provided, it should be a valid date
                        if ($question_expiry_date != '') {
                            $question_expiry_date = str_replace('-', '/', $question_expiry_date);
                            $question_expiry_date = date('Y-m-d', strtotime($question_expiry_date));
                        }
                        
                        
                        // If left empty then Question will be under Default Category.
                        // If entered Category not found, then a new Question Category will be created.
                        $question_category_id = 0;
                        if ($question_category == '') {
                            $question_category_id = 0;
                        } else {
                            $old_category = $this->category_model->get_category($question_category);
                            if ($old_category) {
                                $question_category_id = (int)$question_category;
                            } else {
                               $error_messages[$row][] = 'Question Category is required';
                               $row_has_error = true;
                            }
                        }
                        
                        if($question_category_id > 0){
                            //echo $question_category_id.'   '.$question_text; die;
                            $is_exist_question = $this->question_model->check_question($question_category_id, $question_text);
                            if(!$is_exist_question){
                               $error_messages[$row][] = 'Question Category or Question text does not match with existing question';
                               $row_has_error = true;
                            }
                        }
                        

                        if ($row_has_error) {
                            $invalid_questions[$row] = $question;
                            unset($questions[$row]);
                        } else {
                            $questions[$row]['category_id'] = $question_category_id;
                            $questions[$row]['ques_text'] = $question_text;
                            $questions[$row]['ques_text_new'] = $question_text_new;
                            $questions[$row]['ques_type'] = $question_type;
                            $questions[$row]['mark'] = $question_mark;
                            $questions[$row]['neg_mark'] = $question_neg_mark;
                            $questions[$row]['is_mandatory'] = $is_mandatory;
                            $questions[$row]['ques_choices'] = $question_choices;
                            $questions[$row]['ques_expiry_date'] = $question_expiry_date;
                            $questions[$row]['admin_group'] = $this->session->userdata('logged_in_user')->admin_group;
                        }
                    }
                }
                //print_r_pre($questions);die;


                foreach($questions as $key => $value) {
                    unset($questions[$key]['right_choices']);
                }

                if (count($questions) <= 0 && count($invalid_questions) <= 0) {
                    $this->session->set_flashdata('message_error', 'File does not contain any row.');
                    redirect('administrator/question/edit_bulk');
                }

                $this->session->set_flashdata('bulk_questions', $questions);
                $this->session->set_flashdata('bulk_invalid_questions', $invalid_questions);
                $this->session->set_flashdata('bulk_error_messages', $error_messages);

            } else {
                $this->session->set_flashdata('message_error', $file_error);
                redirect('administrator/question/edit_bulk');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Please upload an Excel file.');
            redirect('administrator/question/edit_bulk');
        }

        $this->session->set_flashdata('bulk_action', 1);
        redirect('administrator/question/edit_bulk_upload_action');
    }

    public function edit_bulk_upload_action()
    {
        // set page specific variables
        $page_info['title'] = 'Take an Action'. $this->site_name;
        $page_info['view_page'] = 'administrator/question_edit_bulk_action_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $page_info['bulk_questions'] = array();
        $page_info['bulk_invalid_questions'] = array();
        $page_info['bulk_error_messages'] = array();


        if ($this->session->flashdata('bulk_action')) {
            $this->session->keep_flashdata('bulk_action');
        }
        if ( (int)$this->session->flashdata('bulk_action') == 0 ) {
            redirect('administrator/question/edit_bulk');
        }


        if ($this->session->flashdata('bulk_questions')) {
            $page_info['bulk_questions'] = $this->session->flashdata('bulk_questions');
            $this->session->keep_flashdata('bulk_questions');
        }
        if ($this->session->flashdata('bulk_invalid_questions')) {
            $page_info['bulk_invalid_questions'] = $this->session->flashdata('bulk_invalid_questions');
            $this->session->keep_flashdata('bulk_invalid_questions');
        }
        if ($this->session->flashdata('bulk_error_messages')) {
            $page_info['bulk_error_messages'] = $this->session->flashdata('bulk_error_messages');
            $this->session->keep_flashdata('bulk_error_messages');
        }


        $bulk_invalid_questions = $page_info['bulk_invalid_questions'];
        $bulk_error_messages = $page_info['bulk_error_messages'];

        if ($bulk_invalid_questions && count($bulk_invalid_questions) < 250) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Question'),
                '1' => array('data'=> 'Error')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            foreach($bulk_invalid_questions as $row => $record) {

                $error_message = '';
                for ($i=0; $i<count($bulk_error_messages[$row]); $i++) {
                    if ($i>0) { $error_message .= '<br />'; }
                    $error_message .= $bulk_error_messages[$row][$i];
                }

                $tbl_row = array(
                    '0' => array('data'=> $record['ques_text']),
                    '1' => array('data'=> $error_message)
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['bulk_invalid_questions_table'] = $this->table->generate();
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

    public function edit_bulk_upload_do_action()
    {
        $bulk_questions = array();

        if ($this->session->flashdata('bulk_questions')) {
            $bulk_questions = $this->session->flashdata('bulk_questions');
        }

        // bulk edit
        $this->question_model->edit_bulk_questions($bulk_questions);
        $this->session->set_flashdata('message_success', 'Record(s) updated successfully.');

        redirect('administrator/question/edit_bulk');
    }

    // Question can’t be deleted, if it already in the system (already added in a exam or question answered by a user);
    /**
     * Delete a question
     * @return void
     */
    /*public function delete()
    {
        $question_id = (int)$this->uri->segment(4);
        $res = $this->question_model->delete_question($question_id);

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Delete is successful.');
        } else {
            $this->session->set_flashdata('message_error', 'Delete is unsuccessful.');
        }
        
        redirect('administrator/question');
    }*/

    // set empty default form field values
    private function _set_fields()
    {
		$this->form_data = new StdClass;
		$this->form_data->question_id = 0;
        $this->form_data->category_id = 0;
        $this->form_data->ques_text = '';
        $this->form_data->ques_type = '';
        $this->form_data->ques_mandatory = '';
        $this->form_data->marks = '';
        $this->form_data->neg_marks = '';
        $this->form_data->ques_expiry_date = '';
        $this->form_data->filter_question = '';
        $this->form_data->filter_category = 0;
        $this->form_data->filter_type = '';
        $this->form_data->filter_expired = '';
    }

    // validation rules
    private function _set_rules()
    {
        $this->form_validation->set_rules('ques_mandatory ', 'Is Mandatory', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('category_id ', 'Category', 'trim|xss_clean|strip_tags');

        $this->form_validation->set_rules('ques_text', 'Question Text', 'required|trim');
        $this->form_validation->set_rules('marks', 'Question Marks', 'required|trim');
        $this->form_validation->set_rules('ques_type', 'Question Type', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ques_expiry_date', 'Expiry Date', 'trim|xss_clean|strip_tags');

    }

    public function questionPending()
    {
        // set page specific variables
        $page_info['title'] = 'Manage Questions (Pending)'. $this->site_name;
        $page_info['view_page'] = 'administrator/qms_page/questionpendingview';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();

        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_question')) {
            $this->session->keep_flashdata('filter_question');
            $filter_question = $this->session->flashdata('filter_question');
            $this->form_data->filter_question = $filter_question;
            $filter['filter_question']['field'] = 'ques_text';
            $filter['filter_question']['value'] = $filter_question;
        }
        if ($this->session->flashdata('filter_category')) {
            $this->session->keep_flashdata('filter_category');
            $filter_category = (int)$this->session->flashdata('filter_category');
            $this->form_data->filter_category = $filter_category;
            $filter['filter_category']['field'] = 'category_id';
            $filter['filter_category']['value'] = $filter_category;
        }
        if ($this->session->flashdata('filter_type')) {
            $this->session->keep_flashdata('filter_type');
            $filter_type = $this->session->flashdata('filter_type');
            $this->form_data->filter_type = $filter_type;
            $filter['filter_type']['field'] = 'ques_type';
            $filter['filter_type']['value'] = $filter_type;
        }
        if ($this->session->flashdata('filter_expired')) {
            $this->session->keep_flashdata('filter_expired');
            $filter_expired = $this->session->flashdata('filter_expired');
            $this->form_data->filter_expired = $filter_expired;
            $filter['filter_expired']['field'] = 'ques_expiry_date';
            $filter['filter_expired']['value'] = $filter_expired;
        }
        if ($this->session->flashdata('filter_status')) {
            $this->session->keep_flashdata('filter_status');
            $filter_status = $this->session->flashdata('filter_status');
            $this->form_data->filter_status = $filter_status;
            $filter['filter_status']['field'] = 'status';
            $filter['filter_status']['value'] = $filter_status;
        }
        //$filter['filter_status']['field'] = 'status';
        //$filter['filter_status']['value'] = 1;
        $page_info['filter'] = $filter;

        //print_r_pre($filter);


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        //echo $uri_segment.'-'.$per_page; die();

        $record_result = $this->question_model->get_paged_questionsPending($per_page, $page_offset, $filter);
        //print_r_pre($record_result);die;
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];
        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/questionpending";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        //print_r_pre($records);
        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'ID', 'min-width' => '30%'),
                '1' => array('data'=> 'Question', 'min-width' => '30%'),
                '2' => array('data'=> 'Category'),
                '3' => array('data'=> 'Details', 'class' => 'center', 'width' => '130'),
                '4' => array('data'=> 'Uses Stat', 'class' => 'center', 'width' => '120'),
                '5' => array('data'=> 'Type', 'class' => 'center', 'width' => '120'),
                '6' => array('data'=> 'Is Expired?', 'class' => 'center', 'width' => '80'),
                '7' => array('data'=> 'Status', 'class' => 'center', 'width' => '120'),
                '8' => array('data'=> 'Created By', 'class' => 'center', 'width' => '80'),
                '9' => array('data'=> 'Action', 'class' => 'center', 'width' => '50')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            $total_used_question_count = $this->question_model->get_total_used_question_count();

            for ($i = 0; $i<count($records); $i++) {

                $category_str = '';
                if ($records[$i]->category_id > 0) {
                    $category_Array = $this->get_categories_recursive($records[$i]->category_id);
                    $result = $this->resolveRecursion([], $category_Array);
                    $categoryStringCaoncated=$this->concateDesendArray($result); 
                    $category_str = $categoryStringCaoncated;

                    //$this->category_model->get_category($records[$i]->category_id)->cat_name;
                    /*$category_str = $this->category_model->get_category($records[$i]->category_id)->cat_name;*/
                }

                $expired_str = '<span class="label label-success">AVAILABLE</span>';
                if ($records[$i]->ques_expiry_date != '' && $records[$i]->ques_expiry_date != '0000-00-00 00:00:00' && $records[$i]->ques_expiry_date <= date('Y-m-d H:i:s')) {
                    $expired_str = '<span class="label label-important">EXPIRED</span>';
                }

                /*$added_date_str = '';
                if ($records[$i]->ques_added != '' && $records[$i]->ques_added != '0000-00-00 00:00:00') {
                    $added_date_str = date('jS F, Y', strtotime($records[$i]->ques_added));
                }*/

                $question_type = $records[$i]->ques_type;
                if ($question_type == 'mcq') {
                    $number_of_choices = $this->get_number_of_choices($records[$i]->ques_choices);
                    $number_of_right_choices = $this->get_number_of_right_choices($records[$i]->ques_choices);
                    $question_type = '<a href="javascript:void(0)" title="Total choices = '. $number_of_choices .', Right choices = '. $number_of_right_choices .'"><span class="label label-info">MCQ</span></a>';
                } elseif ($question_type = 'descriptive') {
                    $question_type = '<span class="label label-info">DESCRIPTIVE</span>';
                }

                $stat_str = '';
                $uses_stat_str = '';
                if ($records[$i]->ques_type == 'mcq') {

                    $correct = $this->question_model->get_correct_answer_count($records[$i]->id);
                    $wrong = $this->question_model->get_wrong_answer_count($records[$i]->id);
                    $dontknow = $this->question_model->get_dontknow_answer_count($records[$i]->id);
                    $unanswered = $this->question_model->get_unanswered_answer_count($records[$i]->id);
                    $user_count = $this->question_model->get_user_count($records[$i]->id);

                    $total_used_question_in_category_count = $this->question_model->get_total_used_question_in_category_count($records[$i]->category_id);
                    $total_count = $correct + $wrong + $dontknow + $unanswered;


                    $stat_str .= '<a href="'. base_url('administrator/question/stats/correct/'. $records[$i]->id) .'" title="'. $correct .' correct answer(s)"><span class="label label-success">'. $correct .'</span></a>&nbsp;&nbsp;';
                    $stat_str .= '<a href="'. base_url('administrator/question/stats/wrong/'. $records[$i]->id) .'" title="'. $wrong .' wrong answer(s)"><span class="label label-important">'. $wrong .'</span></a>&nbsp;&nbsp;';
                    $stat_str .= '<a href="'. base_url('administrator/question/stats/dontknow/'. $records[$i]->id) .'" title="'. $dontknow .' dont know answer(s)"><span class="label label-info">'. $dontknow .'</span></a>&nbsp;&nbsp;';
                    $stat_str .= '<a href="'. base_url('administrator/question/stats/unanswered/'. $records[$i]->id) .'" title="'. $unanswered .' not answered"><span class="label">'. $unanswered .'</span></a>';
                    $stat_str .= ' | <a href="javascript:void(0)" title="appeared to '. $user_count .' user(s)"><span class="label label-warning">'. $user_count .'</span>';

                    $uses_stat_str .= '<span class="mark" title="question appeared '. $total_count .' times / total '. $total_used_question_in_category_count .' questions appeared from question\'s category">'. $total_count .'/'. $total_used_question_in_category_count .'</span>, ';
                    $uses_stat_str .= '<span class="mark" title="question appeared '. $total_count .' times / total '. $total_used_question_count .' questions appeared overall">'. $total_count .'/'. $total_used_question_count .'</span>';
                }
                $action_str = '';
                if( in_array('questionedit', $this->session->userdata('user_privilage_name'))){
                    if(!isSystemAuditor())
                    $action_str .= anchor('administrator/question/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                }
                

                
                /*$status = '<a href="'. base_url('changeQuestionstatus/2:'. $records[$i]->id) .'" title="click here to Approve"><span class="label label-info">Pending</span></a>&nbsp;&nbsp;';
                if($records[$i]->status==2){
                    $status = '<a href="'. base_url('changeQuestionstatus/1:'. $records[$i]->id) .'" title="click here to pending"><span class="label label-success">Approved</span></a>&nbsp;&nbsp;';
                }*/

                if(!isSystemAuditor())
                {
                    if($records[$i]->status==1 || $records[$i]->status==0){
                    $status = '<a href="'. base_url('changeQuestionstatusquestionpending/2:'. $records[$i]->id) .'" title="click here to Approve"><span class="label label-success">Approve</span></a>&nbsp;&nbsp;<a href="'. base_url('changeQuestionstatusquestionpending/3:'. $records[$i]->id) .'" title="click here to Reject"><span class="label label-important">Reject</span></a>';
                    }
                    elseif($records[$i]->status==2){
                    $status = '<span class="label label-success">Approve</span>';
                    }elseif($records[$i]->status==3){
                    $status = '<span class="label label-important">Reject</span>';
                    }
                }
                else
                {
                    if($records[$i]->status==1 || $records[$i]->status==0){
                    $status = '<span class="label label-default">Pending</span>';
                    }
                    elseif($records[$i]->status==2){
                    $status = '<span class="label label-success">Approve</span>';
                    }elseif($records[$i]->status==3){
                    $status = '<span class="label label-important">Reject</span>';
                    }
                }


                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->id, 'min-width' => '30%'),
                    '1' => array('data'=> $records[$i]->ques_text, 'min-width' => '30%'),
                    '2' => array('data'=> $category_str),
                    '3' => array('data'=> $stat_str, 'class' => 'center', 'width' => '130'),
                    '4' => array('data'=> $uses_stat_str, 'class' => 'center', 'width' => '120'),
                    '5' => array('data'=> $question_type, 'class' => 'center', 'width' => '120'),
                    '6' => array('data'=> $expired_str, 'class' => 'center', 'width' => '80'),
                    '7' => array('data'=> $status, 'class' => 'center', 'width' => '50'),
                    '8' => array('data'=>$records[$i]->created_by_name, 'class' => 'center', 'width' => '50'),
                    '9' => array('data'=> $action_str, 'class' => 'center', 'width' => '50')
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


    public function filterpending()
    {
        $filter_question = $this->input->post('filter_question');
        $filter_category = (int)$this->input->post('filter_category');
        $filter_type = $this->input->post('filter_type');
        $filter_expired = $this->input->post('filter_expired');
        $filter_clear = $this->input->post('filter_clear');
        $filter_status = $this->input->post('status');

        if ($filter_clear == '') {
            if ($filter_question != '') {
                $this->session->set_flashdata('filter_question', $filter_question);
            }
            if ($filter_category > 0) {
                $this->session->set_flashdata('filter_category', $filter_category);
            }
            if ($filter_type == 'mcq' || $filter_type == 'descriptive') {
                $this->session->set_flashdata('filter_type', $filter_type);
            }
            if ($filter_expired == 'available' || $filter_expired == 'expired') {
                $this->session->set_flashdata('filter_expired', $filter_expired);
            }
            if ($filter_status !=''){
                $this->session->set_flashdata('filter_status', $filter_status);
            }
        } else {
            $this->session->unset_userdata('filter_question');
            $this->session->unset_userdata('filter_category');
            $this->session->unset_userdata('filter_type');
            $this->session->unset_userdata('filter_expired');
        }

        redirect('administrator/questionpending');
    }



    public function questionApproved()
    {
        // set page specific variables
        $page_info['title'] = 'Manage Questions (Approve)'. $this->site_name;
        $page_info['view_page'] = 'administrator/qms_page/questionapprovedview';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();

        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_question')) {
            $this->session->keep_flashdata('filter_question');
            $filter_question = $this->session->flashdata('filter_question');
            $this->form_data->filter_question = $filter_question;
            $filter['filter_question']['field'] = 'ques_text';
            $filter['filter_question']['value'] = $filter_question;
        }
        if ($this->session->flashdata('filter_category')) {
            $this->session->keep_flashdata('filter_category');
            $filter_category = (int)$this->session->flashdata('filter_category');
            $this->form_data->filter_category = $filter_category;
            $filter['filter_category']['field'] = 'category_id';
            $filter['filter_category']['value'] = $filter_category;
        }
        if ($this->session->flashdata('filter_type')) {
            $this->session->keep_flashdata('filter_type');
            $filter_type = $this->session->flashdata('filter_type');
            $this->form_data->filter_type = $filter_type;
            $filter['filter_type']['field'] = 'ques_type';
            $filter['filter_type']['value'] = $filter_type;
        }
        if ($this->session->flashdata('filter_expired')) {
            $this->session->keep_flashdata('filter_expired');
            $filter_expired = $this->session->flashdata('filter_expired');
            $this->form_data->filter_expired = $filter_expired;
            $filter['filter_expired']['field'] = 'ques_expiry_date';
            $filter['filter_expired']['value'] = $filter_expired;
        }
        if ($this->session->flashdata('filter_status')) {
            $this->session->keep_flashdata('filter_status');
            $filter_status = $this->session->flashdata('filter_status');
            $this->form_data->filter_status = $filter_status;
            $filter['filter_status']['field'] = 'status';
            $filter['filter_status']['value'] = $filter_status;
        }
        $filter['filter_status']['field'] = 'status';
        $filter['filter_status']['value'] = 2;
        $page_info['filter'] = $filter;

        //print_r_pre($filter);


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->question_model->get_paged_questions($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];
        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/questionpending";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        //print_r_pre($records);
        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'ID', 'min-width' => '30%'),
                '1' => array('data'=> 'Question', 'min-width' => '30%'),
                '2' => array('data'=> 'Category'),
                '3' => array('data'=> 'Details', 'class' => 'center', 'width' => '130'),
                '4' => array('data'=> 'Uses Stat', 'class' => 'center', 'width' => '120'),
                '5' => array('data'=> 'Type', 'class' => 'center', 'width' => '120'),
                '6' => array('data'=> 'Is Expired?', 'class' => 'center', 'width' => '80'),
                '7' => array('data'=> 'Status', 'class' => 'center', 'width' => '80'),
                '8' => array('data'=> 'Created By', 'class' => 'center', 'width' => '80'),
                '9' => array('data'=> 'Action', 'class' => 'center', 'width' => '50')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            $total_used_question_count = $this->question_model->get_total_used_question_count();

            for ($i = 0; $i<count($records); $i++) {

                $category_str = '';
                if ($records[$i]->category_id > 0) {
                    $category_Array = $this->get_categories_recursive($records[$i]->category_id);
                    $result = $this->resolveRecursion([], $category_Array);
                    $categoryStringCaoncated=$this->concateDesendArray($result); 
                    $category_str = $categoryStringCaoncated;

                    //$this->category_model->get_category($records[$i]->category_id)->cat_name;
                    /*$category_str = $this->category_model->get_category($records[$i]->category_id)->cat_name;*/
                }

                $expired_str = '<span class="label label-success">AVAILABLE</span>';
                if ($records[$i]->ques_expiry_date != '' && $records[$i]->ques_expiry_date != '0000-00-00 00:00:00' && $records[$i]->ques_expiry_date <= date('Y-m-d H:i:s')) {
                    $expired_str = '<span class="label label-important">EXPIRED</span>';
                }

                /*$added_date_str = '';
                if ($records[$i]->ques_added != '' && $records[$i]->ques_added != '0000-00-00 00:00:00') {
                    $added_date_str = date('jS F, Y', strtotime($records[$i]->ques_added));
                }*/

                $question_type = $records[$i]->ques_type;
                if ($question_type == 'mcq') {
                    $number_of_choices = $this->get_number_of_choices($records[$i]->ques_choices);
                    $number_of_right_choices = $this->get_number_of_right_choices($records[$i]->ques_choices);
                    $question_type = '<a href="javascript:void(0)" title="Total choices = '. $number_of_choices .', Right choices = '. $number_of_right_choices .'"><span class="label label-info">MCQ</span></a>';
                } elseif ($question_type = 'descriptive') {
                    $question_type = '<span class="label label-info">DESCRIPTIVE</span>';
                }

                $stat_str = '';
                $uses_stat_str = '';
                if ($records[$i]->ques_type == 'mcq') {

                    $correct = $this->question_model->get_correct_answer_count($records[$i]->id);
                    $wrong = $this->question_model->get_wrong_answer_count($records[$i]->id);
                    $dontknow = $this->question_model->get_dontknow_answer_count($records[$i]->id);
                    $unanswered = $this->question_model->get_unanswered_answer_count($records[$i]->id);
                    $user_count = $this->question_model->get_user_count($records[$i]->id);

                    $total_used_question_in_category_count = $this->question_model->get_total_used_question_in_category_count($records[$i]->category_id);
                    $total_count = $correct + $wrong + $dontknow + $unanswered;


                    $stat_str .= '<a href="'. base_url('administrator/question/stats/correct/'. $records[$i]->id) .'" title="'. $correct .' correct answer(s)"><span class="label label-success">'. $correct .'</span></a>&nbsp;&nbsp;';
                    $stat_str .= '<a href="'. base_url('administrator/question/stats/wrong/'. $records[$i]->id) .'" title="'. $wrong .' wrong answer(s)"><span class="label label-important">'. $wrong .'</span></a>&nbsp;&nbsp;';
                    $stat_str .= '<a href="'. base_url('administrator/question/stats/dontknow/'. $records[$i]->id) .'" title="'. $dontknow .' dont know answer(s)"><span class="label label-info">'. $dontknow .'</span></a>&nbsp;&nbsp;';
                    $stat_str .= '<a href="'. base_url('administrator/question/stats/unanswered/'. $records[$i]->id) .'" title="'. $unanswered .' not answered"><span class="label">'. $unanswered .'</span></a>';
                    $stat_str .= ' | <a href="javascript:void(0)" title="appeared to '. $user_count .' user(s)"><span class="label label-warning">'. $user_count .'</span>';

                    $uses_stat_str .= '<span class="mark" title="question appeared '. $total_count .' times / total '. $total_used_question_in_category_count .' questions appeared from question\'s category">'. $total_count .'/'. $total_used_question_in_category_count .'</span>, ';
                    $uses_stat_str .= '<span class="mark" title="question appeared '. $total_count .' times / total '. $total_used_question_count .' questions appeared overall">'. $total_count .'/'. $total_used_question_count .'</span>';
                }
                $action_str = '';
                if( in_array('questionedit', $this->session->userdata('user_privilage_name'))){
                    if(!isSystemAuditor())
                    $action_str .= anchor('administrator/question/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                }
                

                /*$status = '<a href="'. base_url('changeQuestionstatus/2:'. $records[$i]->id) .'" title="click here to Approve"><span class="label label-info">Pending</span></a>&nbsp;&nbsp;';
                if($records[$i]->status==2){
                    $status = '<a href="'. base_url('changeQuestionstatus/1:'. $records[$i]->id) .'" title="click here to pending"><span class="label label-success">Approved</span></a>&nbsp;&nbsp;';
                }*/

                if(!isSystemAuditor())
                {
                    $status = '<a href="'. base_url('changeQuestionstatusquestionappreov/3:'. $records[$i]->id) .'" title="click here to Reject"><span class="label label-success">Approve</span></a>';
                }
                else
                {
                    $status = '<span class="label label-success">Approve</span>';
                }





                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->id, 'min-width' => '30%'),
                    '1' => array('data'=> $records[$i]->ques_text, 'min-width' => '30%'),
                    '2' => array('data'=> $category_str),
                    '3' => array('data'=> $stat_str, 'class' => 'center', 'width' => '130'),
                    '4' => array('data'=> $uses_stat_str, 'class' => 'center', 'width' => '120'),
                    '5' => array('data'=> $question_type, 'class' => 'center', 'width' => '120'),
                    '6' => array('data'=> $expired_str, 'class' => 'center', 'width' => '80'),
                    '7' => array('data'=> $status, 'class' => 'center', 'width' => '50'),
                    '8' => array('data'=>$records[$i]->created_by_name, 'class' => 'center', 'width' => '50'),
                    '9' => array('data'=> $action_str, 'class' => 'center', 'width' => '50')
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

    public function questionRejected()
    {
        // set page specific variables
        $page_info['title'] = 'Manage Questions (Rejects)'. $this->site_name;
        $page_info['view_page'] = 'administrator/qms_page/questionrejectedview';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();

        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_question')) {
            $this->session->keep_flashdata('filter_question');
            $filter_question = $this->session->flashdata('filter_question');
            $this->form_data->filter_question = $filter_question;
            $filter['filter_question']['field'] = 'ques_text';
            $filter['filter_question']['value'] = $filter_question;
        }
        if ($this->session->flashdata('filter_category')) {
            $this->session->keep_flashdata('filter_category');
            $filter_category = (int)$this->session->flashdata('filter_category');
            $this->form_data->filter_category = $filter_category;
            $filter['filter_category']['field'] = 'category_id';
            $filter['filter_category']['value'] = $filter_category;
        }
        if ($this->session->flashdata('filter_type')) {
            $this->session->keep_flashdata('filter_type');
            $filter_type = $this->session->flashdata('filter_type');
            $this->form_data->filter_type = $filter_type;
            $filter['filter_type']['field'] = 'ques_type';
            $filter['filter_type']['value'] = $filter_type;
        }
        if ($this->session->flashdata('filter_expired')) {
            $this->session->keep_flashdata('filter_expired');
            $filter_expired = $this->session->flashdata('filter_expired');
            $this->form_data->filter_expired = $filter_expired;
            $filter['filter_expired']['field'] = 'ques_expiry_date';
            $filter['filter_expired']['value'] = $filter_expired;
        }
        if ($this->session->flashdata('filter_status')) {
            $this->session->keep_flashdata('filter_status');
            $filter_status = $this->session->flashdata('filter_status');
            $this->form_data->filter_status = $filter_status;
            $filter['filter_status']['field'] = 'status';
            $filter['filter_status']['value'] = $filter_status;
        }
        $filter['filter_status']['field'] = 'status';
        $filter['filter_status']['value'] = 3;
        $page_info['filter'] = $filter;

        //print_r_pre($filter);


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->question_model->get_paged_questions($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];
        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/questionrejected";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        //print_r_pre($records);
        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'ID', 'min-width' => '30%'),
                '1' => array('data'=> 'Question', 'min-width' => '30%'),
                '2' => array('data'=> 'Category'),
                '3' => array('data'=> 'Details', 'class' => 'center', 'width' => '130'),
                '4' => array('data'=> 'Uses Stat', 'class' => 'center', 'width' => '120'),
                '5' => array('data'=> 'Type', 'class' => 'center', 'width' => '120'),
                '6' => array('data'=> 'Is Expired?', 'class' => 'center', 'width' => '80'),
                '7' => array('data'=> 'Status', 'class' => 'center', 'width' => '80'),
                '8' => array('data'=> 'Created By', 'class' => 'center', 'width' => '80'),
                '9' => array('data'=> 'Action', 'class' => 'center', 'width' => '50')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            $total_used_question_count = $this->question_model->get_total_used_question_count();

            for ($i = 0; $i<count($records); $i++) {

                $category_str = '';
                if ($records[$i]->category_id > 0) {
                    $category_Array = $this->get_categories_recursive($records[$i]->category_id);
                    $result = $this->resolveRecursion([], $category_Array);
                    $categoryStringCaoncated=$this->concateDesendArray($result); 
                    $category_str = $categoryStringCaoncated;

                    //$this->category_model->get_category($records[$i]->category_id)->cat_name;
                    /*$category_str = $this->category_model->get_category($records[$i]->category_id)->cat_name;*/
                }

                $expired_str = '<span class="label label-success">AVAILABLE</span>';
                if ($records[$i]->ques_expiry_date != '' && $records[$i]->ques_expiry_date != '0000-00-00 00:00:00' && $records[$i]->ques_expiry_date <= date('Y-m-d H:i:s')) {
                    $expired_str = '<span class="label label-important">EXPIRED</span>';
                }

                /*$added_date_str = '';
                if ($records[$i]->ques_added != '' && $records[$i]->ques_added != '0000-00-00 00:00:00') {
                    $added_date_str = date('jS F, Y', strtotime($records[$i]->ques_added));
                }*/

                $question_type = $records[$i]->ques_type;
                if ($question_type == 'mcq') {
                    $number_of_choices = $this->get_number_of_choices($records[$i]->ques_choices);
                    $number_of_right_choices = $this->get_number_of_right_choices($records[$i]->ques_choices);
                    $question_type = '<a href="javascript:void(0)" title="Total choices = '. $number_of_choices .', Right choices = '. $number_of_right_choices .'"><span class="label label-info">MCQ</span></a>';
                } elseif ($question_type = 'descriptive') {
                    $question_type = '<span class="label label-info">DESCRIPTIVE</span>';
                }

                $stat_str = '';
                $uses_stat_str = '';
                if ($records[$i]->ques_type == 'mcq') {

                    $correct = $this->question_model->get_correct_answer_count($records[$i]->id);
                    $wrong = $this->question_model->get_wrong_answer_count($records[$i]->id);
                    $dontknow = $this->question_model->get_dontknow_answer_count($records[$i]->id);
                    $unanswered = $this->question_model->get_unanswered_answer_count($records[$i]->id);
                    $user_count = $this->question_model->get_user_count($records[$i]->id);

                    $total_used_question_in_category_count = $this->question_model->get_total_used_question_in_category_count($records[$i]->category_id);
                    $total_count = $correct + $wrong + $dontknow + $unanswered;


                    $stat_str .= '<a href="'. base_url('administrator/question/stats/correct/'. $records[$i]->id) .'" title="'. $correct .' correct answer(s)"><span class="label label-success">'. $correct .'</span></a>&nbsp;&nbsp;';
                    $stat_str .= '<a href="'. base_url('administrator/question/stats/wrong/'. $records[$i]->id) .'" title="'. $wrong .' wrong answer(s)"><span class="label label-important">'. $wrong .'</span></a>&nbsp;&nbsp;';
                    $stat_str .= '<a href="'. base_url('administrator/question/stats/dontknow/'. $records[$i]->id) .'" title="'. $dontknow .' dont know answer(s)"><span class="label label-info">'. $dontknow .'</span></a>&nbsp;&nbsp;';
                    $stat_str .= '<a href="'. base_url('administrator/question/stats/unanswered/'. $records[$i]->id) .'" title="'. $unanswered .' not answered"><span class="label">'. $unanswered .'</span></a>';
                    $stat_str .= ' | <a href="javascript:void(0)" title="appeared to '. $user_count .' user(s)"><span class="label label-warning">'. $user_count .'</span>';

                    $uses_stat_str .= '<span class="mark" title="question appeared '. $total_count .' times / total '. $total_used_question_in_category_count .' questions appeared from question\'s category">'. $total_count .'/'. $total_used_question_in_category_count .'</span>, ';
                    $uses_stat_str .= '<span class="mark" title="question appeared '. $total_count .' times / total '. $total_used_question_count .' questions appeared overall">'. $total_count .'/'. $total_used_question_count .'</span>';
                }
                $action_str = '';
                if( in_array('questionedit', $this->session->userdata('user_privilage_name'))){
                    if(!isSystemAuditor())
                    $action_str .= anchor('administrator/question/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                }
                

                /*$status = '<a href="'. base_url('changeQuestionstatus/2:'. $records[$i]->id) .'" title="click here to Approve"><span class="label label-info">Pending</span></a>&nbsp;&nbsp;';
                if($records[$i]->status==2){
                    $status = '<a href="'. base_url('changeQuestionstatus/1:'. $records[$i]->id) .'" title="click here to pending"><span class="label label-success">Approved</span></a>&nbsp;&nbsp;';
                }*/

                if(!isSystemAuditor())
                {
                    $status = '<a href="'. base_url('changeQuestionstatusquestionreject/2:'. $records[$i]->id) .'" title="click here to Approve"><span class="label label-important">Reject</span></a>';
                }
                else
                {
                    $status = '<span class="label label-success">Reject</span>';
                }





                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->id, 'min-width' => '30%'),
                    '1' => array('data'=> $records[$i]->ques_text, 'min-width' => '30%'),
                    '2' => array('data'=> $category_str),
                    '3' => array('data'=> $stat_str, 'class' => 'center', 'width' => '130'),
                    '4' => array('data'=> $uses_stat_str, 'class' => 'center', 'width' => '120'),
                    '5' => array('data'=> $question_type, 'class' => 'center', 'width' => '120'),
                    '6' => array('data'=> $expired_str, 'class' => 'center', 'width' => '80'),
                    '7' => array('data'=> $status, 'class' => 'center', 'width' => '50'),
                    '8' => array('data'=>$records[$i]->created_by_name, 'class' => 'center', 'width' => '50'),
                    '9' => array('data'=> $action_str, 'class' => 'center', 'width' => '50')
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
    }//question reject list

    public function filterapprove()
    {
        $filter_question = $this->input->post('filter_question');
        $filter_category = (int)$this->input->post('filter_category');
        $filter_type = $this->input->post('filter_type');
        $filter_expired = $this->input->post('filter_expired');
        $filter_clear = $this->input->post('filter_clear');
        $filter_status = $this->input->post('status');

        if ($filter_clear == '') {
            if ($filter_question != '') {
                $this->session->set_flashdata('filter_question', $filter_question);
            }
            if ($filter_category > 0) {
                $this->session->set_flashdata('filter_category', $filter_category);
            }
            if ($filter_type == 'mcq' || $filter_type == 'descriptive') {
                $this->session->set_flashdata('filter_type', $filter_type);
            }
            if ($filter_expired == 'available' || $filter_expired == 'expired') {
                $this->session->set_flashdata('filter_expired', $filter_expired);
            }
            if ($filter_status !=''){
                $this->session->set_flashdata('filter_status', $filter_status);
            }
        } else {
            $this->session->unset_userdata('filter_question');
            $this->session->unset_userdata('filter_category');
            $this->session->unset_userdata('filter_type');
            $this->session->unset_userdata('filter_expired');
        }

        redirect('questionapproved');
    }

}

/* End of file question.php */
/* Location: ./application/controllers/administrator/question.php */