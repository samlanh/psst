<?php
class Mobileapp_Model_DbTable_DbNotice extends Zend_Db_Table_Abstract
{
	protected $_name = 'mobile_notice';

	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}


	public function getById($id)
	{
		$db=$this->getAdapter();
        $sql="SELECT *  FROM ".$this->_name." WHERE id = ".$db->quote($id);
        $sql.=" LIMIT 1 ";
        $row=$db->fetchRow($sql);
        return $row;
	}


	function add($data){

		if(!empty($data['description'])){
			$des =  $data['description'];
		}else{
			$des = '';
		}

      	$db = $this->getAdapter();
        $db->beginTransaction();
        try{
            
            $_arr=array(
                    'title' => $data['title'],   
                    'all' => $data['all'], 
                    'level' => $data['level'],                     
                    'description' => $des,                  
                    'status' => 1,
                    'date'=>date("Y-m-d H:i:s")                    
            );
         $this->_name;
        if(!empty($data['id'])){  
            // var_dump($_arr);exit();                            
            $where = 'id='.$data['id'];          
           $this->update($_arr, $where);                     
        }else{
            $this->insert($_arr);
        }           
            $db->commit();
        }catch(exception $e){
            Application_Form_FrmMessage::message("Application Error");
            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
            $db->rollBack();
        }

 }
 


}