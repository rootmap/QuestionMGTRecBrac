<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Group_model extends CI_Model
{
    private $table_name = 'exm_user_groups';
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
     * Get the number of users under a group
     *
     * @param int $group_id
     * @return int
     */
    public function get_user_count($group_id = 0)
    {
        $group_id = (int)$group_id;

        if ($group_id > 0) {

            $sql = "SELECT COUNT(*) no_of_users FROM ". $this->db->dbprefix('users') ."
                    WHERE user_group_id = ". $group_id;
            $res = $this->db->query($sql);
            return $res->row()->no_of_users;

        } else {
            return 0;
        }
    }

    public function get_user_groups()
    {
        $this->db->order_by('ug_name','ASC');
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    /**
     * Get paginated list of group
     *
     * @param $limit
     * @param int $offset
     * @return bool
     */
    public function get_paged_user_groups($limit, $offset = 0)
    {
        $this->db->order_by('ug_name','ASC');
        $query = $this->db->get($this->table_name, $limit, $offset);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
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
                return $group->ug_name;
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
    public function add_user_group($group)
    {
        if (is_array($group)) {
            $this->db->insert($this->table_name, $group);
            
            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
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
    public function update_user_group($group_id, $group)
    {
        $group_id = (int)$group_id;

        if ($group_id > 0) {

            $this->db->where('id', $group_id);
            $this->db->limit(1);
            $this->db->update($this->table_name, $group);

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

            $no_of_users = $this->get_user_count($group_id);
            if ($no_of_users > 0) {
                $this->error_message = $no_of_users .' already exists in this User Group. Can\'t delete.';
                return false;
            }

            $this->db->where('id', $group_id);
            $this->db->limit(1);
            $this->db->delete($this->table_name);

            $res = (int)$this->db->affected_rows();

            if ($res > 0) {

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

/* End of file group_model.php */
/* Location: ./application/models/group_model.php */