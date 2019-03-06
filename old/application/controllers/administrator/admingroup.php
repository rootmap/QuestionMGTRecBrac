<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Admingroup extends MY_Controller
{
    var $current_page = "admingroup";
    var $tbl_exam_admin_groups    = "exm_admin_groups";
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
        $this->load->model('admin_group_model');
        $this->load->model('global/select_global_model');
        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');

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
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Admin Group'));
        // set page specific variables
        $page_info['title'] = 'Manage Admin Groups'. $this->site_name;
        $page_info['view_page'] = 'administrator/admin_group_list_view';
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


        $record_result = $this->admin_group_model->get_paged_admin_groups($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];

        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/admingroup";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Admin Group Name'),
                '1' => array('data'=> 'Number of Admin', 'class' => 'center', 'width' => '120'),
                '2' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {

                $number_of_admin = $this->admin_group_model->get_admin_count($records[$i]->id);

                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= anchor('administrator/admingroup/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                //if ($number_of_teams <= 0) {
                    $action_str .= '&nbsp;&nbsp;&nbsp;';
                if(!isSystemAuditor())
                    $action_str .= anchor('administrator/admingroup/delete/'. $records[$i]->id, '<i class="icon-trash"></i>', array('title'=>'Delete', 'onclick'=>'return confirm(\'Do you really want to delete this record?\')'));
                //}

                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->group_name),
                    '1' => array('data'=> $number_of_admin, 'class' => 'center', 'width' => '120'),
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

        redirect('administrator/admingroup');
    }

    /**
     * Display add group form
     * @return void
     */
    public function add()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Admin Group'));
        // set page specific variables
        $page_info['title'] = 'Add New Admin Group'. $this->site_name;
        $page_info['view_page'] = 'administrator/admin_group_form_view';
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

    public function add_admin_group()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Group'));
        $page_info['title'] = 'Add New Group'. $this->site_name;
        $page_info['view_page'] = 'administrator/admin_group_form_view';
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

            $check_data = $this->select_global_model->is_data_exists($this->tbl_exam_admin_groups, array('group_name'=>$group_name));

            if($check_data){
                $this->session->set_flashdata('message_error', 'This Admin Group already exists!');
                redirect('administrator/admingroup/add/');
            }

            $data = array(
                'group_name' => $group_name
            );

            $res = (int)$this->admin_group_model->add_admin_group($data);

            if ($res > 0) {
                $this->session->set_flashdata('message_success', 'Add is successful.');
                redirect('administrator/admingroup/add/'. $res);
            } else {
                $page_info['message_error'] = 'Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }

    public function edit()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Admin Group View'));
        // set page specific variables
        $page_info['title'] = 'Edit Group'. $this->site_name;
        $page_info['view_page'] = 'administrator/admin_group_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $this->_set_rules();

        // prefill form values
        $group_id = (int)$this->uri->segment(4);
        $admin_group = $this->admin_group_model->get_admin_group($group_id);

        $this->form_data->group_id = $admin_group->id;
        $this->form_data->group_name = $admin_group->group_name;
        
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        // load view
	$this->load->view('administrator/layouts/default', $page_info);
    }

    public function update_admin_group()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Admin Group'));
        // set page specific variables
        $page_info['title'] = 'Edit Group'. $this->site_name;
        $page_info['view_page'] = 'administrator/admin_group_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $group_id = (int)$this->input->post('group_id');

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->form_data->group_id = $group_id;
            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $group_name = $this->input->post('group_name');

            $check_data_up = $this->select_global_model->is_data_exists($this->tbl_exam_admin_groups, array('id !='=>$group_id,'group_name'=>$group_name));

            if($check_data_up){
                $this->session->set_flashdata('message_error', 'This Admin Group already exists!');
                redirect('administrator/admingroup/edit/'. $group_id);
            }

            $data = array(
                'group_name' => $group_name
            );

            if ($this->admin_group_model->update_admin_group($group_id, $data)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else  {
                $this->session->set_flashdata('message_error', $this->admin_group_model->error_message. ' Update is unsuccessful.');
            }

            redirect('administrator/admingroup/index/'. $group_id);
        }
    }

    /**
     * Delete a user group
     * @return void
     */
    public function delete()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Delete Admin Group'));
        $group_id = (int)$this->uri->segment(4);
        $res = $this->admin_group_model->delete_admin_group($group_id);

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Delete is successful.');
        } else {
            $this->session->set_flashdata('message_error', $this->admin_group_model->error_message .' Delete is unsuccessful.');
        }
        
        redirect('administrator/admingroup');
    }

    // set empty default form field values
    private function _set_fields()
    {
		$this->form_data = new StdClass;
        $this->form_data->group_id = 0;
        $this->form_data->group_name = '';

        $this->form_data->filter_group_name = '';
    }

    // validation rules
    private function _set_rules()
    {
        $this->form_validation->set_rules('group_name', 'Admin Group Name', 'required|trim|xss_clean|strip_tags');
    }
}

/* End of file group.php */
/* Location: ./application/controllers/administrator/group.php */