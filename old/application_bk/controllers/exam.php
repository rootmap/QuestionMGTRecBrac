<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Exam extends MY_Controller
{
    var $current_page = "exam";
    var $logged_in_user = false;

    function __construct()
    {
        parent::__construct();
        $this->load->library('robi_email');
        $this->load->model('exam_model');
        $this->load->model('category_model');
        $this->load->model('result_model');
        $this->load->model('user_exam_model');
        $this->load->model('user_exam_question_model');

        $this->logged_in_user = $this->session->userdata('logged_in_user');


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


        if ($this->input->post('exam_is_started')) {

            $exam_is_started = (int)$this->input->post('exam_is_started');

            if ($exam_is_started != 1) {
                redirect('startexam');
            } else {

                // set session for the first time
                if ( ! $this->session->userdata('exam_is_started') ) {

                    $this->session->set_userdata('exam_is_started', $exam_is_started);

                    $exam = $this->session->userdata('exam');
                    $exam->competency = $this->user_model->get_user_competency();
                    $user_exam_id = (int)$this->session->userdata('user_exam_id');

                    $exam->is_contain_descri = false;


                    $exam->exam_time_start = time();
                    $exam->exam_time_spent = 0;
                    $exam->exam_is_time_up = 0;
                    $exam->exam_questions = $this->user_exam_question_model->get_user_exam_questions($user_exam_id);


                    $log_message = $this->logged_in_user->user_login .' (User ID: '. $this->logged_in_user->id .') started the exam titled '. $exam->exam_title .' (Exam ID: '. $exam->id .')';
                    log_message("info", $log_message, false, 'exam started');

                    // initialize questions with score and user answer properties
                    for ($i=0; $i<count($exam->exam_questions); $i++) {

                        $score_per_question = number_format($exam->exam_score / $exam->exam_total_questions, 2);
                        $exam->exam_questions[$i]->question->ques_score = $score_per_question;
                        $exam->exam_questions[$i]->question->ques_user_score = 0;
                        $ques_type = $exam->exam_questions[$i]->question->ques_type;

                        if ($ques_type == 'mcq') {

                            $exam->exam_questions[$i]->question->ques_answer = array();
                            $exam->exam_questions[$i]->question->ques_answer_type = '';
                            $ques_choices = $exam->exam_questions[$i]->question->ques_choices;

                            for ($j=0; $j<count($ques_choices); $j++) {
                                $exam->exam_questions[$i]->question->ques_choices[$j]['is_user_answer'] = 0;
                                $exam->exam_questions[$i]->question->ques_choices[$j]['is_dontknow'] = 0;
                            }

                            // "i don't know" choice
                            if ((int)$exam->exam_allow_dontknow == 1) {
                                $exam->exam_questions[$i]->question->ques_choices[$j]['text'] = "I don't know";
                                $exam->exam_questions[$i]->question->ques_choices[$j]['is_answer'] = 0;
                                $exam->exam_questions[$i]->question->ques_choices[$j]['is_user_answer'] = 0;
                                $exam->exam_questions[$i]->question->ques_choices[$j]['is_dontknow'] = 1;
                            }

                        } else {
                            $exam->exam_questions[$i]->question->ques_answer = '';
                            $exam->exam_questions[$i]->question->ques_answer_type = '';
                            $exam->is_contain_descri = true;
                        }
                    }

                    $exam->current_question_index = 0;
                    $exam->current_question = $exam->exam_questions[0]->question;

                    if ($exam->current_question_index == 0) {
                        $exam->is_first_question = 1;
                    } else {
                        $exam->is_first_question = 0;
                    }

                    if ($exam->current_question_index == ($exam->exam_total_questions - 1)) {
                        $exam->is_last_question = 1;
                    } else {
                        $exam->is_last_question = 0;
                    }

                    $this->session->set_userdata('exam', $exam);

                    // update status of user_exam to complete;
                    // also update result table to exam's current state
                    // even though the exam is not completed yet, but once started
                    // it will be treated as complete, so that its not available next time
                    $this->user_exam_model->update_user_exam_status($user_exam_id, 'incomplete');
                    $this->user_exam_model->update_user_exam_state($user_exam_id, $exam);

                    $result_data = $this->prepare_result_data($user_exam_id, $exam);
                    $this->result_model->add_result($user_exam_id, $result_data);
                }

                redirect('exam');
            }
        }
    }

    public function index()
    {
        $page_info['title'] = 'Exam Started'. $this->site_name;
        $page_info['view_page'] = 'user/run_exam_view';

        //$exam_is_completed = (int)$this->session->userdata('exam_is_completed');
        $exam_is_started = (int)$this->session->userdata('exam_is_started');
        $exam = $this->session->userdata('exam');


        if ($exam_is_started == 1) {
            if ($exam) {

                $exam->current_question = $exam->exam_questions[$exam->current_question_index]->question;

                if ($exam->current_question_index == 0) {
                    $exam->is_first_question = 1;
                } else {
                    $exam->is_first_question = 0;
                }

                if ($exam->current_question_index == ($exam->exam_total_questions - 1)) {
                    $exam->is_last_question = 1;
                } else {
                    $exam->is_last_question = 0;
                }

                $this->update_time();

                $page_info['exam'] = $exam;

            } else {
                redirect('home');
            }
        } else {
            redirect('startexam');
        }


        // load view
        $this->load->view('user/layouts/default', $page_info);
    }

    public function action()
    {
        $exam = $this->session->userdata('exam');

        $action = $this->input->post('action');
		
        if ($this->input->post('type') && $this->input->post('type') == 'force') {
            $exam->exam_time_spent = (int)($exam->exam_time * 60);
            $exam->exam_is_time_up = 1;
            $this->session->set_userdata('exam', $exam);
            $action = 'force';
        } else {
            $this->update_time();
        }

        if ($action == 'previous') {
            $this->previous();
        } elseif ($action == 'next') {
            $this->next();
        }  elseif ($action == 'finish') {
            $this->finish();
        } elseif ($action = 'force') {
            $this->force_finish();
            //} elseif ($action == 'quit') {
            //$this->finish();
        } elseif ($action == 'pause') {
            $this->pause();
        } else {
            redirect('exam');
        }
    }

    private function update_time()
    {
        $exam = $this->session->userdata('exam');

        $now = time();
        $exam_time_start = (int)$exam->exam_time_start;

        $exam_time_spent = $now - $exam_time_start;
        $exam->exam_time_spent = (int)$exam_time_spent;

        $this->session->set_userdata('exam', $exam);
    }

    private function previous()
    {
        $exam = $this->session->userdata('exam');

        $current_question_index = $exam->current_question_index;
        $current_question_index--;
        $exam->current_question_index = $current_question_index;

        $this->session->set_userdata('exam', $exam);
        redirect('exam');
    }

    private function next()
    {
        $user_exam_id = (int)$this->session->userdata('user_exam_id');
        $exam = $this->session->userdata('exam');
        $exam = $this->update_user_answer($exam);

        $result_data = $this->prepare_result_data($user_exam_id, $exam);
        $this->result_model->update_result($user_exam_id, $result_data);

        $current_question_index = $exam->current_question_index;
        $current_question_index++;
        $exam->current_question_index = $current_question_index;

        $this->session->set_userdata('exam', $exam);
        redirect('exam');
    }

    private function finish()
    {

        $user_exam_id = (int)$this->session->userdata('user_exam_id');
        $this->exam_model->get_exam_user_finish_inactive($user_exam_id);
        //print_r_pre($sqlGetUserDetail->user_id); die();
        $exam = $this->session->userdata('exam');
        $exam = $this->update_user_answer($exam);

        $log_message = $this->logged_in_user->user_login .' (User ID: '. $this->logged_in_user->id .') completed the exam titled '. $exam->exam_title .' (Exam ID: '. $exam->id .')';
        log_message("info", $log_message, false, 'exam finished');

        $this->user_exam_model->update_user_exam_status($user_exam_id, 'complete');

        $exam_questions = $exam->exam_questions;
        $user_exam_questions = array();
        for ($i=0; $i<count($exam_questions); $i++) {
            $user_exam_questions[$i]['user_exam_id'] = $exam_questions[$i]->user_exam_id;
            $user_exam_questions[$i]['question_id'] = $exam_questions[$i]->question_id;
            $user_exam_questions[$i]['user_answer'] = $exam_questions[$i]->question->ques_answer_type;
        }
        $this->user_exam_model->update_user_exam_questions($user_exam_questions);

        $result_data = $this->prepare_result_data($user_exam_id, $exam);
        $result_data['result_status'] = 'complete';
        $this->result_model->update_result($user_exam_id, $result_data);

        $this->session->unset_userdata('exam');
        $this->session->unset_userdata('exam_id');
        $this->session->unset_userdata('user_exam_id');
        $this->session->unset_userdata('exam_is_started');

        $this->session->set_flashdata('user_exam_id', $user_exam_id);
        //$this->robi_email->mcq_result($result_data, $this->logged_in_user);

        redirect('result');
    }

    private function force_finish()
    {
        $user_exam_id = (int)$this->session->userdata('user_exam_id');
        $exam = $this->session->userdata('exam');

        $log_message = $this->logged_in_user->user_login .' (User ID: '. $this->logged_in_user->id .') run out of time and forcefully finished the exam titled '. $exam->exam_title .' (Exam ID: '. $exam->id .')';
        log_message("info", $log_message, false, 'exam finished forcefully');

        $this->user_exam_model->update_user_exam_status($user_exam_id, 'complete');

        $exam_questions = $exam->exam_questions;
        $user_exam_questions = array();
        for ($i=0; $i<count($exam_questions); $i++) {
            $user_exam_questions[$i]['user_exam_id'] = $exam_questions[$i]->user_exam_id;
            $user_exam_questions[$i]['question_id'] = $exam_questions[$i]->question_id;
            $user_exam_questions[$i]['user_answer'] = $exam_questions[$i]->question->ques_answer_type;
        }
        $this->user_exam_model->update_user_exam_questions($user_exam_questions);

        $result_data = $this->prepare_result_data($user_exam_id, $exam);
        $result_data['result_status'] = 'complete';
        $this->result_model->update_result($user_exam_id, $result_data);

        $this->session->unset_userdata('exam');
        $this->session->unset_userdata('exam_id');
        $this->session->unset_userdata('user_exam_id');
        $this->session->unset_userdata('exam_is_started');

        $this->session->set_flashdata('user_exam_id', $user_exam_id);
        $this->robi_email->mcq_result($result_data, $this->logged_in_user);

        redirect('result');
    }

    private function pause()
    {
        redirect('exam');
    }

    private function update_user_answer($exam)
    {
        $user_exam_id = $this->session->userdata('user_exam_id');
        $user_answer = $this->input->post('answer');

        $current_question_index = $exam->current_question_index;
        $is_mcq = false;
        if ($exam->exam_questions[$current_question_index]->question->ques_type == 'mcq') {
            $is_mcq = true;
        }

        // update exam as per user answer
        $exam->exam_questions[$current_question_index]->question->ques_answer = $user_answer;

        if ($is_mcq) {

            $ques_choices = $exam->exam_questions[$current_question_index]->question->ques_choices;
            $score_per_question = number_format($exam->exam_score / $exam->exam_total_questions, 2);
            $ques_score = (float)$exam->exam_questions[$current_question_index]->question->ques_score;
            $total_choices = count($ques_choices);
            $dontknow_answered = 0;
            $no_of_correct_choices = 0;
            $no_of_user_correct_choices = 0;

            for ($i=0; $i<$total_choices; $i++) {
                if (in_array($i, $user_answer)) {
                    $exam->exam_questions[$current_question_index]->question->ques_choices[$i]['is_user_answer'] = 1;
                    if ($exam->exam_questions[$current_question_index]->question->ques_choices[$i]['is_dontknow'] == 1) {
                        $dontknow_answered = 1;
                    }
                    if ($exam->exam_questions[$current_question_index]->question->ques_choices[$i]['is_answer'] == 1) {
                        $no_of_user_correct_choices++;
                    }
                } else {
                    $exam->exam_questions[$current_question_index]->question->ques_choices[$i]['is_user_answer'] = 0;
                }
                if ($exam->exam_questions[$current_question_index]->question->ques_choices[$i]['is_answer'] == 1) {
                    $no_of_correct_choices++;
                }
            }

            // calculate score (no partial marking)
            $negative_score = 0;
            $negative_percent = (int)$exam->exam_negative_mark_weight;
            if ($negative_percent < 0) { $negative_percent = 0; }
            if ((int)$exam->exam_allow_negative_marking == 1) {
                $negative_score = $score_per_question * ($negative_percent / 100) * -1;
            }

            $user_score = 0;
            if ($dontknow_answered == 1) {
                $user_score = 0;
                $exam->exam_questions[$current_question_index]->question->ques_answer_type = 'dontknow';
            } elseif ($no_of_correct_choices == $no_of_user_correct_choices) {
                $user_score = $ques_score;
                $exam->exam_questions[$current_question_index]->question->ques_answer_type = 'correct';
            } else {
                // when negative marking is not allowed even then $negative_score is 0 anyway
                $user_score = $negative_score;
                $exam->exam_questions[$current_question_index]->question->ques_answer_type = 'wrong';
            }

            $exam->exam_questions[$current_question_index]->question->ques_user_score = $user_score;
        }

        // update user exam state
        $this->user_exam_model->update_user_exam_state($user_exam_id, $exam);

        return $exam;
    }

    private function prepare_result_data($user_exam_id, $exam)
    {
        $total_questions = $exam->exam_categories[0]['set_limit'];
        $exam_score = $exam->exam_categories[0]['total_mark'];
        $neg_mark = $exam->exam_categories[0]['neg_mark_per_ques'];
        $user_score = 0;
        $competency_level = '';

        $start_time = date('Y-m-d H:i:s', $exam->exam_time_start);
        $time_spent = (int)$exam->exam_time_spent;
        $end_time = date('Y-m-d H:i:s');

        $is_paused = 0;
        $exam_state = maybe_serialize($this->session->userdata('exam'));

        $exam_is_mcq = false;
        if ($exam->exam_type == 'mcq') {
            $exam_is_mcq  = true;
        }

        $total_answered = 0;
        $total_correct = 0;
        $total_wrong = 0;
        $total_dontknow = 0;

        if ($exam_is_mcq) {

            for ($i=0; $i<count($exam->exam_questions); $i++) {

                $answer = $exam->exam_questions[$i]->question->ques_answer;

                $ques_user_score = $exam->exam_questions[$i]->question->ques_user_score;
                $user_score = $user_score + $ques_user_score;

                if(is_array($answer) && count($answer) > 0) {
                    $total_answered++;
                }

                // calculate total_correct, total_wrong, total_dontknow
                $ques_answer_type = $exam->exam_questions[$i]->question->ques_answer_type;

                if ($ques_answer_type == 'dontknow') {
                    $total_dontknow++;
                } elseif ($ques_answer_type == 'correct') {
                    $total_correct++;
                } elseif ($ques_answer_type == 'wrong') {
                    $total_wrong++;
                }
            }

            $total_wrong = $total_answered - $total_dontknow - $total_correct;
            if ($total_wrong < 0) { $total_wrong = 0; }
        }

        $user_score_percent = ($user_score / $exam_score) * 100;
        $competency_level = $this->result_model->calculate_competency_level($user_score_percent, $exam->competency);

        $result_data = array(
            'user_exam_id' => $user_exam_id,
            'result_total_questions' => $total_questions,
            'result_total_answered' => $total_answered,
            'result_total_correct' => $total_correct,
            'result_total_wrong' => $total_wrong,
            'result_total_dontknow' => $total_dontknow,
            'result_exam_score' => $exam_score,
            'result_user_score' => $user_score,
            'result_competency_level' => $competency_level,
            'result_time_spent' => $time_spent,
            'result_start_time' => $start_time,
            'result_end_time' => $end_time,
            'result_is_paused' => $is_paused,
            'result_exam_state' => $exam_state,
            'neg_mark' => $neg_mark
        );

        return $result_data;
    }





}







/* End of file exam.php */
/* Location: ./application/controllers/exam.php */