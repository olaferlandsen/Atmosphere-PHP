<?php
/** 
    * Smarty plugin 
    * 
    * @package Smarty 
    * @subpackage PluginsModifier 
    */ 

   /** 
    * Smarty date_format modifier plugin 
    * 
    * Type:     modifier<br> 
    * Name:     calculate_age<br> 
    * Purpose:  Calculate age based on a given date<br> 
    * Input:<br> 
    *          - string: date in the YYYY-MM-DD format 
    * 
    * @author Ivan Melgrati  
    * @param string date input date string 
    * @return int 
    */ 
	function smarty_modifier_calculate_age($birthday) 
	{
		list($year,$month,$day) = explode("-",$birthday);
		$year_diff  = date("Y") - $year;
		$month_diff = date("m") - $month;
		$day_diff   = date("d") - $day;
		if ($month_diff < 0) $year_diff--;
		elseif (($month_diff==0) && ($day_diff < 0)) $year_diff--;
		return $year_diff;
		
		return $year_diff-date('Y'); 
	}
?>