<?

//Verificamos o dominio
include("include/includes.php");

$cod = (int) $_GET['c'];

if(!empty($cod)){


	$sql_del = sqlsrv_query($conexao, "UPDATE loja SET D_E_L_E_T_='0' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
	$sql_del_adicionais = sqlsrv_query($conexao, "UPDATE loja_itens_adicionais SET D_E_L_E_T_='0' WHERE LIA_COMPRA='$cod'", $conexao_params, $conexao_options);
	$sql_del_item = sqlsrv_query($conexao, "UPDATE loja_itens SET D_E_L_E_T_='0' WHERE LI_COMPRA='$cod'", $conexao_params, $conexao_options);
	$sql_del_compra = sqlsrv_query($conexao, "UPDATE loja SET D_E_L_E_T_='0' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
	$sql_del_cupom = sqlsrv_query($conexao, "UPDATE cupom SET CP_UTILIZADO=1, CP_DATA_UTILIZACAO=GETDATE() WHERE CP_COMPRA='$cod' AND CP_DATA_UTILIZACAO IS NULL AND CP_UTILIZADO=0", $conexao_params, $conexao_options);

	// $sql_log = sqlsrv_query($conexao, "INSERT INTO loja_excluidas (LE_COMPRA, LE_USUARIO, LE_DATA) VALUES ('$cod', '".$_SESSION['us-cod']."', GETDATE())", $conexao_params, $conexao_options);

	//Fechar conexoes
	include("conn/close.php");

	?>
	<script type="text/javascript">
		alert('Compra reativada');
		location.href='<? echo SITE; ?>financeiro/detalhes/<? echo $cod; ?>/';
	</script>
	<?

	exit();

}

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>