<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Smsormail extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    public function smsUrl($number='',$msg='')
    {
        $url = "http://test_url=".$number."&messageBody=".$msg;
        $response = file_get_contents($url);
        $p = xml_parser_create();
        xml_parse_into_struct($p, $response, $vals, $index);
        xml_parser_free($p);
        return $vals;
    }
    public function emailSend($email='',$mail_body='',$subject)
    {
        $data = $this->mailConfig();
        $config = Array(
              'protocol' => 'smtp',
              'smtp_host' => ''.$data[5]['option_value'].'',
              'smtp_port' => ''.$data[6]['option_value'].'',
              'smtp_user' => ''.$data[7]['option_value'].'', // change it to yours
              'smtp_pass' => ''.$data[12]['option_value'].'', // change it to yours
              'mailtype' => 'html',
              'charset' => 'iso-8859-1',
              'wordwrap' => TRUE
            );
        $this->load->library('email', $config);
        $to = $email;
        $subject = $subject;
        $mail_body = $mail_body;
        $this->email->from("".$data[7]['option_value']."", ''.$data[4]['option_value'].'');
        $this->email->to($to);
        $this->email->set_newline("\r\n");
        $this->email->subject($subject);
        $this->email->message($mail_body);
        return $this->email->send();
    }
    public function mailConfig()
    {
        $query = $this->db->get('exm_options');   
        return $query->result_array();
    }



}
?>