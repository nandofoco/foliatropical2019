<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$login = format($_POST['login']);
$nome = format($_POST['nome']);
$email = format($_POST['email']);
$grupo = format($_POST['grupo']);
$filial = (int) $_POST['filial'];
$editar = (bool) $_POST['editar'];
$senha = format($_POST['senha']);
$csenha = format($_POST['csenha']);
$menus_cod = implode(",",$_POST['menuscod']);
$submenus_cod = implode(",",$_POST['submenuscod']);

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

if(!empty($cod) && $editar) $search_cod = " AND US_COD<>'$cod' ";

//Email já cadastrado
$sql_exist = sqlsrv_query($conexao, "SELECT US_EMAIL FROM usuarios WHERE US_EMAIL='$email' AND D_E_L_E_T_=0 $search_cod", $conexao_params, $conexao_options);
$n = sqlsrv_num_rows($sql_exist);

if(!$erro && ($n > 0)){
	$erro = true;
	$resposta = "O e-mail informado já foi cadastrado";
}

//-----------------------------------------------------------------------------//

//Login já cadastrado
$sql_exist_login = sqlsrv_query($conexao, "SELECT US_LOGIN FROM usuarios WHERE US_LOGIN='$login' AND D_E_L_E_T_=0 $search_cod", $conexao_params, $conexao_options);
$n = sqlsrv_num_rows($sql_exist_login);

if(!$erro && ($n > 0)){
	$erro = true;
	$resposta = "O Login informado já foi cadastrado";
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

if(!$erro && !$editar && !empty($nome) && !empty($email) && !empty($login) && !empty($grupo) && !empty($filial)) {
	
	$caracteres_aceitos = 'abcdefghijklmnopqrstuvwxyz0123456789'; 
	$max = strlen($caracteres_aceitos)-1;
	$codigo_aleatorio = null;
	
	for($i=0; $i < 8; $i++) { $codigo_aleatorio .= $caracteres_aceitos{mt_rand(0, $max)}; }	
	
	$md5_codigo_aleatorio = md5($codigo_aleatorio);

	$sql_insert = sqlsrv_query($conexao, "INSERT INTO usuarios (US_NOME, US_EMAIL, US_SENHA, US_LOGIN, US_GRUPO, US_FILIAL) VALUES ('$nome','$email','$md5_codigo_aleatorio','$login','$grupo','$filial')", $conexao_params, $conexao_options);

	$cod = getLastId();

	if(!empty($cod)) {

		require("include/class.phpmailer.php");

		//gravar permissoes
		$sql_permissoes = sqlsrv_query($conexao, "INSERT INTO menu_permissoes (MP_USUARIO, MP_MENU, MP_SUBMENU) VALUES ('$cod','$menus_cod','$submenus_cod')", $conexao_params, $conexao_options);

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
			<strong>Login:</strong> $login <br/>
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
		
		$mail->Subject = utf8_decode("Folia Tropical - Cadastro de Usuário");  //ASSUNTO DA MENSAGEM
		$mail->Body    = utf8_decode($msg); //CONTEÚDO DA MENSAGEM
		
		$mail->Send();*/


		$remetente_nome = utf8_decode("Grupo Pacífica");
		$remetente_email = 'central@grupopacifica.com.br';
		$destinatario_nome = utf8_decode($nome);
		$destinatario_email = $email;
		$assunto = utf8_decode("Folia Tropical - Cadastro de Usuário");
		$mensagem = utf8_decode($msg);

		enviarEmail($remetente_nome, $remetente_email, $destinatario_nome, $destinatario_email, $assunto, $mensagem);

		$resposta = "Usuário cadastrado com sucesso.";
	}
	?>
	<script type="text/javascript">
		alert('<? echo $resposta; ?>');
		location.href='<? echo SITE; ?>usuarios/';
	</script>
	<?
	exit();

} elseif(!$erro && $editar && !empty($cod) && !empty($nome) && !empty($email) && !empty($login) && !empty($grupo) && !empty($filial)) {

	if(!empty($senha)) {
		$senha = md5($senha);
		$altera_senha = ", US_SENHA='$senha'";
	}
	$sql_update_permissoes = sqlsrv_query($conexao, "UPDATE TOP (1) menu_permissoes SET MP_MENU='$menus_cod', MP_SUBMENU='$submenus_cod' WHERE MP_USUARIO='$cod'", $conexao_params, $conexao_options);

	$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) usuarios SET US_NOME='$nome', US_EMAIL='$email', US_LOGIN='$login', US_GRUPO='$grupo', US_FILIAL='$filial' $altera_senha WHERE US_COD='$cod'", $conexao_params, $conexao_options);

	$resposta = "Usuário alterado com sucesso.";
	?>
	<script type="text/javascript">
		alert('<? echo $resposta; ?>');
		location.href='<? echo SITE; ?>usuarios/editar/<? echo $cod; ?>/';
	</script>
	<?

	//Fechar conexoes
	include("conn/close.php");

	exit();
}
?>
<script type="text/javascript">
	alert('<? echo $resposta; ?>');
	location.href='<? echo SITE; ?>usuarios/';
</script>