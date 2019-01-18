<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

// echo "<pre>";
// var_dump($_SESSION);
// echo "</pre>";
// exit;

//Pagina atual
define('PGCOMPRA', 'true');

// print_r($_SESSION['compra-interna']);

$evento = (int) $_SESSION['usuario-carnaval'];

$quantidade = $_POST['quantidade'];

if(count($_SESSION['compra-interna']) > 0) {

	//arquivos de layout
	include("include/head.php");
	include("include/header.php");

	// Atualizar a quantidade
	if(count($quantidade) > 0){
		foreach ($quantidade as $key => $value) {
			if($value > 0) $_SESSION['compra-interna'][$key]['qtde'] = $value;
		}
	}

	//-----------------------------------------------------------------------------//
	
	$loja_qtde_folia = 0;
	$loja_qtde_frisa = 0;
	$loja_enable_frisa = false;

	//Novos combos
	$loja_qtde_combo = array();

	// Criar o form
	$ingressos_valor_total;
	foreach ($_SESSION['compra-interna'] as $carrinho_valor_total) {
		if(!$carrinho_valor_total['disabled']) $ingressos_valor_total += ($carrinho_valor_total['valor'] * $carrinho_valor_total['qtde']);
	}
	//if($ingressos_valor_total > 0) $ingressos_valor_total = number_format($ingressos_valor_total,2,",",".");

	if($_GET['teste']) print_r($_SESSION['compra-interna']);


	?>
	<section id="conteudo">
		<form id="compras-adicionais" method="post" action="<? echo SITE; ?>compras/novo/post/">
			<header class="titulo">
				<h1>Vendas <span>Adicionais</span></h1>
				
								
				<?
				//Verifica a exestência de desconto, caso sejam comprados 2 ingressos em dias diferentes

				$sql_desconto = sqlsrv_query($conexao, "SELECT DES_VALOR FROM desconto_folia_tropical", $conexao_params, $conexao_options);
						
				$ar_desconto = sqlsrv_fetch_array($sql_desconto);

				$porcentagem_desconto = $ar_desconto['DES_VALOR'] / 100;	

				if (count($_SESSION['data_ingresso_desconto']) >= 2):
					$desconto_folia = $ingressos_valor_total * $porcentagem_desconto;
					$ingressos_valor_total_atualizado = $ingressos_valor_total - $desconto_folia; 
				?>
				<div class="div-desconto" style="display: inline; float: left;">
					<h3>Desconto de <?=$ar_desconto['DES_VALOR']?>% - Folia Tropical:</h3>

					<input type="hidden" name="desconto_porcentagem" id="desconto_porcentagem" value="<?=$ar_desconto['DES_VALOR']?>">
					<br>
					<h3 class="subtotal">Subtotal:</h3>
				</div>


				<div class="div-desconto-rigth" style="display: inline; float: right;">
					<h3 id="desconto-rigth">- R$ <? echo number_format($desconto_folia,2,",","."); ?>

					<p class="close-desconto"><a href="javascript:void(0)" onclick="zeraDescontoFT()"> <img src="<?=SITE;?>img/close_blue.png"></a></p>
				</h3>

					
					<input type="hidden" name="desconto_input" id="desconto_input" value="<?=$desconto_folia?>">

					<br>
					<h3 class="subtotal-valor">R$ <? echo number_format($ingressos_valor_total_atualizado,2,",","."); ?></h3>

				</div>

				<? endif; ?>
				<div class="valor-total">R$ <? echo number_format($ingressos_valor_total,2,",","."); ?></div>
				<div id="valor-compra" style="display: none;"></div>
				<div id="valor-desconto-frisa" style="display: none;"></div>
				<div id="mostra-desconto-frisa" style="display: none;">true</div>
				<br>
				<br>
			</header>						
			
			<section class="secao cadastro-cliente">

				<section id="compras-origem" class="selectbox coluna modificar">
					<h3>Origem da compra:</h3>
					<a href="#" class="arrow"><strong>Telefone</strong><span></span></a>
					<ul class="drop">
						<li><label class="item checked"><input type="radio" name="origem" value="telefone" alt="Telefone" checked="checked" />Telefone</label></li>
						<li><label class="item"><input type="radio" name="origem" value="balcao" alt="Balcão" />Balcão</label></li>
						<li><label class="item"><input type="radio" name="origem" value="site" alt="Site" />Site</label></li>
						<li><label class="item"><input type="radio" name="origem" value="chatonline" alt="Chat Online" />Chat Online</label></li>
						<li><label class="item"><input type="radio" name="origem" value="email" alt="E-mail" />E-mail</label></li>
					</ul>
				</section>

				<section id="compras-cliente" class="checkbox coluna">
					<ul class="hidden"><li><label class="item"><input type="checkbox" name="cliente" value="" /></label></li></ul>

					<div class="sugestao">
						<p>
							<label for="carrinho-cliente">Cliente:</label>
							<input type="text" id="carrinho-cliente" name="cliente-sugestao" class="input sugestao" />
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
					<input type="text" id="compra-deadline" name="deadline" class="input disabled" disabled="disabled" />
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

				<p class="coluna bottom comissao">
					<label for="compra-comissao">Comissão:</label>
					<input type="text" id="compra-comissao" name="comissao" class="input" value="0" />
				</p>

				<section id="compras-comissao-retida" class="checkbox verify coluna bottom">
					<h3>Retida:</h3>
					<ul>
						<li><label class="item"><input type="checkbox" name="retida" value="true" /></label></li>
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

			foreach ($_SESSION['compra-interna'] as $key => $carrinho) {
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

					if(!($ingressos_estoque > 0)) {
						$_SESSION['compra-interna'][$key]['disabled'] = true;
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

						$ingressos_tipo_cod = $ingressos['VE_TIPO'];
						
						$ingressos_fila = utf8_encode($ingressos['VE_FILA']);

						$ingresso_indisponivel = ($ingressos_estoque < $carrinho['qtde']);

						$ingressos_data = (string) date('Y-m-d', strtotime($ingressos_data->format('Y-m-d')));
						if(($ingressos_tipo_tag == 'lounge')) {

							//Combo antigo
							/*if(in_array($ingressos_data, $dias_principais))){
								//Adicionamos na quantidade e excluimos do array
								$loja_qtde_folia++;

								foreach ($dias_principais as $key_dia => $ingressos_dia_atual) {
									if ($ingressos_dia_atual == $ingressos_data) unset($dias_principais[$key_dia]);
								}
							}*/

							//loja_qtde_combo
							if(count($combo_dias) > 0) {

								// Limite
								$loja_data_limite = (string) date('Y-m-d');

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
				<section class="item-carrinho <? if ($ingresso_indisponivel){ echo 'indisponivel'; } if($loja_atual_frisa){ echo ' frisa'; }  ?>">


					<header>
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
						?>
						<tr>
							<td class="check">
								<input type="hidden" name="valoradicional" class="multi" value="<? echo $ingressos_valor_exclusividade; ?>" />
								<section class="checkbox verify vendas-adicionais">
									<ul><li><label class="item"><input type="checkbox" name="exclusividade[<? echo $key; ?>]" value="true" class="adicional" /></label></li></ul>
								</section>
							</td>
							<td class="nome" <? if (($ingressos_tipo_especifico == 'fechado') && ($ingressos_vaga > 0)){ echo 'colspan="2"'; } ?>>Exclusividade - <? echo $ingressos_tipo; ?></td>
							<td class="valor">R$ <? echo number_format($ingressos_valor_exclusividade * $carrinho['qtde'],2,",","."); ?></td>
						</tr>
						<?

						$ingressos_complementar = explode("/", $ingressos_fila);
						if(!empty($ingressos_fila) && count($ingressos_complementar) > 0) {

						?>
						<tr class="complementar">
							<td>&nbsp;</td>
							<td class="complementar-valor" colspan="<? echo (($ingressos_tipo_especifico == 'fechado') && ($ingressos_vaga > 0)) ? '3' : '2'; ?>">
							<section class="selectbox">
								<a href="#" class="arrow"><strong>Exclusividade na fila <? echo $ingressos_complementar[0]; ?></strong><span></span></a>
								<ul class="drop">
									<? foreach ($ingressos_complementar as $compk => $value) { ?>
										<li><label class="item <? if ($compk == 0){ echo 'checked'; } ?>"><input type="radio" name="exclusividadeval[<? echo $key; ?>]" value="<? echo $value; ?>" alt="Exclusividade na fila <? echo $value; ?>" <? if ($compk == 0){ echo 'checked="checked"'; } ?> />Exclusividade na fila <? echo $value; ?></label></li>
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
								

								if($vendas_adicionais_nome_exibicao == 'delivery'){

									if((($vendas_adicionais_opcoes_valor_n > $vendas_adicionais_delivery['valorn']) || $vendas_adicionais_opcoes_incluso) && (!$vendas_adicionais_delivery['incluso'])){

										$vendas_adicionais_delivery['incluso'] = $vendas_adicionais_opcoes_incluso;
										$vendas_adicionais_delivery['label'] = $vendas_adicionais_label;
										$vendas_adicionais_delivery['valorn'] = $vendas_adicionais_opcoes_valor_n;
										$vendas_adicionais_delivery['valor'] = $vendas_adicionais_opcoes_valor;
									}

								} else {

									$vendas_adicionais_transfer = (($vendas_adicionais_nome_exibicao == 'transfer') && ($ingressos_tipo_especifico == 'fechado') && ($ingressos_vaga > 0)) ? true : false;

							?>
							<tr <? if($vendas_adicionais_opcoes_incluso) { echo 'class="checked"'; } ?>>
								<td class="check">
									<? if(!$vendas_adicionais_opcoes_incluso) { ?>
									<input type="hidden" name="valoradicional" <? if ($vendas_adicionais_multi){ echo 'class="multi"'; } ?> value="<? echo $vendas_adicionais_opcoes_valor_n; ?>" />
									<? } ?>
									<section class="checkbox verify vendas-adicionais">
										<ul><li><label class="item <? if($vendas_adicionais_opcoes_incluso) { echo 'checked'; } ?>"><input type="checkbox" name="adicionaiscod[<? echo $key; ?>][]" value="<? echo $vendas_adicionais_cod; ?>" class="adicional" <? if($vendas_adicionais_opcoes_incluso) { echo 'checked="checked"'; } ?> /></label></li></ul>
									</section>
									<? /*} else { ?>
									<input type="hidden" name="adicionaiscod[<? echo $key; ?>][]" value="<? echo $vendas_adicionais_cod; ?>" />
									<? }*/ ?>
								</td>
								<td class="nome <? if($vendas_adicionais_transfer) { echo 'mini'; } ?>" <? if (!$vendas_adicionais_transfer && ($ingressos_tipo_especifico == 'fechado') && ($ingressos_vaga > 0)){ echo 'colspan="2"'; } ?>><? echo $vendas_adicionais_label; ?></td>
								<? if($vendas_adicionais_transfer) { ?>
								<td class="transfer">
									<section class="selectbox transfer">
										<a href="#" class="arrow"><strong>1</strong><span></span></a>
										<ul class="drop">
											<? for ($ivagas=1; $ivagas<=$ingressos_vaga; $ivagas++) { ?>
												<li><label class="item <? if ($ivagas == 1){ echo 'checked'; } ?>"><input type="radio" name="transferqtde[<? echo $key; ?>]" value="<? echo $ivagas; ?>" alt="<? echo $ivagas; ?>" <? if ($ivagas == 1){ echo 'checked="checked"'; } ?> /><? echo $ivagas; ?></label></li>
											<? } ?>
										</ul>
									</section>
									<input type="hidden" name="valoradicionaltransfer" value="<? echo $vendas_adicionais_opcoes_valor_n; ?>" />
								</td>
								<? } ?>
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
							if(!empty($ingressos_vaga) && ($ingressos_tipo_especifico == 'fechado')) { echo " (".$ingressos_vaga." vagas)"; }
						?>:</label>
						<textarea name="comentarios[<? echo $key; ?>]" class="input" id="carrinho-comentarios-<? echo $key; ?>" rows="2"></textarea>
					</p>
					<p class="comentarios interno">
						<label for="carrinho-comentarios-internos-<? echo $key; ?>">Comentários internos:</label>
						<textarea name="comentariosinternos[<? echo $key; ?>]" class="input" id="carrinho-comentarios-internos-<? echo $key; ?>" rows="2"></textarea>
					</p>

				</section>
				<?
					} //Estoque

				}

			}
			
			//if($vendas_adicionais_delivery || ($loja_qtde_folia >= 2) || $loja_enable_frisa) {
			?>
			<section class="item-carrinho extra" id="carrinho-extra">
				<header>Informações extra</header>
				<table class="lista compras-adicionais">
					<tbody>
						<? if($vendas_adicionais_delivery) { ?>
						<tr <? if($vendas_adicionais_delivery['incluso']) { echo 'class="checked"'; } ?>>
							<td class="check">
								<? /*if($vendas_adicionais_delivery['incluso']) { ?>
								<input type="hidden" name="delivery" value="true" />
								<? } else { ?><input type="hidden" name="valoradicional" value="<? echo $vendas_adicionais_delivery['valorn']; ?>" />*/ ?>
								<section class="checkbox verify vendas-adicionais">
									<ul><li><label class="item <? if($vendas_adicionais_delivery['incluso']) { echo 'checked'; } ?>"><input type="checkbox" name="delivery" value="true" class="adicional" <? if($vendas_adicionais_delivery['incluso']) { echo 'checked="checked"'; } ?> /></label></li></ul>
								</section>
								<? /*}*/ ?>
							</td>
							<td class="nome"><? echo $vendas_adicionais_delivery['label']; ?></td>
							<td class="valor text">
								<?
								//echo ($vendas_adicionais_delivery['incluso']) ? 'incluso' : 'R$ '.$vendas_adicionais_delivery['valor'];
								if($vendas_adicionais_delivery['incluso']) {
									echo 'incluso';
								} else {
									?>
									R$ <input type="text" name="valoradicionaldelivery" class="input" value="<? echo $vendas_adicionais_delivery['valorn']; ?>" />
									<?
								}
								?>
							</td>
						</tr>
						<?
						} //Delivery
						
						//Aqui se encontrava a parte de retirada de ingressos, a foi solicitada que seja removida. Encontra-se no arquivo retirada-ingresso.php //

						$loja_combo_desconto = 0;
						foreach ($loja_qtde_combo as $k => $r) {
							if(($r == $combo_dias[$k]['total']) && ($combo_dias[$k]['desconto'] > $loja_combo_desconto)) {
								$loja_combo_desconto = $combo_dias[$k]['desconto'];
								$loja_combo_nome = $combo_dias[$k]['nome'];
							}
						}

						if($loja_combo_desconto > 0) {

						?>
						<tr class="desconto folia novo checked">
							<? /*<td class="check">
								<input type="hidden" name="folia" value="<? echo $loja_combo_desconto; ?>" class="desconto novo" />
							</td>*/ ?>

							<td class="check">
								<section class="checkbox verify">
									<ul><li><label class="item checked"><input type="checkbox" name="folia" value="<? echo $loja_combo_desconto; ?>" class="desconto novo adicional" checked="checked" /></label></li></ul>
								</section>
							</td>
							<td class="nome"><? echo $loja_combo_nome; ?></td>
							<td class="valor">- <? echo str_replace('.', ',', round($loja_combo_desconto, 1)); ?>%</td>
							<td></td>
						</tr>
						<?
						}

						/*if($loja_qtde_folia >= 2) {
						?>
						<tr class="incluso desconto folia">
							<td class="check">
								<input type="hidden" name="folia" value="true" class="desconto" disabled="disabled" />
							</td>
							<td class="nome">Combo 2 dias na Folia</td>
							<td class="valor">- 10%</td>
						</tr>
						<?
						}*/
						
						if($loja_enable_frisa) {
						?>

						

						<tr class="desconto frisa checked" id="desconto_frisa">
							<? /*<td class="check">
								<input type="hidden" name="frisa" value="true" class="desconto" <? if($loja_qtde_frisa_disabled) { echo 'disabled="disabled"'; } ?> />
							</td>*/ ?>

							<td class="check">
								<section class="checkbox verify">
									<ul><li><label class="item checked"><input type="checkbox" name="frisa" value="true" class="desconto novo adicional" checked="checked" <? if($loja_qtde_frisa_disabled) { echo 'disabled="disabled"'; } ?> /></label></li></ul>
								</section>
							</td>

							<td class="nome">Desconto para Frisa fechada</td>
							<td class="valor">- R$ <? echo number_format(($loja_qtde_frisa * 50), 2, ',', '.'); ?></td>
							<td><a class="desfazer-link" href="javascript:void:0" onclick="removeDescontoFrisa()">x</a></td>
						</tr>
						<p id="desfazer" style="display: none;"><a href="javascript:void:0" onclick="desfazerExclusaoFrisa()">Desfazer</a></p>
						<?
						}

						?>
					</tbody>
				</table>
			</section>
			<?
			//}
			
			?>
			</section>

			<footer class="controle">
				<input type="submit" class="submit coluna" value="Confirmar" />
				<a href="<? echo SITE; ?>compras/novo/" class="cancel no-cancel coluna">Voltar</a>
				<div id="valor-total-rodape">R$ <? echo number_format($ingressos_valor_total_atualizado,2,",","."); ?></div>
				<div class="clear"></div>
			</footer>

		</form>
	</section>

	<script>

		function removeDescontoFrisa() {

			$('#mostra-desconto-frisa').html('false');		

			$('#desfazer').show();

			var $frisa_desconto = $('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.desconto.frisa');

			$frisa_desconto.hide().find('input.desconto').attr('disabled', true);

			var total =  parseFloat($('#valor-compra').html());
			var descontoFrisa =  parseFloat($('#valor-desconto-frisa').html());

			total = total + descontoFrisa;

			total = total.toFixed(2).replace(".",",");

            if(total.length > 6) total = total.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

			$('#valor-total-rodape').html('R$ '+ total);
			$('.valor-total').html('R$ '+ total);

		}

		function desfazerExclusaoFrisa() {

			$('#mostra-desconto-frisa').html('true');	

			$('#desconto_frisa').show();
			$('#desfazer').hide();

			var total = parseFloat($('#valor-compra').html());

			total = total.toFixed(2).replace(".",",");

            if(total.length > 6) total = total.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");


			 $('#valor-total-rodape').html('R$ '+ total);
			 $('.valor-total').html('R$ '+ total);

		}
	</script>
	

	<?
	
	//-----------------------------------------------------------------//


	
	include('include/footer.php');

	//Fechar conexoes
	include("conn/close.php");
	include("conn/close-sankhya.php");

	exit();
}

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>