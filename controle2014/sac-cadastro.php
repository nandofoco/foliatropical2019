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
		<h1>Cadastro de <span>Atendimento</span></h1>		
	</header>
	<form id="cadastro-sac" class="cadastro controle" method="post" action="<? echo SITE; ?>sac/cadastro/post/">
		<section class="secao">
			<section class="selectbox coluna pequeno" id="contato-status">
				<h3>Status:</h3>
				<a href="#" class="arrow"><strong>Status</strong><span></span></a>
				<ul class="drop">
                    <li><label class="item"><input type="radio" name="status" alt="Iniciado" value="Iniciado">Iniciado</label></li>               
                    <li><label class="item"><input type="radio" name="status" alt="Pendente" value="Pendente">Pendente</label></li>               
                    <li><label class="item"><input type="radio" name="status" alt="Em Andamento" value="Em Andamento">Em Andamento</label></li>               
                    <li><label class="item"><input type="radio" name="status" alt="Concluído" value="Concluído">Concluído</label></li>               
				</ul>
				<div class="clear"></div>
			</section>
			<div class="clear"></div>

			<p id="contato-data-solicitacao-box">
				<label for="contato-data-solicitacao">Data da Solicitação:</label>
				<input type="text" name="data-solicitacao" class="input pequeno" id="contato-data-solicitacao" />
			</p>
			<div class="clear"></div>

			<section class="selectbox coluna pequeno" id="contato-via">
				<h3>Contato Via:</h3>
				<a href="#" class="arrow"><strong>Contato Via</strong><span></span></a>
					<ul class="drop">
	                    <li><label class="item"><input type="radio" name="via" alt="Chat Online" value="Chat Online">Chat Online</label></li>               
	                    <li><label class="item"><input type="radio" name="via" alt="Facebook" value="Facebook">Facebook</label></li>               
	                    <li><label class="item"><input type="radio" name="via" alt="Google" value="Google">Google</label></li>               
	                    <li><label class="item"><input type="radio" name="via" alt="Instagram" value="Instagram">Instagram</label></li>                             
	                    <li><label class="item"><input type="radio" name="via" alt="Telefone" value="Telefone">Telefone</label></li>                             
	                    <li><label class="item"><input type="radio" name="via" alt="Site" value="Site">Site</label></li>                             
	                    <li><label class="item"><input type="radio" name="via" alt="Fale Conosco" value="Fale Conosco">Fale Conosco</label></li>                             
	                    <li><label class="item"><input type="radio" name="via" alt="Email" value="Email">Email</label></li>                             
	                    <li><label class="item"><input type="radio" name="via" alt="Jornal" value="Jornal">Jornal</label></li>                             
	                    <li><label class="item"><input type="radio" name="via" alt="Outros" value="Outros">Outros</label></li>                             
					</ul>
				<div class="clear"></div>
			</section>
			<div class="clear"></div>

			<p id="cliente-nome-box">
				<label for="cliente-nome">Nome do cliente:</label>
				<input type="text" name="nome" class="input" id="cliente-nome" maxlength="40" />
			</p>

			<p>
				<label for="contato-email">Email:</label>
				<input type="text" name="email" class="input" id="contato-email" maxlength="80" />
			</p>
			
			<div class="clear"></div>
			<p>
				<label for="contato-telefone">Telefone:</label>
				<input type="text" name="telefone" class="input pequeno" id="contato-telefone" />
			</p>

			<p id="contato-atendente-box">
				<label for="contato-atendente">Atendente:</label>
				<input type="text" name="atendente" class="input" id="contato-atendente" maxlength="40" />
			</p>

			<p id="contato-assunto-box">
				<label for="contato-assunto">Assunto:</label>
				<input type="text" name="assunto" class="input" id="contato-assunto" maxlength="40" />
			</p>

			<p>
				<label for="contato-mensagem">Mensagem:</label>
				<textarea name="mensagem" class="input full" id="contato-mensagem"></textarea>
				<div class="clear"></div>
			</p>

			<section class="selectbox coluna pequeno" id="contato-setor">
				<h3>Setor:</h3>
				<a href="#" class="arrow"><strong>Setor</strong><span></span></a>
				<ul class="drop">
                    <li><label class="item"><input type="radio" name="setor" alt="Diretoria" value="Diretoria">Diretoria</label></li>               
                    <li><label class="item"><input type="radio" name="setor" alt="Financeiro" value="Financeiro">Financeiro</label></li>               
                    <li><label class="item"><input type="radio" name="setor" alt="Comercial" value="Comercial">Comercial</label></li>               
                    <li><label class="item"><input type="radio" name="setor" alt="Marketing" value="Marketing">Marketing</label></li>                             
                    <li><label class="item"><input type="radio" name="setor" alt="Produção Avenida" value="Produção Avenida">Produção Avenida</label></li>                             
                    <li><label class="item"><input type="radio" name="setor" alt="Credenciamento" value="Credenciamento">Credenciamento</label></li>                             
                    <li><label class="item"><input type="radio" name="setor" alt="Transporte" value="Transporte">Transporte</label></li>                             
				</ul>
				<div class="clear"></div>
			</section>
			<div class="clear"></div>
			<p id="contato-responsavel-box">
				<label for="contato-responsavel">Responsável:</label>
				<input type="text" name="responsavel" class="input" id="contato-responsavel" maxlength="40" />
			</p>

			<p>
				<label for="contato-solucao">Solução:</label>
				<textarea name="solucao" class="input full" id="contato-solucao"></textarea>
				<div class="clear"></div>
			</p>

			<p id="contato-data-conclusao-box">
				<label for="contato-data-conclusao">Data da Conclusão:</label>
				<input type="text" name="data-conclusao" class="input pequeno" value="<? echo $contato_data_conclusao; ?>" id="contato-data-conclusao" />
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