<?
session_start();

header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

$sucesso = false;

$setor = $_POST['setor'];
$edit = $_POST['edit'];

function ordena($a, $b) {
  return strnatcmp($a['nome'], $b['nome']);
}

function monta_lista($dados) {	
	if(count($dados) > 0) {
		sort($dados);
		$lista = "";
		foreach ($dados as $key => $value) {			
			$lista .= '<li><a href="'.$key.'" class="remover" title="Remover Setor">'.$value.'</a></li>';			
		}
		$sucesso = true;	
		sort($_SESSION['carnaval-setores']);	
	} else {
		$sucesso = false;
	}
	echo json_encode(array("sucesso"=>$sucesso, "lista"=>$lista));
}

function edit_lista($dados) {	
	if(count($dados) > 0) {
		usort($dados, 'ordena');
		$lista = "";
		foreach ($dados as $key => $value) {			
			$lista .= '<li><a href="'.$key.'" class="remover edit" title="Remover Setor">'.$value['nome'].'</a></li>';			
		}
		$sucesso = true;	
		usort($_SESSION['carnaval-setores'], 'ordena');	
	} else {
		$sucesso = false;
	}
	echo json_encode(array("sucesso"=>$sucesso, "lista"=>$lista));
}

if(!$_POST['edit'] && !empty($setor)) {

	$i = count($_SESSION['carnaval-setores']);
	$insere = true;
	//Adicionar ao array se não existir a data cadastrada
	if(count($_SESSION['carnaval-setores']) > 0) {
		foreach ($_SESSION['carnaval-setores'] as $key => $value) {
			if ($value == $setor) {
				$insere = false;
				break;
			}
	   }
	}
	
	if($insere) $_SESSION['carnaval-setores'][$i] = $setor;
				
	monta_lista($_SESSION['carnaval-setores']);

} elseif($_POST['edit'] && !empty($setor)) {

	$i = count($_SESSION['carnaval-setores']);
	$insere = true;
	//Adicionar ao array se não existir a data cadastrada
	if(count($_SESSION['carnaval-setores']) > 0) {
		foreach ($_SESSION['carnaval-setores'] as $key => $value) {
			if ($value['nome'] == $setor) {
				$insere = false;
				break;
			}
	   }
	}
	
	if($insere) $_SESSION['carnaval-setores'][$i]['nome'] = $setor;
				
	edit_lista($_SESSION['carnaval-setores']);
}

if(!$_POST['edit'] && $_POST['key'] != "") {
	unset($_SESSION['carnaval-setores'][(int)$_POST['key']]);
	sort($_SESSION['carnaval-setores']);

	monta_lista($_SESSION['carnaval-setores']);
}

if($_POST['edit'] && $_POST['key'] != "") {

	$cod_remover = $_SESSION['carnaval-setores'][(int)$_POST['key']]['cod'];
	if(!empty($cod_remover)) {
		$_SESSION['setores-remover'][(int)$_POST['key']] = $cod_remover;
	}

	unset($_SESSION['carnaval-setores'][(int)$_POST['key']]);
	sort($_SESSION['carnaval-setores']);

	edit_lista($_SESSION['carnaval-setores']);
}

if($_POST['limpar']) { 
	unset($_SESSION['carnaval-setores']);
	monta_lista($_SESSION['carnaval-setores']);
}

?>