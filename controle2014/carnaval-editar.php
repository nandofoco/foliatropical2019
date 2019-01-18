<?

//Incluir funções básicas
include("include/includes.php");

unset($_SESSION['carnaval-dias'], $_SESSION['carnaval-setores'], $_SESSION['lista-atracoes'], $_SESSION['setores-remover']);

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

$semana = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');

//-----------------------------------------------------------------//

$cod = $_GET['c'];

if(!empty($cod)) {

	//buscar evento
	$sql_evento = sqlsrv_query($conexao, "SELECT TOP 1 * FROM eventos WHERE EV_COD='$cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
	$nev = sqlsrv_num_rows($sql_evento);

	$ev = sqlsrv_fetch_array($sql_evento);
	$ev_nome = utf8_encode($ev['EV_NOME']);
	$ev_ano = $ev['EV_ANO'];

	if($nev > 0) {

?>
		<section id="conteudo">
			<header class="titulo">
				<h1>Editar <span><? echo $ev_nome; ?></span></h1>
			</header>
			<section class="padding">
				<form id="carnaval-novo" method="post" action="#">
					<p class="coluna">
						<label for="carnaval-dia">Adicionar dias</label>
						<input type="text" name="dia" class="input" id="carnaval-dia" />
						<input type="hidden" name="ano" class="input" id="carnaval-ano" value="<? echo $ev_ano; ?>" />
					</p>
					<input type="submit" class="submit adicionar coluna" value="+" />
				</form>
				<form id="carnaval-novo-escolas" method="post" action="<? echo SITE; ?>carnaval/editar/post/">
					<input type="hidden" name="cod" value="<? echo $cod; ?>" />
					<input type="hidden" name="editar" value="true" />
					<section class="secao label-top" id="carnaval-dados">
						<p class="coluna">
							<label for="carnaval-nome">Nome</label>
							<input type="text" name="nome" class="input editar" id="carnaval-nome" value="<? echo $ev_nome; ?>" />
						</p>

						<div class="clear"></div>
					</section>		 
					<section id="carnaval-dias-selecionados" class="secao" style="display:block;">
						<ul>
							<?
							//busca dias do evento
							$sql_dias = sqlsrv_query($conexao, "SELECT * FROM eventos_dias WHERE ED_EVENTO='$cod' AND D_E_L_E_T_='0' ORDER BY ED_DATA ASC", $conexao_params, $conexao_options);
							$ndi = sqlsrv_num_rows($sql_dias);

							if($ndi > 0) {
								$i = 0;
								while ($di = sqlsrv_fetch_array($sql_dias)) {

									$dia_cod = $di['ED_COD'];
									$dia_nome = utf8_encode($di['ED_NOME']);
									$dia_data = $di['ED_DATA'];
									$dia_atracoes = utf8_encode($di['ED_ATRACOES']);
									
									$data = strtotime($dia_data->format('Y-m-d'));

									$_SESSION['carnaval-dias'][$i]['cod'] = $dia_cod;
									$_SESSION['carnaval-dias'][$i]['data'] = $data;
									$_SESSION['lista-atracoes'][$i] = $dia_atracoes;
									
								?>
									<li>
										<a href="<? echo $i ?>" class="editar" title="Editar Dia">
											<h1><? echo $dia_nome; ?></h1>
											<h2><? echo $semana[date('w',$data)]; ?></h2>
											<small><? echo date('d/m/Y',$data); ?></small>
											<span></span>
										</a>								
										<div id="form-edit-<? echo $i; ?>" class="form-edit"><input type="text" name="editar-data[<? echo $i; ?>]" class="input editar" value="<? echo date('d/m',$data); ?>" /><a href="<? echo $i; ?>" class="edit-data" rel="<? echo $dia_cod; ?>"></a></div>
									</li>
									
								<?
									$i++;
								}
							}
							?>
						</ul>
					</section>
					<section id="carnaval-dias-atracoes" class="secao" style="display: block;">
						<h3>Adicione as escolas de samba por dia <span>(Separadas por vírgula)</span></h3>
						<ul>
							<?
							foreach ($_SESSION['lista-atracoes'] as $key => $value) {
							?>
								<li>
									<h4><? echo ($key+1)."&ordm;" ?> dia</h4>
									<p class="coluna">
										<label for="carnaval-escolas-dia-<? echo $key; ?>" class="infield">Ex. Mangueira, Salgueiro, Beija-Flor, Mocidade</label>
										<input type="text" name="escola-dia[<? echo $key; ?>]" class="input" value="<? echo $value; ?>" id="carnaval-escolas-dia-<? echo $key; ?>" />
									</p>
									<div class="clear"></div>
								</li>
							<?
							}
							?>
						</ul>
						<footer class="controle">
							<input type="submit" class="submit coluna" value="Salvar" />
							<a href="#" class="cancel coluna">Cancelar</a>
							<div class="clear"></div>
						</footer>
					</section>
				</form>
				<section class="secao" id="carnaval-setores">
					<form id="carnaval-novo-setores" method="post" action="#">
						<h3>Adicionar Setores</h3>
						<p>
							<label for="carnaval-setor" class="infield"></label>
							<input type="text" name="setor" class="input" id="carnaval-setor" />
							<input type="hidden" name="edit" value="true" />
							<!-- <small>Adicione o número ou a sigla dos setores disponíveis (Ex. "1", "FT")</small> -->
						</p>
						<input type="submit" class="submit adicionar coluna" value="+" />
					</form>
					<ul style="display: block;">
						<?
						//buscar setores
						$sql_setores = sqlsrv_query($conexao, "SELECT * FROM eventos_setores WHERE ES_EVENTO='$cod' AND ES_BLOCK='0' AND D_E_L_E_T_='0' ORDER BY LEN(ES_NOME) ASC, ES_NOME ASC", $conexao_params, $conexao_options);
						$nse = sqlsrv_num_rows($sql_setores);

						if($nse > 0) {
							$i=0;
							while ($se = sqlsrv_fetch_array($sql_setores)) {
								$se_cod = $se['ES_COD'];
								$se_nome = $se['ES_NOME'];

								$_SESSION['carnaval-setores'][$i]['cod'] = $se_cod;
								$_SESSION['carnaval-setores'][$i]['nome'] = $se_nome;
							?>
								<li><a href="<? echo $i; ?>" class="remover edit" title="Remover Setor"><? echo $se_nome; ?></a></li>
							<?
								$i++;
							}
						}
						?>
					</ul>
					<div class="clear"></div>
				</section>
			</section>
		</section>
<?
	}
}
//-----------------------------------------------------------------//

include('include/footer.php');


//Fechar conexoes
include("conn/close.php");

?>