<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Question_set_model extends CI_Model
{
    private $table_question_set = 'question_set';
    private $table_users = 'users';
    private $table_exams = 'exams';
    private $table_questions = 'questions';
    private $table_user_exams = 'user_exams';
    private $table_user_exam_questions = 'user_exam_questions';
    public $error_message = '';

     var $set_list = array();

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
        return $this->db->count_all($this->table_name);
    }

    public function get_questions()
    {
        $this->db->order_by('question_launch_date','DESC');
        $this->db->order_by('question_title','ASC');
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
        
        if( $this->session->userdata('logged_in_user')->user_type == 'Administrator' ){
            $this->db->where('admin_group', $this->session->userdata('logged_in_user')->admin_group);
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





     /**
     * Get paginated list of question sets
     *
     * @param $limit
     * @param int $offset
     * @param array $filter
     * @return bool
     */
    public function get_paged_question_sets($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
               if ($key == 'filter_set_title') {
                    $this->db->like('name', $value['value']);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }
        
        if( $this->session->userdata('logged_in_user')->user_type == 'Administrator' ){
            $this->db->where('admin_group', $this->session->userdata('logged_in_user')->admin_group);
        }

        $this->db->select("S.id,S.name,S.set_status,S.created_by,S.set_limit,S.total_mark
      ,S.neg_mark_per_ques, dbo.total_question_by_set_id(S.id) as question_total");


        $this->db->order_by('S.id','DESC');

        $query = $this->db->get($this->table_question_set.' S');



        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_set_title') {
                        $this->db->like('name', $value['value']);
                    } else {
                        $this->db->where('filter_set_title', $filter[$key]['value']);
                    }
                }
            }

            $result['count'] = $this->db->count_all_results($this->table_question_set);
        }

        return $result;
    }

    


    /**
     * Insert a single question
     *
     * @param $question_SEt
     * @return bool
     */
    public function add_question_set($question_set)
    {
        if (is_array($question_set)) {

            // TODO: workaround to allow \ char. may be use &#92; or use
            $question_set_param['name'] = $question_set['set_name'];

         
           
            $this->db->insert($this->table_question_set, $question_set_param);
            
            if ($this->db->insert_id() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'Question set add unsuccessful. DB error.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }


     /**
     * Get single category by category ID
     *
     * @param $set_id
     * @return bool
     */
    public function get_question_set($set_id)
    {
        $set_id = (int)$set_id;

        if ($set_id > 0) {
            $this->db->where('id', $set_id);
            $query = $this->db->get($this->table_question_set);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Category not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function get_question_answer_by_questionsetId($set_id)
    {
        $set_id = (int)$set_id;

        if ($set_id > 0) {
            $this->db->where('id', $set_id);
            $query = $this->db->get($this->table_question_set);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Category not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }



    public function get_question_set_by_examid($exam_id)
    {
        $exam_id = (int)$exam_id;



        if ($exam_id > 0) {
           $sql = 'select id,name from exm_question_set where id in (select category_id from exm_exam_category where exam_id='.$exam_id.')';
            $res = $this->db->query($sql);

            $result = $res->result();

            return $result;
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }


    public function get_question_by_ques_set_id($ques_set_id)
    {

        $CI =& get_instance();
        $CI->load->helper('serialize');
        $ques_set_id = (int)$ques_set_id;
        if ($ques_set_id > 0) {
            $sql = 'select ques_text,ques_choices,ques_type,mark from exm_questions where id in (select question_id from exm_question_set_question_map where question_set_id='.$ques_set_id.')';
            $res = $this->db->query($sql);

            $result = $res->result();
            //var_dump($result);die;


            for ($i=0; $i<count($result); $i++) {

                $result[$i]->ques_choices = maybe_unserialize($result[$i]->ques_choices);
            }

            return $result;
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }


    public function get_TypeOfExam($ques_set_id)
    {
        $CI =& get_instance();
        $CI->load->helper('serialize');
        $ques_set_id = (int)$ques_set_id;
        if ($ques_set_id > 0) {
            $sql = 'select ques_type from exm_questions where id in (select question_id from exm_question_set_question_map where question_set_id='.$ques_set_id.') GROUP BY ques_type';
            $res = $this->db->query($sql);
            //echo $this->db->last_query(); die();
            $result = $res->result();
            return $result;
        } else {
            return 0;
        }
    }

    public function get_question_by_ques_cat_set_id($ques_set_id,$cat_id=0)
    {

        $CI =& get_instance();
        $CI->load->helper('serialize');



        $ques_set_id = (int)$ques_set_id;

        $getSetDetail=$this->get_question_set($ques_set_id);
        $isSetRandom="Fixed";
        if(isset($getSetDetail))
        {
            $isSetRandom=$getSetDetail->random_qus;
        }

        //echo $isSetRandom; 

        //print_r_pre($getSetDetail); die();

        if ($ques_set_id > 0) {
            if($isSetRandom=="random")
            {
                $sql = 'select ques_text,ques_choices,ques_type,mark from exm_questions where id in (select question_id from exm_question_set_question_map where question_set_id='.$ques_set_id.') AND category_id='.$cat_id.' ORDER BY NEWID()';
            }
            else
            {
                $sql = 'select ques_text,ques_choices,ques_type,mark from exm_questions where id in (select question_id from exm_question_set_question_map where question_set_id='.$ques_set_id.') AND category_id='.$cat_id;
            }

            $res = $this->db->query($sql);

            $result = $res->result();
            //var_dump($result);die;


            for ($i=0; $i<count($result); $i++) {

                $result[$i]->ques_choices = maybe_unserialize($result[$i]->ques_choices);
            }

            return $result;
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function get_question_category_by_ques_set_id($ques_set_id)
    {

        $CI =& get_instance();
        $CI->load->helper('serialize');
        $ques_set_id = (int)$ques_set_id;
        if ($ques_set_id > 0) {
            $sql = 'select category_id,dbo.get_categoryName_by_id(category_id) as cat_name,dbo.get_categoryMark_by_id(category_id,'.$ques_set_id.') as total from exm_questions where id in (select question_id from exm_question_set_question_map where question_set_id='.$ques_set_id.') GROUP BY category_id';
            $res = $this->db->query($sql);

            $result = $res->result();
            //var_dump($result);die;
            return $result;
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }




  
    /**
     * Update a question
     *
     * @param $set_id
     * @param $set_info
     * @return bool
     */
    public function update_question_set($set_id, $set_info)
    {
        $set_id = (int)$set_id;
        
        if ($set_id > 0) {

            // TODO: workaround to allow \ char. may be use &#92; instead
            $set['name'] = $set_info['name'];                   
            $this->db->where('id', $set_id);
            $this->db->update($this->table_question_set, $set);

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
    public function update_question_set_single_field($question_id, $field_name, $field_value)
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



    public function select_question_set_limit($arrayName = array(),$limit, $offset = 0)
    {
        $this->db->select("*,(select count(*) from exm_question_set_question_map TB where TB.question_set_id=TA.id) as total");
        if (count($arrayName)) { $this->db->where($arrayName); }
        $this->db->limit($limit, $offset);
        $this->db->order_by("TA.id",'desc');
        $query = $this->db->get('exm_question_set TA');
        //echo $this->db->last_query();  die();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }else{
            return false;
        }
    }


    public function set_search($arrayName,$limit, $offset = 0)
    {
        $this->db->select("*,(select count(*) from exm_question_set_question_map TB where TB.pull_id=TA.id) as total");
        if ($arrayName) { $this->db->like('TA.name',$arrayName,'both'); }
        $this->db->limit($limit, $offset);
        $this->db->order_by("TA.id",'desc');
        $query = $this->db->get('exm_question_set TA');
        //echo $this->db->last_query();  die();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }else{
            return false;
        }
    }


    public function mappedSetPool($arrayName)
    {
        $this->db->select("TA.*,TB.is_mandatory as mandatory_check,TB.question_mark as current_mark");
        $this->db->from('exm_questions TA');
        $this->db->order_by('TA.id','DESCf');
        $this->db->where($arrayName);
        $this->db->join('exm_question_set_question_map TB', "TA.id = TB.question_id", "left");
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result_array();
        }else{
            return false;
        }
    }

  
    
    
    public function check_question_set($question_category_id, $question_text)
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



    
     /**
     * Delete a category by ID
     *
     * @param $category_id
     * @return int
     */
    public function delete_question_set($set_id)
    {

        $set_id = (int)$set_id;
        
        if ($set_id > 0) {

            // BL; Default category cannot be deleted.

            $to_be_deleted_set = $this->get_question_set($set_id);

            $this->db->where('id', $set_id);
           $res = $this->db->delete($this->table_question_set);

            //$res = (int)$this->db->affected_rows();
            //print_r($res); die();
            if ($res > 0) {

                // update cat_parent of all child categories to
                // prevent broken parent-child relationship
                return $res;

            } else {
                $this->error_message = 'Question set delete unsuccessful. DB error.';
                return 0;
            }

        } else {
            $this->error_message = 'Invalid id.';
            return 0;
        }
    }
    

}

/* End of file question_model.php */
/* Location: ./application/models/question_model.php */