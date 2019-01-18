<?


//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");


//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$pessoa = format($_POST['pessoa']);
$nome = format($_POST['nome']);
$razao	 = format($_POST['razao']);
$email = format($_POST['email']);
$cpfcnpj = format(trim($_POST['cpfcnpj']));
$passaporte = format(trim($_POST['passaporte']));
$sexo = format($_POST['sexo']);
$data_nascimento = todate($_POST['data-nascimento'], "ddmmaaaa");
$ddi = trim(format($_POST['ddi']));
$ddd = trim(format(((int)$_POST['ddd']))."");
$telefone = trim(format($_POST['telefone']));
$ddi_celular = trim(format($_POST['ddi-celular']));
$ddd_celular = trim(format(((int)$_POST['ddd-celular']))."");
$celular = trim(format($_POST['celular']));
$senha = format($_POST['senha']);
$csenha = format($_POST['csenha']);

//Consertar o telefone
$cpfcnpj = preg_replace("/[^0-9]/", "", $cpfcnpj );
// $cep = preg_replace( "@[-]@", "", $cep );
$ddd = preg_replace("/[^0-9]/", "",  $ddd);
$telefone = preg_replace("/[^0-9]/", "",  $telefone);
$ddd_celular = preg_replace("/[^0-9]/", "",  $ddd_celular);
$celular = preg_replace("/[^0-9]/", "",  $celular);

//busca das informações do cliente
//Pegando cpf/cnpj e tipo pessoa
//Tipo Pessoa não altera mas cpf/cpnj altera caso estejam inválidos
$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 TIPPESSOA,PAIS_SIGLA,CGC_CPF FROM TGFPAR WHERE CODPARC='$cod'", $conexao_params, $conexao_options);
$cliente = sqlsrv_fetch_array($sql_cliente);

//informação do tipo da pessoa cadastrad no banco
$pessoa = $cliente['TIPPESSOA'];
$pais = $cliente['PAIS_SIGLA'];
$cliente_cpfcnpj=$cliente['CGC_CPF'];

//-----------------------------------------------------------------------------//

$erro = false;
$resposta = "Ocorreu um erro na edição.<br> Tente novamente!";
$link = "meus-dados2/";

//-----------------------------------------------------------------------------//

//Email invalido	
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){	
	$erro = true;
	$resposta = "Por favor digite um e-mail válido";	
}

//-----------------------------------------------------------------------------//

//Email já cadastrado
$sql_exist = sqlsrv_query($conexao_sankhya, "SELECT EMAIL FROM TGFPAR WHERE CODPARC<>'$cod' AND EMAIL='$email' AND CLIENTE='S'", $conexao_params, $conexao_options);

$n = sqlsrv_num_rows($sql_exist);

if(!$erro && ($n > 0)){
	$erro = true;
	$resposta = "O e-mail informado já foi cadastrado";
}

//-----------------------------------------------------------------------------//

//CPF já cadastrado

$sql_exist_cpf = sqlsrv_query($conexao_sankhya, "SELECT CGC_CPF,TIPPESSOA FROM TGFPAR WHERE CODPARC<>'$cod' AND CGC_CPF='$cpfcnpj' AND CLIENTE='S'", $conexao_params, $conexao_options);
$n = sqlsrv_num_rows($sql_exist_cpf);

//caso ele não tenha um cpf/cnpj já valido
if($pais=="BR"&&!validaCPF($cliente_cpfcnpj)&&!validaCNPJ($cliente_cpfcnpj)){
	if(!$erro && ($n > 0)&&$pais=="BR"){
		$label_cpfcnpj = ($pessoa == 'F') ? 'CPF' : 'CNPJ';
		$erro = true;
		$resposta = "O $label_cpfcnpj informado já foi cadastrado";
	}
	if(!validaCPF($cpfcnpj)&&($pessoa == 'F')){
		$resposta = "O CPF é inválido";
		$erro = true;
	}
	if(!validaCNPJ($cpfcnpj)&&($pessoa == 'J')){
		$resposta = "O CNPJ é inválido";
		$erro = true;
	}
}else{
	$cpfcnpj=$cliente_cpfcnpj;
}

