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
    
    
   
    
    public function getCheckList($delete=0, $columns,$rows,$link=null,$editLink="", $class='items', $textalign= "left", $report=false, $id = "table")
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
    	if($editLink != "") {
    		$col_str .='<th class="tdheader tdedit">'.$tr->translate('EDIT_CAP').'</th>';
    	}
    	$col_str.='</tr></thead>';
    	$row_str='<tbody>';
    	//add element rows	
    	if($rows==NULL) return $head.$col_str.'</table></div><center style="font-size:18pt;">No record</center></form>';
    	$temp=0;
    	/*------------------------Check param id----------------------------------*/

    	/*------------------------End check---------------------------------------*/
    	$r=0;
    	foreach($rows as $row){
    		if($r%2==0)$attb='normal';
    		else $attb='alternate';
    		$r++;
	    		//-------------------check select-----------------

    		//-------------------end check select-----------------
    		$row_str.='<tr class="'.$attb.'"> ';
    				$i=0;
		  			foreach($row as $key=>$read) {
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
		  					$row_str.='<td class="items-no">'.$r.'</td>';
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
    							$clisc='oncontextmenu="setrowdata('.$temp.');return false;" class="context-menu-one" ';
    						}
    						if($i!=1){
	    						$text=$this->textAlign($read);
	    						$read=$this->checkValue($read);

	    						if($textalign != 'left'){
	    							$text  = " align=". $textalign;
	    						}
    						}
    						$row_str.='<td '.$clisc.' >'.$read.'</td>';
			  				if($i == count($columns)) {
	    						if($editLink != "") {
									$row_str.='<td '.$clisc.' ><a class="edit" href="'.$editLink.'/id/'.$temp.'">'.'</a></td>';
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
    	if(!$report){
    		$footer .= '<div class="footer_list">'.$stringPagination.$counter.'</div>';
    	}
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
}

