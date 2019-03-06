<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Home extends MY_Controller
{
    var $current_page = "home";
    var $logged_in_user = false;
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
        $this->load->helper('date');
        $this->load->model('exam_model');
        $this->load->model('result_model');
        $this->load->model('user_exam_model');

        $this->load->model('survey_model');
        $this->load->model('survey_question_model');
        $this->load->model('question_set_model');

        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');




        if ($this->session->userdata('exam_is_started')) {
            redirect('exam');
        } else {
            $this->session->unset_userdata('exam_id');
            $this->session->unset_userdata('user_exam_id');
            $this->session->unset_userdata('exam');
            $this->session->unset_userdata('exam_is_started');
            //$this->session->unset_userdata('exam_is_completed');
        }

        // check if already logged in
        if ( ! $this->logged_in_user) {
            $redirect_url = preg_replace('/(delete|update.*|(add).*)\/?[0-9]*$/', '$2', uri_string());
            $this->session->set_flashdata('redirect_url', $redirect_url);
            redirect('login');
        } else {
            if ($this->logged_in_user->user_type == 'Administrator' || $this->logged_in_user->user_type == 'Super Administrator') {
                redirect('administrator/dashboard');
            }
            if ((int)$this->logged_in_user->user_is_default_password == 1) {
            	redirect('profile/password');
            }
        }

    }

    /**
     * Display User Dashboard page
     * @return void
     */
    public function index()
	{

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'home page'));
        
        $page_info['title'] = 'Home'. $this->site_name;
        $page_info['view_page'] = 'user/home';

        $open_exams = $this->user_exam_model->get_user_exams($this->logged_in_user->id);
        $all_open_exams = $this->user_exam_model->get_user_all_open_exams($this->logged_in_user->id);
        //print_r_pre($open_exams);die;
        
        if ($open_exams) {
            for ($i=0; $i<count($open_exams); $i++) {
                $open_exams[$i]->exam = $this->exam_model->get_exam($open_exams[$i]->exam_id);

                $openExamSetInfo= $this->exam_model->get_Sets($open_exams[$i]->exam_id);
                //print_r_pre($openExamSetInfo);
                $setID=(int)$openExamSetInfo->category_id;
                $open_exams[$i]->exam_setID =$setID;
                $exam_type_string='Multiple Choice Questions (MCQ)';
                $sqlExamType= $this->question_set_model->get_TypeOfExam($setID);
                if(count($sqlExamType)==2)
                {
                    $exam_type_string='Exam Type MCQ & Written';
                }
                elseif(count($sqlExamType)==1)
                {
                    if($sqlExamType[0]->ques_type=='mcq')
                    {
                        $exam_type_string='Multiple Choice Questions (MCQ)';
                    }
                    else
                    {
                        $exam_type_string='Exam Type Written';
                    }
                }

                $open_exams[$i]->exam_type_string = $exam_type_string;


                    //die();

            //$open_exams[$i]->exam = $this->exam_model->getexamcategoriesalldata($open_exams[$i]->exam_id);

               // print_r_pre($open_exams[$i]->exam);
                $data = $this->exam_model->get_number_of_questions($open_exams[$i]->exam_id,$open_exams[$i]->exam->exam_random_qus);
                $open_exams[$i]->exam->exam_no_of_questions = $data[0];
                //print_r_pre($open_exams[$i]->exam->exam_no_of_questions);

            }
        }

        if ($all_open_exams) {
            for ($i=0; $i<count($all_open_exams); $i++) {
                $all_open_exams[$i]->exam = $this->exam_model->get_exam($all_open_exams[$i]->exam_id);

                //$open_exams[$i]->exam = $this->exam_model->getexamcategoriesalldata($open_exams[$i]->exam_id);

                // print_r_pre($open_exams[$i]->exam);
                $data = $this->exam_model->get_number_of_questions($all_open_exams[$i]->exam_id,$all_open_exams[$i]->exam->exam_random_qus);
                $all_open_exams[$i]->exam->exam_no_of_questions = $data[0];
                //print_r_pre($open_exams[$i]->exam->exam_no_of_questions);

            }
        }
        
        $page_info['open_survey_html'] = $this->get_survey('open');
        $page_info['completed_survey_html'] = $this->get_survey('completed');

        //print_r_pre($open_exams);
        $page_info['open_exams'] = $open_exams;
        $page_info['all_open_exams'] = $all_open_exams;


        $prev_exams = $this->result_model->get_results_by_user($this->logged_in_user->id, 3);
        $page_info['prev_exams'] = $prev_exams;


        // load view
	$this->load->view('user/layouts/default', $page_info);
	}

    public function download_admitcard()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Download Admit Card'));



        $this->load->helper('pdf_helper');
        $exam_id= $this->uri->segment(3);
        $question_set_id= (int)$this->uri->segment(5);

       //echo $this->user_model->get_user_profile_path(); die();

        $filePathUserIMage=base_url().'/uploads/user/'.$this->user_model->get_user_profile_path();
        $fileExistsUserIMage=file_exists('uploads/user/'.$this->user_model->get_user_profile_path());
        if(!$fileExistsUserIMage)
        {
            $filePathUserIMage=base_url().'/assets/images/avatar.png';
        }

        $filePathUserSignature=base_url().'uploads/signature/'.$this->user_model->get_user_signature_path();
        $fileExistsUserSignature=file_exists('uploads/signature/'.$this->user_model->get_user_signature_path());
        if(!$fileExistsUserSignature)
        {
            $filePathUserSignature=base_url().'/uploads/signature/signature.png';
        }

        $exam_details = $this->exam_model->get_exam($exam_id);
        $logged_in_user = $this->session->userdata('logged_in_user');

        $user_details = $this->user_model->get_user($logged_in_user->id);
        //var_dump($exam_details);die;

        $UpQRFilePath=base_url().'/assets/qrcode/index.php?data='.substr($exam_details->exam_title,0,2).$exam_id;
        $GenFileQR=file_get_contents($UpQRFilePath);
        $QRfilePath=base_url().'/assets/qrcode/'.$GenFileQR;

        //var_dump($question_set_id);die;


        $signaturenAdmitHeadD=$this->exam_model->get_admit_user();

        $filePathUserSignatureLam=base_url().'uploads/signature/'.$signaturenAdmitHeadD->signature_image;
        $fileExistsUserSignatureLam=file_exists('uploads/signature/'.$signaturenAdmitHeadD->signature_image);
        if(!$fileExistsUserSignatureLam)
        {
            $filePathUserSignatureLam=base_url().'/uploads/signature/signature.png';
        }
        //$questions= $this->question_set_model->get_question_by_ques_set_id($question_set_id);


        $examVenue = $this->exam_model->get_venue($exam_id);
        //print_r_pre($examVenue);

        $venues='';
        $venuesLocation='';
        if(!empty($examVenue))
        {
            $key=0;
            foreach($examVenue as $venue):
                if($key==0)
                {
                    $venues .=$venue['venue_name'];
                    $venuesLocation .=$venue['venue_location'];
                }
                else
                {
                    $venues .=', '.$venue['venue_name'];
                    $venuesLocation .=', '.$venue['venue_location'];
                }
                $key++;
            endforeach;
        }



        $this->db->where('exam_id', $exam_id);
        $this->db->where('user_id',$this->session->userdata('logged_in_user')->id);
        $this->db->select('ue_start_date,ue_end_date');
        $query = $this->db->get("exm_user_exams",0,1);
        //echo $this->db->last_query(); die();
        if ($query->num_rows() > 0) {
            $exam_dateRow=$query->row();
        }

        $dateofExam='';
        if(isset($exam_dateRow))
        {
            $dateofExam=date('d/m/Y',strtotime($exam_dateRow->ue_start_date)).'-'.date('d/m/Y',strtotime($exam_dateRow->ue_end_date));
        }

        //echo $dateofExam; die();


        tcpdf();
        $obj_pdf2 = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
        $obj_pdf2->SetCreator(PDF_CREATOR);
        //$title = "";
        //$obj_pdf->SetTitle($title);
        //$obj_pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $exam_details->exam_title.' Admit Card');
        //$obj_pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $obj_pdf2->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        //$obj_pdf->SetDefaultMonospacedFont('helvetica');
        //$obj_pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        //$obj_pdf2->SetFooterMargin(PDF_MARGIN_FOOTER);
        $obj_pdf2->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $obj_pdf2->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        //$obj_pdf->SetFont('RaviPrakash-Regular', '', 12);
        //$obj_pdf->SetFont('helvetica', '', 10);
        //$obj_pdf->setFontSubsetting(false);
        $obj_pdf2->AddPage();
        //$obj_pdf->resetColumns();
        //$obj_pdf->setEqualColumns(2, 84);  // KEY PART -  number of cols and width
        //$obj_pdf->selectColumn();
        //ob_start();
        // we can have any view part here like HTML, PHP etc
        //echo '<div style="float:left;"><h1>'.$exam_name.'</h1></div>';
        $html = '';
        //$html .='<table><tr width="100%"><td width="50%"><div  style=""><img src="'.$src2.'" width="100" height="100" alt="candidate_image" style="margin-bottom:30px;" /></div></td><td>'.'<h3>'.$this->user_model->get_user_name().'</h3>'.''.$exam_details->exam_description.'</td></tr></table>';


        //$html .= '<div  style=""><img src="'.$src.'" alt="barcode" style="    margin-bottom:30px;" /></div>';

        //$content = ob_get_contents();
        //var_dump($content);die;
        //ob_end_clean();

        $tableCellStyleData=" 
             
              vertical-align: inherit; padding:6px !important; border-style: none !important;";

        $html = '<html >
