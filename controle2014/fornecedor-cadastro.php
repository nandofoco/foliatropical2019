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

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Cadastro de <span>Fornecedor</span></h1>		
	</header>
	<form id="cadastro-fornecedor" class="cadastro" method="post" action="<? echo SITE; ?>fornecedores/cadastro/post/">
		<section class="secao">
			<section id="fornecedor-pessoa" class="radio infield big">
				<h3>Tipo de Pessoa:</h3>
				<ul>
					<li><label class="item checked"><input type="radio" name="pessoa" value="F" checked="checked" />Física</label>
					<li><label class="item"><input type="radio" name="pessoa" value="J" />Jurídica</label>
					</li>
				</ul>
				<div class="clear"></div>
			</section>
			<p id="fornecedor-nome-box">
				<label for="fornecedor-nome">Nome:</label>
				<input type="text" name="nome" class="input" id="fornecedor-nome" maxlength="40" />
			</p>
			<p id="fornecedor-razao-box">
				<label for="fornecedor-razao">Razão Social:</label>
				<input type="text" name="razao" class="input" id="fornecedor-razao" disabled="disabled" maxlength="40" />
			</p>
			<p>
				<label for="fornecedor-email">Email:</label>
				<input type="text" name="email" class="input" id="fornecedor-email" maxlength="80" />
			</p>
			<p id="fornecedor-cpfcnpj-box">
				<label for="fornecedor-cpfcnpj">CPF:</label>
				<input type="text" name="cpfcnpj" class="input" id="fornecedor-cpfcnpj" />
			</p>
			<p id="fornecedor-inscricao-box">
				<label for="fornecedor-inscricao">Inscrição Estadual:</label>
				<input type="text" name="inscricao" class="input" id="fornecedor-inscricao" />
			</p>	
			<p>
				<label for="fornecedor-telefone">Telefone:</label>
				<input type="text" name="telefone" class="input pequeno" id="fornecedor-telefone" />
			</p>
			<p>
				<label for="fornecedor-cep">CEP:</label>
				<input type="text" name="cep" class="input pequeno" id="fornecedor-cep" />
				<a href="http://www.correios.com.br/servicos/cep/" class="esqueci" target="_blank">Esqueci meu CEP</a>
			</p>
			<p>
				<label for="fornecedor-endereco">Endereço:</label>
				<input type="text" name="endereco" class="input" id="fornecedor-endereco" />
			</p>
			<p>
				<label for="fornecedor-numero">Número:</label>
				<input type="text" name="numero" class="input pequeno" id="fornecedor-numero" />
			</p>
			<p>
				<label for="fornecedor-complemento">Complemento:</label>
				<input type="text" name="complemento" class="input pequeno" id="fornecedor-complemento" />
			</p>
			<p>
				<label for="fornecedor-bairro">Bairro:</label>
				<input type="text" name="bairro" class="input" id="fornecedor-bairro" />
			</p>
			<p>
				<label for="fornecedor-cidade">Cidade:</label>
				<input type="text" name="cidade" class="input" id="fornecedor-cidade" />
			</p>
			
			<section class="selectbox coluna" id="fornecedor-estado">
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
				<input type="text" name="banco" class="input pequeno" id="fornecedor-banco" />
			</p>
			<p>
				<label for="fornecedor-agencia">Agência:</label>
				<input type="text" name="agencia" class="input pequeno" id="fornecedor-agencia" />
			</p>
			<p>
				<label for="fornecedor-conta">Conta:</label>
				<input type="text" name="conta" class="input pequeno" id="fornecedor-conta" />
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
include("conn/close-sankhya.php");

?>