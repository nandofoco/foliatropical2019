<?

header('Content-Type: text/html; charset=utf-8');

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$acao = format($_GET['a']);

if(!empty($cod) && !empty($acao)) {

	//-----------------------------------------------------------------------------//

	switch ($acao) {
		case 'bloquear':
		case 'desbloquear':
			$block = ($acao == 'bloquear') ? 1 : 0;
			$block_texto = ($block) ? 'bloqueado' : 'desbloqueado';
			$sql_update = sqlsrv_query($conexao, "UPDATE vendas_adicionais SET VA_BLOCK=$block WHERE VA_COD ='$cod'", $conexao_params, $conexao_options);
			$mensagem = 'Adicional '.$block_texto;
		break;

		case 'excluir':
			$sql_update = sqlsrv_query($conexao, "UPDATE vendas_adicionais SET D_E_L_E_T_=1 WHERE VA_COD='$cod'", $conexao_params, $conexao_options);
			$mensagem = 'Adicional excluído';
		break;				
	}

	
	?>
	<script type="text/javascript">
		alert('<? echo $mensagem; ?>');
		location.href='<? echo SITE; ?>ingressos/adicionais/';
	</script>
	<?

	//Fechar conexoes
	include("conn/close.php");
	
	exit();
}

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente.');
	history.go(-1);
</script>