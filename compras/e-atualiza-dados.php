
<?

//Incluir funções básicas
include("include/includes.php");


//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

include("include/head_atualiza_dados.php");


//-----------------------------------------------------------------------------//
$erro = false;

$cod = $_SESSION['usuario-cod'];
$cpfcnpj = format(trim($_POST['cpfcnpj']));
$passaporte = format(trim($_POST['passaporte']));
$data_nascimento = todate($_POST['data-nascimento'], "ddmmaaaa");


//Consertar o telefone
$cpfcnpj = preg_replace("/[^0-9]/", "", $cpfcnpj );


//busca das informações do cliente
//Pegando cpf/cnpj e tipo pessoa
//Tipo Pessoa não altera mas cpf/cpnj altera caso estejam inválidos
$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 TIPPESSOA,PAIS_SIGLA,CGC_CPF FROM TGFPAR WHERE CODPARC='$cod'", $conexao_params, $conexao_options);
$cliente = sqlsrv_fetch_array($sql_cliente);

//informação do tipo da pessoa cadastrada no banco
$pessoa = $cliente['TIPPESSOA'];
$pais = $cliente['PAIS_SIGLA'];
$cliente_cpfcnpj=$cliente['CGC_CPF'];


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

//Passaporte vazio
if($pais!="BR") {
	if(!$erro && empty($passaporte)){	
		$erro = true;
		$resposta = "Preencha passaporte.";
	}
}

if(!validacaoData($_POST['data-nascimento']))
{
	$erro = true;
	$resposta = "Data inválida!";
}


//-------------------------------------------------------------------------------//

if(!$erro) {
    
	$sql_update = sqlsrv_query($conexao_sankhya, "
		SET DATEFORMAT YMD;
		UPDATE TOP(1) TGFPAR SET DTNASC='$data_nascimento', CGC_CPF='$cpfcnpj', AD_IDENTIFICACAO='$passaporte' WHERE CODPARC='$cod'", $conexao_params, $conexao_options);

    
    $resposta = "Seus dados foram alterados.";
    $link = "compras";


	$_SESSION['atualizacao_dados'] = true;

}


//Canonical



// $meta_canonical = SITE.$link_lang."meus-dados/alterar/";



if ($erro == true)
{

	echo '<script type="text/javascript">

            swal({
                title: "Erro",
                text: "'.$resposta.'",
                html: true,
                type: "error"
            	},
            	function(){
    				window.location.href = "atualiza-dados.php";
				});
        </script>';
}
else
{

    if ($_SESSION['plataforma'] == 'antiga')
	{
		$meta_canonical = SITE.$lg."ingressos/";
	}	
	else if ($_SESSION['plataforma'] == 'atual')
	{
		$meta_canonical = SITE."compras/";
	}

	echo '<script type="text/javascript">
            swal({
                title: "'.$resposta.'",
                html: true,
                type: "success"
            },
            	function(){
    				window.location.href = "'.$meta_canonical.'";
				});
        </script>';
}





//fechar conexao com o banco
include("conn/close.php");
include("conn/close-sankhya.php");

?>