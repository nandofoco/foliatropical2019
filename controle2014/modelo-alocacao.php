<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

?>
<section id="conteudo">
	<section id="compras-alocacao">
		<header class="titulo">
			<h1>Alocação <span>Cliente</span></h1>
		</header>
		<section class="secao" id="alocacao">
			<section id="lista-cliente">
				<div class="box-clientes" id="box-clientes-01">
					<header>
						<span>893</span>
						<h2>Arquibancada</h2>
						<div class="controle">
							<form name="busca" id="busca-alocacao-01" class="busca-alocacao" method="get" action="">
				                <p>
				                    <label for="busca-alocacao-01-input" class="infield">Buscar</label>
				                    <input type="text" name="q" class="input" id="busca-alocacao-01-input" autocomplete="off">
				                </p>
				                <input type="submit" class="submit" value="">
				            </form>
							<a href="#box-clientes-01-lista-01" class="exibir show-hide-slide aberto"></a>
				            <div class="clear"></div>
				        </div>
						<div class="clear"></div>			
					</header>
					<ul id="box-clientes-01-lista-01">
						<li>
							<div>
								<table>
									<tr>
										<td class="cod">745</td>
										<td><span class="classe">1</span></td>
										<td class="nome">Deborah R. Perez</td>
										<td class="alerta">
											<div>
												<a href="#">!</a>
												<div class="tooltip">Esse ingresso foi comprado para outras datas</div>
											</div>
										</td>
										<td class="qtde"></td>
										<td><a href="#" class="lista"></a></td>
									</tr>
								</table>
							</div>
						</li>
						<li>
							<div>
								<table>
									<tr>
										<td class="cod">746</td>
										<td><span class="classe">1</span></td>
										<td class="nome">Liliane A. Cardoso</td>
										<td class="alerta">
											<div>
												<a href="#">!</a>
												<div class="tooltip">Esse ingresso foi comprado para outras datas</div>
											</div>
										</td>
										<td class="qtde"><span>4</span></td>
										<td><a href="#box-clientes-01-lista-01-drop-02" class="lista show-hide-slide"></a></td>
									</tr>							
								</table>
							</div>
							<ul class="drop" id="box-clientes-01-lista-01-drop-02">
								<?
								for($i=1;$i<=4;$i++) {
								?>
									<li>
										<div>
											<table>									
												<tr>
													<td class="cod">746/<? echo $i; ?></td>										
													<td class="nome">Thomaz E. Garcia</td>										
													<td><a href="#" class="lista"></a></td>
												</tr>									
											</table>
										</div>
									</li>
								<?
								}
								?>
								<div class="clear"></div>
							</ul>
						</li>
					</ul>
				</div>
				<div class="box-clientes" id="box-clientes-02">
					<header>
						<span>893</span>
						<h2>Arquibancada</h2>
						<div class="controle">
							<form name="busca" id="busca-alocacao-02" class="infield busca-alocacao" method="get" action="">
				                <p>
				                    <label for="busca-alocacao-02-input" class="infield">Buscar</label>
				                    <input type="text" name="q" class="input" id="busca-alocacao-02-input" autocomplete="off">
				                </p>
				                <input type="submit" class="submit" value="">
				            </form>
							<a href="#box-clientes-02-lista-01" class="exibir show-hide-slide aberto"></a>
				            <div class="clear"></div>
				        </div>
						<div class="clear"></div>			
					</header>
					<ul id="box-clientes-02-lista-01">
						<li>
							<div>
								<table>
									<tr>
										<td class="cod">745</td>
										<td><span class="classe">1</span></td>
										<td class="nome">Deborah R. Perez</td>
										<td class="alerta"></td>
										<td class="qtde"></td>
										<td><a href="#" class="lista"></a></td>
									</tr>
								</table>
							</div>
						</li>
						<li>
							<div>
								<table>
									<tr>
										<td class="cod">746</td>
										<td><span class="classe">1</span></td>
										<td class="nome">Liliane A. Cardoso</td>
										<td class="alerta">
											<div>
												<a href="#">!</a>
												<div class="tooltip">Esse ingresso foi comprado para outras datas</div>
											</div>
										</td>
										<td class="qtde"><span>4</span></td>
										<td><a href="#box-clientes-02-lista-01-drop-02" class="lista show-hide-slide"></a></td>
									</tr>							
								</table>
							</div>
							<ul class="drop" id="box-clientes-02-lista-01-drop-02">
								<?
								for($i=1;$i<=4;$i++) {
								?>
									<li>
										<div>
											<table>									
												<tr>
													<td class="cod">746/<? echo $i; ?></td>										
													<td class="nome">Thomaz E. Garcia</td>										
													<td><a href="#" class="lista"></a></td>
												</tr>									
											</table>
										</div>
									</li>
								<?
								}
								?>
								<div class="clear"></div>
							</ul>
						</li>
					</ul>
				</div>
			</section>			
			<section id="lista-alocacao">
				<header>
					<h2>Selecione a fileira</h2>
					<form name="fileira" id="seleciona-fileira" method="get" action="">
						<section class="selectbox coluna" id="alocacao-fileira">
							<a href="#" class="arrow"><strong>Todas</strong><span></span></a>
							<ul class="drop">
								<li><label class="item"><input type="radio" name="ano" value="Todas" alt="Todas" />TODAS</label></li>
								<li><label class="item"><input type="radio" name="ano" value="A" alt="A" />A</label></li>
								<li><label class="item"><input type="radio" name="ano" value="B" alt="B" />B</label></li>
								<li><label class="item"><input type="radio" name="ano" value="C" alt="C" />C</label></li>
								<li><label class="item"><input type="radio" name="ano" value="D" alt="D" />D</label></li>
							</ul>
						</section>
					</form>				
					<div class="clear"></div>
				</header>
				<header>
					<h2>Selecione a frisa</h2>
					<ul class="lista selecao">
						<li><a href="#">A</a></li>
						<li><a href="#">B</a></li>
						<li><a href="#">C</a></li>
						<li><a href="#">D</a></li>
						<li><a href="#" class="todas selecionada">Todas</a></li>
					</ul>
					<div class="clear"></div>
				</header>
				<header></header>
				<ul class="lista">
					<li class="titulo">A</li>
					<li><a href="#"></a><small>100</small></li>
					<li>
						<a href="#" class="liberado">735<span>1</span></a>
						<small>101</small>
						<div class="tooltip">
							<h3>Audrey B. Horvath</h3>
							<h4>Pacífica</h4>
							<p>R$ 2.300,00</p>
						</div>
					</li>
					<li>
						<a href="#" class="enviado">736<span>2</span></a>
						<small>102</small>
						<div class="tooltip">
							<h3>Audrey B. Horvath</h3>
							<h4>Pacífica</h4>
							<p>R$ 2.300,00</p>
						</div>
					</li>
					<div class="clear"></div>
				</ul>
				<ul class="lista">
					<li class="titulo pequeno">4A<small>17</small></li>
					<li><a href="#"></a></li>
					<li>
						<a href="#" class="liberado">735<span>1</span></a>
						<div class="tooltip">
							<h3>Audrey B. Horvath</h3>
							<h4>Pacífica</h4>
							<p>R$ 2.300,00</p>
						</div>
					</li>
					<li>
						<a href="#" class="enviado">736<span>2</span></a>
						<div class="tooltip">
							<h3>Audrey B. Horvath</h3>
							<h4>Pacífica</h4>
							<p>R$ 2.300,00</p>
						</div>
					</li>
					<div class="clear"></div>
				</ul>
			</section>
			<div class="clear"></div>
		</section>
	</section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");


?>