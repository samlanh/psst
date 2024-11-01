<?php
class Registrar_Model_DbTable_DbInitilizeservice extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
	function getAllStudentCurrentService($search = array()){
		$_db = $this->getAdapter();
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbGb->currentlang();
		
		$branch = $dbGb->getBranchDisplay();
		$titleCol = "title_en";
		$titleColGrade = "gradeTitleEn";
		$titleColDegree = "degreeTitleEn";
		if($lang==1){
			$titleCol = "title";
			$titleColGrade = "gradeTitleKh";
			$titleColDegree = "degreeTitleKh";
		}
		
		$sqlDistinctQue = "";
		$sqlDistinctCondictionQue = "";
		if(!empty($search['distinctStudent'])){
			$sqlDistinctQue = ",COUNT(gds.`gd_id`) AS amtStuService";
			$sqlDistinctCondictionQue = " GROUP BY gds.`stu_id` ";
		}
		$sql="SELECT
				gds.`gd_id` AS id
				,gds.`stu_id` AS studentId
				,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id=vs.branchId LIMIT 1) AS branchName
				,(SELECT CONCAT(aca.fromYear,'-',aca.toYear) FROM rms_academicyear AS aca WHERE aca.id=vs.`academicYear` LIMIT 1) AS academicYearTitle
				,vs.`stuCode`
				,vs.`stuNameKh`
				,vs.`stuNameEn`
				,vs.`tel`
				,vs.`sex`
				,vs.`groupCode`
				,vs.`photo`
				
				,COALESCE(vs.`degreeShortcut`,vs.$titleColDegree) AS degreeTitle
				,COALESCE(vs.`gradeShortcut`,vs.$titleColGrade) AS gradeTitle
				,vs.`academicYear`
				,gds.`itemType`
				,gds.`gd_id` AS detailId
				,gds.`grade`
				,itd.`title` AS itemTitle
				,gds.`degree`
				,it.`title` AS categoryTitle
				,gds.`stop_type`
				,gds.`startDate`
				,gds.`endDate`
				,COALESCE(gds.`feeId`,0) AS feeId
				,gds.`balance`
				,gds.`discount_type`
				,gds.`discount_amount`
				,gds.note
				,gds.`is_current`
			";
		$sql.=$sqlDistinctQue;
		$sql.="
			FROM (`rms_group_detail_student` AS gds  JOIN `v_stu_study_info` AS vs  ON vs.`studentId` = gds.`stu_id` AND vs.`itemType` =1)
				JOIN `rms_itemsdetail` AS itd ON itd.`id` = gds.`grade`  AND itd.`is_onepayment` = 0
				LEFT JOIN `rms_items` AS it ON it.`id` = gds.`degree`
		";
		$sql.="WHERE 1 
			AND gds.`itemType` !=1 
			AND gds.`stop_type` = 0
			AND gds.`is_current` = 1 
			AND gds.`endDate` !='0000-00-00'
		";
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			
			$s_where[]= " REPLACE(vs.`stuCode`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`stuNameKh`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`stuNameEn`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`stuCode`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`degreeTitleKh`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`degreeTitleEn`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`gradeTitleEn`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`gradeTitleKh`,' ','') LIKE '%{$s_search}%'";
			
			$sql.=' AND ('.implode(' OR ', $s_where).')';
		}
		if(!empty($search['academic_year'])){
    		$sql.= " AND vs.`academicYear` = ".$_db->quote($search['academic_year']);
    	}
		if(!empty($search['studentId'])){
    		$sql.= " AND gds.`stu_id` = ".$_db->quote($search['studentId']);
    	}
		if(!empty($search['branch_id'])){
    		$sql.= " AND vs.`branchId` = ".$_db->quote($search['branch_id']);
    	}
		if(!empty($search['groupId'])){
    		$sql.= " AND vs.`groupId` = ".$_db->quote($search['groupId']);
    	}
		if(!empty($search['category'])){
    		$sql.= " AND gds.`degree` = ".$_db->quote($search['category']);
    	}
		if(!empty($search['item'])){
    		$sql.= " AND gds.`grade` = ".$_db->quote($search['item']);
    	}
		if(!empty($search['degree'])){
    		$sql.= " AND vs.`degree` = ".$_db->quote($search['degree']);
    	}
		if(!empty($search['gradeId'])){
    		$sql.= " AND vs.`grade` = ".$_db->quote($search['gradeId']);
    	}
		$sql.= $dbGb->getAccessPermission('vs.`branchId`');
		$sql.= $dbGb->getDegreePermission('COALESCE(vs.`degree`,0)');
		$sql.= $sqlDistinctCondictionQue;
		$orderby = " ORDER BY vs.`degreeOrdering` ASC, vs.`gradeOrdering` ASC, vs.`groupCode` ASC ";
		
		return $_db->fetchAll($sql.$orderby);
	}
	
	function getCurrentStudentServicePro($data = array()){
		$_db = $this->getAdapter();
		$studentId = empty($data["studentId"]) ? 0 : $data["studentId"];
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbGb->currentlang();
		$titleCol = "title_en";
		if($lang==1){
			$titleCol = "title";
		}
		$sql="
			SELECT 
				gds.`stu_id` AS studentId
				,gds.`gd_id` AS detailId
				,gds.`itemType`
				,gds.`grade`
				,itd.$titleCol AS itemTitle
				,gds.`degree`
				,it.$titleCol AS categoryTitle
				,gds.`stop_type` AS stopType
				,gds.`startDate`
				,gds.`endDate`
				,COALESCE(gds.`feeId`,0) AS feeId
				,gds.`balance`
				,COALESCE(gds.`discount_type`,0) AS discountType
				,COALESCE(gds.`discount_amount`,0) AS discountAmount
				,gds.note
				,gds.`is_current` AS isCurrent
				,gds.`isoldBalance` AS isOldBalance
			FROM `rms_group_detail_student` AS gds
				LEFT JOIN `rms_itemsdetail` AS itd ON itd.`id` = gds.`grade`
				LEFT JOIN `rms_items` AS it ON it.`id` = gds.`degree`
			WHERE gds.`itemType` !=1 
			AND gds.`stop_type` = 0
			AND gds.`is_current` = 1
			AND gds.`stu_id` = $studentId
		";
		$orderby = " ORDER BY gds.`degree` ASC, gds.`grade` ASC ";
		return $_db->fetchAll($sql.$orderby);
	}
	
	function getStudentInfo($data = array()){
		
		$thisCurrentService = $this->getCurrentStudentServicePro($data);
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$restulReturn = array();
		$studentId = empty($data["studentId"]) ? 0 : $data["studentId"];
		
		$rs = $dbGb->getStudentinfoGlobalById($studentId);
		$contentHtml='';
		$contentHtmlStudyInfo='';
		if(!empty($rs)){
			$returnContent = $this->getContentStudentInfo($rs);
			$contentHtml = $returnContent["contentHtml"];
			$contentHtmlStudyInfo = $returnContent["contentHtmlStudyInfo"];
		}
		
		$param = array(
			'studentId'=>$studentId,
			'discountForType'=>2,
			'optionList'=>1,
			'fetchAll'=>1
		);
		$disOption = $dbGb->getDiscountListbyStudent($param);
		
		$restulReturn["contentHtml"] = $contentHtml;
		$restulReturn["contentHtmlStudyInfo"] = $contentHtmlStudyInfo;
		$restulReturn["studentData"] = $rs;
		$restulReturn["disOption"] = $disOption;
		$restulReturn["currentService"] = $thisCurrentService;
		return $restulReturn;
	}

	function addInitilizeService($data){
		$_db = $this->getAdapter();
		try{
			
				$mainFeeId =  $data['study_year'];
				$this->_name='rms_group_detail_student';
				$ids = explode(',', $data['identity']);
				$db = new Application_Model_DbTable_DbGlobal();
				if(!empty($ids))foreach ($ids as $i){
					$rowFeeId = $data['rowFeeId'.$i];
					
					if(!empty($data['detailId'.$i])){
						$_arrStuItemInfo=array(
							'feeId'			=> $mainFeeId,
							'startDate'		=> $data['date_start_'.$i],
							'endDate'		=> $data['end_date_'.$i],
							'balance'		=> $data['balance_'.$i],
							'isoldBalance'	=> ( $data['balance_'.$i]>0 ) ? 1 : 0,
							'note'			=> $data['remark'.$i],
							'modify_date'	=> date("Y-m-d H:i:s"),
							'user_id'		=> $this->getUserId(),
							'entryFrom'	=>5,
						);
						if($mainFeeId != $rowFeeId){
							$_arrStuItemInfo["oldFeeId"] = $rowFeeId;
						}
						$this->_name ='rms_group_detail_student';
						$whereStuItemInfo = "gd_id = ".$data['detailId'.$i];
						$this->update($_arrStuItemInfo, $whereStuItemInfo);
					}else{
						
						$param = array(
							'Id'=>$data['itemId_'.$i]
						);
						$resultRow = $db->getItemDetailRow($param);
						if($resultRow['items_type']!=1){
							$arrF = array(
								'studentId' => $data['studentId'],
								'degree'	=> $resultRow['items_id'],
								'grade' 	=> $data['itemId_'.$i],
								'isCurrent' => 1,
							);
							$dbReg = new Registrar_Model_DbTable_DbRegister();
							$stuItemInfo = $dbReg->getStudentGroupDetailInfoByItems($arrF);
							if(!empty($stuItemInfo)){
								if($stuItemInfo["stop_type"]!=0){ // update is_current of stoped service
									$_arrStuItemInfo=array(
										'is_current'=>0,
									);
									$this->_name ='rms_group_detail_student';
									$whereStuItemInfo = "gd_id = ".$stuItemInfo["gd_id"];
									$this->update($_arrStuItemInfo, $whereStuItemInfo);
								}
							}
						}
						
						$_arr= array(
								'branch_id'		=> $data['branch_id'],
								'studentId'		=> $data['studentId'],
								'itemType'		=> $resultRow['items_type'],
								'grade'			=> $data['itemId_'.$i],
								'degree'		=> $resultRow['items_id'],
								'feeId'			=> $mainFeeId,
								'discountType'	=> 0,
								'discountAmount'=> 0,
								'startDate'		=> $data['date_start_'.$i],
								'endDate'		=> $data['end_date_'.$i],
								'balance'		=> $data['balance_'.$i],
								'schoolOption'	=> $resultRow['schoolOption'],
								'isMaingrade'	=> 1,
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
					}
						
				}
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAILE");
		}
	}

	function getContentStudentInfo($data){
		
		$contentHtml='';
		$contentHtmlStudyInfo='';
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$blankImage = Zend_Controller_Front::getInstance()->getBaseUrl() . "/images/no-profile.png";
		$blankPerson = Zend_Controller_Front::getInstance()->getBaseUrl() . "/images/user.png";
		$rs = $data;
		if(!empty($rs)){
			$photo = $blankImage;
			if (!empty($rs["photo"])) {
				if (file_exists(PUBLIC_PATH . "/images/photo/" . $rs["photo"])) {
					$photo = Zend_Controller_Front::getInstance()->getBaseUrl() . "/images/photo/" . $rs["photo"];
				}
			}
			
			$fatherPhoto = $blankPerson;
			if (!empty($rs["fatherPhoto"])) {
				if (file_exists(PUBLIC_PATH . "/images/photo/" . $rs["fatherPhoto"])) {
					$fatherPhoto = Zend_Controller_Front::getInstance()->getBaseUrl() . "/images/photo/" . $rs["fatherPhoto"];
				}
			}
			
			$motherPhoto = $blankPerson;
			if (!empty($rs["motherPhoto"])) {
				if (file_exists(PUBLIC_PATH . "/images/photo/" . $rs["motherPhoto"])) {
					$motherPhoto = Zend_Controller_Front::getInstance()->getBaseUrl() . "/images/photo/" . $rs["motherPhoto"];
				}
			}
			
			$guardianPhoto = $blankPerson;
			if (!empty($rs["guardianPhoto"])) {
				if (file_exists(PUBLIC_PATH . "/images/photo/" . $rs["guardianPhoto"])) {
					$guardianPhoto = Zend_Controller_Front::getInstance()->getBaseUrl() . "/images/photo/" . $rs["guardianPhoto"];
				}
			}
			
			$rs["father_khname"] = empty($rs["father_khname"]) ? "N/A" : $rs["father_khname"];	
			$rs["father_enname"] = empty($rs["father_enname"]) ? "N/A" : $rs["father_enname"];	
			$rs["father_nation"] = empty($rs["father_nation"]) ? "N/A" : $rs["father_nation"];	
			$rs["father_phone"] = empty($rs["father_phone"]) ? "N/A" : $rs["father_phone"];	
				
			$rs["mother_khname"] = empty($rs["mother_khname"]) ? "N/A" : $rs["mother_khname"];	
			$rs["mother_enname"] = empty($rs["mother_enname"]) ? "N/A" : $rs["mother_enname"];	
			$rs["mother_nation"] = empty($rs["mother_nation"]) ? "N/A" : $rs["mother_nation"];	
			$rs["mother_phone"] = empty($rs["mother_phone"]) ? "N/A" : $rs["mother_phone"];	
			
			$rs["guardian_khname"] = empty($rs["guardian_khname"]) ? "N/A" : $rs["guardian_khname"];	
			$rs["guardian_enname"] = empty($rs["guardian_enname"]) ? "N/A" : $rs["guardian_enname"];	
			$rs["guardian_nation"] = empty($rs["guardian_nation"]) ? "N/A" : $rs["guardian_nation"];	
			$rs["guardian_tel"] = empty($rs["guardian_tel"]) ? "N/A" : $rs["guardian_tel"];	
			$contentHtmlStudyInfo.='
					
					<div class="row">
						<div class="col-md-4 col-sm-4 col-xs-12">
							<div class="customer-avatar-section">
							  <div class="d-flex align-items-center flex-column px-2">
								<img class="img-fluid fatherPhoto border rounded mb-2" src="'.$photo.'" height="130px" width="92px" alt="User avatar">
								<div class="customer-info text-center mb-2">
								</div>
							  </div>
							</div>
						</div>
						<div class="col-md-8 col-sm-8 col-xs-12">
							<div class="info-container">
								<h4 class="text-primary mb-0 m-0">'.$rs["stu_code"].'</h4>
								<h4 class="text-primary mb-0 m-0">'.$rs["stu_khname"].'</h4>
								<h6 class="mb-0">'.$rs["last_name"].' '.$rs["stu_enname"].'</h6>
								<ul class="list-unstyled mb-2 recordMoreInfo">
									<li class="mb-0">
									  <small class=" me-1">
									  '.$tr->translate('GRADE').' :
									  </small>
									  <small class="stu-info">'.$rs["grade_label"].'</small>
									</li>
									<li class="mb-0">
									  <small class=" me-1">
									  '.$tr->translate('GROUP').' :
									  </small>
									  <small class="stu-info"><span class="text-primary">'.$rs["group_name"].'</span> '.$rs["parttime_label"].'</small>
									</li>
								</ul>
								<div class="border-top mt-10 mb-10 "></div>
								<ul class="list-unstyled mb-2 recordMoreInfo">
									<li class="mb-0">
									  <small class=" me-1">
									  '.$tr->translate('DOB').' :
									  </small>
									  <small class="stu-info">'.$rs["dob"].'</small>
									</li>
									<li class="mb-0">
									  <small class=" me-1">
									  '.$tr->translate('PHONE').' :
									  </small>
									  <small class="stu-info">'.$rs["tel"].'</small>
									</li>
									<li class="mb-0">
									  <small class=" me-1">
									   '.$tr->translate('Year Enrolled').' :
									  </small>
									  <small class="stu-info">'.$rs["academicYearEnroll"].'</small>
									</li>
									<li class="mb-0">
									  <small class=" me-1">
									   '.$tr->translate('STUDENT_TYPE').' :
									  </small>
									  <small class="stu-info">'.$rs["studentType"].'</small>
									</li>
								</ul>
							</div>
						</div>
					</div>
					
			';
			$contentHtml.='
			<div class="card">
				<div class="card card-form mb-1 border-bottom">
					<div class="card-body p-0">
						'.$contentHtmlStudyInfo.'
					</div>
				</div>
				<div class="card card-form mb-0 border-bottom">
					<div class="card-header ">
						<div class="row g-3">
							<div class="col-md-8 col-sm-8 col-xs-12">
								<h6 class="card-title m-0 me-2 pt-1 mb-0 d-flex align-items-center"><i class="ti ti-user ms-n1 me-2"></i> '.$tr->translate("FATHER_INFO").'</h6>
							</div>
							<div class="col-md-4 col-sm-4 col-xs-12 actionButon">
								
							</div>
						</div>
					</div>
					<div class="card-body p-0">
						<div class="row">
							<div class="col-md-4 col-sm-4 col-xs-12">
								<div class="customer-avatar-section">
								  <div class="d-flex align-items-center flex-column px-2">
									<img class="img-fluid fatherPhoto border rounded mb-2" src="'.$fatherPhoto.'" height="120" width="120" alt="User avatar">
									<div class="customer-info text-center mb-2">
									</div>
								  </div>
								</div>
							</div>
							<div class="col-md-8 col-sm-8 col-xs-12">
								<div class="info-container">
									<h6 class="fatherNameKh text-primary mb-0">'.$rs["father_khname"].'</h6>
									<h6 class="fatherNameEng mb-0">'.$rs["father_enname"].'</h6>
									<div class="border-bottom mb-2 mt-2"></div>
									<ul class="list-unstyled mb-2 recordMoreInfo">
										<li class="mb-0">
										  <small class=" me-1">
										  '.$tr->translate('NATIONALITY').' :
										  </small>
										  <small class="fatherNation">'.$rs["father_nation"].'</small>
										</li>
										<li class="mb-0">
										  <small class=" me-1">
										  '.$tr->translate('PHONE').' :
										  </small>
										  <small class="fatherPhone">'.$rs["father_phone"].'</small>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card card-form mb-0 border-bottom">
					<div class="card-header d-flex justify-content-between">
						<div class="row g-3">
							<div class="col-md-12 col-sm-12 col-xs-12">
								<h6 class="card-title m-0 me-2 pt-1 mb-0 d-flex align-items-center"><i class="ti ti-users ms-n1 me-2"></i> '.$tr->translate("MOTHER_INFO").'</h6>
							</div>
						</div>
					</div>
					<div class="card-body p-0">
						<div class="row">
							<div class="col-md-4 col-sm-4 col-xs-12">
								<div class="customer-avatar-section">
								  <div class="d-flex align-items-center flex-column px-2">
									<img class="img-fluid motherPhoto border rounded mb-2" src="'.$motherPhoto.'" height="120" width="120" alt="User avatar">
									<div class="customer-info text-center mb-2">
									</div>
								  </div>
								</div>
							</div>
							<div class="col-md-8 col-sm-8 col-xs-12">
								<div class="info-container">
									<h6 class="motherNameKh text-primary mb-0">'.$rs["mother_khname"].'</h6>
									<h6 class="motherNameEng mb-0">'.$rs["mother_enname"].'</h6>
									<div class="border-bottom mb-2 mt-2"></div>
									<ul class="list-unstyled mb-2 recordMoreInfo">
										<li class="mb-0">
										  <small class=" me-1">
										  '.$tr->translate('NATIONALITY').' :
										  </small>
										  <small class="motherNation">'.$rs["mother_nation"].'</small>
										</li>
										<li class="mb-0">
										  <small class=" me-1">
										  '.$tr->translate('PHONE').' :
										  </small>
										  <small class="motherPhone">'.$rs["mother_phone"].'</small>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="card card-form mb-0 border-bottom">
					<div class="card-header d-flex justify-content-between">
						<div class="row g-3">
							<div class="col-md-12 col-sm-12 col-xs-12">
								<h6 class="card-title m-0 me-2 pt-1 mb-0 d-flex align-items-center"><i class="ti ti-users ms-n1 me-2"></i> '.$tr->translate("GUARDIAN_INFOMATION").'</h6>
							</div>
						</div>
					</div>
					<div class="card-body p-0">
						<div class="row">
							<div class="col-md-4 col-sm-4 col-xs-12">
								<div class="customer-avatar-section">
								  <div class="d-flex align-items-center flex-column px-2">
									<img class="img-fluid guardianPhoto border rounded mb-2" src="'.$guardianPhoto.'" height="120" width="120" alt="User avatar">
									<div class="customer-info text-center mb-2">
									</div>
								  </div>
								</div>
							</div>
							<div class="col-md-8 col-sm-8 col-xs-12">
								<div class="info-container">
									<h6 class="guardianNameKh text-primary mb-0">'.$rs["guardian_khname"].'</h6>
									<h6 class="guardianNameEng mb-0">'.$rs["guardian_enname"].'</h6>
									<div class="border-bottom mb-2 mt-2"></div>
									<ul class="list-unstyled mb-2 recordMoreInfo">
										<li class="mb-0">
										  <small class=" me-1">
										  '.$tr->translate('NATIONALITY').' :
										  </small>
										  <small class="guardianNation">'.$rs["guardian_nation"].'</small>
										</li>
										<li class="mb-0">
										  <small class=" me-1">
										  '.$tr->translate('PHONE').' :
										  </small>
										  <small class="guardianPhone">'.$rs["guardian_tel"].'</small>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			';
		}
		
		$arr = array(
			"contentHtmlStudyInfo" => $contentHtmlStudyInfo
			,"contentHtml" => $contentHtml
		);
		return $arr;
	}
	
	
	function getSearchingResult($data=array()){
		$rs_rows = $this->getAllStudentCurrentService($data);
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$paginator = Zend_Paginator::factory($rs_rows);
		$paginator->setDefaultItemCountPerPage(35);
		$allItems = $paginator->getTotalItemCount();
		$countPages= $paginator->count();
		$p = empty($data["pages"]) ? 1 : $data["pages"];
		$paginator->setCurrentPageNumber($p);
		$currentPage = $paginator->getCurrentPageNumber();
		
		if($currentPage == $countPages)
		{
			$nextPage = $countPages;
			$previousPage = $currentPage-1;
		}
		else if($currentPage == 1)
		{
			$nextPage = $currentPage+1;
			$previousPage = 1;
		}
		else {
			$nextPage = $currentPage+1;
			$previousPage = $currentPage-1;
		}
		
		$row  = $paginator;
		$htmlContent='';
		if(!empty($rs_rows)){
			$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
			$blankImage = $baseUrl. "/images/no-profile.png";
			$blankImageFemale = $baseUrl. "/images/no-profile-female.png";
			$htmlContent.='<div class="row g-6">';
			foreach($row as $rs){
				$photo = $blankImage;
				if ($rs['sex'] == 2) {
					$photo = $blankImageFemale;
				}
				if (!empty($rs["photo"])) {
					if (file_exists(PUBLIC_PATH . "/images/photo/" . $rs["photo"])) {
						$photo =  $baseUrl. "/images/photo/" . $rs["photo"];
					}
				}
	
				$htmlContent.='
					<div class="col-xl-4 col-lg-4 col-md-6">
							<div class="card card-info">
								<div class="card-body p-0 pt-1 px-2">
									<div class="d-flex justify-content-between align-items-center mb-1">
										<h6 class="fw-normal mb-0 text-body '."branch-".$rs["studentId"].'">'.$rs["branchName"].'</h6>
										<span class="rounded bg-label-primary  p-2">
											<i class="fa fa-briefcase ti-26px me-1"></i>
											<span class="'."stu-aca-".$rs["studentId"].'">'.$rs["academicYearTitle"].'</span>
										</span>
									</div>
									<div class="row">
										<div class="col-md-4 col-sm-4 col-xs-12 mt-0">
											<div class="customer-avatar-section">
											  <div class="d-flex align-items-center flex-column px-2">
												<img class="img-fluid border rounded mb-2" src="'.$photo.'" height="130px" width="92px" alt="User avatar">
												<div class="customer-info text-center mb-2">
												</div>
											  </div>
											</div>
										</div>
										<div class="col-md-8 col-sm-8 col-xs-12 mt-0">
											<div class="info-container">
												<h4 class="text-primary m-0 mb-5 '."stu-code-".$rs["studentId"].'">'.$rs["stuCode"].'</h4>
												<h4 class="text-primary m-0 mb-5 '."stu-namekh-".$rs["studentId"].'">'.$rs["stuNameKh"].'</h4>
												<h6 class="mb-0 '."stu-nameen-".$rs["studentId"].'">'.$rs["stuNameEn"].'</h6>
												
												<div class="border-top mt-10 mb-10 "></div>
												<ul class="list-unstyled mb-2 recordMoreInfo">
													<li class="mb-0">
													  <small class=" me-1">
														'.$tr->translate("DEGREE").' :
													  </small>
													  <small class="stu-info">'.$rs["degreeTitle"].'</small>
													</li>
													<li class="mb-0">
													  <small class=" me-1">
														'.$tr->translate("GROUP_CODE").' :
													  </small>
													  <small class="stu-info"><span class="text-primary">'.$rs["groupCode"].'</span></small>
													</li>
													<li class="mb-0">
														<small class=" me-1">
															'.$tr->translate("AMT_SERVICE").' :
														</small>
														<small class="stu-info">
															<strong class="text-primary fw-bold me-1">'.sprintf('%02d',$rs["amtStuService"]).'</strong>
															<a class="btn btn-default btn-xs" title="'.$tr->translate("ADJSUTMENT").'" href="javascript:void(0);" onclick="addTab('."'".$tr->translate("SET_INITILIZE_USE")."'".','."'".$baseUrl.'/registrar/initilizeuse/add?id='.$rs["studentId"]."&inFrame=true'".');"><i class="fa fa-pencil-square-o text-heading"></i></a>
															<a class="btn btn-default btn-xs" title="'.$tr->translate("VIEW").'" href="javascript:void(0);" onclick="getContentInfo(1,'."'".$rs["studentId"]."'".');"><i class="fa fa-eye text-heading"></i></a>
														</small>
													</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
				';
			}
			$htmlContent.='</div>';
			
			if ($countPages > 1) {
				$statpage = $currentPage - 5;
				$endpage = $currentPage + 5;
				if ($currentPage <= 5) {
					$statpage = 1;
					$endpage = 10;
				}
				if (!empty($countPages)) {
					$htmlContent.='<div class="row g-6">
										<div class="pagin text-center my-2">
											<nav aria-label="Page navigation">
												<ul class="pagination justify-content-center">';
					
												if ($currentPage != 1) {
													$htmlContent.='<li class="page-item"><a class="page-link" href="javascript:void(0);" onClick="getSearching('.$previousPage.');" >'.$tr->translate('Previous').'</a></li>';
												}
												for ($i = 1; $i <= $countPages; $i++) {
													$active="";
													if ($i == $currentPage) {
															$active= "active";
														}
													$htmlContent.='<li class="page-item '.$active.'"><a class="page-link" href="javascript:void(0);" onClick="getSearching('.$i.');" >'.$i.'</a></li>';
												}
												
												if ($countPages != $currentPage) {
													$htmlContent.='<li class="page-item"><a class="page-link" href="javascript:void(0);" onClick="getSearching('.$nextPage.');" >'.$tr->translate('Next').'</a></li>';
												}
												
					$htmlContent.='
												</ul>
											</nav>
											<span>'.$tr->translate('Total Pages') . " : " . $countPages . " " . $tr->translate('Pages').'</span>
										</div>
									</div>';
				}
			}
		}else{
			
			$htmlContent.='<div class="alert alert-secondary alert-dismissible" role="alert">
			  <h5 class="alert-heading text-center mb-2">'.$tr->translate("EMPTY_RECORD").'</h5>
			  <p class="mb-0 text-center">'.$tr->translate("EMPTY_RECORD_DESC").'</p>
			</div>';
		}
		
		
		
		$arrReturn = array(
			'htmlContent'=>$htmlContent,
		);
		return 	$arrReturn;
	}
	
	function getStuserviceContent($search){
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$type =empty($search["type"]) ? 1 : $search["type"];
		$htmlContent='';
		$i=0;
		if($type==1){
			$search["studentId"] = empty($search["recordId"]) ? -1 : $search["recordId"];
			$stService= $this->getAllStudentCurrentService($search);
			if(!empty($stService)) {
				$htmlContent.='
					<div class="card-datatable " style="position: relative;  width: 100%; ">
						<table id="exportExcelList" class="table " border="1">
							<thead>
								<tr>
									<th class="text-center" >'.$tr->translate('NUM').'</th>
									<th class="text-center" >'.$tr->translate('SERVICE').'</th>
									<th class="text-center" >'.$tr->translate('VALIDATE').'</th>
									<th class="text-center" >
										<div class="d-flex align-items-center justify-content-center">
											<span class="rounded bg-label-danger  p-1 px-3">
												<i class="fa fa-usd me-1"></i>
												'.$tr->translate('BALANCE').'
											</span>
										</div>
									</th>
									<th class="text-center" >'.$tr->translate('OTHER').'</th>
								</tr>
							</thead>
							<tbody>
				';
				foreach($stService as $key => $attRow){ 
					$i++;
					$htmlContent.='
							<tr>
								<td class="items-no text-center">'.$i.'</td>
								<td class="">
									<div>
										<span class="d-block text-primary text-truncate fw-medium mb-0 text-wrap" >'.$attRow["itemTitle"].'</span>
										<small class="d-block text-muted text-truncate mb-0 text-wrap" >'.$attRow["categoryTitle"].'</small>
									</div>
								</td>
								
								<td class="text-center" >
									<div class="d-flex align-items-center justify-content-center">
										<span class="rounded bg-label-primary  p-1 px-2">													  
											<span class="d-block">
												'.date("d-M-y",strtotime($attRow["startDate"])).' - '.date("d-M-y",strtotime($attRow["endDate"])).'														  
											</span>
										</span>
									</div>
								</td>
								<td class="text-center" >
									<div class="d-flex align-items-center justify-content-center">
										<span class="rounded bg-label-danger  p-1">
											<i class="fa fa-usd me-1"></i>
											'.number_format($attRow["balance"],2).'
										</span>
									</div>
								</td>
								<td class="text-center" >
									<small class=" fs-tiny text-wrap" >'.$attRow["note"].'</small>
								</td>
							';
				}
				$htmlContent.='
							</tbody>
						</table>
					</div>
				
				';
			}
		}
		
		$arrReturn = array(
			'content'=>$htmlContent
		);
		return $arrReturn;
	}
}