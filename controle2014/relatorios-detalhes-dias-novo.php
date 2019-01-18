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

//busca nome do evento
$sql_evento = sqlsrv_query($conexao, "SELECT TOP 1 EV_NOME FROM eventos WHERE EV_COD='$evento'", $conexao_params, $conexao_options);

$ar_evento = sqlsrv_fetch_array($sql_evento);
$evento_nome = $ar_evento['EV_NOME'];

$tipo = format($_GET['tipo']);
$dia_evento = (int) $_GET['dia'];

$nome_tipo = $conf['tipos'][$tipo]['titulo'];
$nome_tipo_tag = $tipo;

include("include/relatorios-parametros.php");

$sql_ingressos = sqlsrv_query($conexao, "DECLARE @ingressos TABLE (ingressos_compra_valor FLOAT, ingressos_compra INT, ingressos_compra_estoque INT, ingressos_venda INT);

	INSERT INTO @ingressos (ingressos_compra_valor, ingressos_compra, ingressos_compra_estoque, ingressos_venda)
	SELECT 
	ISNULL((SELECT SUM(VE_VALOR) FROM vendas WHERE VE_EVENTO='$evento' AND ".$filtros['tipos'][$tipo]." AND VE_DIA='$dia_evento' AND VE_BLOCK='0' AND D_E_L_E_T_='0'), 0) as ingressos_compra_valor, 
	ISNULL((SELECT COUNT(CO_COD) FROM compras WHERE CO_EVENTO='$evento' AND ".$filtros['compras']['tipos'][$tipo]." AND CO_DIA='$dia_evento' AND CO_BLOCK='0' AND D_E_L_E_T_='0' AND CO_ESTOQUE IS NULL), 0) as ingressos_compra, 
	ISNULL((SELECT SUM(CO_ESTOQUE) FROM compras WHERE CO_EVENTO='$evento' AND ".$filtros['compras']['tipos'][$tipo]." AND CO_DIA='$dia_evento' AND CO_BLOCK='0' AND D_E_L_E_T_='0' AND CO_ESTOQUE IS NOT NULL), 0) as ingressos_compra_estoque, 
	ISNULL((SELECT SUM(VE_ESTOQUE) FROM vendas WHERE VE_EVENTO='$evento' AND ".$filtros['tipos'][$tipo]." AND VE_DIA='$dia_evento' AND VE_BLOCK='0' AND D_E_L_E_T_='0'), 0) as ingressos_venda;

	SELECT *, ISNULL((ingressos_compra+ingressos_compra_estoque),0) as total_ingressos_compra  FROM @ingressos;", $conexao_params, $conexao_options);

if(sqlsrv_next_result($sql_ingressos)) $ar_ingressos = sqlsrv_fetch_array($sql_ingressos);

// print_r($ar_ingressos);
$ingressos_compra_valor = (int) $ar_ingressos['ingressos_compra_valor'];
$qtde_ingressos_compra = (int) $ar_ingressos['ingressos_compra'];
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
#foreach ($filtros['tipos'] as $t => $tipos) {
if($filtros['tipos'][$tipo]) {
	
	$tipos = $filtros['tipos'][$tipo];

	foreach ($filtros['modalidade'] as $m => $modalidade) { $query_itens_tipos .= " SUM(CASE WHEN $tipos THEN $modalidade ELSE 0 END) AS ".$m."_".$tipo.", "; }

	foreach ($filtros['status'] as $s => $status) {	
		foreach ($filtros['modalidade'] as $m => $modalidade) {
			$query_itens_tipos .= " SUM(CASE WHEN $status AND $tipos THEN $modalidade ELSE 0 END) AS ".$m."_".$tipo."_".$s.", ";
		}
	}

	// Busca pelos valores por tipo
	foreach ($filtros['dias'] as $d => $dias) {

		foreach ($filtros['modalidade'] as $m => $modalidade) { $query_itens_dias .= " SUM(CASE WHEN $dias THEN $modalidade ELSE 0 END) AS ".$m."_".$tipo."_".$d.", "; }
		
		foreach ($filtros['status'] as $s => $status) {	
			foreach ($filtros['modalidade'] as $m => $modalidade) {
				$query_itens_dias .= " SUM(CASE WHEN $status AND $dias THEN $modalidade ELSE 0 END) AS ".$m."_".$tipo."_".$d."_".$s.", ";
			}
		}
	}

	//conta o total de itens vendidos e pagos
	$sql_relatorio = sqlsrv_query($conexao, "SELECT 
		$query_itens_status
		$query_itens_tipos
		$query_itens_dias
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
		AND ve.VE_DIA='$dia_evento'
		AND ve.VE_BLOCK='0'
		AND ve.D_E_L_E_T_='0'
		AND lo.LO_EVENTO='$evento'
		-- AND lo.LO_FORMA_PAGAMENTO NOT IN (8,9)

		AND ($tipos)

		", $conexao_params, $conexao_options);

	$ar_relatorio = sqlsrv_fetch_array($sql_relatorio, SQLSRV_FETCH_ASSOC);

}

//array dos últimos 6 dias
$lista_dias = array();

for($i=1; $i<7; $i++) {
	$add_dia = strtotime(date('Y-m-d') . ' -'.$i.' day');
	//$add_dia = strtotime(date('2014-09-13') . ' -'.$i.' day');
	$dia = date("d/m/Y",$add_dia);

	array_push($lista_dias, "'$dia'");
	
	$query_dias .= "SUM(CASE WHEN data='".$dia."' THEN valor_dia ELSE 0 END) AS valor_dia".$i.",";
 	$query_dias .= "SUM(CASE WHEN data='".$dia."' THEN qtde_dia ELSE 0 END) AS qtde_dia".$i.",";
 	$query_dias .= "'".$dia."' AS dia".$i.",";
}

$lista_dias = implode(", ", $lista_dias);

//busca dados por dia
$sql_relatorio_dias = sqlsrv_query($conexao, "SELECT
	$query_dias
	MAX(data)

	FROM (
		SELECT SUM((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) - ISNULL(CASE WHEN tx.TX_TAXA IS NOT NULL THEN li.LI_VALOR * (tx.TX_TAXA / 100) ELSE 0 END,0)) AS valor_dia,
		/*SELECT SUM((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) AS valor_dia, */
		COUNT(lo.LO_COD) AS qtde_dia,
		MAX(CONVERT(VARCHAR, lo.LO_DATA_COMPRA, 103)) as data
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
		AND ve.VE_DIA='$dia_evento'
		AND ve.D_E_L_E_T_='0'
		AND lo.LO_EVENTO='$evento'
		AND ($tipos)

		GROUP BY lo.LO_DATA_COMPRA
	) S WHERE data IN($lista_dias)

	", $conexao_params, $conexao_options);

if(sqlsrv_num_rows($sql_relatorio_dias) > 0) $ar_relatorio_dias = sqlsrv_fetch_array($sql_relatorio_dias, SQLSRV_FETCH_ASSOC);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Relatório de Vendas <span><? echo $evento_nome; ?></span></h1>

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
						$status_link = 'href="'.$item_link.'?tipo='.$tipo.'&dia='.$dia_evento.'&a='.$status.'" ';
					} else {
						$status_tag = 'div';
					}
					
					$status_valor = $ar_relatorio['valor_'.$status];
					$status_qtde = $ar_relatorio['qtde_'.$status];

					$status_porcentagem = ($status_valor > 0) ? (($status_valor*100) / $valor_total) : 0;

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
				<a href="<? echo SITE; ?>relatorios-lista-produtos-dias.php?tipo=<? echo $tipo; ?>&dia=<? echo $dia_evento; ?>" class="fancybox fancybox.iframe width1480">Movimentação Diária<br/>Por produtos/Por dia</a>
				<table>	
					<thead>
						<tr>
							<th>Data</th>
							<th>Qtd.</th>
							<th>Valor</th>
						</tr>
					</thead>
					<tbody>
						<tr id="tempo-real" data-tipo="<? echo $tipos; ?>" data-dia="<? echo $dia_evento; ?>">
							
						</tr>
						<?
						$lista_dias = explode(", ", $lista_dias);
						$lista_dias = str_replace("'", "", $lista_dias);

						for($i=1; $i<7; $i++) {

						?>
						<tr>
							<td class="titulo"><? echo $lista_dias[$i-1]; ?></td>
							<td><? echo (int) $ar_relatorio_dias['qtde_dia'.$i]; ?></td>
							<td>R$ <? echo number_format($ar_relatorio_dias['valor_dia'.$i], 2, ',', '.'); ?></td>
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

    <?
    if($adm == ($nome_tipo_tag == 'frisas')) {
    ?>
    <section class="secao graficos setores mini">
    <?
        //O objetivo do relatório é contar os itens vendidos (pagos e não pagos) da tabela LOJA_ITENS e comparar com a quantidade no estoque da tabela VENDA. A contagem de estoque e itens na tabela COMPRA serve apenas para exibição no gráfico geral de vendas, sem fazer parte da porcentagem.
        $sql_setores = sqlsrv_query($conexao, "DECLARE @dias TABLE (dia INT, setor INT, nome VARCHAR(255));
        DECLARE @compras TABLE (dia INT, setor INT, ingressos_compra INT, ingressos_compra_estoque INT);
        DECLARE @vendas TABLE (dia INT, setor INT, ingressos_dia_venda INT);
        DECLARE @ingressos_dia_vendidos TABLE (
            dia INT,
            setor INT,
            ingressos_dia_vendidos INT, 
            valor_pagos DECIMAL(10,2),
            valor_posterior DECIMAL(10,2),
            valor_cortesias DECIMAL(10,2),
            valor_permutas DECIMAL(10,2),
            valor_reservas DECIMAL(10,2),
            valor_aguardando DECIMAL(10,2),
            valor_saida DECIMAL(10,2),
            itens_tipo_pagos INT,
            itens_tipo_posterior INT,
            itens_tipo_cortesias INT,
            itens_tipo_permutas INT,
            itens_tipo_reservas INT,
            itens_tipo_aguardando INT,
            itens_tipo_saida INT
        );


        INSERT INTO @dias (dia, setor, nome)
        SELECT
        ed.ED_COD,
        es.ES_COD,
        es.ES_NOME
        FROM eventos_dias ed, eventos_setores es WHERE ed.ED_COD='$dia_evento' AND ed.ED_EVENTO='$evento' AND es.ES_EVENTO='$evento' AND es.ES_BLOCK='0' AND ed.D_E_L_E_T_='0' AND es.D_E_L_E_T_='0' ORDER BY ed.ED_COD;

        INSERT INTO @compras (dia, setor, ingressos_compra, ingressos_compra_estoque)
        SELECT 
        CO_DIA, CO_SETOR,
        ISNULL(COUNT(CO_COD), 0), 
        ISNULL(SUM(CO_ESTOQUE), 0)
        FROM compras WHERE CO_EVENTO='$evento' AND ".$filtros['compras']['tipos'][$tipo]." AND CO_BLOCK='0' AND D_E_L_E_T_='0' AND CO_ESTOQUE IS NULL GROUP BY CO_DIA, CO_SETOR;

        INSERT INTO @vendas (dia, setor, ingressos_dia_venda)
        SELECT
        VE_DIA,
        VE_SETOR,
        ISNULL(SUM(VE_ESTOQUE),0) FROM vendas WHERE VE_EVENTO='$evento' AND ".$filtros['tipos'][$tipo]." AND VE_BLOCK='0' AND D_E_L_E_T_='0'  GROUP BY VE_DIA, VE_SETOR;

        INSERT INTO @ingressos_dia_vendidos (
            dia,
            setor,
            ingressos_dia_vendidos,
            
            valor_pagos,
            valor_posterior,
            valor_cortesias,
            valor_permutas,
            valor_reservas,
            valor_aguardando,
            valor_saida,
            itens_tipo_pagos,
            itens_tipo_posterior,
            itens_tipo_cortesias,
            itens_tipo_permutas,
            itens_tipo_reservas,
            itens_tipo_aguardando,
            itens_tipo_saida
        )
        SELECT 
            ve.VE_DIA,
            ve.VE_SETOR,
            ISNULL(COUNT(li.LI_COD), 0),

            SUM(CASE WHEN ".$filtros['status']['pagos']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),
            SUM(CASE WHEN ".$filtros['status']['posterior']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),
            SUM(CASE WHEN ".$filtros['status']['cortesias']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),
            SUM(CASE WHEN ".$filtros['status']['permutas']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),
            SUM(CASE WHEN ".$filtros['status']['reservas']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),
            SUM(CASE WHEN ".$filtros['status']['aguardando']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),
            SUM(CASE WHEN ".$filtros['status']['saida']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),

            SUM(CASE WHEN ".$filtros['status']['pagos']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
            SUM(CASE WHEN ".$filtros['status']['posterior']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
            SUM(CASE WHEN ".$filtros['status']['cortesias']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
            SUM(CASE WHEN ".$filtros['status']['permutas']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
            SUM(CASE WHEN ".$filtros['status']['reservas']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
            SUM(CASE WHEN ".$filtros['status']['aguardando']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
            SUM(CASE WHEN ".$filtros['status']['saida']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END)

        FROM
            loja_itens li,
            vendas ve,
            loja lo
        LEFT JOIN taxa_cartao tx
			ON (lo.LO_FORMA_PAGAMENTO=1 AND lo.LO_CARTAO=tx.TX_CARTAO AND lo.LO_PARCELAS >= tx.TX_PARCELAS_INICIO AND lo.LO_PARCELAS <= tx.TX_PARCELAS_FIM)
            OR (lo.LO_FORMA_PAGAMENTO=6 AND tx.TX_CARTAO='pos')
            OR (lo.LO_FORMA_PAGAMENTO=2014 AND tx.TX_CARTAO='pos')
        
        WHERE lo.LO_COD=li.LI_COMPRA AND li.LI_INGRESSO=ve.VE_COD AND ve.VE_EVENTO='$evento' AND ve.". trim($filtros['tipos'][$tipo]) ." AND ve.VE_BLOCK='0' AND ve.D_E_L_E_T_='0' AND lo.D_E_L_E_T_='0' AND li.D_E_L_E_T_='0' GROUP BY ve.VE_DIA, ve.VE_SETOR;

        SELECT 
        d.dia AS ED_COD, d.setor AS ES_COD, d.nome AS ES_NOME, 
        ISNULL(c.ingressos_compra, 0) AS ingressos_compra,
        ISNULL(c.ingressos_compra_estoque, 0) AS ingressos_compra_estoque,
        ISNULL(v.ingressos_dia_venda, 0) AS ingressos_dia_venda,
        ISNULL(dv.ingressos_dia_vendidos, 0) AS ingressos_dia_vendidos,

        ISNULL(dv.valor_pagos, 0) AS valor_pagos,
        ISNULL(dv.valor_posterior, 0) AS valor_posterior,
        ISNULL(dv.valor_cortesias, 0) AS valor_cortesias,
        ISNULL(dv.valor_permutas, 0) AS valor_permutas,
        ISNULL(dv.valor_reservas, 0) AS valor_reservas,
        ISNULL(dv.valor_aguardando, 0) AS valor_aguardando,
        ISNULL(dv.valor_saida, 0) AS valor_saida,
        ISNULL(dv.itens_tipo_pagos, 0) AS itens_tipo_pagos,
        ISNULL(dv.itens_tipo_posterior, 0) AS itens_tipo_posterior,
        ISNULL(dv.itens_tipo_cortesias, 0) AS itens_tipo_cortesias,
        ISNULL(dv.itens_tipo_permutas, 0) AS itens_tipo_permutas,
        ISNULL(dv.itens_tipo_reservas, 0) AS itens_tipo_reservas,
        ISNULL(dv.itens_tipo_aguardando, 0) AS itens_tipo_aguardando,
        ISNULL(dv.itens_tipo_saida, 0) AS itens_tipo_saida,

        ISNULL(c.ingressos_compra+c.ingressos_compra_estoque, 0) AS total_ingressos_compra

        FROM @dias d
        LEFT JOIN @compras c ON c.dia=d.dia AND c.setor=d.setor
        LEFT JOIN @vendas v ON v.dia=d.dia AND v.setor=d.setor
        LEFT JOIN @ingressos_dia_vendidos dv ON dv.dia=d.dia AND dv.setor=d.setor
        WHERE v.ingressos_dia_venda>0 OR dv.ingressos_dia_vendidos > 0", $conexao_params, $conexao_options);

        if(sqlsrv_next_result($sql_setores) && sqlsrv_next_result($sql_setores) && sqlsrv_next_result($sql_setores) && sqlsrv_next_result($sql_setores))
        $n_setores = sqlsrv_num_rows($sql_setores);

        ?>
        <section id="conteudo">
        <header class="titulo">
            <h1><? echo $nome_dia." Dia - ".$nome_tipo; ?> <span><? echo $evento_nome; ?></span></h1>		
        </header>
        <?
        if($n_setores !== false) {
            while ($setores = sqlsrv_fetch_array($sql_setores)) {
                $setores_nome = utf8_encode($setores['ES_NOME']);
                $setor = $setores['ES_COD'];
                $qtde_itens_vendidos = $setores['ingressos_dia_vendidos'];
                $qtde_ingressos_venda = $setores['ingressos_dia_venda'];
                $qtde_ingressos_compra = $setores['total_ingressos_compra'];
                $qtde_ingressos_estoque = $qtde_ingressos_compra-$qtde_itens_vendidos;


                ?>
                <section class="secao graficos setores mini">
                    <h2>Setor <? echo $setores_nome; ?></h2>
                    <div class="grafico horizontal">
                    <?
                                        
                        foreach ($conf['status'] as $status => $r) {
                            
                            $status_titulo = $r['titulo'];
                            $status_valor = $setores['valor_'.$status];
                            $status_qtde = $setores['itens_tipo_'.$status];
            
                            $status_porcentagem = ($status_qtde > 0) ? (($status_qtde*100) / $qtde_itens_vendidos) : 0;
            
                            if($status != 'saida') echo '<span class="'.$status.'" style="width:'.$status_porcentagem.'%"></span>';
                        }

                        ?>
                    </div>
                    <? 
                    
                    #$item_link = $item_link.'?tipo='.$tipo.'&dia='.$dia_evento.'&setor='.$setor;

                    ?>
                    <? /*<a href="<? echo $item_link; ?>&a=pagos" class="fancybox fancybox.iframe width800 dados float pagos">
                        <span></span>
                        <h3>Pagos</h3>
                        <p><? echo $qtde_itens_pagos; ?> ingresso<? if($qtde_itens_pagos != 1) { echo 's'; }  if($adm) echo "<br />R$ ".$valor_itens_vendidos; ?></p>
                    </a>
                    <a href="<? echo $item_link; ?>&a=aguardando" class="fancybox fancybox.iframe width800 dados float aguardando">
                        <span></span>
                        <h3>Aguardando Pgto.</h3>
                        <p><? echo $qtde_itens_aguardando; ?> ingresso<? if($qtde_itens_aguardando != 1) { echo 's'; }  if($adm) echo "<br />R$ ".$valor_itens_aguardando; ?></p>
                    </a>
                    <a href="<? echo $item_link; ?>&a=reservados" class="fancybox fancybox.iframe width800 dados float reservas">
                        <span></span>
                        <h3>Reservados</h3>
                        <p><? echo $qtde_itens_reservados; ?> ingresso<? if($qtde_itens_reservados != 1) { echo 's'; }  if($adm) echo "<br />R$ ".$valor_itens_reservados; ?></p>
                    </a>
                    <a href="<? echo $item_link; ?>&a=permuta" class="fancybox fancybox.iframe width800 dados float permutas">
                        <span></span>
                        <h3>Permuta</h3>
                        <p><? echo $qtde_itens_permuta; ?> ingresso<? if($qtde_itens_permuta != 1) { echo 's'; }  if($adm) echo "<br />R$ ".$valor_itens_permuta; ?></p>
                    </a>
                    <div class="dados float venda">
                        <span></span>
                        <h3>Ingressos à Venda</h3>
                        <p><? echo ($qtde_ingressos_venda - $qtde_itens_vendidos)."/".$qtde_ingressos_venda; ?></p>
                    </div>

                    <div class="dados float">
                        <span></span>
                        <h3>Em Estoque</h3>
                        <p <? if($qtde_ingressos_estoque < 0) echo 'class="negativo"' ?>><? echo ($qtde_ingressos_estoque)."/".$qtde_ingressos_compra; ?></p>
                    </div>*/ 

                    foreach ($conf['status'] as $status => $r) {

                        $status_titulo = $r['titulo'];
                        $status_link = (bool) $r['link'];
                        $status_exibe_valor = (bool) $r['valor'];

                        if($status_link) {
                            $status_tag = 'a';
                            $status_link = 'href="'.$item_link.'?tipo='.$tipo.'&dia='.$dia_evento.'&setor='.$setor.'&a='.$status.'" ';
                        } else {
                            $status_tag = 'div';
                        }
                        
                        $status_valor = $setores['valor_'.$status];
                        $status_qtde = $setores['itens_tipo_'.$status];

                        $status_porcentagem = ($status_qtde > 0) ? (($status_qtde*100) / $filas_itens_vendidos) : 0;

                        ?>
                        <<? echo $status_tag; ?> <? echo $status_link; ?> class="fancybox fancybox.iframe width1480 dados float <? echo $status ?> tam-fixo">
                            <span></span>
                            <h3><? echo $status_titulo; ?></h3>
                            <p>
                                Qtd.: <? echo $status_qtde; ?><br />
                                <? if($adm && $status_exibe_valor) { ?>R$ <? echo number_format($status_valor, 2, ',', '.'); ?><br /><? } ?>
                                <!--Percentual: <? echo number_format(round($status_porcentagem, 2), 2, ',', '.') ?>%-->
                            </p>
                        </<? echo $status_tag; ?>>
                        <?

                    }

                    // Entregues
                    $valor_entregues = $setores['valor_entregues'];		
                    $qtde_entregues = $setores['qtde_entregues'];
                    
                    ?>
                    <div class="dados float tam-fixo">
                        <span></span>
                        <h3>Em Estoque</h3>
                        <p <? if($qtde_ingressos_estoque < 0) echo 'class="negativo"' ?>><? echo ($qtde_ingressos_estoque)."/".$qtde_ingressos_compra; ?></p>
                    </div>

                    <div class="clear"></div>

                    <?

                    if($adm == ($tag_tipo == 'frisa')) {
                        
                        //Buscar por fila
                        $sql_filas = sqlsrv_query($conexao, "DECLARE @dias TABLE (dia INT, setor INT, nome VARCHAR(255));
                            DECLARE @compras TABLE (fila VARCHAR(255), dia INT, setor INT, ingressos_compra INT, ingressos_compra_estoque INT);
                            DECLARE @vendas TABLE (fila VARCHAR(255), dia INT, setor INT, ingressos_dia_venda INT);
                            DECLARE @ingressos_dia_vendidos TABLE (
                                fila VARCHAR(255),
                                dia INT,
                                setor INT,
                                ingressos_dia_vendidos INT, 
                                valor_pagos DECIMAL(10,2),
                                valor_posterior DECIMAL(10,2),
                                valor_cortesias DECIMAL(10,2),
                                valor_permutas DECIMAL(10,2),
                                valor_reservas DECIMAL(10,2),
                                valor_aguardando DECIMAL(10,2),
                                valor_saida DECIMAL(10,2),
                                itens_tipo_pagos INT,
                                itens_tipo_posterior INT,
                                itens_tipo_cortesias INT,
                                itens_tipo_permutas INT,
                                itens_tipo_reservas INT,
                                itens_tipo_aguardando INT,
                                itens_tipo_saida INT
                            );
                            
                            INSERT INTO @dias (dia, setor, nome)
                            SELECT
                            ed.ED_COD,
                            es.ES_COD,
                            es.ES_NOME
                            FROM eventos_dias ed, eventos_setores es WHERE ed.ED_COD='$dia_evento' AND ed.ED_EVENTO='$evento' AND es.ES_EVENTO='$evento' AND es.ES_BLOCK='0' AND ed.D_E_L_E_T_='0' AND es.D_E_L_E_T_='0' ORDER BY ed.ED_COD;

                            INSERT INTO @compras (fila, dia, setor, ingressos_compra, ingressos_compra_estoque)
                            SELECT 
                            CO_FILA, CO_DIA, CO_SETOR,
                            ISNULL(COUNT(CO_COD), 0), 
                            ISNULL(SUM(CO_ESTOQUE), 0)
                            FROM compras WHERE CO_SETOR='$setor' AND CO_EVENTO='$evento' AND ".$filtros['compras']['tipos'][$tipo]." AND CO_BLOCK='0' AND D_E_L_E_T_='0' AND CO_ESTOQUE IS NULL GROUP BY CO_FILA, CO_DIA, CO_SETOR;

                            INSERT INTO @vendas (fila, dia, setor, ingressos_dia_venda)
                            SELECT
                            VE_FILA,
                            VE_DIA,
                            VE_SETOR,
                            ISNULL(SUM(VE_ESTOQUE),0) FROM vendas WHERE VE_SETOR='$setor' AND VE_EVENTO='$evento' AND ".$filtros['tipos'][$tipo]." AND VE_BLOCK='0' AND D_E_L_E_T_='0'  GROUP BY VE_FILA, VE_DIA, VE_SETOR;

                            INSERT INTO @ingressos_dia_vendidos (
                                fila,
                                dia,
                                setor,
                                ingressos_dia_vendidos,
                                valor_pagos,
                                valor_posterior,
                                valor_cortesias,
                                valor_permutas,
                                valor_reservas,
                                valor_aguardando,
                                valor_saida,
                                itens_tipo_pagos,
                                itens_tipo_posterior,
                                itens_tipo_cortesias,
                                itens_tipo_permutas,
                                itens_tipo_reservas,
                                itens_tipo_aguardando,
                                itens_tipo_saida
                            )
                            SELECT
                                ve.VE_FILA,
                                ve.VE_DIA,
                                ve.VE_SETOR,
                                ISNULL(COUNT(li.LI_COD), 0),

                                SUM(CASE WHEN ".$filtros['status']['pagos']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),
                                SUM(CASE WHEN ".$filtros['status']['posterior']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),
                                SUM(CASE WHEN ".$filtros['status']['cortesias']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),
                                SUM(CASE WHEN ".$filtros['status']['permutas']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),
                                SUM(CASE WHEN ".$filtros['status']['reservas']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),
                                SUM(CASE WHEN ".$filtros['status']['aguardando']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),
                                SUM(CASE WHEN ".$filtros['status']['saida']." THEN ".$filtros['modalidade']['valor']." ELSE 0 END),

                                SUM(CASE WHEN ".$filtros['status']['pagos']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
                                SUM(CASE WHEN ".$filtros['status']['posterior']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
                                SUM(CASE WHEN ".$filtros['status']['cortesias']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
                                SUM(CASE WHEN ".$filtros['status']['permutas']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
                                SUM(CASE WHEN ".$filtros['status']['reservas']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
                                SUM(CASE WHEN ".$filtros['status']['aguardando']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
                                SUM(CASE WHEN ".$filtros['status']['saida']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END)

                            FROM 
                                loja_itens li,
                                vendas ve,
                                loja lo
                            
                            LEFT JOIN taxa_cartao tx
                                ON (lo.LO_FORMA_PAGAMENTO=1 AND lo.LO_CARTAO=tx.TX_CARTAO AND lo.LO_PARCELAS >= tx.TX_PARCELAS_INICIO AND lo.LO_PARCELAS <= tx.TX_PARCELAS_FIM)
                                OR (lo.LO_FORMA_PAGAMENTO=6 AND tx.TX_CARTAO='pos')
                                OR (lo.LO_FORMA_PAGAMENTO=2014 AND tx.TX_CARTAO='pos')

                            WHERE lo.LO_COD=li.LI_COMPRA AND li.LI_INGRESSO=ve.VE_COD AND ve.VE_SETOR='$setor' AND ve.VE_EVENTO='$evento' AND ve.". trim($filtros['tipos'][$tipo]) ." AND ve.VE_BLOCK='0' AND ve.D_E_L_E_T_='0' AND lo.D_E_L_E_T_='0' AND li.D_E_L_E_T_='0' GROUP BY ve.VE_FILA, ve.VE_DIA, ve.VE_SETOR;

                            
                            SELECT 

                            d.dia AS ED_COD, d.setor AS ES_COD, d.nome AS ES_NOME, v.fila AS VE_FILA,
                            ISNULL(c.ingressos_compra, 0) AS ingressos_compra,
                            ISNULL(c.ingressos_compra_estoque, 0) AS ingressos_compra_estoque,
                            ISNULL(v.ingressos_dia_venda, 0) AS ingressos_dia_venda,
                            ISNULL(dv.ingressos_dia_vendidos, 0) AS ingressos_dia_vendidos,
                            ISNULL(c.ingressos_compra+c.ingressos_compra_estoque, 0) AS total_ingressos_compra,

                            ISNULL(dv.valor_pagos, 0) AS valor_pagos,
                            ISNULL(dv.valor_posterior, 0) AS valor_posterior,
                            ISNULL(dv.valor_cortesias, 0) AS valor_cortesias,
                            ISNULL(dv.valor_permutas, 0) AS valor_permutas,
                            ISNULL(dv.valor_reservas, 0) AS valor_reservas,
                            ISNULL(dv.valor_aguardando, 0) AS valor_aguardando,
                            ISNULL(dv.valor_saida, 0) AS valor_saida,
                            ISNULL(dv.itens_tipo_pagos, 0) AS itens_tipo_pagos,
                            ISNULL(dv.itens_tipo_posterior, 0) AS itens_tipo_posterior,
                            ISNULL(dv.itens_tipo_cortesias, 0) AS itens_tipo_cortesias,
                            ISNULL(dv.itens_tipo_permutas, 0) AS itens_tipo_permutas,
                            ISNULL(dv.itens_tipo_reservas, 0) AS itens_tipo_reservas,
                            ISNULL(dv.itens_tipo_aguardando, 0) AS itens_tipo_aguardando,
                            ISNULL(dv.itens_tipo_saida, 0) AS itens_tipo_saida

                            FROM @dias d
                            LEFT JOIN @vendas v ON v.dia=d.dia AND v.setor=d.setor
                            LEFT JOIN @compras c ON c.fila=v.fila AND c.dia=d.dia AND c.setor=d.setor
                            LEFT JOIN @ingressos_dia_vendidos dv ON dv.fila=v.fila AND dv.dia=d.dia AND dv.setor=d.setor
                            WHERE v.ingressos_dia_venda>0 OR dv.ingressos_dia_vendidos > 0", $conexao_params, $conexao_options);

                        if(sqlsrv_next_result($sql_filas) && sqlsrv_next_result($sql_filas) && sqlsrv_next_result($sql_filas) && sqlsrv_next_result($sql_filas))
                        $n_filas = sqlsrv_num_rows($sql_filas);
                        
                        if($n_filas !== false) {
                            while ($filas = sqlsrv_fetch_array($sql_filas)) {

                                $filas_fila = $filas['VE_FILA'];
                                $filas_itens_vendidos = $filas['ingressos_dia_vendidos'];
                                $filas_ingressos_venda = $filas['ingressos_dia_venda'];
                                $filas_ingressos_compra = $filas['total_ingressos_compra'];
                                $filas_ingressos_estoque = $filas_ingressos_compra-$filas_itens_vendidos;

                                $item_link = $item_link.'?tipo='.$tipo.'&dia='.$dia_evento.'&setor='.$setor.'&fila='.$filas_fila;
                                
                                ?>
                                <section class="filas">
                                    <h2>Fila <? echo utf8_encode($filas_fila); ?></h2>
                                    <div class="grafico horizontal">
                                        <?
                                        
                                        foreach ($conf['status'] as $status => $r) {
                                            
                                            $status_titulo = $r['titulo'];
                                            $status_valor = $filas['valor_'.$status];
                                            $status_qtde = $filas['itens_tipo_'.$status];
                            
                                            $status_porcentagem = ($status_valor > 0) ? (($status_valor*100) / $valor_total) : 0;
                            
                                            if($status != 'saida') echo '<span class="'.$status.'" style="width:'.$status_porcentagem.'%"></span>';

                                        }

                                        ?>
                                    </div>

                                    <div class="grupo">
                                        <?

                                            foreach ($conf['status'] as $status => $r) {

                                                $status_titulo = $r['titulo'];
                                                $status_link = (bool) $r['link'];
                                                $status_exibe_valor = (bool) $r['valor'];

                                                if($status_link) {
                                                    $status_tag = 'a';
                                                    $status_link = 'href="'.$item_link.'?tipo='.$tipo.'&dia='.$dia_evento.'&a='.$status.'" ';
                                                } else {
                                                    $status_tag = 'div';
                                                }
                                                
                                                $status_valor = $filas['valor_'.$status];
                                                $status_qtde = $filas['itens_tipo_'.$status];

                                                $status_porcentagem = ($status_valor > 0) ? (($status_valor*100) / $valor_total) : 0;

                                            ?>
                                            <<? echo $status_tag; ?> <? echo $status_link; ?> class="fancybox fancybox.iframe width1480 dados float <? echo $status ?>">
                                                <span></span>
                                                <h3><? echo $status_titulo; ?></h3>
                                                <p>
                                                    Qtd.: <? echo $status_qtde; ?><br />
                                                    <? if($adm && $status_exibe_valor) { ?>R$ <? echo number_format($status_valor, 2, ',', '.'); ?><br /><? } ?>
                                                    <!--Percentual: <? echo number_format(round($status_porcentagem, 2), 2, ',', '.') ?>%-->
                                                </p>
                                            </<? echo $status_tag; ?>>
                                            <?

                                            }

                                            // Entregues
                                            $valor_entregues = $filas['valor_entregues'];		
                                            $qtde_entregues = $filas['qtde_entregues'];
                                            
                                            ?>

                                        <div class="clear"></div>
                                    </div>
                                    <?

                                    //Exclusividade
                                    $sql_exclusividade = sqlsrv_query($conexao, "SELECT * 
                                        FROM  (
                                            SELECT 
                                            li.LI_EXCLUSIVIDADE_VAL AS exclusividade, 
                                            ISNULL(COUNT(li.LI_COD), 0) AS quantidade,
                                            SUM(CASE WHEN ".$filtros['status']['pagos']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END), 
                                            SUM(CASE WHEN ".$filtros['status']['aguardando']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END),
                                            SUM(CASE WHEN ".$filtros['status']['reservas']." THEN ".$filtros['modalidade']['qtde']." ELSE 0 END)

                                            FROM loja_itens li, loja lo, vendas ve 
                                            WHERE li.LI_EXCLUSIVIDADE=1 AND ve.VE_FILA='$filas_fila' AND ve.VE_DIA='$dia' AND lo.LO_COD=li.LI_COMPRA AND li.LI_INGRESSO=ve.VE_COD AND ve.VE_SETOR='$setor' 
                                            AND ve.VE_EVENTO='$evento' AND ve.". trim($filtros['tipos'][$tipo]) ." AND ve.VE_BLOCK='0' AND ve.D_E_L_E_T_='0' AND lo.D_E_L_E_T_='0' AND li.D_E_L_E_T_='0' 
                                            GROUP BY li.LI_EXCLUSIVIDADE_VAL, ve.VE_FILA, ve.VE_DIA, ve.VE_SETOR
                                        ) S

                                        WHERE exclusividade != ''
                                        ", $conexao_params, $conexao_options);
                                    if(sqlsrv_num_rows($sql_exclusividade) > 0) {
                                        ?>
                                        <div class="grupo">
                                        <?
                                        while ($exclusividade = sqlsrv_fetch_array($sql_exclusividade)) {
                                            
                                            $exclusividade_fila = utf8_encode($exclusividade['exclusividade']);
                                            $exclusividade_quantidade = $exclusividade['quantidade'];
                                            $exclusividade_pago = $exclusividade['pago'];
                                            $exclusividade_aguardando = $exclusividade['aguardando'];
                                            $exclusividade_reservado = $exclusividade['reservado'];
                                            
                                            if($exclusividade_pago > 0) {											
                                            ?>
                                            <div class="dados ingressos exclusividade">
                                                <span></span>
                                                <p><? echo $exclusividade_pago; ?> - Exclusividade fila <? echo $exclusividade_fila; ?></p>
                                            </div>
                                            <?
                                            }
                                            if($exclusividade_aguardando > 0) {
                                            ?>
                                            <div class="dados aguardando exclusividade">
                                                <span></span>
                                                <p><? echo $exclusividade_aguardando; ?> - Exclusividade fila <? echo $exclusividade_fila; ?></p>
                                            </div>
                                            <?
                                            }
                                            if($exclusividade_reservado > 0) {
                                            ?>
                                            <div class="dados reservados exclusividade">
                                                <span></span>
                                                <p><? echo $exclusividade_reservado; ?> - Exclusividade fila <? echo $exclusividade_fila; ?></p>
                                            </div>
                                            <?
                                            }
                                        }
                                        ?>
                                        </div>
                                        <?
                                    }


                                    ?>
                                </section>
                                <?

                            }
                            ?>
                            <div class="clear"></div>
                            <?
                        }

                    }

                    ?>
                </section>	
                <?
            }

        }

    ?>
    </section>
    <?
    }
    ?>
	
	<footer class="controle">
		<a href="<? echo SITE; ?>relatorios/detalhes/<? echo $tipo; ?>/" class="cancel coluna">Voltar</a>
		<div class="clear"></div>
	</footer>

</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>