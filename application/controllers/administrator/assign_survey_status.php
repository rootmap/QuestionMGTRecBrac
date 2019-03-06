<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Assign_survey_status extends MY_Controller
{
    var $current_page = "assign_survey_status";
    var $survey_list = array();
    
    var $group_list_filter = array();
    var $status_list_filter = array();
    var $approval_status_list_filter = array();
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
        $this->load->model('user_group_model');
        $this->load->model('training_model');
        $this->load->library('excel');


                $this->load->model('global/update_global_model');
        $this->load->model('global/Select_global_model');

        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');


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


        $this->approval_status_list_filter[''] = 'All Approval status';
        $this->approval_status_list_filter['Pending'] = 'Pending';
        $this->approval_status_list_filter['1'] = 'Approved';
        $this->approval_status_list_filter['2'] = 'Rejected';

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
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Assigned Survey Status View'));
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

        if ($this->session->flashdata('filter_approval_status')) {
            $filter_approval_status = $this->session->flashdata('filter_approval_status');
            if ($filter_approval_status != '') {
                $this->session->keep_flashdata('filter_approval_status');
                $this->form_data->filter_approval_status = $this->session->flashdata('filter_approval_status');
                $filter['filter_approval_status']['field'] = 'is_approved';
                $filter['filter_approval_status']['value'] = $filter_approval_status;
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

    public function survey_assign_list()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Assigned Survey Status View'));
        if($this->session->userdata('records')){
            $this->session->unset_userdata('records');
        }


        
        // set page specific variables
        $page_info['title'] = 'Assigned Survey Status'. $this->site_name;
        $page_info['view_page'] = 'administrator/assign_survey_status_list';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();

        // gather filter options
        $filter = array();
        if ($this->session->flashdata('assign_filter_login')) {
            $this->session->keep_flashdata('assign_filter_login');
            $filter_login = $this->session->flashdata('assign_filter_login');
            $this->form_data->filter_login = $filter_login;
            $filter['filter_login']['field'] = 'user_login';
            $filter['filter_login']['value'] = $filter_login;
        }
        if ($this->session->flashdata('assign_filter_group')) {
            $this->session->keep_flashdata('assign_filter_group');
            $filter_group = (int)$this->session->flashdata('assign_filter_group');
            $this->form_data->filter_group = $filter_group;
            $filter['filter_group']['field'] = 'group_id';
            $filter['filter_group']['value'] = $filter_group;
        }
        if ($this->session->flashdata('assign_filter_status')) {
            $filter_status = $this->session->flashdata('assign_filter_status');
            if ($filter_status != '') {
                $this->session->keep_flashdata('assign_filter_status');
                $this->form_data->filter_status = $this->session->flashdata('assign_filter_status');
                $filter['filter_status']['field'] = 'status';
                $filter['filter_status']['value'] = $filter_status;
            }
        }

       // echo $this->session->flashdata('filter_approval_status'); die();

 
            $filter_approval_status = $this->session->flashdata('filter_approval_status');
            

            
                if ($filter_approval_status != '') {
                    if ($filter_approval_status == 'Pending') {
                        $this->session->keep_flashdata('filter_approval_status');
                        $this->form_data->filter_approval_status = $this->session->flashdata('filter_approval_status');
                        $filter['filter_approval_status']['field'] = 'is_approved';
                        $filter['filter_approval_status']['value'] = 0;
                    }
                    elseif($filter_approval_status > 0)
                    {
                        $this->session->keep_flashdata('filter_approval_status');
                         $this->form_data->filter_approval_status = $this->session->flashdata('filter_approval_status');
                        $filter['filter_approval_status']['field'] = 'is_approved';
                        $filter['filter_approval_status']['value'] = $filter_approval_status;
                    }
                    
                }
                

            
        
        

           //print_r($filter); die();

            $survey_id = (int)$this->session->flashdata('assign_survey_id');


            
            $start_date = $this->session->flashdata('assign_start_date');
            $end_date = $this->session->flashdata('assign_end_date');

            $this->session->keep_flashdata('assign_survey_id');
            $this->session->keep_flashdata('assign_start_date');
            $this->session->keep_flashdata('assign_end_date');

            $this->form_data->survey_id = $survey_id;
            $this->form_data->start_date = date('d/m/Y', strtotime($start_date));
            $this->form_data->end_date = date('d/m/Y', strtotime($end_date));

            $per_page = $this->config->item('per_page');
            $uri_segment = $this->config->item('uri_segment');
            $page_offset = $this->uri->segment($uri_segment);
            $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

            $record_result = $this->survey_model->get_user_survey_by_survey_manage_paged($survey_id, $start_date, $end_date, $per_page, $page_offset, $filter);
            $page_info['records'] = $record_result['result'];
            $records = $record_result['result'];
            //echo "<pre>";
            //print_r($records); die();
            // build paginated list
            $config = array();
            $config["base_url"] = base_url() . "administrator/assign_survey_status/survey_assign_list";
            $config["total_rows"] = $record_result['count'];
            $this->pagination->initialize($config);

            if ($records) {
                $this->session->set_userdata('records', $records);

                $tbl_heading = array(
                    '0' => array('data'=> 'Name'),
                    '1' => array('data'=> 'User Login'),
                    '2' => array('data'=> 'Group'),
                    '3' => array('data'=> 'Survey'),
                    '4' => array('data'=> 'Start Time'),
                    '5' => array('data'=> 'End Time'),
                    '6' => array('data'=> 'Status'),
                    '7' => array('data'=> 'Completed Time'),
                    '8' => array('data'=> 'Approved Status', 'class' => 'center'),
                    '9' => array('data'=> 'Bulk Status Change 
                                                    <br /> 
                                       <input type="checkbox" id="aprall" name="aprall" class="aprall">', 'class' => 'center')
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

                    if(!isSystemAuditor())
                    {
                        if($records[$i]->is_approved==0){
                        $status_approved = '<a href="'. base_url('administrator/assign_survey_status/survey_user_approval/1:'. $records[$i]->user_survey_id) .'" title="click here to Approve"><span class="label label-success">Approve</span></a>&nbsp;&nbsp;<a href="'. base_url('administrator/assign_survey_status/survey_user_approval/2:'. $records[$i]->user_survey_id) .'" title="click here to Reject"><span class="label label-important">Reject</span></a>';
                        }
                        elseif($records[$i]->is_approved==1){
                        $status_approved = '<span class="label label-success">Approve</span>';
                        }elseif($records[$i]->is_approved==2){
                        $status_approved = '<span class="label label-important">Reject</span>';
                        }
                    }
                    else
                    {
                        if($records[$i]->is_approved==0){
                        $status_approved = '<span class="label label-default">Pending</span>';
                        }
                        elseif($records[$i]->is_approved==1){
                        $status_approved = '<span class="label label-success">Approve</span>';
                        }elseif($records[$i]->is_approved==2){
                        $status_approved = '<span class="label label-important">Reject</span>';
                        }
                    }

                    $selectact = '';
                    $selectact .= '<input type="checkbox" name="approve_ques[]"   class="form-control input-sm"  data-name="'.$records[$i]->is_approved.'" id="approval_cam" class="approve_ques" value="'.$records[$i]->user_survey_id.'">';

                    $tbl_row = array(
                        '0' => array('data'=> $user_name),
                        '1' => array('data'=> $user_login),
                        '2' => array('data'=> $group_name),
                        '3' => array('data'=> $records[$i]->survey_name),
                        '4' => array('data'=> $start_date),
                        '5' => array('data'=> $end_date),
                        '6' => array('data'=> $status),
                        '7' => array('data'=> $completed_date),
                        '8' => array('data'=> $status_approved, 'class' => 'center', 'width' => '130px'),
                        '9' => array('data'=> $selectact, 'class' => 'center', 'width' => '130px')
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

    public function survey_assign_list_filter()
    {
        $filter_login = $this->input->post('filter_login');
        $filter_group = (int)$this->input->post('filter_group');
        $filter_status = $this->input->post('filter_status');
       // $filter_approval_status = $this->input->post('filter_approval_status');

        $this->session->keep_flashdata('survey_id');
        $this->session->keep_flashdata('start_date');
        $this->session->keep_flashdata('end_date');

        if ($filter_login != '') {
            $this->session->set_flashdata('assign_filter_login', $filter_login);
        }
        if ($filter_group != '') {
            $this->session->set_flashdata('assign_filter_group', $filter_group);
        }
        if ($filter_status == 'open' || $filter_status == 'inactive' || $filter_status == 'complete' || $filter_status == 'incomplete') {
            $this->session->set_flashdata('assign_filter_status', $filter_status);
        }

        $filter_approval_status = $this->input->post('filter_approval_status');
       // echo $filter_approval_status; die();
        if($filter_approval_status!="")
        {
            if ($filter_approval_status=='Pending') {
                    $this->session->set_flashdata('filter_approval_status', 'Pending');
            }
            elseif(!empty($filter_approval_status))
            {
                     $this->session->set_flashdata('filter_approval_status', $filter_approval_status);
                
            }
        }
        



        redirect('administrator/assign_survey_status/survey_assign_list');
    }

    public function survey_user_approval($value='')
    {
        $getVal = explode(":", $value);
/*        // if($getVal[0]==){
        //     $status = "Approved";
        // }
        print_r($getVal); die();*/
        if(count($getVal)>0)
        {
            $record_result = $this->survey_model->get_user_survey_approval($getVal);
            $this->session->set_flashdata('message_success', 'Survey approval status updated successfully.' );
        }
        else
        {
            $this->session->set_flashdata('message_error', 'Please select a survey.' );
        }

        redirect('administrator/assign_survey_status/survey_assign_list');

    }

    public function survey_user_approval_bulk()
    {

        
         $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Change Bulk Survey Assign Approval Status'));


        $value = $this->input->post('approve_ques');
        $approve_btn = $this->input->post('approve_all');
        $reject_btn = $this->input->post('reject_all');

        //var_dump($value);
        //var_dump($reject_btn);die;
     

        if($reject_btn)
        {

            //var_dump('expression');die;
            $update_data = array();
        foreach ($value as $key => $value) {
                $update_data[$key]['id'] = $value;
                $update_data[$key]['is_approved'] = 2;
            } 

             if($value)
             {
                
                if($this->update_global_model->update_batch('exm_surveys_users','id',$update_data)){
                    $this->session->set_flashdata('message_success', 'Survey Assign Approval Status update successful');
                }else{
                    $this->session->set_flashdata('message_error', 'No data here.');
                }
             }
             else
             {
                 $this->session->set_flashdata('message_error', 'No data selected.');
             }

        }
        elseif($approve_btn)
        {


            $update_data = array();
            foreach ($value as $key => $val) {
                        $update_data[$key]['id'] = $val;
                        $update_data[$key]['is_approved'] = 1;
            }

            //print_r_pre($update_data);die;   
        

             if($value)
             {
                //echo 1; die();
                
                if($this->update_global_model->update_batch('exm_surveys_users','id',$update_data)){
                    $this->session->set_flashdata('message_success', 'Survey Assign Approval Status update successful');
                }else{
                    $this->session->set_flashdata('message_error', 'No data here.');
                }
             }
             else
             {
                //echo 2; die();
                 $this->session->set_flashdata('message_error', 'No data selected.');
             }

        }
        else
        {
            $this->session->set_flashdata('message_error', 'No such action is defined.');
        }


        redirect('administrator/assign_survey_status/survey_assign_list');

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

        $filter_approval_status = $this->input->post('filter_approval_status');
        if (!empty($filter_approval_status)) {
            $this->session->set_flashdata('filter_approval_status', $filter_approval_status);
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
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update User Training Status'));
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
                $this->excel->getActiveSheet()->setCellValue('C1', 'User Login');
                $this->excel->getActiveSheet()->setCellValue('D1', 'Group');
                $this->excel->getActiveSheet()->setCellValue('E1', 'Survey');
                $this->excel->getActiveSheet()->setCellValue('F1', 'Start Time');
                $this->excel->getActiveSheet()->setCellValue('G1', 'End Time');
                $this->excel->getActiveSheet()->setCellValue('H1', 'Status');
                $this->excel->getActiveSheet()->setCellValue('I1', 'Completed Time');
                $this->excel->getActiveSheet()->setCellValue('J1', 'Approval Status');

                $this->excel->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);

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

                // fill data
                for ($i=0; $i<count($records); $i++) {
                
                    $serial = $i+1;
                    
                    $user_survey_id = $records[$i]->user_survey_id;

                    $user_name = $records[$i]->user_first_name .' '. $records[$i]->user_last_name;
                    $user_login = $records[$i]->user_login;
                    $group_name = $records[$i]->group_name;
                    $survey_name = $records[$i]->survey_name;
                    $start_date = date('d/m/Y, g:ia', strtotime($records[$i]->start_date));
                    $end_date = date('d/m/Y, g:ia', strtotime($records[$i]->end_date));
                    $status = ucfirst($records[$i]->status);
					if($records[$i]->completed){
						$completed_date = date('d/m/Y, g:ia', strtotime($records[$i]->completed));
					}else{
						$completed_date = '';
					}


                    if($records[$i]->is_approved==0){
                            $status_approved = 'Pending';
                    }
                    elseif($records[$i]->is_approved==1){
                            $status_approved = 'Approve';
                    }elseif($records[$i]->is_approved==2){
                            $status_approved = 'Reject';
                    }
                    
                    
                    $row = $i + 2;

                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $user_name);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $user_login);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $group_name);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $survey_name);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $start_date);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $end_date);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $status);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $completed_date);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $status_approved);
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
        $this->form_data->filter_approval_status = '';
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