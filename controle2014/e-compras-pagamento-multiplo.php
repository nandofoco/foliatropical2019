<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");

$resposta = 'Ocorreu um erro, tente novamente!';


//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$multiplo = $_POST['multiplo'];
$evento = (int) $_SESSION['usuario-carnaval'];


if(is_numeric($cod) && !empty($evento)) {

	if(count($_SESSION['pagamento-multiplo'][$cod]) > 0) 
	{

		//Verificar se quantidade total atinge o limite;
		$sql_loja = sqlsrv_query($conexao, "SELECT LO_VALOR_TOTAL FROM loja WHERE LO_COD='$cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
		
		if(sqlsrv_num_rows($sql_loja) > 0) 
		{

			$ar_valor_total = sqlsrv_fetch_array($sql_loja);
			
			$valor_total = $ar_valor_total['LO_VALOR_TOTAL'];
			$multiplo_total = 0;
			$multiplo_not = array();

			foreach ($_SESSION['pagamento-multiplo'][$cod] as $key => $compra) 
			{
				if(($compra['valor'] > 0) && !empty($compra['forma'])) 
				{
					$multiplo_total += $compra['valor'];

					if($compra['bd'] && ($compra['cod'] > 0)) 
					{
						array_push($multiplo_not, $compra['cod']);
						unset($_SESSION['pagamento-multiplo'][$cod][$key]);
					}
				}
			}
			
			//-----------------------------------------------------------------//

			if($multiplo_total <= $valor_total) 
			{
				
				if(count($multiplo_not) > 0) $notin = "  AND PM_COD NOT IN (".implode(',', $multiplo_not).") ";
				
				$sql_del = sqlsrv_query($conexao, "DELETE FROM loja_pagamento_multiplo WHERE PM_LOJA='$cod' $notin ", $conexao_params, $conexao_options); 





				foreach ($_SESSION['pagamento-multiplo'][$cod] as $key => $compra) 
				{
					if(($compra['valor'] > 0) && !empty($compra['forma'])) 
					{
						$sql_ins = sqlsrv_query($conexao, "INSERT INTO loja_pagamento_multiplo (PM_LOJA, PM_FORMA, PM_VALOR, PM_DATA_CADASTRO, PM_PAGO) VALUES ('$cod', '".$compra['forma']."', '".$compra['valor']."', GETDATE(),1)", $conexao_params, $conexao_options);

					}

				}

				//Atualizando pagamento
				$sql_update = sqlsrv_query($conexao, "UPDATE loja SET LO_PAGO=1, LO_DATA_PAGAMENTO=GETDATE() WHERE LO_COD='$cod'", $conexao_params, $conexao_options);

				$sql_exist =  sqlsrv_query($conexao, "SELECT LG_COD FROM log WHERE LG_VOUCHER = '$cod' AND LG_ACAO=N'Pagamento liberado' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
				
				if(sqlsrv_num_rows($sql_exist) == 0)
				{
					$sql_log = sqlsrv_query($conexao, "INSERT INTO log (LG_VOUCHER, LG_USUARIO, LG_NOME, LG_ACAO, LG_DATA) VALUES ('$cod', '".$_SESSION['us-cod']."', '".$_SESSION['us-nome']."', 'Pagamento liberado', GETDATE())", $conexao_params, $conexao_options);	
				}
				else
				{
					$sql_log_update =  sqlsrv_query($conexao, "UPDATE log SET LG_DATA=GETDATE() WHERE LG_VOUCHER = '$cod' AND LG_ACAO=N'Pagamento liberado' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
				}

				?>
				
				<script type="text/javascript">
					window.location.replace("https://ingressos.foliatropical.com.br/controle2014/compras/pagamento-multiplo/<? echo $cod ?>/");
				</script>

				<?
				//Fechar conexoes
				include("conn/close.php");
				
				exit();

			} else {
				$resposta = 'Ocorreu um erro, tente novamente';
			}
		}
	}
}

?>
<script type="text/javascript">
	alert('<? echo $resposta; ?>');
	history.go(-1);
	// location.href='<? echo SITE; ?>compras/pagamento-multiplo/'.$cod;
</script>