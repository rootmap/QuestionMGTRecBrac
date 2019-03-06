<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Select_global_model extends CI_Model
{
	private $table_name = 'admin_groups';
    private $table_name_ip = 'allowed_ip';
    public $error_message = '';
	function __construct()
    {
        parent::__construct();
    }


    public function Select_array($tbl,$arrayName = array())
	{
		if (count($arrayName)) { $this->db->where($arrayName); }
        $query = $this->db->get($tbl);   
        if ($query->num_rows() > 0) {
        	return $query->result_array();
        }else{
        	return false;
        }
	}
    public function Select_array_rand_limit($tbl,$arrayName = array(),$limit = 0)
    {
        $this->db->limit($limit);
        $this->db->order_by('id','RANDOM');
        if (count($arrayName)) { $this->db->where($arrayName); }
        $query = $this->db->get($tbl);   
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }else{
            return false;
        }
    }

    public function mappedpulcategory($arrayName)
    {
        $this->db->select("TA.*,TB.ques_text");
        $this->db->from('exm_question_pull_data TA');
        $this->db->where('TA.cat_id',$arrayName);
        $this->db->join('exm_questions TB', "TA.question_id = TB.id", "left");
        $query = $this->db->get();   
        //echo $this->db->last_query();  die();  
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }else{
            return false;
        }
    }

    public function Selectwherenotinarray($tbl,$fields,$arrayName = array(),$where = array())
    {
        if (count($arrayName)) { $this->db->where_not_in($fields,$arrayName); }
        if (count($where)) { $this->db->where($where); }
        $query = $this->db->get($tbl);   
        //echo $this->db->last_query();  die();  
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }else{
            return false;
        }
    }

    public function Select_array_limit($arrayName = array(),$limit, $offset = 0)
    {
        $this->db->select("*,(select count(*) from exm_question_pull_data TB where TB.pull_id=TA.id) as total");
        if (count($arrayName)) { $this->db->where($arrayName); }
        $this->db->limit($limit, $offset);
        $this->db->order_by("TA.id",'desc');
        $query = $this->db->get('exm_question_pull TA');  
        //echo $this->db->last_query();  die();  
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }else{
            return false;
        }
    }

    public function FlyQuery(
        $arrayCond= array(),
        $tbl_name='raw',
        $returnType='select',
        $select='*',
        $order_by=array(),
        $group_by=array())
    {

        if($tbl_name=='raw')
        {
            $query=$this->db->query($arrayCond[0]);
             $returnVal=$query->result_array();
        }
        else
        {
            $this->db->select($select);
            if(!empty($arrayCond))
            {
                foreach ($arrayCond as $col => $val) {
                    $this->db->where($col,$val);
                }
            }
            if(!empty($order_by))
            {
                foreach ($order_by as $colo => $valo) {
                    $this->db->order_by($colo,$valo);
                }
            }
            if(!empty($group_by))
            {
                foreach ($group_by as $valg) {
                    $this->db->group_by($valg);
                }
            }

            $query = $this->db->get($tbl_name);  
            //echo $this->db->last_query();  die();
            if($returnType=='select')
            {  
                if ($query->num_rows() > 0) {
                    $returnVal=$query->result_array();
                }else{
                    $returnVal=false;
                }
            }
            elseif($returnType=='count')
            {  
                $returnVal=$query->num_rows();
            }
            elseif($returnType=='first')
            {  
                if ($query->num_rows() > 0) {
                    $returnVal=$query->first_row();
                }else{
                    $returnVal=false;
                }
            }
            else
            {
                $returnVal=false;
            }
        }

        return $returnVal;
    }



    public function pool_search($arrayName,$limit, $offset = 0)
    {
        $this->db->select("*,(select count(*) from exm_question_pull_data TB where TB.pull_id=TA.id) as total");
        if ($arrayName) { $this->db->like('TA.pull_name',$arrayName,'both'); }
        $this->db->limit($limit, $offset);
        $this->db->order_by("TA.id",'desc');
        $query = $this->db->get('exm_question_pull TA');  
        //echo $this->db->last_query();  die();  
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }else{
            return false;
        }
    }


    public function mappedpuool($arrayName)
    {
        $this->db->select("TA.*,TB.ques_text");
        $this->db->from('exm_question_pull_data TA');
        $this->db->where($arrayName);
        $this->db->join('exm_questions TB', "TA.question_id = TB.id", "left");
        $query = $this->db->get();   
        //echo $this->db->last_query();  die();  
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }else{
            return false;
        }
    }


    public function select_where_not_in($table,$fields,$notdata=array(),$wheredata = array())
    {
           if($notdata){
            $this->db->where_not_in($fields,$notdata);
           }
        
        if($wheredata){
            $this->db->where($wheredata);
        }
        $query = $this->db->get($table);   
        //echo $this->db->last_query();  die();  
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }else{
            return false;
        }
    }

    public function select_where_in($table,$fields,$indata=array(),$wheredata = array())
    {
           if($indata){
            $this->db->where_in($fields,$indata);
           }
        
        if($wheredata){
            $this->db->where($wheredata);
        }
        $query = $this->db->get($table);   
        //echo $this->db->last_query();  die();  
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }else{
            return false;
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

    public function FlySelectExists($tbl , $object_array = array(),$type=0){
        
        $count_col=count($object_array);
        if(!empty($count_col))
        {
            foreach ($object_array as $col=> $val) {
                $this->db->where($col,$val);
            }
        }
        
        $query = $this->db->get($tbl);
        //echo $this->db->last_query(); die();
        if($type==0)
        {
            return $query->num_rows();
        }
        else
        {
            if ($query->num_rows() > 0) {
                $data = $query->result_array();
                return $data;
            } else {
                $this->error_message = 'No record found.';
                return false;
            }
        }
        
        
    } // END OF is_data_exists


}
?>