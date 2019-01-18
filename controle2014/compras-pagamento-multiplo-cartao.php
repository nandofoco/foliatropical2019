<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$item = (int) $_GET['c'];

//-----------------------------------------------------------------//

//Selecionar a compra
$sql_multiplo = sqlsrv_query($conexao, "SELECT TOP 1 * FROM loja_pagamento_multiplo WHERE PM_COD='$item'", $conexao_params, $conexao_options);
if(sqlsrv_num_rows($sql_multiplo) > 0) {

	$multiplo = sqlsrv_fetch_array($sql_multiplo);
	
	$cod = $multiplo['PM_LOJA'];
	$multiplo_valor = $multiplo['PM_VALOR'];
	$multiplo_valor_f = number_format($multiplo_valor, 2, ',','.');

	$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);
	?>
	<section id="conteudo">
		<?
		if(sqlsrv_num_rows($sql_loja) > 0) {

			$loja = sqlsrv_fetch_array($sql_loja);

			$loja_cod = $loja['LO_COD'];
			$loja_cliente = $loja['LO_CLIENTE'];
			$loja_desconto = (bool) $loja['LO_DESCONTO'];

			// $loja_cliente = utf8_encode($loja['CL_NOME']);
			$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

			$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
			$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
			$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));

			$loja_valor_total = $loja['LO_VALOR_TOTAL'];
			$loja_valor_ingressos = $loja['LO_VALOR_INGRESSOS'];
			$loja_valor_adicionais = $loja['LO_VALOR_ADICIONAIS'];
			$loja_valor_total_f = number_format($loja['LO_VALOR_TOTAL'], 2, ',','.');
		?>

		<header class="titulo">
			<h1>Detalhes da <span>Compra</span></h1>

			<div class="valor-total">R$ <? echo $loja_valor_total_f; ?></div>
		</header>
		<section class="padding">
		
			<section class="secao" id="compra-dados">
				<aside><? echo $loja_cod; ?></aside>
				<section>
					<h1><? echo $loja_nome; ?></h1>
					<p><? echo $loja_email; ?></p>
					<p><? echo $loja_telefone; ?></p>
				</section>

				<div class="clear"></div>
			</section>		
			<section id="financeiro-lista" class="secao multiplo label-top">
				<table class="lista">
					<thead>
						<tr>
							<th class="first check"></th>
							<th><strong>Forma de Pagamento</strong></th>
							<th class="right"><strong>Valor (R$)</strong></th>
						</tr>
						<tr class="spacer"><td colspan="3">&nbsp;</td></tr>
					</thead>
					<tbody>
					<?

					$sql_itens = sqlsrv_query($conexao, "SELECT m.*, f.FP_NOME FROM loja_pagamento_multiplo m, formas_pagamento f WHERE m.PM_LOJA='$loja_cod' AND m.PM_FORMA=f.FP_COD AND f.D_E_L_E_T_=0 ORDER BY f.FP_NOME ASC", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_itens) > 0) {
						
						while ($aritem = sqlsrv_fetch_array($sql_itens)) {
							$item_cod = $aritem['PM_COD'];
							$item_forma = utf8_encode($aritem['FP_NOME']);							
							$item_valor = number_format($aritem['PM_VALOR'], 2, ",", ".");

							$item_checked = ($item_cod == $item) ? true : false;

							?>
								<tr <? if($item_checked) { echo 'class="checked"'; } ?>>	
									<td class="first check"></td>
									<td><? echo $item_forma; ?></td>
									<td class="valor"><? echo $item_valor; ?></td>
								</tr>
							<?
						}
					}
					?>
					</tbody>
				</table>
					<div class="clear"></div>
			</section>
			
			<section id="compra-pagamento">
				<section id="cupom-pagamento">
					&nbsp;
				</section>

				<form class="form-padrao controle" id="form-compra-pagamento" action="<? echo SITE; ?>compra/pagamento-multiplo/cartao/post/" method="post">

		        	<input type="submit" class="submit" value="Pagar" />

		        	<section id="compra-cartao" class="radio coluna">
						<!-- <h3>Selecione o cartão de crédito</h3> -->
						<ul>
							<li><label class="item visa"><input type="radio" name="codigoBandeira" value="visa" /></label></li>
							<li><label class="item mastercard"><input type="radio" name="codigoBandeira" value="mastercard" /></label></li>
							<li><label class="item diners"><input type="radio" name="codigoBandeira" value="diners" /></label></li>
							<li><label class="item discover"><input type="radio" name="codigoBandeira" value="discover" /></label></li>
							<li><label class="item elo"><input type="radio" name="codigoBandeira" value="elo" /></label></li>
							<li><label class="item amex"><input type="radio" name="codigoBandeira" value="amex" /></label></li>
							<!-- <li><label class="item aura"><input type="radio" name="codigoBandeira" value="aura" /></label></li>
							<li><label class="item jcb"><input type="radio" name="codigoBandeira" value="jcb" /></label></li> -->
						</ul>
					</section>

		            <section id="compra-forma-pagamento" class="selectbox coluna">
						<a href="#" class="arrow"><strong>Parcelamento</strong><span></span></a>
						<ul class="drop">
							<?

		                    $parcelas = 10;

		                    //Formas de parcelamento
		                    for($p=1;$p<=$parcelas;$p++){
		                    	
			                    $valor_parcela = number_format(($multiplo_valor / $p), 2, ",", ".");
		                    ?>
		                    <li><label class="item"><input type="radio" name="formaPagamento" value="<? echo $p; ?>" alt="<? echo $p; ?>x R$ <? echo $valor_parcela; ?>"><? echo $p; ?>x R$ <? echo $valor_parcela; ?></label></li>
		                    <?
		                    }
		                    ?>
						</ul>
					</section>
		            
		            <div class="clear"></div>

		            <script type="text/javascript">
		            $(document).ready(function(){
		                $("form.form-padrao input[name='formaPagamento']").radioSel('1');
		            });
		            </script>


			        <input type="hidden" name="produto" value="<? echo $multiplo_valor; ?>" />
			        <input type="hidden" name="capturarAutomaticamente" value="false" />
			        <input type="hidden" name="indicadorAutorizacao" value="2" />
			        <input type="hidden" name="tipoParcelamento" value="2" />
			        <input type="hidden" name="compra" value="<? echo $item; ?>" />
			    </form>

			    <div class="clear"></div>

			</section>

		</section>
		<?
		}
		?>	
	</section>
<?

}
//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>