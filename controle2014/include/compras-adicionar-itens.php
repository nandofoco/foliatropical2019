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
$quantidade = 0;

$tipo = (int) $_POST['tipo'];
$setor = (int) $_POST['setor'];
$dia = (int) $_POST['dia'];
$evento = (int) $_SESSION['usuario-carnaval'];
$alterar = (bool) $_POST['alterar'];

if(!empty($tipo) && !empty($setor) && !empty($dia) && !empty($evento)) {

	// if($_SESSION['us-grupo'] == 'VIN') $interno = true;

	$sql_ingressos = sqlsrv_query($conexao, "SELECT v.*, t.TI_NOME FROM vendas v, tipos t WHERE v.VE_EVENTO='$evento' AND v.VE_TIPO='$tipo' AND v.VE_SETOR='$setor' AND v.VE_DIA='$dia' AND v.VE_TIPO=t.TI_COD AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 ORDER BY v.VE_COD ASC", $conexao_params, $conexao_options);
	$n_ingressos = sqlsrv_num_rows($sql_ingressos);

	if($n_ingressos !== false)	 {

		$sucesso = true;
		$ingressos_html;

		if($n_ingressos == 1) {
			$checked_class = 'checked';
			$checked_check = 'checked="checked"';
		}

		// Alterado
		if($alterar) $ingressos_html .= '<section class="radio verify">';

		while($ingressos = sqlsrv_fetch_array($sql_ingressos)) {

			$quantidade++;

			$ingressos_cod = $ingressos['VE_COD'];
			$ingressos_tipo = utf8_encode($ingressos['TI_NOME']);
			//Inserir porcentagem parceiro
			$ingressos_valor = number_format($ingressos['VE_VALOR'],2,",",".");
			
			$ingressos_fila = utf8_encode($ingressos['VE_FILA']);
			$ingressos_vaga = utf8_encode($ingressos['VE_VAGAS']);
			$ingressos_tipo_especifico = utf8_encode($ingressos['VE_TIPO_ESPECIFICO']);

			$ingressos_extra="";

			if(!empty($ingressos_fila)) $ingressos_extra .= " ".$ingressos_fila;
			if(!empty($ingressos_tipo_especifico)) $ingressos_extra .= " ".$ingressos_tipo_especifico;
			if(($ingressos_vaga > 0) && ($ingressos_tipo_especifico == 'fechado')) $ingressos_extra .= " (".$ingressos_vaga." vagas)";

			
			unset($disponibilidade, $ingressos_estoque, $ingressos_comprados);
			$ingressos_estoque = $ingressos['VE_ESTOQUE'];
			
			//Buscar estoque
			$sql_comprados = sqlsrv_query($conexao, "SELECT li.LI_INGRESSO, COUNT(li.LI_COD) as QTDE FROM loja_itens li, loja l WHERE li.LI_INGRESSO='$ingressos_cod' AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 GROUP BY li.LI_INGRESSO", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_comprados)) {
				$comprados = sqlsrv_fetch_array($sql_comprados);
				$ingressos_comprados = (int) $comprados['QTDE'];
			}
			$disponibilidade = ($ingressos_estoque - $ingressos_comprados);
			
			/*if($alterar) {

				$ingressos_html .= 
				'<section class="item-compra '.$checked_class.'">
					<h4>'.$ingressos_tipo.$ingressos_extra.'</h4>
					<section class="preco">
					<p>R$ '.$ingressos_valor.'</p>
					<ul><li><label class="item '.$checked_class.'"><input type="radio" name="item" '.$checked_check.' value="'.$ingressos_cod.'" /></label></li></ul>
					</section>
				</section>';

			} elseif($interno) {

				$ingressos_html .= 
				'<section class="item-compra '.$checked_class.'">
					<h4>'.$ingressos_tipo.$ingressos_extra.'</h4>
					<section class="checkbox verify preco">
					<p>R$ '.$ingressos_valor.'</p>
					<ul><li><label class="item '.$checked_class.'"><input type="checkbox" name="item[]" '.$checked_check.' value="'.$ingressos_cod.'" /></label></li></ul>
					</section>
					<input type="hidden" name="valor['.$ingressos_cod.']" value="'.$ingressos_valor.'" />
				</section>';
			} else {

				$ingressos_html .= 
				'<section class="item-compra '.$checked_class.'">
					<h4>'.$ingressos_tipo.$ingressos_extra.'</h4>
					<section class="checkbox verify preco">
					<p><label>R$</label><input type="text" name="valor['.$ingressos_cod.']" class="input money" value="'.$ingressos_valor.'" /></p>
					<ul><li><label class="item '.$checked_class.'"><input type="checkbox" name="item[]" '.$checked_check.' value="'.$ingressos_cod.'" /></label></li></ul>
					</section>
				</section>';
			}
			
		}*/

			if($alterar) {

				$ingressos_html .= 
				'<section class="item-compra '.$checked_class.'">
					<h4>'.$ingressos_tipo.$ingressos_extra.'</h4>
					<section class="preco">
					<p>R$ '.$ingressos_valor.'</p>
					<ul><li><label class="item '.$checked_class.'"><input type="radio" name="item" '.$checked_check.' value="'.$ingressos_cod.'" /></label></li></ul>
					</section>
				</section>';

			} elseif($interno) {

				$ingressos_html .= 
				'<section class="item-compra '.$checked_class.'">
					<h4>'.$ingressos_tipo.$ingressos_extra.'<span>'.$disponibilidade.'</span></h4>
					<section class="checkbox verify preco">
					<p>R$ '.$ingressos_valor.'</p>
					<ul><li><label class="item '.$checked_class.'"><input type="checkbox" name="item[]" '.$checked_check.' value="'.$ingressos_cod.'" /></label></li></ul>
					</section>
					<input type="hidden" name="valor['.$ingressos_cod.']" value="'.$ingressos_valor.'" />
				</section>';
			} else {
				//<h4>'.$ingressos_tipo.$ingressos_extra.'<span class="item-disponibilidade">'.$disponibilidade.'</span></h4>
				$ingressos_html .= 
				'<section class="item-compra '.$checked_class.'">
					<div class="item-header"><span class="item-tipo-ingresso">'.$ingressos_tipo.$ingressos_extra.'</span><span class="item-disponibilidade">'.$disponibilidade.'</span></div>
					<section class="checkbox verify preco">
					<p>R$ '.$ingressos_valor.'</p>
					<ul><li><label class="item '.$checked_class.'"><input type="checkbox" name="item[]" '.$checked_check.' value="'.$ingressos_cod.'" /></label></li></ul>
					</section>
					<section class="extras">
					<p><span>Desconto</span><label>R$</label><input type="text" name="desconto['.$ingressos_cod.']" class="input money" value="0,00" /></p>
					<p><span>Over interno</span><label>R$</label><input type="text" name="overinterno['.$ingressos_cod.']" class="input money" value="0,00" /></p>
					<p><span>Over externo</span><label>R$</label><input type="text" name="overexterno['.$ingressos_cod.']" class="input money" value="0,00" /></p>
					</section>
					<input type="hidden" name="valor['.$ingressos_cod.']" value="'.$ingressos_valor.'" />
				</section>';
			}
			
		}

		if($alterar) $ingressos_html .= '</section>';
	}
}

echo json_encode(array('sucesso' => $sucesso, 'quantidade'=>$quantidade, 'itens'=> $ingressos_html));

//Fechar conexoes
include("../conn/close.php");

?>