<?
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    ini_set('display_errors', 1);

    include 'conn/conn.php';
    include 'conn/conn-sankhya.php';
    include 'inc/funcoes.php';
    include 'inc/checklogado.php';
    include 'inc/checkwww.php';
    include 'inc/language.php';


    $evento = setcarnaval();
    $logado = checklogado();

    $tipo = $_GET['tipo'];

    include 'inc/partials/head.php';
    include 'inc/partials/header.php';

    if (!isset($ingressos_valor_soma))
    {
        unset($_SESSION['data_ingresso_desconto']);
        unset($_SESSION['desconto_ft_porc']);
        $entrouDescontoFolia = false;
    }

    if (isset($_SESSION['atualizacao_dados']) && $_SESSION['atualizacao_dados'] == false)
    { 
        echo '<script type="text/javascript">location.href="https://ingressos.foliatropical.com.br/'.$link_lang.'/atualiza-dados/";</script>';
    }

    if(isset($_SESSION['usuario-erro'])) {
    ?>
        <script type="text/javascript">
            swal({
                title: "<?=$lg['login_incorreto'];?>",
                text: "<?=$lg['dados_incorretos'];?>",
                html: true,
                type: "error"
            });
        </script>
    <?
        unset($_SESSION['usuario-erro']);
    }

    if(isset($_SESSION['ingresso-add'])) {
    ?>
        <script type="text/javascript">
            swal({
                title: "<?=$lg['adicionado_ingresso'];?>",
                text: "<?=$lg['para_continuar'];?>",
                html: true,
                type: "success"
            });
        </script>
    <?
        unset($_SESSION['ingresso-add']);
    }

?>

<input type="hidden" name="link_lang" id="link_lang" value="<? echo $link_lang; ?>" />


