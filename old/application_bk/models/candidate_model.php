<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Candidate_model extends CI_Model
{
    private $table_name = 'exm_candidates';
    private $table_name_team = 'candidate_teams';
    private $table_name_group = 'candidate_groups';
    private $group_privilage_table_name = 'group_privilage';
    private $table_session = 'ci_session';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Get number of candidates
     *
     * @return int
     */
    public function get_candidate_count()
    {
        return $this->db->count_all($this->table_name);
    }

    public function get_candidate($candidate_id)
    {
        $candidate_id = (int)$candidate_id;

        if ($candidate_id > 0) {
            $this->db->where('id', $candidate_id);
            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'candidate not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }


    public function get_candidate_by_email($email, $exclude_id = 0)
    {
        $exclude_id = (int)$exclude_id;

        if ($email != '') {
            $this->db->where('candidate_email', $email);
            $this->db->where(" id != $exclude_id ", null, false);
            $this->db->limit(1);
            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'candidate not found with the email.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid email address.';
            return false;
        }
    }

    public function get_candidates()
    {

        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function get_active_candidates($type = '')
    {

        $this->db->where('cand_is_active', '1');

        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }


    /**
     * Get paginated list of candidates
     *
     * @param $limit
     * @param int $offset
     * @param array $filter
     * @return array
     */
    public function get_paged_candidates($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        //var_dump($filter);die;

        if (is_array($filter) && count($filter) > 0) {


            foreach ($filter as $key => $value) {
                if ($key == 'filter_cand_name') {
                    $this->db->where("(cand_name LIKE '%" . $value['value'] . "%' )", '', false);
                } else if ($key == 'filter_cand_email') {
                    $this->db->where("(cand_email LIKE '%" . $value['value'] . "%' )", '', false);
                } else if ($key == 'filter_cand_address') {
                    $this->db->where("(cand_address LIKE '%" . $value['value'] . "%' )", '', false);
                } else if ($key == 'filter_phone') {
                    $this->db->where("(phone LIKE '%" . $value['value'] . "%' )", '', false);
                } else if ($key == 'filter_cand_is_active') {
                    $this->db->where('cand_is_active', $value['value']);
                }
            }
        }


        $this->db->select('*');
        $this->db->from($this->table_name);


        $this->db->limit($limit, $offset);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();


        }

        return $result;
    }


    public function get_all_candidates($filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        //var_dump($filter);die;

        if (is_array($filter) && count($filter) > 0) {


            foreach ($filter as $key => $value) {
                if ($key == 'filter_cand_name') {
                    $this->db->where("(cand_name LIKE '%" . $value['value'] . "%' )", '', false);
                } else if ($key == 'filter_cand_email') {
                    $this->db->where("(cand_email LIKE '%" . $value['value'] . "%' )", '', false);
                } else if ($key == 'filter_cand_address') {
                    $this->db->where("(cand_address LIKE '%" . $value['value'] . "%' )", '', false);
                } else if ($key == 'filter_phone') {
                    $this->db->where("(phone LIKE '%" . $value['value'] . "%' )", '', false);
                } else if ($key == 'filter_cand_is_active') {
                    $this->db->where('cand_is_active', $value['value']);
                }
            }
        }


        $this->db->select('*');
        $this->db->from($this->table_name);




        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $result['result'] = $query->result_array();
        }

        return $result;
    }

    public function get_active_candidates_by_candidate_group($group_id = 0)
    {
        $group_id = (int)$group_id;

        if ($group_id > 0) {

            $sql = "SELECT
                        " . $this->db->dbprefix($this->table_name) . ".*,
                        " . $this->db->dbprefix($this->table_name_team) . ".team_name
                    FROM
                        " . $this->db->dbprefix($this->table_name) . ",
                        " . $this->db->dbprefix($this->table_name_team) . ",
                        " . $this->db->dbprefix($this->table_name_group) . "
                    WHERE exm_candidates.candidate_team_id = exm_candidate_teams.id
                        AND exm_candidate_teams.group_id = exm_candidate_groups.id
                        AND exm_candidate_teams.group_id = ?
                        AND exm_candidates.candidate_is_active = 1
                        AND exm_candidates.candidate_type = 'candidate'
                    ORDER BY
                      exm_candidate_teams.team_name,
                      exm_candidates.candidate_login";
            $query = $this->db->query($sql, array($group_id));

            if ($query->num_rows() > 0) {

                return $query->result();

            } else {
                $this->error_message = 'Nothing found.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    public function get_active_candidates_by_candidate_team($team_id = 0)
    {
        $team_id = (int)$team_id;

        if ($team_id > 0) {

            $this->db->where('candidate_team_id', $team_id);
            $this->db->where('candidate_type', 'candidate');
            $this->db->where('candidate_is_active', 1);
            $this->db->order_by('candidate_login', 'ASC');
            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->result();
            } else {
                $this->error_message = 'Nothing found.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    public function get_candidate_name()
    {
        $candidatename = '';

        if ($this->session->candidatedata('logged_in_candidate')) {

            $logged_in_candidate = $this->session->candidatedata('logged_in_candidate');

            $candidate_login = $logged_in_candidate->candidate_login;
            $candidate_first_name = $logged_in_candidate->candidate_first_name;
            $candidate_last_name = $logged_in_candidate->candidate_last_name;

            $candidatename = $candidate_first_name;
            if ($candidate_last_name != '') {
                $candidatename .= ' ' . $candidate_last_name;
            }
            if ($candidatename == '') {
                $candidatename = $candidate_login;
            }
        }

        return $candidatename;
    }


    /**
     * Insert a single candidate
     *
     * @param $candidate
     * @return bool
     */
    public function add_candidate($candidate)
    {


        if (is_array($candidate)) {


            if (isset($candidate['cand_name']) && ($candidate['cand_name'] = '' || $candidate['cand_name'] == null)) {

                $this->error_message = 'Candidate Name cannot be empty.';
                return false;

            }


            // candidate email validation
            if (isset($candidate['cand_email']) && $candidate['candidate_email'] != '') {
                // check if email address is valid
                if (!valid_email($candidate['cand_email'])) {
                    $this->error_message = 'Invalid email address. Please try a different one.';
                    return false;
                }

                // check if email address is unique

            }

            $this->db->insert($this->table_name, $candidate);

            if ($this->db->affected_rows() > 0) {

                // candidate create successfully; send notification mail
                $insert_id = $this->db->insert_id();


                return $insert_id;

            } else {
                $this->error_message = 'candidate add unsuccessful. DB error.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }


    public function add_bulk_candidates($candidates)
    {
        //$CI = get_instance();
       // $CI->load->library('robi_email');

        //$original_candidates = $candidates;
        $affected_rows = 0;

        if (is_array($candidates) && count($candidates) > 0) {

            // update password field in md5 hash

            //print_r_pre($candidates);die;


            $this->db->insert_batch($this->table_name, $candidates);
            //var_dump($hello);die;
            $affected_rows = $this->db->affected_rows();

        }

        return $affected_rows;
    }

    /**
     * Update an candidate
     *
     * @param $candidate_id
     * @param $candidate
     * @param bool $is_admin
     * @return bool|int
     */
    public function update_candidate($candidate_id, $candidate, $is_admin = false)
    {


        $candidate_id = (int)$candidate_id;


        if ($candidate_id > 0 ) {

            //$old_candidate = $this->get_candidate($candidate_id);




            //var_dump($candidate);die;








            if (isset($candidate['candidate_email']) && $candidate['candidate_email'] != '') {
                // check if email address is valid
                if ( ! valid_email($candidate['candidate_email'])) {
                    $this->error_message = 'Invalid email address. Please try a different one.';
                    return false;
                }


            }


            // update candidate
            $this->db->where('id', $candidate_id);
            $this->db->update($this->table_name, $candidate);


            return true;

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }










    /**
     * Delete a candidate by ID
     *
     * @param $candidate_id
     * @return int
     */
    public function delete_candidate($candidate_id)
    {
        //$logged_in_candidate = $this->session->candidatedata('logged_in_candidate');
        $candidate_id = (int)$candidate_id;

        if ($candidate_id > 0 ) {

            $candidate = $this->get_candidate($candidate_id);
            if ($candidate) {




                // There should be at least one Administrator and one Super Administrator account


                $this->db->where('id', $candidate_id);
                $this->db->limit(1);




                if ( $this->db->delete($this->table_name)) {

                    // TODO: After candidate delete remove all candidate assigned exam, results everything.
                    return 1;

                } else {
                    $this->error_message = 'candidate delete unsuccessful. DB error.';
                    return 0;
                }
            } else {
                $this->error_message = 'candidate not found.';
                return 0;
            }

        } else {
            $this->error_message = 'Invalid Id.';
            return 0;
        }
    }



    /**
     * Inactive a candidate by ID
     *
     * @param $candidate_id
     * @return int
     */
    public function inactive_candidate($candidate_id)
    {
        $logged_in_candidate = $this->session->candidatedata('logged_in_candidate');
        $candidate_id = (int)$candidate_id;

        if ($candidate_id > 0 && $logged_in_candidate) {

            $candidate = $this->get_candidate($candidate_id);
            if ($candidate) {

                // can't inactive own candidate account
                if ($logged_in_candidate->id == $candidate->id) {
                    $this->error_message = 'Can\'t inactive own account.';
                    return 0;
                }

                // Administrator can't inactive Super Administrator account
                if ($logged_in_candidate->candidate_type == 'Administrator' && $candidate->candidate_type == 'Super Administrator') {
                    $this->error_message = 'Administrator can\'t inactive Super Administrator account.';
                    return 0;
                }

                // There should be at least one Administrator and one Super Administrator account
                $no_of_candidates = $this->get_active_candidate_count_by_type($candidate->candidate_type, $candidate->id);
                if ($no_of_candidates <= 0 && $candidate->candidate_type != 'candidate') {
                    $this->error_message = 'There should be at least one active '. $candidate->candidate_type .' account in the system.';
                    return 0;
                }

                $data = array(
                    'candidate_is_active' => 0
                );

                $this->db->where('id', $candidate_id);
                $this->db->limit(1);
                $this->db->update($this->table_name, $data);

                $res = (int)$this->db->affected_rows();

                if ($res > 0) {
                    return true;
                } else {
                    $this->error_message = 'candidate inactive unsuccessful. DB error.';
                    return false;
                }
            } else {
                $this->error_message = 'candidate not found.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }

    /**
     * Active a candidate by ID
     *
     * @param $candidate_id
     * @return int
     */
    public function active_candidate($candidate_id)
    {
        $logged_in_candidate = $this->session->candidatedata('logged_in_candidate');
        $candidate_id = (int)$candidate_id;

        if ($candidate_id > 0 && $logged_in_candidate) {

            $candidate = $this->get_candidate($candidate_id);
            if ($candidate) {

                // can't active own candidate account
                if ($logged_in_candidate->id == $candidate->id) {
                    $this->error_message = 'Can\'t active own account.';
                    return 0;
                }

                // Administrator can't active Super Administrator account
                if ($logged_in_candidate->candidate_type == 'Administrator' && $candidate->candidate_type == 'Super Administrator') {
                    $this->error_message = 'Administrator can\'t active Super Administrator account.';
                    return 0;
                }

                // There should be at least one Administrator and one Super Administrator account
                $no_of_candidates = $this->get_active_candidate_count_by_type($candidate->candidate_type, $candidate->id);
                if ($no_of_candidates <= 0 && $candidate->candidate_type != 'candidate') {
                    $this->error_message = 'There should be at least one active '. $candidate->candidate_type .' account in the system.';
                    return 0;
                }

                $data = array(
                    'candidate_is_active' => 1
                );

                $this->db->where('id', $candidate_id);
                $this->db->limit(1);
                $this->db->update($this->table_name, $data);

                $res = (int)$this->db->affected_rows();

                if ($res > 0) {
                    return true;
                } else {
                    $this->error_message = 'candidate active unsuccessful. DB error.';
                    return false;
                }
            } else {
                $this->error_message = 'candidate not found.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }



    /**
     * Delete bulk candidate by Login ID
     *
     * @param $candidate_id
     * @return int
     */

    public function delete_bulk_candidates($candidates)
    {
        $CI = get_instance();
        $affected_rows = false;
        $data = array();

        if (is_array($candidates) && count($candidates) > 0) {
            // update candidates
            foreach($candidates as $key => $value) {
                $data[] = $value['candidate_login']->candidate_login;
            }


            $this->db->where_in('candidate_login', $data);
            $this->db->delete($this->table_name);

            if($this->db->affected_rows() > 0){
                $affected_rows = true;
            }
        }
        return $affected_rows;
    }


    public function get_candidate_list_for_paswword_change()
    {
        $group_ids = null;
        $candidate_group = null;
        $candidate_team = $this->session->candidatedata('logged_in_candidate')->candidate_team_id;

        $this->db->select($this->group_privilage_table_name.'.group_id_for_pass, '.$this->group_privilage_table_name.'.group_id');
        $this->db->from($this->group_privilage_table_name);
        $this->db->join($this->table_name_team, $this->group_privilage_table_name.'.group_id = '.$this->table_name_team.'.group_id');
        $this->db->join($this->table_name, $this->table_name_team.'.id = '.$this->table_name.'.candidate_team_id');
        $this->db->where('candidate_team_id', $candidate_team);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $res = $query->result();
        }

        if($res && isset($res[0])){
            $group_ids = $res[0]->group_id_for_pass;
            $candidate_group = $res[0]->group_id;
        }

        if($group_ids == 0){
            $group_ids = $group_ids;
        }elseif($group_ids){
            $group_array = explode(",", $group_ids);
            foreach($group_array as $k=>$v){
                if($v == 0){
                    $group_ids = 0;
                    break;
                }
            }
        }else{
            $group_ids = $candidate_group;
        }

        if($group_ids == 0 || $group_ids == $candidate_group){
            $group_ids = $group_ids;
        }elseif($group_ids && $group_ids != 0 && $group_ids != $candidate_group){
            $group_ids = $group_ids.','.$candidate_group;
        }

        if($group_ids == 0){
            $candidates = $this->get_active_candidates();
        }else{
            $sql = "select ".$this->db->dbprefix($this->table_name).".* from ".$this->db->dbprefix($this->table_name)." join ".$this->db->dbprefix($this->table_name_team)." on ".$this->db->dbprefix($this->table_name).".candidate_team_id = ".$this->db->dbprefix($this->table_name_team).".id where group_id in (".$group_ids.") and candidate_is_active = 1";
            $query = $this->db->query($sql);

            /*$this->db->select($this->table_name.'.*');
            $this->db->from($this->table_name);
            $this->db->join($this->table_name_team, $this->table_name.'.candidate_team_id = '.$this->table_name_team.'.id');
            $this->db->where_in('group_id', $group_ids);
            $this->db->where('candidate_is_active', '1');
            $this->db->order_by('candidate_first_name', 'ASC');
            $this->db->order_by('candidate_last_name', 'ASC');
            $this->db->order_by('candidate_login', 'ASC');
            $query = $this->db->get();*/

            if ($query->num_rows() > 0) {
                $candidates = $query->result();
            }
        }

        if($candidates){
            return $candidates;
        }
        else {
            return false;
        }
    }


    public function candidate_change_password($candidate_id, $new_password='', $confirm_password='', $is_frontend = false)
    {
        $candidate_id = $candidate_id;
        $new_password = trim($new_password);
        $confirm_password = trim($confirm_password);

        if ($candidate_id > 0) {

            $candidate = $this->get_candidate($candidate_id);

            if ($candidate) {

                if ($new_password == '') {
                    $this->error_message = 'New Password is required.';
                    return false;
                }  elseif ($confirm_password == '') {
                    $this->error_message = 'Confirm Password is required.';
                    return false;
                } elseif ($new_password != $confirm_password) {
                    $this->error_message = 'New Password and Confirm Password does not match.';
                    return false;
                } elseif (strlen($new_password) < 8) {
                    $this->error_message = 'Password must have at least 8 (eight) characters.';
                    return false;
                } else {

                    preg_match_all ("/[A-Z]/", $new_password, $matches);
                    $uppercase = count($matches[0]);

                    preg_match_all ("/[a-z]/", $new_password, $matches);
                    $lowercase = count($matches[0]);

                    preg_match_all ("/\d/i", $new_password, $matches);
                    $numbers = count($matches[0]);

                    preg_match_all ("/[^A-z0-9]/", $new_password, $matches);
                    $special = count($matches[0]);

                    if ($uppercase <= 0) {
                        $this->error_message = 'Password should contain at least one Uppercase letter.';
                        return false;
                    } elseif ($lowercase <= 0) {
                        $this->error_message = 'Password should contain at least one Lowercase letter.';
                        return false;
                    } elseif ($numbers <= 0) {
                        $this->error_message = 'Password should contain at least one Number.';
                        return false;
                    } elseif ($special <= 0) {
                        $this->error_message = 'Password should contain at least one Special character.';
                        return false;
                    }

                    // candidate can't enter last two passwords
                    if ($is_frontend) {
                        if (md5($new_password) == $candidate->candidate_password || md5($new_password) == $candidate->candidate_password_old) {
                            $this->error_message = 'Last two passwords are not allowed. Please try a different password.';
                            return false;
                        }
                    }

                    if ($is_frontend) {
                        $data = array(
                            'candidate_password' => md5($new_password),
                            'candidate_password_old' => $candidate->candidate_password,
                            'candidate_is_default_password' => 0
                        );
                    } else {
                        $data = array(
                            'candidate_password' => md5($new_password),
                            'candidate_password_old' => $candidate->candidate_password
                        );
                    }

                    $this->db->where('id', $candidate_id);
                    $this->db->update($this->table_name, $data);

                    if($this->db->affected_rows() > 0) {
                        return true;
                    } else {
                        $this->error_message = 'DB error.';
                        return false;
                    }
                }

            } else {
                $this->error_message = 'Invalid candidate.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid candidate.';
            return false;
        }
    }


}

/* End of file candidate_model.php */
/* Location: ./application/models/candidate_model.php */