<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "login";
$route['404_override'] = '';

$route['administrator/category/(:num)'] = "administrator/category/index/$1";
$route['administrator/question/(:num)'] = "administrator/question/index/$1";
$route['administrator/exam/(:num)'] = "administrator/exam/index/$1";
$route['administrator/user/(:num)'] = "administrator/user/index/$1";
$route['administrator/team/(:num)'] = "administrator/team/index/$1";
$route['administrator/group/(:num)'] = "administrator/group/index/$1";
$route['administrator/result/(:num)'] = "administrator/result/index/$1";
$route['administrator/result/(:num)/(:num)'] = "administrator/result/index/$1/$2";
$route['administrator/result_team/(:num)'] = "administrator/result_team/index/$1";
$route['administrator/assign_status/(:num)'] = "administrator/assign_status/index/$1";

$route['openSrv'] = "login/openSrv";

$route['completeSurveyNoUser/(:num)'] = "login/completeSurveyNoUser/$1";
$route['startSurveyNoUser/(:num)'] = "login/startSurveyNoUser/$1";
$route['Getsurveylist/(:num)'] = "login/Getsurveylist/$1";



//$route['administrator/result_team/show_results/(:num)'] = "administrator/result_team/show_results/$1";

$route['download/(:any)'] = "download/index/$1";

/* End of file routes.php */
/* Location: ./application/config/routes.php */



/***************qms*******************/

$route['getcategoryquestion/(:num)'] = "qms/qmsglobalcontroller/getcategoryquestion/$1";
$route['getcategorysurveyquestion/(:num)'] = "qms/qmsglobalcontroller/getcategorysurveyquestion/$1";
$route['getcategoryquestions/(:any)'] = "qms/qmsglobalcontroller/getcategoryquestions/$1";
$route['create_question_pool'] = "administrator/exam/create_question_pool";
$route['create_question_pool/(:any)'] = "administrator/exam/create_question_pool/$1";
$route['questionpoollist'] = "administrator/exam/questionpoollist";
$route['questionpoollist/(:any)'] = "administrator/exam/questionpoollist/$1";

$route['changeQuestionstatus/(:any)'] = "qms/qmsglobalcontroller/changeQuestionstatus/$1";
$route['changeQuestionstatusquestionpending/(:any)'] = "qms/qmsglobalcontroller/changeQuestionstatusquestionpending/$1";

$route['changeQuestionstatusquestionreject/(:any)'] = "qms/qmsglobalcontroller/changeQuestionstatusquestionreject/$1";
$route['changeQuestionstatusquestionappreov/(:any)'] = "qms/qmsglobalcontroller/changeQuestionstatusquestionappreov/$1";

$route['question_set'] = "administrator/question_set";
$route['question_set/(:any)'] = "administrator/question_set/$1";



$route['administrator/questionpending'] = "administrator/question/questionPending";
$route['administrator/questionpending/(:num)'] = "administrator/question/questionPending/$1";

//$route['questionpending'] = "administrator/question/questionPending";
//$route['questionpending/(:num)'] = "administrator/question/questionPending/$1";
$route['administrator/questionapproved'] = "administrator/question/questionApproved";
$route['administrator/questionapproved/(:num)'] = "administrator/question/questionApproved/$1";

$route['administrator/questionrejected'] = "administrator/question/questionRejected";
$route['administrator/questionrejected/(:num)'] = "administrator/question/questionRejected/$1";

$route['appreject'] = "administrator/survey_question/appreject/";
$route['appreject/(:any)/(:any)'] = "administrator/survey_question/appreject/$1/$2";
$route['administrator/survey_report/summary/(:num)'] = "administrator/survey_report/summary/$1";
