<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 *  Author     : Arif Uddin
 *  Email      : mail2rupok@gmail.com
 */

class Robi_email
{
    private $CI;

    function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('text');
        $this->CI->load->library('ci_mpdf');
        $this->CI->load->model('option_model');
        $this->CI->load->model('user_model');
        $this->CI->load->model('email_model');
		
    }

    public function user_creation($user, $password)
    {
        $password = trim($password);

        $user_email = '';
        if (is_array($user)) {
            $user_email = $user['user_email'];
        } elseif (is_object($user)) {
            $user_email = $user->user_email;
        }

        $site_name = $this->CI->option_model->get_option('site_name');
        $email['site_name'] = $site_name;
        $email['image_url'] = site_url('assets/backend/images/mail');
        $email['user'] = $user;
        $email['password'] = $password;

        // send email only if the user has an email address
        if ($user && $user_email != '') {

            $to = $user_email;
            $body = $this->CI->load->view('email/user_creation_normal', $email, true);

            $from_name = $this->CI->option_model->get_option('email_from_name');
            $smtp_host = $this->CI->option_model->get_option('email_smtp_host');
            $smtp_port = $this->CI->option_model->get_option('email_smtp_port');
            $smtp_user = $this->CI->option_model->get_option('email_smtp_user');
            $smtp_pass = $this->CI->option_model->get_option('email_smtp_pass');

            $config = Array(
                'protocol'  => 'smtp',
                'smtp_host' => $smtp_host,
                'smtp_port' => $smtp_port,
                'smtp_user' => $smtp_user,
                'smtp_pass' => $smtp_pass,
                'mailtype'  => 'html',
                'charset'   => 'utf-8',
                'wordwrap'  => false,
                'validate'  => true
            );
            $this->CI->load->library('email', $config);

            $this->CI->email->initialize();
            $this->CI->email->set_newline("\r\n");

            $this->CI->email->from($smtp_user, $from_name);
            $this->CI->email->to($to);
            $this->CI->email->reply_to($smtp_user, $from_name);

            $this->CI->email->subject('Your account has been created at '. $site_name);
            $this->CI->email->message($body);

            // prepare email data
            $email_data = array();
            $email_data['email_type'] = 'user creation';
            $email_data['email_to'] = $to;
            $email_data['email_body'] = $body;

            if ( ! $this->CI->email->send()) {
                $email_data['email_error'] = $this->CI->email->print_debugger();
                $email_data['email_status'] = 0;
                $this->CI->email_model->add_email($email_data);
            } else {
                $email_data['email_status'] = 1;
                $this->CI->email_model->add_email($email_data);
            }
        }
    }

    public function forgot_password($user, $new_password)
    {
        $new_password = trim($new_password);

        $user_email = '';
        if (is_array($user)) {
            $user_email = $user['user_email'];
        } elseif (is_object($user)) {
            $user_email = $user->user_email;
        }

        $site_name = $this->CI->option_model->get_option('site_name');
        $email['site_name'] = $site_name;
        $email['image_url'] = site_url('assets/backend/images/mail');
        $email['user'] = $user;
        $email['site_url'] = site_url();
        $email['new_password'] = $new_password;

        if ($user && $user_email != '') {

            $to = $user_email;
            $body = $this->CI->load->view('email/forgot_password', $email, true);

            $from_name = $this->CI->option_model->get_option('email_from_name');
            $smtp_host = $this->CI->option_model->get_option('email_smtp_host');
            $smtp_port = $this->CI->option_model->get_option('email_smtp_port');
            $smtp_user = $this->CI->option_model->get_option('email_smtp_user');
            $smtp_pass = $this->CI->option_model->get_option('email_smtp_pass');

            $config = Array(
                'protocol'  => 'smtp',
                'smtp_host' => $smtp_host,
                'smtp_port' => $smtp_port,
                'smtp_user' => $smtp_user,
                'smtp_pass' => $smtp_pass,
                'mailtype'  => 'html',
                'charset'   => 'utf-8',
                'wordwrap'  => false,
                'validate'  => true
            );
            $this->CI->load->library('email', $config);

            $this->CI->email->initialize();
            $this->CI->email->set_newline("\r\n");

            $this->CI->email->from($smtp_user, $from_name);
            $this->CI->email->to($to);
            $this->CI->email->reply_to($smtp_user, $from_name);

            $this->CI->email->subject('You requested a new password for '. $site_name .' portal');
            $this->CI->email->message($body);

            // prepare email data
            $email_data = array();
            $email_data['email_type'] = 'password reset';
            $email_data['email_to'] = $to;
            $email_data['email_body'] = $body;

            if ( ! $this->CI->email->send()) {
                $email_data['email_error'] = $this->CI->email->print_debugger();
                $email_data['email_status'] = 0;
                $this->CI->email_model->add_email($email_data);
                return false;
            } else {
                $email_data['email_status'] = 1;
                $this->CI->email_model->add_email($email_data);
                return true;
            }
        }
    }

    public function password_change($user, $password)
    {
        $password = trim($password);

        $user_email = '';
        if (is_array($user)) {
            $user_email = $user['user_email'];
        } elseif (is_object($user)) {
            $user_email = $user->user_email;
        }

        $site_name = $this->CI->option_model->get_option('site_name');
        $email['site_name'] = $site_name;
        $email['image_url'] = site_url('assets/backend/images/mail');
        $email['user'] = $user;
        $email['password'] = $password;

        // send email only if the user has an email address
        if ($user && $user_email != '') {

            $to = $user_email;
            $body = $this->CI->load->view('email/password_change_normal', $email, true);

            $from_name = $this->CI->option_model->get_option('email_from_name');
            $smtp_host = $this->CI->option_model->get_option('email_smtp_host');
            $smtp_port = $this->CI->option_model->get_option('email_smtp_port');
            $smtp_user = $this->CI->option_model->get_option('email_smtp_user');
            $smtp_pass = $this->CI->option_model->get_option('email_smtp_pass');

            $config = Array(
                'protocol'  => 'smtp',
                'smtp_host' => $smtp_host,
                'smtp_port' => $smtp_port,
                'smtp_user' => $smtp_user,
                'smtp_pass' => $smtp_pass,
                'mailtype'  => 'html',
                'charset'   => 'utf-8',
                'wordwrap'  => false,
                'validate'  => true
            );
            $this->CI->load->library('email', $config);

            $this->CI->email->initialize();
            $this->CI->email->set_newline("\r\n");

            $this->CI->email->from($smtp_user, $from_name);
            $this->CI->email->to($to);
            $this->CI->email->reply_to($smtp_user, $from_name);

            $this->CI->email->subject('Your password has changed at '. $site_name);
            $this->CI->email->message($body);

            // prepare email data
            $email_data = array();
            $email_data['email_type'] = 'password change';
            $email_data['email_to'] = $to;
            $email_data['email_body'] = $body;

            if ( ! $this->CI->email->send()) {
                $email_data['email_error'] = $this->CI->email->print_debugger();
                $email_data['email_status'] = 0;
                $this->CI->email_model->add_email($email_data);
            } else {
                $email_data['email_status'] = 1;
                $this->CI->email_model->add_email($email_data);
            }
        }
    }

    public function exam_notification($user_id, $user_exam, $exam)
    {
        $user = $this->CI->user_model->get_user($user_id);

        if ($user && $user->user_email != '') {

            $site_name = $this->CI->option_model->get_option('site_name');
            $email['site_name'] = $site_name;
            $email['site_url'] = site_url();
            $email['image_url'] = site_url('assets/backend/images/mail');
            $email['user_exam'] = $user_exam;
            $email['exam'] = $exam;
            $email['user'] = $user;

            $to = $user->user_email;
            $body = $this->CI->load->view('email/exam_notification_normal', $email, true);

            $from_name = $this->CI->option_model->get_option('email_from_name');
            $smtp_host = $this->CI->option_model->get_option('email_smtp_host');
            $smtp_port = $this->CI->option_model->get_option('email_smtp_port');
            $smtp_user = $this->CI->option_model->get_option('email_smtp_user');
            $smtp_pass = $this->CI->option_model->get_option('email_smtp_pass');

            $config = Array(
                'protocol'  => 'smtp',
                'smtp_host' => $smtp_host,
                'smtp_port' => $smtp_port,
                'smtp_user' => $smtp_user,
                'smtp_pass' => $smtp_pass,
                'mailtype'  => 'html',
                'charset'   => 'utf-8',
                'wordwrap'  => true,
                'validate'  => true
            );
            $this->CI->load->library('email', $config);

            $this->CI->email->initialize();
            $this->CI->email->set_newline("\r\n");

            $this->CI->email->from($smtp_user, $from_name);
            $this->CI->email->to($to);
            $this->CI->email->reply_to($smtp_user, $from_name);

            $this->CI->email->subject('Exam notification for '. $exam->exam_title);
            $this->CI->email->message($body);

            // prepare email data
            $email_data = array();
            $email_data['email_type'] = 'exam notification';
            $email_data['email_to'] = $to;
            $email_data['email_body'] = $body;

            if ( ! $this->CI->email->send()) {
                $email_data['email_error'] = $this->CI->email->print_debugger();
                $email_data['email_status'] = 0;
                $this->CI->email_model->add_email($email_data);
            } else {
                $email_data['email_status'] = 1;
                $this->CI->email_model->add_email($email_data);
            }
        }
    }

    public function mcq_result($result, $user)
    {
        $file_path = ABSPATH .'uploads/';
        $file_name = '';
        
        $exam = maybe_unserialize($result['result_exam_state']);
        $is_mail_allowed = (int)$exam->exam_allow_result_mail;

        if ($is_mail_allowed == 1) {

            $site_name = $this->CI->option_model->get_option('site_name');
            $email['site_name'] = $site_name;
            $email['image_url'] = site_url('assets/backend/images/mail');
            $email['result'] = $result;
            $email['exam'] = $exam;
            $email['user'] = $user;

            // send email only if the user has an email address
            if ($user && $user->user_email != '') {

                // generate pdf file
                $file_name = 'Exam_Result_'. (int)$result['user_exam_id']. '.pdf';
                $pdf_content = $this->CI->load->view('email/mcq_result_pdf', $email, true);

                // mPDF
                $this->CI->ci_mpdf->WriteHTML($pdf_content);
                $this->CI->ci_mpdf->Output(ABSPATH .'uploads/'. $file_name, 'F');

                $to = $user->user_email;
                $body = $this->CI->load->view('email/mcq_result_normal', $email, true);
                
                $from_name = $this->CI->option_model->get_option('email_from_name');
                $smtp_host = $this->CI->option_model->get_option('email_smtp_host');
                $smtp_port = $this->CI->option_model->get_option('email_smtp_port');
                $smtp_user = $this->CI->option_model->get_option('email_smtp_user');
                $smtp_pass = $this->CI->option_model->get_option('email_smtp_pass');

                $config = Array(
                    'protocol'  => 'smtp',
                    'smtp_host' => $smtp_host,
                    'smtp_port' => $smtp_port,
                    'smtp_user' => $smtp_user,
                    'smtp_pass' => $smtp_pass,
                    'mailtype'  => 'html',
                    'charset'   => 'utf-8',
                    'wordwrap'  => false,
                    'validate'  => true
                );
                $this->CI->load->library('email', $config);

                $this->CI->email->initialize();
                $this->CI->email->set_newline("\r\n");

                $this->CI->email->from($smtp_user, $from_name);
                $this->CI->email->to($to);
                $this->CI->email->reply_to($smtp_user, $from_name);

                $this->CI->email->subject('Exam Result for '. $exam->exam_title);
                $this->CI->email->message($body);
                if (file_exists(ABSPATH .'uploads/'. $file_name)) {
                    $this->CI->email->attach(ABSPATH .'uploads/'. $file_name);
                }

                // prepare email data
                $email_data = array();
                $email_data['email_type'] = 'mcq result';
                $email_data['email_to'] = $to;
                $email_data['email_body'] = $body;

                if ( ! $this->CI->email->send()) {
                    $email_data['email_error'] = $this->CI->email->print_debugger();
                    $email_data['email_status'] = 0;
                    $this->CI->email_model->add_email($email_data);
                } else {
                    $email_data['email_status'] = 1;
                    $this->CI->email_model->add_email($email_data);
                }

                @unlink(ABSPATH .'uploads/'. $file_name);
            }
        }
    }
}

/* End of file robi_email.php */
/* Location: ./application/libraries/robi_email.php */