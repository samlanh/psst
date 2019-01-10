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
	function currentlang(){
		$session_lang=new Zend_Session_Namespace('lang');
		$lang = $session_lang->lang_id;
		if (empty($session_lang->lang_id)){
			$lang = 1;
		}
		return $lang;
	}
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
   
   function getAllUser($branchId=null){
	   	$db = $this->getAdapter();
	   	$sql="SELECT
			u.id,
			CONCAT(u.last_name,' ',u.first_name) AS name,
			u.branch_id,
			u.branch_list
			 FROM `rms_users` AS u WHERE u.active=1
			AND u.is_system =0";
	   $row = $db->fetchAll($sql);
	   	
	   	$session_user=new Zend_Session_Namespace('authstu');
	   	$branch_list = $session_user->branch_list;
	   	$level = $session_user->level;
	   
	   	$result =array();
	   	if ($level!=1){
		   	$bra = explode(",", $branch_list);
			if (!empty($bra)){
				$array = array();
				foreach ($bra as $ss) {
					$array[$ss] = $ss;
				}
				if (!empty($row)) foreach ($row as $key => $rs){
					$exp = explode(",", $rs['branch_list']);
					foreach ($exp as $ss){
						if (in_array($ss, $array)) {
							$result[$key] = $rs;
							break;
						}
					}
				}
			}
	   	}
	   	if (!empty($result)){
	   		$row = $result;
	   	}
// 	   	// this case check all user that avaiable in all branch that current user can access
// 	   	$sql.= $this->getAccessPermission("u.branch_list");
	   	
		//this for check more by branch record of data
	   	//ex: when we enter register student in which branch filter only user in that branch
	   	$result =array();
	   	if (!empty($branchId)){
	   		$bra = explode(",", $branchId);
			if (!empty($bra)){
				$array = array();
				foreach ($bra as $ss) {
					$array[$ss] = $ss;
				}
				if (!empty($row)) foreach ($row as $key => $rs){
					$exp = explode(",", $rs['branch_list']);
					foreach ($exp as $ss){
						if (in_array($ss, $array)) {
							$result[$key] = $rs;
							break;
						}
					}
				}
			}
	   	}
	   	if (!empty($result)){
	   		$row = $result;
	   	}
	   	return $row;
   }
   
   public function getUserInfo(){
	   	$session_user=new Zend_Session_Namespace('authstu');
	   	$session_teacher=new Zend_Session_Namespace('authteacher');
	   	if (!empty($session_user->user_id)){
		   	$userName=$session_user->user_name;
		   	$GetUserId= $session_user->user_id;
		   	$level = $session_user->level;
		   	$location_id = $session_user->branch_id;
		   	$branch_list = $session_user->branch_list;
		   	$schoolOption = $session_user->schoolOption;
		   	$info = array("user_name"=>$userName,"user_id"=>$GetUserId,"level"=>$level,"branch_id"=>$location_id,"branch_list"=>$branch_list,"schoolOption"=>$schoolOption);
		   	return $info;
	   	}elseif (!empty($session_teacher->teacher_id)){
	   		$userName	=$session_teacher->teacher_name;
	   		$teacherId 	= $session_teacher->teacher_id;
	   		$location_id = $session_teacher->branch_id;
	   		$branch_list = $session_teacher->branch_list;
	   		$schoolOption = $session_teacher->schoolOption;
	   		$info = array("user_name"=>$userName,"teacher_id"=>$teacherId,"level"=>null,"branch_list"=>$branch_list,"schoolOption"=>$schoolOption);
	   		return $info;
	   	}
   }
   
   public function getAllSession(){
	   	$db = $this->getAdapter();
	   	$sql ="SELECT key_code as id,name_en as name FROM rms_view WHERE type=4 AND status=1 ";
	   	return $db->fetchAll($sql);
   }
   

//    public function getProvince(){
//    	$db = $this->getAdapter();
//    	$sql ="SELECT province_en_name,province_id FROM rms_province WHERE status=1 AND province_en_name!='' ";
//    	return $db->fetchAll($sql);
//    }


   public function getProvince(){
   	$db = $this->getAdapter();
   	$lang = $this->currentlang();
   	$field = 'province_en_name';
   	if ($lang==1){
   		$field = 'province_kh_name';
   	}
   	$sql ="SELECT $field as province_en_name,province_id FROM rms_province WHERE status=1 AND province_en_name!='' ";
   	return $db->fetchAll($sql);
   }

   public function getOccupation(){
   	$db = $this->getAdapter();
   	$sql ="SELECT occupation_id as id, occu_name as name FROM rms_occupation WHERE status=1 AND occu_name!='' 
   	ORDER BY occu_name ASC ";
   	return $db->fetchAll($sql);
   }
   
   public function getAllLangLevel(){
	   	$db = $this->getAdapter();
	   	$sql = "SELECT id as id, title as name FROM rms_degree_language WHERE status=1 AND title!='' ORDER BY rms_degree_language.id ASC ";
	   	return $db->fetchAll($sql);
   }
   
   public function getAllNation(){
   	$db = $this->getAdapter();
   	$lang = $this->currentlang();
   	$field = 'name_en';
   	if ($lang==1){
   		$field = 'name_kh';
   	}
   	$sql = "SELECT key_code as id, $field AS name FROM rms_view WHERE rms_view.type=21 AND name_kh!='' ORDER BY rms_view.id ASC";
   	return $db->fetchAll($sql);
   }
   
   public function getAllKnoyBy(){
   	$db = $this->getAdapter();
   	$sql = "SELECT id as id, title as name FROM rms_know_by WHERE status=1 AND title!='' ORDER BY rms_know_by.id ASC ";
   	return $db->fetchAll($sql);
   }
   public function getAllKnowBy(){
   	$db = $this->getAdapter();
   	$sql = "SELECT id as id, title as name FROM rms_know_by WHERE status=1 AND title!='' ORDER BY rms_know_by.id ASC ";
   	return $db->fetchAll($sql);
   }
   public function getAllDocumentType(){
   	$db = $this->getAdapter();
   	$sql = "SELECT name as id, name FROM rms_document_type WHERE types=1 AND status=1 AND name!='' ORDER BY rms_document_type.id ASC ";
   	return $db->fetchAll($sql);
   }
   
   public function getAllDocteacherType(){
   	$db = $this->getAdapter();
   	$sql = " SELECT name as id, name FROM rms_document_type WHERE types=2 AND status=1 AND name!='' ORDER BY rms_document_type.id ASC ";
   	return $db->fetchAll($sql);
   }
   
   public function getAllFecultyNamess($type){
   		return $this->getAllFecultyName();
   }
   public function getAllDegreeName(){
   	return $this->getAllFecultyName();
   }
   
 public function getAllFecultyName(){
   	$db = $this->getAdapter();
   	return $this->getAllItems(1,null);
} 
  
function getAllgroupStudy($teacher_id=null){
   	$db = $this->getAdapter();
   	$sql ="SELECT `g`.`id`, CONCAT(`g`.`group_code`,' ',
   			(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation limit 1) ) AS name
   		FROM `rms_group` AS `g` ";
   	if($teacher_id!=null){
   		$sql.=" ,rms_group_subject_detail AS gsd WHERE g.id =gsd.group_id AND gsd.teacher= ".$teacher_id;
   	}else{
   		$sql.=" WHERE 1";
   	}
   	$sql.=" AND g.status =1 AND group_code!=''";
   	return $db->fetchAll($sql);
}

