<?php
/*
 * Author: 	KRY CHANTO
 * Date	 : 	15-July-2011
 */
class Application_Form_Frmtable
{        
    /*
     * Multi cells in one row list
     */    	
   
    /* @ Desc: show add button
     * @param $url_new
     * */
   
	
    /*
     * Recomment usage
     * @Desc: get full list(legend, table list, check for delete, edit, pagebrowser)
     * @param $delete if $delete = 1 have check box for delete, = 0 not checkbox
     * @param $columns for list culumn which select from table
     * @param $rows data which retrieve from table
     * @param $link field with its link for access to its detail info EX: array('name'=>$link): name is field, link where u want to access
     * @param $editLink for link edit form 
     */
    
    
   
    
    public function getCheckList($delete=0, $columns,$rows,$link=null,$additionalOption=array())
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	/*
     	* Define string of pagination Sophen 27 June 2012
     	*/
    	$stringPagination = '';
    	/* end define string*/
    	
    	$head='<form name="list">
    				<div class="dataTables_scrollBody" style="position: relative; width: 100%; background:#fff;   ">
    					<table border="1" id="datatable-responsive" style="  border-collapse: collapse; border-color: #ddd;"  class="display nowrap dataTable dtr-inline collapsed" cellspacing="0" width="100%" >';
    	$col_str='';
    	$col_str .='<thead><tr>';
    	if($delete== 1 || $delete== 2 ) {
    		$col_str .= '<th class="tdheader tdcheck"></td>';
    	}
    	$col_str .= '<th class="tdheader">'.$tr->translate("NUM").'</th>';
    	//add columns
    	foreach($columns as $column){
    		$col_str=$col_str.'<th class="tdheader"  style="text-align: center;">'.$tr->translate($column).'</th>';
    	}
		
		$actionLink= empty($additionalOption["actionLink"]) ? array() : $additionalOption["actionLink"];
    	if(!empty($actionLink)) {
    		$col_str .='<th class="tdheader tdedit">'.$tr->translate('ACTION').'</th>';
    	}
    	$col_str.='</tr></thead>';
    	$row_str='<tbody>';
    	//add element rows	
    	if($rows==NULL) return $head.$col_str.'</table></div><center style="font-size:18pt;">'.$tr->translate('EMPTY_RECORD').'</center></form>';
    	$temp=0;
    	/*------------------------Check param id----------------------------------*/

    	/*------------------------End check---------------------------------------*/
    	
		$rowRecordInfo= empty($additionalOption["rowRecordInfo"]) ? array() : $additionalOption["rowRecordInfo"];
		$r=0;
    	foreach($rows as $row){
    		if($r%2==0)$attb='normal';
    		else $attb='alternate';
    		$r++;
			//-------------------check select-----------------

    		//-------------------end check select-----------------
    		$row_str.='<tr class="'.$attb.'"> ';
    				$i=0;
    				$columnTitleIndex=0;
					
		  			foreach($row as $key => $read) {
		  				$clisc='';
		  				if($read==null) $read='&nbsp';
		  				if($i==0) {
		  					$temp=$read;
		  					if($delete== 10) {
		  						$clisc='oncontextmenu="setrowdata('.$temp.');return false;" class="context-menu-one" ';
		  					}
		  					if($delete==2){
				    			$row_str .= '<td><input type="radio" onclick="setValue('.$temp.')" name="copy" id="copy" value="'.$temp.'" /></td>';
		  					}else if($delete==1){
		  						$row_str .= '<td><input type="checkbox" name="del[]" id="del[]" value="'.$temp.'" /></td>';
		  					}
							$stringAdditional='';
							if(!empty($rowRecordInfo)){
								$returnAdditionalVal = $this->checkAddtionalColumn($row,$rowRecordInfo);
								$stringAdditional='data-additional-info="' . htmlspecialchars(Zend_Json::encode($returnAdditionalVal)) . '"';
							}
		  					$row_str.='<td id='.$temp.' class="items-no text-center" '.$stringAdditional.' >'.$r.'</td>';
		  				} else {
    						if($link!=null){
    							foreach($link as $column=>$url)
    								if($key==$column){
    									$img='';
    									$array=array('tag'=>'a','attribute'=>array('href'=>Application_Form_FrmMessage::redirectorview($url).'/id/'.$temp));
    									$read=$this->formSubElement($array,$img.$read);
    								}
    						}
    						$text='';
    						if($delete== 10) {
								$classCenter="";
								if($key=="statusRecord" || $key=="processingRecord"){
									$classCenter="text-center";
								}
    							$clisc='oncontextmenu="setrowdata('.$temp.');return false;" class="context-menu-one '.$classCenter.'" ';
    						}
    						if($i!=1){
	    						$text=$this->textAlign($read);
	    						$read=$this->checkValue($read);
    						}
    						$columnSubTitle="subTitleRecord";
    						$processingBg="processingBg";
							if($key=="titleRecord"){
								$row_str.='<td>';
									$row_str.=$read;
									$subTitle = empty($row[$columnSubTitle]) ? "" : $row[$columnSubTitle];
									$row_str.='<small class="subtitle-row text-secondary">'.$subTitle.'</small>';
								$row_str.='</td>';
								$columnTitleIndex = $i;
							}else if($key=="processingRecord"){
								$processingBg = empty($row[$processingBg]) ? "" : $row[$processingBg];
								$arr = array(
									"processTitle" =>$read
									,"bgProcess" =>$processingBg
								);
								$read=$this->processingRecord($arr);
								$row_str.='<td '.$clisc.' >'.$read.'</td>';
							}else if($key=="statusRecord"){
								$read=$this->checkStatusRecord($read);
								$row_str.='<td '.$clisc.' >'.$read.'</td>';
							}else{
								if($i > count($columns)) {
									break;
								}
								if($key!=$columnSubTitle AND $key!=$processingBg ){
									$row_str.='<td '.$clisc.' >'.$read.'</td>';
								}
							}
			  				if($i > count($columns)) {
	    						if(!empty($actionLink)) {
									$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
									$row_str.='<td '.$clisc.' style=" position:relative; ">';
										$row_str.='<ul class="nav navbar-right table-action-col " style=" float: none !important; ">
												<li class="dropdown ">
													<a href="href="javascript:void(0);"" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true"><i class="fa fa-ellipsis-v"></i></a>
													<ul class="dropdown-menu" role="menu">';
														foreach($actionLink as $rowAction){
															$recordKey = empty($rowAction["recordConnect"]) ? 0 : $rowAction["recordConnect"];
															$recordId = empty($row[$recordKey]) ? 0 : $row[$recordKey];
															if(!empty($recordId)){
																$row_str.='<li><a href="'.$baseUrl.$rowAction["link"].'/id/'.$recordId.'" >'.$tr->translate(strtoupper($rowAction["title"])).'</a></li>';	
															}
																										
														}
												$row_str.='</ul>
												</li>
											</ul>';
									$row_str.='</td>';
			    				}
	    					}
    					}
    					$i++;
		  			}
 			$row_str.='</tr>';
    	}
    	$counter='<span class="row_num">'.$tr->translate('NUM-RECORD').count($rows).'</span>';
    	$row_str.='</tbody>';
    	$footer='</table></div></form>';
    	
