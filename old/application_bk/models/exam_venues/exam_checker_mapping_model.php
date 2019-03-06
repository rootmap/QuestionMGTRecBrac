<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Exam_checker_mapping_model extends CI_Model{
	private $table_venue = 'users';
    private $table_exam = 'exams';

    public $error_message = '';

    public function get_user_name_map()
    {
        $sql= "SELECT * FROM ".$this->db->dbprefix('users');
        $res = $this->db->query($sql);
        //echo $this->db->last_query(); die();
        return $res->result();
    }

     public function get_exam_user_name_limit( $limit, $offset=0, $value = array()){
        //var_dump($value); die();
        if(count($value)){
            $this->db->where($value);
        }
        $sql = "SELECT EC.*, EX.exam_title AS EXAM, US.user_first_name AS FNAME, US.user_last_name AS LNAME
        FROM exm_exam_checker_map AS EC
        LEFT JOIN exm_exams AS EX ON EC.exam_id = EX.id
        LEFT JOIN exm_users AS US ON EC.user_id = US.id";
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

}