<?php
class Mobileapp_Model_DbTable_Dbdisciplinnote extends Zend_Db_Table_Abstract
{
	protected $_name = 'mobile_disciplinenote';

	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllAttendencenote($search){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		
		$from_date =(empty($search['start_date']))? '1': "mba.createDate >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "mba.createDate <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;	
		$sql="SELECT mba.id,
		(SELECT ad.title FROM `mobile_disciplinenote_detail` AS ad WHERE ad.displicipline_id = mba.`id` AND ad.lang=$lang LIMIT 1) AS title,
		mba.ordering,mba.createDate,mba.status FROM $this->_name AS mba WHERE 1";
		if($search['search_status']>-1){
			$where.= " AND mba.status = ".$search['search_status'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]= " (SELECT ad.title FROM `mobile_disciplinenote_detail` AS ad WHERE ad.displicipline_id = mba.`id` AND ad.lang=$lang LIMIT 1) LIKE '%{$s_search}%'";
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
      	$db = $this->getAdapter();
        $db->beginTransaction();
        try{
            $arr=array(
            		'ordering' => $data['ordering'],
					'modifyDate'=>date("Y-m-d H:i:s"),
					'userId'=>$this->getUserId(),
            );
         	$dbglobal = new Application_Model_DbTable_DbGlobal();
            $lang = $dbglobal->getLaguage();
        	 if (!empty($data['id'])){
        	 	$arr['status']= $data['status'];
        	 	$where=" id=".$data['id'];
        	 	$this->_name="mobile_disciplinenote";
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
        	 		$this->_name="mobile_disciplinenote_detail";
        	 		$where1=" displicipline_id=".$data['id'];
        	 		if (!empty($iddetail)){
        	 			$where1.=" AND id NOT IN (".$iddetail.")";
        	 		}
        	 		$this->delete($where1);
        	 			
        	 		foreach($lang as $row){
        	 			$title = str_replace(' ','',$row['title']);
        	 			if (!empty($data['iddetail'.$title])){
        	 				$arr_article = array(
        	 						'displicipline_id'=>$article_id,
        	 						'title'=>$data['title'.$title],
        	 						'description'=>$data['description'.$title],
        	 						'lang'=>$row['id'],
        	 				);
        	 				$this->_name="mobile_disciplinenote_detail";
        	 				$wheredetail=" displicipline_id=".$data['id']." AND id=".$data['iddetail'.$title];
        	 				$this->update($arr_article,$wheredetail);
        	 			}else{
        	 				$arr_article = array(
        	 						'displicipline_id'=>$article_id,
        	 						'title'=>$data['title'.$title],
        	 						'description'=>$data['description'.$title],
        	 						'lang'=>$row['id'],
        	 				);
        	 				$this->_name="mobile_disciplinenote_detail";
        	 				$this->insert($arr_article);
        	 			}
        	 		}
        	 	}
        	 }else{
        	 	$this->_name="mobile_disciplinenote";
        	 	$arr['createDate']= date("Y-m-d H:i:s");
        	 	$arr['status']= 1;
        	 	$article_id = $this->insert($arr);
        	 
        	 	if(!empty($lang)) foreach($lang as $row){
        	 		$title = str_replace(' ','',$row['title']);
        	 		$arr_article = array(
        	 				'displicipline_id'=>$article_id,
        	 				'title'=>$data['title'.$title],
        	 				'description'=>$data['description'.$title],
        	 				'lang'=>$row['id'],
        	 		);
        	 		$this->_name="mobile_disciplinenote_detail";
        	 		$this->insert($arr_article);
        	 	}
        	 }       
            $db->commit();
        }catch(exception $e){
            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
            Application_Form_FrmMessage::message("Application Error");
            $db->rollBack();
        }
 }
 function deleteData($id){
 	$db = $this->getAdapter();
 	try{
 		$this->_name = "mobile_disciplinenote";
 		$where=$this->getAdapter()->quoteInto("id=?", $id);
 		$this->delete($where);
 		
 		$this->_name = "mobile_disciplinenote_detail";
 		$where=$this->getAdapter()->quoteInto("displicipline_id=?", $id);
 		$this->delete($where);
 		
 	}catch(exception $e){
 		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
 		Application_Form_FrmMessage::message("Application Error");
 		$db->rollBack();
 	}
 }

 function getArticleTitleByLang($article_id,$lang){
 	$db = $this->getAdapter();
 	$sql="SELECT acd.id,acd.`title`,acd.`lang`,acd.description FROM `mobile_disciplinenote_detail` AS acd WHERE acd.`displicipline_id`=$article_id AND acd.`lang`=$lang";
 	return $db->fetchRow($sql);
 }

}