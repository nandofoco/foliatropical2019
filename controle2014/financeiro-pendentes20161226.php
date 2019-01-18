<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");


//-----------------------------------------------------------------//
//clearsale

require __DIR__.'/ClearSale/vendor/autoload.php';

use ClearSale\ClearSaleAnalysis;
use ClearSale\Environment\Sandbox;
use ClearSale\XmlEntity\Response\OrderReturn;


$environment = new Sandbox(CLEARSALE_ENTITY_CODE);

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$administrador = ($_SESSION['us-grupo'] == 'ADM') ? true : false;
$vendedor = ($_SESSION['us-grupo'] == 'VIN') ? true : false;
$usuario = (int) $_SESSION['us-cod'];

// Se o usuário for vendedor interno, ver apenas as suas vendas
if($vendedor) $search_vendedor = " AND LO_VENDEDOR='$usuario' ";

//-----------------------------------------------------------------//

$q = format($_GET['q']);
if(!empty($q)) {

	if(!is_numeric($q)) {
		// $search_query = is_numeric($q) ? " AND CODPARC='$q' " : " AND NOMEPARC LIKE '%$q%' ";
		$search_query = " AND NOMEPARC LIKE '%$q%' ";

		$sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC, CLIENTE, VENDEDOR FROM TGFPAR WHERE (CLIENTE='S' OR VENDEDOR='S') AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		// $sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC FROM TGFPAR WHERE CLIENTE='S' AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_search) > 0) {
			$ar_clientes_cods = $ar_parceiros_cods = array();
			while ($cods = sqlsrv_fetch_array($sql_search)) {
				if($cods['CLIENTE'] == 'S') array_push($ar_clientes_cods, $cods['CODPARC']);
				if($cods['VENDEDOR'] == 'S') array_push($ar_parceiros_cods, $cods['CODPARC']);
			}
			
			$search = " AND (";

			if(count($ar_clientes_cods) > 0) {
				$clientes_cods = implode(",", $ar_clientes_cods);
				$search .= " LO_CLIENTE IN ($clientes_cods) ";
			}
			if(count($ar_parceiros_cods) > 0) {
				$parceiros_cods = implode(",", $ar_parceiros_cods);
				if(count($ar_clientes_cods) > 0) $search .= " OR ";
				$search .= " LO_PARCEIRO IN ($parceiros_cods) ";
			}

			$search .= ") ";
		} else {
			// $search = " AND LO_CLIENTE IN ('') ";
			$nosearch = true;
		}
	} else {
		$search = " AND LO_COD='$q' ";
	}
}

//-----------------------------------------------------------------//

// Formas de pagamento
$sql_formas_pagamento = sqlsrv_query($conexao, "SELECT FP_COD, FP_NOME FROM formas_pagamento WHERE D_E_L_E_T_=0 ORDER BY FP_NOME ASC", $conexao_params, $conexao_options);
if(sqlsrv_num_rows($sql_formas_pagamento)){
	while ($ar_formas_pagamento = sqlsrv_fetch_array($sql_formas_pagamento)) { 
		$forma_pagamento = $ar_formas_pagamento['FP_NOME'];
		$formas_pagamento[$ar_formas_pagamento['FP_COD']] = ($forma_pagamento == utf8_decode('Cartão de Crédito')) ? utf8_decode('Cartão Crédito') : $forma_pagamento;
	}
}


// Blacklist
$sql_blacklist = sqlsrv_query($conexao, "SELECT * FROM loja_blacklist", $conexao_params, $conexao_options);
if(sqlsrv_num_rows($sql_blacklist)){

	$blacklist['usuario'] = array();
	$blacklist['cpf'] = array();
	$blacklist['cartao'] = array();

	while ($ar_blacklist = sqlsrv_fetch_array($sql_blacklist)) { 

		array_push($blacklist['usuario'], $ar_blacklist['LB_USUARIO']);
		array_push($blacklist['cpf'], $ar_blacklist['LB_CPF']);
		array_push($blacklist['cartao'], $ar_blacklist['LB_CARTAO']);
	}

	$blacklist_usuario = implode(",", $blacklist['usuario']);
	$blacklist_cartao = implode("','", $blacklist['cartao']);
	$blacklist_cpf = implode("','", $blacklist['cpf']);

}

//-----------------------------------------------------------------//

