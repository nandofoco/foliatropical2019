<?

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$acao = $_GET['a'];

switch ($acao) {
	case 'bloquear':
	case 'desbloquear':
		$block = ($acao == 'bloquear') ? 1 : 0;
		$block_texto = ($block) ? 'bloqueado' : 'desbloqueado';
		$sql_update = sqlsrv_query($conexao, "UPDATE TOP(1) cupom SET CP_BLOCK=$block WHERE CP_COD='$cod'", $conexao_params, $conexao_options);
		$mensagem = 'Cupom '.$block_texto;
	break;

	case 'deletar':
		$sql_update = sqlsrv_query($conexao, "UPDATE TOP(1) cupom SET D_E_L_E_T_='1' WHERE CP_COD='$cod'", $conexao_params, $conexao_options);
		$mensagem = 'Cupom deletado com sucesso';
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