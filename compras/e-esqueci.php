<?
include 'conn/conn.php';
include 'conn/conn-sankhya.php';
include 'inc/funcoes.php';
include 'inc/checklogado.php';

$email = format($_POST['esqueci-email']);

//-----------------------------------------------------------------------------//

$erro = false;
$resposta = "Ocorreu um erro na alteração.<br> tente novamente!";
$link = "";

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
    
	$caracteres_aceitos = 'abcdefghijklmnopqrstuvwxyz0123456789'; 
	$max = strlen($caracteres_aceitos)-1;
	$codigo_aleatorio = null;
	
	for($i=0; $i < 8; $i++) { $codigo_aleatorio .= $caracteres_aceitos{mt_rand(0, $max)}; }	
	
	$md5_codigo_aleatorio = md5($codigo_aleatorio);

    //Inserir no Banco
	$sql_insert = sqlsrv_query($conexao_sankhya, "UPDATE TOP(1) TGFPAR SET AD_SENHA='$md5_codigo_aleatorio' WHERE EMAIL='$email' AND CLIENTE='S'", $conexao_params, $conexao_options);
    
    //-----------------------------------------------------------//

	require("inc/class.phpmailer.php");

	//Codificação
	// $nome = utf8_encode($nome);
	// $email = utf8_encode($email);
	// $telefone = utf8_encode($telefone);
	// $mensagem = utf8_encode($mensagem);
	
	$msg = "<body>
			<table width='350' border='0' align='center' cellpadding='0' cellspacing='0'>
			  <tr>
				<td height='150' align='center' valign='top'><img src='https://ingressos.foliatropical.com.br/img/logo-email.png' width='200'height='150'></td>
			  </tr>
			  <tr>
				<td align='left' valign='top'>&nbsp;</td>
			  </tr>
			  <tr>
				<td align='left' valign='top'><font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>Prezado (a),</font></td>
			  </tr>
			  <tr>
				<td align='left' valign='top'>&nbsp;</td>
			  </tr>
			  <tr>
				<td align='left' valign='top'>
				<font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>
				Sua senha foi alterada para $codigo_aleatorio<br />
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

	$remetente_nome = utf8_decode("Folia Tropical");
	$remetente_email = 'central@foliatropical.com.br';
	$destinatario_nome = utf8_decode($nome);
	$destinatario_email = $email;
	$assunto = utf8_decode("Recuperação de senha");
	$mensagem = utf8_decode($msg);

	enviarEmail($remetente_nome, $remetente_email, $destinatario_nome, $destinatario_email, $assunto, $mensagem);

	$mail = new PHPMailer();
	$mail->IsSMTP();        //ENVIAR VIA SMTP
	$mail->SMTPAuth = true; //ATIVA O SMTP AUTENTICADO
	$mail->IsHTML(true);        //ATIVA MENSAGEM NO FORMATO TXT, SE true ATIVA NO FORMATO HTML
	
	$mail->Host     = "smtp.gmail.com";     //SERVIDOR DE SMTP, USE mail.SeuDominio.com OU smtp.dominio.com.br	
	$mail->Username = "sistema@grupopacifica.com.br"; //EMAIL PARA SMTP AUTENTICADO (pode ser qualquer conta de email do seu domínio)
	$mail->Password = "dayse2015";
	$mail->SetFrom("central@grupopacifica.com.br",utf8_decode("Grupo Pacífica"));    //E-MAIL DO REMETENTE, NOME DO REMETENTE
	
	$mail->SMTPSecure = "tls";
	$mail->Host       = "smtp.gmail.com";
	$mail->Port       = 587;	
	
	$mail->AddAddress('thalles@fococomunicacao.com', 'Thalles'); //E-MAIL DO DESINATÁRIO, NOME DO DESINATÁRIO
	#$mail->AddBCC('thalles@fococomunicacao.com', ''); //E-MAIL DO DESINATÁRIO, NOME DO DESINATÁRIO
	// $mail->AddReplyTo($email,utf8_decode($nome)); //CONFIGURA O E-MAIL QUE RECEBERÁ A RESPOSTA DESTA MENSAGEM
	
	$mail->Subject = utf8_decode("Recuperacao de Senha");  //ASSUNTO DA MENSAGEM
	$mail->Body    = utf8_decode($codigo_aleatorio); //CONTEÚDO DA MENSAGEM
	
	$mail->Send();
	
	//-----------------------------------------------------------//
    
    $resposta = "Uma nova senha foi enviada para<br /> <strong>$email</strong>";
    $link = "";
}

//Incluir arquivos de layout
include 'inc/partials/head.php';

?>
<script type="text/javascript">
    swal({
        title: "Recuperar senha",
        text: "<? echo $resposta;  ?>",
        html: true,
        type: "<? echo $erro ? 'error' : 'success' ?>"
    }, function() {
        window.location.href = '<? echo $_SERVER['HTTP_REFERER'].$link; ?>';
    });
</script>
<?

//fechar conexao com o banco
include("conn/close.php");
include("conn/close-sankhya.php");

?>