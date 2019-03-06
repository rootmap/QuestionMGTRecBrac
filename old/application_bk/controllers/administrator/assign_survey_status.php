<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Assign_survey_status extends MY_Controller
{
    var $current_page = "assign_survey_status";
    var $survey_list = array();
    
    var $group_list_filter = array();
    var $status_list_filter = array();

    function __construct()
    {
        parent::__construct();
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('survey_model');
        $this->load->model('user_group_model');
        $this->load->model('training_model');
        $this->load->library('excel');


        // pre-load lists
        $open_survey = $this->survey_model->get_open_surveys();
        $this->survey_list[] = 'Select a Survey';
        if ($open_survey) {
            for ($i=0; $i<count($open_survey); $i++) {
                if($open_survey[$i]->id){
                     $this->survey_list[$open_survey[$i]->id] = $open_survey[$i]->survey_title;
                }
            }
        }

        $user_groups = $this->user_group_model->get_user_groups();
        if($user_groups){
            $this->group_list_filter[] = 'All User Group';
            if ($user_groups) {
                for ($i=0; $i<count($user_groups); $i++) {
                    $this->group_list_filter[$user_groups[$i]->id] = $user_groups[$i]->group_name;
                }
            }
        }
        

        $this->status_list_filter[''] = 'All status';
        $this->status_list_filter['open'] = 'Open';
        $this->status_list_filter['inactive'] = 'Inactive';
        $this->status_list_filter['complete'] = 'Complete';
        $this->status_list_filter['incomplete'] = 'Incomplete';

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
        if($this->session->userdata('records')){
            $this->session->unset_userdata('records');
        }
        
        // set page specific variables
        $page_info['title'] = 'Assigned Survey Status'. $this->site_name;
        $page_info['view_page'] = 'administrator/assign_survey_status_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();

        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_login')) {
            $this->session->keep_flashdata('filter_login');
            $filter_login = $this->session->flashdata('filter_login');
            $this->form_data->filter_login = $filter_login;
            $filter['filter_login']['field'] = 'user_login';
            $filter['filter_login']['value'] = $filter_login;
        }
        if ($this->session->flashdata('filter_group')) {
            $this->session->keep_flashdata('filter_group');
            $filter_group = (int)$this->session->flashdata('filter_group');
            $this->form_data->filter_group = $filter_group;
            $filter['filter_group']['field'] = 'group_id';
            $filter['filter_group']['value'] = $filter_group;
        }
        if ($this->session->flashdata('filter_status')) {
            $filter_status = $this->session->flashdata('filter_status');
            if ($filter_status != '') {
                $this->session->keep_flashdata('filter_status');
                $this->form_data->filter_status = $this->session->flashdata('filter_status');
                $filter['filter_status']['field'] = 'status';
                $filter['filter_status']['value'] = $filter_status;
            }
        }
        

        $survey_id = (int)$this->session->flashdata('survey_id');

        if ($survey_id > 0) {
            
            $start_date = $this->session->flashdata('start_date');
            $end_date = $this->session->flashdata('end_date');

            $this->session->keep_flashdata('survey_id');
            $this->session->keep_flashdata('start_date');
            $this->session->keep_flashdata('end_date');

            $this->form_data->survey_id = $survey_id;
            $this->form_data->start_date = date('d/m/Y', strtotime($start_date));
            $this->form_data->end_date = date('d/m/Y', strtotime($end_date));

            $per_page = $this->config->item('per_page');
            $uri_segment = $this->config->item('uri_segment');
            $page_offset = $this->uri->segment($uri_segment);
            $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

            $record_result = $this->survey_model->get_user_survey_by_survey_paged($survey_id, $start_date, $end_date, $per_page, $page_offset, $filter);
            $page_info['records'] = $record_result['result'];
            $records = $record_result['result'];

            // build paginated list
            $config = array();
            $config["base_url"] = base_url() . "administrator/assign_survey_status";
            $config["total_rows"] = $record_result['count'];
            $this->pagination->initialize($config);

            if ($records) {
                $this->session->set_userdata('records', $records);

                $tbl_heading = array(
                    '0' => array('data'=> 'Name'),
                    '1' => array('data'=> 'ID'),
                    '2' => array('data'=> 'Group'),
                    '3' => array('data'=> 'Start Time'),
                    '4' => array('data'=> 'End Time'),
                    '5' => array('data'=> 'Status'),
                    '6' => array('data'=> 'Completed Time'),
                    '7' => array('data'=> '', 'class' => 'center')
                );
                $this->table->set_heading($tbl_heading);

                $tbl_template = array (
                    'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                    'table_close'         => '</table>'
                );
                $this->table->set_template($tbl_template);

                for ($i = 0; $i<count($records); $i++) {

                    $user_survey_id = $records[$i]->user_survey_id;

                    $user_name = $records[$i]->user_first_name .' '. $records[$i]->user_last_name;
                    $user_login = $records[$i]->user_login;
                    $group_name = $records[$i]->group_name;
                    $start_date = date('d/m/Y, g:ia', strtotime($records[$i]->start_date));
                    $end_date = date('d/m/Y, g:ia', strtotime($records[$i]->end_date));
					if($records[$i]->completed){
						$completed_date = date('d/m/Y, g:ia', strtotime($records[$i]->completed));
					}else{
						$completed_date = '';
					}
                    $status = ucfirst($records[$i]->status);

                    $action_str = '';
                    $action_inactive = anchor(base_url('administrator/assign_survey_status/update_status/'. $user_survey_id .'/inactive/'. $page_offset), '<i class="icon-ban-circle"></i>', array('title' => 'Make Inactive'));
                    $action_active = anchor(base_url('administrator/assign_survey_status/update_status/'. $user_survey_id .'/active/'. $page_offset), '<i class="icon-ok"></i>', array('title' => 'Make Active'));
                    $action_delete = anchor(base_url('administrator/assign_survey_status/update_status/'. $user_survey_id .'/delete/'. $page_offset), '<i class="icon-remove"></i>', array('title' => 'Delete', 'class' => 'action-delete'));

                    if (strtolower($status) == 'open') {
                        $action_str = $action_inactive;
                    } elseif (strtolower($status) == 'inactive') {
                        $action_str = $action_active .'&nbsp;&nbsp;&nbsp;'. $action_delete;
                    }  elseif (strtolower($status) == 'incomplete') {
                        $action_str = $action_delete;
                    }

                    $tbl_row = array(
                        '0' => array('data'=> $user_name),
                        '1' => array('data'=> $user_login),
                        '2' => array('data'=> $group_name),
                        '3' => array('data'=> $start_date),
                        '4' => array('data'=> $end_date),
                        '5' => array('data'=> $status),
						'6' => array('data'=> $completed_date),
                        '7' => array('data'=> $action_str, 'class' => 'center', 'width' => '50px')
                    );
                    $this->table->add_row($tbl_row);
                }

                $page_info['records_table'] = $this->table->generate();
                $page_info['pagin_links'] = $this->pagination->create_links();

            } else {
                $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
                $page_info['pagin_links'] = '';
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

    public function filter()
    {
        $filter_login = $this->input->post('filter_login');
        $filter_group = (int)$this->input->post('filter_group');
        $filter_status = $this->input->post('filter_status');

        $this->session->keep_flashdata('survey_id');
        $this->session->keep_flashdata('start_date');
        $this->session->keep_flashdata('end_date');

        if ($filter_login != '') {
            $this->session->set_flashdata('filter_login', $filter_login);
        }
        if ($filter_group != '') {
            $this->session->set_flashdata('filter_group', $filter_group);
        }
        if ($filter_status == 'open' || $filter_status == 'inactive' || $filter_status == 'complete' || $filter_status == 'incomplete') {
            $this->session->set_flashdata('filter_status', $filter_status);
        }

        redirect('administrator/assign_survey_status');
    }

    public function get_list()
    {
        $survey_id = (int)$this->input->post('survey_id');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        if ($survey_id > 0) {

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

           if($this->input->post('show_assigned_survey_status_submit')){
                $this->session->set_flashdata('survey_id', $survey_id);
                $this->session->set_flashdata('start_date', $start_date);
                $this->session->set_flashdata('end_date', $end_date);
            }
            
            
        } else {
            $this->session->set_flashdata('message_error', 'Please select a survey.' );
        }

        redirect('administrator/assign_survey_status');
    }

    public function update_status($user_training_id = 0, $status = '', $page = 0)
    {
        $user_training_id = (int)$user_training_id;
        $page = (int)$page;
        if ($page <= 0) { $page = ''; }


        if($this->session->flashdata('training_id')) {
            $this->session->keep_flashdata('training_id');
        }
        if($this->session->flashdata('start_date')) {
            $this->session->keep_flashdata('start_date');
        }
        if($this->session->flashdata('end_date')) {
            $this->session->keep_flashdata('end_date');
        }


        if ($user_training_id <= 0 || $status == '') {
            return false;
        }

        if ($status == 'active') {
            $res = $this->training_model->active_user_training($user_training_id);
            if ($res) {
                $this->session->set_flashdata('message_success', "User Training status changed to 'active' successfully.");
            } else {
                $this->session->set_flashdata('message_error', "User Training status can't changed to 'active'.");
            }
        } elseif ($status == 'inactive') {
            $res = $this->training_model->inactive_user_training($user_training_id);
            if ($res) {
                $this->session->set_flashdata('message_success', "User Training status changed to 'inactive' successfully.");
            } else {
                $this->session->set_flashdata('message_error', "User Training status can't changed to 'inactive'.");
            }
        } elseif ($status == 'delete') {
            $res = $this->training_model->delete_user_training($user_training_id);
            if ($res) {
                $this->session->set_flashdata('message_success', "User Training delete successful.");
            } else {
                $this->session->set_flashdata('message_error', "Failed to delete Training.");
            }
            $page = '';
        }

        redirect('administrator/assign_training_status/'. $page);
    }
    
    
    public function export_data()
    {
        if($this->session->userdata('records')){
            $records = $this->session->userdata('records');
            if (is_array($records) && count($records) > 0) {

                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle('Sheet1');

                // set result column header
                $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
                $this->excel->getActiveSheet()->setCellValue('B1', 'Name');
                $this->excel->getActiveSheet()->setCellValue('C1', 'ID');
                $this->excel->getActiveSheet()->setCellValue('D1', 'Group');
                $this->excel->getActiveSheet()->setCellValue('E1', 'Start Time');
                $this->excel->getActiveSheet()->setCellValue('F1', 'End Time');
                $this->excel->getActiveSheet()->setCellValue('G1', 'Status');
                $this->excel->getActiveSheet()->setCellValue('H1', 'Completed Time');

                $this->excel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

                // fill data
                for ($i=0; $i<count($records); $i++) {
                
                    $serial = $i+1;
                    
                    $user_survey_id = $records[$i]->user_survey_id;

                    $user_name = $records[$i]->user_first_name .' '. $records[$i]->user_last_name;
                    $user_login = $records[$i]->user_login;
                    $group_name = $records[$i]->group_name;
                    $start_date = date('d/m/Y, g:ia', strtotime($records[$i]->start_date));
                    $end_date = date('d/m/Y, g:ia', strtotime($records[$i]->end_date));
                    $status = ucfirst($records[$i]->status);
					if($records[$i]->completed){
						$completed_date = date('d/m/Y, g:ia', strtotime($records[$i]->completed));
					}else{
						$completed_date = '';
					}
                    
                    $row = $i + 2;

                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $user_name);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $user_login);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $group_name);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $start_date);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $end_date);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $status);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $completed_date);
                }

                $filename = 'Assigned Survey Status '. date('Y-m-d') .'.xls';

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="'. $filename. '"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save('php://output');

            } else {
                $this->session->set_flashdata('message_error', 'Records not found to export');
                redirect('administrator/assign_survey_status');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Records not found to export');
            redirect('administrator/assign_survey_status');
        }
    }

    // set empty default form field values
    private function _set_fields()
    {
        @$this->form_data->training_id = 0;
        $this->form_data->start_date = date('d/m/Y');
        $this->form_data->end_date = date('d/m/Y', strtotime('1 month', time()));

        $this->form_data->filter_login = '';
        $this->form_data->filter_group = 0;
        $this->form_data->filter_status = '';
    }

    // validation rules
    private function _set_rules()
    {
        $this->form_validation->set_rules('training_id', 'Training', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('start_date', 'Start Date', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('end_date', 'End Date', 'required|trim|xss_clean|strip_tags');
    }

}

/* End of file assign_status.php */
/* Location: ./application/controllers/administrator/assign_status.php */