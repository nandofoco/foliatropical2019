<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];

//-----------------------------------------------------------------//

$q = format($_GET['q']);
if(!empty($q)) $search = is_numeric($q) ? " AND (c.COD='$q' OR c.GRUPO='$q') " : " AND (c.VALOR LIKE '%$q%' OR t.TI_NOME LIKE '%$q%' OR d.ED_NOME LIKE '%$q%' OR s.ES_NOME LIKE '%$q%') ";

$sql_ingressos = sqlsrv_query($conexao, 
	"DECLARE @compras TABLE(COD INT, GRUPO INT, TIPO INT, SETOR INT, DIA INT, FILA VARCHAR(255), NIVEL VARCHAR(255), VALOR DECIMAL(10,2), QTDE INT, ESTOQUE INT, BLOCK TINYINT);

	INSERT INTO @compras (COD, GRUPO, TIPO, SETOR, DIA, FILA, NIVEL, VALOR, QTDE, ESTOQUE, BLOCK)
	SELECT MIN(c.CO_COD), c.CO_GRUPO, c.CO_TIPO, c.CO_SETOR, c.CO_DIA, c.CO_FILA, c.CO_NIVEL, c.CO_VALOR, COUNT(c.CO_COD) AS QTDE, ISNULL(SUM(c.CO_ESTOQUE),0) AS ESTOQUE, MAX(CO_BLOCK) FROM compras c
	WHERE c.CO_EVENTO='$evento' AND c.D_E_L_E_T_=0 
	GROUP BY c.CO_GRUPO, c.CO_TIPO, c.CO_SETOR, c.CO_DIA, c.CO_FILA, c.CO_NIVEL, c.CO_VALOR

	SELECT c.COD AS CO_COD, c.GRUPO AS CO_GRUPO, c.TIPO AS CO_TIPO, c.SETOR AS CO_SETOR, c.DIA AS CO_DIA, c.FILA AS CO_FILA, c.NIVEL AS CO_NIVEL, c.VALOR AS CO_VALOR, c.QTDE, c.ESTOQUE, c.BLOCK AS CO_BLOCK,
	t.TI_NOME, d.ED_NOME, s.ES_NOME FROM @compras c 
	LEFT JOIN tipos t ON t.TI_COD=c.TIPO
	LEFT JOIN eventos_dias d ON d.ED_COD=c.DIA
	LEFT JOIN eventos_setores s ON s.ES_COD=c.SETOR
	WHERE d.D_E_L_E_T_=0 AND 
	t.D_E_L_E_T_=0 AND 
	s.D_E_L_E_T_=0 $search
	ORDER BY s.ES_NOME ASC, t.TI_NOME ASC, d.ED_DATA ASC", $conexao_params, $conexao_options);

if(sqlsrv_next_result($sql_ingressos))
$n_ingressos = sqlsrv_num_rows($sql_ingressos);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Ingresso compra <span>Listagem</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>ingressos/compra/">
			<a href="<? echo SITE; ?>ingressos/compra/novo/" class="adicionar">+</a>
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>ingressos/compra/" class="limpar-busca">&times;</a><? } ?>
				<input type="text" name="q" class="input" id="busca-lista-input" value="<? echo utf8_encode($q); ?>" />
			</p>
			<input type="submit" class="submit" value="" />
		</form>
	</header>
	<section class="secao bottom">
		<table class="lista tablesorter">
			<thead>
				<tr>
					<th>&nbsp;</th>
					<th><strong>Num.</strong> <span></span></th>
					<th><strong>Setor</strong> <span></span></th>
					<th><strong>Qtde.</strong> <span></span></th>
					<th><strong>Data Ref.</strong> <span></span></th>
					<th><strong>Tipo do Ingresso</strong> <span></span></th>
					<th class="right"><span></span> <strong>Valor Un.</strong></th>
					<th class="right"><span></span> <strong>Valor Total.</strong></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="9">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_ingressos !== false) {

				$i=1;
				while($ingressos = sqlsrv_fetch_array($sql_ingressos)) {

					$ingressos_cod = $ingressos['CO_COD'];
					$ingressos_grupo = $ingressos['CO_GRUPO'];
					$ingressos_quantidade = $ingressos['QTDE'];
					$ingressos_setor = utf8_encode($ingressos['ES_NOME']);
					$ingressos_dia = utf8_encode($ingressos['ED_NOME']);
					$ingressos_tipo = utf8_encode($ingressos['TI_NOME']);
					$ingressos_valor = number_format($ingressos['CO_VALOR'],2,",",".");
					// $ingressos_valor_total = number_format(($ingressos['CO_VALOR'] * $ingressos_quantidade),2,",",".");

					$ingressos_block = (bool) $ingressos['CO_BLOCK'];

					$ingressos_estoque = utf8_encode($ingressos['ESTOQUE']);
					$ingressos_fila = utf8_encode($ingressos['CO_FILA']);
					$ingressos_nivel = utf8_encode($ingressos['CO_NIVEL']);
					$ingressos_numero = utf8_encode($ingressos['CO_NUMERO']);
					$ingressos_vaga = utf8_encode($ingressos['CO_VAGA']);
					$acao = ($ingressos_block) ? 'desbloquear' : 'bloquear';
					
					// Para ingressos que tem estoque e nao sao agrupados
					// if(($ingressos_quantidade == 1) && ($ingressos_estoque > 0)) $ingressos_quantidade = $ingressos_estoque;
					if($ingressos_estoque > 0) $ingressos_quantidade = $ingressos_estoque;

					$ingressos_valor_total = number_format(($ingressos['CO_VALOR'] * $ingressos_quantidade),2,",",".");
					
					?>
					<tr <? if($ingressos_block) { echo 'class="block"'; } ?>>
						<td class="block"><a href="<? echo SITE; ?>e-ingressos-compra-gerenciar.php?c=<? echo $ingressos_cod; ?>&a=<? echo $acao; ?>" class="block confirm" title="Tem certeza que deseja <? echo $acao; ?> os ingressos?"></a></td>
						<td class="qtde"><? echo $ingressos_grupo; ?></td>
						<td class="setor"><? echo $ingressos_setor; ?></td>
						<td class="qtde"><? echo $ingressos_quantidade; ?></td>
						<td class="data"><? echo $ingressos_dia; ?> dia</td>
						<td class="tipo">
						<?
							echo $ingressos_tipo;
							if(!empty($ingressos_fila)) { echo " ".$ingressos_fila; }
							if(!empty($ingressos_nivel)) { echo " Nível ".$ingressos_nivel; }
						?>
						</td>
						<td class="valor">R$ <? echo $ingressos_valor; ?></td>
						<td class="valor">R$ <? echo $ingressos_valor_total; ?></td>
						<td class="ctrl">
							<a href="<? echo SITE; ?>ingressos/compra/editar/<? echo $ingressos_cod; ?>/" class="ver"></a>
							<a href="<? echo SITE; ?>e-ingressos-compra-gerenciar.php?c=<? echo $ingressos_cod; ?>&a=excluir" class="excluir confirm" title="Tem certeza que deseja excluir os ingressos?"></a>
						</td>
					</tr>
					<?
					$i++;
				}
			} else {
				?>
				<tr><td class="nenhum" colspan="9">Nenhum ingresso encontrado</td></tr>
				<?
			}

			?>
			</tbody>
		</table>

		<? if ($n_ingressos !== false) { ?>
        <div class="pager-tablesorter">
	        <a href="#" class="first"></a>
	        <a href="#" class="prev"></a>
	        <span class="pagedisplay"></span>
	        <a href="#" class="next"></a>
	        <a href="#" class="last"></a>
	        <input type="hidden" class="pagesize" value="30" />
        </div>
        <? } ?>
	</section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>