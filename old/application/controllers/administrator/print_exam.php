<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Print_exam extends MY_Controller
{
    var $current_page = "assign-exam";
    var $exam_list = array();
    var $user_group_list = array();
    var $user_team_list = array();
    var $user_list = array();
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
		$this->form_data = new StdClass;
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->library('robi_email');
        $this->load->model('exam_model');
        $this->load->model('user_team_model');
        $this->load->model('user_group_model');
        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');


        // pre-load lists
        $open_exams = $this->exam_model->get_open_exams();
        $this->exam_list[] = 'Select an Exam';
        if ($open_exams) {
            for ($i=0; $i<count($open_exams); $i++) {
                $this->exam_list[$open_exams[$i]->id] = $open_exams[$i]->exam_title;
            }
        }

        $user_groups = $this->user_group_model->get_user_groups();
        $this->user_group_list[] = 'Select an User Group';
        if ($user_groups) {
            for ($i=0; $i<count($user_groups); $i++) {
                $this->user_group_list[$user_groups[$i]->id] = $user_groups[$i]->group_name;
            }
        }

        $user_teams = $this->user_team_model->get_user_teams();
        $this->user_team_list[] = 'Select an User Team';
        if ($user_teams) {
            for ($i=0; $i<count($user_teams); $i++) {
                $this->user_team_list[$user_teams[$i]->id] = $user_teams[$i]->team_name;
            }
        }

        $users = $this->user_model->get_active_users('User');
        if ($users) {
            for ($i=0; $i<count($users); $i++) {
                $this->user_list[$users[$i]->id] = $users[$i]->user_first_name .' '. $users[$i]->user_last_name .' - '. $users[$i]->user_login;
            }
        }


        if($this->session->flashdata('ue_start_datetime')) {
            $this->session->keep_flashdata('ue_start_datetime');
        }
        if($this->session->flashdata('ue_end_datetime')) {
            $this->session->keep_flashdata('ue_end_datetime');
        }


        // check if logged in
        if ( ! $this->session->userdata('logged_in_user')) {
            $redirect_url = preg_replace('/(delete|update.*|(add).*)\/?[0-9]*$/', '$2', uri_string());
            $this->session->set_flashdata('redirect_url', $redirect_url);
            redirect('login');
        } else {
            $logged_in_user = $this->session->userdata('logged_in_user');
            if ($logged_in_user->user_type == 'User' && !$this->session->userdata('user_privilage_name')) {
                redirect('home');
            }
        }
    }

    /**
     * Display paginated list of exams
     * @return void
     */
    public function index_old()
	{
        // set page specific variables
        $page_info['title'] = 'Manage Exams'. $this->site_name;
        $page_info['view_page'] = 'administrator/print_exam_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';


        $this->_set_fields();


        if ($this->input->post('assign_exam_submit')) {
            $exam_id = (int)$this->input->post('exam_id');
            if ($exam_id > 0) {
            	redirect('administrator/print_exam/assign/'. encrypt($exam_id));
            } else {
                $page_info['message_error'] = 'Please select an exam from the list.';
            }
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

    public function assign($exam_id = 0)
    {
        // set page specific variables
        $page_info['title'] = 'Print Exam Question'. $this->site_name;
        $page_info['view_page'] = 'administrator/print_exam_add_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

         
        $this->_set_fields();
        $exam_id = dencrypt($this->uri->segment(4));
        $exam_id=(int)($exam_id);  
        if ($exam_id <= 0) {
            $this->session->set_flashdata('message_success', 'Please select an exam from the list.');
            redirect('administrator/print_exam');
        }
		
        $this->form_data->exam_id = encrypt($exam_id);
        $this->form_data->exam_id_hidden = encrypt($exam_id);
        $this->form_data->new_name=$this->exam_list[$exam_id];  
        if ($this->session->flashdata('ue_start_datetime')) {
            $ue_start_datetime = $this->session->flashdata('ue_start_datetime');
            $this->form_data->ue_start_date = date('d/m/Y', strtotime($ue_start_datetime)); 
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

    public function do_assign($exam_id = 0)
    {
        // set page specific variables
        $page_info['title'] = 'Assign Exam to Users'. $this->site_name;
        $page_info['view_page'] = 'administrator/print_exam_add_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
 
        $exam_id = dencrypt($this->uri->segment(4)); 
        $exam_id=(int)($exam_id); 
        if ($exam_id <= 0) {
            $this->session->set_flashdata('message_success', 'Please select an exam from the list.');
            redirect('administrator/print_exam');
        }


        $this->_set_fields();
        $this->_set_rules();

        $this->form_data->exam_id = $exam_id;
        $this->form_data->exam_id_hidden = $exam_id;


        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {
			
            $exam_id = dencrypt($this->input->post('exam_id_hidden'));
            $exam_id=(int)($exam_id);
             
            $ue_start_date = $this->input->post('ue_start_date');
            $new_name = $this->input->post('new_name');
            $exam_description= $this->input->post('exam_description');

            if ($ue_start_date == '') {
                $ue_start_date = '';
            } else {
                $day = (int)substr($ue_start_date, 0, 2);
                $month = (int)substr($ue_start_date, 3, 2);
                $year = (int)substr($ue_start_date, 6, 4);
                $ue_start_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year)); 
            } 

             

            $data = array(
                'exam_id' => $exam_id,
                'create_date' => $ue_start_date,
            	'exam_name' => $new_name,
            	'description' => $exam_description
                 
            );

            $is_assigned = $this->exam_model->print_question($data);


            $this->session->set_flashdata('message_success', 'Qestion is ready for download');
            redirect('administrator/print_exam');
        }
    }


    public function index()
    {
    	// set page specific variables
    	$page_info['title'] = 'Print Question'. $this->site_name;
    	$page_info['view_page'] = 'administrator/print_list_view';
    	$page_info['message_error'] = '';
    	$page_info['message_success'] = '';
    	$page_info['message_info'] = '';
    	
    	$this->_set_fields();
    	
    	
    	// gather filter options
    	$filter = array();
    	if ($this->session->flashdata('filter_exam_title')) {
    		$this->session->keep_flashdata('filter_exam_title');
    		$filter_exam_title = $this->session->flashdata('filter_exam_title');
    		$this->form_data->filter_exam_title = $filter_exam_title;
    		$filter['filter_exam_title']['field'] = 'exam_title';
    		$filter['filter_exam_title']['value'] = $filter_exam_title;
    	} 
    	$page_info['filter'] = $filter;
    	 
    	$per_page = $this->config->item('per_page');
    	$uri_segment = $this->config->item('uri_segment');
    	$page_offset = $this->uri->segment($uri_segment);
    	$page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;
    	 
    	$record_result = $this->exam_model->get_paged_print_exams($per_page, $page_offset, $filter); 
    	
    	$page_info['records'] = $record_result['result'];
    	$records = $record_result['result'];
    	 
    	// build paginated list
    	$config = array();
    	$config["base_url"] = base_url() . "administrator/print_exam";
    	$config["total_rows"] = $record_result['count'];
    	$this->pagination->initialize($config);
    	$page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;
    	
    	
    	if ($records) {
    		// customize and generate records table
    		$tbl_heading = array(
    				'0' => array('data'=> 'Exam Title'),
    				'1' => array('data'=> 'Exam Description'),
    				'2' => array('data'=> 'Exam Date'), 
    				'3' => array('data'=> 'Action', 'class' => 'center', 'width' => '80')
    		);
    		$this->table->set_heading($tbl_heading);
    		
    		$tbl_template = array (
    				'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
    				'table_close'         => '</table>'
    		);
    		$this->table->set_template($tbl_template);
    		
    		for ($i = 0; $i<count($records); $i++) {
    			
    			 
    			$action_str = ''; 
    			$action_str .= anchor('administrator/print_exam/pdf_question/'. encrypt($records[$i]->id), '<span class="btn btn-success"><i class="icon-print"></i></span>', 'title="Print"');
    			/*$action_str .= anchor('administrator/exam/delete/'. $records[$i]->id, '<i class="icon-trash"></i>', array('title'=>'Delete', 'onclick'=>'return confirm(\'Do you really want to delete this record?\')'));*/
    			
    			$tbl_row = array(
    					'0' => array('data'=> $records[$i]->exam_name),
    					'1' => array('data'=> $records[$i]->description), 
    					'2' => array('data'=> $records[$i]->create_date),  
    					'3' => array('data'=> $action_str, 'class' => 'center', 'width' => '100', 'width' => '80')
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
    
    
    public function pdf_question()
    {
    	// set page specific variables
    	$page_info['title'] = 'Edit Exam'. $this->site_name;
    	$page_info['view_page'] = 'administrator/pdfreport';
    	$page_info['message_error'] = '';
    	$page_info['message_success'] = '';
    	$page_info['message_info'] = '';
    	$page_info['is_edit'] = true;
    	
    	$this->load->helper('pdf_helper');
    	
    	$print_id= dencrypt($this->uri->segment(4));
    	$print_id=(int)($print_id); 
    	
    	$questions= $this->exam_model->get_print_questions($print_id);
    	 
    	tcpdf();
    	$obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
    	$obj_pdf->SetCreator(PDF_CREATOR);
    	$title = "Exam Title";
    	$obj_pdf->SetTitle($title);
    	$obj_pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $title);
    	$obj_pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    	$obj_pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA)); 
    	$obj_pdf->SetDefaultMonospacedFont('helvetica');
    	$obj_pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    	$obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    	$obj_pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    	$obj_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    	//$obj_pdf->SetFont('RaviPrakash-Regular', '', 12);
    	$obj_pdf->SetFont('helvetica', '', 10);
    	$obj_pdf->setFontSubsetting(false);
    	$obj_pdf->AddPage(); 
    	$obj_pdf->resetColumns();
    	$obj_pdf->setEqualColumns(2, 84);  // KEY PART -  number of cols and width
    	$obj_pdf->selectColumn(); 
    	ob_start();
    	// we can have any view part here like HTML, PHP etc
    	echo '<h1>1st Exam</h1>';
    	for($i=0; $i<count($questions); $i++){
    		echo ($i+1). ') ' .$questions[$i]->question->ques_text;
    		echo '<br>';
    		 
    		$char = 'A';
    		foreach ($questions[$i]->question->ques_choices as $vaue){
    			echo '&nbsp;&nbsp;&nbsp;'. $char . ') ';
    			echo $vaue['text'];
    			echo '<br>';
    			$char++;
    		}
    		echo '<br>';
    		 
    		
    	}
    	
    	$content = ob_get_contents();
    	ob_end_clean();
    	$obj_pdf->writeHTML($content, true, false, true, false, '');
    	$obj_pdf->Output('output.pdf', 'I');
    }
    
    
    public function m_pdf_question()
    {
    	
    	$print_id= dencrypt($this->uri->segment(4));
    	$print_id=(int)($print_id);
    	
    	$questions= $this->exam_model->get_print_questions($print_id);
    	
    	 
    	$page_info['questions']= $questions;
    	//load the view and saved it into $html variable
    	$html=$this->load->view('welcome_message', $page_info, true);
    	
    	//this the the PDF filename that user will get to download
    	$pdfFilePath = "output_pdf_name.pdf";
    	
    	//load mPDF library
    	$this->load->library('m_pdf');
    	
    	//generate the PDF from the given html
    	$this->m_pdf->pdf->WriteHTML($html);
    	
    	//download it.
    	$this->m_pdf->pdf->Output($pdfFilePath, "D");	
    	
    	
    	
    }
    
    public function filter()
    {
    	$filter_exam_title = $this->input->post('filter_exam_title'); 
    	$filter_clear = $this->input->post('filter_clear');
    	
    	if ($filter_clear == '') {
    		if ($filter_exam_title != '') {
    			$this->session->set_flashdata('filter_exam_title', $filter_exam_title);
    		} 
    	} else {
    		$this->session->unset_userdata('filter_exam_title'); 
    	}
    	
    	redirect('administrator/print_exam');
    }
    
    // set empty default form field values
	private function _set_fields()
	{
		$this->form_data = new StdClass;
		$this->form_data->exam_id = 0;
        $this->form_data->exam_id_hidden = 0;
        $this->form_data->new_name= '';
        $this->form_data->exam_description= '';
        $this->form_data->ue_start_date = date('d/m/Y');
        $this->form_data->ue_start_time = '12:00 AM';
        $this->form_data->ue_end_date = date('d/m/Y', strtotime('1 month', time()));
        $this->form_data->ue_end_time = '12:00 AM';
        $this->form_data->user_group_id = '';
        $this->form_data->user_team_id = '';
        
        $this->form_data->filter_exam_title = '';
	}

	// validation rules
	private function _set_rules()
	{
        $this->form_validation->set_rules('ue_start_date', 'Start Date', 'required|trim|xss_clean|strip_tags'); 
        $this->form_validation->set_rules('new_name', 'Exam New Name', 'required|trim|xss_clean|strip_tags');
        
	}

}

/* End of file assign_exam.php */
/* Location: ./application/controllers/administrator/assign_exam.php */