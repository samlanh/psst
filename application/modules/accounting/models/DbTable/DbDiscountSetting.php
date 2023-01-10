<?php
class Accounting_Model_DbTable_DbDiscountSetting extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_dis_setting';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
    function getAllDiscountset($search){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    
    	$currentLang = $dbp->currentlang();
    	$colunmname='title_en';
    	if ($currentLang==1){
    		$colunmname='title';
    	}
    
    	$sql = "SELECT
	    	 g.discount_id AS id,
	    	(SELECT branch_nameen FROM `rms_branch` WHERE br_id=g.branch_id LIMIT 1) AS branch,
	    	(SELECT name_en FROM `rms_view` WHERE type=35 AND key_code=g.discountOption LIMIT 1)AS discountOption,
	
	    	(SELECT rms_items.$colunmname FROM rms_items WHERE rms_items.id=g.itemType LIMIT 1) AS itemType,
	    	(SELECT rms_itemsdetail.$colunmname FROM rms_itemsdetail WHERE rms_itemsdetail.id =`g`.`itemId` LIMIT 1) AS itemDetail,
	    	(SELECT dis_name AS NAME FROM `rms_discount` WHERE disco_id=g.discountType LIMIT 1) AS disc_name,
	    	CONCAT(g.discountValue,
	    	(CASE
		    	WHEN DisValueType=1 THEN '%'
		    	WHEN DisValueType=2 THEN '$'
	    	END )) AS DisValueType,
			(SELECT CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,''))
    		FROM rms_student AS s
    		WHERE s.stu_id=g.studentId LIMIT 1) AS studentName,
	    	g.start_date,
	    	g.end_date,
	    	(SELECT  CONCAT(first_name) FROM rms_users WHERE id=g.user_id LIMIT 1 ) AS user_name
    	";
    	$sql.=$dbp->caseStatusShowImage("g.status");
    	$sql.=" FROM rms_dis_setting AS g ";
    
    	$order = ' ORDER BY id DESC ';
    	$where = ' WHERE discountType!="" ';
    
     if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " (SELECT dis_name AS name FROM `rms_discount` WHERE disco_id=discountType LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " discountValue LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
     }
     if(!empty($search['branch_id'])){
    		$where.=' AND g.branch_id='.$search['branch_id'];
     }
     if(!empty($search['studentId'])){
    		$where.=' AND g.studentId='.$search['studentId'];
     }
     if(!empty($search['discountId'])){
     	$where.=' AND g.discountType='.$search['discountId'];
     }
   	 if($search['status_search']>-1){
    		$where.=' AND status='.$search['status_search'];
     }
    
    	$where.=$dbp->getAccessPermission('g.branch_id');
    	return $db->fetchAll($sql.$where.$order);
    }
	public function addNewDiscountset($_data){
		$db = $this->getAdapter();
  		try{
// 			$sql="SELECT discount_id FROM rms_dis_setting WHERE 
// 				discountOption=".$_data['discountOption']." AND discountType =".$_data['disname_id'];
// 			$sql.=" AND discountValue='".$_data['discountValue']."'";
// 			$rs = $db->fetchOne($sql);
// 			if(!empty($rs)){
// 				return -1;
// 			}		
	
		$_arr=array(
				'branch_id'   => $_data['branch_id'],
				'studentId'	=>	$_data['studentId'],
				'discountOption'=>$_data['discountOption'],
				'discountType'  => $_data['disname_id'],
				'itemType'  => $_data['itemType'],
				'itemId'  => $_data['itemId'],
				'DisValueType' => $_data['DisValueType'],
				'discountValue'	  => $_data['discountValue'],
				'start_date'  => $_data['start_date'],
				'end_date'	  => $_data['end_date'],
				'create_date' => Zend_Date::now(),
				'status'  	  => 1,
				'user_id'	  => $this->getUserId()
		);
		$this->insert($_arr);
		}catch (Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}	
	public function addNewDiscountPopup($_data){
		$_arr=array(
				'dis_name' => $_data['dis_name'],
				'create_date' => Zend_Date::now(),
				'status'   => $_data['status_j'],
				'user_id'	  => $this->getUserId()
		);
		$this->_name="rms_discount";
		return  $this->insert($_arr);
	}
	public function getDiscountsetById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_dis_setting WHERE discount_id = ".$db->quote($id);
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateDiscountset($_data){
		$_arr=array(
				'branch_id'   => $_data['branch_id'],
				'studentId'	=>	$_data['studentId'],
				'discountOption'=>$_data['discountOption'],
				'discountType'  => $_data['disname_id'],
				'itemType'  => $_data['itemType'],
				'itemId'  => $_data['itemId'],
				'DisValueType' => $_data['DisValueType'],
				'discountValue'	  => $_data['discountValue'],
				'start_date'  => $_data['start_date'],
				'end_date'	  => $_data['end_date'],
				'create_date' => Zend_Date::now(),
				'status'  	  => 1,
				'user_id'	  => $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("discount_id=?", $_data["id"]);
		$this->update($_arr, $where);
	}
		
	public function addDiscounttionset($_data){//ajax
		$_arr=array(
			'dis_name' => $_data['dis_name'],
			'create_date' => Zend_Date::now(),
			'status'   => 1,
			'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
}