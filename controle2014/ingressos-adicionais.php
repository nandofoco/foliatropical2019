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
if(!empty($q)) $search = is_numeric($q) ? " AND VA_COD='$q' " : " AND (VA_LABEL LIKE '%$q%' OR VA_NOME_EXIBICAO LIKE '%$q%') ";

$sql_adicionais = sqlsrv_query($conexao, "SELECT * FROM vendas_adicionais WHERE D_E_L_E_T_=0 $search ORDER BY VA_COD ASC", $conexao_params, $conexao_options);
$n_adicionais = sqlsrv_num_rows($sql_adicionais);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Ingresso Adicionais <span>Listagem</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>ingressos/adicionais/">
			<a href="<? echo SITE; ?>ingressos/adicionais/novo/" class="adicionar">+</a>
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>ingressos/adicionais/" class="limpar-busca">&times;</a><? } ?>
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
					<th><strong>Cód.</strong> <span></span></th>
					<th><strong>Nome</strong> <span></span></th>
					<th><strong>Tag</strong> <span></span></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="5">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_adicionais > 0)	 {

				$i=1;
				while($adicionais = sqlsrv_fetch_array($sql_adicionais)) {

					$adicionais_cod = $adicionais['VA_COD'];
					$adicionais_nome = utf8_encode($adicionais['VA_LABEL']);
					$adicionais_tag = utf8_encode($adicionais['VA_NOME_EXIBICAO']);
					$adicionais_block = (bool) $adicionais['VA_BLOCK'];

					$acao = ($adicionais_block) ? 'desbloquear' : 'bloquear';
					
					?>
					<tr <? if($adicionais_block) { echo 'class="block"'; } ?>>
						<td class="block"><a href="<? echo SITE; ?>e-ingressos-adicionais-gerenciar.php?c=<? echo $adicionais_cod; ?>&a=<? echo $acao; ?>" class="block confirm" title="Tem certeza que deseja <? echo $acao; ?> o adicional?"></a></td>
						<td class="cod"><? echo $adicionais_cod; ?></td>
						<td><? echo $adicionais_nome; ?></td>
						<td><? echo $adicionais_tag; ?></td>
						<td class="ctrl small">
							<a href="<? echo SITE; ?>e-ingressos-adicionais-gerenciar.php?c=<? echo $adicionais_cod; ?>&a=excluir" class="excluir confirm" title="Tem certeza que deseja excluir o adicional?"></a>
						</td>
					</tr>
					<?
					$i++;
				}
			} else {
				?>
				<tr><td class="nenhum" colspan="5">Nenhum adicional encontrado</td></tr>
				<?
			}
			?>
			</tbody>
		</table>

		<? if ($n_adicionais > 0) { ?>
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