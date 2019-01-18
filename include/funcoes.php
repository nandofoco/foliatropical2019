<?


function format($text,$slashed=true) {
    $text = strip_tags(utf8_decode($text));
    $text = mysql_real_escape_string($text);
    // $text = str_replace("'","",$text);
    if($slashed) $text = addslashes($text);
    $text = trim($text);
    
    return $text;
}

function todate($dt,$format) {  
    if(empty($dt)) $dt = "00/00/0000";  
    $d = explode("/", $dt); 
    switch($format){
        case "mmaaaa": $date = date("Y-m-d", mktime(0,0,0,$d[0],01,$d[1])); break;
        case "ddmmaaaa": default: $date = date("Y-m-d", mktime(0,0,0,$d[1],$d[0],$d[2])); break;
    }   
    if(empty($dt)) $date = "0000-00-00";    
    return $date;
}

function formatar_valor($valor) {
    if($valor < 1000) {
        $valor = "R$ ".number_format($valor, 0);
    } elseif($valor > 999 && $valor < 1000000) {
        $valor = "R$ ".(round($valor/1000, 1))." mil";
    } elseif($valor > 999999) {
        ($valor < 2000000) ? $tag = " milhão" : $tag = " milhões";
        $valor = "R$ ".(round($valor/1000000, 1)).$tag;
    }
    return $valor;
}
function getLastId() {
    global $conexao, $conexao_params, $conexao_options;
    $result = sqlsrv_fetch_array(sqlsrv_query($conexao, "select @@IDENTITY as id", $conexao_params, $conexao_options), SQLSRV_FETCH_ASSOC); return $result['id'];
}

function formatCPFCNPJ ($string) {
    $output = preg_replace("[' '-./ t]", '', $string);
    $size = (strlen($output) -2);
    if ($size != 9 && $size != 12) return false;
    $mask = ($size == 9) 
        ? '###.###.###-##' 
        : '##.###.###/####-##'; 
    $index = -1;
    for ($i=0; $i < strlen($mask); $i++):
        if ($mask[$i]=='#') $mask[$i] = $output[++$index];
    endfor;
    return $mask;
}

function formatTelefone ($string) {
    $output = preg_replace("@[()_-]@", '', $string);
    $output = str_replace(' ', '', $output);
    $size = strlen($output);
    if ($size != 10 && $size != 11) return false;
    $mask = ($size == 10) 
        ? '(##) ####-####' 
        : '(##) ####-#####'; 
    $index = -1;
    for ($i=0; $i < strlen($mask); $i++):
        if ($mask[$i]=='#') $mask[$i] = $output[++$index];
    endfor;
    return $mask;
}

