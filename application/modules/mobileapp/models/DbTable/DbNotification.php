<?php
class Mobileapp_Model_DbTable_DbNotification extends Zend_Db_Table_Abstract
{
	protected $_name = 'mobile_notice';

	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}

	function getAllNotification($search){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		
		$from_date =(empty($search['start_date']))? '1': "mba.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "mba.date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;	
		$sql="SELECT mba.id,
		(SELECT ad.title FROM `mobile_notice_detail` AS ad WHERE ad.notification_id = mba.`id` AND ad.lang=$lang LIMIT 1) AS title,
		(SELECT name_kh FROM `rms_view` WHERE type=34 AND key_code=mba.opt_notification LIMIT 1) AS option_type,
		mba.date,mba.status as status FROM $this->_name AS mba WHERE 1";
		if($search['search_status']>-1){
			$where.= " AND mba.status = ".$search['search_status'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]= " (SELECT ad.title FROM `mobile_notice_detail` AS ad WHERE ad.notification_id = mba.`id` AND ad.lang=$lang LIMIT 1) LIKE '%{$s_search}%'";
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
        	$content = array(
        		"en" =>$data['titleKhmer'],
        	);
        	
        	$data_notify = array(
        		"title" => $data['titleKhmer'],
        		"urlType" => 6
        	);
        	
        	$dbg = new Application_Model_DbTable_DbGlobal();
        	
        	if($data['opt_notification']==1){//All 
	        	$fields = array(
	        		'app_id' => APP_ID,
	        		'included_segments' => array('Active Users'),
	        		'data' => $data_notify,
	        		'contents' => $content,
	        		'headings'=>$content,
	        		//'subtitle'=>array('en'=>$data['descriptionKhmer']),
	        		//'template_id'=>'a13d6177-c4fd-4ba0-addf-07d9557b0460'
	        	);
	        	
// 	        	$fields = json_encode($fields);
	//         	print("\nJSON sent:\n");
	//         	print($fields);
	        	
// 	        	$ch = curl_init();
// 	        	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
// 	        	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
// 	        			'Authorization: Basic OGY3MGQ2M2EtMmQ3OS00MjZhLTk2MjYtYjYzMzExYTg5YWRm'));
// 	        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
// 	        	curl_setopt($ch, CURLOPT_HEADER, FALSE);
// 	        	curl_setopt($ch, CURLOPT_POST, TRUE);
// 	        	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
// 	        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	        	
// 	        	$response = curl_exec($ch);
// 	        	curl_close($ch);

        	}elseif($data['opt_notification']==2){//by group 
        		
        		$androidToken = $dbg->getTokenbyGroupId($data['group']);
        		
        		$fields = array(
        				'app_id' => APP_ID,
        				'include_player_ids' => $androidToken,
        				'data' => $data_notify,
        				'contents' => $content
        		);
        	}elseif($data['opt_notification']==3){//by student 
        		
        		$androidToken = $dbg->getTokenbyStudentId($data['student']);
        		
        		$fields = array(
        			'app_id' => APP_ID,
        			'include_player_ids' => $androidToken,
        			'data' => $data_notify,
        			'contents' => $content
        		);
        		
        	}elseif($data['opt_notification']==4){// by degree
        		
        		$androidToken = $dbg->getTokenbyDegreeId($data['degree']);
        		
        		$fields = array(
        			'app_id' => APP_ID,
        			'include_player_ids' => $androidToken,
        			'data' => $data_notify,
        			'contents' => $content
        		);
        	}
        	
        	
        	$fields = json_encode($fields);
//         	        	print("\nJSON sent:\n");
//         	        	print($fields);
        	
        	$ch = curl_init();
        	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
        			'Authorization: Basic OGY3MGQ2M2EtMmQ3OS00MjZhLTk2MjYtYjYzMzExYTg5YWRm'));
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        	curl_setopt($ch, CURLOPT_HEADER, FALSE);
        	curl_setopt($ch, CURLOPT_POST, TRUE);
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        	
        	$response = curl_exec($ch);
        	curl_close($ch);
        	
