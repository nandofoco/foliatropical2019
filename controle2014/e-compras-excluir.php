<?

//Verificamos o dominio
include("include/includes.php");

$cod = (int) $_GET['c'];

if(!empty($cod)){

	$sql_del_adicionais = sqlsrv_query($conexao, "UPDATE loja_itens_adicionais SET D_E_L_E_T_='1' WHERE LIA_COMPRA='$cod'", $conexao_params, $conexao_options);
	// $sql_del_comentarios = sqlsrv_query($conexao, "UPDATE loja_comentarios SET D_E_L_E_T_='1' WHERE LC_COMPRA='$cod'", $conexao_params, $conexao_options);
	$sql_del_item = sqlsrv_query($conexao, "UPDATE loja_itens SET D_E_L_E_T_='1' WHERE LI_COMPRA='$cod'", $conexao_params, $conexao_options);
	$sql_del_compra = sqlsrv_query($conexao, "UPDATE loja SET D_E_L_E_T_='1' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
	$sql_del_cupom = sqlsrv_query($conexao, "UPDATE cupom SET CP_UTILIZADO=0, CP_DATA_UTILIZACAO=NULL WHERE CP_COMPRA='$cod'", $conexao_params, $conexao_options);

	$sql_log = sqlsrv_query($conexao, "INSERT INTO loja_excluidas (LE_COMPRA, LE_USUARIO, LE_DATA) VALUES ('$cod', '".$_SESSION['us-cod']."', GETDATE())", $conexao_params, $conexao_options);

	//Fechar conexoes
	include("conn/close.php");

	?>
	<script type="text/javascript">
		alert('Compra cancelada');
		location.href='<? echo SITE; ?>financeiro/pendentes/';
	</script>
	<?

	exit();

}

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>