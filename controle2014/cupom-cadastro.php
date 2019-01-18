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
	<header class="titulo">
		<h1>Cadastro de <span>Cupom</span></h1>		
	</header>
	<form id="cadastro-cupom" class="cadastro" method="post" action="<? echo SITE; ?>cupom/cadastro/post/">
		<section class="secao">
			<p>
				<label for="cupom-nome">Nome:</label>
				<input type="text" name="nome" class="input" id="cupom-nome" />
			</p>
			<section class="selectbox coluna pequeno" id="cupom-tipo">
				<h3>Tipo:</h3>
				<a href="#" class="arrow"><strong>Tipo</strong><span></span></a>
				<ul class="drop">
                    <li><label class="item"><input type="radio" name="tipo" alt="Porcentagem" value="1">Porcentagem</label></li>                   
                    <li><label class="item"><input type="radio" name="tipo" alt="Valor" value="2">Valor</label></li>
				</ul>
				<div class="clear"></div>
			</section>
			<div class="clear"></div>
			<p id="valor">
				<label for="cupom-valor">Desconto em (%):</label>
				<input type="text" name="valor" class="input pequeno" id="cupom-valor" />
			</p>		
			<p>
				<label for="cupom-data-validade">Data de Validade:</label>
				<input type="text" name="data-validade" class="input pequeno" id="cupom-data-validade" />
			</p>
			<p>
				<label for="cupom-prefixo">Prefixo:</label>
				<input type="text" name="prefixo" class="input pequeno" id="cupom-prefixo" maxlength="4" />
			</p>
			<p>
				<label for="cupom-quantidade">Quantidade:</label>
				<input type="text" name="quantidade" class="input pequeno" id="cupom-quantidade" />
			</p>
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

?>