function getAllgroupStu($branch_id=null){
	$db = $this->getAdapter();
	$sql ="SELECT `g`.`id`, CONCAT(`g`.`group_code`,' ',
	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation limit 1) ) AS name
	FROM `rms_group` AS `g` where branch_id=$branch_id ";
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
   
   public function getAllServiceItemsName($status=1,$type=null){
   		$db = $this->getAdapter();
   		return $this->getAllItems(null);
   }   
   public function getAllSubjectStudy($schoolOption=null){
   	$db = $this->getAdapter();

	   	$lang = $this->currentlang();
	   	$field = 'subject_titleen';
	   	if ($lang==1){
	   		$field = 'subject_titlekh';
	   	}
   		$sql = " SELECT id,$field as name,shortcut FROM `rms_subject` WHERE
   		is_parent=1 AND status = 1 and subject_titlekh!='' ";
   		if (!empty($schoolOption)){
   			$sql.=" AND schoolOption = $schoolOption";
   		}
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
	   			0=>$this->tr->translate("SELECT_DEGREE"),
	   			1=>$this->tr->translate("ASSOCIATE"),
	   			2=>$this->tr->translate("BACHELOR"),
	   			3=>$this->tr->translate('MASTER'),
	   			4=>$this->tr->translate('DOCTORATE'),
	   			5=>$this->tr->translate('English Program'),
	   			6=>$this->tr->translate('Computer Course'),
	   			7=>$this->tr->translate('Other')
	   );
	   if($id==null)return $rs; 
	   return $rs[$id];
   }
   
   public function getAllDegreeStu($id=null){
   	$rs = array(
   			0=>$this->tr->translate("SELECT_DEGREE"),
   			1=>$this->tr->translate("ASSOCIATE"),
   			2=>$this->tr->translate("BACHELOR"),
   			3=>$this->tr->translate('MASTER'),
   			4=>$this->tr->translate('DOCTORATE'),
   			5=>$this->tr->translate('English Program'),
   			6=>$this->tr->translate('Computer Course'),
   			7=>$this->tr->translate('Other')
   	);
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
   public function AllStatusRe($id=null){
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$rs = array(
   			0=>$tr->translate("PLEASE_SELECT_STATUS"),
   			1=>$tr->translate("RELATIVE"),
   			2=>$tr->translate("FRIEND"),
   			3=>$tr->translate("BUSINESS_PARTNER"),
   			4=>$tr->translate("OTHER")
   			);
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
//    			0=>$this->tr->translate('PLEASE_SELECT'),
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
   
   function getTeacherCode($branch_id=0){
   	$db = $this->getAdapter();
   	
   	$pre = $this->getPrefixCode($branch_id);
   	
   	$sql =" SELECT COUNT(id) AS number FROM `rms_teacher` where branch_id = $branch_id LIMIT 1 ";
   	$acc_no = $db->fetchOne($sql);
   
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);

   	for($i = $acc_no;$i<5;$i++){
   		$pre.='0';
   	}
   	//$last = '/CAS';
   	return $pre.$new_acc_no;
   }
   
   function getPrefixCode($branch_id){
	   	$db  = $this->getAdapter();
	   	$sql = " SELECT prefix FROM `rms_branch` WHERE br_id = $branch_id LIMIT 1 ";
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
   	$lang = $this->currentlang();
   	$field = 'province_en_name';
   	if ($lang==1){
   		$field = 'province_kh_name';
   	}
   	$sql="SELECT province_id as id,$field AS name FROM rms_province WHERE status=1 ";
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
   	$lang = $this->currentlang();
   	$field = 'district_name';
   	if ($lang==1){
   		$field = 'district_namekh';
   	}
//    	$sql = " SELECT dis_id,pro_id,CONCAT(district_name,'-',district_namekh) district_name FROM $this->_name WHERE status=1 AND district_name!='' ";
   	$sql = " SELECT dis_id,pro_id,$field AS district_name FROM $this->_name WHERE status=1 AND district_name!='' ";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql);
   }
   
   public function getAllsubject(){
   	$db = $this->getAdapter();
   	
   	$lang = $this->currentlang();
   	$field = 'subject_titleen';
   	if ($lang==1){
   		$field = 'subject_titlekh';
   	}
//    	$sql = "SELECT id ,CONCAT(subject_titleen,'-',subject_titlekh) AS  subject_name
//    	FROM `rms_subject` WHERE status=1 AND(subject_titleen!='' OR subject_titlekh!='')";
   	$sql = "SELECT id ,$field AS  subject_name
   	FROM `rms_subject` WHERE status=1 AND(subject_titleen!='' OR subject_titlekh!='')";
   	return $db->fetchAll($sql);
   }
   function getAllMajor(){
   	return $this->getAllGradeStudy();
   }
  
   
   public function getAllGroup(){
	   	$db = $this->getAdapter();
	   	$sql=" SELECT id ,group_code As name FROM `rms_group` WHERE status=1 AND group_code != '' order by id DESC ";
	   	return $db->fetchAll($sql);
   }
    
   public function getAllTeacherSubject(){
   	$db = $this->getAdapter();
   	$branch_id = $this->getAccessPermission();
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
   public function getAllRoom(){
   	$db = $this->getAdapter();
   	$sql=" SELECT room_id AS id ,room_name As name FROM `rms_room` WHERE is_active=1 AND room_name!='' order by room_id DESC ";
   	return $db->fetchAll($sql);
   }
   public function getAllDegreeMent(){
   	$db = $this->getAdapter();
   	$sql=" SELECT key_code AS id, name_kh AS name FROM rms_view WHERE STATUS=1 AND TYPE=3 AND name_kh!='' ORDER BY rms_view.key_code ASC ";
   	return $db->fetchAll($sql);
   }
   function getSession(){
   	$db=$this->getAdapter();
   	$sql="SELECT key_code AS id,key_code,name_en aS name,name_en AS view_name 
   		FROM rms_view WHERE `type`=4 AND `status`=1";
	return $db->fetchAll($sql);
   }
   function getGender(){
   	$db=$this->getAdapter();
   	$lang = $this->currentlang();
   	$field = 'name_en';
   	if ($lang==1){
   		$field = 'name_kh';
   	}
//    	$sql="SELECT key_code,CONCAT(name_en,'-',name_kh) AS view_name FROM rms_view WHERE `type`=2 AND `status`=1";
   	$sql="SELECT key_code,$field AS view_name FROM rms_view WHERE `type`=2 AND `status`=1";
   	return $db->fetchAll($sql);
   }
   function getAllBranchName(){
   	$db = $this->getAdapter();
   	$sql=" SELECT br_id AS id,branch_nameen as name FROM `rms_branch` WHERE STATUS=1 AND (branch_namekh!='' OR branch_nameen!='') ";
   	return $db->fetchAll($sql);
   }
   
   function getAllDiscount($option=null){
	   	$db = $this->getAdapter();
	   	$sql="  SELECT disco_id AS id,dis_name AS name FROM `rms_discount` 
	   		WHERE status=1 AND dis_name!='' ";
	   	$rows = $db->fetchAll($sql);
	   	if($option!=null){
	   		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	   		$options=array(0=>$tr->translate("CHOOSE"));
	   		if(!empty($rows))foreach($rows AS $row){
	   			$options[$row['id']]=$row['name'];
	   		}
	   		return $options;
	   	}else{
	   		return $rows;
	   	}
   }
   function getAllDepartment(){
   	$db = $this->getAdapter();
   	$sql="  SELECT depart_id AS id,depart_namekh AS name FROM `rms_department` WHERE STATUS=1 AND depart_namekh!=''  ";
   	return $db->fetchAll($sql);
   }
   
   function getAllStudentName($opt=null,$type=2,$branchid=null){
   	$db=$this->getAdapter();
   	$branch_id = $this->getAccessPermission();
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$sql=" SELECT s.stu_id AS id,s.stu_id AS stu_id,
   			stu_code,
		   	CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.stu_enname,''),' ',COALESCE(s.last_name,'')) AS name
		   	FROM rms_student AS s
		   	WHERE
		   	(stu_enname!='' OR s.stu_khname!='')
		   ";
   	if($branchid!=null){
   		$sql.=" AND branch_id=".$branchid;
   	}
   	$sql.=" ORDER BY degree DESC,stu_khname ASC";
   	$rows = $db->fetchAll($sql);
   	if($opt!=null){
   		$options=array(0=>$tr->translate("CHOOSE"));
   		if(!empty($rows))foreach($rows AS $row){
   			$lable = $row['stu_code'];
   			if($type==2){$lable = $row['name'];}
   			$options[$row['id']]=$lable;
   		}
   		return $options;
   	}else{
   		return $rows;
   	}
   }
   
   function getallProductName(){
   	$db = $this->getAdapter();
   	$sql=" SELECT id ,pro_name as name FROM `rms_product` 
   		WHERE status=1 AND pro_name!='' ORDER BY pro_name,sale_set ";
   	return $db->fetchAll($sql);
   }
   
	function getAllBranch(){
    	$db = $this->getAdapter();
    	$branch_id = $this->getAccessPermission('br_id');
    	$sql="select br_id as id, CONCAT(branch_nameen) as name from rms_branch WHERE branch_nameen!='' AND  status=1  $branch_id ";
    	return $db->fetchAll($sql);
    } 
   public function getAccessPermission($branch_str='branch_id'){
	   	$session_user=new Zend_Session_Namespace('authstu');
// 	   	$branch_id = $session_user->branch_id;
	   	$branch_list = $session_user->branch_list;
	  
	   	$result="";
	   	if(!empty($branch_list)){
		   	$level = $session_user->level;
		   	if($level==1 ){
		   		$result.= "";
		   	}
		   	else{
		   		$result.= " AND $branch_str IN ($branch_list)";
		   	}
	   	}
	   	return $result;
	   	
	   	$session_teacher=new Zend_Session_Namespace('authteacher');
	   	$branch_id = $session_teacher->branch_id;
	   	if(!empty($branch_id)){
	   		$result = " AND $branch_str =".$branch_id;
	   		return $result;
	   	}
   }
   
   function getSchoolOptionAccess($schooloption_coloum="schoolOption"){
	   	$session_user=new Zend_Session_Namespace('authstu');
	   	$level = $session_user->level;
	   	if($level==1){
	   		return ;
	   	}	
   	
	   	$user = $this->getUserInfo();
	   	$schooloptionlist = $user['schoolOption'];
	   	$slist = explode(",", $schooloptionlist);
	   	$sql="";
	   	$s_where = array();
	   	foreach ($slist as $option){
	   		$s_where[] = " $schooloption_coloum IN ($option)";
	   	}
	   	$sql .=' AND ( '.implode(' OR ',$s_where).')';
	   	return $sql;
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
   function getViewByType($type,$is_opt=null){
   	$db=$this->getAdapter();
   	
   	$lang = $this->currentlang();
   	$field = 'name_en';
   	if ($lang==1){
   		$field = 'name_kh';
   	}
   	
   	$sql="SELECT key_code as id ,$field AS name FROM rms_view WHERE `type`=$type AND `status`=1 ORDER BY name_kh ASC ";
   	$rows = $db->fetchAll($sql);
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$options= array(-1=>$tr->translate("CHOOSE"));
   	if($is_opt!=null){
   		if(!empty($rows))foreach($rows AS $row){
   			$options[$row['id']]=$row['name'];
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
   		return $this->getAllItems();
   }
   function getAllYear($type=1){
	   	$db = $this->getAdapter();
	   	$branch_id = $this->getAccessPermission();
	   	$sql = "SELECT id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS name,
	   	CONCAT(from_academic,'-',to_academic,'(',generation,')') AS years 
	   		FROM rms_tuitionfee WHERE `status`=1
	   		AND type=1
	   		AND is_finished=0 $branch_id 
	   	GROUP BY branch_id,from_academic,to_academic,generation ";
	   	$order=' ORDER BY id DESC';
	   	return $db->fetchAll($sql.$order);
   }
   function getAllYearByBranch($branch=1){
   	$db = $this->getAdapter();
   	$branch_id = $this->getAccessPermission();
   	$sql = "SELECT id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS name,
   	CONCAT(from_academic,'-',to_academic,'(',generation,')') AS years
   	FROM rms_tuitionfee WHERE `status`=1
   	AND type=1
   	AND is_finished=0 $branch_id ";
   	$sql.=" AND branch_id=$branch ";
   	$sql.=" GROUP BY from_academic,to_academic,generation";
   	$order=' ORDER BY id DESC';
   	return $db->fetchAll($sql.$order);
   }
   function getAllGrade(){
	  return $this->getAllGradeStudy();
   }
   public function getExpenseIncome($type){
   	$db = $this->getAdapter();
   	$branch_id = $this->getAccessPermission();
   	$sql = "SELECT id ,account_name as name FROM `rms_account_name` WHERE status=1 AND account_name!=''
   			AND account_type = ".$type;
   	return $db->fetchAll($sql);
   }
   /*blog get student*/
   function getAllStudent($opt=null,$type=2,$branchid=null){
   	$db=$this->getAdapter();
   	$branch_id = $this->getAccessPermission();
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$sql=" SELECT s.stu_id AS id,s.stu_id AS stu_id,
   			stu_code,
		   	CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.stu_enname,''),' ',COALESCE(s.last_name,'')) AS name
		   	FROM rms_student AS s
		   	WHERE
		   	(stu_enname!='' OR s.stu_khname!='')
		   	AND s.status=1 AND s.is_subspend=0 AND customer_type=1 ";
   	if($branchid!=null){
   		$sql.=" AND branch_id=".$branchid;
   	}
   	$sql.=" ORDER BY degree DESC,stu_khname ASC";
   	$rows = $db->fetchAll($sql);
   	if($opt!=null){
   		$options=array(0=>$tr->translate("CHOOSE"));
   		if(!empty($rows))foreach($rows AS $row){
   			$lable = $row['stu_code'];
   			if($type==2){$lable = $row['name'];}
   			$options[$row['id']]=$lable;
   		}
   		return $options;
   	}else{
   		return $rows;
   	}
   }
   function getAllStuCode(){
   	$db = $this->getAdapter();
   	$branch_id = $this->getAccessPermission();
   	$sql="SELECT 
   	s.stu_id As id,
   	s.stu_id As stu_id,
   	s.stu_code As stu_code,
   	s.stu_code AS name,
   	(CASE WHEN s.stu_khname IS NULL THEN s.stu_enname ELSE s.stu_khname END) AS stu_name
   	FROM rms_student AS s
   	WHERE s.status=1 and s.is_subspend=0 AND customer_type=1 $branch_id  ORDER BY stu_type DESC ";
   	
   	return $db->fetchAll($sql);
   }
   function getAllStuName(){
   	$db = $this->getAdapter();
//    	$sql=" select stu_id as id , CASE WHEN stu_khname IS NULL THEN stu_enname ELSE stu_khname END AS name from rms_student where status=1 and is_subspend=0 ";
//    	return $db->fetchAll($sql);
	return $this->getAllStudent();
   }
   function getStudentinfoById($stu_id){
	   	$db=$this->getAdapter();
	   	$sql="SELECT s.*,
	   		(SELECT group_code FROM `rms_group` WHERE id=s.group_id LIMIT 1) as group_name,
	   		(SELECT name_kh FROM `rms_view` WHERE type=3 AND key_code=s.calture LIMIT 1) as degree_culture,
	   		(SELECT total_amountafter FROM rms_creditmemo WHERE student_id = $stu_id and total_amountafter>0 ) AS total_amountafter,
	   		(SELECT id FROM rms_creditmemo WHERE student_id = $stu_id and total_amountafter>0 ) AS credit_memo_id,
	   		(SELECT title FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=s.grade LIMIT 1) as grade_label,
	   		(SELECT title FROM `rms_items` WHERE rms_items.id=s.degree LIMIT 1) as degree_label,
	   		(SELECT name_kh FROM `rms_view` WHERE type=4 AND key_code=s.session LIMIT 1) as session_label,
	   		(SELECT room_name FROM `rms_room` WHERE room_id=s.room LIMIT 1) AS room_label
	   		FROM rms_student as s
	   			WHERE s.stu_id=$stu_id LIMIT 1 ";
	   	return $db->fetchRow($sql);
   }
   function getStudentTestinfoById($stu_id){//for student with result
   	$db=$this->getAdapter();
   	$sql="SELECT s.*,
	   	(SELECT group_code FROM `rms_group` WHERE id=s.group_id LIMIT 1) as group_name,
	   	(SELECT name_kh FROM `rms_view` WHERE type=3 AND key_code=s.calture LIMIT 1) as degree_culture,
	   	(SELECT total_amountafter FROM rms_creditmemo WHERE student_id = $stu_id and total_amountafter>0 ) AS total_amountafter,
	   	(SELECT id FROM rms_creditmemo WHERE student_id = $stu_id and total_amountafter>0 ) AS credit_memo_id,  	
	   	(SELECT title FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=t.grade_result LIMIT 1) as grade_label,
		(SELECT title FROM `rms_items` WHERE rms_items.id=t.degree_result LIMIT 1) as degree_label,
		t.degree_result AS degree,t.grade_result AS grade,t.session_result AS session,
		t.id,
	    (SELECT name_kh FROM `rms_view` WHERE type=4 AND key_code=s.session LIMIT 1) as session_label,
	   	(SELECT room_name FROM `rms_room` WHERE room_id=s.room LIMIT 1) AS room_label
	   	FROM rms_student as s,
	   	rms_student_test_result As t
	   	WHERE 
	   	t.stu_test_id=$stu_id
	   	AND t.is_current=1 AND updated_result=1
	   	AND s.stu_id=$stu_id LIMIT 1 ";
   	return $db->fetchRow($sql);
   }
   /*tested student*/
   function getAllstudentTest($branch=null,$result=0){//get all
	   	$db=$this->getAdapter();
	   	$branch_id = $this->getAccessPermission();
	   	if($result==0){//មិនទាន់គិតធ្វើតេស និងចេញលទ្ធផល
// 	   		echo 2;exit();
	   		$sql="SELECT stu_id as id,CONCAT(COALESCE(serial,'-'),COALESCE(stu_khname,''),' [',stu_enname,' ',last_name,']') AS name
	   		FROM rms_student
	   		WHERE
	   		(stu_khname!='' OR stu_enname!='') AND status=1 AND customer_type=4 $branch_id  ";
	   		if (!empty($branch)){
	   			$sql.=" AND branch_id = $branch";
	   		}
	   		$sql.=" ORDER BY stu_id DESC";
	   	}else{//ban ធ្វើតេស
// 	   		echo 1;exit();
	   		$sql="SELECT stu_id as id,CONCAT(COALESCE(serial,'-'),COALESCE(stu_khname,''),' [',stu_enname,' ',last_name,']') AS name
	   		FROM rms_student,rms_student_test_result AS result
	   		WHERE
	   		stu_id = result.stu_test_id AND result.updated_result=1 AND
	   		(stu_khname!='' OR stu_enname!='') AND status=1 AND customer_type=4 $branch_id  ";
	   		if (!empty($branch)){
	   			$sql.=" AND branch_id = $branch";
	   		}
	   		$sql.=" ORDER BY stu_id DESC";
	   	}
	   
	   	return $db->fetchAll($sql);
   }
   /*crm student*/
   function getAllCrmstudent($branch=null,$type){//get all
   	$db=$this->getAdapter();
   	$branch_id = $this->getAccessPermission();
   	$sql="SELECT stu_id as id,CONCAT(COALESCE(stu_khname,''),' [',stu_enname,' ',last_name,']') AS name
   	FROM rms_student
   	WHERE (stu_khname!='' OR stu_enname!='') AND status=1 AND customer_type=3 $branch_id  ";
   	if (!empty($branch)){
   		$sql.=" AND branch_id = $branch ";
   	}
   	$sql.=" ORDER BY stu_id DESC";
   	return $db->fetchAll($sql);
   }
//    function getStudentTestbyId($stu_test_id){
//    		$db=$this->getAdapter();
//    		$sql="SELECT * FROM rms_student_test WHERE id = $stu_test_id ";
//    		return $db->fetchRow($sql);
//    }
   /*end blog student*/
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
   
   function getAllGeneration($opt=null,$option=null){
   	$db= $this->getAdapter();
   	$sql="SELECT  DISTINCT(generation) AS generation FROM `rms_tuitionfee` WHERE generation!=''ORDER BY id DESC ";
   	$rows =  $db->fetchAll($sql);
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	if($opt==null){
   		return $rows;
   	}else{
   		if($option!=null){
   			$opt_gen = array(-1=>$tr->translate("Please Select Type"));
   		}else{
   			$opt_gen=array();
   		}
   		if(!empty($rows))foreach($rows AS $row) $opt_gen[$row['generation']]=$row['generation'];
   		return $opt_gen;
   	}
   
   }
   /**notification alert*/
//    function gettokenbystudentid($student_id){
//    	$sql="SELECT token FROM `mobile_mobile_token` WHERE stu_id=$student_id";
//    	return $this->getAdapter()->fetchAll($sql);
//    }
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
   			8=>'lbl_replyfeedback',
   			);
   	$sql="SELECT keyValue FROM `moble_label` 
   		WHERE keyName='".$title_label[$title_id]."'";
   	return $db->fetchOne($sql);
   }
   function getTokenUser($student=null,$group=null,$title_id){
   		$db = $this->getAdapter();
   		if (!empty($group)){//select by gorup
	   		$sql_android="
		 		SELECT mb.`token`
				FROM `rms_group_detail_student` AS sd
				,`mobile_mobile_token` AS mb
				WHERE sd.`group_id` = $group
				AND sd.`stu_id` = mb.`stu_id` AND device_type=1 ";
	   		
	   		$sql_ios="
	   		SELECT mb.`token`
	   		FROM `rms_group_detail_student` AS sd
	   		,`mobile_mobile_token` AS mb
	   		WHERE sd.`group_id` = $group
	   		AND sd.`stu_id` = mb.`stu_id` AND device_type=0 ";
	   		$androidToken =  $db->fetchCol($sql_android);
	   		$iosToken =  $db->fetchCol($sql_ios);
	   		$this->pushSendNotification($androidToken, $iosToken, $title_id);
   		}else if (!empty($student)){//by student id
   			$sql_android="SELECT t.`token` FROM `mobile_mobile_token` AS t WHERE device_type=1 AND t.`stu_id` =$student";
   			$sql_ios="SELECT t.`token` FROM `mobile_mobile_token` AS t WHERE device_type=0 AND t.`stu_id` =$student";
   			$androidToken =  $db->fetchCol($sql_android);
   			$iosToken =  $db->fetchCol($sql_ios);
   			$this->pushSendNotification($androidToken, $iosToken, $title_id);
   		}else{//all 
   			$this->sendMessageAllDevice($title_id);
   		}
   }
   
