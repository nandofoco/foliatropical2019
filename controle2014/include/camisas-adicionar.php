<?

define('PGINCLUDE', 'true');

//Verificamos o dominio
include("checkwww.php");

//Banco de dados
include("../conn/conn.php");

// Checar usuario logado
include("checklogado.php");

//Incluir funções básicas
include("funcoes.php");

//Incluir função para url amigável
include("toascii.php");


header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

$sucesso = false;

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$tamanho = format($_POST['tamanho']);
$quantidade = (int) $_POST['quantidade'];

//-----------------------------------------------------------------------------//

if(is_numeric($cod) && !empty($tamanho) && ($quantidade > 0)) {

	//Adicionar à sessao
	$i = count($_SESSION['compras-camisas'][$cod]);

	//Procuramos se ja existe cadastrada
	if($i > 0) {
		
		foreach ($_SESSION['compras-camisas'][$cod] as $key => $compra) {
			
			// Formatar valor
			if(($compra['tamanho'] == $tamanho)) {

				$qtde = ($quantidade > 0) ? $quantidade : 1;
				$_SESSION['compras-camisas'][$cod][$key]['qtde'] += $qtde;

				unset($tamanho, $quantidade);

			}
		}
	
	}
	
	if(!empty($tamanho) && ($quantidade > 0)) {

		//Se não existe adicionamos o item ao array
		$_SESSION['compras-camisas'][$cod][$i]['tamanho'] = $tamanho;
		$_SESSION['compras-camisas'][$cod][$i]['qtde'] = $quantidade;
		
	}
		
	ksort($_SESSION['compras-camisas'][$cod]);

	$sucesso = true;
	$ingressos_html = '';

	foreach ($_SESSION['compras-camisas'][$cod] as $key => $compra) {		
		// $ingressos_html .= $compra['tamanho'].' - '.$compra['qtde'].'<br />';
		$ingressos_html .= '<li>';
		$ingressos_html .= '<strong>'.$compra['qtde'].'</strong> Tam: '.$compra['tamanho'];
		$ingressos_html .= '<a href="'.SITE.'include/camisas-adicionar.php?c='.$cod.'&i='.$key.'&a=excluir" class="remover">&times;</a>';
		$ingressos_html .= '<input type="hidden" name="quantidade-item" value="'.$compra['qtde'].'" />';
		$ingressos_html .= '</li>';
	}

	//Exibir resposta
	echo json_encode(array('sucesso' => $sucesso, 'itens'=> $ingressos_html));

	//Fechar conexoes
	include("../conn/close.php");

	exit();
}

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$item = (int) $_GET['i'];
$acao = format($_GET['a']);

if(is_numeric($cod) && is_numeric($item) && !empty($acao)) {

	switch ($acao) {
		case 'excluir':			
			unset($_SESSION['compras-camisas'][$cod][$item]);
			sort($_SESSION['compras-camisas'][$cod]);

			$sucesso = true;
			$ingressos_html = '';

			foreach ($_SESSION['compras-camisas'][$cod] as $key => $compra) {		
				// $ingressos_html .= $compra['tamanho'].' - '.$compra['qtde'].'<br />';
				$ingressos_html .= '<li>';
				$ingressos_html .= '<strong>'.$compra['qtde'].'</strong> Tam: '.$compra['tamanho'];
				$ingressos_html .= '<a href="'.SITE.'include/camisas-adicionar.php?c='.$cod.'&i='.$key.'&a=excluir" class="remover">&times;</a>';
				$ingressos_html .= '<input type="hidden" name="quantidade-item" value="'.$compra['qtde'].'" />';
				$ingressos_html .= '</li>';
			}

			//Exibir resposta
			echo json_encode(array('sucesso' => $sucesso, 'itens'=> $ingressos_html));

			exit();

		break;

		/*case 'quantidade':
			$quantidade = (int) $_GET['quantidade'];
			if($quantidade > 0) $_SESSION['compras-camisas'][$cod]['qtde'] = $quantidade;
			echo json_encode(array('sucesso' => true));
			exit();
		break;*/
		
	}

}

//Fechar conexoes
include("../conn/close.php");

echo json_encode(array('sucesso' => $sucesso));

?>