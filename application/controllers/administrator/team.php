<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Team extends MY_Controller
{
    var $current_page = "team";
    var $user_group_list = array();
    var $user_group_list_filter = array();
    var $tbl_exam_user_team    = "user_teams";
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
        $this->load->model('user_team_model');
        $this->load->model('user_group_model');
        //$this->load->model('global/Select_global_model');

        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');


        // pre-fill user team drop-down
        $user_groups = $this->user_group_model->get_user_groups();

        $this->user_group_list[] = 'Select an User Group';
        $this->user_group_list_filter[] = 'All groups';

        if ($user_groups) {
            for ($i=0; $i<count($user_groups); $i++) {
                $this->user_group_list[$user_groups[$i]->id] = $user_groups[$i]->group_name;
                $this->user_group_list_filter[$user_groups[$i]->id] = $user_groups[$i]->group_name;
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
     * Display paginated list of teams
     * @return void
     */
    public function index()
	{
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage User Teams View'));
        // set page specific variables
        $page_info['title'] = 'Manage User Teams'. $this->site_name;
        $page_info['view_page'] = 'administrator/team_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();

        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_team_name')) {
            $this->session->keep_flashdata('filter_team_name');
            $filter_team_name = $this->session->flashdata('filter_team_name');
            $this->form_data->filter_team_name = $filter_team_name;
            $filter['filter_team_name']['field'] = 'team_name';
            $filter['filter_team_name']['value'] = $filter_team_name;
        }
        if ($this->session->flashdata('filter_group')) {
            $this->session->keep_flashdata('filter_group');
            $filter_group = (int)$this->session->flashdata('filter_group');
            $this->form_data->filter_group = $filter_group;
            $filter['filter_group']['field'] = 'group_id';
            $filter['filter_group']['value'] = $filter_group;
        }
        $page_info['filter'] = $filter;


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->user_team_model->get_paged_user_teams($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];

        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/team";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);

        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'User Team Name'),
                '1' => array('data'=> 'Group Name'),
                '2' => array('data'=> 'Number of Users', 'class' => 'center', 'width' => '120'),
                '3' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
            );

            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {
                
                $number_of_users = $this->user_team_model->get_user_count($records[$i]->id);

                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= anchor('administrator/team/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                if ($number_of_users <= 0) {
                    $action_str .= '&nbsp;&nbsp;&nbsp;';
                    if(!isSystemAuditor())
                    $action_str .= anchor('administrator/team/delete/'. $records[$i]->id, '<i class="icon-trash"></i>', array('title'=>'Delete', 'onclick'=>'return confirm(\'Do you really want to delete this record?\')'));
                }

                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->team_name),
                    '1' => array('data'=> $records[$i]->group_name .''),
                    '2' => array('data'=> $number_of_users, 'class' => 'center', 'width' => '120'),
                    '3' => array('data'=> $action_str, 'class' => 'center', 'width' => '100')
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
        $filter_team_name = $this->input->post('filter_team_name');
        $filter_group = (int)$this->input->post('filter_group');
        $filter_clear = $this->input->post('filter_clear');

        if ($filter_clear == '') {
            if ($filter_team_name != '') {
                $this->session->set_flashdata('filter_team_name', $filter_team_name);
            }
            if ($filter_group > 0) {
                $this->session->set_flashdata('filter_group', $filter_group);
            }
        } else {
            $this->session->unset_userdata('filter_team_name');
            $this->session->unset_userdata('filter_group');
        }

        redirect('administrator/team');
    }

    /**
     * Display add team form
     * @return void
     */
    public function add()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New User Team View'));
        // set page specific variables
        $page_info['title'] = 'Add New User Team'. $this->site_name;
        $page_info['view_page'] = 'administrator/team_form_view';
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

    public function add_user_team()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New User Team'));
        $page_info['title'] = 'Add New Team'. $this->site_name;
        $page_info['view_page'] = 'administrator/team_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $group_id = (int)$this->input->post('group_id');
            $team_name = $this->input->post('team_name');

            $data = array(
                'group_id' => $group_id,
                'team_name' => $team_name
            );

            $check_data = $this->user_team_model->is_data_exists($this->tbl_exam_user_team, 
                array('team_name'=>$team_name));

            if($check_data){
                $this->session->set_flashdata('message_error', 'This Team already exists with this group!');
                redirect('administrator/team/add/');
            }

            $res = (int)$this->user_team_model->add_user_team($data);
            //print_r($res); die();
            if ($res > 0) {
                $this->session->set_flashdata('message_success', 'Add is successful.');
                redirect('administrator/team/add/'. $res);
            } else {
                $page_info['message_error'] = 'Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }

    public function edit()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit User Team View'));
        // set page specific variables
        $page_info['title'] = 'Edit Team'. $this->site_name;
        $page_info['view_page'] = 'administrator/team_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $this->_set_rules();

        // prefill form values
        $team_id = (int)$this->uri->segment(4);
		$user_team = $this->user_team_model->get_user_team($team_id);
        //print_r($user_team); die();
		@$this->form_data->team_id = $user_team->id;
		$this->form_data->group_id = $user_team->group_id;
		$this->form_data->team_name = $user_team->team_name;
        
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        // load view
		$this->load->view('administrator/layouts/default', $page_info);
    }

    public function update_user_team()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update User Team'));
        // set page specific variables
        $page_info['title'] = 'Edit Team'. $this->site_name;
        $page_info['view_page'] = 'administrator/team_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $team_id = (int)$this->input->post('team_id');

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->form_data->team_id = $team_id;
            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $group_id = (int)$this->input->post('group_id');
            $team_name = $this->input->post('team_name');

            $check_data_up = $this->user_team_model->is_data_exists($this->tbl_exam_user_team, array('id !=' =>$team_id, 'team_name'=>$team_name));

            if($check_data_up){
                $this->session->set_flashdata('message_error', 'This Team already exists with this group!');
                redirect('administrator/team/edit/'. $team_id);
            }

            $data = array(
                'group_id' => $group_id,
                'team_name' => $team_name
            );

            if ($this->user_team_model->update_user_team($team_id, $data)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else  {
                $this->session->set_flashdata('message_error', $this->user_team_model->error_message. ' Update is unsuccessful.');
            }

            redirect('administrator/team/index/'. $team_id);
        }
    }

    /**
     * Delete a user team
     * @return void
     */
    public function delete()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Delete User Team'));
        $team_id = (int)$this->uri->segment(4);
        $res = $this->user_team_model->delete_user_team($team_id);

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Delete is successful.');
        } else {
            $this->session->set_flashdata('message_error', $this->user_team_model->error_message .' Delete is unsuccessful.');
        }
        
        redirect('administrator/team');
    }

    // set empty default form field values
	private function _set_fields()
	{
		$this->form_data = new StdClass;
        $this->form_data->team_id = 0;
        $this->form_data->group_id = 0;
		$this->form_data->team_name = '';

        $this->form_data->filter_team_name = '';
        $this->form_data->filter_group = 0;
	}

	// validation rules
	private function _set_rules()
	{
		$this->form_validation->set_rules('group_id', 'Group', 'required|trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('team_name', 'User Team Name', 'required|trim|xss_clean|strip_tags');
	}
}

/* End of file team.php */
/* Logrpion: ./appligrpion/controllers/administrator/team.php */