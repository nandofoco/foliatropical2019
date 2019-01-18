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

$cod = (int) $_GET['c'];
$sql_contato = mysql_query("SELECT *, DATE_FORMAT(CO_DATA, '%d/%m/%Y') as data, DATE_FORMAT(CO_DATA_CONCLUSAO, '%d/%m/%Y') as data_conclusao FROM contato WHERE CO_COD=$cod AND D_E_L_E_T_<>'*' LIMIT 1");

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Editar <span>Atendimento</span></h1>		
	</header>
	<?
	if(mysql_num_rows($sql_contato) > 0) {
		$contato = mysql_fetch_array($sql_contato);
		$contato_nome = utf8_encode($contato['CO_NOME']);
		$contato_via = utf8_encode($contato['CO_VIA']);
		$contato_status = utf8_encode($contato['CO_STATUS']);
		$contato_email = ($contato['CO_EMAIL']);
		$contato_telefone = utf8_encode($contato['CO_TELEFONE']);
		$contato_atendente = utf8_encode($contato['CO_ATENDENTE']);
		$contato_assunto = utf8_encode($contato['CO_ASSUNTO']);
		$contato_mensagem = utf8_encode($contato['CO_MENSAGEM']);
		$contato_setor = utf8_encode($contato['CO_SETOR']);	
		$contato_responsavel = utf8_encode($contato['CO_RESPONSAVEL']);	
		$contato_solucao = utf8_encode($contato['CO_SOLUCAO']);	
		$contato_data = ($contato['data']);
		$contato_data_conclusao = ($contato['data_conclusao']);
	?>
		<form id="cadastro-sac" class="cadastro controle" method="post" action="<? echo SITE; ?>sac/cadastro/post/">
			<input type="hidden" name="editar" value="true">
			<input type="hidden" name="cod" value="<? echo $cod; ?>">
			
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
					<input type="text" name="data-solicitacao" class="input pequeno" value="<? echo $contato_data; ?>" id="contato-data-solicitacao" />
				</p>


				<div class="clear"></div>

				<section class="selectbox coluna pequeno" id="cadastro-via">
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
					<input type="text" name="nome" class="input" id="cliente-nome" value="<? echo $contato_nome; ?>" />
				</p>

				<p>
					<label for="contato-email">Email:</label>
					<input type="text" name="email" class="input" id="contato-email" maxlength="80" value="<? echo $contato_email; ?>" />
				</p>
				
				<div class="clear"></div>
				<p>
					<label for="contato-telefone">Telefone:</label>
					<input type="text" name="telefone" class="input pequeno" id="contato-telefone" value="<? echo $contato_telefone; ?>" />
				</p>

				<p id="contato-atendente-box">
					<label for="contato-atendente">Atendente:</label>
					<input type="text" name="atendente" class="input" id="contato-atendente" value="<? echo $contato_atendente; ?>" />
				</p>

				<p id="contato-assunto-box">
					<label for="contato-assunto">Assunto:</label>
					<input type="text" name="assunto" class="input" id="contato-assunto" value="<? echo $contato_assunto; ?>" />
				</p>

				<p>
					<label for="contato-mensagem">Mensagem:</label>
					<textarea name="mensagem" class="input full" id="contato-mensagem"><? echo $contato_mensagem; ?></textarea>
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
					<input type="text" name="responsavel" class="input" id="contato-responsavel" value="<? echo $contato_responsavel; ?>" />
				</p>

				<p>
					<label for="contato-solucao">Solução:</label>
					<textarea name="solucao" class="input full" id="contato-solucao" value="<? echo $contato_solucao; ?>"><? echo $contato_solucao; ?></textarea>
					<div class="clear"></div>
				</p>

				<p id="contato-data-conclusao-box">
					<label for="contato-data-conclusao">Data da Conclusão:</label>
					<input type="text" name="data-conclusao" class="input pequeno" value="<? echo $contato_data_conclusao; ?>" id="contato-data-conclusao" />
				</p>
			</section>
			<footer class="controle">
				<input type="submit" class="submit coluna" value="Alterar" />
				<a href="#" class="cancel coluna">Cancelar</a>
				<div class="clear"></div>
			</footer>
		</form>
<?  } ?>		
</section>
<script type="text/javascript">
$(document).ready(function(){
	$("form#cadastro-sac").find("input[name='status']").radioSel('<? echo $contato_status; ?>');
	$("form#cadastro-sac").find("input[name='setor']").radioSel('<? echo $contato_setor; ?>');
	$("form#cadastro-sac").find("input[name='via']").radioSel('<? echo $contato_via; ?>');
});
</script>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>