if(!$erro && ($pais!="BR" && empty($passaporte))){
	$erro = true;
	$resposta = "Passport is empty.";
}

//-----------------------------------------------------------------------------//

//Senhas diferentes
if(!empty($senha)) {
	if(!$erro && ($senha != $csenha)){	
		$erro = true;
		$resposta = "As senhas digitadas estão diferentes";
	}
}

//-----------------------------------------------------------------------------//

//Passaporte vazio
if($pais!="BR") {
	if(!$erro && empty($passaporte)){	
		$erro = true;
		$resposta = "Preencha passaporte.";
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

//-------------------------------------------------------------------------------//

if(!$erro && !$editar && is_numeric($cod) && !empty($nome) && !empty($pessoa) && !empty($email) && !empty($celular) && !empty($data_nascimento)) {
    
	if(!empty($senha)) {
		$senha = md5($senha);
		$altera_senha = ", AD_SENHA='$senha'";
	}

	// $sql_update = sqlsrv_query($conexao_sankhya, "UPDATE TOP(1) TGFPAR SET NOMEPARC='$nome', RAZAOSOCIAL='$razao', TIPPESSOA='$pessoa', TELEFONE='$telefone', EMAIL='$email', DTALTER=GETDATE(), DTNASC='$data_nascimento', CGC_CPF='$cpfcnpj', CLIENTE='S', ATIVO='S', SEXO='$sexo', AD_IDENTIFICACAO='$passaporte', PAIS_SIGLA='$pais' $altera_senha WHERE CODPARC='$cod'", $conexao_params, $conexao_options);
	$sql_update = sqlsrv_query($conexao_sankhya, "
		SET DATEFORMAT YMD;
		UPDATE TOP(1) TGFPAR SET NOMEPARC='$nome', RAZAOSOCIAL='$razao', TIPPESSOA='$pessoa', DDI='$ddi', DDD='$ddd', TELEFONE='$telefone', DDI_CELULAR='$ddi_celular', DDD_CELULAR='$ddd_celular', CELULAR='$celular',EMAIL='$email', DTALTER=GETDATE(), DTNASC='$data_nascimento', CGC_CPF='$cpfcnpj', AD_IDENTIFICACAO='$passaporte', CLIENTE='S', ATIVO='S', SEXO='$sexo' $altera_senha WHERE CODPARC='$cod'", $conexao_params, $conexao_options);

	//-----------------------------------------------------------//
    
    // $resposta = "Seus dados foram alterados. <strong>Responderemos em breve!</strong>";
    $resposta = "Seus dados foram alterados.";
    $link = "";

	//-----------------------------------------------------------//
    
    $sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 * FROM TGFPAR WHERE CODPARC='$cod'", $conexao_params, $conexao_options);
    $result = sqlsrv_fetch_array($sql_cliente);

    //Setamos as variáveis de sessão
	$_SESSION['usuario-cod'] = trim($result['CODPARC']);
	$_SESSION['usuario-login'] = trim($result['EMAIL']);
	$_SESSION['usuario-senha'] = trim($result['AD_SENHA']);
	$_SESSION['usuario-nome'] = trim($result['NOMEPARC']);
	$_SESSION['usuario-razao-social'] = trim($result['RAZAOSOCIAL']);
	$_SESSION['usuario-tipo-pessoa'] = trim($result['TIPPESSOA']);
	$_SESSION['usuario-email'] = trim($result['EMAIL']);
	
	$user_ip = $_SERVER["REMOTE_ADDR"];
	$user_host = gethostbyaddr($user_ip); //pego o host

	//Codificar dados para usar no cookie
	// include ('include/focoencrypt.php');

	$focoenc = new FocoEncrypt;
	$cookie_valor = $focoenc->criptografar($_SESSION['usuario-login'], $_SESSION['usuario-senha']);

	setcookie('ftropsite', $cookie_valor, time()+3600, '/');

	//-----------------------------------------------------------//

}

define('PGRESPOSTA', 'true');

//Canonical
$meta_canonical = SITE.$link_lang."meus-dados/alterar/";

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