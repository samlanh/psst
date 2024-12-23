<?php 
Class Application_Form_FrmCombineSearchGlobal extends Zend_Dojo_Form {
	function FormIncomeStatisticFilter($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$registrationDate = $frm->getStartDateSearch($search,'Registration Date');
		$studentTypeFilter = $frm->getStudentTypeStatusTypeSearch($search);
		$paymentTermFilter = $frm->getPaymentTermSearch($search);
		$paymentTermFilter->removeMultiOption(1);
		$paymentTermFilter->removeMultiOption(5);
		$startDateFilter = $frm->getPaymentDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$studentstatusFilter = $frm->getStudyTypeSearch($search);
		$activeFilter = $frm->getActiveTypeSearch($search);
		$paymentstatusFilter = $frm->getPaymentStatusSearch($search);
		$termListFilter = $frm->getTermListSearch($search);

		$this->addElements(array(
			$endDateFilter,
			$activeFilter,
			$textSearch,
			$yearFilter,
			$branchFilter,
			$studentstatusFilter,
			$degreeFilter,
			$registrationDate,
			$studentTypeFilter,
			$paymentTermFilter,
			$startDateFilter,
			$paymentstatusFilter,
			$termListFilter
		));
		return $this;
	}

	function FormSearchStudentInfo($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$studentStudystatus = $frm->getStudentStudyStatus($search);
		$mainGradeFilter = $frm->getMainGradeTypeSearch($search);
		$sessionFilter = $frm->getSessionSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$statusFilter = $frm->getStatusSearch($search);
		

		$this->addElements(array(
			$textSearch,
			$yearFilter,
			$branchFilter,
			$degreeFilter,
			$studentStudystatus,
			$mainGradeFilter,
			$sessionFilter,
			$startDateFilter,
			$endDateFilter,
			$statusFilter,
			
		));
		return $this;
	}
	function FormSearchCrm($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$askForFilter = $frm->getAskForSearch($search);
		$khnowByFilter = $frm->getKnowBySearch($search);
		$followUpStatusFilter = $frm->getFollowStatusSearch($search);
		$crmStatusFilter = $frm->getCrmStatusSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$askForFilter,
			$khnowByFilter ,
			$followUpStatusFilter,
			$crmStatusFilter ,
			$startDateFilter,
			$endDateFilter,
		));
		return $this;
	}
	function FormSearchTest($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$testTypeFilter = $frm->getTestTypeSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$degreeFilter,
			$testTypeFilter,
			$startDateFilter,
			$endDateFilter,
		));
		return $this;
	}
	function FormSearchGroup($search=null)// group, student change Group, Student Stop, Student Return
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$statusFilter = $frm->getStatusSearch($search);
		$teacherFilter = $frm->getTeacherSearch($search);
		$isPassFilter = $frm->getIsPassSearch($search);
		$dropTypeFilter = $frm->getStudentDropTypeSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$yearFilter,
			$degreeFilter,
			$startDateFilter,
			$endDateFilter,
			$statusFilter,
			$teacherFilter,
			$isPassFilter,
			$dropTypeFilter
		));
		return $this;
	}

	function FormSearchTeacher($search=null)// group, student change Group, Student Stop, Student Return
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$staffTypeFilter = $frm->staffTypeSearch($search);
		$nationFilter = $frm->getNationSearch($search);
		$teacherTypeFilter = $frm->getTeacherTypeSearch($search);
		$activeTypeFilter = $frm->getActiveTypeSearch($search);
		$departmentFilter = $frm->getDepartSearch($search);
		$statusFilter = $frm->getStatusSearch($search);
		
		$this->addElements(array(
			$textSearch,
			$branchFilter ,
			$degreeFilter,
			$nationFilter,
			$staffTypeFilter,
			$teacherTypeFilter,
			$activeTypeFilter,
			$departmentFilter ,
			$statusFilter,
		
		));
		return $this;
	}
	
	function FormNealyPaymentFilter($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$getServiceTypeSearch = $frm->getServiceTypeSearch($search);
		$nearlyPaymetySort = $frm->getNearlyPaymetySortSearch($search);
		$periodDay = $frm->getPeriodDaySearch($search);
		$nearlyFilterType = $frm->getNearlyPaymetyFilterType($search);
		
		$paymentTermFilter = $frm->getPaymentTermSearch($search);
		$paymentTermFilter->removeMultiOption(1);
		$paymentTermFilter->removeMultiOption(5);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$yearFilter,
			$degreeFilter,
			$endDateFilter,
			$getServiceTypeSearch,
			$nearlyPaymetySort,
			$periodDay,
			$nearlyFilterType,
			$paymentTermFilter,
		));
		return $this;
	}
	
	function FormSearchCalendar($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$calendarTypeFilter = $frm->getCalendarTypeSearch($search);
		$statusFilter = $frm->getStatusSearch($search);

		$this->addElements(array(
			$textSearch,
			$startDateFilter,
			$endDateFilter,
			$calendarTypeFilter,
			$statusFilter,
		));
		return $this;
	}
	function FormSearchTeacherDasboard($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$examTypeFilter = $frm->getExamTypeSearch($search);
		$forSemesterFilter = $frm->getForSemesterSearch($search);
		$forMonthFilter = $frm->getForMonthSearch($search);
		$criterialFilter = $frm->getCriteriaIDSearch($search);
		$teacherFilter = $frm->getTeacherSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$IssueScoreStatusFilter = $frm->getIssueScoreStatusSearch($search);
		$CombineStatusFilter = $frm->getCombineStatusSearch($search);
		$EvaluationStatusFilter = $frm->getEvaluationStatusSearch($search);
		
		$this->addElements(array(
			$branchFilter,
			$yearFilter,
			$degreeFilter,
			$examTypeFilter,
			$forSemesterFilter,
			$forMonthFilter,
			$criterialFilter,
			$teacherFilter,
			$startDateFilter,
			$endDateFilter,
			$IssueScoreStatusFilter,
			$CombineStatusFilter ,
			$EvaluationStatusFilter ,
		));
		return $this;
	}

	function FormSearchSubjectStatistic($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$examTypeFilter = $frm->getExamTypeSearch($search);
		$forSemesterFilter = $frm->getForSemesterSearch($search);
		$forMonthFilter = $frm->getForMonthSearch($search);
		$teacherFilter = $frm->getTeacherSearch($search);
		$departmentFilter = $frm->getDepartSearch($search);
		$degreeSortFilter = $frm->getSortDegreeSearch($search);
		
		$this->addElements(array(
			$branchFilter,
			$yearFilter,
			$degreeFilter,
			$examTypeFilter,
			$forSemesterFilter,
			$forMonthFilter,
			$departmentFilter,
			$teacherFilter,
			$degreeSortFilter
		));
		return $this;
	}
}