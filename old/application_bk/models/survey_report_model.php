<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Survey_report_model extends CI_Model
{
    private $table_name_categories = 'survey_categories';
    private $table_name_surveys = 'surveys';
    private $table_name_questions = 'survey_questions';
    private $table_name_survey_questions = 'surveys_questions';
    private $table_name_answer = 'survey_answer';
    private $table_name_survey_user = 'surveys_users';
    private $table_user = 'users';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    
    public function get_survey($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $this->db->select($this->table_name_surveys.'.survey_title, '.$this->table_name_surveys.'.survey_description, '.$this->table_name_questions.'.ques_text, '.$this->table_name_questions.'.ques_type, '.$this->table_name_questions.'.ques_choices, '.$this->table_name_survey_questions.'.survey_id, '.$this->table_name_survey_questions.'.question_id');
        $this->db->from($this->table_name_surveys);
        $this->db->join($this->table_name_survey_questions, $this->table_name_surveys.'.id = '.$this->table_name_survey_questions.'.survey_id');
        $this->db->join($this->table_name_questions, $this->table_name_questions.'.id = '.$this->table_name_survey_questions.'.question_id');
        
        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                $this->db->where($filter[$key]['field'], $filter[$key]['value']);
            }
        }
        
        $this->db->order_by($this->table_name_surveys.'.survey_title','ASC');
        $this->db->order_by($this->table_name_questions.'.ques_text','ASC');
        
        if($limit && ($limit > 0) ){
            $this->db->limit($limit, $offset);
        }   
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $result['result'] = $query->result();
            // record count
            foreach($result['result'] as $k=>$v){
                $result['count'] += 1;
            }
        }
        return $result;
    }
    
    
    public function get_option_answer_count($survey_id, $question_id, $option_text)
    {
        $count = 0;
        $this->db->select('count('.$this->db->dbprefix($this->table_name_answer).'.answer) as no_of_answer');
        $this->db->from($this->table_name_answer);
        $this->db->where($this->table_name_answer.'.survey_id', $survey_id);
        $this->db->where($this->table_name_answer.'.question_id', $question_id);
        $this->db->where($this->table_name_answer.'.answer', $option_text);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $res = $query->result();
            if($res){
                $count = $res[0]->no_of_answer;  
            }
        }
 
        return $count;
    }

    public function get_descriptive_answer_count($survey_id, $question_id)
    {
        $count = 0;
        $this->db->select('count('.$this->db->dbprefix($this->table_name_answer).'.answer) as no_of_answer');
        $this->db->from($this->table_name_answer);
        $this->db->where($this->table_name_answer.'.survey_id', $survey_id);
        $this->db->where($this->table_name_answer.'.question_id', $question_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $res = $query->result();
            if($res){
                $count = $res[0]->no_of_answer;  
            }
        }
 
        return $count;
    }
	
	public function get_total_assigned($survey_id, $question_id)
    {
        $count = 0;
        $this->db->select('count('.$this->db->dbprefix($this->table_name_answer).'.user_id) as total');
        $this->db->from($this->table_name_answer);
        $this->db->where($this->table_name_answer.'.survey_id', $survey_id);
        $this->db->where($this->table_name_answer.'.question_id', $question_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $res = $query->result();
            if($res){
                $count = $res[0]->total;  
            }
        }
 
        return $count;
    }
    
    public function get_survey_details($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $this->db->select($this->table_name_surveys.'.survey_title, '.$this->table_name_surveys.'.survey_description, '.$this->table_name_questions.'.ques_text, '.$this->table_name_questions.'.ques_type, '.$this->table_name_questions.'.ques_choices, '.$this->table_name_survey_questions.'.survey_id, '.$this->table_name_survey_questions.'.question_id, '.$this->table_name_answer.'.answer, '.$this->table_name_answer.'.user_id, '.$this->table_user.'.user_login, '.$this->table_user.'.user_first_name');
        $this->db->from($this->table_name_surveys);
        $this->db->join($this->table_name_survey_questions, $this->table_name_surveys.'.id = '.$this->table_name_survey_questions.'.survey_id');
        $this->db->join($this->table_name_questions, $this->table_name_questions.'.id = '.$this->table_name_survey_questions.'.question_id');
        $this->db->join($this->table_name_answer, $this->table_name_answer.'.survey_id = '.$this->table_name_survey_questions.'.survey_id AND '.$this->db->dbprefix($this->table_name_answer).'.question_id = '.$this->db->dbprefix($this->table_name_survey_questions).'.question_id');
        $this->db->join($this->table_user, $this->table_name_answer.'.user_id = '.$this->table_user.'.id');
        
        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                $this->db->where($this->table_name_answer.'.'.$filter[$key]['field'], $filter[$key]['value']);
            }
        }

        
        $this->db->order_by($this->table_name_surveys.'.survey_title','ASC');
        $this->db->order_by($this->table_name_questions.'.ques_text','ASC');
        
        if($limit && ($limit > 0) ){
            $this->db->limit($limit, $offset);
        }   
        
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $result['result'] = $query->result();
            // record count
            foreach($result['result'] as $k=>$v){
                $result['count'] += 1;
            }
        }
        return $result;
    }
}

/* End of file category_model.php */
/* Location: ./application/models/category_model.php */