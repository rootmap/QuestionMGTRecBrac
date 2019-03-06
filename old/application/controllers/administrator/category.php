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

    public function index()
	{
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Categories View'));
        // set page specific variables
        $page_info['title'] = 'Manage Categories'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_list_view';
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
            $record_result = $this->category_model->get_paged_categories($per_page, $page_offset, $filter);
        } else {
            $record_result = $this->category_model->get_padded_paged_categories($per_page, $page_offset);
        }
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];
        //print_r_pre($records);die;

        //print_r_pre($this->db->last_query());die;




        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/category";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        // get default category id
        $default_cat_id = (int)$this->global_options['default_category'];

        if ($records) {
            $max_column=0;
            for ($i = 0; $i<count($records); $i++) {
                 //echo 'hi'; die();
                $strFindANdReplace=explode("&mdash;", $records[$i]->cat_name);
                if($max_column<(count($strFindANdReplace)-1))
                {
                    $max_column=(count($strFindANdReplace)-1);
                }
            }

           // if(!isset($filter_cat_name) || trim($filter_cat_name) === '')
                   // {
                       
                     //   $max_column = $max_column;
                    //}
                   // else
                     // {
                      //  $max_column = 3;
                      //}

            //echo $max_column; die();

            // customize and generate records table
            if($max_column==0)
            {
                $tbl_heading = array(
                    '0' => array('data'=> 'Category Id'),
                    '1' => array('data'=> 'Name'),
                    '2' => array('data'=> 'No. of Questions', 'class' => 'center', 'width' => '120'),
                    '3' => array('data'=> 'Creator Name', 'class' => 'center', 'width' => '100'),
                    '4' => array('data'=> 'Date', 'class' => 'center', 'width' => '100'),
                    '5' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
                );
            }
            elseif($max_column==1)
            {
                $tbl_heading = array(
                    '0' => array('data'=> 'Category Id'),
                    '1' => array('data'=> 'Name'),
                    '2' => array('data'=> 'Subcategory Name'),
                    '3' => array('data'=> 'No. of Questions', 'class' => 'center', 'width' => '120'),
                    '4' => array('data'=> 'Creator Name', 'class' => 'center', 'width' => '100'),
                    '5' => array('data'=> 'Date', 'class' => 'center', 'width' => '100'),
                    '6' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
                );
            }
            elseif($max_column==2)
            {
                $tbl_heading = array(
                    '0' => array('data'=> 'Category Id'),
                    '1' => array('data'=> 'Name'),
                    '2' => array('data'=> 'Subcategory Name'),
                    '3' => array('data'=> 'Sub-Subcategory Name'),
                    '4' => array('data'=> 'No. of Questions', 'class' => 'center', 'width' => '120'),
                    '5' => array('data'=> 'Creator Name', 'class' => 'center', 'width' => '100'),
                    '6' => array('data'=> 'Date', 'class' => 'center', 'width' => '100'),
                    '7' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
                );
            }
            elseif($max_column==3)
            {
                $tbl_heading = array(
                    '0' => array('data'=> 'Category Id'),
                    '1' => array('data'=> 'Name'),
                    '2' => array('data'=> 'Subcategory Name'),
                    '3' => array('data'=> 'Sub-Subcategory Name'),
                    '4' => array('data'=> 'Sub Sub-Subcategory Name'),
                    '5' => array('data'=> 'No. of Questions', 'class' => 'center', 'width' => '120'),
                    '6' => array('data'=> 'Creator Name', 'class' => 'center', 'width' => '100'),
                    '7' => array('data'=> 'Date', 'class' => 'center', 'width' => '100'),
                    '8' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
                );
            }
            else
            {
                $tbl_heading = array(
                    '0' => array('data'=> 'Category Id'),
                    '1' => array('data'=> 'Name'),
                    '2' => array('data'=> 'No. of Questions', 'class' => 'center', 'width' => '120'),
                    '3' => array('data'=> 'Creator Name', 'class' => 'center', 'width' => '100'),
                    '4' => array('data'=> 'Date', 'class' => 'center', 'width' => '100'),
                    '5' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
                );
            }
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="category_table" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {

                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= '<a href="category/edit/'. $records[$i]->id.'"><i class="icon-edit"></i></a>';

                /*if ($default_cat_id != $records[$i]->id) {
                    $action_str .= '&nbsp;&nbsp;&nbsp;';
                    if(!isSystemAuditor())
                    $action_str .= '<a onclick="return confirm(\'Do you really want to delete this record?\')" href="category/delete/'. $records[$i]->id.'"><i class="icon-trash"></i></a>';
                }*/

                $no_of_questions = (int)$this->category_model->get_question_count($records[$i]->id);



                



                if($max_column==0)
                {
                    $tbl_row = array(
                        '0' => array('data'=>$records[$i]->id),
                        '1' => array('data'=>$records[$i]->cat_name),
                        '2' => array('data'=> $no_of_questions, 'class' => 'center'),
                        '3' => array('data'=>$records[$i]->created_by, 'class' => 'center'),
                        '4' => array('data'=> $records[$i]->created_at, 'class' => 'center'),
                        '5' => array('data'=> $action_str, 'class' => 'center','width' => '120')
                    );
                }
                elseif($max_column==1)
                {
                    $strFindANdReplace=explode("&mdash;", $records[$i]->cat_name);
                    $LayerDefine=(count($strFindANdReplace)-1);
                    if($LayerDefine==0)
                    {
                        $tbl_row = array(
                            '0' => array('data'=>$records[$i]->id),
                            '1' => array('data'=>$records[$i]->cat_name),
                            '2' => array('data'=>''),
                            '3' => array('data'=> $no_of_questions, 'class' => 'center'),
                            '4' => array('data'=>$records[$i]->created_by, 'class' => 'center'),
                            '5' => array('data'=> $records[$i]->created_at, 'class' => 'center'),
                            '6' => array('data'=> $action_str, 'class' => 'center','width' => '120')
                        );
                    }
                    elseif($LayerDefine==1)
                    {
                        $tbl_row = array(
                            '0' => array('data'=>$records[$i]->id),
                            '1' => array('data'=>''),
                            '2' => array('data'=>$this->removeDashFromString($records[$i]->cat_name)),
                            '3' => array('data'=> $no_of_questions, 'class' => 'center'),
                            '4' => array('data'=>$records[$i]->created_by, 'class' => 'center'),
                            '5' => array('data'=> $records[$i]->created_at, 'class' => 'center'),
                            '6' => array('data'=> $action_str, 'class' => 'center','width' => '120')
                        );
                    }
                }
                elseif($max_column==2)
                {
                    $strFindANdReplace=explode("&mdash;", $records[$i]->cat_name);
                    $LayerDefine=(count($strFindANdReplace)-1);
                    if($LayerDefine==0)
                    {
                        $tbl_row = array(
                            '0' => array('data'=>$records[$i]->id),
                            '1' => array('data'=>$records[$i]->cat_name),
                            '2' => array('data'=>''),
                            '3' => array('data'=>''),
                            '4' => array('data'=> $no_of_questions, 'class' => 'center'),
                            '5' => array('data'=>$records[$i]->created_by, 'class' => 'center'),
                            '6' => array('data'=> $records[$i]->created_at, 'class' => 'center'),
                            '7' => array('data'=> $action_str, 'class' => 'center','width' => '120')
                        );
                    }
                    elseif($LayerDefine==1)
                    {
                        $tbl_row = array(
                            '0' => array('data'=>$records[$i]->id),
                            '1' => array('data'=>''),
                            '2' => array('data'=>$this->removeDashFromString($records[$i]->cat_name)),
                            '3' => array('data'=>''),
                            '4' => array('data'=> $no_of_questions, 'class' => 'center'),
                            '5' => array('data'=>$records[$i]->created_by, 'class' => 'center'),
                            '6' => array('data'=> $records[$i]->created_at, 'class' => 'center'),
                            '7' => array('data'=> $action_str, 'class' => 'center','width' => '120')
                        );
                    }
                    elseif($LayerDefine==2)
                    {
                        $tbl_row = array(
                            '0' => array('data'=>$records[$i]->id),
                            '1' => array('data'=>''),
                            '2' => array('data'=>''),
                            '3' => array('data'=>$this->removeDashFromString($records[$i]->cat_name)),
                            '4' => array('data'=> $no_of_questions, 'class' => 'center'),
                            '5' => array('data'=>$records[$i]->created_by, 'class' => 'center'),
                            '6' => array('data'=> $records[$i]->created_at, 'class' => 'center'),
                            '7' => array('data'=> $action_str, 'class' => 'center','width' => '120')
                        );
                    }

                }
                elseif($max_column==3)
                {
                    $strFindANdReplace=explode("&mdash;", $records[$i]->cat_name);
                    $LayerDefine=(count($strFindANdReplace)-1);
                    
                    if($LayerDefine==0)
                    {
                        $this->session->unset_userdata('first_data');
                        $this->session->set_userdata('first_data', $this->removeDashFromString($records[$i]->cat_name));
  
                        $tbl_row = array(
                            '0' => array('data'=>$records[$i]->id),
                            '1' => array('data'=>$records[$i]->cat_name),
                            '2' => array('data'=>''),
                            '3' => array('data'=>''),
                            '4' => array('data'=>''),
                            '5' => array('data'=> $no_of_questions, 'class' => 'center'),
                            '6' => array('data'=>$records[$i]->created_by, 'class' => 'center'),
                            '7' => array('data'=> $records[$i]->created_at, 'class' => 'center'),
                            '8' => array('data'=> $action_str, 'class' => 'center','width' => '120')
                        );
                    }
                    elseif($LayerDefine==1)
                    {
                        $this->session->unset_userdata('second_data');
                        $this->session->set_userdata('second_data', $this->removeDashFromString($records[$i]->cat_name));
                        $firstLevelGet=$this->session->userdata('first_data');
                        $tbl_row = array(
                            '0' => array('data'=>$records[$i]->id),
                            '1' => array('data'=>$firstLevelGet),
                            '2' => array('data'=>$this->removeDashFromString($records[$i]->cat_name)),
                            '3' => array('data'=>''),
                            '4' => array('data'=>''),
                            '5' => array('data'=> $no_of_questions, 'class' => 'center'),
                            '6' => array('data'=>$records[$i]->created_by, 'class' => 'center'),
                            '7' => array('data'=> $records[$i]->created_at, 'class' => 'center'),
                            '8' => array('data'=> $action_str, 'class' => 'center','width' => '120')
                        );
                    }
                    elseif($LayerDefine==2)
                    {
                        $this->session->unset_userdata('third_data');
                        $this->session->set_userdata('third_data',$this->removeDashFromString($records[$i]->cat_name));
                        $firstLevelGet=$this->session->userdata('first_data');
                        $secondLevelGet=$this->session->userdata('second_data');
                        $tbl_row = array(
                            '0' => array('data'=>$records[$i]->id),
                            '1' => array('data'=>$firstLevelGet),
                            '2' => array('data'=>$secondLevelGet),
                            '3' => array('data'=>$this->removeDashFromString($records[$i]->cat_name)),
                            '4' => array('data'=>''),
                            '5' => array('data'=> $no_of_questions, 'class' => 'center'),
                            '6' => array('data'=>$records[$i]->created_by, 'class' => 'center'),
                            '7' => array('data'=> $records[$i]->created_at, 'class' => 'center'),
                            '8' => array('data'=> $action_str, 'class' => 'center','width' => '120')
                        );
                    }
                    elseif($LayerDefine==3)
                    {
                        $this->session->unset_userdata('fourth_data');
                        $this->session->set_userdata('fourth_data',$this->removeDashFromString($records[$i]->cat_name));
                        $firstLevelGet=$this->session->userdata('first_data');
                        $secondLevelGet=$this->session->userdata('second_data');
                        $thirdLevelGet=$this->session->userdata('third_data');
                        $tbl_row = array(
                            '0' => array('data'=>$records[$i]->id),
                            '1' => array('data'=>$firstLevelGet),
                            '2' => array('data'=>$secondLevelGet),
                            '3' => array('data'=>$thirdLevelGet),
                            '4' => array('data'=>$this->removeDashFromString($records[$i]->cat_name)),
                            '5' => array('data'=> $no_of_questions, 'class' => 'center'),
                            '6' => array('data'=>$records[$i]->created_by, 'class' => 'center'),
                            '7' => array('data'=> $records[$i]->created_at, 'class' => 'center'),
                            '8' => array('data'=> $action_str, 'class' => 'center','width' => '120')
                        );
                    }
                }
                else
                {
                    $tbl_row = array(
                        '0' => array('data'=>$records[$i]->id),
                        '1' => array('data'=> $arrayDataROw),
                        '2' => array('data'=> $no_of_questions, 'class' => 'center'),
                        '3' => array('data'=>$records[$i]->created_by, 'class' => 'center'),
                        '4' => array('data'=> $records[$i]->created_at, 'class' => 'center'),
                        '5' => array('data'=> $action_str, 'class' => 'center','width' => '120')
                    );
                }

                /*$tbl_row = array(
                    '0' => $arrayDataROw,
                    '1' => array('data'=> $no_of_questions, 'class' => 'center'),
                    '2' => array('data'=> $action_str, 'class' => 'center', 'width' => '100', 'width' => '120')
                );*/
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

        redirect('administrator/category');
    }

    /**
     * Display add category form
     * @return void
     */
    public function add()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New Category View'));
        // set page specific variables
        $page_info['title'] = 'Add New Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_form_view';
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

            $ex=$this->Select_global_model->FlyQuery(array('cat_name'=>$this->input->post('cat_name'),'cat_parent'=>0),'exm_categories','count');

           if($ex==0)
           {
                $cat_name = $this->input->post('cat_name');
                $cat_parent = (int)$this->input->post('cat_parent');

                $data = array(
                    'cat_name' => $cat_name,
                    'cat_parent' => $cat_parent,
                    'created_by' =>loggedUserData('id')

                );

                $res = (int)$this->category_model->add_category($data);
    //print_r('dd'); die();
                if ($res > 0) {
                    $this->session->set_flashdata('message_success', 'Add is successful.');
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

    public function edit()
    {


        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Category View'));
        // set page specific variables

        $page_info['title'] = 'Edit Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        // prefill form values
        $cat_id = (int)$this->uri->segment(4);
		$category = $this->category_model->get_category($cat_id);

        if (count($this->cat_list) > 0) {
            foreach($this->cat_list as $key => $value) {
                if ($key == $cat_id) {
                    unset($this->cat_list[$key]);
                    break;
                }
            }
        }

        $this->_set_rules();



		$this->form_data->cat_id = $category->id;
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
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update Category'));
        // set page specific variables
        $page_info['title'] = 'Edit Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/category_form_view';
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
                'cat_parent' => $cat_parent,
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