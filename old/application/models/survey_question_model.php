<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Survey_question_model extends CI_Model
{
    private $table_name = 'survey_questions';
    private $table_users = 'users';
    private $table_questions = 'surveys_questions';
    
    private $table_exams = 'survey_exams';
    private $table_user_exams = 'survey_user_exams';
    private $table_user_exam_questions = 'survey_user_exam_questions';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Get number of questions
     *
     * @return int
     */
    public function get_question_count()
    {
        $result=$this->db->count_all($this->table_name);
        return $result;
    }

    public function get_questions()
    {
        $this->db->order_by('ques_added','DESC');
        $this->db->order_by('ques_text','ASC');
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    
    public function check_duplicate_questions($question_category_id, $question_text)
    {
        $this->db->where('category_id', $question_category_id);
        $this->db->where('ques_text', $question_text);
        $this->db->limit(1);
        $query = $this->db->get($this->table_name);
        
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    
     public function get_questions_by_survey($survey_id)
    {
        $this->db->select($this->table_name.'.*');
        $this->db->from($this->table_name);
        $this->db->join($this->table_questions, $this->table_name.'.id = '.$this->table_questions.'.question_id');
        $this->db->where($this->table_questions.'.survey_id', $survey_id);
        $this->db->order_by($this->table_name.'.id','ASC');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    
    
    public function get_available_questions()
    {
        $this->db->order_by('ques_added','DESC');
        $this->db->order_by('ques_text','ASC');
        //$this->db->where('ques_expiry_date >=', date("Y-m-d"));
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    /**
     * Get paginated list of questions
     *
     * @param $limit
     * @param int $offset
     * @param array $filter
     * @return bool
     */
    public function get_paged_questions($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_expired') {
                    if ($value['value'] == 'expired') {
                        $this->db->where(' ques_expiry_date <= NOW() AND ques_expiry_date != \'0000-00-00 00:00:00\' AND ques_expiry_date IS NOT NULL ', '', false);
                    } elseif ($value['value'] == 'available') {
                        $this->db->where(' ( ques_expiry_date > NOW() OR ques_expiry_date = \'0000-00-00 00:00:00\' OR ques_expiry_date IS NULL ) ', '', false);
                    }
                } elseif ($key == 'filter_question') {
                    $this->db->like($value['field'], $value['value']);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }
        $this->db->order_by('category_id', 'ASC');
        $this->db->order_by('ques_added', 'DESC');
        $this->db->order_by('id','DESC');
        $query = $this->db->get($this->table_name, $limit, $offset);

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_expired') {
                        if ($value['value'] == 'expired') {
                            $this->db->where(' ques_expiry_date <= NOW() AND ques_expiry_date != \'0000-00-00 00:00:00\' AND ques_expiry_date IS NOT NULL ', '', false);
                        } elseif ($value['value'] == 'available') {
                            $this->db->where(' ( ques_expiry_date > NOW() OR ques_expiry_date IS NULL ) ', '', false);
                        }
                    } elseif ($key == 'filter_question') {
                        $this->db->like($value['field'], $value['value']);
                    } else {
                        $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }
            $result['count'] = $this->db->count_all_results($this->table_name);
        }

        return $result;
    }

    /**
     * Get single question by question ID
     * $is_backend = true, returns question of any status
     * $is_backend = false, returns question of 'published' status
     *
     * @param $question_id
     * @param bool $is_backend
     * @return bool
     */
    public function get_question($question_id, $is_backend = false)
    {
        $question_id = (int)$question_id;

        if ($question_id > 0) {

            /*if (!$is_backend) {
                $this->db->where("(question_status = 'published' OR question_status = 'archived')", null, false);
            }*/
            $this->db->where('id', $question_id);
    		$query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Question not found. Invalid id.';
                return FALSE;
            }

        } else {
            $this->error_message = 'Invalid id.';
            return FALSE;
        }
    }

    public function get_question_by_category($category_id, $is_backend = false)
    {
        $category_id = (int)$category_id;
        
        if ($category_id > 0) {

            $this->db->select('id, category_id, question_title, question_description, question_is_popular, question_launch_date, question_expiry_date');
            /*$this->db->select('id, category_id, question_title, question_description, question_status, question_is_popular, question_launch_date, question_expiry_date');
            if (!$is_backend) {
                $this->db->where('question_status', 'published');
            }*/
            $this->db->where('category_id', $category_id);
            $this->db->order_by('question_title', 'ASC');
            $query = $this->db->get($this->table_name);
            
            return $query->result();

        } else {
            $this->error_message = 'Invalid category id.';
            return FALSE;
        }
    }

    //-------------------------------------------------------------------------
    // Question answers statistics
    //-------------------------------------------------------------------------

    public function get_answer_details($question_id, $answer_type, $limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        $question_id = (int)$question_id;
        if ($answer_type == 'unanswered') { $answer_type = ''; }

        if ($question_id > 0) {

            $this->db->select('user_exam_id, question_id, user_id, exam_id, ue_start_date, ue_end_date, user_answer, user_login, user_first_name, user_last_name, user_email, exam_title');

            $this->db->join($this->table_user_exams, $this->table_user_exams .'.id = '. $this->table_user_exam_questions .'.user_exam_id');
            $this->db->join($this->table_users, $this->table_users .'.id = '. $this->table_user_exams .'.user_id');
            $this->db->join($this->table_exams, $this->table_exams .'.id = '. $this->table_user_exams .'.exam_id');

            $this->db->where('ue_status', 'complete');
            $this->db->where('user_answer !=', 'unknown');
            $this->db->where('question_id', $question_id);
            $this->db->where('user_answer', $answer_type);
            $this->db->order_by('exam_title');
            $this->db->order_by('ue_start_date', 'DESC');
            $this->db->limit($limit, $offset);

            $query = $this->db->get($this->table_user_exam_questions);

            if ($query->num_rows() > 0) {

                $result['result'] = $query->result();

                // record count
                $this->db->select('user_exam_id, question_id, user_id, exam_id, ue_start_date, ue_end_date, user_answer, user_login, user_first_name, user_last_name, user_email, exam_title');
                $this->db->join($this->table_user_exams, $this->table_user_exams .'.id = '. $this->table_user_exam_questions .'.user_exam_id');
                $this->db->join($this->table_users, $this->table_users .'.id = '. $this->table_user_exams .'.user_id');
                $this->db->join($this->table_exams, $this->table_exams .'.id = '. $this->table_user_exams .'.exam_id');
                $this->db->where('question_id', $question_id);
                $this->db->where('user_answer !=', 'unknown');
                $this->db->where('user_answer', $answer_type);
                $this->db->from($this->table_user_exam_questions);
                $result['count'] = $this->db->count_all_results();
            }
        }

        return $result;
    }

    /**
     * Returns count of user answer type of a specific question
     * User answer type could be following
     *   - correct
     *   - wrong
     *   - dontknow
     *   - unanswered
     * The count only involves exams question which are completed
     *
     * @param $question_id
     * @param $answer_type
     * @return int
     */
    private function get_answer_count($question_id, $answer_type)
    {
        $question_id = (int)$question_id;
        if ($answer_type == 'unanswered') { $answer_type = ''; }

        if ($question_id > 0) {

            $this->db->select('count('. $this->db->dbprefix($this->table_user_exam_questions) .'.id) as answer_count');
            $this->db->join($this->table_user_exams, $this->table_user_exam_questions .'.user_exam_id = '. $this->table_user_exams .'.id', 'left');
            $this->db->where('question_id', $question_id);
            $this->db->where('ue_status', 'complete');
            $this->db->where('user_answer !=', 'unknown');
            $this->db->where('user_answer', $answer_type);
            $query = $this->db->get($this->table_user_exam_questions);
            return (int)$query->first_row()->answer_count;

        } else {
            $this->error_message = 'Invalid question id.';
            return 0;
        }
    }

    public function get_correct_answer_count($question_id)
    {
        return $this->get_answer_count($question_id, 'correct');
    }

    public function get_wrong_answer_count($question_id)
    {
        return $this->get_answer_count($question_id, 'wrong');
    }

    public function get_dontknow_answer_count($question_id)
    {
        return $this->get_answer_count($question_id, 'dontknow');
    }

    public function get_unanswered_answer_count($question_id)
    {
        return $this->get_answer_count($question_id, 'unanswered');
    }

    /**
     * Returns the count of users who attended a specific question
     * The count only involves exams question which are completed
     *
     * @param $question_id
     * @return int
     */
    public function get_user_count($question_id)
    {
        $question_id = (int)$question_id;

        if ($question_id > 0) {

            $this->db->select('count(distinct(user_id)) as user_id');
            $this->db->join($this->table_user_exams, $this->table_user_exam_questions .'.user_exam_id = '. $this->table_user_exams .'.id', 'left');
            $this->db->where('ue_status', 'complete');
            $this->db->where('user_answer !=', 'unknown');
            $this->db->where('question_id', $question_id);
            $query = $this->db->get($this->table_user_exam_questions);
            return (int)$query->first_row()->user_id;

        } else {
            $this->error_message = 'Invalid question id.';
            return 0;
        }
    }

    /**
     * Returns the count of exams which includes a specific question
     * The count only involves exams question which are completed
     *
     * @param $question_id
     * @return int
     */
    public function get_exam_count($question_id)
    {
        $question_id = (int)$question_id;

        if ($question_id > 0) {

            $this->db->select('count(distinct(exam_id)) as exam_id');
            $this->db->join($this->table_user_exams, $this->table_user_exam_questions .'.user_exam_id = '. $this->table_user_exams .'.id', 'left');
            $this->db->where('ue_status', 'complete');
            $this->db->where('user_answer !=', 'unknown');
            $this->db->where('question_id', $question_id);
            $query = $this->db->get($this->table_user_exam_questions);
            return (int)$query->first_row()->exam_id;

        } else {
            $this->error_message = 'Invalid question id.';
            return 0;
        }
    }

    /**
     * Returns the count of questions which used in a specific exam
     * The count only involves exams question which are completed
     *
     * @param $exam_id
     * @return int
     */
    public function get_used_question_count($exam_id)
    {
        $exam_id = (int)$exam_id;

        if ($exam_id > 0) {

            $this->db->select('count(distinct(question_id)) as question_count');
            $this->db->join($this->table_user_exams, $this->table_user_exam_questions .'.user_exam_id = '. $this->table_user_exams .'.id', 'left');
            $this->db->where('ue_status', 'complete');
            $this->db->where('user_answer !=', 'unknown');
            $this->db->where('exam_id', $exam_id);
            $query = $this->db->get($this->table_user_exam_questions);
            return (int)$query->first_row()->question_count;

        } else {
            $this->error_message = 'Invalid question id.';
            return 0;
        }
    }

    /**
     * Returns the count of questions which has been used in the system so far
     * The count only involves exams question which are completed
     *
     * @return int
     */
    public function get_total_used_question_count()
    {
        $this->db->select('count(distinct(question_id)) as question_count');
        $this->db->join($this->table_user_exams, $this->table_user_exam_questions .'.user_exam_id = '. $this->table_user_exams .'.id', 'left');
        $this->db->where('ue_status', 'complete');
        $this->db->where('user_answer !=', 'unknown');
        $query = $this->db->get($this->table_user_exam_questions);
        return (int)$query->first_row()->question_count;
    }

    public function get_total_used_question_in_category_count($category_id)
    {
        $category_id = (int)$category_id;

        if ($category_id > 0) {

            $this->db->select('count(distinct(question_id)) as question_count');
            $this->db->join($this->table_user_exams, $this->table_user_exam_questions .'.user_exam_id = '. $this->table_user_exams .'.id', 'left');
            $this->db->join($this->table_questions, $this->table_user_exam_questions .'.question_id = '. $this->table_questions .'.id', 'left');
            $this->db->where('ue_status', 'complete');
            $this->db->where('user_answer !=', 'unknown');
            $this->db->where('category_id', $category_id);
            $query = $this->db->get($this->table_user_exam_questions);
            return (int)$query->first_row()->question_count;

        } else {
            $this->error_message = 'Invalid question id.';
            return 0;
        }
    }


    /**
     * Insert a single question
     *
     * @param $question
     * @return bool
     */
    public function add_question($question)
    {
        if (is_array($question)) {

            // TODO: workaround to allow \ char. may be use &#92; or use
            $question['ques_text'] = str_replace('\\', '', $question['ques_text']);

            $this->db->insert($this->table_name, $question);
            
            if ($this->db->insert_id() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'Question add unsuccessful. DB error.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    public function add_bulk_questions($questions)
    {
        $affected_rows = 0;
        if (is_array($questions) && count($questions) > 0) {
            $this->db->insert_batch($this->table_name, $questions);
            $affected_rows = $this->db->affected_rows();
        }

        return $affected_rows;
    }

    /**
     * Update a question
     *
     * @param $question_id
     * @param $question
     * @return bool
     */
    public function update_question($question_id, $question)
    {
        $question_id = (int)$question_id;
        
        if ($question_id > 0) {

            // TODO: workaround to allow \ char. may be use &#92; instead
            $question['ques_text'] = str_replace('\\', '', $question['ques_text']);

            $this->db->where('id', $question_id);
            $this->db->update($this->table_name, $question);

            return true;

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    /**
     * Update a single field of a question
     *
     * @param $question_id
     * @param $field_name
     * @param $field_value
     * @return bool
     */
    public function update_question_single_field($question_id, $field_name, $field_value)
    {
        $question_id = (int)$question_id;

        $allowed_fields = array(
            'category_id',
            'question_title',
            'question_description',
            'question_body',
            'question_is_popular',
            'question_launch_date'
        );

        if ($question_id > 0) {

            if (in_array($field_name, $allowed_fields)) {
                $this->db->set($field_name, $field_value);
                $this->db->where('id', $question_id);
                $this->db->update($this->table_name);

                return true;
            } else  {
                $this->error_message = 'Invalid field.';
                return false;
            }

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    /**
     * Delete a question by ID
     *
     * @param $question_id
     * @return int
     */
    public function delete_question($question_id)
    {
        $question_id = (int)$question_id;
        
        if ($question_id > 0) {

            $this->db->where('id', $question_id);
            $this->db->delete($this->table_name);

            $res = (int)$this->db->affected_rows();

            if ($res > 0) {
                return $res;
            } else {
                $this->error_message = 'Question delete unsuccessful. DB error.';
                return 0;
            }

        } else {
            $this->error_message = 'Invalid id.';
            return 0;
        }
    }
    
    
    public function check_question($question_category_id, $question_text)
    {
        $question_category_id = (int)$question_category_id;
        $question_text = trim($question_text);

        if ($question_category_id > 0) {
             $query = $this->db->select('*')
                               ->from($this->table_name)
                               ->where('category_id', $question_category_id)
                               ->like('LOWER(ques_text)', $question_text)
                               ->get();
             
            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Question not found. Invalid Category id or question.';
                return FALSE;
            }

        } else {
            $this->error_message = 'Invalid category id.';
            return FALSE;
        }
    }
    
    public function edit_bulk_questions($questions)
    {
        $affected_rows = 0;
                
        if (is_array($questions) && count($questions) > 0) {
            // update users
            foreach($questions as $key => $value) {
                $data = array(
                    'ques_text' => $questions[$key]['ques_text_new'],
                    'ques_type' => $questions[$key]['ques_type'],
                    'ques_choices' => $questions[$key]['ques_choices'],
                    'ques_expiry_date' => $questions[$key]['ques_expiry_date']
                );
                
                $where_array = array(
                    'category_id' => $questions[$key]['category_id'],
                    'ques_text' => $questions[$key]['ques_text']
                );
                                
                $this->db->where($where_array);
                $this->db->limit(1);
                $this->db->update($this->table_name, $data);

                if($this->db->affected_rows() > 0)
                {
                    $affected_rows = true;
                }
            }
        }

        return $affected_rows;
    }
}

/* End of file question_model.php */
/* Location: ./application/models/question_model.php */