    	return $head.$col_str.$row_str.$footer;
    }
    
    
    public function formElement($array)
    {
    	$stat='';		
		foreach($array as $tag=>$name){
			if($tag=='tag'){
				$stat.='<'.$name.' ';
				$closetag='</'.$name.'>';
			}
			else 
				foreach($name as $att=>$value)
					$stat.=$att.'="'.$value.'" ';
		}
		$stat.=">".$closetag;
		return $stat;
    }        
    public function formSubElement($array,$element='')
    {
    	$stat='';		
		foreach($array as $tag=>$name){
			if($tag=='tag'){
				$stat.='<'.$name.' ';
				$closetag='</'.$name.'>';
			}
			else 
				foreach($name as $att=>$value)
					$stat.=$att.'="'.$value.'" ';
		}
		$stat.=">".$element.$closetag;
		return $stat;
    }
    public function checkValue($value){
    	if($this->is_date($value)) return date_format(date_create($value), 'd-M-Y');  	
    	return $value;
    }
	private function textAlign($value){		
		$temp=str_replace(',','', $value);
    	if($this->is_date($temp) || strtolower($temp) == "yes" || strtolower($temp) == "no" ) return  'style="text-align:center"';
		else{
    		$temp=explode('-', $value);
    		if(count($temp)>2){
    			if(is_numeric($temp[0]) && is_numeric($temp[2])){
    				if(!is_numeric($temp[1]) && strlen($temp[1])==3) return 'style="text-align:center"'; 
    			}
    		}
    		$pos = strpos($value, "class=\"colorcase");
    		if($pos){
    			return 'style="text-align:center"';
    		}
    	}   		
    	return '';
    }
    public function is_date($str)
    {
    	try{
	       $temp=explode('-', $str);
	       if(is_array($temp) && count($temp)>=3){
				if(is_numeric($temp[0]) && is_numeric($temp[1]) && is_numeric(substr($temp[2],0,2))){
						 				      	
		       		$d=substr($temp[2],0,2);
		       		
		       		$m=$temp[1];
		       		$y=$temp[0];		       		
		       		if(checkdate($m, $d, $y)) return true;
				}
	       }       
	       return false;
    	}catch(Zend_Exception $e){
    		return false;	
    	}    	
    }
	
	public function checkAddtionalColumn($row,$rowRecordInfo){
		if(!empty($rowRecordInfo)) foreach($rowRecordInfo as $key =>$rs){
			$rowRecordInfo[$key] = empty($row[$key]) ? 0 : $row[$key];
		}
		return $rowRecordInfo;
	}
	public function checkStatusRecord($value){
    	$string = '<span class="badge badge-center rounded-pill bg-danger"><i class="fa fa-times"></i></span>';  	
    	if($value==1){
			$string = '<span class="badge badge-center rounded-pill bg-success"><i class="fa fa-check"></i></span>';  	
		}
		return $string;
    }
	public function processingRecord($optionData=array()){
		$bgLable = empty($optionData["bgProcess"]) ? "bg-label-success" : $optionData["bgProcess"];
		$processTitle = empty($optionData["processTitle"]) ? "N/A" : $optionData["processTitle"];
    	$string = '<span class="badge '.$bgLable.'" text-capitalized>'.$processTitle.'</span>';  
		return $string;
    }
	
	
}

