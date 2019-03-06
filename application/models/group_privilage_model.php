<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Group_privilage_model extends CI_Model
{
    private $table_name = 'privilage';
    private $group_privilage_table_name = 'group_privilage';
    private $team_table_name = 'user_teams';
    private $user_table_name = 'users';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }
    
    public function get_privilages()
    {
        $this->db->order_by('id','ASC');
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    
    public function get_group_privilage($group_id)
    {
        $this->db->select($this->table_name.'.*, '.$this->group_privilage_table_name.'.group_id_for_pass');
        $this->db->from($this->table_name);
        $this->db->join($this->group_privilage_table_name, "$this->group_privilage_table_name.privilage_id = $this->table_name.id");
        $this->db->where($this->group_privilage_table_name.'.group_id', $group_id);
        $query = $this->db->get();
         
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    
    public function get_permitted_privilages($user_id)
    {
        $this->db->select($this->table_name.'.*');
        $this->db->from($this->table_name);
        $this->db->join($this->group_privilage_table_name, "$this->group_privilage_table_name.privilage_id = $this->table_name.id");
        $this->db->join($this->team_table_name, "$this->group_privilage_table_name.group_id = $this->team_table_name.group_id");
        $this->db->join($this->user_table_name, "$this->team_table_name.id = $this->user_table_name.user_team_id");
        $this->db->where($this->user_table_name.'.id', $user_id);
        $query = $this->db->get();
         
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
}

/* End of file group_model.php */
/* Location: ./application/models/group_model.php */