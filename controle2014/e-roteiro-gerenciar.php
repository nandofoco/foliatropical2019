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
		$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) roteiros SET RO_BLOCK=$block WHERE RO_COD='$cod'", $conexao_params, $conexao_options);
		$mensagem = 'Roteiro '.$block_texto;
	break;

	case 'deletar':
		$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) roteiros SET D_E_L_E_T_='1' WHERE RO_COD='$cod'", $conexao_params, $conexao_options);
		$sql_update_itens = sqlsrv_query($conexao, "UPDATE transportes SET D_E_L_E_T_='1' WHERE TR_ROTEIRO='$cod'", $conexao_params, $conexao_options);
		$sql_busca_itens = sqlsrv_query($conexao, "SELECT * FROM transportes WHERE TR_ROTEIRO='$cod'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_busca_itens) > 0) {
			while ($itens = sqlsrv_fetch_array($sql_busca_itens)) {
				$itens_cod = $itens['TR_COD'];
				$sql_update_horarios = sqlsrv_query($conexao, "UPDATE transportes_horarios SET D_E_L_E_T_='1' WHERE TH_TRANSPORTE='$itens_cod'", $conexao_params, $conexao_options);
			}
		}
		$mensagem = 'Roteiro deletado com sucesso';
	break;

	case 'exc-item':
		$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) transportes SET D_E_L_E_T_='1' WHERE TR_COD='$cod'", $conexao_params, $conexao_options);
		$sql_update_horarios = sqlsrv_query($conexao, "UPDATE transportes_horarios SET D_E_L_E_T_='1' WHERE TH_TRANSPORTE='$cod'", $conexao_params, $conexao_options);

		$mensagem = 'Item deletado com sucesso';

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