<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Assign_exam extends MY_Controller
{
    var $current_page = "assign-exam";
    var $exam_list = array();
    var $user_group_list = array();
    var $user_team_list = array();
    var $user_list = array();

    function __construct()
    {
        parent::__construct();
		$this->form_data = new StdClass;
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->library('robi_email');
        $this->load->model('exam_model');
        $this->load->model('user_team_model');
        $this->load->model('user_group_model');
        $this->load->model('smsnemail_model');
        $this->load->model('global/select_global_model');
        $this->load->model('global/insert_global_model');


        // pre-load lists
        $open_exams = $this->exam_model->get_open_exams();
        $this->exam_list[] = 'Select an Exam';
        if ($open_exams) {
            for ($i=0; $i<count($open_exams); $i++) {
                $this->exam_list[$open_exams[$i]->id] = $open_exams[$i]->exam_title;
            }
        }

        $user_groups = $this->user_group_model->get_user_groups();
        $this->user_group_list[] = 'Select an User Group';
        if ($user_groups) {
            for ($i=0; $i<count($user_groups); $i++) {
                $this->user_group_list[$user_groups[$i]->id] = $user_groups[$i]->group_name;
            }
        }

        $user_teams = $this->user_team_model->get_user_teams();
        $this->user_team_list[] = 'Select an User Team';
        if ($user_teams) {
            for ($i=0; $i<count($user_teams); $i++) {
                $this->user_team_list[$user_teams[$i]->id] = $user_teams[$i]->team_name;
            }
        }

        $users = $this->user_model->get_active_users('User');
        if ($users) {
            for ($i=0; $i<count($users); $i++) {
                $this->user_list[$users[$i]->id] = $users[$i]->user_first_name .' '. $users[$i]->user_last_name .' - '. $users[$i]->user_login;
            }
        }


        if($this->session->flashdata('ue_start_datetime')) {
            $this->session->keep_flashdata('ue_start_datetime');
        }
        if($this->session->flashdata('ue_end_datetime')) {
            $this->session->keep_flashdata('ue_end_datetime');
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
     * Display paginated list of exams
     * @return void
     */
    public function index()
	{
        // set page specific variables
        $page_info['title'] = 'Manage Exams'. $this->site_name;
        $page_info['view_page'] = 'administrator/assign_exam_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';


        $this->_set_fields();


        if ($this->input->post('assign_exam_submit')) {
            //print_r_pre('d');
            $exam_id = (int)$this->input->post('exam_id');
            if ($exam_id > 0) {
                redirect('administrator/assign_exam/assign/'. $exam_id);
            } else {
                $page_info['message_error'] = 'Please select an exam from the list.';
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

    public function assign($exam_id = 0)
    {
        // set page specific variables
        $page_info['title'] = 'Assign Exam to Users'. $this->site_name;
        $page_info['view_page'] = 'administrator/assign_exam_add_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';


        $this->_set_fields();
        $exam_id = (int)$exam_id;
        if ($exam_id <= 0) {
            $this->session->set_flashdata('message_success', 'Please select an exam from the list.');
            redirect('administrator/assign_exam');
        }

        $this->form_data->exam_id = $exam_id;
        $this->form_data->exam_id_hidden = $exam_id;

        if ($this->session->flashdata('ue_start_datetime')) {
            $ue_start_datetime = $this->session->flashdata('ue_start_datetime');
            $this->form_data->ue_start_date = date('d/m/Y', strtotime($ue_start_datetime));
            $this->form_data->ue_start_time = date('h:i A', strtotime($ue_start_datetime));
        }

        if ($this->session->flashdata('ue_end_datetime')) {
            $ue_end_datetime = $this->session->flashdata('ue_end_datetime');
            $this->form_data->ue_end_date = date('d/m/Y', strtotime($ue_end_datetime));
            $this->form_data->ue_end_time = date('h:i A', strtotime($ue_end_datetime));
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

    public function do_assign($exam_id = 0)
    {

        $this->load->helper('serialize_helper');
        // set page specific variables
        $page_info['title'] = 'Assign Exam to Users'. $this->site_name;
        $page_info['view_page'] = 'administrator/assign_exam_add_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $exam_id = (int)$exam_id;
        if ($exam_id <= 0) {
            $this->session->set_flashdata('message_success', 'Please select an exam from the list.');
            redirect('administrator/assign_exam');
        }


        $this->_set_fields();
        $this->_set_rules();

        $this->form_data->exam_id = $exam_id;
        $this->form_data->exam_id_hidden = $exam_id;

        $mail_array = array();
        $sms_array = array();


        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $exam_id = (int)$this->input->post('exam_id_hidden');
            $user_group_id = (int)$this->input->post('user_group_id');
            $user_team_id = (int)$this->input->post('user_team_id');
            $user_ids = $this->input->post('user_ids');

            $ue_start_date = $this->input->post('ue_start_date');
            $ue_start_time = $this->input->post('ue_start_time');
            $ue_end_date = $this->input->post('ue_end_date');
            $ue_end_time = $this->input->post('ue_end_time');
            $immediate_result = (int)$this->input->post('exam_immediate_result');





            if ($ue_start_date == '') {
                $ue_start_date = '';
            } else {
                $day = (int)substr($ue_start_date, 0, 2);
                $month = (int)substr($ue_start_date, 3, 2);
                $year = (int)substr($ue_start_date, 6, 4);
                $ue_start_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));

                if ($ue_start_time != '') {
                    $hour = (int)substr($ue_start_time, 0, 2);
                    $min = (int)substr($ue_start_time, 3, 2);
                    $ampm = trim(strtolower(substr($ue_start_time, 6, 2)));
                    if ($hour == 12) {
                        $hour = 0;
                    }
                    if ($ampm == 'pm') {
                        $hour = $hour + 12;
                    }
                    $ue_start_date = date('Y-m-d H:i', mktime($hour, $min, 0, $month, $day, $year));
                }
            }

            if ($ue_end_date == '') {
                $ue_end_date = '';
            } else {
                $day = substr($ue_end_date, 0, 2);
                $month = substr($ue_end_date, 3, 2);
                $year = substr($ue_end_date, 6, 4);
                $ue_end_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));

                if ($ue_end_time != '') {
                    $hour = (int)substr($ue_end_time, 0, 2);
                    $min = (int)substr($ue_end_time, 3, 2);
                    $ampm = trim(strtolower(substr($ue_end_time, 6, 2)));
                    if ($hour == 12) {
                        $hour = 0;
                    }
                    if ($ampm == 'pm') {
                        $hour = $hour + 12;
                    }
                    $ue_end_date = date('Y-m-d H:i', mktime($hour, $min, 0, $month, $day, $year));
                }
            }
            // set flash data
            $this->session->set_flashdata('exam_id', $exam_id);
            $this->session->set_flashdata('ue_start_datetime', $ue_start_date);
            $this->session->set_flashdata('ue_end_datetime', $ue_end_date);
            if(empty($user_ids))
            {
                if(!empty($user_team_id))
                {
                    $user_idsQuery = $this->select_global_model->Select_array('exm_users',array('user_team_id'=>$user_team_id),1);
                    if(!empty($user_idsQuery))
                    {
                        $user_ids=[];
                        foreach ($user_idsQuery as $key => $value):
                            $user_ids[]=$value['id'];  
                        endforeach;
                    }

                    //print_r_pre($user_ids); die();
                    if(!empty($user_ids))
                    {
                        foreach ($user_ids as $key => $value) 
                        {
                            //echo $value['id']; die();
                            $setID = $this->select_global_model->select_array_rand_limit('exm_exam_category',array('exam_id'=>$exam_id),1);

                            $existsChecking=$this->select_global_model->FlySelectExists('exm_user_exams',array(
                                'exam_id'=>$exam_id,
                                'ue_start_date'=>$ue_start_date,
                                'ue_end_date'=>$ue_end_date,
                                'user_id'=>$value,
                                'set_id'=>$setID[0]['category_id'],
                                'ue_status'=>'open'
                                
                            ));

                            //echo $existsChecking; die();

                            if($existsChecking==0)
                            {
                                $data[$key]['exam_id'] = $exam_id;
                                $data[$key]['ue_start_date'] = $ue_start_date;
                                $data[$key]['ue_end_date'] = $ue_end_date;
                                $data[$key]['user_id'] =$value;
                                $data[$key]['set_id'] = $setID[0]['category_id'];
                                $data[$key]['ue_status'] = 'open';
                                $data[$key]['immediate_result'] = $immediate_result;
                            }


                            $user_details = $this->user_model->get_user($value);
                            $exam_details = $this->exam_model->get_exam($exam_id);



                            $mail_result = $this->smsnemail_model->get_maillayout_by_examid($exam_id);
                            $sms_result = $this->smsnemail_model->get_smslayout_by_examid($exam_id);

                            //$phrase  = "You should eat fruits, vegetables, and fiber every day.";
                            $prefix = layoutPrefix('1');
                            $date = new DateTime($exam_details->exam_expiry_date);
                            $result = $date->format('Y-m-d');
                            $values   = array($exam_details->exam_title, $exam_details->exam_status, $user_details->user_first_name.' '.$user_details->user_last_name,$exam_details->exam_score,$result,$user_details->user_login,'');


                            $final_mail = str_replace($prefix, $values, $mail_result[0]['cat_layout']);
                            $final_sms = str_replace($prefix, $values, $sms_result[0]['cat_layout']);

                            //print_r_pre($mail_result[0]['cat_layout']);die;

                            if($user_details->user_email ) {
                                $mail_array[$key]['emailornumber'] = $user_details->user_email;
                                $mail_array[$key]['message'] = $final_mail;
                                $mail_array[$key]['type'] = 'email';
                                $mail_array[$key]['user_id'] = $value;
                                $mail_array[$key]['exam_id'] = $exam_id;
                            }

                            if($user_details->phone ) {
                                $sms_array[$key]['emailornumber'] = $user_details->phone;
                                $sms_array[$key]['message'] = $final_sms;
                                $sms_array[$key]['type'] = 'sms';
                                $sms_array[$key]['user_id'] = $value;
                                $sms_array[$key]['exam_id'] = $exam_id;
                            }
                        }



                        if(empty($data))
                        {
                            $this->session->set_flashdata('message_error', 'Failed, All User / Candidate is exists in exam.');
                            redirect('administrator/assign_status');
                        }


                    }
                    else
                    {
                        $this->session->set_flashdata('message_error', 'No User / Candidate Found, Please Select Team ID / User - Candidate!');
                        redirect('administrator/assign_status');
                    }
                    
                }
                else
                {
                    $this->session->set_flashdata('message_error', 'Please Select Team ID / User - Candidate!');
                    redirect('administrator/assign_status');
                }
                
            }
            else
            {
                foreach ($user_ids as $key => $value) {

                    $setID = $this->select_global_model->select_array_rand_limit('exm_exam_category',array('exam_id'=>$exam_id),1);

                    $existsChecking=$this->select_global_model->FlySelectExists('exm_user_exams',array(
                        'exam_id'=>$exam_id,
                        'ue_start_date'=>$ue_start_date,
                        'ue_end_date'=>$ue_end_date,
                        'user_id'=>$value,
                        'set_id'=>$setID[0]['category_id'],
                        'ue_status'=>'open'
                        
                    ));

                    if($existsChecking==0)
                    {
                        $data[$key]['exam_id'] = $exam_id;
                        $data[$key]['ue_start_date'] = $ue_start_date;
                        $data[$key]['ue_end_date'] = $ue_end_date;
                        $data[$key]['user_id'] = $value;
                        $data[$key]['set_id'] = $setID[0]['category_id'];
                        $data[$key]['ue_status'] = 'open';
                        $data[$key]['immediate_result'] = $immediate_result;
                    }


                    $user_details = $this->user_model->get_user($value);
                    $exam_details = $this->exam_model->get_exam($exam_id);



                    $mail_result = $this->smsnemail_model->get_maillayout_by_examid($exam_id);
                    //var_dump($exam_id);die;
                    $sms_result = $this->smsnemail_model->get_smslayout_by_examid($exam_id);

                    //$phrase  = "You should eat fruits, vegetables, and fiber every day.";
                    $prefix = layoutPrefix('1');
                    $date = new DateTime($exam_details->exam_expiry_date);
                    $result = $date->format('Y-m-d');
                    $values   = array($exam_details->exam_title, $exam_details->exam_status, $user_details->user_first_name.' '.$user_details->user_last_name,$exam_details->exam_score,$result,$user_details->user_login,'');


                    $final_mail = str_replace($prefix, $values, $mail_result[0]['cat_layout']);
                    $final_sms = str_replace($prefix, $values, $sms_result[0]['cat_layout']);

                    //print_r_pre($mail_result[0]['cat_layout']);die;

                    if($user_details->user_email ) {
                        $mail_array[$key]['emailornumber'] = $user_details->user_email;
                        $mail_array[$key]['message'] = $final_mail;
                        $mail_array[$key]['type'] = 'email';
                        $mail_array[$key]['user_id'] = $value;
                        $mail_array[$key]['exam_id'] = $exam_id;



                    }

                    if($user_details->phone ) {

                        $sms_array[$key]['emailornumber'] = $user_details->phone;
                        $sms_array[$key]['message'] = $final_sms;
                        $sms_array[$key]['type'] = 'sms';
                        $sms_array[$key]['user_id'] = $value;
                        $sms_array[$key]['exam_id'] = $exam_id;
                    }




                    //var_dump($mail_array);var_dump($user_details->phone);die;







                    
                }

                if(empty($data))
                {
                    $this->session->set_flashdata('message_error', 'Failed, All User / Candidate is exists in exam.');
                    redirect('administrator/assign_status');
                }

            }
            

            if($data)
            {
                 if($this->insert_global_model->globalinsertbatch('exm_user_exams',$data))
                 {

                        $setID = $this->select_global_model->select_where_in('exm_user_exams','user_id',$user_ids,array('exam_id'=>$exam_id));
                        /*echo 1;
                    die();*/
                        if($setID){
                            $set = array();
                            foreach ($setID as $key => $value) {
                               $qusid = $this->select_global_model->Select_array('exm_question_set_question_map',array('question_set_id'=>$value['set_id']));
                                foreach ($qusid as $ke => $val) {
                                    $this->insert_global_model->globalinsert('exm_user_exam_questions',array('user_exam_id'=>$value['id'],'user_exam_id'=>$value['id'],'question_id'=>$val['question_id'],'user_answer'=>'','qus_set'=>$val['question_set_id'],'marks'=>$val['question_mark'],'is_mandatory'=>$val['is_mandatory']));
                                }
                            }
                        }






                    if($mail_array)
                     $this->insert_global_model->globalinsertbatch('exm_smsoremail_job',$mail_array);
                     if($sms_array)
                     $this->insert_global_model->globalinsertbatch('exm_smsoremail_job',$sms_array);


                        $this->session->set_flashdata('message_success', 'Exam Assign is successful.');
                        redirect('administrator/assign_status');
                    }else{
                        $this->session->set_flashdata('message_error', 'Data insert failed');
                         redirect('administrator/assign_status');
                    }
            }else{
                 $this->session->set_flashdata('message_error', 'Empty data!');
                 redirect('administrator/assign_status');
            }
        }
    }


    // set empty default form field values
	private function _set_fields()
	{
		$this->form_data = new StdClass;
		$this->form_data->exam_id = 0;
        $this->form_data->exam_id_hidden = 0;
        $this->form_data->ue_start_date = date('d/m/Y');
        $this->form_data->ue_start_time = '12:00 AM';
        $this->form_data->ue_end_date = date('d/m/Y', strtotime('1 month', time()));
        $this->form_data->ue_end_time = '12:00 AM';
        $this->form_data->user_group_id = '';
        $this->form_data->user_team_id = '';
        $this->form_data->user_result_show = 1;
	}

	// validation rules
	private function _set_rules()
	{
        $this->form_validation->set_rules('ue_start_date', 'Start Date', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ue_start_time', 'Start Time', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ue_end_date', 'End Date', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ue_end_time', 'End Time', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('user_group_id', 'User Group', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('user_team_id', 'User Team', 'required|trim|xss_clean|strip_tags');
	}

}

/* End of file assign_exam.php */
/* Location: ./application/controllers/administrator/assign_exam.php */