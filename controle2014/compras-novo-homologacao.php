<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//Pagina atual
define('PGCOMPRA', 'true');


$_SESSION['compra-interna'] = array(array('item'=>4,'valor'=>1.00,'qtde'=>1,'disabled'=>0));
$quantidade = array(1);

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
	
	// Criar o form
	$ingressos_valor_total;
	foreach ($_SESSION['compra-interna'] as $carrinho_valor_total) {
		if(!$carrinho_valor_total['disabled']) $ingressos_valor_total += ($carrinho_valor_total['valor'] * $carrinho_valor_total['qtde']);
	}
	if($ingressos_valor_total > 0) $ingressos_valor_total = number_format($ingressos_valor_total,2,",",".");

	?>
	<section id="conteudo">
		<form id="compras-adicionais" method="post" action="<? echo SITE; ?>compras/novo/post/">
			<header class="titulo">
				<h1>Vendas <span>Adicionais</span></h1>

				<div class="valor-total">R$ <? echo $ingressos_valor_total; ?></div>
			</header>						
			
			<input type="hidden" name="homologacao" value="true" />
			<input type="hidden" name="cliente" value="2" />
			<input type="hidden" name="canal" value="8" />
			<input type="hidden" name="forma" value="1" />

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
						DECLARE @vendas TABLE (VE_COD INT, VE_TIPO INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT, VE_FILA INT, VE_VAGAS INT, VE_TIPO_ESPECIFICO VARCHAR(255));
						DECLARE @qtde TABLE (COD INT, QTDE INT DEFAULT 0);

						INSERT INTO @vendas (VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO)
						SELECT VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO FROM vendas WHERE VE_COD=@ingresso AND VE_BLOCK=0 AND D_E_L_E_T_=0;

						INSERT INTO @qtde (COD, QTDE)
						SELECT li.LI_INGRESSO, COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE li.LI_INGRESSO=@ingresso AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 GROUP BY li.LI_INGRESSO;

						SELECT TOP 1 * FROM (SELECT ISNULL(q.QTDE, 0) AS QTDE, v.*, CAST((v.VE_ESTOQUE - ISNULL(q.QTDE, 0)) AS INT) AS TOTAL, t.TI_NOME, d.ED_NOME, s.ES_NOME FROM @vendas v 
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

					if(!($ingressos_estoque > 0)) {
						$_SESSION['compra-interna'][$key]['disabled'] = true;
					} else {

						$ingressos_cod = $ingressos['VE_COD'];
						$ingressos_setor = utf8_encode($ingressos['ES_NOME']);
						$ingressos_dia = utf8_encode($ingressos['ED_NOME']);
						$ingressos_tipo = utf8_encode($ingressos['TI_NOME']);
						$ingressos_valor = $carrinho['valor'] * $carrinho['qtde'];
						$ingressos_valor = number_format($ingressos_valor,2,",",".");
						$ingressos_valor_exclusividade = $ingressos['VE_VALOR_EXCLUSIVIDADE'];
						
						$ingressos_fila = utf8_encode($ingressos['VE_FILA']);
						$ingressos_vaga = utf8_encode($ingressos['VE_VAGAS']);
						$ingressos_tipo_especifico = utf8_encode($ingressos['VE_TIPO_ESPECIFICO']);

						$ingresso_indisponivel = ($ingressos_estoque < $carrinho['qtde']);

				?>
				<section class="item-carrinho <? if ($ingresso_indisponivel){ echo 'indisponivel'; } ?>">
					<header>
						<input type="hidden" name="valoritem" value="<? echo $carrinho['valor']; ?>" />
						<? echo $ingressos_dia; ?> dia &ndash; Setor <? echo $ingressos_setor; ?> &ndash; 
						<?
							echo $ingressos_tipo;
							if(!empty($ingressos_fila)) { echo " ".$ingressos_fila; }
							if(!empty($ingressos_tipo_especifico)) { echo " ".$ingressos_tipo_especifico; }
							if(!empty($ingressos_vaga) && ($ingressos_tipo_especifico == 'fechado')) { echo " (".$ingressos_vaga." vagas)"; }
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
								<input type="hidden" name="valoradicional" value="<? echo $ingressos_valor_exclusividade; ?>" />
								<section class="checkbox verify vendas-adicionais">
									<ul><li><label class="item"><input type="checkbox" name="exclusividade[<? echo $key; ?>]" value="true" class="adicional" /></label></li></ul>
								</section>
							</td>
							<td class="nome">Exclusividade - <? echo $ingressos_tipo; ?></td>
							<td class="valor">R$ <? echo number_format($ingressos_valor_exclusividade,2,",","."); ?></td>
						</tr>
						<?

						$ingressos_complementar = explode("/", $ingressos_fila);
						if(!empty($ingressos_fila) && count($ingressos_complementar) > 0) {

						?>
						<tr class="complementar">
							<td>&nbsp;</td>
							<td class="complementar-valor" colspan="2">
							<section class="selectbox">
								<a href="#" class="arrow"><strong>Exclusividade na fila <? echo $ingressos_complementar[0]; ?></strong><span></span></a>
								<ul class="drop">
									<? foreach ($ingressos_complementar as $compk => $value) { ?>
										<li><label class="item <? if ($compk == 0){ echo 'checked'; } ?>"><input type="radio" name="exclusividadeval[<? echo $ingressos_cod; ?>]" value="<? echo $value; ?>" alt="Exclusividade na fila <? echo $value; ?>" <? if ($compk == 0){ echo 'checked="checked"'; } ?> />Exclusividade na fila <? echo $value; ?></label></li>
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
								if($vendas_adicionais_multi) $vendas_adicionais_opcoes_valor_n = $vendas_adicionais_opcoes_valor_n * $carrinho['qtde'];

								$vendas_adicionais_opcoes_valor = number_format($vendas_adicionais_opcoes_valor_n,2,",",".");
								$vendas_adicionais_opcoes_incluso = (bool) $vendas_adicionais['VAV_INCLUSO'];

								if($vendas_adicionais_nome_exibicao == 'delivery'){

									if((!$vendas_adicionais_delivery['incluso']) || $vendas_adicionais_opcoes_incluso || ($vendas_adicionais_opcoes_valor_n > $vendas_adicionais_delivery['valorn'])){

										$vendas_adicionais_delivery['incluso'] = $vendas_adicionais_opcoes_incluso;
										$vendas_adicionais_delivery['label'] = $vendas_adicionais_label;
										$vendas_adicionais_delivery['valorn'] = $vendas_adicionais_opcoes_valor_n;
										$vendas_adicionais_delivery['valor'] = $vendas_adicionais_opcoes_valor;
									}

								} else {

							?>
							<tr <? if($vendas_adicionais_opcoes_incluso) { echo 'class="incluso"'; } ?>>
								<td class="check">
									<? if(!$vendas_adicionais_opcoes_incluso) { ?>
									<input type="hidden" name="valoradicional" value="<? echo $vendas_adicionais_opcoes_valor_n; ?>" />
									<section class="checkbox verify vendas-adicionais">
										<ul><li><label class="item"><input type="checkbox" name="adicionaiscod[<? echo $key; ?>]" value="<? echo $vendas_adicionais_cod; ?>" class="adicional" /></label></li></ul>
									</section>
									<? } else { ?>
									<input type="hidden" name="adicionaiscod[<? echo $key; ?>]" value="<? echo $vendas_adicionais_cod; ?>" />
									<? } ?>
								</td>
								<td class="nome"><? echo $vendas_adicionais_label; ?></td>
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
						<label for="carrinho-comentarios-<? echo $key; ?>">Comentários:</label>
						<textarea name="comentarios[<? echo $key; ?>]" class="input" id="carrinho-comentarios-<? echo $key; ?>" rows="3"></textarea>
					</p>

				</section>
				<?
					} //Estoque

				}

			}
			
			if($vendas_adicionais_delivery) {
			?>
			<section class="item-carrinho extra">
				<header>Informações extra</header>
				<table class="lista compras-adicionais">
					<tbody>
						<tr <? if($vendas_adicionais_delivery['incluso']) { echo 'class="incluso"'; } ?>>
							<td class="check">
								<? if($vendas_adicionais_delivery['incluso']) { ?>
								<input type="hidden" name="delivery" value="true" />
								<? } else { ?>
								<input type="hidden" name="valoradicional" value="<? echo $vendas_adicionais_delivery['valorn']; ?>" />
								<section class="checkbox verify vendas-adicionais">
									<ul><li><label class="item"><input type="checkbox" name="delivery" value="true" class="adicional" /></label></li></ul>
								</section>
								<? } ?>
							</td>
							<td class="nome"><? echo $vendas_adicionais_delivery['label']; ?></td>
							<td class="valor"><? echo ($vendas_adicionais_delivery['incluso']) ? 'incluso' : 'R$ '.$vendas_adicionais_delivery['valor']; ?></td>
						</tr>								
					</tbody>
				</table>
			</section>
			<?
			}
			?>
			</section>

			<footer class="controle">
				<input type="submit" class="submit coluna" value="Confirmar" />
				<a href="<? echo SITE; ?>compras/novo/" class="cancel no-cancel coluna">Voltar</a>
				<div class="valor-total">R$ <? echo $ingressos_valor_total; ?></div>
				<div class="clear"></div>
			</footer>

		</form>
	</section>
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