<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Result_team extends MY_Controller
{
    var $current_page = "result";
    var $exam_list = array();
    var $user_group_list = array();
    var $user_team_list = array();

    function __construct()
    {
        parent::__construct();
        $this->form_data = new StdClass;
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library('excel');
        $this->load->library('table');
        $this->load->library('pagination');
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('user_group_model');
        $this->load->model('user_team_model');
        $this->load->model('exam_model');
        $this->load->model('result_model');


        // pre-load lists
        $open_exams = $this->exam_model->get_exams();
        $this->exam_list[] = 'Select an Exam';
        if ($open_exams) {
            for ($i=0; $i<count($open_exams); $i++) {
                $this->exam_list[$open_exams[$i]->id] = $open_exams[$i]->exam_title;
            }
        }

        $user_groups = $this->user_group_model->get_user_groups();
        $this->user_group_list[] = 'Select an User Group';
        if ($user_groups) {
            for ($i=0; $i<count($user_groups); $i++) {
                $this->user_group_list[$user_groups[$i]->id] = $user_groups[$i]->group_name;
            }
        }

        $user_teams = $this->user_team_model->get_user_teams();
        $this->user_team_list[] = 'Select an User Team';
        if ($user_teams) {
            for ($i=0; $i<count($user_teams); $i++) {
                $this->user_team_list[$user_teams[$i]->id] = $user_teams[$i]->team_name;
            }
        }

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

    public function index()
    {
        // set page specific variables
        $page_info['title'] = 'Exam Results'. $this->site_name;
        $page_info['view_page'] = 'administrator/result_team_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $records = '';
        $records_paged = '';
        $this->_set_fields();

        if ($this->session->flashdata('exam_id')) {
            $this->form_data->exam_id = $this->session->flashdata('exam_id');
            $this->session->keep_flashdata('exam_id');
        }
        if ($this->session->flashdata('group_id')) {
            $this->form_data->group_id = $this->session->flashdata('group_id');
            $this->session->keep_flashdata('group_id');
        }
        if ($this->session->flashdata('team_id')) {
            $this->form_data->team_id = $this->session->flashdata('team_id');
            $this->session->keep_flashdata('team_id');
        }
        if ($this->session->flashdata('team_date_from')) {
            $this->form_data->date_from = date('d/m/Y', strtotime($this->session->flashdata('team_date_from')));
            $this->session->keep_flashdata('team_date_from');
        }
        if ($this->session->flashdata('team_date_to')) {
            $this->form_data->date_to = date('d/m/Y', strtotime($this->session->flashdata('team_date_to')));
            $this->session->keep_flashdata('team_date_to');
        }
        if ($this->session->flashdata('team_records')) {
            $records = $this->session->flashdata('team_records');
            $this->session->keep_flashdata('team_records');
        }

        if ($records) {

            $exam_id = (int)$this->form_data->exam_id;
            $group_id = (int)$this->form_data->group_id;
            $team_id = (int)$this->form_data->team_id;
            $start_date = $this->session->flashdata('team_date_from');
            $end_date = $this->session->flashdata('team_date_to');

            $attendee_number = (int)$this->result_model->get_attendee_list_count($exam_id, $group_id, $team_id, $start_date, $end_date);
            $nonattendee_number = (int)$this->result_model->get_non_attendee_list_count($exam_id, $group_id, $team_id, $start_date, $end_date);
            $page_info['attendee'] = $attendee_number;
            $page_info['non_attendee'] = $nonattendee_number;
            if ( ($attendee_number + $nonattendee_number) > 0 ) {
                $page_info['response_rate'] = floor( ($attendee_number / ($attendee_number + $nonattendee_number)) * 100);
            } else {
                $page_info['response_rate'] = 0;
            }

            // build paginated list
            $config = array();
            $config["base_url"] = base_url() . "administrator/result_team";
            $config["total_rows"] = count($records);
            $per_page = $this->config->item('per_page');
            $uri_segment = $this->config->item('uri_segment');
            $page_offset = $this->uri->segment($uri_segment);

            $this->pagination->initialize($config);
            $page_info['pagin_links'] = $this->pagination->create_links();
            $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

            $records_paged = array_slice($records, $page_offset, $per_page);
            
            $tbl_heading = array(
                '0' => array('data'=> 'Name'),
                '1' => array('data'=> 'ID'),
                '2' => array('data'=> 'Team'),
                '3' => array('data'=> 'Correct'),
                '4' => array('data'=> 'Incorrect'),
                '5' => array('data'=> 'Pass'),
                '6' => array('data'=> 'Total attempt'),
                '7' => array('data'=> 'Total achived'),
                '8' => array('data'=> 'Total Mark'),
                '9' => array('data'=> 'Score %'),
                '10' => array('data'=> 'Competency level'),
                '11' => array('data'=> 'Start Time'),
                '12' => array('data'=> 'End Time'),
                '13' => array('data'=> '', 'class' => 'center')
            );
            $this->table->set_heading($tbl_heading);
            
            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records_paged); $i++) {

                $user_exam_id = (int)$records_paged[$i]->user_exam_id;
                $user_name = $records_paged[$i]->user_name;
                $user_login = $records_paged[$i]->user_login;
                $team_name = $records_paged[$i]->team_name;
                $correct = (int)$records_paged[$i]->result_total_correct;
                $wrong = (int)$records_paged[$i]->result_total_wrong;
                $dontknow = (int)$records_paged[$i]->result_total_dontknow;
                $answered = (int)$records_paged[$i]->result_total_answered;
                $user_score = (float)$records_paged[$i]->result_user_score;
                $exam_score = (float)$records_paged[$i]->result_exam_score;
                $user_score_percent = number_format((float)$records_paged[$i]->result_user_score_percent) .'%';
                $competency_level = $records_paged[$i]->result_competency_level;
                $result_start_time = date('d-m-Y, g:ia', strtotime($records_paged[$i]->result_start_time));
                $result_end_time = date('d-m-Y, g:ia', strtotime($records_paged[$i]->result_end_time));

                $action_str = '';
                if(!empty($records_paged[$i]->result_exam_state))
                {
                    $action_str = anchor(base_url('administrator/result_team/review/'. $user_exam_id), '<i class="icon-check"></i>', array('title' => 'Review Answers'));
                }
                else
                {
                    $action_str = 'Manual Upload';
                }
                
                
                $tbl_row = array(
                    '0' => array('data'=> $user_name),
                    '1' => array('data'=> $user_login),
                    '2' => array('data'=> $team_name),
                    '3' => array('data'=> $correct),
                    '4' => array('data'=> $wrong),
                    '5' => array('data'=> $dontknow),
                    '6' => array('data'=> $answered),
                    '7' => array('data'=> $user_score),
                    '8' => array('data'=> $exam_score),
                    '9' => array('data'=> $user_score_percent),
                    '10' => array('data'=> $competency_level),
                    '11' => array('data'=> $result_start_time),
                    '12' => array('data'=> $result_end_time),
                    '13' => array('data'=> $action_str, 'class' => 'center', 'width' => '50px')
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['records_table'] = $this->table->generate();

        } else {
            //$page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No results found.</div>';
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

    public function show_results()
    {
        $exam_id = (int)$this->input->post('exam_id');
        $group_id = (int)$this->input->post('group_id');
        $team_id = (int)$this->input->post('team_id');
        $date_from = $this->input->post('date_from');
        $date_to = $this->input->post('date_to');

        if ($exam_id > 0) {

            $this->session->set_flashdata('exam_id', $exam_id);
            $this->session->set_flashdata('group_id', $group_id);
            $this->session->set_flashdata('team_id', $team_id);

            $date_from = $this->convert_date_format($date_from);
            $this->session->set_flashdata('team_date_from', $date_from);

            $date_to = $this->convert_date_format($date_to);
            $this->session->set_flashdata('team_date_to', $date_to);

            $results = $this->result_model->get_results_by_team_id($exam_id, $group_id, $team_id, $date_from, $date_to);

            //var_dump($this->db->last_query());die;
            //var_dump($results);die;
            $this->session->set_flashdata('team_records', $results);

        } else {
            $this->session->set_flashdata('message_error', 'Please select an exam.' );
        }

        redirect('administrator/result_team');
    }

    public function export()
    {
        if ($this->session->flashdata('exam_id')) {
            $this->form_data->exam_id = $this->session->flashdata('exam_id');
            $this->session->keep_flashdata('exam_id');
        }
        if ($this->session->flashdata('group_id')) {
            $this->form_data->group_id = $this->session->flashdata('group_id');
            $this->session->keep_flashdata('group_id');
        }
        if ($this->session->flashdata('team_id')) {
            $this->form_data->team_id = $this->session->flashdata('team_id');
            $this->session->keep_flashdata('team_id');
        }
        if ($this->session->flashdata('team_date_from')) {
            $this->form_data->date_from = date('d/m/Y', strtotime($this->session->flashdata('team_date_from')));
            $this->session->keep_flashdata('team_date_from');
        }
        if ($this->session->flashdata('team_date_to')) {
            $this->form_data->date_to = date('d/m/Y', strtotime($this->session->flashdata('team_date_to')));
            $this->session->keep_flashdata('team_date_to');
        }
        if ($this->session->flashdata('team_records')) {
            $records = $this->session->flashdata('team_records');
            $this->session->keep_flashdata('team_records');
        }
        
        $records = $this->session->flashdata('team_records');

        if (is_array($records) && count($records) > 0) {

            $exam_title = $this->exam_list[$this->session->flashdata('exam_id')];

            $this->excel->setActiveSheetIndex(0);
            $this->excel->getActiveSheet()->setTitle('Sheet1');

            // set result column header
            $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
            $this->excel->getActiveSheet()->setCellValue('B1', 'Name');
            $this->excel->getActiveSheet()->setCellValue('C1', 'ID');
            $this->excel->getActiveSheet()->setCellValue('D1', 'Team');
            $this->excel->getActiveSheet()->setCellValue('E1', 'Correct');
            $this->excel->getActiveSheet()->setCellValue('F1', 'Incorrect');
            $this->excel->getActiveSheet()->setCellValue('G1', 'Pass');
            $this->excel->getActiveSheet()->setCellValue('H1', 'Total Attempt');
            $this->excel->getActiveSheet()->setCellValue('I1', 'Point');
            $this->excel->getActiveSheet()->setCellValue('J1', 'Score %');
            $this->excel->getActiveSheet()->setCellValue('K1', 'Competency Level');
            $this->excel->getActiveSheet()->setCellValue('L1', 'Start Time');
            $this->excel->getActiveSheet()->setCellValue('M1', 'End Time');

            $this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
            $this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
            $this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
            $this->excel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
            $this->excel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
            $this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
            $this->excel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
            $this->excel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
            $this->excel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
            $this->excel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);

            // fill data
            for ($i=0; $i<count($records); $i++) {

                $serial = $i+1;

                $user_name = $records[$i]->user_name;
                $user_login = $records[$i]->user_login;
                $team_name = $records[$i]->team_name;
                $correct = $records[$i]->result_total_correct;
                $wrong = $records[$i]->result_total_wrong;
                $dontknow = $records[$i]->result_total_dontknow;
                $answered = $records[$i]->result_total_answered;
                $point = $records[$i]->result_user_score;
                $score = $records[$i]->result_user_score_percent;
                $competency_level = $records[$i]->result_competency_level;
                $start_time = date('n/d/Y g:i A', strtotime($records[$i]->result_start_time));
                $end_time = date('n/d/Y g:i A', strtotime($records[$i]->result_end_time));

                $row = $i + 2;

                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $user_name);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $user_login);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $team_name);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $correct);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $wrong);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $dontknow);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $answered);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $point);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $score);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $competency_level);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(11, $row, $start_time);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $end_time);
            }

            $filename = 'Exam Result ('. $exam_title .') '. date('Y-m-d') .'.xls';

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'. $filename. '"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
            $objWriter->save('php://output');

        } else {
            $this->session->set_flashdata('message_error', 'Records not found to export');
            redirect('administrator/result_team');
        }
    }

    public function export_attendee_list()
    {
        $this->_set_fields();
        
        if ($this->session->flashdata('exam_id')) {
            $this->form_data->exam_id = $this->session->flashdata('exam_id');
            $this->session->keep_flashdata('exam_id');
        }
        if ($this->session->flashdata('group_id')) {
            $this->form_data->group_id = $this->session->flashdata('group_id');
            $this->session->keep_flashdata('group_id');
        }
        if ($this->session->flashdata('team_id')) {
            $this->form_data->team_id = $this->session->flashdata('team_id');
            $this->session->keep_flashdata('team_id');
        }
        if ($this->session->flashdata('team_date_from')) {
            $this->form_data->date_from = date('d/m/Y', strtotime($this->session->flashdata('team_date_from')));
            $this->session->keep_flashdata('team_date_from');
        }
        if ($this->session->flashdata('team_date_to')) {
            $this->form_data->date_to = date('d/m/Y', strtotime($this->session->flashdata('team_date_to')));
            $this->session->keep_flashdata('team_date_to');
        }
        if ($this->session->flashdata('team_records')) {
            $this->session->keep_flashdata('team_records');
        }

        $exam_title = $this->exam_list[$this->session->flashdata('exam_id')];

        $attendee_list = $this->result_model->get_attendee_list($this->form_data->exam_id, $this->form_data->group_id, $this->form_data->team_id, $this->session->flashdata('team_date_from'), $this->session->flashdata('team_date_to'));
        $non_attendee_list = $this->result_model->get_non_attendee_list($this->form_data->exam_id, $this->form_data->group_id, $this->form_data->team_id, $this->session->flashdata('team_date_from'), $this->session->flashdata('team_date_to'));


        // --------------------------------------------------------------------
        // preparing attendee sheet
        // --------------------------------------------------------------------
        $this->excel->setActiveSheetIndex(0);
        $this->excel->getActiveSheet()->setTitle('Attendee List');

        // set result column header
        $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
        $this->excel->getActiveSheet()->setCellValue('B1', 'Name');
        $this->excel->getActiveSheet()->setCellValue('C1', 'ID');
        $this->excel->getActiveSheet()->setCellValue('D1', 'Team');
        $this->excel->getActiveSheet()->setCellValue('E1', 'Assigned Start Time');
        $this->excel->getActiveSheet()->setCellValue('F1', 'Assigned End Time');

        $this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

        // fill data
        for ($i=0; $i<count($attendee_list); $i++) {

            $serial = $i+1;

            $user_name = $attendee_list[$i]->user_name;
            $user_login = $attendee_list[$i]->user_login;
            $team_name = $attendee_list[$i]->team_name;
            $start_time = date('n/d/Y g:i A', strtotime($attendee_list[$i]->ue_start_date));
            $end_time = date('n/d/Y g:i A', strtotime($attendee_list[$i]->ue_end_date));

            $row = $i + 2;

            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $user_name);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $user_login);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $team_name);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $start_time);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $end_time);
        }


        // --------------------------------------------------------------------
        // preparing non attendee sheet
        // --------------------------------------------------------------------
        $this->excel->createSheet(1);
        $this->excel->setActiveSheetIndex(1);
        $this->excel->getActiveSheet()->setTitle('Non Attendee List');

        // set result column header
        $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
        $this->excel->getActiveSheet()->setCellValue('B1', 'Name');
        $this->excel->getActiveSheet()->setCellValue('C1', 'ID');
        $this->excel->getActiveSheet()->setCellValue('D1', 'Team');
        $this->excel->getActiveSheet()->setCellValue('E1', 'Assigned Start Time');
        $this->excel->getActiveSheet()->setCellValue('F1', 'Assigned End Time');

        $this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

        // fill data
        for ($i=0; $i<count($non_attendee_list); $i++) {

            $serial = $i+1;

            $user_name = $non_attendee_list[$i]->user_name;
            $user_login = $non_attendee_list[$i]->user_login;
            $team_name = $non_attendee_list[$i]->team_name;
            $start_time = date('n/d/Y g:i A', strtotime($non_attendee_list[$i]->ue_start_date));
            $end_time = date('n/d/Y g:i A', strtotime($non_attendee_list[$i]->ue_end_date));

            $row = $i + 2;

            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $user_name);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $user_login);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $team_name);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $start_time);
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $end_time);
        }

        $this->excel->setActiveSheetIndex(0);
        $filename = 'Attendee, Non-Attendee List ('. $exam_title .') '. date('Y-m-d') .'.xls';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'. $filename. '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $objWriter->save('php://output');

    }

    public function review($user_exam_id = 0)
    {
        // set page specific variables
        $page_info['title'] = 'Review Results'. $this->site_name;
        $page_info['view_page'] = 'administrator/review_result_view';
        $page_info['back_link'] = site_url('administrator/result_team');
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        
        $user_exam_id = (int)$user_exam_id;

        if ($this->session->flashdata('exam_id')) {
            $this->form_data->exam_id = $this->session->flashdata('exam_id');
            $this->session->keep_flashdata('exam_id');
        }
        if ($this->session->flashdata('team_id')) {
            $this->form_data->team_id = $this->session->flashdata('team_id');
            $this->session->keep_flashdata('team_id');
        }
        if ($this->session->flashdata('team_date_from')) {
            $this->form_data->date_from = date('d/m/Y', strtotime($this->session->flashdata('team_date_from')));
            $this->session->keep_flashdata('team_date_from');
        }
        if ($this->session->flashdata('team_date_to')) {
            $this->form_data->date_to = date('d/m/Y', strtotime($this->session->flashdata('team_date_to')));
            $this->session->keep_flashdata('team_date_to');
        }
        if ($this->session->flashdata('team_records')) {
            $this->session->keep_flashdata('team_records');
        }

        $result = $this->result_model->get_result_by_user_exam_id($user_exam_id);
        $exam = maybe_unserialize($result->result_exam_state);

        $page_info['user_exam_id'] = $user_exam_id;
        $page_info['result'] = $result;
        $page_info['exam'] = $exam;
        $page_info['result_id'] = $result->id;
        //print_r_pre($result); die();
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

    private function convert_date_format($date_ddmmyyyy = '')
    {
        $date = '';
        if ($date_ddmmyyyy == '') {
            $date = '';
        } else {
            $day = substr($date_ddmmyyyy, 0, 2);
            $month = substr($date_ddmmyyyy, 3, 2);
            $year = substr($date_ddmmyyyy, 6, 4);
            $date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
        }
        return $date;
    }

    // set empty default form field values
	private function _set_fields()
	{
		$this->form_data = new StdClass;
        $this->form_data->exam_id = '0';
        $this->form_data->group_id = '0';
        $this->form_data->team_id = '0';
        $this->form_data->date_from = '';
        $this->form_data->date_to = '';
	}

	// validation rules
	private function _set_rules()
	{
        $this->form_validation->set_rules('exam_id', 'Exam', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('group_id', 'User Group', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('team_id', 'User Team', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('date_from', 'Date From', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('date_to', 'Date To', 'trim|xss_clean|strip_tags');
	}
}

/* End of file result_team.php */
/* Location: ./application/controllers/administrator/result_team.php */