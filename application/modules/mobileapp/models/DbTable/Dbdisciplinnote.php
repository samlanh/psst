<?php
class Mobileapp_Model_DbTable_Dbdisciplinnote extends Zend_Db_Table_Abstract
{
	protected $_name = 'mobile_disciplinenote';

	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	function getAllAttendencenote($search){
		$db=$this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "mba.createDate >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "mba.createDate <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;	
		$sql="SELECT mba.id,mba.title,mba.ordering,mba.createDate,mba.status FROM $this->_name AS mba WHERE 1";
		if($search['search_status']>-1){
			$where.= " AND mba.status = ".$search['search_status'];
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
                    'description' => $des,  
            		'ordering' => $data['ordering'],
                    'status' => 1,
					'modifyDate'=>date("Y-m-d H:i:s"),
					'userId'=>$this->getUserId(),
            );
         $this->_name;
        if(!empty($data['id'])){  
            $where = 'id='.$data['id'];          
           $this->update($_arr, $where);                     
        }else{
			$_arr['createDate']=date("Y-m-d H:i:s");
            $this->insert($_arr);
        }           
            $db->commit();
        }catch(exception $e){
            Application_Form_FrmMessage::message("Application Error");
            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
            $db->rollBack();
        }
 }
 function deleteData($id){
 	$db = $this->getAdapter();
 	try{
 		$this->_name = "mobile_disciplinenote";
 		$where=$this->getAdapter()->quoteInto("id=?", $id);
 		$this->delete($where);
 	}catch(exception $e){
 		Application_Form_FrmMessage::message("Application Error");
 		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
 		$db->rollBack();
 	}
 }


}