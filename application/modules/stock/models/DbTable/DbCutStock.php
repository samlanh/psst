<?php
class Stock_Model_DbTable_DbCutStock extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_saledetail';
    
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    public function getCutStockode(){
    	$db = $this->getAdapter();
    	$sql="SELECT COUNT(id) FROM rms_cutstock WHERE 1 ORDER BY id DESC";
    	$stu_num = $db->fetchOne($sql);
    	$pre='ST-';
    	$new_acc_no= (int)$stu_num+1;
    	$length = strlen((int)$new_acc_no);
    	for($i = $length;$i<4;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    public function getStudentProductPaymentDetail($data){
    	$db = $this->getAdapter();
    	
    	$student_id = $data['student_id'];
    	$branch_id = $data['branch_id'];
    	
    	$sql="SELECT 
			spd.*,
			sp.branch_id,
			sp.receipt_number,
			sp.create_date AS payment_date,
			(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sp.student_id LIMIT 1) AS student_name,
			(SELECT ie.title FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS items_name,
			(SELECT ie.items_type FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS items_type
			FROM `rms_student_payment` AS sp,
			`rms_student_paymentdetail` AS spd
			WHERE spd.payment_id = sp.id
			AND (SELECT ie.items_type FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) =3
			AND is_void=0
			AND qty_balance >0
			AND sp.student_id=$student_id
			AND sp.branch_id=$branch_id";
    	if (!empty($data['bypuchase_no'])){
    		$s_search=addslashes(trim($data['bypuchase_no']));
    		$sql.= " AND sp.receipt_number LIKE '%{$s_search}%'";
    	}
    	$rs = $db->fetchAll($sql);
    	 
    	$string='';
    	$no = $data['keyindex'];
    	$identity='';
    	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
    	if(!empty($rs)){
    		foreach ($rs as $key => $row){
    			if (empty($identity)){
    				$identity=$no;
    			}else{$identity=$identity.",".$no;
    			}
    			$string.='
    			<tr id="row'.$no.'" style="background: #fff; border: solid 1px #bac;">
	    			<td align="center" style="  padding: 0 10px;"><input  OnChange="CheckAllTotal('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
	    			<td style="text-align: center;vertical-align: middle; ">'.($key+1).'</td>
	    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;
	    			<label id="billingdatelabel'.$no.'">'.date("d-M-Y",strtotime($row['payment_date'])).'</label>
	    			<input type="hidden" dojoType="dijit.form.TextBox" name="payment_id'.$no.'" id="payment_id'.$no.'" value="'.$row['payment_id'].'" >
	    			<input type="hidden" dojoType="dijit.form.TextBox" name="paymentdetail_id'.$no.'" id="paymentdetail_id'.$no.'" value="'.$row['id'].'" >
	    			<input type="hidden" dojoType="dijit.form.TextBox" name="itemdetail_id'.$no.'" id="itemdetail_id'.$no.'" value="'.$row['itemdetail_id'].'" >
    			</td>
    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 100px;">&nbsp;
    			<label title="'.$row['items_name'].' ('.$row['receipt_number'].')" class="invoicelabel" id="invoicelabel'.$no.'">'.$row['items_name'].' ('.$row['receipt_number'].')</label>
    			<input type="hidden" dojoType="dijit.form.TextBox" name="receipt_number'.$no.'" id="receipt_number'.$no.'" value="'.$row['receipt_number'].'" >
    			</td>
    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; min-width: 70px;">&nbsp;
    			<label id="origtotallabel'.$no.'">'.number_format($row['qty'],2).'</label>
    			</td>
    			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc;  min-width: 70px; ">&nbsp;
    			<label id="duelabel'.$no.'">'.number_format($row['qty_balance'],2).'</label>
    			<input type="hidden" dojoType="dijit.form.TextBox" name="qty_balance'.$no.'" id="qty_balance'.$no.'" value="'.$row['qty_balance'].'" >
    			</td>
    			<td style="width: 70px;"><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="qty_receive'.$no.'" id="qty_receive'.$no.'" value="0" style="text-align: center;" ></td>
    			<td style="width: 70px;"><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$row['qty_balance'].'" style="text-align: center;" ></td>
    			<td >
    				<input class="fullside" type="text" dojoType="dijit.form.DateTextBox" name="remide_date'.$no.'" id="remide_date'.$no.'" value="now" >
    			</td>
    			</tr>
    			';$no++;
    		}
    	}else{
    		$no++;
    	}
    	$all_balance =0;
    	$userbalace = $this->getCurrentBalanceByStudent($data);
    	if (!empty($userbalace)){
    		$all_balance = $userbalace;
    	}
    	$array = array('stringrow'=>$string,'keyindex'=>$no,'identity'=>$identity,'all_balance'=>$all_balance);
    	return $array;
    }
    function getCurrentBalanceByStudent($data){
    	$db = $this->getAdapter();
    	
    	$student_id = $data['student_id'];
    	$branch_id = $data['branch_id'];
    	
    	$sql="SELECT 
    		SUM(spd.`qty_balance`) AS all_balance
			FROM `rms_student_payment` AS sp,
			`rms_student_paymentdetail` AS spd
			WHERE spd.payment_id = sp.id
			AND (SELECT ie.items_type FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) =3
			AND is_void=0
			AND qty_balance >0
			AND sp.student_id=$student_id
			AND sp.branch_id=$branch_id";
//     	$sql = "SELECT SUM(inv.`amount_due_after`) AS all_balance FROM `rms_purchase` AS inv 
//     	WHERE inv.`status`=1 AND inv.`is_paid`=0 AND inv.`sup_id`=".$data['supplier_id']." 
//     	AND inv.branch_id =".$data['branch_id'];
    	return $db->fetchOne($sql);
    }
    
    function addCutStock($_data){
    	try{
    		
    		$_arr=array(
    				'branch_id'	  => $_data['branch_id'],
    				'serailno'	      => $_data['serailno'],
    				'student_id'	      => $_data['student_id'],
    				'balance'      => $_data['balance'],
    				'total_received'=> $_data['total_paid'],
    				'total_qty_due'      => $_data['total_due'],
    				'received_date'      => $_data['date_payment'],
    				'create_date'=> date("Y-m-d H:i:s"),
    				'modify_date'	  => date("Y-m-d H:i:s"),
    				'status'=> 1,
    				'user_id'  =>$this->getUserId(),
    				'note'=>$_data['note'],
    		);
    		$this->_name ='rms_cutstock';
    		$cut_id =  $this->insert($_arr);
    		
    		$ids = explode(',', $_data['identity']);
    		$qtyfter=0;
    		foreach ($ids as $i){
    			
    			$stupaydetail = $this->getStudentPaymentDetailById($_data['paymentdetail_id'.$i],$_data['payment_id'.$i]);
    			$qtyreceive = $_data['qty_receive'.$i];
    			
    			if (!empty($stupaydetail)){
    				$qtyfter = $stupaydetail['qty_balance']-$qtyreceive;
    				// update Purchase Balance
    				$array=array(
    						'qty_balance'=>$qtyfter,
    				);
    				$where="id=".$_data['paymentdetail_id'.$i]." AND payment_id =".$_data['payment_id'.$i];
    				$this->_name="rms_student_paymentdetail";
    				$this->update($array, $where);
    			}
    			
    			$arrs = array(
    					'cutstock_id'=>$cut_id,
//     					'payment_id'=>$_data['payment_id'.$i],
    					'student_paymentdetail_id'=>$_data['paymentdetail_id'.$i],
    					'product_id'=>$_data['itemdetail_id'.$i],
    					'due_amount'=>$_data['qty_balance'.$i],
    					'qty_receive'=>$_data['qty_receive'.$i],
    					'remain'=>$_data['remain'.$i],
//     					'received_date'=>date("Y-m-d"),
    					'remide_date'=>$_data['remide_date'.$i],
    			);
    			$this->_name ='rms_cutstock_detail';
    			$this->insert($arrs);
    		}
    		return $cut_id;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    function getStudentPaymentDetailById($st_paydetail,$stu_payid){
    	$db=$this->getAdapter();
    	$sql="SELECT spd.* 
			FROM`rms_student_payment` AS sp,
			`rms_student_paymentdetail` AS spd
			WHERE  spd.payment_id = sp.id
			AND sp.branch_id=2
			AND spd.id = $st_paydetail
			AND spd.payment_id=$stu_payid
			LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getAllCutStock($search){
    	$db = $this->getAdapter();
    	try{
    		$sql="
    		SELECT
    		pp.id,
    		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = pp.branch_id LIMIT 1) AS branch_name,
    		pp.serailno,
    		(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = pp.student_id LIMIT 1 ) AS student_name,
    		pp.balance,
    		pp.total_received,pp.total_qty_due,
    		pp.received_date,
    		pp.status
    		FROM `rms_cutstock` AS pp WHERE 1
    		";
    		$from_date =(empty($search['start_date']))? '1': " pp.received_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " pp.received_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    		$sql.= " AND  ".$from_date." AND ".$to_date;
    		$where="";
    		if(!empty($search['adv_search'])){
    			$s_where=array();
    			$s_search=addslashes(trim($search['adv_search']));
    			$s_where[]= " pp.serailno LIKE '%{$s_search}%'";
    			$s_where[]= " pp.balance LIKE '%{$s_search}%'";
    			$s_where[]= " pp.total_received LIKE '%{$s_search}%'";
    			$s_where[]= " pp.total_qty_due LIKE '%{$s_search}%'";
    
    			$where.=' AND ('.implode(' OR ', $s_where).')';
    		}
    		if(!empty($search['student_id'])){
    			$where.=" AND pp.student_id=".$search['student_id'];
    		}
    		if(!empty($search['status_search'])){
    			$where.=" AND pp.status=".$search['status_search'];
    		}
    		if(!empty($search['branch_search'])){
    			$where.=" AND pp.branch_id=".$search['branch_search'];
    		}
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$where.=$dbp->getAccessPermission('pp.branch_id');
    		$order=" ORDER BY pp.id DESC";
    
    		return $db->fetchAll($sql.$where.$order);
    
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
}



