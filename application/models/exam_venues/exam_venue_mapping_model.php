<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Exam_venue_mapping_model extends CI_Model
{
    private $table_venue = 'venue';
    private $table_exam = 'exams';

    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    public function get_exam_name()
    {
    	$sql= "SELECT * FROM ".$this->db->dbprefix('exams');
    	$res = $this->db->query($sql);
    	//echo $this->db->last_query(); die();
    	return $res->result();
    }

    public function get_venue_name()
    {
        $sql= "SELECT * FROM ".$this->db->dbprefix('venue');
        $res = $this->db->query($sql);
        //echo $this->db->last_query(); die();
        return $res->result();
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

    public function select_custom_limit($tbl, $limit, $offset=0, $value = array()){
        //var_dump($value); die();
        if(count($value)){
            $this->db->where($value);
        }
        $this->db->from($tbl);
        $this->db->order_by('ID','ASC');
        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        //echo $this->db->last_query(); die();
        if ($query->num_rows() > 0) {
            $data = $query->result_array();
            return $data;
        } else {
            $this->error_message = 'No record found.';
            return false;
        }
        
    } // END OF is_data_exists


    public function get_exam_venue_name_limit( $limit, $offset=0, $value = array()){
        //var_dump($value); die();
        if(count($value)){
            $this->db->where($value);
        }
        $sql = "SELECT EV.*, EX.exam_title AS EXAM, VE.name AS VENUE
        FROM exm_exam_venue_map AS EV
        LEFT JOIN exm_exams AS EX ON EV.exam_id = EX.id
        LEFT JOIN exm_venue AS VE ON EV.venue_id = VE.id";
        $this->db->order_by('ID','ASC');
        $this->db->limit($limit, $offset);

        $res = $this->db->query($sql);
        //echo $this->db->last_query(); die();
        if ($res->num_rows() > 0) {
            $data = $res->result_array();
            return $data;
        } else {
            $this->error_message = 'No record found.';
            return false;
        }
        
    } // END OF is_data_exists

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