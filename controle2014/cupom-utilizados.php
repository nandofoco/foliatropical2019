<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$q = format($_GET['q']);
if(!empty($q)) $search = "AND (cp.CP_NOME LIKE '%$q%' OR cp.CP_CUPOM LIKE '%$q%')";

$sql_cupons = sqlsrv_query($conexao, "SELECT cp.*, lo.*, (CONVERT(VARCHAR, cp.CP_DATA_UTILIZACAO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, cp.CP_DATA_UTILIZACAO, 108),1,5)) AS utilizacao,
	case
	   when cp.CP_TIPO='1' then 'Porcentagem'
	   when cp.CP_TIPO='2' then 'Valor'
	end as tipo
FROM cupom cp, loja lo WHERE cp.CP_COMPRA=lo.LO_COD AND cp.D_E_L_E_T_='0' AND cp.CP_UTILIZADO='1' $search ORDER BY cp.CP_DATA_UTILIZACAO DESC", $conexao_params, $conexao_options);
$n_cupons = sqlsrv_num_rows($sql_cupons);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Cupons <span>Utilizados</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>cupons/utilizados/">
			<a href="<? echo SITE; ?>cupom/cadastro/" class="adicionar">+</a>
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>cupons/utilizados/" class="limpar-busca">&times;</a><? } ?>
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
					<th><strong>Nome</strong><span></span></th>
					<th><strong>Cupom</strong><span></span></th>
					<th><strong>Desconto</strong> <span></span></th>
					<th><strong>Data de Utilização</strong> <span></span></th>
					<th><strong>Valor Parcial</strong> <span></span></th>
					<th><strong>Valor com Desconto</strong> <span></span></th>
				</tr>
				<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_cupons > 0) {

				$i=1;
				while($cupom = sqlsrv_fetch_array($sql_cupons)) {

					$cupom_cod = $cupom['CP_COD'];
					$cupom_compra = $cupom['CP_COMPRA'];
					$cupom_nome = utf8_encode($cupom['CP_NOME']);
					$cupom_codigo = $cupom['CP_CUPOM'];
					$cupom_utilizacao = $cupom['utilizacao'];
					$cupom_valor = $cupom['CP_DESCONTO'];
					$cupom_valor_parcial = "R$ ".number_format($cupom['LO_VALOR_PARCIAL'], 2, ',', '.');
					$cupom_valor_total = "R$ ".number_format($cupom['LO_VALOR_TOTAL'], 2, ',', '.');


					$cupom_tipo = $cupom['CP_TIPO'];
					$valor = ($cupom_tipo == 1) ? round($cupom_valor)."%" : 'R$ '.number_format($cupom_valor, 2, ',', '.');

					$cupom_block = (bool) $cupom['CP_BLOCK'];
					$acao = ($cupom_block) ? 'desbloquear' : 'bloquear';

					?>
					
					<tr>
						<td class="first"></td>
						<td><? echo $cupom_nome; ?></td>
						<td><? echo $cupom_codigo; ?></td>
						<td><? echo $valor; ?></td>
						<td><? echo $cupom_utilizacao; ?></td>
						<td><? echo $cupom_valor_parcial; ?></td>
						<td><? echo $cupom_valor_total; ?></td>
					</tr>
					<?
					$i++;
				}
			} else {
			?>
				<tr>
					<td colspan="6" class="nenhum">Nenhum cupom encontrado.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>

		<? if ($n_cupons > 0) { ?>
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