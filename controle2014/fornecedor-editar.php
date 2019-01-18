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

$cod = (int) $_GET['c'];

$sql_fornecedor = sqlsrv_query($conexao_sankhya, "SELECT p.CODPARC, p.NOMEPARC, p.RAZAOSOCIAL, p.IDENTINSCESTAD, p.EMAIL, p.TELEFONE, p.CGC_CPF, p.TIPPESSOA, p.CEP, p.CODEND, p.NUMEND, p.COMPLEMENTO, p.CODBAI, p.CODCID, p.FORNECEDOR, p.CODBCO, p.CODAGE, p.CODCTABCO, p.DTCAD, p.DTALTER, p.BLOQUEAR, c.CODCID, c.NOMECID, c.UF, u.CODUF, u.UF, e.CODEND, e.NOMEEND, b.CODBAI, b.NOMEBAI FROM TGFPAR p, TSICID c, TSIUFS u, TSIEND e, TSIBAI b WHERE p.CODPARC='$cod' AND p.CODCID=c.CODCID AND c.UF=u.CODUF AND p.CODEND=e.CODEND AND p.CODBAI=b.CODBAI AND p.FORNECEDOR='S'", $conexao_params, $conexao_options);


?>
<section id="conteudo">
	<header class="titulo">
		<h1>Editar <span>Fornecedor</span></h1>		
	</header>
	<?
	if(sqlsrv_num_rows($sql_fornecedor) > 0) {

		$fornecedor = sqlsrv_fetch_array($sql_fornecedor);
		$fornecedor_nome = trim(utf8_encode($fornecedor['NOMEPARC']));
		$fornecedor_razao = trim(utf8_encode($fornecedor['RAZAOSOCIAL']));
		$fornecedor_pessoa = trim(utf8_encode($fornecedor['TIPPESSOA']));
		$fornecedor_email = trim($fornecedor['EMAIL']);
		$fornecedor_cpfcnpj = formatCPFCNPJ(trim($fornecedor['CGC_CPF']));
		$fornecedor_inscricao = trim($fornecedor['IDENTINSCESTAD']);
		$fornecedor_telefone = formatTelefone(trim($fornecedor['TELEFONE']));
		$fornecedor_cep = trim($fornecedor['CEP']);
		$fornecedor_endereco = trim(utf8_encode($fornecedor['NOMEEND']));
		$fornecedor_numero = trim($fornecedor['NUMEND']);
		$fornecedor_complemento = trim($fornecedor['COMPLEMENTO']);
		$fornecedor_bairro = trim(utf8_encode($fornecedor['NOMEBAI']));
		$fornecedor_cidade = trim(utf8_encode($fornecedor['NOMECID']));
		$fornecedor_estado = trim(utf8_encode($fornecedor['CODUF']));
		$fornecedor_banco = trim($fornecedor['CODBCO']);
		$fornecedor_agencia = trim($fornecedor['CODAGE']);
		$fornecedor_conta = trim($fornecedor['CODCTABCO']);
		
	?>
		<form id="cadastro-fornecedor" class="cadastro" method="post" action="<? echo SITE; ?>fornecedores/cadastro/post/">
			<input type="hidden" name="editar" value="true">
			<input type="hidden" name="cod" value="<? echo $cod; ?>">
			<section class="secao">
				<section id="fornecedor-pessoa" class="radio infield big">
					<h3>Tipo de Pessoa:</h3>
					<ul>
						<li><label class="item"><input type="radio" name="pessoa" value="F" />Física</label>
						<li><label class="item"><input type="radio" name="pessoa" value="J" />Jurídica</label>
						</li>
					</ul>
					<div class="clear"></div>
				</section>
				<p id="fornecedor-nome-box">
					<label for="fornecedor-nome">Nome:</label>
					<input type="text" name="nome" class="input" id="fornecedor-nome" value="<? echo $fornecedor_nome; ?>" maxlength="40" />
				</p>
				<p id="fornecedor-razao-box">
					<label for="fornecedor-razao">Razão Social:</label>
					<input type="text" name="razao" class="input" id="fornecedor-razao" disabled="disabled" value="<? echo $fornecedor_razao; ?>" maxlength="40" />
				</p>
				<p>
					<label for="fornecedor-email">Email:</label>
					<input type="text" name="email" class="input" id="fornecedor-email" value="<? echo $fornecedor_email; ?>" maxlength="80" />
				</p>
				<p id="fornecedor-cpfcnpj-box">
					<label for="fornecedor-cpfcnpj">CPF:</label>					
					<input type="text" name="cpfcnpj" class="input" id="fornecedor-cpfcnpj" value="<? echo $fornecedor_cpfcnpj; ?>" />
				</p>
				<p id="fornecedor-inscricao-box">
					<label for="fornecedor-inscricao">Inscrição Estadual:</label>
					<input type="text" name="inscricao" class="input" id="fornecedor-inscricao" value="<? echo $fornecedor_inscricao; ?>" />
				</p>	
				<p>
					<label for="fornecedor-telefone">Telefone:</label>
					<input type="text" name="telefone" class="input pequeno" id="fornecedor-telefone" value="<? echo $fornecedor_telefone; ?>" />
				</p>
				<p>
					<label for="fornecedor-cep">CEP:</label>
					<input type="text" name="cep" class="input pequeno" id="fornecedor-cep" value="<? echo $fornecedor_cep; ?>" />
					<a href="http://www.correios.com.br/servicos/cep/" class="esqueci" target="_blank">Esqueci meu CEP</a>
				</p>
				<p>
					<label for="fornecedor-endereco">Endereço:</label>
					<input type="text" name="endereco" class="input" id="fornecedor-endereco" value="<? echo $fornecedor_endereco; ?>" />
				</p>
				<p>
					<label for="fornecedor-numero">Número:</label>
					<input type="text" name="numero" class="input pequeno" id="fornecedor-numero" value="<? echo $fornecedor_numero; ?>" />
				</p>
				<p>
					<label for="fornecedor-complemento">Complemento:</label>
					<input type="text" name="complemento" class="input pequeno" id="fornecedor-complemento" value="<? echo $fornecedor_complemento; ?>" />
				</p>
				<p>
					<label for="fornecedor-bairro">Bairro:</label>
					<input type="text" name="bairro" class="input" id="fornecedor-bairro" value="<? echo $fornecedor_bairro; ?>" />
				</p>
				<p> 
					<label for="fornecedor-cidade">Cidade:</label>
					<input type="text" name="cidade" class="input" id="fornecedor-cidade" value="<? echo $fornecedor_cidade; ?>" />
				</p>
				
				<section class="selectbox coluna" id="fornecedors-estado">
					<h3>Estado:</h3>
					<a href="#" class="arrow"><strong>Selecione</strong><span></span></a>
					<ul class="drop">
						<?
						$sql_estados = sqlsrv_query($conexao_sankhya, "SELECT CODUF, UF, DESCRICAO FROM TSIUFS ORDER BY DESCRICAO ASC", $conexao_params, $conexao_options);
						$n_estados = sqlsrv_num_rows($sql_estados);

						if($n_estados > 0) {
						while($estados = sqlsrv_fetch_array($sql_estados)) {
							$estado_cod = $estados['CODUF'];
							$estado_nome = strip_tags(utf8_encode(trim($estados['DESCRICAO'])));
							$estado_uf = utf8_encode(trim($estados['UF']));
						?>
	                    	<li><label class="item"><input type="radio" name="estado" alt="<? echo $estado_nome; ?>" data-uf="<? echo $estado_uf; ?>" value="<? echo $estado_cod; ?>"><? echo $estado_nome; ?></label></li>
						<?
							}
						}
						?>                       
					</ul>
					<div class="clear"></div>
				</section>
				<div class="clear"></div>
			</section>
			<header class="titulo">
				<h1>Dados <span>Bancários</span></h1>		
			</header>
			<section class="secao">
				<p>
					<label for="fornecedor-banco">Banco:</label>
					<input type="text" name="banco" class="input pequeno" id="fornecedor-banco" value="<? echo $fornecedor_banco; ?>" />
				</p>
				<p>
					<label for="fornecedor-agencia">Agência:</label>
					<input type="text" name="agencia" class="input pequeno" id="fornecedor-agencia" value="<? echo $fornecedor_agencia; ?>" />
				</p>
				<p>
					<label for="fornecedor-conta">Conta:</label>
					<input type="text" name="conta" class="input pequeno" id="fornecedor-conta" value="<? echo $fornecedor_conta; ?>" />
				</p>
			</section>
			<header class="titulo">
				<h1>Dados de<span> Acesso</span></h1>		
			</header>
			<section class="secao">
				<p>
					<label for="fornecedor-cpfcnpj">CPF/CNPJ:</label>
					<input type="text" name="cpfcnpj" class="input pequeno" id="fornecedor-cpfcnpj" value="<? echo $fornecedor_cpfcnpj; ?>" />
				</p>
				<p>
					<label for="fornecedor-senha">Nova Senha:</label>
					<input type="password" name="senha" class="input pequeno" id="fornecedor-senha" />
				</p>
				<p>
					<label for="fornecedor-csenha">Confirmar Senha:</label>
					<input type="password" name="csenha" class="input pequeno" id="fornecedor-csenha" />
				</p>
			</section>
			<footer class="controle">
				<input type="submit" class="submit coluna" value="Alterar" />
				<a href="#" class="cancel coluna">Cancelar</a>
				<div class="clear"></div>
			</footer>
		</form>
	<?
	}
	?>
</section>
<script type="text/javascript">
$(document).ready(function(){
	$("form#cadastro-fornecedor").find("input[name='pessoa']").radioSel('<? echo $fornecedor_pessoa; ?>');
	$("form#cadastro-fornecedor").find("input[name='estado']").radioSel('<? echo $fornecedor_estado; ?>');
});
</script>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>