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

$cod = (int) $_GET['c'];

$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT *, CONVERT(CHAR, DTNASC, 103) AS dtnasc FROM TGFPAR WHERE CODPARC='$cod'", $conexao_params, $conexao_options);

?>
<section id="overlay" class="fechar-modal"><span class="loader"></span></section>
<section class="modal-box" id="modal">
	<section class="modal-dialog">
		<section class="modal-content">
			<section id="endereco-box">
				<header>
					<h1>Alterar endereço de combrança</h1>
					<a href="#" class="fechar-modal">&times;</a>
				</header>
				<section id="conteudo">
					<form name="endereco" class="cadastro controle" method="post" id="cadastro-endereco" action="<? echo SITE; ?>checkout-endereco.php?t=editar" data-toggle="validator" role="form">
						<input type="hidden" id="total" value="<? echo $loja_valor_total; ?>">
						<input type="hidden" name="cod" value="<? echo $cod; ?>" />
						<input type="hidden" name="cliente" value="<? echo $loja_cliente; ?>" />
						
						<p class="form-group">
							<label>Tipo de Endereço</label>
							<select name="pais" class="drop" style="width: 340px;">
							<?php foreach ($paises as $key => $pais) { ?>
								<option value="<?php echo $pais['PAIS_SIGLA'] ?>"><?php echo $pais['PAIS_NOME'] ?></option>
							<? } ?>
							</select>
						</p>

						<p class="cep form-group">
							<label for="cep">CEP</label>
							<input type="text" name="cep" class="input pequeno" id="cep" required>
							<a class="busca-cep" href="http://www.buscacep.correios.com.br/" target="_blank">Não sei meu CEP</a>
						</p>
						<p class="zipcode form-group" style="display: none;">
							<label for="cep">Zipcode</label>
							<input type="text" name="zipcode" class="input pequeno" id="zipzode" required>
						</p>

						<div class="coluna">
							<p class="cidade form-group">
								<label for="cidade" class="control-label">Cidade</label>
								<input type="text" name="cidade" class="input" id="cidade" required>
							</p>
							<p class="estado form-group">
								<label for="estado" class="control-label">Estado</label>
								<input type="text" name="estado" class="input" id="estado" required>
							</p>
							<div class="clear"></div>
						</div>

						<p class="form-group">
							<label for="bairro">Bairro</label>
							<input type="text" name="bairro" class="input" id="bairro" required>
						</p>


						<p class="form-group">
							<label for="endereco">Endereço</label>
							<input type="text" name="endereco" class="input" id="endereco" required>
						</p>

						<p class="numero form-group">
							<label for="numero" class="control-label">Número</label>
							<input type="number" name="numero" class="input" id="numero" required>
						</p>
						<p class="complemento form-group">
							<label for="complemento">Complemento</label>
							<input type="text" name="complemento" class="input complemento" id="complemento" />
						</p>
					
						<div class="selectbox coluna pequeno form-group" id="usuario-filial">
							<h3>Tipo de Endereço</h3>
							<a href="#" class="arrow"><strong></strong><span></span></a>
							<ul class="drop">
								<li><label class="item"><input type="radio" name="tipo_endereco" alt="Comercial" value="Comercial" required>Comercial</label></li>
								<li><label class="item"><input type="radio" name="tipo_endereco" alt="Residencial"  value="Residencial" required>Residencial</label></li>
							</ul>
							<div class="clear"></div>
						</div>						
						<footer>
							<input type="submit" class="input submit" value="Salvar endereço" />
							<a href="#" class="cancel no-cancel coluna fechar-modal">Cancelar</a>
						</footer>
						<div class="clear"></div>
					</form>		
				</section>
			</section>
		</section>
	</section>
</section>

