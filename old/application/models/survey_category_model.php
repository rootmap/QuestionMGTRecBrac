<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Survey_category_model extends CI_Model
{
    private $table_name = 'survey_categories';
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
        $result=$this->db->count_all($this->table_name);
        return $result;
    }

    /**
     * Get the number of questions under a category
     *
     * @param int $category_id
     * @return int
     */
    public function get_question_count($category_id = 0)
    {
        $category_id = (int)$category_id;

        if ($category_id > 0) {

            $sql = "SELECT COUNT(*) no_of_questions FROM ". $this->db->dbprefix('survey_questions') ."
                    WHERE category_id =".$category_id;
            $res = $this->db->query($sql);
            return $res->row()->no_of_questions;

        } else {
            return 0;
        }
    }

    public function get_question_count_by_type($category_id = 0, $question_type = '')
    {
        $category_id = (int)$category_id;
        if ($question_type == '') {
            return $this->get_question_count($category_id);
        }

        if ($category_id > 0) {

            $sql = "SELECT COUNT(*) no_of_questions FROM ". $this->db->dbprefix('survey_questions') ."
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

        $depth_str = '';
        for ($i=0; $i<$depth; $i++) {
            $depth_str .= '&mdash; ';
        }

        $depth++;

        for ($i=0; $i<count($cats); $i++) {
            $new_cat = new stdClass;
            $new_cat->id = $cats[$i]['id'];
            $new_cat->cat_parent = $cats[$i]['cat_parent'];

            if ($depth == 1) {
                $new_cat->cat_name = $depth_str . $cats[$i]['cat_name'];
            } else {
                $new_cat->cat_name = $depth_str . $cats[$i]['cat_name'];
            }

            $padded_cats[] = $new_cat;
            $this->get_padded_categories($cats[$i]['children'], $depth, $padded_cats);
        }

        return $padded_cats;
    }

    public function get_categories_recursive($parent_id = 0)
    {
        $parent_id = (int)$parent_id;
        $child_list = $this->get_child_categories($parent_id);

        for ($i=0; $i < count($child_list); $i++) {
            $child_list[$i]['children'] = $this->get_categories_recursive($child_list[$i]['id']);
        }

        return $child_list;
    }

    public function get_child_categories($parent_id = 0)
    {
        $parent_id = (int)$parent_id;

        $sql = 'SELECT * FROM '. $this->db->dbprefix($this->table_name) .' 
                WHERE cat_parent = '. $parent_id .' 
                ORDER BY cat_name';
        
        $res = $this->db->query($sql);

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
                if ($key == 'filter_cat_name') {
                    $this->db->where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

        $this->db->order_by('cat_name','ASC');
        $query = $this->db->get($this->table_name, $limit, $offset);

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_cat_name') {
                        $this->db->where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
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

    public function get_padded_paged_categories($limit, $offset = 0)
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        $cats_tree = $this->get_categories_recursive();
        $cats = $this->get_padded_categories($cats_tree);
        if($cats){
            $result['result'] = array_slice($cats, $offset, $limit);

            if (count($result['result']) <= 0) {
                $result['result'] = false;
            }
            $result['count'] = count($cats);
        }
        

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
            $this->db->insert($this->table_name, $category);
            
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
    public function update_category($category_id, $category)
    {
        $category_id = (int)$category_id;

        if ($category_id > 0) {

            if ($category['cat_parent'] == $category_id) {
                $this->error_message = 'Parent category and category cannot be same.' ;
                return false;
            }

            $this->db->where('id', $category_id);
            $this->db->update($this->table_name, $category);

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
        $ci =& get_instance();
        $ci->load->model('option_model');

        $category_id = (int)$category_id;
        
        if ($category_id > 0) {

            // BL; Default category cannot be deleted.
            $default_cat_id = (int)$ci->option_model->get_option('default_survey_category');
            if ($default_cat_id == $category_id) {
                $this->error_message = 'Default category can\'t be deleted.';
                return 0;
            }

            $to_be_deleted_category = $this->get_category($category_id);

            $this->db->where('id', $category_id);
            $res = $this->db->delete($this->table_name);

            //$res = (int)$this->db->affected_rows();

            if ($res > 0) {

                // update cat_parent of all child categories to
                // prevent broken parent-child relationship
                $sql = 'UPDATE '. $this->db->dbprefix($this->table_name) .'
                        SET cat_parent = '. (int)$to_be_deleted_category->cat_parent .'
                        WHERE cat_parent = '. (int)$to_be_deleted_category->id;
                $this->db->query($sql);

                // All contents under the deleted category will be assigned to default category
                $sql = 'UPDATE '. $this->db->dbprefix('survey_questions') .'
                        SET category_id = '. $default_cat_id .'
                        WHERE category_id = '. $category_id;
                $this->db->query($sql);

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

}

/* End of file category_model.php */
/* Location: ./application/models/category_model.php */