//         	print_r($response."dddd");exit();
        	
        	
        	
        	
            $arr=array(
            		'opt_notification' => $data['opt_notification'],
            		'degree' => empty($data['degree'])?"":$data['degree'],
            		'group' => empty($data['group'])?"":$data['group'],
            		'student' => empty($data['student'])?"":$data['student'],
                  	'date'=>$data['public_date'],
					'modify_date'=>date("Y-m-d H:i:s"),
					'user_id'=>$this->getUserId(),
            );
            $dbglobal = new Application_Model_DbTable_DbGlobal();
            $lang = $dbglobal->getLaguage();
        	 if (!empty($data['id'])){
        	 	$arr['status']= $data['status'];
        	 	$where=" id=".$data['id'];
        	 	$this->_name="mobile_notice";
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
        	 		$this->_name="mobile_notice_detail";
        	 		$where1=" notification_id=".$data['id'];
        	 		if (!empty($iddetail)){
        	 			$where1.=" AND id NOT IN (".$iddetail.")";
        	 		}
        	 		$this->delete($where1);
        	 			
        	 		foreach($lang as $row){
        	 			$title = str_replace(' ','',$row['title']);
        	 			if (!empty($data['iddetail'.$title])){
        	 				$arr_article = array(
        	 						'notification_id'=>$article_id,
        	 						'title'=>$data['title'.$title],
        	 						'description'=>$data['description'.$title],
        	 						'lang'=>$row['id'],
        	 				);
        	 				$this->_name="mobile_notice_detail";
        	 				$wheredetail=" notification_id=".$data['id']." AND id=".$data['iddetail'.$title];
        	 				$this->update($arr_article,$wheredetail);
        	 			}else{
        	 				$arr_article = array(
        	 						'notification_id'=>$article_id,
        	 						'title'=>$data['title'.$title],
        	 						'description'=>$data['description'.$title],
        	 						'lang'=>$row['id'],
        	 				);
        	 				$this->_name="mobile_notice_detail";
        	 				$this->insert($arr_article);
        	 			}
        	 		}
        	 	}
        	 }else{
        	 	$this->_name="mobile_notice";
        	 	$arr['create_date']= date("Y-m-d H:i:s");
        	 	$arr['status']= 1;
        	 	$article_id = $this->insert($arr);
        	 
        	 	if(!empty($lang)) foreach($lang as $row){
        	 		$title = str_replace(' ','',$row['title']);
        	 		$arr_article = array(
        	 				'notification_id'=>$article_id,
        	 				'title'=>$data['title'.$title],
        	 				'description'=>$data['description'.$title],
        	 				'lang'=>$row['id'],
        	 		);
        	 		$this->_name="mobile_notice_detail";
        	 		$this->insert($arr_article);
        	 	}
        	 }
            $db->commit();
            
        }catch(exception $e){
        	echo $e->getMessage();exit();
            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
            $db->rollBack();
        }

 }
 
	 function deleteData($id){
	 	$db = $this->getAdapter();
	 	try{
	 		$this->_name = "mobile_notice";
	 		$where=$this->getAdapter()->quoteInto("id=?", $id);
	 		$this->delete($where);
	 		
	 		$this->_name = "mobile_notice_detail";
	 		$where=$this->getAdapter()->quoteInto("notification_id=?", $id);
	 		$this->delete($where);
	 	}catch(exception $e){
	 		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	 		Application_Form_FrmMessage::message("Application Error");
	 		$db->rollBack();
	 	}
	 }
	 
	 function getArticleTitleByLang($article_id,$lang){
	 	$db = $this->getAdapter();
	 	$sql="SELECT acd.id,acd.`title`,acd.`lang`,acd.description FROM `mobile_notice_detail` AS acd WHERE acd.`notification_id`=$article_id AND acd.`lang`=$lang";
	 	return $db->fetchRow($sql);
	 }


}