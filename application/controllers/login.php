<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends MY_Controller
{
    var $current_page = "login";
    var $tbl_exam_users_activity    = "exm_user_activity";
    var $tbl_exam_device_tracking   = "exm_device_tracking";
    
    function __construct()
    {
        parent::__construct();



		$this->load->library('robi_email');
        $this->load->helper('email');
        $this->load->helper('string');
        $this->load->model('group_privilage_model');
        $this->load->model('global/update_global_model');
        $this->load->model('global/insert_global_model');
        $this->load->model('global/select_global_model');


        $this->logged_in_user = $this->session->userdata('logged_in_user');

        // check if already logged in
        if ($this->session->userdata('logged_in_user')) {

            $logged_in_user = $this->session->userdata('logged_in_user');
            //print_r($logged_in_user); die();

            if ($logged_in_user->user_type == 'Administrator' || $logged_in_user->user_type == 'Super Administrator') {
                redirect('administrator/dashboard');
            } else if ($logged_in_user->user_type == 'User') {
                //redirect('home');
				if($this->session->userdata('user_privilage_name')){
					redirect('administrator/dashboard');
				}else{
					redirect('home');
				}
            } else {
                redirect('logout');
            }

        }
    }

    /**
     * Display login form
     * @return void
     */
    public function index()
	{
        $page_info['title'] = 'Login'. $this->site_name;
        $page_info['url_suffix'] = $this->config->item('url_suffix');
        $page_info['message_error'] = '';
        $page_info['redirect_url'] = '';

        if ($this->session->flashdata('redirect_url')) {
            $this->session->keep_flashdata('redirect_url');
            $page_info['redirect_url'] = $this->session->flashdata('redirect_url');
        }
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        if ($this->session->flashdata('show_box')) {
            $page_info['show_box'] = $this->session->flashdata('show_box');
        } else {
            $page_info['show_box'] = '1';
        }
        $page_info['srv'] = $this->select_global_model->getSrv('surveys_users',array(survey_anms=>'yes'));
        // print_r_pre($page_info['srv']);
        //print_r_pre($page_info['srv']);
       // $page_info['open_survey_html'] = $this->generate_survey_html($page_info['srv']);
        //print_r_pre($page_info['open_survey_html']);
		$this->load->view('login', $page_info);
	}


public function openSrv()
{
        $page_info['title'] = 'Login'. $this->site_name;
        $page_info['view_page'] = 'user/srv';
        $page_info['srv'] = $this->select_global_model->getsurveyforNotuser();
        $page_info['open_survey_html'] = $this->generate_survey_html($page_info['srv']);
       // print_r_pre($page_info['open_survey_html']);
       // $page_info['open_survey_html'] = $this->generate_survey_html($page_info['srv']);
        //print_r_pre($page_info['open_survey_html']);
        $this->load->view('user/layouts/default', $page_info);
}

    private function generate_survey_html($list)
    {
        $list_html = '';
        if (count($list) > 0) {
            $list_html .= '<ul class="list">';

            for ($i=0; $i<count($list); $i++) {
                $blink_text = null;
                $blink_class = null;
                //print_r_pre($list);
                $date_difference = (strtotime(date("Y-m-d H:i:s")) - strtotime($list[$i]->added) );
                if( ($list[$i]->status == 'open') && $date_difference > 0 && $date_difference < (48*3600) ){
                    $blink_text = '<img src="'.site_url().'assets/images/blink_image/Green_16x16.gif" alt="New" height="20" width="20">';
                    $blink_text .= '<strong><sup class="blink_text_green">New</sup></strong>';
                    $blink_class = 'blink_text_green';
                }
                if($list[$i]->status=="open"){
                    $url = base_url('Getsurveylist/'.(int)$list[$i]->survey_id);
                }else{
                    $url = "javascript:void();";
                }
                
                $list_html .= '<li>';
                    $list_html .= '<a href="'.$url.'" data-status="'. $list[$i]->status .'"><p>'. $blink_text. $list[$i]->survey_title .'-'. $blink_text. $list[$i]->status .'</p></a>';
                $list_html .= '</li>';
            }

            $list_html .= '</ul>';
        } else {
            $list_html .= lang('home_message_noitemsfound');
        }

        return $list_html;
    }


    public function Getsurveylist($survey_id='')
    {

        $page_info['title'] = 'Survey'. $this->site_name;
        $page_info['view_page'] = 'administrator/pre_survey_start_view';
        $survey_html = '';
        $survey_id = (int)$survey_id;
        $this->load->model('survey_model');
        $survey = $this->select_global_model->getsurveyForUser($survey_id);
        //print_r_pre($survey);
        if ($survey) {
            
            $survey->survey_questions = $this->survey_model->get_survey_questions_details($survey_id);
            
            $total_questions = 0;

            for ($i=0; $i<count($survey->survey_questions); $i++) {
                $survey->survey_questions[$i]->user_answer = '';
                $total_questions += 1;
            }
            $survey->survey_total_questions = $total_questions;
            $this->session->set_userdata('survey', $survey);
            $survey_html .= '<div class="span12">';
            $survey_html .= '<div class="content-wrap">';
                $survey_html .= '<div id="running-survey">';
                    $survey_html .= '<h2 class="survey-title title">'.$survey->survey_title.'</h2>';
                    if ($survey->survey_description != ''): $survey_html .= '<div class="survey-description">'.nl2br($survey->survey_description).'</div>'; endif;
                    $survey_html .= '<h4>Total Questions: '.$survey->survey_total_questions.'</h4>';
                    $survey_html .= "<br/><br/>";
                    $survey_html .= '<div class="survey-info">';
                        if ($error_text != ''):
                        $survey_html .= '<div class="notice alert alert-error">';
                            echo $error_text;
                        $survey_html .= '</div>';
                        endif;

                        $survey_html .= '<div class="notice alert alert-info">';
                            $survey_html .= "Please ensure that you have enough time and resource available to complete the survey. Once you start the
                            survey you have to restart again it, if any problem occurred in the middle of the survey.<br /><br />
                            Please don't close the browser window before completing the survey.";
                        $survey_html .= '</div>';
                        if($survey->survey_total_questions!=0){
                            $survey_html .= '<a href="#" class="start_surveys btn btn-success btn-large" data-survey_is_started="0">Start Survey</a>';
                        }
                $survey_html .= '</div>';                
            $survey_html .= '</div>';
            $survey_html .= '</div>';

        } else {
            $survey_html .= '<div class="span12">';
            $survey_html .= '<div class="content-wrap">';
            $survey_html .= 'Survey not found.';
            $survey_html .= '</div>';
            $survey_html .= '</div>';
        }



        $page_info['survey_start'] = $survey_html;
        $this->load->view('user/layouts/default', $page_info);
    }

public function startSurveyNoUser($current_question_index = 0)
    {
        //print_r_pre($current_question_index);
        $question_html = '';
        $answer = null;
        if($this->input->post('answer')){
            $answer = $this->input->post('answer');
        }
        
        $survey = $this->session->userdata('survey');        
        if($answer){
          $survey->survey_questions[($current_question_index-1)]->user_answer = $answer;  
        }
        
        $survey->current_question_index = $current_question_index;
        $survey->current_question = $survey->survey_questions[$survey->current_question_index];

        if ($survey->current_question_index == 0){
            $survey->is_first_question = 1;
        }else{
            $survey->is_first_question = 0;
        }

        if ($survey->current_question_index == ($survey->survey_total_questions - 1)) {
            $survey->is_last_question = 1;
        } else {
            $survey->is_last_question = 0;
        }

        $this->session->set_userdata('survey', $survey);       
        $survey = $this->session->userdata('survey');
        
        $current_question_number = $survey->current_question_index + 1;
        $question_number_str = 'Question NO: '. $current_question_number .' of '. $survey->survey_total_questions;
        
        $question_html .= '<div class="span12">';
            $question_html .= '<div class="content-wrap">';
                $question_html .= '<div id="running-survey">';
                    $question_html .= '<h2 class="survey-title">'. $survey->survey_title .'</h2>';
                    $question_html .= form_open();
                        $question_html .= '<div class="survey-header">';
                            $question_html .= '<div class="qn">'. $question_number_str .'</div>';
                        $question_html .= '</div>';

                        $question_html .= '<div class="survey-body">';
                            $question_html .= '<div class="question">';
                                $question_html .= '<p><strong>Question:</strong></p>';
                                $question_html .= '<div class="text">';
                                    $question_html .= nl2br($survey->current_question->ques_text);
                                $question_html .= '</div>';
                            $question_html .= '</div>';
                            $question_html .= '<div class="answers">';
                                $question_html .= '<input type="hidden" name="current_index" class="current_index" value="'.($current_question_number-1).'" />';
                                $question_html .= '<p><strong>Answer:</strong></p>';
                                $question_html .= '<div class="choices-cont">';
                                if(isset($survey->current_question->ques_choices)){
                                    $survey->current_question->ques_choices = maybe_unserialize($survey->current_question->ques_choices);
                                }else{
                                    @$survey->current_question->ques_choices = "";
                                }
                                    
                                    if($survey->current_question->ques_type == 'option_based') :
                                        $user_answer = null;
                                        $user_answer = $survey->survey_questions[$survey->current_question_index]->user_answer;
                                        for($i=0; $i<count($survey->current_question->ques_choices); $i++) : 
                                            $question_html .= '<div class="choice">';
                                            if($user_answer == $survey->current_question->ques_choices[$i]['text']){
                                                $is_checked = 'checked';
                                            }else{
                                                $is_checked = null;
                                            }
                                            $question_html .= '<input name="answer" id="answer'.$i.'" value="'.$survey->current_question->ques_choices[$i]['text'].'" '.$is_checked.' type="radio" />';
                                            $question_html .= '<label for="answer'.$i.'" class="text">'.nl2br($survey->current_question->ques_choices[$i]['text']).'</label>';
                                            $question_html .= '</div>';
                                        endfor;
                                    else:
                                        $user_answer = null;
                                        $user_answer = $survey->survey_questions[$survey->current_question_index]->user_answer;
                                        $question_html .= '<textarea name="answer" cols="30" rows="10" placeholder="Write your answer here">'.$user_answer.'</textarea>';
                                    endif;
                                $question_html .= '</div>';
                            $question_html .= '</div>';
                        $question_html .= '</div>';

                        $question_html .= '<div class="survey-footer">';
                            $question_html .= '<div class="row-fluid">';
                                $question_html .= '<div class="span3">';
                                    if(!$survey->is_first_question) : 
                                        $question_html .= '<button type="submit" id="previous-button" class="btn"><span class="icon-arrow-left"></span> Previous Question</button>';
                                    endif;
                                $question_html .= '</div>';

                                $question_html .= '<div class="span6 center">';
                                $question_html .= '</div>';

                                $question_html .= '<div class="span3 right">';
                                    if(!$survey->is_last_question) : 
                                        //$question_html .= '<a href="#" class="next-button btn btn-danger" data-current_question_id="'.$current_question_index.'">Next Question <span class="icon-arrow-right icon-white"></span></a>';
                                        $question_html .= '<button type="submit" id="next-buttons" class="btn btn-danger">Next Question <span class="icon-arrow-right icon-white"></span></button>';
                                    else: 
                                        $question_html .= '<button type="submit" id="finish-buttons" class="btn btn-danger">Finish Survey <span class="icon-arrow-right icon-white"></span></button>';
                                    endif;
                                $question_html .= '</div>';
                            $question_html .= '</div>';
                        $question_html .= '</div>';
                    $question_html .= form_close();

                $question_html .= '</div>';
            $question_html .= '</div>';
        $question_html .= '</div>';

        echo $question_html;
        
    }

    public function completeSurveyNoUser($current_question_index = 0)
    {


        $survey_html = '';        
        $data = array();
        $answer = null;
        if($this->input->post('answer')){
            $answer = $this->input->post('answer');
        }
        
        $survey = $this->session->userdata('survey');
        
        if($answer){
          $survey->survey_questions[($current_question_index-1)]->user_answer = $answer;  
        }

        $this->session->set_userdata('survey', $survey);       
        $survey = $this->session->userdata('survey');
        for($i=0; $i<count($survey->survey_questions); $i++){
            $data[$i]['user_id'] = time();
            $data[$i]['survey_id'] = $survey->survey_id;
            $data[$i]['question_id'] = $survey->survey_questions[$i]->id;
            $data[$i]['answer'] = $survey->survey_questions[$i]->user_answer;
            $data[$i]['added'] = date("Y-m-d H:i:s");
            $data[$i]['type_ans'] = 2;
        }


        
        $res = $this->select_global_model->completeNoUsersurvey($data);

        //var_dump($res);die;

        
        if($res){
            //$this->robi_email->survey_complete_notification($this->session->userdata('logged_in_user')->id);
            $this->session->set_flashdata('message_success', 'Survey Completion is successful.');
            
            $survey_html .= '<div class="span12">';
                $survey_html .= '<div class="content-wrap">';
                    $survey_html .= '<div id="running-survey">';
                        $survey_html .= '<div class="notice alert alert-info">';
                            $survey_html .= "Thank you for completing the survey.";
                        $survey_html .= '</div>';
                    $survey_html .= '</div>';
                $survey_html .= '</div>';
            $survey_html .= '</div>';
        }else{
            $this->session->set_flashdata('message_error', 'Survey Completion is not successful because of some error occur.');
            
            $survey_html .= '<div class="span12">';
                $survey_html .= '<div class="content-wrap">';
                    $survey_html .= 'Sorry the survey are not completed because of db error.';
                $survey_html .= '</div>';
            $survey_html .= '</div>';
        }
        
        echo $survey_html;
        
    }

    function get_ip_address()
    {
       
        $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
        return $ipaddress;
    
    }


    /**
     * Validate and Authenticate Username and Password then redirect to the dashboard
     * @return void
     */
    public function do_login()
    {
        $this->load->library('session');

        //var_dump($this->input->ip_address());die;

        // check authentication
        $username = trim($this->input->post('user_login'));
        $password = trim($this->input->post('user_password'));
        $redirect_url = $this->input->post('redirect_url');

        $logged_in_user = $this->user_model->check_username_password($username, $password);


        $failed_login_message = trim($this->global_options['failed_login_message']);
        if ($failed_login_message == '') {
            $failed_login_message = 'Authentication failed.';
        }

        $locked_login_message = trim($this->global_options['locked_login_message']);
        if ($locked_login_message == '') {
            $locked_login_message = 'Your account has been locked.';
        }


        if( ! $logged_in_user) {

            $this->session->set_flashdata('message_error', $failed_login_message);
            $this->user_model->increment_failed_login_count($username);
            if ($this->user_model->error_message == 'user_locked') {

                $user_id = 0;
                $user = $this->user_model->get_user_by_login($username);
                //print_r($user); die();
                if ($user) {
                    $user_id = $user->id;
                }
                $this->session->set_flashdata('message_error', $locked_login_message);
                log_message("info", 'User locked: consecutive wrong password given', false, 'user locked', $user_id);
            }
            
            log_message("info", 'Unsuccessful login: username/password did not matched', false, 'unsuccessful login');
            redirect('login');

        } else {
            
            if($logged_in_user->user_type == 'Super Administrator') {
                $permited_privilages = $this->group_privilage_model->get_privilages();
            }else{
                $permited_privilages = $this->group_privilage_model->get_permitted_privilages($logged_in_user->id);
            }
            $privilage_name = array();
            if($permited_privilages){
                foreach($permited_privilages as $k=>$v){
                    $privilage_name[] = $v->privilage_name;
                }
            }

            $this->session->set_userdata('user_privilage_name', $privilage_name);

            if ((int)$logged_in_user->user_is_lock == 1) {
                $this->session->set_flashdata('message_error', $locked_login_message);
                log_message("info", 'Unsuccessful login: user account locked', false, 'unsuccessful login');
                redirect('login');
            }

            //print_r_pre($logged_in_user);die;
            $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$logged_in_user->id,
                'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'login'));
            //
            $insert_data = $this->insert_global_model->globalinsert($this->tbl_exam_device_tracking,array('ip_address'=>$this->input->ip_address(),'user_id'=>$logged_in_user->id,'activity'=>'login'));
            if ($redirect_url != '') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \''. $redirect_url .'\' page', false, 'successful login');
                redirect($redirect_url);
            } elseif ($logged_in_user->user_type == 'Super Administrator') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            } elseif ($logged_in_user->user_type == 'Administrator') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            } elseif ($logged_in_user->user_type == 'Recruitment Manager') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            } elseif ($logged_in_user->user_type == 'Head of HR') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            }elseif ($logged_in_user->user_type == 'Subject Matter Experts') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            }
            elseif ($logged_in_user->user_type == 'System Auditor') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            }
            elseif ($logged_in_user->user_type == 'Recruitment Assistant - Question') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            }
            elseif ($logged_in_user->user_type == 'Recruitment Assistant – Result') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            }
            elseif ($logged_in_user->user_type == 'Examiner') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'administrator/dashboard\' page', false, 'successful login');
                redirect('administrator/dashboard');
            }



            elseif ($logged_in_user->user_type == 'User') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'home\' page', false, 'successful login');
				
				if($this->session->userdata('user_privilage_name')){
					redirect('administrator/dashboard');
				}else{
					redirect('home');
				}
            }elseif ($logged_in_user->user_type == 'Candidate') {
                $this->user_model->reset_failed_login_count($logged_in_user->id, $username);
                $this->session->set_userdata('logged_in_user', $logged_in_user);
                log_message("info", 'Successful login: redirected to \'home\' page', false, 'successful login');


                redirect('home');

            }

            else {
                $this->session->set_flashdata('message_error', $failed_login_message);
                $this->user_model->increment_failed_login_count($username);
                log_message("info", 'Unsuccessful login: unknown reason', false, 'unsuccessful login');
                redirect('login');
            }
        }
    }

    /**
     * Generate and send new password by email
     * @return void
     */


    public function send_new_password()
    {

        $user_id = strip_slashes(trim($this->input->post('fp_user_login')));


        $permitted=$this->user_model->get_user_password_reset_permission($user_id);
        if($permitted==0)
        {
            $this->session->set_flashdata('message_error', 'You are not elegible to reset your password, Please contact with system admin.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        if ($user_id == '') {
            $this->session->set_flashdata('message_error', 'Login ID can not be empty.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        $user = $this->user_model->get_user_by_login($user_id);

        // user not found
        if ( ! $user) {
            $this->session->set_flashdata('message_error', $this->user_model->error_message);
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        $user_email = $user->user_email;
        $user_active = (int)$user->user_is_active;
        $user_locked = (int)$user->user_is_lock;

        // user is not active
        if ($user_active == 0) {
            $this->session->set_flashdata('message_error', 'User not found with the login id.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        // user is locked
        if ($user_locked == 1) {
            $this->session->set_flashdata('message_error', 'User is locked. Please contact with an Administrator to unlock your account.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        // invalid email address
        if ( ! valid_email($user_email) ) {
            $this->session->set_flashdata('message_error', 'Invaid email address. Please contact with an Administrator to update your email address.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        // error checking complete; try to send an email
        $new_password = random_string();
        $new_password_md5 = md5($new_password);
        $mail_sent_res = $this->robi_email->forgot_password($user, $new_password);

        // unable to send email
        if ( ! $mail_sent_res) {
            $this->session->set_flashdata('message_error', 'There was a problem in sending email. Please try again later.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }

        // email sent successfully; update db;
        $update_user_res = $this->user_model->reset_password($user->id, $new_password_md5);
        if ($update_user_res) {
            $this->session->set_flashdata('message_success', 'Password reset is successful. Please check your email address for new password.');
            redirect('login');
        } else {
            $this->session->set_flashdata('message_error', 'Database problem occurred. Please try again later.');
            $this->session->set_flashdata('show_box', '2');
            redirect('login');
        }
    }
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */