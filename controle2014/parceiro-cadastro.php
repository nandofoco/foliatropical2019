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
		<h1>Cadastro de <span>Parceiros</span></h1>		
	</header>
	<form id="cadastro-parceiro" class="cadastro" method="post" action="<? echo SITE; ?>parceiros/cadastro/post/">
		<section class="secao">
			<section id="parceiro-pessoa" class="radio infield big">
				<h3>Tipo de Pessoa:</h3>
				<ul>
					<li><label class="item checked"><input type="radio" name="pessoa" value="F" checked="checked" />Física</label></li>
					<li><label class="item"><input type="radio" name="pessoa" value="J" />Jurídica</label></li>
				</ul>
				<div class="clear"></div>
			</section>
			<p id="parceiro-nome-box">
				<label for="parceiro-nome">Nome:</label>
				<input type="text" name="nome" class="input" id="parceiro-nome" maxlength="40" />
			</p>
			<p id="parceiro-razao-box">
				<label for="parceiro-razao">Razão Social:</label>
				<input type="text" name="razao" class="input" id="parceiro-razao" disabled="disabled" maxlength="40" />
			</p>
			<p>
				<label for="parceiro-email">Email:</label>
				<input type="text" name="email" class="input" id="parceiro-email" maxlength="80" />
			</p>
			<p id="parceiro-cpfcnpj-box">
				<label for="parceiro-cpfcnpj">CPF:</label>
				<input type="text" name="cpfcnpj" class="input" id="parceiro-cpfcnpj" />
			</p>
			<p id="parceiro-inscricao-box">
				<label for="parceiro-inscricao">Inscrição Estadual:</label>
				<input type="text" name="inscricao" class="input" id="parceiro-inscricao" />
			</p>	
			<p>
				<label for="parceiro-telefone">Telefone:</label>
				<input type="text" name="telefone" class="input pequeno" id="parceiro-telefone" />
			</p>
			<p>
				<label for="parceiro-cep">CEP:</label>
				<input type="text" name="cep" class="input pequeno" id="parceiro-cep" />
				<a href="http://www.correios.com.br/servicos/cep/" class="esqueci" target="_blank">Esqueci meu CEP</a>
			</p>
			<p>
				<label for="parceiro-endereco">Endereço:</label>
				<input type="text" name="endereco" class="input" id="parceiro-endereco" />
			</p>
			<p>
				<label for="parceiro-numero">Número:</label>
				<input type="text" name="numero" class="input pequeno" id="parceiro-numero" />
			</p>
			<p>
				<label for="parceiro-complemento">Complemento:</label>
				<input type="text" name="complemento" class="input pequeno" id="parceiro-complemento" />
			</p>
			<p>
				<label for="parceiro-bairro">Bairro:</label>
				<input type="text" name="bairro" class="input" id="parceiro-bairro" />
			</p>
			<p>
				<label for="parceiro-cidade">Cidade:</label>
				<input type="text" name="cidade" class="input" id="parceiro-cidade" />
			</p>
			
			<section class="selectbox coluna" id="parceiros-estado">
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

			<? /*<section class="selectbox coluna" id="parceiros-grupo">
				<h3>Grupo:</h3>
				<a href="#" class="arrow"><strong>Selecione</strong><span></span></a>
				<ul class="drop">
                    <li><label class="item"><input type="radio" name="grupo" alt="Parceiro Autorizado" value="1">Parceiro Autorizado</label></li>
                    <li><label class="item"><input type="radio" name="grupo" alt="Parceiro Revendedor" value="2">Parceiro Revendedor</label></li>                   
				</ul>
				<div class="clear"></div>
			</section>
			<div class="clear"></div>*/ ?>
			<section class="selectbox coluna" id="parceiros-tipo">
				<h3>Tipo:</h3>
				<a href="#" class="arrow"><strong>Selecione</strong><span></span></a>
				<ul class="drop">
                    <li><label class="item"><input type="radio" name="tipo" alt="Agência" value="agencia">Agência</label></li>
                    <li><label class="item"><input type="radio" name="tipo" alt="Hotel" value="hotel">Hotel</label></li>
                    <!--<li><label class="item"><input type="radio" name="tipo" alt="Albergue" value="albergue">Albergue</label></li>
                    <li><label class="item"><input type="radio" name="tipo" alt="Operadora" value="operadora">Operadora</label></li>-->
                    <li><label class="item"><input type="radio" name="tipo" alt="Freelancer" value="freelancer">Freelancer</label></li>  
                    <li><label class="item"><input type="radio" name="tipo" alt="Ticketeria" value="ticketeria">Ticketeria</label></li>  
                    <li><label class="item"><input type="radio" name="tipo" alt="Operadora" value="operadora">Operadora</label></li>  
                    <li><label class="item"><input type="radio" name="tipo" alt="Parceiros" value="parceiros">Parceiros</label></li>  
				</ul>
				<div class="clear"></div>
			</section>
			<div class="clear"></div>
			<p>
				<label for="parceiro-comissao">Comissão (%):</label>
				<input type="text" name="comissao" class="input pequeno" id="parceiro-comissao" />
			</p>

			<p>
				<label for="parceiro-desconto">Desconto (%):</label>
				<input type="text" name="desconto" class="input pequeno" id="parceiro-desconto" />
			</p>		
			<div class="clear"></div>
		</section>
		<header class="titulo">
			<h1>Dados <span>Bancários</span></h1>		
		</header>
		<section class="secao">
			<section class="selectbox coluna" id="parceiros-banco">
				<h3>Banco:</h3>
				<a href="#" class="arrow"><strong>Selecione</strong><span></span></a>
				<ul class="drop">
					<?
					$sql_bancos = sqlsrv_query($conexao_sankhya, "SELECT CODBCO, NOMEBCO FROM TSIBCO ORDER BY NOMEBCO ASC", $conexao_params, $conexao_options);
					$n_bancos = sqlsrv_num_rows($sql_bancos);

					if($n_bancos > 0) {
					while($bancos = sqlsrv_fetch_array($sql_bancos)) {
						$banco_cod = $bancos['CODBCO'];
						$banco_nome = strip_tags(utf8_encode(trim($bancos['NOMEBCO'])));
					?>
                    	<li><label class="item"><input type="radio" name="banco" alt="<? echo $banco_cod.' - '.$banco_nome; ?>" value="<? echo $banco_cod; ?>"><? echo $banco_cod.' - '.$banco_nome; ?></label></li>
					<?
						}
					}
					?>                       
				</ul>
				<div class="clear"></div>
			</section>
			<div class="clear"></div>
			<p>
				<label for="parceiro-agencia">Agência:</label>
				<input type="text" name="agencia" class="input pequeno" id="parceiro-agencia" />
			</p>
			<p>
				<label for="parceiro-conta">Conta:</label>
				<input type="text" name="conta" class="input pequeno" id="parceiro-conta" />
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