<?

//Incluir funções básicas
include("include/includes.php");

unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Roteiro <span>Novo</span></h1>
	</header>
	<section class="padding">
		<form id="roteiro-novo" method="post" action="<? echo SITE; ?>roteiros/cadastro/post/">
			<section class="secao label-top" id="roteiro-dados">
				<p class="coluna">
					<label for="roteiro-nome">Insira um nome para o roteiro</label>
					<input type="text" name="titulo" class="input" id="roteiro-nome" value="Roteiro 01" />
				</p>
				<section class="radio infield big coluna">
					<h3>Tipo de Setor</h3>
					<ul>						
						<li><label class="item"><input type="radio" name="tipo" value="1" />Pares</label></li>
						<li><label class="item"><input type="radio" name="tipo" value="2" />Ímpares</label></li>
						<li><label class="item"><input type="radio" name="tipo" value="3" />FT</label></li>
						<li><label class="item"><input type="radio" name="tipo" value="4" />Camarote</label></li>
					</ul>
				</section>
				<div class="clear"></div>
			</section>		
			<section id="roteiro-itens" class="secao">
				<h3>Adicione os itens do roteiro</h3>
				<ul>
					<li rel="1">
						<p class="coluna">
							<label for="roteiro-item-1-nome" class="infield">Nome do Local</label>
							<input type="text" name="nome[1]" class="input nome" id="roteiro-item-1-nome" value="" />
						</p>
						<p class="coluna">
							<label for="roteiro-item-1-endereco" class="infield">Endereço</label>
							<input type="text" name="endereco[1]" class="input horario" id="roteiro-item-1-endereco" value="" />
						</p>
						<p class="coluna">
							<label for="roteiro-item-1-telefone" class="infield">Telefone</label>
							<input type="text" name="telefone[1]" class="input horario" id="roteiro-item-1-telefone" value="" />
						</p>
						<p class="coluna">
							<label for="roteiro-item-1-horario-1" class="infield">Horário 01</label>
							<input type="text" name="horario[1][1]" class="input horario" id="roteiro-item-1-horario-1" value="" />
						</p>
						<p class="coluna">
							<label for="roteiro-item-1-horario-2" class="infield">Horário 02</label>
							<input type="text" name="horario[1][2]" class="input horario" id="roteiro-item-1-horario-2" value="" />
						</p>
						<p class="coluna">
							<label for="roteiro-item-1-horario-3" class="infield">Horário 03</label>
							<input type="text" name="horario[1][3]" class="input horario" id="roteiro-item-1-horario-3" value="" />
						</p>
						<p class="coluna">
							<label for="roteiro-item-1-horario-4" class="infield">Horário 04</label>
							<input type="text" name="horario[1][4]" class="input horario" id="roteiro-item-1-horario-4" value="" />
						</p>
						<div class="clear"></div>
					</li>
					<a href="#" class="adicionar">+</a>
					<div class="clear"></div>
				</ul>
				<footer class="controle">
					<input type="submit" class="submit coluna" value="Inserir" />
					<a href="#" class="cancel coluna">Cancelar</a>
					<div class="clear"></div>
				</footer>
			</section>
		</form>		
	</section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>