<?

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$acao = $_GET['a'];
$return = (bool) $_GET['r'];

if(!empty($cod) && !empty($acao)) {
	//bloquear
	if($acao == "bloquear") {
		$label = "bloqueado";
		$sql_bloquear = sqlsrv_query($conexao, "UPDATE TOP (1) eventos SET EV_BLOCK='1' WHERE EV_COD='$cod'", $conexao_params, $conexao_options);
	//desbloquear
	} elseif($acao == "desbloquear") {
		$label = "desbloqueado";
		$sql_desbloquear = sqlsrv_query($conexao, "UPDATE TOP (1) eventos SET EV_BLOCK='0' WHERE EV_COD='$cod'", $conexao_params, $conexao_options);
		//excluir
	} elseif($acao == "excluir") {
		$label = "excluido";
		$sql_excluir = sqlsrv_query($conexao, "UPDATE TOP (1) eventos SET D_E_L_E_T_='1' WHERE EV_COD='$cod'", $conexao_params, $conexao_options);
		//ativar
	} elseif($acao == "ativar") {
		$label = "ativado";
		$_SESSION['usuario-carnaval'] = $cod;
	}
	
	//desativar
	?>
	<script type="text/javascript">
		<? if ($return) { ?>
			history.go(-1);
		<? } else{ ?>
		alert('Evento <? echo $label; ?> com sucesso.');
		location.href='<? echo SITE; ?>carnaval/lista/';
		<? } ?>
	</script>
	<?

}

//Fechar conexoes
include("conn/close.php");

?>