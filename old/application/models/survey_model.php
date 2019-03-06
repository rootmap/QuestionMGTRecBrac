<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Survey_model extends CI_Model
{
    private $table_name = 'surveys';
    private $table_name_questions = 'survey_questions';
    private $table_name_surveys_questions = 'surveys_questions';
    private $table_surveys_users = 'surveys_users';
    private $table_users = 'users';
    private $table_user_groups = 'user_groups';
    private $table_survey_answer = 'survey_answer';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
        $this->load->helper('serialize');
    }

    /**
     * Get number of caterogies
     *
     * @return int
     */
    public function get_survey_count()
    {
        $result=$this->db->count_all($this->table_name);
        return $result;
    }

    public function get_surveys($status = '')
    {
        if ($status != '') {
            $this->db->where('survey_status', $status);
        }

        $this->db->order_by('survey_title','ASC');
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function get_open_surveys()
    {
        return $this->get_surveys('open');
    }

    /**
     * Get paginated list of exams
     *
     * @param $limit
     * @param int $offset
     * @param array $filter
     * @return bool
     */
    public function get_paged_surveys($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_survey_title') {
                    $this->db->where("(survey_title LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

        $this->db->order_by('survey_status','DESC');
        $this->db->order_by('survey_added','DESC');

        $query = $this->db->get($this->table_name, $limit, $offset);

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_survey_title') {
                        $this->db->where("(survey_title LIKE '%". $value['value'] ."%')", '', false);
                    } else {
                        $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }

            $this->db->from($this->table_name);
            $result['count'] = $this->db->count_all_results();
        }

        return $result;
    }

    /**
     * Get single exam by exam ID
     *
     * @param $exam_id
     * @return bool
     */
    public function get_survey($survey_id)
    {
        $survey_id = (int)$survey_id;

        if ($survey_id > 0) {
            $this->db->where('id', $survey_id);
            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Survey not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }
    
    
     public function get_survey_by_title($survey_title)
    {
        if ($survey_title) {
            //$this->db->where('survey_title', $survey_title);
            $this->db->like('survey_title', $survey_title);
            $this->db->limit(1);
            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Survey not found. Invalid Title.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid Title.';
            return false;
        }
    }


    /**
     * Insert a single exam
     *
     * @param $exam
     * @return bool
     */
    public function add_survey($survey)
    {
        if (is_array($survey)) {
            
            $survey_questions_set = array();

            if (isset($survey['question_ids']) && is_array($survey['question_ids'])) {
                $survey_questions_set = $survey['question_ids'];
                unset($survey['question_ids']);
            }

            $this->db->insert($this->table_name, $survey);
            
            if ($this->db->affected_rows() > 0) {

                $survey_id = $this->db->insert_id();
                
                $data = array();
                // add surveys questions
                for ($i=0; $i<count($survey_questions_set); $i++) {
                    $data[] = array('survey_id' => $survey_id, 'question_id' => $survey_questions_set[$i]);
                }
                $this->db->insert_batch($this->table_name_surveys_questions, $data); 

                return $survey_id;

            } else {
                $this->error_message = 'Survey add unsuccessful. DB error.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }
    
    
    public function get_survey_questions($survey_id = 0)
    {
        $survey_questions = array();
        $survey_id = (int)$survey_id;

        if($survey_id <= 0) { return FALSE; }

        $this->db->select($this->table_name_surveys_questions.'.question_id');
        $this->db->from($this->table_name_surveys_questions);
 
        $this->db->where('survey_id', $survey_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $survey_questions = $query->result();
        }

        return $survey_questions;
    }
    
    public function get_number_of_questions($survey_id = 0)
    {
        $no_of_questions = 0;
        $surveys_questions = $this->get_survey_questions($survey_id);

        for($i=0; $i<count($surveys_questions); $i++) {
            $no_of_questions += 1;
        }

        return $no_of_questions;
    }

    /**
     * Update a exam
     *
     * @param $exam_id
     * @param $exam
     * @return bool
     */
    public function update_survey($survey_id, $survey)
    {
        $survey_id = (int)$survey_id;
        $survey_questions_set = array();

        if (isset($survey['question_ids']) && is_array($survey['question_ids'])) {
            $survey_questions_set = $survey['question_ids'];
            unset($survey['question_ids']);
        }

        if ($survey_id > 0) {

            $this->db->where('id', $survey_id);
            $this->db->update($this->table_name, $survey);

            // delete all 'surveys_questions'
            $this->db->where('survey_id', $survey_id);
            $this->db->delete($this->table_name_surveys_questions);

            $data = array();
            // add surveys questions
            for ($i=0; $i<count($survey_questions_set); $i++) {
                //$this->db->where('user_employee',AirtelLoggedStatus()); 
                $data[] = array('survey_id' => $survey_id, 'question_id' => $survey_questions_set[$i]);
            }
            $this->db->insert_batch($this->table_name_surveys_questions, $data);

            return true;

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }
    
    public function add_user_survey_by_user_id($user_id = 0, $data = array())
    {
        $user_id = (int)$user_id;
        $survey_id = (int)$data['survey_id'];

        if ($user_id > 0) {
            $is_already_assigned = $this->is_user_survey_already_assigned($user_id, $survey_id);

            if ( ! $is_already_assigned ) {
                $data['user_id'] = $user_id;
                $user_survey_id = $this->add_user_survey($data);
                return true;
            } else {
                $this->error_message = 'Training already assigned to the user.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid user id.';
            return false;
        }
    }
    
    
    public function is_user_survey_already_assigned($user_id = 0, $survey_id = 0)
    {
        $user_id = (int)$user_id;
        $survey_id = (int)$survey_id;
        //'user_employee'=>AirtelLoggedStatus()
        $sql = "SELECT * FROM ". $this->db->dbprefix($this->table_surveys_users) ."
                WHERE user_id = $user_id AND survey_id = $survey_id AND status = 'open'";
        $res = $this->db->query($sql);

        $result = $res->result();

        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }

    }
    
    public function add_user_survey($data = array())
    {
        if (is_array($data)) {

            $this->db->insert($this->table_surveys_users, $data);

            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'User Survey add unsuccessful. DB error.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }
    
       
    public function get_user_survey_by_survey_paged($survey_id = 0, $start = '', $end = '', $limit = 50, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        $filter_where = '';
        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_login') {
                    $filter_where .= ' AND '. $filter[$key]['field'] ." LIKE '%". $filter[$key]['value'] ."%' ";
                } else {
                    $filter_where .= ' AND '. $filter[$key]['field'] ." = '". $filter[$key]['value'] ."' ";
                }
            }
        }


        $survey_id = (int)$survey_id;
        $limit = (int)$limit;
        $offset = (int)$offset;
        $date_where = '';
        if ($survey_id > 0) {
            if ($start != '' && $end != '') {
                $date_where = " AND ('". $start ."' <= start_date AND '". $end ."' >= end_date)";
            }
            $this->db->select("TA.id AS user_survey_id,user_id, survey_id, start_date, end_date, completed, status, group_id,user_login, user_first_name, user_last_name, user_email, group_name");
            $this->db->from(''.$this->db->dbprefix($this->table_surveys_users).' TA');
            $this->db->join(''. $this->db->dbprefix($this->table_users) .' TB','TB.id = TA.user_id', 'left');
            $this->db->join('exm_user_teams TC','TC.id = TB.user_team_id', 'left');
            $this->db->join(''. $this->db->dbprefix($this->table_user_groups) .' TD','TD.id = TC.group_id', 'left');
            $this->db->limit($limit, $offset);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $result['result'] = $query->result();
            $this->db->select("count(TA.id) AS total");            
            $this->db->from(''.$this->db->dbprefix($this->table_surveys_users).' TA');
            $this->db->join(''. $this->db->dbprefix($this->table_users) .' TB','TB.id = TA.user_id', 'left');
            $this->db->join('exm_user_teams TC','TC.id = TB.user_team_id', 'left');
            $this->db->join(''. $this->db->dbprefix($this->table_user_groups) .' TD','TD.id = TC.group_id', 'left');
            $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $result['count'] = $query->row()->total;
                }
            }
            return $result;
        } else {
            $this->error_message = 'Invalid survey id.';
            return false;
        }
    }
    
    
    public function assign_survey_bulk_user($survey)
    {
        $affected_rows = 0;
        if (is_array($survey) && count($survey) > 0) {
            $this->db->insert_batch($this->table_surveys_users, $survey);
            $affected_rows = $this->db->affected_rows();
        }

        return $affected_rows;
    }
    
    public function get_survey_for_specific_user($user_id, $status='open')
    {
        if(!$user_id){
            $user_id = $this->session->userdata('logged_in_user')->id;
        }
        $user_id = (int)$user_id;       
        

        if ($user_id > 0) {   



           /* $this->db->select($this->table_surveys_users.'.*, '.$this->table_name.'.survey_title, '.$this->table_name.'.survey_description');
            $this->db->from($this->table_surveys_users);
            $this->db->join($this->table_name, $this->table_surveys_users.'.survey_id = '.$this->table_name.'.id');



            $this->db->where($this->table_surveys_users.'.user_employee',AirtelLoggedStatus()); 
            $query = $this->db->get();
            print_r($this->db->last_query()); die();

            exit();

            */
            $this->db->select($this->table_surveys_users.'.*, '.$this->table_name.'.survey_title, '.$this->table_name.'.survey_description');
            
            $this->db->join($this->table_name, $this->table_surveys_users.'.survey_id = '.$this->table_name.'.id');

            $data_today=date("Y-m-d");
            
            if($status == 'open'){
                $this->db->where($this->table_surveys_users.'.start_date <=', $data_today);
                $this->db->where($this->table_surveys_users.'.end_date >=', $data_today);
            }
            
            $this->db->where($this->table_surveys_users.'.user_id', $user_id);
            $this->db->where($this->table_surveys_users.'.status', $status);
            
            $this->db->from($this->table_surveys_users);
            $query = $this->db->get();
            //print_r($this->db->last_query()); die();



            if ($query->num_rows() > 0) {
                return $query->result();
            } else {
                $this->error_message = 'Survey not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }
    
    public function get_survey_for_user($survey_id)
    {
        $survey_id = (int)$survey_id;
        $user_id = $this->session->userdata('logged_in_user')->id;
        $user_id = (int)$user_id;
        
        if ($survey_id > 0) {
            $this->db->select($this->table_surveys_users.'.*, '.$this->table_name.'.survey_title, '.$this->table_name.'.survey_description');
            
            $this->db->join($this->table_name, $this->table_surveys_users.'.survey_id = '.$this->table_name.'.id');
            $this->db->where($this->table_surveys_users.'.user_id', $user_id);
            $this->db->where($this->table_surveys_users.'.survey_id', $survey_id);
            $this->db->from($this->table_surveys_users);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Survey not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }
    
    public function get_survey_questions_details($survey_id = 0)
    {
        $survey_questions = array();
        $survey_id = (int)$survey_id;

        if($survey_id <= 0) { return FALSE; }

        $this->db->select($this->table_name_questions.'.*');
        $this->db->from($this->table_name_questions);
        $this->db->join($this->table_name_surveys_questions, $this->table_name_questions.'.id = '.$this->table_name_surveys_questions.'.question_id');

        $this->db->where('survey_id', $survey_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $survey_questions = $query->result();
        }

        return $survey_questions;
    }
    
    public function get_questions_details($question_id = 0)
    {
        $questions = array();
        $question_id = (int)$question_id;

        if($question_id <= 0) { return FALSE; }

        $this->db->select($this->table_name_questions.'.*');
        $this->db->from($this->table_name_questions);
        $this->db->where('id', $question_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $questions = $query->result();
        }

        return $questions;
    }
    
    public function complete_user_survey($survey)
    {
        $affected_rows = 0;
        $user_id = 0;
        $survey_id = 0;
        
        if (is_array($survey) && count($survey) > 0) {
            $this->db->insert_batch($this->table_survey_answer, $survey);
            $affected_rows = $this->db->affected_rows();
            
            if(isset($survey[0]) && $survey[0]){   
                $user_id = $survey[0]['user_id'];
                $survey_id = $survey[0]['survey_id'];
            }
            
            if($user_id > 0 && $survey_id > 0){
                $this->update_user_survey($user_id, $survey_id);
            }
        }

        return $affected_rows;
    }
    
    
    public function update_user_survey($user_id, $survey_id)
    {
        $survey_id = (int)$survey_id;
        $user_id = (int)$user_id;

        if ($survey_id > 0) {
            $where = array('survey_id' => $survey_id, 'user_id' => $user_id);
            $update = array('status' => 'completed', 'completed' => date("Y-m-d H:i:s"));
            $this->db->where($where);
            $this->db->update($this->table_surveys_users, $update);
            return true;

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }
    
    public function get_survey_questions_answers($survey_id = 0)
    {
        $survey_questions = array();
        $survey_id = (int)$survey_id;
        $user_id = $this->session->userdata('logged_in_user')->id;

        if($survey_id <= 0) { return FALSE; }
        
        $this->db->select($this->table_name_questions.'.*, '.$this->table_survey_answer.'.answer');
        $this->db->from($this->table_name_questions);
        $this->db->join($this->table_name_surveys_questions, $this->table_name_questions.'.id = '.$this->table_name_surveys_questions.'.question_id');
        $this->db->join($this->table_survey_answer, $this->table_name_questions.'.id = '.$this->table_survey_answer.'.question_id');
        //$this->db->where($this->table_survey_answer.'.survey_id', $this->db->dbprefix($this->table_name_surveys_questions).'.survey_id');
        $this->db->where($this->table_survey_answer.'.survey_id', $survey_id);
        $this->db->where($this->table_survey_answer.'.user_id', $user_id);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $survey_questions = $query->result();
        }

        return $survey_questions;
    }
}

/* End of file exam_model.php */
/* Location: ./application/models/exam_model.php */