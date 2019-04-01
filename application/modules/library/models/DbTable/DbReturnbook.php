<?php

class Library_Model_DbTable_DbReturnbook extends Zend_Db_Table_Abstract
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
    
    function getAllReturnBook($search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT 
    				ret.id,
    				ret.return_no,
			     	ret.return_date,
			     	ret.note,
					(SELECT first_name FROM rms_users WHERE id=ret.user_id LIMIT 1) AS user_name,
					(SELECT name_en FROM rms_view WHERE key_code=ret.status LIMIT 1) AS `status`
				FROM 
					rms_bookreturn AS ret
				WHERE 
					1
    		";
    	
    	$where = '';
    	
    	$from_date =(empty($search['start_date']))? '1': " ret.return_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " ret.return_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  ret.return_no LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	
    	if($search["status_search"]>-1){
    	    $where.=' AND b.status='.$search["status_search"];
    	}
    	
    	if($search["student_name"]>0){
    		$where.=' AND bor.stu_id='.$search["student_name"];
    	}
    	if(!empty($search["is_type_bor"])){
    		$where.=' AND bor.borrow_type='.$search["is_type_bor"];
    	}
    	
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
 
	public function addReturnBook($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr=array(
				"return_no"     => 	$data["borrow_id"],
				"return_date"   => 	date("Y-m-d",strtotime($data['return_date'])),
				"note"          => 	$data['notes'],
				"user_id"       => 	$this->getUserId(),
			);
			$this->_name="rms_bookreturn";
			$return_id = $this->insert($arr); 

			if($data['identity']!=""){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{ 
					$data_item= array(
						'return_id'		=> $return_id,
						'book_id'		=> $data['book_id'.$i],
						'borr_detail_id'=> $data['borrow_detail_id'.$i],
					);
					$this->_name='rms_bookreturndetails';
					$this->insert($data_item);
					
					$borr_item= array(
						'is_return'	=> 1,
					);
					$this->_name='rms_borrowdetails';
					$where=" book_id = ".$data['book_id'.$i]." and id = ".$data['borrow_detail_id'.$i];
					$this->update($borr_item, $where);
					
					$book = array(
							'is_borrow'	=> 0,
					);
					$this->_name='rms_book_detail';
					$where1=" id = ".$data['book_id'.$i];
					$this->update($book, $where1);
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
		}
	}
	
	public function updateReturnBook($data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$row=$this->getReturnBookById($id);
			$row_detail=$this->getReturnDetailById($id);
			if(!empty($row_detail)){
				foreach ($row_detail As $rs_item){
					$arr = array(
						'is_return' =>  0,
					);
					$this->_name="rms_borrowdetails";
					$where=" id = ".$rs_item['borr_detail_id'];
					$this->update($arr, $where);
					
					$arr1 = array(
							'is_borrow' =>  1,
					);
					$this->_name="rms_book_detail";
					$where1=" id = ".$rs_item['book_id'];
					$this->update($arr1, $where1);
				}
			}
			
			$this->_name='rms_bookreturndetails';
			$where2=" return_id = $id ";
			$this->delete($where2);
			
			$arr_return=array(
					"return_no"     => 	$data["borrow_id"],
					"return_date"   => 	date("Y-m-d",strtotime($data['return_date'])),
					"note"          => 	$data['notes'],
					"user_id"       => 	$this->getUserId(),
			);
			$this->_name="rms_bookreturn";
			$where3=" id = $id ";
			$this->update($arr_return, $where3);
			 
			
			if(!empty($data['identity'])){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{ 
					$data_item= array(
						'return_id'		=> $id,
						'book_id'		=> $data['book_id'.$i],
						'borr_detail_id'=> $data['borrow_detail_id'.$i],
					);
					$this->_name='rms_bookreturndetails';
					$this->insert($data_item);
					
					$borr_item= array(
						'is_return'	=> 1,
					);
					$this->_name='rms_borrowdetails';
					$where4=" book_id = ".$data['book_id'.$i]." and id = ".$data['borrow_detail_id'.$i];
					$this->update($borr_item, $where4);
					
					$book = array(
							'is_borrow'	=> 0,
					);
					$this->_name='rms_book_detail';
					$where5=" id = ".$data['book_id'.$i];
					$this->update($book, $where5);
				}
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();exit();
		}
	}
	 
	function getReturnBookById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_bookreturn WHERE id=$id";
		return $db->fetchRow($sql);
	}
	
	function getReturnDetailById($id){
		$db=$this->getAdapter();
		$sql="SELECT 
					brd.*,
					bd.serial,
					bd.barcode,
					DATE_FORMAT(rms_borrow.return_date, '%d-%m-%Y') AS return_date,
					rms_borrow.borrow_no,
					rms_borrow.borrow_type,
					CASE
						WHEN 
							rms_borrow.borrow_type = 1 THEN (select stu_khname from rms_student where rms_student.stu_id = rms_borrow.stu_id)
						ELSE 
							rms_borrow.name
					END as name,
					CASE
						WHEN 
							rms_borrow.borrow_type = 1 THEN (select stu_code from rms_student where rms_student.stu_id = rms_borrow.stu_id)
						ELSE 
							rms_borrow.card_id
					END as code
				FROM 
					rms_bookreturndetails as brd,
					rms_book_detail as bd,
					rms_borrowdetails,
					rms_borrow 
				WHERE 
					bd.id = brd.book_id 
					and brd.borr_detail_id = rms_borrowdetails.id
					and rms_borrowdetails.borr_id = rms_borrow.id
					and brd.return_id=$id
			";
		return $db->fetchAll($sql);
	}
	
	function getReturnBookNo(){
		$db=$this->getAdapter();
		$sql="SELECT count(id) FROM rms_bookreturn limit 1";
		$qty=$db->fetchOne($sql);
		$qty_new = $qty+1;
		$lenght = strlen($qty_new);
		
		$prefix='';
		for($i=$lenght;$i<5;$i++){
			$prefix.='0';
		}
		return $prefix.$qty_new;
	}
	
	function getBookDetail($book_id=0){
		$db=$this->getAdapter();
		$sql="SELECT 
					bd.id as borrow_detail_id,
					b.*,
					s.stu_khname,
					s.last_name,
					s.stu_enname,
					s.stu_code,
					DATE_FORMAT(b.return_date, '%d-%m-%Y') AS return_date
				FROM 
					rms_borrow as b,
					rms_borrowdetails as bd,
					rms_student as s
				WHERE 
					b.id = bd.borr_id
					and b.stu_id = s.stu_id
					and bd.book_id = $book_id
			";
		return $db->fetchRow($sql);
	}
	
}



