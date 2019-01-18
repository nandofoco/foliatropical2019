<?


//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------------------//

$pessoa = format($_POST['pessoa']);
$pais = format($_POST['pais']);
$nome = format($_POST['nome']);
$sobrenome = format($_POST['sobrenome']);
$razao	 = format($_POST['razao']);
$email = format($_POST['email']);
$origem = format($_POST['origem']);
$cpfcnpj = format($_POST['cpfcnpj']);
$passaporte = format(trim($_POST['passaporte']));
$sexo = format($_POST['sexo']);
$data_nascimento = todate($_POST['data-nascimento'], "ddmmaaaa");
$ddi = trim(format($_POST['ddi']));
$ddd = trim(format(((int)$_POST['ddd']))."");
$telefone = trim(format($_POST['telefone']));
$senha = format($_POST['senha']);
$csenha = format($_POST['csenha']);
$sessionID = format($_POST['SessionID']);

$compre = (bool) $_POST['compre'];

//Consertar o telefone
$cpfcnpj = preg_replace("/[^0-9]/", "", $cpfcnpj );
// $cep = preg_replace( "@[-]@", "", $cep );
$ddd = preg_replace("/[^0-9]/", "",  $ddd);
$telefone = preg_replace("/[^0-9]/", "",  $telefone);

//-----------------------------------------------------------------------------//

$erro = false;
$resposta = "Ocorreu um erro no cadastro.<br> tente novamente!";
$link = "cadastro/";

//-----------------------------------------------------------------------------//

//Email invalido	
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){	
	$erro = true;
	$resposta = "Por favor digite um e-mail válido";	
}

//-----------------------------------------------------------------------------//

//Email já cadastrado
$sql_exist = sqlsrv_query($conexao_sankhya, "SELECT EMAIL FROM TGFPAR WHERE EMAIL='$email' AND CLIENTE='S'", $conexao_params, $conexao_options);

$n = sqlsrv_num_rows($sql_exist);

if(!$erro && ($n > 0)){
	$erro = true;
	$resposta = "O e-mail informado já foi cadastrado";
}

//-----------------------------------------------------------------------------//

//CPF já cadastrado
if($pais=="BR"){
	$sql_exist_cpf = sqlsrv_query($conexao_sankhya, "SELECT CGC_CPF FROM TGFPAR WHERE CGC_CPF='$cpfcnpj' AND CLIENTE='S'", $conexao_params, $conexao_options);
	$n = sqlsrv_num_rows($sql_exist_cpf);

	if(!$erro && ($n > 0)){
		$label_cpfcnpj = ($pessoa == 'F') ? 'CPF' : 'CNPJ';
		$erro = true;
		$resposta = "O $label_cpfcnpj informado já foi cadastrado";
	}
}
//-----------------------------------------------------------------------------//

//Senhas diferentes
if($editar) {
	if(!$erro && ($senha != $csenha)){	
		$erro = true;
		$resposta = "As senhas digitadas estão diferentes";
	}
}

//-----------------------------------------------------------------------------//

if(!empty($cpfcnpj)) {

	switch ($pessoa) {
		case 'J':
			if(!validaCNPJ($cpfcnpj)) {
				$erro = true;
				$resposta = "O CNPJ informado é inválido";
			}
		break;
		
		case 'F':
		default:
			if(!validaCPF($cpfcnpj)&&$pais=="BR") {
				$erro = true;
				$resposta = "O CPF informado é inválido";
			}	
		break;
	}
}

if(!$erro && ($pais!="BR" && empty($passaporte))){
	$erro = true;
	$resposta = "Passport is empty.";
}

if(!$erro && empty($ddi)){	
	$erro = true;
	$resposta = "Preencha o DDI (País) do telefone.";
}

if(!$erro && empty($ddd)){	
	$erro = true;
	$resposta = "Preencha o DDD do telefone.";
}

