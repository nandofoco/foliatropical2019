<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$adm = ($_SESSION['us-grupo'] == 'ADM') ? true : false;

define('CODRESERVA','5');
define('CODPERMUTA','8,9');

//-----------------------------------------------------------------//


$dbprefix = ($_SERVER['SERVER_NAME'] == "server" || $_SERVER['SERVER_NAME'] == "localhost" || $_SERVER['SERVER_NAME'] == "192.168.1.120") ? 'foliatropical' : 'foliatropical2014';

// $search = " SELECT l.LI_COMPRA FROM [$dbprefix].[dbo].[loja_itens] l, vendas v WHERE v.VE_COD=l.LI_INGRESSO AND l.D_E_L_E_T_='0' " ;

include("include/relatorios-parametros.php");

$tipo = $_GET['tipo'];
$dia = (int) $_GET['dia'];
$setor = (int) $_GET['setor'];
$fila = format($_GET['fila']);
$acao = format($_GET['a']);


$item_link;

if(!empty($tipo)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'tipo='.$tipo; }
if(!empty($acao)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'a='.$acao; }
if(!empty($dia)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'dia='.$dia; }
if(!empty($setor)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'setor='.$setor; }
if(!empty($fila)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'fila='.$fila; }

// Criação dos gráficos

// Busca pelos valores por tipo


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


$p = (int) $_GET['p'];
if(!($p > 0)) $p = 1;
$limite = 30;
$inicio = (($p*$limite)-($limite-1));
$fim = $inicio+$limite;

//array dos últimos 30 dias
$lista_dias = array();

for($i=$inicio; $i<$fim; $i++) {
	$add_dia = strtotime(date('Y-m-d') . ' -'.($i-1).' day');
	$dia = date("Y-m-d",$add_dia);

	array_push($lista_dias, "'$dia'");

}

$lista_dias = implode(", ", $lista_dias);

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

	?>
<section id="conteudo" class="relatorio-lista-voucher wide">
	<!-- <header class="titulo">
		<h1>Vouchers <span>Confirmados</span></h1>
	</header> -->
	<section class="secao bottom">
		<!-- <header class="titulo">
			<form id="busca-lista" class="busca-lista" method="post" action="<? echo SITE; ?>relatorios-lista-produtos-dias-detalhes.php?a=<? echo $acao; ?>">
				<p class="coluna">
					<label for="busca-lista-input" class="infield">Pesquisar</label>
					<? if(!empty($q)){ ?><a href="<? echo SITE; ?>relatorios-lista-produtos-dias-detalhes.php?a=<? echo $acao; ?>" class="limpar-busca">&times;</a><? } ?>
					<input type="text" name="q" class="input" id="busca-lista-input" value="<? echo utf8_encode($q); ?>" />
				</p>
				<input type="submit" class="submit" value="" />
			</form>
		</header> -->

		<table class="lista mini tablesorter-nopager">
			<thead>
				<tr>
					<th class="first">&nbsp;</th>
					<th><strong>Data</strong><span></span></th>
					<th><strong>Quantidade</strong><span></span></th>
					<th><strong>Valor</strong><span></span></th>
				</tr>
				<tr class="spacer"><td colspan="<? echo ($adm) ? '16' : '9' ; ?>">&nbsp;</td></tr>
			</thead>
			<tbody>

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
			for($i=$inicio; $i<$fim; $i++) {			

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

			if(count($dias) > 0) {
			
				$i=1;
				foreach ($dias as $line) {

					//Total de paginas
					$total_paginas = $line['TOTAL'];

					unset($loja_parceiro, $loja_parceiro_exibir);

					$loja_qtde = $line['qtde'];
					$loja_valor_lucro = $line['loja_valor_lucro'];
					$loja_data = $line['data'];

					$loja_valor_lucro = number_format($loja_valor_lucro, 2, ",", ".");
					$loja_lucro = number_format($loja_lucro, 2, ",", ".");


					?>
						<tr>	
							<td class="first"><a href="<? echo SITE; ?>relatorios-lista-produtos-dias-detalhes.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>data=<? echo urlencode($loja_data); ?>" class="ver"></a></td>
							<td><? echo $loja_data; ?></td>
							<td><? echo $loja_qtde; ?></td>
							<td>R$ <? echo $loja_valor_lucro; ?></td>
						</tr>

					<?
					$i++;
					$exibe_loja = true;
				}

			} 

			if(!$exibe_loja) {
			?>
				<tr>
					<td colspan="<? echo ($adm) ? '16' : '9' ; ?>" class="nenhum">Nenhum voucher encontrado</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<?
		if ($exibe_loja) {
			
			$pagina_de = date("d/m/y",strtotime(' -'.($inicio-1).' day'));
			$pagina_ate = date("d/m/y",strtotime(' -'.($inicio+$limite-2).' day'));			

		?>
        <div class="pager-tablesorter big">
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias-teste.php<? echo $item_link; ?>" class="first"></a>
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias-teste.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo ($p > 1) ? ($p - 1) : 1; ?>" class="prev"></a>
	        <span class="pagedisplay"><? echo $pagina_de; ?> a <? echo $pagina_ate; ?></span>
	        <a href="<? echo SITE; ?>relatorios-lista-produtos-dias-teste.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo $p + 1; ?>" class="next"></a>
	        <!--<a href="<? echo SITE; ?>relatorios-lista-produtos-dias-teste.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo $total_paginas; ?>" class="last"></a>-->

        </div>
        <? } ?>
	</section>
		<!-- <a href="https://ingressos.foliatropical.com.br/controle2014/relatorios-lista-produtos-dias.php" class="fancybox voltar-relatorio">Voltar</a> -->

		
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>