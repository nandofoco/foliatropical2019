<?php

	class Logger
	{
		private $log_file = "../logs/xml.log";
		private $fp = null;
		
		public function logOpen()
		{
			$this->fp = fopen($this->log_file, 'a');
		}
		 
		public function logWrite($strMessage, $transacao)
		{
			if(!$this->fp)
				$this->logOpen();
			
			$path = $_SERVER["REQUEST_URI"];
			$data = date("Y-m-d H:i:s:u (T)");
			
			$log = "***********************************************" . "\n";
			$log .= $data . "\n";
			$log .= "DO ARQUIVO: " . $path . "\n"; 
			$log .= "OPERAÇÃO: " . $transacao . "\n";
			$log .= $strMessage . "\n\n"; 

			$log=preg_replace("/<codigo-seguranca>.+?<\/codigo-seguranca>/i", "<codigo-seguranca></codigo-seguranca>", $log);

			fwrite($this->fp, $log);
		}
	}
?>