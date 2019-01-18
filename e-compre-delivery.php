<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE.$link_lang; ?>';
</script>
<?
	exit();
}

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$transfer = (bool) $_POST['transfer'];
$detalhes = (bool) $_POST['detalhes'];
$cep = format($_POST['cep']);
$endereco = format($_POST['endereco']);
$numero = format($_POST['numero']);
$complemento = format($_POST['complemento']);
$bairro = format($_POST['bairro']);
$cidade = format($_POST['cidade']);
$estado = format($_POST['estado']);

//-----------------------------------------------------------------------------//

$sql_delivery = sqlsrv_query($conexao, "SELECT TOP 1 DE_COD FROM delivery WHERE DE_BAIRRO='$bairro' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
if(sqlsrv_num_rows($sql_delivery) == 0) {
?>
<script type="text/javascript">
	alert('Infelizmente não entregamos para o endereço selecionado');
	history.go(-1);
</script>
<?
	exit();
}

if(!empty($cod) && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($bairro) && !empty($cidade) && !empty($estado)) {
	
	// Atualizar
	$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_CLI_ENDERECO='$endereco', LO_CLI_NUMERO='$numero', LO_CLI_COMPLEMENTO='$complemento', LO_CLI_BAIRRO='$bairro', LO_CLI_CIDADE='$cidade', LO_CLI_ESTADO='$estado', LO_CLI_CEP='$cep' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);

	$link_proximo = SITE.$link_lang.'ingressos/pagamento/'.$cod.'/';
	if($transfer) $link_proximo = SITE.$link_lang.'ingressos/agendamento/'.$cod.'/';
	if($detalhes) $link_proximo = SITE.$link_lang.'minhas-compras/detalhes/'.$cod.'/';

	?>
	<script type="text/javascript">
		alert('Endereço confirmado.');
		location.href='<? echo $link_proximo; ?>';
	</script>
	<?

	//fechar conexao com o banco
	include("conn/close.php");
	include("conn/close-mssql.php");
	include("conn/close-sankhya.php");
	
	exit();

}

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>