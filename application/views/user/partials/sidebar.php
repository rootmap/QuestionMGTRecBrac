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
                                    <li><a href="<?php echo site_url('administrator/question'); ?>">Manage Questions</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                
                <?php if( in_array('create_exam', $this->session->userdata('user_privilage_name')) 
                        || in_array('manage_exams', $this->session->userdata('user_privilage_name')) 
                        || in_array('assign_exam_to_users', $this->session->userdata('user_privilage_name')) 
                        || in_array('assigned_exam_status', $this->session->userdata('user_privilage_name')) ): ?>    
                <div class="accordion-group">
                    <div class="accordion-heading <?php if ($this->current_page == 'exam' || $this->current_page == 'assign-exam' || $this->current_page == 'assign-status') { echo ' sdb_h_active'; }?>">
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
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if( in_array('update_profile', $this->session->userdata('user_privilage_name'))
                       || in_array('change_password', $this->session->userdata('user_privilage_name')) ): ?>
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