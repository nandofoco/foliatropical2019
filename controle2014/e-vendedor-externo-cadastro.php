<?


//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$editar = (bool) $_POST['editar'];
$parceiro = (int) $_POST['parceiro'];
$nome = format($_POST['nome']);
$email = format($_POST['email']);
$telefone = format($_POST['telefone']);

$resposta = "Ocorreu um erro, tente novamente.";
$erro = false;

//-----------------------------------------------------------------------------//

//Email invalido	
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){	
	$erro = true;
	$resposta = "Por favor digite um e-mail válido";	
}

//-----------------------------------------------------------------------------//

if(is_numeric($cod) && $editar) $search_cod = " AND VE_COD<>'$cod' ";

//Email já cadastrado
// $sql_exist = sqlsrv_query($conexao, "SELECT VE_EMAIL FROM vendedor_externo WHERE VE_EMAIL='$email' $search_cod", $conexao_params, $conexao_options);
// $n = sqlsrv_num_rows($sql_exist);

// if(!$erro && ($n > 0)){
// 	$erro = true;
// 	$resposta = "O e-mail informado já foi cadastrado";
// }

//-----------------------------------------------------------------------------//

if(!$erro && !$editar && !empty($nome) && !empty($email) && !empty($telefone) && !empty($parceiro)) {
	
	$sql_insert = sqlsrv_query($conexao, "INSERT INTO vendedor_externo (VE_PARCEIRO, VE_NOME, VE_EMAIL, VE_TEL) VALUES ('$parceiro', '$nome', '$email', '$telefone')", $conexao_params, $conexao_options);
	$cod = getLastId();

	if(!empty($cod)) {

		$resposta = "Vendedor externo cadastrado com sucesso.";

		?>
		<script type="text/javascript">
			alert('<? echo $resposta; ?>');
			location.href='<? echo SITE; ?>vendedor-externo/';
		</script>
		<?

		exit();
	}

} elseif(!$erro && $editar && is_numeric($cod) && !empty($nome) && !empty($email) && !empty($telefone)) {

	$sql_insert = sqlsrv_query($conexao, "UPDATE TOP(1) vendedor_externo SET VE_PARCEIRO='$parceiro', VE_NOME='$nome', VE_EMAIL='$email', VE_TEL='$telefone' WHERE VE_COD='$cod'", $conexao_params, $conexao_options);
	
	$resposta = "Vendedor externo alterado com sucesso.";

	?>
	<script type="text/javascript">
		alert('<? echo $resposta; ?>');
		location.href='<? echo SITE; ?>vendedor-externo/editar/<? echo $cod; ?>/';
	</script>
	<?

	//Fechar conexoes
	include("conn/close.php");

	exit();
}
?>
<script type="text/javascript">
	alert('<? echo $resposta; ?>');
	location.href='<? echo SITE; ?>vendedor-externo/';
</script>