//-------------------------------------------------------------------------------//

 if(!$erro && !$editar && !empty($nome) && !empty($pessoa) && !empty($email) && (($pais=="BR" && !empty($cpfcnpj))||($pais!="BR" && !empty($passaporte))) && !empty($telefone) && !empty($data_nascimento) && !empty($senha) && !empty($pais)) {
//if(!$erro && !$editar && !empty($nome) && !empty($pessoa) && !empty($email) && !empty($telefone) && !empty($senha)) {
    
	$senha = md5($senha);

	// Limitamos a 40 caracteres
	if(!empty($sobrenome)) $nome .= ' '.$sobrenome;
	if(strlen($nome) > 40) $nome = substr($nome, 0, 40);

	// Imposicao SANKHYA
	if($pessoa == 'F') $razaosocial = $nome;

	// "INSERT INTO TGFPAR (CODPARC, NOMEPARC, RAZAOSOCIAL, TIPPESSOA, TELEFONE, EMAIL, DTCAD, DTALTER, DTNASC, CGC_CPF, CLIENTE, ATIVO, SEXO, AD_SENHA) VALUES ((SELECT ISNULL(MAX(CODPARC),0) + 1 FROM TGFPAR), '$nome','$razao','$pessoa','$telefone','$email', GETDATE(), GETDATE(),'$data_nascimento','$cpfcnpj','S','S','$sexo','$senha')";
	
	// echo  "INSERT INTO TGFPAR (CODPARC, NOMEPARC, RAZAOSOCIAL, TIPPESSOA, TELEFONE, EMAIL, DTCAD, DTALTER,DTNASC, CGC_CPF, CLIENTE, ATIVO, SEXO, AD_SENHA, AD_IDENTIFICACAO) VALUES ((SELECT ISNULL(MAX(CODPARC),0) + 1 FROM TGFPAR), '$nome','$razao','$pessoa','$telefone','$email', GETDATE(), GETDATE(),'$data_nascimento','$cpfcnpj','S','S','$sexo','$senha','$passaporte')";
	// exit();
	//Inserir no Banco
	$sql_insert = sqlsrv_query($conexao_sankhya, "
		SET DATEFORMAT YMD;
		INSERT INTO TGFPAR (CODPARC, NOMEPARC, RAZAOSOCIAL, TIPPESSOA, DDI, DDD, TELEFONE, EMAIL, DTCAD, DTALTER,DTNASC, CGC_CPF, CLIENTE, ATIVO, SEXO, AD_SENHA, AD_IDENTIFICACAO,PAIS_SIGLA, ORIGEM) VALUES ((SELECT ISNULL(MAX(CODPARC),0) + 1 FROM TGFPAR), '$nome','$razao','$pessoa','$ddi','$ddd', '$telefone','$email', GETDATE(), GETDATE(),'$data_nascimento','$cpfcnpj','S','S','$sexo','$senha','$passaporte','$pais','$origem')", $conexao_params, $conexao_options);
    
    // CODPARC,
	/*if($sql_insert === false)*/ $getlastid = sqlsrv_fetch_array(sqlsrv_query($conexao_sankhya, "SELECT ISNULL(MAX(CODPARC),0) AS id FROM TGFPAR", $conexao_params, $conexao_options));
	$cod = $getlastid['id'];

	if(!empty($cod)) {

		$sql_last = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 CODPARC FROM TGFPAR WHERE CODPARC='$cod' AND NOMEPARC='$nome' AND TIPPESSOA='$pessoa' AND DDI='$ddi' AND DDD='$ddd' AND TELEFONE='$telefone' AND EMAIL='$email' AND CGC_CPF='$cpfcnpj' AND CLIENTE='S' AND AD_SENHA='$senha' AND AD_IDENTIFICACAO='$passaporte'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_last) > 0) {

		    //-----------------------------------------------------------//

			require("include/class.phpmailer.php");

			//Codificação
			// $nome = utf8_encode($nome);
			// $email = utf8_encode($email);
			// $telefone = utf8_encode($telefone);
			// $mensagem = utf8_encode($mensagem);
			
			$msg = "<body>
					<table width='350' border='0' align='center' cellpadding='0' cellspacing='0'>
					  <tr>
						<td height='150' align='center' valign='top'><img src='".SITE."img/logo-email.png' width='200'height='150'></td>
					  </tr>
					  <tr>
						<td align='left' valign='top'>&nbsp;</td>
					  </tr>
					  <tr>
						<td align='left' valign='top'><font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>Bem vindo(a),</font></td>
					  </tr>
					  <tr>
						<td align='left' valign='top'>&nbsp;</td>
					  </tr>
					  <tr>
						<td align='left' valign='top'>
						<font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>
						Você se cadastrou no site Folia Tropical.<br />
						Estamos sempre a disposição para lhe ajudar!<br /><br />
						Central de Atendimento: 21 3202 6000</font></td>
					  </tr>
					  <tr>
						<td align='left' valign='top'>&nbsp;</td>
					  </tr>
					  <tr>
						<td align='center' height='30'><a href='".SITE."' target='_blank' style='text-decoration: none; color: #999;'><font face='Arial, Helvetica, sans-serif' color='#999' size='1'><strong>www.foliatropical.com.br</strong></font></a></td>
					  </tr>
					</table>
					</body>";
			
			//-----------------------------------------------------------//

			$mail = new PHPMailer();
			$mail->IsSMTP();        //ENVIAR VIA SMTP
			$mail->SMTPAuth = true; //ATIVA O SMTP AUTENTICADO
			$mail->IsHTML(true);        //ATIVA MENSAGEM NO FORMATO TXT, SE true ATIVA NO FORMATO HTML
			
			$mail->Host     = "smtp.gmail.com";     //SERVIDOR DE SMTP, USE mail.SeuDominio.com OU smtp.dominio.com.br	
			$mail->Username = "sistema@grupopacifica.com.br"; //EMAIL PARA SMTP AUTENTICADO (pode ser qualquer conta de email do seu domínio)
			$mail->Password = "sistema2013";                //SENHA DO EMAIL PARA SMTP AUTENTICADO
			$mail->SetFrom($email,utf8_decode($nome));    //E-MAIL DO REMETENTE, NOME DO REMETENTE
			
			$mail->SMTPSecure = "tls";
			$mail->Host       = "smtp.gmail.com";
			$mail->Port       = 587;	
			
			$mail->AddAddress("marketing@grupopacifica.com.br",utf8_decode("Grupo Pacífica")); //E-MAIL DO DESINATÁRIO, NOME DO DESINATÁRIO
			$mail->AddReplyTo($email,utf8_decode($nome)); //CONFIGURA O E-MAIL QUE RECEBERÁ A RESPOSTA DESTA MENSAGEM
			
			$mail->Subject = "Folia Tropical - Cadastro";  //ASSUNTO DA MENSAGEM
			$mail->Body    = utf8_decode($msg); //CONTEÚDO DA MENSAGEM
			
			$mail->Send();


			$remetente_nome = utf8_decode($nome);
			$remetente_email = $email;
			$destinatario_nome = utf8_decode("Grupo Pacífica");
			$destinatario_email = "sistema@grupopacifica.com.br";
			$reply_nome = $email;
			$reply_email = utf8_decode($nome);
			$assunto = utf8_decode("Folia Tropical - Cadastro");
			$mensagem = utf8_decode($msg);

			enviarEmail($remetente_nome, $remetente_email, $destinatario_nome, $destinatario_email, $assunto, $mensagem, $reply_nome, $reply_email);
			
			//-----------------------------------------------------------//
		    
		    $resposta = "Obrigado por se cadastrar.</strong>";
		    $link = ($compre) ? 'ingressos/adicionais/' : 'ingressos/';


		    //Setamos as variáveis de sessão
		    $_SESSION['usuario-cod'] = $cod;
		    $_SESSION['usuario-login'] = $email;
		    $_SESSION['usuario-senha'] = $senha;
		    $_SESSION['usuario-nome'] = $nome;
		    $_SESSION['usuario-razao-social'] = $razao;
		    $_SESSION['usuario-tipo-pessoa'] = $pessoa;
		    $_SESSION['usuario-email'] = $email;
		    $_SESSION['SessionID'] = format($sessionID);
		    
		    $user_ip = $_SERVER["REMOTE_ADDR"];
		    $user_host = gethostbyaddr($user_ip); //pego o host

		    $focoenc = new FocoEncrypt;
		    $cookie_valor = $focoenc->criptografar($_SESSION['usuario-login'], $_SESSION['usuario-senha']);

		    setcookie('ftropsite', $cookie_valor, time()+3600, '/');
		}	    

	}
}

define('PGRESPOSTA', 'true');

//Canonical
$meta_canonical = SITE.$link_lang."cadastro/sucesso/";

//Incluir arquivos de layout
include("include/head.php");
?>
    <section id="resposta">
        <a href="<? echo SITE.$link_lang; ?>" id="logo"><span>Folia Tropical</span></a>
        <h2><? echo $resposta;  ?></h2>
        <a href="<? echo SITE.$link_lang.$link; ?>" class="voltar"><? echo $lg['cadastro_voltar']; ?></a>
    </section>

</body>
</html>
<?

//-----------------------------------------------------------------//

//fechar conexao com o banco
include("conn/close.php");
include("conn/close-sankhya.php");

?>