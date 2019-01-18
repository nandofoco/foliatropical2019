<?

//Incluir funções básicas
include("include/includes.php");

//conexao Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

//Pagina atual
//define('PGATUAL', 'ingressos/compra/novo/');

$cod = (int) $_GET['c'];

if(!empty($cod)) {
	
	$evento = (int) $_SESSION['usuario-carnaval'];

	$sql_ingresso = sqlsrv_query($conexao, "SELECT TOP 1 c.*, t.TI_TAG FROM compras c, tipos t WHERE c.CO_COD='$cod' AND c.CO_EVENTO='$evento' AND c.D_E_L_E_T_=0 AND t.TI_COD=c.CO_TIPO AND t.D_E_L_E_T_=0", $conexao_params, $conexao_options);
	$n_ingresso = sqlsrv_num_rows($sql_ingresso);

	if($n_ingresso > 0) {

		$ingresso = sqlsrv_fetch_array($sql_ingresso);

		$ingresso_grupo = $ingresso['CO_GRUPO'];
		$ingresso_evento = $ingresso['CO_EVENTO'];
		$ingresso_tipo = $ingresso['CO_TIPO'];
		$ingresso_dia = $ingresso['CO_DIA'];
		$ingresso_setor = $ingresso['CO_SETOR'];
		$ingresso_fornecedor = $ingresso['CO_FORNECEDOR'];
		$ingresso_valor = number_format($ingresso['CO_VALOR'],2,",",".");
		
		$tipo_ingresso = $ingresso['TI_TAG'];
		$setor_ingresso = $ingresso['CO_SETOR'];
		$ingresso_tipo_especifico = utf8_encode($ingresso['CO_TIPO_ESPECIFICO']);

?>
<section id="conteudo">
	<form id="ingresso-compra-novo" method="post" action="<? echo SITE; ?>ingressos/compra/editar/post/">

		<input type="hidden" name="cod" value="<? echo $cod; ?>" />

		<header class="titulo">
			<h1>Ingresso Compra <span>Editar</span></h1>
		</header>
		
		<? include('include/secao-tipo-setor.php'); ?>

		<section class="secao label-top">
			<section class="radio infield dias coluna">
				<h3>Selecione os dias</h3>
				<ul>
				<?

				$sql_eventos_dias = sqlsrv_query($conexao, "SELECT ED_COD, ED_NOME, SUBSTRING(CONVERT(CHAR(8), ED_DATA, 103), 1, 5) AS DATA, DATEPART(WEEKDAY, ED_DATA) AS SEMANA FROM eventos_dias WHERE ED_EVENTO='$evento' AND D_E_L_E_T_=0 ORDER BY ED_DATA ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_eventos_dias)){

					while ($ar_eventos_dias = sqlsrv_fetch_array($sql_eventos_dias)) {
						
						$eventos_dias_cod = $ar_eventos_dias['ED_COD'];
						$eventos_dias_nome = utf8_encode($ar_eventos_dias['ED_NOME']);
						$eventos_dias_data = $ar_eventos_dias['DATA'];
						$eventos_dias_semana = $semana_min[($ar_eventos_dias['SEMANA'] - 1)];
						
					?>
					<li>
						<label class="item <? if($ingresso_dia == $eventos_dias_cod){ echo 'checked'; } ?>"><input type="radio" name="dia" value="<? echo $eventos_dias_cod; ?>" <? if($ingresso_dia == $eventos_dias_cod){ echo 'checked="checked"'; } ?> />
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

			<?

			// Selecionar opcoes
			$sql_compras_opcoes = sqlsrv_query($conexao, "SELECT * FROM compras_opcoes WHERE CP_TIPO='$tipo_ingresso' AND D_E_L_E_T_=0 ORDER BY CP_ORDEM ASC", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_compras_opcoes) > 0) {

				while ($compras_opcoes = sqlsrv_fetch_array($sql_compras_opcoes)) {
					
					$compras_opcoes_cod = $compras_opcoes['CP_COD'];
					$compras_opcoes_label = utf8_encode($compras_opcoes['CP_LABEL']);
					$compras_opcoes_nome_exibicao = $compras_opcoes['CP_NOME_EXIBICAO'];
					$compras_opcoes_nome_insercao = $compras_opcoes['CP_NOME_INSERCAO'];
					$compras_opcoes_modelo = $compras_opcoes['CP_MODELO'];
					$compras_opcoes_valores = $compras_opcoes['CP_VALORES'];
					$compras_opcoes_tamanho = $compras_opcoes['CP_TAMANHO'];
					$compras_opcoes_acao = $compras_opcoes['CP_ACAO'];

					switch ($compras_opcoes_modelo) {
						case 'checkbox-outfield':
						case 'checkbox-infield':
						case 'radio-infield':
						case 'radio-outfield':

							$compras_opcoes_valores = json_decode($compras_opcoes_valores);

							$compras_opcoes_tipo = (preg_match("/^radio/", $compras_opcoes_modelo)) ? 'radio' : 'checkbox';
							$compras_opcoes_position = (preg_match("/infield$/", $compras_opcoes_modelo)) ? 'infield' : 'outfield';

							?>
							<section id="<? echo $tipo; ?>-opcao-<? echo $compras_opcoes_nome_exibicao; ?>" class="<? echo $compras_opcoes_tipo; ?> <? echo $compras_opcoes_position; ?> compras-opcoes coluna">
								<h3><? echo $compras_opcoes_label; ?></h3>
								<ul>
								<?
								foreach ($compras_opcoes_valores as $key => $value) {
									
									$compras_opcoes_valores_checked = ($value == $ingresso[$compras_opcoes_nome_insercao]) ? true : false;
									if(($tipo == 'arquibancada') && ($compras_opcoes_nome_exibicao == 'numerada') && $compras_opcoes_valores_checked) {
										$compras_opcoes_numerada = true;
										unset($compras_opcoes_valores_checked);
									}

									?>
									<li>
										<label class="item <? if($compras_opcoes_valores_checked) { echo 'checked'; } ?>">
											<input type="<? echo $compras_opcoes_tipo; ?>" name="<? echo $compras_opcoes_nome_exibicao; if(($compras_opcoes_tipo == 'checkbox') && count($compras_opcoes_valores) > 1) echo '[]'; ?>" value="<? echo $value; ?>" <? if($compras_opcoes_valores_checked) { echo 'checked="checked"'; } ?> />
											<? if ($compras_opcoes_position == 'infield'){ echo $key; } ?>
										</label>
									</li>
								<? } ?>
								</ul>
							</section>
							<?
						
						break;

						case 'selectbox':

							$compras_opcoes_valores = json_decode($compras_opcoes_valores);
							
							?>
							<section id="<? echo $tipo; ?>-opcao-<? echo $compras_opcoes_nome_exibicao; ?>" class="selectbox compras-opcoes coluna">
								<h3><? echo $compras_opcoes_label; ?></h3>
								<a href="#" class="arrow"><strong><? echo ($compras_opcoes_tamanho == 'small') ? 'Sel.' : 'Selecione'; ?></strong><span></span></a>
								<ul class="drop">
									<? foreach ($compras_opcoes_valores as $key => $value) { ?>									
									<li>
										<label class="item <? if($value == $ingresso[$compras_opcoes_nome_insercao]) { echo 'checked'; } ?>">
										<input type="radio" name="<? echo $compras_opcoes_nome_exibicao; ?>" value="<? echo $value; ?>" alt="<? echo utf8_encode($key); ?>" <? if($value == $ingresso[$compras_opcoes_nome_insercao]) { echo 'checked="checked"'; } ?> /><? echo utf8_encode($key); ?>
										</label>
									</li>
									<? } ?>
								</ul>
							</section>
							<?

						break;

						case 'input':

							if(($compras_opcoes_nome_exibicao == 'vagas') && ($ingresso_compras_range_vagas > 0)) $ingresso[$compras_opcoes_nome_insercao] = $ingresso_compras_range_vagas;

							?>
							<p id="<? echo $tipo; ?>-opcao-<? echo $compras_opcoes_nome_exibicao; ?>" class="coluna compras-opcoes">
								<label for="ingresso-<? echo $compras_opcoes_nome_exibicao; ?>"><? echo $compras_opcoes_label; ?></label>
								<input type="text" name="<? echo $compras_opcoes_nome_exibicao; ?>" class="input" id="ingresso-<? echo $compras_opcoes_nome_exibicao; ?>" value="<? echo (!empty($ingresso[$compras_opcoes_nome_insercao])) ? $ingresso[$compras_opcoes_nome_insercao] : $compras_opcoes_valores; ?>" />
							</p>
							<?

						break;

						case 'range':
							
							if(!empty($compras_opcoes_valores)) $compras_opcoes_valores = json_decode($compras_opcoes_valores);

							//echo $ingresso[$compras_opcoes_nome_insercao];
							
							//Selecionar range
							$ingressos_valores_tipo = is_null($ingresso['CO_TIPO']) ? " AND CO_TIPO IS NULL " : " AND CO_TIPO='".$ingresso['CO_TIPO']."'";
							$ingressos_valores_setor = is_null($ingresso['CO_SETOR']) ? " AND CO_SETOR IS NULL " : " AND CO_SETOR='".$ingresso['CO_SETOR']."'";
							$ingressos_valores_dia = is_null($ingresso['CO_DIA']) ? " AND CO_DIA IS NULL " : " AND CO_DIA='".$ingresso['CO_DIA']."'";
							$ingressos_valores_fila = is_null($ingresso['CO_FILA']) ? " AND CO_FILA IS NULL " : " AND CO_FILA='".$ingresso['CO_FILA']."'";
							$ingressos_valores_nivel = is_null($ingresso['CO_NIVEL']) ? " AND CO_NIVEL IS NULL " : " AND CO_NIVEL='".$ingresso['CO_NIVEL']."'";
							$ingressos_valores_valor = is_null($ingresso['CO_VALOR']) ? " AND CO_VALOR IS NULL " : " AND CO_VALOR='".$ingresso['CO_VALOR']."'";


							$sql_ingresso_compras_range = sqlsrv_query($conexao, "SELECT 
								MIN ($compras_opcoes_nome_insercao) OVER () AS DE, 
								MAX ($compras_opcoes_nome_insercao) OVER () AS ATE, 
								COUNT(CO_COD) AS VAGAS 
								FROM compras 
								WHERE CO_EVENTO='$evento' AND D_E_L_E_T_=0 
								$ingressos_valores_tipo $ingressos_valores_setor $ingressos_valores_dia $ingressos_valores_fila $ingressos_valores_nivel $ingressos_valores_valor 
								GROUP BY $compras_opcoes_nome_insercao", $conexao_params, $conexao_options);

							if(sqlsrv_num_rows($sql_ingresso_compras_range)){
								while($ingresso_compras_range = sqlsrv_fetch_array($sql_ingresso_compras_range)) {
									$ingresso_compras_range_de = $ingresso_compras_range['DE'];
									$ingresso_compras_range_ate = $ingresso_compras_range['ATE'];
									$ingresso_compras_range_vagas = $ingresso_compras_range['VAGAS'];
								}
							}


							?>
							<p id="<? echo $tipo; ?>-opcao-<? echo $compras_opcoes_nome_exibicao; ?>-de" class="coluna compras-opcoes">
								<label for="ingresso-<? echo $compras_opcoes_nome_exibicao; ?>-de"><? echo $compras_opcoes_label; ?> de:</label>
								<input type="text" name="<? echo $compras_opcoes_nome_exibicao; ?>[de]" class="input" id="ingresso-<? echo $compras_opcoes_nome_exibicao; ?>-de" value="<? echo (!empty($ingresso_compras_range_de)) ? $ingresso_compras_range_de : $compras_opcoes_valores[0]; ?>" />
							</p>
							<p id="<? echo $tipo; ?>-opcao-<? echo $compras_opcoes_nome_exibicao; ?>-ate" class="coluna compras-opcoes">
								<label for="ingresso-<? echo $compras_opcoes_nome_exibicao; ?>-ate"><? echo $compras_opcoes_label; ?> até:</label>
								<input type="text" name="<? echo $compras_opcoes_nome_exibicao; ?>[ate]" class="input" id="ingresso-<? echo $compras_opcoes_nome_exibicao; ?>-ate" value="<? echo (!empty($ingresso_compras_range_ate)) ? $ingresso_compras_range_ate : $compras_opcoes_valores[1]; ?>" />
							</p>
							<?

						break;
						
					}
				}

			}

			?>

			<p class="coluna">
				<label for="ingresso-valor">Valor unitário</label>
				<input type="text" name="valor" class="input money" id="ingresso-valor" value="<? echo $ingresso_valor; ?>" />
			</p>


			<div class="clear"></div>
		</section>

		<section class="secao label-top">
			<section id="ingresso-fornecedor" class="selectbox coluna">
				<h3>Fornecedor</h3>
				<a href="#" class="arrow"><strong>Selecione</strong><span></span></a>
				<div class="drop">
					<ul>
						<?

						// $sql_fornecedores = sqlsrv_query($conexao, "SELECT FO_COD, FO_NOME FROM fornecedores WHERE FO_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY FO_NOME ASC", $conexao_params, $conexao_options);
						$sql_fornecedores = sqlsrv_query($conexao_sankhya, "SELECT p.CODPARC, p.NOMEPARC, p.RAZAOSOCIAL, p.IDENTINSCESTAD, p.EMAIL, p.TELEFONE, p.CGC_CPF, p.TIPPESSOA, p.CEP, p.CODEND, p.NUMEND, p.COMPLEMENTO, p.CODBAI, p.CODCID, p.FORNECEDOR, p.CODBCO, p.CODAGE, p.CODCTABCO, p.DTCAD, p.DTALTER, p.BLOQUEAR, c.CODCID, c.NOMECID, c.UF, u.CODUF, u.UF FROM TGFPAR p, TSICID c, TSIUFS u WHERE p.CODCID=c.CODCID AND c.UF=u.CODUF AND p.FORNECEDOR='S' AND p.BLOQUEAR='N' ORDER BY p.NOMEPARC ASC", $conexao_params, $conexao_options);
						if(sqlsrv_num_rows($sql_fornecedores) > 0){

							while ($ar_fornecedores = sqlsrv_fetch_array($sql_fornecedores)) {
								
								$fornecedores_cod = trim($ar_fornecedores['CODPARC']);
								$fornecedores_nome = utf8_encode(trim($ar_fornecedores['NOMEPARC']));
								
							?>
							<li><label class="item"><input type="radio" name="fornecedor" value="<? echo $fornecedores_cod; ?>" alt="<? echo $fornecedores_nome; ?>" /><? echo $fornecedores_nome; ?></label></li>
							<?

							}
						}

						?>
					</ul>
				</div>
			</section>

			<p class="coluna">
				<label for="ingresso-grupo">Númeração</label>
				<input type="text" name="grupo" class="input" id="ingresso-grupo" value="<? echo $ingresso_grupo; ?>" />
			</p>

			<div class="clear"></div>

			<script type="text/javascript">
				$(document).ready(function() {
					$('section#ingresso-fornecedor input[name="fornecedor"]:radio').radioSel('<? echo $ingresso_fornecedor; ?>');
					<? if($compras_opcoes_numerada)  { ?>$('section#arquibancada-opcao-numerada input[name="numerada"]:checkbox').trigger('click');<? } ?>
				});
			</script>
		</section>

		<footer class="controle">
			<input type="submit" class="submit coluna" value="Alterar" />
			<a href="#" class="cancel coluna">Cancelar</a>
			<div class="clear"></div>
		</footer>

	</form>

</section>
<?
	}
}

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>