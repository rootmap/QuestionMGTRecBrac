<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Logging Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Logging
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/general/errors.html
 */
class CI_Log {

	protected $_log_path;
	protected $_threshold	= 1;
	protected $_date_fmt	= 'Y-m-d H:i:s';
	protected $_enabled	= TRUE;
    protected $_levels	= array('ERROR' => '1', 'INFO' => '2', 'DEBUG' => '3', 'ALL' => '4');

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$config =& get_config();

		$this->_log_path = ($config['log_path'] != '') ? $config['log_path'] : APPPATH.'logs/';

		if ( ! is_dir($this->_log_path) OR ! is_really_writable($this->_log_path))
		{
			$this->_enabled = FALSE;
		}

		if (is_numeric($config['log_threshold']))
		{
			$this->_threshold = $config['log_threshold'];
		}

		if ($config['log_date_format'] != '')
		{
			$this->_date_fmt = $config['log_date_format'];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @param	string	the error level
	 * @param	string	the error message
	 * @param	bool	whether the error is a native PHP error
	 * @return	bool
	 */
	public function write_log($level = 'error', $msg, $php_error = FALSE, $action = '', $user_id = 0)
	{
		if ($this->_enabled === FALSE)
		{
			return FALSE;
		}

		$level = strtoupper($level);

		if ( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
		{
			return FALSE;
		}

        $this->write_log_in_db($level, $action, $msg, $user_id);

        $filepath = $this->_log_path.'log-'. strtolower($level) .'-'. date('Y-m-d') .'.php';
		$message  = '';

		if ( ! file_exists($filepath))
		{
			$message .= "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
		}

		if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
		{
			return FALSE;
		}

		$message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);

		@chmod($filepath, FILE_WRITE_MODE);
		return TRUE;
	}

    private function write_log_in_db($level = 'error', $action = '', $msg = '', $user_id = 0)
    {
        $CI =& get_instance();

        $user_id = (int)$user_id;
        $ip_address = '';
        $user_agent = '';

        if ($CI->session != null) {
            $ip_address = $CI->session->userdata('ip_address');
            $user_agent = $CI->session->userdata('user_agent');
            $logged_in_user = $CI->session->userdata('logged_in_user');

            if ($logged_in_user) {
                $user_id = (int)$logged_in_user->id;
            }
        }

        if ( is_object($CI->db) ) {
            $data = array(
                'log_type' => $level,
                'log_action' => $action,
                'user_id' => $user_id,
                'user_ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'log_message' => $msg,
            );

            $CI->db->insert('logs', $data);
        }
    }

}
// END Log Class

/* End of file Log.php */
/* Location: ./system/libraries/Log.php */