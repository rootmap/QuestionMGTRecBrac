<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Option_model extends CI_Model
{
    private $table_name = 'options';
    function __construct()
    {
        parent::__construct();
        $this->load->helper('serialize');
    }

    public function get_all_options()
    {
        $all_options = array();
        $this->db->select('option_name, option_value');
        $this->db->order_by('option_name', 'ASC');
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get($this->table_name);
        if ($query->num_rows() > 0) {
            $options = $query->result();
            for ($i=0; $i<count($options); $i++) {
                $all_options[$options[$i]->option_name] = maybe_unserialize($options[$i]->option_value);
            }

        }

        return $all_options;
    }

    /**
     * @param string $key
     * @return object onsuccess, bool onerror
     */
    public function get_option($key = '')
    {
        if($key == '') { return FALSE; }

        $this->db->where('option_name', $key);
        $this->db->limit(1);

        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {

            return maybe_unserialize($query->first_row()->option_value);

        } else {
            return FALSE;
        }
    }

    private function get_option_id($key = '')
    {
        if($key == '') { return FALSE; }

        $this->db->where('option_name', $key);
        $this->db->limit(1);

        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->first_row()->id;
        } else {
            return FALSE;
        }
    }

    /**
     * Add an option;
     * If an option exist with $key then only return the option id
     *
     * @param string $key
     * @param string $value could be string/array/object
     * @return bool|int
     */
    public function add_option($key = '', $value = '')
    {
        if($key == '') { return 0; }
        $option_id = $this->get_option_id($key);

        if($option_id === FALSE){

            $value = maybe_serialize($value);

            $data = array(
                'option_name' => $key,
                'option_value' => $value
            );
            $this->db->insert($this->table_name, $data);
            
            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                return FALSE;
            }

        } else {
            return $option_id;
        }
    }

    public function update_option($key = '', $value = '')
    {
        if($key == '') { return 0; }
        $option_id = $this->get_option_id($key);

        if($option_id === FALSE) {

            return $this->add_option($key, $value);

        } else {

            $value = maybe_serialize($value);

            $this->db->where('option_name', $key);
            $this->db->where('id', $option_id);

            $data = array(
                'option_value' => $value
            );
            $this->db->update($this->table_name, $data);

            return $option_id;
        }
    }

    public function delete_option($key = '')
    {
        if($key == '') { return FALSE; }
        $option_id = $this->get_option_id($key);

        if($option_id !== FALSE && $option_id > 0){

            $this->db->where('id', $option_id);
            $this->db->limit(1);
            $this->db->delete($this->table_name);

            if($this->db->affected_rows() > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
}

/* End of file option_model.php */
/* Location: ./application/models/option_model.php */