<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class User_exam_model extends CI_Model
{
    private $table_name = 'user_exams';
    private $table_user_exam_questions = 'user_exam_questions';
    private $table_users = 'users';
    private $table_user_teams = 'user_teams';
    private $table_user_groups = 'user_groups';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
        $this->load->helper('serialize');
    }

    public function get_user_exam_count()
    {
        return $this->db->count_all($this->table_name);
    }


    public function get_user_exams_by_exam_count($exam_id = 0, $start = '', $end = '')
    {
        $exam_id = (int)$exam_id;
        $date_where = '';

        if ($exam_id > 0) {

            if ($start != '' && $end != '') {
                $date_where = " AND ('". $start ."' <= ue_start_date AND '". $end ."' >= ue_end_date)";
            }

            $sql = "SELECT count(*) AS no_of_records
                    FROM ". $this->db->dbprefix($this->table_name) ."
                    WHERE
                        exam_id = $exam_id
                        $date_where";
            $res = $this->db->query($sql);

            return (int)$res->first_row()->no_of_records;

        } else {
            $this->error_message = 'Invalid exam id.';
            return 0;
        }
    }

    public function get_user_exams_by_exam_paged($exam_id = 0, $start = '', $end = '', $limit = 50, $offset = 0, $filter = array())
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


        $exam_id = (int)$exam_id;
        $limit = (int)$limit;
        $offset = (int)$offset;
        $date_where = '';

        if ($exam_id > 0) {

            if ($start != '' && $end != '') {
                $date_where = " AND ('". $start ."' <= ue_start_date AND '". $end ."' >= ue_end_date)";
            }
                    $this->db->limit($limit, $offset);
                    $this->db->order_by('group_name');
                    $this->db->order_by('team_name');
                    $this->db->order_by('user_login');
                    $this->db->select('TA.id AS user_exam_id, user_id, exam_id, group_id,
                       ue_start_date, ue_end_date, ue_status, ue_added,
                       user_login, user_first_name, user_last_name, user_email,
                       team_name, group_name');
                    $this->db->from(''.$this->db->dbprefix($this->table_name).' TA');
                    $this->db->join(''. $this->db->dbprefix($this->table_users) .' TB', 'TB.id = TA.user_id' ,'left');
                    $this->db->join(''. $this->db->dbprefix($this->table_user_teams) .' TC', 'TC.id = TB.user_team_id' ,'left');
                    $this->db->join(''. $this->db->dbprefix($this->table_user_groups) .' TD', 'TD.id = TC.group_id' ,'left');

                    $this->db->where('TA.exam_id',$exam_id);
                    $query = $this->db->get();
                    //echo $this->db->last_query(); die();
                    $res = $query;

            if ($res->num_rows() > 0) {

                $result['result'] = $res->result();

                // record count
                    $this->db->select('count(TA.id) AS total');
                    $this->db->from(''.$this->db->dbprefix($this->table_name).' TA');
                    $this->db->join(''. $this->db->dbprefix($this->table_users) .' TB', 'TB.id = TA.user_id' ,'left');
                    $this->db->join(''. $this->db->dbprefix($this->table_user_teams) .' TC', 'TC.id = TB.user_team_id' ,'left');
                    $this->db->join(''. $this->db->dbprefix($this->table_user_groups) .' TD', 'TD.id = TC.group_id' ,'left');
                    $this->db->where('TA.exam_id',$exam_id);
                    $query = $this->db->get();

                $res = $query;
                if ($res->num_rows() > 0) {
                    $result['count'] = $res->row()->total;
                }
            }

            return $result;

        } else {
            $this->error_message = 'Invalid exam id.';
            return false;
        }
    }

    public function get_user_exam($user_exam_id)
    {
        $user_exam_id = (int)$user_exam_id;

        if ($user_exam_id > 0) {

            $this->db->where('id', $user_exam_id);
            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function get_user_exams($user_id)
    {
        $user_id = (int)$user_id;

        if ($user_id > 0) {
            $this->db->where('user_id', $user_id);
            $this->db->where(" ( ue_status = 'open' OR ue_status = 'paused' ) ", NULL, FALSE);
            $this->db->where(" ( ue_start_date <= getdate() AND ue_end_date >= getdate() ) ", NULL, FALSE);
            $this->db->order_by('ue_end_date', 'ASC');
            $this->db->order_by('id', 'DESC');
            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->result();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function get_user_all_open_exams($user_id)
    {
        $user_id = (int)$user_id;

        if ($user_id > 0) {
            $this->db->where('user_id', $user_id);
            $this->db->where(" ( ue_status = 'open' OR ue_status = 'paused' ) ", NULL, FALSE);
            $this->db->order_by('ue_end_date', 'ASC');
            $this->db->order_by('id', 'DESC');
            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->result();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function get_user_exam_state($user_exam_id)
    {
        $user_exam_id = (int)$user_exam_id;

        if ($user_exam_id > 0) {

            $this->db->where('id', $user_exam_id);
            $this->db->limit(1);

            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {

                return maybe_unserialize($query->first_row()->ue_state);
            } else {
                return false;
            }
            
        } else {
            return false;
        }
    }

    public function get_user_exam_question($user_exam_id, $question_id)
    {
        $user_exam_id = (int)$user_exam_id;
        $question_id = (int)$question_id;

        if ($user_exam_id > 0 && $question_id > 0) {

            $this->db->where('user_exam_id', $user_exam_id);
            $this->db->where('question_id', $question_id);
            $this->db->limit(1);

            $query = $this->db->get($this->table_user_exam_questions);
            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                return false;
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    public function update_user_exam_questions($user_exam_questions)
    {
        if (is_array($user_exam_questions) && count($user_exam_questions) > 0) {

            for ($i=0; $i<count($user_exam_questions); $i++) {

                $user_exam_id = (int)$user_exam_questions[$i]['user_exam_id'];
                $question_id = (int)$user_exam_questions[$i]['question_id'];

                $existing_user_exam_question = $this->get_user_exam_question($user_exam_id, $question_id);
                if ($existing_user_exam_question !== false) {

                    $this->db->where('id', (int)$existing_user_exam_question->id);
                    $this->db->where('user_exam_id', (int)$existing_user_exam_question->user_exam_id);
                    $this->db->where('question_id', (int)$existing_user_exam_question->question_id);
                    $this->db->limit(1);

                    $data = array(
                        'user_answer' => $user_exam_questions[$i]['user_answer']
                    );
                    $this->db->update($this->table_user_exam_questions, $data);

                }
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    public function update_user_exam_state($user_exam_id, $state)
    {
        $user_exam_id = (int)$user_exam_id;

        if ($user_exam_id > 0) {
            $this->db->where('id', $user_exam_id);
            $this->db->limit(1);
            $data = array(
                'ue_state' => maybe_serialize($state)
            );
            
            $this->db->update($this->table_name, $data);
        } else {
            return false;
        }
    }

    public function update_user_exam_status($user_exam_id, $status)
    {
        $user_exam_id = (int)$user_exam_id;

        if ($user_exam_id > 0) {
            $this->db->where('id', $user_exam_id);
            $this->db->limit(1);
            $data = array(
                'ue_status' => $status
            );
            $this->db->update($this->table_name, $data);
        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }


    public function active_user_exam($user_exam_id)
    {
        $user_exam_id = (int)$user_exam_id;

        if ($user_exam_id > 0) {
            $this->db->where('id', $user_exam_id);
            $this->db->limit(1);
            $data = array(
                'ue_status' => 'open'
            );
            $this->db->update($this->table_name, $data);
            return true;
        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }

    public function inactive_user_exam($user_exam_id)
    {
        $user_exam_id = (int)$user_exam_id;

        if ($user_exam_id > 0) {
            $this->db->where('id', $user_exam_id);
            $this->db->limit(1);
            $data = array(
                'ue_status' => 'inactive'
            );
            $this->db->update($this->table_name, $data);
            return true;
        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }

    /**
     * clears ue_state, set wu_status = 'open', delete result row
     *
     * @param $user_exam_id
     * @return bool
     */
    public function retake_user_exam($user_exam_id)
    {
        $CI =& get_instance();
        $CI->load->model('result_model');
        $user_exam_id = (int)$user_exam_id;

        if ($user_exam_id > 0) {
            $this->db->where('id', $user_exam_id);
            $this->db->limit(1);
            $data = array(
                'ue_status' => 'open',
                'ue_state' => ''
            );
            $this->db->update($this->table_name, $data);

            $CI->result_model->delete_result_by_user_exam($user_exam_id);
            return true;

        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }

    public function delete_user_exam($user_exam_id)
    {
        $CI =& get_instance();
        $CI->load->model('result_model');
        $CI->load->model('user_exam_question_model');
        $user_exam_id = (int)$user_exam_id;

        if ($user_exam_id > 0) {

            $this->db->where('id', $user_exam_id);
            $this->db->limit(1);
            
            $this->db->delete($this->table_name);
            $CI->result_model->delete_result_by_user_exam($user_exam_id);
            $CI->user_exam_question_model->delete_questions_by_user_exam($user_exam_id);

            return true;
            
        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }
    
    public function reassign_user_exam($user_exam_id, $start_date, $end_date)
    {
        $user_exam_id = (int)$user_exam_id;

        if ($user_exam_id > 0) {
            $this->db->where('id', $user_exam_id);
            $this->db->limit(1);
            $data = array(
                'ue_start_date' => $start_date,
                'ue_end_date' => $end_date
            );
            $this->db->update($this->table_name, $data);
            return true;
        } else {
            return false;
        }
    }
    
    public function update_user_exam_datetime($exam_id, $ue_start_date, $ue_end_date)
    {
        $exam_id = (int)$exam_id;

        if ($exam_id > 0) {
            $where_array = array('exam_id' => $exam_id, 'ue_status' => 'open');            
            $this->db->where($where_array);
            //$this->db->limit(1);
            $data = array(
                'ue_start_date' => $ue_start_date,
                'ue_end_date' => $ue_end_date
            );
            $this->db->update($this->table_name, $data);
            return true;
        } else {
            return false;
        }
    }
    

}

/* End of file user_exam_model.php */
/* Location: ./application/models/user_exam_model.php */