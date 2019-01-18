<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//arquivos de layout
// include("include/head.php");
// include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$cod = (int) $_GET['c'];
$entrega = (bool) $_GET['entrega'];

//Novos combos
$loja_qtde_combo = array();

//-----------------------------------------------------------------//

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA, CONVERT(VARCHAR, l.LO_DEADLINE, 103) AS DATA_DEADLINE, CONVERT(CHAR, l.LO_CLI_DATA_ENTREGA, 103) AS DATA_PARA_ENTREGA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);

//paises
$sql_paises = sqlsrv_query($conexao_sankhya, "SELECT PAIS_SIGLA,PAIS_NOME,PAIS_PHONECODE FROM pais ORDER BY PAIS_NOME", $conexao_params, $conexao_options);
$paises=array();
while ($linha = sqlsrv_fetch_array($sql_paises)) {
    $paises[$linha['PAIS_SIGLA']]=array("nome"=>$linha['PAIS_NOME'],"ddi"=>$linha['PAIS_PHONECODE']);
}
utf8_encode_deep($paises);

?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Folia Tropical</title>

<link rel="shortcut icon" href="<? echo SITE; ?>favicon.ico" />
<link rel="stylesheet" type="text/css" href="<? echo SITE; ?>css/print.css"/>

<link rel="stylesheet" type="text/css" href="<? echo SITE; ?>fancybox/jquery.fancybox.css?v=2.1.5" media="screen" />
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400,600,700"/>

<!--[if IE]>
<script src="<? echo SITE; ?>js/html5.js" type="text/javascript"></script>
<![endif]-->

