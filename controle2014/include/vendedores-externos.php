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

$cod = (int) $_POST['cod'];

$sucesso = false;
$vendedores = '';

if(!empty($cod)) {

	// Vendedores
	$sql_vendedor_externo = sqlsrv_query($conexao, "SELECT * FROM vendedor_externo WHERE VE_PARCEIRO='$cod' AND VE_BLOCK='0' AND D_E_L_E_T_='0' ORDER BY VE_NOME ASC", $conexao_params, $conexao_options);
	$n_vendedor_externo = sqlsrv_num_rows($sql_vendedor_externo);
	if($n_vendedor_externo > 0) {

		while($vendedor_externo = sqlsrv_fetch_array($sql_vendedor_externo)) {

			$vendedor_externo_cod = $vendedor_externo['VE_COD'];
			$vendedor_externo_parceiro_cod = $vendedor_externo['VE_PARCEIRO'];
			$vendedor_externo_nome = utf8_encode($vendedor_externo['VE_NOME']);
			$vendedor_externo_email = utf8_encode($vendedor_externo['VE_EMAIL']);
			$vendedor_externo_telefone = utf8_encode($vendedor_externo['VE_TEL']);

			$vendedores .= '
			<li>
				<label class="item">
				<input type="radio" name="vendedor-externo" value="'.$vendedor_externo_cod.'" alt="'.$vendedor_externo_nome.'" />'.$vendedor_externo_nome.'
				</label>
			</li>';
		}

	}

	$sucesso = true;
}

echo json_encode(array('sucesso' => $sucesso, 'quantidade' => $n_vendedor_externo, 'vendedores' => $vendedores));

//Fechar conexoes
include("../conn/close.php");

?>