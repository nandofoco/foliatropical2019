<?
// ini_set('display_errors',1);
// ini_set('display_startup_erros',1);
// error_reporting(E_ALL);

//Banco de dados
include '../../conn/conn.php';
include '../../conn/conn-sankhya.php';
include BASE.'inc/funcoes.php';
include BASE.'inc/checklogado.php';

header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

$sucesso = false;

$dia = (int) $_POST['dia'];
$evento = setcarnaval();

if(!empty($dia) && !empty($evento)) {

    $in = implode(',', $tipos_permitidos);

	$sql_ingressos = sqlsrv_query($conexao, "SELECT
            v.*,
            t.TI_NOME, t.TI_DESCRICAO

		FROM 
            vendas v
            
        LEFT JOIN
            tipos t 
            ON t.TI_COD=v.VE_TIPO
            AND t.D_E_L_E_T_=0
        
        WHERE 
            v.VE_COD IN (
                SELECT
                    MAX(v.VE_COD) AS VE_COD
                
                    FROM 
                    vendas v,
                    tipos t
                    
                WHERE
                    v.VE_EVENTO='$evento' 
                    AND v.VE_TIPO IN ($in) 
                    AND v.VE_DIA='$dia' 
                    AND v.VE_TIPO=t.TI_COD 
                    AND v.VE_BLOCK=0 
                    AND v.VE_VALOR>0
                    AND v.D_E_L_E_T_=0 
                    AND t.D_E_L_E_T_=0
                
                GROUP BY 
        	        v.VE_TIPO
            )
		
        ORDER BY 
            t.TI_NOME ASC", 
        
        $conexao_params, $conexao_options);

	$n_ingressos = sqlsrv_num_rows($sql_ingressos);

	if($n_ingressos > 0) {

		$sucesso = true;
		$ingressos_html;

		if($n_ingressos == 1) {
			$checked_class = 'checked';
			$checked_check = 'checked="checked"';
        }
        
        $i = 0;

		while($ingressos = sqlsrv_fetch_array($sql_ingressos)) {

            $classe = "";

			$ingressos_cod = $ingressos['VE_COD'];
			$ingressos_estoque = $ingressos['VE_ESTOQUE'];

            $ingressos_tipo = ($ingressos['TI_NOME'] == 'Lounge') ? 'Folia Tropical' : utf8_encode($ingressos['TI_NOME']);
            $ingressos_descricao = utf8_encode($ingressos['TI_DESCRICAO']);

			//Inserir porcentagem parceiro
			$ingressos_valor = number_format($ingressos['VE_VALOR'],2,",",".");
			
			$ingressos_fila = utf8_encode($ingressos['VE_FILA']);
			$ingressos_vaga = utf8_encode($ingressos['VE_VAGAS']);
			$ingressos_tipo_especifico = utf8_encode($ingressos['VE_TIPO_ESPECIFICO']);
			
			#$ingressos_lote = utf8_encode($ingressos['VE_LOTE']);
			#if(!empty($ingressos_lote)) $ingressos_extra .= "<br />(".$ingressos_lote."º Lote)";

			$disponibilidade = true;

			//Buscar estoque
			$sql_comprados = sqlsrv_query($conexao, "SELECT 
                    li.LI_INGRESSO,
                    COUNT(li.LI_COD) AS QTDE
                FROM
                    loja_itens li,
                    loja l
                
                WHERE
                    li.LI_INGRESSO='$ingressos_cod'
                    AND l.LO_COD=li.LI_COMPRA
                    AND l.D_E_L_E_T_=0
                    AND li.D_E_L_E_T_=0
                
                GROUP BY
                    li.LI_INGRESSO",
                    
                $conexao_params, $conexao_options);

			if(sqlsrv_num_rows($sql_comprados)) {
				$comprados = sqlsrv_fetch_array($sql_comprados);
				$ingressos_comprados = (int) $comprados['QTDE'];
				$disponibilidade = (($ingressos_estoque - $ingressos_comprados) > 0) ? true : false;
			}

			if($disponibilidade) {

                if($i == 0) $classe = "right";

				$ingressos_html .= 
                '<li>
                    <label class="item-compra '.toAscii($ingressos_tipo).'">
                        <h4>'.$ingressos_tipo.'</h4>
                        <p>R$ '.$ingressos_valor.'</p>

                        <input type="submit" name="item[]" value="'.$ingressos_cod.'" />

                        <div class="tooltip '.$classe.'"><span class="right"></span>'.$ingressos_descricao.'</div>
                    </label>
                </li>';
				
			} else {
				$n_ingressos--;
			}
            
            $i++;
		}
	}
}

echo json_encode(array('sucesso' => $sucesso, 'itens'=> $ingressos_html));

//fechar conexao com o banco
include(BASE."conn/close.php");
include(BASE."conn/close-sankhya.php");

?>