</head>
<body class="entrega caderno">
	<section id="conteudo">

	<?

	if(sqlsrv_num_rows($sql_loja) > 0) {

		$loja_qtde_folia = 0;
		$loja_qtde_frisa = 0;
		$loja_enable_frisa = false;

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_cod = $loja['LO_COD'];
		$loja_cliente = $loja['LO_CLIENTE'];
		$loja_parceiro = $loja['LO_PARCEIRO'];
		$loja_vendedor = $loja['LO_VENDEDOR'];
		$loja_forma = $loja['LO_FORMA_PAGAMENTO'];
		$loja_diferenca_dias = (5 - $loja['DIFERENCA']);
		$loja_status_transacao = $loja['LO_STATUS_TRANSACAO'];
		$loja_data = $loja['DATA'];
		$loja_pago = (bool) $loja['LO_PAGO'];
		$loja_delivery = (bool) $loja['LO_DELIVERY'];
		if(!$loja_delivery) $loja_retirada = $loja['LO_RETIRADA'];
		if(!$loja_delivery) $loja_data_retirada = utf8_encode($loja['DATA_PARA_ENTREGA']);
		$loja_periodo = utf8_encode($loja['LO_CLI_PERIODO']);

		$loja_datetime = $loja['LO_DATA_COMPRA'];
		$anterior = (strtotime($loja_datetime->format('Y-m-d')) < strtotime('2015-10-15')) ? true : false;
		$loja_desconto_folia = ($anterior) ? 1 : (bool) $loja['LO_DESCONTO_FOLIA'];
		$loja_desconto_frisa = ($anterior) ? 1 : (bool) $loja['LO_DESCONTO_FRISA'];
		
		$loja_deadline = $loja['DATA_DEADLINE'];
		$loja_concierge = $loja['LO_CONCIERGE'];
		$loja_origem_ar = $loja['LO_ORIGEM'];

		//$loja_valor_adicionais = number_format($loja['LO_VALOR_ADICIONAIS'] + $loja['LO_VALOR_TRANSFER'] + $loja['LO_VALOR_DELIVERY'], 2, ',','.');
		$loja_valor_adicionais = number_format($loja['LO_VALOR_ADICIONAIS'] + $loja['LO_VALOR_DELIVERY'], 2, ',','.');

		$loja_valor_desconto = $loja['LO_VALOR_DESCONTO'] > 0  ? $loja['LO_VALOR_DESCONTO'] : $loja['LO_VALOR_DESCONTO_FT'];

		// $loja_valor_desconto = $loja['LO_VALOR_DESCONTO_FT'];


      	$loja_valor_over_interno = $loja['LO_VALOR_OVER_INTERNO'];
      	$loja_valor_over_externo = $loja['LO_VALOR_OVER_EXTERNO'];
      	//$loja_valor_ingressos = $loja['LO_VALOR_INGRESSOS'];
      	$loja_comissao = $loja['LO_COMISSAO'];
      	$loja_comissao_retida = (bool) $loja['LO_COMISSAO_RETIDA'];

      	$sql_valor_ingressos = sqlsrv_query($conexao, "SELECT SUM(LI_VALOR_TABELA) AS INGRESSOS FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
      	if(sqlsrv_num_rows($sql_valor_ingressos) > 0) {
      		$loja_valor_ingressos_ar = sqlsrv_fetch_array($sql_valor_ingressos);
      		$loja_valor_ingressos = $loja_valor_ingressos_ar['INGRESSOS'];
      	}
      	
      	//if($loja_cod == '4314') echo " (($loja_valor_ingressos - $loja_valor_desconto + $loja_valor_over_interno) * $loja_comissao / 100) ";

      	$loja_comissao_valor = number_format((($loja_valor_ingressos - $loja_valor_desconto + $loja_valor_over_interno) * $loja_comissao / 100), 2, ",", ".");

      	// $loja_valor_desconto = number_format($loja_valor_desconto, 2, ',','.');
      	$loja_valor_over_interno = number_format($loja_valor_over_interno, 2, ',','.');
      	$loja_valor_over_externo = number_format($loja_valor_over_externo, 2, ',','.');

		switch ($loja_origem_ar) {
			case 'telefone': $loja_origem = 'Telefone'; break;
			case 'balcao': $loja_origem = 'Balcão'; break;			
			case 'site': default: $loja_origem = 'Site'; break;
			case 'chatonline': default: $loja_origem = 'Chat Online'; break;
			case 'email': default: $loja_origem = 'E-mail'; break;
		}

		$cartao_credito = ($loja_forma == 1) ? true : false;
		$faturado = ($loja_forma == 7) ? true : false;
		$reserva = ($loja_forma == 5) ? true : false;
		$multiplo = ($loja_forma == 10) ? true : false;

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, CELULAR, EMAIL, DDD, DDI, DDD_CELULAR, DDI_CELULAR FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

		$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
		$loja_ddi = utf8_encode(trim($loja_cliente_ar['DDI']));
		$loja_ddd = utf8_encode(trim($loja_cliente_ar['DDD']));
		$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));

		$loja_ddi_cel = utf8_encode(trim($loja_cliente_ar['DDI_CELULAR']));
		$loja_ddd_cel = utf8_encode(trim($loja_cliente_ar['DDD_CELULAR']));
		$loja_celular = utf8_encode(trim($loja_cliente_ar['CELULAR']));
		$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));

		// $loja_valor_total = $loja['LO_VALOR_TOTAL'];
		// $loja_valor_total_f = number_format($loja['LO_VALOR_TOTAL'], 2, ',','.');

		//Forma de pagamento
		// LO_FORMA_PAGAMENTO
		$sql_forma = sqlsrv_query($conexao, "SELECT TOP 1 FP_NOME FROM formas_pagamento WHERE FP_COD='$loja_forma'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_forma) > 0) {
			$loja_forma_ar = sqlsrv_fetch_array($sql_forma);
			$loja_forma_pagamento = utf8_encode($loja_forma_ar['FP_NOME']);
		}

		//Valor pendente caso esteja não pago
		$loja_valor_pendente = $loja_valor_total;

		//Se for cartão de credito
		if($cartao_credito) {

			//Buscar a bandeira
			$loja_cartao = $loja['LO_CARTAO'];

			//XML
			$loja_xml = $loja['LO_XML'];

			if(!empty($loja_xml)) {
				$xml = new SimpleXMLElement($loja_xml);
				$loja_parcelas = $xml->{'forma-pagamento'}->parcelas;
  			}
			
		}

		if($faturado) {

			$loja_valor_pendente = 0;

			//Buscar faturas
			$sql_faturas = sqlsrv_query($conexao, "SELECT LF_VALOR, LF_PAGO FROM loja_faturadas WHERE LF_COMPRA='$cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
			$loja_parcelas = sqlsrv_num_rows($sql_faturas);

			if($loja_parcelas > 0) {
				
				while ($faturas = sqlsrv_fetch_array($sql_faturas)) {
					
					$faturas_pago = (bool) $faturas['LF_PAGO'];
					$faturas_valor = $faturas['LF_VALOR'];

					if(!$faturas_pago) $loja_valor_pendente += $faturas_valor;

				}
			}
		}
		
		//Vendedor
		$sql_vendedor = sqlsrv_query($conexao, "SELECT TOP 1 US_NOME FROM usuarios WHERE US_COD='$loja_vendedor'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_vendedor) > 0) {
			$loja_vendedor_ar = sqlsrv_fetch_array($sql_vendedor);			
			$loja_vendedor_nome = utf8_encode($loja_vendedor_ar['US_NOME']);
		}
		


		//Buscar evento
		$sql_evento = sqlsrv_query($conexao, "SELECT TOP 1 EV_NOME FROM eventos WHERE EV_COD='$evento'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_evento) > 0) {
			$eventoar = sqlsrv_fetch_array($sql_evento);
			$evento_nome = utf8_encode($eventoar['EV_NOME']);
		}

		//Buscar informações do parceiro
		$sql_parceiro = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL, DDD, DDI FROM TGFPAR WHERE CODPARC='$loja_parceiro' AND VENDEDOR='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_parceiro) > 0) $loja_parceiro_ar = sqlsrv_fetch_array($sql_parceiro);

		$loja_parceiro_nome = utf8_encode(trim($loja_parceiro_ar['NOMEPARC']));
		$loja_parceiro_ddi = utf8_encode(trim($loja_parceiro_ar['DDI']));
		$loja_parceiro_ddd = utf8_encode(trim($loja_parceiro_ar['DDD']));
		$loja_parceiro_telefone = utf8_encode(trim($loja_parceiro_ar['TELEFONE']));
		$loja_parceiro_email = utf8_encode(trim($loja_parceiro_ar['EMAIL']));


		//Buscar vendedor externo
		if($loja_concierge > 0) {
			
			$sql_vendedor_externo = sqlsrv_query($conexao, "SELECT TOP 1 VE_NOME FROM vendedor_externo WHERE VE_COD='$loja_concierge'", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_vendedor_externo) > 0) {
				$vendedorexar = sqlsrv_fetch_array($sql_vendedor_externo);
				$vendedor_externo_nome = utf8_encode($vendedorexar['VE_NOME']);
			}

		}


	?>
	<header id="topo">
		<img src="<? echo SITE; ?>img/logo-entrega.png" class="logo" />
			<section class="caderno">
				<h1>Controle Interno</h1>
				<p>Atendente: <? echo $loja_vendedor_nome; ?></p>
				<p>Data: <? echo $loja_data; ?></p>
			</section>

			<section class= "vias">
				<p>1ª Via - Caderno</p>
				<p>2ª Via - Pasta</p>
			</section>

	</header>

	<section id="detalhes-compra">
		<h1><? echo $evento_nome; ?></h1>
		<h2>Voucher nº <span><? echo $loja_cod; ?></span></h2>
	</section>

	<section id="informacoes-gerais" class="secao">
		<table>
			<tr>
				<th>Canal de venda</th>
				<th  class="email">Contato</th>
				<th class="telefone">Tel. Canal</th>
			</tr>
			<tr>
				<td><? echo $loja_parceiro_nome; ?></td>
				<td  class="email"><? echo $vendedor_externo_nome; ?></td>
				<td class="telefone"><? echo "+".$paises[$loja_parceiro_ddi]['ddi']." ".$loja_parceiro_ddd." ".$loja_parceiro_telefone; ?></td>
			</tr>
		</table>

		<table>
			<tr>
				<th>Paxs</th>
				<th  class="email">E-mail</th>
				<? if($loja_telefone > 0): ?><th class="telefone">Tel. Pax</th><? endif; ?>
				<? if($loja_celular > 0): ?><th class="celular">Cel. Pax</th><? endif; ?>
			</tr>
			<tr>
				<td><? echo $loja_nome; ?> &nbsp;</td>
				<td  class="email"><? echo $loja_email; ?></td>
				<? if($loja_telefone > 0): ?><td class="telefone"><? echo "+".$paises[$loja_ddi]['ddi']." ".$loja_ddd." ".$loja_telefone; ?></td><? endif; ?>
				<? if($loja_celular > 0): ?><td class="telefone"><? echo "+".$paises[$loja_ddi_cel]['ddi']." ".$loja_ddd_cel." ".$loja_celular; ?></td><? endif; ?>
			</tr>
		</table>
	</section>

	<section id="informacoes-servicos" class="secao">
		
		<?

		$ar_cods_transfer = array();

		//Selecionar código do transfer
		$sql_transfer = sqlsrv_query($conexao, "SELECT VA_COD FROM vendas_adicionais WHERE (VA_NOME_EXIBICAO='transfer' OR VA_NOME_EXIBICAO='transferinout') AND VA_BLOCK='0' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_transfer) > 0) {
			$transfer_cod = array();
			while($ar_transfer = sqlsrv_fetch_array($sql_transfer)) array_push($transfer_cod, $ar_transfer['VA_COD']);
			$transfer_cod = implode(",", $transfer_cod);
			
			//Selecionar somente os que tem transfer
			$sql_cods_transfer = sqlsrv_query($conexao, "SELECT LIA_ITEM FROM loja_itens_adicionais WHERE LIA_COMPRA='$loja_cod' AND LIA_ADICIONAL IN ($transfer_cod) AND LIA_BLOCK='0' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_cods_transfer) > 0) {
				while($cods_transfer = sqlsrv_fetch_array($sql_cods_transfer)) array_push($ar_cods_transfer, $cods_transfer['LIA_ITEM']);
			}

		}

		//Itens
		$sql_item = sqlsrv_query($conexao, " SELECT COUNT(LI_COD) AS QTDE, LI_VALOR, LI_VALOR_TABELA, LI_INGRESSO, LI_VALOR_TRANSFER, LI_VALOR_ADICIONAIS, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0' GROUP BY LI_INGRESSO, LI_VALOR, LI_VALOR_TABELA, LI_VALOR_TRANSFER, LI_VALOR_ADICIONAIS, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO", $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_item) > 0) {
			$i = 1;
			$item_count = 1;

			$loja_valor_total = 0;

			while ($item = sqlsrv_fetch_array($sql_item)) {

				$item_cod = $item['COD'];
				$item_qtde = $item['QTDE'];
				$item_ingresso = $item['LI_INGRESSO'];
				$item_valor_tabela_f =  $item['LI_VALOR_TABELA'];
				$item_valor_tabela =  number_format($item['LI_VALOR_TABELA'], 2, ",", ".");
				$item_desconto =  number_format($item['LI_DESCONTO'], 2, ",", ".");
				$item_desconto_f=  $item['LI_DESCONTO'];
				$item_over_interno =  number_format($item['LI_OVER_INTERNO'], 2, ",", ".");
				$item_over_externo =  number_format($item['LI_OVER_EXTERNO'], 2, ",", ".");				
				$item_adicionais_f =  $item['LI_VALOR_TRANSFER'] + $item['LI_VALOR_ADICIONAIS'];
				$item_valor_f =  $item['LI_VALOR'];
				
				$item_adicionais = number_format($item_adicionais_f, 2, ",", ".");
				$item_valor = number_format($item_valor_f + $item_adicionais_f, 2, ",", ".");
				$item_valor_f = $item_valor_f + $item_adicionais_f;

				$item_exclusividade = $item['EXCLUSIVIDADE'];
				$item_exclusividade_val = $item['EXCLUSIVIDADE_VAL'];

				//Informações adicionais do item
				$sql_info_item = sqlsrv_query($conexao, "
				SELECT v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_NOME, ed.ED_DATA, CONVERT(VARCHAR, ed.ED_DATA, 103) AS DIA, DATEPART(WEEKDAY, ed.ED_DATA) AS SEMANA, tp.TI_NOME, tp.TI_TAG  
				FROM vendas v, eventos_setores es, eventos_dias ed, tipos tp 
				WHERE v.VE_COD='$item_ingresso' AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD", $conexao_params, $conexao_options);

				if(sqlsrv_num_rows($sql_info_item) > 0) {
					$info_item = sqlsrv_fetch_array($sql_info_item);
				
					$item_setor = utf8_encode($info_item['ES_NOME']);
					$item_dia = utf8_encode($info_item['ED_NOME']);
					$item_data = utf8_encode($info_item['DIA']);
					$item_data_n = $info_item['ED_DATA'];
					$item_semana = $semana[($info_item['SEMANA']-1)];
					$item_tipo = utf8_encode($info_item['TI_NOME']);
					$item_tipo_tag = $info_item['TI_TAG'];
					
					$item_fila = utf8_encode($info_item['VE_FILA']);
					$item_vaga = utf8_encode($info_item['VE_VAGAS']);
					$item_tipo_especifico = utf8_encode($info_item['VE_TIPO_ESPECIFICO']);

					$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;

					$item_data_n = (string) date('Y-m-d', strtotime($item_data_n->format('Y-m-d')));

					if($item_fechado) { 
						$item_valor_tabela_f = $item_valor_tabela_f*$item_qtde/$item_vaga;
						$item_over_interno_f_soma = $item['LI_OVER_INTERNO']*$item_vaga;
						// $item_over_interno = number_format($item_over_interno_f, 2, ",", "."); 
						$item_adicionais_f_soma = $item_adicionais_f*$item_vaga; 
						// $item_adicionais = number_format($item_adicionais_f, 2, ",", ".");	
					} else {
						$item_valor_tabela_f = $item_valor_tabela_f*$item_qtde;
						$item_over_interno_f_soma = $item['LI_OVER_INTERNO']*$item_qtde;
						$item_over_externo_f_soma = $item['LI_OVER_EXTERNO']*$item_qtde;
						$item_adicionais_f_soma = $item_adicionais_f*$item_qtde;
						$item_desconto_f = $item_desconto_f*$item_qtde; 
						$item_desconto =  number_format($item_desconto_f, 2, ",", ".");
					}



					$item_valor = number_format($item_valor_tabela_f + $item_over_interno_f_soma + $item_over_externo_f_soma + $item_adicionais_f_soma - $item_desconto_f, 2, ",", ".");
					$item_valor_f = $item_valor_tabela_f + $item_over_interno_f_soma + $item_over_externo_f_soma + $item_adicionais_f_soma;

					$loja_valor_total += $item_valor_f;
					$loja_desconto_total += $item_desconto_f;					

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

								foreach ($combo_dias as $k => $c) {
									//Verificar cada ocorrencia
									if(in_array($item_data_n, $c['dias'])) {

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

					//Desconto Frisa
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
			<table>
				<tr>
					<th class="qtde">Qtde.</th>
					<th>Tipo</th>
					<th class="setor">Setor</th>
					<th class="data">Data</th>
					<th class="total">Val. Un.</th>
					<th class="total">Desconto</th>
					<th class="total">Ov. Int.</th>
					<th class="total">Ov. Ext.</th>
					<th class="total">Adic.</th>
					<th class="total">Total</th>
				</tr>
				<tr>
					<td>
						<? echo $item_qtde; ?> 
						<?
						if (!$item_fechado){ 
							switch ($item_tipo_especifico) {
								case 'vaga':
									echo $item_tipo_especifico;
									if ($item_qtde > 1){ echo 's'; }
								break;
								case 'lugar':
									echo $item_tipo_especifico;
									if ($item_qtde > 1){  echo 'es'; }
								break;									
								case 'fechado':
									echo $item_tipo_especifico;
								break;
								default:
									echo 'vaga';
									if ($item_qtde > 1){  echo 's'; }
								break;

							}							
						}
						?>
					</td>
					<td>
					<?
						echo $item_tipo;
						if(!empty($item_tipo_especifico)) { echo " ".$item_tipo_especifico; }
						if($item_fechado) { echo " (".$item_vaga." vagas)"; }
					?>
					</td>
					<td>
						<?
						echo $item_setor;
						if(!empty($item_fila)) { echo " ".$item_fila; }
						?>
					</td>
					<td>
						<? echo $item_dia; ?> dia -
						<? echo $item_semana; ?> -
						<? echo $item_data; ?>
					</td>
					<td><? echo $item_valor_tabela; ?></td>
					<td><? echo $item_desconto; ?></td>
					<td><? echo $item_over_interno; ?></td>
					<td><? echo $item_over_externo; ?></td>
					<td><? echo $item_adicionais; ?></td>
					<td><? echo $item_valor; ?></td>
				</tr>
				<?

				unset($agendamento, $agendamento_cod, $agendamento_horario, $agendamento_roteiro, $agendamento_transporte);

				if(in_array($item_cod, $ar_cods_transfer)) { 

					//busca agendamento do item
					$sql_agendamento = sqlsrv_query($conexao, "SELECT ta.*, th.*, tr.*, ro.*, SUBSTRING(CONVERT(CHAR, th.TH_HORA, 8), 1, 5) AS HORA  FROM transportes_agendamento ta, transportes_horarios th, transportes tr, roteiros ro WHERE ta.TA_ITEM='$item_cod' AND ta.TA_HORARIO=th.TH_COD AND th.TH_TRANSPORTE=tr.TR_COD AND tr.TR_ROTEIRO=ro.RO_COD AND ta.D_E_L_E_T_='0'", $conexao_params, $conexao_options);
					$n_agendamento = sqlsrv_num_rows($sql_agendamento);
					if($n_agendamento > 0) {

						$agendamento = sqlsrv_fetch_array($sql_agendamento);
						$agendamento_cod = $agendamento['TA_COD'];
						$agendamento_horario = utf8_encode($agendamento['HORA']);
						$agendamento_roteiro = utf8_encode($agendamento['RO_NOME']);
						$agendamento_transporte = utf8_encode($agendamento['TR_NOME']);

					}
				}
				?>
				<tr>
					<th colspan="4">Transfer/Bairro</th>
					<th colspan="4">Hotel</th>
					<th colspan="2">Horário</th>
				</tr>
				<tr>
					<td colspan="4"><? echo $agendamento_roteiro; ?>&nbsp;</td>
					<td colspan="4"><? echo $agendamento_transporte; ?></td>
					<td colspan="2"><? echo $agendamento_horario; ?></td>
				</tr>

				<tr>
					<th>Obs.</th>
					<td colspan="9">
						<?
						#if(($loja_parceiro == 54) && ($loja_qtde_folia >= 2) && !$loja_folia_disabled) {
						/*if(($loja_qtde_folia >= 2) && !$loja_folia_disabled) {
							$loja_folia_disabled = true;
							echo 'Desconto de 10% no Valor Total pelo Combo 2 dias na Folia<br />';
						}*/

						if($loja_desconto_folia && !$loja_folia_disabled) {
							$loja_combo_desconto = 0;
							foreach ($loja_qtde_combo as $k => $r) {
								if(($r == $combo_dias[$k]['total']) && ($combo_dias[$k]['desconto'] > $loja_combo_desconto)) {
									$loja_folia_disabled = true;
									$loja_combo_desconto = $combo_dias[$k]['desconto'];
									echo $combo_dias[$k]['nome'].' (Desconto de '.str_replace('.', ',', round($loja_combo_desconto, 1)).'%)';
								}
							}
						}

						if($loja_desconto_frisa && $loja_enable_frisa) {
							$loja_enable_frisa = false;
							$s = $loja_qtde_frisa > 1 ? 's' : '';
							echo 'Desconto de R$ '.number_format(($loja_qtde_frisa * 50), 2, ',', '.').' no Valor Total para '.$loja_qtde_frisa.' Frisa'.$s.' fechada'.$s.' <br />';
						}

						// $sql_adicionais = sqlsrv_query($conexao, "SELECT lia.LIA_COD, v.* FROM loja_itens_adicionais lia, vendas_adicionais v WHERE v.VA_COD=lia.LIA_ADICIONAL AND lia.LIA_COMPRA='$cod' AND lia.LIA_ITEM='$item_cod'", $conexao_params, $conexao_options);
						$sql_adicionais = sqlsrv_query($conexao, "SELECT MAX(lia.LIA_COD) AS LIA_COD, COUNT(lia.LIA_COD) AS QTDE, MAX(v.VA_LABEL) AS VA_LABEL, MAX(v.VA_NOME_EXIBICAO) AS VA_NOME_EXIBICAO,
							MAX(lia.LIA_VALOR) AS LIA_VALOR,
							MAX(v.VA_VALOR_MULTI) AS VA_VALOR_MULTI,
							MAX(lia.LIA_INCLUSO) AS LIA_INCLUSO
							FROM loja_itens_adicionais lia, vendas_adicionais v, vendas_adicionais_valores vv 

							WHERE vv.VAV_ADICIONAL=v.VA_COD AND vv.VAV_BLOCK=0 AND vv.D_E_L_E_T_=0 AND v.VA_BLOCK=0 AND v.D_E_L_E_T_=0 AND v.VA_COD=lia.LIA_ADICIONAL AND lia.LIA_COMPRA='$cod' AND v.VA_NOME_EXIBICAO LIKE '%transfer%'
							AND lia.LIA_ITEM IN (SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO='$item_ingresso' AND D_E_L_E_T_='0')
							AND lia.D_E_L_E_T_='0'
							GROUP BY v.VA_COD
							", $conexao_params, $conexao_options);
						if(sqlsrv_num_rows($sql_adicionais) > 0) {
							$vendas_adicionais = sqlsrv_fetch_array($sql_adicionais);

							$vendas_adicionais_opcoes_valor_n = $vendas_adicionais['LIA_VALOR'];
							$vendas_adicionais_multi = (bool) $vendas_adicionais['VA_VALOR_MULTI'];
							$vendas_adicionais_opcoes_valor = ($vendas_adicionais_multi) ? $vendas_adicionais_opcoes_valor_n * $item_qtde : $vendas_adicionais_opcoes_valor_n;
							$vendas_adicionais_opcoes_incluso = (bool) $vendas_adicionais['LIA_INCLUSO'];

							if(!$vendas_adicionais_opcoes_incluso && ($vendas_adicionais_opcoes_valor > 0)) echo 'Com Transfer no valor de R$ '.number_format($vendas_adicionais_opcoes_valor, 2, ',', '.').'<br />';
						}
						
						if($item_exclusividade && !empty($item_exclusividade_val)) echo 'Com exclusividade fila '.$item_exclusividade_val.'<br />';

						//Buscar comentarios
						$sql_item_comentario = sqlsrv_query($conexao, "SELECT TOP 1 LC_COMENTARIO FROM loja_comentarios WHERE LC_ITEM='$item_cod'", $conexao_params, $conexao_options);
						if(sqlsrv_num_rows($sql_item_comentario) > 0){
							$ar_item_comentario = sqlsrv_fetch_array($sql_item_comentario);
							echo "<strong>Externo:</strong> ".utf8_encode($ar_item_comentario['LC_COMENTARIO'])."<br />";
						}
						//Buscar comentarios
						$sql_item_comentario_interno = sqlsrv_query($conexao, "SELECT TOP 1 LC_COMENTARIO FROM loja_comentarios_internos WHERE LC_ITEM='$item_cod'", $conexao_params, $conexao_options);
						if(sqlsrv_num_rows($sql_item_comentario_interno) > 0){
							$ar_item_comentario_interno = sqlsrv_fetch_array($sql_item_comentario_interno);
							echo "<strong>Interno:</strong> ".utf8_encode($ar_item_comentario_interno['LC_COMENTARIO']);
						}
						?>
					</td>
				</tr>
			</table>
			<?

			}		

		}

		$loja_valor_total_f = number_format($loja_valor_total-$loja_desconto_total, 2, ",", ".");
		?>
	</section>

	<section id="informacoes-pagamento" class="secao">
		<table>
			<tr>
				<th>Origem</th>
				<th>Forma Pgto.</th>
				<th>Status Pgto.</th>
				<th class="data">Deadline</th>
				<th class="total">Desconto</th>
				<th class="total">Ov. Int.</th>
				<th class="total">Ov. Ext.</th>
				<th class="total">Comissão <? if($loja_comissao_retida) { echo 'Retida'; } ?></th>
				<th class="total">Adic.</th>
				<th class="total">Total Geral</th>
			</tr>
			<tr>
				<td><? echo $loja_origem; ?></td>
				<td>
					<? echo $loja_forma_pagamento; ?> 
					<?
					if($cartao_credito && ($loja_parcelas > 1)) { echo " - Parcelado em ".$loja_parcelas."x"; }
					if($faturado && ($loja_parcelas > 1)) { echo " - Parcelado em ".$loja_parcelas."x"; }
					?>
				</td>
				<td>
					<?
					if($reserva) echo 'Reserva';
					else echo $loja_pago ? 'Pago' : 'Não pago';
					?>
				</td>
				<td><? echo $loja_deadline; ?></td>
				<td><?
					
					//Verificar a existencia de cupom de desconto para essa compra
					$sql_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_COMPRA='$loja_cod' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='1' ", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_cupom) > 0) {

						$cupom_utilizado = true;
						$cupom = sqlsrv_fetch_array($sql_cupom);

						$cupom_cod = $cupom['CP_COD'];
						$cupom_nome = utf8_encode($cupom['CP_NOME']);
						$cupom_codigo = $cupom['CP_CUPOM'];
						$cupom_valor = $cupom['CP_DESCONTO'];
						$cupom_tipo = $cupom['CP_TIPO'];
						
						$loja_valor_desconto = 'Cupom ';
						$loja_valor_desconto .= ($cupom_tipo == 1) ? round($cupom_valor)."%" : number_format($cupom_valor, 2, ',', '.');

					} else {						
						$loja_valor_desconto = number_format($loja_valor_desconto, 2, ',','.');
					}

					echo $loja_valor_desconto;

				?></td>
				<td><? echo $loja_valor_over_interno; ?></td>
				<td><? echo $loja_valor_over_externo; ?></td>
				<td><? echo $loja_comissao_valor; ?></td>
				<td><? echo $loja_valor_adicionais; ?></td>
				<td><? echo $loja_valor_total_f; ?></td>
			</tr>

			<? 
			if ($multiplo): 

				$sql_multiplo = sqlsrv_query($conexao, "SELECT p.PM_FORMA, p.PM_VALOR, p.PM_PAGO
														FROM loja_pagamento_multiplo p, formas_pagamento f
														WHERE p.PM_LOJA=$loja_cod AND p.PM_FORMA=f.FP_COD", $conexao_params, $conexao_options);
			?>
			<tr>
				<th colspan="2">Cartão Pos</th>
				<th colspan="2">Cheque</th>
				<th colspan="2">Depósito</th>
				<th colspan="2">Dinheiro</th>
				<th colspan="2">Outros</th>
			</tr>
			
			<?	
				while ($multiplo_ar = sqlsrv_fetch_array($sql_multiplo))
				{
					
					$multiplo_forma = $multiplo_ar['PM_FORMA'];

					if($multiplo_forma == 6)
					{
						$cartao_pos = $multiplo_ar['PM_VALOR'];
					}
					else if ($multiplo_forma==2)
					{
						$cheque = $multiplo_ar['PM_VALOR'];
					}
					else if ($multiplo_forma==4)
					{
						$deposito += $multiplo_ar['PM_VALOR'];
					}
					else if ($multiplo_forma==3)
					{
						$dinheiro += $multiplo_ar['PM_VALOR'];
					}
					else
					{
						$outros += $multiplo_ar['PM_VALOR'];
					}
				}
					$multiplo_pago = ($multiplo_ar['PM_PAGO'] == 1) ? 'Pago' : 'Não pago';

			?>
			<tr>

				<td colspan="2"><? echo $multiplo_valor = number_format($cartao_pos, 2, ',','.');?></td>
				<td colspan="2"><? echo $multiplo_valor = number_format($cheque, 2, ',','.');?></td>
				<td colspan="2"><? echo $multiplo_valor = number_format($deposito, 2, ',','.');?></td>
				<td colspan="2"><? echo $multiplo_valor = number_format($dinheiro, 2, ',','.');?></td>
				<td colspan="2"><? echo $multiplo_valor = number_format($outros, 2, ',','.');?></td>
				
			</tr>
				
			<? endif ?>



			<tr>
				<!-- <th colspan="4">Local de Retirada</th>
				<th colspan="2">Data / Período</th> -->
				<th colspan="10">Camisas: (Qtde) / Tamanho</th>
			</tr>
			<tr>
				<!-- <td colspan="4"><? if(!$loja_delivery) { echo $loja_retirada; } ?></td>
				<td colspan="2"><? if(!$loja_delivery) { echo $loja_data_retirada.' / '.$loja_periodo; } ?></td> -->
				<td colspan="10">
				<?

				//Numero de camisas
				$sql_camisas = sqlsrv_query($conexao, "
					SELECT COUNT(CA_COD) AS TOTAL, 
					SUM(CASE WHEN CA_TAMANHO='P' THEN 1 ELSE 0 END) AS P,
					SUM(CASE WHEN CA_TAMANHO='M' THEN 1 ELSE 0 END) AS M,
					SUM(CASE WHEN CA_TAMANHO='G' THEN 1 ELSE 0 END) AS G,
					SUM(CASE WHEN CA_TAMANHO='GG' THEN 1 ELSE 0 END) AS GG,
					SUM(CASE WHEN CA_TAMANHO='EXG' THEN 1 ELSE 0 END) AS EXG
					FROM loja_camisas WHERE CA_COMPRA='$loja_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);

				if(sqlsrv_num_rows($sql_camisas) > 0){

					$camisas_ar = array();
					$camisas = sqlsrv_fetch_array($sql_camisas);

					$camisas_total = $camisas['TOTAL'];
					$camisas_total_tamanho['P'] = $camisas['P'];
					$camisas_total_tamanho['M'] = $camisas['M'];
					$camisas_total_tamanho['G'] = $camisas['G'];
					$camisas_total_tamanho['GG'] = $camisas['GG'];
					$camisas_total_tamanho['EXG'] = $camisas['EXG'];

					$icamisas = 0;
					foreach ($camisas_total_tamanho as $key => $camisas_total_qtde) {
						if($camisas_total_qtde > 0) {
							array_push($camisas_ar, '('.$camisas_total_qtde.') '.$key);
						}
					}
					echo implode(' / ', $camisas_ar);
				}


				?>&nbsp;
				</td>
			</tr>
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

			?>
			<tr>
				<th colspan="4">Endereço</th>
				<th colspan="2">Ponto de Referência</th>
				<th colspan="2">A/C</th>
				<th colspan="1">Data</th>
				<th colspan="1">Horário</th>
			</tr>
			<tr>
				<td colspan="4"><? if($loja_delivery) { echo $loja_endereco.', '.$loja_numero; if (!empty($loja_complemento)){ echo '- '.$loja_complemento; } echo ' - '; if(!empty($loja_cep)) { echo 'CEP: '. $loja_cep.' - '; } echo $loja_bairro; } else { echo '&nbsp;'; } ?></td>
				<td colspan="2"><? if($loja_delivery) { echo $loja_referencia; } ?></td>
				<td colspan="2"><? if($loja_delivery) { echo $loja_cuidados.' - '.$loja_celular; } ?></td>
				<td colspan="1"><? if($loja_delivery) { echo $loja_data_para_entrega; } ?></td>
				<td colspan="1"><? if($loja_delivery) { echo $loja_periodo; } ?></td>
			</tr>
			<tr>
				<th>Pendências</th>
				<td colspan="9">
					<?

					$pendencias_ar = array();
					
					//Buscar pendencias
					$sql_pendencias = sqlsrv_query($conexao, "SELECT p.PE_NOME FROM pendencias p, loja_pendencias lp WHERE lp.LP_COMPRA='$loja_cod' AND lp.LP_PENDENCIA=p.PE_COD AND lp.LP_BLOCK=0 AND lp.D_E_L_E_T_=0 AND p.PE_BLOCK=0 AND p.D_E_L_E_T_=0", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_pendencias) > 0) {
						while ($pendencias = sqlsrv_fetch_array($sql_pendencias)) {
							array_push($pendencias_ar, utf8_encode($pendencias['PE_NOME']));
						}
						echo implode(' / ', $pendencias_ar);
					}

					?>
				</td>
			</tr>
		</table>
	</section>
	<?
	}
	?>	
	</section>

	<input type="hidden" id="base-site" value="<? echo SITE; ?>" />
</body>
</html>
<?

//-----------------------------------------------------------------//

// include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>