<?php

class Application_Model_DbTable_DbGlobal extends Zend_Db_Table_Abstract
{
    // set name value
	public function setName($name){
		$this->_name=$name;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	
	/**
	 * get selected record of $sql
	 * @param string $sql
	 * @return array $row;
	 */
	public function getGlobalDb($sql)
  	{
  		$db=$this->getAdapter();
  		$row=$db->fetchAll($sql);  		
  		if(!$row) return NULL;
  		return $row;
  	}
  	
  	public function getGlobalDbRow($sql)
  	{
  		$db=$this->getAdapter();  		
  		$row=$db->fetchRow($sql);
  		if(!$row) return NULL;
  		return $row;
  	}
  	
  	public static function getActionAccess($action)
    {
    	$arr=explode('-', $action);
    	return $arr[0];    	
    }     
    
    public function isRecordExist($conditions,$tbl_name){
		$db=$this->getAdapter();		
		$sql="SELECT * FROM ".$tbl_name." WHERE ".$conditions." LIMIT 1"; 
		$row= count($db->fetchRow($sql));
		if(!$row) return NULL;
		return $row;	
    }
    /*for select 1 record by id of earch table by using params*/
    public function GetRecordByID($conditions,$tbl_name){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM ".$tbl_name." WHERE ".$conditions." LIMIT 1";
    	$row = $this->fetchRow($sql);
    	return $row;
    	$row= $db->fetchRow($sql);
    	return $row;
    }
    
    /**
     * insert record to table $tbl_name
     * @param array $data
     * @param string $tbl_name
     */
    public function addRecord($data,$tbl_name){
    	//print_r($data);exit;    	
    	$this->setName($tbl_name);
    	return $this->insert($data);
    }
    
    /**
     * update record to table $tbl_name
     * @param array $data
     * @param int $id
     * @param string $tbl_name
     */
    public function updateRecord($data,$id,$tbl_name){
    	//print_r($data);exit;
    	$this->setName($tbl_name);
    	$where=$this->getAdapter()->quoteInto('id=?',$id);
    	$this->update($data,$where);    	
    }
    
    public function DeleteRecord($tbl_name,$id){
    	$db = $this->getAdapter();
		$sql = "UPDATE ".$tbl_name." SET status=0 WHERE id=".$id;
		return $db->query($sql);
    } 

     public function DeleteData($tbl_name,$where){
    	$db = $this->getAdapter();
		$sql = "DELETE FROM ".$tbl_name.$where;
		return $db->query($sql);
    } 
    
    public function convertStringToDate($date, $format = "Y-m-d H:i:s")
    {
    	if(empty($date)) return NULL;
    	$time = strtotime($date);
    	return date($format, $time);
    }
    public function getMarjorById($major_id){
    	$db = $this->getAdapter();
    	$sql=" SELECT major_id AS id,major_enname AS name FROM `rms_major`
    	WHERE `dept_id` = $major_id ";
    	$db->fetchAll($sql);
    	return $db->fetchAll($sql);
    }   

    public static function getResultWarning(){
          return array('err'=>1,'msg'=>'មិន​ទាន់​មាន​ទន្និន័យ​នូវ​ឡើយ​ទេ!');	
   }

   public function getUserId(){
   	$session_user=new Zend_Session_Namespace('authstu');
   	return $session_user->user_id;
   }
   
   
   public function getAllSession(){
	   	$db = $this->getAdapter();
	   	$sql ="SELECT key_code as id,name_en as name FROM rms_view WHERE type=4 AND status=1 ";
	   	return $db->fetchAll($sql);
   }
   
   public function getProvince(){
   	$db = $this->getAdapter();
   	$sql ="SELECT province_en_name,province_id FROM rms_province WHERE is_active=1 AND province_en_name!='' ";
   	return $db->fetchAll($sql);
   }
   public function getOccupation(){
   	$db = $this->getAdapter();
   	$sql ="SELECT occupation_id as id, occu_name as name FROM rms_occupation WHERE status=1 AND occu_name!='' 
   	ORDER BY occu_name ASC ";
   	return $db->fetchAll($sql);
   }
   
   public function getAllFecultyNamess($type){
   	$db = $this->getAdapter();
   	if($type==1){
   		$sql ="SELECT dept_id AS id, en_name AS name,en_name,dept_id,shortcut FROM rms_dept WHERE is_active=1 AND (en_name!='' OR kh_name!='') ORDER BY en_name ";
   		return $db->fetchAll($sql);
   	 }else if($type==2){
        $sql="SELECT dept_id AS id, en_name AS `name`,en_name,dept_id,shortcut FROM rms_dept WHERE is_active=1  AND (en_name!='' OR kh_name!='') ORDER BY en_name";
        return $db->fetchAll($sql);
   	 }
   }
   
   public function getAllDegreeName(){
   	$db = $this->getAdapter();
   	$sql ="SELECT dept_id AS id, en_name AS name,en_name,dept_id,shortcut FROM rms_dept WHERE is_active=1 and en_name!='' ORDER BY id ASC";
   	return $db->fetchAll($sql);
   }
   
 public function getAllFecultyName(){
   	$db = $this->getAdapter();
   		$sql ="SELECT dept_id AS id, en_name AS NAME,en_name,dept_id,shortcut FROM rms_dept WHERE is_active=1 AND en_name!='' AND dept_id IN(2,3,4) ORDER BY id DESC";
   		return $db->fetchAll($sql);
   }   public function getGepDept(){
   	$db = $this->getAdapter();
   	$sql ="SELECT dept_id as id,en_name AS name FROM rms_dept WHERE (en_name!='' OR kh_name!='') AND is_active =1";
   	return $db->fetchAll($sql);
   }
   
   public function getAllDegreeKindergarten(){
   	$db = $this->getAdapter();
   	$sql ="SELECT dept_id AS id, en_name AS name FROM rms_dept WHERE is_active=1 AND (en_name!='' OR kh_name!='') ORDER BY id DESC";
   	return $db->fetchAll($sql);
   }
   
   function getAllgroupStudy($teacher_id=null){
   	$db = $this->getAdapter();
   	$sql ="SELECT `g`.`id`, CONCAT(`g`.`group_code`,' ',
   	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation limit 1) ) AS name
   	FROM `rms_group` AS `g`  ";
   	if($teacher_id!=null){
   		$sql.=" ,rms_group_subject_detail AS gsd WHERE g.id =gsd.group_id AND gsd.teacher= ".$teacher_id;
   	}else{
   		$sql.=" WHERE 1";
   	}
   	$sql.=" AND g.status =1 AND group_code!=''";
   	return $db->fetchAll($sql);
   }
   function getAllgroupStudyNotPass($action=null){
   	$db = $this->getAdapter();
   	$sql ="SELECT `g`.`id`, CONCAT(`g`.`group_code`,' ',
   	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) ) AS name
   	FROM `rms_group` AS `g` WHERE g.status =1";
   	$where ='';
   	if (!empty($action)){
   		$where = " AND (g.is_pass=0 || g.id = $action)";
   	}else{
   		$where = " AND g.is_pass=0 ";
   	}
   	return $db->fetchAll($sql.$where);
   }
   
   
   public function getAllDegreeGEP(){
   	$db = $this->getAdapter();
   	$sql ="SELECT dept_id AS id, en_name AS name FROM rms_dept WHERE is_active=1 AND en_name!='' AND dept_id IN(5) ORDER BY id DESC";
   	return $db->fetchAll($sql);
   }
   
   public function getAllServiceItemsName($status=1,$type=null){
   	$db = $this->getAdapter();
   	if($status==1){
   		$sql ="SELECT DISTINCT title,service_id FROM rms_program_name WHERE title!='' AND status=1 ORDER BY title";
   	}else{
   		$sql ="SELECT DISTINCT title,service_id AS id FROM rms_program_name WHERE title!='' ORDER BY title";
   	}
   return $db->fetchAll($sql);
   }
   public function getAllstudentRequest($type=null){
   	$db = $this->getAdapter();
   	if($type!=null){
   		$sql = " SELECT service_id as id,title as name FROM `rms_program_name` WHERE
   		 type=$type AND status = 1 AND title!=''";
   		return $db->fetchAll($sql);
   	}else{
   	$sql = 'SELECT service_id as id,pn.title as name FROM `rms_program_type` AS pt,`rms_program_name` AS pn 
   			WHERE pt.id = pn.ser_cate_id AND pt.type=1 
   				AND pn.status = 1 AND pn.title!=""';
   	}
   	return $db->fetchAll($sql);
   }
   
   
   public function getAllSubjectStudy(){
   	$db = $this->getAdapter();
   		$sql = " SELECT id,subject_titlekh as name,shortcut FROM `rms_subject` WHERE
   		is_parent=1 AND status = 1 and subject_titlekh!='' ";
   	return $db->fetchAll($sql);
   }
   
   
   function getAllDept($search, $start, $limit){
   	$db = $this->getAdapter();
   	$sql = $this->_buildQuery($search)." LIMIT ".$start.", ".$limit;
   	if ($limit == 'All') {
   		$sql = $this->_buildQuery($search);
   	}
   	return $db->fetchAll($sql);
   }
   
   function getCountDept($search=''){
   	$db = $this->getAdapter();
   	$sql = $this->_buildQuery();
   	if(!empty($search)){
   		$sql = $this->_buildQuery($search);
   	}
   	$_result = $db->fetchAll($sql);
   	return count($_result);
   }
   public function getGlobalResultList($sql,$sql_count){
   	$db = $this->getAdapter();
   	$rows= $db->fetchAll($sql);
   	$_count = count($db->fetchAll($sql_count));
   	return array(0=>$rows,1=>$_count);
//get all result by param 0 ,get count record by param1
   }
   
   /*@author Mok Channy
    * for use session navigetor 
    * */
   public static function SessionNavigetor($name_space,$array=null){
   	$session_name = new Zend_Session_Namespace($name_space);
   	return $session_name;   	
   }
   public function getAllDegree($id=null){
	   $rs = array(
	   			1=>$this->tr->translate("ASSOCIATE"),
	   			2=>$this->tr->translate("BACHELOR"),
	   			3=>$this->tr->translate('MASTER'),
	   			4=>$this->tr->translate('DOCTORATE'),
	   			5=>$this->tr->translate('INTERNATION_PROGRAM'));
	   if($id==null)return $rs; 
	   return $rs[$id];
   }
   public static  function getAllStatus($id=null){
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$rs = array(
   			1=>$tr->translate("ACTIVE"),
   			0=>$tr->translate("DEACTIVE"));
   	if($id==null)return $rs;
   	return $rs[$id];
   }
   public function AllStatus($id=null){
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$rs = array(
   			1=>$tr->translate("ACTIVE"),
   			0=>$tr->translate("DEACTIVE"));
   	if($id==null)return $rs;
   	return $rs[$id];
   }
   public function AllStatusHour($id=null){
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$rs = array(
   			1=>$tr->translate("FULL_TIME"),
   			0=>$tr->translate("PART_TIME"));
   	if($id==null)return $rs;
   	return $rs[$id];
   }
   public static function getAllDegreeById($id=null){
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$rs = array(
   			1=>$tr->translate("Grade School"),
   			2=>$tr->translate("Pramary School"),
   			3=>$tr->translate('Other'),
   			);
   	if($id==null)return $rs;
   	return $rs[$id];
   }
   public function getAllPaymentTerm($id=null,$hidemonth=null){
   	if($hidemonth!=null){
   		$opt_term = array(
   				2=>$this->tr->translate('QUARTER'),
   				3=>$this->tr->translate('SEMESTER'),
   				4=>$this->tr->translate('YEAR'),
   		);
   		return $opt_term;
   	}
   	$opt_term = array(
   			1=>$this->tr->translate('MONTHLY'),
   			2=>$this->tr->translate('QUARTER'),
   			3=>$this->tr->translate('SEMESTER'),
   			4=>$this->tr->translate('YEAR'),
   			
   	);
   	if($id==null)return $opt_term;
   	else return $opt_term[$id]; 
   }
   public function getAllServicePayment($id=null){
   	$opt_term = array(
   		1=>$this->tr->translate('RIEL'),
   		2=>$this->tr->translate('PRICE1'),
   		3=>$this->tr->translate('PRICE2'));
   	if($id==null)return $opt_term;
   	else return $opt_term[$id];
   }
   public function getAllGEPPrgramPayment($id=null){
   	$opt_term = array(
   			1=>$this->tr->translate('FEE'),
   			2=>$this->tr->translate('2TERM'),
   			3=>$this->tr->translate('3TERM'));
   	if($id==null)return $opt_term;
   	else return $opt_term[$id];
   }
