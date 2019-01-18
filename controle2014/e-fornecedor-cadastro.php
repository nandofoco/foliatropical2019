<?

//Verificamos o dominio
include("include/includes.php");

//conexao Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$pessoa = format($_POST['pessoa']);
$nome = format($_POST['nome']);
$razaosocial = format($_POST['razao']);
$email = format(strtolower($_POST['email']));
$cpfcnpj = format($_POST['cpfcnpj']);
$inscricao = format($_POST['inscricao']);
$telefone = format($_POST['telefone']);
$cep = format($_POST['cep']);
$endereco = format($_POST['endereco']);
$numero = format($_POST['numero']);
$complemento = format($_POST['complemento']);
$bairro = format($_POST['bairro']);
$cidade = format($_POST['cidade']);
$estado = (int) $_POST['estado'];
$banco = format($_POST['banco']);
$agencia = format($_POST['agencia']);
$conta = format($_POST['conta']);
$senha = format($_POST['senha']);
$csenha = format($_POST['csenha']);
$editar = (bool) $_POST['editar'];

//Consertar telefone
$cpfcnpj = preg_replace( "@[./-]@", "", $cpfcnpj );
$cep = preg_replace( "@[.-]@", "", $cep );
$telefone = str_replace(' ', '', preg_replace( "@[()_-]@", "", $telefone));
$conta = preg_replace( "@[./-]@", "", $conta );

$resposta = "Ocorreu um erro, tente novamente.";
$erro = false;

//-----------------------------------------------------------------------------//

//Email invalido	
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){	
	$erro = true;
	$resposta = "Por favor digite um e-mail válido";	
}


//-----------------------------------------------------------------------------//

if(!empty($cod) && $editar) $search_cod = " AND CODPARC<>'$cod' ";

//Email já cadastrado
$sql_exist = sqlsrv_query($conexao_sankhya, "SELECT EMAIL FROM TGFPAR WHERE EMAIL='$email' AND FORNECEDOR='S' $search_cod", $conexao_params, $conexao_options);
$n = sqlsrv_num_rows($sql_exist);

if(!$erro && ($n > 0)){
	$erro = true;
	$resposta = "O e-mail informado já foi cadastrado";
}

//-----------------------------------------------------------------------------//

//CPF já cadastrado 
/*$sql_exist_cpf = sqlsrv_query($conexao_sankhya, "SELECT CGC_CPF FROM TGFPAR WHERE CGC_CPF='$cpfcnpj' AND FORNECEDOR='S' $search_cod", $conexao_params, $conexao_options);
$n = sqlsrv_num_rows($sql_exist_cpf);

if(!$erro && ($n > 0)){
	$erro = true;
	$resposta = "O CPF/CNPJ informado já foi cadastrado";
}*/

//-----------------------------------------------------------------------------//

//Senhas diferentes
if(!$erro && ($senha != $csenha)){	
	$erro = true;
	$resposta = "As senhas digitadas estão diferentes";
}

//-----------------------------------------------------------------------------//

