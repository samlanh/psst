<?php

class Global_Model_DbTable_DbGroup extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_group';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    
	
	function getAllYears($is_completed=1){
		$db = new Application_Model_DbTable_DbGlobal();
		return $db->getAllYear(1,$is_completed);
	}
	
	
	public function getAllSubjectStudy($opt=null,$schoolOption=null){
		$_db = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$rows = $_db->getAllSubjectStudy($schoolOption);
		array_unshift($rows,array('id' => -1,"name"=>$tr->translate("ADD_NEW_SUBJECT"),"shortcut"=>""));
		if($opt!=null){return $rows;}
		$options = '<option value="0">'.$tr->translate("SELECT_SUBJECT").'</option>';
		if(!empty($rows))foreach($rows as $value){
			$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name']."-".$value['shortcut'], ENT_QUOTES).'</option>';
		}
		return $options;
	}
	
	
	
}

