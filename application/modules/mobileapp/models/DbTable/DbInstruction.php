<?php
class Mobileapp_Model_DbTable_DbInstruction extends Zend_Db_Table_Abstract
{
	protected $_name = 'moble_instruction';

	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}

	function getAllInstruction($search){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		
		$from_date =(empty($search['start_date']))? '1': "mba.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "mba.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;	
		$sql="SELECT mba.id,
		(SELECT ad.title FROM `moble_instruction_detail` AS ad WHERE ad.instruction_id = mba.`id` AND ad.lang=$lang LIMIT 1) AS title,
		mba.create_date,mba.status as status FROM $this->_name AS mba WHERE 1";
		if($search['search_status']>-1){
			$where.= " AND mba.status = ".$search['search_status'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]= " (SELECT ad.title FROM `moble_instruction_detail` AS ad WHERE ad.instruction_id = mba.`id` AND ad.lang=$lang LIMIT 1) LIKE '%{$s_search}%'";
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


	function  add($data){
      	$db = $this->getAdapter();
        $db->beginTransaction();
        try{
			
			$arr=array(
            		
                  	'create_date'=>$data['create_date'],
					'modify_date'=>date("Y-m-d H:i:s"),
					'user_id'=>$this->getUserId(),
            );
            $dbglobal = new Application_Model_DbTable_DbGlobal();
            $lang = $dbglobal->getLaguage();
        	 if (!empty($data['id'])){
				//$notificationId = $data['id'];
        	 	$arr['status']= $data['status'];
        	 	$where=" id=".$data['id'];
        	 	$this->_name="moble_instruction";
        	 	$this->update($arr, $where);
        	 	$article_id =$data['id'];
        	 	 
        	 	if(!empty($lang)){
        	 		$iddetail="";
        	 		foreach($lang as $row){
        	 			$title = str_replace(' ','',$row['title']);
        	 			if (empty($iddetail)){
        	 				if (!empty($data['iddetail'.$title])){
        	 					$iddetail=$data['iddetail'.$title];
        	 				}
        	 			}
        	 			else{
        	 				if (!empty($data['iddetail'.$title])){
        	 					$iddetail=$iddetail.",".$data['iddetail'.$title];
        	 				}
        	 			}
        	 		}
        	 		$this->_name="moble_instruction_detail";
        	 		$where1=" instruction_id =".$data['id'];
        	 		if (!empty($iddetail)){
        	 			$where1.=" AND id NOT IN (".$iddetail.")";
        	 		}
        	 		$this->delete($where1);
        	 			
        	 		foreach($lang as $row){
        	 			$title = str_replace(' ','',$row['title']);
        	 			if (!empty($data['iddetail'.$title])){
        	 				$arr_article = array(
        	 						'instruction_id'=>$article_id,
        	 						'title'=>$data['title'.$title],
        	 						'description'=>$data['description'.$title],
        	 						'lang'=>$row['id'],
        	 				);
        	 				$this->_name="moble_instruction_detail";
        	 				$wheredetail=" instruction_id=".$data['id']." AND id=".$data['iddetail'.$title];
        	 				$this->update($arr_article,$wheredetail);
        	 			}else{
        	 				$arr_article = array(
        	 						'instruction_id'=>$article_id,
        	 						'title'=>$data['title'.$title],
        	 						'description'=>$data['description'.$title],
        	 						'lang'=>$row['id'],
        	 				);
        	 				$this->_name="moble_instruction_detail";
        	 				$this->insert($arr_article);
        	 			}
        	 		}
        	 	}
        	 }
            $db->commit();
            
        }catch(exception $e){
            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
            $db->rollBack();
        }
 }
 
	//  function deleteData($id){
	//  	$db = $this->getAdapter();
	//  	try{
	//  		$this->_name = "mobile_notice";
	//  		$where=$this->getAdapter()->quoteInto("id=?", $id);
	//  		$this->delete($where);
	 		
	//  		$this->_name = "mobile_notice_detail";
	//  		$where=$this->getAdapter()->quoteInto("notification_id=?", $id);
	//  		$this->delete($where);
	//  	}catch(exception $e){
	//  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	//  		Application_Form_FrmMessage::message("Application Error");
	//  		$db->rollBack();
	//  	}
	//  }
	 
	 function getArticleTitleByLang($article_id,$lang){
	 	$db = $this->getAdapter();
	 	$sql="SELECT acd.id,acd.`title`,acd.`lang`,acd.description FROM `moble_instruction_detail` AS acd WHERE acd.`instruction_id`=$article_id AND acd.`lang`=$lang";
	 	return $db->fetchRow($sql);
	 }


}