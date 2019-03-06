<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Qmsglobalcontroller extends MY_Controller
{
	var $current_page = "Qms";
    var $cat_list = array();
    var $exam_type_list_filter = array();
    var $exam_status_list_filter = array();
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
        //$this->form_data = new StdClass;
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('global/select_global_model');
        $this->load->model('global/update_global_model');
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

    public function index()
    {
    	echo "error_log";
    }

    public function getcategoryquestion($value='')
    {

        $type = $this->input->get_post('type');
        $question = $this->select_global_model->Select_array('exm_questions',array('category_id'=>$value,'status'=>2,'ques_type'=>$type));

        $questionRand = $this->select_global_model->FlyQuery(array('category_id'=>$value,'status'=>2,'ques_type'=>$type),'exm_questions','select','id',array('id'=>'RANDOM'),array(),5);

        //print_r($questionRand); die();


        $dataArray=[];
        foreach($question as $ques):
            $dataArray[]=array(
                'id'=>$ques['id'],
                'category_id'=>$ques['category_id'],
                'ques_text'=>html_entity_decode($ques['ques_text']),
                'ques_choices'=>$ques['ques_choices'],
                'ques_type'=>$ques['ques_type'],
                'ques_added'=>$ques['ques_added'],
                'ques_expiry_date'=>$ques['ques_expiry_date'],
                'question_approval_status'=>$ques['question_approval_status'],
                'admin_group'=>$ques['admin_group'],
                'status'=>$ques['status'],
                'created_by'=>$ques['created_by'],
                'created_time'=>$ques['created_time'],
                'is_mandatory'=>$ques['is_mandatory'],
                'mark'=>$ques['mark'],
                'neg_mark'=>$ques['neg_mark']
            );
        endforeach;

        //print_r($dataArray); die();

        echo json_encode(array('question'=>$dataArray,'questionrand'=>$questionRand));
    }

    public function getcategorysurveyquestion($value='')
    {

        $type = $this->input->get_post('type');
        $question = $this->select_global_model->Select_array('exm_survey_questions',array('category_id'=>$value));

        $questionRand = $this->select_global_model->FlyQuery(array('category_id'=>$value),'exm_survey_questions','select','id',array('id'=>'RANDOM'),array(),5);

        //print_r($questionRand); die();


        $dataArray=[];
        foreach($question as $ques):
            $dataArray[]=array(
                'id'=>$ques['id'],
                'category_id'=>$ques['category_id'],
                'ques_text'=>html_entity_decode($ques['ques_text']),
                'mark'=>1,
                'neg_mark'=>0
            );
        endforeach;

        //print_r($dataArray); die();

        echo json_encode(array('question'=>$dataArray,'questionrand'=>$questionRand));
    }

    public function changeQuestionstatus($value='')
    {
       $page_info['message_error'] = '';
       $page_info['message_success'] = '';
       $status = "Pending";
        $getVal = explode(":", $value);
        if($getVal[0]==2){
            $status = "Approved";
        }
        if($getVal){
             $question = $this->select_global_model->Select_array('exm_questions',array('id'=>$getVal[1]));
            if($question){
                if($this->update_global_model->globalupdate('exm_questions',array('id'=>$getVal[1]),array('status'=>$getVal[0]))){
                      $this->session->set_flashdata('message_success', 'Question '.$status.' set successfully!');
                    redirect('administrator/question');
                }else{
                    $this->session->set_flashdata('message_error', 'Question '.$status.' set failed!');
                    redirect('administrator/question');
                }
             //print_r_pre($question);
            }else{
                $this->session->set_flashdata('message_error', 'Wrong Data selected!');
                redirect('administrator/question');
            }
        }else{
            redirect('administrator/question');
        }
        $question = $this->select_global_model->Select_array('exm_questions',array('category_id'=>$value));
        echo json_encode($question);
    }


    public function changeQuestionstatusquestionpending($value='')
    {
       $page_info['message_error'] = '';
       $page_info['message_success'] = '';
       $status = "Pending";
        $getVal = explode(":", $value);
        if($getVal[0]==2){
            $status = "Approved";
        }
        if($getVal){
             $question = $this->select_global_model->Select_array('exm_questions',array('id'=>$getVal[1]));
            if($question){
                if($this->update_global_model->globalupdate('exm_questions',array('id'=>$getVal[1]),array('status'=>$getVal[0]))){
                      $this->session->set_flashdata('message_success', 'Question '.$status.' set successfully!');
                    redirect($_SERVER['HTTP_REFERER']);
                }else{
                    $this->session->set_flashdata('message_error', 'Question '.$status.' set failed!');
                    redirect($_SERVER['HTTP_REFERER']);
                }
             //print_r_pre($question);
            }else{
                $this->session->set_flashdata('message_error', 'Wrong Data selected!');
                redirect($_SERVER['HTTP_REFERER']);
            }
        }else{
            redirect($_SERVER['HTTP_REFERER']);
        }
        $question = $this->select_global_model->Select_array('exm_questions',array('category_id'=>$value));
        echo json_encode($question);
    }


    public function changeQuestionstatusquestionreject($value='')
    {
       $page_info['message_error'] = '';
       $page_info['message_success'] = '';
       $status = "Pending";
        $getVal = explode(":", $value);
        if($getVal[0]==2){
            $status = "Approved";
        }
        if($getVal){
             $question = $this->select_global_model->Select_array('exm_questions',array('id'=>$getVal[1]));
            if($question){
                if($this->update_global_model->globalupdate('exm_questions',array('id'=>$getVal[1]),array('status'=>$getVal[0]))){
                      $this->session->set_flashdata('message_success', 'Question '.$status.' set successfully!');
                    redirect($_SERVER['HTTP_REFERER']);
                }else{
                    $this->session->set_flashdata('message_error', 'Question '.$status.' set failed!');
                    redirect($_SERVER['HTTP_REFERER']);
                }
             //print_r_pre($question);
            }else{
                $this->session->set_flashdata('message_error', 'Wrong Data selected!');
                redirect($_SERVER['HTTP_REFERER']);
            }
        }else{
            redirect($_SERVER['HTTP_REFERER']);
        }
        $question = $this->select_global_model->Select_array('exm_questions',array('category_id'=>$value));
        echo json_encode($question);
    }


    public function changeQuestionstatusquestionappreov($value='')
    {
       $page_info['message_error'] = '';
       $page_info['message_success'] = '';
       $status = "Pending";
        $getVal = explode(":", $value);
        if($getVal[0]==2){
            $status = "Approved";
        }
        if($getVal){
             $question = $this->select_global_model->Select_array('exm_questions',array('id'=>$getVal[1]));
            if($question){
                if($this->update_global_model->globalupdate('exm_questions',array('id'=>$getVal[1]),array('status'=>$getVal[0]))){
                      $this->session->set_flashdata('message_success', 'Question '.$status.' set successfully!');
                    redirect($_SERVER['HTTP_REFERER']);
                }else{
                    $this->session->set_flashdata('message_error', 'Question '.$status.' set failed!');
                    redirect($_SERVER['HTTP_REFERER']);
                }
             //print_r_pre($question);
            }else{
                $this->session->set_flashdata('message_error', 'Wrong Data selected!');
                redirect($_SERVER['HTTP_REFERER']);
            }
        }else{
            redirect($_SERVER['HTTP_REFERER']);
        }
        $question = $this->select_global_model->Select_array('exm_questions',array('category_id'=>$value));
        echo json_encode($question);
    }

}
?>