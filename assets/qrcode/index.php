<?php    

    
    //set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    
    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';

    include "qrlib.php";    
    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
    
    
    $filename = $PNG_TEMP_DIR.'test.png';

    $errorCorrectionLevel = 'H';
    
    $matrixPointSize = 10;

    $baseGetName="QMS";
    if (isset($_REQUEST['data'])) { 
        $baseGetName=$_REQUEST['data'];        
    }

    $filename = $PNG_TEMP_DIR.'BRAC'.time().'.png';
    /*QRcode::png($_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);  */
    if(!file_exists($PNG_WEB_DIR.basename($filename)))
    {
            $backColor = 0xFFFF00;
            $foreColor = 0xFF00FF;
            QRcode::png($_REQUEST['data'], $filename, $errorCorrectionLevel,$matrixPointSize);
    }
    
        
    //display generated file
    echo $PNG_WEB_DIR.basename($filename);
    




    