<?

//Verificamos o dominio
include("checkwww.php");

//Banco de dados
include("../conn/conn.php");

// Checar usuario logado
include("checklogado.php");

// Checar usuario logado
include("language.php");

//Incluir funções básicas
include("funcoes.php");

header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

$q = format($_POST['q']);
$n_faq = 0;

if(!empty($q)) {

	$sucesso = true;

	$sql_faq = mysql_query("SELECT PR_COD FROM lounge_perguntas_respostas WHERE D_E_L_E_T_<>1 AND PR_BLOCK<>1  AND (PR_PERGUNTA_".$session_language." LIKE '%$q%' OR PR_RESPOSTA_".$session_language." LIKE '%$q%')  ORDER BY PR_COD ASC");
	$n_faq = mysql_num_rows($sql_faq);

	if($n_faq > 0) {

		$ar_faq = array();

		while($faq = mysql_fetch_array($sql_faq)){
			$faq_cod = $faq['PR_COD'];
		    array_push($ar_faq, $faq_cod);
		}

	}

}

echo json_encode(array("sucesso"=>$sucesso, "quantidade"=>$n_faq, "faq"=>$ar_faq));
	
?>