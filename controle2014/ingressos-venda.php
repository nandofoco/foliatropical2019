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
if(!empty($q)) $search = is_numeric($q) ? " AND v.VE_COD='$q' " : " AND (v.VE_VALOR LIKE '%$q%' OR t.TI_NOME LIKE '%$q%' OR d.ED_NOME LIKE '%$q%' OR s.ES_NOME LIKE '%$q%') ";

$sql_ingressos = sqlsrv_query($conexao, "SELECT v.*, t.TI_NOME, d.ED_NOME, s.ES_NOME FROM vendas v, tipos t, eventos_dias d, eventos_setores s WHERE v.VE_EVENTO='$evento' AND v.D_E_L_E_T_=0 AND d.ED_COD=v.VE_DIA AND t.TI_COD=v.VE_TIPO AND s.ES_COD=v.VE_SETOR AND d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0 $search ORDER BY s.ES_NOME ASC, t.TI_NOME ASC, d.ED_DATA ASC", $conexao_params, $conexao_options);
$n_ingressos = sqlsrv_num_rows($sql_ingressos);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Ingresso venda <span>Listagem</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>ingressos/venda/">
			<a href="<? echo SITE; ?>ingressos/venda/novo/" class="adicionar">+</a>
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>ingressos/venda/" class="limpar-busca">&times;</a><? } ?>
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
					<th><strong>Setor</strong> <span></span></th>
					<th><strong>QTDE.</strong> <span></span></th>
					<th><strong>Data Ref.</strong> <span></span></th>
					<th><strong>Tipo do Ingresso</strong> <span></span></th>
					<th class="right"><span></span> <strong>Valor Un.</strong></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="7">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_ingressos > 0) {

				$i=1;
				while($ingressos = sqlsrv_fetch_array($sql_ingressos)) {

					$ingressos_cod = $ingressos['VE_COD'];
					$ingressos_setor = utf8_encode($ingressos['ES_NOME']);
					$ingressos_dia = utf8_encode($ingressos['ED_NOME']);
					$ingressos_tipo = utf8_encode($ingressos['TI_NOME']);
					$ingressos_estoque = $ingressos['VE_ESTOQUE'];
					$ingressos_valor = number_format($ingressos['VE_VALOR'],2,",",".");
					$ingressos_valor_total = number_format($ingressos['VE_VALOR'],2,",",".");
					$ingressos_block = (bool) $ingressos['VE_BLOCK'];

					$ingressos_fila = utf8_encode($ingressos['VE_FILA']);
					$ingressos_vaga = utf8_encode($ingressos['VE_VAGAS']);
					$ingressos_tipo_especifico = utf8_encode($ingressos['VE_TIPO_ESPECIFICO']);
					
					if(($ingressos_vaga > 0) && ($ingressos_tipo_especifico == 'fechado')) $ingressos_estoque = $ingressos_estoque / $ingressos_vaga;

					$acao = ($ingressos_block) ? 'desbloquear' : 'bloquear';
					
					?>
					<tr <? if($ingressos_block) { echo 'class="block"'; } ?>>
						<td class="block"><a href="<? echo SITE; ?>e-ingressos-venda-gerenciar.php?c=<? echo $ingressos_cod; ?>&a=<? echo $acao; ?>" class="block confirm" title="Tem certeza que deseja <? echo $acao; ?> o ingresso?"></a></td>
						<td class="setor"><? echo $ingressos_setor; ?></td>
						<td class="qtde"><? echo $ingressos_estoque; ?></td>
						<td class="data"><? echo $ingressos_dia; ?> dia</td>
						<td class="tipo">
						<?

							echo $ingressos_tipo;
							if(!empty($ingressos_fila)) { echo " ".$ingressos_fila; }
							if(!empty($ingressos_tipo_especifico)) { echo " ".$ingressos_tipo_especifico; }
							if(($ingressos_vaga > 0) && ($ingressos_tipo_especifico == 'fechado')) { echo " (".$ingressos_vaga." vagas)"; }
						?>
						</td>
						<td class="valor">R$ <? echo $ingressos_valor; ?></td>
						<td class="ctrl">
							<a href="<? echo SITE; ?>ingressos/venda/editar/<? echo $ingressos_cod; ?>/" class="ver"></a>
							<a href="<? echo SITE; ?>e-ingressos-venda-gerenciar.php?c=<? echo $ingressos_cod; ?>&a=excluir" class="excluir confirm" title="Tem certeza que deseja excluir o ingresso?"></a>
						</td>
					</tr>
					<?
					$i++;
				}
			} else {
				?>
				<tr><td class="nenhum" colspan="7">Nenhum ingresso encontrado</td></tr>
				<?
			}

			?>
			</tbody>
		</table>

		<? if ($n_ingressos > 0) { ?>
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