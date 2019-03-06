<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Email_model extends CI_Model
{
    private $table_emails = 'emails';
    public $error_message = '';

    function __construct()
    {
        parent::__construct();
    }

    public function add_email($email)
    {
        if (is_array($email)) {

            $this->db->insert($this->table_emails, $email);

            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                $this->error_message = 'Email add unsuccessful. DB error.';
                return false;
            }

        } else {
            $this->error_message = 'Invalid parameter.';
            return false;
        }
    }
}

/* End of file email_model.php */
/* Location: ./application/models/email_model.php */