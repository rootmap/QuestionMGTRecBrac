<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Category extends MY_Controller
{
    var $current_page = "category";
    var $cat_list = array();
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
        $this->load->model('category_model');
        $this->load->model('global/Select_global_model');
        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');

        $all_categories_tree = $this->category_model->get_categories_recursive();
        $all_categories = $this->category_model->get_padded_categories($all_categories_tree);

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
                redirect('home');
            }
        }
    }

    /**
     * Display paginated list of categories
     * @return void
     */

    function removeDashFromString($str='')
    {
        return trim(str_replace('&mdash;','', $str));
    }

    public function tabSessionSet()
    {
        $cat_name = $this->input->post('interfaceID');
        $this->activeTab($cat_name);
    }

    private function categoryIndex()
    {

        //echo 1; die();
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Categories'));
        // set page specific variables
        $page_info['title'] = 'Manage Categories'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->userdata('filter_search')) {
            $filter_search = $this->session->userdata('filter_search');
            $this->form_data->filter_search = $filter_search;
            $filter['category']['field'] = 'any';
            $filter['category']['value'] = $filter_search;
        }
    
        $page_info['filter'] = $filter;

        //print_r_pre($filter);


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        $record_result = $this->category_model->get_paged_categories($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];
        //echo "<pre>";
        //print_r($records); die();
        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/category";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) 
        {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'SL', 'class' => 'center', 'width' => '100'),
                '1' => array('data'=> 'Category Title', 'class' => 'center', 'width' => '100'),
                '2' => array('data'=> 'Created By', 'class' => 'center', 'width' => '100'),
                '3' => array('data'=> 'Creation Time', 'class' => 'center', 'width' => '100'),
                '4' => array('data'=> 'Action', 'class' => 'center', 'width' => '80')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'=> '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'=> '</table>'
            );
            $this->table->set_template($tbl_template);
            $d=1;
            for ($i = 0; $i<count($records); $i++) {
                
                $noof_questions_str = 0;
                $noof_questions_used_str = '';//$this->survey_question_model->get_used_question_count($records[$i]->id);

                $htmlAction='';
                $htmlAction .='<form style="padding:0px important; margin: 0 0 0px;" action="'.site_url('administrator/category/modcat').'" method="post">';

                $htmlAction .='<input type="hidden" name="interface" value="1" />';
                $htmlAction .='<input type="hidden" name="cat_id" value="'. $records[$i]->id.'" />';
                $htmlAction .='<button type="submit" class="btn btn-info"><i class="icon-edit"></i></button>';
                
                $htmlAction .='</form>';


                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= $htmlAction;
                //$action_str .= anchor('administrator/category/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                
                $tbl_row = array(
                    '0' => array('data'=> $d, 'class' => 'center', 'width' => '100'),
                    '1' => array('data'=> $records[$i]->cat_name, 'class' => 'center', 'width' => '100'),
                    '2' => array('data'=> $records[$i]->created_by, 'class' => 'center', 'width' => '100'),
                    '3' => array('data'=> $records[$i]->created_at, 'class' => 'center', 'width' => '100'),
                    '4' => array('data'=> $action_str, 'class' => 'center', 'width' => '100', 'width' => '80')
                );
                $d++;
                $this->table->add_row($tbl_row);
                
            }

            $page_info['records_table'] = $this->table->generate();
            $page_info['pagin_links'] = $this->pagination->create_links();
        } 
        else 
        {
            $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
            $page_info['pagin_links'] = '';
        }
        
        // determine messages
        if ($this->session->flashdata('message_error')) 
        {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        if ($this->session->flashdata('message_success')) 
        {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        
        // load view
        $this->load->view('administrator/layouts/default', $page_info);
    }

    private function subCategoryIndex()
    {

        //echo 1; die();
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Sub Categories'));
        // set page specific variables
        $page_info['title'] = 'Manage Sub Categories'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->userdata('filter_search')) {
            $filter_search = $this->session->userdata('filter_search');
            $this->form_data->filter_search = $filter_search;
            $filter['category']['field'] = 'any';
            $filter['category']['value'] = $filter_search;
        }
    
        $page_info['filter'] = $filter;

        //print_r_pre($filter);


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        $record_result = $this->category_model->get_paged_subcategories($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];
        //echo "<pre>";
        //print_r($records); die();
        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/category";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) 
        {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'SL', 'class' => 'center', 'width' => '100'),
                '1' => array('data'=> 'Category Title', 'class' => 'center', 'width' => '100'),
                '2' => array('data'=> 'Sub Category Title', 'class' => 'center', 'width' => '100'),
                '3' => array('data'=> 'Created By', 'class' => 'center', 'width' => '100'),
                '4' => array('data'=> 'Creation Time', 'class' => 'center', 'width' => '100'),
                '5' => array('data'=> 'Action', 'class' => 'center', 'width' => '80')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'=> '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'=> '</table>'
            );
            $this->table->set_template($tbl_template);
            $d=1;
            for ($i = 0; $i<count($records); $i++) {
                
                $noof_questions_str = 0;
                $noof_questions_used_str = '';//$this->survey_question_model->get_used_question_count($records[$i]->id);

                $htmlAction='';
                $htmlAction .='<form style="padding:0px important; margin: 0 0 0px;" action="'.site_url('administrator/category/modcat').'" method="post">';

                $htmlAction .='<input type="hidden" name="interface" value="1" />';
                $htmlAction .='<input type="hidden" name="cat_id" value="'. $records[$i]->id.'" />';
                $htmlAction .='<button type="submit" class="btn btn-info"><i class="icon-edit"></i></button>';
                
                $htmlAction .='</form>';


                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= $htmlAction;
                //$action_str .= anchor('administrator/category/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                
                $tbl_row = array(
                    '0' => array('data'=> $d, 'class' => 'center', 'width' => '100'),
                    '1' => array('data'=> $records[$i]->cat_parent_name, 'class' => 'center', 'width' => '100'),
                    '2' => array('data'=> $records[$i]->cat_name, 'class' => 'center', 'width' => '100'),
                    '3' => array('data'=> $records[$i]->created_by, 'class' => 'center', 'width' => '100'),
                    '4' => array('data'=> $records[$i]->created_at, 'class' => 'center', 'width' => '100'),
                    '5' => array('data'=> $action_str, 'class' => 'center', 'width' => '100', 'width' => '80')
                );
                $d++;
                $this->table->add_row($tbl_row);
                
            }

            $page_info['records_table'] = $this->table->generate();
            $page_info['pagin_links'] = $this->pagination->create_links();
        } 
        else 
        {
            $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
            $page_info['pagin_links'] = '';
        }
        
        // determine messages
        if ($this->session->flashdata('message_error')) 
        {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        if ($this->session->flashdata('message_success')) 
        {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        
        // load view
        $this->load->view('administrator/layouts/default', $page_info);
    }

    private function subTwoCategoryIndex()
    {

        //echo 1; die();
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Sub Categories'));
        // set page specific variables
        $page_info['title'] = 'Manage Sub Categories'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->userdata('filter_search')) {
            $filter_search = $this->session->userdata('filter_search');
            $this->form_data->filter_search = $filter_search;
            $filter['category']['field'] = 'any';
            $filter['category']['value'] = $filter_search;
        }
    
        $page_info['filter'] = $filter;

        //print_r_pre($filter);


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        $record_result = $this->category_model->get_paged_subTwocategories($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];
        //echo "<pre>";
        //print_r($records); die();
        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/category";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) 
        {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'SL', 'class' => 'center'),
                '1' => array('data'=> 'Category', 'class' => 'center'),
                '2' => array('data'=> 'Sub Category', 'class' => 'center', 'width' => '100'),
                '3' => array('data'=> 'Sub 2 Category', 'class' => 'center', 'width' => '100'),
                '4' => array('data'=> 'Created By', 'class' => 'center'),
                '5' => array('data'=> 'Creation Time', 'class' => 'center'),
                '6' => array('data'=> 'Action', 'class' => 'center')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'=> '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'=> '</table>'
            );
            $this->table->set_template($tbl_template);
            $d=1;
            for ($i = 0; $i<count($records); $i++) {
                
                $noof_questions_str = 0;
                $noof_questions_used_str = '';//$this->survey_question_model->get_used_question_count($records[$i]->id);

                $htmlAction='';
                $htmlAction .='<form style="padding:0px important; margin: 0 0 0px;" action="'.site_url('administrator/category/modcat').'" method="post">';

                $htmlAction .='<input type="hidden" name="interface" value="1" />';
                $htmlAction .='<input type="hidden" name="cat_id" value="'. $records[$i]->id.'" />';
                $htmlAction .='<button type="submit" class="btn btn-info"><i class="icon-edit"></i></button>';
                
                $htmlAction .='</form>';


                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= $htmlAction;
                //$action_str .= anchor('administrator/category/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                
                $tbl_row = array(
                    '0' => array('data'=> $d, 'class' => 'center', 'width' => '80'),
                    '1' => array('data'=> $records[$i]->cat_parent_name, 'class' => 'center', 'width' => '100'),
                    '2' => array('data'=> $records[$i]->sub_cat_parent_name, 'class' => 'center', 'width' => '100'),
                    '3' => array('data'=> $records[$i]->cat_name, 'class' => 'center', 'width' => '100'),
                    '4' => array('data'=> $records[$i]->created_by, 'class' => 'center', 'width' => '60'),
                    '5' => array('data'=> $records[$i]->created_at, 'class' => 'center', 'width' => '100'),
                    '6' => array('data'=> $action_str, 'class' => 'center', 'width' => '80')
                );
                $d++;
                $this->table->add_row($tbl_row);
                
            }

            $page_info['records_table'] = $this->table->generate();
            $page_info['pagin_links'] = $this->pagination->create_links();
        } 
        else 
        {
            $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
            $page_info['pagin_links'] = '';
        }
        
        // determine messages
        if ($this->session->flashdata('message_error')) 
        {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        if ($this->session->flashdata('message_success')) 
        {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        
        // load view
        $this->load->view('administrator/layouts/default', $page_info);
    }

    private function subThreeCategoryIndex()
    {

        //echo 1; die();
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Sub Categories'));
        // set page specific variables
        $page_info['title'] = 'Manage Sub Categories'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->userdata('filter_search')) {
            $filter_search = $this->session->userdata('filter_search');
            $this->form_data->filter_search = $filter_search;
            $filter['category']['field'] = 'any';
            $filter['category']['value'] = $filter_search;
        }
    
        $page_info['filter'] = $filter;

        //print_r_pre($filter);


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        $record_result = $this->category_model->get_pagedThreecategories($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];
        //echo "<pre>";
        //print_r($records); die();
        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/category";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) 
        {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'SL', 'class' => 'center'),
                '1' => array('data'=> 'Category', 'class' => 'center'),
                '2' => array('data'=> 'Sub Category', 'class' => 'center', 'width' => '100'),
                '3' => array('data'=> 'Sub 2 Category', 'class' => 'center', 'width' => '100'),
                '4' => array('data'=> 'Sub 3 Category', 'class' => 'center', 'width' => '100'),
                '5' => array('data'=> 'Created By', 'class' => 'center'),
                '6' => array('data'=> 'Creation Time', 'class' => 'center'),
                '7' => array('data'=> 'Action', 'class' => 'center')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'=> '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'=> '</table>'
            );
            $this->table->set_template($tbl_template);
            $d=1;
            for ($i = 0; $i<count($records); $i++) {
                
                $noof_questions_str = 0;
                $noof_questions_used_str = '';//$this->survey_question_model->get_used_question_count($records[$i]->id);

                $htmlAction='';
                $htmlAction .='<form style="padding:0px important; margin: 0 0 0px;" action="'.site_url('administrator/category/modcat').'" method="post">';

                $htmlAction .='<input type="hidden" name="interface" value="1" />';
                $htmlAction .='<input type="hidden" name="cat_id" value="'. $records[$i]->id.'" />';
                $htmlAction .='<button type="submit" class="btn btn-info"><i class="icon-edit"></i></button>';
                
                $htmlAction .='</form>';


                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= $htmlAction;
                //$action_str .= anchor('administrator/category/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                
                $tbl_row = array(
                    '0' => array('data'=> $d, 'class' => 'center', 'width' => '80'),
                    '1' => array('data'=> $records[$i]->cat_parent_name, 'class' => 'center', 'width' => '100'),
                    '2' => array('data'=> $records[$i]->sub_cat_parent_name, 'class' => 'center', 'width' => '100'),
                    '3' => array('data'=> $records[$i]->sub_two_cat_parent_name, 'class' => 'center', 'width' => '100'),
                    '4' => array('data'=> $records[$i]->cat_name, 'class' => 'center', 'width' => '100'),
                    '5' => array('data'=> $records[$i]->created_by, 'class' => 'center', 'width' => '60'),
                    '6' => array('data'=> $records[$i]->created_at, 'class' => 'center', 'width' => '100'),
                    '7' => array('data'=> $action_str, 'class' => 'center', 'width' => '80')
                );
                $d++;
                $this->table->add_row($tbl_row);
                
            }

            $page_info['records_table'] = $this->table->generate();
            $page_info['pagin_links'] = $this->pagination->create_links();
        } 
        else 
        {
            $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
            $page_info['pagin_links'] = '';
        }
        
        // determine messages
        if ($this->session->flashdata('message_error')) 
        {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        if ($this->session->flashdata('message_success')) 
        {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        
        // load view
        $this->load->view('administrator/layouts/default', $page_info);
    }

    private function subFourCategoryIndex()
    {

        //echo 1; die();
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Sub Categories'));
        // set page specific variables
        $page_info['title'] = 'Manage Sub Categories'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->userdata('filter_search')) {
            $filter_search = $this->session->userdata('filter_search');
            $this->form_data->filter_search = $filter_search;
            $filter['category']['field'] = 'any';
            $filter['category']['value'] = $filter_search;
        }
    
        $page_info['filter'] = $filter;

        //print_r_pre($filter);


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        $record_result = $this->category_model->get_pagedFourcategories($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];
        //echo "<pre>";
        //print_r($records); die();
        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/category";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) 
        {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'SL', 'class' => 'center'),
                '1' => array('data'=> 'Category', 'class' => 'center'),
                '2' => array('data'=> 'Sub Category', 'class' => 'center', 'width' => '100'),
                '3' => array('data'=> 'Sub 2 Category', 'class' => 'center', 'width' => '100'),
                '4' => array('data'=> 'Sub 3 Category', 'class' => 'center', 'width' => '100'),
                '5' => array('data'=> 'Sub 4 Category', 'class' => 'center', 'width' => '100'),
                '6' => array('data'=> 'Created By', 'class' => 'center'),
                '7' => array('data'=> 'Creation Time', 'class' => 'center'),
                '8' => array('data'=> 'Action', 'class' => 'center')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'=> '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'=> '</table>'
            );
            $this->table->set_template($tbl_template);
            $d=1;
            for ($i = 0; $i<count($records); $i++) {
                
                $noof_questions_str = 0;
                $noof_questions_used_str = '';//$this->survey_question_model->get_used_question_count($records[$i]->id);

                $htmlAction='';
                $htmlAction .='<form style="padding:0px important; margin: 0 0 0px;" action="'.site_url('administrator/category/modcat').'" method="post">';

                $htmlAction .='<input type="hidden" name="interface" value="1" />';
                $htmlAction .='<input type="hidden" name="cat_id" value="'. $records[$i]->id.'" />';
                $htmlAction .='<button type="submit" class="btn btn-info"><i class="icon-edit"></i></button>';
                
                $htmlAction .='</form>';


                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= $htmlAction;
                //$action_str .= anchor('administrator/category/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                
                $tbl_row = array(
                    '0' => array('data'=> $d, 'class' => 'center', 'width' => '80'),
                    '1' => array('data'=> $records[$i]->cat_parent_name, 'class' => 'center', 'width' => '100'),
                    '2' => array('data'=> $records[$i]->sub_cat_parent_name, 'class' => 'center', 'width' => '100'),
                    '3' => array('data'=> $records[$i]->sub_two_cat_parent_name, 'class' => 'center', 'width' => '100'),
                    '4' => array('data'=> $records[$i]->sub_three_cat_parent_name, 'class' => 'center', 'width' => '100'),
                    '5' => array('data'=> $records[$i]->cat_name, 'class' => 'center', 'width' => '100'),
                    '6' => array('data'=> $records[$i]->created_by, 'class' => 'center', 'width' => '60'),
                    '7' => array('data'=> $records[$i]->created_at, 'class' => 'center', 'width' => '100'),
                    '8' => array('data'=> $action_str, 'class' => 'center', 'width' => '80')
                );
                $d++;
                $this->table->add_row($tbl_row);
                
            }

            $page_info['records_table'] = $this->table->generate();
            $page_info['pagin_links'] = $this->pagination->create_links();
        } 
        else 
        {
            $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
            $page_info['pagin_links'] = '';
        }
        
        // determine messages
        if ($this->session->flashdata('message_error')) 
        {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        if ($this->session->flashdata('message_success')) 
        {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        
        // load view
        $this->load->view('administrator/layouts/default', $page_info);
    }

    public function index()
    {

        if(!empty($this->session->userdata('tab_cat')))
        {
            $this->categoryIndex();
        }
        elseif(!empty($this->session->userdata('tab_subcat')))
        {
            $this->subCategoryIndex();
        }
        elseif(!empty($this->session->userdata('tab_subcat_two')))
        {
            $this->subTwoCategoryIndex();
        }
        elseif(!empty($this->session->userdata('tab_subcat_three')))
        {
            $this->subThreeCategoryIndex();
        }
        elseif(!empty($this->session->userdata('tab_subcat_four')))
        {
            $this->subFourCategoryIndex();
        }
        else
        {
            $this->activeTab(1);
            $this->categoryIndex();
        }

        
    }

    public function filter()
    {

        //print_r_pre($this->session->all_userdata());

        if(!empty($this->session->userdata('tab_cat')))
        {
            $filter_search = $this->input->post('search');
            $filter_clear = $this->input->post('clear');

            if ($filter_clear == '') {
                if ($filter_search != '') {
                    $this->session->set_userdata('filter_search', $filter_search);
                }
            } else {
                $this->session->unset_userdata('filter_search');
            }

            redirect('administrator/category');
        }
        elseif(!empty($this->session->userdata('tab_subcat')))
        {
            $filter_search = $this->input->post('search');
            $filter_clear = $this->input->post('clear');

            if ($filter_clear == '') {
                if ($filter_search != '') {
                    $this->session->set_userdata('filter_search', $filter_search);
                }
            } else {
                $this->session->unset_userdata('filter_search');
            }

            redirect('administrator/category');
        }
        elseif(!empty($this->session->userdata('tab_subcat_two')))
        {
            $filter_search = $this->input->post('search');
            $filter_clear = $this->input->post('clear');

            if ($filter_clear == '') {
                if ($filter_search != '') {
                    $this->session->set_userdata('filter_search', $filter_search);
                }
            } else {
                $this->session->unset_userdata('filter_search');
            }

            redirect('administrator/category');
        }
        elseif(!empty($this->session->userdata('tab_subcat_three')))
        {
            $page_info['page_active_tab'] = '4';
        }
        elseif(!empty($this->session->userdata('tab_subcat_four')))
        {
            $page_info['page_active_tab'] = '5';
        }
        else
        {
            $page_info['page_active_tab'] = '1';
        }


        
    }

    /**
     * Display add category form
     * @return void
     */


    public function activeTab($tabID,$pageInfo='')
    {
        if(!empty($tabID))
        {
            $this->session->unset_userdata('tab_cat');
            $this->session->unset_userdata('tab_subcat');
            $this->session->unset_userdata('tab_subcat_two');
            $this->session->unset_userdata('tab_subcat_three');
            $this->session->unset_userdata('tab_subcat_four');

            if($tabID==1)
            {
                $this->session->set_userdata('tab_cat',true);
            }
            elseif($tabID==2)
            {
                $this->session->set_userdata('tab_subcat',true);
            }
            elseif($tabID==3)
            {
                $this->session->set_userdata('tab_subcat_two',true);
            }
            elseif($tabID==4)
            {
                $this->session->set_userdata('tab_subcat_three',true);
            }
            elseif($tabID==5)
            {
                $this->session->set_userdata('tab_subcat_four',true);
            }
            else
            {
                $this->session->set_userdata('tab_cat',true);
            }

            
        }
        

        return 1;
        
    }

    public function modcat()
    {
        $cat_name = $this->input->post('interface');
        $cat_id = $this->input->post('cat_id');
        $this->activeTab($cat_name);

        return redirect(site_url('administrator/category/edit/'. $cat_id));

    }

    public function add()
    {

        $page_info['cat'] = 1;
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Category View'));
        // set page specific variables
        $page_info['title'] = 'Add New Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;


        $getAllCategoryInfo=$this->Select_global_model->FlyQuery(array(),'exm_categories','select','*');

        //print_r_pre($getAllCategoryInfo);

        $page_info['categoryData'] = $getAllCategoryInfo;

        $this->_set_fields();
        $this->_set_rules();

        // determine messages
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }

       // print_r_pre($page_info);


        // load view
		$this->load->view('administrator/layouts/default', $page_info);
    }

    public function add_category()
    {

        $this->activeTab(1,$page_info);

        $getAllCategoryInfo=$this->Select_global_model->FlyQuery(array(),'exm_categories','select','*');

        //print_r_pre($getAllCategoryInfo);

        $page_info['categoryData'] = $getAllCategoryInfo;

        
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Category'));
        $page_info['title'] = 'Add New Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_form_view';
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
                //$cat_parent = (int)$this->input->post('cat_parent');

            $ex=$this->Select_global_model->FlyQuery(array('cat_name'=>$this->input->post('cat_name')),'exm_categories','count');

            if($ex==0)
           {


                $data = array(
                    'cat_name' => $cat_name,
                    'created_by' =>loggedUserData('id')
                );

                $res = (int)$this->category_model->add_category($data);
    //print_r('dd'); die();
                if ($res > 0) {
                    $this->session->set_flashdata('message_success', 'Category Added successfully.');
                    redirect('administrator/category/add');
                } else {
                    $page_info['message_error'] = 'Add is unsuccessful.';
                    $this->load->view('administrator/layouts/default', $page_info);
                }
           }
           else
           {
                $page_info['message_error'] = '( '.$this->input->post('cat_name').' ) is already exists.';
                $this->load->view('administrator/layouts/default', $page_info);
           }
            


        }
    }

    public function load_category()
    {
        $getAllCategoryInfo=$this->Select_global_model->FlyQuery(array(),'exm_categories','select','*');
        header('Content-Type: application/json');
        echo json_encode($getAllCategoryInfo);
    }

    public function add_sub_category()
    {

        $getAllCategoryInfo=$this->Select_global_model->FlyQuery(array(),'exm_sub_categories','select','*');

        //print_r_pre($getAllCategoryInfo);

        $page_info['categoryData'] = $getAllCategoryInfo;

        
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Sub Category'));
        $page_info['title'] = 'Add New Sub Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['subcat'] = 1;
        $page_info['is_edit'] = false;

        $this->activeTab(2,$page_info);

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

                $cat_name = $this->input->post('cat_name');
                $cat_parent = $this->input->post('cat_parent');
                //$cat_parent = (int)$this->input->post('cat_parent');

            $ex=$this->Select_global_model->FlyQuery(array('cat_name'=>$cat_name,'cat_parent'=>$cat_parent),'exm_sub_categories','count');

            if($ex==0)
           {


                $data = array(
                    'cat_name' => $cat_name,
                    'cat_parent' => $cat_parent,
                    'created_by' =>loggedUserData('id')
                );

                $res = (int)$this->category_model->add_sub_category($data);
    //print_r('dd'); die();
                if ($res > 0) {
                    $this->session->set_flashdata('message_success', 'Sub Category Added successfully.');
                    redirect('administrator/category/add');
                } else {
                    $page_info['message_error'] = 'Add is unsuccessful.';
                    $this->load->view('administrator/layouts/default', $page_info);
                }
           }
           else
           {
                $page_info['message_error'] = '( '.$this->input->post('cat_name').' ) is already exists.';
                $this->load->view('administrator/layouts/default', $page_info);
           }
            


        }
    }

    public function load_sub_category()
    {
        $getAllCategoryInfo=$this->Select_global_model->FlyQuery(array(),'exm_sub_categories','select','*');
        header('Content-Type: application/json');
        echo json_encode($getAllCategoryInfo);
    }

    public function add_sub_two_category()
    {

        $getAllCategoryInfo=$this->Select_global_model->FlyQuery(array(),'exm_sub_two_categories','select','*');

        //print_r_pre($getAllCategoryInfo);

        $page_info['categoryData'] = $getAllCategoryInfo;

        
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Sub Category'));
        $page_info['title'] = 'Add New Sub Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['subcat_two'] = 1;
        $page_info['is_edit'] = false;

        $this->activeTab(3,$page_info);

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

                $cat_name = $this->input->post('cat_name');
                $sub_cat_parent = $this->input->post('sub_cat_parent');
                $cat_parent = $this->input->post('cat_parent');
                //$cat_parent = (int)$this->input->post('cat_parent');

            $ex=$this->Select_global_model->FlyQuery(array('cat_name'=>$cat_name,'cat_parent'=>$cat_parent,'sub_cat_parent'=>$sub_cat_parent),'exm_sub_two_categories','count');

            if($ex==0)
           {


                $data = array(
                    'cat_name' => $cat_name,
                    'cat_parent' => $cat_parent,
                    'sub_cat_parent' => $sub_cat_parent,
                    'created_by' =>loggedUserData('id')
                );

                $res = (int)$this->category_model->add_sub_two_category($data);
    //print_r('dd'); die();
                if ($res > 0) {
                    $this->session->set_flashdata('message_success', 'Sub 2 Category Added successfully.');
                    redirect('administrator/category/add');
                } else {
                    $page_info['message_error'] = 'Add is unsuccessful.';
                    $this->load->view('administrator/layouts/default', $page_info);
                }
           }
           else
           {
                $page_info['message_error'] = '( '.$this->input->post('cat_name').' ) is already exists.';
                $this->load->view('administrator/layouts/default', $page_info);
           }
            


        }
    }

    public function load_sub_two_category()
    {
        $getAllCategoryInfo=$this->Select_global_model->FlyQuery(array(),'exm_sub_two_categories','select','*');
        header('Content-Type: application/json');
        echo json_encode($getAllCategoryInfo);
    }

    public function add_sub_three_category()
    {

        $getAllCategoryInfo=$this->Select_global_model->FlyQuery(array(),'exm_sub_three_categories','select','*');

        //print_r_pre($getAllCategoryInfo);

        $page_info['categoryData'] = $getAllCategoryInfo;

        
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Sub Category'));
        $page_info['title'] = 'Add New Sub Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['subcat_two'] = 1;
        $page_info['is_edit'] = false;

        $this->activeTab(4,$page_info);

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

                $cat_name = $this->input->post('cat_name');
                $sub_cat_parent = $this->input->post('sub_cat_parent');
                $sub_two_cat_parent = $this->input->post('sub_two_cat_parent');
                $cat_parent = $this->input->post('cat_parent');
                //$cat_parent = (int)$this->input->post('cat_parent');

            $ex=$this->Select_global_model->FlyQuery(array('cat_name'=>$cat_name,'cat_parent'=>$cat_parent,'sub_cat_parent'=>$sub_cat_parent,'sub_two_cat_parent'=>$sub_two_cat_parent),'exm_sub_three_categories','count');

            if($ex==0)
           {


                $data = array(
                    'cat_name' => $cat_name,
                    'cat_parent' => $cat_parent,
                    'sub_cat_parent' => $sub_cat_parent,
                    'sub_two_cat_parent' => $sub_two_cat_parent,
                    'created_by' =>loggedUserData('id')
                );

                $res = (int)$this->category_model->add_sub_three_category($data);
    //print_r('dd'); die();
                if ($res > 0) {
                    $this->session->set_flashdata('message_success', 'Sub 3 Category Added successfully.');
                    redirect('administrator/category/add');
                } else {
                    $page_info['message_error'] = 'Add is unsuccessful.';
                    $this->load->view('administrator/layouts/default', $page_info);
                }
           }
           else
           {
                $page_info['message_error'] = '( '.$this->input->post('cat_name').' ) is already exists.';
                $this->load->view('administrator/layouts/default', $page_info);
           }
            


        }
    }

    public function load_sub_three_category()
    {
        $getAllCategoryInfo=$this->Select_global_model->FlyQuery(array(),'exm_sub_three_categories','select','*');
        header('Content-Type: application/json');
        echo json_encode($getAllCategoryInfo);
    }

    public function add_sub_four_category()
    {

        $getAllCategoryInfo=$this->Select_global_model->FlyQuery(array(),'exm_sub_four_categories','select','*');

        //print_r_pre($getAllCategoryInfo);

        $page_info['categoryData'] = $getAllCategoryInfo;

        
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Sub Category'));
        $page_info['title'] = 'Add New Sub Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['subcat_two'] = 1;
        $page_info['is_edit'] = false;

       $this->activeTab(5,$page_info);

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

                $cat_name = $this->input->post('cat_name');
                $sub_cat_parent = $this->input->post('sub_cat_parent');
                $sub_two_cat_parent = $this->input->post('sub_two_cat_parent');
                $sub_three_cat_parent = $this->input->post('sub_three_cat_parent');
                $cat_parent = $this->input->post('cat_parent');
                //$cat_parent = (int)$this->input->post('cat_parent');

            $ex=$this->Select_global_model->FlyQuery(array('cat_name'=>$cat_name,'cat_parent'=>$cat_parent,'sub_cat_parent'=>$sub_cat_parent,'sub_two_cat_parent'=>$sub_two_cat_parent,'sub_three_cat_parent'=>$sub_three_cat_parent),'exm_sub_four_categories','count');

            if($ex==0)
           {


                $data = array(
                    'cat_name' => $cat_name,
                    'cat_parent' => $cat_parent,
                    'sub_cat_parent' => $sub_cat_parent,
                    'sub_two_cat_parent' => $sub_two_cat_parent,
                    'sub_three_cat_parent' => $sub_three_cat_parent,
                    'created_by' =>loggedUserData('id')
                );

                $res = (int)$this->category_model->add_sub_four_category($data);
    //print_r('dd'); die();
                if ($res > 0) {
                    $this->session->set_flashdata('message_success', 'Sub 4 Category Added successfully.');
                    redirect('administrator/category/add');
                } else {
                    $page_info['message_error'] = 'Add is unsuccessful.';
                    $this->load->view('administrator/layouts/default', $page_info);
                }
           }
           else
           {
                $page_info['message_error'] = '( '.$this->input->post('cat_name').' ) is already exists.';
                $this->load->view('administrator/layouts/default', $page_info);
           }
            


        }
    }

    public function load_sub_four_category()
    {
        $getAllCategoryInfo=$this->Select_global_model->FlyQuery(array(),'exm_sub_three_categories','select','*');
        header('Content-Type: application/json');
        echo json_encode($getAllCategoryInfo);
    }


    public function edit()
    {


        if(!empty($this->session->userdata('tab_cat')))
        {
           $activityName="Edit Category ";
           $cat_id = (int)$this->uri->segment(4);
           $category = $this->category_model->get_category($cat_id);
           //print_r_pre($category);
           $this->form_data->cat_id = $category->id;
           $this->form_data->cat_name = $category->cat_name;
        }
        elseif(!empty($this->session->userdata('tab_subcat')))
        {
            //$this->categoryIndex();
        }
        elseif(!empty($this->session->userdata('tab_subcat_two')))
        {
            $page_info['page_active_tab'] = '3';
        }
        elseif(!empty($this->session->userdata('tab_subcat_three')))
        {
            $page_info['page_active_tab'] = '4';
        }
        elseif(!empty($this->session->userdata('tab_subcat_four')))
        {
            $page_info['page_active_tab'] = '5';
        }
        else
        {
            $page_info['page_active_tab'] = '1';
        }

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>$activityName));
        // set page specific variables

        $page_info['title'] = $activityName.' '. $this->site_name;
        $page_info['view_page'] = 'administrator/category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        // prefill form values
        

        if ($this->session->flashdata('message_success'))
        {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        if ($this->session->flashdata('message_error'))
        {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        //print_r_pre();

        // load view
		$this->load->view('administrator/layouts/default', $page_info);
    }

    public function update_category()
    {




        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update Category'));
        // set page specific variables
        $page_info['title'] = 'Edit Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        if(!empty($this->session->userdata('tab_cat')))
        {
            $cat_id = (int)$this->input->post('cat_id');
            $cat_name = $this->input->post('cat_name');


            $this->_set_fields();
            $this->_set_rules(1);

            if ($this->form_validation->run() == FALSE) {

                $this->form_data->cat_id = $cat_id;
                $this->load->view('administrator/layouts/default', $page_info);

            } else {

                $data = array(
                    'cat_name' => $cat_name,
                    'created_by' =>loggedUserData('id')
                );

                if ($this->category_model->update_category($cat_id, $data)) {
                    $this->session->set_flashdata('message_success', 'Update is successful.');
                } else  {
                    $this->session->set_flashdata('message_error', $this->category_model->error_message. ' Update is unsuccessful.');
                }

                redirect('administrator/category/edit/'. $cat_id);
            }
        }
        elseif(!empty($this->session->userdata('tab_subcat')))
        {
            //$this->categoryIndex();
        }
        elseif(!empty($this->session->userdata('tab_subcat_two')))
        {
            $page_info['page_active_tab'] = '3';
        }
        elseif(!empty($this->session->userdata('tab_subcat_three')))
        {
            $page_info['page_active_tab'] = '4';
        }
        elseif(!empty($this->session->userdata('tab_subcat_four')))
        {
            $page_info['page_active_tab'] = '5';
        }
        else
        {
            $page_info['page_active_tab'] = '1';
        }

        
    }

    /**
     * Delete a category
     * @return void
     */
    public function delete()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Delete Category'));
        $cat_id = (int)$this->uri->segment(4);
        $res = $this->category_model->delete_category($cat_id);
       // print_r($res); die();
        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Delete is successful.');
        } else {
            $this->session->set_flashdata('message_error', $this->category_model->error_message .' Delete is unsuccessful.');
        }
        
        redirect('administrator/category');
    }


    // set empty default form field values
	private function _set_fields()
	{
        
		$this->form_data->cat_id = 0;
        $this->form_data->cat_parent = 0;
		$this->form_data->cat_name = '';

        $this->form_data->filter_cat_name = '';
		$this->form_data->filter_search = '';
	}

	// validation rules
	private function _set_rules($param=0)
	{
        if(empty($param))
        {
            $this->form_validation->set_rules('cat_name', 'Category Name', 'required|trim|xss_clean|strip_tags');
            $this->form_validation->set_rules('cat_id', 'Category ID', 'trim|xss_clean|strip_tags');
        }
        elseif($param==1)
        {
            $this->form_validation->set_rules('cat_name', 'Category Name', 'required|trim|xss_clean|strip_tags');
            $this->form_validation->set_rules('cat_id', 'Category ID', 'trim|xss_clean|strip_tags');
        }
		
		//$this->form_validation->set_rules('cat_parent', 'Parent Category', 'trim|xss_clean|strip_tags');
	}

}

/* End of file category.php */
/* Location: ./application/controllers/administrator/category.php */