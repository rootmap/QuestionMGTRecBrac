<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Venue extends MY_Controller
{
    var $current_page = "venue";
    var $current_page_title = "Venue";
    var $cat_list = array();

    function __construct()
    {
        parent::__construct();
		$this->form_data = new StdClass;
        // load necessary library and helper
        $this->load->config("pagination");
        $this->load->library("pagination");
        $this->load->library('table');
        $this->load->library('form_validation');
        $this->load->model('venue_model');

        

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
     * Display paginated list of categories
     * @return void
     */
    public function index()
	{
        // set page specific variables
        $page_info['title'] = 'Manage '.$this->current_page_title.' '. $this->site_name;
        $page_info['view_page'] = 'administrator/'.$this->current_page.'/category_list_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();


        // gather filter options
        $filter = array();
        if ($this->session->flashdata('filter_name')) {
            $this->session->keep_flashdata('filter_name');
            $filter_name = $this->session->flashdata('filter_name');
            $this->form_data->filter_name = $filter_name;
            $filter['filter_name']['field'] = 'name';
            $filter['filter_name']['value'] = $filter_name;
        }
        $page_info['filter'] = $filter;

        $per_page = $this->config->item('per_page');
        $uri_segment = $this->config->item('uri_segment');
        $page_offset = $this->uri->segment($uri_segment);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;
        //echo 1; die();
        if (count($filter) > 0) {
            ///echo 1; die();
            $record_result = $this->venue_model->get_paged_venue($per_page, $page_offset, $filter);
        } else {
            //echo 2; die();
            $this->form_data->filter_name='';
            $record_result = $this->venue_model->get_padded_paged_venue($per_page, $page_offset);
        }
        $page_info['records'] = $record_result['result'];
        $records = $record_result['result'];


        // build paginated list
        $config = array();
        $config["base_url"] = base_url() . "administrator/".$this->current_page;
        $config["total_rows"] = $record_result['count'];
        $this->pagination->initialize($config);
        $page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;


        // get default category id

        if ($records) {
            // customize and generate records table
            $tbl_heading = array(
                '0' => array('data'=> 'ID','class' => 'left', 'width' => '100'),
                '1' => array('data'=> 'Name'),
                '2' => array('data'=> 'Start Time'),
                '3' => array('data'=> 'End Time'),
                '4' => array('data'=> 'Address'),
                '5' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
            );
            $this->table->set_heading($tbl_heading);

            $tbl_template = array (
                'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
                'table_close'         => '</table>'
            );
            $this->table->set_template($tbl_template);

            for ($i = 0; $i<count($records); $i++) {

                $action_str = '';
                if(!isSystemAuditor())
                $action_str .= anchor('administrator/'.$this->current_page.'/edit/'. $records[$i]->id, '<i class="icon-edit"></i>', 'title="Edit"');

                    $action_str .= '&nbsp;&nbsp;&nbsp;';
                if(!isSystemAuditor())
                    $action_str .= anchor('administrator/'.$this->current_page.'/delete/'. $records[$i]->id, '<i class="icon-trash"></i>', array('title'=>'Delete', 'onclick'=>'return confirm(\'Do you really want to delete this record?\')'));
                

                //$no_of_questions = (int)$this->venue_model->get_question_count($records[$i]->id);

                $tbl_row = array(
                    '0' => array('data'=> $records[$i]->id),
                    '1' => array('data'=> $records[$i]->name),
                    '2' => array('data'=> $records[$i]->start_time),
                    '3' => array('data'=> $records[$i]->end_time),
                    '4' => array('data'=> $records[$i]->address),
                    '5' => array('data'=> $action_str, 'class' => 'center', 'width' => '100', 'width' => '120')
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
        $filter_name = $this->input->post('filter_name');
        $filter_clear = $this->input->post('filter_clear');



        if ($filter_clear == '') {


            if ($filter_name != '') {
                $this->session->set_flashdata('filter_name', $filter_name);
            }
        } else {
            $this->session->unset_userdata('filter_name');
        }

        redirect('administrator/'.$this->current_page);
    }

    /**
     * Display add category form
     * @return void
     */
    public function add()
    {
        // set page specific variables
        $page_info['title'] = 'Add New '.$this->current_page_title.'  '. $this->site_name;
        $page_info['view_page'] = 'administrator/'.$this->current_page.'/category_form_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();

        $this->form_data->id = '';
        $this->form_data->name = '';
        $this->form_data->start_time = '09:00 AM';
        $this->form_data->end_time = '06:00 PM';
        $this->form_data->address = '';

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

    public function add_venue()
    {
        $page_info['title'] = 'Add New '.$this->current_page_title.' '. $this->site_name;
        $page_info['view_page'] = 'administrator/'.$this->current_page.'/category_form_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = false;

        $this->_set_fields();
        $this->_set_rules();

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('administrator/layouts/default', $page_info);

        } else {

            $name = $this->input->post('name');
            $start_time = $this->input->post('start_time');
            $end_time = $this->input->post('end_time');
            $address = $this->input->post('address');

            $data = array(
                'name' => $name,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'address' => $address
            );


            $res = (int)$this->venue_model->add_Venue($data);
//print_r('dd'); die();
            if ($res > 0) {
                $this->session->set_flashdata('message_success', $this->current_page_title.' added successfully.');
                redirect('administrator/'.$this->current_page.'/edit/'. $res);
            } else {

                $this->form_data->id = '';
                $this->form_data->name =$name?$name:'';
                $this->form_data->start_time = $start_time?$start_time:'';
                $this->form_data->end_time = $end_time?$end_time:'';
                $this->form_data->address = $address?$address:'';

                $page_info['message_error'] = $this->current_page_title.' Add is unsuccessful.';
                $this->load->view('administrator/layouts/default', $page_info);
            }
        }
    }

    public function edit()
    {

        // set page specific variables
        $page_info['title'] = 'Edit '.$this->current_page_title.' '. $this->site_name;
        $page_info['view_page'] = 'administrator/'.$this->current_page.'/category_form_view';
        $page_info['view_controller'] = $this->current_page;
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;

        // prefill form values
        $venue_id = (int)$this->uri->segment(4);
        $category = $this->venue_model->get_venue($venue_id);

        $this->_set_rules();



        $this->form_data->id = $category->id;
        $this->form_data->name = $category->name;
        $this->form_data->start_time = $category->start_time;
        $this->form_data->end_time = $category->end_time;
        $this->form_data->address = $category->address;


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


    public function update_venue()
    {
        // set page specific variables
        $page_info['title'] = 'Edit '.$this->current_page_title.' '. $this->site_name;
        $page_info['view_page'] = 'administrator/'.$this->current_page.'/category_form_view';
        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';
        $page_info['is_edit'] = true;
        
        $this->_set_fields();
        $this->_set_rules();

        //echo $cat_id; die();
        $venue_id = (int)$this->input->post('venue_id');

        if ($this->form_validation->run() == FALSE) {
            $this->form_data->id = (int)$this->input->post('venue_id');
            $this->load->view('administrator/layouts/default', $page_info);
        } else {

            $name = $this->input->post('name');
            $start_time = $this->input->post('start_time');
            $end_time = $this->input->post('end_time');
            $address = $this->input->post('address');

            $data = array(
                'name' => $name,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'address' => $address
            );

            if ($this->venue_model->update_Venue($venue_id, $data)) {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            } else  {
                $this->session->set_flashdata('message_error', $this->venue_model->error_message. ' Update is unsuccessful.');
            }

            redirect('administrator/'.$this->current_page.'/edit/'. $venue_id);
        }
    }

    /**
     * Delete a category
     * @return void
     */
    public function delete()
    {
        $cat_id = (int)$this->uri->segment(4);
        $res = $this->venue_model->delete_category($cat_id);
       // print_r($res); die();
        if ($res > 0) {
            $this->session->set_flashdata('message_success', 'Venue Deleted successfully.');
        } else {
            $this->session->set_flashdata('message_error', $this->venue_model->error_message .' Delete is unsuccessful.');
        }
        
        redirect('administrator/'.$this->current_page);
    }


    // set empty default form field values
	private function _set_fields()
	{
        
		$this->form_data->name = '';

	}

	// validation rules
	private function _set_rules()
	{
		$this->form_validation->set_rules('name', $this->current_page_title.' Name', 'required|trim|xss_clean|strip_tags');
	}

}

/* End of file category.php */
/* Location: ./application/controllers/administrator/category.php */