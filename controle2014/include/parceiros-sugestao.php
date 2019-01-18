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
$i = 0;
$resposta = array();

$q = format($_GET['b']);

if(!empty($q)) 
{

	$search = is_numeric($q) ? " AND CODPARC='$q' " : " AND (NOMEPARC LIKE '%$q%' OR EMAIL LIKE '%$q%' OR CGC_CPF LIKE '%$q%') ";

	$sql_parceiros = sqlsrv_query($conexao_sankhya, "SELECT TOP 25 CODPARC, NOMEPARC, CGC_CPF, EMAIL FROM TGFPAR WHERE CLIENTE='S' AND BLOQUEAR='N' $search ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);

	
	$n = sqlsrv_num_rows($sql_parceiros);


	if($n > 0) 
	{

		while($parceiro = sqlsrv_fetch_array($sql_parceiros)) 
		{
			
			//$parceiro_cod = trim($parceiro['CODPARC']);
			$parceiro_nome = utf8_encode(trim($parceiro['NOMEPARC']));
			//$parceiro_email = utf8_encode(trim($parceiro['EMAIL']));
			$parceiro_cpf = formatCPFCNPJ(trim($parceiro['CGC_CPF']));

			//$resposta .= '<li><a href="#" rel="'.$cliente_cod.','.$cliente_nome.'">'.$cliente_nome.' &ndash; '.$cliente_cpf.'</a></li>';

			//$resposta[$i]['cod'] = $parceiro_cod;
			//$resposta[$i]['nome'] = $parceiro_nome;
			//$resposta[$i]['cpf'] = $parceiro_cpf;

			$resposta[$i] = $parceiro_nome . " - " . $parceiro_cpf;

			$i++;

			
		}

		// if($total > $n) 
		// {
		// 	$restantes =  $total - $n;
		// 	$plural = ($restantes > 1) ? "s" : "";
		// 	$resposta .= '<li class="mais">Mais '.$restantes.' registro'.$plural.' encontrado'.$plural.'</li>';
		// }
	}

	$sucesso = true;
}

echo json_encode(array("sucesso"=>$sucesso, "encontradas"=>$n, "resposta"=>$resposta));

// echo json_encode(array("sucesso"=>$sucesso, "resposta"=>$resposta));

// echo json_encode($resposta);

//Fechar conexoes
include("../conn/close.php");
include("../conn/close-sankhya.php");

?>