//    function pushSendNotification($studentToken,$title_id,$body='',$data=''){//$stu_id//google
//    	$key = new Application_Model_DbTable_DbKeycode();
//    	$dbset=$key->getKeyCodeMiniInv(TRUE);
//    	if($dbset['notification']==0){return 1;}
//    	$url = "https://fcm.googleapis.com/fcm/send";
//    	$serverKey = 'AAAAFUl5wqk:APA91bFktDCO937lkDQ1JnP3fT5xT9YMfdEmBq0GH-QZs-GUGy9YbceyMvLQHNw3LBkgPbV9tZfDmzjti6oaJQyVHzhWrBmvoTdUaNhvD-q5DC3KNunJMVjDRTG3VLPrBVB8c8H9NNb_';
   
//    	$title = $this->setTitleTonotification($title_id);
   	 
//    	$data=array('id'=>1,
//    			'title'=>$title,
//    			'body'=>$body,
//    			'type'=>$title_id
//    	);
   	 
//    	$notification = array(
//    			'title' =>$title ,
//    			'text' => $body,
//    			'sound' => 'psis',
//    			'badge' => '1'
//    	);
   	 
//    	$countToken = count($studentToken);
//    		if ($countToken>0){
// 	   			$arrayToSend = array(
// 	   					'registration_ids' => $studentToken,
// 	   					'notification' => $notification,
// 	   					'priority'=>'high',
// 	   					'data' =>$data
// 	   			);
// 	   			$json = json_encode($arrayToSend);
// 	   			$headers = array();
// 	   			$headers[] = 'Content-Type: application/json';
// 	   			$headers[] = 'Authorization: key='. $serverKey;
// 	   			$ch = curl_init();
// 	   			curl_setopt($ch, CURLOPT_URL, $url);
// 	   			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
// 	   			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
// 	   			curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
// 	   			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
// 	   			//Send the request
// 	   			$response = curl_exec($ch);
// 	   			//Close request
// 	   			if ($response === FALSE) {
// 	   				//die('FCM Send Error: ' . curl_error($ch));
// 	   			}
// 	   			curl_close($ch);
// 	   			//curl_setopt(CURLOPT_RETURNTRANSFER, true);
//    		}
//    }
   function pushSendNotification($android_token,$ios_token,$title_id) {
   		$title = $this->setTitleTonotification($title_id);
	   	$content = array(
	   			"en" => $title
	   	);
	   	$fields = array(
	   			'app_id' => "cf9d5315-4a18-45fc-b0e2-78f7eda3eb1e",
	   			'include_android_reg_ids' => $android_token,
	   			'include_ios_tokens' => $ios_token,
	   			'data' => array(
	   					"title" => $title,
	   					"type" => $title_id
	   			),
	   			'contents' => $content
	   	);
	   	 
	   	$fields = json_encode($fields);
	   	 
	   	$ch = curl_init();
	   	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	   	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	   			'Content-Type: application/json; charset=utf-8',
	   			'Authorization: Basic NmQ0ZjE1ZWMtNTJmMC00MTkwLTk3NjktMGFlY2JkOTY4NDEz'
	   	));
	   	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	   	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	   	curl_setopt($ch, CURLOPT_POST, TRUE);
	   	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	   	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	   	 
	   	$response = curl_exec($ch);
	   	curl_close($ch);
   }
   function sendMessageAllDevice($title_id) {
   	$title = $this->setTitleTonotification($title_id);
	   	$content      = array(
	   			"en" => $title
	   	);
	   	$fields = array(
	   			'app_id' => "cf9d5315-4a18-45fc-b0e2-78f7eda3eb1e",
	   			'included_segments' => array(
	   					'All'
	   			),
	   			'data' => array(
	   					"title" => $title,
	   					"type" => $title_id
	   			),
	   			'contents' => $content
	   	);
	   
	   	$fields = json_encode($fields);
	   
	   	$ch = curl_init();
	   	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	   	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	   			'Content-Type: application/json; charset=utf-8',
	   			'Authorization: Basic NmQ0ZjE1ZWMtNTJmMC00MTkwLTk3NjktMGFlY2JkOTY4NDEz'
	   	));
	   	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	   	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	   	curl_setopt($ch, CURLOPT_POST, TRUE);
	   	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	   	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	   
	   	$response = curl_exec($ch);
	   	curl_close($ch);
   }
   
   //function for push all device
   
   
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
  	$lang = $this->currentlang();
  	$field = 'subject_titleen';
  	if ($lang==1){
  		$field = 'subject_titlekh';
  	}
  	$sql=" SELECT id ,$field AS name FROM `rms_subject` WHERE STATUS=1 AND subject_titlekh != '' order by id DESC ";
  	return $db->fetchAll($sql);
  }
   
  public function getAllTeahcerName(){
  	$db = $this->getAdapter();
  	$sql=" SELECT id ,teacher_name_kh AS name FROM `rms_teacher` WHERE STATUS=1 AND teacher_name_kh != '' order by id DESC ";
  	return $db->fetchAll($sql);
  }
   
  public function getAllDayName(){
  	$db = $this->getAdapter();
  	$lang = $this->currentlang();
  	$field = 'name_en';
  	if ($lang==1){
  		$field = 'name_kh';
  	}
  	$sql=" SELECT key_code as id ,$field AS name FROM `rms_view` WHERE status=1 AND name_en!= '' AND TYPE=18";
  	return $db->fetchAll($sql);
  }

