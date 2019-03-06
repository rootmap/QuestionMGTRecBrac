<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Exam_model extends CI_Model
{
    private $table_name = 'exams';
    private $table_name_exam_category = 'exam_category';
    private $table_name_user_exams = 'user_exams';
    private $table_print_exam= 'print_exam';
    private $table_print_questions= 'print_exam_questions';
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
    public function get_exam_count()
    {
        return $this->db->count_all($this->table_name);
    }

    public function get_exams($status = '')
    {
        /*if ($status != '') {
            $this->db->where('exam_status', $status);
        }*/
        $this->db->order_by('id','DESC');
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function get_open_exams()
    {
        return $this->get_exams('open');
    }

    /**
     * Get paginated list of exams
     *
     * @param $limit
     * @param int $offset
     * @param array $filter
     * @return bool
     */
    public function get_paged_exams($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_exam_title') {
                    $this->db->where("(exam_title LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

        $this->db->order_by('id','DESC');
        //$this->db->order_by('exam_added','DESC');
        
        $query = $this->db->get($this->table_name, $limit, $offset);

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_exam_title') {
                        $this->db->where("(exam_title LIKE '%". $value['value'] ."%')", '', false);
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
    
    public function get_paged_print_exams($limit, $offset = 0, $filter = array())
    {
    	$result = array();
    	$result['result'] = false;
    	$result['count'] = 0;
    	
    	if (is_array($filter) && count($filter) > 0) {
    		foreach($filter as $key => $value) {
    			if ($key == 'filter_exam_title') {
    				$this->db->where("(exam_name LIKE '%". $value['value'] ."%')", '', false);
    			} else {
    				$this->db->where($filter[$key]['field'], $filter[$key]['value']);
    			}
    		}
    	}
    	
    	$this->db->order_by('exam_status','DESC');
    	$this->db->order_by('create_date','DESC');
    	
    	$query = $this->db->get($this->table_print_exam, $limit, $offset);
    	
    	if ($query->num_rows() > 0) {
    		
    		$result['result'] = $query->result();
    		
    		// record count
    		if (is_array($filter) && count($filter) > 0) {
    			foreach($filter as $key => $value) {
    				if ($key == 'filter_exam_title') {
    					$this->db->where("(exam_name LIKE '%". $value['value'] ."%')", '', false);
    				} else {
    					$this->db->where($filter[$key]['field'], $filter[$key]['value']);
    				}
    			}
    		}
    		
    		$this->db->from($this->table_print_exam);
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
    public function get_exam($exam_id)
    {
        $exam_id = (int)$exam_id;

        // making sure that number of questions does not exceed
        // the number of questions actually exists in the category
        //$this->update_exam_category_no_of_question($exam_id);

        if ($exam_id > 0) {
            $this->db->where('id', $exam_id);
    		$query = $this->db->get($this->table_name);
            //echo $this->db->last_query(); die();
            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Exam not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function get_admit_user()
    {
            $this->db->where('user_is_admit_card',1);
            $query = $this->db->get('exm_users',0,1);
            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Exam not found. Invalid id.';
                return false;
            }
    }

    public function get_exam_user_finish_inactive($exam_user_id=0)
    {
            $this->db->where('id',$exam_user_id);
            $query = $this->db->get('exm_user_exams');
            if ($query->num_rows() > 0) {
                $row=$query->row();
                return $this->get_exam_user_after_inactive($row->user_id);
            } else {
                $this->error_message = 'Exam not found. Invalid id.';
                return false;
            }
    }

    public function get_exam_user_after_inactive($user_id=0)
    {
            $this->db->set('user_is_active',0);
            $this->db->where('id',$user_id);
            $query = $this->db->update('exm_users');
            if ($query) {
                return 1;
            } else {
                return 0;
            }
    }

    public function get_venue($exam_id)
    {
        $exam_id = (int)$exam_id;

        // making sure that number of questions does not exceed
        // the number of questions actually exists in the category
        //$this->update_exam_category_no_of_question($exam_id);

        if ($exam_id > 0) {
            $this->db->where('exam_id', $exam_id);
            $this->db->select('exam_venue_map.*,dbo.get_venue_name_by_id(exm_exam_venue_map.venue_id) as venue_name,dbo.get_venue_location_by_id(exm_exam_venue_map.venue_id) as venue_location');
            $query = $this->db->get('exam_venue_map');
            //echo $this->db->last_query(); die();
            if ($query->num_rows() > 0) {
                return $query->result_array();
            } else {
                $this->error_message = 'Venue not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function get_Sets($exam_id,$filter_type='')
    {
        $exam_id = (int)$exam_id;

        // making sure that number of questions does not exceed
        // the number of questions actually exists in the category
        //$this->update_exam_category_no_of_question($exam_id);

        if ($exam_id > 0) {
            $this->db->where('exam_id', $exam_id);
            $this->db->select('exm_exam_category.*');
            if(!empty($filter_type))
            {
                if($filter_type=='random')
                {
                    $this->db->order_by('id', 'RANDOM');
                }
            }
            $query = $this->db->get('exam_category',0,1);

            //echo $this->db->last_query(); die();
            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Set not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function get_Set($set_id)
    {
        $set_id = (int)$set_id;

        // making sure that number of questions does not exceed
        // the number of questions actually exists in the category
        //$this->update_exam_category_no_of_question($exam_id);

        if ($set_id > 0) {
            $this->db->where('id', $set_id);
            $query = $this->db->get('question_set');
            //echo $this->db->last_query(); die();
            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function get_Set_Info($set_id)
    {
        $set_id = (int)$set_id;

        // making sure that number of questions does not exceed
        // the number of questions actually exists in the category
        //$this->update_exam_category_no_of_question($exam_id);

        if ($set_id > 0) {
            $this->db->where('q.question_set_id', $set_id);
            $this->db->group_by('TB.category_id,q.question_mark');
            $this->db->from('exm_question_set_question_map as q');
            $this->db->select("dbo.get_categoryName_by_id(TB.category_id) as cat_name,CONCAT(count(q.id),' X ',q.question_mark,' = ',(count(q.id)*q.question_mark)) as summary_row,
(count(q.id)*q.question_mark) as total_mark");
            $this->db->join('exm_questions TB', 'TB.id = q.question_id' ,'left');

            $query = $this->db->get();
            //echo $this->db->last_query(); die();
            if ($query->num_rows() > 0) {
                return $query->result_array();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function get_exam_type($exam_id)
    {
        $exam_id = (int)$exam_id;

        if ($exam_id > 0) {
            $this->db->where('id', $exam_id);
    		$query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row()->exam_type;
            } else {
                $this->error_message = 'Exam not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    /**
     * Insert a single exam
     *
     * @param $exam
     * @return bool
     */
    public function add_exam($exam)
    {

        //print_r_pre($exam); die();
        if (is_array($exam)) {

            $exam_questions_set = array();

            if (isset($exam['exam_questions_set']) && is_array($exam['exam_questions_set'])) {
                $exam_questions_set = $exam['exam_questions_set'];
                unset($exam['exam_questions_set']);
            }
            $category_id =0;


            //print_r_pre($exam);die;

            $this->db->insert($this->table_name, $exam);
            
            if ($this->db->affected_rows() > 0) {

                $exam_id = $this->db->insert_id();
                
                // add exam categories
                for ($i=0; $i<count($exam_questions_set); $i++) {
                    $category_id = (int)$exam_questions_set[$i];
                    $noof_questions = 0;
                   // print_r_pre($exam_id);
                    $this->add_exam_category($category_id, $exam_id, $noof_questions);
                }
//print_r_pre($exam_id);
                // making sure that number of questions does not exceed
                // the number of questions actually exists in the category
               // $this->update_exam_category_no_of_question($exam_id);

                return $exam_id;

            } else {
                $this->error_message = 'Exam add unsuccessful. DB error.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    /**
     * Update a exam
     *
     * @param $exam_id
     * @param $exam
     * @return bool
     */
    public function update_exam($exam_id, $exam)
    {
        $exam_id = (int)$exam_id;
        $exam_questions_set = array();
        
        if (isset($exam['exam_questions_set']) && is_array($exam['exam_questions_set'])) {
            $exam_questions_set = $exam['exam_questions_set'];
            unset($exam['exam_questions_set']);
        }

        

        if ($exam_id > 0) {

            $this->db->where('id', $exam_id);
            $this->db->update($this->table_name, $exam);

            $old_exam_categories = $this->get_exam_categories($exam_id);

            // delete all 'exam categories' which is not available in current array but was added before
            for ($i=0; $i<count($old_exam_categories); $i++) {
                if ( !in_array($old_exam_categories[$i]->category_id, $exam_questions_set_category_ids) ) {
                    $this->delete_exam_category($old_exam_categories[$i]->category_id, $exam_id);
                }
            }
//print_r_pre($old_exam_categories);
            // update all new 'exam categories'
            for ($i=0; $i<count($exam_questions_set); $i++) {
                $this->update_exam_category($exam_questions_set[$i], $exam_id, 0);
            }

            // making sure that number of questions does not exceed
            // the number of questions actually available (expired questions does not count) in the category
            //$this->update_exam_category_no_of_question($exam_id);

            return true;

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }


    

      public function add_question_pool($question_pool)
    {
        if (is_array($question_pool)) {



            $this->db->insert($this->table_name, $exam);
            
            if ($this->db->affected_rows() > 0) {

                $exam_id = $this->db->insert_id();

                // add exam categories
            

            } else {
                $this->error_message = 'Pool add unsuccessful. DB error.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }


    private function update_exam_category_no_of_question($exam_id)
    {
        $CI =& get_instance();
        $CI->load->model('category_model');

        $exam_id = (int)$exam_id;
        $exam_type = $this->get_exam_type($exam_id);
        //print_r_pre($exam_type);
        if ($exam_id > 0 && $exam_type != '') {
            $exam_categories = $this->get_exam_categories($exam_id);
            for ($i=0; $i<count($exam_categories); $i++) {
                $no_of_questions = $exam_categories[$i]->no_of_questions;
                $actual_no_of_questions = $CI->category_model->get_question_count_by_type($exam_categories[$i]->category_id, $exam_type);
                if ($actual_no_of_questions <= 0) {
                    $this->delete_exam_category($exam_categories[$i]->category_id, $exam_id);
                } elseif ($no_of_questions > $actual_no_of_questions) {
                    $this->update_exam_category($exam_categories[$i]->category_id, $exam_id, $actual_no_of_questions);
                }
            }
        }
    }

    /* exam delete is not allowed */
    /**
     * Delete a exam by ID
     *
     * @param $exam_id
     * @return int
     */
    /*public function delete_exam($exam_id)
    {
        $ci =& get_instance();
        $ci->load->model('option_model');

        $exam_id = (int)$exam_id;
        
        if ($exam_id > 0) {

            $this->db->where('id', $exam_id);
            $this->db->delete($this->table_name);

            $res = (int)$this->db->affected_rows();

            if ($res > 0) {
                return $res;
            } else {
                $this->error_message = 'Exam delete unsuccessful. DB error.';
                return 0;
            }

        } else {
            $this->error_message = 'Invalid id.';
            return 0;
        }
    }*/


    /**
     * USER EXAM
     ********************************************************************************/

    public function is_user_exam_already_assigned($user_id = 0, $exam_id = 0)
    {
        $user_id = (int)$user_id;
        $exam_id = (int)$exam_id;

        $sql = "SELECT * FROM ". $this->db->dbprefix($this->table_name_user_exams) ."
                WHERE user_id = $user_id AND exam_id = $exam_id AND ue_status = 'open'";
        $res = $this->db->query($sql);

        $result = $res->result();

        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }

    }

    public function add_user_exam($data = array())
    {
        if (is_array($data)) {
            //print_r_pre($data);
            $this->db->insert($this->table_name_user_exams, $data);

            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'User Exam add unsuccessful. DB error.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }
	
    public function print_question($data)
    {
    	  
    	$CI =& get_instance();
    	$CI->load->model('user_exam_question_model');
    	
    	if (is_array($data)) { 
    		
    		$this->db->insert($this->table_print_exam, $data);
    		
    		if ($this->db->affected_rows() > 0) {
    			
    			$print_id = $this->db->insert_id(); 
    			
    			$exam = $this->get_exam($data['exam_id']);
    			if ($exam) {
    				// TODO: need to find exam categories recursively
    				$exam_categories = $this->get_exam_categories($data['exam_id']);
    				$CI->user_exam_question_model->set_random_questions_print($exam_categories, $print_id, $exam->exam_type);
    				return true;
    			} else {
    				$this->error_message = 'Invalid exam. Exam not found.';
    				return false;
    			}
    			
    		} else {
    			$this->error_message = 'Exam add unsuccessful. DB error.';
    			return false;
    		}
    	} else {
    		$this->error_message = 'Invalid parameter.';
    		return false;
    	}
    }
    
    
    public function add_user_exam_by_user_id($user_id = 0, $data = array())
    {
        $CI =& get_instance();
        $CI->load->model('user_exam_question_model');

        $user_id = (int)$user_id;
        $exam_id = (int)$data['exam_id'];

        if ($user_id > 0) {
            $is_already_assigned = $this->is_user_exam_already_assigned($user_id, $exam_id);
            if ( ! $is_already_assigned ) {
                $data['user_id'] = $user_id;
                $setID = $this->getExmSet($data['exam_id']);
                $addArray = array_merge($data,$setID[0]);
                $user_exam_id = $this->add_user_exam($addArray);

                $CI->user_exam_question_model->set_random_questions($setID[0], $user_exam_id, $exam->exam_type);
                    return true;
            } else {
                $this->error_message = 'Exam already assigned to the user.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid user id.';
            return false;
        }
    }

    /******babu function here*******/
    public function getExmSet($val)
    {
        $this->db->limit(1);
        $this->db->order_by('id','RANDOM');
        $this->db->select('category_id as set_id');
        $this->db->where('exam_id',$val);
        $query = $this->db->get('exm_exam_category'); 
         // echo $this->db->last_query(); die(); 
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }else{
            return false;
        }
    }
     /******babu function here*******/

    public function add_user_exam_by_user_team($team_id = 0, $data = array())
    {
        $CI =& get_instance();
        $CI->load->model('user_model');
        $CI->load->model('user_exam_question_model');

        $team_id = (int)$team_id;
        $users = $CI->user_model->get_active_users_by_user_team($team_id);

        if ($users) {
            for($i=0; $i<count($users); $i++) {

                $user_id = (int)$users[$i]->id;
                $exam_id = (int)$data['exam_id'];
                $is_already_assigned = $this->is_user_exam_already_assigned($user_id, $exam_id);
                
                if ( ! $is_already_assigned ) {
                    $data['user_id'] = $user_id;
                    $user_exam_id = $this->add_user_exam($data);
    
                    // add user exam questions
                    $exam = $this->get_exam($data['exam_id']);
                    if ($exam) {
                        $exam_categories = $this->get_exam_categories($data['exam_id']);
                        $CI->user_exam_question_model->set_random_questions($exam_categories, $user_exam_id, $exam->exam_type);
                    } else {
                        $this->error_message = 'Invalid exam.';
                        return false;
                    }

                } else {
                    $this->error_message = 'Exam already assigned to the user.';
                    return false;
                }
            }
        } else {
            $this->error_message = 'There is no user under the team.';
            return false;
        }
    }


    /**
     * EXAM CATEGORY
     *************************************************************************/

    public function get_exam_category($category_id = 0, $exam_id = 0)
    {
        $category_id = (int)$category_id;
        $exam_id = (int)$exam_id;

        if($category_id <= 0 || $exam_id <= 0) { return FALSE; }

        $this->db->where('category_id', $category_id);
        $this->db->where('exam_id', $exam_id);
        $this->db->limit(1);

        $query = $this->db->get($this->table_name_exam_category);

        if ($query->num_rows() > 0) {

            return $query->first_row();

        } else {
            return FALSE;
        }
    }

    public function get_exam_categories($exam_id = 0)
    {
        $exam_categories = array();
        $exam_id = (int)$exam_id;

        if($exam_id <= 0) { return FALSE; }
        $this->db->limit(1); 
        $this->db->order_by('id', 'RANDOM');
        $this->db->where('exam_id', $exam_id);
        $query = $this->db->get($this->table_name_exam_category);
        //echo $this->db->last_query(); die();
        if ($query->num_rows() > 0) {
            $exam_categories = $query->result();
        }
       // print_r_pre($exam_categories);
        return $exam_categories;
    }

    public function get_number_of_questions($exam_id = 0,$exam_type = 0)
    {
        $uid = $this->session->userdata('logged_in_user'); 
        $exam_categories = array();
        $exam_id = (int)$exam_id;
        if($exam_id <= 0) { return FALSE; }
        $this->db->limit(1); 
        $this->db->select('TA.*,TA.set_id as category_id,TB.name,TB.set_status,TB.set_limit,TB.total_mark,TB.neg_mark_per_ques,TB.random_qus,TB.neg_mark_per_ques');
        $this->db->from('exm_user_exams TA');
        $this->db->join('exm_question_set TB', 'TB.id = TA.set_id' ,'left');
        $this->db->where('TA.exam_id', $exam_id);
        $this->db->where('TA.user_id', $uid->id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $exam_categories = $query->result();
        }

        $no_of_questions = 0;
        //$exam_categories = $this->get_exam_categories_all_data($exam_id,$exam_type);
        return $exam_categories;
    }

    private function get_exam_category_id($category_id = 0, $exam_id = 0)
    {
        $category_id = (int)$category_id;
        $exam_id = (int)$exam_id;

        if($category_id <= 0 || $exam_id <= 0) { return FALSE; }

        $this->db->where('category_id', $category_id);
        $this->db->where('exam_id', $exam_id);
        $this->db->limit(1);

        $query = $this->db->get($this->table_name_exam_category);

        if ($query->num_rows() > 0) {
            return $query->first_row()->id;
        } else {
            return FALSE;
        }
    }

    public function add_exam_category($category_id = 0, $exam_id = 0, $noof_questions = 0)
    {
        $category_id = (int)$category_id;
        $exam_id = (int)$exam_id;
        $noof_questions = (int)$noof_questions;


        if($category_id <= 0 || $exam_id <= 0) { return 0; }
        $exam_category_id = $this->get_exam_category_id($category_id, $exam_id);

        if($exam_category_id === FALSE){

            $value = $noof_questions;

            $data = array(
                'category_id' => $category_id,
                'exam_id' => $exam_id,
                'no_of_questions' => $value
            );

            $this->db->insert($this->table_name_exam_category, $data);
            //print_r_pre($this->db->insert_id());
            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                return FALSE;
            }

        } else {
            return $exam_category_id;
        }
    }

    public function update_exam_category($category_id = 0, $exam_id = 0, $noof_questions = 0)
    {
        $category_id = (int)$category_id;
        $exam_id = (int)$exam_id;
        $noof_questions = (int)$noof_questions;

        if($category_id <= 0 || $exam_id <= 0) { return 0; }
        $exam_category_id = $this->get_exam_category_id($category_id, $exam_id);

        if($exam_category_id === FALSE) {

            return $this->add_exam_category($category_id, $exam_id, $noof_questions);

        } else {

            $this->db->where('id', $exam_category_id);
            $data = array(
                'no_of_questions' => $noof_questions
            );
            $this->db->update($this->table_name_exam_category, $data);

            return $exam_category_id;
        }
    }

    public function delete_exam_category($category_id = 0, $exam_id = 0)
    {
        $category_id = (int)$category_id;
        $exam_id = (int)$exam_id;

        if($category_id <= 0 || $exam_id <= 0) { return FALSE; }
        $exam_category_id = $this->get_exam_category_id($category_id, $exam_id);

        if($exam_category_id !== FALSE && $exam_category_id > 0){

            $this->db->where('id', $exam_category_id);
            $this->db->limit(1);
            $this->db->delete($this->table_name_exam_category);

            if($this->db->affected_rows() > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
    
    public function get_print_questions($print_id)
    {
    	$CI =& get_instance();
    	$CI->load->helper('serialize');
    	$CI->load->model('question_model');
    	
    	$user_exam = array();
    	$print_id= (int)$print_id;
    	
    	if ($print_id> 0) {
    		
    		$this->db->where('print_exam_id', $print_id);
    		$this->db->order_by('id', 'ASC');
    		$query = $this->db->get($this->table_print_questions);
    		
    		if ($query->num_rows() > 0) {
    			
    			$user_exam = $query->result();
    			
    			for ($i=0; $i<count($user_exam); $i++) {
    				$user_exam[$i]->question = $CI->question_model->get_question($user_exam[$i]->question_id);
    				$user_exam[$i]->question->ques_choices = maybe_unserialize($user_exam[$i]->question->ques_choices);
    			}
    			
    			return $user_exam;
    			
    		} else {
    			return $user_exam;
    		}
    		
    	} else {
    		return false;
    	}
    }

    /******************this function create by babu*********************/
    public function get_exam_categories_name($exam_id = 0)
    {
        $exam_categories = array();
        $exam_id = (int)$exam_id;

        if($exam_id <= 0) { return FALSE; }
        $this->db->select('TA.*,TB.name');
        $this->db->from(''.$this->table_name_exam_category.' TA');
        $this->db->join('exm_question_set TB', 'TB.id = TA.category_id' ,'left');
        $this->db->where('TA.exam_id', $exam_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $exam_categories = $query->result_array();
        }

        return $exam_categories;
    }


    public function get_exam_categories_all_data($exam_id = 0,$exam_type=0)
    {
        $exam_categories = array();
        $exam_id = (int)$exam_id;
        if($exam_id <= 0) { return FALSE; }
        $this->db->limit(1); 
        $this->db->select('TA.*,TB.name,TB.set_status,TB.set_limit,TB.total_mark,TB.neg_mark_per_ques,TB.random_qus');
        $this->db->from(''.$this->table_name_exam_category.' TA');
        $this->db->join('exm_question_set TB', 'TB.id = TA.category_id' ,'left');
        $this->db->where('TA.exam_id', $exam_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $exam_categories = $query->result();
        }

        return $exam_categories;
    }



    public function getexamcategoriesalldata($exam_id = 0,$exam_type=0)
    {
        $exam_categories = array();
        $exam_id = (int)$exam_id;
        if($exam_id <= 0) { return FALSE; }
        //,TC.name,TC.set_status,TC.set_limit,TC.total_mark,TC.neg_mark_per_ques,TC.random_qus
        $this->db->select('TA.*');
        $this->db->from(''.$this->table_name.' TA'); 
        $this->db->join('exm_user_exam_questions TB', 'TB.id = TA.user_exam_id' ,'left');
        //$this->db->join(''.$this->table_name_exam_category.' TC', 'TC.exam_id = TA.id' ,'left');
        //$this->db->join('exm_question_set TD', 'TD.id = TB.category_id' ,'left');
        $this->db->where('TA.user_exam_id', $exam_id);
        $query = $this->db->get();

        echo $this->db->last_query(); die();

        if ($query->num_rows() > 0) {
            $exam_categories = $query->result();
        }

        return $exam_categories;
    }



}

/* End of file exam_model.php */
/* Location: ./application/models/exam_model.php */