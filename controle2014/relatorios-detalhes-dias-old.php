<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

define('CODRESERVA','5');
define('CODPERMUTA','8,9');

//-----------------------------------------------------------------//

$adm = ($_SESSION['us-grupo'] == 'ADM') ? true : false;

$evento = (int) $_SESSION['usuario-carnaval'];
$tipo = (int) $_GET['tipo'];
$dia = (int) $_GET['dia'];
$setor = (int) $_GET['setor'];

$sql_evento = sqlsrv_query($conexao, "SELECT TOP 1 EV_NOME FROM eventos WHERE EV_COD='$evento'", $conexao_params, $conexao_options);
$ar_evento = sqlsrv_fetch_array($sql_evento);
$evento_nome = $ar_evento['EV_NOME'];

//busca nome do tipo
$sql_tipo = sqlsrv_query($conexao, "SELECT TI_NOME, TI_TAG FROM tipos WHERE TI_COD='$tipo' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
$ar_tipo = sqlsrv_fetch_array($sql_tipo);
$nome_tipo = utf8_encode($ar_tipo['TI_NOME']);
$tag_tipo = utf8_encode($ar_tipo['TI_TAG']);

//busca nome do dia
$sql_dia = sqlsrv_query($conexao, "SELECT ED_NOME FROM eventos_dias WHERE ED_COD='$dia' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
$ar_dia = sqlsrv_fetch_array($sql_dia);
$nome_dia = utf8_encode($ar_dia['ED_NOME']);

//O objetivo do relatório é contar os itens vendidos (pagos e não pagos) da tabela LOJA_ITENS e comparar com a quantidade no estoque da tabela VENDA. A contagem de estoque e itens na tabela COMPRA serve apenas para exibição no gráfico geral de vendas, sem fazer parte da porcentagem.
$sql_setores = sqlsrv_query($conexao, "DECLARE @dias TABLE (dia INT, setor INT, nome VARCHAR(255));
	DECLARE @compras TABLE (dia INT, setor INT, ingressos_compra INT, ingressos_compra_estoque INT);
	DECLARE @vendas TABLE (dia INT, setor INT, ingressos_dia_venda INT);
	DECLARE @ingressos_dia_vendidos TABLE (dia INT, setor INT, ingressos_dia_vendidos INT, valor_vendidos DECIMAL(10,2), valor_aguardando DECIMAL(10,2), valor_reservados DECIMAL(10,2), valor_permuta DECIMAL(10,2), itens_tipo_pagos INT, itens_tipo_aguardando INT, itens_tipo_reserva INT, itens_tipo_permuta INT);
	
	INSERT INTO @dias (dia, setor, nome)
	SELECT
	ed.ED_COD,
	es.ES_COD,
	es.ES_NOME
	FROM eventos_dias ed, eventos_setores es WHERE ed.ED_COD IN($dia) AND ed.ED_EVENTO='$evento' AND es.ES_EVENTO='$evento' AND es.ES_BLOCK='0' AND ed.D_E_L_E_T_='0' AND es.D_E_L_E_T_='0' ORDER BY ed.ED_COD;

	INSERT INTO @compras (dia, setor, ingressos_compra, ingressos_compra_estoque)
	SELECT 
	CO_DIA, CO_SETOR,
	ISNULL(COUNT(CO_COD), 0), 
	ISNULL(SUM(CO_ESTOQUE), 0)
	FROM compras WHERE CO_EVENTO='$evento' AND CO_TIPO='$tipo' AND CO_BLOCK='0' AND D_E_L_E_T_='0' AND CO_ESTOQUE IS NULL GROUP BY CO_DIA, CO_SETOR;

	INSERT INTO @vendas (dia, setor, ingressos_dia_venda)
	SELECT
	VE_DIA,
	VE_SETOR,
	ISNULL(SUM(VE_ESTOQUE),0) FROM vendas WHERE VE_EVENTO='$evento' AND VE_TIPO='$tipo' AND VE_BLOCK='0' AND D_E_L_E_T_='0'  GROUP BY VE_DIA, VE_SETOR;

	INSERT INTO @ingressos_dia_vendidos (dia, setor, ingressos_dia_vendidos, valor_vendidos, valor_aguardando, valor_reservados, valor_permuta, itens_tipo_pagos, itens_tipo_aguardando, itens_tipo_reserva, itens_tipo_permuta)
	SELECT ve.VE_DIA, ve.VE_SETOR,
	ISNULL(COUNT(li.LI_COD), 0),	
	SUM(CASE WHEN lo.LO_PAGO='1' AND lo.LO_FORMA_PAGAMENTO NOT IN (".CODPERMUTA.") THEN (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) ELSE 0 END), 
	SUM(CASE WHEN lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO NOT IN (".CODPERMUTA.") AND lo.LO_FORMA_PAGAMENTO<>'".CODRESERVA."' THEN (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) ELSE 0 END), 
	SUM(CASE WHEN lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO NOT IN (".CODPERMUTA.") AND lo.LO_FORMA_PAGAMENTO='".CODRESERVA."' THEN (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) ELSE 0 END), 
	SUM(CASE WHEN lo.LO_FORMA_PAGAMENTO IN (".CODPERMUTA.") THEN (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) ELSE 0 END), 
	SUM(CASE WHEN lo.LO_PAGO='1' AND lo.LO_FORMA_PAGAMENTO NOT IN (".CODPERMUTA.") THEN 1 ELSE 0 END), 
	SUM(CASE WHEN lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO NOT IN (".CODPERMUTA.") AND lo.LO_FORMA_PAGAMENTO<>'".CODRESERVA."' THEN 1 ELSE 0 END),
	SUM(CASE WHEN lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO NOT IN (".CODPERMUTA.") AND lo.LO_FORMA_PAGAMENTO='".CODRESERVA."' THEN 1 ELSE 0 END),
	SUM(CASE WHEN lo.LO_FORMA_PAGAMENTO IN (".CODPERMUTA.") THEN 1 ELSE 0 END)

	FROM loja_itens li, loja lo, vendas ve WHERE lo.LO_COD=li.LI_COMPRA AND li.LI_INGRESSO=ve.VE_COD AND ve.VE_EVENTO='$evento' AND ve.VE_TIPO='$tipo' AND ve.VE_BLOCK='0' AND ve.D_E_L_E_T_='0' AND lo.D_E_L_E_T_='0' AND li.D_E_L_E_T_='0' GROUP BY ve.VE_DIA, ve.VE_SETOR;

	SELECT 
	d.dia AS ED_COD, d.setor AS ES_COD, d.nome AS ES_NOME, 
	ISNULL(c.ingressos_compra, 0) AS ingressos_compra,
	ISNULL(c.ingressos_compra_estoque, 0) AS ingressos_compra_estoque,
	ISNULL(v.ingressos_dia_venda, 0) AS ingressos_dia_venda,
	ISNULL(dv.ingressos_dia_vendidos, 0) AS ingressos_dia_vendidos,

	ISNULL(dv.valor_vendidos, 0) AS valor_vendidos,
	ISNULL(dv.valor_aguardando, 0) AS valor_aguardando,
	ISNULL(dv.valor_reservados, 0) AS valor_reservados,
	ISNULL(dv.valor_permuta, 0) AS valor_permuta,
	ISNULL(dv.itens_tipo_pagos, 0) AS itens_tipo_pagos,
	ISNULL(dv.itens_tipo_aguardando, 0) AS itens_tipo_aguardando,
	ISNULL(dv.itens_tipo_reserva, 0) AS itens_tipo_reserva,
	ISNULL(dv.itens_tipo_permuta, 0) AS itens_tipo_permuta,

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


			$qtde_itens_dia = $setores['ingressos_venda'];
			$qtde_itens_pagos = $setores['itens_tipo_pagos'];
			$qtde_itens_aguardando = $setores['itens_tipo_aguardando'];
			$qtde_itens_reservados = $setores['itens_tipo_reserva'];
			$qtde_itens_permuta = $setores['itens_tipo_permuta'];

			$valor_itens_vendidos = $setores['valor_vendidos'];
			$valor_itens_aguardando = $setores['valor_aguardando'];
			$valor_itens_reservados = $setores['valor_reservados'];
			$valor_itens_permuta = $setores['valor_permuta'];

			$valor_itens_vendidos = number_format($valor_itens_vendidos, 2, ',', '.');
			$valor_itens_aguardando = number_format($valor_itens_aguardando, 2, ',', '.');
			$valor_itens_reservados = number_format($valor_itens_reservados, 2, ',', '.');
			$valor_itens_permuta = number_format($valor_itens_permuta, 2, ',', '.');

			?>
			<section class="secao graficos setores mini">
				<?
				
				$porcentagem_grafico_pagos = ($qtde_itens_pagos > 0) ?  (($qtde_itens_pagos*100)/$qtde_ingressos_venda) : 0;
				$porcentagem_grafico_aguardando = ($qtde_itens_aguardando > 0) ?  (($qtde_itens_aguardando*100)/$qtde_ingressos_venda) : 0;
				$porcentagem_grafico_reservados = ($qtde_itens_reservados > 0) ?  (($qtde_itens_reservados*100)/$qtde_ingressos_venda) : 0;
				$porcentagem_grafico_permuta = ($qtde_itens_permuta > 0) ?  (($qtde_itens_permuta*100)/$qtde_ingressos_venda) : 0;

				?>
				<h2>Setor <? echo $setores_nome; ?></h2>
				<div class="grafico horizontal">
					<span class="ingressos" style="width: <? echo str_replace(',', '.', $porcentagem_grafico_pagos); ?>%"></span>
					<span class="aguardando" style="width: <? echo str_replace(',', '.', $porcentagem_grafico_aguardando); ?>%"></span>
					<span class="reservados" style="width: <? echo str_replace(',', '.', $porcentagem_grafico_reservados); ?>%"></span>
					<span class="permuta" style="width: <? echo str_replace(',', '.', $porcentagem_grafico_permuta); ?>%"></span>
				</div>
				<? 
				$item_link = SITE.'relatorios-lista-voucher.php?tipo='.$tipo.'&dia='.$dia.'&setor='.$setor;

				/*?>
				<a href="<? echo $item_link; ?>&a=pagos" class="fancybox fancybox.iframe width800 dados float ingressos">
					<span></span>
					<h3>Ingressos Pagos</h3>
					<p><? echo $qtde_itens_pagos; if($adm) echo " - R$ ".$valor_itens_vendidos; ?></p>
				</a>
				<a href="<? echo $item_link; ?>&a=aguardando" class="fancybox fancybox.iframe width800 dados float aguardando">
					<span></span>
					<h3>Aguardando Pagamento</h3>
					<p><? echo $qtde_itens_aguardando; if($adm) echo " - R$ ".$valor_itens_aguardando; ?></p>
				</a>
				<a href="<? echo $item_link; ?>&a=reservados" class="fancybox fancybox.iframe width800 dados float reservados">
					<span></span>
					<h3>Ingressos Reservados</h3>
					<p><? echo $qtde_itens_reservados; if($adm) echo " - R$ ".$valor_itens_reservados; ?></p>
				</a>
				<div class="dados float venda">
					<span></span>
					<h3>Ingressos à Venda</h3>
					<p><? echo ($qtde_ingressos_venda - $qtde_itens_vendidos)."/".$qtde_ingressos_venda; ?></p>
				</div>
				<div class="dados float">
					<span></span>
					<h3>Ingressos em Estoque</h3>
					<p <? if($qtde_ingressos_estoque < 0) echo 'class="negativo"' ?>><? echo ($qtde_ingressos_estoque)."/".$qtde_ingressos_compra; ?></p>
				</div>
				<?*/ ?>
				<a href="<? echo $item_link; ?>&a=pagos" class="fancybox fancybox.iframe width800 dados float ingressos">
					<span></span>
					<h3>Pagos</h3>
					<p><? echo $qtde_itens_pagos; ?> ingresso<? if($qtde_itens_pagos != 1) { echo 's'; }  if($adm) echo "<br />R$ ".$valor_itens_vendidos; ?></p>
				</a>
				<a href="<? echo $item_link; ?>&a=aguardando" class="fancybox fancybox.iframe width800 dados float aguardando">
					<span></span>
					<h3>Aguardando Pgto.</h3>
					<p><? echo $qtde_itens_aguardando; ?> ingresso<? if($qtde_itens_aguardando != 1) { echo 's'; }  if($adm) echo "<br />R$ ".$valor_itens_aguardando; ?></p>
				</a>
				<a href="<? echo $item_link; ?>&a=reservados" class="fancybox fancybox.iframe width800 dados float reservados">
					<span></span>
					<h3>Reservados</h3>
					<p><? echo $qtde_itens_reservados; ?> ingresso<? if($qtde_itens_reservados != 1) { echo 's'; }  if($adm) echo "<br />R$ ".$valor_itens_reservados; ?></p>
				</a>
				<a href="<? echo $item_link; ?>&a=permuta" class="fancybox fancybox.iframe width800 dados float permuta">
					<span></span>
					<h3>Cortesia/Permuta</h3>
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
				</div>
				<div class="clear"></div>

				<?

				if($adm == ($tag_tipo == 'frisa')) {
					
					//Buscar por fila
					$sql_filas = sqlsrv_query($conexao, "DECLARE @dias TABLE (dia INT, setor INT, nome VARCHAR(255));
						DECLARE @compras TABLE (fila VARCHAR(255), dia INT, setor INT, ingressos_compra INT, ingressos_compra_estoque INT);
						DECLARE @vendas TABLE (fila VARCHAR(255), dia INT, setor INT, ingressos_dia_venda INT);
						DECLARE @ingressos_dia_vendidos TABLE (fila VARCHAR(255), dia INT, setor INT, ingressos_dia_vendidos INT, valor_vendidos DECIMAL(10,2), valor_aguardando DECIMAL(10,2), valor_reservados DECIMAL(10,2), itens_tipo_pagos INT, itens_tipo_aguardando INT, itens_tipo_reserva INT);
						
						INSERT INTO @dias (dia, setor, nome)
						SELECT
						ed.ED_COD,
						es.ES_COD,
						es.ES_NOME
						FROM eventos_dias ed, eventos_setores es WHERE ed.ED_COD='$dia' AND ed.ED_EVENTO='$evento' AND es.ES_EVENTO='$evento' AND es.ES_BLOCK='0' AND ed.D_E_L_E_T_='0' AND es.D_E_L_E_T_='0' ORDER BY ed.ED_COD;

						INSERT INTO @compras (fila, dia, setor, ingressos_compra, ingressos_compra_estoque)
						SELECT 
						CO_FILA, CO_DIA, CO_SETOR,
						ISNULL(COUNT(CO_COD), 0), 
						ISNULL(SUM(CO_ESTOQUE), 0)
						FROM compras WHERE CO_SETOR='$setor' AND CO_EVENTO='$evento' AND CO_TIPO='$tipo' AND CO_BLOCK='0' AND D_E_L_E_T_='0' AND CO_ESTOQUE IS NULL GROUP BY CO_FILA, CO_DIA, CO_SETOR;

						INSERT INTO @vendas (fila, dia, setor, ingressos_dia_venda)
						SELECT
						VE_FILA,
						VE_DIA,
						VE_SETOR,
						ISNULL(SUM(VE_ESTOQUE),0) FROM vendas WHERE VE_SETOR='$setor' AND VE_EVENTO='$evento' AND VE_TIPO='$tipo' AND VE_BLOCK='0' AND D_E_L_E_T_='0'  GROUP BY VE_FILA, VE_DIA, VE_SETOR;

						INSERT INTO @ingressos_dia_vendidos (fila, dia, setor, ingressos_dia_vendidos, valor_vendidos, valor_aguardando, valor_reservados, itens_tipo_pagos, itens_tipo_aguardando, itens_tipo_reserva)
						SELECT ve.VE_FILA, ve.VE_DIA, ve.VE_SETOR,
						ISNULL(COUNT(li.LI_COD), 0),

						SUM(CASE WHEN lo.LO_PAGO='1' THEN (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) ELSE 0 END), 
						SUM(CASE WHEN lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO<>'".CODRESERVA."' THEN (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) ELSE 0 END), 
						SUM(CASE WHEN lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO='".CODRESERVA."' THEN (li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) ELSE 0 END), 
						SUM(CASE WHEN lo.LO_PAGO='1' THEN 1 ELSE 0 END), 
						SUM(CASE WHEN lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO<>'".CODRESERVA."' THEN 1 ELSE 0 END),
						SUM(CASE WHEN lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO='".CODRESERVA."' THEN 1 ELSE 0 END)

						FROM loja_itens li, loja lo, vendas ve WHERE lo.LO_COD=li.LI_COMPRA AND li.LI_INGRESSO=ve.VE_COD AND ve.VE_SETOR='$setor' AND ve.VE_EVENTO='$evento' AND ve.VE_TIPO='$tipo' AND ve.VE_BLOCK='0' AND ve.D_E_L_E_T_='0' AND lo.D_E_L_E_T_='0' AND li.D_E_L_E_T_='0' GROUP BY ve.VE_FILA, ve.VE_DIA, ve.VE_SETOR;

						
						SELECT 

						d.dia AS ED_COD, d.setor AS ES_COD, d.nome AS ES_NOME, v.fila AS VE_FILA,
						ISNULL(c.ingressos_compra, 0) AS ingressos_compra,
						ISNULL(c.ingressos_compra_estoque, 0) AS ingressos_compra_estoque,
						ISNULL(v.ingressos_dia_venda, 0) AS ingressos_dia_venda,
						ISNULL(dv.ingressos_dia_vendidos, 0) AS ingressos_dia_vendidos,
						ISNULL(c.ingressos_compra+c.ingressos_compra_estoque, 0) AS total_ingressos_compra,

						ISNULL(dv.valor_vendidos, 0) AS valor_vendidos,
						ISNULL(dv.valor_aguardando, 0) AS valor_aguardando,
						ISNULL(dv.valor_reservados, 0) AS valor_reservados,
						ISNULL(dv.itens_tipo_pagos, 0) AS itens_tipo_pagos,
						ISNULL(dv.itens_tipo_aguardando, 0) AS itens_tipo_aguardando,
						ISNULL(dv.itens_tipo_reserva, 0) AS itens_tipo_reserva

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


							$filas_itens_dia = $filas['ingressos_venda'];
							$filas_itens_pagos = $filas['itens_tipo_pagos'];
							$filas_itens_aguardando = $filas['itens_tipo_aguardando'];
							$filas_itens_reservados = $filas['itens_tipo_reserva'];

							$filas_valor_itens_vendidos = $filas['valor_vendidos'];
							$filas_valor_itens_aguardando = $filas['valor_aguardando'];
							$filas_valor_itens_reservados = $filas['valor_reservados'];

							$filas_valor_itens_vendidos = number_format($filas_valor_itens_vendidos, 2, ',', '.');
							$filas_valor_itens_aguardando = number_format($filas_valor_itens_aguardando, 2, ',', '.');
							$filas_valor_itens_reservados = number_format($filas_valor_itens_reservados, 2, ',', '.');

							$porcentagem_grafico_pagos = ($filas_itens_pagos > 0) ? (($filas_itens_pagos*100)/$filas_ingressos_venda) : 0;
							$porcentagem_grafico_aguardando = ($filas_itens_aguardando > 0) ? (($filas_itens_aguardando*100)/$filas_ingressos_venda) : 0;
							$porcentagem_grafico_reservados = ($filas_itens_reservados > 0) ? (($filas_itens_reservados*100)/$filas_ingressos_venda) : 0;

							$item_link = SITE.'relatorios-lista-voucher.php?tipo='.$tipo.'&dia='.$dia.'&setor='.$setor.'&fila='.$filas_fila;
							
							?>
							<section class="filas">
								<h2>Fila <? echo utf8_encode($filas_fila); ?></h2>
								<div class="grafico horizontal">
									<span class="ingressos" style="width: <? echo str_replace(',', '.', $porcentagem_grafico_pagos); ?>%"></span>
									<span class="aguardando" style="width: <? echo str_replace(',', '.', $porcentagem_grafico_aguardando); ?>%"></span>
									<span class="reservados" style="width: <? echo str_replace(',', '.', $porcentagem_grafico_reservados); ?>%"></span>
								</div>

								<div class="grupo">
									<a href="<? echo $item_link; ?>&a=ingressos" class="fancybox fancybox.iframe width800 dados ingressos">
										<span></span>
										<p><? echo $filas_itens_pagos; if($adm) echo " - R$ ".$filas_valor_itens_vendidos; ?></p>
									</a>
									<a href="<? echo $item_link; ?>&a=aguardando" class="fancybox fancybox.iframe width800 dados aguardando">
										<span></span>
										<p><? echo $filas_itens_aguardando; if($adm) echo " - R$ ".$filas_valor_itens_aguardando; ?></p>
									</a>
									<a href="<? echo $item_link; ?>&a=reservados" class="fancybox fancybox.iframe width800 dados reservados">
										<span></span>
										<p><? echo $filas_itens_reservados; if($adm) echo " - R$ ".$filas_valor_itens_reservados; ?></p>
									</a>
									<div class="dados venda">
										<span></span>
										<p><? echo ($filas_ingressos_venda - $filas_itens_vendidos)."/".$filas_ingressos_venda; ?></p>
									</div>
									<div class="dados">
										<span></span>
										<p <? if($filas_ingressos_estoque < 0) echo 'class="negativo"' ?>><? echo ($filas_ingressos_estoque)."/".$filas_ingressos_compra; ?></p>
									</div>									

									<div class="clear"></div>
								</div>
								<?

								//Exclusividade
								$sql_exclusividade = sqlsrv_query($conexao, "SELECT * 
									FROM  (
										SELECT 
										li.LI_EXCLUSIVIDADE_VAL AS exclusividade, 
										ISNULL(COUNT(li.LI_COD), 0) AS quantidade,
										SUM(CASE WHEN lo.LO_PAGO='1' THEN 1 ELSE 0 END) AS pago,
										SUM(CASE WHEN lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO<>'".CODRESERVA."' THEN 1 ELSE 0 END) AS aguardando,
										SUM(CASE WHEN lo.LO_PAGO='0' AND lo.LO_FORMA_PAGAMENTO='".CODRESERVA."' THEN 1 ELSE 0 END) AS reservado
										FROM loja_itens li, loja lo, vendas ve 
										WHERE li.LI_EXCLUSIVIDADE=1 AND ve.VE_FILA='$filas_fila' AND ve.VE_DIA='$dia' AND lo.LO_COD=li.LI_COMPRA AND li.LI_INGRESSO=ve.VE_COD AND ve.VE_SETOR='$setor' 
										AND ve.VE_EVENTO='$evento' AND ve.VE_TIPO='$tipo' AND ve.VE_BLOCK='0' AND ve.D_E_L_E_T_='0' AND lo.D_E_L_E_T_='0' AND li.D_E_L_E_T_='0' 
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

	} else {
	?>
	<section class="secao">Nenhum item encontrado.</section>
	<?
	}
	?>
	<footer class="controle">
		<a href="<? echo $_SERVER['HTTP_REFERER']; ?>" class="cancel coluna">Voltar</a>
		<div class="clear"></div>
	</footer>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>