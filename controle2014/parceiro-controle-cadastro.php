<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");



//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Cadastro de Parceiros - <span>Login</span></h1>		
	</header>
	<form id="cadastro-parceiros" class="cadastro controle" method="post" action="<? echo SITE; ?>parceiros-controle/cadastro/post/">
		<section class="secao cad-parceiros">			

			<div class="clear"></div>
			<p id="cliente-nome-box">
				<label for="parceiro-nome">Nome:</label>
				<input type="text" name="nome" class="input pequeno" id="parceiro-nome" maxlength="40" />
			</p>

			<p>
				<label for="cliente-email">Senha:</label>
				<input type="text" name="senha" class="input pequeno" id="parceiro-senha" maxlength="80" />
			</p>
			
		</section>
		<footer class="controle">
			<input type="submit" class="submit coluna" value="Inserir" />
			<a href="#" class="cancel coluna">Cancelar</a>
			<div class="clear"></div>
		</footer>
	</form>
</section>

<script>

</script>

<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>