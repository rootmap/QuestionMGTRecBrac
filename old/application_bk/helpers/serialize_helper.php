<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('maybe_serialize'))
{
    /**
     * Serialize data, if needed.
     *
     * @param mixed $data Data that might be serialized.
     * @return mixed A scalar data
     */
    function maybe_serialize( $data ) {
        if ( is_array( $data ) || is_object( $data ) ) {
            return serialize( $data );
        } else {
            return $data;
        }
    }
}

if ( ! function_exists('maybe_unserialize'))
{
    /**
     * Unserialize value only if it was serialized.
     *
     * @param string $original Maybe unserialized original, if is needed.
     * @return mixed Unserialized data can be any type.
     */
    function maybe_unserialize( $original ) {
        if ( is_serialized( $original ) ) {
            return @unserialize( $original );
        }
        return $original;
    }
}


if ( ! function_exists('is_serialized'))
{
    /**
     * Check value to find if it was serialized.
     *
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * @param mixed $data Value to check to see if was serialized.
     * @return bool False if not serialized and true if it was.
     */
    function is_serialized( $data ) {
        // if it isn't a string, it isn't serialized
        if ( ! is_string( $data ) )
            return false;
        $data = trim( $data );
        if ( 'N;' == $data )
            return true;
        $length = strlen( $data );
        if ( $length < 4 )
            return false;
        if ( ':' !== $data[1] )
            return false;
        $lastc = $data[$length-1];
        if ( ';' !== $lastc && '}' !== $lastc )
            return false;
        $token = $data[0];
        switch ( $token ) {
            case 's' :
                if ( '"' !== $data[$length-2] )
                    return false;
            case 'a' :
            case 'O' :
                return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
            case 'b' :
            case 'i' :
            case 'd' :
                return (bool) preg_match( "/^{$token}:[0-9.E-]+;\$/", $data );
        }
        return false;
    }
}

if (!function_exists('layoutPrefix')) {

    function layoutPrefix($returnType='') {
        $array=array('EXAMNAME','EXAMSTATUS','APPLICANTNAME','EXAMSCORE','EXAMDATE','LOGINID','PASSWORD');
        if(empty($returnType))
        {
            return "<font color='#f00'>".rtrim(implode(',', $array), ',')."</font>";
        }
        else
        {
            return $array;
        }
    }
}


if (!function_exists('layoutPrefixUser')) {

    function layoutPrefixUser($returnType='') {
        $array=array('USERNAME','LOGINID','PASSWORD');
        if(empty($returnType))
        {
            return "<font color='#f00'>".rtrim(implode(',', $array), ',')."</font>";
        }
        else
        {
            return $array;
        }
    }
}

if (!function_exists('loggedUserData')) {

    function loggedUserData($returnType='') {
        $CI = & get_instance();
        $varUser=(array)$CI->session->userdata('logged_in_user');
        
        //array_push($varUser,"name"=>$varFullName);
        if(empty($returnType))
        {
            return $varUser;
        }
        elseif($returnType=="name")
        {
            $varFullName=$varUser['user_first_name'].' '.$varUser['user_last_name'];
            return $varFullName;
        }
        else
        {

            return $varUser[$returnType];
        }
        
    }
}

if (!function_exists('isSystemAuditor')) {

    function isSystemAuditor($returnType='') {
        $CI = & get_instance();
        $varUser=(array)$CI->session->userdata('logged_in_user');
        
        //array_push($varUser,"name"=>$varFullName);
        if($varUser['user_type']=="System Auditor")
        {
            return true;
        }
        else
        {

            return false;
        }
        
    }
}


/* global date time format convertion start */
if ( ! function_exists('convert_to_datetime_format'))
{
    function convert_to_datetime_format($date_time = '')
    {
         $converted_date_time = '';
        if($date_time == '')
        {
            $converted_date_time = '';
        }
        else 
        {
            $converted_date_time=date("Y-m-d H:i:s", strtotime($date_time));
        }
        return $converted_date_time;
    }

}
/* global date time format convertion end  */

/* End of file serialize_helper.php */
/* Location: ./application/helpers/serialize_helper.php */