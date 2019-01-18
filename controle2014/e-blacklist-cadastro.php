<?
//-----------------------------------------------------------------------------//

include("include/includes.php");

//-----------------------------------------------------------------------------//

$cliente = format($_POST['cliente']);
$cpf = format($_POST['cpf']);
$num_cartao = format($_POST['cartao']);

$cpf = str_replace('-', '', $cpf);
$cpf = str_replace('.', '', $cpf);
$num_cartao = str_replace('.', '', $num_cartao);




if(!empty($cliente) && !empty($cpf) && !empty($num_cartao)) 
{
	$sql_insert = sqlsrv_query($conexao, "INSERT INTO loja_blacklist (LB_USUARIO, LB_CPF, LB_CARTAO) VALUES ('$cliente','$cpf','$num_cartao')", $conexao_params, $conexao_options);

	if ($sql_insert)
	{
		echo "<script>alert('Inserido com sucesso!')</script>";
		echo "<script>history.go(-1);</script>";
	}
	else
	{
		echo "<script>alert('Ocorreu um erro! Tente novamente')</script>";
		echo "<script>history.go(-1);</script>";
	}
		
}
else
{
	echo "<script>alert('Ocorreu um erro! Tente novamente')</script>";
	echo "<script>history.go(-1);</script>";	
}
