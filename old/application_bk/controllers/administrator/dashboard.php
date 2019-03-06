<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Dashboard extends MY_Controller
{
    var $current_page = "dashboard";
    
    function __construct()
    {

        parent::__construct();
        $this->load->model('utility_model');
       // print_r($this->session->userdata('logged_in_user')); die();
        // check if already logged in
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
     * Display Administrator Dashboard page
     * @return void
     */
    public function index()
	{

        $page_info['title'] = 'Dashboard'. $this->site_name;
        $page_info['view_page'] = 'administrator/dashboard';

        $page_info['event_status'] = ""; //$this->utility_model->get_check_event();

		$this->load->view('administrator/layouts/default', $page_info);
	}

    public function set_event()
    {
        $this->utility_model->set_event_on();
        redirect('administrator/dashboard');
    }

    private function query()
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
    	 
    	tcpdf();
    	$obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
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
    	$obj_pdf->SetFont('helvetica', '', 9);
    	$obj_pdf->setFontSubsetting(false);
    	$obj_pdf->AddPage();
    	ob_start();
    	// we can have any view part here like HTML, PHP etc
    	echo '<h1>1st Exam</h1>';
    	$content = ob_get_contents();
    	ob_end_clean();
    	$obj_pdf->writeHTML($content, true, false, true, false, '');
    	$obj_pdf->Output('output.pdf', 'I');
    }
    
    
    public function mpdf()
    {
    	// set page specific variables
    	$page_info['title'] = 'Edit Exam'. $this->site_name;
    	$page_info['view_page'] = 'administrator/pdfreport';
    	$page_info['message_error'] = '';
    	$page_info['message_success'] = '';
    	$page_info['message_info'] = '';
    	$page_info['is_edit'] = true;
    	
    	$data = [];
    	//load the view and saved it into $html variable
    	$html=$this->load->view('welcome_message', $data, true);
    	
    	//this the the PDF filename that user will get to download
    	$pdfFilePath = "output_pdf_name.pdf";
    	
    	//load mPDF library
    	$this->load->library('m_pdf');
    	
    	//generate the PDF from the given html
    	$this->m_pdf->pdf->WriteHTML($html);
    	
    	//download it.
    	$this->m_pdf->pdf->Output($pdfFilePath, "D");	
    }

}

/* End of file dashboard.php */
/* Location: ./application/controllers/administrator/dashboard.php */