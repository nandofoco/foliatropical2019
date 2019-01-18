<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$q = $_GET['q'];
if(!empty($q)) $search = " AND RO_NOME LIKE '%$q%'";

$sql_roteiros = sqlsrv_query($conexao, "SELECT RO_COD, RO_NOME, RO_BLOCK, RO_TIPO,
	case  
	   when RO_TIPO='1' then 'Pares'
	   when RO_TIPO='2' then 'Ímpares'
	   when RO_TIPO='3' then 'Folia Tropical'
	   when RO_TIPO='4' then 'Camarote'
	end as tipo
	FROM roteiros WHERE D_E_L_E_T_='0' $search ORDER BY RO_NOME ASC", $conexao_params, $conexao_options);
$n_roteiros = sqlsrv_num_rows($sql_roteiros);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Roteiros <span>Listagem</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>roteiros/">
			<a href="<? echo SITE; ?>roteiros/cadastro/" class="adicionar">+</a>
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>roteiros/" class="limpar-busca">&times;</a><? } ?>
				<input type="text" name="q" class="input" id="busca-lista-input" value="<? echo format(utf8_encode($q)); ?>" />
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
					<th><strong>Tipo</strong><span></span></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_roteiros > 0)	 {
				$i=1;
				while($roteiros = sqlsrv_fetch_array($sql_roteiros)) {			
					$roteiros_cod = $roteiros['RO_COD'];
					$roteiros_nome = utf8_encode($roteiros['RO_NOME']);
					$roteiros_tipo = $roteiros['tipo'];

					$roteiros_block = (bool) $roteiros['RO_BLOCK'];
					$acao = ($roteiros_block) ? 'desbloquear' : 'bloquear';
					?>
					<tr <? if($roteiros_block) { echo 'class="block"'; } ?>>
						<td class="block"><a href="<? echo SITE; ?>e-roteiro-gerenciar.php?c=<? echo $roteiros_cod; ?>&a=<? echo $acao; ?>" class="block confirm" title="Tem certeza que deseja <? echo $acao; ?> esse roteiro?"></a></td>
						<td><? echo $roteiros_nome; ?></td>
						<td><? echo $roteiros_tipo; ?></td>
						<td class="ctrl">
							<a href="<? echo SITE; ?>roteiros/editar/<? echo $roteiros_cod; ?>/" class="ver"></a>
							<a href="<? echo SITE; ?>e-roteiro-gerenciar.php?c=<? echo $roteiros_cod; ?>&a=deletar" class="excluir confirm" title="Tem certeza que deseja excluir esse roteiro?"></a>
						</td>
					</tr>
					<?
					$i++;
				}
			} else {
			?>
				<tr>
					<td colspan="4" class="nenhum">Nenhum roteiro encontrado.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<? if ($n_roteiros > 0) { ?>
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