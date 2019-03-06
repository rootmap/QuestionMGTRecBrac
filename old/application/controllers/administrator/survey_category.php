<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Survey_category extends MY_Controller
{
    var $current_page = "survey_category";
    var $cat_list = array();
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('survey_category_model');

        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');

        $all_categories_tree = $this->survey_category_model->get_categories_recursive();
        $all_categories = $this->survey_category_model->get_padded_categories($all_categories_tree);

        $this->cat_list[] = 'Select a Category';
        if ($all_categories) {
            for ($i=0; $i<count($all_categories); $i++) {
                $this->cat_list[$all_categories[$i]->id] = $all_categories[$i]->cat_name;
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
                redirect('landing');
            }
        }
    }

    /**
     * Display paginated list of categories
     * @return void
     */
    public function index()
	{
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Survey Categories'));
        // set page specific variables
        $page_info['title'] = 'Manage Survey Categories'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_category_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_cat_name')) {
            $this->session->keep_flashdata('filter_cat_name');
            $filter_cat_name = $this->session->flashdata('filter_cat_name');
            $this->form_data->filter_cat_name = $filter_cat_name;
            $filter['filter_cat_name']['field'] = 'cat_name';
            $filter['filter_cat_name']['value'] = $filter_cat_name;
        }
        $page_info['filter'] = $filter;

        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        if (count($filter) > 0) {
            $record_result = $this->survey_category_model->get_paged_categories($per_page, $page_offset, $filter);
        } else {
            $record_result = $this->survey_category_model->get_padded_paged_categories($per_page, $page_offset);
        }
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];


        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/survey_category";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;
        //print_r($this->global_options['default_survey_category']); die();

        // get default category id
        $default_cat_id = (int)@$this->global_options['default_survey_category'];

        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Category Id'),
                '1' => array('data'=> 'Name'),
                '2' => array('data'=> 'No. of Questions', 'class' => 'center', 'width' => '120'),
                '3' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {

                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= anchor('administrator/survey_category/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                if ($default_cat_id != $records[$i]->id) {
                    $action_str .= '&nbsp;&nbsp;&nbsp;';
                    if(!isSystemAuditor())
                    $action_str .= anchor('administrator/survey_category/delete/'. $records[$i]->id, '<i class="icon-trash"></i>', array('title'=>'Delete', 'onclick'=>'return confirm(\'Do you really want to delete this record?\')'));
                }

                $no_of_questions = (int)$this->survey_category_model->get_question_count($records[$i]->id);

                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->id, 'class' => 'center', 'width' => '100'),
                    '1' => array('data'=> $records[$i]->cat_name),
                    '2' => array('data'=> $no_of_questions, 'class' => 'center'),
                    '3' => array('data'=> $action_str, 'class' => 'center', 'width' => '100', 'width' => '120')
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
        $filter_cat_name = $this->input->post('filter_cat_name');
        $filter_clear = $this->input->post('filter_clear');

        if ($filter_clear == '') {
            if ($filter_cat_name != '') {
                $this->session->set_flashdata('filter_cat_name', $filter_cat_name);
            }
        } else {
            $this->session->unset_userdata('filter_cat_name');
        }

        redirect('administrator/survey_category');
    }

    /**
     * Display add category form
     * @return void
     */
    public function add()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Survey Category View'));
        // set page specific variables
        $page_info['title'] = 'Add New Survey Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();

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

    public function add_category()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add Survey Category'));
        $page_info['title'] = 'Add New Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();



        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $cat_name = $this->input->post('cat_name');
            $cat_parent = (int)$this->input->post('cat_parent');

            $data = array(
                'cat_name' => $cat_name,
                'cat_parent' => $cat_parent
            );

            $res = (int)$this->survey_category_model->add_category($data);
            //print_r($res); die();
            if ($res > 0) {
                $this->session->set_flashdata('message_success', 'Add is successful.');
                redirect('administrator/survey_category/edit/'. $res);
            } else {
                $page_info['message_error'] = 'Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }

    public function edit()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Survey Category View'));
        // set page specific variables
        $page_info['title'] = 'Edit Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        // prefill form values
        $cat_id = (int)$this->uri->segment(4);
	   $category = $this->survey_category_model->get_category($cat_id);

        if (count($this->cat_list) > 0) {
            foreach($this->cat_list as $key => $value) {
                if ($key == $cat_id) {
                    unset($this->cat_list[$key]);
                    break;
                }
            }
        }

        $this->_set_rules();
        //print_r($category->id); die();
        @$this->form_data->cat_id = $category->id;
        $this->form_data->cat_name = $category->cat_name;
        $this->form_data->cat_parent = $category->cat_parent;

        if ($this->session->flashdata('message_success'))
        {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        if ($this->session->flashdata('message_error'))
        {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        // load view
	$this->load->view('administrator/layouts/default', $page_info);
    }

    public function update_category()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update Survey Category'));
        // set page specific variables
        $page_info['title'] = 'Edit Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $cat_id = (int)$this->input->post('cat_id');

        if (count($this->cat_list) > 0) {
            foreach($this->cat_list as $key => $value) {
                if ($key == $cat_id) {
                    unset($this->cat_list[$key]);
                    break;
                }
            }
        }
        
        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->form_data->cat_id = $cat_id;
            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $cat_name = $this->input->post('cat_name');
            $cat_parent = (int)$this->input->post('cat_parent');

            $data = array(
                'cat_name' => $cat_name,
                'cat_parent' => $cat_parent
            );

            if ($this->survey_category_model->update_category($cat_id, $data)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else  {
                $this->session->set_flashdata('message_error', $this->survey_category_model->error_message. ' Update is unsuccessful.');
            }

            redirect('administrator/survey_category/edit/'. $cat_id);
        }
    }

    /**
     * Delete a category
     * @return void
     */
    public function delete()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Delete Survey Category'));
        $cat_id = (int)$this->uri->segment(4);
        $res = $this->survey_category_model->delete_category($cat_id);

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Delete is successful.');
        } else {
            $this->session->set_flashdata('message_error', $this->survey_category_model->error_message .' Delete is unsuccessful.');
        }
        
        redirect('administrator/survey_category');
    }


    // set empty default form field values
    private function _set_fields()
    {
        @$this->form_data->cat_id = 0;
        $this->form_data->cat_parent = 0;
	$this->form_data->cat_name = '';

	$this->form_data->filter_cat_name = '';
    }

    // validation rules
    private function _set_rules()
    {
        $this->form_validation->set_rules('cat_name', 'Category Name', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('cat_parent', 'Parent Category', 'trim|xss_clean|strip_tags');
    }

}

/* End of file category.php */
/* Location: ./application/controllers/administrator/category.php */