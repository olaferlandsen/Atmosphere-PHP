<?php
	/** 
    * Smarty plugin 
    * 
    * @package Smarty 
    * @subpackage PluginsModifier 
    */ 

   /** 
    * 
    * 
    * Type:     modifier<br> 
    * Name:     short_number<br> 
    * Purpose:  Calculate short number<br> 
    * Input:<br> 
    *          - int: number int 
    * 
    * @author Felipe Espinoza
    * @param int number 
    * @return string short number
    */
	
	function smarty_modifier_short_number($number)
	{
		if($number > 999 and $number < 999999){
			$numberFloat = $number / 1000;
			$numberFloat = number_format($numberFloat, 1, '.', '');
			return $numberFloat.'K';
		}elseif($number > 999998 and $number < 1000000000){
			$numberFloat = $number / 1000000;
			$numberFloat = number_format($numberFloat, 1, '.', '');
			return $numberFloat.'M';
		}else{
			return $number;
		}
	}
?>