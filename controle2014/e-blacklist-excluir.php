<?
//-----------------------------------------------------------------------------//

include("include/includes.php");

//-----------------------------------------------------------------------------//


$cod = $_GET['c'];



if(!empty($cod)) 
{

 	$sql_delete = sqlsrv_query($conexao, "DELETE FROM loja_blacklist WHERE LB_COD='$cod'", $conexao_params, $conexao_options);


	if ($sql_delete)
	{
		echo "<script>alert('Excluído com sucesso!');</script>";
		echo "<script>location.href='https://ingressos.foliatropical.com.br/controle2014/blacklist/';</script>";
	}

	else
	{
		echo "<script>alert('Ocorreu um erro! Tente novamente');</script>";
		echo "<script>location.href='https://ingressos.foliatropical.com.br/controle2014/blacklist/';</script>";		
	}
}
else
{
	echo "<script>alert('Ocorreu um erro! Tente novamente');</script>";
	echo "<script>location.href='https://ingressos.foliatropical.com.br/controle2014/blacklist/';</script>";
}
