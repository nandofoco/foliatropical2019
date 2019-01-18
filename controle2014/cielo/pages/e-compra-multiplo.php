<?php 

session_start();

//Include cielo
require "../includes/include.php";

// $evento = (int) $_POST['evento'];

//------------------------------------------------------------------------//

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

$Pedido->urlRetorno = ReturnURL($compra, 'multiplo');
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
		//$co_delete = ", D_E_L_E_T_='1' ";
	break;
}

$sql_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja_pagamento_multiplo SET PM_TID='$co_tid', PM_CARTAO='$codigo_bandeira', PM_XML='$co_xml', PM_STATUS_TRANSACAO='$co_status' WHERE PM_COD='$compra'", $conexao_params, $conexao_options);

?>
<script type="text/javascript">
	window.location.href="<? echo $Pedido->urlAutenticacao; ?>";
</script>
	</body>
</html>
<?
} else {

	$link_retorno = SITE.'compras/pagamento-multiplo/cartao/';
?>
<script type="text/javascript">
	alert('Ocorreu um erro, por favor tente novamente');
	location.href='<? echo $link_retorno.$compra; ?>/';
	// location.href='<? echo SITE; ?>produtos/pedido/<? echo $compra; ?>/';
</script>
<?
}
?>