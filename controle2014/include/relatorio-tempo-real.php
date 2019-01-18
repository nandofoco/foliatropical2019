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

$dbprefix = ($_SERVER['SERVER_NAME'] == "server" || $_SERVER['SERVER_NAME'] == "localhost" || $_SERVER['SERVER_NAME'] == "192.168.1.120") ? 'foliatropical' : 'foliatropical2014';

$tipo = $_POST['tipo'];
$dia = $_POST['dia'];

if(!empty($tipo)) $search_tipos = "AND".$tipo;
if(!empty($dia)) $search_dias = "AND ve.VE_DIA=".$dia;

// $query_dias .= "SUM(CASE WHEN data='".$dia_atual."' THEN valor_dia ELSE 0 END) AS valor_dia_atual,";
// $query_dias .= "SUM(CASE WHEN data='".$dia_atual."' THEN qtde_dia ELSE 0 END) AS qtde_dia_atual,";
// $query_dias .= "'".$dia_atual."' AS dia_atual,";

$dia_atual = date('Y-m-d');
$dia_atual_f = date('d/m/Y');

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

	WHERE l.LO_EVENTO='$evento' AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 AND l.LO_FORMA_PAGAMENTO NOT IN (8,9) AND CONVERT(DATE, l.LO_DATA_COMPRA) = '$dia_atual'
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
				l.LO_PAGO AS PAGO,

				CASE WHEN l.LO_COMISSAO_RETIDA=1 THEN 'SIM' ELSE '' END AS COMISSAO_RETIDA,
				CASE WHEN l.LO_COMISSAO_PAGA=1 THEN 'SIM' ELSE '' END AS COMISSAO_PAGA,
				
				f.FP_COD AS FORMA_PAGAMENTO,
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

	
	foreach($rows as $value) {

		$forma_pagamento = $value['FORMA_PAGAMENTO'];
		$pago = $value['PAGO'];

		$loja_qtde = $value['QTDE'];
		$loja_valor_lucro = $value['LUCRO'];
		$item_vaga = utf8_encode($value['VAGAS']);
		$item_tipo_especifico = utf8_encode($value['ESPECIFICO']);

		$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;

		if($item_fechado) { 
			$loja_qtde = $loja_qtde/$item_vaga;
			$loja_valor_lucro = $loja_valor_lucro/$item_vaga;
		} 

		// lo.LO_PAGO='1' AND lo.LO_FORMA_PAGAMENTO NOT IN (5,8,9,14,2013) 
		if(($pago == 1) && !(in_array($forma_pagamento, array(5,8,9,14,2013)))) {
			$valor_pagos += $loja_valor_lucro;
			$qtde_pagos += $loja_qtde;
		}

		// lo.LO_FORMA_PAGAMENTO='14'
		if($forma_pagamento == 14) {
			$valor_posterior += $loja_valor_lucro;
			$qtde_posterior += $loja_qtde;
		}

		// lo.LO_FORMA_PAGAMENTO='8' 
		if($forma_pagamento == 8) {
			$valor_cortesias += $loja_valor_lucro;
			$qtde_cortesias += $loja_qtde;
		}

		//  lo.LO_FORMA_PAGAMENTO='9'
		if($forma_pagamento == 9) {
			$valor_permutas += $loja_valor_lucro;
			$qtde_permutas += $loja_qtde;
		}

		// lo.LO_FORMA_PAGAMENTO='5'
		if($forma_pagamento == 5) {
			$valor_reservas += $loja_valor_lucro;
			$qtde_reservas += $loja_qtde;
		}

		// lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO NOT IN (5,6,8,9,14,2013)
		if(($pago == 0) && !(in_array($forma_pagamento, array(5,6,8,9,14,2013)))) {
			$valor_aguardando += $loja_valor_lucro;
			$qtde_aguardando += $loja_qtde;
		}

		// (((lo.LO_PAGO='1' AND lo.LO_FORMA_PAGAMENTO NOT IN (8,9,2013)) OR lo.LO_FORMA_PAGAMENTO IN (5,14)) OR (lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO NOT IN (5,6,8,9,2013)))
		if(($pago == 1) && !(in_array($forma_pagamento, array(8,92013))) || (in_array($forma_pagamento, array(5,14))) || (($pago == 0) && !(in_array($forma_pagamento, array(5,6,8,9,2013))))) {
			$valor_saida += $loja_valor_lucro;
			$qtde_saida += $loja_qtde;
		}

		$valor_dia_atual += $loja_valor_lucro;
		$qtde_dia_atual += $loja_qtde;

	}	

	$sucesso = true;

	$valor_pagos = number_format($valor_pagos, 2, ',', '.');
    $valor_posterior = number_format($valor_posterior, 2, ',', '.');
    $valor_cortesias = number_format($valor_cortesias, 2, ',', '.');
    $valor_permutas = number_format($valor_permutas, 2, ',', '.');
    $valor_reservas = number_format($valor_reservas, 2, ',', '.');
    $valor_aguardando = number_format($valor_aguardando, 2, ',', '.');
    $valor_saida = number_format($valor_saida, 2, ',', '.');
    $valor_dia_atual = number_format($valor_dia_atual, 2, ',', '.');

    $qtde_pagos = (int) $qtde_pagos;
    $qtde_posterior = (int) $qtde_posterior;
    $qtde_cortesias = (int) $qtde_cortesias;
    $qtde_permutas = (int) $qtde_permutas;
    $qtde_reservas = (int) $qtde_reservas;
    $qtde_aguardando = (int) $qtde_aguardando;
    $qtde_saida = (int) $qtde_saida;
    $qtde_dia_atual = (int) $qtde_dia_atual;

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

		"data" => $dia_atual_f);


#} else {
#	$sucesso = false;
#}

echo json_encode($resposta);

//Fechar conexoes
include("../conn/close.php");

?>