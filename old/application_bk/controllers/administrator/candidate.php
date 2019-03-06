<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Candidate extends MY_Controller
{
    var $current_page = "user";
    var $candidate_type_list = array();
    var $candidate_team_list = array();

    var $logged_in_candidate;

    var $type_list_filter = array();
    var $team_list_filter = array();
    var $active_list_filter = array();
    
    var $admin_group_list = array();
    var $tbl_exam_candidates    = "exm_candidates";

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
        $this->load->model('candidate_model');
        $this->load->model('user_team_model');
        $this->load->model('admin_group_model');

        $this->load->model('global/select_global_model');
        $this->load->model('global/insert_global_model');
        $this->load->model('global/delete_global_model');
        $this->load->model('global/update_global_model');

        //$this->logged_in_candidate = $this->session->candidatedata('logged_in_candidate');


        $this->active_list_filter[''] = 'Any';
        $this->active_list_filter['active'] = 'Active';
        $this->active_list_filter['inactive'] = 'Inactive';

        


        

    }

    /**
     * Display paginated list of candidate
     * @return void
     */
    public function index()
    {
        // set page specific variables
        $page_info['title'] = 'Manage Candidate '. $this->site_name;
        $page_info['view_page'] = 'administrator/candidate_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();

        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_cand_name')) {
            $this->session->keep_flashdata('filter_cand_name');
            $cand_name = $this->session->flashdata('filter_cand_name');
            $this->form_data->filter_cand_name = $cand_name;
            $filter['filter_cand_name']['field'] = 'filter_cand_name';
            $filter['filter_cand_name']['value'] = $cand_name;
        }
        if ($this->session->flashdata('filter_cand_email')) {
            $this->session->keep_flashdata('filter_cand_email');
            $cand_email = $this->session->flashdata('filter_cand_email');
            $this->form_data->filter_cand_email = $cand_email;
            $filter['filter_cand_email']['field'] = 'filter_cand_email';
            $filter['filter_cand_email']['value'] = $cand_email;
        }

        if ($this->session->flashdata('filter_cand_address')) {
            $this->session->keep_flashdata('filter_cand_address');
            $cand_address = $this->session->flashdata('filter_cand_address');
            $this->form_data->filter_cand_address = $cand_address;
            $filter['filter_cand_address']['field'] = 'filter_cand_address';
            $filter['filter_cand_address']['value'] = $cand_address;
        }


        if ($this->session->flashdata('filter_phone')) {
            $this->session->keep_flashdata('filter_phone');
            $cand_phone = $this->session->flashdata('filter_phone');
            $this->form_data->filter_phone = $cand_phone;
            $filter['filter_phone']['field'] = 'filter_phone';
            $filter['filter_phone']['value'] = $cand_phone;
        }


        if ($this->session->flashdata('filter_cand_is_active')) {
            $filter_field = 'filter_cand_is_active';
            $filter_active = $this->session->flashdata('filter_cand_is_active');
            if ($filter_active == 'active') {
                $filter_active = '1';
            } elseif ($filter_active == 'inactive') {
                $filter_active = '0';
            } else {
                $filter_active = '';
            }

            if ($filter_active != '') {
                $this->session->keep_flashdata('filter_cand_is_active');
                $this->form_data->filter_cand_is_active = $this->session->flashdata('filter_cand_is_active');
                $filter['filter_cand_is_active']['field'] = $filter_field;
                $filter['filter_cand_is_active']['value'] = $filter_active;
            }
        }
        $page_info['filter'] = $filter;


        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->candidate_model->get_paged_candidates($per_page, $page_offset, $filter);


        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];

        //var_dump($records);die;

        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/candidate";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);


        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'ID'),
                '1' => array('data'=> 'Candidate Name'),
                '2' => array('data'=> 'Email'),
                '3' => array('data'=> 'Address'),
                '4' => array('data'=> 'Phone Number'),
                '5' => array('data'=> 'Status', 'class' => 'center', 'width' => '100'),
                '6' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {

                $candidate_name = trim($records[$i]->cand_name );
                $action_str='';
                $status='';
                if($records[$i]->cand_is_active==1 )
                {
                    if(!isSystemAuditor())
                    $status=anchor('administrator/candidate/change_status/'. $records[$i]->id.'/'.$records[$i]->cand_is_active, 'Active', array('title'=>'Change Status','class'=>'btn btn-success'));
                    else
                        $status = 'Active';
                }
                else{
                    if(!isSystemAuditor())
                    $status=anchor('administrator/candidate/change_status/'. $records[$i]->id.'/'.$records[$i]->cand_is_active, 'Inactive', array('title'=>'Change Status','class'=>'btn btn-warning'));
                    else
                        $status= 'Inactive';
                }
                if(!isSystemAuditor())

                $action_str .= anchor('administrator/candidate/edit/'. $records[$i]->id, '<i class="icon-edit"></i> Edit', array('title'=>'Edit','class'=>'btn btn-success'));
                $action_str .= '&nbsp;&nbsp;&nbsp;';
                


                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->id),
                    '1' => array('data'=> $candidate_name),
                    '2' => array('data'=> $records[$i]->cand_email),
                    '3' => array('data'=> $records[$i]->cand_address),
                    '4' => array('data'=> $records[$i]->phone),
                    '5' => array('data'=> $status),
                    '6' => array('data'=> $action_str, 'class' => 'center', 'width' => '100')
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
        $filter_cand_name = $this->input->post('filter_cand_name');
        $filter_cand_email = $this->input->post('filter_cand_email');
        $filter_cand_address = $this->input->post('filter_cand_address');
        $filter_phone = $this->input->post('filter_phone');
        $filter_cand_is_active = $this->input->post('filter_cand_is_active');
        $filter_clear = $this->input->post('filter_clear');



        //var_dump($filter_clear);die;

        if ($filter_clear == false) {

            if ($filter_cand_name != '') {
                $this->session->set_flashdata('filter_cand_name', $filter_cand_name);
            }
            if ($filter_cand_email != '') {
                $this->session->set_flashdata('filter_cand_email', $filter_cand_email);
            }
            if ($filter_cand_address != ''){
                $this->session->set_flashdata('filter_cand_address', $filter_cand_address);
            }
            if ($filter_phone != '') {
                $this->session->set_flashdata('filter_phone', $filter_phone);
            }
            if ($filter_cand_is_active == 'active' || $filter_cand_is_active == 'inactive' ) {
                $this->session->set_flashdata('filter_cand_is_active', $filter_cand_is_active);
            }


            //var_dump($this->session->flashdata('filter_cand_name'));die;
        } else {
            $this->session->unset_userdata('filter_cand_name');
            $this->session->unset_userdata('filter_cand_email');
            $this->session->unset_userdata('filter_cand_address');
            $this->session->unset_userdata('filter_phone');
            $this->session->unset_userdata('filter_cand_is_active');

        }

        redirect('administrator/candidate');
    }

    /**
     * Display add candidate form
     * @return void
     */
    public function add()
    {
        // set page specific variables
        $page_info['title'] = 'Add New Candidate'. $this->site_name;
        $page_info['view_page'] = 'administrator/candidate_form_view';
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

    public function add_candidate()
    {
        $page_info['title'] = 'Add New Candidate'. $this->site_name;
        $page_info['view_page'] = 'administrator/candidate_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {



            $cand_login = $this->input->post('cand_login');
            $cand_password = $this->input->post('cand_password');
            $cand_confirm_password = $this->input->post('cand_confirm_password');
            $cand_name = $this->input->post('cand_name');
            $cand_email = $this->input->post('cand_email');
            //$cand_address = $this->input->post('cand_address');
            $cand_phone = $this->input->post('cand_phone');
            $nid_passport_no = $this->input->post('nid_passport_no');
            $cand_is_active = $this->input->post('cand_is_active');
            $cand_is_lock = (int)$this->input->post('cand_is_lock');
            //$profile_image = $this->input->post('profile_image');



            $check_data = $this->select_global_model->is_data_exists($this->tbl_exam_candidates, array('cand_email'=>$cand_email, 'phone'=>$cand_phone));

            if($check_data){
                $this->session->set_flashdata('message_error', 'This Candidate already exists!');
                redirect('administrator/candidate/add/');
            }



            if ($cand_name=='' || $cand_name == null) {

                $this->session->set_flashdata('message_error', $this->candidate_model->error_message. ' Candidate Name is empty.');
                redirect('administrator/candidate/add/');
            }


           // $config['upload_path'] = './uploads/candidate/';
           // $config['allowed_types'] = 'xls|xlsx';

            //if ($_FILES['profile_image']['tmp_name'] != '' && $_FILES['candidate_file']['error'] == 0) {


                //$file_error = $this->upload->display_errors();
                //$file_data = $this->upload->data();
            $file_name ="";
            $file_sig ="";
            if (isset($_FILES['profile_image']['name']) && !empty($_FILES['profile_image']['name'])) {
                $config['upload_path'] = './uploads/candidate/';
                $config['allowed_types'] = 'jpg|jpeg|png|gif';
                $file_name = $_FILES["profile_image"]['name'];
                $this->upload->initialize($config);
                $upload = $this->upload->do_upload('profile_image');

                if(!$upload){
                    $this->session->set_flashdata('message_error', $this->upload->display_errors());
                    redirect(base_url('administrator/candidate/add/'));
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
                    //var_dump($_FILES['upload_file']['error']);die;
                    $this->session->set_flashdata('message_error', $this->upload->display_errors());
                    redirect(base_url('administrator/candidate/add/'));
                }

            }


            $data_can = array(
                'user_login' => $cand_login,
                'user_password' => $cand_password,
                'user_confirm_password' => $cand_confirm_password,
                'user_first_name' => $cand_name,
                'user_last_name' => '',
                'user_email' => $cand_email,
                'user_type' => 'Candidate',
                'phone' =>$cand_phone,
                'nid_passport_no' =>$nid_passport_no,
                'user_is_active' => $cand_is_active,
                'user_is_lock' => $cand_is_lock,
                'profile_image' => $file_name,
                'signature_image'=> $file_sig
            );
                

            $res = (int)$this->user_model->add_user($data_can);
            if ($res > 0) {
                $this->session->set_flashdata('message_success', 'Add is successful.');
                redirect('administrator/candidate/add');
            } else {
                $page_info['message_error'] = $this->candidate_model->error_message .' Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }



    public function change_status()
    {
        $cand_id = (int)$this->uri->segment(4);
        $status = (int)$this->uri->segment(5);
        if($status==1)
            $status=2;
        else
            $status=1;


        $res = $this->update_global_model->globalupdate('candidates',array('id'=>$cand_id),array('cand_is_active'=>$status));

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Candidate status updated successfully.');
        } else {
            $this->session->set_flashdata('message_error', ' Delete is unsuccessful.');
        }

        redirect('administrator/candidate');
    }


    public function bulk()
    {
        // set page specific variables
        $page_info['title'] = 'Add Bulk Candidate'. $this->site_name;
        $page_info['view_page'] = 'administrator/candidate_bulk_form_view';
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
        // set page specific variables
        $page_info['title'] = 'Edit Bulk candidates'. $this->site_name;
        $page_info['view_page'] = 'administrator/candidate_edit_bulk_form_view';
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
        // set page specific variables
        $page_info['title'] = 'Delete Bulk Candidate'. $this->site_name;
        $page_info['view_page'] = 'administrator/candidate_delete_bulk_form_view';
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
        $candidates = array();
        $invalid_candidates = array();
        $error_messages = array();
        $file_path = '';
        $has_column_header = (int)$this->input->post('candidate_file_has_column_header');



        // uploading file
        $config['upload_path'] = './uploads/candidate/';
        $config['allowed_types'] = 'xls|xlsx';

        if ($_FILES['candidate_file']['tmp_name'] != '' && $_FILES['candidate_file']['error'] == 0) {

            $this->upload->initialize($config);
            $this->upload->do_upload('candidate_file');

            $file_error = $this->upload->display_errors();
            $file_data = $this->upload->data();

            if ($file_error == '') {

                $file_path = $file_data['full_path'];

                //var_dump($file_path);die;

                $objPHPExcel = PHPExcel_IOFactory::load($file_path);
                @unlink($file_path);

                $objPHPExcel->setActiveSheetIndex(0);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                $max_column_name = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $max_column_number = PHPExcel_Cell::columnIndexFromString($max_column_name);





                // remove first row (if $has_column_header == 1)
                // remove empty rows
                $start = 1;
                if ($has_column_header) {
                    $start = 2;
                }

                for ($i=$start; $i<=count($sheetData); $i++) {

                    $candidate_name = trim($sheetData[$i]['A']);
                    $candidate_mail = trim($sheetData[$i]['B']);
                    $candidate_address = trim($sheetData[$i]['C']);
                    $candidate_phone = trim($sheetData[$i]['D']);
                    $is_active = trim($sheetData[$i]['E']);




                    if ($candidate_name == '' && $candidate_mail == '' && $candidate_address == '' && $candidate_phone == '' ) {
                        continue;
                    } else {
                        $candidates[$i]['cand_name'] = $candidate_name;
                        $candidates[$i]['cand_email'] = $candidate_mail;
                        $candidates[$i]['cand_address'] = $candidate_address;
                        $candidates[$i]['phone'] = $candidate_phone;
                        $candidates[$i]['cand_is_active'] = (int)$is_active;
                    }
                }

                // check for valid data
                if (count($candidates) > 0) {
                    foreach($candidates as $row => $candidate) {

                        $row_has_error = false;

                        $candidate_name = $candidate['cand_name'];
                        $candidate_mail = $candidate['cand_email'];
                        $candidate_address = $candidate['cand_address'];
                        $candidate_phone = $candidate['phone'];
                        $is_active = (int)$candidate['cand_is_active'];



                        if ($candidate_name == '') {
                            $error_messages[$row][] = 'Candidate name can not be empty';
                            $row_has_error = true;
                        }





                        if ($row_has_error) {
                            $invalid_candidates[$row] = $candidate;
                            unset($candidates[$row]);
                        } else {
                            $candidates[$row]['cand_name'] = $candidate_name;
                            $candidates[$row]['cand_email'] = $candidate_mail;
                            $candidates[$row]['cand_address'] = $candidate_address;
                            $candidates[$row]['phone'] = $candidate_phone;
                            $candidates[$row]['cand_is_active'] = (int)$is_active;

                        }
                    }
                }

                if (count($candidates) <= 0 && count($invalid_candidates) <= 0) {
                    $this->session->set_flashdata('message_error', 'File does not contain any row.');
                    redirect('administrator/candidate/bulk');
                }


                //var_dump($candidates);die;

                $this->session->set_flashdata('bulk_candidates', $candidates);
                $this->session->set_flashdata('bulk_invalid_candidates', $invalid_candidates);
                $this->session->set_flashdata('bulk_error_messages', $error_messages);

            } else {
                $this->session->set_flashdata('message_error', $file_error);
                redirect('administrator/candidate/bulk');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Please upload an Excel file.');
            redirect('administrator/candidate/bulk');
        }

        $this->session->set_flashdata('bulk_action', 1);
        redirect('administrator/candidate/bulk_upload_action');
    }
   
    
    public function edit_bulk_upload()
    {
        $candidates = array();
        $invalid_candidates = array();
        $error_messages = array();
        $file_path = '';
        $has_column_header = (int)$this->input->post('candidate_file_has_column_header');

        // uploading file
        $config['upload_path'] = './uploads/candidate/';
        $config['allowed_types'] = 'xls|xlsx';
        
        if ($_FILES['candidate_file']['tmp_name'] != '' && $_FILES['candidate_file']['error'] == 0) {

            $this->upload->initialize($config);
            $this->upload->do_upload('candidate_file');

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
                    redirect('administrator/candidate/edit_bulk');
                }

                // remove first row (if $has_column_header == 1)
                // remove empty rows
                $start = 1;
                if ($has_column_header) {
                    $start = 2;
                }

                for ($i=$start; $i<=count($sheetData); $i++) {

                    $candidate_name = trim($sheetData[$i]['A']);
                    $candidate_mail = trim($sheetData[$i]['B']);
                    $candidate_address = trim($sheetData[$i]['C']);
                    $candidate_phone = trim($sheetData[$i]['D']);
                    $is_active = trim($sheetData[$i]['E']);

                    if ($candidate_name == '' && $candidate_mail == '' && $candidate_address == '' && $candidate_phone == '' ) {
                        continue;
                    } else {
                        $candidates[$i]['cand_name'] = $candidate_name;
                        $candidates[$i]['cand_email'] = $candidate_mail;
                        $candidates[$i]['cand_address'] = $candidate_address;
                        $candidates[$i]['phone'] = $candidate_phone;
                        $candidates[$i]['cand_is_active'] = (int)$is_active;
                    }
                }

                // check for valid data
                if (count($candidates) > 0) {
                    foreach($candidates as $row => $candidate) {

                        $row_has_error = false;
									
                        $candidate_team_id = $candidate['candidate_team_id'];
                        $candidate_login = $candidate['candidate_login'];
                        $candidate_password = $candidate['candidate_password'];
                        $candidate_first_name = $candidate['candidate_first_name'];

                        $candidate_team_id = $this->candidate_team_model->get_candidate_team_by_name($candidate_team_id);
                        if ($candidate_team_id) {
                            $candidate_team_id = $candidate_team_id->id;
                        } 
                        elseif ($candidate_team_id == '') {
                            $error_messages[$row][] = 'Team name can not be empty';
                            $row_has_error = true;
                        }

                        if ($candidate_login == '') {
                            $error_messages[$row][] = 'Login ID can not be empty';
                            $row_has_error = true;
                        } elseif (!$this->candidate_model->get_candidate_by_login($candidate_login)) {
                            $error_messages[$row][] = 'Login ID is not exist';
                            $row_has_error = true;
                        }






                        if ($row_has_error) {
                            $invalid_candidates[$row] = $candidate;
                            unset($candidates[$row]);
                        } else {
                            $candidates[$row]['candidate_team_id'] = $candidate_team_id;
                            $candidates[$row]['candidate_login'] = $candidate_login;
                            $candidates[$row]['candidate_password'] = $candidate_password;
                            $candidates[$row]['candidate_password_old'] = $candidate_password;
                            $candidates[$row]['candidate_first_name'] = $candidate_first_name;

                        }
                    }
                }

                if (count($candidates) <= 0 && count($invalid_candidates) <= 0) {
                    $this->session->set_flashdata('message_error', 'File does not contain any row.');
                    redirect('administrator/candidate/edit_bulk');
                }



                $this->session->set_flashdata('bulk_candidates', $candidates);
                $this->session->set_flashdata('bulk_invalid_candidates', $invalid_candidates);
                $this->session->set_flashdata('bulk_error_messages', $error_messages);
                
            } else {
                $this->session->set_flashdata('message_error', $file_error);
                redirect('administrator/candidate/edit_bulk');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Please upload an Excel file.');
            redirect('administrator/candidate/edit_bulk');
        }

        $this->session->set_flashdata('bulk_action', 1);
        redirect('administrator/candidate/edit_bulk_upload_action');
    }



    public function bulk_upload_action()
    {
        // set page specific variables
        $page_info['title'] = 'Take an Action'. $this->site_name;
        $page_info['view_page'] = 'administrator/candidate_bulk_action_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $page_info['bulk_candidates'] = array();
        $page_info['bulk_invalid_candidates'] = array();
        $page_info['bulk_error_messages'] = array();


        if ($this->session->flashdata('bulk_action')) {
            $this->session->keep_flashdata('bulk_action');
        }
        if ( (int)$this->session->flashdata('bulk_action') == 0 ) {
            redirect('administrator/candidate/bulk');
        }


        if ($this->session->flashdata('bulk_candidates')) {
            $page_info['bulk_candidates'] = $this->session->flashdata('bulk_candidates');
            $this->session->keep_flashdata('bulk_candidates');
        }
        if ($this->session->flashdata('bulk_invalid_candidates')) {
            $page_info['bulk_invalid_candidates'] = $this->session->flashdata('bulk_invalid_candidates');
            $this->session->keep_flashdata('bulk_invalid_candidates');
        }
        if ($this->session->flashdata('bulk_error_messages')) {
            $page_info['bulk_error_messages'] = $this->session->flashdata('bulk_error_messages');
            $this->session->keep_flashdata('bulk_error_messages');
        }

        //var_dump($page_info['bulk_candidates']);die;


        $bulk_invalid_candidates = $page_info['bulk_invalid_candidates'];
        $bulk_error_messages = $page_info['bulk_error_messages'];


        if ($bulk_invalid_candidates && count($bulk_invalid_candidates) < 250) {

            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Candidate name'),
                '1' => array('data'=> 'Email'),
                '2' => array('data'=> 'Address'),
                '3' => array('data'=> 'Phone Number'),
                '4' => array('data'=> 'Is Active'),
                '5' => array('data'=> 'Error')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            foreach($bulk_invalid_candidates as $row => $record) {

                $error_message = '';
                for ($i=0; $i<count($bulk_error_messages[$row]); $i++) {
                    if ($i>0) { $error_message .= '<br />'; }
                    $error_message .= $bulk_error_messages[$row][$i];
                }

                $tbl_row = array(
                    '0' => array('data'=> $record['cand_name']),
                    '1' => array('data'=> $record['cand_email']),
                    '2' => array('data'=> $record['cand_address']),
                    '3' => array('data'=> $record['phone']),
                    '4' => array('data'=> $record['is_cand_active']),
                    '5' => array('data'=> $error_message)
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['bulk_invalid_candidates_table'] = $this->table->generate();
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
        $bulk_candidates = array();

        if ($this->session->flashdata('bulk_candidates')) {
            $bulk_candidates = $this->session->flashdata('bulk_candidates');
        }

        // bulk insert

        //var_dump($bulk_candidates);die;
        $return_value = $this->candidate_model->add_bulk_candidates($bulk_candidates);
        //var_dump($return_value); var_dump('hi');die;
        if($return_value>0)$this->session->set_flashdata('message_success', 'Record(s) inserted successfully.');
        else
            $this->session->set_flashdata('message_error', 'No row is inserted.');

        redirect('administrator/candidate/bulk');
    }



    public function download_candidate(){

        $record_result = $this->candidate_model->get_all_candidates();

        $records = $record_result['result'];

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=User List -".date('Y-m-d').".csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        $file = fopen('php://output', 'w');

        fputcsv($file, array(
            'Id',
            'Candidate Name',
            'Email',
            'Address',
            'Phone Number',
            'Status'
        ));


        $user_info = array();
        foreach ($records as $key => $value) {
            $user_info[$key]['id']=$records[$key]['id'];
            $user_info[$key]['cand_name']=$records[$key]['cand_name'];
            $user_info[$key]['cand_email']=$records[$key]['cand_email'];
            $user_info[$key]['cand_address']=$records[$key]['cand_address'];
            $user_info[$key]['phone']=$records[$key]['phone'];

            if($records[$key]['cand_is_active']==1)
                $user_info[$key]['cand_is_active']='Active';
            else
                $user_info[$key]['cand_is_active']='Inactive';

        }

        foreach ($user_info as $value) {
            $rowD= $value;
            fputcsv($file, $rowD);
        }
        exit();



    }


    //delete bulk upload
    public function delete_bulk_upload()
    {
        $candidates = array();
        $invalid_candidates = array();
        $error_messages = array();
        $file_path = '';
        $has_column_header = (int)$this->input->post('candidate_file_has_column_header');

        // uploading file
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xls|xlsx';
        
        if ($_FILES['candidate_file']['tmp_name'] != '' && $_FILES['candidate_file']['error'] == 0) {

            $this->upload->initialize($config);
            $this->upload->do_upload('candidate_file');

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
                    redirect('administrator/candidate/delete_bulk');
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
                        $candidates[$i]['candidate_login'] = $login;
                    }
                }

                // check for valid data
                if (count($candidates) > 0) {
                    foreach($candidates as $row => $candidate) {

                        $row_has_error = false;

                        $candidate_login = $candidate['candidate_login'];

                        $candidate_login = $this->candidate_model->get_candidate_by_login($candidate_login);
                       
                        if ($candidate_login == '') {
                            $error_messages[$row][] = 'Login ID can not be match with existing candidate';
                            $row_has_error = true;
                        }
                        
                        if ($row_has_error) {
                            $invalid_candidates[$row] = $candidate;
                            unset($candidates[$row]);
                        } else {
                            $candidates[$row]['candidate_login'] = $candidate_login;
                        }
                    }
                }

                if (count($candidates) <= 0 && count($invalid_candidates) <= 0) {
                    $this->session->set_flashdata('message_error', 'File does not contain any row.');
                    redirect('administrator/candidate/delete_bulk');
                }

                $this->session->set_flashdata('bulk_candidates', $candidates);
                $this->session->set_flashdata('bulk_invalid_candidates', $invalid_candidates);
                $this->session->set_flashdata('bulk_error_messages', $error_messages);
                
            } else {
                $this->session->set_flashdata('message_error', $file_error);
                redirect('administrator/candidate/delete_bulk');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Please upload an Excel file.');
            redirect('administrator/candidate/delete_bulk');
        }

        $this->session->set_flashdata('bulk_delete_action', 1);
        redirect('administrator/candidate/delete_bulk_upload_action');
    }
      
    


    
    
    public function edit_bulk_upload_action()
    {
        // set page specific variables
        $page_info['title'] = 'Take an Action'. $this->site_name;
        $page_info['view_page'] = 'administrator/candidate_edit_bulk_action_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $page_info['bulk_candidates'] = array();
        $page_info['bulk_invalid_candidates'] = array();
        $page_info['bulk_error_messages'] = array();


        if ($this->session->flashdata('bulk_action')) {
            $this->session->keep_flashdata('bulk_action');
        }
        if ( (int)$this->session->flashdata('bulk_action') == 0 ) {
            redirect('administrator/candidate/edit_bulk');
        }
        

        if ($this->session->flashdata('bulk_candidates')) {
            $page_info['bulk_candidates'] = $this->session->flashdata('bulk_candidates');
            $this->session->keep_flashdata('bulk_candidates');
        }
        if ($this->session->flashdata('bulk_invalid_candidates')) {
            $page_info['bulk_invalid_candidates'] = $this->session->flashdata('bulk_invalid_candidates');
            $this->session->keep_flashdata('bulk_invalid_candidates');
        }
        if ($this->session->flashdata('bulk_error_messages')) {
            $page_info['bulk_error_messages'] = $this->session->flashdata('bulk_error_messages');
            $this->session->keep_flashdata('bulk_error_messages');
        }


        $bulk_invalid_candidates = $page_info['bulk_invalid_candidates'];
        $bulk_error_messages = $page_info['bulk_error_messages'];

        if ($bulk_invalid_candidates && count($bulk_invalid_candidates) < 250) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Candidate name'),
                '1' => array('data'=> 'Email '),
                '2' => array('data'=> 'Address'),
                '3' => array('data'=> 'Phone Number'),
                '4' => array('data'=> 'Error')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            foreach($bulk_invalid_candidates as $row => $record) {

                $error_message = '';
                for ($i=0; $i<count($bulk_error_messages[$row]); $i++) {
                    if ($i>0) { $error_message .= '<br />'; }
                    $error_message .= $bulk_error_messages[$row][$i];
                }

                $tbl_row = array(
                    '0' => array('data'=> $record['cand_name']),
                    '1' => array('data'=> $record['cand_email']),
                    '2' => array('data'=> $record['cand_address']),
                    '3' => array('data'=> $record['candidate_type']),
                    '4' => array('data'=> $error_message)
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['bulk_invalid_candidates_table'] = $this->table->generate();
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
        $bulk_candidates = array();

        if ($this->session->flashdata('bulk_candidates')) {
            $bulk_candidates = $this->session->flashdata('bulk_candidates');
        }

        // bulk update
        $this->candidate_model->edit_bulk_candidates($bulk_candidates);
        $this->session->set_flashdata('message_success', 'Record(s) updated successfully.');

        redirect('administrator/candidate/edit_bulk');
    }
    
    //delete bulk upload action
    public function delete_bulk_upload_action()
    {
        // set page specific variables
        $page_info['title'] = 'Take an Action'. $this->site_name;
        $page_info['view_page'] = 'administrator/candidate_delete_bulk_action_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $page_info['bulk_candidates'] = array();
        $page_info['bulk_invalid_candidates'] = array();
        $page_info['bulk_error_messages'] = array();


        if ($this->session->flashdata('bulk_delete_action')) {
            $this->session->keep_flashdata('bulk_delete_action');
        }
        if ( (int)$this->session->flashdata('bulk_delete_action') == 0 ) {
            redirect('administrator/candidate/delete_bulk');
        }
        

        if ($this->session->flashdata('bulk_candidates')) {
            $page_info['bulk_candidates'] = $this->session->flashdata('bulk_candidates');
            $this->session->keep_flashdata('bulk_candidates');
        }
        if ($this->session->flashdata('bulk_invalid_candidates')) {
            $page_info['bulk_invalid_candidates'] = $this->session->flashdata('bulk_invalid_candidates');
            $this->session->keep_flashdata('bulk_invalid_candidates');
        }
        if ($this->session->flashdata('bulk_error_messages')) {
            $page_info['bulk_error_messages'] = $this->session->flashdata('bulk_error_messages');
            $this->session->keep_flashdata('bulk_error_messages');
        }


        $bulk_invalid_candidates = $page_info['bulk_invalid_candidates'];
        $bulk_error_messages = $page_info['bulk_error_messages'];

        if ($bulk_invalid_candidates && count($bulk_invalid_candidates) < 250) {
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

            foreach($bulk_invalid_candidates as $row => $record) {

                $error_message = '';
                for ($i=0; $i<count($bulk_error_messages[$row]); $i++) {
                    if ($i>0) { $error_message .= '<br />'; }
                    $error_message .= $bulk_error_messages[$row][$i];
                }

                $tbl_row = array(
                    '0' => array('data'=> $record['candidate_login']),
                    '1' => array('data'=> $error_message)
                );
                $this->table->add_row($tbl_row);
            }

            $page_info['bulk_invalid_candidates_table'] = $this->table->generate();
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
        $bulk_candidates = array();

        if ($this->session->flashdata('bulk_candidates')) {
            $bulk_candidates = $this->session->flashdata('bulk_candidates');
        }
        
        // bulk delete
        $this->candidate_model->delete_bulk_candidates($bulk_candidates);

        $this->session->set_flashdata('message_success', 'Record(s) deleted successfully.');

        redirect('administrator/candidate/delete_bulk');
    }
       

    public function edit()
    {
        // set page specific variables
        $page_info['title'] = 'Edit candidate'. $this->site_name;
        $page_info['view_page'] = 'administrator/candidate_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        // prefill form values
        $candidate_id = (int)$this->uri->segment(4);
		//$candidate = $this->candidate_model->get_candidate($candidate_id);

        $candidate = $this->user_model->get_user($candidate_id);

        $this->form_data->id=$candidate->id;
        $this->form_data->cand_login = $candidate->user_login;
        $this->form_data->cand_password = '';
        $this->form_data->cand_confirm_password = '';
        $this->form_data->cand_name=$candidate->user_first_name;
        $this->form_data->cand_email=$candidate->user_email;
        //$this->form_data->cand_address=$candidate->cand_address;
        $this->form_data->phone=$candidate->phone;
        $this->form_data->nid_passport_no=$candidate->nid_passport_no;
        $this->form_data->cand_is_active=$candidate->user_is_active;
        $this->form_data->cand_is_lock = $candidate->user_is_lock;

        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        // load view
	$this->load->view('administrator/layouts/default', $page_info);
    }

    public function update_candidate()
    {
        // set page specific variables
        $page_info['title'] = 'Edit candidate'. $this->site_name;
        $page_info['view_page'] = 'administrator/candidate_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        $candidate_id = (int)$this->input->post('cand_id');

        $this->_set_fields();
        $this->_set_rules(true);

        if ($this->form_validation->run() == FALSE) {
            $this->form_data->candidate_id = $candidate_id;
            $this->load->view('administrator/layouts/default', $page_info);
        } else {

            $cand_login = $this->input->post('cand_login');
            $cand_password = $this->input->post('cand_password');
            $cand_confirm_password = $this->input->post('cand_confirm_password');
            $cand_name = $this->input->post('cand_name');
            $cand_email = $this->input->post('cand_email');
            //$cand_address = $this->input->post('cand_address');
            $cand_phone = $this->input->post('cand_phone');
            $nid_passport_no = $this->input->post('nid_passport_no');
            $cand_is_active = $this->input->post('cand_is_active');
            $cand_is_lock = (int)$this->input->post('cand_is_lock');

            $check_data_up = $this->select_global_model->is_data_exists($this->tbl_exam_users,
                array('id !='=>$candidate_id,'user_login'=>$cand_login));
            if($check_data_up){
                $this->session->set_flashdata('message_error', 'This Candidate already exists!');
                redirect('administrator/candidate/add/');
            }

            $data = array(
                'user_login' => $cand_login,
                'user_password' => $cand_password,
                'user_confirm_password' => $cand_confirm_password,
                'user_first_name' => $cand_name,
                'user_last_name' => '',
                'user_email' => $cand_email,
                'user_type' => 'Candidate',
                'phone' =>$cand_phone,
                'nid_passport_no' =>$nid_passport_no,
                'user_is_active' => $cand_is_active,
                'user_is_lock' => $cand_is_lock

            );


            if ($cand_login=='' || $cand_login == null) {

                $this->session->set_flashdata('message_error', $this->candidate_model->error_message. ' Login Id cannot be empty.');
                redirect('administrator/candidate/edit/'. $candidate_id);
            }



            if ($this->user_model->update_user($candidate_id, $data)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else {
                $this->session->set_flashdata('message_error', $this->candidate_model->error_message. ' Update is unsuccessful.');
            }

            redirect('administrator/candidate/edit/'. $candidate_id);
        }
    }

    /**
     * Delete a candidate
     * @return void
     */
    public function delete()
    {
        $candidate_id = (int)$this->uri->segment(4);
        $res = $this->candidate_model->delete_candidate($candidate_id);

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Delete is successful.');
        } else {
            $this->session->set_flashdata('message_error', $this->candidate_model->error_message .' Delete is unsuccessful.');
        }
        
        redirect('administrator/candidate');
    }
	
    /**
     * Inactive a candidate
     * @return void
     */
    public function inactive()
    {
        $candidate_id = (int)$this->uri->segment(4);
        $res = $this->candidate_model->inactive_candidate($candidate_id);

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Inactive is successful.');
        } else {
            $this->session->set_flashdata('message_error', $this->candidate_model->error_message .' Inactive is unsuccessful.');
        }
        
        redirect('administrator/candidate');
    }
	
    /**
     * Active a candidate
     * @return void
     */
    public function active()
    {
        $candidate_id = (int)$this->uri->segment(4);
        $res = $this->candidate_model->active_candidate($candidate_id);

        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Active is successful.');
        } else {
            $this->session->set_flashdata('message_error', $this->candidate_model->error_message .' Active is unsuccessful.');
        }
        
        redirect('administrator/candidate');
    }


    // set empty default form field values
    private function _set_fields()
    {
		$this->form_data = new StdClass;
        $this->form_data->id = 0;
        $this->form_data->cand_login = '';
        $this->form_data->cand_name = '';
        $this->form_data->cand_email = '';
        $this->form_data->cand_address = '';
        $this->form_data->phone = '';
        $this->form_data->cand_password = '';
        $this->form_data->cand_confirm_password = '';
        $this->form_data->cand_is_lock = '';
        $this->form_data->nid_passport_no = '';
        //$this->form_data->phone = '';
        $this->form_data->cand_is_active = '1';
        $this->form_data->filter_cand_name = '';
        $this->form_data->filter_cand_email = '';
        $this->form_data->filter_cand_address = '';
        $this->form_data->filter_phone = '';
        $this->form_data->filter_cand_is_active = '1';

    }

    // validation rules
    private function _set_rules($is_edit = false)
    {

        $this->form_validation->set_rules('cand_name', 'candidate  Name', 'trim|xss_clean|strip_tags');

        $this->form_validation->set_rules('cand_email', 'candidate Email', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('cand_address', 'candidate Full Address', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('phone', 'candidate Phone Number', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('cand_is_active', 'candidate Is Active?', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('filter_cand_name', 'candidate  Name', 'trim|xss_clean|strip_tags');

        $this->form_validation->set_rules('filter_cand_email', 'filter Candidate Email', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('filter_cand_address', 'filter Full Address', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('filter_phone', 'filter Candidate Phone Number', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('filter_cand_is_active', 'filter Candidate Is Active?', 'trim|xss_clean|strip_tags');

    }

}

/* End of file candidate.php */
/* Logrpion: ./appligrpion/controllers/administrator/candidate.php */