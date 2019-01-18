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
define('PGATUAL', 'ingressos/compra/novo/');

$tipo_ingresso = format($_GET['t']);
$evento = (int) $_SESSION['usuario-carnaval'];

?>
<section id="conteudo">
	<form id="ingresso-compra-novo" method="post" action="<? echo SITE; ?>ingressos/compra/novo/post/">
		<header class="titulo">
			<h1>Ingresso Compra <span>Novo</span></h1>
		</header>
		
		<? include('include/secao-tipo-setor.php'); ?>

		<section class="secao label-top">
			<section class="checkbox infield dias coluna">
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
						<label class="item"><input type="checkbox" name="dia[]" value="<? echo $eventos_dias_cod; ?>" />
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
								<? foreach ($compras_opcoes_valores as $key => $value) { ?>									
									<li>
										<label class="item"><input type="<? echo $compras_opcoes_tipo; ?>" name="<? echo $compras_opcoes_nome_exibicao; if(($compras_opcoes_tipo == 'checkbox') && count($compras_opcoes_valores) > 1) echo '[]'; ?>" value="<? echo $value; ?>" />
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
									<li><label class="item"><input type="radio" name="<? echo $compras_opcoes_nome_exibicao; ?>" value="<? echo $value; ?>" alt="<? echo utf8_encode($key); ?>" /><? echo utf8_encode($key); ?></label></li>
									<? } ?>
								</ul>
							</section>
							<?

						break;

						case 'input':
							
							?>
							<p id="<? echo $tipo; ?>-opcao-<? echo $compras_opcoes_nome_exibicao; ?>" class="coluna compras-opcoes">
								<label for="ingresso-<? echo $compras_opcoes_nome_exibicao; ?>"><? echo $compras_opcoes_label; ?></label>
								<input type="text" name="<? echo $compras_opcoes_nome_exibicao; ?>" class="input" id="ingresso-<? echo $compras_opcoes_nome_exibicao; ?>" value="<? echo $compras_opcoes_valores; ?>" />
							</p>
							<?

						break;

						case 'range':
							
							if(!empty($compras_opcoes_valores)) $compras_opcoes_valores = json_decode($compras_opcoes_valores);

							?>
							<p id="<? echo $tipo; ?>-opcao-<? echo $compras_opcoes_nome_exibicao; ?>-de" class="coluna compras-opcoes">
								<label for="ingresso-<? echo $compras_opcoes_nome_exibicao; ?>-de"><? echo $compras_opcoes_label; ?> de:</label>
								<input type="text" name="<? echo $compras_opcoes_nome_exibicao; ?>[de]" class="input" id="ingresso-<? echo $compras_opcoes_nome_exibicao; ?>-de" value="<? echo $compras_opcoes_valores[0]; ?>" />
							</p>
							<p id="<? echo $tipo; ?>-opcao-<? echo $compras_opcoes_nome_exibicao; ?>-ate" class="coluna compras-opcoes">
								<label for="ingresso-<? echo $compras_opcoes_nome_exibicao; ?>-ate"><? echo $compras_opcoes_label; ?> até:</label>
								<input type="text" name="<? echo $compras_opcoes_nome_exibicao; ?>[ate]" class="input" id="ingresso-<? echo $compras_opcoes_nome_exibicao; ?>-ate" value="<? echo $compras_opcoes_valores[1]; ?>" />
							</p>
							<?

						break;
						
					}
				}

			}

			?>

			<p class="coluna">
				<label for="ingresso-valor">Valor unitário</label>
				<input type="text" name="valor" class="input money" id="ingresso-valor" />
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
					$n_fornecedores = sqlsrv_num_rows($sql_fornecedores);
					if($n_fornecedores > 0){

						$i_fornecedores = 1;
						$break_fornecedores = ceil($n_fornecedores / 3);

						while ($ar_fornecedores = sqlsrv_fetch_array($sql_fornecedores)) {
							
							$fornecedores_cod = trim($ar_fornecedores['CODPARC']);
							$fornecedores_nome = utf8_encode(trim($ar_fornecedores['NOMEPARC']));

							if(($i_fornecedores < $n_fornecedores) && ($i_fornecedores > 1) && (($i_fornecedores % $break_fornecedores) == 0)) { ?></ul><ul><? }
								
							?>
							<li><label class="item"><input type="radio" name="fornecedor" value="<? echo $fornecedores_cod; ?>" alt="<? echo $fornecedores_nome; ?>" /><? echo $fornecedores_nome; ?></label></li>
							<?

							$i_fornecedores++;

						}
					}

					?>
					</ul>
				</div>
			</section>

			<p class="coluna">
				<label for="ingresso-grupo">Númeração</label>
				<input type="text" name="grupo" class="input" id="ingresso-grupo" />
			</p>

			<div class="clear"></div>
		</section>

		<footer class="controle">
			<input type="submit" class="submit coluna" value="Inserir" />
			<a href="#" class="cancel coluna">Cancelar</a>
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

?>