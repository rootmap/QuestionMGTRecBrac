<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class User_exam_question_model extends CI_Model
{
    private $table_name = 'user_exam_questions';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    public function get_user_exam_question_count()
    {
        return $this->db->count_all($this->table_name);
    }

    public function get_user_exam_questions($user_exam_id)
    {
        $CI =& get_instance();
        $CI->load->helper('serialize');
        $CI->load->model('question_model');

        $user_exam = array();
        $user_exam_id = (int)$user_exam_id;
        
        if ($user_exam_id > 0) {

            $this->db->where('user_exam_id', $user_exam_id);
            $this->db->order_by('id', 'ASC');
            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                $user_exam = $query->result();
                for ($i=0; $i<count($user_exam); $i++) {
                    $user_exam[$i]->question = $CI->question_model->get_question($user_exam[$i]->question_id);
                    $user_exam[$i]->question->ques_choices = maybe_unserialize($user_exam[$i]->question->ques_choices);
                }
                //print_r_pre($user_exam);
                return $user_exam;

            } else {

                return $user_exam;
            }
//print_r_pre('$user_exam');
        } else {
            return false;
        }
    }


    // get random questions
    public function set_random_questions($exam_categories, $user_exam_id, $exam_type)
    {
        $random_questions_id = $this->select_random_questionnew($exam_categories['set_id']);
        $this->insert_random_questions_by_category($random_questions_id, $user_exam_id,$exam_categories[$i]->category_id);
        
    }
    
    public function set_random_questions_print($exam_categories, $print_id, $exam_type)
    {
    	 
    	$random_questions_id =array();
    	for($i=0; $i<count($exam_categories); $i++) {
    		$random_questions_id = $this->select_random_question_id($exam_categories[$i]->category_id, $exam_categories[$i]->no_of_questions, $exam_type);
    		$this->insert_print_questions($random_questions_id, $print_id);
    	}
    	 
    	 
    }
    
    private function insert_print_questions($random_questions_id, $print_id)
    {
    	for($i=0; $i<count($random_questions_id); $i++) {
    		$data = array(
    				'print_exam_id' => $print_id,
    				'question_id' => $random_questions_id[$i]->id
    				 
    		);
    		$this->db->insert($this->db->dbprefix('print_exam_questions'), $data);
    	}
    }

    // select select random question id by category
    private function select_random_question_id($category, $no_of_questions, $exam_type)
    {

        //$this->db->limit(1);
        //$this->db->order_by('question_id', 'RANDOM');
        $this->db->select('question_id as id');
        $this->db->where('question_set_id', $category);
        $res = $this->db->get('exm_question_set_question_map');
       // echo $this->db->last_query(); die();
        return $res->result();
    }

    // function create babu get all qus details
    private function select_random_questionnew($category)
    {
        $this->db->order_by('id', 'RANDOM');
        $this->db->select('*');
        $this->db->where('question_set_id', $category);
        $res = $this->db->get('exm_question_set_question_map');
       // echo $this->db->last_query(); die();
        return $res->result();
    }
    
    // insert random questions by category
    private function insert_random_questions_by_category($random_questions_id, $user_exam_id)
    {
        //print_r_pre($random_questions_id);
        for($i=0; $i<count($random_questions_id); $i++) {
            $data = array(
                'user_exam_id' => $user_exam_id,
                'question_id' => $random_questions_id[$i]->id,
                'qus_set' => $random_questions_id[$i]->question_set_id,
                'marks' => $random_questions_id[$i]->question_mark,
                'is_mandatory' => $random_questions_id[$i]->is_mandatory,
                'user_answer' => ''
            );
           //print_r_pre($data);
            $this->db->insert($this->db->dbprefix('user_exam_questions'), $data);
        }
    }
	
    private function insert_random_questions_by_category_print($random_questions_id, $user_exam_id)
    {
    	for($i=0; $i<count($random_questions_id); $i++) {
    		$data = array(
    				'user_exam_id' => $user_exam_id,
    				'question_id' => $random_questions_id[$i]->id,
    				'user_answer' => ''
    		);
    		$this->db->insert($this->db->dbprefix('user_exam_questions'), $data);
    	}
    }
    
    
    
    public function delete_questions_by_user_exam($user_exam_id)
    {
        $user_exam_id = (int)$user_exam_id;

        if ($user_exam_id > 0) {

            $this->db->where('user_exam_id', $user_exam_id);
            $this->db->delete($this->table_name);

            $res = (int)$this->db->affected_rows();

            if ($res > 0) {
                return true;
            } else {
                $this->error_message = 'User exam questions delete unsuccessful. DB error.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }
}

/* End of file user_exam_question_model.php */
/* Location: ./application/models/user_exam_question_model.php */