<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Category_model extends CI_Model
{
    private $table_name = 'categories';
    private $table_sub_name = 'exm_sub_categories';
    private $table_sub_two_name = 'exm_sub_two_categories';
    private $table_sub_three_name = 'exm_sub_three_categories';
    private $table_sub_four_name = 'exm_sub_four_categories';
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

            $sql = "SELECT COUNT(*) no_of_questions FROM ". $this->db->dbprefix('questions') ."
                    WHERE category_id = $category_id";
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

            $sql = "SELECT COUNT(*) no_of_questions FROM ". $this->db->dbprefix('questions') ."
                    WHERE category_id = $category_id
                    AND ques_type = '". $question_type ."'
                    AND (
                        (convert(datetime,ques_expiry_date) > GETDATE())
                        OR 
                        (ques_expiry_date > convert(datetime,'1990-01-01 00:00:00')) 
                        OR 
                        ques_expiry_date IS NULL
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

    public function get_CatUserName($id)
    {
        $this->db->select('dbo.get_user_login_name_by_id('.$id.') as created_by');
        $query = $this->db->get('');

        if ($query->num_rows() > 0) {
            $Rows=$query->row();
            return $Rows->created_by;
        } else {
            return '';
        }
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
                if ($key == 'category') {
                    $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    //$this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

         // print_r_pre($filter);

        $this->db->order_by('id','DESC');
        /*$this->db->select('id,
                         (SELECT cat_name FROM exm_sub_three_categories WHERE exm_sub_three_categories.id=exm_sub_four_categories.sub_three_cat_parent) as sub_three_cat_parent_name,
                         (SELECT cat_name FROM exm_sub_two_categories WHERE exm_sub_two_categories.id=exm_sub_four_categories.sub_two_cat_parent) as sub_two_cat_parent_name,
                         (SELECT cat_name FROM exm_sub_categories WHERE exm_sub_categories.id=exm_sub_four_categories.sub_cat_parent) as sub_cat_parent_name,
                         (SELECT cat_name FROM exm_categories WHERE exm_categories.id=exm_sub_four_categories.cat_parent) as cat_parent_name,
                         sub_three_cat_parent,
                         sub_two_cat_parent,
                         sub_cat_parent,
                         cat_parent,
                         cat_name,
                         created_at,
                         dbo.get_user_login_name_by_id(created_by) as created_by
                        ');*/

        $this->db->select('id,
                         cat_name,
                         created_at,
                         dbo.get_user_login_name_by_id(created_by) as created_by
                        ');
        $query = $this->db->get('exm_categories', $limit, $offset);

        //print_r_pre($this->db->last_query());

        //echo 'hi';die;

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'category') {
                        $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                    } else {
                        //$this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }
            $this->db->select('id,dbo.get_user_login_name_by_id(created_by) as created_by');
            $this->db->order_by('id','DESC');
            $this->db->from('exm_categories');

            $result['count'] = $this->db->count_all_results();
        }
        //print_r_pre($result);

        return $result;
    }

    public function get_paged_subcategories($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'category') {
                    $this->db->or_where("((SELECT cat_name FROM exm_categories WHERE exm_categories.id=exm_sub_categories.cat_parent) LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    //$this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

         // print_r_pre($filter);

        $this->db->order_by('id','DESC');
        /*$this->db->select('id,
                         (SELECT cat_name FROM exm_sub_three_categories WHERE exm_sub_three_categories.id=exm_sub_four_categories.sub_three_cat_parent) as sub_three_cat_parent_name,
                         (SELECT cat_name FROM exm_sub_two_categories WHERE exm_sub_two_categories.id=exm_sub_four_categories.sub_two_cat_parent) as sub_two_cat_parent_name,
                         (SELECT cat_name FROM exm_sub_categories WHERE exm_sub_categories.id=exm_sub_four_categories.sub_cat_parent) as sub_cat_parent_name,
                         (SELECT cat_name FROM exm_categories WHERE exm_categories.id=exm_sub_four_categories.cat_parent) as cat_parent_name,
                         sub_three_cat_parent,
                         sub_two_cat_parent,
                         sub_cat_parent,
                         cat_parent,
                         cat_name,
                         created_at,
                         dbo.get_user_login_name_by_id(created_by) as created_by
                        ');*/

        $this->db->select('id,
                         cat_name,
                         (SELECT cat_name FROM exm_categories WHERE exm_categories.id=exm_sub_categories.cat_parent) as cat_parent_name,
                         created_at,
                         dbo.get_user_login_name_by_id(created_by) as created_by
                        ');
        $query = $this->db->get('exm_sub_categories', $limit, $offset);

        //print_r_pre($this->db->last_query());

        //echo 'hi';die;

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'category') {
                        $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                    } else {
                        //$this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }
            $this->db->select('id,dbo.get_user_login_name_by_id(created_by) as created_by');
            $this->db->order_by('id','DESC');
            $this->db->from('exm_sub_categories');

            $result['count'] = $this->db->count_all_results();
        }
        //print_r_pre($result);

        return $result;
    }

    public function get_paged_subTwocategories($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'category') {
                    $this->db->or_where("((SELECT cat_name FROM exm_categories WHERE exm_categories.id=exm_sub_categories.cat_parent) LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    //$this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

         // print_r_pre($filter);

        $this->db->order_by('id','DESC');
        /*$this->db->select('id,
                         (SELECT cat_name FROM exm_sub_three_categories WHERE exm_sub_three_categories.id=exm_sub_four_categories.sub_three_cat_parent) as sub_three_cat_parent_name,
                         (SELECT cat_name FROM exm_sub_two_categories WHERE exm_sub_two_categories.id=exm_sub_four_categories.sub_two_cat_parent) as sub_two_cat_parent_name,
                         (SELECT cat_name FROM exm_sub_categories WHERE exm_sub_categories.id=exm_sub_four_categories.sub_cat_parent) as sub_cat_parent_name,
                         (SELECT cat_name FROM exm_categories WHERE exm_categories.id=exm_sub_four_categories.cat_parent) as cat_parent_name,
                         sub_three_cat_parent,
                         sub_two_cat_parent,
                         sub_cat_parent,
                         cat_parent,
                         cat_name,
                         created_at,
                         dbo.get_user_login_name_by_id(created_by) as created_by
                        ');*/

        $this->db->select('id,
                         cat_name,
                         (SELECT cat_name FROM exm_categories WHERE exm_categories.id=exm_sub_two_categories.cat_parent) as cat_parent_name,
                         (SELECT cat_name FROM exm_sub_categories WHERE exm_sub_categories.id=exm_sub_two_categories.sub_cat_parent) as sub_cat_parent_name,
                         created_at,
                         dbo.get_user_login_name_by_id(created_by) as created_by
                        ');
        $query = $this->db->get('exm_sub_two_categories', $limit, $offset);

        //print_r_pre($this->db->last_query());

        //echo 'hi';die;

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'category') {
                        $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                    } else {
                        //$this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }
            $this->db->select('id,dbo.get_user_login_name_by_id(created_by) as created_by');
            $this->db->order_by('id','DESC');
            $this->db->from('exm_sub_two_categories');

            $result['count'] = $this->db->count_all_results();
        }
        //print_r_pre($result);

        return $result;
    }

    public function get_pagedThreecategories($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'category') {
                    $this->db->or_where("((SELECT cat_name FROM exm_categories WHERE exm_categories.id=exm_sub_categories.cat_parent) LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    //$this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

         // print_r_pre($filter);

        $this->db->order_by('id','DESC');
        /*$this->db->select('id,
                         (SELECT cat_name FROM exm_sub_three_categories WHERE exm_sub_three_categories.id=exm_sub_four_categories.sub_three_cat_parent) as sub_three_cat_parent_name,
                         (SELECT cat_name FROM exm_sub_two_categories WHERE exm_sub_two_categories.id=exm_sub_four_categories.sub_two_cat_parent) as sub_two_cat_parent_name,
                         (SELECT cat_name FROM exm_sub_categories WHERE exm_sub_categories.id=exm_sub_four_categories.sub_cat_parent) as sub_cat_parent_name,
                         (SELECT cat_name FROM exm_categories WHERE exm_categories.id=exm_sub_four_categories.cat_parent) as cat_parent_name,
                         sub_three_cat_parent,
                         sub_two_cat_parent,
                         sub_cat_parent,
                         cat_parent,
                         cat_name,
                         created_at,
                         dbo.get_user_login_name_by_id(created_by) as created_by
                        ');*/

        $this->db->select('id,
                         cat_name,
                         (SELECT cat_name FROM exm_categories WHERE exm_categories.id=exm_sub_three_categories.cat_parent) as cat_parent_name,
                         (SELECT cat_name FROM exm_sub_categories WHERE exm_sub_categories.id=exm_sub_three_categories.sub_cat_parent) as sub_cat_parent_name,
                         (SELECT cat_name FROM exm_sub_two_categories WHERE exm_sub_two_categories.id=exm_sub_three_categories.sub_two_cat_parent) as sub_two_cat_parent_name,
                         created_at,
                         dbo.get_user_login_name_by_id(created_by) as created_by
                        ');
        $query = $this->db->get('exm_sub_three_categories', $limit, $offset);

        //print_r_pre($this->db->last_query());

        //echo 'hi';die;

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'category') {
                        $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                    } else {
                        //$this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }
            $this->db->select('id,dbo.get_user_login_name_by_id(created_by) as created_by');
            $this->db->order_by('id','DESC');
            $this->db->from('exm_sub_three_categories');

            $result['count'] = $this->db->count_all_results();
        }
        //print_r_pre($result);

        return $result;
    }

    public function get_pagedFourcategories($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'category') {
                    $this->db->or_where("((SELECT cat_name FROM exm_categories WHERE exm_categories.id=exm_sub_categories.cat_parent) LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    //$this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

         // print_r_pre($filter);

        $this->db->order_by('id','DESC');

        $this->db->select('id,
                         cat_name,
                         (SELECT cat_name FROM exm_categories WHERE exm_categories.id=exm_sub_four_categories.cat_parent) as cat_parent_name,
                         (SELECT cat_name FROM exm_sub_categories WHERE exm_sub_categories.id=exm_sub_four_categories.sub_cat_parent) as sub_cat_parent_name,
                         (SELECT cat_name FROM exm_sub_two_categories WHERE exm_sub_two_categories.id=exm_sub_four_categories.sub_two_cat_parent) as sub_two_cat_parent_name,
                         (SELECT cat_name FROM exm_sub_three_categories WHERE exm_sub_three_categories.id=exm_sub_four_categories.sub_three_cat_parent) as sub_three_cat_parent_name,
                         created_at,
                         dbo.get_user_login_name_by_id(created_by) as created_by
                        ');
        $query = $this->db->get('exm_sub_four_categories', $limit, $offset);

        //print_r_pre($this->db->last_query());

        //echo 'hi';die;

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'category') {
                        $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                    } else {
                        //$this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }
            $this->db->select('id,dbo.get_user_login_name_by_id(created_by) as created_by');
            $this->db->order_by('id','DESC');
            $this->db->from('exm_sub_four_categories');

            $result['count'] = $this->db->count_all_results();
        }
        //print_r_pre($result);

        return $result;
    }

    public function get_paged_categoriesByString($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;



        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_cat_name') {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                } else {
                    $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(cat_parent LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(id LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                }
            }
        }

        $this->db->order_by('id','DESC');
        $this->db->select($this->table_name.'.id,cat_parent,cat_name,created_at,dbo.get_user_login_name_by_id(created_by) as created_by');
        $query = $this->db->get($this->table_name, $limit, $offset);

        //print_r_pre($this->db->last_query());    

        //echo 'hi';die;

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_cat_name') {
                        $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    } else {
                        $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(cat_parent LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(id LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                        $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                    }
                }
            }
            $this->db->select($this->table_name.'.id,cat_parent,cat_name,created_at,dbo.get_user_login_name_by_id(created_by) as created_by');
            $this->db->order_by('id','DESC');
            $this->db->from($this->table_name);

            $result['count'] = $this->db->count_all_results();
        }

        return $result;
    }

    public function get_padded_paged_categories($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        $cats_tree = $this->get_categories_recursive('',$filter);
        //print_r_pre($cats_tree);die;
        $cats = $this->get_padded_categories($cats_tree);

        
        $result['result'] = array_slice($cats, $offset, $limit);

        //print_r_pre($result['result']);

        if (count($result['result']) <= 0) {
            $result['result'] = false;
        }
        $result['count'] = count($cats);

        return $result;
    }

    public function get_survey_padded_categories($cats = array(), $depth = 0, &$padded_cats = array())
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
            //$new_cat->created_by = $this->get_CatUserName($cats[$i]['created_by']);
            //$new_cat->created_at = convert_to_datetime_format($cats[$i]['created_at']);
            $new_cat->cat_parent = $cats[$i]['cat_parent'];

            if ($depth == 1) {
                $new_cat->cat_name = $depth_str . $cats[$i]['cat_name'];
            } else {
                $new_cat->cat_name = $depth_str . $cats[$i]['cat_name'];
            }

            $padded_cats[] = $new_cat;
            $this->get_survey_padded_categories($cats[$i]['children'], $depth, $padded_cats);
        }

        return $padded_cats;
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
            $new_cat->created_by = $this->get_CatUserName($cats[$i]['created_by']);
            $new_cat->created_at = convert_to_datetime_format($cats[$i]['created_at']);
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


    public function get_categories_recursive($parent_id = 0,$filter=array())
    {
        $parent_id = (int)$parent_id;
        $child_list = $this->get_child_categories($parent_id,$filter);

        for ($i=0; $i < count($child_list); $i++) {
            $child_list[$i]['children'] = $this->get_categories_recursive($child_list[$i]['id']);
        }

        return $child_list;
    }

    public function get_survey_categories_recursive($parent_id = 0)
    {
        $parent_id = (int)$parent_id;
        $child_list = $this->get_survey_child_categories($parent_id);

        for ($i=0; $i < count($child_list); $i++) {
            $child_list[$i]['children'] = $this->get_survey_categories_recursive($child_list[$i]['id']);
        }

        return $child_list;
    }

    public function get_survey_child_categories($parent_id = 0)
    {
        $parent_id = (int)$parent_id;

        $sql = 'SELECT * FROM exm_survey_categories 
                WHERE cat_parent = '. $parent_id .' 
                ORDER BY id DESC';
        
        $res = $this->db->query($sql);

        return $res->result_array();
    }

    public function get_child_categories($parent_id = 0,$filter=array())
    {

        $parent_id = (int)$parent_id;

        $this->db->where('cat_parent', $parent_id);
        

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_cat_name') {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                } else {
                    $this->db->or_where("(cat_name LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(cat_parent LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(id LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(dbo.get_user_login_name_by_id(created_by) LIKE '%". $value['value'] ."%')", '', false);
                    $this->db->or_where("(created_at LIKE '%". $value['value'] ."%')", '', false);
                }
            }
        }



        //$this->db->order_by('id','DESC');
        //$this->db->select('id,cat_parent,cat_name,created_at,dbo.get_user_login_name_by_id(created_by) as created_by');
        $res = $this->db->get($this->db->dbprefix($this->table_name));

        /*print_r_pre($res->result_array()); die();
        
        $parent_id = (int)$parent_id;

        $sql = 'SELECT * FROM '. $this->db->dbprefix($this->table_name) .' 
                WHERE cat_parent = '. $parent_id .' 
                ORDER BY id DESC';
        
        $res = $this->db->query($sql);
*/
        
         

        return $res->result_array();
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

    public function add_sub_category($category)
    {
        if (is_array($category)) {
            $insert = $this->db->insert($this->table_sub_name, $category);
            //print_r($insert); die();
            if ($this->db->insert_id() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'Sub Category add unsuccessful. DB error.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    public function add_sub_two_category($category)
    {
        if (is_array($category)) {
            $insert = $this->db->insert($this->table_sub_two_name, $category);
            //print_r($insert); die();
            if ($this->db->insert_id() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'Sub Category add unsuccessful. DB error.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    public function add_sub_three_category($category)
    {
        if (is_array($category)) {
            $insert = $this->db->insert($this->table_sub_three_name, $category);
            //print_r($insert); die();
            if ($this->db->insert_id() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'Sub 3 Category add unsuccessful. DB error.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    public function add_sub_four_category($category)
    {
        if (is_array($category)) {
            $insert = $this->db->insert($this->table_sub_four_name, $category);
            //print_r($insert); die();
            if ($this->db->insert_id() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'Sub 3 Category add unsuccessful. DB error.';
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
            $default_cat_id = (int)$ci->option_model->get_option('default_category');
            if ($default_cat_id == $category_id) {
                $this->error_message = 'Default category can\'t be deleted.';
                return 0;
            }

            $to_be_deleted_category = $this->get_category($category_id);

            $this->db->where('id', $category_id);
           $res = $this->db->delete($this->table_name);

            //$res = (int)$this->db->affected_rows();
            //print_r($res); die();
            if ($res > 0) {

                // update cat_parent of all child categories to
                // prevent broken parent-child relationship
                $sql = 'UPDATE '. $this->db->dbprefix($this->table_name) .'
                        SET cat_parent = '. (int)$to_be_deleted_category->cat_parent .'
                        WHERE cat_parent = '. (int)$to_be_deleted_category->id;
                $this->db->query($sql);
                // All contents under the deleted category will be assigned to default category
                $sql = 'UPDATE '. $this->db->dbprefix('questions') .'
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