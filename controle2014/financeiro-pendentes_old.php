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

$evento = (int) $_SESSION['usuario-carnaval'];

//-----------------------------------------------------------------//

$q = format($_GET['q']);
if(!empty($q)) {

	if(!is_numeric($q)) {
		// $search_query = is_numeric($q) ? " AND CODPARC='$q' " : " AND NOMEPARC LIKE '%$q%' ";
		$search_query = " AND NOMEPARC LIKE '%$q%' ";
		
		$sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC FROM TGFPAR WHERE CLIENTE='S' AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_search) > 0) {
			$ar_cods = array();
			while ($cods = sqlsrv_fetch_array($sql_search)) array_push($ar_cods, $cods['CODPARC']);
			$cods = implode(",", $ar_cods);
			$search = " AND l.LO_CLIENTE IN ($cods) ";
		} else {
			$search = " AND l.LO_CLIENTE IN ('') ";
		}
	} else {
		$search = " AND l.LO_COD='$q' ";
	}
}

$sql_loja = sqlsrv_query($conexao, "SELECT l.*, p.FP_NOME, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, (CONVERT(VARCHAR, l.LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO, ISNULL(DATEDIFF (DAY, l.LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA FROM loja l, formas_pagamento p WHERE l.LO_PAGO='0' AND l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND  l.LO_FORMA_PAGAMENTO=p.FP_COD $search ORDER BY l.LO_DATA_COMPRA DESC", $conexao_params, $conexao_options);
$n_loja = sqlsrv_num_rows($sql_loja);

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
	</header>
	<section class="secao bottom">
		<table class="lista tablesorter">
			<thead>
				<tr>
					<th class="first"><strong>VCH</strong><span></span></th>
					<th><strong>Cliente</strong><span></span></th>
					<th><strong>Data da Compra</strong><span></span></th>
					<th><strong>Itens</strong><span></span></th>
					<th><strong>Forma Pgto</strong><span></span></th>
					<th class="right"><span></span><strong>Valor (R$)</strong></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_loja > 0)	 {

				$i=1;
				while($loja = sqlsrv_fetch_array($sql_loja)) {

					$loja_cod = $loja['LO_COD'];
					$loja_data = $loja['DATA'];
					$loja_cliente_cod = $loja['LO_CLIENTE'];
					$loja_tipo_pagamento = utf8_encode($loja['LO_FORMA_PAGAMENTO']);
					$loja_status_transacao = $loja['LO_STATUS_TRANSACAO'];
					$loja_diferenca_dias = (5 - $loja['DIFERENCA']);
					$cartao_credito = ($loja_tipo_pagamento == 1) ? true : false;
					$faturado = ($loja_tipo_pagamento == 7) ? true : false;
					$reserva = ($loja_tipo_pagamento == 5) ? true : false;

					$loja_forma_pagamento = utf8_encode($loja['FP_NOME']);
					$loja_valor = number_format($loja['LO_VALOR_TOTAL'], 2, ",", ".");				

					unset($loja_cliente);

					// $loja_cliente = utf8_encode($loja['CL_NOME']);
					$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC FROM TGFPAR WHERE CODPARC='$loja_cliente_cod' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_cliente) > 0) {
						$loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);
						$loja_cliente = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
					}

					//buscar itens
					$sql_itens = sqlsrv_query($conexao, "SELECT * FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
					$n_itens = sqlsrv_num_rows($sql_itens);

					$alterar_pagamento = ($cartao_credito && (($loja_status_transacao == 4) && ($loja_diferenca_dias > -1))) ? false : true;
					$alterar_pagamento_classe = '';

					?>
						<tr>	
							<td class="first"><? echo $loja_cod; ?></td>
							<td><? echo $loja_cliente; ?></td>
							<td><? echo $loja_data; ?></td>				
							<td><? echo $n_itens; ?></td>
							<td><? echo $loja_forma_pagamento; ?></td>
							<td class="valor"><? echo $loja_valor; ?></td>
							<td class="ctrl financeiro">
								<section class="selectbox alterar-pagamento <? if ($alterar_pagamento && !$reserva){ echo 'plus'; } ?>">
									<?
									if($cartao_credito) {
										if(($loja_status_transacao == 4) && ($loja_diferenca_dias > -1)) {
										?>
										<a href="<? echo SITE; ?>compra/captura/<? echo $loja_cod; ?>/" class="liberar confirm" title="Confirmar o pagamento da compra <? echo $loja_cod; ?> (<? echo $loja_diferenca_dias; ?> dia<? echo $loja_diferenca_dias != 1 ? 's' : ''; ?> para expirar o prazo de confirmação)?">Confirmar (<? echo $loja_diferenca_dias; ?>)</a>
										<?
										} else {
										?>
										<a href="<? echo SITE; ?>compras/pagamento/<? echo $loja_cod; ?>/" class="liberar pagar confirm" title="Realizar o pagamento da compra <? echo $loja_cod; ?>?">Pagar</a>
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
								<a href="<? echo SITE; ?>financeiro/detalhes/<? echo $loja_cod; ?>/" class="ver"></a>
							</td>
						</tr>
					<?
					$i++;
				}
			} 
			if($n_loja == 0) {
			?>
				<tr>
					<td colspan="7" class="nenhum">Nenhum pagamento pendente.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<? if ($n_loja > 0) { ?>
        <div class="pager-tablesorter">
	        <a href="#" class="first"></a>
	        <a href="#" class="prev"></a>
	        <span class="pagedisplay"></span>
	        <a href="#" class="next"></a>
	        <a href="#" class="last"></a>
	        <input type="hidden" class="pagesize" value="30" />
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