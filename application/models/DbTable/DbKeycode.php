<?php

class Application_Model_DbTable_DbKeycode extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_setting';
 
	function getKeyCodeMiniInv($loginonly = FALSE){
		$db = $this->getAdapter();
		$sql = 'SELECT `keyName`,`keyValue` FROM `rms_setting`';
		if($loginonly){
			//$sql .= " WHERE `code` > 10";
		}
		$_result = $db->fetchAll($sql);
		$_k = array(); 
		foreach ($_result as $key => $k) {
			$_k[$k['keyName']] = $k['keyValue'];
		}
		return $_k;
	}
}

