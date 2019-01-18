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

if($tipo == "folia") $tipo_cod = 4;
if($tipo == "super") $tipo_cod = 6;

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
		AND lo.LO_FORMA_PAGAMENTO NOT IN (8,9)
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

	<section class="secao graficos completo lote" style="margin-top: 0;">	
	<?

	$sql_compras = sqlsrv_query($conexao, "
		SELECT SUM(v.VE_ESTOQUE) AS TOTAL, v.VE_TIPO, v.VE_DIA, v.VE_VALOR FROM vendas v, tipos t, eventos_dias d, eventos_setores s 
		WHERE v.VE_EVENTO='$evento' AND v.VE_DIA='$dia_evento' AND VE_TIPO='$tipo_cod' AND v.D_E_L_E_T_=0 AND d.ED_COD=v.VE_DIA AND t.TI_COD=v.VE_TIPO AND s.ES_COD=v.VE_SETOR AND d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0 
		GROUP BY v.VE_TIPO, v.VE_VALOR, v.VE_DIA", $conexao_params, $conexao_options);

		$n_compras = sqlsrv_num_rows($sql_compras);

		if($n_compras > 0) {

			?>
			<table>
				<thead>
					<tr>
						<th>Lote/Valor</th>
						<th>Vendidos</th>
						<th>Reservas</th>
						<th>Estoque</th>
						<th>Total</th>
					</tr>
				</thead>

				<tbody>
				<?
					$i = 1;

					while($ar_compras = sqlsrv_fetch_array($sql_compras)) {

						$compra_valor = $ar_compras['VE_VALOR'];
						$compra_valor_f = number_format($ar_compras['VE_VALOR'], 2, ',','.');

						$total = $ar_compras['TOTAL'];

						$sql_comprados = sqlsrv_query($conexao, "SELECT 
						SUM(CASE WHEN lo.LO_PAGO='1' AND lo.LO_FORMA_PAGAMENTO NOT IN (5,8,9,14,2013) THEN 1 ELSE 0 END) AS qtde_pagos,
						SUM(CASE WHEN lo.LO_FORMA_PAGAMENTO='5' THEN 1 ELSE 0 END) AS qtde_reservas, 
						SUM(CASE WHEN lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO NOT IN (5,6,8,9,14,2013) THEN 1 ELSE 0 END) AS qtde_aguardando
 						FROM loja_itens li, vendas ve, loja lo 
 						WHERE lo.LO_COD=li.LI_COMPRA AND li.D_E_L_E_T_='0' AND lo.LO_BLOCK='0' AND lo.D_E_L_E_T_='0' AND li.LI_INGRESSO=ve.VE_COD 
						AND li.LI_VALOR_TABELA='$compra_valor' AND ve.VE_EVENTO='$evento' AND ve.VE_DIA='$dia_evento' AND ve.VE_BLOCK='0' AND ve.D_E_L_E_T_='0' AND lo.LO_EVENTO='$evento' AND ( VE_TIPO='$tipo_cod' )", $conexao_params, $conexao_options);

						if(sqlsrv_num_rows($sql_comprados) > 0) $ar_comprados = sqlsrv_fetch_array($sql_comprados);

						$qtde_comprado = $ar_comprados['qtde_pagos'];
						$qtde_reserva = $ar_comprados['qtde_reservas']+$ar_comprados['qtde_aguardando'];
					
					?>
						<tr>
							<td class="first <? if($i == $n_compras) echo 'last'; ?>">R$ <? echo $compra_valor_f; ?></td>
							<td class="<? if($i == $n_compras) echo 'last'; ?>"><? echo $qtde_comprado; ?></td>
							<td class="<? if($i == $n_compras) echo 'last'; ?>"><? echo $qtde_reserva; ?></td>
							<td class="<? if($i == $n_compras) echo 'last'; ?>"><? echo ($total-$qtde_comprado-$qtde_reserva); ?></td>
							<td class="<? if($i == $n_compras) echo 'last'; ?>"><? echo $total; ?></td>
							
						</tr>
					<?
						$i++;
					}
				?>
				</tbody>

			</table>
			<?
		} 
	?>
	</section>
	
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