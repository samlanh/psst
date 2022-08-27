<?php

class Application_Model_DbTable_DbUserLog extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_user_log';

	public function insertLogin($user_id){
		$ipaddress = $this->getUserIP();
    	$data=array(
    				'log_date'=>date("Y/m/d , H:i:s"),
    				'log_type'=>'in',
    				'user_id'=>$user_id	,
    				'ipaddress'=>empty($ipaddress)?"":$ipaddress,
    	           );
    	 $this->insert($data);
    }
    function getUserIP(){ // get current ip
    	$client  = @$_SERVER['HTTP_CLIENT_IP'];
    	$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    	$remote  = $_SERVER['REMOTE_ADDR'];
    	if(filter_var($client, FILTER_VALIDATE_IP))
    	{
    		$ip = $client;
    	}
    	elseif(filter_var($forward, FILTER_VALIDATE_IP))
    	{
    		$ip = $forward;
    	}
    	else
    	{
    		$ip = $remote;
    	}
    	return $ip;
    }
    
    public function insertLogout($user_id)
    {
    	$data=array(
    					'log_date'=>date("Y/m/d , H:i:s"),
    					'log_type'=>'out',
    					'user_id'=>$user_id	
    	           );    	 
    	 $this->insert($data);
    }
    public static function writeMessageError($err)
    {
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$action=$request->getActionName();
    	$controller=$request->getControllerName();
    	$module=$request->getModuleName();
    
    	$session = new Zend_Session_Namespace(SYSTEM_SES);
    	$user_name = $session->user_name;
    
    	$file = "../logs/user.log";
    	if (!file_exists($file)) touch($file);
    	$Handle = fopen($file, 'a');
    	$stringData = "[".date("Y-m-d H:i:s")."]"." user name=>".$user_name." module=>".$module." controller name=>: ".$controller. " action =>".$action." error name=>".$err. "\n";
    	fwrite($Handle, $stringData);
    	fclose($Handle);
    }
}

