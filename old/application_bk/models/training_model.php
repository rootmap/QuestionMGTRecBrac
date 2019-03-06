<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Training_model extends CI_Model
{
    private $table_name = 'training';
    private $table_training_file = 'training_files';
    private $table_training_users = 'training_users';
    private $table_users = 'users';
    private $table_user_groups = 'user_groups';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Get number of contents
     *
     * @return int
     */
    public function get_training_count()
    {
        return $this->db->count_all($this->table_name);
    }

    /**
     * Get paginated list of contents
     *
     * @param $limit
     * @param int $offset
     * @param array $filter
     * @return array
     */
    public function get_paged_training($limit, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        $this->update_expired_training();

        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_title') {
                    $this->db->where("(title LIKE '%". $value['value'] ."%')", '', false);
                }
            }
        }

        $this->db->from($this->table_name);
        $this->db->order_by('title','ASC');
        $this->db->limit($limit, $offset);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $result['result'] = $query->result();

            // record count
            if (is_array($filter) && count($filter) > 0) {
                foreach($filter as $key => $value) {
                    if ($key == 'filter_title') {
                        $this->db->where("(title LIKE '%". $value['value'] ."%')", '', false);
                    }
                }
            }
            $this->db->from($this->table_name);
            $result['count'] = $this->db->count_all_results();
        }

        return $result;
    }
    
 
    /**
     * Get single content by content ID
     * $is_backend = true, returns content of any status
     * $is_backend = false, returns content of 'published' status
     *
     * @param $content_id
     * @param bool $is_backend
     * @return bool
     */
    public function get_training($training_id, $is_backend = false)
    {
        $this->update_expired_training();

        $training_id = (int)$training_id;

        if ($training_id > 0) {

            if (!$is_backend) {
                $this->db->where("(status = 'open')", null, false);
            }
            $this->db->where('id', $training_id);
    		$query = $this->db->get($this->table_name);

            if ($query->num_rows() > 0) {

                $res = $query->row();
                $res->training_files = $this->get_training_files($training_id);
                
                return $res;

            } else {
                $this->error_message = 'Training not found. Invalid id.';
                return FALSE;
            }

        } else {
            $this->error_message = 'Invalid id.';
            return FALSE;
        }
    }
   
    public function get_open_trainings()
    {
        $this->update_expired_training();

        $this->db->where('status', 'open');
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {

            $res = $query->result();
            return $res;

        } else {
            $this->error_message = 'Training not found. Invalid id.';
            return FALSE;
        }
    }
    
    public function check_duplicate_training($title)
    {
        $this->db->where('title', $title);
        $query = $this->db->get($this->table_name);

        if ($query->num_rows() > 0) {

            $res = $query->result();
            return $res;

        } else {
            return FALSE;
        }
    }
     
    /**
     * Insert a single content
     *
     * @param $content
     * @return bool
     */
    public function add_training($training)
    {
        if (is_array($training)) {
            $training_files = array();
            if (count($training['training_files']) > 0) {
                $training_files = $training['training_files'];
            }
            unset($training['training_files']);

            $this->db->insert($this->table_name, $training);
            
            if ($this->db->affected_rows() > 0) {

                $training_id = $this->db->insert_id();

                $this->add_training_files($training_id, $training_files);

                return $training_id;

            } else {
                $this->error_message = 'Training add unsuccessful. DB error.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    /**
     * Update a content
     *
     * @param $content_id
     * @param $content
     * @return bool
     */
    public function update_training($training_id, $training)
    {
        $training_id = (int)$training_id;
        
        if ($training_id > 0) {

            $training_files = array();
            if (isset($training['training_files'])) {
                if (count($training['training_files']) > 0) {
                    $training_files = $training['training_files'];
                }
                unset($training['training_files']);
            }

            $this->db->where('id', $training_id);
            $this->db->update($this->table_name, $training);

            $this->add_training_files($training_id, $training_files);
            
            return true;

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    /**
     * Update a single field of a content. Will update both tables Bengali and English
     *
     * @param $content_id
     * @param $field_name
     * @param $field_value
     * @return bool
     */
    public function update_training_single_field($training_id, $field_name, $field_value)
    {
        $training_id = (int)$training_id;

        $allowed_fields = array(
            'status',
            'start_date',
            'end_date'
        );

        if ($training_id > 0) {

            if (in_array($field_name, $allowed_fields)) {
                
                $this->db->set($field_name, $field_value);
                $this->db->where('id', $training_id);
                $this->db->update($this->table_name);
                
                return true;

            } else  {
                $this->error_message = 'Invalid field.';
                return false;
            }

        } else  {
            $this->error_message = 'Invalid id.';
            return false;
        }
    }

    private function update_expired_training()
    {
        
        $sql = "update ". $this->db->dbprefix($this->table_name) ." set status = 'closed'
                where status = 'open'
                and end_date <= CURDATE() - INTERVAL 1 DAY
                and end_date != '0000-00-00 00:00:00'";
        $this->db->query($sql);
    }

    /**
     * Delete a content by ID
     *
     * @param $content_id
     * @return int
     */
    public function delete_training($training_id)
    {
        $training_id = (int)$training_id;
        
        if ($training_id > 0) {

            $this->db->where('id', $training_id);
            $this->db->delete($this->table_name);

            $res = (int)$this->db->affected_rows();

            if ($res > 0) {

                // delete content files
                $training_files = $this->get_training_files($training_id);
                for ($i=0; $i<count($training_files); $i++) {
                    $this->delete_training_file($training_files[$i]->id);
                }

                return $res;

            } else {
                $this->error_message = 'Training delete unsuccessful. DB error.';
                return 0;
            }

        } else {
            $this->error_message = 'Invalid id.';
            return 0;
        }
    }

    //-------------------------------------------------------------------------
    // training files
    //-------------------------------------------------------------------------

    public function get_training_file($training_file_id)
    {
        $training_file_id = (int)$training_file_id;
        
        if ($training_file_id > 0) {

            $this->db->where('id', $training_file_id);
            $this->db->limit(1);

            $query = $this->db->get($this->table_training_file);

            if ($query->num_rows() > 0) {

                return $query->row();

            } else {
                $this->error_message = "Training file not found.";
                return FALSE;
            }

        } else {
            $this->error_message = "Invalid training file id.";
            return FALSE;
        }

    }

    public function get_training_files($training_id)
    {
        $training_files = array();
        $training_id = (int)$training_id;
        
        if ($training_id > 0) {
            $this->db->order_by('file_name');
            $this->db->where('training_id', $training_id);
            $query = $this->db->get($this->table_training_file);

            if ($query->num_rows() > 0) {
                $training_files = $query->result();
            }
        }

        return $training_files;
    }

    public function add_training_files($training_id, $training_files = array())
    {
        $training_id = (int)$training_id;

        if ($training_id > 0 && is_array($training_files)) {

            for ($i=0; $i<count($training_files); $i++) {
                $data = array(
                    'training_id' => $training_id,
                    'file_name' => $training_files[$i]['name'],
                    'file_raw_name' => $training_files[$i]['raw_name'],
                    'file_size' => $training_files[$i]['size'],
                    'file_mime_type' => $training_files[$i]['mime_type']
                );
                $this->add_training_file($data);
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    public function add_training_file($training_files = array())
    {
        if (is_array($training_files)) {

            $this->db->insert($this->table_training_file, $training_files);

            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'Training add unsuccessful. DB error.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }

    public function delete_training_file($training_file_id)
    {
        $upload_path = str_replace('system/', 'uploads/', BASEPATH);
        $training_file_id = (int)$training_file_id;

        if ($training_file_id > 0) {

            $training_file = $this->get_training_file($training_file_id);

            if ($training_file) {

                $this->db->where('id', $training_file_id);
                $this->db->limit(1);
                $this->db->delete($this->table_training_file);

                if ($this->db->affected_rows() > 0) {
                    // delete file
                    $file_path = $upload_path . $training_file->file_raw_name;
                    @unlink($file_path);
                } else {
                    return FALSE;
                }

            } else {
                $this->error_message = "Training file not found.";
                return FALSE;
            }

        } else {
            $this->error_message = "Invalid training file id.";
            return FALSE;
        }
    }

    //-------------------------------------------------------------------------
    // training relates to user
    //-------------------------------------------------------------------------
    
    public function add_user_training_by_user_id($user_id = 0, $data = array())
    {
        $user_id = (int)$user_id;
        $training_id = (int)$data['training_id'];

        if ($user_id > 0) {
            $is_already_assigned = $this->is_user_training_already_assigned($user_id, $training_id);

            if ( ! $is_already_assigned ) {
                $data['user_id'] = $user_id;
                $user_training_id = $this->add_user_training($data);
                return true;
            } else {
                $this->error_message = 'Training already assigned to the user.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid user id.';
            return false;
        }
    }
    
    public function is_user_training_already_assigned($user_id = 0, $training_id = 0)
    {
        $user_id = (int)$user_id;
        $training_id = (int)$training_id;

        $sql = "SELECT * FROM ". $this->db->dbprefix($this->table_training_users) ."
                WHERE user_id = $user_id AND training_id = $training_id AND status = 'open'";
        $res = $this->db->query($sql);

        $result = $res->result();

        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }

    }
    
    public function add_user_training($data = array())
    {
        if (is_array($data)) {

            $this->db->insert($this->table_training_users, $data);

            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'User Training add unsuccessful. DB error.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }
    
    public function get_user_training_by_training_paged($training_id = 0, $start = '', $end = '', $limit = 50, $offset = 0, $filter = array())
    {
        $result = array();
        $result['result'] = false;
        $result['count'] = 0;

        $filter_where = '';
        if (is_array($filter) && count($filter) > 0) {
            foreach($filter as $key => $value) {
                if ($key == 'filter_login') {
                    $filter_where .= ' AND '. $filter[$key]['field'] ." LIKE '%". $filter[$key]['value'] ."%' ";
                } else {
                    $filter_where .= ' AND '. $filter[$key]['field'] ." = '". $filter[$key]['value'] ."' ";
                }
            }
        }


        $training_id = (int)$training_id;
        $limit = (int)$limit;
        $offset = (int)$offset;
        $date_where = '';

        if ($training_id > 0) {

            if ($start != '' && $end != '') {
                $date_where = " AND ('". $start ."' <= start_date AND '". $end ."' >= end_date)";
            }

            $sql = "SELECT
                       ". $this->db->dbprefix($this->table_training_users) .".id AS user_training_id, user_id, training_id,                           
                       start_date, end_date, status, group_id,
                       user_login, user_first_name, user_last_name, user_email, group_name
                    FROM
                       ". $this->db->dbprefix($this->table_training_users) .",
                       ". $this->db->dbprefix($this->table_users) .",
                       ". $this->db->dbprefix($this->table_user_groups) ."
                    WHERE ". $this->db->dbprefix($this->table_training_users) .".user_id = ". $this->db->dbprefix($this->table_users) .".id
                    AND ". $this->db->dbprefix($this->table_users) .".group_id = ". $this->db->dbprefix($this->table_user_groups) .".id
                    AND training_id = $training_id
                    $date_where
                    $filter_where
                    ORDER BY group_name, user_login
                    LIMIT $offset, $limit";
           
            $res = $this->db->query($sql);

            if ($res->num_rows() > 0) {

                $result['result'] = $res->result();

                // record count
                $sql = "SELECT
                           count(". $this->db->dbprefix($this->table_training_users) .".id) AS total
                        FROM
                           ". $this->db->dbprefix($this->table_training_users) .",
                           ". $this->db->dbprefix($this->table_users) .",
                           ". $this->db->dbprefix($this->table_user_groups) ."
                        WHERE ". $this->db->dbprefix($this->table_training_users) .".user_id = ". $this->db->dbprefix($this->table_users) .".id
                        AND ". $this->db->dbprefix($this->table_users) .".group_id = ". $this->db->dbprefix($this->table_user_groups) .".id
                        AND training_id = $training_id
                        $date_where
                        $filter_where";
                $res = $this->db->query($sql);
                if ($res->num_rows() > 0) {
                    $result['count'] = $res->row()->total;
                }
            }

            return $result;

        } else {
            $this->error_message = 'Invalid training id.';
            return false;
        }
    }
    
    public function active_user_training($user_training_id)
    {
        $user_training_id = (int)$user_training_id;

        if ($user_training_id > 0) {
            $this->db->where('id', $user_training_id);
            $this->db->limit(1);
            $data = array(
                'status' => 'open'
            );
            $this->db->update($this->table_training_users, $data);
            return true;
        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }

    public function inactive_user_training($user_training_id)
    {
        $user_training_id = (int)$user_training_id;

        if ($user_training_id > 0) {
            $this->db->where('id', $user_training_id);
            $this->db->limit(1);
            $data = array(
                'status' => 'inactive'
            );
            $this->db->update($this->table_training_users, $data);
            return true;
        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }

    public function delete_user_training($user_training_id)
    {
        $user_training_id = (int)$user_training_id;

        if ($user_training_id > 0) {

            $this->db->where('id', $user_training_id);
            $this->db->limit(1);
            
            $this->db->delete($this->table_training_users);

            return true;
            
        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }
    
    
    
    public function get_open_training_for_specific_user($user_id = '', $status = 'open')
    {
        $user_id = (int)$user_id;

        $this->db->select($this->table_training_users .'.*, '. $this->table_name .'.title, '. $this->table_name .'.description');
        $this->db->from($this->table_training_users);
        $this->db->join($this->table_name, $this->table_training_users.'.training_id = '.$this->table_name.'.id');
               
        if ($user_id > 0) {
            $this->db->where($this->table_training_users.'.user_id', $user_id);
        }
        $this->db->where($this->table_training_users.'.status', $status);
        
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $res = $query->result();
            return $res;

        } else {
            $this->error_message = 'Training not found. Invalid id.';
            return FALSE;
        }
    }
    
    public function get_training_details($user_training_id)
    {
        $this->update_expired_training();

        $user_training_id = (int)$user_training_id;

        if ($user_training_id > 0) {
            
            $this->db->select($this->table_training_users .'.*, '. $this->table_name .'.title, '. $this->table_name .'.description');
            $this->db->from($this->table_training_users);
            $this->db->join($this->table_name, $this->table_training_users.'.training_id = '.$this->table_name.'.id');
            $this->db->where($this->table_training_users.'.id', $user_training_id);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {

                $res = $query->row();
                $res->training_files = $this->get_training_files($res->training_id);
                
                return $res;

            } else {
                $this->error_message = 'Training not found. Invalid id.';
                return FALSE;
            }

        } else {
            $this->error_message = 'Invalid id.';
            return FALSE;
        }
    }
    
    public function complete_training($user_training_id)
    {
        $user_training_id = (int)$user_training_id;

        if ($user_training_id > 0) {
            $this->db->where('id', $user_training_id);
            $this->db->limit(1);
            $data = array(
                'status' => 'completed'
            );
            $this->db->update($this->table_training_users, $data);
            return true;
        } else {
            $this->error_message = 'Invalid Id.';
            return false;
        }
    }
    
}

/* End of file content_model.php */
/* Location: ./application/models/content_model.php */