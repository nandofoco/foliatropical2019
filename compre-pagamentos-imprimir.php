<?

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//Definir o carnaval ativo
include("include/setcarnaval.php");

//-----------------------------------------------------------------//

//arquivos de layout
// include("include/head.php");
// include("include/header.php");

$cod = (int) $_GET['c'];

//-----------------------------------------------------------------//

if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE; ?>login-cadastro-site/imprimir/<? echo $cod; ?>/';
</script>
<?
	exit();
}

//-----------------------------------------------------------------//

$evento = setcarnaval();
$usuario_cod = $_SESSION['usuario-cod'];

//-----------------------------------------------------------------//

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_CLIENTE='$usuario_cod' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);

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
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700"/>

<!--[if IE]>
<script src="<? echo SITE; ?>js/html5.js" type="text/javascript"></script>
<![endif]-->

</head>
<body>
	<section id="conteudo">

		<header id="topo">
			<img src="<? echo SITE; ?>img/logo-email.png" class="logo" />

			Sede Centro – Av. Passos, 120 14º Centro - RJ<br />
			Cep: 20051-040 Tel/Fax: 21 3202-6000<!--<br />
			Filial Ipanema – Rua Visconde de Pirajá, 411 – 202 - RJ<br />
			Cep: 22410-003 Tel: 21 2267-2666<br />
			Filial Shopping Leblon – Av. Afrânio de Melo Franco, 290 Q37 - RJ<br />
			Tel: 21 2540-7010-->
		</header>
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

		$cartao_credito = ($loja_forma == 1) ? true : false;
		$faturado = ($loja_forma == 7) ? true : false;
		$reserva = ($loja_forma == 5) ? true : false;

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL, DDI,DDD FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
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
		$sql_forma = $sql_cliente = sqlsrv_query($conexao, "SELECT TOP 1 FP_NOME FROM formas_pagamento WHERE FP_COD='$loja_forma'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_forma) > 0) {
			$loja_forma_ar = sqlsrv_fetch_array($sql_forma);
			$loja_forma_pagamento = utf8_encode($loja_forma_ar['FP_NOME']);
		}

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

			//Buscar faturas
			$sql_faturas = sqlsrv_query($conexao, "SELECT LF_VALOR, LF_PAGO FROM loja_faturadas WHERE LF_COMPRA='$cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
			$loja_parcelas = sqlsrv_num_rows($sql_faturas);
			
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


		//Cartão
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
		<h2>Voucher nº <? echo $loja_cod; ?></h2>
	</section>

	<section id="informacoes-gerais" class="secao">
		<h2>Favor fornecer a / Please provide to</h2>

		<table>
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
		</table>
	</section>

	<section id="informacoes-servicos" class="secao">
		<h2>Os seguintes serviços/The following services</h2>

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
						<?
						echo $loja_forma_pagamento;
						if($loja_parcelas > 1) { echo " - Parcelado em ".$loja_parcelas."x"; }						
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
						}
						?>
					</td>
				</tr>
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

	<section class="informacoes-retirada float secao">
		<!--<h3>Retirada ingressos + Kit Folião | Folia Tropical (utilização da camisa passaporte é obrigatória):</h3>
		<p>Candybox (24/02), sábado de acesso (25/02) Folia Tropical (26/02 – 27/02 – 04/03) - Abertura do camarote ocorrerá as 21:00 e o inicio dos desfiles as 22:00.</p>
		<p><strong>Credenciamento:</strong> Clube Monte Líbano - Av. Borges de Medeiros, 701 - Leblon</p>
		<p><strong>Datas e Horários:</strong></p>
		<p>22/02 - 23/02 - 03/03: das 14:00 às 22:00</p>
		<p>24/02 - 25/02 - 26/02 - 27/02 - 04/03: das 14:00 às 23:00</p>-->

		<h3>Retirada ingressos + Kit Folião | Folia Tropical (utilização da camisa passaporte é obrigatória):</h3>
		<p>Candybox (01/03) • Sábado de Acesso (02/03) • Folia tropical (03/03 – 04/03 – 09/03) - Abertura do camarote ocorrerá as 21:00 e o inicio dos desfiles as 22:00.</p>
		<p><strong>Credenciamento:</strong> CLUBE MONTE LÍBANO - (AV. BORGES DE MEDEIROS, 701 -LEBLON) ENTRADA PELO JARDIM ALAH</p>
		<p><strong>Datas e Horários:</strong></p>
		<p>27/02 - 28/02 - 08/03 DAS 14:00 ÀS 22:00</p>
		<p>01/03 - 02/03 - 03/02 - 04/03 - 09/03: DAS 14:00 ÀS 23:00</p>

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
		<p>27/02 - 28/02 - 08/03 DAS 14:00 ÀS 22:00</p>
		<p>01/03 - 02/03 - 03/02 - 04/03 - 09/03: DAS 14:00 ÀS 23:00</p>

	</section>

	<div class="clear"></div>


	
	<!-- <section class="informacoes-retirada secao big">
		<h3>Atenção!</h3>
		<p>Para os clientes que efetuaram a compra pelo nosso site, via cartão crédito, a conferência da titularidade será realizada no credenciamento. O titular do cadastro, obrigatoriamente, deverá ser o titular do cartão de crédito, sendo assim, terá que apresentá-los pessoalmente para retirada dos ingressos.</p>
		<p>Caso o cliente não apresente o cartão de crédito original e o documento com foto, por medida de segurança, os ingressos não serão entregues e a compra será estornada.</p>
	</section>
 -->
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
	<?

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

	}
	?>	
	</section>

	<input type="hidden" id="base-site" value="<? echo SITE; ?>" />
</body>
</html>
<?

//-----------------------------------------------------------------//

// include('include/footer.php');

//fechar conexao com o banco
include("conn/close.php");
include("conn/close-mssql.php");
include("conn/close-sankhya.php");

?>