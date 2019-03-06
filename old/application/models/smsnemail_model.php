<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class SMSnEMail_model extends CI_Model
{
    private $table_name = 'smsnemail_categories';
    private $table_mapping_name='smsnemail_layout_mapping';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Get number of caterogies
     *
     * @return int
     */
    public function get_category_count()
    {
        return $this->db->count_all($this->table_name);
    }


    public function  get_maillayout_by_examid($exam_id=0){
        if ($exam_id > 0) {
            $sql = 'select A.*  from exm_smsnemail_categories A left join exm_smsnemail_layout_mapping M on A.id = M.layout_id where A.cat_layout_type=2 and M.exam_id=' . $exam_id;
            $res = $this->db->query($sql);
            return $res->result_array();
        }
        else{
            return 0;
        }
    }

    public function  get_users_not_mapping_on_job($exam_ids=array(),$layout_details,$user_details){
        $layout = $layout_details[0]['cat_layout'];
        if ($exam_ids > 0) {
            if((int)$layout_details[0]['cat_layout_type']==1) {
                $phone = $user_details['phone'];
                $sql = "INSERT INTO exm_smsoremail_job (exam_id, user_id,emailornumber,message,type) SELECT E.exam_id,E.user_id,'".$phone."','".$layout."','sms'  FROM exm_user_exams   E WHERE E.exam_id IN (" . implode(',', $exam_ids) . ") and E.user_id not in (select M.user_id from exm_smsoremail_job M where  M.user_id= E.user_id and M.exam_id=E.exam_id and M.type='sms' )";
            }
                else {
                    $mail = $user_details['user_email'];
                    //print_r_pre($layout_details[0]['cat_layout']);die;

                    //print_r_pre($layout);die;
                    $sql = "INSERT INTO exm_smsoremail_job (exam_id, user_id,emailornumber,message,type) SELECT E.exam_id,E.user_id,'".$mail."','".$layout."','email' FROM exm_user_exams  E WHERE E.exam_id IN (" . implode(',', $exam_ids) . ") and E.user_id not in (select M.user_id from exm_smsoremail_job M where  M.user_id= E.user_id and M.exam_id=E.exam_id and M.type='email' )";
                }
            $res = $this->db->query($sql);
            return $res;
        }
        else{
            return 0;
        }
    }

    public function  get_smslayout_by_examid($exam_id=0){
        if ($exam_id > 0) {
            $sql = 'select A.*  from exm_smsnemail_categories A left join exm_smsnemail_layout_mapping M on A.id = M.layout_id where A.cat_layout_type=1 and M.exam_id=' . $exam_id;
            $res = $this->db->query($sql);
            return $res->result_array();
        }
        else{
            return 0;
        }
    }

    /**
     * Get the number of questions under a category
     *
     * @param int $category_id
     * @return int
     */
    public function getAllExam()
    {
            $this->db->order_by('id','DESC');
            $res = $this->db->get($this->db->dbprefix('exams'));
            //echo $this->db->last_query(); die();
            return $res->result_array();
    }

    public function getAllLayout()
    {
            $this->db->order_by('id','DESC');
            $res = $this->db->get($this->db->dbprefix($this->table_name));
            //echo $this->db->last_query(); die();
            return $res->result_array();
    }

    public function get_question_count_by_type($category_id = 0, $question_type = '')
    {
        $category_id = (int)$category_id;
        if ($question_type == '') {
            return $this->get_question_count($category_id);
        }

        if ($category_id > 0) {

            $sql = "SELECT COUNT(*) no_of_questions FROM ". $this->db->dbprefix('questions') ."
                    WHERE category_id = $category_id
                    AND ques_type = '". $question_type ."'
                    AND (
                        ques_expiry_date > now()
                        OR ques_expiry_date = '0000-00-00 00:00:00'
                        OR ques_expiry_date IS NULL
                    )";
            $res = $this->db->query($sql);
            return $res->row()->no_of_questions;

        } else {
            return 0;
        }
    }

    public function get_categories()
    {
        $this->db->order_by('cat_name','ASC');
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function get_padded_categories($cats = array(), $depth = 0, &$padded_cats = array())
    {
        if (count($cats) <= 0) { return; }

        //echo $depth; die();
        for ($i=0; $i<count($cats); $i++) {
            $new_cat = new stdClass;
            $new_cat->id = $cats[$i]['id'];
            
            $new_cat->cat_name =$cats[$i]['cat_name'];
            $new_cat->cat_layout = $cats[$i]['cat_layout'];
            $layout_type="Email";
            if(empty($cats[$i]['cat_layout_type']) || $cats[$i]['cat_layout_type']==1)
            {
                $layout_type="SMS";
            }
            $new_cat->cat_layout_type = $layout_type;
            

            $padded_cats[] = $new_cat;
            //$this->get_padded_categories($cats[$i]['children'], $depth, $padded_cats);
        }

        return $padded_cats;
    }

    public function get_padded_mapping($cats = array(), $depth = 0, &$padded_cats = array())
    {
        if (count($cats) <= 0) { return; }

        //echo $depth; die();
        for ($i=0; $i<count($cats); $i++) {
            $new_cat = new stdClass;
            $new_cat->id = $cats[$i]['id'];
            
            $new_cat->exam_id =$cats[$i]['exam_id'];
            $new_cat->layout_id = $cats[$i]['layout_id'];
            
            $padded_cats[] = $new_cat;
            //$this->get_padded_categories($cats[$i]['children'], $depth, $padded_cats);
        }

        //print_r_pre($padded_cats); die();

        return $padded_cats;
    }

    public function get_categories_recursive($parent_id = 0)
    {
        //echo 11; die();
        $parent_id = (int)$parent_id;
        $child_list = $this->get_child_categories($parent_id);
        return $child_list;
    }

    public function get_child_categories($parent_id = 0)
    {
        $parent_id = (int)$parent_id;

        $sql = 'SELECT * FROM '. $this->db->dbprefix($this->table_name) .' ORDER BY id DESC';
        
        $res = $this->db->query($sql);
        //echo $this->db->last_query(); die();

        return $res->result_array();
    }


    public function get_mapping_recursive($parent_id = 0)
    {
        //echo 11; die();
        $parent_id = (int)$parent_id;
        $child_list = $this->get_child_mapping($parent_id);
        return $child_list;
    }

    public function get_child_mapping($parent_id = 0)
    {
        $parent_id = (int)$parent_id;

        $sql = 'SELECT * FROM '. $this->db->dbprefix($this->table_mapping_name) .' ORDER BY id';
        
        $res = $this->db->query($sql);
        //echo $this->db->last_query(); die();

        return $res->result_array();
    }

    /**
     * Get paginated list of caterogies
     *
     * @param $limit
     * @param int $offset
     * @param array $filter
     * @return bool
     */
    public function get_paged_categories($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_exam_name') {
                    $this->db->where("(exam_name LIKE '%". $value['value'] ."%') OR (id LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

        $this->db->order_by('cat_name','ASC');
        $query = $this->db->get($this->table_mapping_name, $limit, $offset);

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_exam_name') {
                        $this->db->where("(exam_name LIKE '%". $value['value'] ."%') OR (id LIKE '%". $value['value'] ."%')", '', false);
                    } else {
                        $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }

            $this->db->from($this->table_mapping_name);
            $result['count'] = $this->db->count_all_results();
        }

        echo $this->db->last_query(); die();

        return $result;
    }

    

    

    public function get_padded_paged_categories($limit, $offset = 0)
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;
        //echo 1; die();
        $cats_tree = $this->get_categories_recursive();
        $cats = $this->get_padded_categories($cats_tree);
        //print_r_pre($cats); die();
        $result['result'] = array_slice($cats, $offset, $limit);

        if (count($result['result']) <= 0) {
            $result['result'] = false;
        }
        $result['count'] = count($cats);

        return $result;
    }

    /**
     * Get single category by category ID
     *
     * @param $category_id
     * @return bool
     */
    public function get_category($category_id)
    {
        $category_id = (int)$category_id;

        if ($category_id > 0) {
            $this->db->where('id', $category_id);
    		$query = $this->db->get($this->table_name);

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


    public function get_mail_layout()
    {



            $this->db->where('type', 'account');
            $query = $this->db->get('exm_user_mail_layout');

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Category not found. Invalid id.';
                return false;
            }

    }

    public function get_pass_mail_layout()
    {



        $this->db->where('type', 'password');
        $query = $this->db->get('exm_user_mail_layout');

        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            $this->error_message = 'Category not found. Invalid id.';
            return false;
        }

    }

    public function get_mapping($category_id)
    {
        $category_id = (int)$category_id;

        if ($category_id > 0) {
            $this->db->where('id', $category_id);
            $query = $this->db->get($this->table_mapping_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Mapping not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function get_category_by_name($category__name)
    {
        $category__name = trim($category__name);

        if ($category__name != '') {
            $this->db->where('cat_name', $category__name);
    		$query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Category not found. Invalid category name.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid category name.';
            return false;
        }
    }

    public function get_category_name($category_id)
    {
        $category_id = (int)$category_id;

        if ($category_id > 0) {

            $category = $this->get_category($category_id);
            if ($category) {
                return $category->cat_name;
            } else {
                $this->error_message = 'Category not found.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    /**
     * Insert a single category
     *
     * @param $category
     * @return bool
     */
    public function add_category($category)
    {
        if (is_array($category)) {
            $insert = $this->db->insert($this->table_name, $category);
            //print_r($insert); die();
            if ($this->db->insert_id() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'Category add unsuccessful. DB error.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    /**
     * Update a category
     *
     * @param $category_id
     * @param $category
     * @return bool
     */

    public function update_mapping($category_id, $category)
    {
        $category_id = (int)$category_id;

        if ($category_id > 0) {

            $this->db->where('id', $category_id);
            $this->db->update($this->table_mapping_name, $category);

            return true;

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function update_category($category_id, $category)
    {
        $category_id = (int)$category_id;

        if ($category_id > 0) {

            $this->db->where('id', $category_id);
            $this->db->update($this->table_name, $category);

            return true;

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function update_user_mail_layout($data, $status)
    {

        if ($data) {

            $this->db->where('type', $status);
            $this->db->update('exm_user_mail_layout', $data);

            return true;

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }




    /**
     * Delete a category by ID
     *
     * @param $category_id
     * @return int
     */
    public function delete_category($category_id)
    {

        $category_id = (int)$category_id;
        
        if ($category_id > 0) {

            // BL; Default category cannot be deleted.

            $to_be_deleted_category = $this->get_category($category_id);

            $this->db->where('id', $category_id);
           $res = $this->db->delete($this->table_name);

            //$res = (int)$this->db->affected_rows();
            //print_r($res); die();
            if ($res > 0) {

                // update cat_parent of all child categories to
                // prevent broken parent-child relationship
                return $res;

            } else {
                $this->error_message = 'Category delete unsuccessful. DB error.';
                return 0;
            }

        } else {
            $this->error_message = 'Invalid id.';
            return 0;
        }
    }



    public function delete_mapping($category_id)
    {

        $category_id = (int)$category_id;
        
        if ($category_id > 0) {

            // BL; Default category cannot be deleted.

            $to_be_deleted_category = $this->get_category($category_id);

            $this->db->where('id', $category_id);
           $res = $this->db->delete($this->table_mapping_name);

            //$res = (int)$this->db->affected_rows();
            //print_r($res); die();
            if ($res > 0) {

                // update cat_parent of all child categories to
                // prevent broken parent-child relationship
                return $res;

            } else {
                $this->error_message = 'Mapping delete unsuccessful. DB error.';
                return 0;
            }

        } else {
            $this->error_message = 'Invalid id.';
            return 0;
        }
    }



    //mapping 

    public function get_paged_mapping($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_exam_title') {
                    $this->db->where("  (exam_id IN (SELECT id FROM exm_exams WHERE exam_title LIKE '%". $value['value'] ."%')) OR (layout_id IN (SELECT id FROM exm_smsnemail_categories WHERE cat_name LIKE '%". $value['value'] ."%'))", '', false);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

        $this->db->order_by('exm_smsnemail_layout_mapping.id','DESC');
        $this->db->order_by('exm_smsnemail_layout_mapping.created_at','DESC');
        $this->db->join('exm_exams', 'exm_exams.id = exm_smsnemail_layout_mapping.exam_id');
        $this->db->join('exm_smsnemail_categories', 'exm_smsnemail_categories.id = exm_smsnemail_layout_mapping.layout_id');
        $this->db->select('exm_smsnemail_layout_mapping.id,exm_exams.exam_title as exam_name,exm_smsnemail_categories.cat_name as layout_name');
        $query = $this->db->get($this->table_mapping_name, $limit, $offset);

        //echo $this->db->last_query(); die();

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_exam_title') {
                        $this->db->where("(exam_id LIKE '%". $value['value'] ."%')", '', false);
                    } else {
                        $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }

            $this->db->from($this->table_mapping_name);
            $result['count'] = $this->db->count_all_results();
        }

        //print_r_pre($result); die();

        return $result;
    }

    public function get_number_of_mapping($exam_id = 0)
    {
        $no_of_questions = 0;
        $exam_categories = $this->get_exam_mapping($exam_id);

        for($i=0; $i<count($exam_categories); $i++) {
            $no_of_questions += $exam_categories[$i]->no_of_questions;
        }

        return $no_of_questions;
    }

    public function get_exam_mapping($exam_id = 0)
    {
        $exam_categories = array();
        $exam_id = (int)$exam_id;

        if($exam_id <= 0) { return FALSE; }

        $this->db->where('exam_id', $exam_id);
        $query = $this->db->get($this->table_mapping_name);

        if ($query->num_rows() > 0) {
            $exam_categories = $query->result();
        }

        return $exam_categories;
    }

    public function get_used_mapping_count($exam_id)
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




}

/* End of file category_model.php */
/* Location: ./application/models/category_model.php */