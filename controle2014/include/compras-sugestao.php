<?

session_start();

include("../conn/conn.php");
include("../conn/conn-sankhya.php");

header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

include("funcoes.php");

$sucesso = false;
$n = 0;
$resposta;

$q = format($_GET['b']);


if(!empty($q)) {

	// $search = " AND (CL_COD LIKE '%$q%' OR CL_EMAIL LIKE '%$q%' OR CL_NOME LIKE '%$q%' OR CL_CPF LIKE '%$q%' OR CL_RG LIKE '%$q%') ";	
	$search = is_numeric($q) ? " AND CODPARC='$q' " : " AND (NOMEPARC LIKE '%$q%' OR EMAIL LIKE '%$q%' OR CGC_CPF LIKE '%$q%') ";

	$sql_total = sqlsrv_query($conexao_sankhya, "SELECT CODPARC, NOMEPARC, CGC_CPF, EMAIL FROM TGFPAR WHERE CLIENTE='S' AND BLOQUEAR='N' $search ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
	$total = sqlsrv_num_rows($sql_total);

	$sql_clientes = sqlsrv_query($conexao_sankhya, "SELECT TOP 25 CODPARC, NOMEPARC, CGC_CPF, EMAIL FROM TGFPAR WHERE CLIENTE='S' AND BLOQUEAR='N' $search ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
	$n = sqlsrv_num_rows($sql_clientes);

	if($n > 0) {

		while($cliente = sqlsrv_fetch_array($sql_clientes)) {
			
			$cliente_cod = trim($cliente['CODPARC']);
			$cliente_nome = utf8_encode(trim($cliente['NOMEPARC']));
			$cliente_email = utf8_encode(trim($cliente['EMAIL']));
			$cliente_cpf = formatCPFCNPJ(trim($cliente['CGC_CPF']));

			$resposta .= '<li><a href="#" rel="'.$cliente_cod.','.$cliente_nome.'">'.$cliente_nome.' &ndash; '.$cliente_cpf.'</a></li>';
		}

		if($total > $n) {
			$restantes =  $total - $n;
			$plural = ($restantes > 1) ? "s" : "";
			$resposta .= '<li class="mais">Mais '.$restantes.' registro'.$plural.' encontrado'.$plural.'</li>';
		}
	}

	$sucesso = true;
	
}

echo json_encode(array("sucesso"=>$sucesso, "encontradas"=>$n, "resposta"=>$resposta));

//Fechar conexoes
include("../conn/close.php");
include("../conn/close-sankhya.php");

?>