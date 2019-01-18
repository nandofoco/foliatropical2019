<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$adm = ($_SESSION['us-grupo'] == 'ADM') ? true : false;

$dbprefix = ($_SERVER['SERVER_NAME'] == "server" || $_SERVER['SERVER_NAME'] == "localhost" || $_SERVER['SERVER_NAME'] == "192.168.1.120") ? 'foliatropical' : 'foliatropical2014';

//busca nome do evento
$sql_evento = sqlsrv_query($conexao, "SELECT TOP 1 EV_NOME FROM eventos WHERE EV_COD='$evento'", $conexao_params, $conexao_options);

$ar_evento = sqlsrv_fetch_array($sql_evento);
$evento_nome = $ar_evento['EV_NOME'];

include("include/relatorios-parametros.php");

$sql_ingressos = sqlsrv_query($conexao, "DECLARE @ingressos TABLE (ingressos_compra_valor FLOAT, ingressos_compra INT, ingressos_compra_estoque INT, ingressos_venda INT, arquibancadas INT, frisas INT, camarotes INT, lounges INT, super INT, folia INT);

	INSERT INTO @ingressos (ingressos_compra_valor, ingressos_compra, ingressos_compra_estoque, ingressos_venda, arquibancadas, frisas, camarotes, lounges, super, folia)
	SELECT 
	ISNULL((SELECT SUM(VE_VALOR) FROM vendas WHERE VE_EVENTO='$evento' AND VE_BLOCK='0' AND D_E_L_E_T_='0'), 0) as ingressos_compra_valor, 
	ISNULL((SELECT COUNT(CO_COD) FROM compras WHERE CO_EVENTO='$evento' AND CO_BLOCK='0' AND D_E_L_E_T_='0' AND CO_ESTOQUE IS NULL), 0) as ingressos_compra, 
	ISNULL((SELECT SUM(CO_ESTOQUE) FROM compras WHERE CO_EVENTO='$evento' AND CO_BLOCK='0' AND D_E_L_E_T_='0' AND CO_ESTOQUE IS NOT NULL), 0) as ingressos_compra_estoque, 
	ISNULL((SELECT SUM(VE_ESTOQUE) FROM vendas WHERE VE_EVENTO='$evento' AND VE_BLOCK='0' AND D_E_L_E_T_='0'), 0) as ingressos_venda, 
	ISNULL((SELECT SUM(VE_ESTOQUE) FROM vendas WHERE VE_EVENTO='$evento' AND ".$filtros['tipos']['arquibancadas']." AND D_E_L_E_T_='0'), 0) as arquibancadas, 
	ISNULL((SELECT SUM(VE_ESTOQUE) FROM vendas WHERE VE_EVENTO='$evento' AND ".$filtros['tipos']['frisas']." AND D_E_L_E_T_='0'), 0) as frisas, 
	ISNULL((SELECT SUM(VE_ESTOQUE) FROM vendas WHERE VE_EVENTO='$evento' AND ".$filtros['tipos']['camarotes']." AND D_E_L_E_T_='0'), 0) as camarotes, 
	ISNULL((SELECT SUM(VE_ESTOQUE) FROM vendas WHERE VE_EVENTO='$evento' AND ".$filtros['tipos']['lounges']." AND D_E_L_E_T_='0'), 0) as lounges, 
	ISNULL((SELECT SUM(VE_ESTOQUE) FROM vendas WHERE VE_EVENTO='$evento' AND ".$filtros['tipos']['super']." AND D_E_L_E_T_='0'), 0) as super, 
	ISNULL((SELECT SUM(VE_ESTOQUE) FROM vendas WHERE VE_EVENTO='$evento' AND ".$filtros['tipos']['folia']."  AND D_E_L_E_T_='0'), 0) as folia;

	SELECT *, ISNULL((ingressos_compra+ingressos_compra_estoque),0) as total_ingressos_compra  FROM @ingressos;", $conexao_params, $conexao_options);

if(sqlsrv_next_result($sql_ingressos)) $ar_ingressos = sqlsrv_fetch_array($sql_ingressos);

// print_r($ar_ingressos);
$ingressos_compra_valor = (int) $ar_ingressos['ingressos_compra_valor'];
$qtde_ingressos_compra = (int) $ar_ingressos['ingressos_compra_estoque'];
$qtde_ingressos_venda = (int) $ar_ingressos['ingressos_venda'];

$query_itens = " MAX(lo.LO_COD) AS LO_COD, 
	MAX(li.LI_COD) AS LI_COD,
	MAX(li.LI_INGRESSO) AS LI_INGRESSO, 
	COUNT(li.LI_COD) AS qtde_vendidos, 
	MAX(ve.VE_TIPO) VE_TIPO, 
	SUM(".$filtros['modalidade']['valor'].") AS valor_total,
	SUM(CASE WHEN lo.LO_PAGO='1' AND lo.LO_ENVIADO='1' THEN ".$filtros['modalidade']['valor']." ELSE 0 END) AS valor_enviados,
	SUM(CASE WHEN lo.LO_PAGO='1' AND lo.LO_ENTREGUE='1' THEN ".$filtros['modalidade']['valor']." ELSE 0 END) AS valor_entregues,
	SUM(CASE WHEN lo.LO_PAGO='1' AND lo.LO_ENVIADO='1' THEN 1 ELSE 0 END) AS qtde_enviados,
	SUM(CASE WHEN lo.LO_PAGO='1' AND lo.LO_ENTREGUE='1' THEN 1 ELSE 0 END) AS qtde_entregues ";

$query_itens_status = $query_itens_tipos = "";

// Busca pelos valores gerais
foreach ($filtros['status'] as $s => $status) {	
	foreach ($filtros['modalidade'] as $m => $modalidade) {
		$query_itens_status .= " SUM(CASE WHEN $status THEN $modalidade ELSE 0 END) AS ".$m."_".$s.", ";
	}
}

// Busca pelos valores por tipo
foreach ($filtros['tipos'] as $t => $tipos) {

	foreach ($filtros['modalidade'] as $m => $modalidade) { $query_itens_tipos .= " SUM(CASE WHEN $tipos THEN $modalidade ELSE 0 END) AS ".$m."_".$t.", "; }

	foreach ($filtros['status'] as $s => $status) {	
		foreach ($filtros['modalidade'] as $m => $modalidade) {
			$query_itens_tipos .= " SUM(CASE WHEN $status AND $tipos THEN $modalidade ELSE 0 END) AS ".$m."_".$t."_".$s.", ";
		}
	}
}

//conta o total de itens vendidos e pagos
$sql_relatorio = sqlsrv_query($conexao, "SELECT 
	$query_itens_status
	$query_itens_tipos
	$query_itens

	FROM loja_itens li, vendas ve, loja lo
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

	", $conexao_params, $conexao_options);
	
$ar_relatorio = sqlsrv_fetch_array($sql_relatorio, SQLSRV_FETCH_ASSOC);

//array dos últimos 6 dias
$lista_dias = array();

for($i=2; $i<8; $i++) {
	$add_dia = strtotime(date('Y-m-d') . ' -'.($i-1).' day');
	$dia = date("Y-m-d",$add_dia);

	array_push($lista_dias, "'$dia'");
}

$lista_dias = implode(", ", $lista_dias);

//busca dados por dia
$sql_loja = sqlsrv_query($conexao, "DECLARE @vendas TABLE (VE_COD INT, VE_TIPO INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT, VE_FILA VARCHAR(255), VE_VAGAS INT, VE_TIPO_ESPECIFICO VARCHAR(255));

	INSERT INTO @vendas (VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO)
	SELECT VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO FROM [$dbprefix].[dbo].[vendas] WHERE VE_BLOCK=0 AND D_E_L_E_T_=0;

	DECLARE @loja TABLE (LO_COD INT, LO_CLIENTE INT, LO_PARCEIRO INT, LO_VENDEDOR INT, LO_ORIGEM VARCHAR(50), LO_DATA_COMPRA DATETIME, LO_VALOR_TOTAL FLOAT, LO_VALOR_PARCIAL FLOAT, LO_VALOR_ITEM FLOAT, LO_VALOR_ITEM_TABELA FLOAT, LO_VALOR_INGRESSOS FLOAT, LO_VALOR_ADICIONAIS FLOAT, LO_VALOR_TRANSFER FLOAT, LO_VALOR_DELIVERY FLOAT, LO_VALOR_DESCONTO FLOAT, LO_VALOR_OVER_INTERNO FLOAT, LO_VALOR_OVER_EXTERNO FLOAT, LO_COMISSAO INT, LO_COMISSAO_RETIDA TINYINT, LO_COMISSAO_PAGA TINYINT, LO_PAGO TINYINT, LO_DESCONTO_FOLIA TINYINT, LO_DESCONTO_FRISA TINYINT, LO_TID VARCHAR(255), LO_FORMA_PAGAMENTO INT, LI_INGRESSO INT, QTDE INT, ITENS INT, TAXA FLOAT);
	INSERT INTO @loja (LO_COD, LO_CLIENTE, LO_PARCEIRO, LO_VENDEDOR, LO_ORIGEM, LO_DATA_COMPRA, LO_VALOR_TOTAL, LO_VALOR_PARCIAL, LO_VALOR_ITEM, LO_VALOR_ITEM_TABELA, LO_VALOR_INGRESSOS, LO_VALOR_ADICIONAIS, LO_VALOR_TRANSFER, LO_VALOR_DELIVERY, LO_VALOR_DESCONTO, LO_VALOR_OVER_INTERNO, LO_VALOR_OVER_EXTERNO, LO_COMISSAO, LO_COMISSAO_RETIDA, LO_COMISSAO_PAGA, LO_PAGO, LO_DESCONTO_FOLIA, LO_DESCONTO_FRISA, LO_TID, LO_FORMA_PAGAMENTO, LI_INGRESSO, QTDE, ITENS, TAXA)

	SELECT 
		l.LO_COD,
		l.LO_CLIENTE,
		MAX(l.LO_PARCEIRO),
		MAX(l.LO_VENDEDOR),
		MAX(l.LO_ORIGEM),
		MAX(l.LO_DATA_COMPRA),
		MAX(l.LO_VALOR_TOTAL),
		MAX(l.LO_VALOR_PARCIAL),
		li.LI_VALOR,
		li.LI_VALOR_TABELA,
		MAX(l.LO_VALOR_INGRESSOS),
		li.LI_VALOR_ADICIONAIS,
		li.LI_VALOR_TRANSFER,
		MAX(l.LO_VALOR_DELIVERY),
		li.LI_DESCONTO,
		li.LI_OVER_INTERNO,
		li.LI_OVER_EXTERNO,
		MAX(l.LO_COMISSAO),
		MAX(l.LO_COMISSAO_RETIDA),
		MAX(l.LO_COMISSAO_PAGA),
		MAX(l.LO_PAGO),
		MAX(l.LO_DESCONTO_FOLIA),
		MAX(l.LO_DESCONTO_FRISA),
		MAX(l.LO_TID),
		MAX(l.LO_FORMA_PAGAMENTO),
		li.LI_INGRESSO,
		COUNT (li.LI_COD),
		MAX(l.LO_NUM_ITENS),
		MAX(ISNULL(CASE WHEN tx.TX_TAXA IS NOT NULL THEN li.LI_VALOR * (tx.TX_TAXA / 100) ELSE 0 END,0))
		
	FROM [$dbprefix].[dbo].[loja_itens] li, [$dbprefix].[dbo].[loja] l

	LEFT JOIN [$dbprefix].[dbo].[taxa_cartao] tx ON l.LO_FORMA_PAGAMENTO=1 AND l.LO_CARTAO=tx.TX_CARTAO AND l.LO_PARCELAS >= tx.TX_PARCELAS_INICIO AND l.LO_PARCELAS <= tx.TX_PARCELAS_FIM

	WHERE l.LO_EVENTO='$evento' AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 AND l.LO_FORMA_PAGAMENTO NOT IN (8,9) AND CONVERT(DATE, l.LO_DATA_COMPRA) IN ($lista_dias)
	GROUP BY li.LI_INGRESSO, l.LO_COD, l.LO_CLIENTE, li.LI_VALOR, li.LI_VALOR_TABELA, li.LI_VALOR_TRANSFER, li.LI_VALOR_ADICIONAIS, li.LI_DESCONTO, li.LI_OVER_INTERNO, li.LI_OVER_EXTERNO;

	DECLARE @folia TABLE (LO_COD INT, DESCONTO FLOAT);
	INSERT INTO @folia (LO_COD, DESCONTO)
	SELECT COD, DESCONTO FROM (
		SELECT
		COD,
		CASE 
			WHEN (DATA <= '2016-01-20' AND PRIMEIRA > 0 AND SEGUNDA > 0) THEN 20 
			WHEN (DATA <= '2016-01-20' AND PRIMEIRA > 0 AND TERCEIRA > 0) THEN 21 
			WHEN (DATA <= '2016-01-20' AND SEGUNDA > 0 AND TERCEIRA > 0) THEN 21 
			WHEN (DATA <= '2016-01-20' AND PRIMEIRA > 0 AND SEGUNDA > 0 AND TERCEIRA > 0) THEN 26.5 

			WHEN (DATA > '2016-01-20' AND PRIMEIRA > 0 AND SEGUNDA > 0) THEN 10
			WHEN (DATA > '2016-01-20' AND PRIMEIRA > 0 AND TERCEIRA > 0) THEN 10
			WHEN (DATA > '2016-01-20' AND SEGUNDA > 0 AND TERCEIRA > 0) THEN 10
			WHEN (DATA > '2016-01-20' AND PRIMEIRA > 0 AND SEGUNDA > 0 AND TERCEIRA > 0) THEN 10

			ELSE 0 
		END AS DESCONTO
		FROM (
		
			SELECT
			l.LO_COD AS COD,
			MAX(l.LO_DATA_COMPRA) AS DATA,
			SUM(CASE WHEN d.ED_DATA='2015-02-15' OR d.ED_DATA='2016-02-07' THEN 1 ELSE 0 END) AS PRIMEIRA,
			SUM(CASE WHEN d.ED_DATA='2015-02-16' OR d.ED_DATA='2016-02-08' THEN 1 ELSE 0 END) AS SEGUNDA,
			SUM(CASE WHEN d.ED_DATA='2015-02-21' OR d.ED_DATA='2016-02-13' THEN 1 ELSE 0 END) AS TERCEIRA

			FROM [$dbprefix].[dbo].[loja] l, [$dbprefix].[dbo].[loja_itens] li 
			LEFT JOIN @vendas v ON v.VE_COD = li.LI_INGRESSO
			LEFT JOIN tipos t ON t.TI_COD=v.VE_TIPO
			LEFT JOIN eventos_dias d ON d.ED_COD=v.VE_DIA
			WHERE l.LO_EVENTO='$evento' AND l.LO_PARCEIRO=54 AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 AND t.TI_TAG='lounge'
			GROUP BY l.LO_COD
		) S
	) S WHERE DESCONTO > 0;


	DECLARE @parceiros TABLE (CODPARC INT, NOMEPARC VARCHAR(255));

	INSERT INTO @parceiros (CODPARC, NOMEPARC)
	SELECT CODPARC, NOMEPARC FROM [parceiros].[dbo].[TGFPAR] WHERE VENDEDOR='S';

	DECLARE @busca TABLE (VOUCHER INT, VENDEDOR_INTERNO VARCHAR(255), PARCEIRO VARCHAR(255), VALOR_TABELA FLOAT, VALOR_DESCONTO FLOAT, QTDE INT, VALOR_TOTAL_VENDA FLOAT, VALOR_ITEM FLOAT, VALOR_COMISSAO FLOAT, LUCRO FLOAT, TIPO VARCHAR(50), DATA VARCHAR(50), PAGO VARCHAR(10), FORMA_PAGAMENTO VARCHAR(50), DATA_COMPRA DATETIME, VAGAS INT, ESPECIFICO VARCHAR(50));
	INSERT INTO @busca (VOUCHER, VENDEDOR_INTERNO, PARCEIRO, VALOR_TABELA, VALOR_DESCONTO, QTDE, VALOR_TOTAL_VENDA, VALOR_ITEM, VALOR_COMISSAO, LUCRO, TIPO, DATA, PAGO, FORMA_PAGAMENTO, DATA_COMPRA, VAGAS, ESPECIFICO)

	SELECT

	VOUCHER,
	VENDEDOR_INTERNO,
	PARCEIRO,
	VALOR_TABELA,
	VALOR_DESCONTO,
	QTDE,
	VALOR_TOTAL_VENDA,
	VALOR_ITEM,
	VALOR_COMISSAO,
	(VALOR_TOTAL_CALCULO_COMISSAO - VALOR_COMISSAO - TAXA),
	TIPO,
	DATA,
	PAGO,
	FORMA_PAGAMENTO,
	DATA_COMPRA,
	VAGAS,
	ESPECIFICO

	FROM (

		SELECT
		*,
		
		(PRECO_AUTORIZADO * QTDE) AS VALOR_TOTAL_CALCULO_COMISSAO,
		((PRECO_PRATICADO * QTDE) + ADICIONAIS) AS VALOR_TOTAL_VENDA,
		((PRECO_AUTORIZADO * QTDE) * COMISSAO / 100) AS VALOR_COMISSAO
		FROM (
			SELECT
			*,		
			(VALOR_TABELA - VALOR_DESCONTO + OVER_INTERNO) AS PRECO_AUTORIZADO,
			(VALOR_TABELA - VALOR_DESCONTO + OVER_INTERNO + OVER_EXTERNO) AS PRECO_PRATICADO
			FROM (
				
				SELECT 
				l.LO_COD AS VOUCHER,
				l.LO_ORIGEM AS VENDEDOR_INTERNO,
				p.NOMEPARC AS PARCEIRO,
				l.LO_VALOR_ITEM_TABELA AS VALOR_TABELA,
				l.LO_VALOR_OVER_INTERNO AS OVER_INTERNO,
				l.LO_VALOR_OVER_EXTERNO AS OVER_EXTERNO,
				l.QTDE AS QTDE,
				l.ITENS AS ITENS,
				l.LO_VALOR_ADICIONAIS AS ADICIONAIS,
				l.LO_VALOR_TRANSFER AS TRANSFER_UNITARIO,
				l.LO_VALOR_DELIVERY AS DELIVERY_TOTAL,
				l.LO_COMISSAO AS COMISSAO,
				l.LO_VALOR_ITEM AS VALOR_ITEM,

				CASE WHEN t.TI_NOME='Lounge' THEN 'Folia Tropical' ELSE t.TI_NOME END AS TIPO,
				ISNULL(v.VE_TIPO_ESPECIFICO,'') AS ESPECIFICO,
				CONVERT(VARCHAR, d.ED_DATA, 103) AS DATA,
				s.ES_NOME AS SETOR,
				ISNULL(v.VE_FILA,'') AS FILA,
				ISNULL(v.VE_VAGAS,'') AS VAGAS,
				CASE WHEN l.LO_PAGO=1 THEN 'PAGO' ELSE '' END AS PAGO,

				CASE WHEN l.LO_COMISSAO_RETIDA=1 THEN 'SIM' ELSE '' END AS COMISSAO_RETIDA,
				CASE WHEN l.LO_COMISSAO_PAGA=1 THEN 'SIM' ELSE '' END AS COMISSAO_PAGA,
				
				f.FP_NOME AS FORMA_PAGAMENTO,
				l.LO_TID AS TID,
				l.LO_DATA_COMPRA AS DATA_COMPRA,
				d.ED_DATA,
				l.TAXA,

				CASE 
					WHEN ((l.LO_DATA_COMPRA < '2015-10-15') OR (l.LO_DESCONTO_FRISA = 1)) AND (t.TI_TAG='frisa' AND (FLOOR(l.QTDE / 6) > 0)) THEN l.LO_VALOR_DESCONTO + (CAST((FLOOR(l.QTDE / 6) * 50 ) AS FLOAT) / CAST(l.QTDE AS FLOAT))
					WHEN ((l.LO_DATA_COMPRA < '2015-10-15') OR (l.LO_DESCONTO_FOLIA = 1)) AND (fo.DESCONTO > 0) THEN l.LO_VALOR_DESCONTO + (((CASE WHEN (l.LO_COD<=2639) THEN 10 ELSE fo.DESCONTO END / 100.00) * ((l.LO_VALOR_ITEM_TABELA - l.LO_VALOR_DESCONTO + l.LO_VALOR_OVER_INTERNO + l.LO_VALOR_OVER_EXTERNO) * l.QTDE)) / l.QTDE)
					WHEN cp.CP_COD IS NOT NULL AND cp.CP_TIPO=1 THEN l.LO_VALOR_DESCONTO + (((cp.CP_DESCONTO / 100.00) * ((l.LO_VALOR_ITEM_TABELA - l.LO_VALOR_DESCONTO + l.LO_VALOR_OVER_INTERNO + l.LO_VALOR_OVER_EXTERNO) * l.QTDE)) / l.QTDE) 
					WHEN cp.CP_COD IS NOT NULL AND cp.CP_TIPO=2 THEN l.LO_VALOR_DESCONTO + (cp.CP_DESCONTO / l.QTDE)
					ELSE l.LO_VALOR_DESCONTO 
				END AS VALOR_DESCONTO,

				CASE 
					WHEN ((l.LO_DATA_COMPRA < '2015-10-15') OR (l.LO_DESCONTO_FRISA = 1)) AND (t.TI_TAG='frisa' AND (FLOOR(l.QTDE / 6) > 0)) THEN 'Frisa fechada'
					WHEN ((l.LO_DATA_COMPRA < '2015-10-15') OR (l.LO_DESCONTO_FOLIA = 1)) AND (fo.DESCONTO > 0) THEN CONCAT('Combo Folia ', FLOOR(CASE WHEN (l.LO_COD<=2639) THEN 10 ELSE fo.DESCONTO END), '% de desconto')
					WHEN cp.CP_COD IS NOT NULL AND cp.CP_TIPO=1 THEN CONCAT('Cupom ', FLOOR(cp.CP_DESCONTO), '% de desconto')
					WHEN cp.CP_COD IS NOT NULL AND cp.CP_TIPO=2 THEN CONCAT('Cumpom R$ ', cp.CP_DESCONTO, ' de desconto')
					ELSE '' 
				END AS TIPO_DESCONTO

				FROM @loja l
				LEFT JOIN @vendas v ON v.VE_COD = l.LI_INGRESSO
				LEFT JOIN @parceiros p ON p.CODPARC = l.LO_PARCEIRO
				LEFT JOIN @folia fo ON fo.LO_COD = l.LO_COD
				LEFT JOIN [$dbprefix].[dbo].[tipos] t ON t.TI_COD=v.VE_TIPO
				LEFT JOIN [$dbprefix].[dbo].[eventos_dias] d ON d.ED_COD=v.VE_DIA
				LEFT JOIN [$dbprefix].[dbo].[eventos_setores] s ON s.ES_COD=v.VE_SETOR
				LEFT JOIN [$dbprefix].[dbo].[formas_pagamento] f ON f.FP_COD=l.LO_FORMA_PAGAMENTO
				LEFT JOIN [$dbprefix].[dbo].[cupom] cp ON cp.CP_COMPRA=l.LO_COD AND cp.CP_UTILIZADO=1 AND cp.CP_BLOCK=0 AND cp.D_E_L_E_T_=0

				
				WHERE d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0

			) S
		) S 
	) S ORDER BY TIPO ASC, ED_DATA ASC;



	DECLARE @PageNumber INT;
	DECLARE @PageSize INT;
	DECLARE @TotalPages INT;

	SET @PageSize = 10000;
	SET @PageNumber = 1;

	IF @PageNumber = 0 BEGIN
	SET @PageNumber = 1
	END;

	SET @TotalPages = CEILING(CONVERT(NUMERIC(20,10), ISNULL((SELECT COUNT(*) FROM @busca), 0)) / @PageSize);

	WITH cadastro(NumeroLinha, VOUCHER, VENDEDOR_INTERNO, PARCEIRO, VALOR_TABELA, VALOR_DESCONTO, QTDE, VALOR_TOTAL_VENDA, VALOR_ITEM, VALOR_COMISSAO, LUCRO, TIPO, DATA, PAGO, FORMA_PAGAMENTO, DATA_COMPRA, VAGAS, ESPECIFICO)
	AS (
	SELECT ROW_NUMBER() OVER (ORDER BY VOUCHER DESC) AS NumeroLinha,
	VOUCHER,
	VENDEDOR_INTERNO,
	PARCEIRO,
	VALOR_TABELA,
	VALOR_DESCONTO,
	QTDE,
	VALOR_TOTAL_VENDA,
	VALOR_ITEM,
	VALOR_COMISSAO,
	LUCRO,
	TIPO,
	DATA,
	PAGO,
	FORMA_PAGAMENTO, 
	DATA_COMPRA,
	VAGAS,
	ESPECIFICO

	FROM @busca 
	)

	SELECT @TotalPages AS TOTAL, @PageNumber AS PAGINA, NumeroLinha, VOUCHER, VENDEDOR_INTERNO, PARCEIRO, VALOR_TABELA, VALOR_DESCONTO, QTDE, VALOR_TOTAL_VENDA, VALOR_ITEM, VALOR_COMISSAO, LUCRO, TIPO, DATA, PAGO, FORMA_PAGAMENTO, DATA_COMPRA, VAGAS, ESPECIFICO
	FROM cadastro
	WHERE NumeroLinha BETWEEN ( ( ( @PageNumber - 1 ) * @PageSize ) + 1 ) AND ( @PageNumber * @PageSize )
	ORDER BY DATA_COMPRA DESC

    ", $conexao_params, $conexao_options);

//busca dias do carnaval
$sql_dias = sqlsrv_query($conexao, "SELECT ED_COD, ED_NOME FROM eventos_dias WHERE ED_EVENTO='$evento' ORDER BY ED_NOME ASC", $conexao_params, $conexao_options);
$n_dias = sqlsrv_num_rows($sql_dias);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Relatório de Vendas <span><? echo $evento_nome; ?></span></h1>

		<section class="selectbox" id="select-relatorio">
			<a href="#" class="arrow"><strong>Gerar relatório</strong><span></span></a>
			<ul class="drop">

				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/financeiro/">Financeiro</a></li>
				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/compras/">Compras</a></li>
				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/pendencias/">Pendências</a></li>
				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/comentarios/">Comentários</a></li>
				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/transportes/">Transportes</a></li>
				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/agendamentos-pendentes/">Agendamentos Pendentes</a></li>
				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/vendedores/">Vendedores</a></li>
				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/marketing/">Marketing</a></li>
				<li><a class="item" href="<? echo SITE; ?>relatorios/canais-venda/">Vendas por Tipo de Canais</a></li>
				<li><a class="item" href="<? echo SITE; ?>relatorios/canais-venda-fca/">Vendas por Tipo de Canais (Frisas, Arquibancadas e Camarotes)</a></li>

				<?
				/*$usuario = (int) $_SESSION['us-cod'];				
				if($usuario == 85 || $_GET['teste']) {*/

				if($adm) {

				?>
				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/teste-financeiro/">Teste - Financeiro</a></li>
				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/teste-pendencias/">Teste - Pendências</a></li>
				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/teste-comentarios/">Teste - Comentários</a></li>
				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/teste-agendamentos-pendentes/">Teste - Agendamentos Pendentes</a></li>
				<li><a class="item" target="_blank" href="<? echo SITE; ?>relatorios/exportar/teste-expedicao/">Teste - Expedição</a></li>
				<?
				}
				?>
			</ul>
			<div class="clear"></div>
		</section>

	</header>
	<section class="secao graficos completo">
		<?

		$qtde_vendidos = (int) $ar_relatorio['qtde_vendidos'];
		$valor_total = $ar_relatorio['valor_total'];
		$valor_enviados = $ar_relatorio['valor_enviados'];
		$valor_entregues = $ar_relatorio['valor_entregues'];
		$qtde_enviados = $ar_relatorio['qtde_enviados'];
		$qtde_entregues = $ar_relatorio['qtde_entregues'];

		// $qtde_ingressos_compra
		// $qtde_ingressos_venda

		
		$item_link = SITE.'relatorios-lista-voucher.php';
		/* <? echo $item_link; ?>?a=pagos */

		?>
		<div class="grafico horizontal">
			<? 

			// Função para organizar o grafico horizontal na mesma ordem que os indicadores abaixo

			$odd = 0;
			$status_grafico_impar = $status_grafico_par;

			foreach ($conf['status'] as $status => $r) {
				$odd ++;

				$status_titulo = $r['titulo'];			
				$status_valor = $ar_relatorio['valor_'.$status];
				$status_qtde = $ar_relatorio['qtde_'.$status];

				$status_porcentagem = ($status_valor > 0) ? (($status_valor*100) / $valor_total) : 0;
				#$status_porcentagem = ($status_qtde > 0) ? (($status_qtde*100) / $qtde_ingressos_venda) : 0;

				if($status != 'saida') {

					$status_grafico = '<span class="'.$status.'" style="width:'.$status_porcentagem.'%"></span>';
					
					switch ($odd % 2) {
						case 0: $status_grafico_par .= $status_grafico; break;
						case 1: $status_grafico_impar .= $status_grafico; break;
					}
				}
			}

			echo $status_grafico_impar.$status_grafico_par;

			?>
		</div>


		<div class="painel-colunas">
			<div class="colunas duas-colunas">
				<?

				foreach ($conf['status'] as $status => $r) {

					$status_titulo = $r['titulo'];
					$status_link = (bool) $r['link'];
					$status_exibe_valor = (bool) $r['valor'];

					if($status_link) {
						$status_tag = 'a';
						$status_link = 'href="'.$item_link.'?a='.$status.'" ';
					} else {
						$status_tag = 'div';
					}

					$status_valor = $ar_relatorio['valor_'.$status];
					$status_qtde = $ar_relatorio['qtde_'.$status];

					$status_porcentagem = ($status_valor > 0) ? (($status_valor*100) / $valor_total) : 0;
					#$status_porcentagem = ($status_qtde > 0) ? (($status_qtde*100) / $qtde_ingressos_venda) : 0;

				?>
				<<? echo $status_tag; ?> <? echo $status_link; ?> class="fancybox fancybox.iframe width1480 dados <? /*float*/ ?> <? echo $status ?>">
					<span></span>
					<h3><? echo $status_titulo; ?></h3>
					<p>
						Qtd.: <? echo $status_qtde; ?><br />
						<? if($adm && $status_exibe_valor) { ?>R$ <? echo number_format($status_valor, 2, ',', '.'); ?><br /><? } ?>
						Percentual: <? echo number_format(round($status_porcentagem, 2), 2, ',', '.') ?>%
					</p>
				</<? echo $status_tag; ?>>
				<?

				}

				// Entregues
				$valor_entregues = $ar_relatorio['valor_entregues'];		
				$qtde_entregues = $ar_relatorio['qtde_entregues'];
				
				?>
			</div>

			<div class="colunas uma-coluna">

				<div class="dados">
					<span></span>
					<h3>Disponível Para Vendas</h3>
					<p>
						Disponível: <? echo $qtde_ingressos_venda - $qtde_vendidos; ?><br />
						Lançados: <? echo $qtde_ingressos_venda; ?><br />
						Total Bruto: R$ <? echo number_format($ingressos_compra_valor, 2, ',', '.'); ?><br /> 
					</p>
				</div>

				<div class="dados">
					<span></span>
					<h3>Itens Comprados</h3>
					<p>
						Comprados: <? echo $qtde_ingressos_compra; ?><br />
						Lançados: <? echo $qtde_ingressos_venda; ?><br />
						Dif. Comprados / Lançados: <? echo $qtde_ingressos_compra - $qtde_ingressos_venda; ?>
					</p>
				</div>
				<?

				$entregues_porcentagem = ($qtde_entregues > 0) ? (($qtde_entregues*100) / $qtde_vendidos) : 0

				?>
				<div class="dados">
					<span></span>
					<h3>Itens Entregues</h3>
					<p>
						Qtde.: <? echo $qtde_entregues; ?><br />
						Percentual: <? echo number_format(round($entregues_porcentagem, 2), 2, ',', '.') ?>%
					</p>
				</div>

			</div>

			<div class="colunas uma-coluna">
				<a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php" class="fancybox fancybox.iframe width1480">Movimentação Diária<br/>Todos produtos/Todos os dias</a>
				<table>	
					<thead>
						<tr>
							<th>Data</th>
							<th>Qtd.</th>
							<th>Valor</th>
						</tr>
					</thead>
					<tbody>
						<tr id="tempo-real">
							
						</tr>
						<?
                        $rows = array();
                        while(sqlsrv_next_result($sql_loja)) {
                            
                            $irelatorio = 1;
                            while($relatorio = sqlsrv_fetch_array($sql_loja)) {

                                //Criamos um array da linha
                                $row = array();
                                foreach ($relatorio as $key => $value) {
                                    //if(!is_numeric($key)) array_push($row, $value);
                                    if(!is_numeric($key)) $row[$key] = $value;
                                }

                                //Adicionamos ao array das linhas

                                array_push($rows, $row);
                                unset($row);
                            }
                        }

                        function searchForData($data, $array) {
                            $keys = array();
                            foreach ($array as $key => $val) {
                                if ($val['DATA_COMPRA']->format('Y-m-d') === $data) {
                                    $keys[] = $key;
                                }
                            }
                            return $keys;
                        }

                        // print_r($rows);
                        $dias = array();

                        //percorrer os dias para gerar tabela
                        for($i=1; $i<7; $i++) {	                            
                            

                            $add_dia = strtotime(date('Y-m-d') . ' -'.($i-1).' day');
                            $dia = date("Y-m-d",$add_dia);
                            $dia_formatado = date("d/m/Y",$add_dia);

                            $keys = searchForData($dia, $rows);

                            foreach ($keys as $value) {

                                $loja_qtde = $rows[$value]['QTDE'];
                                $loja_valor_lucro = $rows[$value]['LUCRO'];
                                $item_vaga = utf8_encode($rows[$value]['VAGAS']);
                                $item_tipo_especifico = utf8_encode($rows[$value]['ESPECIFICO']);

                                $item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;

                                if($item_fechado) { 
                                    $loja_qtde = $loja_qtde/$item_vaga;
                                    $loja_valor_lucro = $loja_valor_lucro/$item_vaga;
                                } 

                                $dias[$i]['loja_valor_lucro'] += $loja_valor_lucro;
                                $dias[$i]['qtde'] += $loja_qtde;
                                $dias[$i]['data'] = $dia_formatado;

                            }

                        }

                        // print_r($dias);

						foreach ($dias as $line) {
                            $loja_qtde = $line['qtde'];
                            $loja_valor_lucro = $line['loja_valor_lucro'];
                            $loja_data = $line['data'];

                            $loja_valor_lucro = number_format($loja_valor_lucro, 2, ",", ".");
                            $loja_lucro = number_format($loja_lucro, 2, ",", ".");
						?>
						<tr>
							<td class="titulo"><? echo $loja_data; ?></td>
							<td><? echo $loja_qtde; ?></td>
							<td>R$ <? echo $loja_valor_lucro; ?></td>
						</tr>
						<?
						}
						?>
					</tbody>
				</table>
			</div>

			<div class="clear"></div>
		</div>

	</section>

	<section class="secao graficos">
		<?

		$itipos = 0;
		foreach ($conf['tipos'] as $tipo => $r) {

			$itipos++;

			$tipo_titulo = $r['titulo'];
			$tipo_link = $r['link'];
			$tipo_cortesia = (bool) $r['cortesia'];
			$tipo_exibe_valor = (bool) $r['valor'];

			if(!empty($tipo_link)) {
				$tipo_tag = 'a';
				$tipo_link = 'href="#"';
			} else {
				$tipo_tag = 'div';
			}
			
			// $tipo_valor = $ar_relatorio['valor_'.$tipo];
			// $tipo_qtde = $ar_relatorio['qtde_'.$tipo];
			
			//$tipo_total = $ar_relatorio['valor_'.$tipo];
			$tipo_total = $ar_ingressos[$tipo];

		?>
		<div class="coluna<? if($itipos > 3) { echo ' bottom'; } if(($itipos%3) == 0) { echo ' last'; } ?>">
			<div class="box <? echo $tipo; ?>">
				<h2><? echo $tipo_titulo; ?></h2>
				<div class="grafico vertical">
					<?

					$porcentagem_grafico = 0;

					/*if($qtde > 0) {
						$porcentagem_grafico = (($qtde_vendidos*100)/$qtde);
						$porcentagem_disponiveis = ((($qtde-$qtde_vendidos_reservados)*100)/$qtde);
						$porcentagem_aguardando = (($qtde_aguardando*100)/$qtde);
						$porcentagem_reservados = (($qtde_reservados*100)/$qtde);
						$porcentagem_permuta = (($qtde_permuta*100)/$qtde);
					}*/

					$qtde_grafico = $ar_relatorio['qtde_'.$tipo.'_pagos'];
					$porcentagem_grafico = ($qtde_grafico > 0) ? (($qtde_grafico*100) / $tipo_total) : 0;

					$tamanho_tag = (round($porcentagem_grafico) > 0) ? round((325*($porcentagem_grafico))/100): 0;
					$posicao_tag = (325-$tamanho_tag+(($tamanho_tag-50)/2));
					if($tamanho_tag < 95) $posicao_tag = 115;


					// Como a quantidade vendida já inclui a permuta, vamos retirá-la para exibir a quantidade paga
					// $qtde_vendidos = $qtde_vendidos - $qtde_permuta;
					
					?>
					<span style="height: <? echo $tamanho_tag."px"; ?>"></span>
					<p style="top: <? echo $posicao_tag."px"; ?>"><? echo round($porcentagem_grafico)."%"; ?></p>
				</div>
				
				<div class="float">
					<?

					// Disponiveis = total - vendidos
					$status_qtde = $ar_ingressos[$tipo] - $ar_relatorio['qtde_'.$tipo.'_saida'] - $ar_relatorio['qtde_'.$tipo.'_cortesias'] - $ar_relatorio['qtde_'.$tipo.'_permutas'] - $ar_relatorio['qtde_'.$tipo.'_'.$dia.'_promoter'];
					$status_porcentagem = ($status_qtde > 0) ? (($status_qtde*100) / $tipo_total) : 0;

					?>
					<div class="dados">
						<span></span>
						<h3><? echo (round($status_porcentagem)); ?>% Disponíveis</h3>
						<p><? echo ($status_qtde); echo ' ingressos'; ?></p>
					</div>
					<?

					foreach ($conf['status'] as $status => $r) {

						$status_titulo = $r['titulo'];
						$status_link = $r['link'];
						$status_exibe_valor = (bool) $r['valor'];

						if(!empty($status_link)) {
							$status_tag = 'a';
							$status_link = 'href="#"';
						} else {
							$status_tag = 'div';
						}
						
						$status_valor = $ar_relatorio['valor_'.$tipo.'_'.$status];
						$status_qtde = $ar_relatorio['qtde_'.$tipo.'_'.$status];

						// $status_porcentagem = ($status_valor > 0) ? (($status_valor*100) / $tipo_total) : 0;
						$status_porcentagem = ($status_qtde > 0) ? (($status_qtde*100) / $ar_ingressos[$tipo]) : 0;

						$status_exibe = true;

						
						// Para os ingressos de Folia e Superfolia a Cortesia nao é somada a Permuta, nos demais sim
						if(!$tipo_cortesia && $status == 'cortesias') $status_exibe = false; 
						if(!$tipo_cortesia && $status == 'permutas') {
							$status_valor += $ar_relatorio['valor_'.$tipo.'_cortesias'];
							$status_qtde += $ar_relatorio['qtde_'.$tipo.'_cortesias'];
						} 


						// if($status == 'saida') $status_exibe = false;
						if($status == 'permutas') $status_exibe = false;
						if($status == 'aguardando') $status_titulo = "Aguardando";
						if($status == 'saida') $status_titulo = "Saída Venda";


						if($status_exibe) {

						?>
						<div class="dados">
							<span></span>
							<h3><? echo (round($status_porcentagem)); ?>% <? echo $status_titulo; ?></h3>
							<p><? echo ($status_qtde); echo ($adm) ? ' - R$ '.number_format($status_valor, 2, ',', '.') : ' ingressos'; ?></p>
						</div>
						<?
							
						}
					}
					?>					
					<a href="<? echo SITE; ?>relatorios/detalhes/<? echo $tipo; ?>/">ver detalhes</a>
				</div>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
		<?

		}
		
		?>
		<div class="clear"></div>
	</section>
	<?
	if($n_dias > 0) {
	?>
		<section class="secao graficos">
			<ul>
				<?
				$i = 1;
				while ($eventos_dias = sqlsrv_fetch_array($sql_dias)) {
					$dias_cod = $eventos_dias['ED_COD'];
					$dias_nome = utf8_encode($eventos_dias['ED_NOME']);

					#SUM ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) AS comissao
					#SUM(CASE WHEN ve.VE_DIA='$dias_cod' THEN li.LI_VALOR ELSE 0 END) AS valor

					//calcula valor dos itens
					$sql_valor_dias = sqlsrv_query($conexao, "SELECT 
						/*SUM(CASE WHEN ve.VE_DIA='$dias_cod' THEN (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) ELSE 0 END) AS VALOR*/
						SUM(CASE WHEN ve.VE_DIA='$dias_cod' THEN (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) - ISNULL(CASE WHEN tx.TX_TAXA IS NOT NULL THEN li.LI_VALOR * (tx.TX_TAXA / 100) ELSE 0 END,0) ELSE 0 END) AS VALOR
						FROM loja_itens li, vendas ve, loja lo
						LEFT JOIN taxa_cartao tx 
							ON (lo.LO_FORMA_PAGAMENTO=1 AND lo.LO_CARTAO=tx.TX_CARTAO AND lo.LO_PARCELAS >= tx.TX_PARCELAS_INICIO AND lo.LO_PARCELAS <= tx.TX_PARCELAS_FIM)
							OR (lo.LO_FORMA_PAGAMENTO=6 AND tx.TX_CARTAO='pos')
							OR (lo.LO_FORMA_PAGAMENTO=2014 AND tx.TX_CARTAO='pos')
						WHERE lo.LO_COD=li.LI_COMPRA AND li.D_E_L_E_T_='0' AND lo.LO_BLOCK='0' AND lo.D_E_L_E_T_='0' AND li.LI_INGRESSO=ve.VE_COD AND ve.VE_EVENTO='$evento' AND ve.VE_BLOCK='0' AND ve.D_E_L_E_T_='0'", $conexao_params, $conexao_options);
					
					$ar_valor_dias = sqlsrv_fetch_array($sql_valor_dias);
					$valor_dia = $ar_valor_dias['VALOR'];
					?>
					<li <? if($i%6 == 0) echo 'class="last"'; ?>>
						<h1><? echo formatar_valor($valor_dia); ?></h1>
						<span></span>
						<h2><? echo $dias_nome." dia de carnaval"; ?></h2>
						<p><? echo "R$ ".number_format($valor_dia, 2, ",", "."); ?></p>

					</li>
					<?
					$i++;	
				}
				?>
				<div class="clear"></div>
			</ul>
		</section>
	<?
	}
	/*?>
	<section class="secao graficos setores">
		<? 
		$porcentagem_grafico_envios = $qtde_itens_pagos > 0 ? (($qtde_itens_enviados*100)/$qtde_itens_pagos) : 0;
		?>
		<div class="grafico horizontal">
			<span style="width: <? echo $porcentagem_grafico_envios."%"; ?>"></span>
		</div>
		<div class="dados float ingressos">
			<span></span>
			<h3>Ingressos Enviados</h3>
			<p><? echo $qtde_itens_enviados."/".$qtde_itens_pagos; ?></p>
		</div>
		<div class="dados float">
			<span></span>
			<h3>Ingressos Pagos</h3>
			<p><? echo $qtde_itens_pagos."/".$qtde_itens_vendidos; ?></p>
		</div>		
		<div class="clear"></div>
	</section>	*/ ?>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>