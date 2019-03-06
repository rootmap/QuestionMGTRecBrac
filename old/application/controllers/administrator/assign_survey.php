<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Assign_survey extends MY_Controller
{
    var $current_page = "assign_survey";
    var $survey_list = array();
    var $user_group_list = array();
    var $user_list = array();
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->library('excel');
        $this->load->helper('number');
        $this->load->library('upload');
        $this->load->library('robi_email');
        $this->load->model('survey_model');
        $this->load->model('user_group_model');
        $this->load->model('user_model');

        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');


        // pre-load lists
        $open_survey = $this->survey_model->get_open_surveys();
        $this->survey_list[] = 'Select a Survey';
        if ($open_survey) {
            for ($i=0; $i<count($open_survey); $i++) {
                $this->survey_list[$open_survey[$i]->id] = $open_survey[$i]->survey_title;
            }
        }

        $user_groups = $this->user_group_model->get_user_groups();
        $this->user_group_list[] = 'Select an User Group';
        if ($user_groups) {
            for ($i=0; $i<count($user_groups); $i++) {
                $this->user_group_list[$user_groups[$i]->id] = $user_groups[$i]->group_name;
            }
        }

        $users = $this->user_model->get_active_users();
        if ($users) {
            for ($i=0; $i<count($users); $i++) {
                $this->user_list[$users[$i]->id] = $users[$i]->user_first_name .' '. $users[$i]->user_last_name .' - '. $users[$i]->user_login;
            }
        }


        if($this->session->flashdata('start_date')) {
            $this->session->keep_flashdata('start_date');
        }
        if($this->session->flashdata('end_date')) {
            $this->session->keep_flashdata('end_date');
        }

        // check if logged in
        if ( ! $this->session->userdata('logged_in_user')) {
            $redirect_url = preg_replace('/(delete|update.*|(add).*)\/?[0-9]*$/', '$2', uri_string());
            $this->session->set_flashdata('redirect_url', $redirect_url);
            redirect('login');
        } else {
            $logged_in_user = $this->session->userdata('logged_in_user');
            if ($logged_in_user->user_type == 'User'&& !$this->session->userdata('user_privilage_name')) {
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
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Assign Survey View'));
        // set page specific variables
        $page_info['title'] = 'Assign Survey'. $this->site_name;
        $page_info['view_page'] = 'administrator/assign_survey_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';


        $this->_set_fields();


        if ($this->input->post('assign_survey_submit')) {
            $survey_id = (int)$this->input->post('survey_id');
            if ($survey_id > 0) {
                redirect('administrator/assign_survey/assign/'. $survey_id);
            } else {
                $page_info['message_error'] = 'Please select a survey from the list.';
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

    public function assign($survey_id = 0)
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Assgin Survey to Users View'));
        // set page specific variables
        $page_info['title'] = 'Assign Survey to Users'. $this->site_name;
        $page_info['view_page'] = 'administrator/assign_survey_add_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $this->_set_fields();
        $survey_id = (int)$survey_id;
        if ($survey_id <= 0) {
            $this->session->set_flashdata('message_success', 'Please select a survey from the list.');
            redirect('administrator/assign_survey');
        }

        $this->form_data->survey_id = $survey_id;
        $this->form_data->survey_id_hidden = $survey_id;

        if ($this->session->flashdata('start_date')) {
            $start_date = $this->session->flashdata('start_date');
            $this->form_data->start_date = date('d/m/Y', strtotime($start_date));
        }
        if ($this->session->flashdata('end_date')) {
            $end_date = $this->session->flashdata('end_date');
            $this->form_data->end_date = date('d/m/Y', strtotime($end_date));
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

    public function do_assign($survey_id = 0)
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Assign Survey'));
        // set page specific variables
        $page_info['title'] = 'Assign Survey to Users'. $this->site_name;
        $page_info['view_page'] = 'administrator/assign_survey_add_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $survey_id = (int)$survey_id;
        if ($survey_id <= 0) {
            $this->session->set_flashdata('message_success', 'Please select a survey from the list.');
            redirect('administrator/assign_survey');
        }

        $this->_set_fields();
        $this->_set_rules();

        $this->form_data->survey_id = $survey_id;
        $this->form_data->survey_id_hidden = $survey_id;


        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $survey_id = (int)$this->input->post('survey_id_hidden');
            $user_group_id = (int)$this->input->post('user_group_id');
            $user_ids = $this->input->post('user_ids');
            $survey_anms = $this->input->post('survey_anms');

            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');

            if ($start_date == '') {
                $start_date = '';
            } else {
                $day = (int)substr($start_date, 0, 2);
                $month = (int)substr($start_date, 3, 2);
                $year = (int)substr($start_date, 6, 4);
                $start_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            }

            if ($end_date == '') {
                $end_date = '';
            } else {
                $day = substr($end_date, 0, 2);
                $month = substr($end_date, 3, 2);
                $year = substr($end_date, 6, 4);
                $end_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            }

            // set flash data
            $this->session->set_flashdata('survey_id', $survey_id);
            $this->session->set_flashdata('start_date', $start_date);
            $this->session->set_flashdata('end_date', $end_date);


            $data = array(
                'survey_id' => $survey_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => 'open',
                'survey_anms' =>$survey_anms,
                'added' => date("Y-m-d H:i:s")
            );


            // constructing user ids
            $users = array();
            $assignable_user_ids = array();
            if($survey_anms=='no'){
                    if ($user_ids) {
                    $assignable_user_ids = array_unique($user_ids);
                } elseif ($user_group_id > 0) {
                    $user_ids = $this->user_model->get_active_users_by_user_group($user_group_id);
                    if ($user_ids) {
                        for ($i=0; $i<count($user_ids); $i++) {
                            $assignable_user_ids[] = $user_ids[$i]->id;
                        }
                        $assignable_user_ids = array_unique($assignable_user_ids);
                    }
                }
            }else{
                $assignable_user_ids = array('0'=>1000000);
            }

            //print_r_pre($assignable_user_ids);
            
            if (count($assignable_user_ids) > 0) {
                    $survey = $this->survey_model->get_survey($survey_id);
                    for ($i=0; $i<count($assignable_user_ids); $i++) {
                        $is_assigned = $this->survey_model->add_user_survey_by_user_id($assignable_user_ids[$i], $data);
                        
                        // send a notification if the survey successfully assigned
                        if ($is_assigned) {
                            $user_survey = new stdClass();
                            $user_survey->start_date = $start_date;
                            $user_survey->end_date = $end_date;
                            
                            //$this->robi_email->survey_notification($assignable_user_ids[$i], $user_survey, $survey);
                        }
                    }
                }

            $this->session->set_flashdata('message_success', 'Survey Assign is successful.');
            redirect('administrator/assign_survey_status');
        }
    }

    public function bulk()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Assign Survey to Bulk User View'));
        // set page specific variables
        $page_info['title'] = 'Add Survey to Bulk Users'. $this->site_name;
        $page_info['view_page'] = 'administrator/assign_survey_bulk_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';


        // determine messages
        if ($this->session->flashdata('message_error')) { $page_info['message_error'] = $this->session->flashdata('message_error'); }
        if ($this->session->flashdata('message_success')) { $page_info['message_success'] = $this->session->flashdata('message_success'); }

        // load view
	$this->load->view('administrator/layouts/default', $page_info);
    }

    public function bulk_upload()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Assign Survey to Bulk User'));
        $survey = array();
        $invalid_survey = array();
        $error_messages = array();
        $file_path = '';
        $has_column_header = (int)$this->input->post('user_file_has_column_header');

        // uploading file
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xls|xlsx';

        if ($_FILES['user_file']['tmp_name'] != '' && $_FILES['user_file']['error'] == 0) {

            $this->upload->initialize($config);
            $this->upload->do_upload('user_file');

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

                if ($max_column_number < 4) {
                    $this->session->set_flashdata('message_error', 'File format does not match.');
                    redirect('administrator/assign_survey/bulk');
                }

                // remove first row (if $has_column_header == 1)
                // remove empty rows
                $start = 1;
                if ($has_column_header) {
                    $start = 2;
                }

                for ($i=$start; $i<=count($sheetData); $i++) {

                    $survey_title = trim($sheetData[$i]['A']);
                    $login = trim($sheetData[$i]['B']);
                    $start_date = trim($sheetData[$i]['C']);
                    $end_date = trim($sheetData[$i]['D']);

                    if ($survey == '' && $login == '' && $start_date == '' && $end_date == '') {
                        continue;
                    } else {
                        $survey[$i]['user_id'] = $login;
                        $survey[$i]['survey_id'] = $survey_title;
                        $survey[$i]['start_date'] = $start_date;
                        $survey[$i]['end_date'] = $end_date;
                        $survey[$i]['status'] = 'open';
                    }
                }
               
                // check for valid data
                if (count($survey) > 0) {
                    foreach($survey as $row => $val) {

                        $row_has_error = false;

                        $user_login = $val['user_id'];
                        $survey_title = $val['survey_id'];
                        $start_date = $val['start_date'];
                        $end_date = $val['end_date'];

                        if ($user_login == '') {
                            $error_messages[$row][] = 'Login ID can not be empty';
                            $row_has_error = true;
                        } elseif (!$this->user_model->get_user_by_login($user_login)) {
                            $error_messages[$row][] = 'Login ID does not exists';
                            $row_has_error = true;
                        } else{
                            $user_id = $this->user_model->get_user_by_login($user_login)->id;
                        }

                        if ($survey_title == '') {
                            $error_messages[$row][] = 'Survey Title can not be empty';
                            $row_has_error = true;
                        } elseif (!$this->survey_model->get_survey_by_title($survey_title)) {
                            $error_messages[$row][] = 'Survey Title does not exists';
                            $row_has_error = true;
                        } else{
                            $survey_id = $this->survey_model->get_survey_by_title($survey_title)->id;
                        }

                        if ($start_date == '') {
                            $error_messages[$row][] = 'Start date can not be empty';
                            $row_has_error = true;
                        } else {
                            $start_date = str_replace('/', '-', $start_date);
                            $start_date = date('Y-m-d H:i:s', strtotime($start_date));
                        }
                        
                        if ($end_date == '') {
                            $error_messages[$row][] = 'End date can not be empty';
                            $row_has_error = true;
                        } else {
                            $end_date = str_replace('/', '-', $end_date);
                            $end_date = date('Y-m-d H:i:s', strtotime($end_date));
                        }

                        if ($row_has_error) {
                            $invalid_survey[$row] = $val;
                            unset($survey[$row]);
                        } else {
                            $survey[$row]['user_id'] = $user_id;
                            $survey[$row]['survey_id'] = $survey_id;
                            $survey[$row]['start_date'] = $start_date;
                            $survey[$row]['end_date'] = $end_date;
                            $survey[$row]['status'] = 'open';
                            $survey[$row]['added'] = date("Y-m-d H:i:s");
                        }
                    }
                }

                if (count($survey) <= 0 && count($invalid_survey) <= 0) {
                    $this->session->set_flashdata('message_error', 'File does not contain any row.');
                    redirect('administrator/assign_survey/bulk');
                }

                $this->session->set_flashdata('bulk_survey', $survey);
                $this->session->set_flashdata('bulk_invalid_survey', $invalid_survey);
                $this->session->set_flashdata('bulk_error_messages', $error_messages);

            } else {
                $this->session->set_flashdata('message_error', $file_error);
                redirect('administrator/assign_survey/bulk');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Please upload an Excel file.');
            redirect('administrator/assign_survey/bulk');
        }

        $this->session->set_flashdata('bulk_action', 1);
        redirect('administrator/assign_survey/bulk_upload_action');
    }

    public function bulk_upload_action()
    {
        // set page specific variables
        $page_info['title'] = 'Take an Action'. $this->site_name;
        $page_info['view_page'] = 'administrator/assign_survey_bulk_action_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $page_info['bulk_survey'] = array();
        $page_info['bulk_invalid_survey'] = array();
        $page_info['bulk_error_messages'] = array();


        if ($this->session->flashdata('bulk_action')) {
            $this->session->keep_flashdata('bulk_action');
        }
        if ( (int)$this->session->flashdata('bulk_action') == 0 ) {
            redirect('administrator/assign_survey/bulk');
        }


        if ($this->session->flashdata('bulk_survey')) {
            $page_info['bulk_survey'] = $this->session->flashdata('bulk_survey');
            $this->session->keep_flashdata('bulk_survey');
        }
        if ($this->session->flashdata('bulk_invalid_survey')) {
            $page_info['bulk_invalid_survey'] = $this->session->flashdata('bulk_invalid_survey');
            $this->session->keep_flashdata('bulk_invalid_survey');
        }
        if ($this->session->flashdata('bulk_error_messages')) {
            $page_info['bulk_error_messages'] = $this->session->flashdata('bulk_error_messages');
            $this->session->keep_flashdata('bulk_error_messages');
        }


        $bulk_invalid_survey = $page_info['bulk_invalid_survey'];
        $bulk_error_messages = $page_info['bulk_error_messages'];

        if ($bulk_invalid_survey && count($bulk_invalid_survey) < 250) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Survey Title'),
                '1' => array('data'=> 'Login ID'),
                '2' => array('data'=> 'Error')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            foreach($bulk_invalid_survey as $row => $record) {

                $error_message = '';
                for ($i=0; $i<count($bulk_error_messages[$row]); $i++) {
                    if ($i>0) { $error_message .= '<br />'; }
                    $error_message .= $bulk_error_messages[$row][$i];
                }

                $tbl_row = array(
                    '0' => array('data'=> $record['survey_id']),
                    '1' => array('data'=> $record['user_id']),
                    '2' => array('data'=> $error_message)
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['bulk_invalid_users_table'] = $this->table->generate();
        }


        // determine messages
        if ($this->session->flashdata('message_error')) { $page_info['message_error'] = $this->session->flashdata('message_error'); }
        if ($this->session->flashdata('message_success')) { $page_info['message_success'] = $this->session->flashdata('message_success'); }

        // load view
	$this->load->view('administrator/layouts/default', $page_info);
    }

    public function bulk_upload_do_action()
    {
        $bulk_users = array();

        if ($this->session->flashdata('bulk_survey')) {
            $bulk_survey = $this->session->flashdata('bulk_survey');
        }

        // bulk insert
        $this->survey_model->assign_survey_bulk_user($bulk_survey);
        $this->session->set_flashdata('message_success', 'Record(s) inserted successfully.');

        redirect('administrator/assign_survey/bulk');
    }
    
    // set empty default form field values
    private function _set_fields()
    {
        @$this->form_data->survey_id = 0;
        $this->form_data->survey_id_hidden = 0;
        $this->form_data->start_date = date('d/m/Y');
        $this->form_data->end_date = date('d/m/Y', strtotime('1 month', time()));
        $this->form_data->user_group_id = '';
    }

    // validation rules
    private function _set_rules()
    {
        $this->form_validation->set_rules('start_date', 'Start Date', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('end_date', 'End Date', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('user_group_id', 'User Group', 'required|trim|xss_clean|strip_tags');
    }

}

/* End of file assign_exam.php */
/* Location: ./application/controllers/administrator/assign_exam.php */