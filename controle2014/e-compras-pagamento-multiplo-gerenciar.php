<?



//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$cod_loja = (int) $_GET['l'];
$forma = (int) $_GET['f'];
$acao = $_GET['a'];


switch ($acao) {
	case 'confirmar':
	case 'bloquear':
		$block = ($acao == 'confirmar') ? 1 : 0;
		$block_texto = ($block) ? 'confirmado' : 'bloqueado';
		$block_data = ($block) ? "'".date('Y-m-d H:i:s')."'" : 'NULL';

		//$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) loja_pagamento_multiplo SET PM_PAGO=1, PM_DATA_PAGAMENTO=$block_data WHERE PM_LOJA='$cod' AND PM_COD='$forma'", $conexao_params, $conexao_options);

		$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) loja_pagamento_multiplo SET PM_PAGO=1, PM_DATA_PAGAMENTO=$block_data WHERE PM_LOJA='$cod_loja' AND PM_COD='$cod'", $conexao_params, $conexao_options);

		$mensagem = 'Pagamento '.$block_texto;

		$sql_pagos = sqlsrv_query($conexao, "SELECT CASE WHEN (SUM(CASE WHEN PM_PAGO=1 THEN 1 ELSE 0 END) = COUNT(PM_COD)) THEN 1 ELSE 0 END AS PAGO FROM loja_pagamento_multiplo WHERE PM_LOJA='$cod_loja'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_pagos) > 0) {

			$arpagos = sqlsrv_fetch_array($sql_pagos);			
			
			$pago_pago = ($arpagos['PAGO'] == 1) ? '1' : '0' ;
			$pago_data = ($arpagos['PAGO'] == 1) ? 'GETDATE()' : 'NULL' ;

			$sql_update = sqlsrv_query($conexao, "UPDATE loja SET LO_PAGO=$pago_pago, LO_DATA_PAGAMENTO=$pago_data WHERE LO_COD='$cod_loja'", $conexao_params, $conexao_options);

		}
		break;


	case 'excluir':
		$sql_exclude = sqlsrv_query($conexao, "DELETE FROM loja_pagamento_multiplo WHERE PM_COD='$cod'", $conexao_params, $conexao_options);

		break;

}


?>
<script type="text/javascript">
	alert('<? echo $mensagem; ?>');
	// history.go(-1);
	//window.location.href="https://ingressos.foliatropical.com.br/controle2014/compras/pagamento-multiplo/<?=$cod?>/";
</script>
<?

header("Location: ".$_SERVER['HTTP_REFERER']);


//Fechar conexoes
include("conn/close.php");

exit();

//-----------------------------------------------------------------------------//

?>