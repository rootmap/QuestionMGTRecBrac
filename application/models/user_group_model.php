<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class User_group_model extends CI_Model
{
    private $table_name = 'user_groups';
    private $group_privilage_table_name = 'group_privilage';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Get number of groups
     *
     * @return int
     */
    public function get_user_group_count()
    {
        return $this->db->count_all($this->table_name);
    }

    /**
     * Get the number of user teams under a group
     *
     * @param int $group_id
     * @return int
     */
    public function get_team_count($group_id = 0)
    {
        $group_id = (int)$group_id;

        if ($group_id > 0) {

            $sql = "SELECT COUNT(*) no_of_teams FROM ". $this->db->dbprefix('user_teams') ."
                    WHERE group_id = ". $group_id;
            $res = $this->db->query($sql);
            return $res->row()->no_of_teams;

        } else {
            return 0;
        }
    }

    public function get_user_groups()
    {
        $this->db->order_by('group_name','ASC');
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    /**
     * Get paginated list of groups
     *
     * @param $limit
     * @param int $offset
     * @param array $filter
     * @return bool
     */
    public function get_paged_user_groups($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_group_name') {
                    $this->db->where("(group_name LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

        $this->db->order_by('group_name','ASC');
        $query = $this->db->get($this->table_name, $limit, $offset);

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_group_name') {
                        $this->db->where("(group_name LIKE '%". $value['value'] ."%')", '', false);
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
     * Get single group by group ID
     *
     * @param $group_id
     * @return bool
     */
    public function get_user_group($group_id)
    {
        $group_id = (int)$group_id;

        if ($group_id > 0) {
            $this->db->where('id', $group_id);
    		$query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'User Group not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function get_group_name($group_id)
    {
        $group_id = (int)$group_id;

        if ($group_id > 0) {

            $group = $this->get_user_group($group_id);
            if ($group) {
                return $group->group_name;
            } else {
                $this->error_message = 'User Group not found.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    /**
     * Insert an user group
     *
     * @param $group
     * @return bool
     */
    public function add_user_group($group, $priv_ids, $user_group_ids)
    {
        if (is_array($group)) {
            $this->db->insert($this->table_name, $group);
            
            if ($this->db->affected_rows() > 0) {
                $insert_id = $this->db->insert_id();
                if( is_array($priv_ids) ){
                    foreach($priv_ids as $k=>$v){
                        $data[$k]['group_id'] = $insert_id;
                        $data[$k]['privilage_id'] = $v;
                        $data[$k]['group_id_for_pass'] = $user_group_ids;
                    }
                    $this->db->insert_batch($this->group_privilage_table_name, $data);
                }
                return $insert_id;
            } else {
                $this->error_message = 'User Group add unsuccessful. DB error.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    /**
     * Update an user group
     *
     * @param $group_id
     * @param $group
     * @return bool
     */
    public function update_user_group($group_id, $group, $priv_ids, $user_group_ids)
    {
        $group_id = (int)$group_id;

        if ($group_id > 0) {

            $this->db->where('id', $group_id);
            $this->db->limit(1);
            $this->db->update($this->table_name, $group);
            
            if ($this->db->affected_rows() >= 0) {
                if( $priv_ids ){
                    foreach($priv_ids as $k=>$v){
                        $data[$k]['group_id'] = $group_id;
                        $data[$k]['privilage_id'] = $v;
                        $data[$k]['group_id_for_pass'] = $user_group_ids;
                    }
                    
                    $this->db->where('group_id', $group_id); 
                    $this->db->delete($this->group_privilage_table_name); 
                    
                    $this->db->insert_batch($this->group_privilage_table_name, $data);
                }
            }

            return true;

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    /**
     * Delete a user group by ID
     *
     * @param $group_id
     * @return int
     */
    public function delete_user_group($group_id)
    {
        $group_id = (int)$group_id;
        
        if ($group_id > 0) {

            $this->db->where('id', $group_id);
            $this->db->limit(1);
            $this->db->delete($this->table_name);

            $res = (int)$this->db->affected_rows();

            if ($res > 0) {
                $this->db->where('group_id', $group_id); 
                $this->db->delete($this->group_privilage_table_name);
                    
                return $res;
            } else {
                $this->error_message = 'User Group delete unsuccessful. DB error.';
                return 0;
            }
        } else {
            $this->error_message = 'Invalid Id.';
            return 0;
        }
    }
}

/* End of file user_group_model.php */
/* Location: ./application/models/user_group_model.php */