<?php 
	
session_start();

//Include cielo
require "../includes/include.php";

// $evento = (int) $_POST['evento'];

if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE; ?>';
</script>
<?
	exit();
}

//------------------------------------------------------------------------//

// Resgata último pedido feito da SESSION
$cod = (int) $_GET['c'];

//-----------------------------------------------------------------------------//

if(!empty($cod)) {


	$cliente = $_SESSION['us-cod'];

	$sql_compra = sqlsrv_query($conexao, "SELECT TOP 1 * FROM loja_pagamento_multiplo WHERE PM_COD=$cod", $conexao_params, $conexao_options);

	if(sqlsrv_num_rows($sql_compra) > 0) 
	{
		$co = sqlsrv_fetch_array($sql_compra);
		
		$co_xml = $co['PM_XML'];
		$co_valor = $co['PM_VALOR'];
		$co_compra = $co['PM_LOJA'];
		
		$Pedido = new Pedido();
		$Pedido->FromString($co_xml);

		$objResposta = $Pedido->RequisicaoCancelamento();
		
		$Pedido->status = $objResposta->status;

		$StrPedido = $Pedido->ToString();
		$co_status = $Pedido->status;

		//$_SESSION["pedidos"]->offsetSet($ultimoPedido, $StrPedido);
		// $sql_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja_pagamento_multiplo SET PM_XML='$StrPedido', PM_STATUS_TRANSACAO='$co_status', PM_PAGO=0 WHERE PM_COD=$cod", $conexao_params, $conexao_options);

		$sql_up = sqlsrv_query($conexao, "DELETE from loja_pagamento_multiplo WHERE PM_COD=$cod", $conexao_params, $conexao_options);

		echo "Foi";

		//------------------------------------------------------------------------//

		$sql_pagos = sqlsrv_query($conexao, "SELECT CASE WHEN (SUM(CASE WHEN PM_PAGO=1 THEN 1 ELSE 0 END) = COUNT(PM_COD)) THEN 1 ELSE 0 END AS PAGO FROM loja_pagamento_multiplo WHERE PM_LOJA='$co_compra'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_pagos) > 0) {

			$arpagos = sqlsrv_fetch_array($sql_pagos);			
			
			$pago_pago = ($arpagos['PAGO'] == 1) ? '1' : '0' ;
			$pago_data = ($arpagos['PAGO'] == 1) ? 'GETDATE()' : 'NULL' ;

			$sql_update = sqlsrv_query($conexao, "UPDATE loja SET LO_PAGO=$pago_pago, LO_DATA_PAGAMENTO=$pago_data WHERE LO_COD='$co_compra'", $conexao_params, $conexao_options);

		}


?>
<html>
	<head>
		<title>Cancelamento</title>		
	</head>
	<body>

	<script type="text/javascript">
		// alert('Pagamento confirmado!');
		window.location.href="<? echo SITE; ?>compras/pagamento-multiplo/<? echo $cod; ?>/";
	</script>
	</body>
</html>
<?
		exit();

	}

}
?>
<script type="text/javascript">
	alert('Ocorreu um erro, por favor tente novamente');
	location.href='<? echo SITE; ?>';
</script>