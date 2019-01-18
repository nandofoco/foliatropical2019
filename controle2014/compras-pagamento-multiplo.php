<?



//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");



//-----------------------------------------------------------------//


// if (isset($_SESSION['reload'])) 
// {
// 	header('Location: https://ingressos.foliatropical.com.br/controle2014/compras/pagamento-multiplo/'.$_SESSION["reload-cod"]);
// 	unset($_SESSION['reload']);
// }


//Pagina atual
define('PGCOMPRA', 'true');

$evento = (int) $_SESSION['usuario-carnaval'];
$cod = (int) $_GET['c'];
$limpar = (bool) $_GET['limpar'];
$tipo_ingresso = format($_GET['t']);

// Ao optar por editar, os dados serão substituídos pelos novos
// if ($_SESSION['exclui_multiplo']) 
// {
// 	$sql_loja = sqlsrv_query($conexao, "delete from loja_pagamento_multiplo where PM_LOJA=$cod", $conexao_params, $conexao_options);
// 	$_SESSION['exclui_multiplo'] = false;
// }


if(!empty($cod) && !empty($evento)) {
	
	//Página atual	
	$loja_qtde_folia = 0;
	$loja_qtde_frisa = 0;
	$loja_enable_frisa = false;

	//Limpar carrinho
	// if($limpar)
	// print_r($_SESSION['pagamento-multiplo']);
	unset($_SESSION['pagamento-multiplo'][$cod]);

	$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA, CONVERT(CHAR, l.LO_CLI_DATA_ENTREGA, 103) AS DATA_PARA_ENTREGA, CONVERT(VARCHAR, l.LO_DEADLINE, 103) AS DATA_DEADLINE FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);
	
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
		$loja_retirada = $loja['LO_RETIRADA'];
		$loja_periodo = $loja['LO_CLI_PERIODO'];
		$loja_data_para_entrega = utf8_encode($loja['DATA_PARA_ENTREGA']);

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
		

		if($loja_delivery) {
			$lo_valor_delivery = $loja['LO_VALOR_DELIVERY'];
			$lo_valor_delivery_f = number_format($lo_valor_delivery, 2, ',','.');			
		}

		$loja_deadline = $loja['DATA_DEADLINE'];
		$loja_concierge = $loja['LO_CONCIERGE'];
		$loja_origem = $loja['LO_ORIGEM'];
		$loja_comissao = $loja['LO_COMISSAO'];
		$loja_comissao_retida = (bool) $loja['LO_COMISSAO_RETIDA'];

		if($loja_comissao_retida) $loja_valor_total = $loja_valor_total-($loja_valor_total*$loja_comissao/100);

		$loja_valor_total_f = number_format($loja_valor_total, 2, ',','.');
		
		//-----------------------------------------------------------------------------//

		//arquivos de layout
		include("include/head.php");
		include("include/header.php");

		//-----------------------------------------------------------------------------//

		$sql_formas_pagamento = sqlsrv_query($conexao, "SELECT FP_COD, FP_NOME FROM formas_pagamento WHERE FP_COD NOT IN (5,10) AND D_E_L_E_T_=0 ORDER BY FP_NOME ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_formas_pagamento)){

			while ($ar_formas_pagamento = sqlsrv_fetch_array($sql_formas_pagamento)) {
				
				$formas_pagamento_cod = $ar_formas_pagamento['FP_COD'];
				$formas_pagamento_nome = $ar_formas_pagamento['FP_NOME'];				
				$formas_pagamento[$formas_pagamento_cod] = $formas_pagamento_nome;

			}
		}

		?>
		<section id="conteudo" class="multiplo">

				<header class="titulo">
					<h1>Pagamento <span>Múltiplo</span></h1>

					<div class="valor-total">R$ <span>0,00</span> / R$ <? echo $loja_valor_total_f; ?></div>					
				</header>

				<section class="secao" id="compra-dados">
					<aside><? echo $loja_cod; ?></aside>
					<section>
						<h1><? echo $loja_nome; ?></h1>
						<p><? echo $loja_email; ?></p>
						<p><? echo $loja_telefone; ?></p>
					</section>
					<div class="clear"></div>
					
					<input type="hidden" name="total-ingressos" value="<? echo $loja_valor_total; ?>" />

				</section>

				<section id="multiplo-adicionar" class="secao">

					<form id="adicionar" method="post" action="#">

						<input type="hidden" name="cod" id="cod" value="<? echo $loja_cod; ?>" />

					
						<h3>Adicionar forma de pagamento:</h3>		
						<p class="coluna">
							<label for="multiplo-valor">Valor:</label>
							<input type="text" name="valor" class="input money" id="multiplo-valor" value="0,00" />
						</p>

						<section class="selectbox coluna" id="multiplo-forma">
							<a href="#" class="arrow"><strong>Forma de pagamento:</strong><span></span></a>
							<ul class="drop">
								<?

								foreach ($formas_pagamento as $key => $forma) {

									$formas_pagamento_nome = utf8_encode($forma);

									?>
									<li><label class="item"><input type="radio" name="forma" value="<? echo $key; ?>" alt="<? echo $formas_pagamento_nome; ?>" /><? echo $formas_pagamento_nome; ?></label></li>
									<?

								}
								?>
							</ul>
							<div class="clear"></div>
						</section>

						<input type="submit" class="submit coluna" value="Adicionar" />

						<div class="clear"></div>

					</form>
				</section>

				<section id="multiplo-lista">
					<form method="post" action="<? echo SITE; ?>compras/pagamento-multiplo/post/">


						<section class="secao">
						<input type="hidden" name="cod" value="<? echo $cod; ?>" />

						<ul>
						<?

						// Buscar formas de pagamento multiplo
						$sql_pagamento = sqlsrv_query($conexao, "SELECT *, ISNULL(DATEDIFF (DAY, PM_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA FROM loja_pagamento_multiplo WHERE PM_LOJA='$cod' ORDER BY PM_FORMA ASC", $conexao_params, $conexao_options);
						

						if(sqlsrv_num_rows($sql_pagamento)){
							
							$ipagamento = 0;
							while ($ar_pagamento = sqlsrv_fetch_array($sql_pagamento)) {
								$pagamento_cod = $ar_pagamento['PM_COD'];
								$pagamento_forma = $ar_pagamento['PM_FORMA'];
								$pagamento_valor = $ar_pagamento['PM_VALOR'];
								$pagamento_pago =  (bool) $ar_pagamento['PM_PAGO'];
								$pagamento_diferenca_dias = (5 - $ar_pagamento['DIFERENCA']);
								$pagamento_status_transacao = $ar_pagamento['PM_STATUS_TRANSACAO'];
								

								//Se for cartão de credito
								if($pagamento_forma == 1) {
									//XML
									$pagamento_xml = $ar_pagamento['LO_XML'];
									if(!empty($pagamento_xml)) $xml = new SimpleXMLElement($pagamento_xml);
									
								}

								

								//Se não existe adicionamos o item ao array
								$_SESSION['pagamento-multiplo'][$cod][$ipagamento]['valor'] = $pagamento_valor;
								$_SESSION['pagamento-multiplo'][$cod][$ipagamento]['forma'] = $pagamento_forma;
								$_SESSION['pagamento-multiplo'][$cod][$ipagamento]['pago'] = $pagamento_pago;
								$_SESSION['pagamento-multiplo'][$cod][$ipagamento]['bd'] = true;
								$_SESSION['pagamento-multiplo'][$cod][$ipagamento]['cod'] = $pagamento_cod;
								$_SESSION['pagamento-multiplo'][$cod][$ipagamento]['diferenca'] = $pagamento_diferenca_dias;

								if(($pagamento_forma == 1) && ($pagamento_status_transacao == 4) && ($pagamento_diferenca_dias > -1)) $_SESSION['pagamento-multiplo'][$cod][$ipagamento]['captura'] = true;
								//else $_SESSION['pagamento-multiplo'][$cod][$ipagamento]['captura'] = true;								
								//if(!empty($loja_xml)) { }

								$ipagamento++;
							}
						}

						

						if(count($_SESSION['pagamento-multiplo'][$cod]) > 0) {

							foreach ($_SESSION['pagamento-multiplo'][$cod] as $key => $compra) {
								
								$item = $compra['cod'];
								$forma = $compra['forma'];
								$pago = $compra['pago'] ? 'pago' : '';
								$acao = $compra['pago'] ? 'bloquear' : 'confirmar';
								$disabled = $compra['bd'] ? '' : 'disabled';
								$sessao = $compra['sessao'] ? ' class="sessao"' : '';
								
								// $link = $compra['bd'] ? SITE.'e-compras-pagamento-multiplo-gerenciar.php?a='.$acao.'&c='.$item.'&f='.$forma.'&l='.$loja_cod : '#';
								
								// $label = 'Confirmar';
								// $cartao_credito = ($compra['forma'] == 1) ? true : false;
								
								// if(!$compra['pago'] && $compra['bd'] && $cartao_credito) {
								// 	$label = 'Pagar';
								// 	$class = 'pagar';
								// 	$link = SITE.'compras/pagamento/v2/'.$cod.'/';
								// }
								// if(!$compra['pago'] && $compra['bd'] && $cartao_credito && $compra['captura']) {
								// 	$label = 'Confirmar ('.$compra['diferenca'].')';
								// 	$link = SITE.'compra/pagamento-multiplo/captura/'.$item.'/';
								// }

								// if($compra['pago'] && $compra['bd'] && $cartao_credito) {
								// 	$label = 'Cancelar';
									$link = SITE.'pagamento-multiplo-cancelar.php?c='.$item.'&l='.$loja_cod;
								// }
								
								$ingressos_html .= '<li id="linha'.$compra['cod'].'">';
								$ingressos_html .= '<strong>'.utf8_encode($formas_pagamento[$compra['forma']]).'</strong> R$ '.number_format($compra['valor'], 2, ',','.');
								// if(!$pago) {

									// $ingressos_html .= '<a href="'.SITE.'include/multiplo-adicionar.php?c='.$compra['cod'].'&i='.$key.'&a=excluir&l='.$cod.'" class="remover">&times;</a>';

									//$ingressos_html .= '<a href="'.SITE.'e-compras-pagamento-multiplo-gerenciar.php?c='.$compra['cod'].'&i='.$key.'&a=excluir&l='.$cod.'" class="remover" id="remover" onclick="escondeLinha('.$compra['cod'].')">&times;</a>';

									// $ingressos_html .= '<a href="'.SITE.'e-compras-pagamento-multiplo-gerenciar.php?c='.$pagamento_cod.'&i='.$key.'&a=excluir" class="remover">&times;</a>';
									

									// if($compra['bd']) $ingressos_html .= '<a href="'.$link.'" class="confirmar '.$class.' '.$disabled.'" title="Confirmar pagamento">'.$label.'</a>';
								// } else {
									if($compra['bd']) $ingressos_html .= '<a href="" class="pago"></a> <a href="'.$link.'" class="remover" title="Cancelar pagamento">×</a>';									
								// }
								$ingressos_html .= '<input type="hidden" name="multiplo['.$compra['forma'].']" value="'.$compra['valor'].'" '.$sessao.' />';

							}

							echo $ingressos_html;


						}

						?>
						</ul>

						</section>
						
						<footer class="controle">
							<input type="submit" class="submit coluna" value="Cadastrar" />
							<a href="<? echo SITE; ?>financeiro/detalhes/<? echo $cod; ?>/" class="cancel no-cancel coluna">Voltar</a>
							<div class="clear"></div>
						</footer>

					</form>
				</section>
				
		</section>
		<input type="hidden" name="vendedor-externo-checked" value="<? echo $loja_concierge; ?>" />
		<script>
			function escondeLinha(id_linha)
			{
				$('#linha'+id_linha).hide();
			}

			$(".remover").click(function() {
			  alert( "Removido com sucesso!" );
			  // window.location.reload(true);
			  // window.location.replace(" https://ingressos.foliatropical.com.br/controle2014/compras/pagamento-multiplo/")
			  window.parent.location.href='https://ingressos.foliatropical.com.br/controle2014/compras/pagamento-multiplo/<? echo $cod; ?>'

			});
		</script>
		<?


		//-----------------------------------------------------------------//

		include('include/footer.php');

		//Fechar conexoes
		include("conn/close.php");
		include("conn/close-sankhya.php");
		
		exit();
	}
}

?>
<script type="text/javascript">
	history.go(-1);
</script>

