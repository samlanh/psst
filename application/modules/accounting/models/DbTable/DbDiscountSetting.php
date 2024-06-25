<?php
class Accounting_Model_DbTable_DbDiscountSetting extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_dis_setting';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllDiscountset($search)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();

		$currentLang = $dbp->currentlang();
		$colunmname = 'name_en';
		$strDegree = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'name_kh';
			$strDegree = 'title';
		}

		$strStudent = "(SELECT CONCAT(COALESCE(s.stu_code,''),' ',COALESCE(s.stu_khname,''),'-',COALESCE(s.stu_enname,'')) FROM rms_student AS s WHERE s.stu_id=ds.studentId LIMIT 1) ";

		$sqlPeriod = "(SELECT name_en FROM `rms_view` WHERE type=39 AND key_code=ds.discountPeriod LIMIT 1) ";
		$sqlDiscountFor = "(SELECT $colunmname FROM `rms_view` WHERE TYPE=37 AND key_code=ds.discountFor LIMIT 1)";

		$sql = "SELECT ds.id, 
					(SELECT branch_nameen FROM `rms_branch` WHERE br_id=ds.branchId LIMIT 1) AS branch,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academicYear LIMIT 1) as academicYear,
					discountTitle,
					(CASE 
						WHEN ds.discountFor=2 THEN $sqlDiscountFor
						WHEN ds.discountFor=1 THEN $strStudent
					END) AS discountForText,
					(SELECT $colunmname FROM `rms_view` WHERE TYPE=38 AND key_code=ds.discountFor LIMIT 1) AS discountForOption,
					(SELECT GROUP_CONCAT($strDegree) FROM `rms_items` WHERE FIND_IN_SET(id,degree)) as degreeList,
					(SELECT dis_name AS NAME FROM `rms_discount` WHERE disco_id=ds.discountId LIMIT 1) AS discName,
					CONCAT(ds.discountValue, 
					(CASE WHEN DisValueType=1 THEN '%' WHEN DisValueType=2 THEN '$' END )) AS DisValueType,	
					CONCAT(COALESCE($sqlPeriod),' ',COALESCE(DATE_FORMAT(ds.startDate,'%d-%m-%Y')),'/',COALESCE(DATE_FORMAT(ds.endDate,'%d-%m-%Y'))) AS discountPeriod, 
					(SELECT first_name FROM rms_users WHERE id=ds.userId LIMIT 1 ) AS user_name,
					ds.createDate";

		$sql .= $dbp->caseStatusShowImage("ds.status");
		$sql .= " FROM rms_dis_setting AS ds ";

		$order = " ORDER BY id DESC ";
		$where = " WHERE 1";

		if (!empty($search['title'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " discountTitle LIKE '%{$s_search}%'";
			$s_where[] = " discountValue LIKE '%{$s_search}%'";
			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if (($search['academic_year']) > 0) {
			$where .= ' AND ds.academicYear=' . $search['academic_year'];
		}
		if (!empty($search['branch_id'])) {
			$where .= ' AND ds.branchId=' . $search['branch_id'];
		}
		if (!empty($search['studentId'])) {
			$where .= ' AND ds.studentId=' . $search['studentId'];
		}
		if (!empty($search['discountId'])) {
			$where .= ' AND ds.discountId=' . $search['discountId'];
		}
		if (!empty($search['discountFor'])) {
			$where .= ' AND ds.discountFor=' . $search['discountFor'];
		}
		if (!empty($search['discountPeriod'])) {
			$where .= ' AND ds.discountPeriod=' . $search['discountPeriod'];
		}
		if ($search['status_search'] > -1) {
			$where .= ' AND status=' . $search['status_search'];
		}
		$where .= $dbp->getAccessPermission('ds.branchId');
		return $db->fetchAll($sql . $where . $order);
	}
	public function addNewDiscountset($_data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$degree = "";
			if (!empty($_data['selector']))
				foreach ($_data['selector'] as $rs) {
					if (empty($dept)) {
						$degree = $rs;
					} else {
						$degree = $dept . "," . $rs;
					}
				}
			$_data['degree'] = $degree;

			$_arr = array(
				'branchId' => $_data['branch_id'],
				'academicYear' => $_data['academic_year'],
				'discountTitle' => $_data['discountTitle'],
				'discountFor' => $_data['discountFor'],
				'studentId' => $_data['studentId'],
				'discountForType' => $_data['discountforType'],
				'degree' => $_data['degree'],
				'discountId' => $_data['discount_id'],
				'DisValueType' => $_data['DisValueType'],
				'discountValue' => $_data['discountValue'],
				'discountPeriod' => $_data['discountPeriod'],
				'startDate' => $_data['start_date'],
				'endDate' => $_data['end_date'],
				'createDate' => Zend_Date::now(),
				'modifyDate' => Zend_Date::now(),
				'status' => 1,
				'userId' => $this->getUserId()
			);
			$this->insert($_arr);
			$db->commit();
		} catch (Exception $e) {
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
		}
	}
	public function addNewDiscountPopup($_data)
	{
		$_arr = array(
			'dis_name' => $_data['dis_name'],
			'create_date' => Zend_Date::now(),
			'status' => $_data['status_j'],
			'user_id' => $this->getUserId()
		);
		$this->_name = "rms_discount";
		return $this->insert($_arr);
	}
	public function getDiscountsetById($id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_dis_setting WHERE id=" . $db->quote($id);
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getAccessPermission('branch_id');
		$sql .= " LIMIT 1 ";
		$row = $db->fetchRow($sql);
		return $row;
	}
	public function updateDiscountset($_data)
	{
		$degree = "";
		if (!empty($_data['selector']))
			foreach ($_data['selector'] as $rs) {
				if (empty($dept)) {
					$degree = $rs;
				} else {
					$degree = $dept . "," . $rs;
				}
			}
		$_data['degree'] = $degree;

		$_arr = array(
			'branchId' => $_data['branch_id'],
			'academicYear' => $_data['academic_year'],
			'discountTitle' => $_data['discountTitle'],
			'discountFor' => $_data['discountFor'],
			'studentId' => $_data['studentId'],
			'discountForType' => $_data['discountforType'],
			'degree' => $_data['degree'],
			'discountId' => $_data['discount_id'],
			'DisValueType' => $_data['DisValueType'],
			'discountValue' => $_data['discountValue'],
			'discountPeriod' => $_data['discountPeriod'],
			'startDate' => $_data['start_date'],
			'endDate' => $_data['end_date'],
			'createDate' => Zend_Date::now(),
			'modifyDate' => Zend_Date::now(),
			'status' => $_data['status'],
			'userId' => $this->getUserId()
		);
		$where = $this->getAdapter()->quoteInto("id=?", $_data["id"]);
		$this->update($_arr, $where);
	}

	public function addDiscounttionset($_data)
	{//ajax
		$_arr = array(
			'dis_name' => $_data['dis_name'],
			'create_date' => Zend_Date::now(),
			'status' => 1,
			'user_id' => $this->getUserId()
		);
		return $this->insert($_arr);
	}
}