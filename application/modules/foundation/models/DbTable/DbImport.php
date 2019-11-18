<?php

class Foundation_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{

    protected $_name = 'ldc_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    public function updateItemsByImport($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	for($i=1; $i<=$count; $i++){
    		$stu_code = $dbg->getnewStudentId(7,1);
    		$arr = array(
    				'branch_id'=>9,
    				'user_id'=>1,
    				'stu_code'=>$stu_code,
    				'stu_khname'=>$data[$i]['C'],
    				'stu_enname'=>trim($data[$i]['D']),
    				'last_name'=>trim($data[$i]['E']),
    				'sex'=>($data[$i]['F']=="M")?1:2,
    				'tel'=>$data[$i]['G'],
    				'dob'=>date("Y-m-d",strtotime($data[$i]['H'])),
    				'pob'=>$data[$i]['I'],
    				
    				'father_enname'=>$data[$i]['J'],
    				'father_khname'=>$data[$i]['J'],
    				'father_phone'=>$data[$i]['K'],
    				
    				'mother_khname'=>$data[$i]['L'],
    				'mother_enname'=>$data[$i]['L'],
    				'mother_phone'=>$data[$i]['M'],
    				
    				'guardian_first_name'=>$data[$i]['N'],
    				'guardian_enname'=>$data[$i]['N'],
    				'guardian_khname'=>$data[$i]['N'],
    				'guardian_tel'=>$data[$i]['O'],
    				
    				//'stu_code'=>$data[$i]['P'],
    				
//     				'degree'=>3,
//     				'grade'=>16,
    				//'remark'=>$data[$i]['P'],
    				
    				'is_stu_new'=>0,
    				'customer_type'=>1,
    				'status'=>1,
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'create_date'=>date("Y-m-d H:i:s")
    				//'note'=>$data[$i]['J'].",".$data[$i]['K'].",".$data[$i]['L'],
    		);
     		$this->_name='rms_student';
    		$id = $this->insert($arr);
    		
//     		$arr = array(
//     				'branch_id'=>1,
//     				'stu_id'=>$id,
//     				'degree'=>4,
//     				'status'=>1,
//     		);
//     		$this->_name='rms_student_id';
//     		$id = $this->insert($arr);
    		
    		/*$arr = array(
    				'group_id'=>0,
    				'branch_id'=>1,
    				'user_id'=>$this->getUserId(),
    				'stu_enname'=>$data[$i]['B'],
    				'stu_khname'=>$data[$i]['C'],
    				'stu_code'=>$data[$i]['E'],
    				'academic_year'=>0,
    				'grade'=>$data[$i]['H'],
    				//     				'session'=>$data[$i]['D'],
    		//     				'degree'=>$data[$i]['D'],
    				'sex'=>($data[$i]['D']=="M")?1:2,
    				'nationality'=>$data[$i]['J'],
    				'dob'=>date("Y-m-d",strtotime($data[$i]['F'])),
    				'age'=>$data[$i]['G'],
    				//     				'tel'=>$data[$i]['D'],
    				'pob'=>$data[$i]['K'],
    				//     				'email'=>$data[$i]['D'],
    				'address'=>$data[$i]['F'],
    				'father_enname'=>$data[$i]['R'],
    				'father_khname'=>$data[$i]['R'],
    				'father_phone'=>$data[$i]['S'],
    				'mother_khname'=>$data[$i]['P'],
    				'mother_enname'=>$data[$i]['P'],
    				'mother_phone'=>$data[$i]['Q'],
    				'guardian_enname'=>$data[$i]['P'],
    				'guardian_khname'=>$data[$i]['P'],
    				'guardian_document'=>$data[$i]['M'],
    				'guardian_tel'=>$data[$i]['N'],
    				//     				'stu_type'=>1,
    		//     				'guardian_dob'=>$data[$i]['D'],
    		//     				'street'=>$data[$i]['D'],
    		//     				'vill_id'=>$data[$i]['D'],
    				//     				'mother_dob'=>$data[$i]['D'],
    				//     				'mother_nation'=>$data[$i]['D'],
    				//     				'mother_job'=>$data[$i]['D'],
    				//     				'father_dob'=>$data[$i]['D'],
    				//     				'father_nation'=>$data[$i]['D'],
    				//     				'father_job'=>$data[$i]['D'],
    				//     				'home_num'=>$data[$i]['D'],
    				//     				'street_num'=>$data[$i]['D'],
    				//     				'village_name'=>$data[$i]['D'],
    				//     				'commune_name'=>$data[$i]['D'],
    				//     				'district_name'=>$data[$i]['D'],
    				//     				'province_id'=>$data[$i]['D'],
    		
    				'remark'=>$data[$i]['D'],
    				'create_date'=>$data[$i]['T'],
    				'is_stu_new'=>0,
    				'is_subspend'=>0,
    				'modify_date'=>date("Y-m-d"),
    				'is_setgroup'=>0,
    				'password'=>12345,
    		);
    		$this->_name='rms_student';
    		$id = $this->insert($arr);
    		$arr = array(
    				'branch_id'=>1,
    				'stu_id'=>$id,
//     				'degree'=>$data[$i]['D'],
    				'status'=>1,
    		);
    		$this->_name='rms_student_id';
    		$id = $this->insert($arr);*/
    	}
    }
}   

