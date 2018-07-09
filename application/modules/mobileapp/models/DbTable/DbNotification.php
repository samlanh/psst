<?php
class Mobileapp_Model_DbTable_DbNotification extends Zend_Db_Table_Abstract
{
	protected $_name = 'mobile_notice';

	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}

	function getAllNotification($search){
		$db=$this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "mba.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "mba.date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;	
		$sql="SELECT mba.id,mba.title,mba.date,mba.active as status FROM $this->_name AS mba WHERE 1";
		if($search['search_status']>-1){
			$where.= " AND mba.active = ".$search['search_status'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]= " mba.title LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		$order = " ORDER BY mba.id DESC";
		return $db->fetchAll($sql.$where.$order);
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
            		'opt_notification' => $data['opt_notification'],
            		'group' => empty($data['group'])?"":$data['group'],
            		'student' => empty($data['student'])?"":$data['student'],
                    'description' => $des,                  
                    'active' => 1,//use instead status
                    'date'=>date("Y-m-d H:i:s"),
					'modify_date'=>date("Y-m-d H:i:s"),
					'user_id'=>$this->getUserId(),
					'status' => 1,					
            );
         $this->_name;
        if(!empty($data['id'])){  
            // var_dump($_arr);exit();                            
            $where = 'id='.$data['id'];          
           $this->update($_arr, $where);                     
        }else{
       		
			$_arr['create_date']=date("Y-m-d H:i:s");
            $id =  $this->insert($_arr);
           
            $dbpush = new  Application_Model_DbTable_DbGlobal();
            if ($data['opt_notification']==2){
            	$token =	$dbpush->getTokenUser($data['group']);
            }else if ($data['opt_notification']==3){
            	$token = $dbpush->getTokenUser(null,$data['student'],6);
            }else{
            	$token = $dbpush->getTokenUser(null,null,6);
            }
        }           
            $db->commit();
        }catch(exception $e){
            Application_Form_FrmMessage::message("Application Error");
            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
            $db->rollBack();
        }

 }
 
// 	 function getTokenStudentGroup($group_id){
// 	 	$db = $this->getAdapter();
// 	 	$sql="
// 	 		SELECT mb.`token`
// 			FROM `rms_group_detail_student` AS sd
// 			,`mobile_mobile_token` AS mb
// 			WHERE sd.`group_id` = $group_id
// 			AND sd.`stu_id` = mb.`stu_id`
// 	 	";
// 	 	return $db->fetchCol($sql);
// 	 }
	 function deleteData($id){
	 	$db = $this->getAdapter();
	 	try{
	 		$where=$this->getAdapter()->quoteInto("id=?", $id);
	 		$this->delete($where);
	 	}catch(exception $e){
	 		Application_Form_FrmMessage::message("Application Error");
	 		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	 		$db->rollBack();
	 	}
	 }


}