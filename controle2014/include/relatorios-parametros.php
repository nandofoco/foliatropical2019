<?

//busca folia tropical
$sql_folia = sqlsrv_query($conexao, "SELECT TOP 1 ES_COD FROM eventos_setores WHERE ES_EVENTO='$evento' AND ES_NOME='FT'", $conexao_params, $conexao_options);

$ar_folia = sqlsrv_fetch_array($sql_folia);
$setor_folia = $ar_folia['ES_COD'];

// Conta o total de ingressos disponiveis
// O objetivo do relatório é contar os itens vendidos (pagos e não pagos) da tabela LOJA_ITENS e comparar com a quantidade no estoque da tabela VENDA. A contagem de estoque e itens na tabela COMPRA serve apenas para exibição no gráfico geral de vendas, sem fazer parte da porcentagem.

// Agrupa 2 queries acima para contar ingressos disponiveis e ingressos a venda

// Definição de todos os parâmetros para a busca de ingressos, agrupados por quantidade e valor / tipos de ingresso / formas de pagamento etc.
// Como são muitos filtros, os parâmetros das buscas serão agrupados para que seja possível repeti-los várias vezes

// Tipos possíveis

// Pagos
// Pagamento Posterior
// Cortesias
// Permutas
// Reservas
// Aguardando Pagamento
// Total Saída (Vendidos)

// 5	Reserva 	reserva
// 6	Cartão POS	cartao-pos
// 8	Cortesia	cortesia
// 9	Permuta		permuta
$filtros['compras']['tipos']['arquibancadas']  = " CO_TIPO='1' ";
$filtros['compras']['tipos']['frisas']         = " CO_TIPO='2' ";
$filtros['compras']['tipos']['camarotes']      = " CO_TIPO='3' ";
#$filtros['compras']['tipos']['cadeiras']       = " CO_TIPO='5' ";
$filtros['compras']['tipos']['folia']          = " CO_TIPO='4' AND CO_SETOR='$setor_folia' ";
$filtros['compras']['tipos']['super']          = " CO_TIPO='6' ";
$filtros['compras']['tipos']['lounges']        = " CO_TIPO IN ('4','6') ";


$filtros['tipos']['arquibancadas']  = " VE_TIPO='1' ";
$filtros['tipos']['frisas']         = " VE_TIPO='2' ";
$filtros['tipos']['camarotes']      = " VE_TIPO='3' ";
#$filtros['tipos']['cadeiras']       = " VE_TIPO='5' ";
$filtros['tipos']['folia']          = " VE_TIPO='4' AND VE_SETOR='$setor_folia' ";
$filtros['tipos']['super']          = " VE_TIPO='6' ";
$filtros['tipos']['lounges']        = " VE_TIPO IN ('4','6') ";

$filtros['status']['pagos'] 		= " lo.LO_PAGO='1' AND lo.LO_FORMA_PAGAMENTO NOT IN (5,8,9,14,2013) ";
$filtros['status']['posterior'] 	= " lo.LO_FORMA_PAGAMENTO='14' ";
$filtros['status']['cortesias'] 	= " lo.LO_FORMA_PAGAMENTO='8' ";
$filtros['status']['promoter'] 		= " lo.LO_FORMA_PAGAMENTO='2013' ";
$filtros['status']['permutas'] 		= " lo.LO_FORMA_PAGAMENTO='9' ";
$filtros['status']['reservas'] 		= " lo.LO_FORMA_PAGAMENTO='5' ";
$filtros['status']['aguardando'] 	= " lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO NOT IN (5,6,8,9,14,2013) ";
// $filtros['status']['saida'] 		= " ((lo.LO_PAGO='1' OR lo.LO_FORMA_PAGAMENTO IN (5,6,8,9)) OR (lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO NOT IN (5,6,8,9))) ";
$filtros['status']['saida'] 		= " (((lo.LO_PAGO='1' AND lo.LO_FORMA_PAGAMENTO NOT IN (8,9,2013)) OR lo.LO_FORMA_PAGAMENTO IN (5,14)) OR (lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO NOT IN (5,6,8,9,2013))) ";

$filtros['modalidade']['valor'] 	= " (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) - ISNULL(CASE WHEN tx.TX_TAXA IS NOT NULL THEN li.LI_VALOR * (tx.TX_TAXA / 100) ELSE 0 END, 0)  ";
$filtros['modalidade']['qtde'] 		= " 1 ";

//busca dias do carnaval
$sql_dias = sqlsrv_query($conexao, "SELECT ED_COD, ED_NOME, CONVERT(CHAR(5), ED_DATA, 103) AS data FROM eventos_dias WHERE ED_EVENTO='$evento' ORDER BY ED_NOME ASC", $conexao_params, $conexao_options);
$n_dias = sqlsrv_num_rows($sql_dias);

$dias = array();

if($n_dias > 0) {

	$idia = 1;
	while ($eventos_dias = sqlsrv_fetch_array($sql_dias)) {
		$dias_cod = $eventos_dias['ED_COD'];
		$dias_nome = utf8_encode($eventos_dias['ED_NOME']);
		$dias_data = $eventos_dias['data'];
		
		$filtros['dias'][$dias_cod] = " VE_DIA='$dias_cod' ";

		$dias[$dias_cod]['titulo'] = $dias_nome;
		$dias[$dias_cod]['legenda'] = $dias_data;
	}
}

// Títulos
$conf = array(
	// Tipos
	'tipos' => array(
		'arquibancadas' => array(
			'titulo' => 'Arquibancadas',
			'link' => false,
			'valor' => false
		),
		'frisas' => array(
			'titulo' => 'Frisas',
			'link' => false,
			'valor' => false
		),
		'camarotes' => array(
			'titulo' => 'Camarotes',
			'link' => false,
			'valor' => false
		),
		'folia' => array(
			'titulo' => 'Folia Tropical',
			'link' => false,
			'valor' => false,
			'cortesia' => true
		),
		'super' => array(
			'titulo' => 'Super Folia',
			'link' => false,
			'valor' => false,
			'cortesia' => true
		),
		'lounges' => array(
			'titulo' => 'Folia + Super Folia',
			'link' => false,
			'valor' => false,
			'cortesia' => true
		)
	),

	// Status
	'status' => array(
		'pagos' => array(
			'titulo' => 'Pagos',
			'link' => true,
			'valor' => true
		),
		'reservas' => array(
			'titulo' => 'Reservas',
			'link' => true,
			'valor' => true
		),
		'posterior' => array(
			'titulo' => 'Pgto. Posterior',
			'link' => true,
			'valor' => true
		),
		'aguardando' => array(
			'titulo' => 'Aguardando Pgto. Site',
			'link' => false,
			'valor' => true
		),
		'cortesias' => array(
			'titulo' => 'Cortesias',
			'link' => true,
			'valor' => false
		),
		'promoter' => array(
			'titulo' => 'Cortesias Promoter',
			'link' => true,
			'valor' => false
		),
		'saida' => array(
			'titulo' => 'Total Saída Vendas',
			'link' => false,
			'valor' => true
		),
		'permutas' => array(
			'titulo' => 'Permutas',
			'link' => true,
			'valor' => false
		)
	),

	'dias' => $dias
);


?>