<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Ajax extends MY_Controller
{
    var $current_page = "ajax";
    var $tbl_exam_users_activity    = "exm_user_activity";

    function __construct()
    {
        parent::__construct();
        $this->load->model('global/insert_global_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');

        // check if logged in
        if ( ! $this->session->userdata('logged_in_user')) {
            exit(0);
        } else {
            $logged_in_user = $this->session->userdata('logged_in_user');
            if ($logged_in_user->user_type == 'User' && !$this->session->userdata('user_privilage_name')) {
                echo 0;
                exit(0);
            }
        }
    }

    public function index()
    {
        echo 0;
        exit(0);
    }

    public function user_team_select_box_by_group($user_group_id, $control_id = '')
    {
        $this->load->model('user_team_model');
        $user_group_id = (int)$user_group_id;

        if ($user_group_id > 0) {

            $user_teams = $this->user_team_model->get_user_teams_by_group($user_group_id);
            if ($user_teams) {
                echo $this->generate_user_team_list_html($user_teams, $control_id);
            } else {
                echo $this->generate_user_team_list_html(array(), $control_id);
            }

        } elseif ($user_group_id == 0) {

            $user_teams = $this->user_team_model->get_user_teams();
            if ($user_teams) {
                echo $this->generate_user_team_list_html($user_teams, $control_id);
            }

        } else {
            echo '';
        }

        exit(0);
    }

    private function generate_user_team_list_html($list, $control_id = '')
    {
        $list_html = '';
        if ($control_id == '') {
            $control_id = 'user_team_id';
        }

        if (count($list) > 0) {
            $list_html .= '<select name="'. $control_id .'" id="'. $control_id .'" class="input-xxlarge chosen-select">';
            $list_html .= '<option value="0">Select an User Team</option>';
            
            for ($i=0; $i<count($list); $i++) {
                $list_html .= '<option value="'. $list[$i]->id .'">';
                    $list_html .= $list[$i]->team_name;
                $list_html .= '</option>';
            }

            $list_html .= '</select>';
        } else {
            $list_html .= '<select name="'. $control_id .'" id="'. $control_id .'" class="input-xxlarge chosen-select">';
            $list_html .= '<option value="0">Select an User Team</option>';
            $list_html .= '</select>';
        }

        return $list_html;
    }

    public function user_select_box_by_team($user_team_id, $control_id = '')
    {
        $user_team_id = (int)$user_team_id;

        if ($user_team_id > 0) {

            $users = $this->user_model->get_active_users_by_user_team($user_team_id);
            if ($users) {
                echo $this->generate_user_list_html($users, $control_id);
            } else {
                echo $this->generate_user_list_html(array(), $control_id);
            }

        } elseif ($user_team_id == 0) {

            $users = $this->user_model->get_active_users('User');
            if ($users) {
                echo $this->generate_user_list_html($users, $control_id);
            }

        } else {
            echo '';
        }

        exit(0);
    }

    private function generate_user_list_html($list, $control_id = '')
    {
        $list_html = '';
        if ($control_id == '') {
            $control_id = 'user_ids';
        }

        if (count($list) > 0) {
            $list_html .= '<select name="'. $control_id .'[]" id="'. $control_id .'" multiple="multiple" class="input-xxlarge chosen-select">';

            for ($i=0; $i<count($list); $i++) {
                $list_html .= '<option value="'. $list[$i]->id .'">';
                    $list_html .= $list[$i]->user_first_name .' '. $list[$i]->user_last_name .' - '. $list[$i]->user_login;
                $list_html .= '</option>';
            }

            $list_html .= '</select>';
        } else {
            $list_html .= '<select name="'. $control_id .'[]" id="'. $control_id .'" multiple="multiple" class="input-xxlarge chosen-select">';
            //$list_html .= '<option value="0">No User found under the Team</option>';
            $list_html .= '</select>';
        }

        return $list_html;
    }

}

/* End of file ajax.php */
/* Location: ./application/controllers/administrator/ajax.php */