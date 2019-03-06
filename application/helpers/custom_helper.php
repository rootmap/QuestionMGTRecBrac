<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('print_r_pre')) {

    function print_r_pre($var = array()) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        die();
    }
}



?>