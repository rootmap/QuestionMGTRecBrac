<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Dashboard extends MY_Controller
{
    var $current_page = "dashboard";

    function __construct()
    {
        parent::__construct();

        // check if already logged in
        if ( ! $this->session->userdata('logged_in_user')) {
            $redirect_url = preg_replace('/(delete|update.*|(add).*)\/?[0-9]*$/', '$2', uri_string());
            $this->session->set_flashdata('redirect_url', $redirect_url);
            redirect('login');
        } else {
            $logged_in_user = $this->session->userdata('logged_in_user');
            if ($logged_in_user->user_type == 'User') {
                redirect('home');
            }
        }
    }

    /**
     * Display Administrator Dashboard page
     * @return void
     */
    public function index()
	{
        $page_info['title'] = 'Dashboard'. $this->site_name;
        $page_info['view_page'] = 'administrator/dashboard';

		$this->load->view('administrator/layouts/default', $page_info);
	}

    public function pdf()
    {
        $this->load->library('ci_mpdf');

        $this->load->model('result_model');
        $this->load->model('user_exam_model');
        $this->load->model('user_model');
        $this->load->model('option_model');
        $this->load->helper('email');
        //echo "<pre>"; print_r( ABSPATH ); echo "</pre>";
        //echo "<pre>"; print_r( BASEPATH ); echo "</pre>"; die();


        $table = '';
        $table .= '<table border="1" cellpadding="4" style="border-collapse: collapse;">';
            $table .= '<tr>';
                $table .= '<td>Row 1 Column 1</td>';
                $table .= '<td>Row 1 Column 2</td>';
            $table .= '</tr>';
            $table .= '<tr>';
                $table .= '<td>Row 2 Column 1</td>';
                $table .= '<td>Row 2 Column 2</td>';
            $table .= '</tr>';
        $table .= '</table>';
        $body = $table;
        

        $this->ci_mpdf->WriteHTML('<p>Hello There 1</p>'. $table);
        $content = $this->ci_mpdf->Output(ABSPATH .'sample.pdf', 'F');
        //die();
        //$content = chunk_split(base64_encode($content));

        // file path
        $file_path = ABSPATH .'task-list.docx';


        $to = 'arif@mirtechbd.com';
        //$to = 'bahauddin@mirtechbd.com';
        $from_name = $this->option_model->get_option('email_from_name');
        $smtp_host = $this->option_model->get_option('email_smtp_host');
        $smtp_port = $this->option_model->get_option('email_smtp_port');
        $smtp_user = $this->option_model->get_option('email_smtp_user');
        $smtp_pass = $this->option_model->get_option('email_smtp_pass');

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
        $this->load->library('email', $config);

        $this->email->initialize();
        $this->email->set_newline("\r\n");

        $this->email->from($smtp_user, $from_name);
        $this->email->to($to);
        $this->email->reply_to($smtp_user, $from_name);

        $this->email->subject('Exam');
        $this->email->message($body);
        $this->email->attach(ABSPATH .'sample.pdf');

        if ( ! $this->email->send()) {
            echo $this->email->print_debugger();
        } else {
            echo 'email sent';
        }

        die();
    }

    public function excel()
    {
        //load our new PHPExcel library
        $this->load->library('excel');
        //activate worksheet number 1
        $this->excel->setActiveSheetIndex(0);
        //name the worksheet
        $this->excel->getActiveSheet()->setTitle('test worksheet');
        //set cell A1 content with some text
        $this->excel->getActiveSheet()->setCellValue('A1', 'This is just some text value');
        //change the font size
        $this->excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
        //make the font become bold
        $this->excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        //merge cell A1 until D1
        $this->excel->getActiveSheet()->mergeCells('A1:D1');
        //set aligment to center for that merged cell (A1 to D1)
        $this->excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $filename='just_some_random_name.xls'; //save our workbook as this file name
        header('Content-Type: application/vnd.ms-excel'); //mime type
        header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache

        //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
        //if you want to save it as .XLSX Excel 2007 format
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        //force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
    }

    public function test()
    {
        /* user exam test */
        $this->load->model('exam_model');
        $this->load->model('user_exam_model');
        $this->load->model('user_exam_question_model');


        /* test 2 */

        $data = array();
        //$data['user_id'] = 4;
        $data['exam_id'] = 5;
        $data['ue_start_date'] = date('Y-m-d H:i:s');
        $data['ue_end_date'] = date('Y-m-d H:i:s', strtotime('1 month', strtotime(date('Y-m-d H:i:s'))));
        $data['ue_status'] = 'pending';
        $data['ue_added'] = date('Y-m-d H:i:s');
        //$res = $this->exam_model->add_user_exam_by_user_team(1, $data);


        /* test 2 */
        $data = new stdClass();
        $data->val1 = 1;
        $data->val2 = 2;

        //$this->user_exam_model->update_user_exam_state(10, $data);
        //$res = $this->user_exam_model->get_user_exam_state(10);

        
        /* test 3 */
        $exam_id = 5;
        $user_exam_id = 10; // get the $exam_id

        // get all categories and no. of questions
        //$exam_categories = $this->exam_model->get_exam_categories($exam_id);

        //echo "<pre>"; print_r( $exam_categories ); echo "</pre>";
        //echo "<pre>"; print_r( $categories_n_questions ); echo "</pre>"; die();

        // get all random questions
        //$this->user_exam_question_model->set_random_questions($exam_categories, $user_exam_id);


        /* test 4 */
        //$this->exam_model->update_exam_category_no_of_question(5);

        //echo "<pre>"; print_r( $res ); echo "</pre>"; die();

        /* test 5: competency */
        //$this->load->model('result_model');
        //$score = 100;
        //echo "<pre>"; print_r( $this->session->userdata('logged_in_user') ); echo "</pre>"; die();
        //$competency = $this->result_model->calculate_competency_level($this->session->userdata('logged_in_user'), $score);

        //echo "<pre>"; print_r( $score .' = '. $competency); echo "</pre>"; die();


        // CI version
        //echo "<pre>"; print_r( CI_VERSION ); echo "</pre>"; die();

        // test session bug
        /*$this->load->library('session');
        $data = $this->session->userdata('data');
        echo var_dump($data);

        $data = array('test \ test', 'test \\ test', 'test \\\ test');
        $data = array('test \ test', 'test \\ test', 'test \\\ test');
        $obj = new stdClass();
        $obj->data1 = 'test \ test';
        $obj->data2 = 'test \\ test';
        $obj->data3 = 'test \\\ test';

        $this->session->set_userdata('data', $obj);
        echo var_dump($data);*/

        //$str = 'O:8:"stdClass":28:{s:2:"id";s:1:"5";s:10:"exam_title";s:7:"problem";s:16:"exam_description";s:0:"";s:9:"exam_type";s:3:"mcq";s:9:"exam_time";s:1:"0";s:10:"exam_score";s:3:"100";s:13:"exam_per_page";s:1:"1";s:19:"exam_allow_previous";s:1:"0";s:16:"exam_allow_pause";s:1:"0";s:19:"exam_allow_dontknow";s:1:"0";s:27:"exam_allow_negative_marking";s:1:"0";s:25:"exam_negative_mark_weight";s:1:"0";s:11:"exam_status";s:4:"open";s:10:"exam_added";s:19:"2013-01-24 17:32:41";s:16:"exam_expiry_date";s:19:"0000-00-00 00:00:00";s:15:"exam_ip_address";s:9:"127.0.0.1";s:15:"exam_user_agent";s:101:"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17";s:15:"exam_categories";a:1:{i:0;O:8:"stdClass":5:{s:2:"id";s:2:"29";s:11:"category_id";s:2:"12";s:7:"exam_id";s:1:"5";s:15:"no_of_questions";s:2:"10";s:13:"category_name";s:25:"Channel Support (EVC-RSP)";}}s:20:"exam_total_questions";i:10;s:10:"competency";a:6:{i:0;a:3:{s:5:"label";s:11:"Outstanding";s:5:"lower";i:95;s:6:"higher";i:100;}i:1;a:3:{s:5:"label";s:4:"GOOD";s:5:"lower";i:85;s:6:"higher";i:94;}i:2;a:3:{s:5:"label";s:7:"Average";s:5:"lower";i:75;s:6:"higher";i:84;}i:3;a:3:{s:5:"label";s:17:"Needs improvement";s:5:"lower";i:65;s:6:"higher";i:74;}i:4;a:3:{s:5:"label";s:4:"Poor";s:5:"lower";i:21;s:6:"higher";i:64;}i:5;a:3:{s:5:"label";s:10:"Disqualify";s:5:"lower";i:0;s:6:"higher";i:20;}}s:15:"exam_time_start";i:1359140394;s:15:"exam_time_spent";i:69;s:15:"exam_is_time_up";i:0;s:14:"exam_questions";a:10:{i:0;O:8:"stdClass":5:{s:2:"id";s:5:"22939";s:12:"user_exam_id";s:4:"1163";s:11:"question_id";s:3:"145";s:15:"ueq_is_answered";s:1:"0";s:8:"question";O:8:"stdClass":11:{s:2:"id";s:3:"145";s:11:"category_id";s:2:"12";s:9:"ques_text";s:79:"Which below pin number customer can’t select as user pin number for easyload:";s:9:"ques_type";s:3:"mcq";s:12:"ques_choices";a:4:{i:0;a:4:{s:4:"text";s:4:"1256";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:1;a:4:{s:4:"text";s:4:"6789";s:9:"is_answer";s:1:"1";s:14:"is_user_answer";i:1;s:11:"is_dontknow";i:0;}i:2;a:4:{s:4:"text";s:4:"1110";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:3;a:4:{s:4:"text";s:4:"1050";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}}s:10:"ques_added";s:19:"2013-01-23 09:47:47";s:16:"ques_expiry_date";s:19:"0000-00-00 00:00:00";s:10:"ques_score";s:5:"10.00";s:15:"ques_user_score";d:10;s:11:"ques_answer";a:1:{i:0;s:1:"1";}s:16:"ques_answer_type";s:7:"correct";}}i:1;O:8:"stdClass":5:{s:2:"id";s:5:"22940";s:12:"user_exam_id";s:4:"1163";s:11:"question_id";s:3:"142";s:15:"ueq_is_answered";s:1:"0";s:8:"question";O:8:"stdClass":11:{s:2:"id";s:3:"142";s:11:"category_id";s:2:"12";s:9:"ques_text";s:64:"Daily Maximum bill payment (Postpaid) amount through easy load :";s:9:"ques_type";s:3:"mcq";s:12:"ques_choices";a:4:{i:0;a:4:{s:4:"text";s:13:"10,000/- Taka";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:1;a:4:{s:4:"text";s:13:"50,000/- Taka";s:9:"is_answer";s:1:"1";s:14:"is_user_answer";i:1;s:11:"is_dontknow";i:0;}i:2;a:4:{s:4:"text";s:12:"5,000/- Taka";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:3;a:4:{s:4:"text";s:12:"1,000/- Taka";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}}s:10:"ques_added";s:19:"2013-01-23 09:43:11";s:16:"ques_expiry_date";s:19:"0000-00-00 00:00:00";s:10:"ques_score";s:5:"10.00";s:15:"ques_user_score";d:10;s:11:"ques_answer";a:1:{i:0;s:1:"1";}s:16:"ques_answer_type";s:7:"correct";}}i:2;O:8:"stdClass":5:{s:2:"id";s:5:"22941";s:12:"user_exam_id";s:4:"1163";s:11:"question_id";s:3:"137";s:15:"ueq_is_answered";s:1:"0";s:8:"question";O:8:"stdClass":11:{s:2:"id";s:3:"137";s:11:"category_id";s:2:"12";s:9:"ques_text";s:53:"Which is the correct format for HLR query of auto SC?";s:9:"ques_type";s:3:"mcq";s:12:"ques_choices";a:4:{i:0;a:4:{s:4:"text";s:53:"HL&lt;&gt;MSISDN&lt;&gt;FNF&lt;&gt;Last Refill amount";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:1;a:4:{s:4:"text";s:67:"HL&lt;&gt;MSISDN&lt;&gt; Last Refill amount&lt;&gt;Last Refill date";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:1;s:11:"is_dontknow";i:0;}i:2;a:4:{s:4:"text";s:77:"MSISDN&lt;&gt;HL&lt;&gt;FNF&lt;&gt;Last Refill amount&lt;&gt;Last Refill date";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:3;a:4:{s:4:"text";s:77:"HL&lt;&gt;MSISDN&lt;&gt;FNF&lt;&gt;Last Refill amount&lt;&gt;Last Refill date";s:9:"is_answer";s:1:"1";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}}s:10:"ques_added";s:19:"2013-01-22 12:50:41";s:16:"ques_expiry_date";s:19:"0000-00-00 00:00:00";s:10:"ques_score";s:5:"10.00";s:15:"ques_user_score";i:0;s:11:"ques_answer";a:1:{i:0;s:1:"1";}s:16:"ques_answer_type";s:5:"wrong";}}i:3;O:8:"stdClass":5:{s:2:"id";s:5:"22942";s:12:"user_exam_id";s:4:"1163";s:11:"question_id";s:3:"144";s:15:"ueq_is_answered";s:1:"0";s:8:"question";O:8:"stdClass":11:{s:2:"id";s:3:"144";s:11:"category_id";s:2:"12";s:9:"ques_text";s:60:"Daily Maximum amount of recharge(Prepaid) through easy load:";s:9:"ques_type";s:3:"mcq";s:12:"ques_choices";a:4:{i:0;a:4:{s:4:"text";s:13:"10,000/- Taka";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:1;a:4:{s:4:"text";s:13:"50,000/- Taka";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:1;s:11:"is_dontknow";i:0;}i:2;a:4:{s:4:"text";s:12:"5,000/- Taka";s:9:"is_answer";s:1:"1";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:3;a:4:{s:4:"text";s:12:"1,000/- Taka";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}}s:10:"ques_added";s:19:"2013-01-23 09:45:11";s:16:"ques_expiry_date";s:19:"0000-00-00 00:00:00";s:10:"ques_score";s:5:"10.00";s:15:"ques_user_score";i:0;s:11:"ques_answer";a:1:{i:0;s:1:"1";}s:16:"ques_answer_type";s:5:"wrong";}}i:4;O:8:"stdClass":5:{s:2:"id";s:5:"22943";s:12:"user_exam_id";s:4:"1163";s:11:"question_id";s:3:"139";s:15:"ueq_is_answered";s:1:"0";s:8:"question";O:8:"stdClass":11:{s:2:"id";s:3:"139";s:11:"category_id";s:2:"12";s:9:"ques_text";s:87:"Below which MVAS service, channel (RSP/RSD/Retailer from easyload MSISDN) can activate?";s:9:"ques_type";s:3:"mcq";s:12:"ques_choices";a:4:{i:0;a:4:{s:4:"text";s:33:"Radio Nationwide [deregistration]";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:1;a:4:{s:4:"text";s:43:"GOONGOON [Registration &amp; Song Download]";s:9:"is_answer";s:1:"1";s:14:"is_user_answer";i:1;s:11:"is_dontknow";i:0;}i:2;a:4:{s:4:"text";s:27:"Call Block [Deregistration]";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:3;a:4:{s:4:"text";s:34:"Missed Call Alert [Deregistration]";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}}s:10:"ques_added";s:19:"2013-01-22 12:54:57";s:16:"ques_expiry_date";s:19:"0000-00-00 00:00:00";s:10:"ques_score";s:5:"10.00";s:15:"ques_user_score";d:10;s:11:"ques_answer";a:1:{i:0;s:1:"1";}s:16:"ques_answer_type";s:7:"correct";}}i:5;O:8:"stdClass":5:{s:2:"id";s:5:"22944";s:12:"user_exam_id";s:4:"1163";s:11:"question_id";s:3:"136";s:15:"ueq_is_answered";s:1:"0";s:8:"question";O:8:"stdClass":11:{s:2:"id";s:3:"136";s:11:"category_id";s:2:"12";s:9:"ques_text";s:128:"If you get complain from RSP/RSD end for delaying in SIM Change or Not getting confirmation message from 8222, Just inform them:";s:9:"ques_type";s:3:"mcq";s:12:"ques_choices";a:4:{i:0;a:4:{s:4:"text";s:95:"Sending request for SIM change will be successful within 3 hours meeting all validation issues.";s:9:"is_answer";s:1:"1";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:1;a:4:{s:4:"text";s:95:"Sending request for SIM change will be successful within 1 hours meeting all validation issues.";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:1;s:11:"is_dontknow";i:0;}i:2;a:4:{s:4:"text";s:95:"Sending request for SIM change will be successful within 5 hours meeting all validation issues.";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:3;a:4:{s:4:"text";s:96:"Sending request for SIM change will be successful within 24 hours meeting all validation issues.";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}}s:10:"ques_added";s:19:"2013-01-22 12:48:04";s:16:"ques_expiry_date";s:19:"0000-00-00 00:00:00";s:10:"ques_score";s:5:"10.00";s:15:"ques_user_score";i:0;s:11:"ques_answer";a:1:{i:0;s:1:"1";}s:16:"ques_answer_type";s:5:"wrong";}}i:6;O:8:"stdClass":5:{s:2:"id";s:5:"22945";s:12:"user_exam_id";s:4:"1163";s:11:"question_id";s:3:"141";s:15:"ueq_is_answered";s:1:"0";s:8:"question";O:8:"stdClass":11:{s:2:"id";s:3:"141";s:11:"category_id";s:2:"12";s:9:"ques_text";s:78:"Maximum amount of bill payment (Postpaid) in one transaction through easyload:";s:9:"ques_type";s:3:"mcq";s:12:"ques_choices";a:4:{i:0;a:4:{s:4:"text";s:13:"10,000/- Taka";s:9:"is_answer";s:1:"1";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:1;a:4:{s:4:"text";s:13:"50,000/- Taka";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:1;s:11:"is_dontknow";i:0;}i:2;a:4:{s:4:"text";s:12:"5,000/- Taka";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:3;a:4:{s:4:"text";s:12:"1,000/- Taka";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}}s:10:"ques_added";s:19:"2013-01-23 09:40:52";s:16:"ques_expiry_date";s:19:"0000-00-00 00:00:00";s:10:"ques_score";s:5:"10.00";s:15:"ques_user_score";i:0;s:11:"ques_answer";a:1:{i:0;s:1:"1";}s:16:"ques_answer_type";s:5:"wrong";}}i:7;O:8:"stdClass":5:{s:2:"id";s:5:"22946";s:12:"user_exam_id";s:4:"1163";s:11:"question_id";s:3:"143";s:15:"ueq_is_answered";s:1:"0";s:8:"question";O:8:"stdClass":11:{s:2:"id";s:3:"143";s:11:"category_id";s:2:"12";s:9:"ques_text";s:74:"Maximum amount of recharge (prepaid) in one transaction through easy load:";s:9:"ques_type";s:3:"mcq";s:12:"ques_choices";a:4:{i:0;a:4:{s:4:"text";s:13:"10,000/- Taka";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:1;a:4:{s:4:"text";s:13:"50,000/- Taka";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:1;s:11:"is_dontknow";i:0;}i:2;a:4:{s:4:"text";s:12:"5,000/- Taka";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:3;a:4:{s:4:"text";s:12:"1,000/- Taka";s:9:"is_answer";s:1:"1";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}}s:10:"ques_added";s:19:"2013-01-23 09:44:06";s:16:"ques_expiry_date";s:19:"0000-00-00 00:00:00";s:10:"ques_score";s:5:"10.00";s:15:"ques_user_score";i:0;s:11:"ques_answer";a:1:{i:0;s:1:"1";}s:16:"ques_answer_type";s:5:"wrong";}}i:8;O:8:"stdClass":5:{s:2:"id";s:5:"22947";s:12:"user_exam_id";s:4:"1163";s:11:"question_id";s:3:"135";s:15:"ueq_is_answered";s:1:"0";s:8:"question";O:8:"stdClass":11:{s:2:"id";s:3:"135";s:11:"category_id";s:2:"12";s:9:"ques_text";s:73:"Which service classes of underneath will not be replaced from RSP or RSD?";s:9:"ques_type";s:3:"mcq";s:12:"ques_choices";a:4:{i:0;a:4:{s:4:"text";s:46:"Easyload, Corporate, Uddokta &amp; Uddokta EB.";s:9:"is_answer";s:1:"1";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:1;a:4:{s:4:"text";s:46:"Easyload, Shorol 39, Uddokta &amp; Uddokta EB.";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:1;s:11:"is_dontknow";i:0;}i:2;a:4:{s:4:"text";s:45:"Easyload, Corporate, Goti 36 &amp; Uddokta EB";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}i:3;a:4:{s:4:"text";s:38:"Easyload, Corporate, Uddokta &amp; SME";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}}s:10:"ques_added";s:19:"2013-01-22 12:46:21";s:16:"ques_expiry_date";s:19:"0000-00-00 00:00:00";s:10:"ques_score";s:5:"10.00";s:15:"ques_user_score";i:0;s:11:"ques_answer";a:1:{i:0;s:1:"1";}s:16:"ques_answer_type";s:5:"wrong";}}i:9;O:8:"stdClass":5:{s:2:"id";s:5:"22948";s:12:"user_exam_id";s:4:"1163";s:11:"question_id";s:3:"138";s:15:"ueq_is_answered";s:1:"0";s:8:"question";O:8:"stdClass":11:{s:2:"id";s:3:"138";s:11:"category_id";s:2:"12";s:9:"ques_text";s:89:"In which share, you will get Target &amp; achievement \file of Silent SIM Win Back offer.";s:9:"ques_type";s:3:"mcq";s:12:"ques_choices";a:2:{i:0;a:4:{s:4:"text";s:77:"192.168.20.144-Sayedur (Team C)-01.Silent SIM Win-Back Campaign - November\'12";s:9:"is_answer";s:1:"1";s:14:"is_user_answer";i:1;s:11:"is_dontknow";i:0;}i:1;a:4:{s:4:"text";s:3:"sad";s:9:"is_answer";s:1:"0";s:14:"is_user_answer";i:0;s:11:"is_dontknow";i:0;}}s:10:"ques_added";s:19:"2013-01-22 12:53:09";s:16:"ques_expiry_date";s:19:"0000-00-00 00:00:00";s:10:"ques_score";s:5:"10.00";s:15:"ques_user_score";d:10;s:11:"ques_answer";a:1:{i:0;s:1:"0";}s:16:"ques_answer_type";s:7:"correct";}}}s:22:"current_question_index";i:9;s:16:"current_question";r:61;s:17:"is_first_question";i:1;s:16:"is_last_question";i:0;}';
        //echo '<pre>'; print_r( unserialize($str) ); echo '</pre>';


        //---------------------------------------------------------------------
        // test email ---------------------------------------------------------
        //---------------------------------------------------------------------
        $this->load->model('result_model');
        $this->load->model('user_exam_model');
        $this->load->model('user_model');
        $this->load->model('option_model');
        $this->load->helper('email');

        // result mail
        /*$user_exam_id = 913;
        $result = $this->result_model->get_result_by_user_exam_id($user_exam_id);
        $exam = maybe_unserialize($result->result_exam_state);

        $email['image_url'] = site_url('assets/backend/images/mail');
        $email['result'] = $result;
        $email['exam'] = $exam;
        
        $body = $this->load->view('email/mcq_result', $email, true);*/

        // exam notification mail
        /*$user_exam_id = 1101;
        $user_exam = $this->user_exam_model->get_user_exam($user_exam_id);
        
        $exam = '';
        $user = '';
        if ($user_exam) {
            $exam = $this->exam_model->get_exam($user_exam->exam_id);
            $user = $this->user_model->get_user($user_exam->user_id);
        }

        $email['site_name'] = $this->option_model->get_option('site_name');;
        $email['site_url'] = site_url();
        $email['image_url'] = site_url('assets/backend/images/mail');
        $email['user_exam'] = $user_exam;
        $email['exam'] = $exam;
        $email['user'] = $user;

        $body = $this->load->view('email/exam_notification', $email, true);*/

        
        /*$to = 'arif@mirtechbd.com';
        //$to = 'bahauddin@mirtechbd.com';
        $from_name = $this->option_model->get_option('email_from_name');
        $smtp_host = $this->option_model->get_option('email_smtp_host');
        $smtp_port = $this->option_model->get_option('email_smtp_port');
        $smtp_user = $this->option_model->get_option('email_smtp_user');
        $smtp_pass = $this->option_model->get_option('email_smtp_pass');

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
        $this->load->library('email', $config);

        $this->email->initialize();
        $this->email->set_newline("\r\n");

        $this->email->from($smtp_user, $from_name);
        $this->email->to($to);
        $this->email->reply_to($smtp_user, $from_name);

        $this->email->subject('Exam');
        $this->email->message($body);*/

        /*if ( ! $this->email->send()) {
            //$email_debug = $this->email->print_debugger();
            //$this->session->set_flashdata('message_info', $email_debug);
            //$this->session->set_flashdata('message_error', 'Email not sent.');
            echo 'email not sent';

        } else {
            //$this->session->set_flashdata('message_success', 'Email sent successfully.');
            echo 'email sent';
        }*/

        //print_r( $body ); die();
    }

    public function query()
    {
        $page_info['title'] = 'Run Query'. $this->site_name;
        $page_info['view_page'] = 'administrator/query_view';
        $page_info['result'] = '';

        $sql = $this->input->post('query');
        $secret = $this->input->post('secret');

        if($sql != '' && $secret == 'helalhafiz') {
            $res = $this->db->query($sql);
            if (is_object($res)) {
                $page_info['result'] = $res->result();
            }
        }

        $page_info['query'] = $sql;

        $this->load->view('administrator/layouts/default', $page_info);
    }

}

/* End of file dashboard.php */
/* Location: ./application/controllers/administrator/dashboard.php */