<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class User extends MY_Controller
{
    var $current_page = "user";
    var $user_type_list = array();
    var $user_team_list = array();

    var $logged_in_user;

    var $type_list_filter = array();
    var $team_list_filter = array();
    var $active_list_filter = array();
    
    var $admin_group_list = array();
    var $tbl_exam_users    = "exm_users";
    var $tbl_exm_user_activity = "exm_user_activity";
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
        $this->form_data = new StdClass;
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->helper('number');
        $this->load->helper('email');
        $this->load->library('excel');
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('upload');
        $this->load->library('form_validation');
        $this->load->model('user_team_model');
        $this->load->model('admin_group_model');
        $this->load->model('global/select_global_model');
        $this->load->model('global/insert_global_model');
        $this->load->model('user_model');
        $this->load->model('smsnemail_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');
        
        
        // pre-fill user type drop-down
        $this->user_type_list['User'] = 'User';
        $this->user_type_list['Administrator'] = 'Administrator';
        $this->user_type_list['Recruitment Manager'] = 'Recruitment Manager';
        $this->user_type_list['Head of HR'] = 'Head of HR';
        $this->user_type_list['Subject Matter Experts'] = 'Subject Matter Experts';
        $this->user_type_list['Recruitment Assistant - Question'] = 'Recruitment Assistant - Question';
        $this->user_type_list['System Auditor'] = 'System Auditor';
        $this->user_type_list['Recruitment Assistant – Result'] = 'Recruitment Assistant – Result';
        $this->user_type_list['Examiner'] = 'Examiner';
        if ($this->logged_in_user->user_type == 'Super Administrator') {
            $this->user_type_list['Super Administrator'] = 'Super Administrator';
        }

        $this->type_list_filter[''] = 'Any type';
        $this->type_list_filter['User'] = 'User';
        $this->type_list_filter['Administrator'] = 'Administrator';
        $this->type_list_filter['Super Administrator'] = 'Super Administrator';
        //$this->type_list_filter['Recruitment Manager'] = 'Recruitment Manager';
        //$this->type_list_filter['Recruitment Manager'] = 'Recruitment Manager';
        //$this->type_list_filter['Recruitment Manager'] = 'Recruitment Manager';
        //$this->type_list_filter['Recruitment Manager'] = 'Recruitment Manager';

        // pre-fill user team drop-down
        $user_teams = $this->user_team_model->get_user_teams();
        $this->user_team_list[] = 'Select an User Team';
        $this->team_list_filter[] = 'All teams';

        if ($user_teams) {
            for ($i=0; $i<count($user_teams); $i++) {
                $this->user_team_list[$user_teams[$i]->id] = $user_teams[$i]->team_name;
                $this->team_list_filter[$user_teams[$i]->id] = $user_teams[$i]->team_name;
            }
        }
        
        // pre-fill admin group drop-down
        $admin_group = $this->admin_group_model->get_admin_groups();
        $this->admin_group_list[] = 'Select an Admin Group';

        if ($admin_group) {
            for ($i=0; $i<count($admin_group); $i++) {
                $this->admin_group_list[$admin_group[$i]->id] = $admin_group[$i]->group_name;
            }
        }

        // pre-fill is_user_active drop-down
        $this->active_list_filter[''] = 'Any';
        $this->active_list_filter['active'] = 'Active';
        $this->active_list_filter['inactive'] = 'Inactive';
        $this->active_list_filter['locked'] = 'Locked';

        
        // check if logged in
        if ( ! $this->session->userdata('logged_in_user')) {
            $redirect_url = preg_replace('/(delete|update.*|(add).*)\/?[0-9]*$/', '$2', uri_string());
            $this->session->set_flashdata('redirect_url', $redirect_url);
            redirect('login');
        } else {
            $this->logged_in_user = $this->session->userdata('logged_in_user');
            if ($this->logged_in_user->user_type == 'User' && !$this->session->userdata('user_privilage_name')) {
                redirect('home');
            }
        }
    }

    /**
     * Display paginated list of user
     * @return void
     */
    public function index()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage User View'));
        // set page specific variables
        $page_info['title'] = 'Manage Users'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_loginoremail')) {
            $this->session->keep_flashdata('filter_loginoremail');
            $filter_loginoremail = $this->session->flashdata('filter_loginoremail');
            $this->form_data->filter_loginoremail = $filter_loginoremail;
            $filter['filter_loginoremail']['field'] = 'login_email';
            $filter['filter_loginoremail']['value'] = $filter_loginoremail;
        }
        if ($this->session->flashdata('filter_team')) {
            $this->session->keep_flashdata('filter_team');
            $filter_team = (int)$this->session->flashdata('filter_team');
            $this->form_data->filter_team = $filter_team;
            $filter['filter_team']['field'] = 'user_team_id';
            $filter['filter_team']['value'] = $filter_team;
        }
        if ($this->session->flashdata('filter_type')) {
            $this->session->keep_flashdata('filter_type');
            $filter_type = $this->session->flashdata('filter_type');
            $this->form_data->filter_type = $filter_type;
            $filter['filter_type']['field'] = 'user_type';
            $filter['filter_type']['value'] = $filter_type;
        }
        if ($this->session->flashdata('filter_active')) {
            $filter_field = 'user_is_active';
            $filter_active = $this->session->flashdata('filter_active');
            if ($filter_active == 'active') {
                $filter_active = '1';
            } elseif ($filter_active == 'inactive') {
                $filter_active = '0';
            } elseif ($filter_active == 'locked') {
                $filter_active = '1';
                $filter_field = 'user_is_lock';
            } else {
                $filter_active = '';
            }

            if ($filter_active != '') {
                $this->session->keep_flashdata('filter_active');
                $this->form_data->filter_active = $this->session->flashdata('filter_active');
                $filter['filter_active']['field'] = $filter_field;
                $filter['filter_active']['value'] = $filter_active;
            }
        }
        $page_info['filter'] = $filter;


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->user_model->get_paged_users($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];

        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/user";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);


        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Login Id'),
                '1' => array('data'=> 'User Name'),
                '2' => array('data'=> 'Email'),
                '3' => array('data'=> 'User Type', 'class' => 'center', 'width' => '150'),
                '4' => array('data'=> 'User Team'),
                '5' => array('data'=> 'Status', 'class' => 'center', 'width' => '120'),
                '6' => array('data'=> 'Action', 'class' => 'center', 'width' => '200')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {

                $user_name = trim($records[$i]->user_first_name .' '. $records[$i]->user_last_name);

                /*$is_active_str = '';
                if ($records[$i]->user_is_active == 1) {
                    $is_active_str = '<span class="label label-success">Active</span>&nbsp;&nbsp;';
                } else {
                    $is_active_str = '<span class="label label-important">Inactive</span>&nbsp;&nbsp;';
                }*/
				
				$is_active_str = '';
                if ($records[$i]->user_type != 'Super Administrator' || $this->logged_in_user->user_type != 'Administrator') {
					if ($records[$i]->user_is_active == 1) {
						$is_active_str .= anchor('administrator/user/inactive/'. $records[$i]->id, '<button class="btn btn-success">Active</button>', array('title'=>'Inactive', 'onclick'=>'return confirm(\'Do you really want to inactive this record?\')'));
					}
					if ($records[$i]->user_is_active == 0) {
						$is_active_str .= anchor('administrator/user/active/'. $records[$i]->id, '<button class="btn btn-danger">Inactive</button>', array('title'=>'Active', 'onclick'=>'return confirm(\'Do you really want to active this record?\')'));
					}
                }

                $is_locked_str = '';
                if ($records[$i]->user_is_lock == 1) {
                    $is_locked_str = '<span class="label label-important">Locked</span>';
                }

                $action_str = '';
                if ($records[$i]->user_type != 'Super Administrator' || $this->logged_in_user->user_type != 'Administrator') {
                    $action_str .= anchor('administrator/user/download_user_profile/'. $records[$i]->id, '<button class="btn btn-success">Export</button>', 'title="Edit"');
                    $action_str .= '&nbsp;&nbsp;&nbsp;';
                    if(!isSystemAuditor())
                    $action_str .= anchor('administrator/user/edit/'. $records[$i]->id, '<button class="btn btn-info"><i class="icon-edit"></i></button>', 'title="Edit"');
                    $action_str .= '&nbsp;&nbsp;&nbsp;';
                    if(!isSystemAuditor())
                    $action_str .= anchor('administrator/user/delete/'. $records[$i]->id, '<button class="btn btn-danger"><i class="icon-trash"></i></button>', array('title'=>'Delete', 'onclick'=>'return confirm(\'Do you really want to delete this record?\')'));
                }

                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->user_login),
                    '1' => array('data'=> $user_name),
                    '2' => array('data'=> $records[$i]->user_email),
                    '3' => array('data'=> ucfirst($records[$i]->user_type), 'class' => 'center', 'width' => '150'),
                    '4' => array('data'=> $records[$i]->team_name .''),
                    '5' => array('data'=> $is_active_str . $is_locked_str, 'class' => 'center', 'width' => '120'),
                    '6' => array('data'=> $action_str, 'class' => 'center', 'width' => '150')
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


    public function download_user(){

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Export User List'));

        $record_result = $this->user_model->get_all_users();

        $records = $record_result['result'];

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=User List -".date('Y-m-d').".csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        $file = fopen('php://output', 'w');

        fputcsv($file, array(
            'Login Id',
            'User Name',
            'User Type',
            'Email',
            'Status'

        ));


        $user_info = array();
        foreach ($records as $key => $value) {
            $user_info[$key]['user_login']=$records[$key]['user_login'];
            $user_info[$key]['user_name']=$records[$key]['user_first_name'].' '.$records[$key]['user_last_name'];
            $user_info[$key]['user_email']=$records[$key]['user_email'];
            $user_info[$key]['user_type']=$records[$key]['user_type'];

            if($records[$key]['user_is_active']==1)
            $user_info[$key]['user_is_active']='Active';
            else
                $user_info[$key]['user_is_active']='Inactive';

        }

        foreach ($user_info as $value) {
            $rowD= $value;
            fputcsv($file, $rowD);
        }
        exit();



    }


    public function download_user_profile()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Export User Profile'));

        $user_id = (int)$this->uri->segment(4);
        $user = $this->user_model->get_user($user_id);

        //var_dump($user);die;

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=User Profile -".date('Y-m-d').".csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        $file = fopen('php://output', 'w');

        fputcsv($file, array(
            'Login Id',
            'User Name',
            'Email',
            'User Type',
            'Competency Level',
            'Is Active',
            'Is Locked',


        ));

        $user_info = array();
        foreach ($user as $key => $value) {
            $user_info[$key]['user_login']=$user->user_login;
            $user_info[$key]['user_name']=$user->user_first_name.' '.$user->user_last_name;
            $user_info[$key]['user_email']=$user->user_email;
            $user_info[$key]['user_type']=$user->user_type;
            $user_info[$key]['user_competency']=$user->user_competency;

            if($user->user_is_active==1)
                $user_info[$key]['user_is_active']='Active';
            else
                $user_info[$key]['user_is_active']='Inactive';



            if($user->user_is_lock==1)
                $user_info[$key]['user_is_lock']='Yes';
            else
                $user_info[$key]['user_is_lock']='No';

        }

        foreach ($user_info as $value) {
            $rowD= $value;
            fputcsv($file, $rowD);
        }
        exit();



    }

    public function filter()
    {
        $filter_loginoremail = $this->input->post('filter_loginoremail');
        $filter_type = $this->input->post('filter_type');
        $filter_team = (int)$this->input->post('filter_team');
        $filter_active = $this->input->post('filter_active');
        $filter_clear = $this->input->post('filter_clear');

        if ($filter_clear == '') {
            if ($filter_loginoremail != '') {
                $this->session->set_flashdata('filter_loginoremail', $filter_loginoremail);
            }
            if ($filter_team > 0) {
                $this->session->set_flashdata('filter_team', $filter_team);
            }
            if ($filter_type != '') {
                $this->session->set_flashdata('filter_type', $filter_type);
            }
            if ($filter_active == 'active' || $filter_active == 'inactive' || $filter_active == 'locked') {
                $this->session->set_flashdata('filter_active', $filter_active);
            }
        } else {
            $this->session->unset_userdata('filter_loginoremail');
            $this->session->unset_userdata('filter_team');
            $this->session->unset_userdata('filter_type');
            $this->session->unset_userdata('filter_active');
        }

        redirect('administrator/user');
    }

    /**
     * Display add user form
     * @return void
     */
    public function add()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New User View'));
        // set page specific variables
        $page_info['title'] = 'Add New User'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();

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

    public function add_user()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New User'));
        $page_info['title'] = 'Add New User'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $user_team_id = $this->input->post('user_team_id');
            $user_login = $this->input->post('user_login');
            $user_password = $this->input->post('user_password');
            $user_confirm_password = $this->input->post('user_confirm_password');
            $user_first_name = $this->input->post('user_first_name');
            $user_last_name = $this->input->post('user_last_name');
            $user_phone = $this->input->post('user_phone');
            $nid_passport_no = $this->input->post('nid_passport_no');
            $user_email = $this->input->post('user_email');
            $user_type = $this->input->post('user_type');
            $user_competency = $this->input->post('user_competency');
            $user_is_active = (int)$this->input->post('user_is_active');
            $user_is_lock = (int)$this->input->post('user_is_lock');
            $user_is_admit_card = (int)$this->input->post('user_is_admit_card');
            $department = $this->input->post('department');
            $designation = $this->input->post('designation');
            
            $admin_group_id = (int)$this->input->post('admin_group_id');
            $is_password_reset = (int)$this->input->post('is_password_reset');

            $file_name ="";
            $file_sig ="";
            if (isset($_FILES['profile_image']['name']) && !empty($_FILES['profile_image']['name'])) {
                $config['upload_path'] = './uploads/user/';
                $config['allowed_types'] = 'jpg|jpeg|png|gif';
                //$config['file_name'] = $_FILES["profile_image"]['name'];
                $file_name = $_FILES["profile_image"]['name'];
                $this->upload->initialize($config);
                $upload = $this->upload->do_upload('profile_image');

                if(!$upload){
                    $this->session->set_flashdata('message_error', $this->upload->display_errors());
                    redirect(base_url('administrator/user/add/'));
                }
            }
            //$config['encrypt_name'] = true;
            if (isset($_FILES['signature_image']['name']) && !empty($_FILES['signature_image']['name'])) {
                $config_sig['upload_path'] = './uploads/signature/';
                $config_sig['allowed_types'] = 'jpg|jpeg|png|gif';

                $file_sig = $_FILES["signature_image"]['name'];
                $this->upload->initialize($config_sig);
                $upload_sig = $this->upload->do_upload('signature_image');

                if(!$upload_sig){
                    $this->session->set_flashdata('message_error', $this->upload->display_errors());
                    redirect(base_url('administrator/user/add/'));
                }
            }
            //$CI =& get_instance();
            //$CI->load->library('upload', $config);
            //var_dump($uploadpath);die;
            //$upload=$CI->upload->do_upload("profile_image");

            
            

            $check_data = $this->select_global_model->is_data_exists($this->tbl_exam_users, 
                array('user_login'=>$user_login));

            if($check_data){
                $this->session->set_flashdata('message_error', 'This User already exists!');
                redirect('administrator/admingroup/add/');
            }

            if ($user_competency == 1) { $user_competency = 'Front Office'; }
            elseif ($user_competency == 0) { $user_competency = 'Back Office'; }
            else { $user_competency = 'Front Office'; }

            $data = array(
                'user_team_id' => $user_team_id,
                'user_login' => $user_login,
                'user_password' => $user_password,
                'user_confirm_password' => $user_confirm_password,
                'is_password_reset' => $is_password_reset,
                'user_first_name' => $user_first_name,
                'user_last_name' => $user_last_name,
                'user_email' => $user_email,
                'user_type' => $user_type,
                'phone' => $user_phone,
                'nid_passport_no' => $nid_passport_no,
                'user_competency' => $user_competency,
                'user_is_active' => $user_is_active,
                'user_is_lock' => $user_is_lock,
                'user_is_admit_card' => $user_is_admit_card,
                'department' => $department,
                'designation' => $designation,
                'admin_group' => $admin_group_id,
                'profile_image' => $file_name,
                'signature_image'=> $file_sig
            );
             
            $res = (int)$this->user_model->add_user($data);
            //print_r($res); die();
            //print_r($res); die();
            if ($res > 0) {
                $mail_array = array();
                $mail_layout = $this->smsnemail_model->get_mail_layout();
                $prefix = layoutPrefixUser('1');
                $values = array($user_first_name.' '.$user_last_name,$user_login,$user_password);

                if($user_email) {
                    $final_mail = str_replace($prefix, $values, $mail_layout->mail_body);
                    $mail_array[0]['emailornumber'] = $user_email;
                    $mail_array[0]['message'] = $final_mail;
                    $mail_array[0]['type'] = 'email';
                    $this->insert_global_model->globalinsertbatch('exm_smsoremail_job',$mail_array);
                }

                $this->session->set_flashdata('message_success', 'Add is successful.');
                redirect('administrator/user/add');
            } else {
                $page_info['message_error'] = $this->user_model->error_message .' Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }

    public function bulk()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add Bulk User View'));
        // set page specific variables
        $page_info['title'] = 'Add Bulk Users'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_bulk_form_view';
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
    
    
    public function edit_bulk()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Bulk User View'));
        // set page specific variables
        $page_info['title'] = 'Edit Bulk Users'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_edit_bulk_form_view';
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
    
    
    public function delete_bulk()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Delete Bulk User View'));
        // set page specific variables
        $page_info['title'] = 'Delete Bulk Users'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_delete_bulk_form_view';
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
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Upload Bulk User'));
        $users = array();
        $invalid_users = array();
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

                if ($max_column_number < 1) {
                    $this->session->set_flashdata('message_error', 'File format does not match.');
                    redirect('administrator/user/bulk');
                }

                // remove first row (if $has_column_header == 1)
                // remove empty rows
                $start = 1;
                if ($has_column_header) {
                    $start = 2;
                }

                for ($i=$start; $i<=count($sheetData); $i++) {

                    $login = trim($sheetData[$i]['A']);
                    $password = trim($sheetData[$i]['B']);
                    $team_name = trim($sheetData[$i]['C']);
                    $first_name = trim($sheetData[$i]['D']);
                    $last_name = trim($sheetData[$i]['E']);
                    $email = trim($sheetData[$i]['F']);
                    $type = 'User';
                    $competency = trim($sheetData[$i]['G']);
                    $is_active = trim($sheetData[$i]['H']);

                    if ($login == '' && $password == '' && $team_name == '' && $first_name == '' && $last_name == '' && $email == '' && $type == '' && $competency == '' && $is_active == '') {
                        continue;
                    } else {
                        $users[$i]['user_team_id'] = $team_name;
                        $users[$i]['user_login'] = $login;
                        $users[$i]['user_password'] = $password;
                        $users[$i]['user_first_name'] = $first_name;
                        $users[$i]['user_last_name'] = $last_name;
                        $users[$i]['user_email'] = $email;
                        $users[$i]['user_type'] = $type;
                        $users[$i]['user_competency'] = $competency;
                        $users[$i]['user_is_active'] = $is_active;
                    }
                }

                // check for valid data
                if (count($users) > 0) {
                    foreach($users as $row => $user) {

                        $row_has_error = false;

                        $user_team_id = $user['user_team_id'];
                        $user_login = $user['user_login'];
                        $user_password = $user['user_password'];
                        $user_first_name = $user['user_first_name'];
                        $user_last_name = $user['user_last_name'];
                        $user_email = $user['user_email'];
                        $user_type = $user['user_type'];
                        $user_competency = $user['user_competency'];
                        $user_is_active = $user['user_is_active'];

                        $user_team_id = $this->user_team_model->get_user_team_by_name($user_team_id);
                        if ($user_team_id) {
                            $user_team_id = $user_team_id->id;
                        } /*else {
                            $user_team_id = 0;
                        }*/
                        elseif ($user_team_id == '') {
                            $error_messages[$row][] = 'Team name can not be empty';
                            $row_has_error = true;
                        }

                        if ($user_login == '') {
                            $error_messages[$row][] = 'Login ID can not be empty';
                            $row_has_error = true;
                        } elseif ($this->user_model->get_user_by_login($user_login)) {
                            $error_messages[$row][] = 'Login ID is already exists';
                            $row_has_error = true;
                        }

                        if ($user_password == '') {
                            $error_messages[$row][] = 'Password can not be empty';
                            $row_has_error = true;
                        }


                        if ($user_email != '') {
                            if ( ! valid_email($user_email)) {
                                $error_messages[$row][] = 'Invalid email address';
                                $row_has_error = true;
                            } elseif ($this->user_model->get_user_by_email($user_email)) {
                                $error_messages[$row][] = 'Email Address is already exists';
                                $row_has_error = true;
                            }
                        }

                        $user_type = strtolower($user_type);
                        if ($user_type == 'super administrator') {
                            $user_type = 'Super Administrator';
                        } elseif ($user_type == 'administrator') {
                            $user_type = 'Administrator';
                        } elseif ($user_type == 'user') {
                            $user_type = 'User';
                        } else {
                            $user_type = '';
                            $error_messages[$row][] = "Invalid type. Must be 'Super Administrator' or 'Administrator' or 'User'";
                            $row_has_error = true;
                        }

                        $user_competency = strtolower($user_competency);
                        if ($user_competency == 'front office') {
                            $user_competency = 'Front Office';
                        } elseif ($user_competency == 'back office') {
                            $user_competency = 'Back Office';
                        } /*else {
                            $user_competency = '';
                        }*/
                        elseif ($user_competency == '') {
                            $error_messages[$row][] = "Invalid type. Must be 'Front Office' or 'Back Office'";
                            $row_has_error = true;
                        }

			if ($user_is_active == '') {
                            $error_messages[$row][] = "IsActive can not be empty.";
                            $row_has_error = true;
                        }elseif ($user_is_active != 0 && $user_is_active != 1) {
                            $error_messages[$row][] = "IsActive must be 0 or 1.";
                            $row_has_error = true;
                        }

                        if ($row_has_error) {
                            $invalid_users[$row] = $user;
                            unset($users[$row]);
                        } else {
                            $users[$row]['user_team_id'] = $user_team_id;
                            $users[$row]['user_login'] = $user_login;
                            $users[$row]['user_password'] = $user_password;
                            $users[$row]['user_password_old'] = $user_password;
                            $users[$row]['user_first_name'] = $user_first_name;
                            $users[$row]['user_last_name'] = $user_last_name;
                            $users[$row]['user_email'] = $user_email;
                            $users[$row]['user_type'] = $user_type;
                            $users[$row]['user_competency'] = $user_competency;
                            $users[$row]['user_is_active'] = $user_is_active;
                        }
                    }
                }

                if (count($users) <= 0 && count($invalid_users) <= 0) {
                    $this->session->set_flashdata('message_error', 'File does not contain any row.');
                    redirect('administrator/user/bulk');
                }

                $this->session->set_flashdata('bulk_users', $users);
                $this->session->set_flashdata('bulk_invalid_users', $invalid_users);
                $this->session->set_flashdata('bulk_error_messages', $error_messages);
                
            } else {
                $this->session->set_flashdata('message_error', $file_error);
                redirect('administrator/user/bulk');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Please upload an Excel file.');
            redirect('administrator/user/bulk');
        }

        $this->session->set_flashdata('bulk_action', 1);
        redirect('administrator/user/bulk_upload_action');
    }
   
    
    public function edit_bulk_upload()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Bulk User'));
        $users = array();
        $invalid_users = array();
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

                if ($max_column_number < 1) {
                    $this->session->set_flashdata('message_error', 'File format does not match.');
                    redirect('administrator/user/edit_bulk');
                }

                // remove first row (if $has_column_header == 1)
                // remove empty rows
                $start = 1;
                if ($has_column_header) {
                    $start = 2;
                }

                for ($i=$start; $i<=count($sheetData); $i++) {

                    $login = trim($sheetData[$i]['A']);
                    $password = trim($sheetData[$i]['B']);
                    $team_name = trim($sheetData[$i]['C']);
                    $first_name = trim($sheetData[$i]['D']);
                    $last_name = trim($sheetData[$i]['E']);
                    $email = trim($sheetData[$i]['F']);
                    $type = 'User';
                    $competency = trim($sheetData[$i]['G']);
                    $is_active = trim($sheetData[$i]['H']);

                    if ($login == '' && $password == '' && $team_name == '' && $first_name == '' && $last_name == '' && $email == '' && $type == '' && $competency == '' && $is_active == '') {
                        continue;
                    } else {
                        $users[$i]['user_team_id'] = $team_name;
                        $users[$i]['user_login'] = $login;
                        $users[$i]['user_password'] = $password;
                        $users[$i]['user_first_name'] = $first_name;
                        $users[$i]['user_last_name'] = $last_name;
                        $users[$i]['user_email'] = $email;
                        $users[$i]['user_type'] = $type;
                        $users[$i]['user_competency'] = $competency;
                        $users[$i]['user_is_active'] = $is_active;
                    }
                }

                // check for valid data
                if (count($users) > 0) {
                    foreach($users as $row => $user) {

                        $row_has_error = false;
									
                        $user_team_id = $user['user_team_id'];
                        $user_login = $user['user_login'];
                        $user_password = $user['user_password'];
                        $user_first_name = $user['user_first_name'];
                        $user_last_name = $user['user_last_name'];
                        $user_email = $user['user_email'];
                        $user_type = $user['user_type'];
                        $user_competency = $user['user_competency'];
                        $user_is_active = $user['user_is_active'];

                        $user_team_id = $this->user_team_model->get_user_team_by_name($user_team_id);
                        if ($user_team_id) {
                            $user_team_id = $user_team_id->id;
                        } 
                        elseif ($user_team_id == '') {
                            $error_messages[$row][] = 'Team name can not be empty';
                            $row_has_error = true;
                        }

                        if ($user_login == '') {
                            $error_messages[$row][] = 'Login ID can not be empty';
                            $row_has_error = true;
                        } elseif (!$this->user_model->get_user_by_login($user_login)) {
                            $error_messages[$row][] = 'Login ID is not exist';
                            $row_has_error = true;
                        }

                        if ($user_password == '') {
                            $error_messages[$row][] = 'Password can not be empty';
                            $row_has_error = true;
                        }
                        
                        if ($user_email != '') {
                            if ( ! valid_email($user_email)) {
                                $error_messages[$row][] = 'Invalid email address';
                                $row_has_error = true;
                            } /*elseif ($this->user_model->get_user_by_email($user_email)) {
                                $error_messages[$row][] = 'Email Address is already exists';
                                $row_has_error = true;
                            }*/
                        }

                        $user_type = strtolower($user_type);
                        if ($user_type == 'super administrator') {
                            $user_type = 'Super Administrator';
                        } elseif ($user_type == 'administrator') {
                            $user_type = 'Administrator';
                        } elseif ($user_type == 'user') {
                            $user_type = 'User';
                        } else {
                            $user_type = '';
                            $error_messages[$row][] = "Invalid type. Must be 'Super Administrator' or 'Administrator' or 'User'";
                            $row_has_error = true;
                        }

                        $user_competency = strtolower($user_competency);
                        if ($user_competency == 'front office') {
                            $user_competency = 'Front Office';
                        } elseif ($user_competency == 'back office') {
                            $user_competency = 'Back Office';
                        }
                        elseif ($user_competency == '') {
                            $error_messages[$row][] = "Invalid type. Must be 'Front Office' or 'Back Office'";
                            $row_has_error = true;
                        }
												
			if ($user_is_active == '') {
                            $error_messages[$row][] = "IsActive can not be empty.";
                            $row_has_error = true;
                        }elseif ($user_is_active != 0 && $user_is_active != 1) {
                            $error_messages[$row][] = "IsActive must be 0 or 1.";
                            $row_has_error = true;
                        }

                        if ($row_has_error) {
                            $invalid_users[$row] = $user;
                            unset($users[$row]);
                        } else {
                            $users[$row]['user_team_id'] = $user_team_id;
                            $users[$row]['user_login'] = $user_login;
                            $users[$row]['user_password'] = $user_password;
                            $users[$row]['user_password_old'] = $user_password;
                            $users[$row]['user_first_name'] = $user_first_name;
                            $users[$row]['user_last_name'] = $user_last_name;
                            $users[$row]['user_email'] = $user_email;
                            $users[$row]['user_type'] = $user_type;
                            $users[$row]['user_competency'] = $user_competency;
                            $users[$row]['user_is_active'] = $user_is_active;
                        }
                    }
                }

                if (count($users) <= 0 && count($invalid_users) <= 0) {
                    $this->session->set_flashdata('message_error', 'File does not contain any row.');
                    redirect('administrator/user/edit_bulk');
                }

                $this->session->set_flashdata('bulk_users', $users);
                $this->session->set_flashdata('bulk_invalid_users', $invalid_users);
                $this->session->set_flashdata('bulk_error_messages', $error_messages);
                
            } else {
                $this->session->set_flashdata('message_error', $file_error);
                redirect('administrator/user/edit_bulk');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Please upload an Excel file.');
            redirect('administrator/user/edit_bulk');
        }

        $this->session->set_flashdata('bulk_action', 1);
        redirect('administrator/user/edit_bulk_upload_action');
    }

    //delete bulk upload
    public function delete_bulk_upload()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Delete Bulk User'));
        $users = array();
        $invalid_users = array();
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

                if ($max_column_number < 1) {
                    $this->session->set_flashdata('message_error', 'File format does not match.');
                    redirect('administrator/user/delete_bulk');
                }

                // remove first row (if $has_column_header == 1)
                // remove empty rows
                $start = 1;
                if ($has_column_header) {
                    $start = 2;
                }

                for ($i=$start; $i<=count($sheetData); $i++) {
                    $login = trim($sheetData[$i]['A']);

                    if ($login == '') {
                        continue;
                    } else {
                        $users[$i]['user_login'] = $login;
                    }
                }

                // check for valid data
                if (count($users) > 0) {
                    foreach($users as $row => $user) {

                        $row_has_error = false;

                        $user_login = $user['user_login'];

                        $user_login = $this->user_model->get_user_by_login($user_login);
                       
                        if ($user_login == '') {
                            $error_messages[$row][] = 'Login ID can not be match with existing user';
                            $row_has_error = true;
                        }
                        
                        if ($row_has_error) {
                            $invalid_users[$row] = $user;
                            unset($users[$row]);
                        } else {
                            $users[$row]['user_login'] = $user_login;
                        }
                    }
                }

                if (count($users) <= 0 && count($invalid_users) <= 0) {
                    $this->session->set_flashdata('message_error', 'File does not contain any row.');
                    redirect('administrator/user/delete_bulk');
                }

                $this->session->set_flashdata('bulk_users', $users);
                $this->session->set_flashdata('bulk_invalid_users', $invalid_users);
                $this->session->set_flashdata('bulk_error_messages', $error_messages);
                
            } else {
                $this->session->set_flashdata('message_error', $file_error);
                redirect('administrator/user/delete_bulk');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Please upload an Excel file.');
            redirect('administrator/user/delete_bulk');
        }

        $this->session->set_flashdata('bulk_delete_action', 1);
        redirect('administrator/user/delete_bulk_upload_action');
    }
      
    

    public function bulk_upload_action()
    {
        // set page specific variables
        $page_info['title'] = 'Take an Action'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_bulk_action_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $page_info['bulk_users'] = array();
        $page_info['bulk_invalid_users'] = array();
        $page_info['bulk_error_messages'] = array();


        if ($this->session->flashdata('bulk_action')) {
            $this->session->keep_flashdata('bulk_action');
        }
        if ( (int)$this->session->flashdata('bulk_action') == 0 ) {
            redirect('administrator/user/bulk');
        }
        

        if ($this->session->flashdata('bulk_users')) {
            $page_info['bulk_users'] = $this->session->flashdata('bulk_users');
            $this->session->keep_flashdata('bulk_users');
        }
        if ($this->session->flashdata('bulk_invalid_users')) {
            $page_info['bulk_invalid_users'] = $this->session->flashdata('bulk_invalid_users');
            $this->session->keep_flashdata('bulk_invalid_users');
        }
        if ($this->session->flashdata('bulk_error_messages')) {
            $page_info['bulk_error_messages'] = $this->session->flashdata('bulk_error_messages');
            $this->session->keep_flashdata('bulk_error_messages');
        }


        $bulk_invalid_users = $page_info['bulk_invalid_users'];
        $bulk_error_messages = $page_info['bulk_error_messages'];

        if ($bulk_invalid_users && count($bulk_invalid_users) < 250) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Login ID'),
                '1' => array('data'=> 'Team Name'),
                '2' => array('data'=> 'Email Address'),
                '3' => array('data'=> 'User Type'),
                '4' => array('data'=> 'Error')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            foreach($bulk_invalid_users as $row => $record) {

                $error_message = '';
                for ($i=0; $i<count($bulk_error_messages[$row]); $i++) {
                    if ($i>0) { $error_message .= '<br />'; }
                    $error_message .= $bulk_error_messages[$row][$i];
                }

                $tbl_row = array(
                    '0' => array('data'=> $record['user_login']),
                    '1' => array('data'=> $record['user_team_id']),
                    '2' => array('data'=> $record['user_email']),
                    '3' => array('data'=> $record['user_type']),
                    '4' => array('data'=> $error_message)
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['bulk_invalid_users_table'] = $this->table->generate();
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
        $bulk_users = array();

        if ($this->session->flashdata('bulk_users')) {
            $bulk_users = $this->session->flashdata('bulk_users');
        }

        // bulk insert
        $this->user_model->add_bulk_users($bulk_users);
        $this->session->set_flashdata('message_success', 'Record(s) inserted successfully.');

        redirect('administrator/user/bulk');
    }
    
    
    public function edit_bulk_upload_action()
    {
        // set page specific variables
        $page_info['title'] = 'Take an Action'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_edit_bulk_action_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $page_info['bulk_users'] = array();
        $page_info['bulk_invalid_users'] = array();
        $page_info['bulk_error_messages'] = array();


        if ($this->session->flashdata('bulk_action')) {
            $this->session->keep_flashdata('bulk_action');
        }
        if ( (int)$this->session->flashdata('bulk_action') == 0 ) {
            redirect('administrator/user/edit_bulk');
        }
        

        if ($this->session->flashdata('bulk_users')) {
            $page_info['bulk_users'] = $this->session->flashdata('bulk_users');
            $this->session->keep_flashdata('bulk_users');
        }
        if ($this->session->flashdata('bulk_invalid_users')) {
            $page_info['bulk_invalid_users'] = $this->session->flashdata('bulk_invalid_users');
            $this->session->keep_flashdata('bulk_invalid_users');
        }
        if ($this->session->flashdata('bulk_error_messages')) {
            $page_info['bulk_error_messages'] = $this->session->flashdata('bulk_error_messages');
            $this->session->keep_flashdata('bulk_error_messages');
        }


        $bulk_invalid_users = $page_info['bulk_invalid_users'];
        $bulk_error_messages = $page_info['bulk_error_messages'];

        if ($bulk_invalid_users && count($bulk_invalid_users) < 250) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Login ID'),
                '1' => array('data'=> 'Team Name'),
                '2' => array('data'=> 'Email Address'),
                '3' => array('data'=> 'User Type'),
                '4' => array('data'=> 'Error')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            foreach($bulk_invalid_users as $row => $record) {

                $error_message = '';
                for ($i=0; $i<count($bulk_error_messages[$row]); $i++) {
                    if ($i>0) { $error_message .= '<br />'; }
                    $error_message .= $bulk_error_messages[$row][$i];
                }

                $tbl_row = array(
                    '0' => array('data'=> $record['user_login']),
                    '1' => array('data'=> $record['user_team_id']),
                    '2' => array('data'=> $record['user_email']),
                    '3' => array('data'=> $record['user_type']),
                    '4' => array('data'=> $error_message)
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['bulk_invalid_users_table'] = $this->table->generate();
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
        $bulk_users = array();

        if ($this->session->flashdata('bulk_users')) {
            $bulk_users = $this->session->flashdata('bulk_users');
        }

        // bulk update
        $this->user_model->edit_bulk_users($bulk_users);
        $this->session->set_flashdata('message_success', 'Record(s) updated successfully.');

        redirect('administrator/user/edit_bulk');
    }
    
    //delete bulk upload action
    public function delete_bulk_upload_action()
    {
        // set page specific variables
        $page_info['title'] = 'Take an Action'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_delete_bulk_action_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $page_info['bulk_users'] = array();
        $page_info['bulk_invalid_users'] = array();
        $page_info['bulk_error_messages'] = array();


        if ($this->session->flashdata('bulk_delete_action')) {
            $this->session->keep_flashdata('bulk_delete_action');
        }
        if ( (int)$this->session->flashdata('bulk_delete_action') == 0 ) {
            redirect('administrator/user/delete_bulk');
        }
        

        if ($this->session->flashdata('bulk_users')) {
            $page_info['bulk_users'] = $this->session->flashdata('bulk_users');
            $this->session->keep_flashdata('bulk_users');
        }
        if ($this->session->flashdata('bulk_invalid_users')) {
            $page_info['bulk_invalid_users'] = $this->session->flashdata('bulk_invalid_users');
            $this->session->keep_flashdata('bulk_invalid_users');
        }
        if ($this->session->flashdata('bulk_error_messages')) {
            $page_info['bulk_error_messages'] = $this->session->flashdata('bulk_error_messages');
            $this->session->keep_flashdata('bulk_error_messages');
        }


        $bulk_invalid_users = $page_info['bulk_invalid_users'];
        $bulk_error_messages = $page_info['bulk_error_messages'];

        if ($bulk_invalid_users && count($bulk_invalid_users) < 250) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Login ID'),
                '1' => array('data'=> 'Error')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            foreach($bulk_invalid_users as $row => $record) {

                $error_message = '';
                for ($i=0; $i<count($bulk_error_messages[$row]); $i++) {
                    if ($i>0) { $error_message .= '<br />'; }
                    $error_message .= $bulk_error_messages[$row][$i];
                }

                $tbl_row = array(
                    '0' => array('data'=> $record['user_login']),
                    '1' => array('data'=> $error_message)
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['bulk_invalid_users_table'] = $this->table->generate();
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

    public function delete_bulk_upload_do_action()
    {
        $bulk_users = array();

        if ($this->session->flashdata('bulk_users')) {
            $bulk_users = $this->session->flashdata('bulk_users');
        }
        
        // bulk delete
        $this->user_model->delete_bulk_users($bulk_users);

        $this->session->set_flashdata('message_success', 'Record(s) deleted successfully.');

        redirect('administrator/user/delete_bulk');
    }
       

    public function edit()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit User View'));
        // set page specific variables
        $page_info['title'] = 'Edit User'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        // prefill form values
        $user_id = (int)$this->uri->segment(4);
		$user = $this->user_model->get_user($user_id);

        $this->_set_rules();

	$this->form_data->user_id = $user->id;
        $this->form_data->user_team_id = $user->user_team_id;
        $this->form_data->user_login = $user->user_login;
        $this->form_data->user_password = '';
        $this->form_data->user_confirm_password = '';
        $this->form_data->phone = $user->phone;
        $this->form_data->nid_passport_no = $user->nid_passport_no;
        $this->form_data->user_first_name = $user->user_first_name;
        $this->form_data->user_last_name = $user->user_last_name;
        $this->form_data->user_email = $user->user_email;
        $this->form_data->user_type = $user->user_type;
        $this->form_data->user_competency = $user->user_competency;
        $this->form_data->user_is_active = $user->user_is_active;
        $this->form_data->user_is_lock = $user->user_is_lock;
        $this->form_data->user_is_admit_card = $user->user_is_admit_card;
        $this->form_data->designation = $user->designation;
        $this->form_data->department = $user->department;
        $this->form_data->profile_image = $user->profile_image;
        $this->form_data->signature_image = $user->signature_image;
        $this->form_data->is_password_reset = $user->is_password_reset;
        
        $this->form_data->admin_group_id = $user->admin_group;

        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        // load view
	$this->load->view('administrator/layouts/default', $page_info);
    }

    public function update_user()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update User'));
        // set page specific variables
        $page_info['title'] = 'Edit User'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $user_id = (int)$this->input->post('user_id');

        $this->_set_fields();
        $this->_set_rules(true);

        if ($this->form_validation->run() == FALSE) {

            $this->form_data->user_id = $user_id;
            $this->load->view('administrator/layouts/default', $page_info);

        } else {



            $user_team_id = $this->input->post('user_team_id');
            $user_login = $this->input->post('user_login');
            $user_password = $this->input->post('user_password');
            $user_confirm_password = $this->input->post('user_confirm_password');
            $user_first_name = $this->input->post('user_first_name');
            $user_last_name = $this->input->post('user_last_name');
            $user_email = $this->input->post('user_email');
            $user_phone = $this->input->post('user_phone');
            $nid_passport_no = $this->input->post('nid_passport_no');
            $user_type = $this->input->post('user_type');
            $user_competency = $this->input->post('user_competency');
            $user_is_active = (int)$this->input->post('user_is_active');
            $user_is_lock = (int)$this->input->post('user_is_lock');
            $is_password_reset = (int)$this->input->post('is_password_reset');
            $user_is_admit_card = (int)$this->input->post('user_is_admit_card');
            $designation =$this->input->post('designation');
            $department = $this->input->post('department');
            $user_team_id=$user_team_id?$user_team_id:5;
            $admin_group_id = (int)$this->input->post('admin_group_id');

            $check_data_up = $this->select_global_model->is_data_exists($this->tbl_exam_users, 
                array('id !='=>$user_id,'user_login'=>$user_login));

            if($check_data_up){
                $this->session->set_flashdata('message_error', 'This User already exists!');
                redirect('administrator/admingroup/edit/'. $user_id);
            }

            if ($user_competency == 1) { $user_competency = 'Front Office'; }
            elseif ($user_competency == 0) { $user_competency = 'Back Office'; }
            else { $user_competency = 'Front Office'; }

            $file_name ="";
            $file_sig ="";

            if (isset($_FILES['profile_image']['name']) && !empty($_FILES['profile_image']['name'])) {
                $config['upload_path'] = './uploads/user/';
                $config['allowed_types'] = 'jpg|jpeg|png|gif';
                //$config['file_name'] = $_FILES["profile_image"]['name'];
                $file_name = $_FILES["profile_image"]['name'];
                $this->upload->initialize($config);
                $upload = $this->upload->do_upload('profile_image');

                if(!$upload){
                    $this->session->set_flashdata('message_error', $this->upload->display_errors());
                    redirect(base_url('administrator/user/add/'));
                }
            }
            else
            {
                if($this->input->post('profile_image_ex'))
                {
                    $file_name = $this->input->post('profile_image_ex');
                }
            }
            //$config['encrypt_name'] = true;
            if (isset($_FILES['signature_image']['name']) && !empty($_FILES['signature_image']['name'])) {
                $config_sig['upload_path'] = './uploads/signature/';
                $config_sig['allowed_types'] = 'jpg|jpeg|png|gif';

                $file_sig = $_FILES["signature_image"]['name'];
                $this->upload->initialize($config_sig);
                $upload_sig = $this->upload->do_upload('signature_image');

                if(!$upload_sig){
                    $this->session->set_flashdata('message_error', $this->upload->display_errors());
                    redirect(base_url('administrator/user/add/'));
                }
            }
            else
            {
                if($this->input->post('signature_image_ex'))
                {
                    $file_sig = $this->input->post('signature_image_ex');
                }
            }



            $data = array(
                'user_team_id' => $user_team_id,
                'user_password' => $user_password,
                'user_confirm_password' => $user_confirm_password,
                'is_password_reset' => $is_password_reset,
                'user_first_name' => $user_first_name,
                'user_last_name' => $user_last_name,
                'user_email' => $user_email,
                'user_type' => $user_type,
                'phone' => $user_phone,
                'nid_passport_no' => $nid_passport_no,
                'user_competency' => $user_competency,
                'user_is_active' => $user_is_active,
                'user_is_lock' => $user_is_lock,
                'user_is_admit_card' => $user_is_admit_card,
                'designation' => $designation,
                'department' => $department,
                'admin_group' => $admin_group_id,
                'profile_image' => $file_name,
                'signature_image'=> $file_sig
            );
            

            //echo $user_id; die();

            if ($this->user_model->update_user($user_id, $data)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else {
                $this->session->set_flashdata('message_error', $this->user_model->error_message. ' Update is unsuccessful.');
            }

            redirect('administrator/user/index/'. $user_id);
        }
    }


    /*User Log Function*/
    public function user_activity()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'User Activity View'));
        // set page specific variables
        $page_info['title'] = 'User Activity'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_activity_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $this->_set_fields();
        // gather filter options
        $filter_act_user = array();
        if ($this->session->flashdata('filter_login_names')) {
            $this->session->keep_flashdata('filter_login_names');
            $filter_login_name = $this->session->flashdata('filter_login_names');
            //echo $filter_login_name; die();
            $this->form_data->filter_login_name = $filter_login_name;
            $filter_act_user['filter_login_names']['field'] = 'user_login';
            $filter_act_user['filter_login_names']['value'] = $filter_login_name;
        }


        $page_info['filter_act_user'] = $filter_act_user;
        //print_r($filter_act_user); die();

        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->user_model->get_paged_users_activity($per_page, $page_offset, $filter_act_user);
       //print_r_pre($this->db->last_query()); die();

        $page_info['records'] = $record_result['result'];
        $records_act = $record_result['result'];
        //print_r_pre($records_act); die();

        // build paginated list
        $config = array();
        $config["base_url"] = base_url()."administrator/user/user_activity";
        $config["total_rows"] = $record_result['count'];
        $config['per_page'] = $this->config->item('per_page');
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        // GENERATING TABLE
        if ($records_act) {
            $tbl_heading = array(
                '0' => array('data'=> 'Act.Log ID'),
                '1' => array('data'=> 'User Activity'),
                '2' => array('data'=> 'Activity Time'),
                '3' => array('data'=> 'System user id', ),
                '4' => array('data'=> 'Name', /*'class' => 'center', 'width' => '80'*/),
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records_act); $i++){
                 $act_str = $records_act[$i]->activity;
                 $act_time_str = $records_act[$i]->activity_time;
                 $act_uid_str = $records_act[$i]->user_id;
                 $act_ulog_str = $records_act[$i]->user_login;

                 $tbl_row = array(
                    '0' => array('data'=> $records_act[$i]->id),
                    '1' => array('data'=> $act_str),
                    '2' => array('data'=> $act_time_str),
                    '3' => array('data'=> $act_uid_str),
                    '4' => array('data'=> $act_ulog_str)
                 );
                 $this->table->add_row($tbl_row);
            }

            $page_info['records_table'] = $this->table->generate();
            $page_info['pagin_links'] = $this->pagination->create_links();

        }else {
            $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
            $page_info['pagin_links'] = '';
        }
        // load view
        $this->load->view('administrator/layouts/default', $page_info);
    }

    public function filter_act_user()
    {
        $filter_login_name = $this->input->post('filter_login_name');
        $filter_clear = $this->input->post('filter_clear');

       

        if ($filter_clear == '') {
            if ($filter_login_name != '') {
                $this->session->set_flashdata('filter_login_names', $filter_login_name);
            }
        } else {
            $this->session->unset_userdata('filter_login_names');
        }



        //echo $this->session->flashdata('filter_login_names'); die();

        redirect('administrator/user/user_activity');
    }


    /*User Log Function*/
    public function user_iptracking()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'User IP Tracking View'));
        // set page specific variables
        $page_info['title'] = 'User IP Tracking'. $this->site_name;
        $page_info['view_page'] = 'administrator/user_ip_tracking_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $this->_set_fields();
        // gather filter options
        $filter_act_user = array();
        if ($this->session->flashdata('filter_login_name')) {
            $this->session->keep_flashdata('filter_login_name');
            $filter_login_name = $this->session->flashdata('filter_login_name');
            $this->form_data->filter_login_name = $filter_login_name;
            $filter_act_user['filter_login_name']['field'] = 'user_login';
            $filter_act_user['filter_login_name']['value'] = $filter_login_name;
        }
        $page_info['filter_act_user'] = $filter_act_user;
        //print_r($filter_act_user); die();

        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->user_model->get_paged_users_iptracking($per_page, $page_offset, $filter_act_user);
       //print_r_pre($this->db->last_query()); die();

        $page_info['records'] = $record_result['result'];
        $records_act = $record_result['result'];
        //print_r_pre($records_act); die();

        // build paginated list
        $config = array();
        $config["base_url"] = base_url()."administrator/user/user_activity";
        $config["total_rows"] = $record_result['count'];
        $config['per_page'] = $this->config->item('per_page');
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        // GENERATING TABLE
        if ($records_act) {
            $tbl_heading = array(
               
                '1' => array('data'=> 'IP Address'),
                '2' => array('data'=> 'Activity Time'),
                '3' => array('data'=> 'System User Id', ),
                '4' => array('data'=> 'Login Pin', /*'class' => 'center', 'width' => '80'*/),
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records_act); $i++){
                $act_str = $records_act[$i]->ip_address;
                 $act_time_str = $records_act[$i]->timestamp;
                 $act_uid_str = $records_act[$i]->user_id;
                 $act_ulog_str = $records_act[$i]->user_login;

                 $tbl_row = array(
                   
                    '1' => array('data'=> $act_str),
                    '2' => array('data'=> $act_time_str),
                    '3' => array('data'=> $act_uid_str),
                    '4' => array('data'=> $act_ulog_str)
                 );
                 $this->table->add_row($tbl_row);
            }

            $page_info['records_table'] = $this->table->generate();
            $page_info['pagin_links'] = $this->pagination->create_links();

        }else {
            $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
            $page_info['pagin_links'] = '';
        }
        // load view
        $this->load->view('administrator/layouts/default', $page_info);
    }

    

    public function filter_user_iptracking()
    {
        $filter_login_name = $this->input->post('filter_login_name');
        $filter_clear = $this->input->post('filter_clear');

        //echo $filter_login_name; die();

        if ($filter_clear == '') {
            if ($filter_login_name != '') {
                $this->session->set_flashdata('filter_login_name', $filter_login_name);
            }
        } else {
            $this->session->unset_userdata('filter_login_name');
        }

        //echo $this->session->flashdata('filter_login_name'); die();

        redirect('administrator/user/user_iptracking');
    }

    /**
     * Delete a user
     * @return void
     */
    public function delete()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Delete User'));
        $user_id = (int)$this->uri->segment(4);
        $res = $this->user_model->delete_user($user_id);

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Delete is successful.');
        } else {
            $this->session->set_flashdata('message_error', $this->user_model->error_message .' Delete is unsuccessful.');
        }
        
        redirect('administrator/user');
    }
	
    /**
     * Inactive a user
     * @return void
     */
    public function inactive()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Deactivate User'));
        $user_id = (int)$this->uri->segment(4);
        $res = $this->user_model->inactive_user($user_id);

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Inactive is successful.');
        } else {
            $this->session->set_flashdata('message_error', $this->user_model->error_message .' Inactive is unsuccessful.');
        }
        
        redirect('administrator/user');
    }
	
    /**
     * Active a user
     * @return void
     */
    public function active()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Activate User'));
        $user_id = (int)$this->uri->segment(4);
        $res = $this->user_model->active_user($user_id);

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Active is successful.');
        } else {
            $this->session->set_flashdata('message_error', $this->user_model->error_message .' Active is unsuccessful.');
        }
        
        redirect('administrator/user');
    }


    // set empty default form field values
    private function _set_fields()
    {
		$this->form_data = new StdClass;
		//$this->form_data = new StdClass;
        $this->form_data->user_id = 0;
        $this->form_data->user_team_id = 0;
        $this->form_data->user_login = '';
        $this->form_data->user_password = '';
        $this->form_data->user_confirm_password = '';
        $this->form_data->is_password_reset = 0;
        $this->form_data->user_first_name = '';
        $this->form_data->user_last_name = '';
        $this->form_data->user_email = '';
        $this->form_data->user_type = '';
        $this->form_data->user_is_admit_card = 0;
        $this->form_data->department = '';
        $this->form_data->designation = '';
        $this->form_data->user_competency = 'Front Office';
        $this->form_data->user_is_active = 1;
        $this->form_data->user_is_lock = 0;
        $this->form_data->filter_loginoremail = '';
        $this->form_data->filter_type = '';
        $this->form_data->filter_team = 0;
        $this->form_data->filter_active = '';
        $this->form_data->admin_group_id = '';
        $this->form_data->phone = '' ;
        $this->form_data->nid_passport_no = '' ;

        $this->form_data->filter_login_name = '';
    }

    // validation rules
    private function _set_rules($is_edit = false)
    {
        $this->form_validation->set_rules('user_login', 'User Login', 'required|trim|xss_clean|strip_tags');
        if (!$is_edit) {
            $this->form_validation->set_rules('user_password', 'User Password', 'required|trim|xss_clean|strip_tags');
            $this->form_validation->set_rules('user_confirm_password', 'User Confirm Password', 'required|trim|xss_clean|strip_tags');
        }
        $this->form_validation->set_rules('user_first_name', 'User First Name', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('user_last_name', 'User Last Name', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('user_email', 'User Email', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('user_type', 'User Type', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('user_competency', 'User Competency Level', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('user_is_active', 'User Is Active?', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('user_is_lock', 'User Is Locked?', 'trim|xss_clean|strip_tags');
    }

}

/* End of file user.php */
/* Logrpion: ./appligrpion/controllers/administrator/user.php */