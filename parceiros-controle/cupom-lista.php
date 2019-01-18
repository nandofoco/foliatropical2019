<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$q = format($_GET['q']);
// if(!empty($q)) $search = "AND (CP_NOME LIKE '%$q%' OR CP_CUPOM LIKE '%$q%' OR tipo='$q')";
if(!empty($q)) $search = "AND (CP_NOME LIKE '%$q%' OR CP_CUPOM LIKE '%$q%')";

$sql_cupons = sqlsrv_query($conexao, "SELECT *, CONVERT(CHAR, CP_DATA_VALIDADE, 103) AS validade,
	case
	   when CP_TIPO='1' then 'Porcentagem'
	   when CP_TIPO='2' then 'Valor'
	end as tipo
FROM cupom WHERE D_E_L_E_T_='0' AND CP_UTILIZADO='0' $search ORDER BY CP_DATA_VALIDADE DESC", $conexao_params, $conexao_options);
$n_cupons = sqlsrv_num_rows($sql_cupons);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Cupons <span>Listagem</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>cupons/">
			<a href="<? echo SITE; ?>cupom/cadastro/" class="adicionar">+</a>
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>cupons/" class="limpar-busca">&times;</a><? } ?>
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
					<th><strong>Validade</strong> <span></span></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_cupons > 0)	 {

				$i=1;
				while($cupom = sqlsrv_fetch_array($sql_cupons)) {

					$cupom_cod = $cupom['CP_COD'];
					$cupom_nome = utf8_encode($cupom['CP_NOME']);
					$cupom_codigo = $cupom['CP_CUPOM'];
					$cupom_validade = $cupom['validade'];
					$cupom_valor = $cupom['CP_DESCONTO'];
					$cupom_tipo = $cupom['CP_TIPO'];

					$valor = ($cupom_tipo == 1) ? round($cupom_valor)."%" : 'R$ '.number_format($cupom_valor, 2, ',', '.');

					$cupom_block = (bool) $cupom['CP_BLOCK'];
					$acao = ($cupom_block) ? 'desbloquear' : 'bloquear';

					?>
					<tr <? if($cupom_block) { echo 'class="block"'; } ?>>
						<td class="block"><a href="<? echo SITE; ?>e-cupom-gerenciar.php?c=<? echo $cupom_cod; ?>&a=<? echo $acao; ?>" class="block confirm" title="Tem certeza que deseja <? echo $acao; ?> esse cupom?"></a></td>
						<td><? echo $cupom_nome; ?></td>
						<td><? echo $cupom_codigo; ?></td>
						<td><? echo $valor; ?></td>
						<td><? echo $cupom_validade; ?></td>
						<td class="ctrl">							
							<a href="<? echo SITE; ?>e-cupom-gerenciar.php?c=<? echo $cupom_cod; ?>&a=deletar" class="excluir confirm" title="Tem certeza que deseja excluir esse cupom?"></a>
						</td>
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