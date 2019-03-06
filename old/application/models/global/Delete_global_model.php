<?php
/**
* 
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Delete_global_model extends CI_Model
{
	
	public  $error_message    	=   '';
    public  $success_message  	=   '';
    
	function __construct()
	{
		parent::__construct();
	}

	public function globaldelete($tbl,$arrayName = array())
	{
		$this->db->where($arrayName); 
        $this->db->delete($tbl);
        return true; 
	}
}
?>