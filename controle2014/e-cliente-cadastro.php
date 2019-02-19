<?

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$pessoa = format($_POST['pessoa']);
$pais = format($_POST['pais']);
$nome = format($_POST['nome']);
$sobrenome = format($_POST['sobrenome']);
$razao	 = format($_POST['razao']);
$origem	 = format($_POST['origem']);
$email = format($_POST['email']);
$cpfcnpj = format($_POST['cpfcnpj']);
$passaporte = format($_POST['passaporte']);
$sexo = format($_POST['sexo']);
$data_nascimento = empty($_POST['data-nascimento'])?"NULL":"'".todate($_POST['data-nascimento'], "ddmmaaaa")."'";
$ddi = format($_POST['ddi']);
$ddd = format($_POST['ddd']);
$telefone = format($_POST['telefone']);
$ddi_celular = format($_POST['ddi_celular']);
$ddd_celular = format($_POST['ddd_celular']);
$celular = format($_POST['celular']);

$editar = (bool) $_POST['editar'];
$senha = format($_POST['senha']);
$csenha = format($_POST['csenha']);

//Consertar o telefone
$cpfcnpj = preg_replace( "@[./-]@", "", $cpfcnpj );
// $cep = preg_replace( "@[-]@", "", $cep );
$telefone = str_replace(' ', '', preg_replace( "@[()_-]@", "", $telefone));
$celular = str_replace(' ', '', preg_replace( "@[()_-]@", "", $celular));


//-----------------------------------------------------------------------------//

$resposta = "Ocorreu um erro, tente novamente.";
$erro = false;

//-----------------------------------------------------------------------------//

//Email invalido	
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){	
	$erro = true;
	$resposta = "Por favor digite um e-mail válido";	
}

//-----------------------------------------------------------------------------//

if(is_numeric($cod) && $editar) $search_cod = " AND CODPARC<>'$cod' ";

//Email já cadastrado
$sql_exist = sqlsrv_query($conexao_sankhya, "SELECT EMAIL FROM TGFPAR WHERE EMAIL='$email' AND CLIENTE='S' $search_cod", $conexao_params, $conexao_options);
$n = sqlsrv_num_rows($sql_exist);

if(!$erro && ($n > 0)){
	$erro = true;
	$resposta = "O e-mail informado já foi cadastrado";
}

//-----------------------------------------------------------------------------//

//CPF já cadastrado
// if($pais=="BR"){
// 	$sql_exist_cpf = sqlsrv_query($conexao_sankhya, "SELECT CGC_CPF FROM TGFPAR WHERE CGC_CPF='$cpfcnpj' AND CLIENTE='S' $search_cod", $conexao_params, $conexao_options);
// 	$n = sqlsrv_num_rows($sql_exist_cpf);

// 	if(!$erro && ($n > 0)){
// 		$label_cpfcnpj = ($pessoa == 'F') ? 'CPF' : 'CNPJ';
// 		$erro = true;
// 		$_SESSION['ALERT'] = array('erro',"O $label_cpfcnpj informado já foi cadastrado");
// 	}
// }

//-----------------------------------------------------------------------------//

//Senhas diferentes
if($editar) {
	if(!$erro && ($senha != $csenha)){	
		$erro = true;
		$resposta = "As senhas digitadas estão diferentes";
	}
}

if(!$erro && empty($ddi_celular)){	
	$erro = true;
	$resposta = "Preencha o DDI (País) do celular.";
}
if(!$erro && empty($ddd_celular)){	
	$erro = true;
	$resposta = "Preencha o DDD do celular.";
}

//-----------------------------------------------------------------------------//
if(!$erro && (empty($nome) || empty($pessoa) || empty($email)|| empty($ddd_celular) || /*($pais=="BR" && empty($cpfcnpj)) || ($pais!="BR" && empty($passaporte)) ||*/ empty($celular) || empty($pais))){
	echo "AAA";
	$erro = true;
	$resposta = "Preencha todas as informações.";
}

