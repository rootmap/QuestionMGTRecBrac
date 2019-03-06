<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Venue_model extends CI_Model
{
    private $table_name = 'venue';
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
    public function get_venue_count()
    {
        return $this->db->count_all($this->table_name);
    }

    /**
     * Get the number of questions under a category
     *
     * @param int $category_id
     * @return int
     */



   /* public function get_venue()
    {
        $this->db->order_by('name','ASC');
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }*/

    public function get_padded_venue($cats = array(), $depth = 0, &$padded_cats = array())
    {
        if (count($cats) <= 0) { return; }

        //echo $depth; die();
        for ($i=0; $i<count($cats); $i++) {
            $new_cat = new stdClass;
            $new_cat->id = $cats[$i]['id'];
            
            $new_cat->name =$cats[$i]['name'];
            $new_cat->start_time =$cats[$i]['start_time'];
            $new_cat->end_time =$cats[$i]['end_time'];
            $new_cat->address = $cats[$i]['address'];
            

            $padded_cats[] = $new_cat;
            //$this->get_padded_categories($cats[$i]['children'], $depth, $padded_cats);
        }

        return $padded_cats;
    }

    public function get_venue_recursive($parent_id = 0)
    {
        //echo 11; die();
        $child_list = $this->get_child_venue($parent_id);
        return $child_list;
    }

    public function get_child_venue($parent_id = 0)
    {

        $sql = 'SELECT * FROM '. $this->db->dbprefix($this->table_name) .' ORDER BY name';
        
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
    public function get_paged_venue($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_name') {
                    $this->db->where("(name LIKE '%". $value['value'] ."%') OR (id LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

        $this->db->order_by('name','ASC');
        $query = $this->db->get($this->table_name, $limit, $offset);

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_name') {
                        $this->db->where("(name LIKE '%". $value['value'] ."%') OR (id LIKE '%". $value['value'] ."%')", '', false);
                    } else {
                        $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }

            $this->db->from($this->table_name);
            $result['count'] = $this->db->count_all_results();
        }

        //echo $this->db->last_query(); die();

        return $result;
    }

    public function get_padded_paged_venue($limit, $offset = 0)
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;
        //echo 1; die();
        $cats_tree = $this->get_venue_recursive();
        $cats = $this->get_padded_venue($cats_tree);
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
    public function get_venue($venue_id)
    {
        $venue_id = (int)$venue_id;

        if ($venue_id > 0) {
            $this->db->where('id', $venue_id);
    		$query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Venue not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function get_venue_by_name($venue_name)
    {
        $venue_name = trim($venue_name);

        if ($venue_name != '') {
            $this->db->where('name', $venue_name);
    		$query = $this->db->get($this->venue_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Venue not found. Invalid venue name.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid venue name.';
            return false;
        }
    }

    public function get_venue_name($venue_id)
    {
        $venue_id = (int)$venue_id;

        if ($venue_id > 0) {

            $venue = $this->get_venue($venue_id);
            if ($venue) {
                return $venue->cat_name;
            } else {
                $this->error_message = 'Venue not found.';
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
    public function add_Venue($venue)
    {
        if (is_array($venue)) {
            $insert = $this->db->insert($this->table_name, $venue);
            //print_r($insert); die();
            if ($this->db->insert_id() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = ' Venue add unsuccessful. DB error.';
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
    public function update_Venue($venue_id, $venue)
    {
        $venue_id = (int)$venue_id;

        if ($venue_id > 0) {

            $this->db->where('id', $venue_id);
            $this->db->update($this->table_name, $venue);

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
    public function delete_venue($venue_id)
    {

        $venue_id = (int)$venue_id;
        
        if ($venue_id > 0) {

            // BL; Default category cannot be deleted.

            $to_be_deleted_venue = $this->get_venue($venue_id);

            $this->db->where('id', $venue_id);
           $res = $this->db->delete($this->table_name);

            //$res = (int)$this->db->affected_rows();
            //print_r($res); die();
            if ($res > 0) {

                // update cat_parent of all child categories to
                // prevent broken parent-child relationship
                return $res;

            } else {
                $this->error_message = 'Venue delete unsuccessful. DB error.';
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