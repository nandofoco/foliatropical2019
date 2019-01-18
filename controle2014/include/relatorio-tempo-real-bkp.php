<?
session_start();

include("../conn/conn.php");

header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

include("relatorios-parametros.php");

$sucesso = false;

$evento = (int) $_SESSION['usuario-carnaval'];

global $conexao, $conexao_params, $conexao_options;

$tipo = $_POST['tipo'];
$dia = $_POST['dia'];

if(!empty($tipo)) $search_tipos = "AND".$tipo;
if(!empty($dia)) $search_dias = "AND ve.VE_DIA=".$dia;

// $query_dias .= "SUM(CASE WHEN data='".$dia_atual."' THEN valor_dia ELSE 0 END) AS valor_dia_atual,";
// $query_dias .= "SUM(CASE WHEN data='".$dia_atual."' THEN qtde_dia ELSE 0 END) AS qtde_dia_atual,";
// $query_dias .= "'".$dia_atual."' AS dia_atual,";

$dia_atual = date('d/m/Y');

$query_itens = " 
	COUNT(li.LI_COD) AS qtde_dia_atual, 
	SUM(".$filtros['modalidade']['valor'].") AS valor_dia_atual
	 ";
	
	$query_itens_status = "";
	// Busca pelos valores gerais
	foreach ($filtros['status'] as $s => $status) {	
		foreach ($filtros['modalidade'] as $m => $modalidade) {
			$query_itens_status .= " SUM(CASE WHEN $status THEN $modalidade ELSE 0 END) AS ".$m."_".$s.", ";
		}
	}


	$sql_relatorio_dias = sqlsrv_query($conexao, "SELECT

	$query_itens_status
	$query_itens
	

	FROM  loja_itens li, vendas ve, loja lo
	LEFT JOIN taxa_cartao tx
		ON (lo.LO_FORMA_PAGAMENTO=1 AND lo.LO_CARTAO=tx.TX_CARTAO AND lo.LO_PARCELAS >= tx.TX_PARCELAS_INICIO AND lo.LO_PARCELAS <= tx.TX_PARCELAS_FIM)
		OR (lo.LO_FORMA_PAGAMENTO=6 AND tx.TX_CARTAO='pos')
		OR (lo.LO_FORMA_PAGAMENTO=2014 AND tx.TX_CARTAO='pos')

	WHERE lo.LO_COD=li.LI_COMPRA
	AND li.D_E_L_E_T_='0'
	AND lo.LO_BLOCK='0'
	AND lo.D_E_L_E_T_='0'
	AND li.LI_INGRESSO=ve.VE_COD
	AND ve.VE_EVENTO='$evento'
	AND ve.VE_BLOCK='0'
	AND ve.D_E_L_E_T_='0'
	AND lo.LO_EVENTO='$evento'
	AND lo.LO_FORMA_PAGAMENTO NOT IN (8,9)
	AND CONVERT(VARCHAR, lo.LO_DATA_COMPRA, 103) = '$dia_atual'
	$search_tipos
	$search_dias	
		
	", $conexao_params, $conexao_options);

	echo "SELECT

	$query_itens_status
	$query_itens
	

	FROM  loja_itens li, vendas ve, loja lo
	LEFT JOIN taxa_cartao tx
		ON (lo.LO_FORMA_PAGAMENTO=1 AND lo.LO_CARTAO=tx.TX_CARTAO AND lo.LO_PARCELAS >= tx.TX_PARCELAS_INICIO AND lo.LO_PARCELAS <= tx.TX_PARCELAS_FIM)
		OR (lo.LO_FORMA_PAGAMENTO=6 AND tx.TX_CARTAO='pos')
		OR (lo.LO_FORMA_PAGAMENTO=2014 AND tx.TX_CARTAO='pos')

	WHERE lo.LO_COD=li.LI_COMPRA
	AND li.D_E_L_E_T_='0'
	AND lo.LO_BLOCK='0'
	AND lo.D_E_L_E_T_='0'
	AND li.LI_INGRESSO=ve.VE_COD
	AND ve.VE_EVENTO='$evento'
	AND ve.VE_BLOCK='0'
	AND ve.D_E_L_E_T_='0'
	AND lo.LO_EVENTO='$evento'
	AND lo.LO_FORMA_PAGAMENTO NOT IN (8,9)
	AND CONVERT(VARCHAR, lo.LO_DATA_COMPRA, 103) = '$dia_atual'
	$search_tipos
	$search_dias	
		
	";

	$ar_relatorio_dias = sqlsrv_fetch_array($sql_relatorio_dias, SQLSRV_FETCH_ASSOC);

	$valor_pagos = number_format($ar_relatorio_dias['valor_pagos'], 2, ',', '.');
    $valor_posterior = number_format($ar_relatorio_dias['valor_posterior'], 2, ',', '.');
    $valor_cortesias = number_format($ar_relatorio_dias['valor_cortesias'], 2, ',', '.');
    $valor_permutas = number_format($ar_relatorio_dias['valor_permutas'], 2, ',', '.');
    $valor_reservas = number_format($ar_relatorio_dias['valor_reservas'], 2, ',', '.');
    $valor_aguardando = number_format($ar_relatorio_dias['valor_aguardando'], 2, ',', '.');
    $valor_saida = number_format($ar_relatorio_dias['valor_saida'], 2, ',', '.');
    $valor_dia_atual = number_format($ar_relatorio_dias['valor_dia_atual'], 2, ',', '.');

    $qtde_pagos = (int) $ar_relatorio_dias['qtde_pagos'];
    $qtde_posterior = (int) $ar_relatorio_dias['qtde_posterior'];
    $qtde_cortesias = (int) $ar_relatorio_dias['qtde_cortesias'];
    $qtde_permutas = (int) $ar_relatorio_dias['qtde_permutas'];
    $qtde_reservas = (int) $ar_relatorio_dias['qtde_reservas'];
    $qtde_aguardando = (int) $ar_relatorio_dias['qtde_aguardando'];
    $qtde_saida = (int) $ar_relatorio_dias['qtde_saida'];
    $qtde_dia_atual = (int) $ar_relatorio_dias['qtde_dia_atual'];
	

	$sucesso = true;

	$resposta = array(
		"sucesso" => $sucesso, 
		"valor_pagos" => $valor_pagos,
		"valor_posterior" => $valor_posterior,
		"valor_cortesias" => $valor_cortesias,
		"valor_permutas" => $valor_permutas,
		"valor_reservas" => $valor_reservas,
		"valor_aguardando" => $valor_aguardando,
		"valor_saida" => $valor_saida,
		"valor_atual" => $valor_dia_atual,

		"qtde_pagos" => $qtde_pagos,
		"qtde_posterior" => $qtde_posterior,
		"qtde_cortesias" => $qtde_cortesias,
		"qtde_permutas" => $qtde_permutas,
		"qtde_reservas" => $qtde_reservas,
		"qtde_aguardando" => $qtde_aguardando,
		"qtde_saida" => $qtde_saida,
		"qtde_atual" => $qtde_dia_atual,

		"data" => $dia_atual);


#} else {
#	$sucesso = false;
#}

echo json_encode($resposta);

//Fechar conexoes
include("../conn/close.php");

?>