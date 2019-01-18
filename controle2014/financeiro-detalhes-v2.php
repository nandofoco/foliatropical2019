<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$administrador = ($_SESSION['us-grupo'] == 'ADM') ? true : false;
$cod = (int) $_GET['c'];

//-----------------------------------------------------------------//

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, (CONVERT(VARCHAR, l.LO_DATA_ENTREGA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_ENTREGA, 108),1,5)) AS DATA_ENTREGA, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA, CONVERT(CHAR, l.LO_CLI_DATA_ENTREGA, 103) AS DATA_PARA_ENTREGA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);

?>
<section id="conteudo">
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
		$loja_parceiro = $loja['LO_PARCEIRO'];
		$loja_forma = $loja['LO_FORMA_PAGAMENTO'];
		$loja_diferenca_dias = (5 - $loja['DIFERENCA']);
		$loja_status_transacao = $loja['LO_STATUS_TRANSACAO'];
		$loja_pago = (bool) $loja['LO_PAGO'];
		$loja_delivery = (bool) $loja['LO_DELIVERY'];
		$loja_desconto = (bool) $loja['LO_DESCONTO'];
		if(!$loja_delivery) $loja_retirada = $loja['LO_RETIRADA'];
		if(!$loja_delivery) $loja_data_retirada = utf8_encode($loja['DATA_PARA_ENTREGA']);
		$loja_periodo = utf8_encode($loja['LO_CLI_PERIODO']);
		$loja_comissao_retida = (bool) $loja['LO_COMISSAO_RETIDA'];
		$loja_cancelado = (bool) $loja['D_E_L_E_T_'];
		$loja_cancelado_int = $loja_cancelado ? 1 : 0;
		
		$cartao_credito = ($loja_forma == 1) ? true : false;
		$multiplo = ($loja_forma == 10) ? true : false;
		$faturado = ($loja_forma == 7) ? true : false;
		$reserva = ($loja_forma == 5) ? true : false;

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

		$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
		$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
		$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));

		$loja_valor_total = $loja['LO_VALOR_TOTAL'];
		$loja_valor_total_f = number_format($loja_valor_total, 2, ',','.');

		if($loja_delivery) {
			$lo_valor_delivery = $loja['LO_VALOR_DELIVERY'];
			$lo_valor_delivery_f = number_format($lo_valor_delivery, 2, ',','.');			
		}
		
		$loja_entregue = (bool) $loja['LO_ENTREGUE'];
		if($loja_entregue) {
			$loja_data_entrega = $loja['DATA_ENTREGA'];
			$loja_entregue_nome = utf8_encode($loja['LO_ENTREGUE_NOME']);			
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

		$loja_camisas = true;

		//Verificar se tem um folia tropical do dia 01/03;
		//$loja_folia_item = ($_SERVER['SERVER_NAME'] == "server") ? 28 :  176;
		//$sql_folia = sqlsrv_query($conexao, "SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO<>'$loja_folia_item' AND D_E_L_E_T_='$loja_cancelado_int'", $conexao_params, $conexao_options);
		$sql_folia = sqlsrv_query($conexao, "SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO NOT IN (SELECT v.VE_COD FROM vendas v LEFT JOIN eventos_dias d ON d.ED_COD=v.VE_DIA WHERE v.VE_TIPO=4 AND d.ED_DATA IN ('2015-02-13', '2015-02-14', '2016-02-05', '2016-02-06')  AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0) AND D_E_L_E_T_='$loja_cancelado_int'", $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_folia) == 0) $loja_camisas = false;

		$loja_tid = $loja['LO_TID'];

	?>
	<header class="titulo">
		<h1>Detalhes da <span>Compra</span></h1>
		<div class="valor-total">R$ <? echo $loja_valor_total_f; ?></div>
	</header>
	<section class="padding">
		<section class="secao" id="compra-dados">
			<aside><? echo $loja_cod; ?></aside>
			<section>
				<h1><? echo $loja_nome; ?></h1>
				<p><? echo $loja_email; ?></p>
				<p><? echo formatTelefone($loja_telefone); ?></p>
			</section>

			<div class="informacoes-compra">
				<p><? echo $loja_forma_pagamento; ?></p>
				
				<?
				if(!$loja_cancelado && $cartao_credito && !empty($loja_cartao)) { ?><p class="cartao"><span class="<? echo $loja_cartao; ?>"></span> <? echo $loja_parcelas; ?>x R$ <? echo number_format(($loja_valor_total / $loja_parcelas), 2, ",", "."); ?> <strong> • <? echo $loja_cartao; ?></strong></p><? }
				
				//Faturado
				if($faturado) {
					
					$faturas_total = 0;

					//Buscar faturas
					$sql_faturas = sqlsrv_query($conexao, "SELECT LF_VALOR, COUNT(LF_COD) AS PARCELAS FROM loja_faturadas WHERE LF_COMPRA='$cod' AND D_E_L_E_T_='0' GROUP BY LF_VALOR", $conexao_params, $conexao_options);
					$n_faturas = sqlsrv_num_rows($sql_faturas);

					if($n_faturas > 0) {
						?>
						<p class="faturado">
						<?
						$ifaturas = 1;
						while ($faturas = sqlsrv_fetch_array($sql_faturas)) {
							
							$faturas_parcelas = $faturas['PARCELAS'];
							$faturas_valor = number_format($faturas['LF_VALOR'], 2, ",", ".");

							if($ifaturas > 1) echo ' + ';
							echo $faturas_parcelas.'x R$ '.$faturas_valor;
							$faturas_total += $faturas_parcelas;

							$ifaturas++;
						}
						?>
						</p>
						<?
					}

				}
				
				//Drop down para imprimir
				if(!$loja_cancelado) {
				
				?>
				<section class="menu-impressao fade">
					<a href="#" class="arrow"></a>

					<ul class="drop">
						<li><a href="<? echo SITE; ?>financeiro/etiqueta/<? echo $loja_cod; ?>/" class="etiqueta" title="Imprimir envelope do voucher <? echo $loja_cod; ?>?" target="_blank">Envelope do voucher</a></li>
						<li><a href="<? echo SITE; ?>financeiro/imprimir/<? echo $loja_cod; ?>/" class="print" title="Imprimir voucher <? echo $loja_cod; ?>?" target="_blank">Voucher impressão</a></li>
						<li><a href="<? echo SITE; ?>financeiro/imprimir/<? echo $loja_cod; ?>/entrega/" class="print entrega" title="Imprimir voucher entrega <? echo $loja_cod; ?>?" target="_blank">Voucher entrega</a></li>
						<li><a href="<? echo SITE; ?>financeiro/caderno/<? echo $loja_cod; ?>/" class="print entrega" title="Imprimir voucher caderno <? echo $loja_cod; ?>?" target="_blank">Controle interno</a></li>
						<li><a href="<? echo SITE; ?>financeiro/recibo/<? echo $loja_parceiro; ?>/" class="recibo" title="Imprimir recibo de comissão do parceiro?" target="_blank">Recibo de comissão</a></li>
						<? if(!empty($loja_tid)) { ?><li><span class="tid">TID: <? echo $loja_tid; ?></span></li><? } ?>
					</ul>
				</section>
				<?

				} elseif(!empty($loja_tid)) {

				?>
				<section class="menu-impressao fade">
					<a href="#" class="arrow"></a>

					<ul class="drop">
						<li><span class="tid">TID: <? echo $loja_tid; ?></span></li>
					</ul>
				</section>
				<?
				}

				if($cartao_credito && $loja_cielo_v2) {

					?>
					<section class="menu-cartao fade">
						<a href="#" class="liberar arrow">Info. cartão</a>

						<ul class="drop">
							<li><strong>Cartão de crédito:</strong> <? echo $loja_cielo_v2_numero_cartao; ?></li>
							<li><strong>Nome do cliente:</strong> <? echo $loja_cielo_v2_nome; ?></li>
							<li><strong>CPF/CNPJ:</strong> <? echo $loja_cielo_v2_cpf; ?></li>
							<li><strong>E-mail:</strong> <? echo $loja_cielo_v2_email; ?></li>
							<li><strong>Telefone:</strong> <? echo $loja_cielo_v2_telefone; ?></li>
							<? if(!empty($loja_tid)) { ?><li class="tid"><strong class="tid">TID:</strong> <? echo $loja_tid; ?></li><? } ?>
							<li class="<? echo $loja_cielo_v2_antifraude_classe; ?>"><strong>Anti Fraude:</strong> <? echo $loja_cielo_v2_antifraude_texto; ?></li>
						</ul>
					</section>
					<?
				}

				if(!$loja_cancelado && $administrador && $cartao_credito && !$loja_pago)  {

					//Captura de pagamento
					if(($loja_status_transacao == 4) && ($loja_diferenca_dias > -1)) {

						if($loja_cielo_v2) {
							?>
							<a href="https://cieloecommerce.cielo.com.br/Backoffice/Merchant/Order?OrderNumber=<? echo $loja_checkoutid; ?>&PageSize=50&PageIndex=1" target="_blank" class="liberar confirm" title="Confirmar o pagamento da compra <? echo $loja_cod; ?> (<? echo $loja_diferenca_dias; ?> dia<? echo $loja_diferenca_dias != 1 ? 's' : ''; ?> para expirar o prazo de confirmação)?">Confirmar (<? echo $loja_diferenca_dias; ?>)</a>
							<?
						} else {
							?>
							<a href="<? echo SITE; ?>compra/captura/<? echo $loja_cod; ?>/" class="liberar confirm" title="Confirmar o pagamento da compra <? echo $loja_cod; ?> (<? echo $loja_diferenca_dias; ?> dia<? echo $loja_diferenca_dias != 1 ? 's' : ''; ?> para expirar o prazo de confirmação)?">Confirmar (<? echo $loja_diferenca_dias; ?>)</a>
							<?
						}



					//Pagar compra sem status
					} else {
						?>
						<a href="<? echo SITE; ?>compras/pagamento/v2/<? echo $loja_cod; ?>/" class="liberar pagar confirm" title="Realizar o pagamento da compra <? echo $loja_cod; ?>?">Pagar</a>
						<?
					}					
					
					//Atualizar informações da compra
					//if(!empty($loja_xml) && !(($loja_status_transacao == 4) && ($loja_diferenca_dias > -1))) {
					if(!empty($loja_xml)) {
					?>
					<a href="<? echo SITE; ?>compra/atualizar/<? echo $loja_cod; ?>/" class="atualizar confirm" title="Atualizar as informações do pagamento da compra <? echo $loja_cod; ?>?"></a>
					<?
					}
				}

				if($multiplo && !$loja_cancelado && !$loja_pago && $administrador) {
					?>
						<a href="<? echo SITE; ?>compras/pagamento-multiplo/<? echo $loja_cod; ?>/" class="liberar confirm" title="Confirmar o pagamento da compra <? echo $loja_cod; ?>?">Confirmar</a>
					<?
				}
				
				//Faturado
				if($faturado) {
					
					/*$faturas_total = 0;

					//Buscar faturas
					$sql_faturas = sqlsrv_query($conexao, "SELECT LF_VALOR, COUNT(LF_COD) AS PARCELAS FROM loja_faturadas WHERE LF_COMPRA='$cod' AND D_E_L_E_T_='0' GROUP BY LF_VALOR", $conexao_params, $conexao_options);
					$n_faturas = sqlsrv_num_rows($sql_faturas);

					if($n_faturas > 0) {
						?>
						<p class="faturado">
						<?
						$ifaturas = 1;
						while ($faturas = sqlsrv_fetch_array($sql_faturas)) {
							
							$faturas_parcelas = $faturas['PARCELAS'];
							$faturas_valor = number_format($faturas['LF_VALOR'], 2, ",", ".");

							if($ifaturas > 1) echo ' + ';
							echo $faturas_parcelas.'x R$ '.$faturas_valor;
							$faturas_total += $faturas_parcelas;

							$ifaturas++;
						}
						?>
						</p>
						<?
					}*/
					
					if(!$loja_cancelado && !$loja_pago && $administrador){
					?>
					<a href="<? echo SITE; ?>financeiro/faturado/<? echo $loja_cod; ?>/" class="liberar" title="Confirmar o pagamento da compra <? echo $loja_cod; ?>?">Confirmar</a>
					<?						
					}

				}

				if(!$loja_cancelado && !$loja_pago && !$cartao_credito && !$multiplo && !$faturado && !$reserva && $administrador) {
					?>
					<a href="<? echo SITE; ?>e-financeiro-gerenciar.php?c=<? echo $loja_cod; ?>&a=confirmar" class="liberar confirm" title="Confirmar o pagamento da compra <? echo $loja_cod; ?>?">Confirmar</a>
					<?
				}

				if ($loja_pago){ ?> <span class="pago">Pago</span><? }
				if ($loja_cancelado){ ?> <span class="cancelado">Cancelado</span><? } ?>
			</div>

			<div class="clear"></div>
		</section>

		<?

		if(!$loja_pago) {
			$cupom_permitir = true;

			if(($loja_status_transacao == 4) && ($loja_diferenca_dias > -1)) $cupom_permitir = false;
			if($faturado && ($faturas_total > 0)) $cupom_permitir = false;
		}

		//Verificar a existencia de cupom de desconto para essa compra
		$sql_exist_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_COMPRA='$loja_cod' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='1' ", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_exist_cupom) > 0) {

			$cupom_utilizado = true;
			$cupom = sqlsrv_fetch_array($sql_exist_cupom);

			$cupom_cod = $cupom['CP_COD'];
			$cupom_nome = utf8_encode($cupom['CP_NOME']);
			$cupom_codigo = $cupom['CP_CUPOM'];
			$cupom_valor = $cupom['CP_DESCONTO'];
			$cupom_tipo = $cupom['CP_TIPO'];

		} 

		
		
		//if (!$loja_desconto || $loja_camisas){
		?>
		<section id="financeiro-cupom-camisas">

			<section id="comissao-retida" class="checkbox verify coluna">
				<ul><li><a href="<? echo SITE; ?>e-comissao-gerenciar.php?c=<? echo $loja_cod; ?>&a=<? echo $loja_comissao_retida ? 'cancelar' : 'confirmar' ; ?>" class="item <? if($loja_comissao_retida) { echo 'checked'; } ?>">Comissão retida</a></li></ul>
				<div class="clear"></div>
			</section>
			
			<? if (!$loja_desconto){ ?>
			<section id="cupom-pagamento" class="financeiro">
				<? if ($cupom_cod > 0){ ?>					
					<span class="cupom">
						<? echo $cupom_nome; ?> •  <? echo $cupom_codigo; ?> <? if (!(0 === strpos($cupom_codigo, 'FOLIA'))) { ?>• Desconto de  <? echo ($cupom_tipo == 1) ? round($cupom_valor)."%" : 'R$ '.number_format($cupom_valor, 2, ',', '.'); } ?>
						<? if($cupom_permitir) { ?><a href="<? echo SITE; ?>financeiro/detalhes/cupom/remover/<? echo $cupom_cod; ?>/<? echo $cod; ?>/" class="excluir confirm" title="Deseja remover o cupom &rdquo;<? echo $cupom_nome; ?>&ldquo;">&times;</a><? } ?>
					</span>
				<? } elseif($cupom_permitir && !$loja_cancelado) { ?>
				<form class="controle" id="form-cupom-pagamento" action="<? echo SITE; ?>financeiro/detalhes/cupom/" method="post">
					<input type="hidden" name="cod" value="<? echo $cod; ?>" />
					<input type="hidden" name="cliente" value="<? echo $loja_cliente; ?>" />
					<input type="hidden" name="financeiro" value="true" />
					<p>
						<label for="compra-cupom">Cupom de desconto:</label>
						<input type="text" name="cupom" class="input" id="compra-cupom" />
						<input type="submit" class="submit adicionar" value="Ok" />
					</p>
				</form>
				<? } ?>
			</section>
			<? } ?>

			<? if (!$loja_cancelado && $loja_camisas){ ?><a href="<? echo SITE; ?>financeiro/detalhes/camisas/<? echo $loja_cod; ?>/" class="cadastrar-camisas fancybox fancybox.iframe width600"></a><? } ?>

		</section>
		<? //} ?>

		<section id="financeiro-detalhes-itens" class="secao">
		<?

		$sql_item = sqlsrv_query($conexao, " SELECT COUNT(LI_COD) AS QTDE, LI_VALOR, LI_INGRESSO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='$loja_cancelado_int' GROUP BY LI_INGRESSO, LI_VALOR", $conexao_params, $conexao_options);

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
				$item_exclusividade = (bool) $item['EXCLUSIVIDADE'];
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
					<a href="<? echo SITE; ?>ingressos/comentario/novo/<? echo $item_cod; ?>/" class="comentario fancybox fancybox.iframe width600"></a>
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
							<td class="nome"><? if ($item_fechado && ($vendas_adicionais_nome_exibicao == 'transfer')){ echo "Qtde. ".$vendas_adicionais_qtde." - "; } echo $vendas_adicionais_label; ?></td>
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
		
		if($vendas_adicionais_delivery || $loja_desconto || !empty($loja_retirada) || $loja_entregue) {
		?>
		<section class="item-carrinho extra">
			<header>Informações extra</header>
			<table class="lista compras-adicionais">
				<tbody>
					<? if($vendas_adicionais_delivery) { ?>
					<tr class="incluso">
						<td class="check">&nbsp;</td>
						<td class="nome"><? echo $vendas_adicionais_delivery['label']; ?></td>
						<td class="valor">R$ <? echo $lo_valor_delivery_f; ?></td>
					</tr>
					<? }

					if(!empty($loja_retirada)) {
					?>
					<tr class="incluso">
						<td class="check">&nbsp;</td>
						<td class="nome" colspan="2">
							Retirada do Ingresso: <? echo $loja_data_retirada; ?> - <? echo ucfirst($loja_retirada); ?> - <? echo ucfirst($loja_periodo); ?>
							<? if($loja_entregue) echo '('.$loja_data_entrega.' - '.$loja_entregue_nome.')'; ?>
						</td>
					</tr>
					<?
					} elseif($loja_entregue) {
					?>
					<tr class="incluso">
						<td class="check">&nbsp;</td>
						<td class="nome" colspan="2">
							Entregue: <? echo $loja_data_entrega.' - '.$loja_entregue_nome; ?>
						</td>
					</tr>
					<?
					}

					if($loja_entregue) {
						$loja_data_entrega = $loja['DATA_ENTREGA'];
						$loja_entregue_nome = utf8_encode($loja['LO_ENTREGUE_NOME']);			
					}
					
					/*if($loja_desconto) {
					?>
					<tr class="incluso">
						<td class="check">&nbsp;</td>
						<td class="nome">Combo 2 dias na Folia (Desconto de 10%)</td>
					</tr>
					<? }*/

					if($loja_parceiro == 54) {

						$loja_combo_desconto = 0;

						if($loja_qtde_folia >= 2) {
							$loja_combo_desconto = 10;
							$loja_combo_nome = "Combo 2 dias na Folia (Desconto de 10%)";
						} else {
							foreach ($loja_qtde_combo as $k => $r) {
								if(($r == $combo_dias[$k]['total']) && ($combo_dias[$k]['desconto'] > $loja_combo_desconto)) {
									$loja_combo_desconto = $combo_dias[$k]['desconto'];
									$loja_combo_nome = $combo_dias[$k]['nome'].' (Desconto de '.str_replace('.', ',', round($loja_combo_desconto, 1)).'%)';
								}
							}
						}

						
						if($loja_combo_desconto > 0) {
						?>
						<tr class="incluso">
							<td class="check">&nbsp;</td>
							<td class="nome"><? echo $loja_combo_nome; ?></td>
							<td class="valor">- <? echo str_replace('.', ',', round($loja_combo_desconto, 1)); ?>%</td>
						</tr>
						<?
						}

					}
					
					if($loja_enable_frisa) {
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
		<footer class="controle">

			<? if (!$loja_cancelado && $administrador){ ?>
				<a href="<? echo SITE; ?>compras/alterar/<? echo $loja_cod; ?>/" class="button coluna big">Alterar tipos</a>
				<? /*if($reserva) {*/ ?><a href="<? echo SITE; ?>compras/modificar/<? echo $loja_cod; ?>/limpar/" class="button coluna modificar big">Modificar compra</a><? /*}*/ ?>
				
				<?
				$link_cancelar = ($loja_pago && $cartao_credito && ($loja_status_transacao == 6) && !$loja_cielo_v2) ? 'cancelar' : 'excluir';
				$texto_cancelar = ($loja_pago && $cartao_credito && ($loja_status_transacao == 6) && $loja_cielo_v2) ? ' O pedido deverá ser cancelado também no Backoffice da Cielo' : ''; 
				?>

				<a href="<? echo SITE; ?>compras/<? echo $link_cancelar; ?>/<? echo $loja_cod; ?>/" class="button cancelar-compra coluna big confirm" title="Deseja realmente cancelar a compra?<? echo $texto_cancelar; ?>">Cancelar compra</a>
			<? } ?>
			<a href="<? echo $_SERVER['HTTP_REFERER'];  /*strpos($_SERVER['HTTP_REFERER'], 'financeiro') ? $_SERVER['HTTP_REFERER'] : SITE.'financeiro/';*/ ?>" class="cancel coluna">Voltar</a>
			<div class="clear"></div>
		</footer>

		<section id="financeiro-pendencias">
			<form name="pendencias" method="post" action="<? echo SITE; ?>compras/pendencias/">
				
				<input type="hidden" name="cod" value="<? echo $loja_cod; ?>">

				<h2>Pendências</h3>
				<section id="compras-pendencias" class="checkbox verify">
					<ul>
						<?

						//Buscar pendencias
						$sql_pendencias = sqlsrv_query($conexao, "SELECT * FROM pendencias WHERE PE_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
						if(sqlsrv_num_rows($sql_pendencias) > 0) {

							while ($pendencias = sqlsrv_fetch_array($sql_pendencias)) {
								
								$pendencias_cod = $pendencias['PE_COD'];
								$pendencias_nome = utf8_encode($pendencias['PE_NOME']);

								//Buscar pendencias
								$sql_pendencias_ins = sqlsrv_query($conexao, "SELECT LP_COD FROM loja_pendencias WHERE LP_COMPRA='$loja_cod' AND LP_PENDENCIA='$pendencias_cod' AND LP_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
								$pendencias_check = (sqlsrv_num_rows($sql_pendencias_ins) > 0) ? true : false;

								?>
								<li><label class="item <? if($pendencias_check) { echo 'checked'; } ?>"><input type="checkbox" name="pendencias[]" value="<? echo $pendencias_cod; ?>" <? if($pendencias_check) { echo 'checked="checked"'; } ?> /><? echo $pendencias_nome; ?></label></li>
								<?
							}
						}

						?>
					</ul>
					<div class="clear"></div>
				</section>

				<footer class="controle">
					<input type="submit" class="submit" value="Confirmar" />
					<div class="clear"></div>
				</footer>
			</form>
		</section>
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
				$loja_data_para_entrega = utf8_encode($loja['DATA_PARA_ENTREGA']);
				$loja_cuidados = utf8_encode($loja['LO_CLI_CUIDADOS']);
				$loja_celular = utf8_encode($loja['LO_CLI_CELULAR']);
				$loja_referencia = utf8_encode($loja['LO_CLI_PONTO_REFERENCIA']);

				if(!empty($loja_endereco) && !empty($loja_numero) && !empty($loja_bairro)) {

				?>
				<p><? echo $loja_endereco; ?>, <? echo $loja_numero; ?> <? if (!empty($loja_complemento)){ echo '- '.$loja_complemento; } ?></p>
				<p><? if(!empty($loja_cep)) { ?>CEP: <? echo $loja_cep; ?> - <? } echo $loja_bairro; ?>, <? echo $loja_referencia; ?></p>
				<p><? echo $loja_data_para_entrega ?> - Período: <? echo $loja_periodo; ?></p>
				<p>A/C.: <? echo $loja_cuidados; ?> - <? echo $loja_celular; ?></p>
				<? if(!$loja_cancelado) { ?><a href="<? echo SITE; ?>compras/delivery/<? echo $loja_cod; ?>/detalhes/" class="button alterar">Alterar endereço de entrega</a><? } ?>
				<?

				} elseif(!$loja_cancelado) {
				?>
				<a href="<? echo SITE; ?>compras/delivery/<? echo $loja_cod; ?>/detalhes/" class="button">Cadastrar endereço de entrega</a>
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

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>