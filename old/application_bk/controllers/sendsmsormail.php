<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Sendsmsormail extends MY_Controller
{
   
    function __construct()
    {
        parent::__construct();
        $this->load->model('smsormail'); 
        $this->load->model('global/select_global_model');
        $this->load->model('global/update_global_model');  
    }

    public function index()
    {
        $logstatus = "failed";
        $sendData = $this->select_global_model->Select_array_rand_limit('exm_smsoremail_job',array('status'=>1),1);
        if($sendData){
            if(strtolower($sendData[0]['type'])=='sms'){
                $respons = $this->smsormail->smsUrl($sendData[0]['emailornumber'],$sendData[0]['message']);
                if($respons[3]['value']=='000'){ $logstatus = "Send"; }
                $this->update_global_model->globalupdate('exm_smsoremail_job',array('id'=>$sendData[0]['id']),array('status_log'=>$logstatus,'sending_time'=>date('Y-m-d H:i:s'),'status'=>2));
            }else{
                $sendstatus = $this->smsormail->emailSend($sendData[0]['emailornumber'],$sendData[0]['message'],$sendData[0]['subject']);
                if($sendstatus==1){ $logstatus = "Send"; }
                $this->update_global_model->globalupdate('exm_smsoremail_job',array('id'=>$sendData[0]['id']),array('status_log'=>$logstatus,'sending_time'=>date('Y-m-d H:i:s'),'status'=>2));
            }
        }
        
    }

}


 