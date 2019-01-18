<?php
	require "logger.php";
	
	class Pedido
	{
		private $logger;
				
		public $dadosEcNumero;
		public $dadosEcChave;
		
		public $dadosPortadorNumero;
		public $dadosPortadorVal;
		public $dadosPortadorInd;
		public $dadosPortadorCodSeg;
		public $dadosPortadorNome;
		
		public $dadosPedidoNumero;
		public $dadosPedidoValor;
		public $dadosPedidoMoeda = "986";
		public $dadosPedidoData;
		public $dadosPedidoDescricao;
		public $dadosPedidoIdioma = "PT";
		
		public $formaPagamentoBandeira;
		public $formaPagamentoProduto;
		public $formaPagamentoParcelas;
		
		public $urlRetorno;
		public $autorizar;
		public $capturar;

		public $observacoes;

		public $bin;
		public $token;
		
		public $tid;
		public $status;
		public $urlAutenticacao;

		public $clienteEndereco;
		public $clienteComplemento;
		public $clienteCep;
		public $clienteNumero;
		public $clienteBairro;
		public $clienteCPF;

		
		const ENCODING = "ISO-8859-1";
		
		function __construct()
		{
			// cria um logger
			$this->logger = new Logger();
		}

		// Geradores de XML
		private function XMLHeader()
		{
			return '<?xml version="1.0" encoding="' . self::ENCODING . '" ?>'; 
		}
		
		private function XMLDadosEc()
		{
			$msg = '<dados-ec>' . "\n      " .
						'<numero>'
							. $this->dadosEcNumero . 
						'</numero>' . "\n      " .
						'<chave>'
							. $this->dadosEcChave .
						'</chave>' . "\n   " .
					'</dados-ec>';
							
			return $msg;
		}
		
		private function XMLDadosPortador()
		{
			$msg = '<dados-portador>' . "\n      " . 
						'<numero>' 
							. $this->dadosPortadorNumero .
						'</numero>' . "\n      " .
						'<validade>'
							. $this->dadosPortadorVal .
						'</validade>' . "\n      " .
						'<indicador>'
							. $this->dadosPortadorInd .
						'</indicador>' . "\n      " .
						'<codigo-seguranca>'
							. $this->dadosPortadorCodSeg .
						'</codigo-seguranca>' . "\n   ";
			
			// Verifica se Nome do Portador foi informado
			if($this->dadosPortadorNome != null && $this->dadosPortadorNome != "")
			{
				$msg .= '   <nome-portador>'
							. $this->dadosPortadorNome .
						'</nome-portador>' . "\n   " ;
			}
			
			$msg .= '</dados-portador>';
			
			return $msg;
		}
		
		private function XMLDadosCartao()
		{
			$msg = '<dados-cartao>' . "\n      " . 
						'<numero>' 
							. $this->dadosPortadorNumero .
						'</numero>' . "\n      " .
						'<validade>'
							. $this->dadosPortadorVal .
						'</validade>' . "\n      " .
						'<indicador>'
							. $this->dadosPortadorInd .
						'</indicador>' . "\n      " .
						'<codigo-seguranca>'
							. $this->dadosPortadorCodSeg .
						'</codigo-seguranca>' . "\n   ";

			// Verifica se Nome do Portador foi informado				
			if($this->dadosPortadorNome != null && $this->dadosPortadorNome != "")
			{
				$msg .= '   <nome-portador>'
							. $this->dadosPortadorNome .
						'</nome-portador>' . "\n   " ;
			}
			
			$msg .= '</dados-cartao>';
			
			return $msg;
		}
		
		private function XMLDadosPedido()
		{
			$this->dadosPedidoData = date("Y-m-d") . "T" . date("H:i:s");
			$msg = '<dados-pedido>' . "\n      " .
						'<numero>'
							. $this->dadosPedidoNumero . 
						'</numero>' . "\n      " .
						'<valor>'
							. $this->dadosPedidoValor .
						'</valor>' . "\n      " .
						'<moeda>'
							. $this->dadosPedidoMoeda .
						'</moeda>' . "\n      " .
						'<data-hora>'
							. $this->dadosPedidoData .
						'</data-hora>' . "\n      ";
			if($this->dadosPedidoDescricao != null && $this->dadosPedidoDescricao != "")
			{
				$msg .= '<descricao>'
					. $this->dadosPedidoDescricao .
					'</descricao>' . "\n      ";
			}
			$msg .= '<idioma>'
						. $this->dadosPedidoIdioma .
					'</idioma>' . "\n   " .
					'</dados-pedido>';
							
			return $msg;
		}
		
		private function XMLFormaPagamento()
		{
			$msg = '<forma-pagamento>' . "\n      " .
						'<bandeira>' 
							. $this->formaPagamentoBandeira .
						'</bandeira>' . "\n      " .
						'<produto>'
							. $this->formaPagamentoProduto .
						'</produto>' . "\n      " .
						'<parcelas>'
							. $this->formaPagamentoParcelas .
						'</parcelas>' . "\n   " .
					'</forma-pagamento>';
							
			return $msg;
		}
		 
		private function XMLUrlRetorno()
		{
			$msg = '<url-retorno>' . $this->urlRetorno . '</url-retorno>';
			
			return $msg;
		}

		private function XMLBin(){
			$msg = '<bin>' . $this->bin . '</bin>';
			
			return $msg;	
		}

		private function XMLToken(){
			$msg = '<gerar-token>' . $this->token . '</gerar-token>';
			
			return $msg;	
		}

		private function XMLAvs(){

			$msg = '<avs>'."\n      ".
						'<![CDATA[ ' . "\n      ".
							'<dados-avs> ' . "\n      ".
								'<endereco>' . $this->clienteEndereco .'</endereco>'."\n      ".
								'<complemento>' . $this->clienteComplemento . '</complemento>'."\n      ".
								'<numero>'. $this->clienteNumero . '</numero>'."\n      ".
								'<bairro>' . $this->clienteBairro. '</bairro>'."\n      ".
								'<cep>'.$this->clienteCep. '</cep>'."\n      ".
							'</dados-avs>'."\n      ".
						']]>'."\n      ".
					'</avs>';
			return $msg;

		}
		
		private function XMLAutorizar()
		{
			$msg = '<autorizar>' . $this->autorizar . '</autorizar>';
			
			return $msg;
		}


		private function XMLObservacoes()
		{
			$msg = '<campo-livre>' . $this->observacoes . '</campo-livre>';
			return $msg;
		}
		
		private function XMLCapturar()
		{
			$msg = '<capturar>' . $this->capturar . '</capturar>';
			
			return $msg;
		}

		// Envia Requisição
		public function Enviar($vmPost, $transacao)
		{
			//escrever log envio
			$this->logger->logWrite("ENVIO: " . $vmPost, $transacao);
	
			// ENVIA REQUISIÇÃO SITE CIELO
			$vmResposta = httprequest(ENDERECO, "mensagem=" . $vmPost);

			//escrever log resposta
			$this->logger->logWrite("RESPOSTA: " . $vmResposta, $transacao);
			
			VerificaErro($vmPost, $vmResposta);
			
			return simplexml_load_string($vmResposta);
		}
		
		// Requisições
		public function RequisicaoTransacao($incluirPortador){
			$msg = $this->XMLHeader() . "\n" .
				   '<requisicao-transacao id="' . md5(date("YmdHisu")) . '" versao="' . VERSAO . '">' . "\n   "
				   		. $this->XMLDadosEc() . "\n   ";
			if($incluirPortador == true)
			{
					$msg .=	$this->XMLDadosPortador() . "\n   ";
			}
			$msg .=		  $this->XMLDadosPedido() . "\n   "
				   		. $this->XMLFormaPagamento() . "\n   "
				   		. $this->XMLUrlRetorno() . "\n   "
				   		. $this->XMLAutorizar() . "\n   "
				   		. $this->XMLCapturar() . "\n   "
				   		. $this->XMLBin() . "\n   "
				   		//. $this->XMLObservacoes() . "\n"
				   		. $this->XMLToken() . "\n   "
				   		. $this->XMLAvs() . "\n   ";
			$msg .= '</requisicao-transacao>';

			// print($msg . '<br>');			
			$objResposta = $this->Enviar($msg, "Transacao");
			return $objResposta;
		}
		// Requisições
		// public function RequisicaoTransacaoAvs($incluirPortador){
		// 	$msg = $this->XMLHeader() . "\n" .
		// 		   '<requisicao-transacao id="' . md5(date("YmdHisu")) . '" versao="' . VERSAO . '">' . "\n   "
		// 		   		. $this->XMLDadosEc() . "\n   ";
		// 	if($incluirPortador == true)
		// 	{
		// 			$msg .=	$this->XMLDadosPortador() . "\n   ";
		// 	}
		// 	$msg .=		  $this->XMLDadosPedido() . "\n   "
		// 		   		. $this->XMLFormaPagamento() . "\n   "
		// 		   		. $this->XMLUrlRetorno() . "\n   "
		// 		   		. $this->XMLAutorizar() . "\n   "
		// 		   		. $this->XMLCapturar() . "\n   "
		// 		   		. $this->XMLBin() . "\n   "
		// 		   		//. $this->XMLObservacoes() . "\n"
		// 		   		. $this->XMLToken() . "\n   "
		// 		   		. $this->XMLAvs() . "\n   ";
		// 	$msg .= '</requisicao-transacao>';

		// 	echo "XML Envio:</br><textarea name='' id='' cols='30' rows='10'>".$msg."</textarea>";		
		// 	$objResposta = $this->Enviar($msg, "Transacao");
		// 	return $objResposta;
		// }
		
		public function RequisicaoTid()
		{

			$msg = $this->XMLHeader() . "\n" .
				   '<requisicao-tid id="' . md5(date("YmdHisu")) . '" versao ="' . VERSAO . '">' . "\n   "
				        . $this->XMLDadosEc() . "\n   " 
				        . $this->XMLFormaPagamento() . "\n" .
				   '</requisicao-tid>';

			$objResposta = $this->Enviar($msg, "Requisicao Tid");

			return $objResposta;
		}
		
		public function RequisicaoAutorizacaoPortador()
		{
			$msg = $this->XMLHeader() . "\n" .
				   '<requisicao-autorizacao-portador id="' . md5(date("YmdHisu")) . '" versao ="' . VERSAO . '">' . "\n"
				   		. '<tid>' . $this->tid . '</tid>' . "\n   "
				        . $this->XMLDadosEc() . "\n   " 
				        . $this->XMLDadosCartao() . "\n   "
				        . $this->XMLDadosPedido() . "\n   "
				        . $this->XMLFormaPagamento() . "\n   "
				        . '<capturar-automaticamente>' . $this->capturar . '</capturar-automaticamente>' . "\n" .
				   '</requisicao-autorizacao-portador>';
			//print($msg);
		
			$objResposta = $this->Enviar($msg, "Autorizacao Portador");
			return $objResposta;
		}
		
		public function RequisicaoAutorizacaoTid()
		{
			$msg = $this->XMLHeader() . "\n" .
				 '<requisicao-autorizacao-tid id="' . md5(date("YmdHisu")) . '" versao="' . VERSAO . '">' . "\n  "
				 	. '<tid>' . $this->tid . '</tid>' . "\n  "
				 	. $this->XMLDadosEc() . "\n" .
				 '</requisicao-autorizacao-tid>';
				 	
			$objResposta = $this->Enviar($msg, "Autorizacao Tid");
			return $objResposta;
		}
		
		public function RequisicaoCaptura($PercentualCaptura, $anexo)
		{
			$msg = $this->XMLHeader() . "\n" .
				    '<requisicao-captura id="' . md5(date("YmdHisu")) . '" versao="' . VERSAO . '">' . "\n   "
				   	. '<tid>' . $this->tid . '</tid>' . "\n   "
				   	. $this->XMLDadosEc() . "\n   "
				   	. '<valor>' . $PercentualCaptura . '</valor>' . "\n";
			if($anexo != null && $anexo != "")
			{
				$msg .=	'   <anexo>' . $anexo . '</anexo>' . "\n";
			}
			$msg .= '</requisicao-captura>';
			
			$objResposta = $this->Enviar($msg, "Captura");
			return $objResposta;
		}
		
		public function RequisicaoCancelamento()
		{
			$msg = $this->XMLHeader() . "\n" . 
				   '<requisicao-cancelamento id="' . md5(date("YmdHisu")) . '" versao="' . VERSAO . '">' . "\n   "
				    . '<tid>' . $this->tid . '</tid>' . "\n   "
				    . $this->XMLDadosEc() . "\n" .
				   '</requisicao-cancelamento>';
			
			$objResposta = $this->Enviar($msg, "Cancelamento");
			return $objResposta;
		}
		
		public function RequisicaoConsulta()
		{
			$msg = $this->XMLHeader() . "\n" .
				   '<requisicao-consulta id="' . md5(date("YmdHisu")) . '" versao="' . VERSAO . '">' . "\n   "
				    . '<tid>' . $this->tid . '</tid>' . "\n   "
				    . $this->XMLDadosEc() . "\n" .
				   '</requisicao-consulta>';
			
			$objResposta = $this->Enviar($msg, "Consulta");
			return $objResposta;
		}
		
		
		// Transforma em/lê string
		public function ToString()
		{
			$msg = $this->XMLHeader() .
				   '<objeto-pedido>'
				    . '<tid>' . $this->tid . '</tid>'
				    . '<status>' . $this->status . '</status>'
				   	. $this->XMLDadosEc()
				   	. $this->XMLDadosPedido()
				   	. $this->XMLFormaPagamento() .
				   '</objeto-pedido>';
			return $msg;
		}
		
		public function FromString($Str)
		{
			$DadosEc = "dados-ec";
			$DadosPedido = "dados-pedido";
			$DataHora = "data-hora";
			$FormaPagamento = "forma-pagamento";
			
			$XML = simplexml_load_string($Str);
			
			$this->tid = $XML->tid;
			$this->status = $XML->status;
			$this->dadosEcChave = $XML->$DadosEc->chave;
			$this->dadosEcNumero = $XML->$DadosEc->numero;
			$this->dadosPedidoNumero = $XML->$DadosPedido->numero;
			$this->dadosPedidoData = $XML->$DadosPedido->$DataHora;
			$this->dadosPedidoValor = $XML->$DadosPedido->valor;
			$this->formaPagamentoProduto = $XML->$FormaPagamento->produto;
			$this->formaPagamentoParcelas = $XML->$FormaPagamento->parcelas;
		}
		
		// Traduz cógigo do Status
		public function getStatus()	{
			$status;
			
			/*switch($this->status)
			{
				case "0": $status = "Criada";
						break;
				case "1": $status = "Em andamento";
						break;
				case "2": $status = "Autenticada";
						break;
				case "3": $status = "Não autenticada";
						break;
				case "4": $status = "Autorizada";
						break;
				case "5": $status = "Não autorizada";
						break;
				case "6": $status = "Capturada";
						break;
				case "8": $status = "Não capturada";
						break;
				case "9": $status = "Cancelada";
						break;
				case "10": $status = "Em autenticação";
						break;
				default: $status = "n/a";
						break;
			}*/
			
			$sql_st = mysql_query("SELECT * FROM compras_status WHERE CS_COD='".$this->status."' LIMIT 1");
			if(mysql_num_rows($sql_st) > 0) {
				
				$st = mysql_fetch_array($sql_st);
				$status = $st['CS_STATUS'];
			}
			
			return $status;
		}
		
	}
	
?>