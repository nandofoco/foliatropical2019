<?

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//Definir o carnaval ativo
include("include/setcarnaval.php");


unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//

if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE.$link_lang; ?>';
</script>
<?
	exit();
}

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = setcarnaval();
$cod = (int) $_GET['c'];
$transfer = (bool) $_GET['transfer'];

$usuario_cod = $_SESSION['usuario-cod'];

//-----------------------------------------------------------------//

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_CLIENTE='$usuario_cod' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);

?>
<section id="conteudo">
	<div id="breadcrumb" itemprop="breadcrumb"> 
		<a href="<? echo SITE.$link_lang; ?>"><? echo $lg['menu_inicio']; ?></a> &rsaquo; 
		<a href="<? echo SITE.$link_lang; ?>minhas-compras/"><? echo $lg['menu_minhas_compras']; ?></a> &rsaquo; 
		<? echo $lg['minhas_compras_detalhes']; ?>
	</div>
	<section id="compre-aqui">

	<?
	if(sqlsrv_num_rows($sql_loja) > 0) {

		$loja_qtde_folia = 0;
		$loja_qtde_frisa = 0;
		$loja_enable_frisa = false;

		//Novos combos
		$loja_qtde_combo = array();

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_cod = $loja['LO_COD'];
		$loja_cliente = $loja['LO_CLIENTE'];
		$loja_forma = $loja['LO_FORMA_PAGAMENTO'];
		$loja_diferenca_dias = (5 - $loja['DIFERENCA']);
		$loja_status_transacao = $loja['LO_STATUS_TRANSACAO'];
		$loja_pago = (bool) $loja['LO_PAGO'];
		$loja_delivery = (bool) $loja['LO_DELIVERY'];
		$loja_desconto = (bool) $loja['LO_DESCONTO'];

		if(!$loja_delivery) $loja_retirada = $loja['LO_RETIRADA'];

		$cartao_credito = ($loja_forma == 1) ? true : false;
		$faturado = ($loja_forma == 7) ? true : false;
		$reserva = ($loja_forma == 5) ? true : false;

		//Data da compra
		$loja_data = $loja['LO_DATA_COMPRA'];
		$anterior = (strtotime($loja_data->format('Y-m-d')) < strtotime('2015-10-15')) ? true : false;
		$loja_desconto_folia = ($anterior) ? 1 : (bool) $loja['LO_DESCONTO_FOLIA'];
		$loja_desconto_frisa = ($anterior) ? 1 : (bool) $loja['LO_DESCONTO_FRISA'];

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

		$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
		$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
		$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));

		$loja_valor_total = $loja['LO_VALOR_TOTAL'];
		$loja_valor_total_f = number_format($loja['LO_VALOR_TOTAL'], 2, ',','.');

		if($loja_delivery) {
			$lo_valor_delivery = $loja['LO_VALOR_DELIVERY'];
			$lo_valor_delivery_f = number_format($lo_valor_delivery, 2, ',','.');			
		}

		//Forma de pagamento
		// LO_FORMA_PAGAMENTO
		$sql_forma = sqlsrv_query($conexao, "SELECT TOP 1 FP_NOME FROM formas_pagamento WHERE FP_COD='$loja_forma'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_forma) > 0) {
			$loja_forma_ar = sqlsrv_fetch_array($sql_forma);
			$loja_forma_pagamento = utf8_encode($loja_forma_ar['FP_NOME']);
		}

		//Se for cartão de credito
		if($loja_forma == 1) {

			//Buscar a bandeira
			$loja_cartao = $loja['LO_CARTAO'];

			//XML
			$loja_xml = $loja['LO_XML'];

			if(!empty($loja_xml)) {
				$xml = new SimpleXMLElement($loja_xml);
				$loja_parcelas = $xml->{'forma-pagamento'}->parcelas;
  			}
			
		}

		//Cielo V2
		$loja_cielo_v2 = (bool) $loja['LO_CARTAO_V2'];
		if($loja_cielo_v2) {
			$loja_cielo_v2_numero_cartao = utf8_encode($loja['LO_CARTAO_BANDEIRA']);
			$loja_cielo_v2_nome = utf8_encode($loja['LO_CARTAO_NOME']);
			$loja_cielo_v2_cpf = formatCPFCNPJ($loja['LO_CARTAO_CPF']);
			$loja_cielo_v2_email = utf8_encode($loja['LO_CARTAO_EMAIL']);
			$loja_cielo_v2_telefone = formatTelefone($loja['LO_CARTAO_TELEFONE']);
			$loja_cielo_v2_antifraude = $loja['LO_CARTAO_ANTIFRAUDE'];
			$loja_parcelas = $loja['LO_PARCELAS'];
			$loja_checkoutid = $loja['LO_CARTAO_CHECKOUTID'];

			switch ($loja_cielo_v2_antifraude) {
				case 1:
					$loja_cielo_v2_antifraude_classe = 'baixo';
					$loja_cielo_v2_antifraude_texto = 'Baixo Risco';
				break;
				case 2:
					$loja_cielo_v2_antifraude_classe = 'alto';
					$loja_cielo_v2_antifraude_texto = 'Alto Risco';
				break;
				case 3:
					$loja_cielo_v2_antifraude_classe = 'nao-finalizado';
					$loja_cielo_v2_antifraude_texto = 'Não Finalizado';
				break;
				case 4:
					$loja_cielo_v2_antifraude_classe = 'moderado';
					$loja_cielo_v2_antifraude_texto = 'Risco Moderado';
				break;
				default:
					$loja_cielo_v2_antifraude_classe = '';
					$loja_cielo_v2_antifraude_texto = 'N/A';
				break;
			}
		}

		//$loja_camisas = (date('Ymd') < '20140220') ? true : false;
		$loja_camisas = true;

		//Verificar se tem um folia tropical do dia 01/03;
		#$loja_folia_item = ($_SERVER['SERVER_NAME'] == "bruno") ? 28 :  176;
		$sql_folia = sqlsrv_query($conexao, "SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO NOT IN (SELECT v.VE_COD FROM vendas v LEFT JOIN eventos_dias d ON d.ED_COD=v.VE_DIA WHERE ((v.VE_TIPO=4 AND d.ED_DATA IN ('2015-02-13', '2015-02-14', '2016-02-05', '2016-02-06')) OR (v.VE_TIPO IN (1,2))) AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0) AND D_E_L_E_T_=0", $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_folia) == 0) $loja_camisas = false;
		
		?>
		<header class="titulo">
			<h1><? echo $lg['minhas_compras_detalhes']; ?></h1>
			<div class="valor-total">R$ <? echo $loja_valor_total_f; ?></div>
		</header>
		<section class="padding">
		
			<section class="secao relative <? if($loja_camisas) { echo 'margin-less'; } ?>" id="compra-dados">
				<div>
					<aside><? echo $loja_cod; ?></aside>
					<section>
						<h3><? echo $loja_nome; ?></h3>
						<p><? echo $loja_email; ?></p>
						<p><? echo formatTelefone($loja_telefone); ?></p>
					</section>

					<div class="clear"></div>
				</div>
				<div class="informacoes-compra">
					<p><? echo $loja_forma_pagamento; ?></p>
					<? if($cartao_credito && !empty($loja_cartao)) { ?><p class="cartao"><span class="<? echo $loja_cartao; ?>"></span> <? echo $loja_parcelas; ?>x R$ <? echo number_format(($loja_valor_total / $loja_parcelas), 2, ",", "."); ?> <strong> • <? echo $loja_cartao; ?></strong></p><? } ?>
					<?
					//verificacao antiga de dias para captura, removida em 08/01/2018
					// if($cartao_credito && ($loja_status_transacao != 4)  && !($loja_diferenca_dias > -1)) {
						if($cartao_credito && ($loja_status_transacao != 4)  && ($loja_pago == 0)) {
						?>
						<!-- <a href="<? echo SITE.$link_lang; ?>ingressos/pagamento/v2/<? echo $loja_cod; ?>/" class="liberar pagar confirm" title="Realizar o pagamento da compra <? echo $loja_cod; ?>?"><? echo $lg['minhas_compras_pagar']; ?></a> -->

						<a href="https://ingressos.foliatropical.com.br/compras/pagamento/<? echo $loja_cod; ?>/" class="liberar pagar confirm" title="<? echo $lg['minhas_compras_deseja_pagar'].$loja_cod; ?>?" data-sim="<? echo $lg['minhas_compras_sim']; ?>" data-cancelar="<? echo $lg['minhas_compras_cancelar']; ?>"><? echo $lg['minhas_compras_pagar']; ?></a>
						<?

						/*// Permitir apenas compras do Folia Tropical e Super Folia 

						$sql_item_paypal = sqlsrv_query($conexao, " SELECT COUNT(LI_COD) AS QTDE, LI_VALOR, LI_INGRESSO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='$loja_cancelado_int' GROUP BY LI_INGRESSO, LI_VALOR", $conexao_params, $conexao_options);
						$n_paypal_folia = $n_paypal_outros = 0;


						if(sqlsrv_num_rows($sql_item_paypal) > 0) {
							
							while ($item_paypal = sqlsrv_fetch_array($sql_item_paypal)) {
									
								$item_paypal_ingresso = $item_paypal['LI_INGRESSO'];
								
								//Informações adicionais do item
								$sql_info_item_paypal = sqlsrv_query($conexao, "
								SELECT tp.TI_TAG 
								FROM vendas v, eventos_setores es, eventos_dias ed, tipos tp 
								WHERE v.VE_COD='$item_paypal_ingresso' AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD", $conexao_params, $conexao_options);

								if(sqlsrv_num_rows($sql_info_item_paypal) > 0) {
									$info_item_paypal = sqlsrv_fetch_array($sql_info_item_paypal);
								
									$item_paypal_tipo_tag = $info_item_paypal['TI_TAG'];
									
									if(($item_paypal_tipo_tag == 'lounge') || ($item_paypal_tipo_tag == 'super')) $n_paypal_folia++;											
									else $n_paypal_outros++;
								}
							}
						}

						if(($n_paypal_folia > 0) && ($n_paypal_outros == 0)) {
						?>
						<a href="<? echo SITE.$link_lang; ?>ingressos/pagamento/paypal/<? echo $loja_cod; ?>/" class="liberar pagar confirm" title="Realizar o pagamento da compra <? echo $loja_cod; ?>?">Paypal</a>
						<?							
						}*/
					}

					if($loja_pago) {
					?>
					<a href="<? echo SITE.$link_lang; ?>minhas-compras/imprimir/<? echo $loja_cod; ?>/" class="print" title="Imprimir compra <? echo $loja_cod; ?>?" target="_blank"></a>
					<?
					}
					?>
				</div>

			</section>

			<!-- <?

			// Verificar data
			if($loja_camisas) { //-----------------------------------------------------------------//

			?>
			<a href="<? echo SITE.$link_lang; ?>minhas-compras/detalhes/camisas/<? echo $loja_cod; ?>/" class="cadastrar-camisas fancybox fancybox.iframe width600"><? echo $lg['minhas_compras_detalhes_camisas']; ?></a>
			<?

			} //----------------------------------------------------------------------------------------------//

			?> -->
				
			<section id="financeiro-detalhes-itens" class="secao">
			<?

			$sql_item = sqlsrv_query($conexao, " SELECT COUNT(LI_COD) AS QTDE, LI_VALOR, LI_INGRESSO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0' GROUP BY LI_INGRESSO, LI_VALOR", $conexao_params, $conexao_options);

			if(sqlsrv_num_rows($sql_item) > 0) {
				$i = 1;
				$item_count = 1;

				while ($item = sqlsrv_fetch_array($sql_item)) {
						
					// $item_id = $item['LI_ID'];
					// $item_nome = utf8_encode($item['LI_NOME']);

					$item_cod = $item['COD'];
					$item_qtde = $item['QTDE'];
					$item_ingresso = $item['LI_INGRESSO'];
					$item_valor =  number_format($item['LI_VALOR'], 2, ",", ".");
					$item_exclusividade = $item['EXCLUSIVIDADE'];
					$item_exclusividade_val = $item['EXCLUSIVIDADE_VAL'];

					//Informações adicionais do item
					$sql_info_item = sqlsrv_query($conexao, "
					SELECT v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME, tp.TI_TAG 
					FROM vendas v, eventos_setores es, eventos_dias ed, tipos tp 
					WHERE v.VE_COD='$item_ingresso' AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD", $conexao_params, $conexao_options);

					if(sqlsrv_num_rows($sql_info_item) > 0) {
						$info_item = sqlsrv_fetch_array($sql_info_item);
					
						$item_setor = utf8_encode($info_item['ES_NOME']);
						$item_dia = utf8_encode($info_item['ED_NOME']);
						$item_data = utf8_encode($info_item['dia']);
						$item_data_n = $info_item['ED_DATA'];
						$item_tipo = utf8_encode($info_item['TI_NOME']);
						$item_tipo_tag = $info_item['TI_TAG'];
						
						$item_fila = utf8_encode($info_item['VE_FILA']);
						$item_vaga = utf8_encode($info_item['VE_VAGAS']);
						$item_tipo_especifico = utf8_encode($info_item['VE_TIPO_ESPECIFICO']);

						$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;

						$item_data_n = (string) date('Y-m-d', strtotime($item_data_n->format('Y-m-d')));

						/*if(($item_tipo_tag == 'lounge') && (in_array($item_data_n, $dias_principais))){
							//Adicionamos na quantidade e excluimos do array
							$loja_qtde_folia++;

							foreach ($dias_principais as $key_dia => $ingressos_dia_atual) {
								if ($ingressos_dia_atual == $item_data_n) unset($dias_principais[$key_dia]);
							}
							
						}*/

						if(($item_tipo_tag == 'lounge')) {

							if($loja_cod <= $combo_dias_limite) {
								if(in_array($item_data_n, $dias_principais)){
									//Adicionamos na quantidade e excluimos do array
									$loja_qtde_folia++;
									foreach ($dias_principais as $key_dia => $item_dia_atual) {
										if ($item_dia_atual == $item_data_n) unset($dias_principais[$key_dia]);
									}
								}
								
							} else {

								//loja_qtde_combo
								if(count($combo_dias) > 0) {

									// Limite
									$loja_data_limite = (string) date('Y-m-d', strtotime($loja_data->format('Y-m-d')));

									foreach ($combo_dias as $k => $c) {
										//Verificar cada ocorrencia
										// if(in_array($item_data_n, $c['dias'])) {
										// Modificacao por causa da data de compra
										
										if(in_array($item_data_n, $c['dias']) && ($loja_data_limite >= $c['limite'][0]) && ($loja_data_limite <= $c['limite'][1])) {

											$loja_qtde_combo[$k] = 1 + ((int) $loja_qtde_combo[$k]);

											//Retiramos do combo o valor encontrado
											foreach ($c['dias'] as $kd => $ingressos_dia_atual) {
												if ($ingressos_dia_atual == $item_data_n) unset($combo_dias[$k]['dias'][$kd]);
											}
										}									
									}
								}
								
							}

							
						}

						if($evento > 1) {
							unset($loja_atual_frisa);

							if($item_tipo_tag == 'frisa'){

								$loja_frisa_fechadas = floor($item_qtde / 6);
								if($loja_frisa_fechadas > 0) {
									$loja_qtde_frisa = $loja_qtde_frisa + $loja_frisa_fechadas;
									$loja_enable_frisa = true;
								}

							}
						}
					}

					// loja_itens_adicionais
					if($item_fechado) $item_qtde = $item_qtde / $item_vaga;

					?>
					<section class="item-carrinho">
						
						<header>
							<strong>Qtde. <? echo $item_qtde; ?></strong> &ndash; 
							<?
								echo $item_tipo;
								if(!empty($item_fila)) { echo " ".$item_fila; }
								if(!empty($item_tipo_especifico)) { echo " ".$item_tipo_especifico; }
								if($item_fechado) { echo " (".$item_vaga." vagas)"; }
							?>
							<span class="valor">R$ <? echo $item_valor; ?></span>
						</header>
						
						<div class="cliente">
							<? echo $item_dia; ?> dia (<? echo $item_data; ?>) &ndash; Setor <? echo $item_setor; ?>
							<a href="<? echo SITE.$link_lang; ?>ingressos/comentario/novo/<? echo $item_cod; ?>/" class="comentario fancybox fancybox.iframe width600"></a>
						</div>
						
						<table class="lista compras-adicionais">
							<tbody>
							<?

							//Exclusividade
							if($item_exclusividade) {
							?>
							<tr class="incluso">
								<td class="check">&nbsp;</td>
								<td class="nome">Exclusividade - <? echo $item_tipo; ?> <? if(!empty($item_exclusividade_val)) { ?> na fila <? echo utf8_encode($item_exclusividade_val); } ?></td>
							</tr>
							<?
							}

							// $sql_adicionais = sqlsrv_query($conexao, "SELECT lia.LIA_COD, v.* FROM loja_itens_adicionais lia, vendas_adicionais v WHERE v.VA_COD=lia.LIA_ADICIONAL AND lia.LIA_COMPRA='$cod' AND lia.LIA_ITEM='$item_cod'", $conexao_params, $conexao_options);
							$sql_adicionais = sqlsrv_query($conexao, "SELECT MAX(lia.LIA_COD) AS LIA_COD, COUNT(lia.LIA_COD) AS QTDE, MAX(v.VA_LABEL) AS VA_LABEL, MAX(v.VA_NOME_EXIBICAO) AS VA_NOME_EXIBICAO
								FROM loja_itens_adicionais lia, vendas_adicionais v 
								WHERE v.VA_COD=lia.LIA_ADICIONAL AND lia.LIA_COMPRA='$cod' 
								AND lia.LIA_ITEM IN (SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO='$item_ingresso' AND D_E_L_E_T_='0')
								AND lia.D_E_L_E_T_='0'
								GROUP BY VA_COD
								", $conexao_params, $conexao_options);
							if(sqlsrv_num_rows($sql_adicionais) > 0) {

								while ($vendas_adicionais = sqlsrv_fetch_array($sql_adicionais)) {
									$vendas_adicionais_label = utf8_encode($vendas_adicionais['VA_LABEL']);
									$vendas_adicionais_nome_exibicao = $vendas_adicionais['VA_NOME_EXIBICAO'];
									$vendas_adicionais_qtde = $vendas_adicionais['QTDE'];
									
									if($vendas_adicionais_nome_exibicao == 'delivery'){
										$vendas_adicionais_delivery['label'] = $vendas_adicionais_label;
									} else {


								?>
								<tr class="incluso">
									<td class="check">&nbsp;</td>
									<td class="nome"><? if ($item_fechado){ echo "Qtde. ".$vendas_adicionais_qtde." - "; } echo $vendas_adicionais_label; ?></td>
								</tr>								
								<?
									}

								}

							}
							
							?>
							</tbody>
						</table>

					</section>
					<?
				}		

			}
			
			if($vendas_adicionais_delivery || $loja_desconto || !empty($loja_retirada)) {
			?>
			<section class="item-carrinho extra">
				<header><? echo $lg['compre_ingressos_info_extra']; ?></header>
				<table class="lista compras-adicionais">
					<tbody>
						<? if ($vendas_adicionais_delivery){ ?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome"><? echo $vendas_adicionais_delivery['label']; ?></td>
							<td class="valor">R$ <? echo $lo_valor_delivery_f; ?></td>
						</tr>
						<?
						}
						if(!empty($loja_retirada)) {
						?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome" colspan="2"><? echo $lg['compre_adicionais_retirada_ingresso']; ?>: <? echo ucfirst($loja_retirada); ?></td>
						</tr>
						<?
						}

						/*if($loja_qtde_folia >= 2) {
						?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome">Combo 2 dias na Folia (10% de Desconto)</td>
							<td class="valor">- 10%</td>
						</tr>
						<?
						}*/
						

						$loja_combo_desconto = 0;

						/*if($loja_qtde_folia >= 2) {
							$loja_combo_desconto = 10;
							$loja_combo_nome = "Combo 2 dias na Folia (Desconto de 10%)";
						} else {*/
							foreach ($loja_qtde_combo as $k => $r) {
								if(($r == $combo_dias[$k]['total']) && ($combo_dias[$k]['desconto'] > $loja_combo_desconto)) {
									$loja_combo_desconto = $combo_dias[$k]['desconto'];
									$loja_combo_nome = $combo_dias[$k]['nome'].' (Desconto de '.str_replace('.', ',', round($loja_combo_desconto, 1)).'%)';
								}
							}
						/*}*/
						
						if($loja_desconto_folia && ($loja_combo_desconto > 0)) {
						?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome"><? echo $loja_combo_nome; ?></td>
							<td class="valor">- <? echo str_replace('.', ',', round($loja_combo_desconto, 1)); ?>%</td>
						</tr>
						<?
						}


						if($loja_desconto_frisa && $loja_enable_frisa) {
						?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome">Desconto para Frisa fechada</td>
							<td class="valor">- R$ <? echo number_format(($loja_qtde_frisa * 50), 2, ',', '.'); ?></td>
						</tr>
						<?
						}
						?>
					</tbody>
				</table>
			</section>
			<?
			}
			?>
			</section>
			
		</section>

		<footer class="controle">
			<? /*if($cartao_credito && ($loja_status_transacao != 4)) { ?><a href="<? echo SITE.$link_lang; ?>minhas-compras/excluir/<? echo $loja_cod; ?>/" class="button cancelar-compra coluna big confirm" title="Deseja realmente cancelar a compra?">Cancelar compra</a><? }*/ ?>
			<a href="<? echo SITE.$link_lang; ?>minhas-compras/" class="cancel coluna">Voltar</a>
			<div class="clear"></div>
		</footer>
		<?

		if($loja_delivery) {

		?>
		<section id="financeiro-delivery" class="secao">
			<h2>Delivery</h2>
			<?

				$loja_endereco = utf8_encode($loja['LO_CLI_ENDERECO']);
				$loja_numero = utf8_encode($loja['LO_CLI_NUMERO']);
				$loja_complemento = utf8_encode($loja['LO_CLI_COMPLEMENTO']);
				$loja_bairro = utf8_encode($loja['LO_CLI_BAIRRO']);
				$loja_cidade = utf8_encode($loja['LO_CLI_CIDADE']);
				$loja_estado = utf8_encode($loja['LO_CLI_ESTADO']);
				$loja_cep = utf8_encode($loja['LO_CLI_CEP']);

				if(!empty($loja_endereco) && !empty($loja_numero) && !empty($loja_bairro) && !empty($loja_cidade) && !empty($loja_estado) && !empty($loja_cep)) {

				?>
				<p><? echo $loja_endereco; ?>, <? echo $loja_numero; ?> <? if (!empty($loja_complemento)){ echo '- '.$loja_complemento; } ?></p>
				<p>CEP: <? echo $loja_cep; ?> - <? echo $loja_bairro; ?>, <? echo $loja_cidade; ?> / <? echo $loja_estado; ?></p>

				<a href="<? echo SITE.$link_lang; ?>ingressos/delivery/<? echo $loja_cod; ?>/detalhes/" class="button alterar">Alterar endereço de entrega</a>
				<?

				} else {
				?>
				<a href="<? echo SITE.$link_lang; ?>ingressos/delivery/<? echo $loja_cod; ?>/detalhes/" class="button">Cadastrar endereço de entrega</a>
				<?
				}

			?>
		</section>
		<?
		
		}
	}
	?>	
	</section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//fechar conexao com o banco
include("conn/close.php");
include("conn/close-mssql.php");
include("conn/close-sankhya.php");

?>