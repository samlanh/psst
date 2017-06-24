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
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
   
	function getReturnBookLate($search=null){
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission('sp.branch_id');
		$sql="SELECT bd.id,b.phone,        
			    b.borrow_no,(SELECT book_no FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1) AS bookno,
			    (SELECT title FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1) AS bookname,
		        (SELECT stu_code FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) AS stu_code,
			    (SELECT stu_enname FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) AS stu_name,
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
			$s_where[]="  b.phone LIKE '%{$s_search}%'";
			$s_where[]="  bd.borr_qty LIKE '%{$s_search}%'";
			$s_where[]= "(SELECT stu_code FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]= "(SELECT stu_enname FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		 
		if($search["status_search"]>-1){
			$where.=' AND b.status='.$search["status_search"];
		}
		
		if($search["cood_book"]>0){
			$where.=' AND bd.book_id='.$search["cood_book"];
		}
		
		$order=" ORDER BY b.id DESC ";
		$str_next = '+1 week';
		$search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
		$to_date = (empty($search['end_date']))? '1': " b.return_date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$to_date;
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getBookIdName(){
		$db=$this->getAdapter();
		$sql="SELECT id,title AS name FROM rms_book WHERE `status`=1";
		return $db->fetchAll($sql);
	}
	
}



