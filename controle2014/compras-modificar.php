<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//Pagina atual
define('PGCOMPRA', 'true');

$evento = (int) $_SESSION['usuario-carnaval'];
$cod = (int) $_GET['c'];
$limpar = (bool) $_GET['limpar'];
$tipo_ingresso = format($_GET['t']);

if($_GET['teste']) print_r($_SESSION['compra-modificar']);


if(!empty($cod) && !empty($evento)) {
	
	//Página atual	
	define('PGATUAL', 'compras/modificar/'.$cod.'/');

	$loja_qtde_folia = 0;
	$loja_qtde_frisa = 0;
	$loja_enable_frisa = false;

	//Novos combos
	$loja_qtde_combo = array();

	//Limpar carrinho
	if($limpar) unset($_SESSION['compra-modificar'][$cod]);

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

		$loja_data = $loja['LO_DATA_COMPRA'];
		$anterior = (strtotime($loja_data->format('Y-m-d')) < strtotime('2015-10-15')) ? true : false;
		$loja_desconto_folia = ($anterior) ? 1 : (bool) $loja['LO_DESCONTO_FOLIA'];
		$loja_desconto_frisa = ($anterior) ? 1 : (bool) $loja['LO_DESCONTO_FRISA'];

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

		if($loja_delivery) {
			$lo_valor_delivery = $loja['LO_VALOR_DELIVERY'];
			$lo_valor_delivery_f = number_format($lo_valor_delivery, 2, ',','.');			
		}

		$loja_deadline = $loja['DATA_DEADLINE'];
		$loja_concierge = $loja['LO_CONCIERGE'];
		$loja_origem = $loja['LO_ORIGEM'];
		$loja_comissao = $loja['LO_COMISSAO'];
		$loja_comissao_retida = (bool) $loja['LO_COMISSAO_RETIDA'];
		
		//-----------------------------------------------------------------------------//

		// if($reserva && !(count($_SESSION['compra-modificar'][$cod]) > 0)) {
		if(!(count($_SESSION['compra-modificar'][$cod]) > 0)) {

			//Adicionar itens ao array
			$sql_item = sqlsrv_query($conexao, "SELECT COUNT(LI_COD) AS QTDE, LI_VALOR, LI_INGRESSO, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0' GROUP BY LI_INGRESSO, LI_VALOR, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO", $conexao_params, $conexao_options);

			if(sqlsrv_num_rows($sql_item) > 0) {

				//Limpar a sessao

				$i = 0;
				while ($item = sqlsrv_fetch_array($sql_item)) {
					$item_cod = $item['COD'];
					$item_qtde = $item['QTDE'];
					$item_ingresso = $item['LI_INGRESSO'];
					$item_desconto = $item['LI_DESCONTO'];
					$item_over_interno = $item['LI_OVER_INTERNO'];
					$item_over_externo = $item['LI_OVER_EXTERNO'];
					$item_valor =  $item['LI_VALOR'];
					$item_exclusividade = (bool) $item['EXCLUSIVIDADE'];
					$item_exclusividade_val = $item['EXCLUSIVIDADE_VAL'];


					//Informações adicionais do item
					$sql_info_item = sqlsrv_query($conexao, "
					SELECT v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_NOME, ed.ED_DATA, tp.TI_NOME 
					FROM vendas v, eventos_setores es, eventos_dias ed, tipos tp 
					WHERE v.VE_COD='$item_ingresso' AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD", $conexao_params, $conexao_options);

					if(sqlsrv_num_rows($sql_info_item) > 0) {
						$info_item = sqlsrv_fetch_array($sql_info_item);
					
						$item_setor = utf8_encode($info_item['ES_NOME']);
						$item_dia = utf8_encode($info_item['ED_NOME']);
						$item_tipo = utf8_encode($info_item['TI_NOME']);
						
						$item_fila = utf8_encode($info_item['VE_FILA']);
						$item_vaga = utf8_encode($info_item['VE_VAGAS']);
						$item_tipo_especifico = utf8_encode($info_item['VE_TIPO_ESPECIFICO']);

						$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;
					}

					// loja_itens_adicionais
					if($item_fechado) $item_qtde = $item_qtde / $item_vaga;

					$_SESSION['compra-modificar'][$cod][$i]['cod'] = $item_cod;
					$_SESSION['compra-modificar'][$cod][$i]['item'] = $item_ingresso;
					$_SESSION['compra-modificar'][$cod][$i]['valorbase'] = $item_valor - $item_over_externo - $item_over_interno;
					$_SESSION['compra-modificar'][$cod][$i]['valor'] = $item_valor;
					$_SESSION['compra-modificar'][$cod][$i]['desconto'] = $item_desconto;
					$_SESSION['compra-modificar'][$cod][$i]['overexterno'] = $item_over_externo;
					$_SESSION['compra-modificar'][$cod][$i]['overinterno'] = $item_over_interno;
					$_SESSION['compra-modificar'][$cod][$i]['qtde'] = $item_qtde;
					$_SESSION['compra-modificar'][$cod][$i]['estoque'] = $item_qtde;
					$_SESSION['compra-modificar'][$cod][$i]['exclusividade'] = $item_exclusividade;
					$_SESSION['compra-modificar'][$cod][$i]['exclusividade-val'] = $item_exclusividade_val;

					$i++;
				}
				unset($i);
				ksort($_SESSION['compra-modificar'][$cod]);

			} else {
				$permitir = true;
			}

			$limpar_carrinho = false;

		} else {
			$limpar_carrinho = true;
		}


		// $permitir = in_array($loja_cod, array(4251, 4278, 4312, 4329, 4350, 4356, 4370, 4406, 4433, 4449, 4470, 4476)) ? true :  false;

		//-----------------------------------------------------------------------------//

		// if($reserva && count($_SESSION['compra-modificar'][$cod]) > 0) {
		//if(count($_SESSION['compra-modificar'][$cod]) > 0) {		
		if($permitir || count($_SESSION['compra-modificar'][$cod]) > 0) {		

			//arquivos de layout
			include("include/head.php");
			include("include/header.php");

			//-----------------------------------------------------------------------------//

			?>
			<section id="conteudo">

				<form id="compras-novo" class="modificar" method="post" action="<? echo SITE; ?>compras/modificar/adicionar/">
					<input type="hidden" name="compra" value="<? echo $cod; ?>" />
					<header class="titulo">
						<h1>Adicionar <span>Ingressos</span></h1>
						<a href="#" class="showhide adicionar"><? echo (empty($tipo_ingresso)) ? '+' : '-'; ?></a>
					</header>

					<section class="hidden" <? if(!empty($tipo_ingresso)) { echo 'style="display: block"'; } ?>>

						<? include('include/secao-tipo-setor.php'); ?>

						<section class="secao label-top">
							<section id="compra-dias" class="radio infield dias coluna">
								<h3>Selecione o dia</h3>
								<ul>
								<?

								$sql_eventos_dias = sqlsrv_query($conexao, "SELECT ED_COD, ED_NOME, SUBSTRING(CONVERT(CHAR(8), ED_DATA, 103), 1, 5) AS DATA, DATEPART(WEEKDAY, ED_DATA) AS SEMANA FROM eventos_dias WHERE ED_EVENTO='$evento' AND D_E_L_E_T_=0 ORDER BY ED_DATA ASC;", $conexao_params, $conexao_options);
								if(sqlsrv_num_rows($sql_eventos_dias) !== false){

									while ($ar_eventos_dias = sqlsrv_fetch_array($sql_eventos_dias)) {
										
										$eventos_dias_cod = $ar_eventos_dias['ED_COD'];
										$eventos_dias_nome = utf8_encode($ar_eventos_dias['ED_NOME']);
										$eventos_dias_data = $ar_eventos_dias['DATA'];
										$eventos_dias_semana = $semana_min[($ar_eventos_dias['SEMANA']-1)];
										
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
							<input type="submit" class="submit coluna" value="Adicionar" />
							<div class="clear"></div>
						</footer>

					</section>
				</form>

				<form id="compras-adicionais" class="modificar" method="post" action="<? echo SITE; ?>compras/modificar/post/">
					<input type="hidden" name="compra" value="<? echo $cod; ?>" />
					<input type="hidden" name="parceiro" value="<? echo $loja_parceiro; ?>" />
					<header class="titulo">
						<h1>Alterar <span>Venda</span></h1>

						<div class="valor-total">R$ <? echo $loja_valor_total_f; ?></div>
					</header>

					<section class="secao" id="compra-dados">
						<aside><? echo $loja_cod; ?></aside>
						<section>
							<h1><? echo $loja_nome; ?></h1>
							<p><? echo $loja_email; ?></p>
							<p><? echo $loja_telefone; ?></p>
						</section>
						<div class="clear"></div>
					</section>

					<section class="secao cadastro-cliente">

						<section id="compras-origem" class="selectbox coluna modificar">
							<h3>Origem da compra:</h3>
							<a href="#" class="arrow"><strong>Selecione a origem</strong><span></span></a>
							<ul class="drop">
								<li><label class="item"><input type="radio" name="origem" value="telefone" alt="Telefone" />Telefone</label></li>
								<li><label class="item"><input type="radio" name="origem" value="balcao" alt="Balcão" />Balcão</label></li>
								<li><label class="item"><input type="radio" name="origem" value="site" alt="Site" />Site</label></li>
								<li><label class="item"><input type="radio" name="origem" value="chatonline" alt="Chat Online" />Chat Online</label></li>
								<li><label class="item"><input type="radio" name="origem" value="email" alt="E-mail" />E-mail</label></li>
							</ul>
						</section>

						<section id="compras-cliente" class="checkbox coluna">
							<ul class="hidden"><li><label class="item checked"><input type="checkbox" name="cliente" value="<? echo $loja_cliente; ?>" checked="checked" /></label></li></ul>

							<div class="sugestao">
								<p>
									<label for="carrinho-cliente">Cliente:</label>
									<input type="text" id="carrinho-cliente" name="cliente-sugestao" class="input sugestao" value="<? echo $loja_nome; ?>" />
								</p>
								<div class="drop">
							    	<ul></ul>
							    </div>
							</div>
						</section>

						<a href="<? echo SITE; ?>clientes/cadastro/" class="adicionar" title="Adicionar cliente" target="_blank">+</a>


						<section id="compra-forma-pagamento" class="selectbox coluna">
							<h3>Forma de pagamento:</h3>
							<a href="#" class="arrow"><strong>Selecione a forma</strong><span></span></a>
							<ul class="drop">
								<?

								$sql_formas_pagamento = sqlsrv_query($conexao, "SELECT FP_COD, FP_NOME FROM formas_pagamento WHERE D_E_L_E_T_=0 ORDER BY FP_NOME ASC", $conexao_params, $conexao_options);
								if(sqlsrv_num_rows($sql_formas_pagamento)){

									while ($ar_formas_pagamento = sqlsrv_fetch_array($sql_formas_pagamento)) {
										
										$formas_pagamento_cod = $ar_formas_pagamento['FP_COD'];
										$formas_pagamento_nome = utf8_encode($ar_formas_pagamento['FP_NOME']);
										
									?>
									<li><label class="item"><input type="radio" name="forma" value="<? echo $formas_pagamento_cod; ?>" alt="<? echo $formas_pagamento_nome; ?>" /><? echo $formas_pagamento_nome; ?></label></li>
									<?

									}
								}

								?>
							</ul>
						</section>

						<p class="coluna deadline">
							<label for="compra-deadline">Deadline:</label>
							<input type="text" id="compra-deadline" name="deadline" class="input disabled" disabled="disabled" value="<? echo $loja_deadline; ?>" />
						</p>

						<div class="clear"></div>

						<section id="compra-canal-venda" class="selectbox coluna bottom">
							<h3>Canal de venda:</h3>
							<a href="#" class="arrow"><strong>Selecione o canal de venda</strong><span></span></a>
							<div class="drop">
								<ul>
								<?

								// $sql_parceiros = sqlsrv_query($conexao, "SELECT PA_COD, PA_NOME FROM parceiros WHERE PA_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY PA_NOME ASC", $conexao_params, $conexao_options);
								$sql_parceiros = sqlsrv_query($conexao_sankhya, "SELECT CODPARC, NOMEPARC, CGC_CPF, EMAIL, AD_COMISSAO FROM TGFPAR WHERE VENDEDOR='S' AND BLOQUEAR='N' $search ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
								$n_parceiros = sqlsrv_num_rows($sql_parceiros);
								if($n_parceiros){

									$i_parceiros = 0;
									$break_parceiros = ceil($n_parceiros / 3);

									while ($ar_parceiros = sqlsrv_fetch_array($sql_parceiros)) {
										
										$parceiros_cod = $ar_parceiros['CODPARC'];
										$parceiros_nome = utf8_encode(trim($ar_parceiros['NOMEPARC']));
										$parceiros_comissao = trim($ar_parceiros['AD_COMISSAO']);

										if(($i_parceiros < $n_parceiros) && ($i_parceiros > 1) && (($i_parceiros % $break_parceiros) == 0)) { ?></ul><ul><? }
										
									?>
									<li><label class="item"><input type="radio" name="canal" value="<? echo $parceiros_cod; ?>" alt="<? echo $parceiros_nome; ?>" rel="<? echo $parceiros_comissao; ?>" /><? echo $parceiros_nome; ?></label></li>
									<?

										$i_parceiros++;
									}
								}

								?>
								</ul>
							</div>
						</section>

						<p class="coluna bottom comissao-modificar">
							<label for="compra-comissao">Comissão:</label>
							<input type="text" id="compra-comissao" name="comissao" class="input" value="<? echo (!empty($loja_comissao)) ? $loja_comissao : '0'; ?>" />
						</p>

						<section id="compras-comissao-retida" class="checkbox verify coluna bottom">
							<h3>Retida:</h3>
							<ul>
								<li><label class="item <? if($loja_comissao_retida) { echo 'checked'; } ?>"><input type="checkbox" name="retida" value="true" <? if($loja_comissao_retida) { echo 'checked="checked"'; } ?> /></label></li>
							</ul>
							<div class="clear"></div>
						</section>


						<section id="compra-vendedor-externo" class="selectbox coluna bottom disabled">
							<h3>Vendedor Externo:</h3>
							<a href="#" class="arrow"><strong>Selecione o vendedor externo</strong><span></span></a>
							<ul class="drop">
							</ul>
						</section>

						<div class="clear"></div>
						
					</section>
					
					<section class="secao">
					<?

					#retirar depois
					if(count($_SESSION['compra-modificar'][$cod]) > 0) {

						foreach ($_SESSION['compra-modificar'][$cod] as $key => $carrinho) {


							/*"SELECT v.*, t.TI_NOME, d.ED_NOME, s.ES_NOME,
									@ingresso:=v.VE_COD AS COD,
									@ingressos:=(SELECT COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.LI_INGRESSO=@ingresso AND li.D_E_L_E_T_=0) AS QTDE,
									@total := CAST((v.VE_ESTOQUE - @ingressos) AS SIGNED), IF(@total < 0,0, @total) AS TOTAL
									FROM vendas v, tipos t, eventos_dias d, eventos_setores s WHERE v.VE_COD='".$carrinho['item']."' AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0 AND d.ED_COD=v.VE_DIA AND t.TI_COD=v.VE_TIPO AND s.ES_COD=v.VE_SETOR AND d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0 LIMIT 1"*/
									
							$sql_ingressos = sqlsrv_query($conexao, "
									DECLARE @ingresso INT='".$carrinho['item']."';
									DECLARE @vendas TABLE (VE_COD INT, VE_TIPO INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT, VE_FILA VARCHAR(255), VE_VAGAS INT, VE_TIPO_ESPECIFICO VARCHAR(255), VE_VALOR_EXCLUSIVIDADE DECIMAL(10,2));
									DECLARE @qtde TABLE (COD INT, QTDE INT DEFAULT 0);

									INSERT INTO @vendas (VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO, VE_VALOR_EXCLUSIVIDADE)
									SELECT VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO,VE_VALOR_EXCLUSIVIDADE FROM vendas WHERE VE_COD=@ingresso AND VE_BLOCK=0 AND D_E_L_E_T_=0;

									INSERT INTO @qtde (COD, QTDE)
									SELECT li.LI_INGRESSO, COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE li.LI_INGRESSO=@ingresso AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 GROUP BY li.LI_INGRESSO;

									SELECT TOP 1 * FROM (SELECT ISNULL(q.QTDE, 0) AS QTDE, v.*, CAST((v.VE_ESTOQUE - ISNULL(q.QTDE, 0)) AS INT) AS TOTAL, t.TI_NOME, t.TI_TAG, d.ED_NOME, d.ED_DATA, s.ES_NOME FROM @vendas v 
									LEFT JOIN @qtde q ON v.VE_COD = q.COD
									LEFT JOIN tipos t ON t.TI_COD=v.VE_TIPO
									LEFT JOIN eventos_dias d ON d.ED_COD=v.VE_DIA
									LEFT JOIN eventos_setores s ON s.ES_COD=v.VE_SETOR
									WHERE d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0) S", $conexao_params, $conexao_options);

							
							if(sqlsrv_next_result($sql_ingressos) && sqlsrv_next_result($sql_ingressos))
							if(sqlsrv_num_rows($sql_ingressos) !== false) {

								$i=1;
								$ingressos = sqlsrv_fetch_array($sql_ingressos);

								$ingressos_estoque = (int) $ingressos['TOTAL'];
								$ingressos_vaga = utf8_encode($ingressos['VE_VAGAS']);
								$ingressos_tipo_especifico = utf8_encode($ingressos['VE_TIPO_ESPECIFICO']);

								//Calculo de estoque
								if(($ingressos_tipo_especifico == 'fechado') && ($ingressos_vaga > 0)) {
									$ingressos_estoque = $ingressos['VE_ESTOQUE'] / $ingressos_vaga;
									$ingressos_estoque = $ingressos_estoque - ($ingressos['QTDE'] / $ingressos_vaga);
								}

								//Estoque descontando o que já foi comprado
								$ingressos_estoque += $carrinho['estoque'];

								if(!($ingressos_estoque > 0)) {
									// $_SESSION['compra-modificar'][$cod][$key]['disabled'] = true;
								} else {

									$ingressos_cod = $ingressos['VE_COD'];
									$ingressos_setor = utf8_encode($ingressos['ES_NOME']);
									$ingressos_dia = utf8_encode($ingressos['ED_NOME']);
									$ingressos_data = $ingressos['ED_DATA'];
									$ingressos_tipo = utf8_encode($ingressos['TI_NOME']);
									$ingressos_tipo_tag = $ingressos['TI_TAG'];
									$ingressos_valor = $carrinho['valor'] * $carrinho['qtde'];
									$ingressos_valor = number_format($ingressos_valor,2,",",".");
									$ingressos_valor_exclusividade = $ingressos['VE_VALOR_EXCLUSIVIDADE'];
									
									$ingressos_fila = utf8_encode($ingressos['VE_FILA']);

									$ingresso_indisponivel = ($ingressos_estoque < $carrinho['qtde']);

									if(is_object($ingressos_data)) $ingressos_data = (string) date('Y-m-d', strtotime($ingressos_data->format('Y-m-d')));

									/*if(($ingressos_tipo_tag == 'lounge') && (in_array($ingressos_data, $dias_principais))){
										//Adicionamos na quantidade e excluimos do array
										$loja_qtde_folia++;

										foreach ($dias_principais as $key_dia => $ingressos_dia_atual) {
											if ($ingressos_dia_atual == $ingressos_data) unset($dias_principais[$key_dia]);
										}
										
									}*/

									if(($ingressos_tipo_tag == 'lounge')) {

										/*if($loja_cod <= $combo_dias_limite) {

											//Combo antigo
											if(in_array($ingressos_data, $dias_principais))){
												//Adicionamos na quantidade e excluimos do array
												$loja_qtde_folia++;

												foreach ($dias_principais as $key_dia => $ingressos_dia_atual) {
													if ($ingressos_dia_atual == $ingressos_data) unset($dias_principais[$key_dia]);
												}
											}
											
										} else {*/

											//loja_qtde_combo
											if(count($combo_dias) > 0) {

												// Limite
												$loja_data_limite = (string) date('Y-m-d', strtotime($loja_data->format('Y-m-d')));

												foreach ($combo_dias as $k => $c) {
													//Verificar cada ocorrencia
													// if(in_array($item_data_n, $c['dias'])) {
													// Modificacao por causa da data de compra

													if(in_array($ingressos_data, $c['dias']) && ($loja_data_limite >= $c['limite'][0]) && ($loja_data_limite <= $c['limite'][1])) {

														$loja_qtde_combo[$k] = 1 + ((int) $loja_qtde_combo[$k]);

														//Retiramos do combo o valor encontrado
														foreach ($c['dias'] as $kd => $ingressos_dia_atual) {
															if ($ingressos_dia_atual == $ingressos_data) unset($combo_dias[$k]['dias'][$kd]);
														}
													}									
												}
											}
											
										//}
										
									}

									if($evento > 1) {

										unset($loja_atual_frisa);

										if($ingressos_tipo_tag == 'frisa'){

											$loja_enable_frisa = $loja_atual_frisa = true;
											$loja_frisa_fechadas = floor($carrinho['qtde'] / 6);
											if($loja_frisa_fechadas > 0) $loja_qtde_frisa = $loja_qtde_frisa + $loja_frisa_fechadas;

										}
										
									}

							?>
							<section class="item-carrinho modificar <? if ($ingresso_indisponivel){ echo 'indisponivel'; } if($loja_atual_frisa){ echo ' frisa'; } ?>">
								<header>
									<a href="<? echo SITE; ?>e-compras-modificar-adicionar.php?c=<? echo $key; ?>&cod=<? echo $cod; ?>&a=excluir" class="remover button confirm" title="Deseja remover o ingresso?">Remover</a>

									<input type="hidden" name="valoritem" value="<? echo $carrinho['valor']; ?>" />
									<? echo $ingressos_dia; ?> dia &ndash; Setor <? echo $ingressos_setor; ?> &ndash; 
									<?
										echo $ingressos_tipo;
										if(!empty($ingressos_fila)) { echo " ".$ingressos_fila; }
										if(!empty($ingressos_tipo_especifico)) { echo " ".$ingressos_tipo_especifico; }
										if(($ingressos_vaga > 0) && ($ingressos_tipo_especifico == 'fechado')) { echo " (".$ingressos_vaga." vagas)"; }
									?>

									<p class="quantidade">
										<label for="carrinho-qtde-<? echo $key; ?>">Qtde.</label>
										<input type="text" id="carrinho-qtde-<? echo $key; ?>" name="quantidade[<? echo $key; ?>]" class="input qtde" value="<? echo $carrinho['qtde']; ?>" rel="<? echo $key; ?>" />
										<? if ($ingresso_indisponivel){ ?>
											<span class="aviso"><? echo $ingressos_estoque; ?> disponíve<? echo ($ingressos_estoque==1) ? 'l' : 'is' ; ?></span>
										<? } ?>
									</p>
									<input type="hidden" name="estoque" value="<? echo $ingressos_estoque; ?>" />

									<span class="valor">R$ <? echo $ingressos_valor; ?></span>
								</header>

								<table class="lista compras-adicionais">
									<tbody>
									<?

									//Exclusividade
									if($ingressos_valor_exclusividade > 0) {

										// $carrinho['exclusividade'];
										// $carrinho['exclusividade-val'];
									?>
									<tr <? if ($carrinho['exclusividade']){ echo 'class="checked"'; } ?>>
										<td class="check">
											<input type="hidden" name="valoradicional" class="multi" value="<? echo $ingressos_valor_exclusividade; ?>" />
											<section class="checkbox verify vendas-adicionais">
												<ul>
												<li>
													<label class="item <? if ($carrinho['exclusividade']){ echo 'checked'; } ?>">
													<input type="checkbox" name="exclusividade[<? echo $key; ?>]" value="true" class="adicional" <? if ($carrinho['exclusividade']){ echo 'checked="checked"'; } ?> />
													</label>
												</li>
												</ul>
											</section>
										</td>
										<td class="nome" <? if (($ingressos_tipo_especifico == 'fechado') && ($ingressos_vaga > 0)){ echo 'colspan="2"'; } ?>>Exclusividade - <? echo $ingressos_tipo; ?></td>
										<td class="valor">R$ <? echo number_format($ingressos_valor_exclusividade * $carrinho['qtde'],2,",","."); ?></td>
									</tr>
									<?

									
									$ingressos_complementar = explode("/", $ingressos_fila);
									if(!empty($ingressos_fila) && count($ingressos_complementar) > 0) {

										$ingressos_complementar_default = $ingressos_complementar[0];
										$ingressos_complementar_select = false;

										if($carrinho['exclusividade'] && !empty($carrinho['exclusividade-val'])) {
											$ingressos_complementar_default =  $carrinho['exclusividade-val'];
											$ingressos_complementar_select = true;
										}

									?>
									<tr class="complementar">
										<td>&nbsp;</td>
										<td class="complementar-valor" colspan="<? echo (($ingressos_tipo_especifico == 'fechado') && ($ingressos_vaga > 0)) ? '3' : '2'; ?>">
										<section class="selectbox">
											<a href="#" class="arrow"><strong>Exclusividade na fila <? echo $ingressos_complementar_default; ?></strong><span></span></a>
											<ul class="drop">
												<?
													foreach ($ingressos_complementar as $compk => $value) {
														$checked = ((!$ingressos_complementar_select && ($compk == 0)) || ($ingressos_complementar_select && ($ingressos_complementar_default == $value))) ? true : false;

													?>
													<li>
														<label class="item <? if ($checked){ echo 'checked'; } ?>">
														<input type="radio" name="exclusividadeval[<? echo $key; ?>]" value="<? echo $value; ?>" alt="Exclusividade na fila <? echo $value; ?>" 
														<? if ($checked){ echo 'checked="checked"'; } ?> />
														Exclusividade na fila <? echo $value; ?></label>
													</li>
												<? } ?>
											</ul>
										</section>
										</td>
									</tr>
									<?
									} // count
									}

									$sql_vendas_adicionais = sqlsrv_query($conexao, "SELECT v.*, vv.* FROM vendas_adicionais v, vendas_adicionais_valores vv WHERE vv.VAV_VENDA='$ingressos_cod' AND vv.VAV_ADICIONAL=v.VA_COD AND vv.VAV_BLOCK=0 AND vv.D_E_L_E_T_=0 AND v.VA_BLOCK=0 AND v.D_E_L_E_T_=0 ORDER BY v.VA_COD ASC", $conexao_params, $conexao_options);
									if(sqlsrv_num_rows($sql_vendas_adicionais) !== false) {

										while ($vendas_adicionais = sqlsrv_fetch_array($sql_vendas_adicionais)) {
											$vendas_adicionais_cod = $vendas_adicionais['VA_COD'];
											$vendas_adicionais_tipo = $vendas_adicionais['VA_TIPO'];
											$vendas_adicionais_label = utf8_encode($vendas_adicionais['VA_LABEL']);
											$vendas_adicionais_nome_exibicao = $vendas_adicionais['VA_NOME_EXIBICAO'];
											$vendas_adicionais_nome_insercao = $vendas_adicionais['VA_NOME_INSERCAO'];
											$vendas_adicionais_multi = (bool) $vendas_adicionais['VA_VALOR_MULTI'];

											$vendas_adicionais_opcoes_cod = $vendas_adicionais['VAV_COD'];
											$vendas_adicionais_opcoes_valor_n = $vendas_adicionais['VAV_VALOR'];
											// if($vendas_adicionais_multi) $vendas_adicionais_opcoes_valor_n = $vendas_adicionais_opcoes_valor_n * $carrinho['qtde'];

											$vendas_adicionais_opcoes_valor = ($vendas_adicionais_multi) ?  number_format($vendas_adicionais_opcoes_valor_n * $carrinho['qtde'],2,",",".") : number_format($vendas_adicionais_opcoes_valor_n,2,",",".");
											$vendas_adicionais_opcoes_incluso = (bool) $vendas_adicionais['VAV_INCLUSO'];

											//Verificar se está selecionado
											// $sql_vendas_adicionais_exist = sqlsrv_query($conexao, "SELECT LIA_COD FROM loja_itens_adicionais WHERE LIA_COMPRA='$loja_cod' AND LIA_ITEM='".$carrinho['cod']."' AND LIA_ADICIONAL='$vendas_adicionais_cod' AND LIA_INCLUSO=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
											// $vendas_adicionais_checked = (sqlsrv_num_rows($sql_vendas_adicionais_exist) > 0) ? true : false;

											$sql_vendas_adicionais_exist = sqlsrv_query($conexao, "SELECT LIA_COD FROM loja_itens_adicionais WHERE LIA_COMPRA='$loja_cod' AND LIA_ADICIONAL='$vendas_adicionais_cod' AND D_E_L_E_T_=0  AND LIA_ITEM IN (
												SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO='".$carrinho['item']."' AND D_E_L_E_T_='0')", $conexao_params, $conexao_options);
											$vendas_adicionais_exist = sqlsrv_num_rows($sql_vendas_adicionais_exist);
											$vendas_adicionais_checked = ($vendas_adicionais_exist > 0) ? true : false;

											if($vendas_adicionais_nome_exibicao == 'delivery'){

												if((!$vendas_adicionais_delivery['incluso']) || $vendas_adicionais_opcoes_incluso || ($vendas_adicionais_opcoes_valor_n > $vendas_adicionais_delivery['valorn'])){

													$vendas_adicionais_delivery['incluso'] = $vendas_adicionais_opcoes_incluso;
													$vendas_adicionais_delivery['label'] = $vendas_adicionais_label;
													$vendas_adicionais_delivery['valorn'] = $vendas_adicionais_opcoes_valor_n;
													$vendas_adicionais_delivery['valor'] = $vendas_adicionais_opcoes_valor;
													if($vendas_adicionais_checked) $vendas_adicionais_delivery['checked'] = true;

												}

											} else {

												$vendas_adicionais_transfer = (($vendas_adicionais_nome_exibicao == 'transfer') && ($ingressos_tipo_especifico == 'fechado') && ($ingressos_vaga > 0)) ? true : false;


												// Se for transfer buscar a quantidade;
										?>
										<tr <? /*if($vendas_adicionais_opcoes_incluso) { echo 'class="incluso"'; }*/ if($vendas_adicionais_checked) { echo 'class="checked"'; } ?>>
											<td class="check">
												<? if(!$vendas_adicionais_opcoes_incluso) { ?>
												<input type="hidden" name="valoradicional" <? if ($vendas_adicionais_multi){ echo 'class="multi"'; } ?> value="<? echo $vendas_adicionais_opcoes_valor_n; ?>" />
												<? } ?>
												<section class="checkbox verify vendas-adicionais">
													<ul><li><label class="item <? if($vendas_adicionais_checked) { echo 'checked'; } ?>"><input type="checkbox" name="adicionaiscod[<? echo $key; ?>][]" value="<? echo $vendas_adicionais_cod; ?>" class="adicional" <? if($vendas_adicionais_checked) { echo 'checked="checked"'; } ?> /></label></li></ul>
												</section>
												<? /*} else { ?>
												<input type="hidden" name="adicionaiscod[<? echo $key; ?>][]" value="<? echo $vendas_adicionais_cod; ?>" />
												<? }*/ ?>
											</td>
											<td class="nome <? if($vendas_adicionais_transfer) { echo 'mini'; } ?>" <? if (!$vendas_adicionais_transfer && ($ingressos_tipo_especifico == 'fechado') && ($ingressos_vaga > 0)){ echo 'colspan="2"'; } ?>><? echo $vendas_adicionais_label; ?></td>
											<?
											if($vendas_adicionais_transfer) {
												$ivagasdefault = ($vendas_adicionais_exist > 0) ? $vendas_adicionais_exist : 1;											
											?>
											<td class="transfer">
												<section class="selectbox transfer" id="transfer-qtde-<? echo $carrinho['item']; ?>">
													<a href="#" class="arrow"><strong>1</strong><span></span></a>
													<ul class="drop">
														<? for ($ivagas=1; $ivagas<=$ingressos_vaga; $ivagas++) { ?>
															<li><label class="item <? if ($ivagas == 1){ echo 'checked'; } ?>"><input type="radio" name="transferqtde[<? echo $key; ?>]" value="<? echo $ivagas; ?>" alt="<? echo $ivagas; ?>" <? if ($ivagas == 1){ echo 'checked="checked"'; } ?> /><? echo $ivagas; ?></label></li>
														<? } ?>
													</ul>
												</section>
												<input type="hidden" name="valoradicionaltransfer" value="<? echo $vendas_adicionais_opcoes_valor_n; ?>" />
											</td>
											<?
												//Radiosel para selecionar a quantidade correta
												if($ivagasdefault) {
												?>
												<script type="text/javascript">
													$(document).ready(function(){ $('section#transfer-qtde-<? echo $carrinho["item"]; ?> input[name^="transferqtde"]').radioSel('<? echo $ivagasdefault; ?>'); });
												</script>
												<?
												}
											}
											?>
											<td class="valor"><? echo ($vendas_adicionais_opcoes_incluso) ? 'incluso' : 'R$ '.$vendas_adicionais_opcoes_valor; ?></td>
										</tr>								
										<?
											}

										}
									}
									?>
									</tbody>
								</table>

								<p class="comentarios">
									<label for="carrinho-comentarios-<? echo $key; ?>">Comentários sobre <?
										echo $ingressos_tipo;
										if(!empty($ingressos_fila)) { echo " ".$ingressos_fila; }
										if(!empty($ingressos_tipo_especifico)) { echo " ".$ingressos_tipo_especifico; }
										if(($ingressos_vaga > 0) && ($ingressos_tipo_especifico == 'fechado')) { echo " (".$ingressos_vaga." vagas)"; }
									?>:</label>
									<textarea name="comentarios[<? echo $key; ?>]" class="input" id="carrinho-comentarios-<? echo $key; ?>" rows="3"><?

									//Buscar comentarios
									$sql_item_comentario = sqlsrv_query($conexao, "SELECT TOP 1 LC_COMENTARIO FROM loja_comentarios WHERE LC_COMPRA=$loja_cod ORDER BY LC_COD DESC", $conexao_params, $conexao_options);
									if(sqlsrv_num_rows($sql_item_comentario) > 0){
										$ar_item_comentario = sqlsrv_fetch_array($sql_item_comentario);
										echo utf8_encode($ar_item_comentario['LC_COMENTARIO']);
									}

									?></textarea>
								</p>
								<p class="comentarios interno">
									<label for="carrinho-comentarios-internos-<? echo $key; ?>">Comentários Internos:</label>
									<textarea name="comentariosinternos[<? echo $key; ?>]" class="input" id="carrinho-comentarios-internos-<? echo $key; ?>" rows="3"><?

									//Buscar comentarios
									$sql_item_comentario_interno = sqlsrv_query($conexao, "SELECT TOP 1 LC_COMENTARIO FROM loja_comentarios_internos WHERE LC_COMPRA=$loja_cod ORDER BY LC_COD DESC", $conexao_params, $conexao_options);
									if(sqlsrv_num_rows($sql_item_comentario_interno) > 0){
										$ar_item_comentario_interno = sqlsrv_fetch_array($sql_item_comentario_interno);
										echo utf8_encode($ar_item_comentario_interno['LC_COMENTARIO']);
									}

									?></textarea>
								</p>

							</section>
							<?
								} //Estoque

							}

						}
					}

					if($loja_parceiro != 54) $loja_qtde_folia = 0;

					//if($vendas_adicionais_delivery || ($loja_qtde_folia >= 2) || $loja_enable_frisa) {
						
					//Aqui se encontrava a parte de retirada de ingressos, a foi solicitada que seja removida. Encontra-se no arquivo retirada-ingresso-modificar.php

					//}
					?>
					</section>

					<footer class="controle">
						<input type="submit" class="submit coluna" value="Confirmar" />
						<? if($limpar_carrinho) { ?><a href="<? echo SITE; ?>compras/modificar/<? echo $loja_cod; ?>/limpar/" title="Deseja desfazer as alterações na compra" class="button cancelar-compra desfazer coluna big confirm">Desfazer alterações</a><? } ?>
						<a href="<? echo SITE; ?>financeiro/detalhes/<? echo $cod; ?>/" class="cancel no-cancel coluna">Voltar</a>
						<div class="valor-total">R$ <? echo $loja_valor_total_f; ?></div>
						<div class="clear"></div>
					</footer>

				</form>
			</section>

			<script type="text/javascript">
				$(document).ready(function(){ 
					$('section#compras-origem input[name="origem"]').radioSel('<? echo $loja_origem; ?>');
					$('section#compra-forma-pagamento input[name="forma"]').radioSel('<? echo $loja_forma; ?>');
					$('section#compra-canal-venda input[name="canal"]').radioSel('<? echo $loja_parceiro; ?>');
					//$('section#compra-vendedor-externo input[name="vendedor-externo"]').radioSel('<? echo $loja_concierge; ?>');
				});
			</script>
			<input type="hidden" name="vendedor-externo-checked" value="<? echo $loja_concierge; ?>" />
			<?


			//-----------------------------------------------------------------//

			include('include/footer.php');

			//Fechar conexoes
			include("conn/close.php");
			include("conn/close-sankhya.php");
			
			exit();
		}
	}
}

?>
<script type="text/javascript">
	history.go(-1);
</script>