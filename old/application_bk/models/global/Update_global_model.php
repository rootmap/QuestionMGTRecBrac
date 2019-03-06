<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Update_global_model extends CI_Model
{
	public  $error_message    	=   '';
    public  $success_message  	=   '';
	function __construct(){parent::__construct();}
	public function globalupdate($tbl,$index = array(),$arrayName = array())
	{
		$this->db->where($index);
        $this->db->update($tbl, $arrayName);
        if ($this->db->affected_rows() > 0) {
            return $this->db->affected_rows();
        } else {
            return false;
        }
	}
    public function globalupdatebatch($tbl,$index=array(),$arrayName = array())
    {
        $this->db->where($index);
        $this->db->update($tbl, $arrayName);
        //echo $this->db->last_query(); die();
        if ($this->db->affected_rows() > 0) {
                return $this->db->affected_rows();
            } else {
                return false;
            }
    }

    public function update_batch($tbl, $where = array(), $value  = array())
    {
        return $this->db->update_batch($tbl,$value,$where );
    }
}
?>