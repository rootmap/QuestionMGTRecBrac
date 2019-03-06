<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Result_model extends CI_Model
{
    private $table_name = 'results';
    private $table_result_upload = 'result_upload';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    // for frontend use only
    // only shows results which has been attended
    public function get_results_by_user($user_id, $duration = 0)
    {
        $user_id = (int)$user_id;
        $duration = "-".$duration;
        $duration_clause = '';

        if ($duration > 0) {
            $duration_clause = ' AND result_start_time BETWEEN DATEADD(month,'.$duration .',GETDATE()) AND getdate() ';
        }

        if ($user_id > 0) {

            $sql = "SELECT *
                    FROM exm_results LEFT OUTER JOIN exm_user_exams
                        ON exm_results.user_exam_id = exm_user_exams.id, exm_users, exm_exams
                    WHERE exm_user_exams.user_id = exm_users.id
                        AND exm_user_exams.exam_id = exm_exams.id
                        $duration_clause
                        AND user_id = $user_id
                    ORDER BY result_start_time DESC";
                    //echo $sql; die();
            $res = $this->db->query($sql);

            return $res->result();

        } else {
            return false;
        }
    }

    public function get_exam_name()
    {
        $sql= "SELECT * FROM ".$this->db->dbprefix('exams');
        $res = $this->db->query($sql);
        //echo $this->db->last_query(); die();
        return $res->result();
    }


    public function add_bulk_in_results($results)
    {
        $affected_rows = 0;

        if (is_array($results) && count($results) > 0) {
            $newDataStruct=[];
            foreach($results as $row):

                $newDataStruct[]=array('user_exam_id'=>$row['user_exam_id']
                                      ,'result_total_questions'=>$row['result_total_question']
                                      ,'result_total_answered'=>$row['result_total_answered']
                                      ,'result_total_correct'=>$row['result_total_correct']
                                      ,'result_total_wrong'=>$row['result_total_wrong']
                                      ,'result_exam_score'=>$row['result_exam_score']
                                      ,'result_user_score'=>$row['result_user_score']
                                      ,'result_competency_level'=>$row['result_competency_level']
                                      ,'result_time_spent'=>$row['result_time_spent']
                                      ,'result_start_time'=>$row['exam_start_time']
                                      ,'result_end_time'=>$row['exam_end_time']
                                      ,'result_status'=>$row['result_status']
                                      ,'neg_mark'=>0);
            endforeach;    




            $this->db->insert_batch('exm_results', $newDataStruct);
            $affected_rows = $this->db->affected_rows();
        }

        return $affected_rows;
    }

    public function getSubjectHead($ques_set_id)
    {
        $ques_set_id = (int)$ques_set_id;
        if ($ques_set_id > 0) {
            $sql = 'select category_id,dbo.get_categoryName_by_id(category_id) as cat_name,dbo.get_categoryMark_by_id(category_id,'.$ques_set_id.') as total from exm_questions where id in (select question_id from exm_question_set_question_map where question_set_id='.$ques_set_id.') GROUP BY category_id';
            
            $res = $this->db->query($sql);

            $result = $res->result();
            //var_dump($result);die;
            return $result;
        } else {
            return false;
        }
    }

    public function add_bulk_results($results)
    {
        $affected_rows = 0;

        //print_r_pre($results);

        if (is_array($results) && count($results) > 0) {
            $this->db->insert_batch($this->table_result_upload, $results);
            $affected_rows = $this->db->affected_rows();
            $this->add_bulk_in_results($results);
        }

        return $affected_rows;
    }

    public function get_results_by_team_id($exam_id = 0, $group_id = 0, $team_id = 0, $start_date = '', $end_date = '')
    {
        $exam_id = (int)$exam_id;
        $group_id = (int)$group_id;
        $team_id = (int)$team_id;
        $group_where = '';
        $team_where = '';
        $date_where = '';

        if ($exam_id > 0) {
            

            /*$sql = "SELECT
                        user_exam_id,
                        CONCAT(user_first_name,' ', user_last_name) as 'user_name',
                        user_login,
                        team_name,
                        result_total_correct,
                        result_total_wrong,
                        result_total_dontknow,
                        result_total_answered,
                        result_user_score,
                        ((result_user_score / result_exam_score) * 100) as 'result_user_score_percent',
                        result_competency_level,
                        result_start_time,
                        result_end_time
                    FROM
                        exm_results,
                        exm_user_exams,
                        exm_users,
                        exm_user_teams,
                        exm_user_groups
                    WHERE exm_results.user_exam_id = exm_user_exams.id
                    AND exm_user_exams.user_id = exm_users.id
                    AND exm_users.user_team_id = exm_user_teams.id
                    AND exm_user_teams.group_id = exm_user_groups.id
                    AND (result_status = 'complete' or result_status = 'published')
                    AND exam_id = $exam_id
                    $group_where
                    $team_where
                    $date_where
                    ORDER BY result_start_time DESC";
                    //echo $sql; die();*/
                    $this->db->select("user_exam_id,CONCAT(user_first_name,' ', user_last_name) as user_name,user_login,
                        team_name,
                        result_total_correct,
                        result_total_wrong,
                        result_total_dontknow,
                        result_total_answered,
                        result_exam_state,
                        result_user_score, phone,
                        CAST (((CAST(NULLIF(result_user_score,0) AS FLOAT)  / CAST(NULLIF(result_exam_score,0) AS FLOAT) ) * 100) AS FLOAT) as 'result_user_score_percent',                       
                        result_competency_level,
                        result_start_time,
                        result_end_time");
        $this->db->from('exm_results TA');
        $this->db->join('exm_user_exams TB','TB.id = TA.user_exam_id', 'left');
        $this->db->join('exm_users TC','TC.id = TB.user_id', 'left');
        $this->db->join('exm_user_teams TD','TD.id = TC.user_team_id', 'left');
        $this->db->join('exm_user_groups TE','TE.id = TD.group_id', 'left');

        if ($group_id > 0){
               // $group_where = " AND group_id = ";
                $this->db->where('TD.group_id',$group_id);
            }
            if($team_id > 0){
                //$team_where = " AND user_team_id = $team_id ";
                $this->db->where('TC.user_team_id',$team_id);
            }
            if ($start_date != '' && $end_date != '') {
                $date_where = "('". $start_date ."' <= result_start_time AND DATEADD(DAY,1,'".$end_date."') > result_end_time)";
                $this->db->where($date_where);
            }
            $query = $this->db->get();

            //echo $this->db->last_query(); die();
            return $query->result();

        } else {
            $this->error_message = 'Invalid exam id.';
            return false;
        }
    }

    public function get_results_by_user_id($user_id, $start_date = '', $end_date = '')
    {

        $user_id = (int)$user_id;
        //echo $user_id; die();
        $date_where = '';
//DATEADD(month,'.$duration .',GETDATE())
        if ($start_date != '' && $end_date != '') {
            $date_where = " AND ( CAST('". $start_date ."' AS DATE) <= CAST(result_start_time AS DATE) AND DATEADD(DAY,1, CAST('".$end_date."'  AS DATE)) > CAST(result_end_time AS DATE))";
        }

        if ($user_id > 0) {

            $sql = "SELECT
                        r.user_exam_id,
                        e.exam_title,
                        ue.user_id,
                        r.exam_id,
                        r.result_total_correct,
                        r.result_total_wrong,
                        r.result_total_dontknow,
                        r.result_total_answered,
                        r.result_user_score,
                        r.result_exam_state,
                        NULLIF(((NULLIF(r.result_user_score,0) / NULLIF(r.result_exam_score,0)) * 100 ),0) AS 'result_user_score_percent', 
                        r.result_competency_level,
                        r.result_start_time,
                        r.result_end_time,
                        r.result_status
                    FROM
                        exm_results r,
                        exm_user_exams ue,
                        exm_exams e
                    WHERE r.user_exam_id = ue.id
                    AND ue.exam_id = e.id
                    AND (result_status = 'complete' or result_status = 'published')
                    AND ue.user_id = $user_id ".$date_where." ORDER BY result_start_time DESC";
                    //echo $sql; die();
            $res = $this->db->query($sql);
           // echo $this->db->last_query(); die();
            return $res->result();

        } else {
            return false;
        }
    }

    public function get_attendee_list($exam_id = 0, $group_id = 0, $team_id = 0, $start_date = '', $end_date = '')
    {
        $exam_id = (int)$exam_id;
        $group_id = (int)$group_id;
        $team_id = (int)$team_id;
        $group_where = '';
        $team_where = '';
        $date_where = '';

        if ($exam_id > 0) {
 

           /* $sql = "SELECT
                        CONCAT(user_first_name, ' ', user_last_name) as 'user_name',
                        user_login,
                        team_name,
                        ue_start_date,
                        ue_end_date
                    FROM
                        exm_user_exams,
                        exm_users,
                        exm_user_teams,
                        exm_user_groups
                    WHERE exm_user_exams.user_id = exm_users.id
                    AND exm_users.user_team_id = exm_user_teams.id
                    AND exm_user_teams.group_id = exm_user_groups.id
                    AND (ue_status = 'complete' || ue_status = 'pending result')
                    AND exam_id = $exam_id
                    $group_where
                    $team_where
                    $date_where
                    ORDER BY team_name, user_login";

            $res = $this->db->query($sql);*/

        $this->db->select("CONCAT(TC.user_first_name, ' ', TC.user_last_name) as user_name,TC.user_login,TD.team_name,TC.phone,ue_start_date,ue_end_date");
        $this->db->from('exm_results TA');
        $this->db->join('exm_user_exams TB','TB.id = TA.user_exam_id', 'left');
        $this->db->join('exm_users TC','TC.id = TB.user_id', 'left');
        $this->db->join('exm_user_teams TD','TD.id = TC.user_team_id', 'left');
        $this->db->join('exm_user_groups TE','TE.id = TD.group_id', 'left');

        if ($group_id > 0){
               // $group_where = " AND group_id = ";
                $this->db->where('TD.group_id',$group_id);
            }
            if($team_id > 0){
                //$team_where = " AND user_team_id = $team_id ";
                $this->db->where('TC.user_team_id',$team_id);
            }
            if ($start_date != '' && $end_date != '') {
                $date_where = "('". $start_date ."' <= result_start_time AND DATEADD(DAY,1,'".$end_date."') > result_end_time)";
                $this->db->where($date_where);
            }
            $query = $this->db->get();


            return $query->result();

        } else {
            $this->error_message = 'Invalid exam id.';
            return false;
        }
    }

    public function get_attendee_list_count($exam_id = 0, $group_id = 0, $team_id = 0, $start_date = '', $end_date = '')
    {
        $exam_id = (int)$exam_id;
        $group_id = (int)$group_id;
        $team_id = (int)$team_id;
        $group_where = '';
        $team_where = '';
        $date_where = '';

        if ($exam_id > 0) {

           /* $sql = "SELECT
                        count(exm_user_exams.id) AS total
                    FROM
                        exm_user_exams,
                        exm_users,
                        exm_user_teams,
                        exm_user_groups
                    WHERE exm_user_exams.user_id = exm_users.id
                    AND exm_users.user_team_id = exm_user_teams.id
                    AND exm_user_teams.group_id = exm_user_groups.id
                    AND (ue_status = 'complete' or ue_status = 'pending result')
                    AND exam_id = $exam_id
                    $group_where
                    $team_where
                    $date_where
                    ORDER BY team_name, user_login";
            $res = $this->db->query($sql);*/



        $this->db->select("count(TB.id) AS total");
        $this->db->from('exm_results TA');
        $this->db->join('exm_user_exams TB','TB.id = TA.user_exam_id', 'left');
        $this->db->join('exm_users TC','TC.id = TB.user_id', 'left');
        $this->db->join('exm_user_teams TD','TD.id = TC.user_team_id', 'left');
        $this->db->join('exm_user_groups TE','TE.id = TD.group_id', 'left');

        if ($group_id > 0){
               // $group_where = " AND group_id = ";
                $this->db->where('TD.group_id',$group_id);
            }
            if($team_id > 0){
                //$team_where = " AND user_team_id = $team_id ";
                $this->db->where('TC.user_team_id',$team_id);
            }
            if ($start_date != '' && $end_date != '') {
                $date_where = "('". $start_date ."' <= result_start_time AND DATEADD(DAY,1,'".$end_date."') > result_end_time)";
                $this->db->where($date_where);
            }
            $query = $this->db->get();
            //return $query->result();


            if ($query->num_rows() > 0) {
                return (int)$query->row()->total;
            } else {
                return 0;
            }

        } else {
            $this->error_message = 'Invalid exam id.';
            return false;
        }
    }

    public function get_non_attendee_list($exam_id = 0, $group_id = 0, $team_id = 0, $start_date = '', $end_date = '')
    {
        $exam_id = (int)$exam_id;
        $group_id = (int)$group_id;
        $team_id = (int)$team_id;
        $group_where = '';
        $team_where = '';
        $date_where = '';

        if ($exam_id > 0) {
 

            /*$sql = "SELECT
                        CONCAT(user_first_name, ' ', user_last_name) as 'user_name',
                        user_login,
                        team_name,
                        ue_start_date,
                        ue_end_date
                    FROM
                        exm_user_exams,
                        exm_users,
                        exm_user_teams,
                        exm_user_groups
                    WHERE exm_user_exams.user_id = exm_users.id
                    AND exm_users.user_team_id = exm_user_teams.id
                    AND exm_user_teams.group_id = exm_user_groups.id
                    AND (ue_status = 'open' || ue_status = 'paused')
                    AND exam_id = $exam_id
                    $group_where
                    $team_where
                    $date_where
                    ORDER BY team_name, user_login";
            $res = $this->db->query($sql);*/


        $this->db->select("CONCAT(TC.user_first_name, ' ', TC.user_last_name) as user_name,TC.user_login,TC.phone,TD.team_name,ue_start_date,ue_end_date");
        $this->db->from('exm_results TA');
        $this->db->join('exm_user_exams TB','TB.id = TA.user_exam_id', 'left');
        $this->db->join('exm_users TC','TC.id = TB.user_id', 'left');
        $this->db->join('exm_user_teams TD','TD.id = TC.user_team_id', 'left');
        $this->db->join('exm_user_groups TE','TE.id = TD.group_id', 'left');
        $this->db->order_by('TD.team_name', 'DESC');
        $this->db->order_by('TC.user_login', 'DESC');
        if ($group_id > 0){
               // $group_where = " AND group_id = ";
                $this->db->where('TD.group_id',$group_id);
            }
            if($team_id > 0){
                //$team_where = " AND user_team_id = $team_id ";
                $this->db->where('TC.user_team_id',$team_id);
            }
            if ($start_date != '' && $end_date != '') {
                $date_where = "('". $start_date ."' <= ue_start_date AND DATEADD(DAY,1,'".$end_date."') > ue_end_date)";
                $this->db->where($date_where);
            }
            $this->db->where("ue_status = 'open' or ue_status = 'paused'");
            $query = $this->db->get();

            return $query->result();



        } else {
            $this->error_message = 'Invalid exam id.';
            return false;
        }
    }

    public function get_non_attendee_list_count($exam_id = 0, $group_id = 0, $team_id = 0, $start_date = '', $end_date = '')
    {
        $exam_id = (int)$exam_id;
        $group_id = (int)$group_id;
        $team_id = (int)$team_id;
        $group_where = '';
        $team_where = '';
        $date_where = '';

        if ($exam_id > 0) {

            /*if ($group_id > 0) {
                $group_where = " AND group_id = $group_id ";
            }
            if ($team_id > 0) {
                $team_where = " AND user_team_id = $team_id ";
            }
            if ($start_date != '' && $end_date != '') {
                $date_where = " AND ('". $start_date ."' <= ue_start_date AND DATE_ADD('". $end_date ."', INTERVAL 1 DAY) > ue_end_date)";
            }

            $sql = "SELECT
                        count(exm_user_exams.id) AS total
                    FROM
                        exm_user_exams,
                        exm_users,
                        exm_user_teams,
                        exm_user_groups
                    WHERE exm_user_exams.user_id = exm_users.id
                    AND exm_users.user_team_id = exm_user_teams.id
                    AND exm_user_teams.group_id = exm_user_groups.id
                    AND (ue_status = 'open' || ue_status = 'paused')
                    AND exam_id = $exam_id
                    $group_where
                    $team_where
                    $date_where
                    ORDER BY team_name, user_login";
            $res = $this->db->query($sql);*/

        $this->db->select("count(TB.id) AS total");
        $this->db->from('exm_results TA');
        $this->db->join('exm_user_exams TB','TB.id = TA.user_exam_id', 'left');
        $this->db->join('exm_users TC','TC.id = TB.user_id', 'left');
        $this->db->join('exm_user_teams TD','TD.id = TC.user_team_id', 'left');
        $this->db->join('exm_user_groups TE','TE.id = TD.group_id', 'left');

        if ($group_id > 0){
                $this->db->where('TD.group_id',$group_id);
            }
            if($team_id > 0){
                $this->db->where('TC.user_team_id',$team_id);
            }
            if ($start_date != '' && $end_date != '') {
                $date_where = "('". $start_date ."' <= result_start_time AND DATEADD(DAY,1,'".$end_date."') > result_end_time)";
                $this->db->where($date_where);
            }
            $this->db->where("ue_status = 'open' or ue_status = 'paused'");
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return (int)$query->row()->total;
            } else {
                return 0;
            }
        } else {
            $this->error_message = 'Invalid exam id.';
            return false;
        }
    }

    public function get_result($result_id)
    {
        $result_id = (int)$result_id;
        if ($result_id > 0) {
            $this->db->where('id', $result_id);
            $this->db->limit(1);
            $query = $this->db->get($this->table_name);
            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function get_result_by_user_exam_id($user_exam_id)
    {
        $user_exam_id = (int)$user_exam_id;

        if ($user_exam_id > 0) {
            $this->db->where('user_exam_id', $user_exam_id);
            $this->db->limit(1);
            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function add_result($user_exam_id, $result)
    {
        $user_exam_id = (int)$user_exam_id;
        $old_result = $this->get_result_by_user_exam_id($user_exam_id);
        
        if ($old_result == false) {

            $this->db->insert($this->table_name, $result);

            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                return false;
            }

        } else {
            return true;
        }
    }

    public function update_result($user_exam_id, $result)
    {
        $user_exam_id = (int)$user_exam_id;
        $old_result = $this->get_result_by_user_exam_id($user_exam_id);

        if ($old_result == false) {

            return $this->add_result($user_exam_id, $result);

        } else {

            $this->db->where('user_exam_id', $user_exam_id);
            $this->db->limit(1);
            $this->db->update($this->table_name, $result);

            return true;
        }
    }

    public function update_result_exam_state($result_id, $exam_state)
    {
        $result_id = (int)$result_id;

        

        $data = array(
            'result_exam_state' => maybe_serialize($exam_state)
        );

        //print_r_pre($data);

        $this->db->where('id', $result_id);
        //$this->db->limit(1);
        $res =$this->db->update($this->table_name, $data);

        if ($res > 0) {
            return true;
        } else {
            $this->error_message = 'User result update unsuccessful. DB error.';
            return 0;
        }
    }

    public function update_result_exam_status($result_id)
    {
        $result_id = (int)$result_id;

        $data = array(
            'result_status' => 'published'
        );

        $this->db->where('id', $result_id);
        $this->db->limit(1);
        $res =$this->db->update($this->table_name, $data);

        if ($res > 0) {
            return true;
        } else {
            $this->error_message = 'User result status update unsuccessful. DB error.';
            return 0;
        }
    }

    public function delete_result_by_user_exam($user_exam_id)
    {
        $user_exam_id = (int)$user_exam_id;

        if ($user_exam_id > 0) {

            $this->db->where('user_exam_id', $user_exam_id);
            $this->db->limit(1);
            $this->db->delete($this->table_name);

            $res = (int)$this->db->affected_rows();

            if ($res > 0) {
                return true;
            } else {
                $this->error_message = 'User result delete unsuccessful. DB error.';
                return 0;
            }

        } else {
            $this->error_message = 'Invalid Id.';
            return 0;
        }
    }

    public function calculate_competency_level($score, $competency)
    {
        $competency_level = '';

        if ( ! $competency) {
            return $competency_level;
        }

        $score = (float)$score;

        for($i=0; $i<count($competency); $i++) {

            $label = $competency[$i]['label'];
            $lower = (int)$competency[$i]['lower'];
            $higher = (int)$competency[$i]['higher'];

            if ($score >= $lower && $score <= $higher) {
                $competency_level = $label;
                break;
            }
        }

        return $competency_level;
    }

    public function getCategoryResult(){
        $uid = $this->uri->segment(5);
        $EXid = $this->uri->segment(4);
        //echo "<pre>";
        $arrayTotalResCid=[];
       // if (count($uid)) { $this->db->where('user_id',$uid); }
        if (count($EXid)) { $this->db->where('user_exam_id',$EXid); }
        $query = $this->db->get("exm_results"); 
        $result = $query->result_array();  
        if(!empty($result[0]['result_exam_state']))
        {

            $Datas = maybe_unserialize($result[0]['result_exam_state']);
            $Qus = $Datas->exam_questions;
            //print_r_pre($result[0]['result_exam_state']); die();
            $dataArry = array();
            //$arrayTotalRes=[];
            
            /*for($i=2; $i<=5; $i++)
            {*/
                foreach ($Qus as $key => $value) {
                    /*$dataArry[$key]['AnsStatus'] = $value->question->ques_answer_type;
                    $dataArry[$key]['Result'] = $value->marks;
                    $dataArry[$key]['qid']= $value->question_id ; //$this->getCategory($value->question_id);
                    $arrayTotalRes['cid']= "2" ;*/
                    $dataCid=$this->getCategory($value->question_id);
                    $arrayTotalRes=['wrong'=>0, 'correct'=>0,'wrongCount'=>0,'correctCount'=>0];
                    if(array_key_exists($dataCid, $arrayTotalResCid))
                    {
                        $arrayTotalRes=$arrayTotalResCid[$dataCid];
                    }

                    if($value->question->ques_answer_type=="wrong")
                    {
                        $arrayTotalRes['wrong']+=$value->marks;
                        $arrayTotalRes['wrongCount']+=1;
                    }

                    if($value->question->ques_answer_type=="correct")
                    {
                        $arrayTotalRes['correct']+=$value->marks;
                        $arrayTotalRes['correctCount']+=1;
                    }
                    
                    $arrayTotalResCid[$dataCid]=$arrayTotalRes;
                    //$arrayTotalResCid[$key]=$dataCid;

                 //  print_r($value->question->ques_answer_type);
                }
            //}
           // $r = $this->getCategory($dataArry);
            // print_r_pre($arrayTotalResCid);
             //print_r_pre($dataArry);
            // print_r_pre($Datas->exam_questions);
            if ($arrayTotalResCid) {
                return $arrayTotalResCid;
            }else{
                return false;
            }
        }
        else
        {
            //print_r_pre($result[0]);
            
            return array();
        }
        
    }

    public function getSubjectResult(){
        
        $uid = $this->uri->segment(5);
        $EXid = $this->uri->segment(4);
        $arrayTotalResCid=[];
        
        if(count($EXid)) 
        { 
            $this->db->where('id',$EXid); 
           // $this->db->where('id',51); 
        }
        $query = $this->db->get("user_exams"); 
        $row = $query->row(); 

        //$parseData=unserialize($row->ue_state);

        //print_r_pre($parseData); 

        $setID=$row->set_id;
        $examID=$row->exam_id;
        $this->db->where('exam_id',$examID); 
        $this->db->where('set_id',$setID); 
        $querys = $this->db->get("exam_subject_wise_mark"); 
        $rows = $querys->result_array(); 

        return $rows;
        
        
        
    }

     public function getCategory($quID){
        $this->db->select('TB.cat_name as cat_name');
        //$this->db->grpup_by('TA.category_id');
        $this->db->from('exm_questions TA');
        $this->db->join('exm_categories TB', 'TB.id = TA.category_id' ,'left');
        $this->db->where_in('TA.id',$quID);
        $query = $this->db->get();
        $result = $query->result();
        return $result[0]->cat_name; 
     }
    
}

/* End of file result_model.php */
/* Location: ./application/models/result_model.php */