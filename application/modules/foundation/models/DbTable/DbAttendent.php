<?php

class Foundation_Model_DbTable_DbAttendent extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_attendent';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	
	}
	public function addAttendent($_data){
// 		print_r($_data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr = array(
				'academic_id' => $_data['year_study'],
				'session_id' => $_data['session'],
				'group_id' => $_data['study_group'],
				'subject_id' => $_data['parent_name'],
				'date' => $_data['start_date'],
				'note'   => $_data['note'],
				'status'   =>$_data['status'],
				'user_id'=> $this->getUserId()
				);
		    $id= $this->insert($arr);
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					$arr=array(
							'attd_id'=> $id,
							'student_id'=>$_data['stu_id_'.$i],
							'student_code'=>$_data['stu_code_'.$i],
							'sex'=>$_data['sex_'.$i],
							'grade_id'=>$_data['grade_'.$i],
							'att_type'=>$_data['at_type_'.$i],
							'no_att_type'=>$_data['studen_out_'.$i],
							'permission'=>$_data['permiss_'.$i],
							'no_permission'=>$_data['no_permiss_'.$i],
							'note'=>$_data['note_'.$i],
							'status'=>$_data['status'],
							'user_id'=>$this->getUserId()
					);
					$this->_name='rms_attendent_detail';
					//$db->getProfiler()->setEnabled(true);
					$this->insert($arr);
				}
			}
			//exit();
			$db->commit();
		}catch (Exception $e){
// 			echo $e;exit();
			$db->rollBack();
		}
	}
	
	public function updateAttendent($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr = array(
					'academic_id' => $_data['year_study'],
					'session_id' => $_data['session'],
					'group_id' => $_data['study_group'],
					'subject_id' => $_data['parent_name'],
					'date' => $_data['start_date'],
					'note'   => $_data['note'],
					'status'   =>$_data['status'],
					'user_id'=> $this->getUserId()
			);
			$where="id=".$_data['id'];
			$this->update($arr, $where);
//////////////////////////////////delete all recode rms_attendent_detail by id-> att_id
            $delete="attd_id =".$_data['id'];
            $this->_name='rms_attendent_detail';
            $this->delete($delete);
			
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					$arr=array(
							'attd_id'=>$_data['id'],
							'student_id'=>$_data['stu_id_'.$i],
							'student_code'=>$_data['stu_code_'.$i],
							'sex'=>$_data['sex_'.$i],
							'grade_id'=>$_data['grade_'.$i],
							'att_type'=>$_data['at_type_'.$i],
							'no_att_type'=>$_data['studen_out_'.$i],
							'permission'=>$_data['permiss_'.$i],
							'no_permission'=>$_data['no_permiss_'.$i],
							'note'=>$_data['note_'.$i],
							'status'=>$_data['status'],
							'user_id'=>$this->getUserId()
					);
					$this->_name='rms_attendent_detail';
					//$db->getProfiler()->setEnabled(true);
					$this->insert($arr);
				}
			}
			//exit();
			$db->commit();
		}catch (Exception $e){
			//echo $e;exit();
			$db->rollBack();
		}
	}
	
   function getAllAttendent($search=null){
		$db=$this->getAdapter();
		$sql="SELECT id,(SELECT group_code FROM rms_group WHERE id=rms_attendent.group_id ) AS  group_id,
	        (SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE id=rms_attendent.academic_id AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_id,
	        (SELECT CONCAT(name_en ,'-',name_kh ) FROM rms_view WHERE `type`=4 AND rms_view.key_code=rms_attendent.session_id) As session_id,
	        (SELECT CONCAT(subject_titleen,' - ',subject_titlekh) FROM rms_subject WHERE id=rms_attendent.subject_id ) AS subject_id,
	        `date`,note,`status` 
	        FROM rms_attendent WHERE `status`=1";
		$where ='';
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
  		if(!empty($search['group_name'])){
			$where.= " AND  group_id=".$search['group_name'];
		}
		if(!empty($search['study_year'])){
			$where.=" AND academic_id=".$search['study_year'];
		}
		if(!empty($search['grade'])){
			$where.=" AND group_id=".$search['grade'];
		}
		if(!empty($search['session'])){
			$where.=" AND session_id=".$search['session'];
		}
		// 		if(!empty($search['time'])){
		// 			$where.=" AND sp.time=".$search['time'];
		// 		}
// 		print_r($sql.$where);
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getGroupSearch(){
		$db=$this->getAdapter();
		$sql="SELECT group_id AS id,(SELECT group_code FROM rms_group WHERE id=rms_attendent.group_id) AS `name`
		FROM rms_attendent WHERE `status`=1 GROUP BY group_id";
		return $db->fetchAll($sql);
	}
	function getAttendentById($id){
		$db=$this->getAdapter();
		$sql="SELECT id,academic_id,session_id,group_id,subject_id,`date`,`note` FROM rms_attendent WHERE id=$id";
	    return $db->fetchRow($sql);
	}
	function getAttendentDetail($id){
		$db=$this->getAdapter();
		$sql=" SELECT id,attd_id,student_id,
				   (SELECT
				     CONCAT(stu_enname,'-',stu_khname)
				   FROM rms_student AS s
				   WHERE s.stu_id = rms_attendent_detail.student_id) AS stu_name,
				  (SELECT CONCAT(major_enname) FROM rms_major WHERE rms_attendent_detail.grade_id=rms_major.major_id) AS grade_name,grade_id,
				  student_code,sex,permission,note,att_type,no_permission,no_att_type
				FROM rms_attendent_detail
				WHERE attd_id = $id";
		return $db->fetchAll($sql);
	}
	function getStudent($data){
		$db=$this->getAdapter();
		$sql="SELECT s.stu_id,s.stu_code,CONCAT(s.stu_enname,' - ',s.stu_khname) AS stu_khname,s.sex,(SELECT CONCAT(major_enname,' - ',major_khname ) AS major_enname
		FROM rms_major WHERE rms_major.major_id=s.grade) AS grade,s.grade As grade_id
		FROM rms_student AS s,rms_group_detail_student AS g  WHERE s.stu_id=g.stu_id AND g.group_id=$data";
		$order=" ORDER BY stu_code ASC";
		return $db->fetchAll($sql.$order);
	}
	 
}

