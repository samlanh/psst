<?php
class Mobileapp_Model_DbTable_DbCategory extends Zend_Db_Table_Abstract
{
	protected $_name = 'mobile_category_video';

public static function getUserId(){
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	return $dbg->getUserId();
    }
    public function getAllArticle($search=null,$parent = 0, $spacing = '', $cate_tree_array = ''){
    	$db=$this->getAdapter();
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$label = $this->tr->translate("ALL_BRANCH");
    	$lang = $dbp->currentlang();
    	$sql="SELECT
		    	act.`id`,
		    	act.parent,
		    	(SELECT ad.title FROM `mobile_category_video_detail` AS ad WHERE ad.news_id = act.`id` AND ad.lang=$lang LIMIT 1) AS title,
		    	act.`created_date`,
		    	(SELECT u.first_name FROM `rms_users` AS u WHERE u.id = act.`user_id` LIMIT 1) AS user_name
		    	";
    	
    	
    	$sql.=$dbp->caseStatusShowImage("act.`status`");
    	$sql.=" FROM `mobile_category_video` AS act WHERE 1  AND parent = $parent ";
    	
    	$where='';
    	$from_date =(empty($search['start_date']))? '1': " act.created_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " act.created_date <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['adv_search']));
	    	$s_where[] = " (SELECT ad.title FROM `mobile_category_video_detail` AS ad WHERE ad.news_id = act.`id` AND ad.lang=$lang LIMIT 1) LIKE '%{$s_search}%'";
	    	$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status_search']>-1){
    		$where .=' AND act.`status` = '.$search['status_search'];
    	}
    	$order = "  ORDER BY act.`id` DESC";
    	$rows = $db->fetchAll($sql.$where.$order);
    	
    	if (!is_array($cate_tree_array))
    		$cate_tree_array = array();
    	if (count($rows) > 0) {
    		foreach ($rows as $row){
    			$cate_tree_array[] = array("id" => $row['id'],"parent" => $row['parent'], "title" => $spacing . $row['title'],"created_date" => $row['created_date'],"user_name" => $row['user_name'],"status" => $row['status']);
    			$cate_tree_array = $this->getAllArticle($search,$row['id'], $spacing . ' - ', $cate_tree_array);
    		}
    	}
    	return $cate_tree_array;
    }
    
    function addCategory($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$dbglobal = new Application_Model_DbTable_DbGlobal();
    		$lang = $dbglobal->getLaguage();
    		$arr = array(
    				'parent'	=>$data['parent'],
    				'ordering'	=>$data['ordering'],
    				'modify_date'	=>date("Y-m-d H:i:s"),
    				'user_id'		=>$this->getUserId(),
    		);
    		
    		if (!empty($data['id'])){
    			$arr['status']		=$data['status'];
    			$where=" id=".$data['id'];
    			$this->_name="mobile_category_video";
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
    	    
     				$this->_name="mobile_category_video_detail";
     				$where1=" news_id=".$data['id'];
    				if (!empty($iddetail)){
    					$where1.=" AND id NOT IN (".$iddetail.")";
    				}
    				$this->delete($where1);
    	    
    				foreach($lang as $row){
    					$title = str_replace(' ','',$row['title']);
    					if (!empty($data['iddetail'.$title])){
    						$arr_article = array(
    								'news_id'=>$article_id,
		    						'title'=>$data['title'.$title],
		    						'description'=>$data['description'.$title],
		    						'lang'=>$row['id'],
    						);
    						$this->_name="mobile_category_video_detail";
    						$wheredetail=" news_id=".$data['id']." AND id=".$data['iddetail'.$title];
    						$this->update($arr_article,$wheredetail);
    					}else{
    						$arr_article = array(
    								'news_id'=>$article_id,
		    						'title'=>$data['title'.$title],
		    						'description'=>$data['description'.$title],
		    						'lang'=>$row['id'],
    						);
    						$this->_name="mobile_category_video_detail";
    						$this->insert($arr_article);
    					}
    				}
    			}
    		}else{
    			$this->_name="mobile_category_video";
    			$arr['status']		=1;
    			$arr['created_date']= date("Y-m-d H:i:s");
    			$article_id = $this->insert($arr);
    
    			if(!empty($lang)) foreach($lang as $row){
    				$title = str_replace(' ','',$row['title']);
    				$arr_article = array(
    						'news_id'=>$article_id,
    						'title'=>$data['title'.$title],
    						'description'=>$data['description'.$title],
    						'lang'=>$row['id'],
    				);
    				$this->_name="mobile_category_video_detail";
    				$this->insert($arr_article);
    			}
    		}
    		$db->commit();
    	}catch(exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function getCategoryById($id){
    	$db= $this->getAdapter();
    	$sql="SELECT * FROM $this->_name WHERE id =".$id;
    	return $db->fetchRow($sql);
    }
    function getArticleTitleByLang($article_id,$lang){
    	$db = $this->getAdapter();
    	$sql="SELECT acd.id,acd.`title`,acd.`lang`,acd.description FROM `mobile_category_video_detail` AS acd WHERE acd.`news_id`=$article_id AND acd.`lang`=$lang";
    	return $db->fetchRow($sql);
    }
    function deleteNews($id){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    	$where=" id= $id";
	    	$this->delete($where);
	    	
	    	$this->_name="mobile_category_video_detail";
	    	$where=" news_id= $id";
	    	$this->delete($where);
	    	$db->commit();
    	}catch(exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    
    public function getAllCategoryList($parent = 0, $spacing = '', $cate_tree_array = ''){
    	$db=$this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$lang = $dbp->currentlang();
    	$sql="SELECT
	    	act.`id`,
	    	(SELECT ad.title FROM `mobile_category_video_detail` AS ad WHERE ad.news_id = act.`id` AND ad.lang=$lang LIMIT 1) AS name,
	    	parent
    	";
    	$sql.=" FROM `mobile_category_video` AS act WHERE act.`status`=1 AND parent = $parent ";
    	$rows = $db->fetchAll($sql);
    	 
    	if (!is_array($cate_tree_array)){
    		$cate_tree_array = array();
    	}
    	if (count($rows) > 0) {
	    	foreach ($rows as $row){
		    	$cate_tree_array[] = array("id" => $row['id'],"parent" => $row['parent'], "name" => $spacing . $row['name']);
		    	$cate_tree_array = $this->getAllCategoryList($row['id'], $spacing . ' - ', $cate_tree_array);
	    	}
    	}
    	return $cate_tree_array;
    }
    	
    public function getAllCategoryListParent($id=0){
    	$db=$this->getAdapter();
    	 
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$lang = $dbp->currentlang();
    	$sql="SELECT
    	act.`id`,
    	(SELECT ad.title FROM `mobile_category_video_detail` AS ad WHERE ad.news_id = act.`id` AND ad.lang=$lang LIMIT 1) AS name
    	";
    	$sql.=" FROM `mobile_category_video` AS act WHERE act.`status`=1 AND parent = 0 AND act.`id`!= $id";
    	return $db->fetchAll($sql);
    }

    function getChildCategory($id,$idetity=null){
    	$where='';
    	$db = $this->getAdapter();
    	$sql=" SELECT c.`id` FROM `mobile_category_video` AS c WHERE c.`parent` = $id AND c.`status`=1 ";
    	$child = $db->fetchAll($sql);
    	foreach ($child as $va) {
    		if (empty($idetity)){
    			$idetity=$id.",".$va['id'];
    		}else{$idetity=$idetity.",".$va['id'];
    		}
    		$idetity = $this->getChildCategory($va['id'],$idetity);
    	}
    	return $idetity;
    }

}