<section id="conteudo">
	<header class="titulo">
		<h1>Editar <span>Cliente</span></h1>		
	</header>
	<?
	if(sqlsrv_num_rows($sql_cliente) > 0) {
		$cliente = sqlsrv_fetch_array($sql_cliente);
		$cliente_nome = utf8_encode(trim($cliente['NOMEPARC']));
		$cliente_razao = utf8_encode(trim($cliente['RAZAOSOCIAL']));
		$cliente_pessoa = trim($cliente['TIPPESSOA']);
		$cliente_email = trim($cliente['EMAIL']);
		$cliente_cpfcnpj = formatCPFCNPJ(trim($cliente['CGC_CPF']));
		$cliente_ddi = trim($cliente['DDI']);
		$cliente_ddd = trim($cliente['DDD']);
		$cliente_telefone = trim($cliente['TELEFONE']);
		$cliente_ddi_celular = trim($cliente['DDI_CELULAR']);
		$cliente_ddd_celular = trim($cliente['DDD_CELULAR']);
		$cliente_celular = trim($cliente['FAX']);
		$cliente_sexo = trim($cliente['SEXO']);
		$cliente_datanascimento = trim($cliente['dtnasc']);
		$cliente_passaporte = trim($cliente['AD_IDENTIFICACAO']);
		$cliente_pais = trim($cliente['PAIS_SIGLA']);

		?>
		<form id="cadastro-cliente" class="cadastro controle" method="post" action="<? echo SITE; ?>clientes/cadastro/post/">
			<input type="hidden" name="editar" value="true">
			<input type="hidden" name="cod" value="<? echo $cod; ?>">
			<section class="secao">

				
				<p class="selectbox coluna pequeno" id="pais">
					<label for="pais">Pais:</label>
					<select name="pais" class="drop" style="width: 790px;">
						<?php foreach ($paises as $key => $pais) { ?>
							<option value="<?php echo $pais['PAIS_SIGLA'] ?>" <?php echo $pais['PAIS_SIGLA']==$cliente_pais?"selected":"" ?>><?php echo $pais['PAIS_NOME'] ?></option>
						<? } ?>
					</select>
					<div class="clear"></div>
				</p>

				<section id="cliente-pessoa" class="radio infield big">
					<h3>Tipo de Pessoa:</h3>
					<ul>
						<li><label class="item"><input type="radio" name="pessoa" value="F" id="pessoa_fisica">Física</label></li>
						<li><label class="item"><input type="radio" name="pessoa" value="J" id="pessoa_juridica">Jurídica</label></li>
					</ul>
					<div class="clear"></div>
				</section>

				<div class="clear"></div>
				<p id="cliente-nome-box">
					<label for="cliente-nome">Nome:</label>
					<input type="text" name="nome" class="input" id="cliente-nome" value="<? echo $cliente_nome; ?>" maxlength="40" />
				</p>
				<p id="cliente-razao-box">
					<label for="cliente-razao">Razão Social:</label>
					<input type="text" name="razao" class="input" id="cliente-razao" disabled="disabled" value="<? echo $cliente_razao; ?>" maxlength="40" />
				</p>
				<p>
					<label for="cliente-email">Email:</label>
					<input type="text" name="email" class="input" id="cliente-email" value="<? echo $cliente_email; ?>" maxlength="80" />
				</p>
				<p id="cliente-cpfcnpj-box">
					<label for="<? echo ($cliente_pais == 'BR') ? 'cadastro-cpfcnpj' : 'cadastro-passaporte'; ?>"><? echo ($cliente_pais == 'BR') ? "CPF" : "Passaporte"; ?></label>
					<input type="text" name="<? echo ($cliente_pais == 'BR') ? 'cpfcnpj' : 'passaporte'; ?>" class="input" id="cadastro-<? echo ($cliente_pais == 'BR') ? 'cpfcnpj' : 'passaporte'; ?>" value="<? echo ($cliente_pais == 'BR') ? $cliente_cpfcnpj : $cliente_passaporte; ?>">
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
				<div class="clear"></div>
				<p id="cliente-data-nascimento-box">
					<label for="cliente-data-nascimento">Data de Nascimento:</label>
					<input type="text" name="data-nascimento" class="input pequeno" id="cliente-data-nascimento" value="<? echo $cliente_datanascimento; ?>" />
				</p>
				<section class="selectbox ddi pequeno" id="ddi">
					<p><label for="cliente-ddi">DDI: </label></p>
					<select name="ddi" class="drop" style="width: 240px;">
						<option value=""></option>
						<?php foreach ($paises as $key => $pais) { ?>
							<option value="<?php echo $pais['PAIS_SIGLA'] ?>" <?php echo ($pais['PAIS_SIGLA']==$cliente_ddi)?"selected":"" ?>><?php echo $pais['PAIS_NOME']." +".$pais['PAIS_PHONECODE'] ?></option>
						<? } ?>
					</select>
				</section>
				<p>
					<label for="cliente-ddd">DDD:</label>
					<input type="text" name="ddd" class="input pequeno" id="cliente-ddd" value="<? echo $cliente_ddd; ?>" />
				</p>
				<p>
					<label for="cliente-telefone">Telefone:</label>
					<input type="text" name="telefone" class="input pequeno" id="cliente-telefone" value="<? echo $cliente_telefone; ?>" />
				</p>
				<section class="selectbox ddi pequeno" id="ddi_celular">
					<p><label for="cliente-ddi">DDI:</label></p>
					<select name="ddi_celular" class="drop" style="width: 240px;">
						<option value=""></option>
						<?php foreach ($paises as $key => $pais) { ?>
							<option value="<?php echo $pais['PAIS_SIGLA'] ?>" <?php echo ($pais['PAIS_SIGLA']==$cliente_ddi_celular)?"selected":"" ?>><?php echo $pais['PAIS_NOME']." +".$pais['PAIS_PHONECODE'] ?></option>
						<? } ?>
					</select>
				</section>
				<p>
					<label for="cliente-ddd-celular">DDD:</label>
					<input type="text" name="ddd_celular" class="input pequeno" id="cliente-ddd-celular" value="<? echo $cliente_ddd_celular; ?>" />
				</p>
				<p>
					<label for="cliente-celular">Celular:</label>
					<input type="text" name="celular" class="input pequeno" id="cliente-celular" value="<? echo $cliente_celular; ?>" />
				</p>
			</section>
			<section class="secao">
				<p>
					<label for="cliente-senha">Nova Senha:</label>
					<input type="password" name="senha" class="input pequeno" id="cliente-senha" />
				</p>
				<p>
					<label for="cliente-csenha">Confirmar Senha:</label>
					<input type="password" name="csenha" class="input pequeno" id="cliente-csenha" />
				</p>
			</section>
			<footer class="controle">
				<input type="submit" class="submit coluna" value="Alterar" />
				<a href="#" class="cancel coluna">Cancelar</a>
				<div class="clear"></div>
			</footer>
		</form>
		
		<?

		//Exibir endereços do cliente
		$sql_enderecos = sqlsrv_query($conexao_sankhya, "SELECT * FROM clientes_enderecos WHERE CE_CLIENTE='$cod' AND CE_BLOCK='0' AND D_E_L_E_T_='0'  AND CE_ENDERECO<>''", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_enderecos) > 0) {

		?>
		<section id="cliente-enderecos">
			<h1>Endereços</h1>

			<ul>

			<?
				$i == 1;
				while($enderecos = sqlsrv_fetch_array($sql_enderecos)) {
					$endereco_cod = utf8_encode($enderecos['CE_COD']);
					$endereco_endereco = utf8_encode($enderecos['CE_ENDERECO']);
					$endereco_numero = utf8_encode($enderecos['CE_NUMERO']);
					$endereco_complemento = utf8_encode($enderecos['CE_COMPLEMENTO']);
					$endereco_bairro = utf8_encode($enderecos['CE_BAIRRO']);
					$endereco_cidade = utf8_encode($enderecos['CE_CIDADE']);
					$endereco_estado = utf8_encode($enderecos['CE_ESTADO']);
					$endereco_cep = utf8_encode($enderecos['CE_CEP']);
					$endereco_pais = utf8_encode($enderecos['CE_PAIS']);
					$endereco_tipo_endereco = utf8_encode($enderecos['CE_TIPO_ENDERECO']);

					?>
						<li class="endereco open-modal <? if($i%3 == 0) echo 'last'; ?>" data-cod="<?php echo $endereco_cod ?>" data-endereco="<?php echo $endereco_endereco ?>" data-numero="<?php echo $endereco_numero ?>" data-complemento="<?php echo $endereco_complemento ?>" data-bairro="<?php echo $endereco_bairro ?>" data-cidade="<?php echo $endereco_cidade ?>" data-estado="<?php echo $endereco_estado ?>" data-cep="<?php echo $endereco_cep ?>" data-pais="<?php echo $endereco_pais ?>" data-tipo-endereco="<?php echo $endereco_tipo_endereco ?>">
							<!-- <h3><? echo $endereco_nome_destinatario; ?></h3> -->
							<p><? echo $endereco_endereco.', '.$endereco_numero; ?><br />
							<? echo $endereco_bairro; ?></p>
							<p>CEP <? echo $endereco_cep; ?></p>
							<p><? echo $endereco_cidade.', '.$endereco_estado; ?></p>
						</li>
					<?
					$i++;

				}
			}
			?>
			</ul>
		</section>
		<?


	}
	?>
