<?
function strSemAcentos($string="", $mesma=1) 
{	
	if($string != "")
	{		
		$com_acento = "à á â ã ä è é ê ë ì í î ï ò ó ô õ ö ù ú û ü À Á Â Ã Ä È É Ê Ë Ì Í Î Ò Ó Ô Õ Ö Ù Ú Û Ü ç Ç ñ Ñ";	
		$sem_acento = "a a a a a e e e e i i i i o o o o o u u u u A A A A A E E E E I I I O O O O O U U U U c C n N";	
		$c = explode(' ',$com_acento);
		$s = explode(' ',$sem_acento);
	
		$i=0;
		foreach($c as $letra)
		{
			if(preg_match("/$letra/", $string))
			{
				$pattern[] = $letra;
				$replacement[] = $s[$i];
			}		
			$i=$i+1;		
		}
		
		if(isset($pattern))
		{
			$i=0;
			foreach($pattern as $letra)
			{ 				
				$string = preg_replace("/$letra/i", $replacement[$i], $string);
				$i=$i+1;		
			}
			return $string; # retorna string alterada
		}	
		if ($mesma != 0) 
		{
			return $string; # retorna a mesma string se nada mudou
		}
	}
return ""; # sem mudança retorna nada
}
?>