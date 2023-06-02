<?php
class Registrar_Model_DbTable_DbInitilizeservice extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}

	function addInitilizeService($data){
		$_db = $this->getAdapter();
		try{
				$this->_name='rms_group_detail_student';
				$ids = explode(',', $data['identity']);
				$db = new Application_Model_DbTable_DbGlobal();
				if(!empty($ids))foreach ($ids as $i){
					
				
						$param = array(
							'Id'=>$data['itemId_'.$i]
						);
						$resultRow = $db->getItemDetailRow($param);
						
						$_arr= array(
								'branch_id'		=> $data['branch_id'],
								'studentId'		=> $data['studentId'],
								'itemType'		=> $resultRow['items_type'],
								'grade'			=> $data['itemId_'.$i],
								'degree'		=> $resultRow['items_id'],
								'feeId'			=> $data['study_year'],
								'discountType'	=> $data['discount_type'.$i],
								'discountAmount'=> $data['discount_amount'.$i],
								'startDate'		=> empty($data['balance_'.$i])?'':$data['date_start_'.$i],
								'endDate'		=> empty($data['balance_'.$i])?'':$data['end_date_'.$i],
								'balance'		=> ($data['balance_'.$i] > 0)?1:0,
								'schoolOption'	=> $resultRow['schoolOption'],
								'isMaingrade'	=>1,
								'isCurrent'		=> 1,
								'stopType'		=> 0,
								'status'		=> 1,
								'isNewStudent'	=> 1,
								'remark'		=> $data['remark'.$i],
								'create_date'	=> date("Y-m-d H:i:s"),
								'user_id'		=> $this->getUserId(),
								'entryFrom'	=>5,
						);
						$db->AddItemToGroupDetailStudent($_arr);//to insert rms_group_detail_student Item
						
						
// 						//if grade and have existing dont's add 
// 						$_arr= array(
// 								'branch_id'		=> $data['branch_id'],
// 								'stu_id'		=> $data['studentId'],
// 								'itemType'		=> $resultRow['items_type'],
// 								'feeId'			=> $data['study_year'],
// 								'balance'		=> $data['balance_'.$i],
// 								'discount_type'	=> $data['discount_type'.$i],
// 								'discount_amount'=> $data['discount_amount'.$i],
// 								'degree'		=> $resultRow['items_id'],
// 								'grade'			=> $data['itemId_'.$i],
// 								'startDate'		=> empty($data['balance_'.$i])?'':$data['date_start_'.$i],
// 								'endDate'		=> empty($data['balance_'.$i])?'':$data['end_date_'.$i],
// 								'is_maingrade'	=> ($resultRow['items_type']==1)?1:'',
// 								'isoldBalance'	=> ($data['balance_'.$i] > 0)?1:0,
// 								'school_option'	=> $resultRow['schoolOption'],
// 								'is_current'	=> 1,
// 								'stop_type'		=> 0,
// 								'status'		=> 1,
// 								'is_newstudent'	=> 1,
// 								'academic_year'	=> $year,
// 								'note'			=> $data['remark'.$i],
// 								'create_date'	=> date("Y-m-d H:i:s"),
// 								'user_id'		=> $this->getUserId(),
// 						);
// 						$id = $this->insert($_arr);
				}
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAILE");
		}
	}


}