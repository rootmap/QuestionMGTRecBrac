<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Survey_question extends MY_Controller
{
    var $current_page = "survey_question";
    var $cat_list = array();
    var $cat_list_filter = array();
    var $type_list_filter = array();
    var $expired_list_filter = array();
    var $tbl_exam_users_activity    = "exm_user_activity";
    private $default_survey_category = 0;

    function __construct()
    {
        parent::__construct();

        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->helper('serialize');
        $this->load->library("pagination");
        $this->load->library('excel');
        $this->load->library('table');
        $this->load->library('upload');
        $this->load->library('form_validation');
        $this->load->model('survey_category_model');
        $this->load->model('global/update_global_model');
        $this->load->model('survey_question_model');

        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');

        
        // prefill dropdowns
        $all_categories_tree = $this->survey_category_model->get_categories_recursive();
        $all_categories = $this->survey_category_model->get_padded_categories($all_categories_tree);

        $this->cat_list[] = 'Select a Category';
        $this->cat_list_filter[] = 'All categories';

        if ($all_categories) {
            for ($i=0; $i<count($all_categories); $i++) {
                $this->cat_list[$all_categories[$i]->id] = $all_categories[$i]->cat_name;
                $this->cat_list_filter[$all_categories[$i]->id] = $all_categories[$i]->cat_name;
            }
        }
        
        
        $this->type_list_filter[''] = 'All types';
        $this->type_list_filter['option_based'] = 'Option Based';
        $this->type_list_filter['descriptive'] = 'Descriptive';

        $this->expired_list_filter[''] = 'Any ';
        $this->expired_list_filter['available'] = 'Available';
        $this->expired_list_filter['expired'] = 'Expired';

        if(!isset($this->global_options['default_survey_category']))
        {
            $this->global_options['default_survey_category']=0;
        }
        $this->default_survey_category= $this->global_options['default_survey_category'];
        
        
        
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
     * Display paginated list of questions
     * @return void
     */
    public function index()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Survey Questions View'));
        // set page specific variables
        $page_info['title'] = 'Manage Questions'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_question_list_view';
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
        $page_info['filter'] = $filter;


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->survey_question_model->get_paged_questions($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];


        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/survey_question";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);

        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Question', 'min-width' => '30%'),
                '1' => array('data'=> 'Category'),
                '2' => array('data'=> 'Status'),
                '3' => array('data'=> 'Bulk Status Change'),
                '4' => array('data'=> 'Type', 'class' => 'center', 'width' => '120'),
                '5' => array('data'=> 'Is Expired?', 'class' => 'center', 'width' => '80'),
                '6' => array('data'=> 'Action', 'class' => 'center', 'width' => '50')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            //$total_used_question_count = $this->survey_question_model->get_total_used_question_count();
            //print_r_pre($records); die();
            for ($i = 0; $i<count($records); $i++) {

                $category_str = '';
                if ($records[$i]->category_id > 0) {
                    $category_str = $this->survey_category_model->get_category($records[$i]->category_id)->cat_name;
                }

                $qus_fixed=$records[$i]->qus_fixed;

                //echo $qus_fixed; die();

                $expired_str = '<span class="label label-success">AVAILABLE</span>';
                if($qus_fixed==0 || empty($qus_fixed))
                {
                    if ($records[$i]->ques_expiry_date != '' && $records[$i]->ques_expiry_date != '0000-00-00 00:00:00' && $records[$i]->ques_expiry_date <= date('Y-m-d H:i:s')) {
                        $expired_str = '<span class="label label-important">EXPIRED</span>';
                    }
                }
                

                $question_type = $records[$i]->ques_type;
                if ($question_type == 'option_based') {
                    $question_type = '<span class="label label-info">OPTION BASED</span>';
                } elseif ($question_type == 'descriptive') {
                    $question_type = '<span class="label label-info">DESCRIPTIVE</span>';
                } else {
                    $question_type = 'UNKNOWN';
                }
                $statusQus="";
                $selectact = '';
                $suid = $records[$i]->id;
                
                if($qus_fixed==0 || empty($qus_fixed))
                {

                    if($records[$i]->qus_status==1){
                        $statusQus .= '<a href="'.base_url("appreject/$suid/1").'" class="label label-important">Reject</a>';
                                        $selectact = '<input type="checkbox" name="approve_ques[]"   class="form-control input-sm"  data-name="'.$records[$i]->qus_status.'" id="approval_cam" value="'.$records[$i]->id.'">';

                    }else if($records[$i]->qus_status==2){
                          $selectact .= '<input type="checkbox" name="approve_ques[]"   class="form-control input-sm"  data-name="'.$records[$i]->qus_status.'" id="approval_cam" value="'.$records[$i]->id.'">';

                        $statusQus = '<a href="'.base_url("appreject/$suid/2").'" class="label label-success">Approve</a>';
                    }else{
                     $selectact .= '<input type="checkbox" name="approve_ques[]"   class="form-control input-sm"  data-name="'.$records[$i]->qus_status.'" id="approval_cam" value="'.$records[$i]->id.'">';

                        $statusQus = '<a href="'.base_url("appreject/$suid/2").'" class="label label-success">Approve</a>  <a href="'.base_url("appreject/$suid/1").'" class="label label-important">Reject</a>  ';
                    }
                }
                else
                {
                    $statusQus = '<a href="javascript:void(0);" class="label label-warning">Template</a>';
                }


                $stat_str = '';
                $uses_stat_str = '';
                 
                $action_str = '';
                if(!isSystemAuditor())
                {
                    if($qus_fixed==0 || empty($qus_fixed))
                    {
                        $action_str .= anchor('administrator/survey_question/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                    }
                }
                
               
                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->ques_text, 'min-width' => '30%'),
                    '1' => array('data'=> $category_str),
                    '2' => array('data'=> $statusQus),
                    '3' => array('data'=> $selectact, 'class' => 'center', 'width' => '50'),
                    '4' => array('data'=> $question_type, 'class' => 'center', 'width' => '120'),
                    '5' => array('data'=> $expired_str, 'class' => 'center', 'width' => '80'),
                    '6' => array('data'=> $action_str, 'class' => 'center', 'width' => '50')
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

    public function appreject($value='',$type='')
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Approve/Reject Survey Question'));
        //print_r_pre($type);
         if($value=="" || $type==""){
            $this->session->set_flashdata('message_error', 'No data here.');
            redirect('administrator/survey_question');
         }

         if($value)
         {
            $msg ="";
            if($type==2){
                $msg = "Approved";
            }else{
                $msg = "Rejected";
            }
            if($this->update_global_model->globalupdate('exm_survey_questions',array('id'=>$value),array('qus_status'=>$type))){
                $this->session->set_flashdata('message_success', 'Qusetion '.$msg.' successful');
                redirect('administrator/survey_question');
            }else{
                $this->session->set_flashdata('message_error', 'No data here.');
                redirect('administrator/survey_question');
            }
         }



    }



    public function change_all_status()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Change All Status'));


        $value = $this->input->post('approve_ques');
        $approve_btn = $this->input->post('approve_all');
        $reject_btn = $this->input->post('reject_all');

        //var_dump($approve_btn);
        //var_dump($reject_btn);die;

        if($reject_btn)
        {

            //var_dump('expression');die;
            $update_data = array();
        foreach ($value as $key => $value) {
            
            
                $update_data[$key]['id'] = $value;
                $update_data[$key]['qus_status'] = 1;
                
                
               
            }   

            
        

         if($value)
         {
            
            if($this->update_global_model->update_batch('exm_survey_questions','id',$update_data)){
                $this->session->set_flashdata('message_success', 'Qusetion '.$msg.' successful');
                redirect('administrator/survey_question');
            }else{
                $this->session->set_flashdata('message_error', 'No data here.');
                redirect('administrator/survey_question');
            }
         }
         else
         {
             $this->session->set_flashdata('message_error', 'No data selected.');
                redirect('administrator/survey_question');
         }

        }
        else if ($approve_btn)
        {


            $update_data = array();
        foreach ($value as $key => $value) {
            
            
                $update_data[$key]['id'] = $value;
                $update_data[$key]['qus_status'] = 2;
                
                
               
            }

            //print_r_pre($update_data);die;   
        

         if($value)
         {
            
            if($this->update_global_model->update_batch('exm_survey_questions','id',$update_data)){
                $this->session->set_flashdata('message_success', 'Question update successful');
                redirect('administrator/survey_question');
            }else{
                $this->session->set_flashdata('message_error', 'No data here.');
                redirect('administrator/survey_question');
            }
         }
         else
         {
             $this->session->set_flashdata('message_error', 'No data selected.');
                redirect('administrator/survey_question');
         }

        }
        else
        {
            $this->session->set_flashdata('message_error', 'No such action is defined.');
                redirect('administrator/survey_question');
        }
        
        



    }

    public function stats($stat_type='', $question_id=0)
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Survey Questions Statistics View'));
        // set page specific variables
        $page_info['title'] = 'Question Statistics'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_question_stat_view';
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
            redirect('administrator/survey_question');
        } elseif ($question_id <= 0) {
            redirect('administrator/survey_question');
        }


        $question = $this->survey_question_model->get_question($question_id);
        if ($question) {

            $page_info['question_id'] = $question_id;
            $page_info['correct_count'] = (int)$this->survey_question_model->get_correct_answer_count($question_id);
            $page_info['wrong_count'] = (int)$this->survey_question_model->get_wrong_answer_count($question_id);
            $page_info['dontknow_count'] = (int)$this->survey_question_model->get_dontknow_answer_count($question_id);
            $page_info['unanswered_count'] = (int)$this->survey_question_model->get_unanswered_answer_count($question_id);
            $page_info['total_used_question_count'] = $this->survey_question_model->get_total_used_question_count();
            $page_info['total_used_question_in_category_count'] = $this->survey_question_model->get_total_used_question_in_category_count($question->category_id);
            $page_info['user_count'] = (int)$this->survey_question_model->get_user_count($question_id);
            $page_info['exam_count'] = (int)$this->survey_question_model->get_exam_count($question_id);


            $per_page = $this->config->item('per_page');
            $uri_segment = 6;
            $page_offset = $this->uri->segment($uri_segment);
            $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

            //$record_result = $this->question_model->get_answer_details($per_page, $page_offset, $filter);
            $record_result = $this->survey_question_model->get_answer_details($question_id, $stat_type, $per_page, $page_offset, $filter);
            $page_info['records'] = $record_result['result'];
            $records = $record_result['result'];

            // build paginated list
            $config = array();
            $config["base_url"] = base_url() . "administrator/survey_question/stats/". $stat_type .'/'. $question_id;
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
            redirect('administrator/survey_question');
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
        } else {
            $this->session->unset_userdata('filter_question');
            $this->session->unset_userdata('filter_category');
            $this->session->unset_userdata('filter_type');
            $this->session->unset_userdata('filter_expired');
        }

        redirect('administrator/survey_question');
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
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Survey Question View'));
        // set page specific variables
        $page_info['title'] = 'Add New Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_question_form_view';
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
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Survey Question'));
        $page_info['title'] = 'Add New Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_question_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();
        
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $category_id = (int)$this->input->post('category_id');
            $ques_text = htmlspecialchars($this->input->post('ques_text'));
            $ques_type = $this->input->post('ques_type');
            $qus_fixed = $this->input->post('qus_fixed');
            $mcq_options = $this->input->post('mcq_options');
            $ques_expiry_date = $this->input->post('ques_expiry_date');
            $mcq_marks = $this->input->post('mcq_marks');
            $ques_added = date('Y-m-d H:i:s');

            if ($category_id <= 0) {
                $this->session->set_flashdata('message_error', 'Category is required');
                $this->load->view('administrator/layouts/default', $page_info);
                return false;
            }
            
            if ($ques_type == 'option_based') {
                // building choices array
                $ques_choices = array();
                //$ques_choices = array();
                $j = 0;

                for($i=0; $i<count($mcq_options); $i++) {
                    if (trim($mcq_options[$i]) != '') {
                        $ques_choices[$j]['text'] = htmlspecialchars($mcq_options[$i]);
                        $ques_choices[$j]['marks'] = htmlspecialchars($mcq_marks[$i]);
                        $j++;
                    }
                }

            } else  {
                $ques_choices = '';
            }

            $ques_choices = maybe_serialize($ques_choices);
            //print_r_pre($ques_choices);
            // if question type is not selected; set a default one based on the question choice field
            if ($ques_type == '' || ($ques_type != 'option_based' && $ques_type != 'descriptive')) {
                if ($ques_choices == '') {
                    $ques_type = 'descriptive';
                } else {
                    $ques_type = 'Option Based';
                }
            }

            if ($ques_expiry_date == '') {
                $ques_expiry_date = '';
            } else {
                $day = substr($ques_expiry_date, 0, 2);
                $month = substr($ques_expiry_date, 3, 2);
                $year = substr($ques_expiry_date, 6, 4);
                $ques_expiry_date = date('Y-m-d H:i:s', mktime(0, 0, 0, $month, $day, $year));
            }
            
            $duplicate_check = $this->survey_question_model->check_duplicate_questions($category_id, $ques_text);
            if($duplicate_check){
               $page_info['message_error'] = 'Add is unsuccessful because of duplicate question.';
               $this->load->view('administrator/layouts/default', $page_info);
               return false;
            }
  
            $data = array(
                'category_id' => $category_id,
                'ques_text' => $ques_text,
                'ques_type' => $ques_type,
                'qus_fixed' => $qus_fixed,
                'ques_choices' => $ques_choices,
                'ques_added' => $ques_added,
                'ques_expiry_date' => $ques_expiry_date
            );

            $res = (int)$this->survey_question_model->add_question($data);

            if ($res > 0) {
                $this->session->set_flashdata('message_success', 'Add is successful.');
                redirect('administrator/survey_question/edit/'. $res);
            } else {
                $page_info['message_error'] = $this->question_model->error_message .'Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }

    public function edit()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Survey Question View'));
        // set page specific variables
        $page_info['title'] = 'Edit Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_question_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $this->_set_rules();
        
        // prefill form values
        $question_id = (int)$this->uri->segment(4);
        $question = $this->survey_question_model->get_question($question_id, true);

        @$this->form_data->question_id = $question->id;
        $this->form_data->category_id = $question->category_id;
        $this->form_data->ques_text = $question->ques_text;
        $this->form_data->ques_type = $question->ques_type;
        $this->form_data->qus_fixed = $question->qus_fixed;
	   $this->form_data->ques_choices = maybe_unserialize($question->ques_choices);
       //print_r_pre($this->form_data->ques_choices);
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
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update Survey Question'));
        // set page specific variables
        $page_info['title'] = 'Edit Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_question_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $question_id = (int)$this->input->post('question_id');
        $category_id = (int)$this->input->post('category_id');
        $ques_text = htmlspecialchars($this->input->post('ques_text'));
        $ques_type = $this->input->post('ques_type');

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->form_data->question_id = $question_id;
            $this->form_data->category_id = $category_id;
            $this->form_data->ques_text = $ques_text;
            $this->form_data->ques_type = $ques_type;
            $this->form_data->qus_fixed = $qus_fixed;
            $this->load->view('administrator/layouts/default', $page_info);

        } else {
            
            $category_id = (int)$this->input->post('category_id');
            $ques_text = htmlspecialchars($this->input->post('ques_text'));
            $ques_type = $this->input->post('ques_type');
            $qus_fixed = $this->input->post('qus_fixed');
            $mcq_marks = $this->input->post('mcq_marks');
            $mcq_options = $this->input->post('mcq_options');
            $ques_expiry_date = $this->input->post('ques_expiry_date');

            if ($ques_type == 'option_based') {
                // building choices array
                $ques_choices = array();
                $j = 0;

                for($i=0; $i<count($mcq_options); $i++) {
                    if (trim($mcq_options[$i]) != '') {
                        $ques_choices[$j]['text'] = htmlspecialchars($mcq_options[$i]);
                        $ques_choices[$j]['marks'] = htmlspecialchars($mcq_marks[$i]);
                        $j++;
                    }
                }

            } else  {
                $ques_choices = '';
            }

            $ques_choices = maybe_serialize($ques_choices);
            //print_r_pre($ques_choices);
            // if question type is not selected; set a default one based on the question choice field
            if ($ques_type == '' || ($ques_type != 'option_based' && $ques_type != 'descriptive')) {
                if ($ques_choices == '') {
                    $ques_type = 'descriptive';
                } else {
                    $ques_type = 'Option Based';
                }
            }
            
            
            if ($ques_expiry_date == '') {
                $ques_expiry_date = '';
            } else {
                $day = substr($ques_expiry_date, 0, 2);
                $month = substr($ques_expiry_date, 3, 2);
                $year = substr($ques_expiry_date, 6, 4);
                $ques_expiry_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            }
            
            $duplicate_check = $this->survey_question_model->check_duplicate_questions($category_id, $ques_text);
            if($duplicate_check  && ($duplicate_check[0]->id != $question_id && ($question_id > 0))){
               $this->session->set_flashdata('message_error', 'Update is unsuccessful because of duplicate question.');
               redirect('administrator/survey_question/edit/'. $question_id);
               return false;
            }

            $data = array(
                'category_id' => $category_id,
                'ques_text' => $ques_text,
                'ques_type' => $ques_type,
                'qus_fixed' => $qus_fixed,
                'ques_choices' => $ques_choices,
                'ques_expiry_date' => $ques_expiry_date
            );

            if ($this->survey_question_model->update_question($question_id, $data)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else  {
                $this->session->set_flashdata('message_error', 'Update is unsuccessful.');
            }

            redirect('administrator/survey_question/edit/'. $question_id);
        }
    }
    
    
    public function bulk()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add Survey Bulk Question View'));
        // set page specific variables
        $page_info['title'] = 'Add Bulk Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/question_bulk_form_survey_view';
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
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Upload Survey Bulk Question'));
        $questions = array();
        $invalid_questions = array();
        $error_messages = array();

        $file_path = '';
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

                if ($max_column_number < 5) {
                    $this->session->set_flashdata('message_error', 'File format does not match.');
                    redirect('administrator/survey_question/bulk');
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
                    $question_choices = $sheetData[$i]['H'];
                    $question_expiry_date = trim($sheetData[$i]['I']);

                    if ($question_category == '' && $question_text == '' && $question_type == '' && $question_choices == '' && $question_expiry_date == '') {
                        continue;
                    } else {
                        $questions[$i]['category_id'] = $question_category;
                        $questions[$i]['ques_text'] = $question_text;
                        $questions[$i]['ques_type'] = $question_type;
                        $questions[$i]['ques_choices'] = $question_choices;
                        $questions[$i]['ques_expiry_date'] = $question_expiry_date;
                    }
                }

                // check for valid data
                if (count($questions) > 0) {
                    foreach($questions as $row => $question) {

                        $row_has_error = false;

                        $question_category = $question['category_id'];
                        $question_text = $question['ques_text'];
                        $question_type = $question['ques_type'];
                        $question_choices = $question['ques_choices'];
                        $question_expiry_date = $question['ques_expiry_date'];

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
                        if ($question_type != 'option_based' && $question_type != 'descriptive') {
                            $question_type = '';
                            $error_messages[$row][] = 'Question Type should be \'option_based\' or \'descriptive\'';
                            $row_has_error = true;
                        }

                        // question choices are only required when question type is mcq
                        if ($question_type == 'option_based') {

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
                                    $i++;
                                }
                            }
                                                       
                            $question_choices = maybe_serialize($question_choices);
                        } else {
                            $question_choices = '';
                        }

                        // question expiry date is optional. if provided, it should be a valid date
                        if ($question_expiry_date != '') {
                            $question_expiry_date = str_replace('/', '-', $question_expiry_date);
                            $question_expiry_date = date('Y-m-d', strtotime($question_expiry_date));
                        }
                        
                        // If entered Category not found, then a new Question Category will be created.
                        $question_category_id = '';
                        if ($question_category == '') {
                            $error_messages[$row][] = 'Question Category should be required.';
                            $row_has_error = true;
                        } else {
                            $old_category = $this->survey_category_model->get_category($question_category);
                            if ($old_category) {
                                $question_category_id = (int)$question_category;
                            } else {
                                //$new_category = array();
                                //$new_category['cat_name'] = $question_category;
                                //$question_category_id = $this->survey_category_model->add_category($new_category);
                            }
                        }
                        
                        $duplicate_check = $this->survey_question_model->check_duplicate_questions($question_category_id, $question_text);
                        if($duplicate_check){
                           $error_messages[$row][] = 'Duplicate Question';
                           $row_has_error = true; 
                        }
                        
                        if ($row_has_error) {
                            $invalid_questions[$row] = $question;
                            unset($questions[$row]);
                        } else {
                            $questions[$row]['category_id'] = $question_category_id;
                            $questions[$row]['ques_text'] = $question_text;
                            $questions[$row]['ques_type'] = $question_type;
                            $questions[$row]['ques_choices'] = $question_choices;
                            $questions[$row]['ques_expiry_date'] = $question_expiry_date;
                        }
                    }
                }

                //print_r_pre($questions);die;

                if (count($questions) <= 0 && count($invalid_questions) <= 0) {
                    $this->session->set_flashdata('message_error', 'File does not contain any row.');
                    redirect('administrator/survey_question/bulk');
                }

                $this->session->set_flashdata('bulk_questions', $questions);
                $this->session->set_flashdata('bulk_invalid_questions', $invalid_questions);
                $this->session->set_flashdata('bulk_error_messages', $error_messages);

            } else {
                $this->session->set_flashdata('message_error', $file_error);
                redirect('administrator/survey_question/bulk');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Please upload an Excel file.');
            redirect('administrator/survey_question/bulk');
        }

        $this->session->set_flashdata('bulk_action', 1);
        redirect('administrator/survey_question/bulk_upload_action');
    }

    public function bulk_upload_action()
    {
        // set page specific variables
        $page_info['title'] = 'Take an Action'. $this->site_name;
        $page_info['view_page'] = 'administrator/question_bulk_action_survey_view';
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
            redirect('administrator/survey_question/bulk');
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

        // bulk insert
        $this->survey_question_model->add_bulk_questions($bulk_questions);
        $this->session->set_flashdata('message_success', 'Record(s) inserted successfully.');

        redirect('administrator/survey_question/bulk');
    }
    
    public function edit_bulk()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Survey Bulk Question View'));
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
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Upload Bulk Survey Question'));
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
                    $question_text = trim($sheetData[$i]['B']);
                    $question_text_new = trim($sheetData[$i]['C']);
                    $question_type = trim($sheetData[$i]['D']);
                    $question_choices = $sheetData[$i]['E'];
                    $right_choices = trim($sheetData[$i]['F']);
                    $question_expiry_date = trim($sheetData[$i]['G']);

                    if ($question_category == '' && $question_text == '' && $question_text_new == '' && $question_type == '' && $question_choices == '' && $right_choices == '' && $question_expiry_date == '') {
                        continue;
                    } else {
                        $questions[$i]['category_id'] = $question_category;
                        $questions[$i]['ques_text'] = $question_text;
                        $questions[$i]['ques_text_new'] = $question_text_new;
                        $questions[$i]['ques_type'] = $question_type;
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
                        
                        
                        // Question Text is required
                        if ($question_text_new == '') {
                            $error_messages[$row][] = 'New Question is required';
                            $row_has_error = true;
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
                            $question_category_id = $default_category_id;
                        } else {
                            $old_category = $this->category_model->get_category_by_name($question_category);
                            if ($old_category) {
                                $question_category_id = (int)$old_category->id;
                            } else {
                               $error_messages[$row][] = 'Question Category is required';
                               $row_has_error = true;
                            }
                        }
                        
                        if($question_category_id > 0){
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
                            $questions[$row]['ques_choices'] = $question_choices;
                            $questions[$row]['ques_expiry_date'] = $question_expiry_date;
                            $questions[$row]['admin_group'] = $this->session->userdata('logged_in_user')->admin_group;
                        }
                    }
                } 
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


    // set empty default form field values
    private function _set_fields()
    {
        @$this->form_data->question_id = 0;
        $this->form_data->category_id = 0;
        $this->form_data->ques_text = '';
        $this->form_data->ques_type = '';
        $this->form_data->qus_fixed = 0;
        $this->form_data->ques_expiry_date = '';


        $this->form_data->filter_question = '';
        $this->form_data->filter_category = 0;
        $this->form_data->filter_type = '';
        $this->form_data->filter_expired = '';
    }

    // validation rules
    private function _set_rules()
    {
        $this->form_validation->set_rules('category_id ', 'Category', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ques_text', 'Question Text', 'required|trim');
        $this->form_validation->set_rules('qus_fixed', 'Question Format', 'required|trim');
        $this->form_validation->set_rules('ques_type', 'Question Type', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ques_expiry_date', 'Expiry Date', 'required|trim|xss_clean|strip_tags');
    }

}

/* End of file question.php */
/* Location: ./application/controllers/administrator/question.php */