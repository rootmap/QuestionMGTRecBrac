<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['per_page'] = 50;
$config['uri_segment'] = 3;
$config['uri_new_segment'] = 2;
$config['num_links'] = 4;

$config['full_tag_open'] = '<div class="pagination"><ul>';
$config['full_tag_close'] = '</ul></div>';

$config['num_tag_open'] = '<li>';
$config['num_tag_close'] = '</li>';

$config['cur_tag_open'] = '<li class="active"><a href="javascript:void(0)">';
$config['cur_tag_close'] = '</a></li>';

$config['first_link'] = '&laquo First';
//$config['first_link'] = FALSE;
$config['first_tag_open'] = '<li>';
$config['first_tag_close'] = '</li>';

$config['last_link'] = 'Last &raquo';
//$config['last_link'] = FALSE;
$config['last_tag_open'] = '<li>';
$config['last_tag_close'] = '</li>';

$config['prev_link'] = '&laquo;';
$config['prev_tag_open'] = '<li>';
$config['prev_tag_close'] = '</li>';

$config['next_link'] = '&raquo;';
$config['next_tag_open'] = '<li>';
$config['next_tag_close'] = '</li>';

/* End of file pagination.php */
/* Location: ./application/config/pagination.php */