function validaCPF($cpf) {   // Verifiva se o número digitado contém todos os digitos
    $cpf = str_pad(preg_replace('/[^0-9]/', '', $cpf), 11, '0', STR_PAD_LEFT);
    
    // Verifica se nenhuma das sequências abaixo foi digitada, caso seja, retorna falso
    if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999') { return false; } 
    else {   // Calcula os números para verificar se o CPF é verdadeiro
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf{$c} * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf{$c} != $d) {
                return false;
            }
        }

        return true;
    }
}
function validaCNPJ($cnpj){
    //Etapa 1: Cria um array com apenas os digitos numéricos, isso permite receber o cnpj em diferentes formatos como "00.000.000/0000-00", "00000000000000", "00 000 000 0000 00" etc...
    $j=0;
    for($i=0; $i<(strlen($cnpj)); $i++)
        {
            if(is_numeric($cnpj[$i]))
                {
                    $num[$j]=$cnpj[$i];
                    $j++;
                }
        }
    //Etapa 2: Conta os dígitos, um Cnpj válido possui 14 dígitos numéricos.
    if(count($num)!=14)
        {
            $isCnpjValid=false;
        }
    //Etapa 3: O número 00000000000 embora não seja um cnpj real resultaria um cnpj válido após o calculo dos dígitos verificares e por isso precisa ser filtradas nesta etapa.
    if ($num[0]==0 && $num[1]==0 && $num[2]==0 && $num[3]==0 && $num[4]==0 && $num[5]==0 && $num[6]==0 && $num[7]==0 && $num[8]==0 && $num[9]==0 && $num[10]==0 && $num[11]==0)
        {
            $isCnpjValid=false;
        }
    //Etapa 4: Calcula e compara o primeiro dígito verificador.
    else
        {
            $j=5;
            for($i=0; $i<4; $i++)
                {
                    $multiplica[$i]=$num[$i]*$j;
                    $j--;
                }
            $soma = array_sum($multiplica);
            $j=9;
            for($i=4; $i<12; $i++)
                {
                    $multiplica[$i]=$num[$i]*$j;
                    $j--;
                }
            $soma = array_sum($multiplica); 
            $resto = $soma%11;          
            if($resto<2)
                {
                    $dg=0;
                }
            else
                {
                    $dg=11-$resto;
                }
            if($dg!=$num[12])
                {
                    $isCnpjValid=false;
                } 
        }
    //Etapa 5: Calcula e compara o segundo dígito verificador.
    if(!isset($isCnpjValid))
        {
            $j=6;
            for($i=0; $i<5; $i++)
                {
                    $multiplica[$i]=$num[$i]*$j;
                    $j--;
                }
            $soma = array_sum($multiplica);
            $j=9;
            for($i=5; $i<13; $i++)
                {
                    $multiplica[$i]=$num[$i]*$j;
                    $j--;
                }
            $soma = array_sum($multiplica); 
            $resto = $soma%11;          
            if($resto<2)
                {
                    $dg=0;
                }
            else
                {
                    $dg=11-$resto;
                }
            if($dg!=$num[13])
                {
                    $isCnpjValid=false;
                }
            else
                {
                    $isCnpjValid=true;
                }
        }
    //Trecho usado para depurar erros.
    /*
    if($isCnpjValid==true)
        {
            echo "<p><font color="GREEN">Cnpj é Válido</font></p>";
        }
    if($isCnpjValid==false)
        {
            echo "<p><font color="RED">Cnpj Inválido</font></p>";
        }
    */
    //Etapa 6: Retorna o Resultado em um valor booleano.
    return $isCnpjValid;            
}
function utf8_encode_deep(&$input) {
    if (is_string($input)) {
        $input = utf8_encode($input);
    } else if (is_array($input)) {
        foreach ($input as &$value) {
            utf8_encode_deep($value);
        }

        unset($value);
    } else if (is_object($input)) {
        $vars = array_keys(get_object_vars($input));

        foreach ($vars as $var) {
            utf8_encode_deep($input->$var);
        }
    }
}

//------------------------------------------------------------------------------------------------------//

function enviarEmail($remetente_nome, $remetente_email, $destinatario_nome, $destinatario_email, $assunto, $mensagem, $reply_nome=null, $reply_email=null) {
    $mail = new PHPMailer();
    $mail->IsSMTP();        //ENVIAR VIA SMTP
    $mail->SMTPAuth = true; //ATIVA O SMTP AUTENTICADO
    $mail->IsHTML(true);        //ATIVA MENSAGEM NO FORMATO TXT, SE true ATIVA NO FORMATO HTML
    
    $mail->Host     = "smtp.gmail.com";     //SERVIDOR DE SMTP, USE mail.SeuDominio.com OU smtp.dominio.com.br  
    $mail->Username = "sistema@grupopacifica.com.br"; //EMAIL PARA SMTP AUTENTICADO (pode ser qualquer conta de email do seu domínio)
    $mail->Password = "dayse2015";                //SENHA DO EMAIL PARA SMTP AUTENTICADO
    $mail->SetFrom($remetente_email, $remetente_nome);    //E-MAIL DO REMETENTE, NOME DO REMETENTE
    
    $mail->SMTPSecure = "tls";
    $mail->Host       = "smtp.gmail.com";
    $mail->Port       = 587;    
    
    $mail->AddAddress($destinatario_email, $destinatario_nome); //E-MAIL DO DESINATÁRIO, NOME DO DESINATÁRIO
    if($reply_nome && $reply_email) $mail->AddReplyTo($reply_email,utf8_decode($reply_nome)); //CONFIGURA O E-MAIL QUE RECEBERÁ A RESPOSTA DESTA MENSAGEM
    
    /*$mail->Subject = utf8_decode($assunto);  //ASSUNTO DA MENSAGEM
    $mail->Body    = utf8_decode($mensagem); //CONTEÚDO DA MENSAGEM*/

    $mail->Subject = $assunto;  //ASSUNTO DA MENSAGEM
    $mail->Body    = $mensagem; //CONTEÚDO DA MENSAGEM
    
    $mail->Send();
}

