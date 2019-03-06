<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Admin_group_model extends CI_Model
{
    private $table_name = 'admin_groups';
    private $table_name_ip = 'allowed_ip';
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
    public function get_admin_group_count()
    {
        return $this->db->count_all($this->table_name);
    }

    /**
     * Get the number of user teams under a group
     *
     * @param int $group_id
     * @return int
     */
    public function get_admin_count($group_id = 0)
    {
        $group_id = (int)$group_id;

        if ($group_id > 0) {

            $sql = "SELECT COUNT(*) no_of_admin FROM ". $this->db->dbprefix('users') ."
                    WHERE admin_group = ". $group_id;
            $res = $this->db->query($sql);
            return $res->row()->no_of_admin;

        } else {
            return 0;
        }
    }

    public function get_admin_groups()
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
    public function get_paged_admin_groups($limit, $offset = 0, $filter = array())
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
    public function get_admin_group($group_id)
    {
        $group_id = (int)$group_id;

        if ($group_id > 0) {
            $this->db->where('id', $group_id);
    		$query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'Admin Group not found. Invalid id.';
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

            $group = $this->get_admin_group($group_id);
            if ($group) {
                return $group->group_name;
            } else {
                $this->error_message = 'Admin Group not found.';
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
    public function add_admin_group($group)
    {
        if (is_array($group)) {
            $this->db->insert($this->table_name, $group);
            
            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'Admin Group add unsuccessful. DB error.';
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
    public function update_admin_group($group_id, $group)
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
    public function delete_admin_group($group_id)
    {
        $group_id = (int)$group_id;
        
        if ($group_id > 0) {

            $this->db->where('id', $group_id);
            $this->db->limit(1);
            $this->db->delete($this->table_name);

            $res = (int)$this->db->affected_rows();

            if ($res > 0) {
                return $res;
            } else {
                $this->error_message = 'Admin Group delete unsuccessful. DB error.';
                return 0;
            }
        } else {
            $this->error_message = 'Invalid Id.';
            return 0;
        }
    }
    
    public function add_admin_ip($data)
    {
    	if (is_array($data)) {
    		$this->db->insert($this->table_name_ip, $data);
    		
    		if ($this->db->affected_rows() > 0) {
    			return $this->db->insert_id();
    		} else {
    			$this->error_message = 'Admin IP add unsuccessful. DB error.';
    			return false;
    		}
    	} else {
    		$this->error_message = 'Invalid parameter.';
    		return false;
    	}
    }
    
    public function get_paged_admin_ip($limit, $offset = 0, $filter = array())
    {
    	$result = array();
    	$result['result'] = false;
    	$result['count'] = 0;
    	
    	if (is_array($filter) && count($filter) > 0) {
    		foreach($filter as $key => $value) {
    			if ($key == 'filter_admin_ip') {
    				$this->db->where("(ip LIKE '%". $value['value'] ."%')", '', false);
    			} else {
    				$this->db->where($filter[$key]['field'], $filter[$key]['value']);
    			}
    		}
    	}
    	 
    	$this->db->select($this->table_name_ip.'.*,'. $this->table_name.'.group_name');
    	$this->db->join($this->table_name, $this->table_name_ip.'.role_id ='.$this->table_name.'.id');
    	  
    	$query = $this->db->get($this->table_name_ip,$limit, $offset); 
    	 
    	$result['result'] = $query->result(); 
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
    		
    		$this->db->from($this->table_name_ip);
    		$result['count'] = $this->db->count_all_results();
    	}
    	
    	return $result;
    }
    
    
    public function update_admin_ip($ip_id, $data)
    {
    	$ip_id= (int)$ip_id;
    	
    	if ($ip_id> 0) {
    		
    		$this->db->where('id', $ip_id);
    		$this->db->limit(1);
    		$this->db->update($this->table_name_ip, $data);
    		
    		return true;
    		
    	} else  {
    		$this->error_message = 'Invalid id.';
    		return false;
    	}
    }
    
    public function delete_admin_ip($ip_id)
    {
    	$ip_id= (int)$ip_id;
    	
    	if ($ip_id> 0) {
    		
    		$this->db->where('id', $ip_id); 
    		$this->db->delete($this->table_name_ip);
    		
    		return true;
    		
    	} else  {
    		$this->error_message = 'Invalid id.';
    		return false;
    	}
    }
    public function get_admin_ip($id)
    {
    	$id= (int)$id;
    	
    	if ($id> 0) {
    		$this->db->where('id', $id);
    		$query = $this->db->get($this->table_name_ip);
    		
    		if ($query->num_rows() > 0) {
    			return $query->row();
    		} else {
    			$this->error_message = 'IP not found. Invalid id.';
    			return false;
    		}
    	} else {
    		$this->error_message = 'Invalid id.';
    		return false;
    	}
    }
}

/* End of file user_group_model.php */
/* Location: ./application/models/user_group_model.php */