//    public static function getSessionById($id){
//    	$tr= Application_Form_FrmLanguages::getCurrentlanguage();
//    	$arr_opt = array(
//    			1=>$tr->translate('MORNING'),
// 			2=>$tr->translate('AFTERNOON'),
// 			3=>$tr->translate('EVERNING'),
// 			4=>$tr->translate('WEEKEND'));
//    	return $arr_opt[$id];
//    }
   public static function getAllMention($id=null){
   	$tr= Application_Form_FrmLanguages::getCurrentlanguage();
    $opt_rank = array(
		  		1=>$tr->translate('A'),
		  		2=>$tr->translate('B'),
		  		3=>$tr->translate('C'),
		  		4=>$tr->translate('D'),
		  		5=>$tr->translate('E'),
		  );
    if($id==null)return $opt_rank;
    else return $opt_rank[$id];
   }
 
   public function getServiceType($type=null){
   	$db = $this->getAdapter();
   	$sql ="SELECT DISTINCT title,id FROM rms_program_type WHERE title!='' AND status=1 ";
   	if(!empty($type)){$sql.=" AND type=$type";}
   	$order = " ORDER BY title ";
   	return $db->fetchAll($sql.$order);
   }
   public function getAllTypeCategory($id = null){
   	$_status_type = array(
   			1=>$this->tr->translate("SERVICE"),
   			2=>$this->tr->translate("PROGRAM"));
   	if($id==null)return $_status_type;
   	else return $_status_type[$id];
    
   }
   public function getServicTypeByName($cate_title,$type){
   	$db = $this->getAdapter();
   	$sql ="SELECT * FROM rms_program_type WHERE title!='' AND title='".$cate_title."' AND type= $type";
   	return $db->fetchRow($sql);
   }
   public function getServiceFeeByServiceWtPayType($service_id,$pay_type){
   	$sql = "SELECT * FROM rms_servicefee_detail WHERE service_id = $service_id AND pay_type =$pay_type LIMIT 1";
   	return $this->getAdapter()->fetchRow($sql);
   }
   public function getProgramFeeByServiceWtPayType($service_id,$pay_type){
   	$sql = "SELECT * FROM mrs_programfee_detail WHERE programfeeid = $service_id AND pay_type =$pay_type LIMIT 1";
   	return $this->getAdapter()->fetchRow($sql);
   }
   public function getRate(){
   	$_db = $this->getAdapter();
   	$_sql = "SELECT * FROM rms_rate ";
   	return $_db->fetchRow($_sql);
   }
   public  function getTutionFeebyCondition($data){
   	$db = $this->getAdapter();
   	//for bachelor
   	$degree = $data['degree'];
   	$metion = $data['metion'];
   	$batch = $data['batch'];
   	$faculty_id = $data['faculty_id'];
   	$payment_type = $data['payment_term'];
   	if($degree==2){
   		$sql = " SELECT tuition_fee FROM `rms_tuitionfee` AS f,`rms_tuitionfee_detail` AS fd
   		WHERE f.fee_id = fd.fee_id AND metion = $metion AND  degree =$degree AND
   		batch = $batch AND faculty_id = $faculty_id AND `payment_type`=$payment_type LIMIT 1";
   	}else{
   		$sql = "SELECT tuition_fee FROM `rms_tuitionfee` AS f,`rms_tuitionfee_detail` AS fd
   		WHERE f.fee_id = fd.fee_id AND metion = $faculty_id AND  degree =$degree AND
   		batch = $batch AND `payment_type`=$payment_type";
   	}
   	return $db->fetchOne($sql);
   	
   }
   function getTeacherCode(){
   	$db = $this->getAdapter();
   	$sql ="SELECT COUNT(id) AS number FROM `rms_teacher` LIMIT 1 ";
   	$acc_no = $db->fetchOne($sql);
   
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);
   	$pre="";
   	for($i = $acc_no;$i<5;$i++){
   		$pre.='0';
   	}
   	$last = '/CAS';
   	return $pre.$new_acc_no.$last;
   }
   function getPrefixCode($branch_id){
   	$db  = $this->getAdapter();
   	$sql = " SELECT prefix FROM `rms_branch` WHERE br_id= $branch_id LIMIT 1 ";
   	return $db->fetchOne($sql);
   }
   function getallComposition(){
   	$db  = $this->getAdapter();
   	$sql = " SELECT occupation_id AS id,occu_name AS name FROM `rms_occupation` WHERE occu_name!='' AND status=1 ORDER BY id DESC ";
   	return $db->fetchAll($sql);
   
   }
   function getallSituation(){
   	$db  = $this->getAdapter();
   	$sql = " SELECT situ_id AS id ,situ_name AS name FROM `rms_situation` WHERE situ_name!='' AND status=1 ORDER BY id DESC ";
   	return $db->fetchAll($sql);
   }
   function getAllProvince($opt=null,$option=null){
   	$db= $this->getAdapter();
   	$sql="SELECT province_id as id,province_kh_name AS name FROM ln_province WHERE status=1 ";
   	$rows =  $db->fetchAll($sql);
   	if($opt==null){
   		return $rows;
   	}else{
   		if($option!=null){
   			$opt_province = array(-1=>"Please Select Location");
   		}else{$opt_province=array();
   		}
   		if(!empty($rows))foreach($rows AS $row) $opt_province[$row['id']]=$row['province_name'];
   		return $opt_province;
   	}
   	
   }
   
   public function getAllDistrict(){
   	$this->_name='ln_district';
   	$sql = " SELECT dis_id,pro_id,CONCAT(district_name,'-',district_namekh) district_name FROM $this->_name WHERE status=1 AND district_name!='' ";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql);
   }
   
   public function getAllsubject(){
   	$db = $this->getAdapter();
   	$sql = "SELECT id ,CONCAT(subject_titleen,'-',subject_titlekh) AS  subject_name
   	FROM `rms_subject` WHERE status=1 AND(subject_titleen!='' OR subject_titlekh!='')";
   	return $db->fetchAll($sql);
   }
   function getAllMajor(){
   	$db = $this->getAdapter();
   	$sql = "SELECT major_id AS id ,CONCAT(major_enname,' (',(select shortcut from rms_dept where rms_dept.dept_id=rms_major.dept_id),')') AS name FROM `rms_major`
   	WHERE is_active=1 Order BY major_id DESC ";
   	return $db->fetchAll($sql);
   }
   public function getAllRoom(){
   	$db = $this->getAdapter();
   	$sql=" SELECT room_id AS id ,room_name As name FROM `rms_room` WHERE is_active=1 AND room_name!='' order by room_id DESC ";
   	return $db->fetchAll($sql);
   }
   
   public function getAllGroup(){
	   	$db = $this->getAdapter();
	   	$sql=" SELECT id ,group_code As name FROM `rms_group` WHERE status=1 AND group_code != '' order by id DESC ";
	   	return $db->fetchAll($sql);
   }
    
   public function getAllTeacherSubject(){
   	$db = $this->getAdapter();
   	$_db = new Application_Model_DbTable_DbGlobal();
   	$branch_id = $_db->getAccessPermission();
   	$sql = "SELECT  id ,CONCAT(teacher_name_en) AS name FROM `rms_teacher`
   	                        WHERE status = 1  $branch_id  Order BY id DESC";
   	return $db->fetchAll($sql);
   }
   public static function getSessionById($id=null){
   	$tr= Application_Form_FrmLanguages::getCurrentlanguage();
   	$arr_opt = array(
   			1=>$tr->translate('MORNING'),
   			2=>$tr->translate('AFTERNOON'),
   			3=>$tr->translate('EVERNING'),
   			4=>$tr->translate('WEEKEND'));
   	if($id!=null){
   		return $arr_opt[$id];
   	}return $arr_opt;
   
   }
   function getRoom(){
   	$db=$this->getAdapter();
   	$sql="SELECT room_id,room_name FROM rms_room ";
   	return $db->fetchAll($sql.'ORDER  BY room_id DESC');
   }
   function getSession(){
   	$db=$this->getAdapter();
   	//$sql="SELECT key_code,CONCAT(name_en,'-',name_kh) AS view_name FROM rms_view WHERE `type`=4 AND `status`=1";
   	$sql="SELECT key_code,name_en AS view_name FROM rms_view WHERE `type`=4 AND `status`=1";
	return $db->fetchAll($sql);
   }
   function getGender(){
   	$db=$this->getAdapter();
   	$sql="SELECT key_code,CONCAT(name_en,'-',name_kh) AS view_name FROM rms_view WHERE `type`=2 AND `status`=1";
   	return $db->fetchAll($sql);
   }
   function getAllBranchName(){
   	$db = $this->getAdapter();
   	$sql=" SELECT br_id AS id,branch_nameen as name FROM `rms_branch` WHERE STATUS=1 AND (branch_namekh!='' OR branch_nameen!='') ";
   	return $db->fetchAll($sql);
   }
   function getallProductName(){
   	$db = $this->getAdapter();
   	$sql=" SELECT id ,pro_name as name FROM `rms_product` 
   		WHERE status=1 AND pro_name!='' ORDER BY pro_name,sale_set ";
   	return $db->fetchAll($sql);
   }
   
   function getAllBranch(){
   	$db = $this->getAdapter();
   	$sql=" SELECT br_id,branch_namekh,branch_nameen FROM `rms_branch` WHERE STATUS=1 AND branch_namekh!='' ";
   	$sql.=$this->getAccessPermission('br_id');
   	return $db->fetchAll($sql);
   }
   public function getAccessPermission($branch_str='branch_id'){
	   	$session_user=new Zend_Session_Namespace('authstu');
	   	$branch_id = $session_user->branch_id;
	   	if(!empty($branch_id)){
		   	$level = $session_user->level;
		   	if($level==1 OR $level==2){
		   		$result = "";
		   		return $result;
		   	}
		   	else{
		   		$result = " AND $branch_str =".$branch_id;
		   		return $result;
		   	}
	   	}
	   	$session_teacher=new Zend_Session_Namespace('authteacher');
	   	$branch_id = $session_teacher->branch_id;
	   	if(!empty($branch_id)){
	   		$result = " AND $branch_str =".$branch_id;
	   		return $result;
	   	}
   }
   
   public function getUserAccessPermission($user_id='user_id'){
	   	$user = $this->getUserId();
	   	$session_user=new Zend_Session_Namespace('authstu');
	   	$level = $session_user->level;
	   	if($level==1){
	   		$result = "";
	   		return $result;
	   	}
	   	else{
	   		$result = " AND $user_id =".$user;
	   		return $result;
	   	}
   }
   
   function getUserType(){
   	$session_user=new Zend_Session_Namespace('authstu');
   	return $session_user->level;
   }
   
   function getViewById($type,$is_opt=null){
   	$db=$this->getAdapter();
   	$sql="SELECT key_code,name_kh AS view_name FROM rms_view WHERE `type`=$type AND `status`=1 ";
   	$rows = $db->fetchAll($sql);
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$options= array(-1=>$tr->translate("CHOOSE"));
   	if($is_opt!=null){
   		if(!empty($rows))foreach($rows AS $row){
   			$options[$row['key_code']]=$row['view_name'];
   		}
   	}else{
   		return $rows;
   	}
   	return $options;
   }
   function getExchangeRate(){
	   	$db=$this->getAdapter();
	   	$sql="select reil from rms_exchange_rate";
	   	return $db->fetchOne($sql);
   }
   function getDegree(){
	   	$db=$this->getAdapter();
	   	$sql="SELECT dept_id as id,CONCAT(en_name) AS name FROM rms_dept WHERE `is_active`=1";
	   	return $db->fetchAll($sql);
   }
   function getAllYear(){
	   	$db = $this->getAdapter();
	   	$sql = "SELECT id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS name FROM rms_tuitionfee WHERE `status`=1
	   	GROUP BY from_academic,to_academic,generation";
	   	$order=' ORDER BY id DESC';
	   	return $db->fetchAll($sql.$order);
   }
   function getAllGrade(){
	   	$db=$this->getAdapter();
	   	$sql="SELECT major_id as id,major_enname AS name FROM rms_major WHERE `is_active`=1";
	   	return $db->fetchAll($sql);
   }
   public function getExpenseIncome($type){
   	$db = $this->getAdapter();
   	$_db = new Application_Model_DbTable_DbGlobal();
   	$branch_id = $_db->getAccessPermission();
   	$sql = "SELECT id ,account_name as name FROM `rms_account_name` WHERE status=1 AND account_name!=''
   			AND account_type = ".$type;
   	return $db->fetchAll($sql);
   }
   function getAllStudent($opt=null,$type){
   	$db = $this->getAdapter();
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$sql=" SELECT stu_id As id,stu_code,CONCAT(stu_khname,'-',stu_enname) as name FROM `rms_student` WHERE stu_khname!='' AND STATUS=1 AND is_subspend=0 ";
   	$rows = $db->fetchAll($sql);
   	if($opt!=null){
   		$options=array(0=>$tr->translate("CHOOSE"));
   		if(!empty($rows))foreach($rows AS $row){
   			$lable = $row['stu_code'];
   			if($type==2){$lable = $row['name'];}
   			$options[$row['id']]=$lable;
   		}
   	}
   	return $options;
   }
   function getDeduct(){
	   	$db = $this->getAdapter();
	   	$sql=" SELECT value FROM `ln_system_setting` WHERE id=19 ";
	   	return $db->fetchOne($sql);
   }
   public function getExpenseRecieptNo(){
   	$db = $this->getAdapter();
   	$_db = new Application_Model_DbTable_DbGlobal();
//    	$branch_id = $_db->getAccessPermission();
   	$sql="SELECT id FROM ln_expense WHERE 1 ORDER BY id DESC LIMIT 1 ";
   	$acc_no = $db->fetchOne($sql);
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);
   	$pre=0;
   	for($i = $acc_no;$i<6;$i++){
   		$pre.='0';
   	}
   	return $pre.$new_acc_no;
   }
   
   function getAllStuCode(){
   		$db = $this->getAdapter();
		$sql=" select stu_id as id , stu_code from rms_student where status=1 and is_subspend=0 ";
		return $db->fetchAll($sql);
   }
   
   
   function getAllStuName(){
   	$db = $this->getAdapter();
   	$sql=" select stu_id as id , CASE WHEN stu_khname IS NULL THEN stu_enname ELSE stu_khname END AS name from rms_student where status=1 and is_subspend=0 ";
   	return $db->fetchAll($sql);
   }
   function getAllGeneration($opt=null,$option=null){
   	$db= $this->getAdapter();
   	$sql="SELECT  DISTINCT(generation) AS generation FROM `rms_tuitionfee` WHERE generation!=''ORDER BY id DESC ";
   	$rows =  $db->fetchAll($sql);
   	if($opt==null){
   		return $rows;
   	}else{
   		if($option!=null){
   			$opt_gen = array(-1=>"Please Select Type");
   		}else{
   			$opt_gen=array();
   		}
   		if(!empty($rows))foreach($rows AS $row) $opt_gen[$row['generation']]=$row['generation'];
   		return $opt_gen;
   	}
   
   }
   /**notification alert*/
   function gettokenbystudentid($student_id){
   	$sql="SELECT token FROM `mobile_mobile_token` WHERE stu_id=$student_id";
   	return $this->getAdapter()->fetchAll($sql);
   }
   function setTitleTonotification($title_id){
   	$db = $this->getAdapter();
   	$title_label = array(
   			1=>'lbl_smspaymentpaid',
   			2=>'lbl_smsatt',
   			3=>'lbl_smsmistake',
   			4=>'lbl_smsscore',
   			5=>'lbl_smsnews',
   			6=>'lbl_smsnotification',
   			7=>'lbl_paymentnotification',
   			);
   	$sql="SELECT keyValue FROM `moble_label` 
   		WHERE keyName='".$title_label[$title_id]."'";
   	return $db->fetchOne($sql);
//    	$title_str = array(
//    			1=>"Thanks your payment",
//    			2=>"you got student attendance !",
//    			3=>"you got student mistake !",
//    			4=>"you got score result !",
//    			5=>"you got news/events notification for psis!",
//    	);
//    	return $title_str[$title_id];
   }
   function pushSendNotification($student_id,$title_id,$body='',$data=''){//$stu_id
   	$key = new Application_Model_DbTable_DbKeycode();
   	$dbset=$key->getKeyCodeMiniInv(TRUE);
   	if($dbset['notification']==0){return 1;}
   	$url = "https://fcm.googleapis.com/fcm/send";
   	//$token = "f5-E45BencY:APA91bGLSiLBRH7vEWVvXr9s9vuuSxWnXH8cRvf6EsJUGtwQRjO8yyftsODIGZ7pPzt7CD_63vEbJjcQYSgSUHDaoDIdal46J_Tdi-R1y-Y02wSsJrmZ7v349_-fHy8-Sj34Hurdkwg-";
   	$serverKey = 'AAAAFUl5wqk:APA91bFktDCO937lkDQ1JnP3fT5xT9YMfdEmBq0GH-QZs-GUGy9YbceyMvLQHNw3LBkgPbV9tZfDmzjti6oaJQyVHzhWrBmvoTdUaNhvD-q5DC3KNunJMVjDRTG3VLPrBVB8c8H9NNb_';
   	//$title = $req->title;
   	//$body = $req->message;
   
   	$title = $this->setTitleTonotification($title_id);
   	$rs_appuse = $this->gettokenbystudentid($student_id);
   	 
   	$data=array('id'=>1,
   			'title'=>$title,
   			'body'=>$body,
   			'note_type'=>1
   	);
   	 
   	$notification = array(
   			'title' =>$title ,
   			'text' => $body,
   			'data' =>$data,
   			'sound' => 'default',
   			'badge' => '1'
   	);
   	 
   	if(!empty($rs_appuse)){
   		foreach ($rs_appuse as $rs){
   			
   			$arrayToSend = array(
   					'to' => $rs['token'],
   					'notification' => $notification,
   					'priority'=>'high'
   			);
   			$json = json_encode($arrayToSend);
   			$headers = array();
   			$headers[] = 'Content-Type: application/json';
   			$headers[] = 'Authorization: key='. $serverKey;
   			$ch = curl_init();
   			curl_setopt($ch, CURLOPT_URL, $url);
   			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
   			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
   			curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
   			//Send the request
   			$response = curl_exec($ch);
   			//Close request
   			if ($response === FALSE) {
   				//die('FCM Send Error: ' . curl_error($ch));
   			}
   			curl_close($ch);
   		}
   	}
   }
   
   function resizeImase($image,$part,$new_imagename=null){
    $photo = $image;
    $temp = explode(".", $photo["name"]);
    $new_name = $temp[0].end($temp);
    if (!empty($new_imagename)){
      $new_name = $new_imagename;
    }
    move_uploaded_file($image["tmp_name"], $part .$new_name);
      
    $uploadimage=$part.$new_name;
//    $newname=$image["name"];
//    // Load the stamp and the photo to apply the watermark to
    if (end($temp) == 'jpg') {
      $im = imagecreatefromjpeg($uploadimage);
    } else
      if (end($temp) == 'jpeg') {
      $im = imagecreatefromjpeg($uploadimage);
    } else
      if (end($temp) == 'png') {
      $im = imagecreatefrompng($uploadimage);
    } else
      if (end($temp) == 'gif') {
      $im = imagecreatefromgif($uploadimage);
    }
  
    if ($image['size']>(1000000*5)){
      // Save the image to file and free memory quality 50%
      imagejpeg($im, $uploadimage, 50);
    }else if($image['size']>(1000000)){
      imagejpeg($im, $uploadimage, 70); //quality 80%
    }else if($image['size']>512000){
      // Save the image to file and free memory quality 60%
      imagejpeg($im, $uploadimage, 80);
    }else if($image['size']>=1024000){
      	// Save the image to file and free memory quality 60%
      	imagejpeg($im, $uploadimage, 90);
    }
  //  imagedestroy($uploadimage);
    	
    return $new_name;
      
  }
   
  
  public function getAllSubjectName(){
  	$db = $this->getAdapter();
  	$sql=" SELECT id ,subject_titlekh AS name FROM `rms_subject` WHERE STATUS=1 AND subject_titlekh != '' order by id DESC ";
  	return $db->fetchAll($sql);
  }
   
  public function getAllTeahcerName(){
  	$db = $this->getAdapter();
  	$sql=" SELECT id ,teacher_name_kh AS name FROM `rms_teacher` WHERE STATUS=1 AND teacher_name_kh != '' order by id DESC ";
  	return $db->fetchAll($sql);
  }
   
  public function getAllDayName(){
  	$db = $this->getAdapter();
  	$sql=" SELECT key_code as id ,name_en AS name FROM `rms_view` WHERE status=1 AND name_en!= '' AND TYPE=18";
  	return $db->fetchAll($sql);
  }

  function getAllStudentConcat(){
  	$db=$this->getAdapter();
  	$_db = new Application_Model_DbTable_DbGlobal();
  	$branch_id = $_db->getAccessPermission();
  	$sql="SELECT s.stu_id As id,s.stu_code As stu_code,CONCAT(s.stu_enname,'(',s.stu_code,')')AS `name` FROM rms_student AS s
  	WHERE s.status=1 and s.is_subspend=0  $branch_id  ORDER BY stu_type DESC ";
  	return $db->fetchAll($sql);
  }
  
  function getAllTerm(){
  	$db = $this->getAdapter();
  	$SQL="select key_code as id , name_en as name from rms_view where type=6 and status=1 ";
  	return $db->fetchAll($SQL);
  }
  function getTestStudentId(){
  	$db = $this->getAdapter();
  	$sql ="SELECT id AS number FROM `rms_student_test` ORDER BY id DESC LIMIT 1 ";
  	$acc_no = $db->fetchOne($sql);
  	 
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
  	$pre="";
  	for($i = $acc_no;$i<6;$i++){
  		$pre.='0';
  	}
  	$last = '';
  	return $pre.$new_acc_no.$last;
  }
  function getallTermtest(){
  	$db = $this->getAdapter();
  	$sql="select start_date,end_date,note,
  		CONCAT(note,'(',start_date,' to ',end_date,')') as id,
  		CONCAT(note,'(',start_date,' to ',end_date,')') as name
  	 FROM rms_test_term ";
  	return $db->fetchAll($sql);
  }
  
}
?>