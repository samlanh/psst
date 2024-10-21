<?php 
Class Application_Form_FrmCombineSearchGlobal extends Zend_Dojo_Form {
	function FormIncomeStatisticFilter($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$statusFilter = $frm->getStatusSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$registrationDate = $frm->getStartDateSearch($search,'Registration Date');
		$studentTypeFilter = $frm->getStudentTypeStatusTypeSearch($search);
		$paymentTermFilter = $frm->getPaymentTermSearch($search);
		$startDateFilter = $frm->getPaymentDateSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$statusFilter,
			$degreeFilter,
			$registrationDate,
			$studentTypeFilter,
			$paymentTermFilter,
			$startDateFilter
		));
		return $this;
	}
}