//------------------------------------------------------------------------------------------------------//
function validacaoData($data){
    if (strlen($data) < 10)
    {
        return false;
    }
    else
    {
        // verifica se a data possui
        // a barra (/) de separação
        if(strpos($data, "/") !== FALSE){
            //
            $partes = explode("/", $data);

            // pega o dia da data
            $dia = $partes[0];
            // pega o mês da data
            $mes = $partes[1];
            // prevenindo Notice: Undefined offset: 2
            // caso informe data com uma única barra (/)
            // $ano = isset($partes[0]) ? $partes[0] : 0;
            $ano = $partes[2];
 
            if (strlen($ano) < 4) 
            {
                return false;
            } 
            else 
            {
                // verifica se a data é válida
                if (checkdate($mes, $dia, $ano)) {
                    return true;
                } 
                else 
                {
                    return false;
                }
            }
        }
        else
        {
            return false;
        }
    }
}

//------------------------------------------------------------------------------------------------------//
//Definir se usuario esta logado
// if(checklogado()) define('USLOGADO', 'true');

$dias_principais = array('2014-03-02', '2014-03-03', '2014-03-08', '2015-02-15', '2015-02-16', '2016-02-07', '2016-02-08');
$dias_candybox = array('2015-02-13', '2016-02-05');
$roteiros_nao_agendar = ($_SERVER['SERVER_NAME'] == "server") ? array(5,6,7) : array(37,38,39);


//Novos combos
#Folia de Domingo (15) + Folia de Segunda (16) de R$ 4.998,00 para R$ 3.999,00  >> 20% Desconto
#Folia de Domingo (15) OU Folia de Segunda (16) + Folia de Campeãs (21)  de R$ 4.298,00 para R$ 3.399,00 >> 21% desconto
#Folia de Domingo (15) + Folia de Segunda (16) + Folia de Campeãs (21) de R$ 6.797,00 para R$ 4.999,00 >> 26,5% desconto

$combo_dias_limite = $_SERVER['SERVER_NAME'] == 'server' ? 107 : 2639;
$combo_dias = array(

    array('limite' => array('2014-01-01', '2016-01-20'), 'nome'=>'Combo 2 dias na Folia', 'desconto'=>20, 'dias'=> array('2015-02-15', '2015-02-16', '2016-02-07', '2016-02-08'), 'total'=>2),
    array('limite' => array('2014-01-01', '2016-01-20'), 'nome'=>'Combo 2 dias na Folia', 'desconto'=>21, 'dias'=> array('2015-02-15', '2015-02-21', '2016-02-07', '2016-02-13'), 'total'=>2),
    array('limite' => array('2014-01-01', '2016-01-20'), 'nome'=>'Combo 2 dias na Folia', 'desconto'=>21, 'dias'=> array('2015-02-16', '2015-02-21', '2016-02-08', '2016-02-13'), 'total'=>2),
    array('limite' => array('2014-01-01', '2016-01-20'), 'nome'=>'Combo 3 dias na Folia', 'desconto'=>26.5, 'dias'=> array('2015-02-15', '2015-02-16', '2015-02-21', '2016-02-07', '2016-02-08', '2016-02-13'), 'total'=>3),


    array('limite' => array('2016-01-21', '2030-01-01'), 'nome'=>'Combo 2 dias na Folia', 'desconto'=>10, 'dias'=> array('2015-02-15', '2015-02-16', '2016-02-07', '2016-02-08'), 'total'=>2),
    array('limite' => array('2016-01-21', '2030-01-01'), 'nome'=>'Combo 2 dias na Folia', 'desconto'=>10, 'dias'=> array('2015-02-15', '2015-02-21', '2016-02-07', '2016-02-13'), 'total'=>2),
    array('limite' => array('2016-01-21', '2030-01-01'), 'nome'=>'Combo 2 dias na Folia', 'desconto'=>10, 'dias'=> array('2015-02-16', '2015-02-21', '2016-02-08', '2016-02-13'), 'total'=>2),
    array('limite' => array('2016-01-21', '2030-01-01'), 'nome'=>'Combo 3 dias na Folia', 'desconto'=>10, 'dias'=> array('2015-02-15', '2015-02-16', '2015-02-21', '2016-02-07', '2016-02-08', '2016-02-13'), 'total'=>3)

);