if(!$erro && !$editar) {
// if(!$erro && !$editar && !empty($nome) && !empty($pessoa) && !empty($email) && !empty($telefone)) {

	// Limitamos a 40 caracteres
	if(!empty($sobrenome)) $nome .= ' '.$sobrenome;
	if(strlen($nome) > 40) $nome = substr($nome, 0, 40);
	
	// Imposicao SANKHYA
	if($pessoa == 'F') $razao = $nome;
	
	$caracteres_aceitos = 'abcdefghijklmnopqrstuvwxyz0123456789'; 
	$max = strlen($caracteres_aceitos)-1;
	$codigo_aleatorio = null;
	
	for($i=0; $i < 8; $i++) { $codigo_aleatorio .= $caracteres_aceitos{mt_rand(0, $max)}; }	
	
	$md5_codigo_aleatorio = md5($codigo_aleatorio);
	
	// $sql_insert = sqlsrv_query($conexao_sankhya, "INSERT INTO clientes (CL_NOME, CL_EMAIL, CL_SENHA, CL_TEL, CL_CEL, CL_CPF, CL_RG, CL_DTNASC, CL_SEXO, CL_FACEBOOK, CL_SKYPE) VALUES ('$nome','$email','$md5_codigo_aleatorio','$telefone','$celular','$cpf','$rg','$data_nascimento','$sexo','$facebook','$skype')", $conexao_params, $conexao_options);

	$sql_insert = sqlsrv_query($conexao_sankhya, "
		SET DATEFORMAT YMD;
		INSERT INTO TGFPAR (CODPARC, NOMEPARC, RAZAOSOCIAL, TIPPESSOA, DDI, DDD, TELEFONE, DDD_CELULAR, FAX, EMAIL, DTCAD, DTALTER,DTNASC, CGC_CPF, CLIENTE, CODTIPPARC, ATIVO, SEXO, OBSERVACOES, AD_SENHA,AD_IDENTIFICACAO, PAIS_SIGLA, ORIGEM) VALUES ((SELECT ISNULL(MAX(CODPARC),0) + 1 FROM TGFPAR), '$nome','$razao','$pessoa', '$ddi', '$ddd' ,'$telefone', '$ddd_celular', '$celular', '$email', GETDATE(), GETDATE(), $data_nascimento,'$cpfcnpj','S', '10303000', 'S','$sexo', 'INTEGRACAO FOCO', '$md5_codigo_aleatorio','$passaporte','$pais','$origem')", $conexao_params, $conexao_options);

	/*if($sql_insert === false)*/ $getlastid = sqlsrv_fetch_array(sqlsrv_query($conexao_sankhya, "SELECT ISNULL(MAX(CODPARC),0) AS id FROM TGFPAR", $conexao_params, $conexao_options));
	$cod = $getlastid['id'];

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
			<td align='left' valign='top'><p><font face='Arial, Helvetica, sans-serif' color='#333333' size='4'><strong>$nome, você foi cadastrado como cliente em nosso sistema.</strong></td>
		  </tr>
		  <tr>
			<td align='left' valign='top'>&nbsp;</td>
		  </tr>
		  <tr>
			<td align='left' valign='top'>
			<font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>
			Para acessar sua área de cliente utilize os seguintes dados: <br/>
			<strong>Login:</strong> $email <br/>
			<strong>Senha:</strong> $codigo_aleatorio<br/>			
			</font></td>
		  </tr>
		  <tr>
			<td align='left' valign='top'>&nbsp;</td>
		  </tr>
		  <tr>
			<td align='center' height='30'><a href='http://ingressos.foliatropical.com.br/' target='_blank' style='text-decoration: none; color: #000;'><font face='Arial, Helvetica, sans-serif' color='#000' size='3'><strong>www.foliatropical.com.br</strong></font></a></td>
		  </tr>
		</table>
		</body>";

		$mail = new PHPMailer();
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
		
		$mail->Subject = "Folia Tropical - Cadastro de Cliente";  //ASSUNTO DA MENSAGEM
		$mail->Body    = utf8_decode($msg); //CONTEÚDO DA MENSAGEM
		
		$mail->Send();

		$remetente_nome = utf8_decode("Grupo Pacífica");
		$remetente_email = 'central@grupopacifica.com.br';
		$destinatario_nome = utf8_decode($nome);
		$destinatario_email = $email;
		$assunto = utf8_decode("Folia Tropical - Cadastro de Cliente");
		$mensagem = utf8_decode($msg);

		enviarEmail($remetente_nome, $remetente_email, $destinatario_nome, $destinatario_email, $assunto, $mensagem);
		
		$_SESSION['ALERT'] = array('sucesso',"Cliente cadastrado com sucesso.");
	}

// } elseif(!$erro && $editar && is_numeric($cod) && !empty($nome) && !empty($pessoa) && !empty($email) && !empty($cpfcnpj) && !empty($telefone) && !empty($data_nascimento)) {
} else if(!$erro && $editar && is_numeric($cod)) {

	if(!empty($senha)) {
		$senha = md5($senha);
		$altera_senha = ", AD_SENHA='$senha'";
	}

	// // echo "UPDATE TOP(1) TGFPAR SET NOMEPARC='$nome', RAZAOSOCIAL='$razao', TIPPESSOA='$pessoa', TELEFONE='$telefone', FAX='$celular', EMAIL='$email', DTALTER=GETDATE(), CGC_CPF='$cpfcnpj', CLIENTE='S', ATIVO='S', SEXO='$sexo',CL_DTNASC='$data_nascimento' $altera_senha WHERE CODPARC='$cod'";
	// exit();

	// $sql_insert = sqlsrv_query($conexao_sankhya, "INSERT INTO TGFPAR (NOMEPARC, RAZAOSOCIAL, TIPPESSOA, TELEFONE, EMAIL, DTALTER, DTNASC, CGC_CPF, CLIENTE, ATIVO, SEXO) VALUES ('$nome','$razao','$pessoa','$telefone','$email',GETDATE(),'$data_nascimento','$cpfcnpj','S','S','$sexo')", $conexao_params, $conexao_options);
	// 

	$sql_update = sqlsrv_query($conexao_sankhya, "
		SET DATEFORMAT YMD;
		UPDATE TOP(1) TGFPAR SET NOMEPARC='$nome', RAZAOSOCIAL='$razao', TIPPESSOA='$pessoa', DDI='$ddi', DDD='$ddd', TELEFONE='$telefone', DDI_CELULAR='$ddi_celular', DDD_CELULAR='$ddd_celular', CELULAR='$celular', EMAIL='$email', DTALTER=GETDATE(), CGC_CPF='$cpfcnpj', CLIENTE='S', ATIVO='S', SEXO='$sexo', AD_IDENTIFICACAO='$passaporte',PAIS_SIGLA='$pais',ORIGEM='$origem',DTNASC=$data_nascimento $altera_senha WHERE CODPARC='$cod'", $conexao_params, $conexao_options);
	$_SESSION['ALERT'] = array('sucesso',"Cliente alterado com sucesso.");
}else{
	$_SESSION['ALERT'] = array('aviso',$resposta);
}

/*?>
<script type="text/javascript">
	alert('<? echo $resposta; ?>');
	location.href='<? echo SITE; ?>clientes/editar/<? echo $cod; ?>/';
</script>
<?*/

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");


header("Location: ".$_SERVER['HTTP_REFERER']);