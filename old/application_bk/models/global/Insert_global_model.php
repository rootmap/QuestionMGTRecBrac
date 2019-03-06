<?php
/**
* 
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Insert_global_model extends CI_Model
{
	public  $error_message    	=   '';
    public  $success_message  	=   '';

	function __construct()
	{
		parent::__construct();
	}

	public function globalinsert($tbl,$arrayName = array())
	{
		if (is_array($arrayName)) {
            $this->db->insert($tbl, $arrayName);
            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                return false;
            }
        }
	}

    public function globalinsertbatch($tbl,$arrayName)
    {
       return $this->db->insert_batch($tbl,$arrayName);
    }

    public function update_mapping($tbl,$index = array(),$value = array())
    {
        $this->db->where($index);
        $this->db->update($tbl, $value);
        if ($this->db->affected_rows() > 0) {
            return $this->db->affected_rows();
        } else {
            $this->error_message = 'Data update unsuccessful. DB error.';
            return false;
        }
    }

    
}
?>