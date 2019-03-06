<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class User_team_model extends CI_Model
{
    private $table_name = 'user_teams';
    private $table_group_name = 'user_groups';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Get number of teams
     *
     * @return int
     */
    public function get_user_team_count()
    {
        return $this->db->count_all($this->table_name);
    }

    /**
     * Get the number of users under a team
     *
     * @param int $team_id
     * @return int
     */
    public function get_user_count($team_id = 0)
    {
        $team_id = (int)$team_id;

        if ($team_id > 0) {

            $sql = "SELECT COUNT(*) no_of_users FROM ". $this->db->dbprefix('users') ."
                    WHERE user_team_id = ". $team_id;
            $res = $this->db->query($sql);
            return $res->row()->no_of_users;

        } else {
            return 0;
        }
    }

    /**
     * Get single team by team ID
     *
     * @param $team_id
     * @return bool
     */
    public function get_user_team($team_id)
    {
        $team_id = (int)$team_id;

        if ($team_id > 0) {
            $this->db->where('id', $team_id);
            $this->db->limit(1);
    		$query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'User Team not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function get_user_team_by_name($team_name)
    {
        if ($team_name != '') {
            $this->db->where('team_name', $team_name);
            $this->db->limit(1);
    		$query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'User Team not found. Invalid team name.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid team name.';
            return false;
        }
    }

    public function get_user_teams()
    {
        $this->db->order_by('team_name','ASC');
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function get_team_name($team_id)
    {
        $team_id = (int)$team_id;

        if ($team_id > 0) {

            $team = $this->get_user_team($team_id);
            if ($team) {
                return $team->team_name;
            } else {
                $this->error_message = 'User Team not found.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function get_user_teams_by_group($group_id = 0)
    {
        $group_id = (int)$group_id;

        if ($group_id > 0) {

            $this->db->where('group_id', $group_id);
            $this->db->order_by('team_name','ASC');
            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->result();
            } else {
                return false;
            }

        } else {
            $this->error_message = 'Invalid Group ID.';
            return false;
        }
    }

    /**
     * Get paginated list of teams
     *
     * @param $limit
     * @param int $offset
     * @return bool
     */
    public function get_paged_user_teams($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_team_name') {
                    $this->db->where("(team_name LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

        $this->db->select($this->table_name.'.*, '. $this->table_group_name .'.group_name');
        $this->db->from($this->table_name);
        $this->db->join($this->table_group_name, $this->table_name .'.group_id = '. $this->table_group_name .'.id', 'LEFT OUTER');
        $this->db->order_by('group_name','ASC');
        $this->db->order_by('team_name','ASC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_team_name') {
                        $this->db->where("(team_name LIKE '%". $value['value'] ."%')", '', false);
                    } else {
                        $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }
            $this->db->from($this->table_name, $this->table_group_name);
            $this->db->join($this->table_group_name, $this->table_name .'.group_id = '. $this->table_group_name .'.id', 'LEFT OUTER');

            $result['count'] = $this->db->count_all_results();
        }

        return $result;
    }

    /**
     * Insert an user team
     *
     * @param $team
     * @return bool
     */
    public function add_user_team($team)
    {
        if (is_array($team)) {
            $this->db->insert($this->table_name, $team);
            
            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'User Team add unsuccessful. DB error.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    /**
     * Update an user team
     *
     * @param $team_id
     * @param $team
     * @return bool
     */
    public function update_user_team($team_id, $team)
    {
        $team_id = (int)$team_id;

        if ($team_id > 0) {

            $this->db->where('id', $team_id);
            $this->db->limit(1);
            $this->db->update($this->table_name, $team);

            return true;

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    /**
     * Delete a user team by ID
     *
     * @param $team_id
     * @return int
     */
    public function delete_user_team($team_id)
    {
        $team_id = (int)$team_id;
        
        if ($team_id > 0) {

            $no_of_users = $this->get_user_count($team_id);
            if ($no_of_users > 0) {
                $this->error_message = $no_of_users .' users already exists in this User Team. Can\'t delete.';
                return false;
            }

            $this->db->where('id', $team_id);
            $this->db->limit(1);
            $this->db->delete($this->table_name);

            $res = (int)$this->db->affected_rows();

            if ($res > 0) {
                return $res;
            } else {
                $this->error_message = 'User Team delete unsuccessful. DB error.';
                return 0;
            }
        } else {
            $this->error_message = 'Invalid Id.';
            return 0;
        }
    }

    public function is_data_exists($tbl , $value = array()){
        
        $this->db->where($value);
        $query = $this->db->get($tbl);
        if ($query->num_rows() > 0) {
            $data = $query->result_array();
            return $data;
        } else {
            $this->error_message = 'No record found.';
            return false;
        }
        
    } // END OF is_data_exists

}

/* End of file user_team_model.php */
/* Location: ./application/models/user_team_model.php */