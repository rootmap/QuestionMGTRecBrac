<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Assign_status extends MY_Controller
{
    var $current_page = "assign-status";
    var $exam_list = array();

    var $team_list_filter = array();
    var $status_list_filter = array();

    function __construct()
    {
        parent::__construct();
		$this->form_data = new StdClass;
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('exam_model');
        $this->load->model('user_exam_model');
        $this->load->model('user_team_model');


        // pre-load lists
        $open_exams = $this->exam_model->get_open_exams();
        $this->exam_list[] = 'Select an Exam';
        if ($open_exams) {
            for ($i=0; $i<count($open_exams); $i++) {
                $this->exam_list[$open_exams[$i]->id] = $open_exams[$i]->exam_title;
            }
        }

        $this->status_list_filter[''] = 'All status';
        $this->status_list_filter['open'] = 'Open';
        $this->status_list_filter['inactive'] = 'Inactive';
        $this->status_list_filter['complete'] = 'Complete';
        $this->status_list_filter['incomplete'] = 'Incomplete';

        $user_teams = $this->user_team_model->get_user_teams();
        $this->team_list_filter[] = 'All teams';
        if ($user_teams) {
            for ($i=0; $i<count($user_teams); $i++) {
                $this->team_list_filter[$user_teams[$i]->id] = $user_teams[$i]->team_name;
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

    /**
     * Display paginated list of exams
     * @return void
     */
    public function index()
    {
        // set page specific variables
        $page_info['title'] = 'Assigned Exam Status'. $this->site_name;
        $page_info['view_page'] = 'administrator/assign_status_form_view';
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
        if ($this->session->flashdata('filter_team')) {
            $this->session->keep_flashdata('filter_team');
            $filter_team = (int)$this->session->flashdata('filter_team');
            $this->form_data->filter_team = $filter_team;
            $filter['filter_team']['field'] = 'user_team_id';
            $filter['filter_team']['value'] = $filter_team;
        }
        if ($this->session->flashdata('filter_status')) {
            $filter_status = $this->session->flashdata('filter_status');
            if ($filter_status != '') {
                $this->session->keep_flashdata('filter_status');
                $this->form_data->filter_status = $this->session->flashdata('filter_status');
                $filter['filter_status']['field'] = 'ue_status';
                $filter['filter_status']['value'] = $filter_status;
            }
        }
        

        $exam_id = (int)$this->session->flashdata('exam_id');

        if ($exam_id > 0) {
            
            $ue_start_datetime = $this->session->flashdata('ue_start_datetime');
            $ue_end_datetime = $this->session->flashdata('ue_end_datetime');

            $this->session->keep_flashdata('exam_id');
            $this->session->keep_flashdata('ue_start_datetime');
            $this->session->keep_flashdata('ue_end_datetime');

            $this->form_data->exam_id = $exam_id;
            $this->form_data->ue_start_date = date('d/m/Y', strtotime($ue_start_datetime));
            $this->form_data->ue_start_time = date('h:i A', strtotime($ue_start_datetime));
            $this->form_data->ue_end_date = date('d/m/Y', strtotime($ue_end_datetime));
            $this->form_data->ue_end_time = date('h:i A', strtotime($ue_end_datetime));


            $per_page = $this->config->item('per_page');
            $uri_segment = $this->config->item('uri_segment');
            $page_offset = $this->uri->segment($uri_segment);
            $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

            $record_result = $this->user_exam_model->get_user_exams_by_exam_paged($exam_id, $ue_start_datetime, $ue_end_datetime, $per_page, $page_offset, $filter);
            $page_info['records'] = $record_result['result'];
            $records = $record_result['result'];

            // build paginated list
            $config = array();
            $config["base_url"] = base_url() . "administrator/assign_status";
            $config["total_rows"] = $record_result['count'];
            $this->pagination->initialize($config);

            if ($records) {

                $tbl_heading = array(
                    '0' => array('data'=> 'Name'),
                    '1' => array('data'=> 'ID'),
                    '2' => array('data'=> 'Team'),
                    '3' => array('data'=> 'Start Time'),
                    '4' => array('data'=> 'End Time'),
                    '5' => array('data'=> 'Status'),
                    '6' => array('data'=> '', 'class' => 'center'),
                    '7' => array('data'=> 'Reassign')
                );
                $this->table->set_heading($tbl_heading);

                $tbl_template = array (
                    'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                    'table_close'         => '</table>'
                );
                $this->table->set_template($tbl_template);

                for ($i = 0; $i<count($records); $i++) {

                    $user_exam_id = $records[$i]->user_exam_id;

                    $user_name = $records[$i]->user_first_name .' '. $records[$i]->user_last_name;
                    $user_login = $records[$i]->user_login;
                    $team_name = $records[$i]->team_name;
                    $start_time = date('d/m/Y, g:ia', strtotime($records[$i]->ue_start_date));
                    $end_time = date('d/m/Y, g:ia', strtotime($records[$i]->ue_end_date));
                    $status = ucfirst($records[$i]->ue_status);

                    $action_str = '';
                    $action_reassign = '';
                    $action_inactive = anchor(base_url('administrator/assign_status/update_status/'. $user_exam_id .'/inactive/'. $page_offset), '<i class="icon-ban-circle"></i>', array('title' => 'Make Inactive'));
                    $action_active = anchor(base_url('administrator/assign_status/update_status/'. $user_exam_id .'/active/'. $page_offset), '<i class="icon-ok"></i>', array('title' => 'Make Active'));
                    $action_retake = anchor(base_url('administrator/assign_status/update_status/'. $user_exam_id .'/retake/'. $page_offset), '<i class="icon-repeat"></i>', array('title' => 'Retake', 'class' => 'action-retake'));
                    $action_delete = anchor(base_url('administrator/assign_status/update_status/'. $user_exam_id .'/delete/'. $page_offset), '<i class="icon-remove"></i>', array('title' => 'Delete', 'class' => 'action-delete'));

                    if (strtolower($status) == 'open') {
                        $action_str = $action_inactive;
                        $action_reassign = '<a href="#ReassignModal" id="reassign_button" role="button" class="btn btn-danger" data-toggle="modal" data-start_date="'.date('d/m/Y', strtotime($records[$i]->ue_start_date)).'" data-end_date="'.date('d/m/Y', strtotime($records[$i]->ue_end_date)).'" data-start_time="'.date('h:i A', strtotime($records[$i]->ue_start_date)).'" data-end_time="'.date('h:i A', strtotime($records[$i]->ue_end_date)).'" data-user_exam_id="'.$user_exam_id.'" data-page="'.$page_offset.'">Reassign</a>';
                        //$action_reassign = anchor(base_url('administrator/assign_status/reassign/'. $user_exam_id .'/'. $page_offset), '<button class="btn btn-danger">Reassign</button>', array('title' => 'Reassign'));
                    } elseif (strtolower($status) == 'inactive') {
                        $action_str = $action_active .'&nbsp;&nbsp;&nbsp;'. $action_delete;
                    }  elseif (strtolower($status) == 'incomplete') {
                        $action_str = $action_retake .'&nbsp;&nbsp;&nbsp;'. $action_delete;
                    }

                    //$action_str = $action_delete;

                    $tbl_row = array(
                        '0' => array('data'=> $user_name),
                        '1' => array('data'=> $user_login),
                        '2' => array('data'=> $team_name),
                        '3' => array('data'=> $start_time),
                        '4' => array('data'=> $end_time),
                        '5' => array('data'=> $status),
                        '6' => array('data'=> $action_str, 'class' => 'center', 'width' => '50px'),
                        '7' => array('data'=> $action_reassign, 'class' => 'center', 'width' => '50px')
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
        $filter_team = (int)$this->input->post('filter_team');
        $filter_status = $this->input->post('filter_status');

        $this->session->keep_flashdata('exam_id');
        $this->session->keep_flashdata('ue_start_datetime');
        $this->session->keep_flashdata('ue_end_datetime');

        if ($filter_login != '') {
            $this->session->set_flashdata('filter_login', $filter_login);
        }
        if ($filter_team != '') {
            $this->session->set_flashdata('filter_team', $filter_team);
        }
        if ($filter_status == 'open' || $filter_status == 'inactive' || $filter_status == 'complete' || $filter_status == 'incomplete') {
            $this->session->set_flashdata('filter_status', $filter_status);
        }

        redirect('administrator/assign_status');
    }

    public function get_list()
    {
        $exam_id = (int)$this->input->post('exam_id');
        $ue_start_date = $this->input->post('ue_start_date');
        $ue_start_time = $this->input->post('ue_start_time');
        $ue_end_date = $this->input->post('ue_end_date');
        $ue_end_time = $this->input->post('ue_end_time');

        if ($exam_id > 0) {

            if ($ue_start_date == '') {
                $ue_start_date = '';
            } else {
                $day = (int)substr($ue_start_date, 0, 2);
                $month = (int)substr($ue_start_date, 3, 2);
                $year = (int)substr($ue_start_date, 6, 4);
                $ue_start_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));

                if ($ue_start_time != '') {
                    $hour = (int)substr($ue_start_time, 0, 2);
                    $min = (int)substr($ue_start_time, 3, 2);
                    $ampm = trim(strtolower(substr($ue_start_time, 6, 2)));
                    if ($ampm == 'pm') {
                        $hour = $hour + 12;
                    } elseif ($ampm == 'am') {
                        if ($hour == 12) {
                            $hour = 0;
                        }
                    }
                    $ue_start_date = date('Y-m-d H:i', mktime($hour, $min, 0, $month, $day, $year));
                }
            }

            if ($ue_end_date == '') {
                $ue_end_date = '';
            } else {
                $day = substr($ue_end_date, 0, 2);
                $month = substr($ue_end_date, 3, 2);
                $year = substr($ue_end_date, 6, 4);
                $ue_end_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));

                if ($ue_end_time != '') {
                    $hour = (int)substr($ue_end_time, 0, 2);
                    $min = (int)substr($ue_end_time, 3, 2);
                    $ampm = trim(strtolower(substr($ue_end_time, 6, 2)));
                    if ($hour == 12) {
                        $hour = 0;
                    }
                    if ($ampm == 'pm') {
                        $hour = $hour + 12;
                    }
                    $ue_end_date = date('Y-m-d H:i', mktime($hour, $min, 0, $month, $day, $year));
                }
            }

            if($this->input->post('update_date_time_assigned_exam_submit')){
                $res = $this->user_exam_model->update_user_exam_datetime($exam_id, $ue_start_date, $ue_end_date);
                if ($res) {
                    $this->session->set_flashdata('message_success', "User Exam Datetime updated successfully.");
                } else {
                    $this->session->set_flashdata('message_error', "User Exam Datetime can't update.");
                }
            }
            elseif($this->input->post('show_assigned_exam_status_submit')){
                $this->session->set_flashdata('exam_id', $exam_id);
                $this->session->set_flashdata('ue_start_datetime', $ue_start_date);
                $this->session->set_flashdata('ue_end_datetime', $ue_end_date);
            }
            
            
        } else {
            $this->session->set_flashdata('message_error', 'Please select an exam.' );
        }

        redirect('administrator/assign_status');
    }

    public function update_status($user_exam_id = 0, $status = '', $page = 0)
    {
        $user_exam_id = (int)$user_exam_id;
        $page = (int)$page;
        if ($page <= 0) { $page = ''; }


        if($this->session->flashdata('exam_id')) {
            $this->session->keep_flashdata('exam_id');
        }
        if($this->session->flashdata('ue_start_datetime')) {
            $this->session->keep_flashdata('ue_start_datetime');
        }
        if($this->session->flashdata('ue_end_datetime')) {
            $this->session->keep_flashdata('ue_end_datetime');
        }


        if ($user_exam_id <= 0 || $status == '') {
            return false;
        }

        if ($status == 'active') {
            $res = $this->user_exam_model->active_user_exam($user_exam_id);
            if ($res) {
                $this->session->set_flashdata('message_success', "User Exam status changed to 'active' successfully.");
            } else {
                $this->session->set_flashdata('message_error', "User Exam status can't changed to 'active'.");
            }
        } elseif ($status == 'inactive') {
            $res = $this->user_exam_model->inactive_user_exam($user_exam_id);
            if ($res) {
                $this->session->set_flashdata('message_success', "User Exam status changed to 'inactive' successfully.");
            } else {
                $this->session->set_flashdata('message_error', "User Exam status can't changed to 'inactive'.");
            }
        }  elseif ($status == 'inactive') {
            $res = $this->user_exam_model->inactive_user_exam($user_exam_id);
            if ($res) {
                $this->session->set_flashdata('message_success', "User Exam status changed to 'inactive' successfully.");
            } else {
                $this->session->set_flashdata('message_error', "User Exam status can't changed to 'inactive'.");
            }
        } elseif ($status == 'retake') {
            $res = $this->user_exam_model->retake_user_exam($user_exam_id);
            if ($res) {
                $this->session->set_flashdata('message_success', "Retake exam is successfully.");
            } else {
                $this->session->set_flashdata('message_error', "Failed to retaking exam.");
            }
        } elseif ($status == 'delete') {
            $res = $this->user_exam_model->delete_user_exam($user_exam_id);
            if ($res) {
                $this->session->set_flashdata('message_success', "User Exam delete successful.");
            } else {
                $this->session->set_flashdata('message_error', "Failed to delete exam.");
            }
            $page = '';
        }

        redirect('administrator/assign_status/'. $page);
    }
    
     public function reassign()
    {
        $user_exam_id = (int)$this->input->post('user_exam_id');
        $page = (int)$this->input->post('page');
        $start_date = $this->input->post('start_date');
        $start_time = $this->input->post('start_time');
        $end_date = $this->input->post('end_date');
        $end_time = $this->input->post('end_time');
        
        if ($page <= 0) { $page = ''; }
        
        if ($start_date == '') {
            $start_date = '';
        } else {
            $day = (int)substr($start_date, 0, 2);
            $month = (int)substr($start_date, 3, 2);
            $year = (int)substr($start_date, 6, 4);
            $start_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));

            if($start_time != '') {
                $hour = (int)substr($start_time, 0, 2);
                $min = (int)substr($start_time, 3, 2);
                $ampm = trim(strtolower(substr($start_time, 6, 2)));
                if ($ampm == 'pm') {
                    $hour = $hour + 12;
                } elseif ($ampm == 'am') {
                    if ($hour == 12) {
                        $hour = 0;
                    }
                }
                $start_date = date('Y-m-d H:i', mktime($hour, $min, 0, $month, $day, $year));
            }
        }

        if ($end_date == '') {
            $end_date = '';
        } else {
            $day = substr($end_date, 0, 2);
            $month = substr($end_date, 3, 2);
            $year = substr($end_date, 6, 4);
            $end_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));

            if ($end_time != '') {
                $hour = (int)substr($end_time, 0, 2);
                $min = (int)substr($end_time, 3, 2);
                $ampm = trim(strtolower(substr($end_time, 6, 2)));
                if ($hour == 12) {
                    $hour = 0;
                }
                if ($ampm == 'pm') {
                    $hour = $hour + 12;
                }
                $end_date = date('Y-m-d H:i', mktime($hour, $min, 0, $month, $day, $year));
            }
        }

        
        $res = $this->user_exam_model->reassign_user_exam($user_exam_id, $start_date, $end_date);
        if ($res) {
            $this->session->set_flashdata('message_success', "User Exam reassign successfully.");
        } else {
            $this->session->set_flashdata('message_error', "User Exam can't reassign.");
        }
        redirect('administrator/assign_status/'. $page);
    }

    // set empty default form field values
    private function _set_fields()
    {
	$this->form_data = new StdClass;
	$this->form_data->exam_id = 0;
        $this->form_data->ue_start_date = date('d/m/Y');
        $this->form_data->ue_start_time = '12:00 AM';
        $this->form_data->ue_end_date = date('d/m/Y', strtotime('1 month', time()));
        $this->form_data->ue_end_time = '12:00 AM';

        $this->form_data->filter_login = '';
        $this->form_data->filter_team = 0;
        $this->form_data->filter_status = '';
    }

    // validation rules
    private function _set_rules()
    {
        $this->form_validation->set_rules('exam_id', 'Exam', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ue_start_date', 'Start Date', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ue_start_time', 'Start Time', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ue_end_date', 'End Date', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ue_end_time', 'End Time', 'trim|xss_clean|strip_tags');
    }

}

/* End of file assign_status.php */
/* Location: ./application/controllers/administrator/assign_status.php */