</section>
<script type="text/javascript">
$(document).ready(function(){
	$("form#cadastro-cliente").find("input[name='pessoa']").radioSel('<? echo $cliente_pessoa; ?>');
	$("form#cadastro-cliente").find("input[name='sexo']").radioSel('<? echo $cliente_sexo; ?>');

	$('#cadastro-cliente select[name="pais"]').change(function(event) {
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

    $(document).on('click','.open-modal',function(){
        $("body").addClass("modal-open");
        $("#overlay,#modal").fadeIn("fast");
    });
    $(document).on('click', 'a.fechar-modal', function(){
        $("body").removeClass('modal-open');
        $("#overlay").fadeOut("fast");
        $("#modal").fadeOut("fast");
        return false;
    });
    
    $('form.cadastro.controle select[name="pais"],select[name="ddi"],select[name="ddi_celular"]').select2().trigger('change');

    // $('#cadastro-endereco select[name="pais"]').select2();

    $('body #cliente-enderecos').on('click','.endereco',function(){
        //preencher o formulário
        $this=$(this);
        
        $('#modal #endereco-box').find('input[name="cod"]').val($this.data('cod')).blur();

        $('#modal #endereco-box').find('select[name="pais"]').val($this.data('pais')).trigger('change');;
        // $('#modal #endereco-box').find('select[name="pais"]').select2("val", $this.data('pais'));
        // $('#modal #endereco-box').find('select[name="pais"]').trigger('change.select2');
        // $('#modal #endereco-box').find('select[name="pais"]').trigger('change');
        // console.log($this.data('pais'),$('#modal #endereco-box').find('select[name="pais"]').val());


        $('#modal #endereco-box').find('input[name="zipcode"]').val($this.data('cep'))

        if($this.data('pais')!="BR"){
        	$('#modal #endereco-box').find('input[name="cep"]').val('');
        	$('#modal #endereco-box').find('input[name="zipcode"]').val($this.data('cep'));
        }else{
        	$('#modal #endereco-box').find('input[name="cep"]').val($this.data('cep'));
        	$('#modal #endereco-box').find('input[name="zipcode"]').val('')
        }
        
        $('#modal #endereco-box').find('input[name="endereco"]').val($this.data('endereco')).blur();
        $('#modal #endereco-box').find('input[name="numero"]').val($this.data('numero')).blur();
        $('#modal #endereco-box').find('input[name="complemento"]').val($this.data('complemento')).blur();
        $('#modal #endereco-box').find('input[name="bairro"]').val($this.data('bairro')).blur();
        $('#modal #endereco-box').find('input[name="cidade"]').val($this.data('cidade')).blur();
        $('#modal #endereco-box').find('input[name="estado"]').val($this.data('estado')).blur();
        $('#modal #endereco-box').find('input[name="tipo_endereco"][value="'+$this.data('tipo-endereco')+'"]').trigger('click');
    });
});
</script>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>