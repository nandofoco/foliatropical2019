<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//busca paises
$sql_paises= sqlsrv_query($conexao_sankhya, "SELECT * FROM pais", $conexao_params, $conexao_options);
$paises=array();
while($linha = sqlsrv_fetch_array($sql_paises)){
	array_push($paises, $linha);
}


//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Cadastro de <span>Clientes</span></h1>		
	</header>
	<form id="cadastro-cliente" class="cadastro controle" method="post" action="<? echo SITE; ?>clientes/cadastro/post/">
		<section class="secao">
			<p class="selectbox coluna pequeno" id="pais">
				<label for="pais">Pais:</label>
				<select name="pais" class="drop" style="width: 790px;">
					<?php foreach ($paises as $key => $pais) { ?>
						<option value="<?php echo $pais['PAIS_SIGLA'] ?>" <?php echo $pais['PAIS_SIGLA']=="BR"?"selected":"" ?>><?php echo $pais['PAIS_NOME'] ?></option>
					<? } ?>
				</select>
				<div class="clear"></div>
			</p>

			<section id="cliente-pessoa" class="radio infield big">
				<h3>Tipo de Pessoa:</h3>
				<ul>
					<li><label class="item checked"><input type="radio" name="pessoa" value="F" checked="checked" id="pessoa_fisica">Física</label></li>
					<li><label class="item"><input type="radio" name="pessoa" value="J" id="pessoa_juridica">Jurídica</label></li>
				</ul>
				<div class="clear"></div>
			</section>

			<div class="clear"></div>
			<p id="cliente-nome-box">
				<label for="cliente-nome">Nome:</label>
				<input type="text" name="nome" class="input" id="cliente-nome" maxlength="40" />
			</p>
			<p id="cliente-sobrenome-box">
				<label for="cliente-sobrenome">Sobrenome:</label>
				<input type="text" name="sobrenome" class="input" id="cliente-sobrenome" maxlength="40" />
			</p>
			<p id="cliente-razao-box">
				<label for="cliente-razao">Razão Social:</label>
				<input type="text" name="razao" class="input" id="cliente-razao" disabled="disabled" maxlength="40" />
			</p>
			<p>
				<label for="cliente-email">Email:</label>
				<input type="text" name="email" class="input" id="cliente-email" maxlength="80" />
			</p>
			<p id="cliente-cpfcnpj-box">
				<label for="cliente-cpfcnpj">CPF</label>
				<input type="text" name="cpfcnpj" class="input" id="cliente-cpfcnpj" />
			</p>
			<section class="selectbox coluna pequeno" id="cliente-sexo">
				<h3>Sexo:</h3>
				<a href="#" class="arrow"><strong>Sexo</strong><span></span></a>
				<ul class="drop">
                    <li><label class="item"><input type="radio" name="sexo" alt="Masculino" value="M">Masculino</label></li>
                    <li><label class="item"><input type="radio" name="sexo" alt="Feminino" value="F">Feminino</label></li>                   
				</ul>
				<div class="clear"></div>
			</section>
			<section class="selectbox coluna pequeno" id="cliente-origem">
				<h3>Origem:</h3>
				<a href="#" class="arrow"><strong>Origem</strong><span></span></a>
				<ul class="drop">
                    <li><label class="item"><input type="radio" name="origem" alt="Sites de busca" value="Sites de busca">Sites de busca</label></li>
                    <li><label class="item"><input type="radio" name="origem" alt="Redes Sociais" value="Redes Sociais">Redes Sociais</label></li>                 
                    <li><label class="item"><input type="radio" name="origem" alt="Indicação de amigo" value="Indicação de amigo">Indicação de amigo</label></li>                 
                    <li><label class="item"><input type="radio" name="origem" alt="Rádio" value="Rádio">Rádio</label></li>                 
                    <li><label class="item"><input type="radio" name="origem" alt="TV" value="TV">TV</label></li>                 
                    <li><label class="item"><input type="radio" name="origem" alt="Agência" value="Agência">Agência</label></li>                 
                    <li><label class="item"><input type="radio" name="origem" alt="Hotel" value="Hotel">Hotel</label></li>                 
                    <li><label class="item"><input type="radio" name="origem" alt="Outros" value="Outros">Outros</label></li>                   
				</ul>
				<div class="clear"></div>
			</section>
			<div class="clear"></div>
			<p id="cliente-data-nascimento-box">
				<label for="cliente-data-nascimento">Data de Nascimento:</label>
				<input type="text" name="data-nascimento" class="input pequeno" id="cliente-data-nascimento" value="<? echo $cliente_datanascimento; ?>" />
			</p>
			<section class="selectbox ddi pequeno" id="ddi">
				<p><label for="cliente-ddi">DDI:</label></p>
				<select name="ddi" class="drop" style="width: 240px;" >
					<?php foreach ($paises as $key => $pais) { ?>
						<option value="<?php echo $pais['PAIS_SIGLA'] ?>" <?php echo $pais['PAIS_SIGLA']=="BR"?"selected":"" ?>><?php echo $pais['PAIS_NOME']." +".$pais['PAIS_PHONECODE'] ?></option>
					<? } ?>
				</select>
			</section>
			<p>
				<label for="cliente-ddd">DDD:</label>
				<input type="text" name="ddd" maxlength="2" class="input pequeno" id="cliente-ddd" />
			</p>
			<p>
				<label for="cliente-telefone">Telefone:</label>
				<input type="text" name="telefone" class="input pequeno" id="cliente-telefone" />
			</p>
			<section class="selectbox ddi pequeno" id="ddi_celular">
				<p><label for="cliente-ddi-celular">DDI:</label></p>
				<select name="ddi_celular" class="drop" style="width: 240px;">
					<?php foreach ($paises as $key => $pais) { ?>
						<option value="<?php echo $pais['PAIS_PHONECODE'] ?>" <?php echo $pais['PAIS_SIGLA']=="BR"?"selected":"" ?>><?php echo $pais['PAIS_NOME']." +".$pais['PAIS_PHONECODE'] ?></option>
					<? } ?>
				</select>
			</section>
			<p>
				<label for="cliente-ddd-celular">DDD:</label>
				<input type="text" name="ddd_celular"  maxlength="2" class="input pequeno" id="cliente-ddd-celular" />
			</p>
			<p>
				<label for="cliente-celular">Celular:</label>
				<input type="text" name="celular" class="input pequeno" id="cliente-celular" />
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
	$(document).ready(function(){
		$('select[name="pais"],select[name="ddi"],select[name="ddi_celular"]').select2().trigger('change');

		$('select[name="pais"]').change(function(event) {
            var pais = $(this).val();
            if(pais=="BR"){
                $('#cliente-cpfcnpj-box').find('input').attr('name','cpfcnpj').mask('999.999.999-99');
                $('#cliente-cpfcnpj-box').find('label').html('CPF:');
                $('#cliente-pessoa').show();
            }else{
                $('#pessoa_fisica').closest('.item').trigger('click');
                $('#cliente-cpfcnpj-box').find('label').html('Passaporte:');
                $('#cliente-cpfcnpj-box').find('input').attr('name','passaporte').unmask();
                $('#cliente-pessoa').hide();
            }
        });
	});
</script>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>