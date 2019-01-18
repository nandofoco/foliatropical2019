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
		AND ve.D_E_L_E_T_='0'
		AND lo.LO_EVENTO='$evento'
		AND lo.LO_FORMA_PAGAMENTO NOT IN (8,9)
		GROUP BY lo.LO_DATA_COMPRA
	) S WHERE data IN($lista_dias)

	/*GROUP BY data*/", $conexao_params, $conexao_options);

$ar_relatorio_dias = array();

if(sqlsrv_num_rows($sql_relatorio_dias) > 0) $ar_relatorio_dias = sqlsrv_fetch_array($sql_relatorio_dias, SQLSRV_FETCH_ASSOC);

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
					$status_qtde = $ar_ingressos[$tipo] - $ar_relatorio['qtde_'.$tipo.'_saida'] - $ar_relatorio['qtde_'.$tipo.'_cortesias'] - $ar_relatorio['qtde_'.$tipo.'_permutas'];
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
						if($status == 'saida') $status_titulo = "Saída Estq.";


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