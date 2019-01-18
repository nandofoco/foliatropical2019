<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");
?>
	<section id="conteudo">
		<form id="compras-adicionais" method="post" action="<? echo SITE; ?>blacklist/cadastro/post/">
			<header class="titulo">
				<h1>Inclusão na <span>Blacklist</span></h1>				
			</header>						
			
			<section class="secao cadastro-cliente" style="border-bottom: unset;">

				<section id="compras-cliente" class="checkbox coluna">
					<ul class="hidden"><li><label class="item"><input type="checkbox" name="cliente" value="" /></label></li></ul>

					<div class="sugestao">
						<p>
							<label for="carrinho-cliente">Cliente:</label>
							<input type="text" id="carrinho-cliente" name="cliente-sugestao" class="input sugestao" />
						</p>
						<div class="drop">
					    	<ul></ul>
					    </div>
					</div>
				</section>
			</section>

			<br>

			<div class="coluna" style="margin-top: 60px; float: unset; margin-left: 10px;">
				<p>
					<label for="cpf">Cpf:</label>
					<input type="text" id="cpf" name="cpf" class="input" />
				</p>
			</div>

			<br>

			<div class="coluna" style="margin-left: 10px; float: unset;">
				<p>
					<label for="cartao">Número do cartão:</label>
					<input type="text" id="cartao" name="cartao" class="input" max="16" />
				</p>
			</div>

		<div class="coluna" style="margin-left: 10px; float: unset; margin-top: 35px;">
			<input type="submit" class="submit" value="Adicionar">
			<a href="<? echo SITE; ?>blacklist/" class="cancel no-cancel coluna">Voltar</a>
		</div>


		</form>
	</section>

	<script>

		
	</script>
	

	<?
	
	//-----------------------------------------------------------------//


	
	include('include/footer.php');

	//Fechar conexoes
	include("conn/close.php");
	include("conn/close-sankhya.php");

	exit();


?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>