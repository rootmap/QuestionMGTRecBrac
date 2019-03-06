<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Group extends MY_Controller
{
    var $current_page = "group";
    
    var $priv_list = array();
    var $user_group_list = array();
    var $tbl_exam_user_groups    = "exm_user_groups";

    function __construct()
    {
        parent::__construct();
$this->form_data = new StdClass;
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('user_group_model');
        $this->load->model('group_privilage_model');
        $this->load->model('global/select_global_model');
        
        
        // pre-fill privilage drop-down
        $priv_ids = $this->group_privilage_model->get_privilages();
        
        if ($priv_ids) {
            for ($i=0; $i<count($priv_ids); $i++) {
                $this->priv_list[$priv_ids[$i]->id] = $priv_ids[$i]->privilage_description;
            }
        }
        
        // pre-fill group drop-down
        $user_groups = $this->user_group_model->get_user_groups();
        $this->user_group_list[0] = 'All User Group';
        if ($user_groups) {
            for ($i=0; $i<count($user_groups); $i++) {
                $this->user_group_list[$user_groups[$i]->id] = $user_groups[$i]->group_name;
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
     * Display paginated list of groups
     * @return void
     */
    public function index()
    {
        // set page specific variables
        $page_info['title'] = 'Manage User Groups'. $this->site_name;
        $page_info['view_page'] = 'administrator/group_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_group_name')) {
            $this->session->keep_flashdata('filter_group_name');
            $filter_group_name = $this->session->flashdata('filter_group_name');
            $this->form_data->filter_group_name = $filter_group_name;
            $filter['filter_group_name']['field'] = 'group_name';
            $filter['filter_group_name']['value'] = $filter_group_name;
        }
        $page_info['filter'] = $filter;


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        $record_result = $this->user_group_model->get_paged_user_groups($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];


        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/group";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'User Group Name'),
                '1' => array('data'=> 'Number of Teams', 'class' => 'center', 'width' => '120'),
                '2' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {

                $number_of_teams = $this->user_group_model->get_team_count($records[$i]->id);

                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= anchor('administrator/group/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                if ($number_of_teams <= 0) {
                    $action_str .= '&nbsp;&nbsp;&nbsp;';
                    if(!isSystemAuditor())
                    $action_str .= anchor('administrator/group/delete/'. $records[$i]->id, '<i class="icon-trash"></i>', array('title'=>'Delete', 'onclick'=>'return confirm(\'Do you really want to delete this record?\')'));
                }

                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->group_name),
                    '1' => array('data'=> $number_of_teams, 'class' => 'center', 'width' => '120'),
                    '2' => array('data'=> $action_str, 'class' => 'center', 'width' => '100')
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
        $filter_group_name = $this->input->post('filter_group_name');
        $filter_clear = $this->input->post('filter_clear');

        if ($filter_clear == '') {
            if ($filter_group_name != '') {
                $this->session->set_flashdata('filter_group_name', $filter_group_name);
            }
        } else {
            $this->session->unset_userdata('filter_group_name');
        }

        redirect('administrator/group');
    }

    /**
     * Display add group form
     * @return void
     */
    public function add()
    {
        // set page specific variables
        $page_info['title'] = 'Add New User Group'. $this->site_name;
        $page_info['view_page'] = 'administrator/group_form_view';
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

    public function add_user_group()
    {
        $page_info['title'] = 'Add New Group'. $this->site_name;
        $page_info['view_page'] = 'administrator/group_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $group_name = $this->input->post('group_name');
            
            $priv_ids = $this->input->post('priv_ids');
            
            $user_group_ids = $this->input->post('user_group_ids');
            if($user_group_ids){
                $user_group_ids = implode(",", $this->input->post('user_group_ids'));
            }

            $check_data = $this->select_global_model->is_data_exists($this->tbl_exam_user_groups, array('group_name'=>$group_name));

            if($check_data){
                $this->session->set_flashdata('message_error', 'This Group Name already exists!');
                redirect('administrator/group/add/');
            }
            
            $data = array(
                'group_name' => $group_name
            );

            $res = (int)$this->user_group_model->add_user_group($data, $priv_ids, $user_group_ids);

            if ($res > 0) {
                $this->session->set_flashdata('message_success', 'Add is successful.');
                redirect('administrator/group/add_user_group/'. $res);
            } else {
                $page_info['message_error'] = 'Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }

    public function edit()
    {
        // set page specific variables
        $page_info['title'] = 'Edit Group'. $this->site_name;
        $page_info['view_page'] = 'administrator/group_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $this->_set_rules();

        // prefill form values
        $group_id = (int)$this->uri->segment(4);
        $user_group = $this->user_group_model->get_user_group($group_id);

        $this->form_data->group_id = $user_group->id;
        $this->form_data->group_name = $user_group->group_name;
        $user_group_ids = 0;
        $group_privilage = $this->group_privilage_model->get_group_privilage($group_id);
        $priv_ids = array();
        if( $group_privilage ){
            foreach($group_privilage as $k=>$v){
                $priv_ids[] = $v->id;
            }
            $user_group_ids = $group_privilage[0]->group_id_for_pass;
        }
        $this->form_data->priv_ids = $priv_ids;
        
		if($user_group_ids == 0){
           $this->form_data->user_group_ids = explode(",", $user_group_ids); 
        }
        elseif($user_group_ids){
           $this->form_data->user_group_ids = explode(",", $user_group_ids); 
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

    public function update_user_group()
    {
        // set page specific variables
        $page_info['title'] = 'Edit Group'. $this->site_name;
        $page_info['view_page'] = 'administrator/group_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $group_id = (int)$this->input->post('group_id');
        
        $priv_ids = $this->input->post('priv_ids');
        $user_group_ids = $this->input->post('user_group_ids');

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->form_data->group_id = $group_id;
            $this->form_data->priv_ids = $priv_ids;
            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $group_name = $this->input->post('group_name');
            $priv_ids = $this->input->post('priv_ids');
            $user_group_ids = $this->input->post('user_group_ids');
            
            if($user_group_ids){
                $user_group_ids = implode(",", $this->input->post('user_group_ids'));
            }

            $check_data_up = $this->select_global_model->is_data_exists($this->tbl_exam_user_groups, array('id !=' =>$group_id, 'group_name'=>$group_name));

            if($check_data_up){
                $this->session->set_flashdata('message_error', 'This Group Name already exists!');
                redirect('administrator/group/edit/'. $group_id);
            }
            
            $data = array(
                'group_name' => $group_name
            );
            //print_r( $data); die();

            if ($this->user_group_model->update_user_group($group_id, $data, $priv_ids, $user_group_ids)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else  {
                $this->session->set_flashdata('message_error', $this->user_group_model->error_message. ' Update is unsuccessful.');
            }

            redirect('administrator/group/index/'. $group_id);
        }
    }

    /**
     * Delete a user group
     * @return void
     */
    public function delete()
    {
        $group_id = (int)$this->uri->segment(4);
        $res = $this->user_group_model->delete_user_group($group_id);

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Delete is successful.');
        } else {
            $this->session->set_flashdata('message_error', $this->user_group_model->error_message .' Delete is unsuccessful.');
        }
        
        redirect('administrator/group');
    }

    // set empty default form field values
    private function _set_fields()
    {
		$this->form_data = new StdClass;
        $this->form_data->group_id = 0;
        $this->form_data->group_name = '';

        $this->form_data->filter_group_name = '';
        
        $this->form_data->priv_ids = array();
        $this->form_data->user_group_ids = array();
    }

    // validation rules
    private function _set_rules()
    {
        $this->form_validation->set_rules('group_name', 'User Group Name', 'required|trim|xss_clean|strip_tags');
    }
}

/* End of file group.php */
/* Location: ./application/controllers/administrator/group.php */