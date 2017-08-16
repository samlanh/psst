<?php

class Library_Model_DbTable_DbNeardayreturnbook extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_book';
 	protected $tr;
 	public function init()
 	{
 		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
 	}
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
   
	function getReturnBookLate($search=null){
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission('sp.branch_id');
		$sql="SELECT bd.id,b.phone,b.stu_id,        
			    b.borrow_no,(SELECT book_no FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1) AS bookno,
			    (SELECT title FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1) AS bookname,
		          b.card_id,b.name,(SELECT name_kh FROM rms_view WHERE rms_view.key_code=b.borrow_type AND rms_view.type=13 LIMIT 1) AS `type`,
			    (SELECT name_en FROM rms_view WHERE rms_view.type=2 AND key_code=(SELECT sex FROM rms_student WHERE rms_student.stu_id=b.stu_id LIMIT 1))AS sex,
			    (SELECT major_enname FROM rms_major WHERE major_id = (SELECT grade FROM rms_student WHERE rms_student.stu_id=b.stu_id LIMIT 1)) AS grade,
			    b.borrow_date,b.return_date,bd.borr_qty
           FROM rms_borrow AS b,rms_borrowdetails AS bd 
           WHERE b.id=bd.borr_id
           AND bd.is_full=0
           AND b.is_completed=0";
		$where = '';
		if(!empty($search["title"])){
			$s_where=array();
			$s_search = addslashes(trim($search['title']));
			$s_where[]="  b.borrow_no LIKE '%{$s_search}%'";
			$s_where[]="  b.card_id LIKE '%{$s_search}%'";
			$s_where[]="  b.name LIKE '%{$s_search}%'";
			$s_where[]="  b.phone LIKE '%{$s_search}%'";
			$s_where[]="  bd.borr_qty LIKE '%{$s_search}%'";
			$s_where[]= "(SELECT stu_code FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]= "(SELECT stu_enname FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		 
		if($search["status_search"]>-1){
			$where.=' AND b.status='.$search["status_search"];
		}
		
		if(!empty($search["is_type_bor"])){
    		$where.=' AND b.borrow_type='.$search["is_type_bor"];
    	}
		
		if($search["cood_book"]>0){
			$where.=' AND bd.book_id='.$search["cood_book"];
		}
		
		$order=" ORDER BY b.stu_id DESC ";
		$str_next = '+1 3 days';
		$search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
		$to_date = (empty($search['end_date']))? '1': " b.return_date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$to_date;
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getBookIdName(){
		$db=$this->getAdapter();
		$sql="SELECT id,title AS name FROM rms_book WHERE `status`=1 and title!=''";
		return $db->fetchAll($sql);
	}
	
	function getAllStudentId($type){//type = 1 =>student id , 2=student name
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
		if($type==1){
			$sql="SELECT s.stu_id As stu_id,s.stu_code As stu_code FROM rms_student AS s
			WHERE s.status=1 and s.is_subspend=0  $branch_id  ORDER BY stu_type DESC ";
		}else {
			$sql="SELECT s.stu_id As stu_id,CONCAT(s.stu_enname) as name FROM rms_student AS s
			WHERE s.status=1 and s.is_subspend=0  $branch_id  ORDER BY stu_type DESC ";
		}
		return $db->fetchAll($sql);
	}
	
	function getAllBorrowName(){
		$db=$this->getAdapter();
		$sql="SELECT id,name FROM rms_borrow WHERE is_completed=0";
		return $db->fetchAll($sql);
	}
	
	function getIsTypeBorowName(){
		$db=$this->getAdapter();
		$sql="SELECT key_code AS id , name_en AS `name`  FROM rms_view  WHERE `type`=13";
		return $db->fetchAll($sql);
	}
	
	function getAllBlcok(){//type = 1 =>student id , 2=student name
		$db=$this->getAdapter();
		 $sql="SELECT id,block_name AS `name` FROM rms_blockbook WHERE `status`=1 AND block_name!=''";
		return $db->fetchAll($sql);
	}
	
}



