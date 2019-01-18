<?

include("include/includes.php");

echo $_SESSION['reload'];


$pagamento_cod = (int) $_GET['c'];

$loja_cod = (int) $_GET['l'];


$sql_up = sqlsrv_query($conexao, "DELETE from loja_pagamento_multiplo WHERE PM_COD=$pagamento_cod", $conexao_params, $conexao_options);

$sql_update = sqlsrv_query($conexao, "UPDATE loja SET LO_PAGO=0, LO_DATA_PAGAMENTO='' WHERE LO_COD='$loja_cod'", $conexao_params, $conexao_options);

// echo '<script type="text/javascript">
// 	alert("Excluido com sucesso!");
// 	window.location.href="https://ingressos.foliatropical.com.br/controle2014/compras/pagamento-multiplo/'.$loja_cod.'/";</script>';

header("Location: https://ingressos.foliatropical.com.br/controle2014/compras/pagamento-multiplo/$loja_cod");

//header("Location: ".$_SERVER['HTTP_REFERER']);



?>





