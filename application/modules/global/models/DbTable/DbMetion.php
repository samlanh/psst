<?php
class Global_Model_DbTable_DbMetion extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_metionscore_setting';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    public function getAllMentionSetting($search){
    	$db= $this->getAdapter();
    	$sql="SELECT ms.id,
			(SELECT CONCAT(tu.from_academic,'-',tu.to_academic,'(',tu.generation,')') FROM rms_tuitionfee AS tu 
			WHERE tu.`status`=1 AND tu.id = ms.academic_year GROUP BY tu.from_academic,tu.to_academic,tu.generation,tu.time LIMIT 1) AS `academic_year`,
			ms.title,
			(SELECT i.title FROM `rms_items` AS i WHERE i.type=1 AND i.id = ms.degree LIMIT 1) AS degree,
			ms.status,
			(SELECT first_name FROM `rms_users` WHERE id=ms.user_id LIMIT 1) as user_name 
			 FROM `rms_metionscore_setting` AS ms
    	WHERE
    	1
    	";
    	$where = "";
    	if(!empty($search['search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['search']));
    		$s_where[]= " title LIKE '%{$s_search}%'";
    		$s_where[]= " note LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if (!empty($search['academic_year'])){
    		$where.=" AND academic_year= ".$search['academic_year'];
    	}
    	if (!empty($search['degree'])){
    		$where.=" AND degree= ".$search['degree'];
    	}
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
	public function addMetionSetting($data){
    	$db= $this->getAdapter();
    	try{
    		$arr = array(
    				'academic_year'	=>$data['academic_year'],
    				'degree'		=>$data['degree'],
    				'title'			=>$data['title'],
    				'note'			=>$data['note'],
    				'status'		=>1,
    				'create_date'	=>date("Y-m-d H:i:s"),
    				'modify_date'	=>date("Y-m-d H:i:s"),
    				'user_id'		=>$this->getUserId(),
    		);
    		$this->_name='rms_metionscore_setting';
    		$id = $this->insert($arr);
    		
    		if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'metion_score_id'	=>$id,
// 							'min_score'			=>$data['min_score'.$i],
							'max_score'			=>$data['max_score'.$i],
							'metion_grade'		=>$data['metion_grade'.$i],
							'metion_in_khmer'	=>$data['metion_khmer'.$i],
							'mention_in_english'=>$data['metion_eng'.$i],
						);
					$this->_name='rms_metionscore_setting_detail';	
					$this->insert($arr);
				}
    		}
    	}catch(Exception $e){
    		echo $e->getMessage();
    	}
	}
	
	public function editMentionSettingID($data){
		$db= $this->getAdapter();
		try{
			$arr = array(
    				'academic_year'	=>$data['academic_year'],
    				'degree'		=>$data['degree'],
    				'title'			=>$data['title'],
    				'note'			=>$data['note'],
    				'status'		=>1,
    				'create_date'	=>date("Y-m-d H:i:s"),
    				'modify_date'	=>date("Y-m-d H:i:s"),
    				'user_id'		=>$this->getUserId(),
    		);
    		$this->_name='rms_metionscore_setting';
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
			
			$id = $data['id'];
			
			$identitys = explode(',',$data['identity']);
			$detailId="";
			if (!empty($identitys)){
				foreach ($identitys as $i){
					if (empty($detailId)){
						if (!empty($data['detailid'.$i])){
							$detailId = $data['detailid'.$i];
						}
					}else{
						if (!empty($data['detailid'.$i])){
							$detailId= $detailId.",".$data['detailid'.$i];
						}
					}
				}
			}
			$this->_name='rms_metionscore_setting_detail';
			$where = 'metion_score_id = '.$id;
			if (!empty($detailId)){
				$where.=" AND id NOT IN ($detailId) ";
			}
			$this->delete($where);
			
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					if (!empty($data['detailid'.$i])){
						$arr = array(
								'metion_score_id'	=>$id,
								//'min_score'			=>$data['min_score'.$i],
								'max_score'			=>$data['max_score'.$i],
								'metion_grade'		=>$data['metion_grade'.$i],
								'metion_in_khmer'	=>$data['metion_khmer'.$i],
								'mention_in_english'=>$data['metion_eng'.$i],
						);
						$this->_name='rms_metionscore_setting_detail';
						$where =" id =".$data['detailid'.$i];
						$this->update($arr, $where);
					}else{
						$arr = array(
								'metion_score_id'	=>$id,
								//'min_score'			=>$data['min_score'.$i],
								'max_score'			=>$data['max_score'.$i],
								'metion_grade'		=>$data['metion_grade'.$i],
								'metion_in_khmer'	=>$data['metion_khmer'.$i],
								'mention_in_english'=>$data['metion_eng'.$i],
						);
						$this->_name='rms_metionscore_setting_detail';
						$this->insert($arr);
					}
				}
			}
    	}catch(Exception $e){
    		echo $e->getMessage();
    	}
	}
	function getMentionSettingById($id=null){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_metionscore_setting WHERE 1 ";
		if (!empty($id)){
			$sql.=" AND id = $id LIMIT 1";
		}
		return $db->fetchRow($sql);
	}
	function getMentionSettingDetailById($id=null){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `rms_metionscore_setting_detail` WHERE 1 ";
		if (!empty($id)){
			$sql.=" AND metion_score_id = $id";
		}
		return $db->fetchAll($sql);
	}
	function checkMentionAlreayExist($data){
		$db = $this->getAdapter();
		$sql="SELECT id FROM `rms_metionscore_setting` WHERE academic_year=".$data['academic_year']." AND degree =".$data['degree'];
		if (!empty($data['id'])){
			$sql.=" AND id !=".$data['id'];
		}
		$row = $db->fetchOne($sql);
		if (!empty($row)) {
			return 1;
		}
		return null;
	}
}