//------------------------------------------------------------------------------------------------------//

$pagina = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
$pagina = str_replace(SITE, "", $pagina);

$pagina = explode("/", $pagina);

$permissao = 1;

/*$current_pagina = current($pagina)."/";
$next_pagina = next($pagina);
$pagina_full = (empty($next_pagina)) ? $current_pagina : $current_pagina.$next_pagina."/";

//busca permissao na página
$sql_permissao = mysql_query("SELECT * FROM ((SELECT @menu:=ME_COD as codigo, ME_LINK as pagina,  
@menu_permissoes:=(SELECT MP_MENU FROM menu_permissoes WHERE MP_USUARIO=".$_SESSION['usuario-cod'].") as menu_permissoes, 
CASE WHEN FIND_IN_SET(@menu, @menu_permissoes) THEN 1 ELSE 0 END as permitido   FROM menu WHERE D_E_L_E_T_=0) UNION 
(SELECT @menu:=SM_COD as codigo, SM_LINK as pagina,
@menu_permissoes:=(SELECT MP_SUBMENU FROM menu_permissoes WHERE MP_USUARIO=".$_SESSION['usuario-cod'].") as menu_permissoes, 
CASE WHEN FIND_IN_SET(@menu, @menu_permissoes) THEN 1 ELSE 0 END as permitido FROM submenu WHERE D_E_L_E_T_=0)) S HAVING pagina='$pagina_full'");
$n_permissao = mysql_num_rows($sql_permissao);

if($n_permissao > 0) { 
    $permissao = mysql_result($sql_permissao, 0, 'permitido');
} else {
    //busca permissao na origem da pagina (current)
    $sql_permissao_current = mysql_query("SELECT * FROM ((SELECT @menu:=ME_COD as codigo, ME_LINK as pagina,  
    @menu_permissoes:=(SELECT MP_MENU FROM menu_permissoes WHERE MP_USUARIO=".$_SESSION['usuario-cod'].") as menu_permissoes, 
    CASE WHEN FIND_IN_SET(@menu, @menu_permissoes) THEN 1 ELSE 0 END as permitido   FROM menu WHERE D_E_L_E_T_=0) UNION 
    (SELECT @menu:=SM_COD as codigo, SM_LINK as pagina,
    @menu_permissoes:=(SELECT MP_SUBMENU FROM menu_permissoes WHERE MP_USUARIO=".$_SESSION['usuario-cod'].") as menu_permissoes, 
    CASE WHEN FIND_IN_SET(@menu, @menu_permissoes) THEN 1 ELSE 0 END as permitido FROM submenu WHERE D_E_L_E_T_=0)) S HAVING pagina='$current_pagina'");
    $n_current = mysql_num_rows($sql_permissao_current);

    if($n_current > 0) { $permissao = mysql_result($sql_permissao_current, 0, 'permitido'); }
}

if(!$permissao) { 
    header("location: ".SITE."bloqueado/"); 
}*/
?>