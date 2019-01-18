<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$ingresso_tipo = format($_GET['t']);
$ingresso_setor = format($_GET['s']);
$ingresso_dia = format($_GET['d']);
$ingresso_fila_nivel = format($_GET['f']);
$evento = (int) $_SESSION['usuario-carnaval'];


?>
<section id="conteudo">
	<section id="compras-alocacao">
		<header class="titulo">
			<h1>Alocação <span>Cliente</span></h1>

			<section id="alocacao-legenda">
				<ul>
					<li><span class="pagos"></span> Pagos</li>
					<li><span class="pendentes"></span> Pendentes</li>
				</ul>
			</section>
		</header>
		
		<section id="tipo-setor" class="secao label-top">
			<section id="tipo-ingresso" class="coluna">
				<h3>Selecione o tipo de ingresso</h3>
				<ul>
				<?

				/*$sql_tipo_ingresso = sqlsrv_query($conexao, "SELECT MAX(t.TI_COD) AS TI_COD, MAX(t.TI_NOME) AS TI_NOME, MAX(t.TI_TAG) AS TI_TAG, MAX(t.TI_ORDEM) AS TI_ORDEM FROM tipos t
					LEFT JOIN compras c ON t.TI_COD=c.CO_TIPO
					WHERE c.CO_ESTOQUE IS NULL AND c.CO_BLOCK=0 AND c.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 
					GROUP BY t.TI_COD, t.TI_ORDEM
					ORDER BY t.TI_ORDEM ASC", $conexao_params, $conexao_options);

				$sql_tipo_ingresso = sqlsrv_query($conexao, "SELECT CASE WHEN MAX(c.CO_ESTOQUE) IS NULL THEN 1 ELSE 0 END AS EXIBIR, MAX(t.TI_COD) AS TI_COD, MAX(t.TI_NOME) AS TI_NOME, MAX(t.TI_TAG) AS TI_TAG, MAX(t.TI_ORDEM) AS TI_ORDEM FROM tipos t
					LEFT JOIN compras c ON t.TI_COD=c.CO_TIPO
					WHERE c.CO_BLOCK=0 AND c.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 
					GROUP BY t.TI_COD, t.TI_ORDEM
					ORDER BY t.TI_ORDEM ASC", $conexao_params, $conexao_options);

				*/
				
				$sql_tipo_ingresso = sqlsrv_query($conexao, "SELECT TI_COD, TI_NOME, TI_TAG, TI_ORDEM FROM tipos WHERE D_E_L_E_T_=0 ORDER BY TI_ORDEM ASC", $conexao_params, $conexao_options);

				if(sqlsrv_num_rows($sql_tipo_ingresso)){

					while ($ar_tipo_ingresso = sqlsrv_fetch_array($sql_tipo_ingresso)) {
						
						$tipo_ingresso_cod = $ar_tipo_ingresso['TI_COD'];
						$tipo_ingresso_nome = utf8_encode($ar_tipo_ingresso['TI_NOME']);
						$tipo_ingresso_tag = $ar_tipo_ingresso['TI_TAG'];
						$tipo_ingresso_ordem = $ar_tipo_ingresso['TI_ORDEM'];
						$tipo_ingresso_exibir = (bool) $ar_tipo_ingresso['EXIBIR'];
						
						if(($ingresso_tipo == $tipo_ingresso_tag) || (empty($ingresso_tipo) && ($tipo_ingresso_ordem ==1))) {
							$ingresso_tipo = $tipo_ingresso_cod;
							$tipo = $tipo_ingresso_tag;
						}

						$ingresso_checked = false;
						if ($ingresso_tipo == $tipo_ingresso_cod) {
							$ingresso_checked = true;
							$ingresso_tipo_tag = $tipo_ingresso_tag;
							$ingresso_tipo_nome = $tipo_ingresso_nome;
						}

					?>
					<li><a href="<? echo SITE; ?>ingressos/alocacao/<? echo $tipo_ingresso_tag; ?>/" class="item <? if(strlen($tipo_ingresso_nome) < 10) { echo ' small';  } if ($ingresso_checked) { echo ' checked'; } ?>"><? echo $tipo_ingresso_nome; ?></a></li>
					<?
					}
				}

				?>
				</ul>
				<input type="hidden" name="tipo" value="<? echo $ingresso_tipo; ?>" />
			</section>

			<section id="setor-ingresso" class="radio coluna">
				<h3>Selecione o setor</h3>
				<ul>
				<?

				//Arquibancada numerada
				// if($ingresso_tipo== 1) $search_tipo = " AND v.VE_TIPO_ESPECIFICO='numerada'";

				// Verificar se temos ingresso para o setor e dia selecionados
				/*$sql_dias_setores = sqlsrv_query($conexao, "(SELECT v.VE_SETOR AS SETOR, v.VE_DIA AS DIA
				FROM vendas v, loja l, loja_itens li
				WHERE v.VE_EVENTO='$evento' AND v.VE_TIPO='$ingresso_tipo' AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0 $search_tipo
				AND v.VE_COD=li.LI_INGRESSO AND l.LO_COD=li.LI_COMPRA AND li.LI_ALOCADO=0 AND l.LO_BLOCK=0 AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0)
				UNION
				(SELECT CO_SETOR AS SETOR, CO_DIA AS DIA FROM compras WHERE CO_EVENTO='$evento' AND CO_TIPO='$ingresso_tipo' AND CO_ESTOQUE IS NULL AND CO_BLOCK=0 AND D_E_L_E_T_=0)
				ORDER BY DIA ASC", $conexao_params, $conexao_options);*/

				$sql_dias_setores = sqlsrv_query($conexao, "(SELECT v.VE_SETOR AS SETOR, v.VE_DIA AS DIA
				FROM vendas v, loja l, loja_itens li
				WHERE v.VE_EVENTO='$evento' AND v.VE_TIPO='$ingresso_tipo' AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0 $search_tipo
				AND v.VE_COD=li.LI_INGRESSO AND l.LO_COD=li.LI_COMPRA AND l.LO_BLOCK=0 AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0)
				UNION
				(SELECT CO_SETOR AS SETOR, CO_DIA AS DIA FROM compras WHERE CO_EVENTO='$evento' AND CO_TIPO='$ingresso_tipo' AND CO_BLOCK=0 AND D_E_L_E_T_=0)
				ORDER BY DIA ASC", $conexao_params, $conexao_options);

				if(sqlsrv_num_rows($sql_dias_setores) !== false) {
					while ($ar = sqlsrv_fetch_array($sql_dias_setores)) {
						$iar = count($dias_setores[$ar['SETOR']]);
						$dias_setores[$ar['SETOR']][$iar] = $ar['DIA'];
					}
					unset($ar);
				}

				$sql_setor_ingresso = sqlsrv_query($conexao, "SELECT * FROM eventos_setores WHERE ES_EVENTO='$evento' AND ES_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY LEN(ES_NOME) ASC, ES_NOME ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_setor_ingresso)){

					while ($ar_setor_ingresso = sqlsrv_fetch_array($sql_setor_ingresso)) {
						
						$setor_ingresso_cod = $ar_setor_ingresso['ES_COD'];
						$setor_ingresso_nome = utf8_encode($ar_setor_ingresso['ES_NOME']);

						if(count($dias_setores[$setor_ingresso_cod]) > 0) {
							$ardias = $dias_setores[$setor_ingresso_cod];
							$dias = json_encode($ardias);
							$dias = " rel='".$dias."' ";
							unset($ardias);

						} else {
							$disabled = true;
						}
						
					?>
					<li><label class="item <? if($ingresso_setor == $setor_ingresso_cod) { echo 'checked'; } if($disabled) { echo 'disabled'; } ?>"><input type="radio" name="setor" value="<? echo $setor_ingresso_cod; ?>" <? if($ingresso_setor == $setor_ingresso_cod) { echo 'checked="checked"'; } ?> <? if($disabled) { echo 'class="disabled"'; } echo $dias; ?> /><? echo $setor_ingresso_nome; ?></label></li>
					<?
						unset($disabled,$dias);
					}
				}

				?>
				</ul>
			</section>
			
			<section id="alocacao-dias" class="coluna">
				<h3>Selecione o dia</h3>
				<ul>
				<?

				define('PGATUAL', SITE.'ingressos/alocacao/'.$ingresso_tipo_tag);

				$sql_eventos_dias = sqlsrv_query($conexao, "SELECT ED_COD, ED_NOME, SUBSTRING(CONVERT(CHAR(8), ED_DATA, 103), 1, 5) AS DATA, DATEPART(WEEKDAY, ED_DATA) AS SEMANA FROM eventos_dias WHERE ED_EVENTO='$evento' AND D_E_L_E_T_=0 ORDER BY ED_DATA ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_eventos_dias)){

					while ($ar_eventos_dias = sqlsrv_fetch_array($sql_eventos_dias)) {
						
						$eventos_dias_cod = $ar_eventos_dias['ED_COD'];
						$eventos_dias_nome = utf8_encode($ar_eventos_dias['ED_NOME']);
						$eventos_dias_data = $ar_eventos_dias['DATA'];
						$eventos_dias_semana = $semana_min[($ar_eventos_dias['SEMANA']-1)];
						$rel = json_encode(array('dia' => $eventos_dias_cod, 'link' => PGATUAL.'/$1/'.$eventos_dias_cod.'/'));
						
						$eventos_dias_disabled = true;
						$eventos_dias_link = '#';

						if(!empty($ingresso_setor)) {
							if(in_array($eventos_dias_cod, $dias_setores[$ingresso_setor])){
								$eventos_dias_disabled = false;
								$eventos_dias_link = PGATUAL.'/'.$ingresso_setor.'/'.$eventos_dias_cod.'/';
							}
						}
						


					?>
					<li>
						<a class="item <? if ($eventos_dias_disabled){ echo 'disabled'; } ?> <? if ($ingresso_dia == $eventos_dias_cod){ echo 'checked'; } ?>" href="<? echo $eventos_dias_link; ?>" rel='<? echo $rel; ?>'>
							<h5><? echo $eventos_dias_nome; ?></h5>
							<p><? echo $eventos_dias_semana; ?></p>
							<span><? echo $eventos_dias_data; ?></span>
						</a>
					</li>
					<?
					}
				}

				?>
				</ul>
			</section>

			<div class="clear"></div>
		</section>
		
		<?

		if(!empty($ingresso_tipo) && !empty($ingresso_setor) && !empty($ingresso_dia)) {

		?>

		<section class="secao" id="alocacao">
			<section id="lista-cliente">
				<?

				//Buscar clientes que compraram
				$sql_ingressos_venda = sqlsrv_query($conexao, "DECLARE @vendas TABLE (VE_COD INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT, VE_FILA VARCHAR(50), VE_VAGAS INT, VE_TIPO_ESPECIFICO VARCHAR(255));
				DECLARE @qtde TABLE (COD INT, QTDE INT DEFAULT 0);

				INSERT INTO @vendas (VE_COD, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO)
				SELECT v.VE_COD, v.VE_ESTOQUE, v.VE_SETOR, v.VE_DIA, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO FROM vendas v WHERE v.VE_EVENTO='$evento' AND v.VE_TIPO='$ingresso_tipo' AND v.VE_SETOR='$ingresso_setor' AND v.VE_DIA='$ingresso_dia' AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0 $search_tipo;

				INSERT INTO @qtde (COD, QTDE)
				SELECT li.LI_INGRESSO, COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 AND li.LI_ALOCADO=0 GROUP BY li.LI_INGRESSO;

				SELECT * FROM (SELECT ISNULL(q.QTDE, 0) AS QTDE, v.* FROM @vendas v 
				LEFT JOIN @qtde q ON v.VE_COD = q.COD) S WHERE QTDE > 0 ORDER BY VE_SETOR, VE_DIA", $conexao_params, $conexao_options);
				
				if(sqlsrv_next_result($sql_ingressos_venda) && sqlsrv_next_result($sql_ingressos_venda))
				$n_ingressos_venda = sqlsrv_num_rows($sql_ingressos_venda);

				if($n_ingressos_venda !== false) {

					while ($ingressos_venda = sqlsrv_fetch_array($sql_ingressos_venda)) {

						$ingressos_venda_cod = $ingressos_venda['VE_COD'];
						$ingressos_venda_qtde = $ingressos_venda['QTDE'];
						// $ingressos_venda_valor = number_format($ingressos_venda['VE_VALOR'],2,",",".");
						
						$ingressos_venda_fila = utf8_encode($ingressos_venda['VE_FILA']);
						$ingressos_venda_vaga = utf8_encode($ingressos_venda['VE_VAGAS']);
						$ingressos_venda_tipo_especifico = utf8_encode($ingressos_venda['VE_TIPO_ESPECIFICO']);
						
				?>
				<div class="box-clientes" id="box-clientes-<? echo $ingressos_venda_cod; ?>">
					<header>
						<span><? echo $ingressos_venda_qtde; ?></span>
						<h2>
							<?
							
							echo $ingresso_tipo_nome;
							if(!empty($ingressos_venda_fila)) { echo " ".$ingressos_venda_fila; }
							if(!empty($ingressos_venda_tipo_especifico)) { echo " ".$ingressos_venda_tipo_especifico; }
							if(!empty($ingressos_venda_vaga) && ($ingressos_venda_tipo_especifico == 'fechado')) { echo " (".$ingressos_venda_vaga." vagas)"; }

							?>
						</h2>
						<div class="controle">
							<form name="busca" id="busca-alocacao-<? echo $ingressos_venda_cod; ?>" class="busca-alocacao" method="get" action="">
				                <p>
				                    <label for="busca-alocacao-<? echo $ingressos_venda_cod; ?>-input" class="infield">Buscar</label>
				                    <input type="text" name="q" class="input" id="busca-alocacao-<? echo $ingressos_venda_cod; ?>-input" autocomplete="off">
				                    <a href="#" class="limpar">&times;</a>
				                </p>
				                <input type="submit" class="submit" value="">

				                <input type="hidden" name="tipo" value="<? echo $ingresso_tipo; ?>" />
				                <input type="hidden" name="setor" value="<? echo $ingresso_setor; ?>" />
				                <input type="hidden" name="dia" value="<? echo $ingresso_dia; ?>" />
				                <input type="hidden" name="fila-nivel" value="<? echo $ingresso_fila_nivel; ?>" />
				            </form>
							<a href="#box-clientes-<? echo $ingressos_venda_cod; ?>-lista" class="exibir show-hide-slide aberto"></a>
				            <div class="clear"></div>
				        </div>
						<div class="clear"></div>			
					</header>
					<ul id="box-clientes-<? echo $ingressos_venda_cod; ?>-lista">
						<?

						//Buscar itens e compras
						$sql_ingressos_venda_item = sqlsrv_query($conexao, "DECLARE @qtde TABLE (LO_COD INT, QTDE INT DEFAULT 0, LO_PAGO TINYINT DEFAULT 0, LO_CLI_NOME VARCHAR(255));
						DECLARE @qtdeoutros TABLE (LO_COD INT, QTDE INT DEFAULT 0);
						DECLARE @outros TABLE (LO_COD INT, QTDE INT DEFAULT 0);

						INSERT INTO @qtde (LO_COD, QTDE, LO_PAGO, LO_CLI_NOME)
						SELECT l.LO_COD, COUNT(li.LI_COD), MAX(l.LO_PAGO) AS LO_PAGO, MAX(l.LO_CLI_NOME) AS LO_CLI_NOME FROM loja l, loja_itens li WHERE li.LI_COMPRA=l.LO_COD AND l.LO_BLOCK=0 AND l.D_E_L_E_T_=0 AND li.LI_INGRESSO='$ingressos_venda_cod' AND li.LI_ALOCADO=0 AND li.D_E_L_E_T_=0 GROUP BY l.LO_COD;

						INSERT INTO @qtdeoutros (LO_COD, QTDE)
						SELECT l.LO_COD, COUNT(li.LI_COD) FROM loja l, loja_itens li WHERE li.LI_COMPRA=l.LO_COD AND l.LO_BLOCK=0 AND l.D_E_L_E_T_=0 AND li.LI_INGRESSO='$ingressos_venda_cod' AND li.D_E_L_E_T_=0 GROUP BY l.LO_COD;

						INSERT INTO @outros (LO_COD, QTDE)
						SELECT l.LO_COD, COUNT(li.LI_COD) FROM vendas v, loja_itens li, loja l WHERE li.LI_COMPRA=l.LO_COD AND v.VE_COD=li.LI_INGRESSO AND v.VE_EVENTO='$evento' AND v.VE_TIPO='$ingresso_tipo' AND v.VE_SETOR='$ingresso_setor' AND v.VE_DIA<>'$ingresso_dia' AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0  $search_tipo GROUP BY l.LO_COD;

						SELECT q.*, ISNULL(qo.QTDE,0) AS QTDEOUTROS, ISNULL(o.QTDE,0) AS OUTROS FROM @qtde q
						LEFT JOIN @qtdeoutros qo ON qo.LO_COD=q.LO_COD
						LEFT JOIN @outros o ON o.LO_COD=q.LO_COD WHERE q.QTDE > 0", $conexao_params, $conexao_options);
						
						if(sqlsrv_next_result($sql_ingressos_venda_item) && sqlsrv_next_result($sql_ingressos_venda_item) && sqlsrv_next_result($sql_ingressos_venda_item))
						if(sqlsrv_num_rows($sql_ingressos_venda_item) !== false) {

							while ($ingressos_venda_item = sqlsrv_fetch_array($sql_ingressos_venda_item)) {
								
								$ingressos_venda_item_cod = $ingressos_venda_item['LO_COD'];
								$ingressos_venda_item_cliente = utf8_encode($ingressos_venda_item['LO_CLI_NOME']);
								$ingressos_venda_item_quantidade = $ingressos_venda_item['QTDE'];
								$ingressos_venda_item_outros = (((int) $ingressos_venda_item['OUTROS']) > 0);
								$ingressos_venda_item_pago = (bool) $ingressos_venda_item['LO_PAGO'];

								// $ingressos_venda_item_id = $ingressos_venda_item['EXIBIRID'];
								// $ingressos_venda_item_comentario = $ingressos_venda_item['COMENTARIO'];

								if(($ingressos_venda_item_quantidade == 1) && ($ingressos_venda_item_outros > 1)) {
									$ingressos_venda_item_id = sqlsrv_query($conexao, "SELECT li.LI_ID FROM loja_itens li WHERE li.LI_COMPRA=$ingressos_venda_item_cod AND li.LI_INGRESSO='$ingressos_venda_cod' AND li.LI_ALOCADO=0 AND li.D_E_L_E_T_=0", $conexao_params, $conexao_options);
									// $ingressos_venda_item_id = (sqlsrv_num_rows($ingressos_venda_item_id) > 0) ? mssql_result($ingressos_venda_item_id, 0, 'LI_ID'): null;
									if(sqlsrv_num_rows($ingressos_venda_item_id) > 0){
										$ar_ingressos_venda_item_id = sqlsrv_fetch_array($ingressos_venda_item_id);
										$ingressos_venda_item_id = $ar_ingressos_venda_item_id['LI_ID'];
									} else {
										$ingressos_venda_item_id = null;
									}
									
								}

								unset($ingressos_venda_item_comentario);
								if($ingressos_venda_item_quantidade == 1) {
									$sql_ingressos_venda_item_comentario = sqlsrv_query($conexao, "SELECT TOP 1 lc.LC_COD FROM loja_itens li, loja_comentarios lc WHERE lc.LC_ITEM=li.LI_COD AND lc.LC_COMPRA=$ingressos_venda_item_cod AND li.LI_COMPRA=$ingressos_venda_item_cod AND li.LI_INGRESSO='$ingressos_venda_cod' AND li.LI_ALOCADO=0 AND li.D_E_L_E_T_=0", $conexao_params, $conexao_options);
									//$ingressos_venda_item_comentario = (sqlsrv_num_rows($sql_ingressos_venda_item_comentario) > 0) ? mssql_result($sql_ingressos_venda_item_comentario, 0, 'LC_COD') : null;
									if(sqlsrv_num_rows($sql_ingressos_venda_item_comentario) > 0){
										$ar_ingressos_venda_item_comentario = sqlsrv_fetch_array($sql_ingressos_venda_item_comentario);
										$ingressos_venda_item_comentario = $ar_ingressos_venda_item_comentario['LC_COD'];
									} else {
										$ingressos_venda_item_comentario = null;
									}
								}

								$ingressos_venda_item_show_lista = false;
								if($ingressos_venda_item_quantidade > 1) $ingressos_venda_item_show_lista = true;
								else {
									$sql_ingressos_venda_item_lista = sqlsrv_query($conexao, "SELECT TOP 1 li.LI_COD FROM loja_itens li WHERE li.LI_COMPRA='$ingressos_venda_item_cod' AND li.LI_INGRESSO='$ingressos_venda_cod' AND li.LI_ALOCADO=0 AND li.D_E_L_E_T_=0", $conexao_params, $conexao_options);
									//if(sqlsrv_num_rows($sql_ingressos_venda_item_lista) > 0) $ingressos_venda_item_lista_cod = mssql_result($sql_ingressos_venda_item_lista, 0, 'LI_COD');
									if(sqlsrv_num_rows($sql_ingressos_venda_item_lista) > 0){
										$ar_ingressos_venda_item_lista_cod = sqlsrv_fetch_array($sql_ingressos_venda_item_lista);
										$ingressos_venda_item_lista_cod = $ar_ingressos_venda_item_lista_cod['LI_COD'];
									} else {
										$ingressos_venda_item_lista_cod = null;
									}
								}
						?>
						<li>
							<div class="row first <? if (!$ingressos_venda_item_pago){ echo 'nao-pago'; } ?>" data-compra="<? echo $ingressos_venda_item_cod; ?>">
								<table>
									<tr>
										<!-- <td class="cod"><? echo $ingressos_venda_item_cod; if(!empty($ingressos_venda_item_id)) { echo '/'.$ingressos_venda_item_id; } ?></td> -->
										<td class="cod detalhes-voucher" data-cod="<? echo $ingressos_venda_item_cod; ?>" data-cancelado="false">
											<div class="relative">
												<? echo $ingressos_venda_item_cod; ?>
												<section class="detalhes"></section>
											</div>
										</td>
										<td class="nome"><? echo $ingressos_venda_item_cliente; ?></td>
										<td class="alerta">
											<? if($ingressos_venda_item_outros) { ?>
											<div>
												<a href="#">!</a>
												<div class="tooltip">Esse ingresso foi comprado para outras datas</div>
											</div>
											<? } ?>
										</td>
										<td class="qtde">
											<?
											if($ingressos_venda_item_show_lista) { ?><span><? echo $ingressos_venda_item_quantidade; ?></span><? }
											if(!empty($ingressos_venda_item_comentario)) { ?><a href="<? echo SITE; ?>ingressos/comentario/<? echo $ingressos_venda_item_comentario; ?>/" class="comentario fancybox fancybox.iframe width600"></a><? }
											?>
										</td>
										<td>
											<?
											if($ingressos_venda_item_show_lista) { ?><a href="#box-clientes-<? echo $ingressos_venda_cod; ?>-lista-drop-<? echo $ingressos_venda_item_cod; ?>" class="slide show-hide-slide"></a><? } 
											else { ?><span class="drag" data-compra="<? echo $ingressos_venda_item_lista_cod; ?>"></span><? } 

											?>
										</td>
									</tr>							
								</table>
							</div>
							<? 
							if($ingressos_venda_item_show_lista){
							?>
							<ul class="drop" id="box-clientes-<? echo $ingressos_venda_cod; ?>-lista-drop-<? echo $ingressos_venda_item_cod; ?>">
								<?
								
								$sql_ingressos_venda_item_lista = sqlsrv_query($conexao, "DECLARE @itens TABLE (LI_COD INT, LI_ID INT, LI_NOME VARCHAR(255));

								INSERT INTO @itens (LI_COD, LI_ID, LI_NOME)
								SELECT LI_COD, LI_ID, LI_NOME FROM loja_itens WHERE LI_COMPRA='$ingressos_venda_item_cod' AND LI_INGRESSO='$ingressos_venda_cod' AND LI_ALOCADO=0 AND D_E_L_E_T_=0;

								SELECT i.*, c.LC_COD AS COMENTARIO FROM @itens i
								LEFT JOIN loja_comentarios c ON c.LC_ITEM=i.LI_COD", $conexao_params, $conexao_options);

								if(sqlsrv_next_result($sql_ingressos_venda_item_lista))
								$n_ingressos_venda_item_lista = sqlsrv_num_rows($sql_ingressos_venda_item_lista);

								if($n_ingressos_venda_item_lista !== 0) {
									
									$i_ingressos_venda_lista = 1;

									while ($ingressos_venda_item_lista = sqlsrv_fetch_array($sql_ingressos_venda_item_lista)) {
										$ingressos_venda_item_lista_cod = $ingressos_venda_item_lista['LI_COD'];
										$ingressos_venda_item_lista_id = $ingressos_venda_item_lista['LI_ID'];
										$ingressos_venda_item_lista_nome = utf8_encode($ingressos_venda_item_lista['LI_NOME']);
										$ingressos_venda_item_lista_comentario = $ingressos_venda_item_lista['COMENTARIO'];
									
								?>
									<li>
										<div class="row <? if (!$ingressos_venda_item_pago){ echo 'nao-pago'; } if($n_ingressos_venda_item_lista == $i_ingressos_venda_lista) { echo ' last'; } ?>">
											<table>									
												<tr>
													<td class="cod"><? echo $ingressos_venda_item_cod; ?>/<? echo $ingressos_venda_item_lista_id; ?></td>										
													<td class="nome"><? echo $ingressos_venda_item_lista_nome; ?></td>										
													<td class="qtde"><? if(!empty($ingressos_venda_item_lista_comentario)) { ?><a href="<? echo SITE; ?>ingressos/comentario/<? echo $ingressos_venda_item_lista_comentario; ?>/" class="comentario fancybox fancybox.iframe width600"></a><? } ?></td>
													<td><span class="drag" data-compra="<? echo $ingressos_venda_item_lista_cod; ?>"></span></td>
												</tr>									
											</table>
										</div>
									</li>
								<?
										$i_ingressos_venda_lista++;
									}
								}
								?>
							</ul>
							<div class="clear"></div>
							<?
							}
							?>
						</li>
						<?
							}
						}
						?>
					</ul>
				</div>
				<?
						$ingressos_venda_disponivel = true;
					}

				}

				if(!$ingressos_venda_disponivel) {
				?>
				<h1 class="nenhum">Nenhum ingresso disponível para alocação</h1>
				<?
				}
				?>
				
			</section>

			<!--


			-->

			<section id="lista-alocacao">
				<header>
				<?

					$sql_compras_quantidade = sqlsrv_query($conexao, "SELECT CASE WHEN(ESTOQUE > 0) THEN ESTOQUE ELSE QTDE END AS QTDE  FROM (SELECT ISNULL(SUM(CO_ESTOQUE),0) AS ESTOQUE, COUNT(CO_COD) AS QTDE FROM compras WHERE CO_EVENTO='$evento' AND CO_TIPO='$ingresso_tipo' AND CO_SETOR='$ingresso_setor' AND CO_DIA='$ingresso_dia' AND CO_BLOCK=0 AND D_E_L_E_T_=0) S", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_compras_quantidade) > 0) $ar_compras_quantidade = sqlsrv_fetch_array($sql_compras_quantidade);

					$compras_quantidade = (int) $ar_compras_quantidade['QTDE'];

					?>
					<span><? echo $compras_quantidade; ?></span>
					<?


					switch ($ingresso_tipo_tag) {
						case 'arquibancada':
						case 'cadeira':
							$fileiras_nivel_titulo = 'Selecione a fileira';
							$fileiras_nivel_todos = 'Todas';
							$fileiras_nivel_show_vaga = true;
						break;

						case 'frisa':
							$fileiras_nivel_titulo = 'Selecione a Frisa';
							$fileiras_nivel_todos = 'Todas';
							$fileiras_nivel_show_vaga = false;
						break;

						case 'camarote':
							$fileiras_nivel_titulo = 'Selecione o nível';
							$fileiras_nivel_todos = 'Todos';
							$fileiras_nivel_show_vaga = false;
						break;
					}


					// Selecionar fileiras
					$sql_fileiras_nivel = sqlsrv_query($conexao, "SELECT CO_FILA, CO_NIVEL FROM compras WHERE CO_ESTOQUE IS NULL AND CO_EVENTO='$evento' AND CO_TIPO='$ingresso_tipo' AND CO_SETOR='$ingresso_setor' AND CO_DIA='$ingresso_dia' AND CO_BLOCK=0 AND D_E_L_E_T_=0 GROUP BY CO_FILA, CO_NIVEL ORDER BY CO_FILA, CO_NIVEL", $conexao_params, $conexao_options);
					$n_fileiras_nivel = sqlsrv_num_rows($sql_fileiras_nivel);
					
					if($n_fileiras_nivel > 1) {

						$fileiras_nivel_tipo = ($n_fileiras_nivel > 5) ? 'select' : 'check';
						
						define('PGATUALFILA', SITE.'ingressos/alocacao/'.$ingresso_tipo_tag.'/'.$ingresso_setor.'/'.$ingresso_dia.'/');

					?>
					<h2><? echo $fileiras_nivel_titulo; ?></h2>
					<section class="fileira-nivel <? echo $fileiras_nivel_tipo; ?>">
						<? if($fileiras_nivel_tipo == 'select') { ?>
						<a href="#select-fileira-nivel" class="arrow show-hide-slide"><strong><? echo empty($ingresso_fila_nivel) ? $fileiras_nivel_todos : $ingresso_fila_nivel; ?></strong><span></span></a>
						<? } ?>

						<ul class="selecao" id="select-fileira-nivel">
							<li><a href="<? echo PGATUALFILA; ?>" class="item todas <? if (empty($ingresso_fila_nivel)){ echo 'checked'; } ?>"><? echo $fileiras_nivel_todos; ?></a></li>
							<?
							while ($fileiras_nivel = sqlsrv_fetch_array($sql_fileiras_nivel)) {
								$fileiras_nivel_valor = !empty($fileiras_nivel['CO_FILA']) ? $fileiras_nivel['CO_FILA'] : $fileiras_nivel['CO_NIVEL'];
							?>
							<li><a href="<? echo PGATUALFILA.$fileiras_nivel_valor.'/'; ?>" class="item <? if ($ingresso_fila_nivel == $fileiras_nivel_valor){ echo 'checked'; } ?>"><? echo $fileiras_nivel_valor; ?></a></li>
							<?
							}
							?>
						</ul>
					</section>		
					<div class="clear"></div>
					<?
					}

				?>
				</header>
				<form id="alocacao-marcacao" method="post" action="#">
					<input type="hidden" name="tipo" value="<? echo $ingresso_tipo; ?>" />
					<input type="hidden" name="setor" value="<? echo $ingresso_setor; ?>" />
					<input type="hidden" name="dia" value="<? echo $ingresso_dia; ?>" />
					<input type="hidden" name="fila-nivel" value="<? echo $ingresso_fila_nivel; ?>" />
				</form>

				<?
				
				//Filtro
				if(!empty($ingresso_fila_nivel)) $search_fila_nivel = " AND ((CO_FILA='$ingresso_fila_nivel' AND CO_NIVEL IS NULL) OR (CO_NIVEL='$ingresso_fila_nivel' AND CO_FILA IS NULL)) ";

				$sql_ingressos_lugares = sqlsrv_query($conexao, "SELECT * FROM compras WHERE CO_ESTOQUE IS NULL AND CO_EVENTO='$evento' AND CO_TIPO='$ingresso_tipo' AND CO_SETOR='$ingresso_setor' AND CO_DIA='$ingresso_dia' AND CO_BLOCK=0 AND D_E_L_E_T_=0 $search_fila_nivel ORDER BY CO_FILA, CO_NIVEL, CO_NUMERO, CO_VAGA", $conexao_params, $conexao_options);
				$n_ingressos_lugares = sqlsrv_num_rows($sql_ingressos_lugares);
					
				if($n_ingressos_lugares > 0) {

					$i_ingressos_lugares = 1;

					while ($ingressos_lugares = sqlsrv_fetch_array($sql_ingressos_lugares)) {
						
						$ingressos_lugares_cod = $ingressos_lugares['CO_COD'];
						$ingressos_lugares_fila = $ingressos_lugares['CO_FILA'];
						$ingressos_lugares_nivel = $ingressos_lugares['CO_NIVEL'];
						$ingressos_lugares_numero = $ingressos_lugares['CO_NUMERO'];
						$ingressos_lugares_vaga = $ingressos_lugares['CO_VAGA'];

						if($fileiras_nivel_show_vaga) {
							$ingressos_lugares_vaga = $ingressos_lugares_numero;
							unset($ingressos_lugares_numero);
						}

						$ingressos_lugares_indicador = !empty($ingressos_lugares_fila) ? $ingressos_lugares_fila : $ingressos_lugares_nivel;

						if(($ingressos_lugares_indicador != $ingressos_lugares_indicador_anterior) || ($ingressos_lugares_numero != $ingressos_lugares_numero_anterior)) {

							if($i_ingressos_lugares > 1) {
							?>
							<li class="clear"></li>
							</ul>
							<?
							}

							?>
							<ul class="lista">
							<li class="item titulo <? if(!empty($ingressos_lugares_numero)){ echo 'pequeno'; } ?>">
								<? echo $ingressos_lugares_indicador ?>
								<? if(!empty($ingressos_lugares_numero)){ ?><small><? echo $ingressos_lugares_numero; ?></small><? } ?>
							</li>
							<?
						}

						?>
						<li class="item <? if ($fileiras_nivel_show_vaga){ echo 'vaga'; } ?>">
							<div class="lugar" data-lugar="<? echo $ingressos_lugares_cod; ?>"></div>
							<? if ($fileiras_nivel_show_vaga){ ?><small><? echo $ingressos_lugares_vaga; ?></small><? } ?>
						</li>
						<?

						if($i_ingressos_lugares == $n_ingressos_lugares) {
						?>
						<li class="clear"></li>
						</ul>
						<?
						}

						$ingressos_lugares_cod_anterior = $ingressos_lugares_cod;
						$ingressos_lugares_indicador_anterior = $ingressos_lugares_indicador;
						$ingressos_lugares_numero_anterior = $ingressos_lugares_numero;
						$ingressos_lugares_vaga_anterior = $ingressos_lugares_vaga;

						$i_ingressos_lugares++;

					}

				} else {

					$sql_ingressos_alocacao = sqlsrv_query($conexao, "SELECT CO_COD FROM compras WHERE CO_ESTOQUE IS NOT NULL AND CO_EVENTO='$evento' AND CO_TIPO='$ingresso_tipo' AND CO_SETOR='$ingresso_setor' AND CO_DIA='$ingresso_dia' AND CO_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_ingressos_alocacao) > 0) {

						?>
						<h1 class="nenhum">Ingressos do tipo <? echo $ingresso_tipo_nome; ?> não são alocados</h1>
						<?

					} else {

						$a = ($ingresso_tipo_tag == 'frisa') ? true : false;
						

						?>
						<h1 class="nenhum">Nenhum<? echo $a ? '' : 'a'; ?> <? echo $a ? 'lugar' : 'vaga'; ?> disponível para ser alocad<? echo $a ? 'o' : 'a'; ?></h1>
						<?

					}

				}
				?>
			</section>
			<div class="clear"></div>
		</section>
		<?
		}
		?>
	</section>


</section>
<section id="ingressos-desalocar">Solte aqui para desalocar</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>