<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$adm = ($_SESSION['us-grupo'] == 'ADM') ? true : false;

define('CODRESERVA','5');
define('CODPERMUTA','8,9');

//-----------------------------------------------------------------//


$dbprefix = ($_SERVER['SERVER_NAME'] == "server" || $_SERVER['SERVER_NAME'] == "localhost" || $_SERVER['SERVER_NAME'] == "192.168.1.120") ? 'foliatropical' : 'foliatropical2014';

// $search = " SELECT l.LI_COMPRA FROM [$dbprefix].[dbo].[loja_itens] l, vendas v WHERE v.VE_COD=l.LI_INGRESSO AND l.D_E_L_E_T_='0' " ;

include("include/relatorios-parametros.php");

$data = todate(format($_GET['data']), 'ddmmaaaa');
$tipo = $_GET['tipo'];
$dia = (int) $_GET['dia'];
$setor = (int) $_GET['setor'];
$fila = format($_GET['fila']);
$acao = format($_GET['a']);

$search = "";
if(!empty($filtros['tipos'][$tipo])) $search .= " AND ".$filtros['tipos'][$tipo];
if(!empty($dia)) $search .= " AND VE_DIA=".$dia;

$search_acao = " AND CONVERT(DATE, l.LO_DATA_COMPRA) = '$data' ";
$search_acao_graf = " AND CONVERT(DATE, lo.LO_DATA_COMPRA) = '$data' ";


$relatorio_exel = array();

$html = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
<Workbook xmlns:x="urn:schemas-microsoft-com:office:excel"
  xmlns="urn:schemas-microsoft-com:office:spreadsheet"
  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">

    <Styles>
        <Style ss:ID="Header">
            <Font ss:Bold="1" />
        </Style>
        <Style ss:ID="Default" />
        <Style ss:ID="Date" ss:Name="Date">
            <NumberFormat ss:Format="Short Date" />
        </Style>
        <Style ss:ID="DateTime">
            <NumberFormat ss:Format="General Date" />
        </Style>
        <Style ss:ID="Time">
            <NumberFormat ss:Format="Long Time" />
        </Style>
        <Style ss:ID="Weight">
            <NumberFormat ss:Format="0.000" />
        </Style>
        <Style ss:ID="Currency">
            <NumberFormat ss:Format="Currency" />
        </Style>
        <Style ss:ID="Percent">
			<NumberFormat ss:Format="0.0%"/>
		</Style>
        <Style ss:ID="Hyperlink">
            <Font ss:Color="#0000FF" ss:Underline="Single" />
        </Style>
    </Styles>
';

$html .= '<Worksheet ss:Name="Relatório Movimentação Diária" ss:Description="Relatório Movimentação Diária"><ss:Table>';

$html .= '<ss:Row>';
$html .= '<ss:Cell><Data ss:Type="String">Vendas realizadas no dia '.$_GET['data'].'</Data></ss:Cell>' . "\n";
$html .= '</ss:Row>';

$html .= '<ss:Row ss:StyleID="Header">';
$html .= '<ss:Cell><Data ss:Type="String">Dia</Data></ss:Cell>' . "\n";
$html .= '<ss:Cell><Data ss:Type="String">Tipo Ingresso</Data></ss:Cell>' . "\n";
$html .= '<ss:Cell><Data ss:Type="String">Vendas (%)</Data></ss:Cell>' . "\n";
$html .= '<ss:Cell><Data ss:Type="String">Qtd</Data></ss:Cell>' . "\n";
$html .= '<ss:Cell><Data ss:Type="String">Total (R$)</Data></ss:Cell>' . "\n";
$html .= '</ss:Row>';



foreach ($filtros['tipos'] as $t => $tipos) {

	foreach ($filtros['modalidade'] as $m => $modalidade) {
		$query_itens_tipos .= " SUM(CASE WHEN $tipos THEN $modalidade ELSE 0 END) AS ".$m."_".$t.", ";
	}

	// Busca pelos valores por tipo
	foreach ($filtros['dias'] as $d => $dias) {
		foreach ($filtros['modalidade'] as $m => $modalidade) {
			$query_itens_dias .= " SUM(CASE WHEN $tipos AND $dias THEN $modalidade ELSE 0 END) AS ".$m."_".$t."_".$d.", ";
		}
	}
}


//conta o total de itens vendidos e pagos
$sql_grafico = sqlsrv_query($conexao, "SELECT 
	$query_itens_tipos
	$query_itens_dias
	MAX(lo.LO_COD) AS LO_COD

	FROM loja_itens li, vendas ve, loja lo
	LEFT JOIN taxa_cartao tx 
		ON (lo.LO_FORMA_PAGAMENTO=1 AND lo.LO_CARTAO=tx.TX_CARTAO AND lo.LO_PARCELAS >= tx.TX_PARCELAS_INICIO AND lo.LO_PARCELAS <= tx.TX_PARCELAS_FIM)
		OR (lo.LO_FORMA_PAGAMENTO=6 AND tx.TX_CARTAO='pos')

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
	$search_acao_graf
	$search

	", $conexao_params, $conexao_options);

$ar_grafico = sqlsrv_fetch_array($sql_grafico, SQLSRV_FETCH_ASSOC);


include("relatorio_movimentacao_diaria.php");




foreach ($relatorio_exel as $key => $venda) 
{	
	$html .= '<ss:Row>';
	$html .= '<ss:Cell><Data ss:Type="String">'.$venda['dia'].'</Data></ss:Cell>' . "\n";
	$html .= '<ss:Cell><Data ss:Type="String">'.$venda['tipo'].'</Data></ss:Cell>' . "\n";
	$html .= '<ss:Cell><Data ss:Type="String">'.$venda['porcentagem'].'</Data></ss:Cell>' . "\n";
	$html .= '<ss:Cell><Data ss:Type="String">'.$venda['qtd'].'</Data></ss:Cell>' . "\n";
	$html .= '<ss:Cell><Data ss:Type="String">'.$venda['valor'].'</Data></ss:Cell>' . "\n";
	$html .= '</ss:Row>';	
}

$html .= '</ss:Table></Worksheet>'; 
$html .= '</Workbook>';	

$nome_export = 'Relatorio_Mov_Diaria.xls';

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");
header("Content-type: application/excel");
header("Content-Disposition: attachment; filename=\"{$nome_export}\"");
header("Content-Description: PHP Generated Data");

echo $html;
exit();