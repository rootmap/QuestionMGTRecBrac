<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Result extends MY_Controller
{
    var $current_page = "result";
    var $exam_list = array();
    var $user_team_list = array();
    var $tbl_exam_users_activity    = "exm_user_activity";

    var $set_list = array();

    function __construct()
    {
        parent::__construct();
$this->form_data = new StdClass;
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library('output');
        $this->load->library('excel');
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('user_model');
        $this->load->library('upload');
        $this->load->library('form_validation');
        $this->load->model('user_team_model');
        $this->load->model('exam_model');
        $this->load->model('result_model');
        $this->load->model('global/select_global_model');
        
        $this->load->model('global/delete_global_model');
        $this->load->model('global/update_global_model');

        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');

        $this->output->nocache();
        //$this->output->enable_profiler(TRUE);

        // pre-load lists
        $open_exams = $this->exam_model->get_exams();
        $this->exam_list[] = 'Select an Exam';
        if ($open_exams) {
            for ($i=0; $i<count($open_exams); $i++) {
                $this->exam_list[$open_exams[$i]->id] = $open_exams[$i]->exam_title;
            }
        }

        /*

        $priv_ids = $this->_model->get_privilages();

        if ($priv_ids) {
            for ($i=0; $i<count($priv_ids); $i++) {
                $this->priv_list[$priv_ids[$i]->id] = $priv_ids[$i]->privilage_description;
            }
        }
        */


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

    public function index($exam_id = 0, $user_team_id = 0)
	{
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Review Results View'));
        // set page specific variables
        $page_info['title'] = 'Review Results'. $this->site_name;
        $page_info['view_page'] = 'administrator/result_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';


        $this->_set_fields();
        

        $exam_id = (int)$exam_id;
        $user_team_id = (int)$user_team_id;

        if ($this->input->post('exam_id') && $this->input->post('user_team_id')) {
            $exam_id = (int)$this->input->post('exam_id');
            $user_team_id = (int)$this->input->post('user_team_id');
            redirect('administrator/result/'. $exam_id. '/'. $user_team_id);
        }

        if ($exam_id > 0 && $user_team_id > 0) {

            $this->form_data->exam_id = $exam_id;
            $this->form_data->user_team_id = $user_team_id;
            
            $records = $this->result_model->get_results_by_exam_and_user_team($exam_id, $user_team_id);
            $page_info['records'] = $records;

            if ($records) {

                // customize and generate records table
                $tbl_heading = array(
                    '0' => array('data'=> 'User'),
                    '1' => array('data'=> 'Questions Answered'),
                    '2' => array('data'=> 'Score'),
                    '3' => array('data'=> 'Status'),
                    '4' => array('data'=> '', 'class' => 'center')
                );
                $this->table->set_heading($tbl_heading);

                $tbl_template = array (
                    'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                    'table_close'         => '</table>'
                );
                $this->table->set_template($tbl_template);

                for ($i = 0; $i<count($records); $i++) {

                    $user_name = '';
                    $user_str = $records[$i]->user_login;
                    if ($records[$i]->user_first_name != '') { $user_name .= $records[$i]->user_first_name; }
                    if ($records[$i]->user_last_name != '') { $user_name .= ' '. $records[$i]->user_last_name;}
                    $user_name = trim($user_name);
                    if ($user_name != '') { $user_str = $user_name .' ('. $user_str .')'; }

                    $questions_str = 'n/a';
                    $total_questions = (int)
                    $total_answered = (int)$records[$i]->result_total_answered;
                    if ($total_questions > 0) {
                        $questions_str = $total_answered .' of '. $total_questions;
                    }

                    $score_str = 'n/a';
                    $total_score = (int)$records[$i]->result_exam_score;
                    $user_score = (int)$records[$i]->result_user_score;
                    if ($total_score > 0) {
                        $score_str = $user_score .' of '. $total_score;
                    }

                    $status_str = ucfirst($records[$i]->ue_status);


                    $action_str = '';
                    if ($records[$i]->ue_status == 'complete') {
                        $action_str = anchor(base_url('administrator/result/answer/'. $exam_id .'/'. $user_team_id .'/'. $records[$i]->exm_result_id), '<i class="icon-check"></i>', array('title' => 'Review Answers', 'target' => '_blank'));
                    }

                    $tbl_row = array(
                        '0' => array('data'=> $user_str),
                        '1' => array('data'=> $questions_str),
                        '2' => array('data'=> $score_str),
                        '3' => array('data'=> $status_str),
                        '4' => array('data'=> $action_str, 'class' => 'center', 'width' => '50px')
                    );
                    $this->table->add_row($tbl_row);
                }

                $page_info['records_table'] = $this->table->generate();

            } else {
                $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No results found.</div>';
            }
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


	public function upload_result ()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Upload Result View'));
              // set page specific variables
        $page_info['title'] = 'Upload Result'. $this->site_name;
        $page_info['view_page'] = 'administrator/exam_upload_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();

        $page_info['exam_all'] = $this->result_model->get_exam_name();
        $page_info['qSet']  = $this->select_global_model->select_array('exm_question_set',array('set_status'=>1));



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
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Upload Bulk Result'));
        $results = array();
        $invalid_results = array();
        $error_messages = array();
        $file_path = '';
        $has_column_header = (int)$this->input->post('result_file_has_column_header');
        $exam_id = (int)$this->input->post('mo_exam_all');
        $set_id = (int)$this->input->post('exam_question_Set');


        if(empty($exam_id) || empty($set_id))
        {
            $this->session->set_flashdata('message_error', 'Please select exam & question set.');
            redirect('administrator/result/upload_result');
        }


        // uploading file
        $config['upload_path'] = './uploads/result/';
        $config['allowed_types'] = 'xls|xlsx';

        if ($_FILES['result_file']['tmp_name'] != '' && $_FILES['result_file']['error'] == 0) {

            $this->upload->initialize($config);
            $this->upload->do_upload('result_file');

            $file_error = $this->upload->display_errors();
            $file_data = $this->upload->data();

            if ($file_error == '') {

                $file_path = $file_data['full_path'];

                //var_dump($file_path);die;

                $objPHPExcel = PHPExcel_IOFactory::load($file_path);
                @unlink($file_path);

                $objPHPExcel->setActiveSheetIndex(0);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                $max_column_name = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $max_column_number = PHPExcel_Cell::columnIndexFromString($max_column_name);





                // remove first row (if $has_column_header == 1)
                // remove empty rows
                $start = 1;
                if ($has_column_header) {
                    $start = 2;
                }

                for ($i=$start; $i<=count($sheetData); $i++) {

                    $examinee_ID = trim($sheetData[$i]['A']);
                    $result_status = trim($sheetData[$i]['B']);
                    $result_total_question = trim($sheetData[$i]['C']);
                    $result_total_answered = trim($sheetData[$i]['D']);
                    $result_total_correct = trim($sheetData[$i]['E']);
                    $result_total_wrong = trim($sheetData[$i]['F']);
                    $result_exam_score = trim($sheetData[$i]['G']);
                    $result_user_score = trim($sheetData[$i]['H']);
                    $result_competency_level = trim($sheetData[$i]['I']);
                    $result_time_spent = trim($sheetData[$i]['J']);
                    $exam_start_time = trim($sheetData[$i]['K']);
                    $exam_end_time = trim($sheetData[$i]['L']);


                    if ($examinee_ID == '' && $examinee_name == '' && $marks == ''  ) {
                        continue;
                    } else {
                        $results[$i]['examinee_id'] = $examinee_ID;
                        $results[$i]['result_status'] = $result_status;
                        $results[$i]['result_total_question'] = $result_total_question;
                        $results[$i]['result_total_answered'] = $result_total_answered;
                        $results[$i]['result_total_correct'] = $result_total_correct;
                        $results[$i]['result_total_wrong'] = $result_total_wrong;
                        $results[$i]['result_exam_score'] = $result_exam_score;
                        $results[$i]['result_user_score'] = $result_user_score;
                        $results[$i]['result_competency_level'] = $result_competency_level;
                        $results[$i]['result_time_spent'] = $result_time_spent;
                        $results[$i]['exam_start_time'] = $exam_start_time;
                        $results[$i]['exam_end_time'] = $exam_end_time;

                    }
                }

                //print_r_pre($results);

                // check for valid data
                if (count($results) > 0) {
                    foreach($results as $row => $result) {

                        $row_has_error = false;

                        $examinee_ID = $result['examinee_id'];
                        $result_status = $result['result_status'];
                        $result_total_question = $result['result_total_question'];
                        $result_total_answered = $result['result_total_answered'];
                        $result_total_correct = $result['result_total_correct'];
                        $result_total_wrong = $result['result_total_wrong'];
                        $result_exam_score = $result['result_exam_score'];
                        $result_user_score = $result['result_user_score'];
                        $result_competency_level = $result['result_competency_level'];
                        $result_time_spent = $result['result_time_spent'];
                        $exam_start_time = $result['exam_start_time'];
                        $exam_end_time = $result['exam_end_time'];


                        $examinee_sql=$this->user_model->get_user_by_login($examinee_ID);
                        $examinee_row_id=$examinee_sql->id;

                        if (!isset($examinee_row_id) || empty($examinee_row_id)) {
                            $error_messages[$row][] = 'Examinee ('.$examinee_ID.') id is invalid';
                            $row_has_error = true;
                        }


                        if ($examinee_ID == '') {
                            $error_messages[$row][] = 'Examinee id can not be empty';
                            $row_has_error = true;
                        }

                        if ($result_status == '') {
                            $error_messages[$row][] = 'Result Status can not be empty';
                            $row_has_error = true;
                        }

                        if ($result_total_question == '') {
                            $error_messages[$row][] = 'Result Total Question can not be empty';
                            $row_has_error = true;
                        }

                        if ($result_total_answered == '') { $error_messages[$row][] = 'Result Total Answered can not be empty'; $row_has_error = true; }
                        if ($result_total_correct == '') { $error_messages[$row][] = 'Result Total Correct can not be empty'; $row_has_error = true; }
                        if ($result_total_wrong == '') { $error_messages[$row][] = 'Result Total Wrong can not be empty'; $row_has_error = true; }
                        if ($result_exam_score == '') { $error_messages[$row][] = 'Exam Total Score can not be empty'; $row_has_error = true; }
                        if ($result_user_score == '') { $error_messages[$row][] = 'Result User Score can not be empty'; $row_has_error = true; }
                        if ($result_competency_level == '') { $error_messages[$row][] = 'Result Competency Level can not be empty'; $row_has_error = true; }
                        if ($result_time_spent == '') { $error_messages[$row][] = 'Result Time Spent can not be empty'; $row_has_error = true; }
                        if ($exam_start_time == '') { $error_messages[$row][] = 'Exam Start Time can not be empty'; $row_has_error = true; }
                        if ($exam_end_time == '') { $error_messages[$row][] = 'Exam End Time can not be empty'; $row_has_error = true; }

                        $assignedExam=$this->user_model->get_user_assigned_exam_row_id($examinee_row_id,$exam_id,$set_id);
                            //print_r_pre($assignedExam);
                        $assignedExamRowID='';
                        if(isset($assignedExam))
                        {
                            $assignedExamRowID=$assignedExam->id;
                        }

                        if(empty($assignedExamRowID))
                        {
                            $error_messages[$row][] = 'Exam Not Assigned ('.$examinee_ID.').'; 
                            $row_has_error = true;
                        }
                        




                        if ($row_has_error) {
                            $invalid_results[$row] = $result;
                            unset($results[$row]);
                        } else {

                            $results[$row]['examinee_id'] = $examinee_row_id;
                            $results[$row]['result_status'] = $result_status;
                            $results[$row]['result_total_question'] = $result_total_question;
                            $results[$row]['result_total_answered'] = $result_total_answered;
                            $results[$row]['result_total_correct'] = $result_total_correct;
                            $results[$row]['result_total_wrong'] = $result_total_wrong;
                            $results[$row]['result_exam_score'] = $result_exam_score;
                            $results[$row]['result_user_score'] = $result_user_score;
                            $results[$row]['result_competency_level'] = $result_competency_level;
                            $results[$row]['result_time_spent'] = $result_time_spent;
                            $results[$row]['exam_start_time'] = $exam_start_time;
                            $results[$row]['exam_end_time'] = $exam_end_time;

                            $results[$row]['exam_id'] = $exam_id;
                            $results[$row]['set_id'] = $set_id;
                            $results[$row]['user_exam_id'] = $assignedExamRowID;
                        }
                    }
                }

                //print_r_pre($results);

                if (count($results) <= 0 && count($invalid_results) <= 0) {
                    $this->session->set_flashdata('message_error', 'File does not contain any row.');
                    redirect('administrator/result/result_upload');
                }

                $this->session->set_flashdata('bulk_results', $results);
                $this->session->set_flashdata('bulk_invalid_results', $invalid_results);
                $this->session->set_flashdata('bulk_error_messages', $error_messages);

            } else {
                $this->session->set_flashdata('message_error', $file_error);
                redirect('administrator/result/upload_result');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Please upload an Excel file.');
            redirect('administrator/result/upload_result');
        }

        $this->session->set_flashdata('bulk_action', 1);
        redirect('administrator/result/bulk_upload_action');
    }


    public function bulk_upload_action()
    {
        // set page specific variables
        $page_info['title'] = 'Take an Action'. $this->site_name;
        $page_info['view_page'] = 'administrator/result_bulk_action_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $page_info['bulk_results'] = array();
        $page_info['bulk_invalid_results'] = array();
        $page_info['bulk_error_messages'] = array();


        if ($this->session->flashdata('bulk_action')) {
            $this->session->keep_flashdata('bulk_action');
        }
        if ( (int)$this->session->flashdata('bulk_action') == 0 ) {
            redirect('administrator/result/upload_result');
        }


        if ($this->session->flashdata('bulk_results')) {
            $page_info['bulk_results'] = $this->session->flashdata('bulk_results');
            $this->session->keep_flashdata('bulk_results');
        }
        if ($this->session->flashdata('bulk_invalid_results')) {
            $page_info['bulk_invalid_candidates'] = $this->session->flashdata('bulk_invalid_results');
            $this->session->keep_flashdata('bulk_invalid_results');
        }

        if ($this->session->flashdata('bulk_error_messages')) {
            $page_info['bulk_error_messages'] = $this->session->flashdata('bulk_error_messages');
            $this->session->keep_flashdata('bulk_error_messages');
        }

        //var_dump($page_info['bulk_candidates']);die;


        $bulk_invalid_results = $page_info['bulk_invalid_results'];
        $bulk_error_messages = $page_info['bulk_error_messages'];


        if ($bulk_invalid_results && count($bulk_invalid_results) < 250) {

            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Examinee Name'),
                '1' => array('data'=> 'Marks'),
                '2' => array('data'=> 'Error')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            foreach($bulk_invalid_results as $row => $record) {

                $error_message = '';
                for ($i=0; $i<count($bulk_error_messages[$row]); $i++) {
                    if ($i>0) { $error_message .= '<br />'; }
                    $error_message .= $bulk_error_messages[$row][$i];
                }

                $tbl_row = array(
                    '0' => array('data'=> $record['examinee_name']),
                    '1' => array('data'=> $record['marks']),
                    '2' => array('data'=> $error_message)
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['bulk_invalid_results_table'] = $this->table->generate();
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
        $bulk_results = array();

        if ($this->session->flashdata('bulk_results')) {
            $bulk_results = $this->session->flashdata('bulk_results');
        }

        // bulk insert
        //$return_value = $this->result_model->add_bulk_results($bulk_results);

        
        $return_value = $this->result_model->add_bulk_results($bulk_results);
        //var_dump($return_value); var_dump('hi');die;
        if($return_value>0)$this->session->set_flashdata('message_success', 'Record(s) inserted successfully.');
        else
            $this->session->set_flashdata('message_error', 'No row is inserted.');

        redirect('administrator/result/upload_result');
    }

    public function answer($exam_id = 0, $user_team_id = 0, $result_id = 0)
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Review Answer View'));
        // set page specific variables
        $page_info['title'] = 'Review Answers'. $this->site_name;
        $page_info['view_page'] = 'administrator/review_answers_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';


        $page_info['exam_id'] = (int)$exam_id;
        $page_info['user_team_id'] = (int)$user_team_id;
        $page_info['result_id'] = (int)$result_id;
        $page_info['current_question_index'] = 0;


        $exam = '';
        $result = $this->result_model->get_result($result_id);

        if ($result) {
            $exam = maybe_unserialize($result->result_exam_state);
        } else {
            redirect('administrator/result/'. $exam_id .'/'. $user_team_id);
        }

        
        if ($this->input->post('save_button')) {

            // update result score
            $user_scores = $this->input->post('user_score');
            for ($i=0; $i<count($user_scores); $i++) {

                $question = $exam->exam_questions[$i]->question;

                $user_score = (float)$user_scores[$i];
                if ($user_score < 0) { $user_score = 0; }
                elseif ($user_score > (float)$question->ques_score) { $user_score = (float)$question->ques_score; }

                $exam->exam_questions[$i]->question->ques_user_score = $user_score;
            }

            // update database
            $this->result_model->update_result_exam_state($result_id, $exam);

            $page_info['current_question_index'] = (int)$this->input->post('current_question_index');

        } elseif ($this->input->post('publish_button')) {

            // update result score
            $user_scores = $this->input->post('user_score');
            for ($i=0; $i<count($user_scores); $i++) {

                $question = $exam->exam_questions[$i]->question;

                $user_score = (float)$user_scores[$i];
                if ($user_score < 0) { $user_score = 0; }
                elseif ($user_score > (float)$question->ques_score) { $user_score = (float)$question->ques_score; }

                $exam->exam_questions[$i]->question->ques_user_score = $user_score;
            }

            // update database
            $result->result_status = 'published';
            $result->result_exam_state = maybe_serialize($exam);
            
            $this->result_model->update_result($result->user_exam_id, $result);

            // TODO: Send a notification email to the user.

        }
        //echo '<pre>'; print_r( $_POST ); echo '</pre>';
        //echo '<pre>'; print_r( $exam ); echo '</pre>'; die();


        $page_info['result'] = $result;
        $page_info['exam'] = $exam;



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

    // set empty default form field values
	private function _set_fields()
	{
		$this->form_data = new StdClass;
        $this->form_data->exam_id = 0;
        $this->form_data->user_team_id = 0;
	}

	// validation rules
	private function _set_rules()
	{
        $this->form_validation->set_rules('exam_id', 'Exam', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('user_team_id', 'User Team', 'required|trim|xss_clean|strip_tags');
	}
}





