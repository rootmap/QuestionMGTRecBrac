<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Survey_details_report extends MY_Controller
{
    var $current_page = "survey_details_report";
    var $survey_list = array();
    var $question_list = array();

    function __construct()
    {
        parent::__construct();
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('survey_report_model');
        $this->load->model('survey_model');
        $this->load->model('survey_question_model');
        $this->load->library('excel');
     
        $survey = $this->survey_model->get_surveys();

        $this->survey_list[] = 'Select a Survey';
        if ($survey) {
            for ($i=0; $i<count($survey); $i++) {
                $this->survey_list[$survey[$i]->id] = $survey[$i]->survey_title;
            }
        }
        
        $question = $this->survey_question_model->get_questions();

        $this->question_list[] = 'Select a Question';
        if ($question) {
            for ($i=0; $i<count($question); $i++) {
                $this->question_list[$question[$i]->id] = $question[$i]->ques_text;
            }
        }

        // check if logged in
        if ( ! $this->session->userdata('logged_in_user')) {
            $redirect_url = preg_replace('/(delete|update.*|(add).*)\/?[0-9]*$/', '$2', uri_string());
            $this->session->set_flashdata('redirect_url', $redirect_url);
            redirect('login');
        } else {
            $logged_in_user = $this->session->userdata('logged_in_user');
            if ($logged_in_user->user_type == 'User' && !$this->session->userdata('user_privilage_name')) {
                redirect('landing');
            }
        }
    }

    /**
     * Display paginated list of categories
     * @return void
     */
    public function index()
    {
        if($this->session->userdata('records')){
            $this->session->unset_userdata('records');
        }
        // set page specific variables
        $page_info['title'] = 'Survey Report'. $this->site_name;
        $page_info['view_page'] = 'administrator/survey_details_report_list_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_survey')) {
            $this->session->keep_flashdata('filter_survey');
            $filter_survey = $this->session->flashdata('filter_survey');
            $this->form_data->filter_survey = $filter_survey;
            $filter['filter_survey']['field'] = 'survey_id';
            $filter['filter_survey']['value'] = $filter_survey;
        }
        if ($this->session->flashdata('filter_question')) {
            $this->session->keep_flashdata('filter_question');
            $filter_question = $this->session->flashdata('filter_question');
            $this->form_data->filter_survey = $filter_question;
            $filter['filter_question']['field'] = 'question_id';
            $filter['filter_question']['value'] = $filter_question;
        }
        $page_info['filter'] = $filter;

        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->survey_report_model->get_survey_details($per_page, $page_offset, $filter);

        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];


        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/survey_details_report";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        if ($records) {
            $this->session->set_userdata('records', $records);
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'Title'),
                '1' => array('data'=> 'Questions'),
                '2' => array('data'=> 'Question Type', 'class' => 'center', 'width' => '100'),
                '3' => array('data'=> 'User Answer'),
                '4' => array('data'=> 'User')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {
                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->survey_title),
                    '1' => array('data'=> $records[$i]->ques_text),
                    '2' => array('data'=> $records[$i]->ques_type, 'class' => 'center', 'width' => '100', 'width' => '120'),
                    '3' => array('data'=> $records[$i]->answer),
                    '4' => array('data'=> $records[$i]->user_first_name.' ('.$records[$i]->user_login.')')
                ); 
                $this->table->add_row($tbl_row);
            }

            $page_info['records_table'] = $this->table->generate();
            $page_info['pagin_links'] = $this->pagination->create_links();
        } else {
            $page_info['records_table'] = '<div class="alert alert-info"><a data-dismiss="alert" class="close">&times;</a>No records found.</div>';
            $page_info['pagin_links'] = '';
        }
        
        // determine messages
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }

        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        
        // load view
	$this->load->view('administrator/layouts/default', $page_info);
    }

    public function filter()
    {
        $filter_survey = $this->input->post('filter_survey');
        $filter_question = $this->input->post('filter_question');
        $filter_clear = $this->input->post('filter_clear');

        if ($filter_clear == '') {
            if ($filter_survey != '') {
                $this->session->set_flashdata('filter_survey', $filter_survey);
            }
            if ($filter_question != '') {
                $this->session->set_flashdata('filter_question', $filter_question);
            }
        } else {
            $this->session->unset_userdata('filter_survey');
            $this->session->unset_userdata('filter_question');
        }

        redirect('administrator/survey_details_report');
    }
    
    public function export_data()
    {
        if($this->session->userdata('records')){
            $records = $this->session->userdata('records');
            if (is_array($records) && count($records) > 0) {

                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle('Sheet1');

                // set result column header
                $this->excel->getActiveSheet()->setCellValue('A1', 'Sl.');
                $this->excel->getActiveSheet()->setCellValue('B1', 'Survey Title');
                $this->excel->getActiveSheet()->setCellValue('C1', 'Questions');
                $this->excel->getActiveSheet()->setCellValue('D1', 'Question Type');
                $this->excel->getActiveSheet()->setCellValue('E1', 'User Answer');
                $this->excel->getActiveSheet()->setCellValue('F1', 'User');

                $this->excel->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

                // fill data
                $serial = 0;
                $row = 1;
                for ($i=0; $i<count($records); $i++) {                            
                    $serial++;
                    $row++;
                    
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $serial);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $records[$i]->survey_title);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $records[$i]->ques_text);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $records[$i]->ques_type);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $records[$i]->answer);
                    $this->excel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $records[$i]->user_first_name.' ('.$records[$i]->user_login.')');                     
                }

                $filename = 'Survey Details Report '. date('Y-m-d') .'.xls';

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="'. $filename. '"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                $objWriter->save('php://output');

            } else {
                $this->session->set_flashdata('message_error', 'Records not found to export');
                redirect('administrator/survey_details_report');
            }
        } else {
            $this->session->set_flashdata('message_error', 'Records not found to export');
            redirect('administrator/survey_details_report');
        }
    }
    
    // set empty default form field values
    private function _set_fields()
    {
	    @$this->form_data->filter_survey = '';
        @$this->form_data->filter_question = '';
    }
}

/* End of file category.php */
/* Location: ./application/controllers/administrator/category.php */