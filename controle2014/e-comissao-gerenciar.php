<?

//Verificamos o dominio
include("include/includes.php");

header('Content-Type: text/html; charset=utf-8');

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$acao = $_GET['a'];
$tipo = $_GET['t'];

switch ($tipo) {

	case 'paga':
		switch ($acao) {
			case 'confirmar':
			case 'cancelar':
				$block = ($acao == 'confirmar') ? 1 : 0;
				$block_texto = ($block) ? 'paga' : 'não paga';
				$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_COMISSAO_PAGA='$block' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
				$mensagem = 'Comissão '.$block_texto;
			break;	
		}
	break;

	case 'retida':
	default:
		switch ($acao) {
			case 'confirmar':
			case 'cancelar':
				$block = ($acao == 'confirmar') ? 1 : 0;
				$block_texto = ($block) ? 'retida' : 'não retida';
				$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_COMISSAO_RETIDA='$block' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
				$mensagem = 'Comissão '.$block_texto;
			break;	
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