<body>

<div style=" text-align:center; border:1px #000 solid;">
   <table cellspacing="7"  >
   <tbody>
      <tr >
         <td width="98.5%"  align="center" bgcolor="#000000" style="font-size:18px;"> <font color="#ffffff" >Admit Card</font></td>
      </tr>
      <tr style="'.$tableCellStyleData.'">
         <td    width="30%" >
         
            <img src="'.$filePathUserIMage.'" width="100" height="100" alt="candidate_image"  />
          
         </td>
         <td   width="70%" height="100" align="right">
         <table>
         <tbody>
         <tr>
         <td style="font-size:10px;">
            <table>
                <tbody >
                    <tr>
                    <td width="60%" align="left">
                       
                         <b> Candidate ID</b>
                    </td>
                    
                    <td align="left">
                           <b>: '.$logged_in_user->user_login.'</b>
                    </td>
                    </tr>
                    <tr>
                    <td width="60%" align="left">
                        <b> NID or Smart Card</b>
                    </td>
                    
                    <td width="100%" align="left">
                          <b>: </b>
                    </td>
                    </tr>
                    <tr>
                    <td width="60%" align="left">
                        <b> Exam Name:</b>
                    </td>
                    
                    <td width="100%" align="left">
                           <b>: '.$exam_details->exam_title.' </b>
                    </td>
                    </tr>
                    <tr>
                    <td width="60%" align="left">
                       <b> Exam Id:</b>
                    </td>
                    
                    <td align="left">
                            <b>: '.$exam_details->id.' </b>
                    </td>
                    </tr>
                    <tr>
                    <td width="60%" align="left">
                        <b> Exam Center</b>
                    </td>
                    
                    <td width="100%" align="left">
                           <b>: '.$venues.'</b>
                    </td>
                    </tr>
                    <tr>
                    <td width="60%" align="left">
                        <b> Exam Location</b>
                    </td>
                    
                    <td width="100%" align="left">
                           <b>: '.$venuesLocation.'</b>
                    </td>
                    </tr>
                    
                    
                    <tr>
                    <td width="60%" align="left">
                         <b> Date</b>
                    </td>
                    
                    <td width="100%" align="left">
                           <b>: '.$dateofExam.'</b>
                    </td>
                    </tr>
                </tbody>
            
            </table>
            
            
            </td>
            <td>
           
            
            </td>
            </tr>
            </tbody>
            </table>
         </td>
      </tr>
      
      <tr>
      <td width="30%">
      <table>
        <tr>
            <td><img height="30" src="'.$filePathUserSignature.'" alt="barcode"  /></td>
        </tr>
        <tr>
            <td><img height="100" src="'.$QRfilePath.'" alt="barcode"  /></td>
        </tr>
      </table>
      </td>
      <td width="30%">
      
