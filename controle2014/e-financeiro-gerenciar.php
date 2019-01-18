<?

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$acao = $_GET['a'];

switch ($acao) {
	case 'confirmar':
	case 'bloquear':
		$block = ($acao == 'confirmar') ? 1 : 0;
		$block_texto = ($block) ? 'confirmado' : 'bloqueado';
		$block_data = ($block) ? "'".date('Y-m-d H:i:s')."'" : 'NULL';

		/*echo "UPDATE TOP (1) loja SET LO_PAGO=$block, LO_DATA_PAGAMENTO=$block_data WHERE LO_COD='$cod'";
		exit();*/
		$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_PAGO=$block, LO_DATA_PAGAMENTO=$block_data WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
		$mensagem = 'Pagamento '.$block_texto.' com sucesso';

		// Log de liberacao do pagamento
		if($block) {
			$sql_exist =  sqlsrv_query($conexao, "SELECT LG_COD FROM log WHERE LG_VOUCHER = '$cod' AND LG_ACAO=N'Pagamento liberado' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_exist) == 0) $sql_log = sqlsrv_query($conexao, "INSERT INTO log (LG_VOUCHER, LG_USUARIO, LG_NOME, LG_ACAO, LG_DATA) VALUES ('$cod', '".$_SESSION['us-cod']."', '".$_SESSION['us-nome']."', 'Pagamento liberado', GETDATE())", $conexao_params, $conexao_options);
		}

	break;

	case 'alterar':
		$forma = (int) $_GET['f'];
		if($forma > 0) {
			$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_FORMA_PAGAMENTO='$forma', LO_PAGO=0 WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
			$mensagem = 'Forma de pagamento alterada';
		}
	break;
}

?>
<script type="text/javascript">
	alert('<? echo $mensagem; ?>');
	history.go(-1);
</script>
<?

//Fechar conexoes
include("conn/close.php");

exit();

//-----------------------------------------------------------------------------//

?>