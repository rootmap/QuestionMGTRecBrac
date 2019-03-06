<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class User_model extends CI_Model
{
    private $table_name = 'users';
    private $table_user_activity = 'user_activity';
    private $table_name_team = 'user_teams';
    private $table_name_group = 'user_groups';
    private $group_privilage_table_name = 'group_privilage';
    private $table_session = 'ci_session';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Get number of users
     *
     * @return int
     */
    public function get_user_count()
    {
        return $this->db->count_all($this->table_name);
    }

    public function get_paged_users_activity($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_login_name') {
                    $this->db->where("(user_login LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

        $this->db->select($this->table_user_activity.'.*, dbo.get_user_full_name_by_id(exm_users.id) as user_login');
        $this->db->from($this->table_user_activity);
        $this->db->join($this->table_name, $this->table_user_activity .'.user_id = '. $this->table_name .'.id', 'LEFT OUTER');
        $this->db->order_by('id','DESC');
        //$this->db->order_by('user_login','DESC');
        $this->db->limit($limit, $offset);

        $query = $this->db->get();


        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_login_name') {
                        $this->db->where("(user_login LIKE '%". $value['value'] ."%')", '', false);
                    } else {
                        $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }

            
            $this->db->from($this->table_user_activity);
            $this->db->join($this->table_name, $this->table_user_activity .'.user_id = '. $this->table_name .'.id', 'LEFT OUTER');

            $result['count'] = $this->db->count_all_results();
        }

        return $result;
    } 
    // END OF is_data_exists


    public function get_paged_users_iptracking($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_login_name') {
                    $this->db->where("(user_login LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

        $this->db->select('exm_device_tracking.*, '. $this->table_name .'.user_login');
        $this->db->from('exm_device_tracking');
        $this->db->join($this->table_name, 'exm_device_tracking.user_id = '. $this->table_name .'.id', 'LEFT OUTER');
        $this->db->order_by('id','DESC');
        //$this->db->order_by('user_login','DESC');
        $this->db->limit($limit, $offset);

        $query = $this->db->get();


        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
           
        }

        return $result;
    } 
    // END OF is_data_exists

    /**
     * Get single user by ID
     *
     * @param $user_id
     * @return bool
     */
    public function get_user($user_id)
    {
        $user_id = (int)$user_id;

        if ($user_id > 0) {
            $this->db->where('id', $user_id);
    		$query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'User not found. Invalid id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    public function get_user_by_login($login_id)
    {
        if ($login_id != '') {
            $this->db->where('user_login', $login_id);
            $this->db->limit(1);
            $query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'User not found with the login id.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid login id.';
            return false;
        }
    }

    public function get_user_assigned_exam_row_id($user_id,$exam_id,$set_id)
    {
        if ($user_id != '') {
            $this->db->where('user_id', $user_id);
            $this->db->where('exam_id', $exam_id);
            $this->db->where('set_id', $set_id);
            $this->db->limit(1);
            $this->db->order_by('id','DESC');
            $query = $this->db->get('exm_user_exams');
            $num=$query->num_rows();
            if($num>0)
            {
                return $query->row();
            }
        } else {
            return false;
        }
    }

    public function get_user_password_reset_permission($login_id)
    {
        if ($login_id != '') {
            $this->db->where('user_login', $login_id);
            $this->db->where('is_password_reset',1);
            $this->db->limit(1);
    		$query = $this->db->get($this->table_name);
            return $query->num_rows();
        } else {
            return 0;
        }
    }

    public function get_user_by_email($email, $exclude_id = 0)
    {
        $exclude_id = (int)$exclude_id;

        if ($email != '') {
            $this->db->where('user_email', $email);
            $this->db->where(" id != $exclude_id ", null, false);
            $this->db->limit(1);
    		$query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                $this->error_message = 'User not found with the email.';
                return false;
            }
        } else {
            $this->error_message = 'Invalid email address.';
            return false;
        }
    }

    public function get_users()
    {
        $this->db->order_by('user_login','ASC');
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function get_active_users($type = '')
    {
        if ($type == 'User') {
            $this->db->where('user_type', 'User');
        }
        $this->db->where('user_is_active', '1');
        $this->db->order_by('user_first_name', 'ASC');
        $this->db->order_by('user_last_name', 'ASC');
        $this->db->order_by('user_login', 'ASC');
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function get_active_user_count_by_type($user_type, $exclude_id = 0)
    {
        //print_r_pre($user_type); die();
        $exclude_id = (int)$exclude_id;
        $validUserType=array(
            'User',
            'Administrator',
            'Recruitment Manager',
            'Subject Matter Experts',
            'Recruitment Assistant - Question',
            'System Auditor',
            'Recruitment Assistant – Result',
            'Examiner',
            'Super Administrator',
            'Any type'
        );
        
        if (in_array($user_type, $validUserType)) {

            if ($exclude_id > 0) {
                $this->db->where('id !=', $exclude_id);
            }
            $this->db->where('user_is_active', '1');
            $this->db->where('user_type', $user_type);
            $this->db->from($this->table_name);

            return $this->db->count_all_results();

        } else {
            $this->error_message = 'Invalid user type.';
            return 0;
        }
    }

    /**
     * Get paginated list of users
     *
     * @param $limit
     * @param int $offset
     * @param array $filter
     * @return array
     */
    public function get_paged_users($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_loginoremail') {
                    $this->db->where("(user_login LIKE '%". $value['value'] ."%' OR user_email LIKE '%". $value['value'] ."%')", '', false);
                } else {
                    $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                }
            }
        }

        $this->db->select($this->table_name.'.*, '. $this->table_name_team .'.team_name');
        $this->db->from($this->table_name);
        $this->db->join($this->table_name_team, $this->table_name .'.user_team_id = '. $this->table_name_team .'.id', 'LEFT OUTER');
        $this->db->order_by('user_type','ASC');
        $this->db->order_by('team_name','ASC');
        $this->db->order_by('user_login','ASC');
        $this->db->limit($limit, $offset);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_loginoremail') {
                        $this->db->where("(user_login LIKE '%". $value['value'] ."%' OR user_email LIKE '%". $value['value'] ."%')", '', false);
                    } else {
                        $this->db->where($filter[$key]['field'], $filter[$key]['value']);
                    }
                }
            }
            $this->db->from($this->table_name);
            $this->db->join($this->table_name_team, $this->table_name .'.user_team_id = '. $this->table_name_team .'.id', 'LEFT OUTER');

            $result['count'] = $this->db->count_all_results();
        }

        return $result;
    }



    public function get_all_users()
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;


        $this->db->select($this->table_name.'.*, '. $this->table_name_team .'.team_name');
        $this->db->from($this->table_name);
        $this->db->join($this->table_name_team, $this->table_name .'.user_team_id = '. $this->table_name_team .'.id', 'LEFT OUTER');
        $this->db->order_by('user_type','ASC');
        $this->db->order_by('team_name','ASC');
        $this->db->order_by('user_login','ASC');


        $query = $this->db->get();

        if ($query->num_rows() > 0) {

            $result['result'] = $query->result_array();

            $this->db->from($this->table_name);
            $this->db->join($this->table_name_team, $this->table_name .'.user_team_id = '. $this->table_name_team .'.id', 'LEFT OUTER');

            $result['count'] = $this->db->count_all_results();
        }

        return $result;
    }

    public function get_active_users_by_user_group($group_id = 0)
    {
        $group_id = (int)$group_id;

        if ($group_id > 0) {

            $sql = "SELECT
                        ". $this->db->dbprefix($this->table_name) .".*,
                        ". $this->db->dbprefix($this->table_name_team) .".team_name
                    FROM
                        ". $this->db->dbprefix($this->table_name) .",
                        ". $this->db->dbprefix($this->table_name_team) .",
                        ". $this->db->dbprefix($this->table_name_group) ."
                    WHERE exm_users.user_team_id = exm_user_teams.id
                        AND exm_user_teams.group_id = exm_user_groups.id
                        AND exm_user_teams.group_id = ?
                        AND exm_users.user_is_active = 1
                        AND exm_users.user_type = 'User'
                    ORDER BY
                      exm_user_teams.team_name,
                      exm_users.user_login";
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

    public function get_active_users_by_user_team($team_id = 0)
    {
        $team_id = (int)$team_id;

        if ($team_id > 0) {

            $this->db->where('user_team_id', $team_id);
            $this->db->where('user_type', 'User');
            $this->db->where('user_is_active', 1);
            $this->db->order_by('user_login','ASC');
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

    public function get_user_name()
    {
        $username = '';

        if ($this->session->userdata('logged_in_user')) {

            $logged_in_user = $this->session->userdata('logged_in_user');

            $user_login = $logged_in_user->user_login;
            $user_first_name = $logged_in_user->user_first_name;
            $user_last_name = $logged_in_user->user_last_name;

            $username = $user_first_name;
            if ($user_last_name != '') { $username .= ' '. $user_last_name; }
            if ($username == '') { $username = $user_login; }
        }

        return $username;
    }



    public function get_user_profile_path()
    {
        $propic_path = '';

        if ($this->session->userdata('logged_in_user')) {
            $logged_in_user = $this->session->userdata('logged_in_user');

            $propic_path = $logged_in_user->profile_image;

        }

        return $propic_path;
    }

    public function get_user_signature_path()
    {
        $propic_path = '';

        if ($this->session->userdata('logged_in_user')) {
            $logged_in_user = $this->session->userdata('logged_in_user');

            $propic_path = $logged_in_user->signature_image;

        }

        return $propic_path;
    }

    public function get_user_competency()
    {
        $CI =& get_instance();
        $CI->load->model('option_model');
        $competency = array();

        if ($this->session->userdata('logged_in_user')) {

            $user_skill = array();

            $logged_in_user = $this->session->userdata('logged_in_user');
            if ($logged_in_user) {
                $skill_level = $logged_in_user->user_competency;
            } else {
                $skill_level = '';
            }

            if ($skill_level == 'Front Office') {
                $user_skill = $CI->option_model->get_option('front_office_competency');
            } elseif ($skill_level == 'Back Office') {
                $user_skill = $CI->option_model->get_option('back_office_competency');
            }

            if ($user_skill) {
                $competency = $user_skill;
            }
        }

        return $competency;
    }

    function is_admin()
    {
        if ($this->session->userdata('logged_in_user')) {
            $logged_in_user = $this->session->userdata('logged_in_user');
            if ($logged_in_user->user_type == 'Administrator' || $logged_in_user->user_type == 'Super Administrator') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function is_super_admin()
    {
        if ($this->session->userdata('logged_in_user')) {
            $logged_in_user = $this->session->userdata('logged_in_user');
            if ($logged_in_user->user_type == 'Super Administrator') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function check_username_password($username, $password)
    {
        $sql = "SELECT * FROM ". $this->db->dbprefix($this->table_name) ."
                WHERE user_login = ?
                AND user_password = ?
                AND user_is_active = 1";

        $query = $this->db->query($sql, array($username, md5($password)));
        $row = $query->first_row();

        if ($query->num_rows() > 0) {
            return $row;
        } else {
            return false;
        }
    }


    /**
     * Insert a single user
     *
     * @param $user
     * @return bool
     */
    public function add_user($user)
    {

        //
        $CI = get_instance();
        $CI->load->helper('email');
        $CI->load->library('robi_email');
        $user_password_original = $user['user_password'];

        if (is_array($user)) {

            // Administrator can't create Super Administrator account
            $logged_in_user = $this->session->userdata('logged_in_user');
            if ($logged_in_user->user_type == 'Administrator' && $user['user_type'] == 'Super Administrator') {
                $this->error_message = 'Administrator can\'t create Super Administrator account.';
                return false;
            }

            // check if login id is unique
            if ($this->get_user_by_login($user['user_login'])) {
                $this->error_message = 'The login id is already exists. Please try a different Id.';
                return false;
            }

            // check password
            if ($user['user_password'] == '') {
                $this->error_message = 'Password can\'t be empty.';
                return false;
            } elseif ($user['user_password'] != $user['user_confirm_password']) {
                $this->error_message = 'Password does not match with Confirm Password.';
                return false;
            } elseif (strlen($user['user_password']) < 8) {
                $this->error_message = 'Password must have at least 8 (6eight) characters.';
                return false;
            } else {
                $password = $user['user_password'];

                preg_match_all ("/[A-Z]/", $password, $matches);
                $uppercase = count($matches[0]);
                
                preg_match_all ("/[a-z]/", $password, $matches);
                $lowercase = count($matches[0]);

                preg_match_all ("/\d/i", $password, $matches);
                $numbers = count($matches[0]);

                preg_match_all ("/[^A-z0-9]/", $password, $matches);
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
            }



            if (isset($user['user_confirm_password'])) {
                unset($user['user_confirm_password']);
            }
            $password_md5 = md5($user['user_password']);
            $user['user_password'] = $password_md5;
            $user['user_password_old'] = $password_md5;

            // user email validation
            if (isset($user['user_email']) && $user['user_email'] != '') {
                // check if email address is valid
                if ( ! valid_email($user['user_email'])) {
                    $this->error_message = 'Invalid email address. Please try a different one.';
                    return false;
                }
                
                // check if email address is unique
                if ($this->get_user_by_email($user['user_email'])) {
                    $this->error_message = 'Email is already exists. Please try a different one.';
                    return false;
                }
            }

            //var_dump('hello2');die;
            //print_r_pre($user);die;
            $this->db->insert($this->table_name, $user);

            if ($this->db->affected_rows() > 0) {

                // user create successfully; send notification mail
                $insert_id = $this->db->insert_id();
                
                $CI->robi_email->user_creation($user, $user_password_original);


                return $insert_id;

            } else {
                $this->error_message = 'User add unsuccessful. DB error.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    public function add_bulk_users($users)
    {
        $CI = get_instance();
        $CI->load->library('robi_email');

        $original_users = $users;
        $affected_rows = 0;

        if (is_array($users) && count($users) > 0) {

            // update password field in md5 hash
            foreach($users as $key => $value) {
                $users[$key]['user_password'] = md5($users[$key]['user_password']);
                $users[$key]['user_password_old'] = md5($users[$key]['user_password_old']);
            }

            $this->db->insert_batch($this->table_name, $users);
            $affected_rows = $this->db->affected_rows();

            // send user creation mail
            foreach($original_users as $key => $value) {
                $CI->robi_email->user_creation($original_users[$key], $original_users[$key]['user_password']);
            }
        }

        return $affected_rows;
    }
    
    /**
     * Update an user
     *
     * @param $user_id
     * @param $user
     * @param bool $is_admin
     * @return bool|int
     */
    public function update_user($user_id, $user, $is_admin = false)
    {
        $CI = get_instance();
        $CI->load->helper('email');
        $CI->load->library('robi_email');



        $logged_in_user = $this->session->userdata('logged_in_user');
        //print_r($logged_in_user); die();
        $user_id = (int)$user_id;
        $password_change = false;
        $actual_password = $user['user_password'];

        if ($user_id > 0 && $logged_in_user) {

            $old_user = $this->get_user($user_id);

            if ( ! $old_user ) {
                $this->error_message = 'User not found.';
                return false;
            }

            // Can't make own account inactive
            if ($logged_in_user->id == $user_id && (int)$user['user_is_active'] != 1) {
                $this->error_message = 'Own account can\'t be inactive.';
                return false;
            }

            // Can't make own account locked
            if ($logged_in_user->id == $user_id && (int)$user['user_is_lock'] == 1) {
                $this->error_message = 'Own account can\'t be locked.';
                return false;
            }

            // Administrator can't update Super Administrator account
            if ($logged_in_user->user_type == 'Administrator' && $old_user->user_type == 'Super Administrator') {
                $this->error_message = 'Administrator cant update Super Administrator account.';
                return false;
            }

            // There should be at least one Administrator and one Super Administrator account
            $no_of_users = $this->get_active_user_count_by_type($old_user->user_type, $old_user->id);
            /*if ($no_of_users <= 0 && $old_user->user_type != 'User') {
                $this->error_message = 'There should be at least one active '. $old_user->user_type .' account in the system.';
                return 0;
            }*/
            

            // check password
            if ($user['user_password'] != '') {

                if ($user['user_password'] != $user['user_confirm_password']) {
                    $this->error_message = 'Password does not match with Confirm Password.';
                    return false;
                } elseif (strlen($user['user_password']) < 8) {
                    $this->error_message = 'Password must have at least 8 (eight) characters.';
                    return false;
                } else {

                    $password = $user['user_password'];

                    preg_match_all ("/[A-Z]/", $password, $matches);
                    $uppercase = count($matches[0]);

                    preg_match_all ("/[a-z]/", $password, $matches);
                    $lowercase = count($matches[0]);

                    preg_match_all ("/\d/i", $password, $matches);
                    $numbers = count($matches[0]);

                    preg_match_all ("/[^A-z0-9]/", $password, $matches);
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

                    $password_change = true;
                }
                $user['user_password'] = md5($user['user_password']);

            } elseif (isset($user['user_password'])) {
                unset($user['user_password']);
            }
            
            if (isset($user['user_confirm_password'])) {
                unset($user['user_confirm_password']);
            }

            if (isset($user['user_email']) && $user['user_email'] != '') {
                // check if email address is valid
                if ( ! valid_email($user['user_email'])) {
                    $this->error_message = 'Invalid email address. Please try a different one.';
                    return false;
                }

                // check if email address is unique
                if ($this->get_user_by_email($user['user_email'], $user_id)) {
                    $this->error_message = 'Email is already exists. Please try a different one.';
                    return false;
                }
            }

            if ( ! $is_admin ) {
                $user['user_is_default_password'] = 1;
            }

            // update user
            $this->db->where('id', $user_id);
            $this->db->update($this->table_name, $user);

            // if password changes then send password change mail
            if ($password_change) {
                $user['user_login'] = $old_user->user_login;
                $CI->robi_email->password_change($user, $actual_password);
            }

            return true;

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }
    
    
    public function edit_bulk_users($users)
    {
        $CI = get_instance();
        $affected_rows = false;

        if (is_array($users) && count($users) > 0) {

            // update users
            foreach($users as $key => $value) {
                $data = array(
                    'user_team_id' => $users[$key]['user_team_id'],
                    'user_first_name' => $users[$key]['user_first_name'],
                    'user_last_name' => $users[$key]['user_last_name'],
                    'user_email' => $users[$key]['user_email'],
                    'user_competency' => $users[$key]['user_competency'],
                    'user_is_active' => $users[$key]['user_is_active']
                );
                                
                $this->db->where('user_login', $users[$key]['user_login']);
                $this->db->limit(1);
                $this->db->update($this->table_name, $data);

                if($this->db->affected_rows() > 0)
                {
                    $affected_rows = true;
                }
            }
        }

        return $affected_rows;
    }

    public function update_user_activity_time($user_id = 0)
    {
        $user_id = (int)$user_id;

        if ($user_id > 0) {

            $data = array(
                'user_last_activity_time' => date("Y-m-d H:i:s")
            );

            $this->db->where('id', $user_id);
            $this->db->limit(1);
            $this->db->update($this->table_name, $data);
           // echo  $this->db->last_query(); die();
           /* if ($this->db->affected_rows() > 0) {
                return true;
            } else {
                $this->error_message = "DB error or nothing to update.";
                return false;
            }*/
return true;
        } else {
            $this->error_message = "Invalid user id.";
            return false;
        }
    }

    public function increment_failed_login_count($user_login = '')
    {
        $CI = get_instance();
        $CI->load->model('option_model');

        $user_login = trim($user_login);

        if ($user_login != '') {

            $user = $this->get_user_by_login($user_login);

            if ($user && (int)$user->user_is_active == 1) {

                $failed_login_count = (int)$CI->option_model->get_option('failed_login_count');
				//var_dump($failed_login_count); die();
				
				
                $user_lock_field = '';
                if ($failed_login_count > 0 && $failed_login_count <= (int)$user->user_failed_login_count + 1) {
                    $user_lock_field = ' , user_is_lock = 1 ';
                    $this->error_message = "user_locked";
                }

                $sql = "UPDATE ". $this->db->dbprefix($this->table_name) ."
                        SET
                            user_failed_login_count = user_failed_login_count + 1,
                            user_last_failed_login_time = '". date("Y-m-d H:i:s") ."'
                            ". $user_lock_field ."
                        WHERE user_login = '". $user_login ."'";

                //echo $sql; die();        
                $this->db->query($sql);

                if ($this->db->affected_rows() > 0) {
                    return true;
                } else {
                    $this->error_message = "DB error or nothing to update.";
                    return false;
                }

            } else {
                $this->error_message = "User not found.";
                return false;
            }

        } else {
            $this->error_message = "Invalid user id.";
            return false;
        }
    }

    public function reset_failed_login_count($user_id = 0, $user_login = '')
    {
        $user_id = (int)$user_id;
        $user_login = trim($user_login);

        if ($user_id > 0) {

            // clearing all previous session created for the user
            // to restrict multiple login using same user id
            //$this->clear_user_sessions($user_login);

            $data = array(
                'user_failed_login_count' => 0,
                'user_last_successful_login_time' => date("Y-m-d H:i:s")
            );

            $this->db->where('id', $user_id);
            $this->db->limit(1);
            $this->db->update($this->table_name, $data);

            /*if ($this->db->affected_rows() > 0) {
                return true;
            } else {
                $this->error_message = "DB error or nothing to update.";
                return false;
            }*/
 return true;
        } else {
            $this->error_message = "Invalid user id.";
            return false;
        }
    }

    private function clear_user_sessions($user_login = '')
    {
        if ($user_login != '') {
            $this->db->like('user_data', '"'. $user_login .'";s:19:"user_password"');
            $this->db->delete($this->table_session);
        }
    }

    public function change_password($user_id, $old_password='', $new_password='', $confirm_password='', $is_frontend = false)
    {
        $user_id = (int)$user_id;
        $old_password = trim($old_password);
        $new_password = trim($new_password);
        $confirm_password = trim($confirm_password);

        if ($user_id > 0) {

            $user = $this->get_user($user_id);

            if ($user) {

                // checking current password
                if ($old_password == '') {
                    $this->error_message = 'Current Password is required.';
                    return false;
                } elseif ($new_password == '') {
                    $this->error_message = 'New Password is required.';
                    return false;
                }  elseif ($confirm_password == '') {
                    $this->error_message = 'Confirm Password is required.';
                    return false;
                } elseif (md5($old_password) != $user->user_password) {
                    $this->error_message = 'Current Password does not match.';
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

                    // user can't enter last two passwords
                    if ($is_frontend) {
                        if (md5($new_password) == $user->user_password || md5($new_password) == $user->user_password_old) {
                            $this->error_message = 'Last two passwords are not allowed. Please try a different password.';
                            return false;
                        }
                    }

                    if ($is_frontend) {
                        $data = array(
                            'user_password' => md5($new_password),
                            'user_password_old' => $user->user_password,
                            'user_is_default_password' => 0
                        );
                    } else {
                        $data = array(
                            'user_password' => md5($new_password),
                            'user_password_old' => $user->user_password
                        );
                    }

                    $this->db->where('id', $user_id);
                    $this->db->update($this->table_name, $data);

                    if($this->db->affected_rows() > 0) {
                        return true;
                    } else {
                        $this->error_message = 'DB error.';
                        return false;
                    }
                }

            } else {
                $this->error_message = 'Invalid user.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid user.';
            return false;
        }
    }

    public function reset_password($user_id, $reset_password)
    {
        $user_id = (int)$user_id;

        if ($user_id > 0) {

            $user = $this->get_user($user_id);
            if ($user) {

                $user_data = array(
                    'user_password' => $reset_password,
                    'user_password_old' => $user->user_password,
                    'user_is_default_password' => 1
                );

                $this->db->where('id', $user_id);
                $this->db->limit(1);
                $this->db->update($this->table_name, $user_data);

                if($this->db->affected_rows() > 0) {
                    return true;
                } else {
                    $this->error_message = 'DB error.';
                    return false;
                }

            } else {
                $this->error_message = 'Invalid user.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid user.';
            return false;
        }
    }

    /**
     * Delete a user by ID
     *
     * @param $user_id
     * @return int
     */
    public function delete_user($user_id)
    {
        $logged_in_user = $this->session->userdata('logged_in_user');
        $user_id = (int)$user_id;

        if ($user_id > 0 && $logged_in_user) {

            $user = $this->get_user($user_id);
            if ($user) {

                // can't delete own user account
                if ($logged_in_user->id == $user->id) {
                    $this->error_message = 'Can\'t delete own account.';
                    return 0;
                }

                // Administrator can't delete Super Administrator account
                if ($logged_in_user->user_type == 'Administrator' && $user->user_type == 'Super Administrator') {
                    $this->error_message = 'Administrator can\'t delete Super Administrator account.';
                    return 0;
                }

                // There should be at least one Administrator and one Super Administrator account
                $no_of_users = $this->get_active_user_count_by_type($user->user_type, $user->id);
                if ($no_of_users <= 0 && $user->user_type != 'User') {
                    $this->error_message = 'There should be at least one active '. $user->user_type .' account in the system.';
                    return 0;
                }

                $this->db->where('id', $user_id);
                $this->db->limit(1);
                $this->db->delete($this->table_name);

                $res = (int)$this->db->affected_rows();

                if ($res > 0) {

                    // TODO: After user delete remove all user assigned exam, results everything.
                    return $res;

                } else {
                    $this->error_message = 'User delete unsuccessful. DB error.';
                    return 0;
                }
            } else {
                $this->error_message = 'User not found.';
                return 0;
            }

        } else {
            $this->error_message = 'Invalid Id.';
            return 0;
        }
    }
	
	
	
	/**
     * Inactive a user by ID
     *
     * @param $user_id
     * @return int
     */
    public function inactive_user($user_id)
    {
        $logged_in_user = $this->session->userdata('logged_in_user');
        $user_id = (int)$user_id;

        if ($user_id > 0 && $logged_in_user) {

            $user = $this->get_user($user_id);
            if ($user) {

                // can't inactive own user account
                if ($logged_in_user->id == $user->id) {
                    $this->error_message = 'Can\'t inactive own account.';
                    return 0;
                }

                // Administrator can't inactive Super Administrator account
                if ($logged_in_user->user_type == 'Administrator' && $user->user_type == 'Super Administrator') {
                    $this->error_message = 'Administrator can\'t inactive Super Administrator account.';
                    return 0;
                }

                // There should be at least one Administrator and one Super Administrator account
                $no_of_users = $this->get_active_user_count_by_type($user->user_type, $user->id);
                if ($no_of_users <= 0 && $user->user_type != 'User') {
                    $this->error_message = 'There should be at least one active '. $user->user_type .' account in the system.';
                    return 0;
                }
				
                $data = array(
                    'user_is_active' => 0
                );

                $this->db->where('id', $user_id);
                $this->db->limit(1);
                $this->db->update($this->table_name, $data);

                $res = (int)$this->db->affected_rows();

                if ($res > 0) {
                    return true;
                } else {
                    $this->error_message = 'User inactive unsuccessful. DB error.';
                    return false;
                }
            } else {
                $this->error_message = 'User not found.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }
	
	/**
     * Active a user by ID
     *
     * @param $user_id
     * @return int
     */
    public function active_user($user_id)
    {
        $logged_in_user = $this->session->userdata('logged_in_user');
        $user_id = (int)$user_id;

        if ($user_id > 0 && $logged_in_user) {

            $user = $this->get_user($user_id);
            if ($user) {

                // can't active own user account
                if ($logged_in_user->id == $user->id) {
                    $this->error_message = 'Can\'t active own account.';
                    return 0;
                }

                // Administrator can't active Super Administrator account
                if ($logged_in_user->user_type == 'Administrator' && $user->user_type == 'Super Administrator') {
                    $this->error_message = 'Administrator can\'t active Super Administrator account.';
                    return 0;
                }

                // There should be at least one Administrator and one Super Administrator account
                $no_of_users = $this->get_active_user_count_by_type($user->user_type, $user->id);
                if ($no_of_users <= 0 && $user->user_type != 'User') {
                    $this->error_message = 'There should be at least one active '. $user->user_type .' account in the system.';
                    return 0;
                }
				
                $data = array(
                    'user_is_active' => 1
                );

                $this->db->where('id', $user_id);
                $this->db->limit(1);
                $this->db->update($this->table_name, $data);

                $res = (int)$this->db->affected_rows();

                if ($res > 0) {
                    return true;
                } else {
                    $this->error_message = 'User active unsuccessful. DB error.';
                    return false;
                }
            } else {
                $this->error_message = 'User not found.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }	
    
    
    
    /**
     * Delete bulk user by Login ID
     *
     * @param $user_id
     * @return int
     */
    
    public function delete_bulk_users($users)
    {
        $CI = get_instance();
        $affected_rows = false;
        $data = array();
  
        if (is_array($users) && count($users) > 0) {
            // update users
            foreach($users as $key => $value) {
                $data[] = $value['user_login']->user_login;
            }
            
            
            $this->db->where_in('user_login', $data);
            $this->db->delete($this->table_name);
            
            if($this->db->affected_rows() > 0){
                $affected_rows = true;
            }
        }
        return $affected_rows;                
    }
    
    
    public function get_user_list_for_paswword_change()
    {
        $group_ids = null;
        $user_group = null;
        $user_team = $this->session->userdata('logged_in_user')->user_team_id;
        
        $this->db->select($this->group_privilage_table_name.'.group_id_for_pass, '.$this->group_privilage_table_name.'.group_id');
        $this->db->from($this->group_privilage_table_name);
        $this->db->join($this->table_name_team, $this->group_privilage_table_name.'.group_id = '.$this->table_name_team.'.group_id');
        $this->db->join($this->table_name, $this->table_name_team.'.id = '.$this->table_name.'.user_team_id');
        $this->db->where('user_team_id', $user_team);
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $res = $query->result();
        }
        
        if(isset($res) && isset($res[0])){
            $group_ids = $res[0]->group_id_for_pass;
            $user_group = $res[0]->group_id;
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
            $group_ids = $user_group;
        }
        
        if($group_ids == 0 || $group_ids == $user_group){
            $group_ids = $group_ids;
        }elseif($group_ids && $group_ids != 0 && $group_ids != $user_group){
            $group_ids = $group_ids.','.$user_group;
        }
        
        if($group_ids == 0){
            $users = $this->get_active_users();
        }else{
            $sql = "select ".$this->db->dbprefix($this->table_name).".* from ".$this->db->dbprefix($this->table_name)." join ".$this->db->dbprefix($this->table_name_team)." on ".$this->db->dbprefix($this->table_name).".user_team_id = ".$this->db->dbprefix($this->table_name_team).".id where group_id in (".$group_ids.") and user_is_active = 1";
            $query = $this->db->query($sql);
			
            /*$this->db->select($this->table_name.'.*');
            $this->db->from($this->table_name);
            $this->db->join($this->table_name_team, $this->table_name.'.user_team_id = '.$this->table_name_team.'.id');
            $this->db->where_in('group_id', $group_ids);
            $this->db->where('user_is_active', '1');
            $this->db->order_by('user_first_name', 'ASC');
            $this->db->order_by('user_last_name', 'ASC');
            $this->db->order_by('user_login', 'ASC');
            $query = $this->db->get();*/

            if ($query->num_rows() > 0) {
                $users = $query->result();
            } 
        }
        
        if($users){
            return $users;
        }        
        else {
            return false;
        }
    }
    
    
    public function user_change_password($user_id, $new_password='', $confirm_password='', $is_frontend = false)
    {
        $user_id = $user_id;
        $new_password = trim($new_password);
        $confirm_password = trim($confirm_password);

        if ($user_id > 0) {

            $user = $this->get_user($user_id);

            if ($user) {

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

                    // user can't enter last two passwords
                    if ($is_frontend) {
                        if (md5($new_password) == $user->user_password || md5($new_password) == $user->user_password_old) {
                            $this->error_message = 'Last two passwords are not allowed. Please try a different password.';
                            return false;
                        }
                    }

                    if ($is_frontend) {
                        $data = array(
                            'user_password' => md5($new_password),
                            'user_password_old' => $user->user_password,
                            'user_is_default_password' => 0
                        );
                    } else {
                        $data = array(
                            'user_password' => md5($new_password),
                            'user_password_old' => $user->user_password
                        );
                    }
					
					$this->db->where('id', $user_id);
                    $this->db->update($this->table_name, $data);

                    if($this->db->affected_rows() > 0) {
                        return true;
                    } else {
                        $this->error_message = 'DB error.';
                        return false;
                    }
                }

            } else {
                $this->error_message = 'Invalid user.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid user.';
            return false;
        }
    }
   

}

/* End of file user_model.php */
/* Location: ./application/models/user_model.php */