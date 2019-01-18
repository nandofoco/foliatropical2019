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

$vendedor = ($_SESSION['us-grupo'] == 'VIN') ? true : false;
$usuario = (int) $_SESSION['us-cod'];

// Se o usuário for vendedor interno, ver apenas as suas vendas
if($vendedor) $search_vendedor = " AND l.LO_VENDEDOR='$usuario' ";

//-----------------------------------------------------------------//

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA, CONVERT(CHAR, l.LO_CLI_DATA_ENTREGA, 103) AS DATA_PARA_ENTREGA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod' $search_vendedor", $conexao_params, $conexao_options);

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
<body class="entrega etiqueta">
	<section id="conteudo">
	<?
	if(sqlsrv_num_rows($sql_loja) > 0) {

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_cod = $loja['LO_COD'];
		$loja_cliente = $loja['LO_CLIENTE'];
		$loja_parceiro = $loja['LO_PARCEIRO'];
		$loja_forma = $loja['LO_FORMA_PAGAMENTO'];
		$loja_diferenca_dias = (5 - $loja['DIFERENCA']);
		$loja_status_transacao = $loja['LO_STATUS_TRANSACAO'];
		$loja_pago = (bool) $loja['LO_PAGO'];
		$loja_delivery = (bool) $loja['LO_DELIVERY'];

		$loja_endereco = utf8_encode($loja['LO_CLI_ENDERECO']);
		$loja_numero = utf8_encode($loja['LO_CLI_NUMERO']);
		$loja_complemento = utf8_encode($loja['LO_CLI_COMPLEMENTO']);
		$loja_bairro = utf8_encode($loja['LO_CLI_BAIRRO']);
		$loja_cidade = utf8_encode($loja['LO_CLI_CIDADE']);
		$loja_estado = utf8_encode($loja['LO_CLI_ESTADO']);
		$loja_cep = utf8_encode($loja['LO_CLI_CEP']);

		$cartao_credito = ($loja_forma == 1) ? true : false;
		$faturado = ($loja_forma == 7) ? true : false;
		$reserva = ($loja_forma == 5) ? true : false;

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

		$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
		$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
		$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));

		$loja_valor_total = $loja['LO_VALOR_TOTAL'];
		$loja_valor_total_f = number_format($loja['LO_VALOR_TOTAL'], 2, ',','.');

		//Forma de pagamento
		// LO_FORMA_PAGAMENTO
		$sql_forma = $sql_cliente = sqlsrv_query($conexao, "SELECT TOP 1 FP_NOME FROM formas_pagamento WHERE FP_COD='$loja_forma'", $conexao_params, $conexao_options);
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


		//Buscar evento
		$sql_evento = sqlsrv_query($conexao, "SELECT TOP 1 EV_NOME FROM eventos WHERE EV_COD='$evento'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_evento) > 0) {
			$eventoar = sqlsrv_fetch_array($sql_evento);
			$evento_nome = utf8_encode($eventoar['EV_NOME']);
		}

		//Buscar informações do parceiro
		$sql_parceiro = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$loja_parceiro' AND VENDEDOR='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_parceiro) > 0) $loja_parceiro_ar = sqlsrv_fetch_array($sql_parceiro);

		$loja_parceiro_nome = utf8_encode(trim($loja_parceiro_ar['NOMEPARC']));
		$loja_parceiro_telefone = utf8_encode(trim($loja_parceiro_ar['TELEFONE']));
		$loja_parceiro_email = utf8_encode(trim($loja_parceiro_ar['EMAIL']));
	?>
	<section id="detalhes-compra">
		<h1><? echo $evento_nome; ?></h1>
		<h2>Voucher nº <? echo $loja_cod; ?></h2>
	</section>

	<section id="informacoes-gerais" class="secao">
		<table>
			<tr>
				<th>Agência/Hotel</th>
				<td><? echo $loja_parceiro_nome; ?></td>
				<td class="telefone"><? echo formatTelefone($loja_parceiro_telefone); ?></td>
			</tr>
			<tr>
				<th>Nome Paxs</th>
				<td><? echo $loja_nome; ?></td>
				<td class="telefone"><? echo formatTelefone($loja_telefone); ?></td>
			</tr>
		</table>
	</section>

	<section id="informacoes-servicos" class="secao">
		<h2>Ingressos</h2>
		<?
		$sql_item = sqlsrv_query($conexao, " SELECT COUNT(LI_COD) AS QTDE, LI_VALOR, LI_INGRESSO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUISIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUISIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0' GROUP BY LI_INGRESSO, LI_VALOR", $conexao_params, $conexao_options);

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
				<!-- <tr>
					<th colspan="4" class="full"><? echo $evento_nome; ?></th>
				</tr> -->
				<tr>
					<th>Informações</th>
					<td class="qtde">
						Qtde: 
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
						&ndash; Setor
						<?
						echo $item_setor;
						if(!empty($item_fila)) { echo " ".$item_fila; }
						?>
					</td>				
					<td class="data">
						<? echo $item_dia; ?> dia -
						<? echo $item_semana; ?> -
						<? echo $item_data; ?>
					</td>
				</tr>
				<? /*<tr>
					<th>Observações</th>
					<td colspan="3">
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
						$n_adicionais = sqlsrv_num_rows($sql_adicionais);
						if($n_adicionais > 0) {
							$iadicionais = 1;
							while ($vendas_adicionais = sqlsrv_fetch_array($sql_adicionais)) {
								$vendas_adicionais_label = utf8_encode($vendas_adicionais['VA_LABEL']);
								$vendas_adicionais_nome_exibicao = $vendas_adicionais['VA_NOME_EXIBICAO'];
								$vendas_adicionais_qtde = $vendas_adicionais['QTDE'];
								
								if ($item_fechado && ($vendas_adicionais_nome_exibicao == 'transfer')) echo $vendas_adicionais_qtde." ";
								// echo $vendas_adicionais_label."<br />";
								echo $vendas_adicionais_label;
								if($iadicionais < $n_adicionais) echo " &ndash; ";

								$iadicionais++;
							}
						}

						?>
					</td>
				</tr>*/ ?>
				<tr>
					<th>Observações</th>
					<td colspan="3">&nbsp;</td>
				</tr>
			</table>
			<?

			}		

		}

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

			$camisas = sqlsrv_fetch_array($sql_camisas);

			$camisas_total = $camisas['TOTAL'];
			$camisas_total_tamanho['P'] = $camisas['P'];
			$camisas_total_tamanho['M'] = $camisas['M'];
			$camisas_total_tamanho['G'] = $camisas['G'];
			$camisas_total_tamanho['GG'] = $camisas['GG'];
			$camisas_total_tamanho['EXG'] = $camisas['EXG'];

			$camisas_ar = array();
			foreach ($camisas_total_tamanho as $key => $camisas_total_qtde) {
				if($camisas_total_qtde > 0) array_push($camisas_ar, '('.$camisas_total_qtde.') '.$key);
			}
			
			if(count($camisas_ar) > 0) {
			?>
			<table>
				<tr>
					<th>Camisas</th>
					<td colspan="3">
					<? echo implode(' &nbsp;/&nbsp; ', $camisas_ar); ?>
					</td>
				</tr>
			</table>
			<?
			}

		}
		?>
	</section>

	<section id="informacoes-entrega" class="secao">
		<?
		if($loja_delivery) { 

			$loja_periodo = utf8_encode($loja['LO_CLI_PERIODO']);
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
			<table>
				<tr>
					<th>Delivery</th>
					<th>Ponto de Referência</th>
					<th>Cuidados</th>
				</tr>
				<tr>
					<td><? echo $loja_endereco.', '.$loja_numero; if (!empty($loja_complemento)){ echo '- '.$loja_complemento; } echo ' - '; if(!empty($loja_cep)) { echo 'CEP: '. $loja_cep.' - '; } echo $loja_bairro; ?></td>
					<td><? echo $loja_referencia; ?></td>
					<td><? echo $loja_cuidados.' - '.$loja_celular; ?></td>
				</tr>
			</table>
			
			<div class="form">
				<p><strong>Data:</strong> <? echo $loja_data_para_entrega; ?></p>
				
				<p>
					<strong>Período:</strong> 
					
					<span class="check"><span><? if($loja_periodo == 'manha') { echo '<strong>&times;</strong>'; } ?></span> Manhã</span>  
					<span class="check"><span><? if($loja_periodo == 'tarde') { echo '<strong>&times;</strong>'; } ?></span> Tarde</span>  
					<span class="check"><span><? if($loja_periodo == 'noite') { echo '<strong>&times;</strong>'; } ?></span> Noite</span>
				</p>
			</div>
		<?
		}
		?>

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