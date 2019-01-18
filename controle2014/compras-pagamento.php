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
$cod = (int) $_GET['c'];

//-----------------------------------------------------------------//

$parcelas = array(3);

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
		<section id="financeiro-lista" class="secao label-top">
			<table class="lista">
				<thead>
					<tr>
						<? /*<th class="first"><strong>VCH</strong></th>*/ ?>
						<th class="first"><strong>Cliente</strong></th>
						<th><strong>Tipo</strong></th>
						<th><strong>Dia</strong></th>
						<th><strong>Setor</strong></th>
						<th class="right"><strong>Valor (R$)</strong></th>
					</tr>
					<tr class="spacer"><td colspan="5">&nbsp;</td></tr>
				</thead>
				<tbody>
				<?

				$item_count = 1;
				
				$sql_itens = sqlsrv_query($conexao, "SELECT li.*, v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME, tp.TI_TAG FROM loja_itens li, vendas v, eventos_setores es, eventos_dias ed, tipos tp WHERE li.LI_COMPRA='$loja_cod' AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD AND li.D_E_L_E_T_='0' ORDER BY LI_COD ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_itens) > 0) {
					$i = 1;
					while ($item = sqlsrv_fetch_array($sql_itens)) {
						$item_cod = $item['LI_COD'];
						$item_id = $item['LI_ID'];
						$item_nome = utf8_encode($item['LI_NOME']);
						$item_tipo = utf8_encode($item['TI_NOME']);
						$item_tipo_tag = $item['TI_TAG'];
						$item_dia = utf8_encode($item['dia']);
						$item_setor = $item['ES_NOME'];
						$item_valor = number_format($item['LI_VALOR'], 2, ",", ".");

						$item_data_n = $item['ED_DATA'];
						$item_data_n = (string) date('Y-m-d', strtotime($item_data_n->format('Y-m-d')));

						$item_fila = utf8_encode($item['VE_FILA']);
						$item_vaga = utf8_encode($item['VE_VAGAS']);
						$item_tipo_especifico = utf8_encode($item['VE_TIPO_ESPECIFICO']);

						$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;

						if(!$item_fechado) $item_count = 1;

						switch($item_tipo_tag) {
							case 'lounge':
								if(in_array($item_data_n, $dias_candybox)) array_push($parcelas, 3);
								else array_push($parcelas, 10);
							break;
							case 'arquibancada':
								array_push($parcelas, 6);
							break;
							case 'frisa':
								array_push($parcelas, 10);
							break;
							case 'camarote':
								array_push($parcelas, 10);
							break;

						}

						?>
							<tr>	
								<? /*<td class="first"><? echo $loja_cod."/".$item_id; ?></td>*/ ?>
								<td class="first"><? echo $item_nome; ?></td>
								<td>
									<?
									echo $item_tipo;
									if(!empty($item_fila)) { echo " ".$item_fila; }
									if(!empty($item_tipo_especifico)) { echo " ".$item_tipo_especifico; }
									if($item_fechado) { echo " (".$item_id."/".$item_vaga.")"; }
									?>
								</td>
								<td><? echo $item_dia; ?></td>
								<td><? echo $item_setor; ?></td>
								<td class="valor"><? echo (!$item_fechado || ($item_fechado && ($item_count == 1))) ? $item_valor : '--'; ?></td>
							</tr>
						<?
						$i++;
						if($item_fechado) $item_count++;
					}
				}
				?>
				</tbody>
			</table>
				<div class="clear"></div>
		</section>
		
		<?

		//Verificar a existencia de cupom de desconto para essa compra
		$sql_exist_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_COMPRA='$loja_cod' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='1' ", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_exist_cupom) > 0) {

			$cupom_utilizado = true;
			$cupom = sqlsrv_fetch_array($sql_exist_cupom);

			$cupom_cod = $cupom['CP_COD'];
			$cupom_nome = utf8_encode($cupom['CP_NOME']);
			$cupom_codigo = $cupom['CP_CUPOM'];
			$cupom_valor = $cupom['CP_DESCONTO'];
			$cupom_tipo = $cupom['CP_TIPO'];

		} else {

			//Verificar a existencia de cupom de desconto
			if($_SESSION['compra-cupom']['usuario'] == $loja_cliente) {
				
				$cupom_cod = $_SESSION['compra-cupom']['cod'];
				$cupom_delete = true;

				$sql_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_COD='$cupom_cod' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='0' AND CP_DATA_VALIDADE >= GETDATE() ", $conexao_params, $conexao_options);
				$n_cupom = sqlsrv_num_rows($sql_cupom);

				if($n_cupom > 0) {

					$cupom = sqlsrv_fetch_array($sql_cupom);

					$cupom_cod = $cupom['CP_COD'];
					$cupom_nome = utf8_encode($cupom['CP_NOME']);
					$cupom_codigo = $cupom['CP_CUPOM'];
					$cupom_valor = $cupom['CP_DESCONTO'];
					$cupom_tipo = $cupom['CP_TIPO'];

					// 1 Porcentagem
					// 2 Valor

					$_SESSION['compra-cupom']['usuario'] = $loja_cliente;
					$_SESSION['compra-cupom']['cod'] = $cupom_cod;
					$_SESSION['compra-cupom']['compra'] = $cod;

					switch ($cupom_tipo) {
						case 1:
							$loja_valor_ingressos = $loja_valor_ingressos - (($cupom_valor * $loja_valor_ingressos) / 100);
						break;
						
						case 2:
							if($loja_valor_ingressos >= $cupom_valor) $loja_valor_ingressos = $loja_valor_ingressos - $cupom_valor;
							else unset($_SESSION['compra-cupom'], $cupom_cod);
						break;
					}
					
					$loja_valor_total = $loja_valor_ingressos + $loja_valor_adicionais;

					//Total formatado
					$loja_valor_total_f = number_format($loja_valor_total, 2, ',','.');
				}

			}
		}

		?>
		<section id="compra-pagamento">
			<section id="cupom-pagamento">
				<? if (!$loja_desconto){ ?>
					<? if ($cupom_cod > 0){ ?>					
						<span class="cupom">
							<? echo $cupom_nome; ?> <? if (!(0 === strpos($cupom_codigo, 'FOLIA'))) { ?>• Desconto de  <? echo ($cupom_tipo == 1) ? round($cupom_valor)."%" : 'R$ '.number_format($cupom_valor, 2, ',', '.'); } ?>
							<? if ($cupom_delete){ ?><a href="<? echo SITE; ?>compras/pagamento/cupom/remover/<? echo $cupom_cod; ?>/<? echo $cod; ?>/" class="excluir confirm" title="Deseja remover o cupom &rdquo;<? echo $cupom_nome; ?>&ldquo;">&times;</a><? } ?>
						</span>
					<? } else { ?>
					<form class="controle" id="form-cupom-pagamento" action="<? echo SITE; ?>compras/pagamento/cupom/" method="post">
						<input type="hidden" name="cod" value="<? echo $cod; ?>" />
						<input type="hidden" name="cliente" value="<? echo $loja_cliente; ?>" />
						<p>
							<label class="infield" for="compra-cupom">Cupom de desconto:</label>
							<input type="text" name="cupom" class="input" id="compra-cupom" />
							<input type="submit" class="submit adicionar" value="Ok" />
						</p>
					</form>
					<? } ?>
				<? } else { ?>
				&nbsp;
				<? } ?>
			</section>

			<form class="form-padrao controle" id="form-compra-pagamento" action="<? echo SITE; ?>compra/post/" method="post">

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

	                    $parcelas = max($parcelas);

	                    //Formas de parcelamento
	                    for($p=1;$p<=$parcelas;$p++){
	                    	
		                    $valor_parcela = number_format(($loja_valor_total / $p), 2, ",", ".");
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


		        <input type="hidden" name="produto" value="<? echo $loja_valor_total; ?>" />
		        <input type="hidden" name="capturarAutomaticamente" value="false" />
		        <input type="hidden" name="indicadorAutorizacao" value="2" />
		        <input type="hidden" name="tipoParcelamento" value="2" />
		        <input type="hidden" name="compra" value="<? echo $cod; ?>" />
		    </form>

		    <div class="clear"></div>

		</section>

	</section>
	<?
	}
	?>	
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>