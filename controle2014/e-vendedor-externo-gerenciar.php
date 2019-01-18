<?

//Verificamos o dominio
include("include/includes.php");

header('Content-Type: text/html; charset=utf-8');

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$acao = $_GET['a'];

switch ($acao) {
	case 'bloquear':
	case 'desbloquear':
		$block = ($acao == 'bloquear') ? '1' : '0';
		$block_texto = ($acao == 'bloquear') ? 'bloqueado' : 'desbloqueado';
		$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) vendedor_externo SET VE_BLOCK='$block' WHERE VE_COD='$cod'", $conexao_params, $conexao_options);
		$mensagem = 'Vendedor externo '.$block_texto;
	break;

	case 'deletar':
		$sql_delete = sqlsrv_query($conexao, "UPDATE TOP (1) vendedor_externo SET D_E_L_E_T_='1' WHERE VE_COD='$cod'", $conexao_params, $conexao_options);
		$mensagem = 'Vendedor externo excluído com sucesso';
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