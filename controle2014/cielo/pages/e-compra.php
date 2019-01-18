<?php 

session_start();

//Include cielo
require "../includes/include.php";

// $evento = (int) $_POST['evento'];
$fromcliente = isset($_GET['fromcliente']) ? true : false;

if($fromcliente) {
	
	/*if(!checklogado()){
	?>
	<script type="text/javascript">
		location.href='<? echo str_replace('controle2014/', '', SITE); ?>';
	</script>
	<?
		exit();
	}*/
	
}

//------------------------------------------------------------------------//

$cliente = $fromcliente ? $_SESSION['usuario-cod'] : null;
$tipo = $fromcliente ? 'cliente' : null;

$codigo_bandeira = format($_POST['codigoBandeira']);
$forma_pagamento = format($_POST['formaPagamento']);
$tipo_parcelamento = format($_POST['tipoParcelamento']);
$capturar_automaticamente = format($_POST['capturarAutomaticamente']);
$indicador_autorizacao = (int) $_POST['indicadorAutorizacao'];
$produto = format($_POST['produto']);

// Autorizar direto se for Elo, Diners, Discover, Aura e JCB
switch ($codigo_bandeira) {
	case 'diners':
	case 'discover':
	case 'elo':
	case 'amex':
		$indicador_autorizacao = 3;
	break;
}

$compra = (int) $_POST['compra'];
$produto_f = number_format($produto,2,"","");

unset($_SESSION['qtde-indisponivel']);

if($compra > 0) {

	//Atualizar o valor da compra com desconto e cupom
	//if($fromcliente && isset($_SESSION['compra-cupom'])){
	//	if(($_SESSION['compra-cupom']['compra'] == $compra) && ($_SESSION['compra-cupom']['usuario'] == $cliente)) {
	
	if(isset($_SESSION['compra-cupom'])){
		if($_SESSION['compra-cupom']['compra'] == $compra) {
		
			$sql_cupom_usado = sqlsrv_query($conexao, "UPDATE TOP (1) cupom SET CP_UTILIZADO=1, CP_COMPRA='$compra', CP_DATA_UTILIZACAO=GETDATE() WHERE CP_COD='".$_SESSION['compra-cupom']['cod']."'", $conexao_params, $conexao_options);
			$sql_compra_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_VALOR_TOTAL='$produto' WHERE LO_COD='$compra'", $conexao_params, $conexao_options);

			unset($_SESSION['compra-cupom']);
		}
	}

	if(isset($_SESSION['compra-cupom-petros']) && !empty($cliente)){
		
		$matricula = isset($_SESSION['compra-cupom-petros']['matricula']) ? format($_SESSION['compra-cupom-petros']['matricula']) : null;

		if(($_SESSION['compra-cupom-petros']['compra'] == $compra) && !empty($matricula)) {
		
			$sql_cupom_petros = sqlsrv_query($conexao, "INSERT INTO loja_cupom_petros (LCP_COMPRA, LCP_USUARIO, LCP_MATRICULA, LCP_DATA) VALUES ('$compra', '$cliente', '$matricula', GETDATE())", $conexao_params, $conexao_options);
			$sql_compra_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_VALOR_TOTAL='$produto' WHERE LO_COD='$compra'", $conexao_params, $conexao_options);

			unset($_SESSION['compra-cupom-petros']);
		}
	}

?>
<html>
	<head>
		<title>Pagamento <?php echo strtoupper($codigo_bandeira); ?></title>		
	</head>
	<body>
		Redirecionando...		
<?php

	
//------------------------------------------------------------------------//

$Pedido = new Pedido();

// Lê dados do $_POST
$Pedido->formaPagamentoBandeira = $codigo_bandeira; 
if($forma_pagamento != "A" && $forma_pagamento != "1")
{
	$Pedido->formaPagamentoProduto = $tipo_parcelamento;
	$Pedido->formaPagamentoParcelas = $forma_pagamento;
} 
else 
{
	$Pedido->formaPagamentoProduto = $forma_pagamento;
	$Pedido->formaPagamentoParcelas = 1;
}

$Pedido->dadosEcNumero = CIELO;
$Pedido->dadosEcChave = CIELO_CHAVE;

$Pedido->capturar = $capturar_automaticamente;	
$Pedido->autorizar = $indicador_autorizacao;

$Pedido->dadosPedidoNumero = $compra; 
$Pedido->dadosPedidoValor = $produto_f;

$Pedido->urlRetorno = ReturnURL($compra, $tipo);
// $Pedido->urlRetorno = ReturnURL($compra);

// ENVIA REQUISIÇÃO SITE CIELO
$objResposta = $Pedido->RequisicaoTransacao(false);

$Pedido->tid = $objResposta->tid;
$Pedido->pan = $objResposta->pan;
$Pedido->status = $objResposta->status;

$urlAutenticacao = "url-autenticacao";
$Pedido->urlAutenticacao = $objResposta->$urlAutenticacao;

// Serializa Pedido e guarda no Banco
$StrPedido = $Pedido->ToString();

$co_xml = $StrPedido;
$co_status = $Pedido->status;
$co_tid = $Pedido->tid;
$co_delete = '';

switch($co_status) {
	case 3:
	case 5:
	case 8:
	case 9:
		$co_delete = ", D_E_L_E_T_='1' ";
	break;
}

$sql_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_TID='$co_tid', LO_CARTAO='$codigo_bandeira', LO_XML='$co_xml', LO_STATUS_TRANSACAO='$co_status' $co_delete WHERE LO_COD='$compra'", $conexao_params, $conexao_options);

?>
<script type="text/javascript">
	window.location.href="<? echo $Pedido->urlAutenticacao; ?>";
</script>
	</body>
</html>
<?
} else {

	$link_retorno = $fromcliente ? str_replace('controle2014/', '', SITE).'comprar-ingressos-carnaval-2015-rj/pagamento/' : SITE.'produtos/pedido/';
?>
<script type="text/javascript">
	alert('Ocorreu um erro, por favor tente novamente');
	location.href='<? echo $link_retorno.$compra; ?>/';
	// location.href='<? echo SITE; ?>produtos/pedido/<? echo $compra; ?>/';
</script>
<?
}
?>