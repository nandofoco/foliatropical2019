<?

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

//Definir o carnaval ativo
include("include/setcarnaval.php");


if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE.$link_lang; ?>';
</script>
<?
	exit();
}

//-----------------------------------------------------------------//

$evento = setcarnaval();
$cod = (int) $_GET['c'];
$usuario_cod = $_SESSION['usuario-cod'];

//-----------------------------------------------------------------//

if(!empty($cod)){

	$sql_compra = sqlsrv_query($conexao, "SELECT LO_COD FROM loja WHERE LO_COD='$cod' AND LO_CLIENTE='$usuario_cod' AND LO_EVENTO='$evento'", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_compra) > 0) {

		$sql_del_adicionais = sqlsrv_query($conexao, "UPDATE loja_itens_adicionais SET D_E_L_E_T_='1' WHERE LIA_COMPRA='$cod'", $conexao_params, $conexao_options);
		// $sql_del_comentarios = sqlsrv_query($conexao, "UPDATE loja_comentarios SET D_E_L_E_T_='1' WHERE LC_COMPRA='$cod'", $conexao_params, $conexao_options);
		$sql_del_item = sqlsrv_query($conexao, "UPDATE loja_itens SET D_E_L_E_T_='1' WHERE LI_COMPRA='$cod'", $conexao_params, $conexao_options);
		$sql_del_compra = sqlsrv_query($conexao, "UPDATE loja SET D_E_L_E_T_='1' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
		$sql_del_cupom = sqlsrv_query($conexao, "UPDATE cupom SET CP_UTILIZADO=0, CP_DATA_UTILIZACAO=NULL WHERE CP_COMPRA='$cod'", $conexao_params, $conexao_options);

		$sql_log = sqlsrv_query($conexao, "INSERT INTO loja_excluidas (LE_COMPRA, LE_USUARIO, LE_DATA) VALUES ('$cod', '$usuario_cod', GETDATE())", $conexao_params, $conexao_options);

		?>
		<script type="text/javascript">
			alert('Compra cancelada');
			location.href='<? echo SITE.$link_lang; ?>minhas-compras/';
		</script>
		<?

	}


} else {
	
	?>
	<script type="text/javascript">
		alert('Ocorreu um erro, tente novamente!');
		history.go(-1);
	</script>
	<?
}

//fechar conexao com o banco
include("conn/close.php");
include("conn/close-mssql.php");

?>