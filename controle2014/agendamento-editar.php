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
$multiplo = (bool) $_GET['multiplo'];
$cod = (int) $_GET['c'];

//-----------------------------------------------------------------//

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, CONVERT(CHAR, l.LO_DATA_COMPRA, 103) AS DATA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);
?>
<section id="conteudo">
	<header class="titulo">
		<h1>Editar <span>Agendamentos</span></h1>
	</header>
	<section class="padding">
	<?
	if(sqlsrv_num_rows($sql_loja) > 0) {

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_cod = $loja['LO_COD'];
		$loja_cliente = $loja['LO_CLIENTE'];

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

		$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
		$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
		$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));
	?>
		<section class="secao" id="compra-dados">
			<aside><? echo $loja_cod; ?></aside>
			<section>
				<h1><? echo $loja_nome; ?></h1>
				<p><? echo $loja_email; ?></p>
				<p><? echo $loja_telefone; ?></p>
			</section>

			<div class="clear"></div>
		</section>	

		<section id="compra-itens" class="secao">
			<h3>Itens da compra</h3>
			<ul class="itens">
			<?

			$cods_transfer_itens = "''";

			//Selecionar código do transfer
			$sql_transfer = sqlsrv_query($conexao, "SELECT VA_COD FROM vendas_adicionais WHERE (VA_NOME_EXIBICAO='transfer' OR VA_NOME_EXIBICAO='transferinout') AND VA_BLOCK='0' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_transfer) > 0) {
				$transfer_cod = array();
				while($ar_transfer = sqlsrv_fetch_array($sql_transfer)) array_push($transfer_cod, $ar_transfer['VA_COD']);
				$transfer_cod = implode(",", $transfer_cod);
				
				//Selecionar somente os que tem transfer
				$sql_cods_transfer = sqlsrv_query($conexao, "SELECT LIA_ITEM FROM loja_itens_adicionais WHERE LIA_COMPRA='$loja_cod' AND LIA_ADICIONAL IN ($transfer_cod) AND LIA_BLOCK='0' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_cods_transfer) > 0) {
					$ar_cods_transfer = array();
					while($cods_transfer = sqlsrv_fetch_array($sql_cods_transfer)) array_push($ar_cods_transfer, $cods_transfer['LIA_ITEM']);
					$cods_transfer_itens = implode(",", $ar_cods_transfer);
				}

			}

			$sql_itens = sqlsrv_query($conexao, "SELECT li.*, t.TI_NOME, t.TI_TAG, v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_NOME, ed.ED_DATA FROM loja_itens li, vendas v, eventos_setores es, eventos_dias ed, tipos t WHERE li.LI_COMPRA='$loja_cod' AND li.LI_COD IN ($cods_transfer_itens) AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND t.TI_COD=v.VE_TIPO AND li.D_E_L_E_T_='0' ORDER BY LI_COD ASC", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_itens) > 0) {
				$i = 1;
				while ($item = sqlsrv_fetch_array($sql_itens)) {
					$item_cod = $item['LI_COD'];
					$item_id = $item['LI_ID'];
					$item_nome = utf8_encode($item['LI_NOME']);
					$item_dia = utf8_encode($item['ED_NOME']);
					$item_data = $item['ED_DATA'];
					$item_setor = $item['ES_NOME'];
					$item_tipo = utf8_encode($item['TI_NOME']);
					$item_tipo_tag = utf8_encode($item['TI_TAG']);

					$item_fila = utf8_encode($item['VE_FILA']);
					$item_vaga = utf8_encode($item['VE_VAGAS']);
					$item_tipo_especifico = utf8_encode($item['VE_TIPO_ESPECIFICO']);

					$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;
					unset($search);

					if($item_tipo_tag == 'camarote') { $tipo_roteiro = 4; }
					elseif(is_numeric($item_setor) && ($item_setor%2 == 0)) { $tipo_roteiro = 1; } 
					elseif(is_numeric($item_setor) && ($item_setor%2 != 0)) { $tipo_roteiro = 2; } 
					elseif($item_setor == 'FT') { 
						$tipo_roteiro = 3;

						$item_data = (string) date('Y-m-d', strtotime($item_data->format('Y-m-d')));
						
						//Terrasse
						if($item_data == '2015-02-14') $search = " AND RO_COD='43' ";
						else $search = " AND RO_COD<>'43' ";

							
					}

					//busca agendamento do item
					$sql_agendamento = sqlsrv_query($conexao, "SELECT ta.*, th.*, tr.*, ro.* FROM transportes_agendamento ta, transportes_horarios th, transportes tr, roteiros ro WHERE ta.TA_ITEM='$item_cod' AND ta.TA_HORARIO=th.TH_COD AND th.TH_TRANSPORTE=tr.TR_COD AND tr.TR_ROTEIRO=ro.RO_COD AND ta.D_E_L_E_T_='0'", $conexao_params, $conexao_options);
					$n_agendamento = sqlsrv_num_rows($sql_agendamento);
					if($n_agendamento > 0) {
						$agendamento = sqlsrv_fetch_array($sql_agendamento);

						$agendamento_cod = $agendamento['TA_COD'];
						$agendamento_roteiro = $agendamento['RO_COD'];						
						$agendamento_horario = $agendamento['TH_COD'];						
						$agendamento_transporte = $agendamento['TR_COD'];						

						//buscar locais
						$sql_transportes = sqlsrv_query($conexao, "SELECT * FROM transportes WHERE TR_ROTEIRO='$agendamento_roteiro' AND TR_BLOCK='0' AND D_E_L_E_T_='0' ORDER BY TR_COD ASC", $conexao_params, $conexao_options);
						$n_transportes = sqlsrv_num_rows($sql_transportes);

						//buscar horarios
						$sql_horarios = sqlsrv_query($conexao, "SELECT *, SUBSTRING(CONVERT(CHAR, TH_HORA, 8), 1, 5) AS HORA FROM transportes_horarios WHERE TH_TRANSPORTE='$agendamento_transporte' AND TH_BLOCK='0' AND D_E_L_E_T_='0' ORDER BY TH_HORA ASC", $conexao_params, $conexao_options);
						$n_horarios = sqlsrv_num_rows($sql_horarios);
						?>						
						<script type="text/javascript">
							$(document).ready(function(){
								$(".selectbox#item-roteiro-<? echo $item_cod; ?>").find("input[name='item-roteiro']").radioSel('<? echo $agendamento_roteiro; ?>');
								$(".selectbox#item-transporte-<? echo $item_cod; ?>").find("input[name='item-transporte']").radioSel('<? echo $agendamento_transporte; ?>');
								$(".selectbox#item-horario-<? echo $item_cod; ?>").find("input[name='item-horario']").radioSel('<? echo $agendamento_horario; ?>');
								$(".selectbox#item-roteiro-<? echo $item_cod; ?>").removeClass("start");                         
    							$(".selectbox#item-transporte-<? echo $item_cod; ?>").removeClass("start");												
							});
						</script>
						<?
					}
			?>					
					<li class="lista-itens <? if($n_agendamento > 0) echo 'agendado'; ?>">						
						<!-- <span class="dia"><? echo $item_dia; ?></span>
						<span class="setor"><? echo $item_setor; ?></span> -->

						<div class="info">
							<? echo $item_tipo; ?>
							<div class="mais <? if($item_fechado) { echo 'big'; } ?>">
								<? if($item_fechado) { ?><span class="vch"><? echo $item_cod; ?>/<? echo $item_id; ?> &ndash;</span><? } ?>
								<span class="dia"><? echo $item_dia; ?> dia</span>
								<span class="setor" title="Setor <? echo $item_setor; ?>"><? echo $item_setor; ?></span>
							</div>
						</div>
						<form method="post" id="agendamento-<? echo $item_cod; ?>" action="<? echo SITE; ?>e-agendamento.php">
							<input type="hidden" name="loja" value="<? echo $cod; ?>" />
							<input type="hidden" name="multiplo" value="<? echo $multiplo; ?>" />
							<input type="hidden" name="cod" value="<? echo $item_cod; ?>" />
							
							<?
							if($n_agendamento > 0) {
							?>
								<input type="hidden" name="editar" value="true" />
								<input type="hidden" name="agendamento" value="<? echo $agendamento_cod; ?>" />
							<?
							}
							?>
							<p class="coluna">
								<label for="item-nome-<? echo $i; ?>" class="infield">Nome</label>
								<input type="text" name="item-nome" class="input nome" id="item-nome-<? echo $i; ?>" value="<? echo $item_nome; ?>" />
							</p>
							<section class="selectbox coluna roteiro <? if($n_agendamento > 0) echo 'start'; ?>" id="item-roteiro-<? echo $item_cod; ?>">
								<a href="#" class="arrow"><strong>Roteiro</strong><span></span></a>
								<ul class="drop">
								<?
								$sql_roteiros = sqlsrv_query($conexao, "SELECT * FROM roteiros WHERE RO_BLOCK='0' AND RO_TIPO='$tipo_roteiro' AND D_E_L_E_T_='0' $search  ORDER BY RO_NOME ASC");
								if($sql_roteiros > 0) {
									while ($roteiro = sqlsrv_fetch_array($sql_roteiros)) {
										$roteiro_cod = $roteiro['RO_COD'];
										$roteiro_nome = utf8_encode($roteiro['RO_NOME']);
								?>										
										<li><label class="item"><input type="radio" name="item-roteiro" value="<? echo $roteiro_cod; ?>" alt="<? echo $roteiro_nome; ?>" /><? echo $roteiro_nome; ?></label></li>
								<?
									}
								}
								?>
								</ul>
							</section>
							<section class="selectbox coluna transporte <? if($n_agendamento > 0) echo 'start'; ?>" id="item-transporte-<? echo $item_cod; ?>">
								<a href="#" class="arrow"><strong>Local</strong><span></span></a>
								<ul class="drop">
								<?
								if($n_transportes > 0) {
									while ($transportes = sqlsrv_fetch_array($sql_transportes)) {
										$transportes_cod = $transportes['TR_COD'];
										$transportes_nome = utf8_encode($transportes['TR_NOME']);
								?>							
										<li><label class="item"><input type="radio" name="item-transporte" value="<? echo $transportes_cod; ?>" alt="<? echo $transportes_nome; ?>" /><? echo $transportes_nome; ?></label></li>
								<?
									}
								}
								?>
								</ul>
							</section>
							<section class="selectbox coluna horario" id="item-horario-<? echo $item_cod; ?>">
								<a href="#" class="arrow"><strong>Horário</strong><span></span></a>
								<ul class="drop">
									<?
									if($n_horarios > 0) {
										while ($horarios = sqlsrv_fetch_array($sql_horarios)) {
											$horarios_cod = $horarios['TH_COD'];
											$horarios_hora = $horarios['HORA'];
									?>										
											<li><label class="item"><input type="radio" name="item-horario" value="<? echo $horarios_cod; ?>" alt="<? echo $horarios_hora; ?>" /><? echo $horarios_hora; ?></label></li>
									<?
										}
									}
									?>
								</ul>
							</section>
							<input type="submit" class="submit adicionar coluna" value="OK">
							<div class="clear"></div>
						</form>
					</li>
					<?
					$i++;
				}
			}
				?>
				<div class="clear"></div>
			</ul>
		</section>
		<footer class="controle">
			<a href="<? echo SITE; ?>agendamentos/" class="cancel coluna">Voltar</a>
			<? if($multiplo) { ?><a href="<? echo SITE; ?>pagamento-multiplo/<? echo $cod; ?>/" class="cancel coluna">Pagamento</a><? } ?>
			<div class="clear"></div>
		</footer>

	<?
	}
	?>	
	</section>	
</section>
<script type="text/javascript">
$(document).ready(function(){
	$("form#roteiro-novo").find("input[name='tipo']").radioSel('<? echo $loja_tipo; ?>');
});
</script>
<?

//-----------------------------------------------------------------//

include('include/footer.php');


//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>