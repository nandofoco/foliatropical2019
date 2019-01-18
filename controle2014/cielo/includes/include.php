<?php

define('PGINCLUDE', 'true');
define('NOCHECK', 'true');

//Conectar ao servidor
include("../../conn/conn.php");
include("../../conn/conn-sankhya.php");
include("../../include/checklogado.php");
include("../../../include/setcarnaval.php");

//Incluir funчѕes bсsicas
include("../../include/funcoes.php");

require 'errorHandling.php';
require_once 'pedido.php';
require_once 'logger.php';

define('VERSAO', "1.1.0");

//testes
// define("ENDERECO_BASE", "https://qasecommerce.cielo.com.br");

//producao
define("ENDERECO_BASE","https://ecommerce.cielo.com.br");
define("ENDERECO", ENDERECO_BASE."/servicos/ecommwsec.do");


//testes
// define("LOJA", "1006993069");
// define("LOJA_CHAVE", "25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3");

//producao
define("LOJA",  "1060538137");
define("LOJA_CHAVE", "ada401374b980f0cc7f0da53e6b6491d0ae9c114cdb9421560e804574890c8f9");


// CONSTANTES

// Envia requisiчуo
function httprequest($paEndereco, $paPost){

	$sessao_curl = curl_init();
	curl_setopt($sessao_curl, CURLOPT_URL, $paEndereco);
	
	curl_setopt($sessao_curl, CURLOPT_FAILONERROR, true);

	//  CURLOPT_SSL_VERIFYPEER
	//  verifica a validade do certificado
	//curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYPEER, false);
	//  CURLOPPT_SSL_VERIFYHOST
	//  verifica se a identidade do servidor bate com aquela informada no certificado
	//curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYHOST, false);

	//  CURLOPT_SSL_CAINFO
	//  informa a localizaчуo do certificado para verificaчуo com o peer
	//curl_setopt($sessao_curl, CURLOPT_CAINFO, getcwd() .
	//		"/ssl/VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt");
	//curl_setopt($sessao_curl, CURLOPT_SSLVERSION, 3);

	//  CURLOPT_CONNECTTIMEOUT
	//  o tempo em segundos de espera para obter uma conexуo
	curl_setopt($sessao_curl, CURLOPT_CONNECTTIMEOUT, 10);

	//  CURLOPT_TIMEOUT
	//  o tempo mсximo em segundos de espera para a execuчуo da requisiчуo (curl_exec)
	curl_setopt($sessao_curl, CURLOPT_TIMEOUT, 40);

	//  CURLOPT_RETURNTRANSFER
	//  TRUE para curl_exec retornar uma string de resultado em caso de sucesso, ao
	//  invщs de imprimir o resultado na tela. Retorna FALSE se hс problemas na requisiчуo
	curl_setopt($sessao_curl, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($sessao_curl, CURLOPT_POST, true);
	curl_setopt($sessao_curl, CURLOPT_POSTFIELDS, $paPost );

	$resultado = curl_exec($sessao_curl);
	
	if ($resultado)
	{
		return $resultado;
	}
	else
	{
		return curl_error($sessao_curl);
	}
	
	curl_close($sessao_curl);
}

// Monta URL de retorno
/*function ReturnURL()
{
	$pageURL = 'http';

	if ($_SERVER["SERVER_PORT"] == 443) // protocolo https
	{
		$pageURL .= 's';
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80")
	{
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"]. substr($_SERVER["REQUEST_URI"], 0);
	}
	// ALTERNATIVA PARA SERVER_NAME -> HOST_HTTP

	$file = substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);

	$ReturnURL = str_replace($file, "retorno.php", $pageURL);

	return $ReturnURL;
}*/


// Monta URL de retorno
// function ReturnURL($cod)
function ReturnURL($cod,$tipo)
{
	
	$pagehttp = "http";
	if ($_SERVER["SERVER_PORT"] == 443)	{ $pagehttp .= "s"; }
	$pagehttp .= "://";

	$return_url = str_replace("http://", $pagehttp, SITE);
	
	switch ($tipo) {
		case 'cliente':
			$return_url = str_replace("controle2014/", '', $return_url);
			$return_url .= "ingressos-carnaval-2017/pagamento/cielo/retorno/$cod/";
		break;
		case 'multiplo':
			$return_url .= "compra/pagamento-multiplo/retorno/$cod/";
		break;
		default:
			$return_url .= "compra/retorno/$cod/";
		break;
	}

	return $return_url;
}

?>