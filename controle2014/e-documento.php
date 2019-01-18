<?

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$tipo = format($_POST['tipo']);
$tipo = utf8_decode($tipo);

//-----------------------------------------------------------------------------//

$resposta = "Ocorreu um erro, tente novamente.";
$erro = false;

//-----------------------------------------------------------------------------//

if(!$erro && !empty($cod) && !empty($tipo)) {

	$arquivo = $_FILES['arquivo'];

	if (!isset($_FILES['arquivo']['name']) || empty($_FILES['arquivo']['name'])) {
		die("<script>alert('Nenhum arquivo selecionado'); history.go(-1);</script>");
		exit;
	}

	if (ereg("[][><}{}():;,!?*%&#@]", $_FILES['arquivo']['name'])){ 
		die("<script>alert('O nome do arquivo contém caracteres inválidos'); history.go(-1);</script>");
		exit;
	}	

	$uploaddir = BASE.'documentos/';

	$temp = explode(".", $_FILES["arquivo"]["name"]);
	$newfilename = round(microtime(true)) . '.' . end($temp);
	$uploadfile = $uploaddir.$newfilename;

	if (move_uploaded_file($_FILES["arquivo"]["tmp_name"], $uploadfile)) {
	    $uploadSucesso = true;
	} else {
	    $uploadSucesso = false; exit;
	}

	//FAZER UPLOAD
	if( $uploadSucesso ){
		
		//vai inserir no banco 
		$sql_insert = sqlsrv_query($conexao, "INSERT INTO loja_documentos (DO_DATA, DO_ARQUIVO, DO_TIPO, DO_COMPRA) VALUES (GETDATE(), '$newfilename', '$tipo', '$cod')", $conexao_params, $conexao_options);

    	include("conn/close.php");
    	echo "<script>alert('Documento adicionado com sucesso!');location='".SITE."financeiro/detalhes/".$cod."';</script>";
    	exit;
	}

	?>
	<script type="text/javascript">
		alert('<? echo $resposta; ?>');
		history.go(-1);
	</script>
	<?

	//Fechar conexoes
	include("conn/close.php");
	
	exit();
}

?>
<script type="text/javascript">
	alert('<? echo $resposta; ?>');
	history.go(-1);
</script>