//   function getAllStudent(){
//   	$db=$this->getAdapter();
//   	$_db = new Application_Model_DbTable_DbGlobal();
//   	$branch_id = $_db->getAccessPermission();
//   	$sql="SELECT s.stu_id AS id,s.stu_id AS stu_id,
//     		CONCAT(s.stu_code,'-',s.stu_khname,'-',s.stu_enname,' ',s.last_name) AS name
//     		FROM rms_student AS s
//     		WHERE 
//     		(stu_enname!='' OR s.stu_khname!='') 
//     		AND s.status=1 AND s.is_subspend=0 AND customer_type=1 
//   		$branch_id ORDER BY stu_type DESC,stu_khname ASC ";
//   	return $db->fetchAll($sql);
//   }
  function getAllTermStudy(){
  	$db = $this->getAdapter();
  	$sql="select id,start_date,end_date,note,CONCAT(note,'(',start_date,' to ',end_date,')') as name
  	FROM rms_startdate_enddate WHERE status=1 ORDER BY start_date ASC";
  	return $db->fetchAll($sql);
  }
  function getAllTerm(){
  	$db = $this->getAdapter();
  	$SQL="select key_code as id , name_en as name from rms_view where type=6 and status=1 ";
  	return $db->fetchAll($SQL);
  }
  function getTestStudentId($branch=null){
  	$db = $this->getAdapter();
  	$sql ="SELECT COUNT(stu_id) AS number FROM `rms_student` WHERE customer_type =4  ";
  	if (!empty($branch)){
  		$sql.= " AND branch_id=1";
  	}
  	$sql.=" ORDER BY stu_id DESC LIMIT 1 ";
  	$acc_no = $db->fetchOne($sql);
  	 
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
  	$pre="T";
  	$branch_prefix="";
  	if (!empty($branch)){
  		$row = $this->getBranchInfo($branch);
  		if (!empty($row['prefix'])){
  			$pre.= $row['prefix'];
  		}
  	}
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
  
  function addLangLevel($data){
  	$array = array(
  				"title"		=>$data['title'],
	  			"user_id"	=>$this->getUserId(),
	  			"modify_date"=>date("Y-m-d"),
  			);
  	$this->_name="rms_degree_language";
  	return $this->insert($array);
  }
  
  function addNationType($data){
  	try{
  		$db = $this->getAdapter();
  		$key_code = $this->getLastKeycodeByType(21);
//   		$sql="SELECT id FROM rms_view WHERE status =".$data['status_na'];
//   		$sql.=" AND name_kh='".$data['name_kh']."'";
//   		$rs = $db->fetchOne($sql);
//   		if(!empty($rs)){
//   			return -1;
//   		}
  		$arr = array(
  				'name_en'	=>$data['title_en'],
  				'name_kh'	=>$data['title_kh'],
  				'status'	=>$data['status_na'],
  				'key_code'	=>$key_code,
  				'displayby'	=>1,
  				'type'=>21,
  				 
  		);
  		$this->_name="rms_view";
  		return $this->insert($arr);
  	}catch (Exception $e){
  		echo '<script>alert('."$e".');</script>';
  	}
  }
  function getLastKeycodeByType($type){
  	$sql = "SELECT key_code FROM `rms_view` WHERE type=$type ORDER BY key_code DESC LIMIT 1 ";
  	$db =$this->getAdapter();
  	$number = $db->fetchOne($sql);
  	return $number+1;
  }
  
  function addKnowBy($data){
  	$array = array(
  			"title"		=>$data['title_know_by'],
  			"user_id"	=>$this->getUserId(),
  			"create_date"=>date("Y-m-d H:i:s"),
  	);
  	$this->_name="rms_know_by";
  	return $this->insert($array);
  }
  
  function addDocstudentType($data){
  	$array = array(
  			"name"		=>$data['title_doc_type'],
  			"types"		=>1,
  			"user_id"	=>$this->getUserId(),
  			"create_date"=>date("Y-m-d H:i:s"),
  	);
  	$this->_name="rms_document_type";
  	return $this->insert($array);
  }
  
  function addDoctecherType($data){
  	$array = array(
  			"name"		=>$data['title_doc_type'],
  			"types"		=>2,
  			"user_id"	=>$this->getUserId(),
  			"create_date"=>date("Y-m-d H:i:s"),
  	);
  	$this->_name="rms_document_type";
  	return $this->insert($array);
  }
  public function getLaguage(){
  	$db = $this->getAdapter();
  	$sql="SELECT * FROM `ln_language` AS l WHERE l.`status`=1 ORDER BY l.ordering ASC";
  	return $db->fetchAll($sql);
  }
  
  //New Edition
  function getBranchInfo($branch_id){
  	$db = $this->getAdapter();
  	$sql="SELECT b.* FROM `rms_branch` AS b WHERE b.br_id = $branch_id LIMIT 1";
  	return $db->fetchRow($sql);
  }
//   function getAllSchoolOption($branch=null){
//   	$db = $this->getAdapter();
//   	$this->_name = "rms_schooloption";
//   	$sql="SELECT s.id, s.title AS name FROM $this->_name AS s WHERE s.status = 1";
//   	if($branch!==1 AND $branch !=null){
//   		$branchinfo = $this->getBranchInfo($branch);
//   		$schooloption = empty($branchinfo['schooloptionlist'])?0:$branchinfo['schooloptionlist'];
//   		$sql.=" AND s.id IN ($schooloption) ";
  	
//   	}
//   	return $db->fetchAll($sql);
//   }
  function getAllSchoolOption($branchlist=null){
  	$db = $this->getAdapter();
  	$this->_name = "rms_schooloption";
  	$sql="SELECT s.id, s.title AS name FROM $this->_name AS s WHERE s.status = 1";
  	$session_user=new Zend_Session_Namespace('authstu');
  	$branchlist = empty($branchlist)?$session_user->branch_list:$branchlist;
  	if($branchlist!=1 AND $branchlist !=null){
  		$bran = explode(",", $branchlist);
  		$s_where = array();
  		foreach ($bran as $branch){
  			$branchinfo = $this->getBranchInfo($branch);
  			$schooloption = empty($branchinfo['schooloptionlist'])?0:$branchinfo['schooloptionlist'];
  			$s_where[] = " s.id IN ($schooloption)";
  		}
  		$sql .=' AND ( '.implode(' OR ',$s_where).')';
  	}
  	$user = $this->getUserInfo();
  	$level = $user['level'];
  	if ($level!=1){
  		$sql .=' AND s.id = '.$user['schoolOption'];
  	}
  	return $db->fetchAll($sql);
  }
  function getAllDegreetype(){
  	$db = $this->getAdapter();
  	$this->_name = "rms_itemstype";
  	$sql="SELECT s.id, s.title AS name FROM $this->_name AS s WHERE s.status = 1";
  	return $db->fetchAll($sql);
  }
  
//   function getAllItems($type=null,$branch=null){
//   	$db = $this->getAdapter();
//   	$this->_name = "rms_items";
//   	$sql="SELECT m.id, m.title AS name FROM $this->_name AS m WHERE m.status=1 ";
//   	if (!empty($type)){
//   		$sql.=" AND m.type=$type";
//   	}
//   	if($branch!==1 AND $branch !=null){
//   		$branchinfo = $this->getBranchInfo($branch);
//   		$schooloption = empty($branchinfo['schooloptionlist'])?0:$branchinfo['schooloptionlist'];
//   		$ids = explode(",", $schooloption);
//   		$s_where = array();
//   		if (!empty($ids)) foreach ($ids as $i){
//   			$s_where[] = " $i IN (m.schoolOption)";
//   		}
//   		$sql .=' AND ( '.implode(' OR ',$s_where).')';
//   	}
//   	return $db->fetchAll($sql);
//   }
  function getAllItems($type=null,$branch=null,$schooloption=null){
  	$db = $this->getAdapter();
  	$this->_name = "rms_items";
  	$sql="SELECT m.id, m.title AS name FROM $this->_name AS m WHERE m.status=1 ";
  	if (!empty($type)){
  		$sql.=" AND m.type=$type";
  	}
  	$branchlist = $this->getAllSchoolOption($branch);
  	if (!empty($branchlist)){ 
  		foreach ($branchlist as $i){
  		  $s_where[] = $i['id']." IN (m.schoolOption)";
  		}
  		$sql .=' AND ( '.implode(' OR ',$s_where).')';
  	}
//  $sql .=' AND 2 IN (m.schoolOption)';
  	$user = $this->getUserInfo();
  	$level = $user['level'];
  	if ($level!=1){
  		$sql .=' AND '.$user['schoolOption'].' IN (m.schoolOption)';
  	}
  	if (!empty($schooloption)){
  		$sql.=" AND $schooloption IN (m.schoolOption) ";
  	}
  	$sql .=' ORDER BY m.schoolOption ASC,m.type DESC, m.title ASC';
  	return $db->fetchAll($sql);
  }
  
  function getItemType($type){
  	$db = $this->getAdapter();
  	$this->_name = "rms_itemstype";
  	$sql="SELECT * FROM $this->_name AS t WHERE t.id = $type LIMIT 1";
  	return $db->fetchRow($sql);
  }
  function getItemsDetailCodeByItemsType($type){
  	$db = $this->getAdapter();
  	$this->_name = "rms_itemsdetail";
  	$sql ="SELECT COUNT(id) AS number FROM $this->_name WHERE items_type =$type  LIMIT 1 ";
  	$acc_no = $db->fetchOne($sql);
  	$pre = $this->getItemType($type);
  	$pre = empty($pre['prefix'])?"":$pre['prefix'];
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
//   	$pre="";
  	for($i = $acc_no;$i<5;$i++){
  		$pre.='0';
  	}
  	return $pre.$new_acc_no;
  }
  
  function getAllGradeStudy($option=1){
  	$db = $this->getAdapter();
  	$sql="SELECT i.id,
			CONCAT(i.title,' (',(SELECT it.title FROM `rms_items` AS it WHERE it.id = i.items_id LIMIT 1),')') AS name
		FROM `rms_itemsdetail` AS i 
		WHERE i.status =1 ";
  	if($option!=null){
  		$sql.=" AND i.items_type=".$option;
  	}
  	
  	$branchlist = $this->getAllSchoolOption();
  	if (!empty($branchlist)){
  		foreach ($branchlist as $i){
  			$s_where[] = $i['id']." IN (i.schoolOption)";
  		}
  		$sql .=' AND ( '.implode(' OR ',$s_where).')';
  	}
  	$user = $this->getUserInfo();
  	$level = $user['level'];
  	if ($level!=1){
  		$sql .=' AND '.$user['schoolOption'].' IN (i.schoolOption)';
  	}
  	$sql.=" ORDER BY i.items_id ASC, i.ordering ASC";
  	return $db->fetchAll($sql);
  }
  
  function getAllGradeStudyByDegree($category_id=null,$student_id=null){
  	$db = $this->getAdapter();
//   	$sql="SELECT i.id,
//   	CONCAT(i.title,' (',(SELECT it.title FROM `rms_items` AS it WHERE it.id = i.items_id LIMIT 1),')') AS name
//   	FROM `rms_itemsdetail` AS i
//   	WHERE i.status =1 ";
  	$sql="SELECT i.id,
  		i.title AS name
  	FROM `rms_itemsdetail` AS i
  	WHERE i.status =1 ";
  	if($category_id!=null AND $category_id>0 AND $category_id!=''){
  		$sql.=" AND i.items_id=".$category_id;
  	}
  	if($student_id!=null){
  		$sql.=" AND (i.items_type !=1 OR i.id=(SELECT grade FROM `rms_student` WHERE stu_id =$student_id LIMIT 1)) ";
  	}
  	 
  	$branchlist = $this->getAllSchoolOption();
  	if (!empty($branchlist)){
  		foreach ($branchlist as $i){
  			$s_where[] = $i['id']." IN (i.schoolOption)";
  		}
  		$sql .=' AND ( '.implode(' OR ',$s_where).')';
  	}
  	$user = $this->getUserInfo();
  	$level = $user['level'];
  	if ($level!=1){
  		$sql .=' AND '.$user['schoolOption'].' IN (i.schoolOption)';
  	}
  	$sql.=" ORDER BY i.items_id ASC, i.ordering ASC";
  	//return $sql;
  	return $db->fetchAll($sql);
  }
  
  public function getAllGradeStudyOption($type=1){
  	$rows = $this->getAllGradeStudy($type);
  	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  	array_unshift($rows, array('id'=>-1,'name'=>$tr->translate("PLEASE_SELECT")));
  	$options = '';
  	if(!empty($rows))foreach($rows as $value){
  		$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
  	}
  	return $options;
  }
  function getProcessTypeView(){
  	$db = $this->getAdapter();
  	$lang = $this->currentlang();
  	$field = 'name_en';
  	if ($lang==1){
  		$field = 'name_kh';
  	}
  	$sql="SELECT key_code AS id , $field AS `name` FROM rms_view  WHERE `status`=1 AND type=12";
  	return $db->fetchAll($sql);
  }
  function getAllDiscountName(){
  	$db = $this->getAdapter();
  	$sql="SELECT disco_id AS id,dis_name FROM `rms_discount` WHERE 
  		status=1 AND dis_name!='' 
  			ORDER BY dis_name ASC ";
  	return $db->fetchAll($sql);
  }
  
  public function getAllNew($limit=null){
  	$db = $this->getAdapter();
  	$user = $this->getUserInfo();
  	$currentLang = $this->currentlang();
  	$userid = empty($user['user_id'])?0:$user['user_id'];
  	$sql="SELECT n.*,
  		(SELECT nd.title FROM `ln_news_detail` AS nd WHERE nd.news_id =n.id AND nd.lang=$currentLang LIMIT 1 ) AS title,
  		(SELECT nd.description FROM `ln_news_detail` AS nd WHERE nd.news_id =n.id AND nd.lang=$currentLang LIMIT 1 ) AS description,
		(SELECT nr.is_read FROM `ln_news__read` AS nr WHERE nr.new_feed_id = n.id AND nr.cus_id=$userid LIMIT 1) AS is_read
		 FROM `ln_news` AS n
		 WHERE n.status = 1 
		 ";
  	
  	$dbp = new Application_Model_DbTable_DbGlobal();
  	$sql.=$dbp->getAccessPermission("n.branch_id");
  	
  	$sql.=" OR n.branch_id=0 ";
  	$sql.="  ORDER BY 
		 (SELECT nr.is_read FROM `ln_news__read` AS nr WHERE nr.new_feed_id = n.id AND nr.cus_id=$userid LIMIT 1) ASC,
		 n.publish_date DESC, n.created_date DESC ";
  	
  	if (!empty($limit)){
  		if (!is_numeric($limit)){
  			$limit = 5;
  		}
  		$sql.=" LIMIT $limit";
  	}
  	return $db->fetchAll($sql);
  }
  
  public function getNewNotreadByUser(){
  	$db = $this->getAdapter();
  	$user = $this->getUserInfo();
  	$userid = empty($user['user_id'])?0:$user['user_id'];
  	$sql="SELECT COUNT(n.id) AS notread
		 FROM `ln_news` AS n
		 WHERE n.status = 1
		 AND
		 n.id NOT IN (SELECT nr.new_feed_id FROM `ln_news__read` AS nr WHERE nr.new_feed_id = n.id AND nr.cus_id=$userid )";
  	return $db->fetchOne($sql);
  }
  public function getDetailNews($id){
  	$db = $this->getAdapter();
  	$user = $this->getUserInfo();
  	$currentLang = $this->currentlang();
  	$userid = empty($user['user_id'])?0:$user['user_id'];
  	$sql="SELECT n.*,
	  	(SELECT nd.title FROM `ln_news_detail` AS nd WHERE nd.news_id =n.id AND nd.lang=$currentLang LIMIT 1 ) AS title,
	  	(SELECT nd.description FROM `ln_news_detail` AS nd WHERE nd.news_id =n.id AND nd.lang=$currentLang LIMIT 1 ) AS description,
	  	(SELECT nr.is_read FROM `ln_news__read` AS nr WHERE nr.new_feed_id = n.id AND nr.cus_id=$userid LIMIT 1) AS is_read
	  	FROM `ln_news` AS n
	  	WHERE n.status = 1 AND n.id = $id LIMIT 1";
	  return $db->fetchRow($sql);
  }
  function getPrefixByDegree($degree){
  	$db= $this->getAdapter();
  	$sql=" SELECT shortcut FROM `rms_items` WHERE id=$degree LIMIT 1";
  	return $db->fetchOne($sql);
  }
  function getnewStudentId($branch_id,$degree){//used global
	  	$db = $this->getAdapter();
	  	$option_type=1;//1 id by branch ,2 by degree
	  	
	  	$length = '';
	  	$pre = '';
	  	if($option_type==1){
	  		$sql="SELECT COUNT(stu_id) FROM `rms_student` WHERE customer_type=1 AND branch_id=".$branch_id;
	  		$stu_num = $db->fetchOne($sql);
	  		$pre = $this->getPrefixCode($branch_id);//by branch
	  	}else{
	  		$pre = $this->getPrefixByDegree($degree);
	  		$sql="SELECT id_start FROM `rms_items` WHERE id= $degree ";
	  		$stu_num = $db->fetchOne($sql);
	  	}
		  	$new_acc_no= (int)$stu_num+1;
		  	$length = strlen((int)$new_acc_no);
		  	for($i = $length;$i<4;$i++){
		  		$pre.='0';
		  	}
	  	return $pre.$new_acc_no;
  }
  function getStudentProfileblog($student_id,$data_from=1){
  	$db = $this->getAdapter();
  	if($data_from==1 OR $data_from==3){
  		$rs = $this->getStudentinfoById($student_id);
    }elseif($data_from==2){
  		$rs = $this->getStudentTestinfoById($student_id);
  	}
  	$tr = $this->tr;
  	$str = '';
//   	print_r($rs);exit();
  	$student_type=$tr->translate("Old Student");
  	$style="style='color:white'";
  	if($rs["is_stu_new"]==1){
  		$student_type=$tr->translate("New Student");
  		$style="style='color:#99e5fd'";
  	}
  	
  	 //if($rs["is_subspend"]!=0){style="background: red !important;";}
  	if(!empty($rs)){
  		$str='<div class="text-center card-box-border">
  			<div class="member-card card-display-reg">
  				<div class="img-back"></div>
  				<span class="user-badge bg-warning" '.$style.'>'.$student_type.'</span>
  		   	 		<div class="col-md-4 col-sm-4 col-xs-12">
  			                       	<div class="thumb-xl member-thumb m-b-10 center-block">';
  			                       		$photo = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/no-profile.png";
  			                       		if (!empty($rs["photo"])){
  			                       				if (file_exists(PUBLIC_PATH."/images/photo/".$rs["photo"])){
  			                       				$photo = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/photo/".$rs["photo"];
  			                       			}
  			                       		}
  			                       		
  			                           	$str.='<img src='.$photo.' class=" img-thumbnail" alt="profile-image">';
  			                            if ($rs["sex"]==1){
  			                            	$str.='<input type="hidden" id="lbl_gender" value="Male"><i class="fa fa-male member-star text-active" title="verified user"></i>';
  			                            }else{
  			                           	 	$str.='<input type="hidden" id="lbl_gender" value="Female"><i class="fa fa-female member-star text-deactive" title="verified user"></i>';
  			                            }
  			                        $str.='</div>
  			                        <div class="center">
  			                       </div>
  		                        </div>
  		                        <div class="col-md-8 col-sm-8 col-xs-12">
  			                       <p class="text-muted info-list font-13">
  			                       		<span class="title-info">'.$tr->translate("STUDENT_CODE").'</span> : <span id="lbl_stucode" class="inf-value" >'.$rs["stu_code"].'</span><br />
  			                       		<span class="title-info">'.$tr->translate("NAME_KH").'</span> : <span id="lbl_namekh" class="inf-value" >'.$rs["stu_khname"].'</span><br />
  			                       		<span class="title-info">'.$tr->translate("NAME_EN").'</span> : <span id="lbl_nameen" class="inf-value" >'.$rs["last_name"]." ".$rs["stu_enname"].'</span><br />
  			                       		<span class="title-info">'.$tr->translate("DOB").'</span> : <span id="lbl_dob" class="inf-value" >'.date("d/m/Y",strtotime($rs['dob'])).'</span><br />
  			                            <span class="title-info">'.$tr->translate("PHONE").'</span> : <span id="lbl_phone" class="inf-value">'. $rs['tel'].'</span>
  			                        	<span class="title-info">'.$tr->translate("PARENT_PHONE").'</span> : <span id="lbl_parentphone" class="inf-value">'.$rs['guardian_tel'].'</span>
  			                       		<span class="title-info">'.$tr->translate("GROUP").'</span> : <span id="lbl_group" class="inf-value" >'.$rs['group_name'].'</span><br />
  			                            <span class="title-info">'.$tr->translate("CULTURE_LEVEL").'</span> : <span id="lbl_culturelevel" class="inf-value" >'.$rs['degree_culture'].'</span><br />
  			                            <span class="title-info">'.$tr->translate("DEGREE").'</span> : <span id="lbl_degree" class="inf-value">'.$rs['degree_label'].'</span> <br />
  			                            <span class="title-info">'.$tr->translate("GRADE").'</span> : <span id="lbl_grade" class="inf-value">'.$rs['grade_label'].'</span><br />
  			                            <span class="title-info">'.$tr->translate("SESSION").'</span> : <span id="lbl_session" class="inf-value">'.$rs['session_label'].'</span><br />
  			                            <span class="title-info">'.$tr->translate("ROOM").'</span> : <span id="lbl_room" class="inf-value">'. $rs['room_label'].'</span><br />
  			         	 		  </p>
  		          </div>
  		      <div style="clear: both;"></div>
  		 </div>
  	</div>';
  	}
  	return $str;
  }
  function getCardBackground($branch,$card_type,$schoolOption=null){
	  $db = $this->getAdapter();
	  $sql="SELECT c.* FROM `rms_cardbackground` AS c WHERE c.branch_id=$branch 
	  AND c.default=1  AND c.card_type=$card_type 
	  ";
	  if (!empty($schoolOption)){
	  	$sql.=" AND c.schoolOption=$schoolOption ";
	  }
	  $sql.=" ORDER BY c.id DESC LIMIT 1";
	  return $db->fetchRow($sql);
  }
  function getPickupCardBackground($branch,$schoolOption=null){
  	$db = $this->getAdapter();
  	$sql="SELECT c.* FROM `rms_pickupcard` AS c WHERE c.branch_id=$branch
  	AND c.default=1
  	";
  	if (!empty($schoolOption)){
  	$sql.=" AND c.schoolOption=$schoolOption ";
  	}
  	$sql.=" ORDER BY c.id DESC LIMIT 1";
  	return $db->fetchRow($sql);
  }
  function getStudentGroupInfoById($id){
  	$db = $this->getAdapter();
  	$sql = "SELECT start_date,expired_date,
  	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=rms_group.academic_year )AS year,
  	(SELECT rms_items.title FROM rms_items WHERE rms_items.id=rms_group.degree AND rms_items.type=1 LIMIT 1) AS degree,
  	(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id =`rms_group`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
  	(SELECT name_en FROM `rms_view` WHERE `rms_view`.`type`=4 AND `rms_view`.`key_code`=`rms_group`.`session` LIMIT 1) AS session,
  	(SELECT room_name FROM rms_room WHERE room_id = rms_group.room_id LIMIT 1) AS room,
  	academic_year AS academic_year_id,
  	degree AS degree_id,
  	grade AS grade_id,
  	SESSION AS session_id,
  	room_id
  	FROM
  	`rms_group` WHERE id=$id LIMIT 1 ";
  	return $db->fetchRow($sql);
  }
  function getPuchaseNo($branch_id){//used global
  	$db = $this->getAdapter();
  	$branch_id = empty($branch_id)?0:$branch_id;
  
  	$db = $this->getAdapter();
  	$sql="SELECT COUNT(id) FROM rms_purchase WHERE branch_id=$branch_id ORDER BY id DESC";
  	$stu_num = $db->fetchOne($sql);
  	$pre = $this->getPrefixCode($branch_id);//by branch
  	$pre.='PO-';
  	$new_acc_no= (int)$stu_num+1;
  	$length = strlen((int)$new_acc_no);
  	for($i = $length;$i<4;$i++){
  		$pre.='0';
  	}
  	return $pre.$new_acc_no;
  }
  function ifStudentinGroupReady($student_id,$group_id){
  	$db = $this->getAdapter();
  	$sql="SELECT * FROM rms_group_detail_student WHERE stu_id=$student_id AND group_id=$group_id";
  	return $db->fetchRow($sql);
  }
  function getAllGroupByBranch($branch_id=null){
  	$db = $this->getAdapter();
  	$sql ="SELECT `g`.`id`, CONCAT(`g`.`group_code`,' ',
  	(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) ) AS name
  	FROM `rms_group` AS `g` where (g.is_pass=0 OR g.is_pass=2) and status=1 ";
  	if (!empty($branch_id)){
  		$sql.=" AND g.branch_id = $branch_id";
  	}
  	$sql.=" ORDER BY `g`.`id` DESC ";
  	return $db->fetchAll($sql);
  }
  function getNumberInkhmer($number){
  	$khmernumber = array("០","១","២","៣","៤","៥","៦","៧","៨","៩");
  	$spp = str_split($number);
  	$num="";
  	foreach ($spp as $ss){
  		 
  		if (!empty($khmernumber[$ss])){
  			$num.=$khmernumber[$ss];
  		}else{
  			$num.=$ss;
  		}
  	}
  	return $num;
  }
  function calCulateGrade($score,$max_score){
  	$score_avg = ($score / $max_score)*100;
  	if($score_avg < 50){//0.67
  		return 'F';
  	}else if($score_avg < 60){
  		return 'E';
  	}else if($score_avg < 70){
  		return 'D';
  	}else if($score_avg < 80){
  		return 'C';
  	}else if($score_avg < 90){
  		return 'B';
  	}else{
  		return 'A';
  	}
  }
  function calCulateGradeKhmerMention($score,$max_score){
  	$score_avg = ($score / $max_score)*100;
  	if($score_avg < 50){//0.67
  		return 'ខ្សោយ';
  	}else if($score_avg < 60){
  		return 'មធ្យម';
  	}else if($score_avg < 70){
  		return 'ល្អបង្គួរ';
  	}else if($score_avg < 80){
  		return 'ល្អ';
  	}else if($score_avg < 90){
  		return 'ល្អណាស់';
  	}else{
  		return 'ល្អប្រសើរ';
  	}
  }
  function calCulateGradeEnglishMention($score,$max_score){
  	$score_avg = ($score / $max_score)*100;
  	if($score_avg < 50){//0.67
  		return 'Fail';
  	}else if($score_avg < 60){
  		return 'Average';
  	}else if($score_avg < 70){
  		return 'Above Average';
  	}else if($score_avg < 80){
  		return 'Good';
  	}else if($score_avg < 90){
  		return 'Very  Good';
  	}else{
  		return 'Excellent';
  	}
  }
  function getMentionScore($score,$academic,$degree,$mentiontype=1){
  	$db = $this->getAdapter();
  	$column="sd.metion_grade";
  	if ($mentiontype==1){//grade A/B/C
  		$column="sd.metion_grade";
  	}else if ($mentiontype==2){// ល្អប្រសើរ/ល្អណាស់/ល្អ
  		$column="sd.metion_in_khmer";
  	}else if ($mentiontype==3){// Excellent/Very  Good/Good
  		$column="sd.mention_in_english";
  	}//,sd.max_score
  	$sql="SELECT $column AS mention
			FROM `rms_metionscore_setting_detail` AS sd,
			`rms_metionscore_setting` AS s
			WHERE s.id = sd.metion_score_id
			AND s.academic_year=$academic
			AND degree = $degree
			AND $score < sd.max_score
			ORDER BY sd.max_score ASC
			LIMIT 1";
  	return $db->fetchOne($sql);
  }
  function updateReadNotif($type,$notif_id){
  	$db = $this->getAdapter();
  		$_arr= array(
			'notif_type'		=>$type,
			'notification_id'	=>$notif_id,
			'user_id'	=>$this->getUserId(),
			'is_read'	=>1,
			'is_click'		=>1,
  			'date'		=>date("Y-m-d H:i:s"),
		);
  		$this->_name ='rms_read_unread_notif';
		$id = $this->insert($_arr);
	return $id;
  }
  
  function getAllYearServiceFeeByBranch($branch_id){
  	$db  = $this->getAdapter();
  	$sql = " SELECT 
  					t.id ,
  					CONCAT(t.from_academic,' - ',t.to_academic) AS name 
  				FROM 
  					`rms_tuitionfee` as t 
  				WHERE 
  					status=1 
  					and type=2
  					and branch_id = $branch_id
  				ORDER BY 
  					id DESC 
  			";
  	return $db->fetchAll($sql);
  }
  
}
?>