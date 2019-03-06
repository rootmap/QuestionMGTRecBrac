<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class SMSnEMail extends MY_Controller
{
    var $current_page = "smsnemail";
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
        $this->load->model('smsnemail_model');
        $this->load->model('global/insert_global_model');
        $this->load->model('global/select_global_model');



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
     * Display paginated list of categories
     * @return void
     */
    public function index()
	{
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage SMS & Email Category'));
        // set page specific variables
        $page_info['title'] = 'Manage SMS & Email Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/category_list_view';
        $page_info['view_controller'] = $this->current_page;
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
        //echo 1; die();
        if (count($filter) > 0) {
            ///echo 1; die();
            $record_result = $this->smsnemail_model->get_paged_categories($per_page, $page_offset, $filter);
        } else {
            //echo 2; die();
            $record_result = $this->smsnemail_model->get_padded_paged_categories($per_page, $page_offset);
        }
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];


        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/smsnemail";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        // get default category id

        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'ID','class' => 'left', 'width' => '100'),
                '1' => array('data'=> 'Name'),
                '2' => array('data'=> 'Layout Type'),
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
                $action_str .= anchor('administrator/smsnemail/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');

                    $action_str .= '&nbsp;&nbsp;&nbsp;';
                if(!isSystemAuditor())
                    $action_str .= anchor('administrator/smsnemail/delete/'. $records[$i]->id, '<i class="icon-trash"></i>', array('title'=>'Delete', 'onclick'=>'return confirm(\'Do you really want to delete this record?\')'));
                

                //$no_of_questions = (int)$this->smsnemail_model->get_question_count($records[$i]->id);

                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->id),
                    '1' => array('data'=> $records[$i]->cat_name),
                    '2' => array('data'=> $records[$i]->cat_layout_type),
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

        redirect('administrator/smsnemail');
    }



    //------mapping start---
    public function mappingfilter()
    {
        $filter_cat_name = $this->input->post('filter_exam_title');
        $filter_clear = $this->input->post('filter_clear');

        if ($filter_clear == '') {
            if ($filter_cat_name != '') {
                $this->session->set_flashdata('filter_exam_title', $filter_cat_name);
            }
        } else {
            $this->session->unset_userdata('filter_exam_title');
        }

        redirect('administrator/smsnemail/mapping');
    }

    public function mapping()
    {
        //echo "<pre>";
        //echo loggedUserData('');
        //print_r(loggedUserData('name')); die();
        // set page specific variables
        $page_info['title'] = 'Manage SMS & Email Mapping '. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/mapping_list_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options

        $filter = array();
        if ($this->session->flashdata('filter_exam_title')) {
            $this->session->keep_flashdata('filter_exam_title');
            $filter_exam_title = $this->session->flashdata('filter_exam_title');
            $this->form_data->filter_exam_title = $filter_exam_title;
            $filter['filter_exam_title']['field'] = 'exam_title';
            $filter['filter_exam_title']['value'] = $filter_exam_title;
        }

        if(!isset($this->form_data->filter_exam_title))
        {
            $this->form_data->filter_exam_title='';
        }

        $page_info['filter'] = $filter;

       //print_r_pre($filter);
       //exit();


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        $record_result = $this->smsnemail_model->get_paged_mapping($per_page, $page_offset, $filter);
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];

        //print_r($records); die();


        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/smsnemail/mapping";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'ID', 'width' => '80'),
                '1' => array('data'=> 'Layout Title'),
                '2' => array('data'=> 'Exam Title'),
                '3' => array('data'=> 'Action', 'class' => 'center', 'width' => '80')
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
                $action_str .= anchor('administrator/smsnemail/editmapping/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');
                if(!isSystemAuditor())
                $action_str .= anchor('administrator/smsnemail/deletemapping/'. $records[$i]->id, '<i class="icon-trash"></i>', array('title'=>'Delete', 'onclick'=>'return confirm(\'Do you really want to delete this record?\')'));

                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->id),
                    '1' => array('data'=> $records[$i]->layout_name, 'class' => 'center', 'width' => '120'),
                    '2' => array('data'=> $records[$i]->exam_name, 'class' => 'center', 'width' => '120'),
                    '3' => array('data'=> $action_str, 'class' => 'center', 'width' => '100', 'width' => '80')
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


    public function newmapping()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add New SMS & Email Mapping View'));
        // set page specific variables
        $page_info['title'] = 'Add New SMS & Email Mapping'. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/mapping_form_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $exam=$this->smsnemail_model->getAllExam();
        $page_info['exam'] = $exam;
        $layoutData=$this->smsnemail_model->getAllLayout();
        $page_info['layoutData'] = $layoutData;
        //print_r_pre($layoutData); die();

        $this->_set_fields();
        $this->_set_rules();
        $this->form_data->exam_id='';
        $this->form_data->layout_id='';
        $this->form_data->exam_name='';
        $this->form_data->mapping_id='';

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
    //-----mapping end----

    /**
     * Display add category form
     * @return void
     */
    public function add()
    {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Category View'));
        // set page specific variables
        $page_info['title'] = 'Add New SMS & Email Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/category_form_view';
        $page_info['view_controller'] = $this->current_page;
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


    public function account_creation()
    {
        // set page specific variables
        $page_info['title'] = 'New Account Creation Mail'. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/account_mail_form_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;


        $mail_layout = $this->smsnemail_model->get_mail_layout();
        //print_r_pre($mail_layout);die;
        $this->_set_fields();
        $this->_set_rules();


        $this->form_data->account_mail_layout = $mail_layout->mail_body;

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


    public function password_change()
    {
        // set page specific variables
        $page_info['title'] = 'Password Change Mail Layout '. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/password_mail_form_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;


        $mail_layout = $this->smsnemail_model->get_pass_mail_layout();
        //print_r_pre($mail_layout);die;
        $this->_set_fields();
        $this->_set_rules();


        $this->form_data->password_mail_layout = $mail_layout->mail_body;

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
        $page_info['title'] = 'Add New SMS &amp; Email Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/category_form_view';
        $page_info['view_controller'] = $this->current_page;
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
            $cat_layout = $this->input->post('cat_layout');
            $cat_layout_type = $this->input->post('cat_layout_type');

            $data = array(
                'cat_name' => $cat_name,
                'cat_layout' => $cat_layout,
                'cat_layout_type' => $cat_layout_type,
                'created_by' =>loggedUserData('id')

            );

            $res = (int)$this->smsnemail_model->add_category($data);
//print_r('dd'); die();
            if ($res > 0) {
                $this->session->set_flashdata('message_success', 'Category added successfully.');
                redirect('administrator/'.$this->current_page.'/edit/'. $res);
            } else {
                $page_info['message_error'] = 'Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }

    public function add_mapping()
    {
        $page_info['title'] = 'Add New SMS &amp; Email Mapping '. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/mapping_form_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $exam=$this->smsnemail_model->getAllExam();
        $page_info['exam'] = $exam;
        $layoutData=$this->smsnemail_model->getAllLayout();
        $page_info['layoutData'] = $layoutData;
        //print_r_pre($layoutData); die();



            $layout_id = $this->input->post('layout_id');
            $exam_id = $this->input->post('exam_id');

            //var_dump($exam_id[0]);die;

            if(!empty($layout_id) && $exam_id[0]!="" && count($exam_id)>0)
            {
                $data=[];
                foreach($exam_id as $exm):
                    $data[] = array(
                    'exam_id' => $exm,
                    'layout_id' => $layout_id,
                    'created_by' =>loggedUserData('id')
                    );
                endforeach;    

                $check_layout_array =array('id'=>$layout_id);
                $res = (int)$this->insert_global_model->globalinsertbatch($this->db->dbprefix('smsnemail_layout_mapping'),$data);



                if ($res > 0) {
                    $layout_details = $this->select_global_model->Select_array($this->db->dbprefix('smsnemail_categories'),$check_layout_array);

                    $smsnmailjob=$this->smsnemail_model->get_users_not_mapping_on_job($exam_id,$layout_details,loggedUserData());
                    if($smsnmailjob){
                        $this->session->set_flashdata('message_success', 'Mapping added successfully.');
                        redirect('administrator/'.$this->current_page.'/newmapping/');
                    }
                    else{
                        $this->form_data->exam_id='';
                        $this->form_data->layout_id='';
                        $this->form_data->mapping_id='';
                        $page_info['message_error'] = 'Add is unsuccessful.';
                        $this->load->view('administrator/layouts/default', $page_info);
                    }

                } else {
                    $this->form_data->exam_id='';
                    $this->form_data->layout_id='';
                    $this->form_data->mapping_id='';
                    $page_info['message_error'] = 'Add is unsuccessful.';
                    $this->load->view('administrator/layouts/default', $page_info);
                }
            }
            else
            {
                $this->form_data->exam_id='';
                $this->form_data->layout_id='';
                $this->form_data->mapping_id='';
                $page_info['message_error'] = 'Please select mandatory field.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
            
        
    }

    public function editmapping()
    {

        // set page specific variables
        $page_info['title'] = 'Edit SMS &amp; Email Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/mapping_form_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $exam=$this->smsnemail_model->getAllExam();
        $page_info['exam'] = $exam;
        $layoutData=$this->smsnemail_model->getAllLayout();
        $page_info['layoutData'] = $layoutData;
        //print_r_pre($layoutData); die();

        // prefill form values
        $cat_id = (int)$this->uri->segment(4);
        $category = $this->smsnemail_model->get_mapping($cat_id);

        $this->_set_rules();



        $this->form_data->mapping_id = $category->id;
        $this->form_data->exam_id = $category->exam_id;
        $this->form_data->layout_id = $category->layout_id;


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


    public function update_account_mail_layout()
    {
        $page_info['title'] = 'New Account Creation Mail '. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/account_mail_form_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $mail_layout = $cat_layout = $this->input->post('cat_layout');


        if( !empty($mail_layout))
        {
            $update_array = array('mail_body'=>$mail_layout);
            $update_status = $this->smsnemail_model->update_user_mail_layout($update_array,'account');
            if($update_status){
                $this->session->set_flashdata('message_success',' Mail Layout is updated.');
            }
            else{
                $this->session->set_flashdata('message_error',' Mail Layout is unsuccessful.');
            }




        }
        else
        {
            $this->session->set_flashdata('message_error',' Mail Layout is empty, Please try again.');
        }

        redirect('administrator/'.$this->current_page.'/account_creation');
    }


    public function update_password_mail_layout()
    {
        $page_info['title'] = ' Password Change Mail '. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/password_mail_form_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $mail_layout = $cat_layout = $this->input->post('cat_layout');


        if( !empty($mail_layout))
        {
            $update_array = array('mail_body'=>$mail_layout);
            $update_status = $this->smsnemail_model->update_user_mail_layout($update_array,'password');
            if($update_status){
                $this->session->set_flashdata('message_success',' Mail Layout is updated.');
            }
            else{
                $this->session->set_flashdata('message_error',' Mail Layout is unsuccessful.');
            }




        }
        else
        {
            $this->session->set_flashdata('message_error',' Mail Layout is empty, Please try again.');
        }

        redirect('administrator/'.$this->current_page.'/password_change');
    }





    public function edit()
    {

        // set page specific variables
        $page_info['title'] = 'Edit SMS &amp; Email Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/category_form_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        // prefill form values
        $cat_id = (int)$this->uri->segment(4);
        $category = $this->smsnemail_model->get_category($cat_id);

        $this->_set_rules();



        $this->form_data->cat_id = $category->id;
        $this->form_data->cat_name = $category->cat_name;
        $this->form_data->cat_layout = $category->cat_layout;
        $this->form_data->cat_layout_type = $category->cat_layout_type;


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

    public function getencapsulatedData()
    {
       // $cat_id = (int)$this->uri->segment(4); die();
        //$cat_id = $this->input->post('cat_id');
        //$cat_id=10;

           // prefill form values
        $cat_id = (int)$this->uri->segment(4);
		$category = $this->smsnemail_model->get_category($cat_id);

        $this->form_data->cat_layout = $category->cat_layout;

        echo json_encode($category->cat_layout);

    }

    public function update_mapping()
    {
        // set page specific variables
        $page_info['title'] = 'Edit SMS & Email Mapping '. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/mapping_form_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $cat_id = (int)$this->input->post('mapping_id');
        
         $exam_id = $this->input->post('exam_id');
            $layout_id = $this->input->post('layout_id');
       // echo $cat_id; die();
        if(!empty($cat_id) && !empty($exam_id) && !empty($layout_id))
        {

           

            $data = array(
                'exam_id' => $exam_id,
                'layout_id' => $layout_id,
                'updated_by' =>loggedUserData('id')
            );

            if ($this->smsnemail_model->update_mapping($cat_id, $data)) {
                $this->session->set_flashdata('message_success', 'Mapping is updated successfully.');
            } else  {
                $this->session->set_flashdata('message_error', $this->category_model->error_message. ' Update is unsuccessful, Please try again.');
            }
        }
        else
        {
            $this->session->set_flashdata('message_error',' Some field is empty, Please try again.');
        }

            redirect('administrator/'.$this->current_page.'/editmapping/'. $cat_id);

    }

    public function update_category()
    {
        // set page specific variables
        $page_info['title'] = 'Edit Category'. $this->site_name;
        $page_info['view_page'] = 'administrator/configure/category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $cat_id = (int)$this->input->post('cat_id');
        
        $this->_set_fields();
        $this->_set_rules();

        //echo $cat_id; die();

        if ($this->form_validation->run() == FALSE) {

            $this->form_data->cat_id = $cat_id;
            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $cat_name = $this->input->post('cat_name');
            $cat_layout = $this->input->post('cat_layout');
            $cat_layout_type = $this->input->post('cat_layout_type');

            $data = array(
                'cat_name' => $cat_name,
                'cat_layout' => $cat_layout,
                'cat_layout_type' => $cat_layout_type,
                'updated_by' =>loggedUserData('id')
            );

            if ($this->smsnemail_model->update_category($cat_id, $data)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else  {
                $this->session->set_flashdata('message_error', $this->category_model->error_message. ' Update is unsuccessful.');
            }

            redirect('administrator/'.$this->current_page.'/edit/'. $cat_id);
        }
    }

    /**
     * Delete a category
     * @return void
     */
    public function delete()
    {
        $cat_id = (int)$this->uri->segment(4);
        $res = $this->smsnemail_model->delete_category($cat_id);
       // print_r($res); die();
        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Category Deleted successfully.');
        } else {
            $this->session->set_flashdata('message_error', $this->category_model->error_message .' Delete is unsuccessful.');
        }
        
        redirect('administrator/smsnemail');
    }    


    public function deletemapping()
    {
        $cat_id = (int)$this->uri->segment(4);
        $res = $this->smsnemail_model->delete_mapping($cat_id);
       // print_r($res); die();
        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Mapping Deleted successfully.');
        } else {
            $this->session->set_flashdata('message_error', $this->smsnemail_model->error_message .' Delete is unsuccessful.');
        }
        
        redirect('administrator/smsnemail/mapping');
    }


    // set empty default form field values
	private function _set_fields()
	{
        
		$this->form_data->cat_id = 0;
        $this->form_data->cat_layout_type = 0;
		$this->form_data->cat_name = '';

		$this->form_data->filter_cat_name = '';
        $this->form_data->account_mail_layout = '';
        $this->form_data->password_mail_layout = '';

	}

	// validation rules
	private function _set_rules()
	{
		$this->form_validation->set_rules('cat_name', 'Category Name', 'required|trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('cat_layout_type', 'Layout Type', 'trim|xss_clean|strip_tags');
	}

   

}

/* End of file category.php */
/* Location: ./application/controllers/administrator/category.php */