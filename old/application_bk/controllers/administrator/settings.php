<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Settings extends MY_Controller
{
    var $current_page = "settings";
    var $cat_list = array();
    var $admin_list = array();

    function __construct()
    {
        parent::__construct();
$this->form_data = new StdClass;
        // load necessary library and helper
		$this->load->config("pagination");
		$this->load->library("pagination");
		$this->load->library('table');
        $this->load->library('form_validation');
        $this->load->helper('email');
        $this->load->model('category_model');
        $this->load->model('admin_group_model');
        $all_categories_tree = $this->category_model->get_categories_recursive();
        $all_categories = $this->category_model->get_padded_categories($all_categories_tree);
        //$this->cat_list[] = 'Select a Category';
        if ($all_categories) {
            for ($i=0; $i<count($all_categories); $i++) {
                $this->cat_list[$all_categories[$i]->id] = $all_categories[$i]->cat_name;
            }
        }
        
        $all_admin_groups= $this->admin_group_model->get_admin_groups();
        
        if (count($all_categories)) {
            if ($all_admin_groups) {
                // print_r($all_admin_groups); die();
                for ($i=0; $i<count($all_admin_groups); $i++) {
                    $this->admin_list[$all_admin_groups[$i]->id] = $all_admin_groups[$i]->group_name;
                }
            }
        	
        }
         
        
        //$this->cat_list[] = 'Select a Category';
        if ($all_categories) {
        	for ($i=0; $i<count($all_categories); $i++) {
        		$this->cat_list[$all_categories[$i]->id] = $all_categories[$i]->cat_name;
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
                redirect('home');
            }
        }
    }

    public function index()
    {
        $page_info['title'] = 'General Settings'. $this->site_name;
        $page_info['view_page'] = 'administrator/settings_form_view';

        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();
        $this->_set_rules();

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

    public function update()
    {
        $site_name = $this->input->post('site_name');
        $this->option_model->update_option('site_name', $site_name);

        $default_category = (int)$this->input->post('default_category');
        $this->option_model->update_option('default_category', $default_category);

        $failed_login_message = $this->input->post('failed_login_message');
        $this->option_model->update_option('failed_login_message', $failed_login_message);

        $locked_login_message = $this->input->post('locked_login_message');
        $this->option_model->update_option('locked_login_message', $locked_login_message);

        $failed_login_count = (int)$this->input->post('failed_login_count');
        if ($failed_login_count < 0) { $failed_login_count = 0; }
        $this->option_model->update_option('failed_login_count', $failed_login_count);

        $user_inactivity_period = (int)$this->input->post('user_inactivity_period');
        if ($user_inactivity_period < 0) { $user_inactivity_period = 0; }
        $this->option_model->update_option('user_inactivity_period', $user_inactivity_period);

        // front office competency level
        $clfu = array();
        $clfu_label = $this->input->post('clfu_label');
        $clfu_lower = $this->input->post('clfu_lower');
        $clfu_higher = $this->input->post('clfu_higher');

        // validate and format
        $j = 0;
        for($i=0; $i<count($clfu_label); $i++) {
            $label = trim($clfu_label[$i]);
            $lower = trim($clfu_lower[$i]);
            $higher = trim($clfu_higher[$i]);
            if (strtolower($lower) == 'below') {
                $lower = -99999;
            } else {
                $lower = (int)$lower;
            }
            if (strtolower($higher) == 'above') {
                $higher = 99999;
            } else {
                $higher = (int)$higher;
            }
            if ($lower > $higher) {
                $label = '';
            }
            if ($label != '') {
                $clfu[$j]['label'] = $label;
                $clfu[$j]['lower'] = $lower;
                $clfu[$j]['higher'] = $higher;
                $j++;
            }
        }
        $this->option_model->update_option('front_office_competency', $clfu);

        // back office competency level
        $clbu = array();
        $clbu_label = $this->input->post('clbu_label');
        $clbu_lower = $this->input->post('clbu_lower');
        $clbu_higher = $this->input->post('clbu_higher');

        // validate and format
        $j = 0;
        for($i=0; $i<count($clbu_label); $i++) {
            $label = trim($clbu_label[$i]);
            $lower = trim($clbu_lower[$i]);
            $higher = trim($clbu_higher[$i]);
            if (strtolower($lower) == 'below') {
                $lower = -99999;
            } else {
                $lower = (int)$lower;
            }
            if (strtolower($higher) == 'above') {
                $higher = 99999;
            } else {
                $higher = (int)$higher;
            }
            if ($lower > $higher) {
                $label = '';
            }
            if ($label != '') {
                $clbu[$j]['label'] = $label;
                $clbu[$j]['lower'] = $lower;
                $clbu[$j]['higher'] = $higher;
                $j++;
            }
        }
        $this->option_model->update_option('back_office_competency', $clbu);

        $this->session->set_flashdata('message_success', 'Update is successful.');
        redirect('administrator/settings');
    }

    public function email()
    {
        $page_info['title'] = 'Email Settings'. $this->site_name;
        $page_info['view_page'] = 'administrator/settings_email_form_view';

        $page_info['message_error'] = '';
        $page_info['message_success'] = '';
        $page_info['message_info'] = '';

        $this->_set_fields();
        $this->_set_rules();

        // determine messages
        if ($this->session->flashdata('message_error')) {
            $page_info['message_error'] = $this->session->flashdata('message_error');
        }
        if ($this->session->flashdata('message_success')) {
            $page_info['message_success'] = $this->session->flashdata('message_success');
        }
        if ($this->session->flashdata('message_info')) {
            $page_info['message_info'] = $this->session->flashdata('message_info');
        }

        // load view
		$this->load->view('administrator/layouts/default', $page_info);
    }

    public function update_email()
    {
        $error = '';

        if ($this->input->post('send_test_email_submit')) {
            $this->send_test_email();
        } else {
            $email_from_name = $this->input->post('email_from_name');
            $this->option_model->update_option('email_from_name', $email_from_name);

            $email_smtp_host = $this->input->post('email_smtp_host');
            $this->option_model->update_option('email_smtp_host', $email_smtp_host);

            $email_smtp_port = $this->input->post('email_smtp_port');
            $this->option_model->update_option('email_smtp_port', $email_smtp_port);

            $email_smtp_user = $this->input->post('email_smtp_user');
            $this->option_model->update_option('email_smtp_user', $email_smtp_user);
            if (valid_email($email_smtp_user)) {
                $this->option_model->update_option('email_smtp_user', $email_smtp_user);
            } else {
                $error = 'Invalid email address.';
            }

            $email_smtp_pass = $this->input->post('email_smtp_pass');
            if ($email_smtp_pass != '') {
                $this->option_model->update_option('email_smtp_pass', $email_smtp_pass);
            }

            if ($error != '') {
                $this->session->set_flashdata('message_error', $error);
            } else {
                $this->session->set_flashdata('message_success', 'Update is successful.');
            }
            redirect('administrator/settings/email');
        }
    }

    private function send_test_email()
    {
        $to = $this->input->post('email_send_to');
        $from_name = $this->global_options['email_from_name'];
        $smtp_host = $this->global_options['email_smtp_host'];
        $smtp_port = $this->global_options['email_smtp_port'];
        $smtp_user = $this->global_options['email_smtp_user'];
        $smtp_pass = $this->global_options['email_smtp_pass'];

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

        $this->email->subject('Test Email');
        $this->email->message('Testing email.');

        if ( ! $this->email->send()) {
            $email_debug = $this->email->print_debugger();
            $this->session->set_flashdata('message_info', $email_debug);
            $this->session->set_flashdata('message_error', 'Email not sent.');
        } else {
            $this->session->set_flashdata('message_success', 'Email sent successfully.');
        }
        
        redirect('administrator/settings/email');
    }
    
    public function admin_ip()
    {
    	// set page specific variables
    	$page_info['title'] = 'Manage admin IP'. $this->site_name;
    	$page_info['view_page'] = 'administrator/admin_ip_list_view';
    	$page_info['message_error'] = '';
    	$page_info['message_success'] = '';
    	$page_info['message_info'] = '';
    	
    	$this->_set_fields();
    	//dd($this->input->ip_address());
    	
    	// gather filter options
    	$filter = array();
    	if ($this->session->flashdata('admin_ip')) {
    		$this->session->keep_flashdata('admin_ip');
    		$filter_admin_ip= $this->session->flashdata('admin_ip');
    		$this->form_data->admin_ip= $filter_admin_ip;
    		$filter['filter_admin_ip']['field'] = 'ip';
    		$filter['filter_admin_ip']['value'] = $filter_admin_ip;
    	}
    	$page_info['filter'] = $filter;
    	
    	$per_page = $this->config->item('per_page');
    	$uri_segment = $this->config->item('uri_segment');
    	$page_offset = $this->uri->segment($uri_segment);
    	$page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;
    	 
    	$record_result = $this->admin_group_model->get_paged_admin_ip($per_page, $page_offset, $filter);
    	 
    	$page_info['records'] = $record_result['result'];
    	$records = $record_result['result'];
    	
    	
    	// build paginated list
    	$config = array();
    	$config["base_url"] = base_url() . "administrator/category";
    	$config["total_rows"] = $record_result['count'];
    	$this->pagination->initialize($config);
    	$page_offset = ($this->uri->segment($uri_segment)) ? $this->uri->segment($uri_segment) : 0;
    	
    	
    	// get default category id
    	$default_cat_id = (int)$this->global_options['default_category'];
    	 
    	if ($records) {
    		// customize and generate records table
    		$tbl_heading = array(
    				'0' => array('data'=> 'ID'),
    				'1' => array('data'=> 'Admin Group'),
    				'2' => array('data'=> 'IP'),
    				'3' => array('data'=> 'Status', 'class' => 'center', 'width' => '120'),
    				'4' => array('data'=> 'Action', 'class' => 'center', 'width' => '100')
    		);
    		$this->table->set_heading($tbl_heading);
    		
    		$tbl_template = array (
    				'table_open'          => '<table class="table table-bordered table-striped" id="smpl_tbl" style="margin-bottom: 0;">',
    				'table_close'         => '</table>'
    		);
    		$this->table->set_template($tbl_template);
    		
    		for ($i = 0; $i<count($records); $i++) {
    			
    			$action_str = '';
    			$action_str .= anchor('administrator/settings/edit_ip/'. encrypt($records[$i]->id), '<i class="icon-edit"></i>', 'title="Edit"');
    			$action_str .= '&nbsp;&nbsp;&nbsp;';
    			$action_str .= anchor('administrator/settings/delete_ip/'. encrypt($records[$i]->id), '<i class="icon-trash"></i>', array('title'=>'Delete', 'onclick'=>'return confirm(\'Do you really want to delete this record?\')'));
    			
    			$is_active_str = '';
    			 
    				if ($records[$i]->status == 1) {
    					$is_active_str .= '<span class="label label-success">Active</span>';
    				}
    				if ($records[$i]->status== 0) {
    					$is_active_str .= '<span class="label label-danger">Inactive</span>';
    				} 
    				
    			$tbl_row = array(
    					'0' => array('data'=> $records[$i]->id),
    					'1' => array('data'=> $records[$i]->group_name),
    					'2' => array('data'=> $records[$i]->ip),
    					'3' => array('data'=> $is_active_str, 'class' => 'center', 'width' => '120'),
    					'4' => array('data'=> $action_str, 'class' => 'center', 'width' => '100', 'width' => '120')
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
    
    public function add_ip()
    {
    	// set page specific variables
    	$page_info['title'] = 'Add New IP'. $this->site_name;
    	$page_info['view_page'] = 'administrator/ip_form_view';
    	$page_info['message_error'] = '';
    	$page_info['message_success'] = '';
    	$page_info['message_info'] = '';
    	$page_info['is_edit'] = false;
    	
    	$this->_set_fields();
    	$this->_set_rules_ip();
    	
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
    
    public function do_add_ip()
    {
    	$page_info['title'] = 'Add New Category'. $this->site_name;
    	$page_info['view_page'] = 'administrator/ip_form_view';
    	$page_info['message_error'] = '';
    	$page_info['message_success'] = '';
    	$page_info['message_info'] = '';
    	$page_info['is_edit'] = false;
    	
    	$this->_set_fields();
    	$this->_set_rules_ip();
    	
    	if ($this->form_validation->run() == FALSE) {
    		
    		$this->load->view('administrator/layouts/default', $page_info);
    		
    	} else {
    		
    		$admin_list= (int)$this->input->post('admin_list');
    		$allowed_ip= $this->input->post('allowed_ip');
    		$ip_is_active= (int)$this->input->post('ip_is_active');
    		
    		$data = array(
    				'role_id' => $admin_list,
    				'ip' => $allowed_ip,
    				'status' => $ip_is_active
    		);
    		
    		$res = (int)$this->admin_group_model->add_admin_ip($data);
    		
    		if ($res > 0) {
    			$this->session->set_flashdata('message_success', 'Add is successful.');
    			redirect('administrator/settings/admin_ip');
    		} else {
    			$page_info['message_error'] = 'Add is unsuccessful.';
    			$this->load->view('administrator/layouts/default', $page_info);
    		}
    	}
    }
    public function edit_ip()
    {
    	// set page specific variables
    	$page_info['title'] = 'Edit Category'. $this->site_name;
    	$page_info['view_page'] = 'administrator/ip_form_view';
    	$page_info['message_error'] = '';
    	$page_info['message_success'] = '';
    	$page_info['message_info'] = '';
    	$page_info['is_edit'] = true;
    	
    	// prefill form values
    	$ip_id= dencrypt($this->uri->segment(4)); 
    	$ip_id= (int)$ip_id; 
    	 
    	$category = $this->admin_group_model->get_admin_ip($ip_id);  
    	if($category){
    	$this->_set_rules();  
    	$this->form_data->allowed_ip_id= encrypt($category->id);
    	$this->form_data->admin_list= $category->role_id;
    	$this->form_data->allowed_ip= $category->ip;
    	$this->form_data->ip_is_active= $category->status; 
    	}
    	else{
    		$this->_set_fields();
    		$page_info['message_error']=$this->admin_group_model->error_message;
    	}
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
    
    public function update_ip()
    {
    	// set page specific variables
    	$page_info['title'] = 'Edit Category'. $this->site_name;
    	$page_info['view_page'] = 'administrator/ip_form_view';
    	$page_info['message_error'] = '';
    	$page_info['message_success'] = '';
    	$page_info['message_info'] = '';
    	$page_info['is_edit'] = true;
    	
    	 
    	$this->_set_fields();
    	$this->_set_rules_ip();
    	
    	if ($this->form_validation->run() == FALSE) {
    		
    		$this->load->view('administrator/layouts/default', $page_info);
    		
    	} else {
    		
    		$admin_list= (int)$this->input->post('admin_list');
    		$allowed_ip= $this->input->post('allowed_ip');
    		$ip_is_active= (int)$this->input->post('ip_is_active');
    		$ip_id= dencrypt($this->input->post('allowed_ip_id'));
    		$ip_id= (int)$ip_id; 
    		$data = array(
    				'role_id' => $admin_list,
    				'ip' => $allowed_ip,
    				'status' => $ip_is_active
    				 
    		);
    		
    		if ($this->admin_group_model->update_admin_ip($ip_id, $data)) {
    			$this->session->set_flashdata('message_success', 'Update is successful.');
    		} else  {
    			$this->session->set_flashdata('message_error', $this->category_model->error_message. ' Update is unsuccessful.');
    		}
    		
    		redirect('administrator/settings/admin_ip');
    	}
    }
    
    /**
     * Delete a category
     * @return void
     */
    public function delete_ip()
    {
    	$ip_id= dencrypt($this->uri->segment(4));
    	$ip_id= (int)$ip_id; 
    	$res = $this->admin_group_model->delete_admin_ip($ip_id);
    	
    	if ($res > 0) {
    		$this->session->set_flashdata('message_success', 'Delete is successful.');
    	} else {
    		$this->session->set_flashdata('message_error', $this->admin_group_model->error_message .' Delete is unsuccessful.');
    	}
    	
    	redirect('administrator/settings/admin_ip');
    }
    
    
    

    // set empty default form field values
	private function _set_fields()
	{
		$this->form_data = new StdClass;
        $this->form_data->site_name = $this->global_options['site_name'];
        $this->form_data->default_category = (int)$this->global_options['default_category'];
        $this->form_data->failed_login_message = $this->global_options['failed_login_message'];
        $this->form_data->locked_login_message = $this->global_options['locked_login_message'];
        $this->form_data->failed_login_count = (int)$this->global_options['failed_login_count'];
        $this->form_data->user_inactivity_period = (int)$this->global_options['user_inactivity_period'];
        $this->form_data->front_office_competency = $this->global_options['front_office_competency'];
        $this->form_data->back_office_competency = $this->global_options['back_office_competency'];

        $this->form_data->email_from_name = $this->global_options['email_from_name'];
        $this->form_data->email_smtp_host = $this->global_options['email_smtp_host'];
        $this->form_data->email_smtp_port = $this->global_options['email_smtp_port'];
        $this->form_data->email_smtp_user = $this->global_options['email_smtp_user'];
        $this->form_data->email_smtp_pass = ''; 
        $this->form_data->admin_list= ''; 
        $this->form_data->allowed_ip= ''; 
        $this->form_data->allowed_ip_id= ''; 
        $this->form_data->ip_is_active= 1;
        $this->form_data->admin_ip=''; 
        
	}

	// validation rules
	private function _set_rules()
	{
		$this->form_validation->set_rules('site_name', 'Site Name', 'trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('default_category', 'Default Category', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('failed_login_message', 'Failed Login Message', 'trim|xss_clean|strip_tags');
        $this->form_validation->set_rules('locked_login_message', 'Locked Login Message', 'trim|xss_clean|strip_tags');

		$this->form_validation->set_rules('email_from_name', 'From Name', 'required|trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('email_smtp_host', 'SMTP Host', 'required|trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('email_smtp_port', 'SMTP Port', 'required|trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('email_smtp_user', 'SMTP User Email Address', 'required|trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('email_smtp_pass', 'SMTP User Password', 'trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('admin_list', 'Admin Group', 'required|trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('allowed_ip', 'Allowed IP', 'required|trim|xss_clean|strip_tags');
	}
	
	public function filter()
	{
		$admin_ip= $this->input->post('admin_ip');
		$filter_clear = $this->input->post('filter_clear');
		
		if ($filter_clear == '') {
			if ($admin_ip!= '') {
				$this->session->set_flashdata('admin_ip', $admin_ip);
			} 
		} else {
			$this->session->unset_userdata('admin_ip'); 
		}
		
		redirect('administrator/settings/admin_ip');
	}
	
	
	private function _set_rules_ip()
	{
		 
		$this->form_validation->set_rules('admin_list', 'Admin Group', 'required|trim|xss_clean|strip_tags');
		$this->form_validation->set_rules('allowed_ip', 'Allowed IP', 'required|trim|xss_clean|strip_tags');
	}

}

/* End of file settings.php */
/* Location: ./application/controllers/administrator/settings.php */