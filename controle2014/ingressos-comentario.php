<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");

//-----------------------------------------------------------------//


$evento = (int) $_SESSION['usuario-carnaval'];
$cod = (int) $_GET['c'];
$coditem = (int) $_GET['i'];

if((!empty($cod) || !empty($coditem)) && !empty($evento)) {

	if(!empty($cod)) $sql_item = sqlsrv_query($conexao, "SELECT lc.LC_COMENTARIO, li.*, v.VE_DIA, v.VE_SETOR, es.ES_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME FROM loja_itens li, loja_comentarios lc, vendas v, eventos_setores es, eventos_dias ed, tipos tp WHERE lc.LC_COD='$cod' AND li.LI_COD=lc.LC_ITEM AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD /*AND li.D_E_L_E_T_='0'*/ ORDER BY LI_COD ASC", $conexao_params, $conexao_options);
	if(!empty($coditem)) $sql_item = sqlsrv_query($conexao, "SELECT TOP 1 li.*, v.VE_DIA, v.VE_SETOR, es.ES_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME FROM loja_itens li, vendas v, eventos_setores es, eventos_dias ed, tipos tp WHERE li.LI_COD='$coditem' AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD /*AND li.D_E_L_E_T_='0'*/ ORDER BY LI_COD ASC", $conexao_params, $conexao_options);

	if(sqlsrv_num_rows($sql_item) !== false) {
		$i = 1;
		while ($item = sqlsrv_fetch_array($sql_item)) {
			$item_cod = $item['LI_COD'];
			$item_id = $item['LI_ID'];
			$item_compra = $item['LI_COMPRA'];
			$item_nome = utf8_encode($item['LI_NOME']);
			$item_tipo = utf8_encode($item['TI_NOME']);
			$item_dia = utf8_encode($item['dia']);
			$item_setor = $item['ES_NOME'];
			$item_valor = number_format($item['LI_VALOR'], 2, ",", ".");

			if(!empty($coditem)) {
				$sql_item_comentario = sqlsrv_query($conexao, "SELECT TOP 1 lc.LC_COD, lc.LC_COMENTARIO FROM loja_itens li, loja_comentarios lc WHERE lc.LC_ITEM=li.LI_COD AND li.LI_COD='$item_cod' AND li.D_E_L_E_T_=0", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_item_comentario) > 0){
					$ar_item_comentario = sqlsrv_fetch_array($sql_item_comentario);
					$item_comentario = utf8_encode($ar_item_comentario['LC_COMENTARIO']);
					$cod = $ar_item_comentario['LC_COD'];
				}

				$sql_item_comentario_interno = sqlsrv_query($conexao, "SELECT TOP 1 lc.LC_COD, lc.LC_COMENTARIO FROM loja_itens li, loja_comentarios_internos lc WHERE lc.LC_ITEM=li.LI_COD AND li.LI_COD='$item_cod' AND li.D_E_L_E_T_=0", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_item_comentario_interno) > 0){
					$ar_item_comentario_interno = sqlsrv_fetch_array($sql_item_comentario_interno);
					$item_comentario_interno = utf8_encode($ar_item_comentario_interno['LC_COMENTARIO']);
					$cod_interno = $ar_item_comentario_interno['LC_COD'];
				}
			} else {
				$item_comentario = utf8_encode($item['LC_COMENTARIO']);
			}

?>
<section id="conteudo" class="comentario">

	<form id="comentario" method="post" action="<? echo SITE; ?>ingressos/comentario/post/">

		<?
		if(!empty($cod)) { ?><input type="hidden" name="cod" value="<? echo $cod; ?>" /><? } 
		if(!empty($cod_interno)) { ?><input type="hidden" name="codinterno" value="<? echo $cod_interno; ?>" /><? } 
		if(!empty($coditem)) { ?>
		<input type="hidden" name="item" value="<? echo $coditem; ?>" />
		<input type="hidden" name="compra" value="<? echo $item_compra; ?>" />
		<? } ?>

		<header class="titulo">
			<h1>Comentário da <span>Compra</span></h1>
		</header>

		<section id="comentarios-detalhes" class="secao label-top">
			<table class="lista">
				<thead>
					<tr>
						<th class="first"><strong>VCH</strong></th>
						<th><strong>Cliente</strong></th>
						<th><strong>Tipo</strong></th>
						<th><strong>Dia</strong></th>
						<th><strong>Setor</strong></th>
						<th class="right"><strong>Valor (R$)</strong></th>
					</tr>
				</thead>
				<tbody>				
					<tr>	
						<td class="first"><? echo $item_compra."/".$item_id; ?></td>
						<td><? echo $item_nome; ?></td>
						<td><? echo $item_tipo; ?></td>
						<td><? echo $item_dia; ?></td>
						<td><? echo $item_setor; ?></td>
						<td class="valor"><? echo $item_valor; ?></td>
					</tr>
				</tbody>
			</table>
		</section>

		<section class="secao label-top comentario">
			
			<p>
				<label for="item-comentario">Comentário</label>
				<textarea name="comentario" class="input comentario" id="item-comentario" rows="5"><? echo $item_comentario; ?></textarea>
			</p>
		</section>
		<section class="secao label-top comentario interno">
			<p>
				<label for="item-comentario-interno">Comentário Interno</label>
				<textarea name="comentario-interno" class="input comentario" id="item-comentario-interno" rows="5"><? echo $item_comentario_interno; ?></textarea>
			</p>

		</section>

		<footer class="controle">
			<input type="submit" class="submit coluna" value="Alterar" />
			<a href="#" class="cancel no-cancel fancy-close coluna">Cancelar</a>
			<div class="clear"></div>
		</footer>


	</form>

</section>
<?
		}
	}
}

//-----------------------------------------------------------------//

// include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>
</body>
</html>