</td>
            <td align="left" style="font-size: 10px;">
            <p>
            <img src="'.$filePathUserSignatureLam.'" height="30" alt="barcode" />
            <br><b>'.$signaturenAdmitHeadD->user_first_name.' '.$signaturenAdmitHeadD->user_last_name.'</b></p>
            <p><b>'.$signaturenAdmitHeadD->designation.'</b></p>
            <p><b>'.$signaturenAdmitHeadD->department.'</b></p>
            <p><b>BRAC Bank Limited</b></p>
            
      
        </td>
      </tr>
      </tbody>
   </table>
   
   </div>
   
   
   <h3 align="center">Exam Centre Instructions</h3>
   <p style="font-size: 10px;" align="center">Please read the following instructions carefully:</p>
   <div align="center">
   <ul style="font-size: 10px;font-family: "Franklin Gothic Medium", "Franklin Gothic", "ITC Franklin Gothic", Arial, sans-serif;">
      <li> The Admit Card must be presented for verification in the exam venue along with at least one original (not photocopy or scanned copy) valid photo identification proof (Example: NID, Smart Card, Passport)</li>
      <li> This admit card is valid only if the candidates photograph and signature images are readable. To ensure this, please take a clear print out (preferably color printout) of the admit card in A4 sized paper. </li>
      <li> Candidates must report at the examination venue minimum <b> 30 minutes </b> before scheduled start of the examination. </li>
      <li> Candidates will be permitted to appear for the examination only after their credentials are verified by Centre officials.</li>
      <li> Candidates will be permitted to occupy their allocated seats <b>20 minutes</b> before the scheduled start of the examination. </li>
      <li> In case of <b>ONLINE EXAMINATION</b>, candidates can login and start reading the necessary instructions <b>15 minutes</b> before start of the examination. The automated timer will start counting the time, the moment you enter the examination. The examination will close automatically when the time runs out. You must submit the answers before the time expires. </li>
      <li> In case of <b>WRITTEN EXAMINATION</b>, you can be provided with OMR based answer scripts to mark your answers. You are requested to fill out the form carefully and not to fold the OMR Sheet or attach anything with it. To answer all the questions, use black ball pen only. </li>
      <li> Candidates will not be permitted to leave the examination hall prior the examination ends or
         instructed by the invigilator to do so.
      </li>
      <li> Personal calculator, Mobile Phones or any other electronic devices are not allowed un the examination hall. Candidates should not bring any additional documents into the examination hall. BRAC Bank authority will not be responsible for safe keeping of candidate’s personal belongings.</li>
   </ul>
   </div>
   
   