// if(!$erro && !$editar && !empty($nome) && !empty($email) && !empty($cpfcnpj) && !empty($telefone) && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($bairro) && !empty($cidade) && !empty($estado)) {
if(!$erro && !$editar && !empty($nome) && !empty($email) && !empty($telefone) && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($bairro) && !empty($cidade) && !empty($estado)) {
	
	// Imposicao SANKHYA
	if($pessoa == 'F') $razaosocial = $nome;
	
	$caracteres_aceitos = 'abcdefghijklmnopqrstuvwxyz0123456789';
	$max = strlen($caracteres_aceitos)-1;
	$codigo_aleatorio = null;
	
	for($i=0; $i < 8; $i++) { $codigo_aleatorio .= $caracteres_aceitos{mt_rand(0, $max)}; }	
	
	$md5_codigo_aleatorio = md5($codigo_aleatorio);

	//busca endereco
	$sql_exist_endereco = sqlsrv_query($conexao_sankhya, "SELECT CODEND, NOMEEND FROM TSIEND WHERE NOMEEND='$endereco'", $conexao_params, $conexao_options);
	$n_end = sqlsrv_num_rows($sql_exist_endereco);

	if($n_end == 0) {
		//inserir endereco 
		$sql_insert_endereco = sqlsrv_query($conexao_sankhya, "INSERT INTO TSIEND (CODEND, NOMEEND, DTALTER) VALUES ((SELECT ISNULL(MAX(CODEND),0) + 1 FROM TSIEND), '$endereco', GETDATE())", $conexao_params, $conexao_options);
			$codend = getLastId(true);
	} else {
		$existend = sqlsrv_fetch_array($sql_exist_endereco);
		$codend = $existend['CODEND'];
	}	

	//busca bairro
	$sql_exist_bairro = sqlsrv_query($conexao_sankhya, "SELECT CODBAI, NOMEBAI FROM TSIBAI WHERE NOMEBAI='$bairro'", $conexao_params, $conexao_options);
	$n_bai = sqlsrv_num_rows($sql_exist_bairro);

	if($n_bai == 0) {
		//inserir bairro 
		$sql_insert_bairro = sqlsrv_query($conexao_sankhya, "INSERT INTO TSIBAI (CODBAI, NOMEBAI, DTALTER) VALUES ((SELECT ISNULL(MAX(CODBAI),0) + 1 FROM TSIBAI), '$bairro', GETDATE())", $conexao_params, $conexao_options);
		$codbai = getLastId(true);
	} else {
		$existbai = sqlsrv_fetch_array($sql_exist_bairro);
		$codbai = $existbai['CODBAI'];
	}			

	//busca cidade
	$sql_exist_cidade = sqlsrv_query($conexao_sankhya, "SELECT CODCID, NOMECID, UF FROM TSICID WHERE NOMECID='$cidade' AND UF='$estado'", $conexao_params, $conexao_options);
	$n_cid = sqlsrv_num_rows($sql_exist_cidade);

	if($n_cid == 0) {
		//inserir cidade
		$sql_insert_cidade = sqlsrv_query($conexao_sankhya, "INSERT INTO TSICID (CODCID, UF, NOMECID, DTALTER) VALUES ((SELECT ISNULL(MAX(CODCID),0) + 1 FROM TSICID),'$estado','$cidade', GETDATE())", $conexao_params, $conexao_options);
		$codcid = getLastId(true);
	} else {
		$existcid = sqlsrv_fetch_array($sql_exist_cidade);
		$codcid = $existcid['CODCID'];
	}	

	$sql_insert = sqlsrv_query($conexao_sankhya, "INSERT INTO TGFPAR (CODPARC, NOMEPARC, RAZAOSOCIAL, IDENTINSCESTAD, EMAIL, TELEFONE, CGC_CPF, TIPPESSOA, CEP, CODEND, NUMEND, COMPLEMENTO, CODBAI, CODCID, CLIENTE, FORNECEDOR, CODBCO, CODAGE, CODCTABCO, DTCAD, DTALTER, BLOQUEAR, AD_SENHA) VALUES ((SELECT ISNULL(MAX(CODPARC),0) + 1 FROM TGFPAR),'$nome','$razaosocial','$inscricao','$email','$telefone','$cpfcnpj','$pessoa','$cep','$codend','$numero','$complemento','$codbai','$codcid','N','S','$banco','$agencia','$conta',GETDATE(),GETDATE(),'N', '$senha')", $conexao_params, $conexao_options);
	$cod = getLastId(true);	

	if(!empty($cod)) {

		require("include/class.phpmailer.php");

		
		$msg = "<body>
		<table width='350' border='0' align='center' cellpadding='0' cellspacing='0'>
		  <tr>
			<td height='70' align='center' valign='top'><img src='".SITE."img/logo-email.png' width='200'height='149'></td>
		  </tr>
		  <tr>
			<td align='left' valign='top'>&nbsp;</td>
		  </tr>
		  <tr>
			<td align='left' valign='top'><p><font face='Arial, Helvetica, sans-serif' color='#333333' size='4'><strong>$nome, você foi cadastrado como parceiro em nosso sistema.</strong></td>
		  </tr>
		  <tr>
			<td align='left' valign='top'>&nbsp;</td>
		  </tr>
		  <tr>
			<td align='left' valign='top'>
			<font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>
			Para acessar sua área de parceiro utilize os seguintes dados: <br/>
			<strong>Login:</strong> $cpfcnpj <br/>
			<strong>Senha:</strong> $codigo_aleatorio <br/>			
			</font></td>
		  </tr>
		  <tr>
			<td align='left' valign='top'>&nbsp;</td>
		  </tr>
		  <tr>
			<td align='center' height='30'><a href='".SITE."' target='_blank' style='text-decoration: none; color: #000;'><font face='Arial, Helvetica, sans-serif' color='#000' size='3'><strong>www.foliatropical.com.br</strong></font></a></td>
		  </tr>
		</table>
		</body>";
		
		/*$mail = new PHPMailer();
		$mail->IsSMTP();        //ENVIAR VIA SMTP
		$mail->SMTPAuth = true; //ATIVA O SMTP AUTENTICADO
		$mail->IsHTML(true);        //ATIVA MENSAGEM NO FORMATO TXT, SE true ATIVA NO FORMATO HTML
		
		$mail->Host     = "smtp.gmail.com";     //SERVIDOR DE SMTP, USE mail.SeuDominio.com OU smtp.dominio.com.br	
		$mail->Username = "sistema@grupopacifica.com.br"; //EMAIL PARA SMTP AUTENTICADO (pode ser qualquer conta de email do seu domínio)
		$mail->Password = "sistema2013";                //SENHA DO EMAIL PARA SMTP AUTENTICADO
		$mail->SetFrom("central@grupopacifica.com.br",utf8_decode("Grupo Pacífica"));    //E-MAIL DO REMETENTE, NOME DO REMETENTE
		
		$mail->SMTPSecure = "tls";
		$mail->Host       = "smtp.gmail.com";
		$mail->Port       = 587;	
		
		$mail->AddAddress($email,utf8_decode($nome)); //E-MAIL DO DESINATÁRIO, NOME DO DESINATÁRIO
		$mail->AddReplyTo("central@grupopacifica.com.br",utf8_decode("Grupo Pacífica")); //CONFIGURA O E-MAIL QUE RECEBERÁ A RESPOSTA DESTA MENSAGEM
		
		$mail->Subject = "Folia Tropical - Cadastro de Fornecedor";  //ASSUNTO DA MENSAGEM
		$mail->Body    = utf8_decode($msg); //CONTEÚDO DA MENSAGEM
		
		$mail->Send();*/

		$remetente_nome = utf8_decode("Grupo Pacífica");
		$remetente_email = 'central@grupopacifica.com.br';
		$destinatario_nome = utf8_decode($nome);
		$destinatario_email = $email;
		$assunto = utf8_decode("Folia Tropical - Cadastro de Fornecedor");
		$mensagem = utf8_decode($msg);

		// enviarEmail($remetente_nome, $remetente_email, $destinatario_nome, $destinatario_email, $assunto, $mensagem);

		

		$resposta = "Fornecedor cadastrado com sucesso.";
	}
	?>
	<script type="text/javascript">
		alert('<? echo $resposta; ?>');
		location.href='<? echo SITE; ?>fornecedores/';
	</script>
	<?
	exit();

// } elseif(!$erro && $editar && !empty($cod) && !empty($email) && !empty($cpfcnpj) && !empty($telefone) && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($bairro) && !empty($cidade) && !empty($estado)) {
} elseif(!$erro && $editar && !empty($cod) && !empty($email) && !empty($telefone) && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($bairro) && !empty($cidade) && !empty($estado)) {

	if(!empty($senha)) {
		$senha = md5($senha);
		$altera_senha = ", AD_SENHA='$senha'";
	}

	//busca endereco
	$sql_exist_endereco = sqlsrv_query($conexao_sankhya, "SELECT CODEND, NOMEEND FROM TSIEND WHERE NOMEEND='$endereco'", $conexao_params, $conexao_options);
	$n_end = sqlsrv_num_rows($sql_exist_endereco);

	if($n_end == 0) {
		//inserir endereco 
		$sql_insert_endereco = sqlsrv_query($conexao_sankhya, "INSERT INTO TSIEND (CODEND, NOMEEND, DTALTER) VALUES (((SELECT MAX(CODEND) FROM TSIEND) + 1), '$endereco', GETDATE())", $conexao_params, $conexao_options);
			$codend = getLastId(true);
	} else {
		$existend = sqlsrv_fetch_array($sql_exist_endereco);
		$codend = $existend['CODEND'];
	}	

	//busca bairro
	$sql_exist_bairro = sqlsrv_query($conexao_sankhya, "SELECT CODBAI, NOMEBAI FROM TSIBAI WHERE NOMEBAI='$bairro'", $conexao_params, $conexao_options);
	$n_bai = sqlsrv_num_rows($sql_exist_bairro);

	if($n_bai == 0) {
		//inserir bairro 
		$sql_insert_bairro = sqlsrv_query($conexao_sankhya, "INSERT INTO TSIBAI (CODBAI, NOMEBAI, DTALTER) VALUES (((SELECT MAX(CODBAI) FROM TSIBAI) + 1), '$bairro', GETDATE())", $conexao_params, $conexao_options);
		$codbai = getLastId(true);
	} else {
		$existbai = sqlsrv_fetch_array($sql_exist_bairro);
		$codbai = $existbai['CODBAI'];
	}			

	//busca cidade
	$sql_exist_cidade = sqlsrv_query($conexao_sankhya, "SELECT CODCID, NOMECID, UF FROM TSICID WHERE NOMECID='$cidade' AND UF='$estado'", $conexao_params, $conexao_options);
	$n_cid = sqlsrv_num_rows($sql_exist_cidade);

	if($n_cid == 0) {
		//inserir cidade
		$sql_insert_cidade = sqlsrv_query($conexao_sankhya, "INSERT INTO TSICID (CODCID, UF, NOMECID, DTALTER) VALUES (((SELECT MAX(CODCID) FROM TSICID) + 1),'$estado','$cidade', GETDATE())", $conexao_params, $conexao_options);
		$codcid = getLastId(true);
	} else {
		$existcid = sqlsrv_fetch_array($sql_exist_cidade);
		$codcid = $existcid['CODCID'];
	}	

	$sql_update = sqlsrv_query($conexao_sankhya, "UPDATE TOP (1) TGFPAR SET NOMEPARC='$nome', RAZAOSOCIAL='$razaosocial', IDENTINSCESTAD='$inscricao', EMAIL='$email', TELEFONE='$telefone', CGC_CPF='$cpfcnpj', TIPPESSOA='$pessoa', CEP='$cep', CODEND='$codend', NUMEND='$numero', COMPLEMENTO='$complemento', CODBAI='$codbai', CODCID='$codcid', CODBCO='$banco', CODAGE='$agencia', CODCTABCO='$banco', DTALTER=GETDATE() $altera_senha WHERE CODPARC='$cod'", $conexao_params, $conexao_options);

	$resposta = "Parceiro alterado com sucesso.";
	?>
	<script type="text/javascript">
		alert('<? echo $resposta; ?>');
		location.href='<? echo SITE; ?>fornecedores/editar/<? echo $cod; ?>/';
	</script>
	<?

	//Fechar conexoes
	include("conn/close.php");
	include("conn/close-sankhya.php");
	
	exit();
}
?>
<script type="text/javascript">
	alert('<? echo $resposta; ?>');
	location.href='<? echo SITE; ?>fornecedores/';
</script>