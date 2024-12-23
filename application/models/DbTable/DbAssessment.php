<?php

class Application_Model_DbTable_DbAssessment extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_grading';
    	
	public static function getUserExternalId(){
		$dbExternal= new Application_Model_DbTable_DbExternal();
		$userId = $dbExternal->getUserExternalId();
		$userId = empty($userId) ? 0 :$userId;
		return $userId;
	}
	
	function getAllScoreResult($search=null){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		$subjectTitle='subject_titleen';
		if ($currentLang==1){
			$colunmname='title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
			$subjectTitle='subject_titlekh';
		}
		$sql="SELECT s.*, g.*,
		s.id as scoreId,
		s.group_id as groupId,
		(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=s.branch_id LIMIT 1) as branchName,	
		(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1) as examTypeTitle,
		CASE
			WHEN s.exam_type  = 3 THEN 'Annual'
			WHEN s.exam_type  = 2 THEN s.for_semester
			ELSE (SELECT $month FROM `rms_month` WHERE id=s.for_month LIMIT 1) 
		END AS forMonthTitle,
		(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName,
		(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear,
		COALESCE((SELECT a.id FROM `rms_studentassessment` AS a WHERE s.`id`=a.scoreId AND a.forMonth=s.for_month AND a.groupId=s.group_id LIMIT 1),0) AS IsAssesment,
		(SELECT a.isLock FROM `rms_studentassessment` AS a WHERE s.`id`=a.scoreId AND a.forMonth=s.for_month AND a.groupId=s.group_id LIMIT 1) AS islisLock
		
		";
		
		$sql.=" FROM  `rms_score` AS s
		INNER JOIN  `rms_group` AS g ON s.`group_id` = g.`id` WHERE 1 AND s.status =1 ";
		
		$where ='';
		$from_date =(empty($search['start_date']))? '1': "s.date_input >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_input <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		$where.=' AND g.`teacher_id` ='.$this->getUserExternalId();

		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]=" a.title_score LIKE '%{$s_search}%'";
			$s_where[]=" a.title_score_en LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT br.branch_namekh FROM `rms_branch` AS br WHERE br.br_id=s.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT br.branch_nameen FROM `rms_branch` AS br WHERE br.br_id=s.branch_id LIMIT 1) LIKE '%{$s_search}%'";
		
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['degree']>0){
			$where.= " AND g.degree =".$search['degree'];
		}
		if(!empty($search['academic_year'])){
			$where.=" AND g.academic_year =".$search['academic_year'];
		}
		if(!empty($search['grade'])){
			$where.=" AND `g`.`grade` =".$search['grade'];
		}

		if($search['for_month']>0){
			$where.=" AND s.for_month =".$search['for_month'];
		}
		if($search['exam_type']>0){
			$where.= " AND s.exam_type =".$search['exam_type'];
		}
		if($search['for_semester']>0){
			$where.= " AND s.for_semester =".$search['for_semester'];
		}
		$order=" ORDER BY s.`id` DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getAssessmentByID($id){
	   	$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		$subjectTitle='subject_titleen';
		if ($currentLang==1){
			$colunmname='title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
			$subjectTitle='subject_titlekh';
		}
		$sql="SELECT 
				grd.*
				,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
				,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameKh
				,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameEn
				,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.forType LIMIT 1) as examTypeTitle
				,CASE
					WHEN grd.forType = 2 THEN grd.forSemester
				ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1) 
				END AS forMonthTitle
				,g.group_code AS  groupCode
				,g.is_pass AS  is_pass
				,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
				,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
				,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
				,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
				,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
		";
		
		
	   	$sql.= "
			   	FROM rms_studentassessment AS grd,
					rms_group AS g
		   		WHERE grd.groupId=g.id 
		   			AND grd.id=".$id;
		$sql.=' AND grd.teacherId='.$this->getUserExternalId();
	   	$sql.="  LIMIT 1 ";
	   	return $db->fetchRow($sql);
	}
	
	function checkingDuplicate($_data){
		$db = $this->getAdapter();
		$sql=" SELECT grd.* ";
		$sql.="FROM rms_studentassessment As grd ";
		$sql.=" WHERE grd.status =1 ";
		$sql.=" AND grd.groupId=".$_data['groupId'];
		$sql.=" AND grd.forType=".$_data['examType'];
		$sql.=" AND grd.scoreId=".$_data['scoreId'];
		if($_data['examType']==1){
			$sql.=" AND grd.forMonth=".$_data['forMonth'];
		}else{
			$sql.=" AND grd.forSemester=".$_data['forSemester'];
		}
		if(!empty($_data['currentId'])){
			$sql.=" AND grd.id !=".$_data['currentId'];
		}
		$row = $db->fetchRow($sql);
		if(!empty($row)){
			return 1;
		}
		return 0;
	}
	
	public function addStudentAssessment($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
// 			$dbExternal = new Application_Model_DbTable_DbExternal();
			$degreeId = empty($_data['degree'])?0:$_data['degree'];
			$_arr = array(
					'branchId'			=>$_data['branchId'],
					'groupId'			=>$_data['groupId'],
					'degreeId'			=>$degreeId,
				
			        'forType'			=>$_data['examType'],
					'forMonth'			=>$_data['forMonth'],
					'forSemester'		=>$_data['forSemester'],	
					'issueDate'			=>$_data['issueDate'],
					'returnDate'		=>$_data['returnDate'],
					'note'				=>$_data['note'],
					'scoreId'			=>$_data['scoreId'],
					'createDate'		=>date("Y-m-d H:i:s"),
					'modifyDate'		=>date("Y-m-d H:i:s"),
					'status'			=>1,
					'teacherId'			=>$this->getUserExternalId(),
					'inputOption'		=>2, //1 normal,2 teache input
			);
			$this->_name='rms_studentassessment';		
			$assessmentId=$this->insert($_arr);
			
			$comments = explode(',', $_data['identityComment']);
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				
				if(!empty($ids))foreach ($ids as $i){
					$studentId 		= $_data['student_id'.$i];
					$teacherComment = $_data['teacherComment'.$i];
					if(!empty($comments)) foreach($comments AS $rowCri){
						$commentId =$rowCri;// $rowCri['id'];
						$arr=array(
								'assessmentId'		=> $assessmentId,
								'studentId'			=> $studentId,
								'commentId'			=> $commentId,
								'ratingId'			=> $_data['rating_id_'.$i.'_'.$commentId],
								'teacherComment'	=> $teacherComment,
								);
							$teacherComment='';
							$this->_name='rms_studentassessment_detail';
							$this->insert($arr);
					}
				}
			}
		
		  $db->commit();
		  return $assessmentId;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
   
  
	function getAllRating(){
		$db=$this->getAdapter();
		$sql="SELECT 
					rt.id,
					rt.rating AS name
				FROM  
					rms_rating AS rt 
				
			";
		return $db->fetchAll($sql);
	}
   function getStudentForAssessment($data){
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$students = $dbExternal->getStudentByGroupExternal($data);
	 
		$degreeId = empty($data['degree'])?0:$data['degree'];
		$comments = $dbExternal->getCommentByDegree($degreeId);
		$countComment = count($comments);
		$countComment = empty($countComment)?1:$countComment;
		
		$rating = $this->getAllRating();
		
	   $tr=Application_Form_FrmLanguages::getCurrentlanguage();
	   $db=$this->getAdapter();
	   
	   $keyIndex = $data['keyIndex'];
	   
	   
	   $identity="";
	   $identityComment='';
	   
	   $arrClassCol = array(
			2=>"col-md-6 col-sm-6 col-xs-12"
			,3=>"col-md-4 col-sm-4 col-xs-12"
			,4=>"col-md-3 col-sm-3 col-xs-12"
	   );
	   $commentContentInfo="";
		$commentContentInfo='';
				$commentContentInfo.='<div class="card-info bg-gradient-directional-notice">';
					$commentContentInfo.='<div class="card-content">';
						$commentContentInfo.='<div class="card-body">';
							$commentContentInfo.='<div class="media d-flex">';
								$commentContentInfo.='<div class="media-body text-dark text-left align-self-bottom ">';
									$commentContentInfo.='<ul class="optListRow gradingInfo">';
										$commentContentInfo.='<li class="opt-items titleEx"><h4 class="text-dark mb-10">ព័ត៌មានការវាយតម្លៃសិស្ស / Student Assessment Info.</h4></li>';
	   $string='';
	   $string='';
		$string.='<table class="collape responsiveTable" id="table" >';
			$string.='<thead>';
				$string.='<tr class="head-td" align="center">';
					$string.='<th scope="col" width="10px"  >ល.រ<small class="lableEng" >N<sup>o</sup></small></th>';
					$string.='<th scope="col"  style="width:150px;">សិស្ស<small class="lableEng" >Student</small></th>';
					$string.='<th scope="col" >ភេទ<small class="lableEng" >Gender</small></td>';
					
					if(!empty($comments)) foreach($comments AS $key => $rComment){ 
						$string.='<th scope="col">ចំនុចទី '.($key+1).'<small class="lableEng" >Section '.($key+1).'</small></th>';
						$commentContentInfo.='<li class="opt-items "><strong>ចំនុចទី '.($key+1).'/Section '.($key+1).'</strong>.) '.$rComment['name'].'</li>';
					}
					
			$string.='</tr>';
		$string.='</thead>';
		
									$commentContentInfo.='</ul>';
								$commentContentInfo.='</div>';
							$commentContentInfo.='<div class="align-self-top">';
						$commentContentInfo.='<i class="fa fa-info-circle icon-opacity2 text-dark font-large-4 float-end"></i>';
					$commentContentInfo.='</div>';
				$commentContentInfo.='</div>';
			$commentContentInfo.='</div>';
		$commentContentInfo.='</div>';
	$commentContentInfo.='</div>';
		
		if(!empty($students)) foreach($students AS $key => $stu){
			$key++;
			$keyIndex=$keyIndex+1;
			
			if (empty($identity)){
				$identity=$keyIndex;
			}else{
				$identity=$identity.",".$keyIndex;
			}
			
			$rowClasss="odd";
			if(($keyIndex%2)==0){
				$rowClasss= "regurlar";
			}
			$gender = $tr->translate('MALE');
			if($stu['sex']==2){
				$gender = $tr->translate('FEMALE');
			}
					
			$string.='<tr  class="rowData '.$rowClasss.'" id="row'.$keyIndex.'">';
				$string.='<td rowspan="2" data-label="'.$tr->translate("NUM").'"  align="center">&nbsp;'.$key.'</td>';
				$string.='<td rowspan="2" data-label="'.$tr->translate("STUDENT").'"  align="left">';
					$string.='<strong class="text-dark">'.$stu['stuCode'].'</strong><br />';
					$string.='<strong class="text-dark">'.$stu['stuKhName'].'</strong><br />';
					$string.='<strong class="text-dark">'.$stu['stuEnName'].'</strong><br />';
					$string.='<input dojoType="dijit.form.TextBox" name="student_id'.$keyIndex.'" value="'.$stu['stu_id'].'" type="hidden" >';
				$string.='</td>';
				$string.='<td  rowspan="2" data-label="'.$tr->translate("SEX").'" >'.$gender.'</td>';
				$number=1;
				if(!empty($comments)) foreach($comments AS $keyComment => $rComment){ 
					$number = $number+$keyComment;
					$string.='<td  data-label="'.$rComment['name'].'">';
						$string.='<input dojoType="dijit.form.TextBox" class="fullside" name="commentId'.$keyIndex.'_'.$rComment['id'].'"  value="'.$rComment['id'].'" type="hidden" >';
						$string.='<select queryExpr="*${0}*" autoComplete="false" name="rating_id_'.$keyIndex.'_'.$rComment['id'].'" class="fullside" id="rating_id_'.$keyIndex.'_'.$rComment['id'].'" dojoType="dijit.form.FilteringSelect" >';
								if(!empty($rating)){ foreach($rating as $rate){
									$selected="";
									if($rate['id']==2){ $selected="selected='selected'";}
								$string.='<option '.$selected.' value="'.$rate['id'].'">'.$rate['id'].'-'.$rate['name'].'</option>';
								}}
							$string.='</select>';						
					$string.='</td>';
					
					if($key==1){//for first student
						if (empty($identityComment)){
							$identityComment=$number;
						}else{
							$identityComment=$identityComment.",".$number;
						}
					
					}
				}
				
				$string.='';
			$string.='</tr>';
			$string.='<tr  class="rowData '.$rowClasss.'" >';
				$string.='<td colspan="'.$countComment.'" ><div >មតិយោបល់របស់គ្រូ/Teacher Comment</div>';
				$string.='<input dojoType="dijit.form.TextBox" class="fullside" name="teacherComment'.$keyIndex.'"  value="" type="text" ></td>';
			$string.='</tr>';
			
		}
		
		$string.='';
		$string.='</table>';
		
	   $arrContent = array(
		'contentHtml'=>$string
		,'identity'=>$identity
	   	,'identityComment'=>$identityComment
		,'keyIndex'=>$keyIndex
		,'commentContentInfo'=>$commentContentInfo
	   );
	   
	   return $arrContent;
   }
   function getPreviousRating($data){
   		$sql="SELECT 
   				ratingId,
   				(SELECT rating FROM `rms_rating` r WHERE r.id=ratingId LIMIT 1) AS previousRating 
   			FROM `rms_studentassessment_detail` 
   				WHERE 1 ";
   		if(!empty($data['studentId'])){
   			$sql.=" AND studentId=".$data['studentId'];
   		}
   		if(!empty($data['commentId'])){
   			$sql.=" AND commentId=".$data['commentId'];
   		}
   		$sql.=" ORDER BY id DESC LIMIT 1 ";
   		return $this->getAdapter()->fetchRow($sql);
   }
   function getSecondFormatStudentForAssessment($data){
		   	$dbExternal = new Application_Model_DbTable_DbExternal();
		   	$students = $dbExternal->getStudentByGroupExternal($data);
		   
		   	$degreeId = empty($data['degree'])?0:$data['degree'];
		   	$comments = $dbExternal->getCommentByDegree($degreeId);
		   	$countComment = count($comments);
		   	$countComment = empty($countComment)?1:$countComment;
		   	
		   	$rating = $this->getAllRating();
		   	
		   	$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		   	$db=$this->getAdapter();
		   	
		   	$keyIndex = $data['keyIndex'];
		  
		   	
		   	$identity="";
		   	$identityComment = '';
		   	$commentContentInfo="";
		   	$commentContentInfo='';
		   	$commentContentInfo.='<div class="card-info bg-gradient-directional-notice">';
		   	$commentContentInfo.='<div class="card-content">';
		   	$commentContentInfo.='<div class="card-body">';
		   	$commentContentInfo.='<div class="media d-flex">';
		   	$commentContentInfo.='<div class="media-body text-dark text-left align-self-bottom ">';
		   	$commentContentInfo.='<ul class="optListRow gradingInfo">';
		   	$commentContentInfo.='<li class="opt-items titleEx"><h4 class="text-dark mb-10">ព័ត៌មានការវាយតម្លៃសិស្ស / Student Assessment Info.</h4></li>';
		   	$string='';
		   	$string='';
		   	$string.='<table class="collape responsiveTable" id="table" >';
		   	$string.='<thead>';
			   	$string.='<tr class="head-td" align="center">';
			   	$string.='<th scope="col" width="10px"  >ល.រ<small class="lableEng" >N<sup>o</sup></small></th>';
			   	$string.='<th scope="col"  style="width:150px;">សិស្ស<small class="lableEng" >Student</small></th>';
			   	$string.='<th scope="col" >ភេទ<small class="lableEng" >Gender</small></td>';
			   	$string.='<th scope="col" >ភេទ<small class="lableEng" >Comment</small></td>';
		   		
// 		   	if(!empty($comments)) foreach($comments AS $key => $rComment){
// 		   		$string.='<th scope="col">ចំនុចទី '.($key+1).'<small class="lableEng" >Section '.($key+1).'</small></th>';
// 		   		$commentContentInfo.='<li class="opt-items "><strong>ចំនុចទី '.($key+1).'/Section '.($key+1).'</strong>.) '.$rComment['name'].'</li>';
// 		   	}
		   		
		   	$string.='</tr>';
		   	$string.='</thead>';
		   	
		   	$commentContentInfo.='</ul>';
		   	$commentContentInfo.='</div>';
		   	$commentContentInfo.='<div class="align-self-top">';
		   	$commentContentInfo.='<i class="fa fa-info-circle icon-opacity2 text-dark font-large-4 float-end"></i>';
		   	$commentContentInfo.='</div>';
		   	$commentContentInfo.='</div>';
		   	$commentContentInfo.='</div>';
		   	$commentContentInfo.='</div>';
		   	$commentContentInfo.='</div>';
		   	$imgtick=BASE_URL.'/images/icon/tick.png';
		   
		   	if(!empty($students)) foreach($students AS $key => $stu){
		   		$key++;
		   		$keyIndex=$keyIndex+1;
		   			
		   		if (empty($identity)){
		   			$identity=$keyIndex;
		   		}else{
		   			$identity=$identity.",".$keyIndex;
		   		}
		   			
		   		$rowClasss="odd";
		   		if(($keyIndex%2)==0){
		   			$rowClasss= "regurlar";
		   		}
		   		$gender = $tr->translate('MALE');
		   		if($stu['sex']==2){
		   			$gender = $tr->translate('FEMALE');
		   		}
		   		$exspan="false";
		   		$showRow=" ";
		   		if($keyIndex==1){
		   			$exspan="true";
		   			$showRow="in";
		   		}
		   			
		   		$string.='<tr  data-toggle="collapse" data-target="#demo_'.$keyIndex.'"  aria-expanded="'.$exspan.'" class="rowData '.$rowClasss.' accordion-toggle text-primary text-center" id="row'.$keyIndex.'">';
			   		$string.='<td rowspan="2" data-label="'.$tr->translate("NUM").'"  align="center">&nbsp;'.$key.'</td>';
			   		$string.='<td rowspan="2" data-label="'.$tr->translate("STUDENT").'"  align="left">';
				   		$string.='<strong class="text-dark">'.$stu['stuCode'].'</strong><br />';
				   		$string.='<strong class="text-dark">'.$stu['stuKhName'].'</strong><br />';
				   		$string.='<strong class="text-dark">'.$stu['stuEnName'].'</strong><br />';
				   		$string.='<input dojoType="dijit.form.TextBox" name="student_id'.$keyIndex.'" value="'.$stu['stu_id'].'" type="hidden" >';
		   			$string.='</td>';
			   		$string.='<td rowspan="2" data-label="'.$tr->translate("SEX").'" >'.$gender.'';
			   		
			   		$string.='</td>';
		   			$string.='<td align="right" data-label="'.$tr->translate("COMMENT").'" >';
		   				$string.='<input type="button" class="button-class button-warning" iconClass="glyphicon glyphicon-comment" label="ចូលវាយតម្លៃសិស្ស" dojoType="dijit.form.Button"/>';
		   			$string.='</td>';
		   		$string.='</tr>';
		   		$stringOption='';
		   		if(!empty($rating)){
		   			foreach($rating as $rate){
		   				$selected="";
		   				if($rate['id']==2){
		   					$selected="selected='selected'";
		   				}
		   				$stringOption.='<option '.$selected.' value="'.$rate['id'].'">'.$rate['id'].'-'.$rate['name'].'</option>';
		   			}
		   		}
		   		$string.='<tr>';
// 			   		$string.='<td colspan="'.$countComment.'" ><div >មតិយោបល់របស់គ្រូ/Teacher Comment</div>';
		   		$string.='<td ><div  class="accordian-body collapse '.$showRow.'" id="demo_'.$keyIndex.'" aria-expanded="'.$exspan.'">';
			   		$string.='<table class="table table-striped">'; 
			   					$string.='<thead><tr class="bg-primary">';
								   		$string.='<td>'.$tr->translate('N_O').'</td>';
								   		$string.='<td>'.$tr->translate('COMMENT').'</td>';
								   		$string.='<td>'.$tr->translate('PREVIOUSE_RATING').'</td>';
								   		$string.='<td>'.$tr->translate('Action').'</td>';
								   		$string.='<td>'.$tr->translate('RATING').'</td>';
				   				$string.='</tr></thead>';
						   		$string.='<tbody  class="accordion-toggle" id="table_cmt_row_" >';
						   		$commentType='';
						   		if(!empty($comments)) foreach($comments AS $keyComment => $rComment){
						   			$param = array(
						   					'studentId'=>$stu['stu_id'],
						   					'commentId'=>$rComment['id']
						   					);
						   			$rsComment = $this->getPreviousRating($param);
						   			
						   		if($commentType!=$rComment['commentType']){
						   			$string.='<tr class="commentType">';
						   				$string.='<td></td>';
						   				$string.='<td align="center">'.$rComment['commentType'].'</td>';
						   				$string.='<td></td>';
						   				$string.='<td></td>';
						   				$string.='<td></td>';
						   			$string.='</tr>';
						   		}
						   		$commentType=$rComment['commentType'];
						   		$number = $keyComment+1;
						   		$string.='<tr>';
							   		$string.='<td align="center">'.($number).'</td>';
							   		$string.='<td>'.$rComment['name'].'</td>';
							   		$string.='<td>'.$rsComment['previousRating'].'</td>';
							   		///camappgit/psis/public/images/icon/apply2.png
							   		$string.='<td><img title="click to copy" class="pointer" src='.$imgtick.'  onclick="copyPreviousData('.$keyIndex.','.$rComment['id'].','.$rsComment['ratingId'].');" /></td>';
							   		$string.='<td><select queryExpr="*${0}*" autoComplete="false" id="rating_id_'.$keyIndex.'_'.$rComment['id'].'"  name="rating_id_'.$keyIndex.'_'.$rComment['id'].'" class="fullside"  dojoType="dijit.form.FilteringSelect" >'.$stringOption.'</select></td>';
						   		$string.='</tr>';
						   		
						   			if($key==1){//for first student
								   		if (empty($identityComment)){
								   			$identityComment=$rComment['id'];
								   		}else{
								   			$identityComment=$identityComment.",".$rComment['id'];
								   		}
						   			}
						   			
						   		}
						   		$string.='</tbody>';
			   		
			   			$string.='</table></div>';
			   			$string.='<div class="col-md-6 col-sm-6 col-xs-12"><div class="noted">'.$stu['teacherComment'].'</div></div>';
				   		$string.='<div class="col-md-6 col-sm-6 col-xs-12"><textarea class="teacherComment" dojoType="dijit.form.Textarea" class="fullside" name="teacherComment'.$keyIndex.'"  value="" ></textarea></div></td>';
			   		$string.='</td></tr>';
			   			
			   	}
			   	
			   	$string.='';
			   	$string.='</table>';
		   	$arrContent = array(
		   			'contentHtml'=>$string
		   			,'identity'=>$identity
		   			,'identityComment'=>$identityComment
		   			,'keyIndex'=>$keyIndex
		   			,'commentContentInfo'=>$commentContentInfo
		   	);
		   	return $arrContent;
   }

   function getSecondFormatStudentForAssessmentEdit($data){
	$dbExternal = new Application_Model_DbTable_DbExternal();
	$students = $dbExternal->getStudentByGroupExternal($data);
	
	$currentID = empty($data['currentID'])?0:$data['currentID'];
	$degreeId = empty($data['degree'])?0:$data['degree'];
	$comments = $dbExternal->getCommentByDegree($degreeId);
	$countComment = count($comments);
	$countComment = empty($countComment)?1:$countComment;
	
	$rating = $this->getAllRating();
	
	$tr=Application_Form_FrmLanguages::getCurrentlanguage();
	$db=$this->getAdapter();
	
	$keyIndex = $data['keyIndex'];

	
	$identity="";
	$identityComment = '';
	$commentContentInfo="";
	$commentContentInfo='';
	$commentContentInfo.='<div class="card-info bg-gradient-directional-notice">';
	$commentContentInfo.='<div class="card-content">';
	$commentContentInfo.='<div class="card-body">';
	$commentContentInfo.='<div class="media d-flex">';
	$commentContentInfo.='<div class="media-body text-dark text-left align-self-bottom ">';
	$commentContentInfo.='<ul class="optListRow gradingInfo">';
	$commentContentInfo.='<li class="opt-items titleEx"><h4 class="text-dark mb-10">ព័ត៌មានការវាយតម្លៃសិស្ស / Student Assessment Info.</h4></li>';
	$string='';
	$string='';
	$string.='<table class="collape responsiveTable" id="table" >';
	$string.='<thead>';
		$string.='<tr class="head-td" align="center">';
		$string.='<th scope="col" width="10px"  >ល.រ<small class="lableEng" >N<sup>o</sup></small></th>';
		$string.='<th scope="col"  style="width:150px;">សិស្ស<small class="lableEng" >Student</small></th>';
		$string.='<th scope="col" >ភេទ<small class="lableEng" >Gender</small></td>';
		$string.='<th scope="col" >ភេទ<small class="lableEng" >Comment</small></td>';
		
	$string.='</tr>';
	$string.='</thead>';
	
	$commentContentInfo.='</ul>';
	$commentContentInfo.='</div>';
	$commentContentInfo.='<div class="align-self-top">';
	$commentContentInfo.='<i class="fa fa-info-circle icon-opacity2 text-dark font-large-4 float-end"></i>';
	$commentContentInfo.='</div>';
	$commentContentInfo.='</div>';
	$commentContentInfo.='</div>';
	$commentContentInfo.='</div>';
	$commentContentInfo.='</div>';
	$imgtick=BASE_URL.'/images/icon/tick.png';

	if(!empty($students)) foreach($students AS $key => $stu){
		$key++;
		$keyIndex=$keyIndex+1;
			
		if (empty($identity)){
			$identity=$keyIndex;
		}else{
			$identity=$identity.",".$keyIndex;
		}
			
		$rowClasss="odd";
		if(($keyIndex%2)==0){
			$rowClasss= "regurlar";
		}
		$gender = $tr->translate('MALE');
		if($stu['sex']==2){
			$gender = $tr->translate('FEMALE');
		}
		$exspan="false";
		$showRow=" ";
		if($keyIndex==1){
			$exspan="true";
			$showRow="in";
		}
			
		$string.='<tr  data-toggle="collapse" data-target="#demo_'.$keyIndex.'"  aria-expanded="'.$exspan.'" class="rowData '.$rowClasss.' accordion-toggle text-primary text-center" id="row'.$keyIndex.'">';
			$string.='<td rowspan="2" data-label="'.$tr->translate("NUM").'"  align="center">&nbsp;'.$key.'</td>';
			$string.='<td rowspan="2" data-label="'.$tr->translate("STUDENT").'"  align="left">';
				$string.='<strong class="text-dark">'.$stu['stuCode'].'</strong><br />';
				$string.='<strong class="text-dark">'.$stu['stuKhName'].'</strong><br />';
				$string.='<strong class="text-dark">'.$stu['stuEnName'].'</strong><br />';
				$string.='<input dojoType="dijit.form.TextBox" name="student_id'.$keyIndex.'" value="'.$stu['stu_id'].'" type="hidden" >';
			$string.='</td>';
			$string.='<td rowspan="2" data-label="'.$tr->translate("SEX").'" >'.$gender.'';
			
			$string.='</td>';
			$string.='<td align="right" data-label="'.$tr->translate("COMMENT").'" >';
				$string.='<input type="button" class="button-class button-warning" iconClass="glyphicon glyphicon-comment" label="ចូលវាយតម្លៃសិស្ស" dojoType="dijit.form.Button"/>';
			$string.='</td>';
		$string.='</tr>';
	
		$string.='<tr>';

		$string.='<td ><div  class="accordian-body collapse '.$showRow.'" id="demo_'.$keyIndex.'" aria-expanded="'.$exspan.'">';
			$string.='<table class="table table-striped">'; 
						$string.='<thead><tr class="bg-primary">';
								$string.='<td>'.$tr->translate('N_O').'</td>';
								$string.='<td>'.$tr->translate('COMMENT').'</td>';
								$string.='<td>'.$tr->translate('PREVIOUSE_RATING').'</td>';
								$string.='<td>'.$tr->translate('Action').'</td>';
								$string.='<td>'.$tr->translate('RATING').'</td>';
						$string.='</tr></thead>';
						$string.='<tbody  class="accordion-toggle" id="table_cmt_row_" >';
						$commentType='';
						$teacherComment="";
						if(!empty($comments)) foreach($comments AS $keyComment => $rComment){
							$param = array(
									'studentId'=>$stu['stu_id'],
									'commentId'=>$rComment['id']
									);
							$rsComment = $this->getPreviousRating($param);
							
							$parameter = array(
								'assessmentId'=>$currentID
								,'studentId'=>$stu['stu_id']
								,'commentId'=>$rComment['id']
							);
							$rsDetail = $this->getAssessmentDetail($parameter);
							$stringOption='';
							if(!empty($rsComment)) {
							
								if($teacherComment==""){
									$teacherComment=$rsDetail['teacherComment'];
								}
								$stringOption='';
								if(!empty($rating)){
									foreach($rating as $rate){
										$selected="";
										if($rate['id']==$rsDetail['ratingId']){
											$selected="selected='selected'";
										}
										$stringOption.='<option '.$selected.' value="'.$rate['id'].'">'.$rate['id'].'-'.$rate['name'].'</option>';
									}
								}
							}else{
									
								if(!empty($rating)){
									foreach($rating as $rate){
										$selected="";
										if($rate['id']==2){
											$selected="selected='selected'";
										}
										$stringOption.='<option '.$selected.' value="'.$rate['id'].'">'.$rate['id'].'-'.$rate['name'].'</option>';
									}
								}
							}


						if($commentType!=$rComment['commentType']){
							$string.='<tr class="commentType">';
								$string.='<td></td>';
								$string.='<td align="center">'.$rComment['commentType'].'</td>';
								$string.='<td></td>';
								$string.='<td></td>';
								$string.='<td></td>';
							$string.='</tr>';
						}
						$commentType=$rComment['commentType'];
						$number = $keyComment+1;
						$string.='<tr>';
							$string.='<td align="center">'.($number).'</td>';
							$string.='<td>'.$rComment['name'].'</td>';
							$string.='<td>'.$rsComment['previousRating'].'</td>';
							///camappgit/psis/public/images/icon/apply2.png
							$string.='<td><img title="click to copy" class="pointer" src='.$imgtick.'  onclick="copyPreviousData('.$keyIndex.','.$rComment['id'].','.$rsComment['ratingId'].');" /></td>';
							$string.='<td><select queryExpr="*${0}*" autoComplete="false" id="rating_id_'.$keyIndex.'_'.$rComment['id'].'"  name="rating_id_'.$keyIndex.'_'.$rComment['id'].'" class="fullside"  dojoType="dijit.form.FilteringSelect" >'.$stringOption.'</select></td>';
						$string.='</tr>';
						
							if($key==1){//for first student
								if (empty($identityComment)){
									$identityComment=$rComment['id'];
								}else{
									$identityComment=$identityComment.",".$rComment['id'];
								}
							}
							
						}
						$string.='</tbody>';
			
				$string.='</table></div>';
				$string.='<div class="col-md-6 col-sm-6 col-xs-12"><div class="noted">'.$stu['teacherComment'].'</div></div>';
				$string.='<div class="col-md-6 col-sm-6 col-xs-12"><textarea class="teacherComment" dojoType="dijit.form.Textarea" class="fullside" name="teacherComment'.$keyIndex.'"  value="'.$teacherComment.'" ></textarea></div></td>';
			$string.='</td></tr>';
				
		}
		
		$string.='';
		$string.='</table>';
	$arrContent = array(
			'contentHtml'=>$string
			,'identity'=>$identity
			,'identityComment'=>$identityComment
			,'keyIndex'=>$keyIndex
			,'commentContentInfo'=>$commentContentInfo
	);
	return $arrContent;
}
   
   function getAssessmentDetail($data){
	  $db = $this->getAdapter();
		$assessmentId = empty($data['assessmentId'])?0:$data['assessmentId'];
		$studentId = empty($data['studentId'])?0:$data['studentId'];
		$commentId = empty($data['commentId'])?0:$data['commentId'];
	  
	  	$sql="  SELECT assDetail.*  ";
	  		$sql.="  ,(SELECT rt.rating FROM rms_rating AS rt WHERE rt.id=assDetail.ratingId LIMIT 1) AS ratingTitle ";
	  	$sql.=" FROM rms_studentassessment_detail AS assDetail ";
	  	$sql.="  WHERE assDetail.assessmentId= ".$assessmentId;
				$sql.="  AND assDetail.studentId= ".$studentId;
				$sql.="  AND assDetail.commentId= ".$commentId;
	  	$sql.="  ORDER BY assDetail.id ASC LIMIT 1 ";
	   	return $db->fetchRow($sql);
   }
   
   function getStudentForAssessmentEdit($data){
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$students = $dbExternal->getStudentByGroupExternal($data);
	 
		$currentID = empty($data['currentID'])?0:$data['currentID'];
		$degreeId = empty($data['degree'])?0:$data['degree'];
		$comments = $dbExternal->getCommentByDegree($degreeId);
		$countComment = count($comments);
		$countComment = empty($countComment)?1:$countComment;
		
		$rating = $this->getAllRating();
		
	   $tr=Application_Form_FrmLanguages::getCurrentlanguage();
	   $db=$this->getAdapter();
	   
	   $keyIndex = $data['keyIndex'];
	   
	   
	   $identity="";
	   $arrClassCol = array(
			2=>"col-md-6 col-sm-6 col-xs-12"
			,3=>"col-md-4 col-sm-4 col-xs-12"
			,4=>"col-md-3 col-sm-3 col-xs-12"
	   );
	   $commentContentInfo="";
		$commentContentInfo='';
				$commentContentInfo.='<div class="card-info bg-gradient-directional-notice">';
					$commentContentInfo.='<div class="card-content">';
						$commentContentInfo.='<div class="card-body">';
							$commentContentInfo.='<div class="media d-flex">';
								$commentContentInfo.='<div class="media-body text-dark text-left align-self-bottom ">';
									$commentContentInfo.='<ul class="optListRow gradingInfo">';
										$commentContentInfo.='<li class="opt-items titleEx"><h4 class="text-dark mb-10">ព័ត៌មានការវាយតម្លៃសិស្ស / Student Assessment Info.</h4></li>';
	   $string='';
		$string.='<table class="collape responsiveTable" id="table" >';
			$string.='<thead>';
				$string.='<tr class="head-td" align="center">';
					$string.='<th scope="col" width="10px"  >ល.រ<small class="lableEng" >N<sup>o</sup></small></th>';
					$string.='<th scope="col"  style="width:150px;">សិស្ស<small class="lableEng" >Student</small></th>';
					$string.='<th scope="col" >ភេទ<small class="lableEng" >Gender</small></td>';
					
					if(!empty($comments)) foreach($comments AS $key => $rComment){ 
						$string.='<th scope="col">ចំនុចទី '.($key+1).'<small class="lableEng" >Section '.($key+1).'</small></th>';
						$commentContentInfo.='<li class="opt-items "><strong>ចំនុចទី '.($key+1).'/Section '.($key+1).'</strong>.) '.$rComment['name'].'</li>';
					}
					
			$string.='</tr>';
		$string.='</thead>';
		
									$commentContentInfo.='</ul>';
								$commentContentInfo.='</div>';
							$commentContentInfo.='<div class="align-self-top">';
						$commentContentInfo.='<i class="fa fa-info-circle icon-opacity2 text-dark font-large-4 float-end"></i>';
					$commentContentInfo.='</div>';
				$commentContentInfo.='</div>';
			$commentContentInfo.='</div>';
		$commentContentInfo.='</div>';
	$commentContentInfo.='</div>';
		
		if(!empty($students)) foreach($students AS $key => $stu){
			$key++;
			$keyIndex=$keyIndex+1;
			
			if (empty($identity)){
				$identity=$keyIndex;
			}else{
				$identity=$identity.",".$keyIndex;
			}
			
			$rowClasss="odd";
			if(($keyIndex%2)==0){
				$rowClasss= "regurlar";
			}
			$gender = $tr->translate('MALE');
			if($stu['sex']==2){
				$gender = $tr->translate('FEMALE');
			}
					
			$string.='<tr  class="rowData '.$rowClasss.'" id="row'.$keyIndex.'">';
				$string.='<td rowspan="2" data-label="'.$tr->translate("NUM").'"  align="center">&nbsp;'.$key.'</td>';
				$string.='<td rowspan="2" data-label="'.$tr->translate("STUDENT").'"  align="left">';
					$string.='<strong class="text-dark">'.$stu['stuCode'].'</strong><br />';
					$string.='<strong class="text-dark">'.$stu['stuKhName'].'</strong><br />';
					$string.='<strong class="text-dark">'.$stu['stuEnName'].'</strong><br />';
					$string.='<input dojoType="dijit.form.TextBox" name="student_id'.$keyIndex.'" value="'.$stu['stu_id'].'" type="hidden" >';
				$string.='</td>';
				$string.='<td  rowspan="2" data-label="'.$tr->translate("SEX").'" >'.$gender.'</td>';
				
				$teacherComment="";
				if(!empty($comments)) foreach($comments AS $key => $rComment){ 
				
					$parameter = array(
							'assessmentId'=>$currentID
							,'studentId'=>$stu['stu_id']
							,'commentId'=>$rComment['id']
						);
					$rowDetail = $this->getAssessmentDetail($parameter);
						
						
					if(!empty($rowDetail)){
						if($teacherComment==""){
							$teacherComment=$rowDetail['teacherComment'];
						}
						$string.='<td  data-label="'.$rComment['name'].'">';
							$string.='<select queryExpr="*${0}*" autoComplete="false" name="rating_id_'.$keyIndex.'_'.$rComment['id'].'" class="fullside" id="rating_id_'.$keyIndex.'_'.$rComment['id'].'" dojoType="dijit.form.FilteringSelect" >';
									if(!empty($rating)){ foreach($rating as $rate){
										$selected="";
										if($rate['id']==$rowDetail['ratingId']){ $selected="selected='selected'";}
									$string.='<option '.$selected.' value="'.$rate['id'].'">'.$rate['id'].'-'.$rate['name'].'</option>';
									}}
								$string.='</select>';						
						$string.='</td>';
						

					}else{
						$string.='<td  data-label="'.$rComment['name'].'">';
							$string.='<select queryExpr="*${0}*" autoComplete="false" name="rating_id_'.$keyIndex.'_'.$rComment['id'].'" class="fullside" id="rating_id_'.$keyIndex.'_'.$rComment['id'].'" dojoType="dijit.form.FilteringSelect" >';
									if(!empty($rating)){ foreach($rating as $rate){
										$selected="";
										if($rate['id']==2){ $selected="selected='selected'";}
									$string.='<option '.$selected.' value="'.$rate['id'].'">'.$rate['id'].'-'.$rate['name'].'</option>';
									}}
								$string.='</select>';						
						$string.='</td>';
					}
				}
				
				$string.='';
			$string.='</tr>';
			$string.='<tr  class="rowData '.$rowClasss.'" >';
				$string.='<td colspan="'.$countComment.'" ><div >មតិយោបល់របស់គ្រូ/Teacher Comment</div>';
				$string.='<input dojoType="dijit.form.TextBox" class="fullside" name="teacherComment'.$keyIndex.'"  value="'.$teacherComment.'" type="text" ></td>';
			$string.='</tr>';
			
		}
		
		
		
		$string.='';
		$string.='</table>';
		
		
				
	   
	   $arrContent = array(
		'contentHtml'=>$string
		,'identity'=>$identity
		,'keyIndex'=>$keyIndex
		,'commentContentInfo'=>$commentContentInfo
	   );
	   
	   return $arrContent;
   }
   
   
   public function updateAssessmentByClass($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{		
			
			
			$status = empty($_data['status'])?0:1;
			$degreeId = empty($_data['degree'])?0:$_data['degree'];
			$_arr = array(
					'branchId'			=>$_data['branchId'],
					'groupId'			=>$_data['groupId'],
					'degreeId'			=>$degreeId,
				
			        'forType'			=>$_data['examType'],
					'forMonth'			=>$_data['forMonth'],
					'forSemester'		=>$_data['forSemester'],	
					'issueDate'			=>$_data['issueDate'],
					'returnDate'		=>$_data['returnDate'],
					'note'				=>$_data['note'],
					'scoreId'			=>$_data['scoreId'],
					
					'modifyDate'		=>date("Y-m-d H:i:s"),
					'status'			=>$status,
					'teacherId'			=>$this->getUserExternalId(),
					'inputOption'		=>2, //1 normal,2 teache input
			);
			$this->_name='rms_studentassessment';	

			$assessmentId=$_data['id'];
			$where="id=".$assessmentId;
			$this->update($_arr, $where);			
		
			if(!empty($_data['status'])){
				
				$this->_name='rms_studentassessment_detail';
				$this->delete("assessmentId=".$assessmentId);

				$comments = explode(',', $_data['identityComment']);
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					
					if(!empty($ids))foreach ($ids as $i){
						$studentId 		= $_data['student_id'.$i];
						$teacherComment = $_data['teacherComment'.$i];
						if(!empty($comments)) foreach($comments AS $rowCri){
							$commentId =$rowCri;// $rowCri['id'];
							$arr=array(
									'assessmentId'		=> $assessmentId,
									'studentId'			=> $studentId,
									'commentId'			=> $commentId,
									'ratingId'			=> $_data['rating_id_'.$i.'_'.$commentId],
									'teacherComment'	=> $teacherComment,
									);
								$teacherComment='';
								$this->_name='rms_studentassessment_detail';
								$this->insert($arr);
						}
					}
				}
				
				// $comments = $dbExternal->getCommentByDegree($degreeId);
				// if(!empty($_data['identity'])){
				// 	$ids = explode(',', $_data['identity']);
					
				// 	if(!empty($ids))foreach ($ids as $i){
				// 		$studentId 		= $_data['student_id'.$i];
				// 		$teacherComment = $_data['teacherComment'.$i];
				// 		if(!empty($comments)) foreach($comments AS $rowCri){
				// 			$commentId = $rowCri['id'];
				// 			$arr=array(
				// 					'assessmentId'		=> $assessmentId,
				// 					'studentId'			=> $studentId,
				// 					'commentId'			=> $commentId,
				// 					'ratingId'			=> $_data['rating_id_'.$i.'_'.$commentId],
				// 					'teacherComment'	=> $teacherComment,
				// 					);
							
				// 				$this->_name='rms_studentassessment_detail';
				// 				$this->insert($arr);
				// 				$teacherComment='';
				// 		}
				// 	}
				// }
			}

		  $db->commit();
		  return $assessmentId;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
   
   
}