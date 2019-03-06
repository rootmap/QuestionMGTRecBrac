<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<!-- sidebar -->
<a href="javascript:void(0)" class="sidebar_switch on_switch ttip_r" title="Hide Sidebar">Sidebar switch</a>
<div class="sidebar">

    <div class="antiScroll"><div class="antiscroll-inner"><div class="antiscroll-content">

        <div class="sidebar_inner">

            <div id="side_accordion" class="accordion">               
                <?php if( in_array('add_category', $this->session->userdata('user_privilage_name')) 
                        || in_array('manage_categories', $this->session->userdata('user_privilage_name')) 
                        || in_array('add_question', $this->session->userdata('user_privilage_name')) 
                        || in_array('add_bulk_questions', $this->session->userdata('user_privilage_name'))
                        || in_array('edit_bulk_questions', $this->session->userdata('user_privilage_name')) 
                        || in_array('manage_questions', $this->session->userdata('user_privilage_name')) ): ?>
                <div class="accordion-group">
                    <div class="accordion-heading <?php if ($this->current_page == 'category' || $this->current_page == 'question') { echo ' sdb_h_active'; }?>">
                        <a href="#collapseCategory" data-parent="#side_accordion" data-toggle="collapse" class="accordion-toggle">
                            <i class="icon-list"></i> Questions
                        </a>
                    </div>
                    <div class="accordion-body  <?php if ($this->current_page == 'category' || $this->current_page == 'question') { echo ' in collapse'; } else { echo ' collapse'; } ?>" id="collapseCategory">
                        <div class="accordion-inner">
                            <ul class="nav nav-list">
                                <?php if( in_array('add_category', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/category/add'); ?>">Add Category</a></li>
                                <?php endif; ?>
                                <?php if( in_array('manage_categories', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/category'); ?>">Manage Categories</a></li>
                                <?php endif; ?>
                                <?php if( in_array('add_question', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/question/add'); ?>">Add Question</a></li>
                                <?php endif; ?>
                                <?php if( in_array('add_bulk_questions', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/question/bulk'); ?>">Add Bulk Questions</a></li>
                                <?php endif; ?>
                                <?php if( in_array('edit_bulk_questions', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/question/edit_bulk'); ?>">Edit Bulk Questions</a></li>
                                <?php endif; ?>
                                <?php if( in_array('manage_questions', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/question'); ?>">Manage Questions(All)</a></li>
                                <?php endif; ?>

                                 <?php if( in_array('questionpending', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/questionpending'); ?>">Manage Questions(Pending)</a></li>
                                <?php endif; ?>
                                <?php if( in_array('questionapproved', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/questionapproved'); ?>">Manage Questions(Approve)</a></li>
                                <?php endif; ?>
                                <?php if( in_array('questionrejected', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/questionrejected'); ?>">Manage Questions(Reject)</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                
                <?php if( in_array('create_exam', $this->session->userdata('user_privilage_name')) 
                        || in_array('manage_exams', $this->session->userdata('user_privilage_name')) 
                        || in_array('exam_checker_mapping', $this->session->userdata('user_privilage_name')) 
                        || in_array('exam_venue_mapping', $this->session->userdata('user_privilage_name')) 
                        || in_array('assign_exam_to_users', $this->session->userdata('user_privilage_name')) 
                        || in_array('assigned_exam_status', $this->session->userdata('user_privilage_name')) ): ?>    
                <div class="accordion-group">
                    <div class="accordion-heading <?php 
                    if ($this->current_page == 'exam' || 
                    $this->current_page == 'exam_checker_mapping' || 
                    $this->current_page == 'exam_venue_mapping' || 
                    $this->current_page == 'assign-exam' || 
                    $this->current_page == 'assign-status') { echo ' sdb_h_active'; }?>">
                        <a href="#collapseExam" data-parent="#side_accordion" data-toggle="collapse" class="accordion-toggle">
                            <i class="icon-file"></i> Exams
                        </a>
                    </div>
                    <div class="accordion-body <?php if ($this->current_page == 'exam' || $this->current_page == 'assign-exam' || $this->current_page == 'assign-status') { echo ' in collapse'; } else { echo ' collapse'; } ?>" id="collapseExam">
                        <div class="accordion-inner">
                            <ul class="nav nav-list">
                                <?php if( in_array('create_exam', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/exam/add'); ?>">Create Exam</a></li>
                                <?php endif; ?>  
                                <?php if( in_array('manage_exams', $this->session->userdata('user_privilage_name')) ): ?>    
                                    <li><a href="<?php echo site_url('administrator/exam'); ?>">Manage Exams</a></li>
                                <?php endif; ?>
                                <?php if( in_array('assign_exam_to_users', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/assign_exam'); ?>">Assign Exam to Users</a></li>
                                <?php endif; ?>
                                <?php if( in_array('assigned_exam_status', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/assign_status'); ?>">Assigned Exam Status</a></li>
                                <?php endif; ?>
                                <?php if( in_array('create_question_pool', $this->session->userdata('user_privilage_name'))): ?>
                                    <li><a href="<?php echo site_url('create_question_pool'); ?>">Create Question Set</a></li>
                                <?php endif; ?>
                                <?php if( in_array('questionpoollist', $this->session->userdata('user_privilage_name'))): ?>
                                    <li><a href="<?php echo site_url('question_set'); ?>">Question Set List</a></li>
                                <?php endif; ?>
                                <?php if( in_array('create_new_venue', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/venue/add'); ?>">Create New Venue</a></li>
                                <?php endif; ?>
                                <?php if( in_array('manage_venue', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/venue'); ?>">Manage Venue</a></li>
                                <?php endif; ?>
                                <!--
                                <?php //if( in_array('assigned_exam_status', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php //echo site_url('administrator/question_set/add'); ?>">Question Set</a></li>
                                <?php //endif; ?>
                                <?php //if( in_array('assigned_exam_status', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php //echo site_url('administrator/question_set'); ?>">Manage Question Set</a></li>
                                <?php //endif; ?>
                                -->
                                <?php if( in_array('exam_venue_mapping', $this->session->userdata('user_privilage_name')) ): ?>
                                <li><a href="<?php echo site_url('exam_venues/exam_venue_mapping'); ?>"> Exam Venue Mapping </a></li>
                                <?php endif; ?>

                                <?php if( in_array('exam_checker_mapping', $this->session->userdata('user_privilage_name')) ): ?>
                                <li><a href="<?php echo site_url('exam_venues/exam_checker_mapping'); ?>"> Exam Checker Mapping </a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                
                <?php if( in_array('exam_results', $this->session->userdata('user_privilage_name'))
                        || in_array('user_results', $this->session->userdata('user_privilage_name'))): ?>
                <div class="accordion-group">
                    <div class="accordion-heading <?php if ($this->current_page == 'result') { echo ' sdb_h_active'; }?>">
                        <a href="#collapseResult" data-parent="#side_accordion" data-toggle="collapse" class="accordion-toggle">
                            <i class="icon-certificate"></i> Results
                        </a>
                    </div>
                    <div class="accordion-body <?php if ($this->current_page == 'result') { echo ' in collapse'; } else { echo ' collapse'; } ?>" id="collapseResult">
                        <div class="accordion-inner">
                            <ul class="nav nav-list">
                                <li><a href="<?php echo site_url('administrator/result/upload_result'); ?>"> Exam Result Upload </a></li>

                                <?php if( in_array('exam_results', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/result_team'); ?>">Exam Results</a></li>
                                <?php endif; ?>
                                <?php if( in_array('user_results', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/result_user'); ?>">User Results</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if( in_array('add_survey_category', $this->session->userdata('user_privilage_name')) 
                        || in_array('manage_survey_category', $this->session->userdata('user_privilage_name')) 
                        || in_array('add_survey_question', $this->session->userdata('user_privilage_name'))
                        || in_array('add_bulk_survey_questions', $this->session->userdata('user_privilage_name'))
                        || in_array('manage_survey_questions', $this->session->userdata('user_privilage_name'))
                        || in_array('add_survey', $this->session->userdata('user_privilage_name'))
                        || in_array('manage_survey', $this->session->userdata('user_privilage_name'))
                        || in_array('assign_survey_to_user', $this->session->userdata('user_privilage_name'))
                        || in_array('assign_survey_to_bulk_user', $this->session->userdata('user_privilage_name'))
                        || in_array('assigned_survey_status', $this->session->userdata('user_privilage_name'))
                        || in_array('survey_report', $this->session->userdata('user_privilage_name')) 
                        || in_array('survey_details_report', $this->session->userdata('user_privilage_name')) ): ?>
                        
                        <div class="accordion-group">
                            <div class="accordion-heading <?php if ($this->current_page == 'survey_category' || $this->current_page == 'survey_question' || $this->current_page == 'survey' || $this->current_page == 'assign_survey' || $this->current_page == 'assign_survey_status' || $this->current_page == 'survey_report' || $this->current_page == 'survey_details_report') { echo ' sdb_h_active'; }?>">
                                <a href="#collapseSurvey" data-parent="#side_accordion" data-toggle="collapse" class="accordion-toggle">
                                    <i class="icon-file"></i> Survey
                                </a>
                            </div>
                            <div class="accordion-body  <?php if ($this->current_page == 'survey_category' || $this->current_page == 'survey_question' || $this->current_page == 'survey' || $this->current_page == 'assign_survey' || $this->current_page == 'assign_survey_status' || $this->current_page == 'survey_report' || $this->current_page == 'survey_details_report') { echo ' in collapse'; } else { echo ' collapse'; } ?>" id="collapseSurvey">
                                <div class="accordion-inner">
                                    <ul class="nav nav-list">
                                        <?php if( in_array('add_survey_category', $this->session->userdata('user_privilage_name')) ): ?>
                                            <li><a href="<?php echo site_url('administrator/survey_category/add'); ?>">Add Survey Category</a></li>
                                        <?php endif; ?>
                                        <?php if( in_array('manage_survey_category', $this->session->userdata('user_privilage_name')) ): ?>
                                            <li><a href="<?php echo site_url('administrator/survey_category'); ?>">Manage Survey Categories</a></li>
                                        <?php endif; ?>
                                        <?php if( in_array('add_survey_question', $this->session->userdata('user_privilage_name')) ): ?>
                                            <li><a href="<?php echo site_url('administrator/survey_question/add'); ?>">Add Survey Question</a></li>
                                        <?php endif; ?>
                                        <?php if( in_array('add_bulk_survey_questions', $this->session->userdata('user_privilage_name')) ): ?>
                                            <li><a href="<?php echo site_url('administrator/survey_question/bulk'); ?>">Add Bulk Survey Questions</a></li>
                                        <?php endif; ?>
                                        <?php if( in_array('manage_survey_questions', $this->session->userdata('user_privilage_name')) ): ?>
                                            <li><a href="<?php echo site_url('administrator/survey_question'); ?>">Manage Survey Questions</a></li>
                                        <?php endif; ?>
                                        <?php if( in_array('add_survey', $this->session->userdata('user_privilage_name')) ): ?>
                                            <li><a href="<?php echo site_url('administrator/survey/add'); ?>">Add Survey</a></li>
                                        <?php endif; ?>
                                        <?php if( in_array('manage_survey', $this->session->userdata('user_privilage_name')) ): ?>
                                            <li><a href="<?php echo site_url('administrator/survey'); ?>">Manage Survey</a></li>
                                        <?php endif; ?>
                                        <?php if( in_array('assign_survey_to_user', $this->session->userdata('user_privilage_name')) ): ?>
                                            <li><a href="<?php echo site_url('administrator/assign_survey'); ?>">Assign Survey to User</a></li>
                                        <?php endif; ?>
                                        <?php if( in_array('assign_survey_to_bulk_user', $this->session->userdata('user_privilage_name')) ): ?>
                                            <li><a href="<?php echo site_url('administrator/assign_survey/bulk'); ?>">Assign Survey to Bulk User</a></li>
                                        <?php endif; ?>
                                        <?php if( in_array('assigned_survey_status', $this->session->userdata('user_privilage_name')) ): ?>
                                            <li><a href="<?php echo site_url('administrator/assign_survey_status'); ?>">Assigned Survey Status</a></li>
                                        <?php endif; ?>
                                        <?php if( in_array('survey_report', $this->session->userdata('user_privilage_name')) ): ?>
                                            <li><a href="<?php echo site_url('administrator/survey_report'); ?>">Survey Report</a></li>
                                        <?php endif; ?>
                                        <?php if( in_array('survey_details_report', $this->session->userdata('user_privilage_name')) ): ?>
                                            <li><a href="<?php echo site_url('administrator/survey_details_report'); ?>">Survey Details Report</a></li>
                                        <?php endif; ?>    
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                
                <?php if( in_array('add_group', $this->session->userdata('user_privilage_name'))
                        || in_array('manage_groups', $this->session->userdata('user_privilage_name'))
                        || in_array('add_team', $this->session->userdata('user_privilage_name'))
                        || in_array('manage_teams', $this->session->userdata('user_privilage_name'))
                        || in_array('add_admin_group', $this->session->userdata('user_privilage_name'))
                        || in_array('manage_admin_groups', $this->session->userdata('user_privilage_name'))
                        || in_array('add_user', $this->session->userdata('user_privilage_name'))
                        || in_array('add_bulk_users', $this->session->userdata('user_privilage_name'))
                        || in_array('edit_bulk_users', $this->session->userdata('user_privilage_name'))
                        || in_array('delete_bulk_users', $this->session->userdata('user_privilage_name'))
                        || in_array('manage_users', $this->session->userdata('user_privilage_name')) ): ?>
                <div class="accordion-group">
                    <div class="accordion-heading <?php if ($this->current_page == 'user' || $this->current_page == 'group' || $this->current_page == 'admingroup' || $this->current_page == 'team') { echo ' sdb_h_active'; }?>">
                        <a href="#collapseUser" data-parent="#side_accordion" data-toggle="collapse" class="accordion-toggle">
                            <i class="icon-users"></i> Users
                        </a>
                    </div>
                    <div class="accordion-body <?php if ($this->current_page == 'user' || $this->current_page == 'group' || $this->current_page == 'admingroup' || $this->current_page == 'team') { echo ' in collapse'; } else { echo ' collapse'; } ?>" id="collapseUser">
                        <div class="accordion-inner">
                            <ul class="nav nav-list">
                                <?php if( in_array('add_group', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/group/add'); ?>">Add Group</a></li>
                                <?php endif; ?>
                                <?php if( in_array('manage_groups', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/group'); ?>">Manage Groups</a></li>
                                <?php endif; ?>
                                <?php if( in_array('add_team', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/team/add'); ?>">Add Team</a></li>
                                <?php endif; ?>
                                <?php if( in_array('manage_teams', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/team'); ?>">Manage Teams</a></li>
                                <?php endif; ?>
                                <?php if( in_array('add_admin_group', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/admingroup/add'); ?>">Add Admin Group</a></li>
                                <?php endif; ?>
                                <?php if( in_array('manage_admin_groups', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/admingroup'); ?>">Manage Admin Groups</a></li>
                                <?php endif; ?>
                                <?php if( in_array('add_user', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/user/add'); ?>">Add User</a></li>
                                <?php endif; ?>
                                <?php if( in_array('add_bulk_users', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/user/bulk'); ?>">Add Bulk Users</a></li>
                                <?php endif; ?>
                                <?php if( in_array('edit_bulk_users', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/user/edit_bulk'); ?>">Edit Bulk Users</a></li>
                                <?php endif; ?>
                                <?php if( in_array('delete_bulk_users', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/user/delete_bulk'); ?>">Delete Bulk Users</a></li>
                                <?php endif; ?>
                                <?php if( in_array('manage_users', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/user'); ?>">Manage Users</a></li>

                                <?php endif; ?>


                                <?php if( in_array('add_candidate', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/candidate/add'); ?>">Add Candidate</a></li>
                                <?php endif; ?>
                                <?php if( in_array('add_bulk_candidates', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/candidate/bulk'); ?>">Add Bulk Candidates</a></li>
                                <?php endif; ?>
                                <!--
                                <?php //if( in_array('edit_bulk_users', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php //echo site_url('administrator/candidate/edit_bulk'); ?>">Edit Bulk Candidate</a></li>
                                <?php //endif; ?>
                                <?php //if( in_array('delete_bulk_users', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php //echo site_url('administrator/candidate/delete_bulk'); ?>">Delete Bulk Candidate</a></li>
                                <?php //endif; ?>
                                -->
                                <?php if( in_array('manage_candidates', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/candidate'); ?>">Manage Candidates</a></li>

                                <?php endif; ?>
                                <?php if( in_array('user_activity', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/user/user_activity'); ?>">User Activity</a></li>

                                <?php endif; ?>
                                
                                <?php if( in_array('user_iptracking', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/user/user_iptracking'); ?>">Device Tracking</a></li>

                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if( in_array('update_profile', $this->session->userdata('user_privilage_name'))
                       || in_array('change_password', $this->session->userdata('user_privilage_name'))
                       || in_array('user_password_change', $this->session->userdata('user_privilage_name')) ): ?>
                <div class="accordion-group">
                    <div class="accordion-heading <?php if ($this->current_page == 'profile') { echo ' sdb_h_active'; }?>">
                        <a href="#collapseProfile" data-parent="#side_accordion" data-toggle="collapse" class="accordion-toggle">
                            <i class="icon-user"></i> Profile
                        </a>
                    </div>
                    <div class="accordion-body <?php if ($this->current_page == 'profile') { echo ' in collapse'; } else { echo ' collapse'; } ?>" id="collapseProfile">
                        <div class="accordion-inner">
                            <ul class="nav nav-list">
                                <?php if( in_array('update_profile', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/profile'); ?>">Update Profile</a></li>
                                <?php endif; ?>
                                <?php if( in_array('change_password', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/profile/password'); ?>">Change Password</a></li>
                                <?php endif; ?>
                                <?php if( in_array('user_password_change', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/profile/user_password'); ?>">User Password Change</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if( in_array('configure_sms_and_email', $this->session->userdata('user_privilage_name'))
                        || in_array('new_sms_and_email_layout', $this->session->userdata('user_privilage_name'))
                        || in_array('manage_sms_and_email_category', $this->session->userdata('user_privilage_name'))
                        || in_array('manage_account_mail_layout', $this->session->userdata('user_privilage_name'))
                        || in_array('manage_pass_change_mail_layout', $this->session->userdata('user_privilage_name'))
                        || in_array('sms_and_email_mapping', $this->session->userdata('user_privilage_name'))): ?>
                <div class="accordion-group">
                    <div class="accordion-heading <?php if ($this->current_page == 'smsnemail') { echo ' sdb_h_active'; }?>">
                        <a href="#collapseconfiguresmsnemail" data-parent="#side_accordion" data-toggle="collapse" class="accordion-toggle">
                            <i class="icon-certificate"></i> Configure SMS &amp; E-Mail
                        </a>
                    </div>
                    <div class="accordion-body <?php if ($this->current_page == 'smsnemail') { echo ' in collapse'; } else { echo ' collapse'; } ?>" id="collapseconfiguresmsnemail">
                        <div class="accordion-inner">
                            <ul class="nav nav-list">
                                <?php if( in_array('new_sms_and_email_layout', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/smsnemail/add'); ?>"> New Exam SMS &amp; Email Layout</a></li>
                                <?php endif; ?>
                                <?php if( in_array('manage_sms_and_email_category', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/smsnemail'); ?>">Manage Exam SMS &amp; Email Category</a></li>
                                <?php endif; ?>
                                <?php if( in_array('manage_account_mail_layout', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/smsnemail/account_creation'); ?>">Manage Account Creation Email Layout</a></li>
                                <?php endif; ?>
                                <?php if( in_array('manage_pass_change_mail_layout', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/smsnemail/password_change'); ?>">Manage Password Change Email Layout</a></li>
                                <?php endif; ?>

                                <?php if( in_array('sms_and_email_mapping', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/smsnemail/mapping'); ?>">SMS &amp; Email Mapping</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if( in_array('general', $this->session->userdata('user_privilage_name'))
                       || in_array('email', $this->session->userdata('user_privilage_name')) ): ?>
                <div class="accordion-group">
                    <div class="accordion-heading <?php if ($this->current_page == 'settings') { echo ' sdb_h_active'; }?>">
                        <a href="#collapseSettings" data-parent="#side_accordion" data-toggle="collapse" class="accordion-toggle">
                            <i class="icon-wrench"></i> Settings
                        </a>
                    </div>
                    <div class="accordion-body <?php if ($this->current_page == 'settings') { echo ' in collapse'; } else { echo ' collapse'; } ?>" id="collapseSettings">
                        <div class="accordion-inner">
                            <ul class="nav nav-list">
                                <?php if( in_array('general', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/settings'); ?>">General</a></li>
                                <?php endif; ?>
                                <?php if( in_array('email', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/settings/email'); ?>">Email</a></li>
                                <?php endif; ?>
                                <?php if( in_array('manage_ip', $this->session->userdata('user_privilage_name')) ): ?>
                                    <li><a href="<?php echo site_url('administrator/settings/admin_ip'); ?>">Manage IP</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <div class="push"></div>
        </div>

    </div></div></div>

</div><!--sidebar ends-->