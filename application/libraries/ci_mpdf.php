<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 *  Author     : Arif Uddin
 *  Email      : mail2rupok@gmail.com
 */

require_once APPPATH ."/third_party/mpdf/mpdf.php";

class CI_mpdf extends mPDF
{
    function __construct()
    {
        parent::__construct();
    }
	
	function load($param=NULL)

	{

		include_once APPPATH.'/third_party/mpdf/mpdf.php'; 
		if ($params == NULL) 
		{ 
			$param = '"en-GB-x","A4","","",10,10,10,10,6,3'; 
		} 
		return new mPDF($param);

	}
}

/* End of file ci_mpdf.php */
/* Location: ./application/libraries/ci_mpdf.php */