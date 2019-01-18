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

//conexao Sankhya
include("../conn/conn-sankhya.php");

header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

$tipo = (int) $_POST['tipo'];
$setor = (int) $_POST['setor'];
$dia = (int) $_POST['dia'];
$fila_nivel = format($_POST['fila-nivel']);

$evento = (int) $_SESSION['usuario-carnaval'];

if(!empty($evento) && !empty($tipo) && !empty($setor) && !empty($dia)) {

	// Filtros
	if($tipo == 1) $search_tipo = " AND v.VE_TIPO_ESPECIFICO='numerada'";
	if(!empty($fila_nivel)) $search_fila_nivel = " AND ((c.CO_FILA='$fila_nivel' AND c.CO_NIVEL IS NULL) OR (c.CO_NIVEL='$fila_nivel' AND c.CO_FILA IS NULL)) ";

	//Filtro
	$sql_ingressos_lugares = sqlsrv_query($conexao, "DECLARE @ingressos TABLE (CO_COD INT, LI_COD INT, LI_ID INT, LI_NOME VARCHAR(255), LI_VALOR DECIMAL(10,2), LO_COD INT, LO_ENVIADO TINYINT DEFAULT 0, LO_PARCEIRO INT, LO_PAGO TINYINT DEFAULT 0);
		DECLARE @outros TABLE (LO_COD INT, QTDE INT DEFAULT 0);

		INSERT INTO @ingressos (CO_COD, LI_COD, LI_ID, LI_NOME, LI_VALOR, LO_COD, LO_ENVIADO, LO_PARCEIRO, LO_PAGO)
		SELECT c.CO_COD, li.LI_COD, li.LI_ID, li.LI_NOME, li.LI_VALOR, l.LO_COD, l.LO_ENVIADO, l.LO_PARCEIRO, l.LO_PAGO FROM compras c, loja l, loja_itens li, alocacao a WHERE c.CO_ESTOQUE IS NULL AND c.CO_EVENTO='$evento' AND c.CO_TIPO='$tipo' AND c.CO_SETOR='$setor' AND c.CO_DIA='$dia' AND c.CO_BLOCK=0 AND c.D_E_L_E_T_=0 $search_fila_nivel
		AND l.LO_EVENTO='$evento' AND l.LO_BLOCK=0 AND l.D_E_L_E_T_=0 AND l.LO_COD=li.LI_COMPRA AND li.LI_ALOCADO=1 AND li.D_E_L_E_T_=0 AND l.LO_EVENTO='$evento' AND l.LO_BLOCK=0 AND l.D_E_L_E_T_=0 AND l.LO_COD=li.LI_COMPRA AND li.LI_ALOCADO=1 AND li.D_E_L_E_T_=0
		AND a.AL_ITEM=li.LI_COD AND a.AL_LUGAR=c.CO_COD AND a.AL_BLOCK=0 AND a.D_E_L_E_T_=0

		INSERT INTO @outros (LO_COD, QTDE)
		SELECT l.LO_COD, COUNT(li.LI_COD) FROM vendas v, loja_itens li, loja l WHERE v.VE_COD=li.LI_INGRESSO AND li.LI_COMPRA=l.LO_COD AND v.VE_EVENTO='$evento' AND v.VE_TIPO='$tipo' AND v.VE_SETOR='$setor' AND v.VE_DIA<>'$dia' AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0 AND l.D_E_L_E_T_=0 $search_tipo GROUP BY l.LO_COD;

		SELECT i.*, ISNULL(o.QTDE,0) AS OUTROS, c.LC_COD AS COMENTARIO FROM @ingressos i
		LEFT JOIN @outros o ON o.LO_COD=i.LO_COD
		LEFT JOIN loja_comentarios c ON c.LC_COMPRA=i.LO_COD AND c.LC_ITEM=i.LI_COD", $conexao_params, $conexao_options);

	if(sqlsrv_next_result($sql_ingressos_lugares) && sqlsrv_next_result($sql_ingressos_lugares))
	$n_ingressos_lugares = sqlsrv_num_rows($sql_ingressos_lugares);
	if($n_ingressos_lugares !== 0) {

		$sucesso = true;
		$ar_ingressos_lugares = array();

		while($ingressos_lugares = sqlsrv_fetch_array($sql_ingressos_lugares)){
			$ingressos_lugares_ingresso = $ingressos_lugares['CO_COD'];
		    $ingressos_lugares_parceiro_cod = $ingressos_lugares['LO_PARCEIRO'];
		    
		    $ingressos_lugares_cod = $ingressos_lugares['LI_COD'];
		    $ingressos_lugares_compra = $ingressos_lugares['LO_COD'];
		    $ingressos_lugares_compra_id = $ingressos_lugares['LI_ID'];
		    // $ingressos_lugares_compras_exibir_id = ($ingressos_lugares['EXIBIRID'] > 0);
		    $ingressos_lugares_compras_outros = ($ingressos_lugares['OUTROS'] > 0);
		    $ingressos_lugares_nome = utf8_encode($ingressos_lugares['LI_NOME']);
		    $ingressos_lugares_valor = number_format($ingressos_lugares['LI_VALOR'],2,",",".");
		    $ingressos_lugares_enviado = (bool) $ingressos_lugares['LO_ENVIADO'];
		    $ingressos_lugares_pago = (bool) $ingressos_lugares['LO_PAGO'];
		    $ingressos_lugares_comentario = $ingressos_lugares['COMENTARIO'];

		    $ingressos_lugares_id = sqlsrv_query($conexao, "SELECT COUNT(LI_ID) AS QTDE FROM loja_itens WHERE LI_COMPRA=$ingressos_lugares_compra AND LI_ID<>'$ingressos_lugares_compra_id' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
			//$ingressos_lugares_id = (sqlsrv_num_rows($ingressos_lugares_id) > 0) ? mssql_result($ingressos_lugares_id, 0, 'QTDE'): null;
			if(sqlsrv_num_rows($ingressos_lugares_id) > 0) {
				$ar_ingressos_lugares_id = sqlsrv_fetch_array($ingressos_lugares_id);
				$ingressos_lugares_id = $ar_ingressos_lugares_id['QTDE'];
			} else {
				$ingressos_lugares_id = null;	
			}

			$ingressos_lugares_compras_exibir_id = ($ingressos_lugares_id > 0);
			

		    $ingressos_cliente_classe = '1';

		    // Canal
		    $sql_ingressos_lugares_parceiro = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC FROM TGFPAR WHERE CODPARC='$ingressos_lugares_parceiro_cod' AND VENDEDOR='S'", $conexao_params, $conexao_options);
		    // if(sqlsrv_num_rows($sql_ingressos_lugares_parceiro) > 0) $ingressos_lugares_canal = utf8_encode(mssql_result($sql_ingressos_lugares_parceiro, 0, 'PA_NOME'));
		    if(sqlsrv_num_rows($sql_ingressos_lugares_parceiro) > 0) {
				$ar_ingressos_lugares_canal = sqlsrv_fetch_array($sql_ingressos_lugares_parceiro);
				$ingressos_lugares_canal = utf8_encode($ar_ingressos_lugares_canal['NOMEPARC']);
			} else {
				$ingressos_lugares_canal = null;	
			}

		    array_push($ar_ingressos_lugares, array(
				"cod" => $ingressos_lugares_cod,
				"ingresso" => $ingressos_lugares_ingresso,
			    "canal" => $ingressos_lugares_canal,
			    "compra" => $ingressos_lugares_compra,
			    "compra_id" => $ingressos_lugares_compra_id,
			    "exibir_id" => $ingressos_lugares_compras_exibir_id,
			    "outros" => $ingressos_lugares_compras_outros,
			    "nome" => $ingressos_lugares_nome,
			    "cliente_classe" => $ingressos_cliente_classe,
			    "valor" => $ingressos_lugares_valor,
			    "enviado" => $ingressos_lugares_enviado,
			    "pago" => $ingressos_lugares_pago,
			    "comentario" => $ingressos_lugares_comentario
			));
		}

	}

}

echo json_encode(array("sucesso"=>$sucesso, "lugares"=>$ar_ingressos_lugares));

//Fechar conexoes
include("../conn/close.php");
include("../conn/close-sankhya.php");

?>