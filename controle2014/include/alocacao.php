<?

define('PGINCLUDE', 'true');

//Verificamos o dominio
include("checkwww.php");

//Banco de dados
include("../conn/conn.php");

// Checar usuario logado
include("checklogado.php");

//Incluir funções básicas
include("funcoes.php");

//Incluir função para url amigável
include("toascii.php");


header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

$acao = format($_POST['acao']);
$compra = (int) $_POST['compra'];
$lugar = (int) $_POST['lugar'];

$sucesso = false;

if(!empty($acao) && !empty($compra) && !empty($lugar)) {

	switch ($acao) {
		case 'marcar':			
			
			// Marcar
			$sql_marcar = sqlsrv_query($conexao, "INSERT INTO alocacao (AL_ITEM, AL_LUGAR, AL_DATA) VALUES ('$compra', '$lugar', GETDATE())", $conexao_params, $conexao_options);
			if(getLastId() > 0) {

				$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) loja_itens SET LI_ALOCADO=1 WHERE LI_COD='$compra' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
				$sucesso = true;

			}			

		break;

		case 'alterar':

			// Marcar
			$sql_marcar = sqlsrv_query($conexao, "UPDATE alocacao SET AL_LUGAR='$lugar', AL_DATA=GETDATE() WHERE AL_ITEM='$compra' AND AL_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);

			// if(sqlsrv_rows_affected($sql_marcar) > 0) {
				$sql_update = sqlsrv_query($conexao, "UPDATE loja_itens SET LI_ALOCADO=1 WHERE LI_COD='$compra' AND D_E_L_E_T_=0 LIMIT 1", $conexao_params, $conexao_options);
				$sucesso = true;
			// }

		break;

		case 'desalocar':
			
			$sucesso = false;

			// Marcar
			// $sql_marcar = sqlsrv_query($conexao, "DELETE FROM alocacao SET D_E_L_E_T_=1, AL_DATA=GETDATE() WHERE AL_LUGAR='$lugar' AND AL_ITEM='$compra' AND AL_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
			$sql_marcar = sqlsrv_query($conexao, "DELETE TOP (1) FROM alocacao WHERE AL_LUGAR='$lugar' AND AL_ITEM='$compra' AND AL_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
			// if(sqlsrv_rows_affected($sql_marcar) > 0) {
				$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) loja_itens SET LI_ALOCADO=0 WHERE LI_COD='$compra' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
				$sucesso = true;
			// }

		break;
		
	}

}

echo json_encode(array('sucesso' => $sucesso));

//Fechar conexoes
include("../conn/close.php");

?>