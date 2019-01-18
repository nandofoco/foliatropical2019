<?


//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------------------//

$email = format($_POST['email']);

//-----------------------------------------------------------------------------//

$erro = false;
$resposta = "Ocorreu um erro na alteração.<br> tente novamente!";
$link = "esqueci-senha/";

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
				<td align='left' valign='top'><font face='Arial, Helvetica, sans-serif' color='#666666' size='4'>Prezado (a),</font></td>
			  </tr>
			  <tr>
				<td align='left' valign='top'>&nbsp;</td>
			  </tr>
			  <tr>
				<td align='left' valign='top'>
				<font face='Arial, Helvetica, sans-serif' color='#666666' size='3'>
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

	$remetente_nome = utf8_decode("Grupo Pacífica");
	$remetente_email = 'central@grupopacifica.com.br';
	$destinatario_nome = utf8_decode($nome);
	$destinatario_email = $email;
	$assunto = utf8_decode("Recuperação de senha");
	$mensagem = utf8_decode($msg);

	enviarEmail($remetente_nome, $remetente_email, $destinatario_nome, $destinatario_email, $assunto, $mensagem);
	
	//-----------------------------------------------------------//
    
    $resposta = "Uma nova senha foi enviada para<br /> <strong>$email</strong>";
    $link = "";
}

define('PGRESPOSTA', 'true');

//Canonical
$meta_canonical = SITE.$link_lang."esqueci-senha/sucesso/";

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