<main>
    <? if(empty($tipo)) { ?>
        <section id="escolha-dia" class="secao">
            <header>
                <h2><?=$lg['escolha_dia'];?></h2>
            </header>
            
            <div class="checkbox" id="check-dia">
                <ul>
                <?

                $sql_eventos_dias = sqlsrv_query($conexao, "SELECT 
                 ED_COD,
                 ED_NOME,
                 ED_ATRACOES,
                 ED_ATRACOES_US,
                 DATEPART(DAY, ED_DATA) AS DIA,
                 DATEPART(MONTH, ED_DATA) AS MES,
                 DATEPART(WEEKDAY, ED_DATA) AS SEMANA
                FROM 
                 eventos_dias
                WHERE 
                 ED_EVENTO='$evento' AND DATEPART(DAY, ED_DATA) NOT IN ('10', '11', '12') AND DATEPART(WEEKDAY, ED_DATA) <> 6 AND D_E_L_E_T_=0
                ORDER BY
                 ED_DATA ASC
                    ", $conexao_params, $conexao_options);

                if(sqlsrv_num_rows($sql_eventos_dias)){

                    while ($ar_eventos_dias = sqlsrv_fetch_array($sql_eventos_dias)) {
                        
                        $eventos_dias_cod = $ar_eventos_dias['ED_COD'];

                        $eventos_dias_nome = utf8_encode($ar_eventos_dias['ED_NOME']);

                        $eventos_dias_atracoes = utf8_encode($ar_eventos_dias['ED_ATRACOES']);
                        $eventos_dias_atracoes_us = utf8_encode($ar_eventos_dias['ED_ATRACOES_US']);

                        $eventos_dias_dia = $ar_eventos_dias['DIA'];
                        
                        $eventos_dias_mes = $meses_min[$ar_eventos_dias['MES']];
                        $eventos_dias_month = $months_min[$ar_eventos_dias['MES']];
                        
                        if((int) $eventos_dias_nome > 4) $ar_eventos_dias['SEMANA'] = 8;
                        
                        $eventos_dias_semana = $semana[($ar_eventos_dias['SEMANA']-1)];
                        $eventos_dias_week = $week[($ar_eventos_dias['SEMANA']-1)];

                        
                    ?>
                    <li>
                        <label class="item <? echo toAscii($eventos_dias_semana); ?>"><input type="radio" name="dia" value="<? echo $eventos_dias_cod; ?>" />
                            <strong><? echo ($session_language == "BR" ? $eventos_dias_semana : $eventos_dias_week); ?></strong>
                            <div class="wrap" style="margin-bottom: 5px">
                                <p><? echo $eventos_dias_dia; ?></p>
                                <span><? echo ($session_language == "BR" ? $eventos_dias_mes : $eventos_dias_month); ?></span>
                            </div>


                            <a href="javascript:void(0)" onclick="mostraInfo(<?=$eventos_dias_cod?>)">
                                
                                 <label class="mais-info" id="mais-info-<?=$eventos_dias_cod?>">
                                <div class="tooltip-link">
                                    + <?=$lg['info']?>
                                </div>
                                </label>

                            </a>
                           
                            <a href="javascript:void(0)"  onclick="escondeInfo(<?=$eventos_dias_cod?>)">
                            <div class="tooltip-link">
                            <label class="menos-info" id="menos-info-<?=$eventos_dias_cod?>">
                                - <?=$lg['info']?>
                            
                            </label>
                            </div>
                            </a>

                            <!-- <div class="tooltip"><span></span><? echo $eventos_dias_atracoes; ?></div>-->
                            <div class="tooltip-hidden" id="tooltip-hidden-<?=$eventos_dias_cod?>"><? echo ($session_language == "BR" ? $eventos_dias_atracoes : $eventos_dias_atracoes_us); ?></div>
                            
                            
                        </label>
                    </li>
                    <?

                    }
                }

                ?>
                </ul>
                <div class="tooltip-informacoes"></div>
                
            </div>
        
        </section>
    <? } ?>

    <section id="escolha-tipo" class="secao">
        <header>
            <h2><?=$lg['tipo_ingresso'];?></h2>
        </header>
        
        <form class="checkbox" id="check-tipo" method="post" action="<? echo SITE.$link_lang; ?>carrinho/adicionar/">
            <? if(empty($tipo)) { ?>
                <ul>
                    <p><?=$lg['selecione_dia'];?></p>
                </ul>
            <? } else {

                $link_tipo = "candybox/";

                $sql_ingressos = sqlsrv_query($conexao, "SELECT
                v.*,
                t.TI_NOME, t.TI_DESCRICAO, t.TI_DESCRICAO2
    
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
                            AND v.VE_TIPO = 4 
                            AND v.VE_DIA=1037
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
                    ?>
                    <ul>
                    <?
                    if($n_ingressos == 1) {
                        $checked_class = 'checked';
                        $checked_check = 'checked="checked"';
                    }
                    
                    $i = 0;

                    while($ingressos = sqlsrv_fetch_array($sql_ingressos)) {
                        $classe = "";
                        
                        $ingressos_cod = $ingressos['VE_COD'];
                        $ingressos_estoque = $ingressos['VE_ESTOQUE'];
            
                        $ingressos_tipo = ($ingressos['TI_NOME'] == 'Lounge') ? 'Candybox' : utf8_encode($ingressos['TI_NOME']);
                        $ingressos_descricao = ($ingressos['TI_NOME'] == 'Lounge') ? utf8_encode($ingressos['TI_DESCRICAO2']) : utf8_encode($ingressos['TI_DESCRICAO']);
                        // $ingressos_descricao = utf8_encode($ingressos['TI_DESCRICAO']);
            
                        //Inserir porcentagem parceiro
                        $ingressos_valor = number_format($ingressos['VE_VALOR'],2,",",".");
                        
                        $ingressos_fila = utf8_encode($ingressos['VE_FILA']);
                        $ingressos_vaga = utf8_encode($ingressos['VE_VAGAS']);
                        $ingressos_tipo_especifico = utf8_encode($ingressos['VE_TIPO_ESPECIFICO']);

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
            
                            ?>
                            <li>
                                <label class="item-compra folia-tropical">
                                    <h4><? echo $ingressos_tipo; ?></h4>
                                    <p>R$ <? echo $ingressos_valor; ?></p>
            
                                    <input type="submit" name="item[]" value="<? echo $ingressos_cod; ?>" />
            
                                    <div class="tooltip <? echo $classe; ?>"><span class="right"></span><? echo $ingressos_descricao; ?></div>
                                </label>
                            </li>
                            <?
                            
                        } else {
                            $n_ingressos--;
                        }
                        
                        $i++;

                    }
                    ?>
                    </ul>
                    <?
                } 
                
                if(count($_SESSION['compra-site']) == 0) {
                ?>
                    <script type="text/javascript">
                        $(".item-compra").trigger("click");
                    </script>
                <? }
            } ?>
        </form>
    
    </section>

    <?
    
    if(count($_SESSION['compra-site']) > 0) {    
    ?>
	<section class="secao" id="carrinho">
		<header>
			<h2><?=$lg['resumo_compra'];?></h2>
		</header>

        <p style="width: 830px; margin: 0 auto; color: #333; font-size: 16px; text-transform: uppercase;">Clientes <strong>PNE</strong>, favor entrar em contato através do telefone <strong>21 3202-6000</strong>, para um melhor atendimento</p>
		
        <table class="lista">
            <thead>
                <tr>
                    <th class="dia"><?=$lg['resumo_dia'];?></th>
                    <th><?=$lg['resumo_tipo_ingresso'];?></th>
                    <th><?=$lg['resumo_quantidade'];?></th>
                    <th class="valor"><?=$lg['resumo_valor'];?></th>
                </tr>
            </thead>
            <tbody>
            <?
            
            $idisponivel = $ingressos_valor_soma = 0;

            
            foreach ($_SESSION['compra-site'] as $key => $carrinho) {

                
                $sql_ingressos = sqlsrv_query($conexao, "
                    DECLARE @ingresso INT='".$carrinho['item']."';
                    DECLARE @vendas TABLE (VE_COD INT, VE_TIPO INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT, VE_FILA VARCHAR(255), VE_VAGAS INT, VE_TIPO_ESPECIFICO VARCHAR(255));
                    DECLARE @qtde TABLE (COD INT, QTDE INT DEFAULT 0);

                    INSERT INTO @vendas (VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO)
                    SELECT VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO FROM vendas WHERE VE_COD=@ingresso AND VE_VALOR>0 AND D_E_L_E_T_=0;

                    INSERT INTO @qtde (COD, QTDE)
                    
                    SELECT li.LI_INGRESSO, COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE li.LI_INGRESSO=@ingresso AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 GROUP BY li.LI_INGRESSO;
                    
                    SELECT TOP 1 * FROM (SELECT ISNULL(q.QTDE, 0) AS QTDE, v.*, CAST((v.VE_ESTOQUE - ISNULL(q.QTDE, 0)) AS INT) AS TOTAL, t.TI_NOME, SUBSTRING(CONVERT(VARCHAR, d.ED_DATA, 103), 1, 5) AS ED_NOME, s.ES_NOME FROM @vendas v 
                    LEFT JOIN @qtde q ON v.VE_COD = q.COD
                    LEFT JOIN tipos t ON t.TI_COD=v.VE_TIPO
                    LEFT JOIN eventos_dias d ON d.ED_COD=v.VE_DIA
                    LEFT JOIN eventos_setores s ON s.ES_COD=v.VE_SETOR
                    WHERE d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0) S", $conexao_params, $conexao_options);
                
                    if(sqlsrv_next_result($sql_ingressos) && sqlsrv_next_result($sql_ingressos))
                    if(sqlsrv_num_rows($sql_ingressos) !== false) {
                    $i=1;
                    $ingressos = sqlsrv_fetch_array($sql_ingressos);


                    $ingressos_cod = $ingressos['VE_COD'];
                    $ingressos_setor = utf8_encode($ingressos['ES_NOME']);
                    $ingressos_dia = utf8_encode($ingressos['ED_NOME']);
                    $ingressos_tipo = ($ingressos['TI_NOME'] == 'Lounge') ? 'Folia Tropical' : utf8_encode($ingressos['TI_NOME']);
                    $ingressos_valor = $carrinho['valor'];
                    $ingressos_valor_total = ($ingressos_valor * $carrinho['qtde']);
                    $ingressos_valor_totalf = number_format($ingressos_valor_total,2,",",".");
                    $ingressos_estoque = (int) $ingressos['TOTAL'];

                    $ingressos_valor_soma += $ingressos_valor_total;

                    $ingressos_block = ($ingressos_estoque == 0);
                    $ingresso_indisponivel = ($ingressos_estoque < $carrinho['qtde']);
                    $_SESSION['compra-site'][$key]['disabled'] = ($ingressos_block) ? true : false;
                    if(!$ingressos_block) $idisponivel++;

                    // Contador para desconto 
                    // Desconto aplicado ao total da compra quando há dois ingressos comprados para o domingo ou segunda
                    if (!in_array($ingressos_cod, $_SESSION['data_ingresso_desconto']))
                    {
                        if ($ingressos['VE_DIA']==36 || $ingressos['VE_DIA']==35) 
                        {
                            $_SESSION['data_ingresso_desconto'][] = $ingressos_dia;
                        }
                    }

                ?>
                <tr>
                    <td class="dia"><? echo $ingressos_dia; ?></td>
                    <td class="tipo"><? echo $ingressos_tipo; ?></td>
                    <td class="qtde">
                        <!-- <select name="quantidade[<? echo $key; ?>]" class="input" rel="<? echo $key; ?>">
                            <? for($q = 0; $q <= $ingressos_estoque; $q++) { ?>
                            <option value="<? echo $q; ?>" <? if($q == $carrinho['qtde']) { echo 'selected'; } ?>><? echo $q; ?></option>
                            <? } ?>
                        </select> -->
                        <input type="text" class="mask" id="<? echo $key; ?>" name="quantidade[<? echo $key; ?>]" min="1" max="<? echo $ingressos_estoque; ?>" value="<? echo $carrinho['qtde'];?>" onblur="verificaEstoque(<? echo $ingressos_estoque; ?>,<? echo $key; ?>)">

                        <input type="hidden" name="valor_individual" value="<? echo $ingressos_valor; ?>" />
                        <input type="hidden" name="valortotal" value="<? echo $ingressos_valor_total; ?>" />
                    </td>
                    <td class="valor_individual">R$ <? echo $ingressos_valor; ?></td>
                    <td class="ctrl"><a href="<? echo SITE.$link_lang; ?>carrinho/adicionar/?c=<? echo $key; ?>&a=excluir" class="excluir confirm" title="Deseja remover este ingresso?">&times;</a></td>
                    <!-- <td class="ctrl small">
                        <a href="<? echo SITE.$link_lang; ?>ingressos/adicionar/?c=<? echo $key; ?>&a=excluir" class="excluir confirm" title="Tem certeza que deseja excluir o ingresso?"></a>
                    </td> -->
                </tr>
                <?
                }
            }
            ?>
                <tr class="total">
                    <td colspan="4" class="valor">
                        <strong>Total</strong>
                        <span class="valor">R$ <? echo number_format($ingressos_valor_soma, 2, ",", "."); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <? if($logado) { ?>
            <footer class="controle">
                <a class="prosseguir" href="<? echo SITE.$link_lang.$link_tipo; ?>ingressos/"><?=$lg['prosseguir'];?></a>
            </footer>
        <? } ?>

	</section>
	<?
    }
    

    if(!$logado) {
    ?>
    <section class="secao" id="login">
		<header>
			<h2><?=$lg['cadastro_login'];?></h2>
		</header>

        <div class="primeira">
            <h3><?=$lg['primeira_compra'];?></h3>
            <a href="#" class="cadastro"><?=$lg['cadastre'];?></a>
        </div>

        <div class="login">
            
            <form name="login" class="login padrao" data-toggle="validator" method="post" action="<? echo SITE.$link_lang; ?>acessa/">
				<input type="hidden" id="SessionID" name="SessionID">
            
				<p id="login-email-box">
					<label for="login-email">E-mail</label>
					<input type="text" name="email" class="input" id="login-email" />
				</p>
				
				<p>
					<label for="login-senha"><?=$lg['cadastro_senha'];?></label>
					<input type="password" name="senha" class="input" id="login-senha" />
				</p>
				
                <p class="submit">
					<a href="#" class="esqueci">* <?=$lg['login_esqueci'];?></a>
					<input type="submit" class="submit" value="<?=$lg['avancar'];?>" />
				</p>
				
			</form>
        </div>
    </section>

    <?
    
    //busca paises
    $sql_paises= sqlsrv_query($conexao_sankhya, "SELECT * FROM pais", $conexao_params, $conexao_options);
    
    $paises=array();    
    while($linha = sqlsrv_fetch_array($sql_paises)){ array_push($paises, $linha); }

    ?>
    <section id="cadastro" class="secao">
        <header>
            <h2><?=$lg['cadastro_novo_user'];?></h2>
        </header>

        <form name="cadastro" class="infield padrao" data-toggle="validator" method="post" action="<? echo SITE.$link_lang; ?>cadastro/">
            <input type="hidden" id="SessionID" name="SessionID">
            <p style="color: #f3901e;"><strong><? echo $lg['compre_cadastro_titular']; ?></strong></p>
            <section id="cadastro-pessoa" class="radio">
                <ul>
                    <li>
                        <label class="item checked">
                            <span><i class="fa fa-check" aria-hidden="true"></i></span>
                            <input type="radio" name="pessoa" value="F" checked="checked" id="pessoa_fisica"/>
                            <?=$lg['cadastro_pessoa_fisica'];?>
                        </label>
                    </li>
                    <li>
                        <label class="item">
                            <span><i class="fa fa-check" aria-hidden="true"></i></span>
                            <input type="radio" name="pessoa" value="J"  id="pessoa_juridica"/>
                            <?=$lg['cadastro_pessoa_juridica'];?>
                        </label>
                    </li>
                </ul>
                <div class="clear"></div>
            </section>

            <input type="hidden" name="legendas" value='<? echo json_encode(array('nome' => 'Nome', 'nomefantasia' => 'Nome Fantasia', 'cpf' => 'CPF', 'passaporte' => 'Passaporte', 'cnpj' => 'CNPJ', 'datanascimento' => 'Data de Nascimento', 'datafundacao' => 'Data da Fundação')); ?>' />

            <div class="clear"></div>

            <section class="selectbox select2" id="pais">
                <select name="pais" class="drop">
                    <? foreach ($paises as $pais) { ?>
                        <option value="<? echo $pais['PAIS_SIGLA'];?>" <? echo $pais['PAIS_SIGLA'] == "BR" ? "selected" : ""; ?>><? echo $pais['PAIS_NOME'];?></option>
                    <? } ?>
                </select>
            </section>
            
            <div class="form-group">
                <p id="cadastro-nome-box">
                    <label for="cadastro-nome"><?=$lg['cadastro_nome'];?></label>
                    <input type="text" name="nome" class="input" id="cadastro-nome" required/>
                </p>
            </div>
            <div class="form-group">
                <p id="cadastro-sobrenome-box">
                    <label for="cadastro-sobrenome"><?=$lg['cadastro_sobrenome'];?></label>
                    <input type="text" name="sobrenome" class="input" id="cadastro-sobrenome" required />
                </p>
            </div>
            <div class="form-group">
                <p id="cadastro-razao-box">
                    <label for="cadastro-razao"><?=$lg['cadastro_razao_social'];?></label>
                    <input type="text" name="razao" class="input" id="cadastro-razao" disabled="disabled" required />
                </p>
            </div>

            <div class="form-group">
                <p id="cadastro-email">
                    <label for="cadastro-email">E-mail</label>
                    <input type="text" name="email" class="input" id="cadastro-email" required />
                </p>
            </div>




            <div class="clear"></div>

            <div class="form-group">
                 <section class="selectbox ddi select2" id="cadastro-ddi">
                    <select name="ddi" class="drop" required>
                        <? foreach ($paises as $pais) { ?>
                            <option value="<? echo $pais['PAIS_SIGLA'];?>" <? echo $pais['PAIS_SIGLA'] == "BR" ? "selected":""; ?>>+<? echo $pais['PAIS_PHONECODE'];?> <? echo $pais['PAIS_NOME'];?></option>
                        <? } ?>
                    </select>
                </section>
                <div class="form-group">
                    <p class="ddd">
                        <label for="cadastro-ddd">DDD</label>
                        <input type="text" name="ddd" min="0" maxlength="2" class="input" id="cadastro-ddd" />
                    </p>
                </div>
                <div class="form-group">
                    <p class="telefone">
                        <label for="cadastro-telefone"><?=$lg['cadastro_telefone'];?></label>
                        <input type="text" name="telefone" class="input" id="cadastro-telefone"/>
                    </p>
                </div>
                
                <div class="clear"></div>

            </div>

            <div class="clear"></div>

            <div class="form-group">
                 <section class="selectbox ddi select2" id="cadastro-ddi">
                    <select name="ddi_celular" class="drop" required>
                        <? foreach ($paises as $pais) { ?>
                            <option value="<? echo $pais['PAIS_SIGLA'];?>" <? echo $pais['PAIS_SIGLA'] == "BR" ? "selected":""; ?>>+<? echo $pais['PAIS_PHONECODE'];?> <? echo $pais['PAIS_NOME'];?></option>
                        <? } ?>
                    </select>
                </section>
                <div class="form-group">
                    <p class="ddd-cel">
                        <label for="cadastro-ddd-cel">DDD</label>
                        <input type="text" name="ddd-cel" min="0" maxlength="2" class="input" id="cadastro-ddd-cel" required/>
                    </p>
                </div>
                <div class="form-group">
                    <p class="celular">
                        <label for="cadastro-celular"><?=$lg['celular'];?></label>
                        <input type="text" name="celular" class="input" id="cadastro-celular" required/>
                    </p>
                </div>
                
                <div class="clear"></div>

            </div>


            
            <div class="form-group">
                <p id="cadastro-data-nascimento-box" class="coluna col-1-2">
                    <label for="cadastro-data-nascimento"><?=$lg['cadastro_data_nascimento'];?></label>
                    <input type="text" name="data-nascimento" class="input" id="cadastro-data-nascimento" required />
                </p>
            </div>
            
            <div class="form-group">
                <p id="cadastro-cpfcnpj-box" class="coluna last col-1-2">
                    <label for="cadastro-cpfcnpj"><?=$lg['cadastro_cpf'];?></label>
                    <input type="text" name="cpfcnpj" class="input" id="cadastro-cpfcnpj" required />
                </p> 
            </div>
            
            <div class="clear"></div>

            <section class="selectbox select2" id="origem">
                <select name="origem" data-placeholder="<?=$lg['como_conheceu_ft'];?>">
                    <option></option>
                    <option value="Sites de Busca"><?=$lg['como_conheceu_sb'];?></option>
                    <option value="Redes Socias"><?=$lg['como_conheceu_rs'];?></option>
                    <option value="Indicação de amigo"><?=$lg['como_conheceu_ia'];?></option>
                    <option value="Rádio"><?=$lg['como_conheceu_radio'];?></option>
                    <option value="TV">TV</option>
                    <option value="Agência"><?=$lg['como_conheceu_agencia'];?></option>
                    <option value="Hotel"><?=$lg['como_conheceu_hotel'];?></option>
                    <option value="Outros"><?=$lg['como_conheceu_outro'];?></option>
                </select>
            </section>

            <div class="clear"></div>
            
            <div class="form-group">
                <p class="coluna col-1-2">
                    <label for="cadastro-senha"><?=$lg['cadastro_senha'];?></label>
                    <input type="password" name="senha" class="input" id="cadastro-senha" required />
                </p>
            </div>
            <div class="form-group">
                <p class="coluna last col-1-2">
                    <label for="cadastro-csenha"><?=$lg['cadastro_csenha'];?></label>
                    <input type="password" name="csenha" class="input" id="cadastro-csenha" required />
                </p>
            </div>

            <div class="clear"></div>
            
            <h4><?=$lg['seja_bem_vindo'];?></h4>

            <p class="submit"><input type="submit" class="submit" value="<?=$lg['finalizar_cadastro'];?>" /></p>
        </form>
    </section>

    <section id="esqueci" class="secao">
        <header>
            <h2><?=$lg['login_esqueci'];?></h2>
        </header>
        
        <p><?=$lg['login_esqueci_texto'];?></p>

        <form name="esqueci" class="infield padrao" data-toggle="validator" method="post" action="<? echo SITE.$link_lang; ?>esqueci/">
            <div class="form-group">
                <p id="esqueci-email-box">
                    <label for="esqueci-email">E-mail</label>
                    <input type="email" name="esqueci-email" class="input" id="esqueci-email" required/>
                </p>
            </div>
            <p class="submit"><input type="submit" class="submit" value="<?=$lg['enviar'];?>" /></p>
        </form>
    </section>
    <?
    } else {
    ?>
    <!-- <a href="<? echo SITE.$link_lang; ?>ingressos/">Prosseguir</a> -->
    <?
    }
	?>

</main>

  <script type='text/javascript' src="https://rawgit.com/RobinHerbots/jquery.inputmask/3.x/dist/jquery.inputmask.bundle.js"></script>


<script>

    function verificaEstoque(qtde_estoque, id) {
        
        var qtde_ingressos = $('#'+id).val();
  
        if (qtde_ingressos > qtde_estoque) {
            alert('Apenas '+ qtde_estoque + ' ingressos disponíveis no estoque!');
            $('#'+id).val(qtde_estoque);
        }
    }


    $(".mask").inputmask('Regex', {regex: "^[0-9]{1,6}(\\.\\d{1,2})?$"});

     // --------------------------------------- //    

	 (function(a, b, c, d, e, f, g) {
	 a['CsdpObject'] = e; a[e] = a[e] || function() {
	 (a[e].q = a[e].q || []).push(arguments)
	 }, a[e].l = 1 * new Date(); f = b.createElement(c),
	g = b.getElementsByTagName(c)[0]; f.async = 1; f.src = d;
	g.parentNode.insertBefore(f, g)
	 })(window, document, 'script',
	'//device.clearsale.com.br/p/fp.js', 'csdp');
	 csdp('app', '<? echo CLEARSALE_APP; ?>');
	 csdp('outputsessionid', 'SessionID');



     // --------------------------------------- //

        

     function mostraInfo(id) {

        $('.tooltip-informacoes').empty(); 

        // Oculta os itens anteriores
        $('.menos-info').css("display", "none");
        $('.mais-info').css("display", "block");

        // Trata o elemento específico que chamou a função
        mais_info = '#mais-info-'+id;
        menos_info = '#menos-info-'+id;
        tol_hidden = '#tooltip-hidden-'+id;

        $(mais_info).css("display", "none");
        $(menos_info).css("display", "block");

        $('.tooltip-informacoes').css("display", "block");


        $(tol_hidden).clone().attr('class', 'tooltip-novo').appendTo(".tooltip-informacoes");

     }

     function escondeInfo(id) {
        mais_info = '#mais-info-'+id;
        menos_info = '#menos-info-'+id;

        $(menos_info).css("display", "none");
        $(mais_info).css("display", "block");
        $('.tooltip-informacoes').css("display", "none");
     }

     // $('.tooltip-link').blur(function() {
     //  $('.tooltip-informacoes').css("display", "none");   
     // });

     

</script>
<?

include 'inc/partials/footer.php';

//fechar conexao com o banco
include 'conn/close.php';
include 'conn/close-sankhya.php';

?>