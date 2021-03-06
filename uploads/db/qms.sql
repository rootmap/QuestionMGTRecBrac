USE [brac_qms]
GO
/****** Object:  Table [dbo].[exm_admin_groups]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_admin_groups](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[group_name] [varchar](200) NULL
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_categories]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_categories](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[cat_parent] [int] NULL,
	[cat_name] [varchar](200) NULL,
 CONSTRAINT [PK_exm_categories] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_ci_session]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_ci_session](
	[session_id] [varchar](200) NOT NULL,
	[ip_address] [varchar](200) NULL,
	[user_agent] [varchar](200) NULL,
	[last_activity] [int] NULL,
	[user_data] [text] NULL,
	[data] [text] NULL,
	[timestamp] [varchar](300) NULL,
 CONSTRAINT [PK_exm_ci_session] PRIMARY KEY CLUSTERED 
(
	[session_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_emails]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_emails](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[email_type] [varchar](300) NULL,
	[email_to] [varchar](300) NULL,
	[email_body] [text] NULL,
	[email_error] [text] NULL,
	[email_status] [tinyint] NULL,
	[email_time] [datetime] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_exam_category]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_exam_category](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[category_id] [int] NULL,
	[exam_id] [int] NULL,
	[no_of_questions] [int] NULL
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_exams]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_exams](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[exam_title] [varchar](300) NULL,
	[exam_description] [text] NULL,
	[exam_type] [varchar](300) NULL,
	[exam_time] [int] NULL,
	[exam_score] [int] NULL,
	[exam_per_page] [int] NULL,
	[exam_allow_previous] [tinyint] NULL,
	[exam_allow_pause] [tinyint] NULL,
	[exam_allow_dontknow] [tinyint] NULL,
	[exam_allow_negative_marking] [tinyint] NULL,
	[exam_negative_mark_weight] [int] NULL,
	[exam_allow_result_mail] [int] NULL,
	[exam_status] [varchar](300) NULL,
	[exam_added] [datetime] NULL,
	[exam_expiry_date] [datetime] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_group_privilage]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_group_privilage](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[group_id] [int] NULL,
	[privilage_id] [int] NULL,
	[group_id_for_pass] [varchar](300) NULL
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_logs]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_logs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[log_type] [varchar](300) NULL,
	[log_action] [varchar](300) NULL,
	[user_id] [int] NULL,
	[user_ip_address] [varchar](300) NULL,
	[user_agent] [varchar](300) NULL,
	[log_time] [datetime] NULL,
	[log_message] [text] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_options]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_options](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[option_name] [varchar](300) NULL,
	[option_value] [text] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_privilage]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_privilage](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[privilage_name] [varchar](300) NULL,
	[privilage_description] [varchar](300) NULL
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_questions]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_questions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[category_id] [int] NULL,
	[ques_text] [text] NULL,
	[ques_choices] [varchar](500) NULL,
	[ques_type] [varchar](300) NULL,
	[ques_added] [varchar](100) NULL,
	[ques_expiry_date] [datetime] NULL,
	[admin_group] [tinyint] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_user_exam_questions]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_user_exam_questions](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_exam_id] [bigint] NULL,
	[question_id] [bigint] NULL,
	[user_answer] [varchar](500) NULL
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_user_exams]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_user_exams](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NULL,
	[exam_id] [int] NULL,
	[ue_start_date] [date] NULL,
	[ue_end_date] [date] NULL,
	[ue_status] [varchar](300) NULL,
	[ue_state] [text] NULL,
	[ue_added] [datetime] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_user_groups]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_user_groups](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[group_name] [varchar](400) NULL
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_user_teams]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_user_teams](
	[id] [int] NULL,
	[group_id] [int] NULL,
	[team_name] [varchar](200) NULL
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_usermeta]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_usermeta](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NULL,
	[user_meta_key] [varchar](300) NULL,
	[user_meta_value] [text] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
/****** Object:  Table [dbo].[exm_users]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[exm_users](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_team_id] [bigint] NULL,
	[user_login] [varchar](300) NULL,
	[user_password] [varchar](300) NULL,
	[user_password_old] [varchar](300) NULL,
	[user_is_default_password] [tinyint] NULL,
	[user_first_name] [varchar](300) NULL,
	[user_last_name] [varchar](300) NULL,
	[user_email] [varchar](300) NULL,
	[user_registered] [datetime] NULL,
	[user_type] [varchar](300) NULL,
	[user_last_activity_time] [datetime] NULL,
	[user_failed_login_count] [int] NULL,
	[user_last_successful_login_time] [datetime] NULL,
	[user_last_failed_login_time] [datetime] NULL,
	[user_competency] [varchar](200) NULL,
	[user_is_lock] [tinyint] NULL,
	[user_is_active] [tinyint] NULL,
	[admin_group] [tinyint] NULL
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[group_name]    Script Date: 10-Dec-17 11:42:06 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[group_name](
	[id] [int] NULL,
	[group_name] [varchar](300) NULL
) ON [PRIMARY]

GO
SET IDENTITY_INSERT [dbo].[exm_categories] ON 

INSERT [dbo].[exm_categories] ([id], [cat_parent], [cat_name]) VALUES (1, 0, N'test')
INSERT [dbo].[exm_categories] ([id], [cat_parent], [cat_name]) VALUES (10, 0, N'Category 1')
INSERT [dbo].[exm_categories] ([id], [cat_parent], [cat_name]) VALUES (11, NULL, N'Demo Category')
INSERT [dbo].[exm_categories] ([id], [cat_parent], [cat_name]) VALUES (12, NULL, N'VAS')
SET IDENTITY_INSERT [dbo].[exm_categories] OFF
INSERT [dbo].[exm_ci_session] ([session_id], [ip_address], [user_agent], [last_activity], [user_data], [data], [timestamp]) VALUES (N'd5f22c37505c69f57493865393367d66', N'192.168.7.114', N'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36', 1512822050, N'', NULL, NULL)
INSERT [dbo].[exm_ci_session] ([session_id], [ip_address], [user_agent], [last_activity], [user_data], [data], [timestamp]) VALUES (N'ef765aceba15f5277e014cac57ac1d8e', N'192.168.7.114', N'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36', 1512812613, N'a:2:{s:19:"user_privilage_name";a:28:{i:0;s:12:"add_category";i:1;s:17:"manage_categories";i:2;s:12:"add_question";i:3;s:18:"add_bulk_questions";i:4;s:19:"edit_bulk_questions";i:5;s:16:"manage_questions";i:6;s:11:"create_exam";i:7;s:12:"manage_exams";i:8;s:20:"assign_exam_to_users";i:9;s:20:"assigned_exam_status";i:10;s:12:"exam_results";i:11;s:12:"user_results";i:12;s:9:"add_group";i:13;s:13:"manage_groups";i:14;s:8:"add_team";i:15;s:12:"manage_teams";i:16;s:15:"add_admin_group";i:17;s:19:"manage_admin_groups";i:18;s:8:"add_user";i:19;s:14:"add_bulk_users";i:20;s:15:"edit_bulk_users";i:21;s:17:"delete_bulk_users";i:22;s:12:"manage_users";i:23;s:14:"update_profile";i:24;s:15:"change_password";i:25;s:7:"general";i:26;s:5:"email";i:27;s:20:"user_password_change";}s:14:"logged_in_user";O:8:"stdClass":19:{s:2:"id";s:1:"1";s:12:"user_team_id";s:1:"0";s:10:"user_login";s:2:"sa";s:13:"user_password";s:32:"563bdf04b00f36817c2e54b43d5e42bb";s:17:"user_password_old";s:32:"c12e01f2a13ff5587e1e9e4aedb8242d";s:24:"user_is_default_password";i:0;s:15:"user_first_name";s:11:"Super Admin";s:14:"user_last_name";s:4:"User";s:10:"user_email";s:16:"sa@janaojana.com";s:15:"user_registered";s:23:"2012-12-24 14:36:56.000";s:9:"user_type";s:19:"Super Administrator";s:23:"user_last_activity_time";s:23:"2017-12-05 09:55:35.000";s:23:"user_failed_login_count";i:0;s:31:"user_last_successful_login_time";s:23:"2017-12-05 08:59:33.000";s:27:"user_last_failed_login_time";s:23:"2017-11-06 07:26:10.000";s:15:"user_competency";s:0:"";s:12:"user_is_lock";i:0;s:14:"user_is_active";i:1;s:11:"admin_group";N;}}', NULL, NULL)
SET IDENTITY_INSERT [dbo].[exm_options] ON 

INSERT [dbo].[exm_options] ([id], [option_name], [option_value]) VALUES (1, N'site_name', N'Brac Bank QMS')
INSERT [dbo].[exm_options] ([id], [option_name], [option_value]) VALUES (2, N'default_category', N'1')
INSERT [dbo].[exm_options] ([id], [option_name], [option_value]) VALUES (3, N'front_office_competency', N'a:6:{i:0;a:3:{s:5:"label";s:11:"Outstanding";s:5:"lower";i:95;s:6:"higher";i:100;}i:1;a:3:{s:5:"label";s:6:"Strong";s:5:"lower";i:85;s:6:"higher";i:94;}i:2;a:3:{s:5:"label";s:7:"Average";s:5:"lower";i:75;s:6:"higher";i:84;}i:3;a:3:{s:5:"label";s:17:"Needs improvement";s:5:"lower";i:65;s:6:"higher";i:74;}i:4;a:3:{s:5:"label";s:4:"Poor";s:5:"lower";i:21;s:6:"higher";i:64;}i:5;a:3:{s:5:"label";s:10:"Disqualify";s:5:"lower";i:0;s:6:"higher";i:20;}}')
INSERT [dbo].[exm_options] ([id], [option_name], [option_value]) VALUES (4, N'back_office_competency', N'a:5:{i:0;a:3:{s:5:"label";s:11:"Outstanding";s:5:"lower";i:86;s:6:"higher";i:100;}i:1;a:3:{s:5:"label";s:6:"Strong";s:5:"lower";i:71;s:6:"higher";i:85;}i:2;a:3:{s:5:"label";s:7:"Average";s:5:"lower";i:51;s:6:"higher";i:70;}i:3;a:3:{s:5:"label";s:17:"Needs improvement";s:5:"lower";i:36;s:6:"higher";i:50;}i:4;a:3:{s:5:"label";s:4:"Poor";s:5:"lower";i:0;s:6:"higher";i:35;}}')
INSERT [dbo].[exm_options] ([id], [option_name], [option_value]) VALUES (5, N'email_from_name', N'Knowledge Test')
INSERT [dbo].[exm_options] ([id], [option_name], [option_value]) VALUES (6, N'email_smtp_host', N'smtp.robi.com.bd')
INSERT [dbo].[exm_options] ([id], [option_name], [option_value]) VALUES (7, N'email_smtp_port', N'25')
INSERT [dbo].[exm_options] ([id], [option_name], [option_value]) VALUES (8, N'email_smtp_user', N'janaojana@robi.com.bd')
INSERT [dbo].[exm_options] ([id], [option_name], [option_value]) VALUES (9, N'failed_login_message', N'PLease Login by Correct User ID & Password')
INSERT [dbo].[exm_options] ([id], [option_name], [option_value]) VALUES (10, N'failed_login_count', N'5')
INSERT [dbo].[exm_options] ([id], [option_name], [option_value]) VALUES (11, N'user_inactivity_period', N'180')
INSERT [dbo].[exm_options] ([id], [option_name], [option_value]) VALUES (12, N'locked_login_message', N'Your account has been locked.')
SET IDENTITY_INSERT [dbo].[exm_options] OFF
SET IDENTITY_INSERT [dbo].[exm_privilage] ON 

INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (1, N'add_category', N'Add Category')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (2, N'manage_categories', N'Manage Categories')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (3, N'add_question', N'Add Question')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (4, N'add_bulk_questions', N'Add Bulk Questions')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (5, N'edit_bulk_questions', N'Edit Bulk Questions')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (6, N'manage_questions', N'Manage Questions')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (7, N'create_exam', N'Create Exam')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (8, N'manage_exams', N'Manage Exams')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (9, N'assign_exam_to_users', N'Assign Exam to Users')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (10, N'assigned_exam_status', N'Assigned Exam Status')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (11, N'exam_results', N'Exam Results')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (12, N'user_results', N'User Results')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (13, N'add_group', N'Add Group')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (14, N'manage_groups', N'Manage Groups')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (15, N'add_team', N'Add Team')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (16, N'manage_teams', N'Manage Teams')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (17, N'add_admin_group', N'Add Admin Group')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (18, N'manage_admin_groups', N'Manage Admin Groups')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (19, N'add_user', N'Add User')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (20, N'add_bulk_users', N'Add Bulk Users')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (21, N'edit_bulk_users', N'Edit Bulk Users')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (22, N'delete_bulk_users', N'Delete Bulk Users')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (23, N'manage_users', N'Manage Users')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (24, N'update_profile', N'Update Profile')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (25, N'change_password', N'Change Password')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (26, N'general', N'General')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (27, N'email', N'Email')
INSERT [dbo].[exm_privilage] ([id], [privilage_name], [privilage_description]) VALUES (28, N'user_password_change', N'User Password Change')
SET IDENTITY_INSERT [dbo].[exm_privilage] OFF
SET IDENTITY_INSERT [dbo].[exm_questions] ON 

INSERT [dbo].[exm_questions] ([id], [category_id], [ques_text], [ques_choices], [ques_type], [ques_added], [ques_expiry_date], [admin_group]) VALUES (1, 10, N'Question 1', N'a:2:{i:0;a:2:{s:4:"text";s:8:"Question";s:9:"is_answer";i:1;}i:1;a:2:{s:4:"text";s:9:"Question1";s:9:"is_answer";i:0;}}', N'mcq', N'2017-12-05 09:39:31', CAST(N'2017-12-05T00:00:00.000' AS DateTime), NULL)
INSERT [dbo].[exm_questions] ([id], [category_id], [ques_text], [ques_choices], [ques_type], [ques_added], [ques_expiry_date], [admin_group]) VALUES (2, 10, N'Question 1', N'a:2:{i:0;a:2:{s:4:"text";s:11:"Question444";s:9:"is_answer";i:1;}i:1;a:2:{s:4:"text";s:12:"fsdfs sdfsfs";s:9:"is_answer";i:1;}}', N'mcq', N'2017-12-05 09:40:34', CAST(N'2017-12-13T00:00:00.000' AS DateTime), NULL)
SET IDENTITY_INSERT [dbo].[exm_questions] OFF
SET IDENTITY_INSERT [dbo].[exm_users] ON 

INSERT [dbo].[exm_users] ([id], [user_team_id], [user_login], [user_password], [user_password_old], [user_is_default_password], [user_first_name], [user_last_name], [user_email], [user_registered], [user_type], [user_last_activity_time], [user_failed_login_count], [user_last_successful_login_time], [user_last_failed_login_time], [user_competency], [user_is_lock], [user_is_active], [admin_group]) VALUES (1, 0, N'sa', N'563bdf04b00f36817c2e54b43d5e42bb', N'c12e01f2a13ff5587e1e9e4aedb8242d', 0, N'Super Admin', N'User', N'sa@janaojana.com', CAST(N'2012-12-24T14:36:56.000' AS DateTime), N'Super Administrator', CAST(N'2017-12-09T10:47:37.000' AS DateTime), 0, CAST(N'2017-12-09T10:28:18.000' AS DateTime), CAST(N'2017-11-06T07:26:10.000' AS DateTime), N'', 0, 1, NULL)
INSERT [dbo].[exm_users] ([id], [user_team_id], [user_login], [user_password], [user_password_old], [user_is_default_password], [user_first_name], [user_last_name], [user_email], [user_registered], [user_type], [user_last_activity_time], [user_failed_login_count], [user_last_successful_login_time], [user_last_failed_login_time], [user_competency], [user_is_lock], [user_is_active], [admin_group]) VALUES (2, 167, N'10000041', N'1b138844a6904342edc128669c2b58ee', N'6b82873cf5638f9073ec259133eb625f', 1, N'Mohammed', N'Soliman Demirel', N'demi@robi.com.bd', CAST(N'2012-12-24T14:36:56.000' AS DateTime), N'User', CAST(N'2014-04-28T19:29:14.000' AS DateTime), 0, CAST(N'2014-04-28T19:18:42.000' AS DateTime), CAST(N'2014-04-28T19:18:28.000' AS DateTime), N'Back Office', 0, 0, NULL)
SET IDENTITY_INSERT [dbo].[exm_users] OFF
ALTER TABLE [dbo].[exm_exams] ADD  CONSTRAINT [DF_exm_exams_exam_added]  DEFAULT (getdate()) FOR [exam_added]
GO
ALTER TABLE [dbo].[exm_logs] ADD  CONSTRAINT [DF_exm_logs_log_time]  DEFAULT (getdate()) FOR [log_time]
GO
ALTER TABLE [dbo].[exm_questions] ADD  CONSTRAINT [DF_exm_questions_ques_expiry_date]  DEFAULT (getdate()) FOR [ques_expiry_date]
GO
ALTER TABLE [dbo].[exm_user_exams] ADD  CONSTRAINT [DF_exm_user_exams_ue_added]  DEFAULT (getdate()) FOR [ue_added]
GO
ALTER TABLE [dbo].[exm_users] ADD  CONSTRAINT [DF_exm_users_user_registered]  DEFAULT (getdate()) FOR [user_registered]
GO
ALTER TABLE [dbo].[exm_users] ADD  CONSTRAINT [DF_exm_users_user_last_activity_time]  DEFAULT (getdate()) FOR [user_last_activity_time]
GO
ALTER TABLE [dbo].[exm_users] ADD  CONSTRAINT [DF_exm_users_user_last_successful_login_time]  DEFAULT (getdate()) FOR [user_last_successful_login_time]
GO
ALTER TABLE [dbo].[exm_users] ADD  CONSTRAINT [DF_exm_users_user_last_failed_login_time]  DEFAULT (getdate()) FOR [user_last_failed_login_time]
GO
