<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$transfer = format($_POST['transfer']);
$multiplo = format($_POST['multiplo']);
$detalhes = (int) $_POST['detalhes'];
$periodo = format($_POST['periodo']);
$data = todate(format($_POST['data']), 'ddmmaaaa');
$celular = format($_POST['celular']);
$cep = format($_POST['cep']);
$endereco = format($_POST['endereco']);
$numero = (int) $_POST['numero'];
$complemento = format($_POST['complemento']);
$bairro = format($_POST['bairro']);
$referencia = format($_POST['referencia']);
$cuidados = format($_POST['cuidados']);

//-----------------------------------------------------------------------------//

if(!empty($cod) && !empty($data) && !empty($endereco) && !empty($numero) && !empty($bairro) && !empty($periodo)) {
	
	// Atualizar
	//$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_CLI_ENDERECO='$endereco', LO_CLI_NUMERO='$numero', LO_CLI_COMPLEMENTO='$complemento', LO_CLI_BAIRRO='$bairro', LO_CLI_CIDADE='$cidade', LO_CLI_ESTADO='$estado', LO_CLI_CEP='$cep', LO_CLI_PERIODO='$periodo', LO_CLI_DATA_ENTREGA='$data', LO_CLI_CUIDADOS='$cuidados', LO_CLI_CELULAR='$celular', LO_CLI_PONTO_REFERENCIA='$referencia' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
	$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_CLI_ENDERECO='$endereco', LO_CLI_NUMERO='$numero', LO_CLI_COMPLEMENTO='$complemento', LO_CLI_BAIRRO='$bairro', LO_CLI_CIDADE='$cidade', LO_CLI_ESTADO='$estado', LO_CLI_PERIODO='$periodo', LO_CLI_DATA_ENTREGA='$data', LO_CLI_CUIDADOS='$cuidados', LO_CLI_CELULAR='$celular', LO_CLI_PONTO_REFERENCIA='$referencia' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);

	$link_proximo = SITE.'financeiro/pendentes/';
	if($transfer) $link_proximo = SITE.'agendamentos/editar/'.$cod.'/';	
	if($detalhes) $link_proximo = SITE.'financeiro/detalhes/'.$cod.'/';

	if($transfer && $multiplo) $link_proximo .= 'multiplo/';
	if(!$transfer && $multiplo) $link_proximo = SITE.'compras/pagamento-multiplo/'.$cod.'/';

	?>
	<script type="text/javascript">
		alert('Endereço confirmado.');
		location.href='<? echo $link_proximo; ?>';
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