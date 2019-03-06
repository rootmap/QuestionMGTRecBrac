<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Exam_venue_mapping extends MY_Controller
{
    var $current_page = "exam";
    var $exam_list = array();
    var $user_group_list = array();
    var $user_team_list = array();
    var $user_list = array();
    var $tbl_exam_venue_mapping    = "exam_venue_map";
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
        $this->load->model('exam_venues/exam_venue_mapping_model');
        $this->load->model('global/insert_global_model');
        $this->load->model('global/delete_global_model');
        $this->logged_in_user = $this->session->userdata('logged_in_user');
        $this->load->library("pagination");
        $this->load->library('table');
        //print_r($this->logged_in_user); die;


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
    public function index()
	{

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Exam Venues Mapping View'));
        // set page specific variables
        $page_info['title'] = 'Exam Venues Mapping'. $this->site_name;
        $page_info['view_page'] = 'exam_venues/exam_venue_mapping_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';



        //$this->_set_fields();

        $page_info['exam_all'] = $this->exam_venue_mapping_model->get_exam_name();
        $page_info['venue_all'] = $this->exam_venue_mapping_model->get_venue_name();

        $exam_venue_mapping_data= $this->exam_venue_mapping_model->is_data_exists($this->tbl_exam_venue_mapping);

         $uri_segment =  $this->config->item('uri_segment');
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;

       //var_dump($page_offset);die();
        $per_page = $this->config->item('per_page');
        $record_result = $this->exam_venue_mapping_model->get_exam_venue_name_limit($per_page,$page_offset);
       
        //var_dump($per_page);die();

        $page_info['records'] = $record_result[0]['id'];
        $config = array();
        $config["base_url"] = base_url()."exam_venues/exam_venue_mapping/index";
        $config["total_rows"] = count($exam_venue_mapping_data);
        $config['per_page'] = $this->config->item('per_page');
        $this->pagination->initialize($config);
        $page_info['pagin_links'] = $this->pagination->create_links();


        // GENERATING TABLE
        if ($record_result) {

            $tbl_heading = array(
                '0' => array('data'=> 'ID'),
                '1' => array('data'=> 'EXAM NAME'),
                '2' => array('data'=> 'VENUE NAME'),
                '3' => array('data'=> 'ACTION', 'class' => 'center', 'width' => '80')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);
            $i=1;
            foreach ($record_result as $key4) {
                //print_r($key3); die();
                $exam_str = $key4['EXAM'];
                $venue_str = $key4['VENUE'];

                $actionE_str = '';
                $actionD_str = '';
                if(!isSystemAuditor())
                $actionE_str .= '<a data-evid="'.$key4['id'].'" data-mo_exam_all="'.$key4['exam_id'].'" data-mo_venue_all="'.$key4['venue_id'].'" data-toggle="modal" title="Edit" data-target="#myModal"><i class="icon-edit"></i></a>
                    ';
                if(!isSystemAuditor())
                $actionD_str.=anchor('exam_venues/exam_venue_mapping/exam_venue_delete/'.$key4['id'], '<i class="icon-trash"></i>', array('title'=>'Delete', 'onclick'=>'return confirm(\'Do you really want to delete this record?\')'));
                $tbl_row = array(
                    '0' => array('data'=> $i),
                    '1' => array('data'=> $exam_str),
                    '2' => array('data'=> $venue_str),
                    '3' => array('data'=> $actionE_str."&nbsp;&nbsp;".$actionD_str, 'class' => 'center', 'width' =>'100')
                );
                $this->table->add_row($tbl_row);
                $i++; 
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

    public function do_mapping()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Map Exam Venue'));
        // set page specific variables
        $page_info['title'] = 'Exam Venues Mapping'. $this->site_name;
        $page_info['view_page'] = 'exam_venues/exam_venue_mapping_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $userid = $this->logged_in_user->id;
        //var_dump($userid); die();

       // $this->_set_fields();
        //$this->_set_rules();


        $exam_id = (int)$this->input->post('exam_all');
        $venue_id = $this->input->post('venue_all');


        if(empty($exam_id) || count($venue_id)<0){
            $this->session->set_flashdata('message_error', 'Some fields are empty!');
            redirect('exam_venues/exam_venue_mapping');
        }

        $insertArray=[];
        foreach ($venue_id as $venue):
            $insertArray[]= array('exam_id'=>$exam_id, 'venue_id'=>$venue, 'created_by'=>$userid);
        endforeach;
        //var_dump($insertArray); die();
        
        $data_insert = $this->insert_global_model->globalinsertbatch($this->tbl_exam_venue_mapping, $insertArray);
        //print_r($insertArray); die();
        if($data_insert){
            $this->session->set_flashdata('message_success','Exam Venue Mapping successful.');
            redirect('exam_venues/exam_venue_mapping');
        }


        // constructing user ids
        
    }

    public function exam_venue_delete()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Delete Exam Venue'));
        $set_id = (int)$this->uri->segment(4);
        $res = $this->delete_global_model->globaldelete($this->tbl_exam_venue_mapping,array('id'=>$set_id));
       // print_r($res); die();
        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Exam Venue Deleted successfully.');
        } else {
            $this->session->set_flashdata('message_error', ' Delete is unsuccessful.');
        }
        
        redirect('exam_venues/exam_venue_mapping');
    }

    public function update_mapping()
    {

        $update_data = $this->insert_global_model->globalinsert($this->tbl_exam_users_activity,array('user_id'=>$this->logged_in_user->id,
            'activity_time'=>date('Y-m-d H:i:s'),'activity'=>'Update Venue Mapping'));
        $page_info['message_error']   = '';
        $page_info['message_success'] = '';
        $page_info['message_info']    = '';

        $session_user_id = $this->logged_in_user->id;
        //var_dump($session_user_id); die();
        $exam_id    = $this->input->post('mo_exam_all');
        $venue_id   = $this->input->post('mo_venue_all');
        $did         = $this->input->post('did');

        if(empty($exam_id || $venue_id)){
            $this->session->set_flashdata('message_error', 'Some fields are empty!');
            redirect('exam_venues/exam_venue_mapping');
        }
        $checkArray  = array('id !='=>$did,'exam_id'=>$exam_id, 'venue_id'=>$venue_id);
        $chk_data = $this->exam_venue_mapping_model->is_data_exists($this->tbl_exam_venue_mapping,$checkArray);
        if($chk_data){
            $this->session->set_flashdata('message_error', 'This Exam Venue Mapping already exists!');
            redirect('exam_venues/exam_venue_mapping');
        }
        $insertArray  = array('exam_id'=>$exam_id,'venue_id'=>$venue_id,'updated_by'=>$session_user_id, 'updated_at'=>date('Y-m-d H:i:s'));
        $data_insert = $this->exam_venue_mapping_model->update_mapping($this->tbl_exam_venue_mapping,array('id'=>$did),$insertArray);

        if($data_insert){
            $this->session->set_flashdata('message_success', 'Exam Venue Mapping update successful');
            redirect('exam_venues/exam_venue_mapping');
        }else{
            $this->session->set_flashdata('message_error', 'Exam Venue Mapping update fail');
            redirect('exam_venues/exam_venue_mapping');
        }
    }


    // set empty default form field values
	private function _set_fields()
	{
		$this->form_data = new StdClass;
		$this->form_data->exam_id = 0;
        $this->form_data->exam_id_hidden = 0;
        $this->form_data->ue_start_date = date('d/m/Y');
        $this->form_data->ue_start_time = '12:00 AM';
        $this->form_data->ue_end_date = date('d/m/Y', strtotime('1 month', time()));
        $this->form_data->ue_end_time = '12:00 AM';
        $this->form_data->user_group_id = '';
        $this->form_data->user_team_id = '';
	}

	// validation rules
	private function _set_rules()
	{
        $this->form_validation->set_rules('ue_start_date', 'Start Date', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ue_start_time', 'Start Time', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ue_end_date', 'End Date', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('ue_end_time', 'End Time', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('user_group_id', 'User Group', 'required|trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('user_team_id', 'User Team', 'required|trim|xss_clean|strip_tags');
	}

}

/* End of file assign_exam.php */
/* Location: ./application/controllers/administrator/assign_exam.php */