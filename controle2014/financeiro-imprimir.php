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

// $vendedor = ($_SESSION['us-grupo'] == 'VIN' && $_SESSION['us-cod'] != 4309) ? true : false;
$usuario = (int) $_SESSION['us-cod'];

// Se o usuário for vendedor interno, ver apenas as suas vendas
// if($vendedor) $search_vendedor = " AND l.LO_VENDEDOR='$usuario' ";

//-----------------------------------------------------------------//
//clearsale

require __DIR__.'/ClearSale/vendor/autoload.php';

use ClearSale\ClearSaleAnalysis;
use ClearSale\Environment\Sandbox;
use ClearSale\XmlEntity\Response\OrderReturn;


$entityCode = CLEARSALE_ENTITY_CODE;
$environment = new Sandbox($entityCode);

//-----------------------------------------------------------------//

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA, CONVERT(CHAR, l.LO_CLI_DATA_ENTREGA, 103) AS DATA_PARA_ENTREGA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod' $search_vendedor", $conexao_params, $conexao_options);

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
<body <? if ($entrega){ echo 'class="entrega"'; } ?>>
	<section id="conteudo">

		<header id="topo">
			<img src="<? echo SITE; ?>img/logo-<? echo ($entrega) ? 'entrega' : 'email' ?>.png" class="logo" />


			Sede Centro – Av. Passos, 120 14º Centro - RJ<br />Cep: 20051-040 Tel/Fax: 21 3202-6000<!--<br />
			Filial Ipanema – Rua Visconde de Pirajá, 411 – 202 - RJ &ndash; Cep: 22410-003 Tel: 21 2267-2666<br />
			Filial Shopping Leblon – Av. Afrânio de Melo Franco, 290 Q37 - RJ &ndash; Tel: 21 2540-7010-->

			<? /*if ($entrega){ ?>
			Sede Centro – Av. Passos, 120 14º Centro - RJ &ndash; Cep: 20051-040 Tel/Fax: 21 3202-6000<br />
			Filial Ipanema – Rua Visconde de Pirajá, 411 – 202 - RJ &ndash; Cep: 22410-003 Tel: 21 2267-2666<br />
			Filial Shopping Leblon – Av. Afrânio de Melo Franco, 290 Q37 - RJ &ndash; Tel: 21 2540-7010
			<? } else { ?>
			Sede Centro – Av. Passos, 120 14º Centro - RJ<br />
			Cep: 20051-040 Tel/Fax: 21 3202-6000<br />
			Filial Ipanema – Rua Visconde de Pirajá, 411 – 202 - RJ<br />
			Cep: 22410-003 Tel: 21 2267-2666<br />
			Filial Shopping Leblon – Av. Afrânio de Melo Franco, 290 Q37 - RJ<br />
			Tel: 21 2540-7010
			<? }*/ ?>
		</header>
	<?
	if(sqlsrv_num_rows($sql_loja) > 0) {

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_cod = $loja['LO_COD'];
		$loja_cliente = $loja['LO_CLIENTE'];
		$loja_parceiro = $loja['LO_PARCEIRO'];
		$loja_vendedor = $loja['LO_VENDEDOR'];
		$loja_forma = $loja['LO_FORMA_PAGAMENTO'];
		$loja_diferenca_dias = (5 - $loja['DIFERENCA']);
		$loja_status_transacao = $loja['LO_STATUS_TRANSACAO'];
		$loja_pago = (bool) $loja['LO_PAGO'];
		$loja_delivery = (bool) $loja['LO_DELIVERY'];
		if(!$loja_delivery) $loja_retirada = $loja['LO_RETIRADA'];
		if(!$loja_delivery) $loja_data_retirada = utf8_encode($loja['DATA_PARA_ENTREGA']);
		$loja_periodo = utf8_encode($loja['LO_CLI_PERIODO']);
		$loja_concierge = $loja['LO_CONCIERGE'];

		$cartao_credito = ($loja_forma == 1) ? true : false;
		$faturado = ($loja_forma == 7) ? true : false;
		$reserva = ($loja_forma == 5) ? true : false;

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, DDI, DDD, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

		$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
		$loja_ddi = utf8_encode(trim($loja_cliente_ar['DDI']));
		$loja_ddd = utf8_encode(trim($loja_cliente_ar['DDD']));
		$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
		$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));

		$loja_valor_total = $loja['LO_VALOR_TOTAL'];
		$loja_valor_total_f = number_format($loja['LO_VALOR_TOTAL'], 2, ',','.');

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

  			$loja_antifraude_status = $loja['LO_ANTIFRAUDE_STATUS'];
  			$loja_antifraude_score = $loja['LO_ANTIFRAUDE_SCORE'];
  			// $loja_antifraude_score=$loja_antifraude_score*100;
  			$loja_antifraude_quiz_url = $loja['LO_ANTIFRAUDE_QUIZ_URL'];
			
			//clearsale
			if($cartao_credito/* && $loja_cielo_v2*/) {
				//iniciar variavel para analise da clearsale
				$clearSale = new ClearSaleAnalysis($environment);

				//variavel de retorno com os dados se ja tiver consultado antes e estiver no banco
				$orderReturn = new OrderReturn($loja_cod,$loja_antifraude_status,$loja_antifraude_score);

				if($clearSale->approvedReturn($orderReturn)){
					$antifraudeAprovado=true;
					//cor do score do pedido
					switch (true) {
						case $loja_antifraude_score<30:
							$aprovado_score_cor="verde";
							break;
						case $loja_antifraude_score<60:
							$aprovado_score_cor="laranja";
							break;

						case $loja_antifraude_score<90:
							$aprovado_score_cor="vermelho";
							break;
						case $loja_antifraude_score<100:
							$aprovado_score_cor="vermelho";
							break;
					} 
				}else if($clearSale->notApprovedReturn($orderReturn)){
					$antifraudeReprovado=true;
				} else if($clearSale->waitingForApprovalReturn($orderReturn)){
					$antifraudeAnalisando=true;
				}
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
		$sql_parceiro = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL,DDD,DDI FROM TGFPAR WHERE CODPARC='$loja_parceiro' AND VENDEDOR='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
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


		//Cartao
		// $loja_cielo_v2 = (bool) $loja['LO_CARTAO_V2'];
		if($cartao_credito) {
			$loja_cielo_v2_numero_cartao = utf8_encode($loja['LO_CARTAO_BANDEIRA']);
			$loja_cielo_v2_nome = utf8_encode($loja['LO_CARTAO_NOME']);
			$loja_cielo_v2_cpf = utf8_encode($loja['LO_CARTAO_CPF']);
			$loja_cielo_v2_email = utf8_encode($loja['LO_CARTAO_EMAIL']);
			$loja_cielo_v2_telefone = formatTelefone($loja['LO_CARTAO_TELEFONE']);
			$loja_cielo_v2_antifraude = $loja['LO_CARTAO_ANTIFRAUDE'];
			$loja_parcelas = $loja['LO_PARCELAS'];
			$loja_checkoutid = $loja['LO_CARTAO_CHECKOUTID'];

			//ENDERECO COBRANÇA
			$loja_cielo_endereco=array(
				"pais"=>utf8_encode($loja['LO_CLI_PAIS']),
				"estado"=>utf8_encode($loja['LO_CLI_ESTADO']),
				"cidade"=>utf8_encode($loja['LO_CLI_CIDADE']),
				"bairro"=>utf8_encode($loja['LO_CLI_BAIRRO']),
				"logradouro"=>utf8_encode($loja['LO_CLI_ENDERECO']),
				"numero"=>utf8_encode($loja['LO_CLI_NUMERO']),
				"complemento"=>utf8_encode($loja['LO_CLI_COMPLEMENTO'])
			);
		}

	?>
	<section id="detalhes-compra">
		<h1><? echo $evento_nome; ?></h1>
		<h2>Voucher nº <span><? echo $loja_cod; ?></span></h2>
	</section>

	<section id="informacoes-gerais" class="secao">
		<h2>Favor fornecer a / Please provide to</h2>

		<table>
			<tr>
				<th>Email</th>
				<td class="email"><? echo $loja_parceiro_email; ?></td>
				<td class="telefone"><? echo "+".$paises[$loja_parceiro_ddi]['ddi']." ".$loja_parceiro_ddd." ".$loja_parceiro_telefone; ?></td>
			</tr>
			<tr>
				<th>Agência/Hotel</th>
				<td colspan="2"><? echo $loja_parceiro_nome; ?></td>
			</tr>
			<? if(!$entrega && !empty($loja_vendedor_nome)) { ?>
			<tr>
				<th>Vendedor</th>
				<td colspan="2"><? echo ($loja_concierge > 0) ? $vendedor_externo_nome : $loja_vendedor_nome; ?></td>
			</tr>
			<? } ?>
			<tr>
				<th>Nome Paxs</th>
				<td colspan="2"><? echo $loja_nome; ?></td>
			</tr>
			<tr>
				<th>Email Cliente</th>
				<td class="email"><? echo $loja_email; ?></td>
				<td class="telefone"><? echo "+".$paises[$loja_ddi]['ddi']." ".$loja_ddd." ".$loja_telefone; ?> </td>
			</tr>
		</table>
	</section>

	<section id="informacoes-servicos" class="secao">
		<h2>Os seguintes serviços/The following services</h2>

		<?
		$sql_item = sqlsrv_query($conexao, " SELECT COUNT(LI_COD) AS QTDE, LI_VALOR, LI_INGRESSO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0' GROUP BY LI_INGRESSO, LI_VALOR", $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_item) > 0) {
			$i = 1;
			$item_count = 1;

			while ($item = sqlsrv_fetch_array($sql_item)) {

				$item_cod = $item['COD'];
				$item_qtde = $item['QTDE'];
				$item_ingresso = $item['LI_INGRESSO'];
				$item_valor =  number_format($item['LI_VALOR'], 2, ",", ".");
				$item_exclusividade = $item['EXCLUSIVIDADE'];
				$item_exclusividade_val = $item['EXCLUSIVIDADE_VAL'];

				//Informações adicionais do item
				$sql_info_item = sqlsrv_query($conexao, "
				SELECT v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_NOME, ed.ED_DATA, CONVERT(VARCHAR, ed.ED_DATA, 103) AS DIA, DATEPART(WEEKDAY, ed.ED_DATA) AS SEMANA, tp.TI_NOME 
				FROM vendas v, eventos_setores es, eventos_dias ed, tipos tp 
				WHERE v.VE_COD='$item_ingresso' AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD", $conexao_params, $conexao_options);

				if(sqlsrv_num_rows($sql_info_item) > 0) {
					$info_item = sqlsrv_fetch_array($sql_info_item);
				
					$item_setor = utf8_encode($info_item['ES_NOME']);
					$item_dia = utf8_encode($info_item['ED_NOME']);
					$item_data = utf8_encode($info_item['DIA']);
					$item_semana = $semana[($info_item['SEMANA']-1)];
					$item_tipo = utf8_encode($info_item['TI_NOME']);
					
					$item_fila = utf8_encode($info_item['VE_FILA']);
					$item_vaga = utf8_encode($info_item['VE_VAGAS']);
					$item_tipo_especifico = utf8_encode($info_item['VE_TIPO_ESPECIFICO']);

					$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;
				}

				// loja_itens_adicionais
				if($item_fechado) $item_qtde = $item_qtde / $item_vaga;
			?>
			<table>
				<tr>
					<th colspan="2" class="full"><? echo $evento_nome; ?></th>
				</tr>
				<tr>
					<th>Quantidade</th>
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
				</tr>
				<tr>
					<th>Tipo</th>
					<td>
					<?
						echo $item_tipo;
						if(!empty($item_tipo_especifico)) { echo " ".$item_tipo_especifico; }
						if($item_fechado) { echo " (".$item_vaga." vagas)"; }
					?>
					</td>
				</tr>
				<tr>
					<th>Setor</th>
					<td>
						<?
						echo $item_setor;
						if(!empty($item_fila)) { echo " ".$item_fila; }
						?>
					</td>
				</tr>
				<tr>
					<th>Data</th>
					<td>
						<? echo $item_dia; ?> dia -
						<? echo $item_semana; ?> -
						<? echo $item_data; ?>
					</td>
				</tr>
				<tr>
					<th>Forma de Pagamento</th>
					<td>
						<? echo $loja_forma_pagamento; ?> 
						<?
						if($faturado && ($loja_parcelas > 1)) { echo " - Parcelado em ".$loja_parcelas."x"; }
						if($cartao_credito && ($loja_parcelas > 1)) { echo " - Parcelado em ".$loja_parcelas."x"; }
						if($cartao_credito) {

							echo "<br />Número do cartão: ".$loja_cielo_v2_numero_cartao."<br />
							Nome do cliente: ".$loja_cielo_v2_nome."<br />
							CPF/CNPJ/Passaporte: ".$loja_cielo_v2_cpf."<br />";
							// echo empty($loja_email)?"":"E-mail: ".$loja_email."<br />";
							echo empty($loja_telefone)?"":"Telefone: +".$paises[$loja_ddi]['ddi']." ".$loja_ddd." ".$loja_telefone."<br />";

							if(!empty($loja_cielo_endereco)){
								echo "Endereço de cobrança: ";
								echo empty($loja_cielo_endereco['complemento'])?"":$loja_cielo_endereco['complemento'].", ";
								echo $loja_cielo_endereco['logradouro'].", ".$loja_cielo_endereco['numero'].", ";
								echo empty($loja_cielo_endereco['bairro'])?"":$loja_cielo_endereco['bairro'].", ";
								echo $loja_cielo_endereco['cidade']." - ".$loja_cielo_endereco['estado']." - ".$loja_cielo_endereco['pais'];
							}

							if ($entrega) 
							{
								switch (true) {
								case $loja_antifraude_score<30:
									$antifraude_texto = "(Baixo)";
									break;
								case $loja_antifraude_score<60:
									$antifraude_texto = "(Médio)";
									break;

								case $loja_antifraude_score<90:
									$antifraude_texto = "(Alto)";
									break;

								case $loja_antifraude_score<100:
									$antifraude_texto = "(Crítico)";
									break;
								
								default:
									$antifraude_texto = "(Desconhecido)";
									break;
								}

								if($antifraudeAprovado){
									echo '<br/>Analisado<br/>';
									echo $antifraude_texto;
								}else if($antifraudeReprovado){
									echo '<br/>Reprovado<br/>';
									echo $antifraude_texto;
								} else if($antifraudeAnalisando){
									echo '<br/>Analisando<br/>';
								} else {
									echo '<br/>Não Analisado<br/>';
								}
							}
						}
						?>
					</td>
				</tr>
				<?

					//Buscar comentarios
					$sql_item_comentario = sqlsrv_query($conexao, "SELECT TOP 1 LC_COMENTARIO FROM loja_comentarios WHERE LC_ITEM='$item_cod'", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_item_comentario) > 0){


						$ar_item_comentario = sqlsrv_fetch_array($sql_item_comentario);
						$item_comentario = $ar_item_comentario['LC_COMENTARIO'];

						if(strlen($item_comentario) > 2) {

				?>
				<tr>
					<th>Comentários</th>
					<td>
						<?
						echo utf8_encode(nl2br($item_comentario));
						?>
					</td>
				</tr>
				<?
						}

					}

				?>
				<tr>
					<th>Observações</th>
					<td>
						<?
						//Exclusividade
						if($item_exclusividade) {
						?>
						Exclusividade - <? echo $item_tipo; ?> <? if(!empty($item_exclusividade_val)) { ?> na fila <? echo utf8_encode($item_exclusividade_val); } ?><br />
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
								
								if ($item_fechado && ($vendas_adicionais_nome_exibicao == 'transfer')) echo $vendas_adicionais_qtde." ";
								echo $vendas_adicionais_label."<br />";
							}
						}


						?>
						
						<div class="atencao">
							<p><strong>Atenção!</strong></p>
							<p>Para os clientes que efetuaram a compra pelo nosso site, via cartão crédito, a conferência da titularidade será realizada no credenciamento. O titular do cadastro, obrigatoriamente, deverá ser o titular do cartão de crédito, sendo assim, terá que apresentá-los pessoalmente para retirada dos ingressos.</p>
							<p>Caso o cliente não apresente o cartão de crédito original e o documento com foto, por medida de segurança, os ingressos não serão entregues e a compra será estornada.</p>
						</div>
					</td>
				</tr>
			</table>
			<?


			}		

		}
		?>
	</section>

	<? if(!$loja_pago) { ?>
	<section class="secao pendencias">
		<table>
			<tbody>
				<tr>
					<th>Pendências</th>
					<td>Pagamento R$ <? echo number_format($loja_valor_pendente, 2, ",", "."); ?></td>
				</tr>
			</tbody>
		</table>
	</section>
	<? } ?>

	<? /*<section id="informacoes-assinatura" class="secao">
		<table>
			<thead>
				<tr>
					<th>Confirmado/Confirmed by</th>
					<th>Reservado Por/Reserved by</th>
					<th>Assinatura Autorizada/Signature</th>
					<th class="data">Data Emissão</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td><? echo date('d/m/Y'); ?></td>
				</tr>
			</tbody>
		</table>
	</section>*/ ?>

	<?

	$sql_exist_camisa = sqlsrv_query($conexao, "SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO NOT IN (SELECT v.VE_COD FROM vendas v LEFT JOIN eventos_dias d ON d.ED_COD=v.VE_DIA WHERE ((v.VE_TIPO=4 AND d.ED_DATA IN ('2015-02-13', '2015-02-14', '2016-02-05', '2016-02-06')) OR (v.VE_TIPO IN (1,2))) AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0) AND D_E_L_E_T_='$loja_cancelado_int'", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_exist_camisa) > 0) {

		//Numero de camisas
		$sql_camisas = sqlsrv_query($conexao, "
			SELECT COUNT(CA_COD) AS TOTAL, 
			SUM(CASE WHEN CA_TAMANHO='P' THEN 1 ELSE 0 END) AS P,
			SUM(CASE WHEN CA_TAMANHO='M' THEN 1 ELSE 0 END) AS M,
			SUM(CASE WHEN CA_TAMANHO='G' THEN 1 ELSE 0 END) AS G,
			SUM(CASE WHEN CA_TAMANHO='GG' THEN 1 ELSE 0 END) AS GG,
			SUM(CASE WHEN CA_TAMANHO='EXG' THEN 1 ELSE 0 END) AS EXG
			FROM loja_camisas WHERE CA_COMPRA='$loja_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);

		$n_camisas = sqlsrv_num_rows($sql_camisas);

		if($n_camisas > 0) {

			$camisas = sqlsrv_fetch_array($sql_camisas);

			$camisas_total = $camisas['TOTAL'];
			$camisas_total_tamanho['P'] = $camisas['P'];
			$camisas_total_tamanho['M'] = $camisas['M'];
			$camisas_total_tamanho['G'] = $camisas['G'];
			$camisas_total_tamanho['GG'] = $camisas['GG'];
			$camisas_total_tamanho['EXG'] = $camisas['EXG'];
		}
		
	}


	//-----------------------------------------------------------------//

	//Transporte
	$cods_transfer_itens = "''";

	//Selecionar código do transfer
	$sql_transfer = sqlsrv_query($conexao, "SELECT VA_COD FROM vendas_adicionais WHERE (VA_NOME_EXIBICAO='transfer' OR VA_NOME_EXIBICAO='transferinout') AND VA_BLOCK='0' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_transfer) > 0) {
		$transfer_cod = array();
		while($ar_transfer = sqlsrv_fetch_array($sql_transfer)) array_push($transfer_cod, $ar_transfer['VA_COD']);
		$transfer_cod = implode(",", $transfer_cod);
		
		//Selecionar somente os que tem transfer
		$sql_cods_transfer = sqlsrv_query($conexao, "SELECT LIA_ITEM FROM loja_itens_adicionais WHERE LIA_COMPRA='$loja_cod' AND LIA_ADICIONAL IN ($transfer_cod) AND LIA_BLOCK='0' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cods_transfer) > 0) {
			$ar_cods_transfer = array();
			while($cods_transfer = sqlsrv_fetch_array($sql_cods_transfer)) array_push($ar_cods_transfer, $cods_transfer['LIA_ITEM']);
			$cods_transfer_itens = implode(",", $ar_cods_transfer);
		}

	}

	$sql_itens = sqlsrv_query($conexao, "SELECT li.*, t.TI_NOME, v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_NOME FROM loja_itens li, vendas v, eventos_setores es, eventos_dias ed, tipos t WHERE li.LI_COMPRA='$loja_cod' AND li.LI_COD IN ($cods_transfer_itens) AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND t.TI_COD=v.VE_TIPO AND li.D_E_L_E_T_='0' ORDER BY LI_COD ASC", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_itens) > 0) {
		$iagendamento = 0;
		while ($item = sqlsrv_fetch_array($sql_itens)) {
			$item_cod = $item['LI_COD'];
			$item_id = $item['LI_ID'];
			$item_nome = utf8_encode($item['LI_NOME']);
			$item_dia = utf8_encode($item['ED_NOME']);
			$item_setor = $item['ES_NOME'];
			$item_tipo = utf8_encode($item['TI_NOME']);

			$item_fila = utf8_encode($item['VE_FILA']);
			$item_vaga = utf8_encode($item['VE_VAGAS']);
			$item_tipo_especifico = utf8_encode($item['VE_TIPO_ESPECIFICO']);

			$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;

			//if(is_numeric($item_setor) && ($item_setor%2 == 0)) { $tipo_roteiro = 1; } elseif(is_numeric($item_setor) && ($item_setor%2 != 0)) { $tipo_roteiro = 2; } elseif(!is_numeric($item_setor)) { $tipo_roteiro = 3; }

			//busca agendamento do item
			$sql_agendamento = sqlsrv_query($conexao, "SELECT ta.*, th.*, tr.*, ro.*, SUBSTRING(CONVERT(CHAR, th.TH_HORA, 8), 1, 5) AS HORA  FROM transportes_agendamento ta, transportes_horarios th, transportes tr, roteiros ro WHERE ta.TA_ITEM='$item_cod' AND ta.TA_HORARIO=th.TH_COD AND th.TH_TRANSPORTE=tr.TR_COD AND tr.TR_ROTEIRO=ro.RO_COD AND ta.D_E_L_E_T_='0'", $conexao_params, $conexao_options);
			$n_agendamento = sqlsrv_num_rows($sql_agendamento);
			if($n_agendamento > 0) {

				$agendamento = sqlsrv_fetch_array($sql_agendamento);
				$agendamento_cod = $agendamento['TA_COD'];
				$agendamento_roteiro = utf8_encode($agendamento['RO_NOME']);
				$agendamento_horario = utf8_encode($agendamento['HORA']);
				$agendamento_transporte = utf8_encode($agendamento['TR_NOME']);

				$loja_agentamento[$iagendamento]['nome'] = $item_nome;
				$loja_agentamento[$iagendamento]['dia'] = $item_dia;
				$loja_agentamento[$iagendamento]['setor'] = $item_setor;
				$loja_agentamento[$iagendamento]['tipo'] = $item_tipo;
				$loja_agentamento[$iagendamento]['fila'] = $item_fila;
				$loja_agentamento[$iagendamento]['vaga'] = $item_vaga;
				$loja_agentamento[$iagendamento]['tipo_especifico'] = $item_tipo_especifico;
				$loja_agentamento[$iagendamento]['fechado'] = $item_fechado;
				$loja_agentamento[$iagendamento]['roteiro'] = $agendamento_roteiro;
				$loja_agentamento[$iagendamento]['horario'] = $agendamento_horario;
				$loja_agentamento[$iagendamento]['transporte'] = $agendamento_transporte;

				$iagendamento++;
			}
		}
	}

	if($entrega) {

	if(($n_camisas > 0) || ($n_agendamento > 0)){ 
	
	?>
	<section class="secao">
		<table>
			<tbody>

				<? if($n_agendamento > 0){ ?>
				<tr>
					<th>Roteiro do Transporte</th>
					<td>
					<?

					foreach ($loja_agentamento as $agendamento) {
						
						echo $agendamento['tipo'];
						if(!empty($agendamento['fila'])) { echo " ".$agendamento['fila']; }
						if(!empty($agendamento['tipo_especifico'])) { echo " ".$agendamento['tipo_especifico']; }
						if($agendamento['fechado']) { echo " (".$agendamento['vaga']." vagas)"; }
						
						echo ' - '.$agendamento['nome'].' - '.$agendamento[ 'roteiro'].' - '.$agendamento[ 'transporte'].' - '.$agendamento[ 'horario'].'<br />';
						
					}
					?>
					</td>
				</tr>
				<?
				}
				if($n_camisas > 0){
				?>
				<tr <? if($camisas_total == 0) { echo 'class= "pendencias"'; } ?>>
					<th>Camisas</th>
					<td>
					<?
		
					$icamisas = 0;
					foreach ($camisas_total_tamanho as $key => $camisas_total_qtde) {
						if($camisas_total_qtde > 0) {
							echo 'Tamanho: '.$key.' ('.$camisas_total_qtde.')<br />';
							$icamisas++;
						}
					}
					
					?>
					</td>
				</tr>
				<? } ?>
			</tbody>
		</table>
	</section>
	<?
	}
	?>

	<section class="secao">
		<table>
			<tbody>
				<tr>
				<?

					if($loja_delivery) {

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
					<th>Endereço de entrega</th>
					<td>
						<? echo $loja_endereco; ?>, <? echo $loja_numero; ?> <? if (!empty($loja_complemento)){ echo '- '.$loja_complemento; } ?>
						 - <? if(!empty($loja_cep)) { ?>CEP: <? echo $loja_cep; ?> - <? } echo $loja_bairro; if(!empty($loja_referencia)) { ?> - Referência: <? echo $loja_referencia; } ?><br /><? echo $loja_data_para_entrega ?> - Período: <? echo $loja_periodo; ?> - A/C.: <? echo $loja_cuidados; ?> - <? echo $loja_celular; ?>
					</td>
					<? } else { ?>
					<th>Retirada</th>
					<td><? echo $loja_data_retirada; ?> - <? echo ucfirst($loja_retirada); ?> - Período: <? echo $loja_periodo; ?>
					</td>
					<? } ?>
				</tr>
			</tbody>
		</table>
	</section>

	<h3 class="voucher">Voucher nº <span><? echo $loja_cod; ?></span></h3>
	
	<section id="painel-pendencias" class="secao">

		<section class="wrap pendencias">
			<h2>Painel de pendências</h2>
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
						<li><span><? if($pendencias_check) { echo '<strong>&times;</strong>'; } ?></span><? echo $pendencias_nome; ?></li>
						<?
					}
				}

				?>
			</ul>
		</section>

	</section>
	<section id="pendencias-entrega" class="secao formulario">
		<table class="form">
			<tr>
				<td class="data">Data Entrega</td>
				<td>Endereço entrega</td>
				<td class="cuidados">A/C</td>
				<td class="celular">Celular</td>
			</tr>
		</table>
	</section>

	<section id="informacoes-entrega" class="secao formulario">
		<table class="form">
			<tr>
				<td class="data">Data</td>
				<td>Motoqueiro</td>
				<td class="hora">Saída</td>
				<td class="hora">Retorno</td>
			</tr>
		</table>

		<table class="form">
			<tr>
				<td>Recebido por</td>
				<td class="nome">Nome</td>
				<td class="data">Data</td>
				<td class="hora">Hora</td>
			</tr>		
		</table>

		<p class="assinatura">Afirmo que recebi do Folia Tropical o pacote de carnaval citado acima</p>

	</section>

	<? } ?>
	
	<? if(!$entrega) { ?>
	<section class="informacoes-retirada float secao">
		<!--<h3>Retirada ingressos + Kit Folião | Folia Tropical (utilização da camisa passaporte é obrigatória):</h3>
		<p>Candybox (24/02), sábado de acesso (25/02) Folia Tropical (26/02 – 27/02 – 04/03) - Abertura do camarote ocorrerá as 21:00 e o inicio dos desfiles as 22:00.</p>
		<p><strong>Credenciamento:</strong> Clube Monte Líbano - Av. Borges de Medeiros, 701 - Leblon</p>
		<p><strong>Datas e Horários:</strong></p>
		<p>22/02 - 23/02 - 03/03: das 14:00 às 22:00</p>
		<p>24/02 - 25/02 - 26/02 - 27/02 - 04/03: das 14:00 às 23:00</p>-->


		<!-- <h3>Retirada ingressos + Kit Folião | Folia Tropical (utilização da camisa passaporte é obrigatória):</h3>
		<p>Candybox (09/02) • Sábado de Acesso (10/02) • Folia tropical (11/02 – 12/02 – 17/02) - Abertura do camarote ocorrerá as 21:00 e o inicio dos desfiles as 22:00.</p>
		<p><strong>Credenciamento:</strong> Zona Sul</p>
		<p><strong>Datas e Horários:</strong></p>
		<p>07/02 - 08/02 - 16/02: das 14:00 às 22:00</p>
		<p>09/02 - 10/02 - 11/02 - 12/02 - 17/02: das 14:00 às 23:00</p> -->

		<!-- <h3>Retirada ingressos + Kit Folião | Folia Tropical (utilização da camisa passaporte é obrigatória):</h3>
		<p>Candybox (01/03), sábado de acesso (02/03) Folia Tropical (03/03 – 04/03 – 09/03) - Abertura dos camarotes ocorrerá as 21:00 e o início dos desfiles às 22:00</p>
		<p><strong>Credenciamento:</strong> CLUBE MONTE LÍBANO - (AV. BORGES DE MEDEIROS, 701 -LEBLON) ENTRADA PELO JARDIM ALAH</p>
		<p><strong>Datas e Horários:</strong></p>
		<p>27/02 - 28/02 - 08/03 das 14:00 às 22:00</p>
		<p>01/03 - 02/03 - 03/02 - 04/03 - 09/03: das 14:00 às 23:00</p> -->
		
		<h3>Retirada ingressos + Kit Folião | Folia Tropical (utilização da camisa passaporte é obrigatória):</h3>
		<p><strong>GRUPO DE ACESSO:</strong><br/>
		01/03 – CANDYBOX – SEXTA | 02/03 – SÁBADO<br/>
		ABERTURA DOS CAMAROTES OCORRERÁ ÀS 21h30min E O INÍCIO DOS DESFILES ÀS 22h30min</p>

		<p><strong>GRUPO ESPECIAL:</strong><br/>
		03/03 – DOMINGO | 04/03 – SEGUNDA | 09/03 – CAMPEÃS<br/>
		ABERTURA DOS CAMAROTES OCORRERÁ ÀS 20h15min E O INÍCIO DOS DESFILES ÀS 21h15min</p>

		<p><strong>CREDENCIAMENTO:</strong> CLUBE MONTE LÍBANO – (AV. BORGES DE MEDEREIROS, 701– LEBLON) ENTRADA PELO JARDIM ALAH.<br/>
		<strong>DATAS E HORÁRIOS:</strong><br/>
		27/02 – 28/02 – 08/03 das 14h00 ÀS 22h00<br/>
		01/03 – 02/03 -03/02 – 04/03 – 09/03: das 14h00 ÀS 23h00</p>

	</section>




	<section class="informacoes-retirada float last secao">
		<!--<h3>Retirada dos ingressos de Arquibancadas, Frisas e Camarotes:</h3>
		<p><strong>Credenciamento:</strong> Clube Monte Líbano - Av. Borges de Medeiros, 701 - Leblon</p>
		<p><strong>Datas e Horários:</strong></p>
		<p>22/02 - 23/02 - 03/03: das 14:00 às 22:00</p>
		<p>24/02 - 25/02 - 26/02 - 27/02 - 04/03: das 14:00 às 23:00</p>-->

		<h3>Retirada dos ingressos de Arquibancadas, Frisas e Camarotes:</h3>
		<p><strong>Credenciamento:</strong> CLUBE MONTE LÍBANO - (AV. BORGES DE MEDEIROS, 701 -LEBLON) ENTRADA PELO JARDIM ALAH</p>
		<p><strong>Datas e Horários:</strong></p>
		<p>27/02 - 28/02 - 08/03 das 14:00 às 22:00</p>
		<p>01/03 - 02/03 - 03/02 - 04/03 - 09/03: das 14:00 às 23:00</p>

	</section>

	<div class="clear"></div>


	
	<!-- <section class="informacoes-retirada secao big">
		<h3>Atenção!</h3>
		<p>Para os clientes que efetuaram a compra pelo nosso site, via cartão crédito, a conferência da titularidade será realizada no credenciamento. O titular do cadastro, obrigatoriamente, deverá ser o titular do cartão de crédito, sendo assim, terá que apresentá-los pessoalmente para retirada dos ingressos.</p>
		<p>Caso o cliente não apresente o cartão de crédito original e o documento com foto, por medida de segurança, os ingressos não serão entregues e a compra será estornada.</p>
	</section> -->

	<section class="informacoes-retirada secao big side">
		<!-- <h3>Observações para a retirada do ingresso</h3>
		<p>- O Cliente deverá levar identidade, o voucher impresso para fazer a troca pelos ingressos e Kit Folião.</p>
		<p>- ATENÇÃO! Para quem efetuou a compra pelo cartão crédito, a conferência da titularidade será realizada na apresentação do voucher.</p>
		<p>O titular do cadastro, obrigatoriamente, deve ser o titular do cartão de crédito, sendo assim, terá que apresentá-los pararetirada dos ingressos.</p> -->	

		<h3>Observações para a retirada do ingresso:</h3>
		<p>O Cliente deverá levar:</p>
		<p>• Identidade;</p>
		<p>• Cartão de crédito (caso seja essa a forma de pagamento) ;</p>
		<p>• Voucher impresso para fazer a troca pelos ingressos e Kit Folião.</p>

	</section>
	
	<section id="informacoes-cancelamento" class="secao big side">
		<!-- <h3>Cancelamento - Não previsto, portanto não haverá restituição da importância paga total ou parcial.</h3>
		<p>A PACÍFICA TURISMO se reserva o direito de fornecer qualquer item do pacote com valor superior ao comprado sem nenhum custo extra ao cliente.</p>
		<p>A PACÍFICA TURISMO não será responsável por adiamento do evento, no horário do evento, mudança de local e condições do tempo.</p>
		<h3>Todas as compras dos pacotes e consequentemente dos itens incluídos no pacote são finais</h3> -->

		<h3>Cancelamento - de acordo com decreto federal nº 7.962/13</h3>
		<p>O FOLIA TROPICAL se reserva o direito de fornecer qualquer item do pacote com valor superior ao comprado sem nenhum custo extra ao cliente. (up grade) </p>
		<p>O FOLIA TROPICAL não será responsável por adiamento do evento, na mudança horário do evento, mudança de local e condições do tempo.</p>

	</section>

	<div class="clear"></div>

	<footer>
		<p>www.foliatropical.com.br</p>
		<p>ABAV: 417 &nbsp;&ndash;&nbsp; EMBRATUR: 0459000417 &nbsp;&ndash;&nbsp; IATA: 57-628196 &nbsp;&ndash;&nbsp; SISBACEN: 90824/0001</p>
		<p>CNPJ: 31.235.617/0001-10 &nbsp;&ndash;&nbsp; INSC. MUN. 207.412-5</p>
	</footer>
	<? } ?>
	
	<? if($entrega) { ?>
	<section id="informacoes-entrega-destaque" class="secao formulario">
		<table class="form">
			<tr>
				<td class="data">Data</td>
				<td>Motoqueiro</td>
				<td class="hora">Saída</td>
				<td class="hora">Retorno</td>
			</tr>
		</table>

		<table>
			<thead>
				<tr>
					<th colspan="3" class="voucher">Voucher nº <? echo $loja_cod; ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th>Email</th>
					<td class="email"><? echo $loja_parceiro_email; ?></td>
					<td class="telefone"><? echo formatTelefone($loja_parceiro_telefone); ?></td>
				</tr>
				<tr>
					<th>Agência/Hotel</th>
					<td colspan="2"><? echo $loja_parceiro_nome; ?></td>
				</tr>
				<tr>
					<th>Nome Paxs</th>
					<td colspan="2"><? echo $loja_nome; ?></td>
				</tr>
				<tr>
					<th>Email Cliente</th>
					<td class="email"><? echo $loja_email; ?></td>
					<td class="telefone"><? echo formatTelefone($loja_telefone); ?></td>
				</tr>
			</tbody>
		</table>

	</section>

	<? if(!$loja_pago) { ?>
	<section class="secao pendencias">
		<table>
			<tbody>
				<tr>
					<th>Pendências</th>
					<td>Pagamento R$ <? echo number_format($loja_valor_pendente, 2, ",", "."); ?></td>
				</tr>
			</tbody>
		</table>
	</section>
	<? } ?>

	<? } // entrega

	}

	/*if(!$entrega) {
	?>
		<header id="topo-procuracao">
			<img src="<? echo SITE; ?>img/logo-<? echo ($entrega) ? 'entrega' : 'email' ?>.png" class="logo" />

			Sede Centro – Av. Passos, 120 14º Centro - RJ<br />Cep: 20051-040 Tel/Fax: 21 3202-6000<!--<br />
			Filial Ipanema – Rua Visconde de Pirajá, 411 – 202 - RJ &ndash; Cep: 22410-003 Tel: 21 2267-2666<br />
			Filial Shopping Leblon – Av. Afrânio de Melo Franco, 290 Q37 - RJ &ndash; Tel: 21 2540-7010-->

		</header>

		<article id="procuracao">
			<h3>Procuração</h3>
			<h2>AUTORIZAÇÃO PARA RETIRADA DE INGRESSOS POR TERCEIROS</h2>

			<p>
				Pelo presente instrumento particular de procuração, _____________________________________________________________________________,
				inscrito (a) no CPF son n° _______________________________, nomeia e constitui seu (sua) bastante
				procurador (a), _____________________________________________________________________________ inscrito (a) no CPF sob
				n° _______________________________, para o fim específico de retirar, aceitar, trocar e/ou 
				promover todos os demais atos pertinentes ao recebimento do(s) ingresso(s) cujo número 
				do pedido/voucher __________________, no valor total de R$ ________________________, adquirido(s) pelo Outorgante 
				no site da FOLIATROPICAL (www.foliatropical.com.br), nos termos procedimento, prazos e locais convencionados.
			</p>
			
			<p>O presente instrumento de procuração encontra-se limitado aos poderes específicos para a causa indicada, tendo prazo determinado até a data de evento relacionado e serve como comprovação para eventuais contestações junto a credenciadoras dos cartões de crédito de que os ingressos foram comprados e utilizados.</p>
			<p>* É necessária a cópia de um documento de identificação com foto da pessoa que comprou os ingressos, assim como também é necessário entregar cópia (frente) do cartão que foi efetuada a compra.</p>

			<p class="assinatura">______________________________________________,____________,________________________________________________<br /> Assinatura e CPF de quem comprou os Ingressos.</p>
			<p class="assinatura">______________________________________________,____________,________________________________________________<br /> Assinatura e CPF de quem retirou os Ingressos.</p>

			<h4>Caso tenha alguma dúvida sobre o seu pedido, entre em contato conosco  através do e-mail: sac@grupopacifica.com.br ou acesse www.foliatropical.com.br</h4>

		</article>


		<footer>
			<p>www.foliatropical.com.br</p>
			<p>ABAV: 417 &nbsp;&ndash;&nbsp; EMBRATUR: 0459000417 &nbsp;&ndash;&nbsp; IATA: 57-628196 &nbsp;&ndash;&nbsp; SISBACEN: 90824/0001</p>
			<p>CNPJ: 31.235.617/0001-10 &nbsp;&ndash;&nbsp; INSC. MUN. 207.412-5</p>
		</footer>

	<?
	}*/
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