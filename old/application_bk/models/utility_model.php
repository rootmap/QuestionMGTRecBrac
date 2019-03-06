<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Utility_model extends CI_Model
{
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    public function get_check_event()
    {
        $sql = 'SHOW VARIABLES LIKE "EVENT_SCHEDULER"';
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->first_row()->Value;
        } else {
            $this->error_message = 'Event is not supported.';
            return false;
        }
    }

    public function set_event_on()
    {
        $sql = 'SET GLOBAL event_scheduler = ON';
        $this->db->query($sql);
    }
}

/* End of file utility_model.php */
/* Location: ./application/models/utility_model.php */