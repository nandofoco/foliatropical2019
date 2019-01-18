<?

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$editar = $_POST['editar'];
$status = format($_POST['status']);
$data_solicitacao = empty($_POST['data-solicitacao']) ? "NULL" : "'".todate($_POST['data-solicitacao'], "ddmmaaaa")."'";
$data_conclusao = empty($_POST['data-conclusao']) ? "NULL" : "'".todate($_POST['data-conclusao'], "ddmmaaaa")."'";
$nome = format($_POST['nome']);
$via = format($_POST['via']);
$email = format($_POST['email']);
$telefone = format($_POST['telefone']);
$atendente = format($_POST['atendente']);
$assunto = format($_POST['assunto']);
$mensagem = format($_POST['mensagem']);
$setor = format($_POST['setor']);
$responsavel = format($_POST['responsavel']);
$solucao = format($_POST['solucao']);

//-----------------------------------------------------------------------------//

$resposta = "Ocorreu um erro, tente novamente.";
$erro = false;

//-----------------------------------------------------------------------------//

//Email invalido	
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){	
	$erro = true;
	$_SESSION['ALERT'] = array('erro',"Por favor digite um e-mail válido");	
}

//-----------------------------------------------------------------------------//

if(!$erro && !$editar) {
// if(!$erro && !$editar && !empty($nome) && !empty($pessoa) && !empty($email) && !empty($telefone)) {
	
	$sql_insert = mysql_query("INSERT INTO contato (CO_NOME, CO_VIA, CO_EMAIL, CO_TELEFONE, CO_MENSAGEM, CO_DATA, CO_DATA_CONCLUSAO, CO_ASSUNTO, CO_SETOR, CO_ATENDENTE, CO_RESPONSAVEL, CO_STATUS, CO_SOLUCAO, CO_DATA_STATUS, CO_STATUS) VALUES ('$nome','$via','$email','$telefone','$mensagem',$data_solicitacao,$data_conclusao,'$assunto','$setor','$atendente','$responsavel','$status','$solucao',NOW(),'$status')")or(mysql_error());		
	$cod = mysql_insert_id();

	if(!empty($cod)) {		
		$_SESSION['ALERT'] = array('sucesso',"Atendimento cadastrado com sucesso.");
	}

} else if(!$erro && $editar && is_numeric($cod)) {

	$sql_update = mysql_query("UPDATE contato SET CO_NOME='$nome', CO_VIA='$via', CO_RESPONSAVEL='$responsavel', CO_ATENDENTE='$atendente', CO_EMAIL='$email', CO_TELEFONE='$telefone', CO_MENSAGEM='$mensagem', CO_DATA=$data_solicitacao, CO_DATA_CONCLUSAO=$data_conclusao, CO_ASSUNTO='$assunto', CO_SETOR='$setor', CO_STATUS='$status', CO_SOLUCAO='$solucao', CO_DATA_STATUS=NOW() WHERE CO_COD=$cod")or die(mysql_error());
	$_SESSION['ALERT'] = array('sucesso',"Atendimento alterado com sucesso.");

}else{
	$_SESSION['ALERT'] = array('aviso',$resposta);
}

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");


header("Location: ".$_SERVER['HTTP_REFERER']);