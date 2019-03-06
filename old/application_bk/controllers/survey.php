<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Survey extends MY_Controller
{
    var $current_page = "survey";
    var $is_frontend = true;
    var $logged_in_user = false;

    function __construct()
    {
        parent::__construct($this->is_frontend);
        $this->load->helper('number');
        $this->load->model('survey_model');
        $this->load->library('table');
        $this->load->library('robi_email');

        $this->logged_in_user = $this->session->userdata('logged_in_user');
        
                
        // check if already logged in
        if ( ! $this->logged_in_user) {
            redirect('login');
        } else {
            /*if ($this->logged_in_user->user_type == 'Administrator') {
                redirect('administrator/dashboard');
            }*/
            if ((int)$this->logged_in_user->user_is_default_password == 1) {
            	redirect('profile/password');
            }
        }
    }

    public function survey_list($survey_id='')
    {

        $page_info['title'] = 'Survey'. $this->site_name;
        $page_info['view_page'] = 'administrator/pre_survey_start_view';

        $survey_html = '';
        $survey_id = (int)$survey_id;
        $this->load->model('survey_model');
        

        $survey = $this->survey_model->get_survey_for_user($survey_id);
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
                        
                        $survey_html .= '<a href="#" class="start_survey btn btn-success btn-large" data-survey_is_started="0">Start Survey</a>';
                        
                   /*$survey_html .= '<div class="start-survey">'.form_open('survey/start_survey');
                            $survey_html .= '<input type="hidden" name="survey_is_started" value="1" />';
                            $survey_html .= '<button type="submit" name="start_survey_button" value="Start Survey" class="btn btn-danger btn-large">Start Survey <i class="icon-play icon-white"></i></button>';
                        $survey_html .= form_close();

                    $survey_html .= '</div>';*/

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
    
    public function start_survey($current_question_index = 0)
    {
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

        if ($survey->current_question_index == 0) {
            $survey->is_first_question = 1;
        } else {
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
                                    $survey->current_question->ques_choices = maybe_unserialize($survey->current_question->ques_choices);
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
                                        $question_html .= '<button type="submit" id="next-button" class="btn btn-danger">Next Question <span class="icon-arrow-right icon-white"></span></button>';
                                    else: 
                                        $question_html .= '<button type="submit" id="finish-button" class="btn btn-danger">Finish Survey <span class="icon-arrow-right icon-white"></span></button>';
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

   
    public function complete_survey($current_question_index = 0)
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
            $data[$i]['user_id'] = $survey->user_id;
            $data[$i]['survey_id'] = $survey->survey_id;
            $data[$i]['question_id'] = $survey->survey_questions[$i]->id;
            $data[$i]['answer'] = $survey->survey_questions[$i]->user_answer;
            $data[$i]['added'] = date("Y-m-d H:i:s");
        }


        
        $res = $this->survey_model->complete_user_survey($data);

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
    
    
    public function get_completed_survey($survey_id)
    {
        $survey_html = '';
        $survey_id = (int)$survey_id;
        $this->load->model('survey_model');
        

        $survey = $this->survey_model->get_survey_for_user($survey_id);
        
        if ($survey) {
            $survey->survey_questions = $this->survey_model->get_survey_questions_answers($survey_id);
            
            $survey_html .= '<div class="span12">';
            $survey_html .= '<div class="content-wrap">';
                $survey_html .= '<div id="completed-survey">';
                    $survey_html .= '<h2 class="survey-title title">'.$survey->survey_title.'</h2>';
                    if ($survey->survey_description != ''): $survey_html .= '<div class="survey-description">'.nl2br($survey->survey_description).'</div>'; endif;
                    $survey_html .= '<h4>Status - Completed</h4>';
                    $survey_html .= "<br/>";
                    
                    $tbl_heading = array(
                        '0' => array('data'=> 'Question'),
                        '1' => array('data'=> 'Your Answer')
                    );
                    $this->table->set_heading($tbl_heading);

                    $tbl_template = array (
                        'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                        'table_close'         => '</table>'
                    );
                    $this->table->set_template($tbl_template);

                    foreach($survey->survey_questions as $k=>$v):
                        $tbl_row = array(
                            '0' => array('data'=> $v->ques_text),
                            '1' => array('data'=> $v->answer)
                        );
                        $this->table->add_row($tbl_row);
                    endforeach;

                    $records_table = $this->table->generate();
                    $survey_html .= $records_table;

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

        echo $survey_html;
    }
}

/* End of file content.php */
/* Location: ./application/controllers/content.php */