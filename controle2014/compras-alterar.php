<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//


$evento = (int) $_SESSION['usuario-carnaval'];
$cod = (int) $_GET['c'];

if(!empty($cod) && !empty($evento)) {

?>
<section id="conteudo">
	<form id="compras-alterar" method="post" action="<? echo SITE; ?>compras/alterar/post/">

		<input type="hidden" name="cod" value="<? echo $cod; ?>" />

		<header class="titulo">
			<h1>Vendas <span>Alterar</span></h1>
		</header>
		
		<section class="secao">
			<table class="lista">
				<thead>
					<tr>
						<th></th>
						<th><strong>VCH</strong></th>
						<th><strong>Cliente</strong></th>
						<th><strong>Tipo</strong></th>
						<th><strong>Dia</strong></th>
						<th><strong>Setor</strong></th>
						<th class="right"><strong>Valor (R$)</strong></th>
					</tr>
					<tr class="spacer"><td colspan="7">&nbsp;</td></tr>
				</thead>
				<tbody>
				<?

				$n_alocados = 0;
				$sql_itens = sqlsrv_query($conexao, "SELECT li.*, l.LO_ENVIADO, v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME, tp.TI_TAG
					FROM loja l, loja_itens li, vendas v, eventos_setores es, eventos_dias ed, tipos tp 
					WHERE li.LI_COMPRA='$cod' AND li.LI_COMPRA=l.LO_COD AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD AND l.LO_BLOCK=0 AND l.D_E_L_E_T_='0' AND li.D_E_L_E_T_='0' ORDER BY LI_COD ASC", $conexao_params, $conexao_options);
				$n_itens = sqlsrv_num_rows($sql_itens);
				if($n_itens !== false) {
					$i = 1;
					while ($item = sqlsrv_fetch_array($sql_itens)) {
						$item_cod = $item['LI_COD'];
						$item_id = $item['LI_ID'];
						$item_nome = utf8_encode($item['LI_NOME']);
						$item_tipo = utf8_encode($item['TI_NOME']);
						$item_dia = utf8_encode($item['dia']);
						$item_setor = $item['ES_NOME'];
						$item_valor = number_format($item['LI_VALOR'], 2, ",", ".");
						$item_alocado = (bool) $item['LI_ALOCADO'];
						$item_enviado = (bool) $item['LO_ENVIADO'];

						$item_tipo_tag = utf8_encode($item['TI_TAG']);
						$item_setor_cod = utf8_encode($item['VE_SETOR']);
						$item_dia_cod = utf8_encode($item['VE_DIA']);

						$item_fila = utf8_encode($item['VE_FILA']);
						$item_vaga = utf8_encode($item['VE_VAGAS']);
						$item_tipo_especifico = utf8_encode($item['VE_TIPO_ESPECIFICO']);

						if($item_alocado) $n_alocados++;

						?>
							<tr <? if ($item_alocado){ echo 'class="alocado"'; } ?>>
								<td class="check">
									<? if (!$item_alocado){ ?>
									<section class="checkbox verify">
										<ul><li><label class="item"><input type="checkbox" name="loja[]" value="<? echo $item_cod; ?>" /></label></li></ul>
									</section>
									<? } else {
										$item_link = $item_enviado ? '#' : SITE.'ingressos/alocacao/'.$item_tipo_tag.'/'.$item_setor_cod.'/'.$item_dia_cod.'/';
										$item_mensagem = $item_enviado ? 'Este ingresso já foi enviado e por isso não pode ser alterado' : 'Este ingresso já está alocado. Clique para desalocar.' ;
									?>
									<a class="alocado" href="<? echo $item_link; ?>">
										<span>!</span>
										<div class="tooltip"><? echo $item_mensagem; ?></div>
									</a>
									<? } ?>
								</td>
								<td><? echo $cod."/".$item_id; ?></td>
								<td><? echo $item_nome; ?></td>
								<td><?
									echo $item_tipo;
									if(!empty($item_fila)) { echo " ".$item_fila; }
									if(!empty($item_tipo_especifico)) { echo " ".$item_tipo_especifico; }
									if(($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) { echo " (".$item_vaga." vagas)"; }
								?></td>
								<td><? echo $item_dia; ?></td>
								<td><? echo $item_setor; ?></td>
								<td class="valor"><? echo $item_valor; ?></td>
							</tr>
						<?
						$i++;
					}
				}
				
				?>
				</tbody>
			</table>
		</section>
		
		<section id="tipo-setor" class="secao label-top">
			<section id="tipo-ingresso" class="radio coluna">
				<h3>Selecione o tipo de ingresso</h3>
				<ul>
				<?

				$sql_tipo_ingresso = sqlsrv_query($conexao, "SELECT * FROM tipos WHERE D_E_L_E_T_=0 ORDER BY TI_ORDEM ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_tipo_ingresso) !== false){

					while ($ar_tipo_ingresso = sqlsrv_fetch_array($sql_tipo_ingresso)) {

						$disabled = false;
						
						$tipo_ingresso_cod = $ar_tipo_ingresso['TI_COD'];
						$tipo_ingresso_nome = utf8_encode($ar_tipo_ingresso['TI_NOME']);
						$tipo_ingresso_tag = $ar_tipo_ingresso['TI_TAG'];
						$tipo_ingresso_ordem = $ar_tipo_ingresso['TI_ORDEM'];


						if($n_alocados < $n_itens) {


							//Buscar disponibilidade do tipo e setores
							$sql_disponibilidade = sqlsrv_query($conexao, "DECLARE @vendas TABLE (VE_COD INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT);
							DECLARE @qtde TABLE (COD INT, QTDE INT DEFAULT 0);

							INSERT INTO @vendas (VE_COD, VE_ESTOQUE, VE_SETOR, VE_DIA)
							SELECT VE_COD, VE_ESTOQUE, VE_SETOR, VE_DIA FROM vendas WHERE VE_EVENTO='$evento' AND VE_TIPO='$tipo_ingresso_cod' AND VE_BLOCK=0 AND D_E_L_E_T_=0;

							INSERT INTO @qtde (COD, QTDE)
							SELECT li.LI_INGRESSO, COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 GROUP BY li.LI_INGRESSO;

							SELECT * FROM (SELECT ISNULL(q.QTDE, 0) AS QTDE, v.*, CAST((v.VE_ESTOQUE - ISNULL(q.QTDE, 0)) AS INT) AS TOTAL FROM @vendas v 
							LEFT JOIN @qtde q ON v.VE_COD = q.COD) S WHERE TOTAL > 0 ORDER BY VE_SETOR, VE_DIA", $conexao_params, $conexao_options);

							if(sqlsrv_next_result($sql_disponibilidade) && sqlsrv_next_result($sql_disponibilidade))
							if(sqlsrv_num_rows($sql_disponibilidade) !== false) {
								$ardisp = $ardispdias = array();
								while ($ar = sqlsrv_fetch_array($sql_disponibilidade)) {
									if(!in_array($ar['VE_SETOR'],$ardisp)) array_push($ardisp, (string) $ar['VE_SETOR']);

									//Adicionamos o dia
									$ardispdias[$ar['VE_SETOR']][count($ardispdias[$ar['VE_SETOR']])] = (string) $ar['VE_DIA'];

								}
								$disponibilidade = json_encode(array('setores' => $ardisp, 'dias' => $ardispdias));
								$disponibilidade = " rel='".$disponibilidade."' ";
							} else {
								$disabled = true;
							}
						} else {
							$disabled = true;
						}
						
					?>
					<li>
						<label class="item <? if ($disabled){ echo 'disabled'; } ?>">
							<input type="radio" name="tipo" value="<? echo $tipo_ingresso_cod; ?>" <? if ($disabled){ echo 'class="disabled"'; } echo $disponibilidade; ?> /><? echo $tipo_ingresso_nome; ?>
						</label>
					</li>
					<?
						unset($ar, $ardisp, $ardispdias, $disponibilidade);
					}
				}

				?>
				</ul>
				
			</section>

			<section id="setor-ingresso" class="radio coluna">
				<h3>Selecione o setor</h3>
				<ul>
				<?

				// Verificar se temos ingresso para o setor e dia selecionados
				if(defined('PGCOMPRA')) {
					$sql_compras_setores = sqlsrv_query($conexao, "SELECT VE_SETOR FROM vendas WHERE VE_EVENTO='$evento' AND VE_TIPO='$tipo_ingresso' AND VE_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_compras_setores) !== false) {
						$compras_setores = array();
						while ($ar = sqlsrv_fetch_array($sql_compras_setores)) { array_push($compras_setores, $ar['VE_SETOR']); }
						unset($ar);
					}
				}

				$sql_setor_ingresso = sqlsrv_query($conexao, "SELECT * FROM eventos_setores WHERE ES_EVENTO='$evento' AND ES_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY LEN(ES_NOME) ASC, ES_NOME ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_setor_ingresso) !== false){

					while ($ar_setor_ingresso = sqlsrv_fetch_array($sql_setor_ingresso)) {
						
						$setor_ingresso_cod = $ar_setor_ingresso['ES_COD'];
						$setor_ingresso_nome = utf8_encode($ar_setor_ingresso['ES_NOME']);
						
					?>
					<li>
						<label class="item disabled">
							<input type="radio" name="setor" value="<? echo $setor_ingresso_cod; ?>" class="disabled" /><? echo $setor_ingresso_nome; ?>
						</label>
					</li>
					<?

					}
				}

				?>
				</ul>
			</section>
			<div class="clear"></div>
		</section>

		<section class="secao label-top">
			<section id="compra-dias" class="radio infield dias coluna">
				<h3>Selecione o dia</h3>
				<ul>
				<?

				$sql_eventos_dias = sqlsrv_query($conexao, "SELECT ED_COD, ED_NOME, SUBSTRING(CONVERT(CHAR(8), ED_DATA, 103), 1, 5) AS DATA, DATEPART(WEEKDAY, ED_DATA) AS SEMANA FROM eventos_dias WHERE ED_EVENTO='$evento' AND D_E_L_E_T_=0 ORDER BY ED_DATA ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_eventos_dias) !== false){

					while ($ar_eventos_dias = sqlsrv_fetch_array($sql_eventos_dias)) {
						
						$eventos_dias_cod = $ar_eventos_dias['ED_COD'];
						$eventos_dias_nome = utf8_encode($ar_eventos_dias['ED_NOME']);
						$eventos_dias_data = $ar_eventos_dias['DATA'];
						$eventos_dias_semana = $semana_min[($ar_eventos_dias['SEMANA'] - 1)];
						
					?>
					<li>
						<label class="item disabled"><input type="radio" name="dia" value="<? echo $eventos_dias_cod; ?>" class="disabled" />
							<h5><? echo $eventos_dias_nome; ?></h5>
							<p><? echo $eventos_dias_semana; ?></p>
							<span><? echo $eventos_dias_data; ?></span>
						</label>
					</li>
					<?
					}
				}

				?>
				</ul>
			</section>

			<section id="compras-itens"></section>

			<div class="clear"></div>
		</section>

		<footer class="controle">
			<input type="hidden" name="alterar" value="true" />

			<? if ($n_alocados < $n_itens){ ?><input type="submit" class="submit coluna" value="Alterar" /><? } ?>
			<a href="<? echo $_SERVER['HTTP_REFERER']; ?>" class="cancel no-cancel coluna">Cancelar</a>
			<div class="clear"></div>
		</footer>

	</form>
	
</section>
<?
}

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>