$p = (int) $_GET['p'];
if(!($p > 0)) $p = 1;

 // LO_COD, LO_CLIENTE, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, (CONVERT(VARCHAR, LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA, (CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA

if(!$nosearch) {

	$sql_loja = sqlsrv_query($conexao, "

	DECLARE @PageNumber INT;
	DECLARE @PageSize INT;
	DECLARE @TotalPages INT;

	SET @PageSize = 20;
	SET @PageNumber = $p;

	IF @PageNumber = 0 BEGIN
	SET @PageNumber = 1
	END;

	SET @TotalPages = CEILING(CONVERT(NUMERIC(20,10), ISNULL((SELECT COUNT(*) FROM loja (NOLOCK) WHERE 
	LO_PAGO='0' AND LO_EVENTO='$evento' AND LO_BLOCK='0' AND D_E_L_E_T_='0' $search), 0)) / @PageSize);

	WITH cadastro(NumeroLinha, LO_COD, LO_CLIENTE, LO_PARCEIRO, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, LO_DATA_COMPRA, LO_DEADLINE, LO_CARTAO_V2, LO_CARTAO_CHECKOUTID, DATA, DATA_PAGAMENTO, DATA_MINI, DATA_PAGAMENTO_MINI, DIFERENCA, LO_CARTAO_BANDEIRA, LO_CARTAO_CPF, BLACKLIST_CLIENTE, BLACKLIST_CARTAO, BLACKLIST_CPF,LO_ANTIFRAUDE_STATUS,LO_ANTIFRAUDE_SCORE,LO_ANTIFRAUDE_QUIZ_URL)
	AS (
	SELECT ROW_NUMBER() OVER (ORDER BY LO_COD DESC) AS NumeroLinha,
	LO_COD, 
	LO_CLIENTE,
	LO_PARCEIRO, 
	LO_FORMA_PAGAMENTO, 
	LO_STATUS_TRANSACAO, 
	LO_VALOR_TOTAL, 
	LO_DATA_COMPRA,
	LO_DEADLINE,
	LO_CARTAO_V2,
	LO_CARTAO_CHECKOUTID,
	(CONVERT(VARCHAR, LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA, 
	(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO, 
	(SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA_MINI, 
	(SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO_MINI, 
	ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA,
	LO_CARTAO_BANDEIRA,
	LO_CARTAO_CPF,

	CASE WHEN LO_CLIENTE IN ($blacklist_usuario) THEN 1 ELSE 0 END AS BLACKLIST_CLIENTE,
	CASE WHEN LO_CARTAO_BANDEIRA IN ('$blacklist_cartao') THEN 1 ELSE 0 END AS BLACKLIST_CARTAO,
	CASE WHEN LO_CARTAO_CPF IN ('$blacklist_cpf') THEN 1 ELSE 0 END AS BLACKLIST_CPF,
	LO_ANTIFRAUDE_STATUS,
	LO_ANTIFRAUDE_SCORE,
	LO_ANTIFRAUDE_QUIZ_URL

	FROM loja (NOLOCK) WHERE 
	LO_PAGO='0' AND LO_EVENTO='$evento' AND LO_BLOCK='0' AND D_E_L_E_T_='0' $search $search_vendedor
	)

	SELECT @TotalPages AS TOTAL, @PageNumber AS PAGINA, NumeroLinha, LO_COD, LO_CLIENTE, LO_PARCEIRO, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, LO_DEADLINE, LO_CARTAO_V2, LO_CARTAO_CHECKOUTID, DATA, DATA_PAGAMENTO, DATA_MINI, DATA_PAGAMENTO_MINI, DIFERENCA, LO_CARTAO_BANDEIRA, LO_CARTAO_CPF, BLACKLIST_CLIENTE, BLACKLIST_CARTAO, BLACKLIST_CPF,LO_ANTIFRAUDE_STATUS,LO_ANTIFRAUDE_SCORE,LO_ANTIFRAUDE_QUIZ_URL 
	FROM cadastro
	WHERE NumeroLinha BETWEEN ( ( ( @PageNumber - 1 ) * @PageSize ) + 1 ) AND ( @PageNumber * @PageSize )
	ORDER BY LO_DATA_COMPRA DESC

	", $conexao_params, $conexao_options);
 $n_loja = sqlsrv_num_rows($sql_loja);

} else {
	$n_loja = false;
}

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Pagamentos <span>Pendentes</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>financeiro/pendentes/">
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>financeiro/pendentes/" class="limpar-busca">&times;</a><? } ?>
				<input type="text" name="q" class="input" id="busca-lista-input" value="<? echo utf8_encode($q); ?>" />
			</p>
			<input type="submit" class="submit" value="" />
		</form>


		<a class="relatorio-financeiro" target="_blank" href="<? echo SITE; ?>relatorios-exportar.php?c=financeiro">Relatório Financeiro</a>

	</header>
	<section class="secao bottom">
		<table class="lista mini tablesorter-nopager">
			<thead>
				<tr>
					<th class="first"><strong>VCH</strong><span></span></th>
					<th><strong>Cliente</strong><span></span></th>
					<th><strong>parceiro</strong><span></span></th>
					<th><strong>Dt. Compra</strong><span></span></th>
					<th><strong>Itens</strong><span></span></th>
					<th><strong>Forma Pgto</strong><span></span></th>
					<th class="right"><span></span><strong>Valor (R$)</strong></th>
					<th class="th-left" title="AntiFraude"><strong>AF</strong></th>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="9">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_loja !== false) {

				$i=1;
				while($loja = sqlsrv_fetch_array($sql_loja)) {
					
					//Total de paginas
					$total_paginas = $loja['TOTAL'];

					$loja_cod = $loja['LO_COD'];
					$loja_data = $loja['DATA'];
					$loja_data_mini = $loja['DATA_MINI'];
					$loja_data_pagamento_mini = $loja['DATA_PAGAMENTO_MINI'];
					$loja_cliente_cod = $loja['LO_CLIENTE'];
					$loja_parceiro_cod = $loja['LO_PARCEIRO'];
					$loja_tipo_pagamento = $loja['LO_FORMA_PAGAMENTO'];
					$loja_status_transacao = $loja['LO_STATUS_TRANSACAO'];
					$loja_diferenca_dias = (5 - $loja['DIFERENCA']);
					$loja_antifraude_status = $loja['LO_ANTIFRAUDE_STATUS'];
					$loja_antifraude_score = $loja['LO_ANTIFRAUDE_SCORE'];
					$loja_antifraude_quiz_url = $loja['LO_ANTIFRAUDE_QUIZ_URL'];
					$cartao_credito = ($loja_tipo_pagamento == 1) ? true : false;
					$multiplo = ($loja_tipo_pagamento == 10) ? true : false;
					$faturado = ($loja_tipo_pagamento == 7) ? true : false;
					$reserva = ($loja_tipo_pagamento == 5) ? true : false;

					$loja_blacklist_cliente_cartao = utf8_encode($loja['LO_CARTAO_BANDEIRA']);
					$loja_blacklist_cliente_cpf = formatCPFCNPJ(str_pad($loja['LO_CARTAO_CPF'], 11, '0', STR_PAD_LEFT));
					$loja_blacklist_cliente = (bool) $loja['BLACKLIST_CLIENTE'];
					$loja_blacklist_cartao = (bool) $loja['BLACKLIST_CARTAO'];
					$loja_blacklist_cpf = (bool) $loja['BLACKLIST_CPF'];

					// $loja_forma_pagamento = utf8_encode($loja['FP_NOME']);
					$loja_forma_pagamento = utf8_encode($formas_pagamento[$loja_tipo_pagamento]);
					$loja_valor = number_format($loja['LO_VALOR_TOTAL'], 2, ",", ".");				

					unset($loja_cliente, $loja_cliente_exibir, $loja_parceiro, $loja_parceiro_exibir);

					// $loja_cliente = utf8_encode($loja['CL_NOME']);
					$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 2 NOMEPARC, CODPARC FROM TGFPAR WHERE CODPARC IN ('$loja_cliente_cod','$loja_parceiro_cod') AND (CLIENTE='S' OR VENDEDOR='S') AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_cliente) > 0) {
						while($loja_cliente_ar = sqlsrv_fetch_array($sql_cliente)) {
							switch ($loja_cliente_ar['CODPARC']) {
								case $loja_cliente_cod:
									$loja_cliente = trim($loja_cliente_ar['NOMEPARC']);
									$loja_cliente_exibir = (strlen($loja_cliente) > 20) ? substr($loja_cliente, 0, 20)."..." : $loja_cliente;									
								break;

								case $loja_parceiro_cod:
									$loja_parceiro = trim($loja_cliente_ar['NOMEPARC']);
									$loja_parceiro_exibir = (strlen($loja_parceiro) > 20) ? substr($loja_parceiro, 0, 20)."..." : $loja_parceiro;									
								break;
							}							
						}
					}

					//buscar itens
					$sql_itens = sqlsrv_query($conexao, "SELECT * FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
					$n_itens = sqlsrv_num_rows($sql_itens);

					$alterar_pagamento = ($cartao_credito && (($loja_status_transacao == 4) && ($loja_diferenca_dias > -1))) ? false : true;
					$alterar_pagamento = $multiplo ? false : $alterar_pagamento;
					$alterar_pagamento_classe = '';

					unset($loja_deadline_class);

					//Deadline
					if(!empty($loja['LO_DEADLINE'])) {
						$loja_deadline_n = $loja['LO_DEADLINE'];
						$loja_deadline_n = date('Y-m-d', strtotime($loja_deadline_n->format('Y-m-d')));

						$loja_deadline_class = ($loja_deadline_n < date('Y-m-d')) ? ' class="block"' : '';
					}

					//Cielo V2
					$loja_cielo_v2 = (bool) $loja['LO_CARTAO_V2'];
					if($loja_cielo_v2) $loja_checkoutid = $loja['LO_CARTAO_CHECKOUTID'];

					//antifraude
					if($cartao_credito/* && $loja_cielo_v2*/) {
						//iniciar variavel para analise da clearsale
						$clearSale = new ClearSaleAnalysis($environment);

						//variavel de retorno com os dados se ja tiver consultado antes e estiver no banco
						$orderReturn = new OrderReturn($loja_cod,$loja_antifraude_status,$loja_antifraude_score);

						$antifraudeAnalisando=$antifraudeReprovado=$antifraudeAprovado=false;
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

					?>
						<tr <? echo $loja_deadline_class; ?>>	
							<td class="first detalhes-voucher" data-cod="<? echo $loja_cod; ?>" data-cancelado="false">
								<div class="relative">
									<? echo $loja_cod; ?>
									<section class="detalhes"></section>
								</div>
							</td>
							<td <? if($loja_cliente != $loja_cliente_exibir) { echo 'title="'.utf8_encode($loja_cliente).'"'; } ?>>
								<? echo utf8_encode($loja_cliente_exibir); ?>
							</td>
							<td <? if($loja_parceiro != $loja_parceiro_exibir) { echo 'title="'.utf8_encode($loja_parceiro).'"'; } ?>>
								<? echo utf8_encode($loja_parceiro_exibir); ?>
							</td>
							<td title="<? echo $loja_data; ?>"><? echo $loja_data_mini; ?></td>
							<td><? echo $n_itens; ?></td>
							<td><? echo $loja_forma_pagamento; ?></td>
							<td class="valor"><? echo $loja_valor; ?></td>
							<td class="antifraude">
								<? if($cartao_credito) {
									if(($loja_status_transacao == 4 || $loja_status_transacao == 6) && ($loja_diferenca_dias > -1)) {?>
										<div class="relative">
											<?php if($antifraudeAprovado){ ?>
												<div class="status <?php echo $aprovado_score_cor ?>">
													<div class="info">
														<strong class="titulo">Analisado</strong>
														<p class="texto"><!-- Pedido analisado. --></p>
														<p class="score">Score: <?php echo number_format($loja_antifraude_score, 2, ',', '.')."%"; ?> 
														<?php switch (true) {
															case $loja_antifraude_score<30:
																echo "(Risco Baixo)";
																break;
															case $loja_antifraude_score<60:
																echo "(Risco Médio)";
																break;

															case $loja_antifraude_score<90:
																echo "(Risco Alto)";
																break;

															case $loja_antifraude_score<100:
																echo "(Risco Crítico)";
																break;
															
															default:
																echo "(Risco Desconhecido)";
																break;
														} ?></p>
													</div>
												</div>
											<? }else if($antifraudeReprovado){?>
												<div class="status nao-analisado">
													<div class="info">
														<strong class="titulo">Reprovado</strong>
														<p class="texto">Análise reprovada.</p>
													</div>
												</div>
											<?php } else if($antifraudeAnalisando){ ?>
												<div class="status nao-analisado">
													<div class="info">
														<strong class="titulo">Aguardando análise</strong>
														<p class="texto">Pedido em análise.</p>
														<?php if(!empty($loja_antifraude_quiz_url)){ ?>
															<a href="<?php echo SITE ?>ClearSale/paginas/enviar_questionario.php?cod=<?php echo $loja_cod ?>" class="btn-quiz quiz active">Enviar questionário ao cliente</a>
														<?php } ?>
													</div>
												</div>
												<button  title="Atualizar Antifraude" class="acao" data-cod="<?php echo $loja_cod ?>" data-acao="consultar"><img src="<?php echo SITE ?>img/verificar_compra.png"></button>
											<?php } else { ?>
												<div class="status nao-analisado">
													<div class="info">
														<strong class="titulo">Não analisado</strong>
														<p class="texto">Clique em atualizar para analizar o pedido!</p>
														<p class="score"></p>
														<a href="<?php echo SITE ?>ClearSale/paginas/enviar_questionario.php?cod=<?php echo $loja_cod ?>" class="btn-quiz quiz">Enviar questionário ao cliente</a>
													</div>
												</div>
												<button  title="Atualizar Antifraude" class="acao" data-cod="<?php echo $loja_cod ?>" data-acao="enviar"><img src="<?php echo SITE ?>img/verificar_compra.png"></button>
											<? } ?>
										</div>
									<?}
								} ?>
							</td>
							<td class="ctrl <? if($administrador) { echo 'financeiro'; } ?>">
								<? if($administrador) { ?>
								<section class="selectbox alterar-pagamento <? if ($alterar_pagamento && !$reserva){ echo 'plus'; } ?>">
									<?
									if($cartao_credito) {
										if(($loja_status_transacao == 4) && ($loja_diferenca_dias > -1)) {
											// if(!$clearSale->notApprovedReturn($orderReturn)&&!$clearSale->ApprovedReturn($orderReturn)){
												if($loja_cielo_v2) {
													?>
													<!-- <a href="https://cieloecommerce.cielo.com.br/Backoffice/Merchant/Order?OrderNumber=<? echo $loja_checkoutid; ?>&PageSize=50&PageIndex=1" target="_blank" class="liberar confirm" title="Confirmar o pagamento da compra <? echo $loja_cod; ?> (<? echo $loja_diferenca_dias; ?> dia<? echo $loja_diferenca_dias != 1 ? 's' : ''; ?> para expirar o prazo de confirmação)?">Confirmar (<? echo $loja_diferenca_dias; ?>)</a> -->
													<a href="<?php echo SITE."compra/captura/$loja_cod/" ?>" target="_blank" class="liberar confirm <?php if($antifraudeAprovado){ 
														echo $aprovado_score_cor;
													}else if($antifraudeReprovado){
														echo "nao-analisado";
													} else if($antifraudeAnalisando){ 
														echo "nao-analisado";
													} else { 
														echo "nao-analisado";
													}?> " title="Confirmar o pagamento da compra <? echo $loja_cod; ?> (<? echo $loja_diferenca_dias; ?> dia<? echo $loja_diferenca_dias != 1 ? 's' : ''; ?> para expirar o prazo de confirmação)?">Confirmar (<? echo $loja_diferenca_dias; ?>)</a>
													<?
												} else {
													?>
													<a href="<? echo SITE; ?>compra/captura/<? echo $loja_cod; ?>/" class="liberar confirm <?php if($antifraudeAprovado){ 
														echo $aprovado_score_cor;
													}else if($antifraudeReprovado){
														echo "nao-analisado";
													} else if($antifraudeAnalisando){ 
														echo "nao-analisado";
													} else { 
														echo "nao-analisado";
													}?> " title="Confirmar o pagamento da compra <? echo $loja_cod; ?> (<? echo $loja_diferenca_dias; ?> dia<? echo $loja_diferenca_dias != 1 ? 's' : ''; ?> para expirar o prazo de confirmação)?">Confirmar (<? echo $loja_diferenca_dias; ?>)</a>
													<?
												}
											// }
										} else {
										?>
										<a href="<? echo SITE; ?>compras/pagamento/v2/<? echo $loja_cod; ?>/" class="liberar pagar confirm" title="Realizar o pagamento da compra <? echo $loja_cod; ?>?">Pagar</a>
										<?
											$alterar_pagamento_classe = 'pagar';
										}

									} elseif($reserva) {
									?>
										<a href="#" class="liberar reserva arrow">Alterar</a>
									<?
									} elseif($faturado) {
									?>
										<a href="<? echo SITE; ?>financeiro/faturado/<? echo $loja_cod; ?>/" class="liberar" title="Confirmar o pagamento da compra <? echo $loja_cod; ?>?">Confirmar</a>
									<?	
									} elseif($multiplo) {
									?>
										<a href="<? echo SITE; ?>compras/pagamento-multiplo/<? echo $loja_cod; ?>/" class="liberar" title="Confirmar o pagamento da compra <? echo $loja_cod; ?>?">Confirmar</a>
									<?	
									} else {
									?>
										<a href="<? echo SITE; ?>e-financeiro-gerenciar.php?c=<? echo $loja_cod; ?>&a=confirmar" class="liberar confirm" title="Confirmar o pagamento da compra <? echo $loja_cod; ?>?">Confirmar</a>
									<? }

									if($alterar_pagamento) {
										if(!$reserva){
										?>
										<a href="#" class="arrow plus <? echo $alterar_pagamento_classe; ?>"></a>
										<?
										}
										?>
										<ul class="drop">
											<?

											$sql_formas_pagamento = sqlsrv_query($conexao, "SELECT FP_COD, FP_NOME FROM formas_pagamento WHERE D_E_L_E_T_=0 ORDER BY FP_NOME ASC", $conexao_params, $conexao_options);
											if(sqlsrv_num_rows($sql_formas_pagamento)){

												while ($ar_formas_pagamento = sqlsrv_fetch_array($sql_formas_pagamento)) {
													
													$formas_pagamento_cod = $ar_formas_pagamento['FP_COD'];
													$formas_pagamento_nome = utf8_encode($ar_formas_pagamento['FP_NOME']);
													
												?>
												<li><a class="item" href="<? echo SITE; ?>e-financeiro-gerenciar.php?c=<? echo $loja_cod; ?>&a=alterar&f=<? echo $formas_pagamento_cod; ?>"><? echo $formas_pagamento_nome; ?></a></li>
												<?

												}
											}

											?>
										</ul>
										<?	
									}
									?>
								</section>
								<? } ?>
							</td>
							<td class="ctrl fin-af">
								<a href="<? echo SITE; ?>financeiro/detalhes/<? echo $loja_cod; ?>/" class="ver"></a>
								<? if($loja_blacklist_cliente || $loja_blacklist_cartao || $loja_blacklist_cpf) { ?>
								<div class="relative blacklist" title="Esta compra pode ser uma fraude">
									<span></span>
									<section class="detalhes-blacklist">
										<ul>
											<? if($loja_blacklist_cliente) { ?>
											<li><strong>Cliente</strong> listado na Blacklist</li>
											<? }
											if($loja_blacklist_cartao) { ?>
											<li><strong>Cartão:</strong> <? echo $loja_blacklist_cliente_cartao; ?></li>
											<? }
											if($loja_blacklist_cpf) { ?>
											<li><strong>CPF:</strong> <? echo $loja_blacklist_cliente_cpf; ?></li>
											<? } ?>
										</ul>
									</section>
								</div>
								<? } ?>
							</td>
						</tr>
					<?
					$i++;
				}

				$exibe_loja = true;
			} 
			if(!$exibe_loja) {
			?>
				<tr>
					<td colspan="9" class="nenhum">Nenhum pagamento pendente.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<?
		if ($exibe_loja) {

			
			
		?>
        <div class="pager-tablesorter">
	        <a href="<? echo SITE; ?>financeiro/pendentes/<? if(!empty($q)) echo '?q='.urlencode(utf8_encode($q)); ?>" class="first"></a>
	        <a href="<? echo SITE; ?>financeiro/pendentes/?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo ($p > 1) ? ($p - 1) : 1; ?>" class="prev"></a>
	        <span class="pagedisplay"><? echo $p; ?>/<? echo $total_paginas; ?></span>
	        <a href="<? echo SITE; ?>financeiro/pendentes/?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo ($p < $total_paginas) ? ($p + 1) : $total_paginas; ?>" class="next"></a>
	        <a href="<? echo SITE; ?>financeiro/pendentes/?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo $total_paginas; ?>" class="last"></a>
	        <!-- <input type="hidden" class="pagesize" value="30" /> -->
        </div>
        <? } ?>
	</section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>