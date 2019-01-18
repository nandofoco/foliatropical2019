<?

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$acao = $_GET['a'];

switch ($acao) {
	case 'confirmar':
	case 'cancelar':
		$block = ($acao == 'confirmar') ? 1 : 0;
		$block_texto = ($block) ? 'confirmada' : 'cancelada';
		$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_ENVIADO='$block', LO_DATA_ENTREGA=GETDATE() WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
		$mensagem = 'Entrega '.$block_texto.' com sucesso';
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