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
    * Name:     calculate_rating<br> 
    * Purpose:  Calculate rating string<br> 
    * Input:<br> 
    *          - int: rating int 
    * 
    * @author Felipe Espinoza
    * @param int rating 
    * @return string rating
    */
	
	function smarty_modifier_calculate_rating($rating)
	{
		$string_rating = '';
		switch ($rating) {
			case 1:
				$string_rating = 'Muy malo';
				break;
			case 2:
				$string_rating = 'Malo';
				break;
			case 3:
				$string_rating = 'Normal';
				break;
			case 4:
				$string_rating = 'Bueno';
				break;
			case 5:
				$string_rating = 'Muy Bueno';
				break;
			default:
				$string_rating = 'Sin Calificar';
		}
		return $string_rating;
	}
?>