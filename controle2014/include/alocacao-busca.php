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

$tipo = (int) $_POST['tipo'];
$setor = (int) $_POST['setor'];
$dia = (int) $_POST['dia'];
$fila_nivel = format($_POST['fila-nivel']);
$q = format($_POST['q']);

$evento = (int) $_SESSION['usuario-carnaval'];
$n_ingressos = 0;

if(!empty($evento) && !empty($tipo) && !empty($setor) && !empty($dia)  && !empty($q)) {

	$sucesso = true;
	
	if($tipo == 1) $search_tipo = " AND v.VE_TIPO_ESPECIFICO='numerada'";

	//Buscar itens e compras
	$sql_ingressos = sqlsrv_query($conexao, "SELECT l.LO_COD FROM loja l, loja_itens li WHERE li.LI_COMPRA=l.LO_COD AND li.LI_INGRESSO IN 
		(SELECT VE_COD FROM vendas WHERE VE_EVENTO='$evento' AND VE_TIPO='$tipo' AND VE_SETOR='$setor' AND VE_DIA='$dia' AND VE_BLOCK=0 AND D_E_L_E_T_=0)
		AND (li.LI_NOME LIKE '%$q%' OR l.LO_CLI_NOME LIKE '%$q%' OR l.LO_COD LIKE '%$q%') AND li.LI_ALOCADO=0 AND li.D_E_L_E_T_=0 GROUP BY l.LO_COD", $conexao_params, $conexao_options);

	
	$n_ingressos = sqlsrv_num_rows($sql_ingressos);
	if($n_ingressos > 0) {

		$ar_ingressos = array();

		while($ingressos = sqlsrv_fetch_array($sql_ingressos)){

			$ingressos_cod = $ingressos['LO_COD'];

		    array_push($ar_ingressos, $ingressos_cod);
		}

	}

}

echo json_encode(array("sucesso"=>$sucesso, "quantidade"=>$n_ingressos, "ingressos"=>$ar_ingressos));

//Fechar conexoes
include("../conn/close.php");

?>