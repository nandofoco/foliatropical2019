<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$pendencias = $_POST['pendencias'];

//-----------------------------------------------------------------------------//

if(!empty($cod)) {

	// Apagar
	$sql_update = sqlsrv_query($conexao, "DELETE FROM loja_pendencias WHERE LP_COMPRA='$cod'", $conexao_params, $conexao_options);
	
	foreach ($pendencias as $value) {
		if($value > 0) $sql_ins = sqlsrv_query($conexao, "INSERT INTO loja_pendencias (LP_COMPRA ,LP_PENDENCIA) VALUES ('$cod', '$value')", $conexao_params, $conexao_options);
	}

	?>
	<script type="text/javascript">
		alert('Pendências atualizadas.');
		location.href='<? echo SITE; ?>financeiro/detalhes/<? echo $cod; ?>/';
	</script>
	<?

	//Fechar conexoes
	include("conn/close.php");
	
	exit();

}

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>