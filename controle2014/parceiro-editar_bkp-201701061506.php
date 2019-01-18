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

$sql_parceiro = sqlsrv_query($conexao_sankhya, "SELECT p.CODPARC, p.NOMEPARC, p.RAZAOSOCIAL, p.IDENTINSCESTAD, p.EMAIL, p.TELEFONE, p.CGC_CPF, p.TIPPESSOA, p.CEP, p.CODEND, p.NUMEND, p.COMPLEMENTO, p.CODBAI, p.CODCID, p.VENDEDOR, p.CODBCO, p.CODAGE, p.CODCTABCO, p.DTCAD, p.DTALTER, p.BLOQUEAR, p.AD_COMISSAO, p.TIPO, p.DESCONTO, c.CODCID, c.NOMECID, c.UF, u.CODUF, u.UF, e.CODEND, e.NOMEEND, b.CODBAI, b.NOMEBAI FROM TGFPAR p, TSICID c, TSIUFS u, TSIEND e, TSIBAI b WHERE p.CODPARC='$cod' AND p.CODCID=c.CODCID AND c.UF=u.CODUF AND p.CODEND=e.CODEND AND p.CODBAI=b.CODBAI AND p.VENDEDOR='S'", $conexao_params, $conexao_options);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Editar <span>Parceiro</span></h1>		
	</header>
	<?
	if(sqlsrv_num_rows($sql_parceiro) > 0) {

		$parceiro = sqlsrv_fetch_array($sql_parceiro);
		$parceiro_nome = trim(utf8_encode($parceiro['NOMEPARC']));
		$parceiro_razao = trim(utf8_encode($parceiro['RAZAOSOCIAL']));
		$parceiro_pessoa = trim(utf8_encode($parceiro['TIPPESSOA']));
		$parceiro_email = trim($parceiro['EMAIL']);
		$parceiro_cpfcnpj = formatCPFCNPJ(trim($parceiro['CGC_CPF']));
		$parceiro_inscricao = trim($parceiro['IDENTINSCESTAD']);
		$parceiro_telefone = formatTelefone(trim($parceiro['TELEFONE']));
		$parceiro_cep = trim($parceiro['CEP']);
		$parceiro_endereco = trim(utf8_encode($parceiro['NOMEEND']));
		$parceiro_numero = trim($parceiro['NUMEND']);
		$parceiro_complemento = trim($parceiro['COMPLEMENTO']);
		$parceiro_bairro = trim(utf8_encode($parceiro['NOMEBAI']));
		$parceiro_cidade = trim(utf8_encode($parceiro['NOMECID']));
		$parceiro_estado = trim(utf8_encode($parceiro['CODUF']));
		$parceiro_tipo = $parceiro['TIPO'];
		$parceiro_grupo = $parceiro['GRUPO'];
		$parceiro_banco = trim($parceiro['CODBCO']);
		$parceiro_agencia = trim($parceiro['CODAGE']);
		$parceiro_conta = trim($parceiro['CODCTABCO']);
		$parceiro_comissao = $parceiro['AD_COMISSAO'];
		$parceiro_cupom = trim(utf8_encode($parceiro['CUPOM']));
		$parceiro_desconto = (int) $parceiro['DESCONTO'];
	?>
		<form id="cadastro-parceiro" class="cadastro" method="post" action="<? echo SITE; ?>parceiros/cadastro/post/">
			<input type="hidden" name="editar" value="true">
			<input type="hidden" name="cod" value="<? echo $cod; ?>">
			<section class="secao">
				<section id="parceiro-pessoa" class="radio infield big">
					<h3>Tipo de Pessoa:</h3>
					<ul>
						<li><label class="item"><input type="radio" name="pessoa" value="F" />Física</label>
						<li><label class="item"><input type="radio" name="pessoa" value="J" />Jurídica</label>
						</li>
					</ul>
					<div class="clear"></div>
				</section>
				<p id="parceiro-nome-box">
					<label for="parceiro-nome">Nome:</label>
					<input type="text" name="nome" class="input" id="parceiro-nome" value="<? echo $parceiro_nome; ?>" maxlength="40" />
				</p>
				<p id="parceiro-razao-box">
					<label for="parceiro-razao">Razão Social:</label>
					<input type="text" name="razao" class="input" id="parceiro-razao" disabled="disabled" value="<? echo $parceiro_razao; ?>" maxlength="40" />
				</p>
				<p>
					<label for="parceiro-email">Email:</label>
					<input type="text" name="email" class="input" id="parceiro-email" value="<? echo $parceiro_email; ?>" maxlength="80" />
				</p>
				<p id="parceiro-cpfcnpj-box">
					<label for="parceiro-cpfcnpj">CPF:</label>					
					<input type="text" name="cpfcnpj" class="input" id="parceiro-cpfcnpj" value="<? echo $parceiro_cpfcnpj; ?>" />
				</p>
				<p id="parceiro-inscricao-box">
					<label for="parceiro-inscricao">Inscrição Estadual:</label>
					<input type="text" name="inscricao" class="input" id="parceiro-inscricao" value="<? echo $parceiro_inscricao; ?>" />
				</p>	
				<p>
					<label for="parceiro-telefone">Telefone:</label>
					<input type="text" name="telefone" class="input pequeno" id="parceiro-telefone" value="<? echo $parceiro_telefone; ?>" />
				</p>
				<p>
					<label for="parceiro-cep">CEP:</label>
					<input type="text" name="cep" class="input pequeno" id="parceiro-cep" value="<? echo $parceiro_cep; ?>" />
					<a href="http://www.correios.com.br/servicos/cep/" class="esqueci" target="_blank">Esqueci meu CEP</a>
				</p>
				<p>
					<label for="parceiro-endereco">Endereço:</label>
					<input type="text" name="endereco" class="input" id="parceiro-endereco" value="<? echo $parceiro_endereco; ?>" />
				</p>
				<p>
					<label for="parceiro-numero">Número:</label>
					<input type="text" name="numero" class="input pequeno" id="parceiro-numero" value="<? echo $parceiro_numero; ?>" />
				</p>
				<p>
					<label for="parceiro-complemento">Complemento:</label>
					<input type="text" name="complemento" class="input pequeno" id="parceiro-complemento" value="<? echo $parceiro_complemento; ?>" />
				</p>
				<p>
					<label for="parceiro-bairro">Bairro:</label>
					<input type="text" name="bairro" class="input" id="parceiro-bairro" value="<? echo $parceiro_bairro; ?>" />
				</p>
				<p> 
					<label for="parceiro-cidade">Cidade:</label>
					<input type="text" name="cidade" class="input" id="parceiro-cidade" value="<? echo $parceiro_cidade; ?>" />
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
					</ul>
					<div class="clear"></div>
				</section>
				<div class="clear"></div>
				<p>
					<label for="parceiro-comissao">Comissão (%):</label>
					<input type="text" name="comissao" class="input pequeno" id="parceiro-comissao" value="<? echo $parceiro_comissao; ?>" />
				</p>
				<?

				// Cupom
				$parceiro_cupom = empty($parceiro_cupom) ? substr(str_replace('-', '', toAscii($parceiro_nome)), 0, 10) : str_replace('FOLIA', '', $parceiro_cupom);

				?>
				<p>
					<label for="parceiro-cupom">Código Parceria:</label>
					<span class="parceiro-cupom">Folia</span>
					<input type="text" name="cupom" class="input pequeno" id="parceiro-cupom" value="<? echo $parceiro_cupom; ?>" />
				</p>

				<p>
					<label for="parceiro-desconto">Desconto (%):</label>
					<input type="text" name="desconto" class="input pequeno" id="parceiro-desconto" value="<? echo $parceiro_desconto; ?>" />
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
					<input type="text" name="agencia" class="input pequeno" id="parceiro-agencia" value="<? echo $parceiro_agencia; ?>" />
				</p>
				<p>
					<label for="parceiro-conta">Conta:</label>
					<input type="text" name="conta" class="input pequeno" id="parceiro-conta" value="<? echo $parceiro_conta; ?>" />
				</p>
			</section>
			<header class="titulo">
				<h1>Dados de<span> Acesso</span></h1>		
			</header>
			<section class="secao">
				<p>
					<label for="parceiro-cpfcnpj">CPF/CNPJ:</label>
					<input type="text" name="cpfcnpj" class="input pequeno" id="parceiro-cpfcnpj" value="<? echo $parceiro_cpfcnpj; ?>" />
				</p>
				<p>
					<label for="parceiro-senha">Nova Senha:</label>
					<input type="password" name="senha" class="input pequeno" id="parceiro-senha" />
				</p>
				<p>
					<label for="parceiro-csenha">Confirmar Senha:</label>
					<input type="password" name="csenha" class="input pequeno" id="parceiro-csenha" />
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
	$("form#cadastro-parceiro").find("input[name='pessoa']").radioSel('<? echo $parceiro_pessoa; ?>');
	$("form#cadastro-parceiro").find("input[name='estado']").radioSel('<? echo $parceiro_estado; ?>');
	$("form#cadastro-parceiro").find("input[name='banco']").radioSel('<? echo $parceiro_banco; ?>');
	/*$("form#cadastro-parceiro").find("input[name='grupo']").radioSel('<? echo $parceiro_grupo; ?>');*/
	$("form#cadastro-parceiro").find("input[name='tipo']").radioSel('<? echo $parceiro_tipo; ?>');
});
</script>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>