</body>
</html>';

        //echo $html;die;
        $obj_pdf2->writeHTML($html, true, false, true, false, '');
        $obj_pdf2->SetDisplayMode('fullpage');
        ob_end_clean();
        $obj_pdf2->Output('Admit_card.pdf', 'I');
    }

    public function get_survey($status = 'open')
    {
        $user_id = $this->session->userdata('logged_in_user')->id;

        $res = $this->survey_model->get_survey_for_specific_user($user_id, $status);
        $survey_html='';
        if($res){
            $survey_html = $this->generate_survey_html($res);
        }
        return $survey_html;
    }
    
    private function generate_survey_html($list)
    {
        $list_html = '';
       // print_r_pre($list);
        if (count($list) > 0) {
            $list_html .= '<ul class="list">';

            for ($i=0; $i<count($list); $i++) {
                $blink_text = null;
                $blink_class = null;
                $date_difference = (strtotime(date("Y-m-d H:i:s")) - strtotime($list[$i]->added) );
                if( ($list[$i]->status == 'open') && $date_difference > 0 && $date_difference < (48*3600) ){
                    $blink_text = '<img src="'.site_url().'assets/images/blink_image/Green_16x16.gif" alt="New" height="20" width="20">';
                    $blink_text .= '<strong><sup class="blink_text_green">New</sup></strong>';
                    $blink_class = 'blink_text_green';
                }


                $url ='#';
                if($list[$i]->status=="open")
                {
                    $url = base_url('survey/survey_list/'.(int)$list[$i]->survey_id);
                }
                else
                {
                    $url = "javascript:void();";
                }
                
               // print_r_pre($url);
                
                $list_html .= '<li>';
                    $list_html .= '<a href="'.$url.'" data-status="'. $list[$i]->status .'"><p>'. $blink_text. $list[$i]->survey_title .'</p></a>';
                $list_html .= '</li>';
            }

            $list_html .= '</ul>';
        } else {
            $list_html .= lang('home_message_noitemsfound');
        }

        return $list_html;
    }
    
    private function generate_survey_tree_html($list)
    {
        $list_html = '';
        $list_html .= '<ul>';
        if (count($list) > 0) {
            for ($i=0; $i<count($list); $i++) {
                $list_html .= '<li class="noLink folder">';
                    $list_html .=  $list[$i]->survey_title ;
                    
                    $question = $this->survey_question_model->get_questions_by_survey($list[$i]->id);
                    if($question){
                        $list_html .= '<ul>';
                        foreach($question as $k=>$v){
                            $list_html .= '<li><a href="#" class="ajax-question-link" data-id="'. (int)$v->id .'">'. $v->ques_text .'</a></li>';                            
                        }
                        $list_html .= '</ul>';
                    }
                $list_html .= '</li>';
            }

            $list_html .= '</ul>';
        } else {
            $list_html .= lang('home_message_noitemsfound');
        }

        return $list_html;
    }




    public function page_loading_time($loadTime,$page)
    {
        $user = $this->session->userdata('logged_in_user');
        $loadingTime = $loadTime; //$this->input->post('loadingTime');
        $username = $user->user_login;
        $userid = $user->id;
        $userlogin_time = $user->user_last_failed_login_time;
        $array = array('user_id'=>$userid,'user_name'=>$username,'loading_time'=>$loadingTime,'page'=>$page);
        if($this->content_model->page_loading_time($array)){
            echo json_encode(array('status'=>'success'));
        }else{
            echo json_encode(array('status'=>'failed'));
        }
    }


    function lang($line, $id = '')
    {
        $CI =& get_instance();
        $line = $CI->lang->line($line);

        if ($id != '')
        {
            $line = '<label for="'.$id.'">'.$line."</label>";
        }

        return $line;
    }


}

/* End of file home.php */
/* Location: ./application/controllers/home.php */