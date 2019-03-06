<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Question_set extends MY_Controller
{
  var $current_page = "exam";
  var $set_list = array();
  var $set_list_filter = array();
  var $exam_status_list_filter = array();
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
    $this->load->model('category_model');
    $this->load->model('exam_model');
    $this->load->model('question_model');
    $this->load->model('question_set_model');

    $this->load->model('global/select_global_model');
    
    $this->load->model('global/delete_global_model');
    $this->load->model('global/update_global_model');
    $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');







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
     * Display paginated list of sets
     * @return void
     */
      /**
     * Display paginated list of questions
     * @return void
     */
      public function index()
      {
        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Manage Question Set View'));
        // set page specific variables
        $page_info['title'] = 'Manage Question Sets'. $this->site_name;
        $page_info['view_page'] = 'administrator/manage_ques_set_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();

        // gather filter options

         //print_r_pre($this->session->flashdata('filter_set_title'));

        $filter_set = array();
        if ($this->session->flashdata('filter_set_title')) {
          $this->session->keep_flashdata('filter_set_title');
          $filter_set_title = $this->session->flashdata('filter_set_title');
          $this->form_data->filter_set_title = $filter_set_title;
          $filter_set['filter_set_title']['field'] = 'filter_set_title';
          $filter_set['filter_set_title']['value'] = $filter_set_title;
        }


        $page_info['filter'] = $filter_set;



        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

        $record_result = $this->question_set_model->get_paged_question_sets($per_page, $page_offset, $filter_set);


        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];




        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/question_set";
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_info['pagin_links'] = $this->pagination->create_links();

        if ($records) {
            // customize and generate records table
          $tbl_heading = array(
            '0' => array('data'=> 'ID', 'min-width' => '30%'),
            '1' => array('data'=> 'Set Name', 'min-width' => '30%'),
            '2' => array('data'=> 'No of Question', 'class' => 'center', 'width' => '70'),
            '3' => array('data'=> 'Question Limit', 'class' => 'center', 'width' => '50'),
            '4' => array('data'=> 'Total Mark','class' => 'center', 'width' => '50'),
            '5' => array('data'=> 'Negative Mark Per Question', 'class' => 'center', 'width' => '100'),
            '6' => array('data'=> 'Status'),
            '7' => array('data'=> 'Action', 'class' => 'center', 'width' => '200')
          );
          $this->table->set_heading($tbl_heading);

          $tbl_template = array (
            'table_open'          => '<table class="table table-bordered table-striped" id="question_set_tbl" style="margin-bottom: 0;">',
            'table_close'         => '</table>'
          );
          $this->table->set_template($tbl_template);

          for ($i = 0; $i<count($records); $i++) {
            $status='';
            if($records[$i]->set_status==1 )
            {
              $status=anchor('administrator/question_set/change_status/'. $records[$i]->id.'/'.$records[$i]->set_status, 'Active', array('title'=>'Change Status','class'=>'btn btn-success'));
            }
            else{
              $status=anchor('administrator/question_set/change_status/'. $records[$i]->id.'/'.$records[$i]->set_status, 'Inactive', array('title'=>'Change Status','class'=>'btn btn-warning'));
            }

            $action_str = '';
            $status_str = '';

            $action_str .= anchor('administrator/question_set/questionSetPreview/'. $records[$i]->id, '<i class="icon-eye-open"></i> ', array('target'=>'_blank','title'=>'Preview','class'=>'btn btn-success'));
              $action_str .= '&nbsp;&nbsp;&nbsp;';
              if(!isSystemAuditor())
            $action_str .= anchor('administrator/question_set/edit/'. $records[$i]->id, '<i class="icon-edit"></i> ', array('title'=>'Edit','class'=>'btn btn-info'));
            $action_str .= '&nbsp;&nbsp;&nbsp;';
              $action_str .= anchor('administrator/question_set/export_answer/'. $records[$i]->id, '<i class="icon-download"></i> ', array('title'=>'Export Answer','class'=>'btn btn-success'));
              $action_str .= '&nbsp;&nbsp;&nbsp;';


            $tbl_row = array(
              '0' => array('data'=> $records[$i]->id, 'min-width' => '30%'),
              '1' => array('data'=> $records[$i]->name, 'min-width' => '30%'),
              '2' => array('data'=> (int)$records[$i]->question_total),
              '3' => array('data'=> $records[$i]->set_limit, 'min-width' => '30%'),
              '4' => array('data'=> $records[$i]->total_mark),
              '5' => array('data'=> $records[$i]->neg_mark_per_ques, 'min-width' => '30%'),
              '6' => array('data'=> $status),

              '7' => array('data'=> $action_str, 'class' => 'center', 'width' => '50')
            );
            $this->table->add_row($tbl_row);
          }

          $page_info['records_table'] = $this->table->generate();

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




     /**
     * Display add qustion set form
     * @return void
     */
     public function add()
     {
      $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add Question Set View'));
      // set page specific variables
      $page_info['title'] = 'Add Question Set'. $this->site_name;
      $page_info['view_page'] = 'administrator/create_question_set_view';
      $page_info['message_error'] = '';
      $page_info['message_success'] = '';
      $page_info['message_info'] = '';
      $page_info['is_edit'] = false;

      $this->_set_fields();
      $this->_set_rules();

      $this->form_data->set_name = '';
      $this->form_data->set_id = '';
      $page_info['questionSet']=null;


      $page_info['quesPool'] = $this->select_global_model->select_array('question_pull');

        //var_dump($page_info['quesPool']);die;

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



    public function add_question_set()
    {

$update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Add Question Set'));
      $page_info['title'] = 'Add Question Set' . $this->site_name;
      $page_info['view_page'] = 'administrator/create_question_set_view';
      $page_info['message_error'] = '';
      $page_info['message_success'] = '';
      $page_info['message_info'] = '';
      $page_info['is_edit'] = false;

      $this->_set_fields();
      $this->_set_rules();

      if ($this->form_validation->run() == FALSE) {

        $this->load->view('administrator/layouts/default', $page_info);

      } else {

        $sessdata = $this->session->userdata('logged_in_user');
        $fechData = array();
        $set_name = $this->input->post('set_name');
        $selected_pool = $this->input->post('selectedPool');
        if (empty($set_name) || empty($selected_pool)) {
          $this->session->set_flashdata('message_error', 'Some fields are empty!');
          redirect('create_question_pool');
        }

        $data = array('name' => $set_name);


        $setid = $this->select_global_model->select_array('question_set', array('name' => $set_name));
        if (!$setid) {
          $insert_id=$this->insert_global_model->globalinsert('question_set', array('name' => $set_name, 'created_by' => $sessdata->id));
          if ($selected_pool) {
            foreach ($selected_pool as $key => $value) {
              $fechData[$key]['question_set_id'] = $insert_id;
              $fechData[$key]['question_pool_id'] = $value;
              $fechData[$key]['created_by'] = $sessdata->id;
            }
          }
          //$res = (int)$this->question_set_model->add_question_set($data);

          if ($this->insert_global_model->globalinsertbatch('exm_question_set_pool_map', $fechData)) {
            $this->session->set_flashdata('message_success', 'Mapping successful.');
            redirect('administrator/question_set/add/');
          } else {
            $this->session->set_flashdata('message_error', 'Mapping failed!');
            redirect('administrator/question_set/add/');
          }


        } else {
          $this->session->set_flashdata('message_error', 'Question set name already exists!');
          redirect('administrator/question_set');
        }

      }
    }


    public function edit()
    {
      $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Edit Question Set View'));
      // set page specific variables
      $page_info['title'] = 'Edit Question Set'. $this->site_name;
      $page_info['view_page'] = 'administrator/create_question_set_view';
      $page_info['message_error'] = '';
      $page_info['message_success'] = '';
      $page_info['message_info'] = '';
      $page_info['is_edit'] = true;

      // prefill form values
      //$set_id = (int)$this->uri->segment(4);
      //$question_set = $this->question_set_model->get_question_set($set_id);


      $getData = $this->uri->segment(4);
      if ($getData) {
        $page_info['questionSet'] = $this->select_global_model->select_array('question_set',array('id'=>$getData));

        $page_info['setData'] = $this->question_set_model->mappedSetPool(array('TB.question_set_id'=>$getData));

      }

        //print_r_pre($page_info['setData']);die;

      $page_info['exaCategory'] = $this->select_global_model->select_array('exm_categories');
//print_r_pre($page_info['questionSet'][0]['random_qus']);die;




      $this->_set_rules();

        //var_dump($question_set);die;




      if ($this->session->flashdata('message_success'))
      {
        $page_info['message_success'] = $this->session->flashdata('message_success');
      }
      if ($this->session->flashdata('message_error'))
      {
        $page_info['message_error'] = $this->session->flashdata('message_error');
      }

        // load view
      $this->load->view('administrator/layouts/default', $page_info);
    }


    public function getQuestionSetByExam($exam_id){

      $question_set=$this->question_set_model->get_question_set_by_examid($exam_id);

      echo json_encode($question_set);
    }



    public function export_answer(){

      $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Export Answer'));

        $set_id = $this->uri->segment(4);
        $questions= $this->question_set_model->get_question_by_ques_set_id($set_id);
        //print_r_pre($questions);die;

        //$record_result = $this->question_set_model->get_question_answer_by_questionsetId($set_id);

        //$records = $record_result['result'];

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=Question Set Answer -".date('Y-m-d').".csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        $file = fopen('php://output', 'w');

        fputcsv($file, array(
            'Question',
            'Answer'
        ));


        $ques_info = array();

        $index =0;
        for($i=0; $i<count($questions); $i++){




            $char = 'A';
            if($questions[$i]->ques_type=='mcq') {
                $ques_info[$index]['question']=$questions[$i]->ques_text;
                $ques_info[$index]['answer'] ='';
                foreach ($questions[$i]->ques_choices as $keyy=>$vaue) {
                    //$strHtml .='&nbsp;&nbsp;&nbsp;' . $char . ') ';
                    if($vaue['is_answer']==1)
                    {
                        if($keyy!=0)
                        {
                            $ques_info[$index]['answer'] .=', ';
                        }
                        $ques_info[$index]['answer'] .=$vaue['text'];
                    }

                    //$strHtml .='<br>';
                    //$char++;
                }

            }
            $index++;
        }

        /*

        foreach ($records as $key => $value) {
            $ques_info[$key]['question']=$records[$key]->ques_text;
            $ques_info[$key]['answer'] ='';
            var_dump($records[$key]->ques_choices[0]);die;
            foreach ($records[$key]->ques_choices as $key2 => $value2) {
                if($records[$key]->ques_choices[$key2]['answer']==1)
                $ques_info[$key]['answer'].= $records[$key]->ques_choices[$key2]['text'].', ';
            }
        }
        */

        //var_dump($ques_info);die;

        foreach ($ques_info as $value) {
            $rowD= $value;
            fputcsv($file, $rowD);
        }
        exit();



    }


    public function printHardcopy(){

      $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Download Question Set Hardcopy'));

      ob_start();

      $this->load->helper('pdf_helper');

      $question_set_id= $this->uri->segment(4);
      $exam_id= $this->uri->segment(5);
      $exam_name= $this->uri->segment(6);

      $exam = $this->exam_model->get_exam($exam_id);
        //var_dump($exam_details);die;
      //$src =base_url('assets/barcode/test_1D.php?text='.substr(trim($exam->exam_title),0,3).$exam_id.$question_set_id);

      $src = base_url('assets/qrcode/index.php?data='.substr(trim($exam->exam_title),0,3).$exam_id.$question_set_id);

       //var_dump($question_set_id);die;


     

      //print_r_pre($questions);

        $this->logged_in_user = $this->session->userdata('logged_in_user');

        $user = $this->user_model->get_user($this->logged_in_user->id);
        //print_r_pre($user); die();
        $page_info['user'] = $user;


        //$exam_id = (int)$this->uri->segment(4);
        //$set_id = (int)$this->uri->segment(5);

        $getExamInfo=$this->select_global_model->FlyQuery(array('id'=>$exam_id),'exams','first');
        $geSetInfo=$this->select_global_model->FlyQuery(array('id'=>$question_set_id),'question_set','first');
        $geSetListInfo=$this->select_global_model->FlyQuery(
            array('SELECT qsm.question_id,qsm.question_mark,qsm.is_mandatory,q.ques_text,q.ques_choices,q.ques_type
  FROM exm_question_set_question_map qsm 
  LEFT JOIN exm_questions q ON qsm.question_id=q.id
  WHERE qsm.question_set_id='.$question_set_id)
        );

        if($question_set_id)
        {
            $examSetInfo = $this->exam_model->get_Set_Info($question_set_id);

        }


        $examVenue = $this->exam_model->get_venue($exam_id);
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

        $exam_name = $exam->exam_title;



        $exam_time =$exam->exam_time;


        //print_r_pre($geSetListInfo); die();



        $this->setmark = (int)$geSetInfo->total_mark;
        $this->totalqus = (int)$this->session->userdata('totalqus');

        $getExamInfo->exam_score = $this->setmark;



      tcpdf();

      //ob_start();
      $obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
      $obj_pdf->SetCreator(PDF_CREATOR);
      $title = $exam->exam_title;
      $obj_pdf->SetTitle($title);
      //$obj_pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, '');

      //$obj_pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
      $obj_pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
      $obj_pdf->SetDefaultMonospacedFont('helvetica');
      //$obj_pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
      //$obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
      //$obj_pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $obj_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        //$obj_pdf->SetFont('RaviPrakash-Regular', '', 12);
      $obj_pdf->SetFont('helvetica', '', 10);
      $obj_pdf->setFontSubsetting(false);
      $obj_pdf->AddPage();



      $strHtml='';
$strHtml .='
<html>
   <head><title>PDF</title>
   
   </head>

   <body>
      ';

        $strHtml .='
        <div id="running-exam">


    <div class="exam-info">

        <!-- custom form start -->
        <table border="0" cellpadding="5" width="100%" align="center">
            <tbody>
            <tr>
                <td width="33%"></td>
                <td width="33%" align="center"><img  height="100" src="'.base_url("assets/images/brac_bank.png").'"></td>
                <td width="33%" align="right">Set Code :'.$question_set_id.'</td>
            </tr>
            </tbody>
        </table>
        <h3 style="border: 3px #ccc solid; padding-top: 10px; padding-bottom: 10px;" align="center">
            DO NOT OPEN THE QUESTIONNAIRE UNTIL YOU ARE DIRECTED TO DO SO
        </h3>
        <table border="0">
    <tr style="line-height: 20px;" > 
    <td></td>
    </tr>
    </table>
        <table border="0" width="100%" align="center">
            <tbody>
            <tr>
                <td width="50%" align="left"><b>Time :</b> '.date("H:i:s",strtotime($exam_time)).'</td>
                <td width="50%" align="right"><b>Total Marks</b> :';
        if($this->setmark){ $strHtml .=$this->setmark; }else{ $strHtml .= 0; }
        $exam_nop=$exam->exam_nop?$exam->exam_nop:'________________________';
        $strHtml .='</td>
            </tr>
            </tbody>
        </table>
        <h4 style="padding-top: 5px; padding-bottom: 5px;" align="center">
            Written Examination for '.$exam_nop.'
        </h4>
        <table border="0" width="100%" align="center">
            <tbody>
            <tr>
                <td width="50%" align="left" valign="middle">
                <br><br>
                    <table  style="border: 1px #ccc solid;" cellpadding="5" cellspacing="0" width="100%" align="left">
                        <tbody>
                        <tr>
                            <td  style="border: 1px #ccc solid;" width="50%">
                                <strong>Exam Name</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <strong>'.$exam->exam_title.'</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Exam ID</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <strong>'.$exam->id.'</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Exam Centre</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <strong>'.$venues.'</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Exam Location</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <strong>'.$venuesLocation.'</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Candidate ID</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <strong></strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>NID/Passport No.</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                '.$user->nid_passport_no.'
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">

                                <strong>'.html_entity_decode('Candidates Signature').'</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">';


        $strHtml .='</td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Date</strong>
                            </td>
                            <td style="border: 1px #ccc solid;"></td>
                        </tr>
                        </tbody>
                    </table>



                    <table cellpadding="5" cellspacing="0" width="90%" align="center">
                        <tbody>
                        <tr>
                            <td  valign="middle" align="center">
                                <br><br><br><br>
                                <strong>For Official Use Only</strong>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </td>
                <td width="50%" align="right" valign="top">
                    <table cellpadding="5" cellspacing="1" width="100%" align="center">
                        <tbody>
                        <tr>
                            <td width="40%">
                            </td>
                            <td width="30%" style="height: 100px;" valign="middle" align="center">';

                                  $UpFilePath=base_url().'/assets/qrcode/index.php?data='.substr($exam->exam_title,0,2).$exam->id;
                                    $GenFileQR=file_get_contents($UpFilePath);
                                    $filePath=base_url().'/assets/qrcode/'.$GenFileQR;
                                

                                $strHtml .= '<img src ="'.$filePath.'"  width="125" height="120">
                            </td>
                            <td width="30%" style=" height: 100px;" valign="middle" align="center">';

                                
                                    $filePath=base_url().'/assets/images/avatar.png';
                                

                                $strHtml .= '<img src ="'.$filePath.'"  width="125" height="120">
                            </td>
                            <td width="3%">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    
    <table border="0">
    <tr style="line-height: 20px;" > 
    <td></td>
    </tr>
    </table>
                    
                    <table cellpadding="5" cellspacing="1" width="100%" style="padding:20px;" align="center">
                        <tbody>
                        <tr>
                        <td  width="10%">
                        </td>
                            <td style="border: 1px #ccc solid; padding:20px;" width="90%">
                                <p>
                                    <strong>
                                        <u>Instructions to Candidates</u>
                                    </strong>
                                </p>
                                ';

                                
                                    if(!empty(trim($exam->exam_instructions)))
                                    {
                                        $exam_instructions=explode('->',$exam->exam_instructions);
                                        if(substr(trim($exam->exam_instructions),0,2)=="->")
                                        {
                                            $exam_instructions=explode('->',substr(trim($exam->exam_instructions),2,200000));
                                        }

                                        

                                        $strHtml .= '<ul>';
                                       
                                            foreach($exam_instructions as $ei):
                                              $strHtml .= '<li>'.$ei.'</li>';
                                            endforeach;
                                            
                                        $strHtml .= '</ul>';

                                        
                                    }
                                    else
                                    {
                                        $strHtml .= 'Not Mention.';
                                    }
                                  

                                    $strHtml .= '
                                
                            </td>

                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        
        <table border="0">
    <tr style="line-height: 20px;" > 
    <td></td>
    </tr>
    </table>
        <table  cellspacing="0" width="100%" align="center">
            <tbody>
            <tr>
                <td width="80%">
                    <table  style="border: 1px #ccc solid;" cellpadding="5" cellspacing="0" width="100%" align="left">
                        <tbody>
                        <tr>
                            <td  style="border: 1px #ccc solid;" width="10%">
                                <strong>Sec.</strong>
                            </td>
                            <td style="border: 1px #ccc solid;" width="29%">
                                <strong>Segment</strong>
                            </td>
                            <td  style="border: 1px #ccc solid;" width="29%">
                                <strong>Allocated Mark</strong>
                            </td>
                            <td  style="border: 1px #ccc solid;" width="29%">
                                <strong>Obtained Mark</strong>
                            </td>
                        </tr>';

                        $totalSetMark=0;
                        if(isset($examSetInfo) && !empty($examSetInfo))
                        {
                            foreach ($examSetInfo as $key=>$sinfo) {

                                $strHtml .='<tr>
                                    <td  style="border: 1px #ccc solid;">'.
                                        ($key+1)
                                    .'</td>
                                    <td style="border: 1px #ccc solid;">'.
                                        $sinfo['cat_name'].'
                                    </td>
                                    <td  style="border: 1px #ccc solid;">'.
                                        $sinfo['summary_row'].
                                    '</td>
                                    <td  style="border: 1px #ccc solid;">

                                    </td>
                                </tr>';

                                $totalSetMark+=$sinfo['total_mark'];
                            }
                        }



            $strHtml .='</tbody>
                    </table>
                </td>
                <td width="20%" style="border: 1px #ccc solid;">

                </td>
            </tr>
            <tr>
                <td>
                    <table   cellspacing="0" width="100%" align="left">
                        <tbody>
                        <tr>
                            <td align="center" width="39%">
                                <strong>Total</strong>
                            </td>
                            <td width="29%">
                                <strong>'.$totalSetMark.'</strong>
                            </td>
                            <td width="29%">

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
                <td  align="center" width="20%">
                    <b>Invigilator&#39;s PIN &amp; Signature</b>
                </td>
            </tr>
            </tbody>
        </table>

        

    


    </div>';







            $strHtml .='</div>

        </div>

<div class="mask-layer"></div>
<!--running-exam ends-->

';



      $strHtml .= '<div style="page-break-before:always"></div>
      <h4 align="center">Questions Paper </h4><br><br>';

       $questionsCateWithMark= $this->question_set_model->get_question_category_by_ques_set_id($question_set_id);
      //questionsCateWithMark
      $slqsPart=1;
      foreach($questionsCateWithMark as $catMark):

      $strHtml .='<table border="0" width="100%">
                    <tbody>
                      <tr>
                        <td width="90%" style="border-bottom:2px #000 solid;">
                          <b>'.$slqsPart.'. '.$catMark->cat_name.'</b>
                        </td>
                        <td>
                          <b>'.number_format($catMark->total,2).'</b>
                        </td>
                      </tr>
                    </tbody>    

                  </table><br><br>';

        $questions= $this->question_set_model->get_question_by_ques_cat_set_id($question_set_id,$catMark->category_id);

        //print_r_pre($questions);

        for($i=0; $i<count($questions); $i++){
            $quesNo=$i+1;
            $strHtml .=  $quesNo. ') ' .$questions[$i]->ques_text . '&nbsp;('.$questions[$i]->mark.')';
            $strHtml .='<br>';

            $char = 'A';
            if($questions[$i]->ques_type=='mcq') {
              $strHtml .='<br>';
              foreach ($questions[$i]->ques_choices as $vaue) {
                $strHtml .='&nbsp;&nbsp;&nbsp;' . $char . ') ';
                $strHtml .=$vaue['text'];
                $strHtml .='<br>';
                $char++;
              }
              $strHtml .='<br>';
              $strHtml .='<br>';
            }
            else
            {
              $strHtml .='<div style="page-break-before:always"></div>';
            }
          }

          $slqsPart++;
      endforeach;

      

      $strHtml .='</div></body></html>';



      //echo $strHtml; die();
       // var_dump($strHtml);die;

      $obj_pdf->writeHTML($strHtml, true, false, true, false, '');
      $obj_pdf->SetDisplayMode('fullpage');
      ob_end_clean();
      $obj_pdf->Output(str_replace(' ','_',trim($title)).'_set_'.$question_set_id.'.pdf', 'I');
    }







    public function questionSetPreview(){

      $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Question Set Priview'));

        ob_start();

        $this->load->helper('pdf_helper');

        $question_set_id= $this->uri->segment(4);

        

        $question_set_details= $this->question_set_model->get_question_set($question_set_id);
        $rand=$question_set_details->random_qus;
        $randID=0;
        if(!empty($rand))
        {
            if($rand=="random")
            {
                $randID=1;
            }
        }
        $questions= $this->question_set_model->get_question_by_ques_set_id($question_set_id);
        //print_r($question_set_details);die;
        //echo $randID; die();
        tcpdf();

        //ob_start();
        $obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
        $obj_pdf->SetCreator(PDF_CREATOR);

        $obj_pdf->SetTitle($question_set_details->name);
        //$obj_pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, '');

        //$obj_pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $obj_pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $obj_pdf->SetDefaultMonospacedFont('helvetica');
        //$obj_pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $obj_pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        //$obj_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        //$obj_pdf->SetFont('RaviPrakash-Regular', '', 12);
        $obj_pdf->SetFont('helvetica', '', 10);
        $obj_pdf->setFontSubsetting(false);
        $obj_pdf->AddPage();



        $strHtml='';


        $strHtml .= '<h4 align="center">Questions Paper </h4><br><br>';

       $questionsCateWithMark= $this->question_set_model->get_question_category_by_ques_set_id($question_set_id);
      //questionsCateWithMark
      $slqsPart=1;
      foreach($questionsCateWithMark as $catMark):

      $strHtml .='<table border="0" width="100%">
                    <tbody>
                      <tr>
                        <td width="90%" style="border-bottom:2px #000 solid;">
                          <b>'.$slqsPart.'. '.$catMark->cat_name.'</b>
                        </td>
                        <td>
                          <b>'.number_format($catMark->total,2).'</b>
                        </td>
                      </tr>
                    </tbody>    

                  </table><br><br>';

        $questions= $this->question_set_model->get_question_by_ques_cat_set_id($question_set_id,$catMark->category_id,$randID);

        //print_r_pre($questions);

        for($i=0; $i<count($questions); $i++){
            $quesNo=$i+1;
            $strHtml .=  $quesNo. ') ' .str_replace('"',"&#34;",$questions[$i]->ques_text) . '&nbsp;('.$questions[$i]->mark.')';
            $strHtml .='<br>';

            $char = 'A';
            if($questions[$i]->ques_type=='mcq') {
              $strHtml .='<br>';
              foreach ($questions[$i]->ques_choices as $vaue) {
                $strHtml .='&nbsp;&nbsp;&nbsp;' . $char . ') ';
                $strHtml .=$vaue['text'];
                $strHtml .='<br>';
                $char++;
              }
              $strHtml .='<br>';
              $strHtml .='<br>';
            }
            else
            {
              $strHtml .='<div style="page-break-before:always"></div>';
            }
          }

          $slqsPart++;
          $strHtml .='<div style="page-break-before:always"></div>';
      endforeach;
        $strHtml .='</body></html>';

        //echo $strHtml;

        //exit();

        //echo 'hi';

        //$content = ob_get_contents();
        //var_dump($content);die;
        //ob_end_clean();

        $obj_pdf->writeHTML($strHtml, true, false, true, false, '');
        //$obj_pdf->SetDisplayMode('fullpage');
        ob_end_clean();
        $obj_pdf->Output(str_replace(' ','_',trim($question_set_details->name)).'.pdf', 'I');
    }










    public function update_question_set()
    {
      $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update Question Set'));
      if(!in_array('create_question_pool', $this->session->userdata('user_privilage_name'))){
        redirect('administrator/dashboard');
      }
      $sessdata = $this->session->userdata('logged_in_user');
      $fechData = array();
      $ids = $this->input->post('id');
      $setid = $this->input->post('setid');
      $set_name = $this->input->post('set_name');
      $set_limit = $this->input->post('set_limit');
      $total_mark = $this->input->post('total_mark');
      //$neg_mark_per_ques = $this->input->post('neg_mark');
      $random = $this->input->post('random');




      $setids = $this->select_global_model->select_array('exm_question_set',array('id !='=>$setid,'name'=>$set_name));
      if(!$setids){
        $this->update_global_model->globalupdate('exm_question_set',array('id'=>$setid),array('name'=>$set_name,'set_limit'=>$set_limit,'total_mark'=>$total_mark,'updated_by'=>$sessdata->id,'updated_at'=>date('Y-m-d H:i:s'),'random_qus'=>$random));

        $this->delete_global_model->globaldelete('exm_question_set_question_map',array('question_set_id'=>$setid));
        if($ids){
          foreach ($ids as $key => $value) {
            $fechData[$key]['question_set_id'] = $setid;
            $fechData[$key]['question_id'] = $value;
            $fechData[$key]['updated_by'] = $sessdata->id;
            if($this->input->post('is_mandatory_'.$value))
            {
              $fechData[$key]['is_mandatory'] = 1;
            }
            else{
              $fechData[$key]['is_mandatory'] = 0;
            }
            $fechData[$key]['question_mark'] = $this->input->post('mark_'.$value);
          }
        }
            //print_r_pre($fechData);
        if($this->insert_global_model->globalinsertbatch('exm_question_set_question_map',$fechData)){
          $this->session->set_flashdata('message_success', 'Mapping successful.');
          redirect('administrator/question_set/edit/'.$setid);
        }else{
          $this->session->set_flashdata('message_error', 'Mapping failed!');
          redirect('administrator/question_set/edit/'.$setid);
        }
      }else{
        $this->session->set_flashdata('message_error', 'Pull name already exists!');
        redirect('administrator/question_set/edit/'.$setid);
      }
    }



    public function filter()
    {

      $filter_set = $this->input->post('filter_set_title');

      if ($filter_set != '') {
        $this->session->set_flashdata('filter_set_title', $filter_set);
      }
      else 
      {
        $this->session->unset_userdata('filter_set_title');

      }

      redirect('administrator/question_set');

    }


     /**
     * Delete a Question Set
     * @return void
     */
     public function change_status()
     {
      $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Change Question Set Status'));
      $set_id = (int)$this->uri->segment(4);
      $status = (int)$this->uri->segment(5);
      if($status==1)
        $status=2;
      else
        $status=1;


      $res = $this->update_global_model->globalupdate('exm_question_set',array('id'=>$set_id),array('set_status'=>$status));

      if ($res > 0) {
        $this->session->set_flashdata('message_success', 'Question Set status updated successfully.');
      } else {
        $this->session->set_flashdata('message_error', $this->question_set_model->error_message .' Delete is unsuccessful.');
      }

      redirect('administrator/question_set');
    }


    public function getQuestionPool($value='')
    {
      $question = $this->select_global_model->Select_array('exm_question_pull',array('id'=>$value));
      echo json_encode($question);
    }



    private function _set_fields()
    {
      $this->form_data = new StdClass;
      $this->form_data->set_id = 0;


      $this->form_data->filter_set = [];
      $this->form_data->filter_set_title = '';


    }

    // validation rules
    private function _set_rules()
    {
      $this->form_validation->set_rules('set_name', 'Question Set Name', 'required|trim|xss_clean|strip_tags');

    }


  }