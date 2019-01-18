$(document).ready(function(){	
    var site = $("#base-site").val();
    //----------------------------------------------------------------------------
    //antifraude clearsale
    $('body').on('click', '.antifraude button.acao.loading', function(event) {
        return false;
    });
    $('body').on('click', '.antifraude button.acao', function(event) {
        $this=$(this);
        if(!$this.hasClass('loading')){
            $this.addClass('loading');
            $.ajax({
                url: site+'ClearSale/paginas/'+$this.data('acao')+'.php',
                type: 'POST',
                dataType: 'json',
                data: {cod: $this.data('cod')},
            })
            .done(function(data) {
                if(data.status){
                    switch(data.titulo){
                        case 'Aprovado':
                            //atualiza textos
                            $this.closest('.antifraude').find('.titulo').html('Analisado');
                            $this.closest('.antifraude').find('.texto').html('');

                            var score_texto="",score_cor="";
                            // data.score=data.score*100;
                            switch (true) {
                                case data.score<30:
                                    score_cor="verde";
                                    score_texto="(Risco Baixo)";
                                    break;
                                case data.score<60:
                                    score_cor="laranja";
                                    score_texto="(Risco Médio)";
                                    break;

                                case data.score<90:
                                    score_cor="vermelho";
                                    score_texto="(Risco Alto)";
                                    break;

                                case data.score<100:
                                    score_cor="vermelho";
                                    score_texto="(Risco Crítico)";
                                    break;
                                
                                default:
                                    score_texto="(Risco Desconhecido)";
                                    break;
                            }

                            $this.closest('.antifraude').find('.status').removeClass('nao-analisado').addClass(score_cor);
                            //atualizar botoes
                            $this.closest('.informacoes-compra').find('a.liberar.confirm').removeClass('nao-analisado reprovado aprovado analisando').addClass('score_cor');
                            $this.closest('tr').find('.ctrl.financeiro a.liberar.confirm').removeClass('nao-analisado reprovado aprovado analisando').addClass('score_cor');

                            $this.closest('.antifraude').find('.score').html("Score: "+parseFloat(data.score).toFixed(2)+"% "+score_texto);


                            $this.hide();
                            break;
                        case 'Reprovado':
                            // $this.closest('.antifraude').find('.status').removeClass('nao-analisado reprovado aprovado analisando').addClass('reprovado');

                            //atualizar botoes
                            // $this.closest('.informacoes-compra').find('a.liberar.confirm').removeClass('nao-analisado reprovado aprovado analisando').addClass('reprovado');
                            // $this.closest('tr').find('.ctrl.financeiro a.liberar.confirm').removeClass('nao-analisado reprovado aprovado analisando').addClass('reprovado');
                            
                            //atualiza textos
                            $this.closest('.antifraude').find('.titulo').html('Reprovado');
                            $this.closest('.antifraude').find('.texto').html('Análise reprovada.');

                            $this.hide();
                            break;
                        case 'Aguardando aprovação':
                            //atualizar circulo
                            // $this.closest('.antifraude').find('.status').removeClass('nao-analisado reprovado aprovado analisando').addClass('analisando');
                            
                            //atualizar botoes
                            // $this.closest('.informacoes-compra').find('a.liberar.confirm').removeClass('nao-analisado reprovado aprovado analisando').addClass('analisando');
                            // $this.closest('tr').find('.ctrl.financeiro a.liberar.confirm').removeClass('nao-analisado reprovado aprovado analisando').addClass('analisando');
                            
                            //atualiza textos
                            $this.closest('.antifraude').find('.titulo').html('Aguardando análise');
                            $this.closest('.antifraude').find('.texto').html('Pedido em análise.');
                            
                            $this.data('consultar');
                            break;
                        case 'Erro':
                            $this.data('enviar');
                            swal('','Um erro ocorreu ao classificar pedido: '+data.titulo ,'error');
                            break;
                    }
                    if(data.quizUrl != null){
                        $this.closest('.antifraude').find('a.btn-quiz').addClass('active');
                    }else{
                        $this.closest('.antifraude').find('a.btn-quiz').removeClass('active');
                    }
                }else{
                    $this.data('enviar');
                    swal('','Um erro ocorreu ao analisar pedido: '+data.titulo,'error');
                }
            })
            .always(function() {
                $this.removeClass('loading');
            });
        }
        event.stopPropagation();
    });
    
	
    //------------------------------------------------------------------------------

    $('#cadastro-endereco').on('change','select[name="pais"]',function(event) {
        var pais = $(this).val();
        if(pais=="BR"){
            $(this).closest('form').find('input[name="bairro"]').attr('data-validate', 'true').closest('.form-group').show();
            $(this).closest('form').find('input[name="cep"]').attr('data-validate', 'true').closest('.form-group').show();
            $(this).closest('form').find('input[name="zipcode"]').attr('data-validate', 'false').val('').closest('.form-group').hide();
            $('#cadastro-endereco').validator('update');
        }else{
            $(this).closest('form').find('input[name="bairro"]').attr('data-validate', 'false').val('').closest('.form-group').hide();
            $(this).closest('form').find('input[name="cep"]').attr('data-validate', 'false').val('').closest('.form-group').hide();
            $(this).closest('form').find('input[name="zipcode"]').attr('data-validate', 'true').closest('.form-group').show();
            $('#cadastro-endereco').validator('update');
        }
    });

    //----------------------------------------------------------------------------

    // $('#numero_cartao').keyup(function(){
    //     var value = $(this).val();
    //     getCreditCardLabel(value);
    // });
    // if($('#numero_cartao').val()!=""){
    //     $('#numero_cartao').trigger('keyup');
    // }

	$(window).load(function () {
	   if($.browser.msie && parseInt($.browser.version) <= 8) { $("body").addClass("ie"); }
	});

	//------------------- Infield -------------------//
	
	//Label
	$("label.infield").inFieldLabels({ fadeOpacity:0.3 });

    //-------------------------------------------------------------------//

    $(document).on('focus', ':input', function(){ $( this ).attr('autocomplete', 'off'); });

    //-------------------------------------------------------------------//

	$("a.show-hide-slide").click(function(){
		var $alvo = $($(this).attr("href"));
        $(this).toggleClass('aberto'); 
        $alvo.slideToggle('fast');
		return false;
	});
	
    // $('a.confirm').click(function(){
    //     var msg = $(this).attr('title');
    //     return confirm(msg);
    // });
     $('a.confirm').click(function(){
        var $this=$(this);
        var msg = $(this).attr('title');
        swal({
            title: "Deseja continuar?",
            text: $this.attr('title'),
            showCancelButton: true,
            confirmButtonText: "Sim, continuar!",
            closeOnConfirm: true
        },function(){
            return location.href = $this.prop('href');
        });
        return false;
    });



    //-------------------------------------------------------------------//

    if($('header#topo section#logado .wrapper section#busca-voucher') [0]){
        var $buscavoucher = $('header#topo section#logado .wrapper section#busca-voucher');

        $buscavoucher.find('a.show').click(function() {
            $(this).fadeOut('fast');
            $buscavoucher.find('form').fadeIn('fast', function() {
                $buscavoucher.find('form input.input').focus();    
            });
            return false;
        });

        $buscavoucher.find('form input.input').blur(function() {
            $buscavoucher.find('form').fadeOut('fast');
            $buscavoucher.find('a.show').fadeIn('fast');
        });
        
    }

    //-------------------------------------------------------------------//

    if($('header#topo section#logado .wrapper section#header-muda-carnaval') [0]){
        var $mudacarnaval = $('header#topo section#logado .wrapper section#header-muda-carnaval');
        $mudacarnaval.find('a.arrow').click(function () {
            $mudacarnaval.toggleClass('open');
            $mudacarnaval.find('ul.drop').slideToggle('fast');
            return false;
        })
    }

    //-------------------------------------------------------------------//

    $("form .cancel").not('.no-cancel').click(function(){
        window.location.reload();
        return false;
    });

    $("form .cancel.fancy-close").click(function(){ parent.$.fancybox.close(); });

    //-------------------------------------------------------------------//

    $("form .selectbox, section#conteudo section.secao#compra-dados .informacoes-compra section.menu-impressao, .informacoes-compra section.menu-cartao").selectbox();
    $("form .radio").radio();
    $("form .checkbox").checkbox();

    //-------------------------------------------------------------------//

    if($(".tablesorter tbody td")[0]) {
        $(".tablesorter").tablesorter({
            sortInitialOrder: "desc",
            selectorHeaders: 'thead th',
            textExtraction: function(node){ 
                return $(node).text().replace('.','');
                return $(node).text().replace(',','.');
            }
        }).tablesorterPager({
            container: $(".pager-tablesorter"),
            positionFixed: false,
            size: 30
        });
        
    }

    if($(".tablesorter-nopager tbody td")[0]) {
        $(".tablesorter-nopager").tablesorter({
            sortInitialOrder: "desc",
            selectorHeaders: 'thead th',
            textExtraction: function(node){ 
                return $(node).text().replace('.','');
                return $(node).text().replace(',','.');
            }
        });        
    }

    //-------------------------------------------------------------------//

    if($("a.fancybox") [0]) {
        $("a.fancybox").fancybox({
            padding: 0 ,
            helpers : { title : null }
        });

        $("a.fancybox.modal").fancybox({ modal:true, helpers : { title : null } });
        $("a.fancybox.padding").fancybox({ padding: 15 });
        $("a.fancybox.width600").fancybox({ width : 600});
        $("a.fancybox.width800").fancybox({ width : 800});
        $("a.fancybox.width1480").fancybox({
            width : '1480',
            maxWidth : '100%',
        });
    }

    //-----------------------------------------------------------------//

    //Prevent Form Submit        
    $("#carnaval-dias-selecionados").on("keydown", 'input[name^="editar-data["]', function(event){
        if(event.keyCode == 13) {            
            // Clicar no submit
            $(this).nextAll('a.edit-data').trigger('click');

            event.preventDefault();
            return false;
        }
    });

    //adicionar mascaras
    $('form input[name="cep"]:text').mask('99999-999');
    $('form input[name="cpf"]:text').mask('999.999.999-99');
    $('form input[name^="data"]:text').mask('99/99/9999');
    $('form input[name^="hora"]:text').mask('99:99');
    $('form input[name="dia"]:text').mask('99/99');
    $('form input[name^="editar-data"]:text').mask('99/99');

    //-----------------------------------------------------------------//

    if($('form#cadastro-cliente')[0]) {

        var clientecpfcnpj = $('form#cadastro-cliente input[name="cpfcnpj"]:text').val();
        $('form#cadastro-cliente input[name="cpfcnpj"]:text').mask('999.999.999-99').val(clientecpfcnpj);
        $('form#cadastro-cliente input[name="pessoa"]:radio').change(function(){
            var pessoa = $(this).val();
            var $cpfcnpj = $('#cliente-cpfcnpj-box');
            var $nome = $('#cliente-nome-box');
            if($('#cliente-sobrenome-box')[0]) var $sobrenome = $('#cliente-sobrenome-box');
            var $razao = $('#cliente-razao-box');
            var $datanasc = $('#cliente-data-nascimento-box');

            var clientecpfcnpj = $('form#cadastro-cliente input[name="cpfcnpj"]:text').val();

            switch(pessoa) {
                case 'F':
                    $cpfcnpj.find('label').html('CPF:');
                    $cpfcnpj.find('input[name="cpfcnpj"]:text').unmask().mask('999.999.999-99').val(clientecpfcnpj);
                    $('form#cadastro-cliente section#cliente-sexo.selectbox').removeClass('disabled empty').find('input[name="sexo"]:radio').removeAttr('disabled');

                    $nome.find('label').html('Nome:');
                    if($('#cliente-sobrenome-box')[0]) $sobrenome.slideDown('fast').find('input[name="sobrenome"]:text').removeAttr('disabled');
                    $datanasc.find('label').html('Data de Nascimento:');
                    $razao.slideUp('fast').find('input[name="razao"]:text').attr('disabled', true);
                break;

                case 'J':
                    $cpfcnpj.find('label').html('CNPJ:');
                    $cpfcnpj.find('input[name="cpfcnpj"]:text').unmask().mask('99.999.999/9999-99').val(clientecpfcnpj);
                    $('form#cadastro-cliente section#cliente-sexo.selectbox').addClass('disabled').removeClass('empty').find('input[name="sexo"]:radio').attr('disabled', true);

                    $nome.find('label').html('Nome Fantasia:');
                    if($('#cliente-sobrenome-box')[0]) $sobrenome.slideUp('fast').find('input[name="sobrenome"]:text').attr('disabled', true);
                    $datanasc.find('label').html('Data de Fundação:');
                    $razao.slideDown('fast').find('input[name="razao"]:text').removeAttr('disabled');
                break;
            }
        });

    }

    //-----------------------------------------------------------------//

    if($('form#cadastro-parceiro')[0]) {

        var parceirocpfcnpj = $('form#cadastro-parceiro input[name="cpfcnpj"]:text').val();
        $('form#cadastro-parceiro input[name="cpfcnpj"]:text').mask('999.999.999-99').val(parceirocpfcnpj);
        $('form#cadastro-parceiro input[name="pessoa"]:radio').change(function(){
            var pessoa = $(this).val();
            var $cpfcnpj = $('#parceiro-cpfcnpj-box');
            var $nome = $('#parceiro-nome-box');
            var $razao = $('#parceiro-razao-box');
            var $inscricao = $('#parceiro-inscricao-box');
            var parceirocpfcnpj = $('form#cadastro-parceiro input[name="cpfcnpj"]:text').val();

            switch(pessoa) {
                case 'F':
                    $cpfcnpj.find('label').html('CPF:');
                    $cpfcnpj.find('input[name="cpfcnpj"]:text').unmask().mask('999.999.999-99').val(parceirocpfcnpj);
                    $('form#cadastro-parceiro section#parceiro-sexo.selectbox').removeClass('disabled empty').find('input[name="sexo"]:radio').removeAttr('disabled');

                    $nome.find('label').html('Nome:');
                    $razao.slideUp('fast').find('input[name="razao"]:text').attr('disabled', true);
                    $inscricao.slideUp('fast').find('input[name="razao"]:text').attr('disabled', true);
                break;

                case 'J':
                    $cpfcnpj.find('label').html('CNPJ:');
                    $cpfcnpj.find('input[name="cpfcnpj"]:text').unmask().mask('99.999.999/9999-99').val(parceirocpfcnpj);
                    $('form#cadastro-parceiro section#parceiro-sexo.selectbox').addClass('disabled').removeClass('empty').find('input[name="sexo"]:radio').attr('disabled', true);

                    $nome.find('label').html('Nome Fantasia:');
                    $razao.slideDown('fast').find('input[name="razao"]:text').removeAttr('disabled');
                    $inscricao.slideDown('fast').find('input[name="razao"]:text').removeAttr('disabled');
                break;
            }
        });

    }

    //-----------------------------------------------------------------//

    if($('form#cadastro-fornecedor')[0]) {

        var fornecedorcpfcnpj = $('form#cadastro-fornecedor input[name="cpfcnpj"]:text').val();
        $('form#cadastro-fornecedor input[name="cpfcnpj"]:text').mask('999.999.999-99').val(fornecedorcpfcnpj);
        $('form#cadastro-fornecedor input[name="pessoa"]:radio').change(function(){
            var pessoa = $(this).val();
            var $cpfcnpj = $('#fornecedor-cpfcnpj-box');
            var $nome = $('#fornecedor-nome-box');
            var $razao = $('#fornecedor-razao-box');
            var $inscricao = $('#fornecedor-inscricao-box');
            var fornecedorcpfcnpj = $('form#cadastro-fornecedor input[name="cpfcnpj"]:text').val();

            switch(pessoa) {
                case 'F':
                    $cpfcnpj.find('label').html('CPF:');
                    $cpfcnpj.find('input[name="cpfcnpj"]:text').unmask().mask('999.999.999-99').val(fornecedorcpfcnpj);
                    $('form#cadastro-fornecedor section#fornecedor-sexo.selectbox').removeClass('disabled empty').find('input[name="sexo"]:radio').removeAttr('disabled');

                    $nome.find('label').html('Nome:');
                    $razao.slideUp('fast').find('input[name="razao"]:text').attr('disabled', true);
                    $inscricao.slideUp('fast').find('input[name="razao"]:text').attr('disabled', true);
                break;

                case 'J':
                    $cpfcnpj.find('label').html('CNPJ:');
                    $cpfcnpj.find('input[name="cpfcnpj"]:text').unmask().mask('99.999.999/9999-99').val(fornecedorcpfcnpj);
                    $('form#cadastro-fornecedor section#fornecedor-sexo.selectbox').addClass('disabled').removeClass('empty').find('input[name="sexo"]:radio').attr('disabled', true);

                    $nome.find('label').html('Nome Fantasia:');
                    $razao.slideDown('fast').find('input[name="razao"]:text').removeAttr('disabled');
                    $inscricao.slideDown('fast').find('input[name="razao"]:text').removeAttr('disabled');
                break;
            }
        });

    }

    //-----------------------------------------------------------------//

    //Criando os requires
    $('form#cadastro-parceiro').validation({
        rules: {
            email: { tipo: 'email' },
            cep: { tipo: 'cep' },
            cpfcnpj: { tipo: 'cpfcnpj' },
            celular: { required: false },
            complemento: { required: false },
            inscricao: { required: false },
            banco: { required: false },
            agencia: { required: false },
            conta: { required: false },
            senha: { required: false },
            csenha: { required: false }
        }
    });

    $('form#cadastro-fornecedor').validation({
        rules: {
            email: { tipo: 'email' },
            cep: { tipo: 'cep' },
            cpfcnpj: { tipo: 'cpfcnpj' },
            celular: { required: false },
            complemento: { required: false },
            inscricao: { required: false },
            banco: { required: false },
            agencia: { required: false },
            conta: { required: false },
            senha: { required: false },
            csenha: { required: false }
        }
    });

    //Criando os requires
    $('form#cadastro-cliente').validation({
        rules: {
            email: { tipo: 'email' },
            cpfcnpj: { tipo: 'cpfcnpj' },
            senha: { required: false },
            csenha: { required: false },
            celular: { required: false },
            ddd_celular: { required: false },
            cpfcnpj: { required: false },
            passaporte: { required: false },
            'data-nascimento': {required: false}
        }
    });

    $('form#cadastro-sac').validation({
        rules: {
            email: { tipo: 'email' },
            solucao: { required: false }
        }
    });

    //Criando os requires
    $('form#roteiro-novo').validation({
        rules: {
            horario: { tipo: 'hora' }
        }
    });

    $('form#cadastro-cupom').validation({
         rules: {
            quantidade: { tipo: 'int' }
         }
    });
    $('form#carnaval-novo-escolas').validation();
    $('form#carnaval-novo-setores').validation();
    $('form[id^=agendamento]').validation();
    $('form#carnaval-novo').validation({
        rules: {
            dia: { tipo: 'data' }
        }
    });  
    
    $('form#cadastro-usuario').validation();

    $('form#form-compra-pagamento').validation();

    //Criando os requires
    $('form#cadastro-vendedor-externo').validation({
        rules: {
            email: { tipo: 'email' },
            tel: { tipo: 'tel' }
        }
    });
    
    $("#ano_validade").mask("9999");
    $("#mes_validade").mask("99");
    $("#codigo_seguranca").mask("9?999");
    
    $('form#pagamento-cartao').validation({
        rules: {
            numero_cartao: { required: true},
            ano_validade: { required: true },
            mes_validade: { required: true },
            codigo_seguranca: { required: true },
            cpfcnpj: { required: true },
            parcelas: { required: true },
            endereco: { required: true }
        }
    });

    //-----------------------------------------------------------------//

    //------------------------------------------------------------------------------------------//

    $("form#form-compra-pagamento #compra-cartao input[name='codigoBandeira']").change(function(){
        var cartao = $(this).val();

        if(cartao == 'discover') {
            $("form#form-compra-pagamento #compra-forma-pagamento.selectbox input[name='formaPagamento']").radioSel("1");
            $("form#form-compra-pagamento #compra-forma-pagamento.selectbox ul.drop li").each(function(){
                if(parseInt($(this).find("input:radio").val()) > 1) { $(this).hide(); }
            });
        } else if(cartao == 'amex') {
            $("form#form-compra-pagamento #compra-forma-pagamento.selectbox input[name='formaPagamento']").radioSel("1");
            $("form#form-compra-pagamento #compra-forma-pagamento.selectbox ul.drop li").each(function(){
                if(parseInt($(this).find("input:radio").val()) > 6) { $(this).hide(); }
            });
        } else {
            $("form#form-compra-pagamento #compra-forma-pagamento.selectbox ul.drop li").each(function(){ $(this).show() });
        }
    });

    //-----------------------------------------------------------------//

    //check permissoes usuarios
    $('section#conteudo form#cadastro-usuario input[name="menuscod[]"]').change(function(){
        var menu = $(this).val();
        if($(this).is(':checked')) {
            $(this).closest('tr').addClass('checked');
            $(this).closest('form').find('input[name="submenuscod[]"][rel="'+menu+'"]').each(function(){
                if(!$(this).is(':checked')) $(this).trigger("click");
            });
        } else {
            $(this).closest('tr').removeClass('checked');    
            $(this).closest('form').find('input[name="submenuscod[]"][rel="'+menu+'"]').each(function(){
                if($(this).is(':checked')) $(this).trigger("click");
            });        
        }
    });

    $('section#conteudo form#cadastro-usuario input[name="submenuscod[]"]').change(function(){
        var menu = $(this).attr("rel");
        if($(this).is(':checked')) {
            // $(this).closest('tr').addClass('checked');
            // $(this).closest('form').find('input[name="menuscod[]"][value="'+menu+'"]').not(':checked').closest("label.item").addClass("checked").closest("tr").addClass("checked");
            $(this).closest('tr').not('.checked').addClass('checked');
            $principal = $(this).closest('tr').prevAll('tr.principal');
            if($principal.not('.checked')) {
                $principal.addClass('checked').find('label.item').addClass('checked').find('input[name="menuscod[]"]').attr('checked', true);
            }
        } else {
            $(this).closest('tr').removeClass('checked');
            // var contagem = $(this).closest('form').find('input[name="submenuscod[]"][rel="'+menu+'"]').length;
            var contagem = $(this).closest('form').find('input[name="submenuscod[]"][rel="'+menu+'"]:checked').length;
            if(contagem == 0) $(this).closest('form').find('input[name="menuscod[]"][value="'+menu+'"]').trigger('click');
        }
    });

    //-----------------------------------------------------------------//

    //alterar campo quando mudar o tipo de cupom
    $("#cadastro-cupom").find("input[name='tipo']").change(function(){
        var $obj = $(this);
        var tipo = $obj.val();

        if(tipo == 1) {
            $obj.closest("form").find("#valor").show();
            $obj.closest("form").find("#valor label").html("Desconto em (%)");
            $obj.closest("form").find("input[name='valor']").maskMoney('destroy');
            $obj.closest("form").find("input[name='valor']").val("");
        } else if(tipo == 2) {
            $obj.closest("form").find("#valor").show();
            $obj.closest("form").find("#valor label").html("Desconto em (R$)");
            $obj.closest("form").find("input[name='valor']").maskMoney({symbol:'', thousands:'.', decimal:',', symbolStay: false, allowZero: true });
            $obj.closest("form").find("input[name='valor']").maskMoney({symbol:'', thousands:'.', decimal:',', symbolStay: false, allowZero: true });
            $obj.closest("form").find("input[name='valor']").val("");
        }
    });

    //-----------------------------------------------------------------//

    //Relatório em tempo real
    if($('tr#tempo-real')[0]) {
        setInterval(function(){
            temporeal();
        }, 300000);
    }

    temporeal();

    function temporeal() {

        var tipo = $("#tempo-real").data("tipo");
        var dia = $("#tempo-real").data("dia");

        $.post(site + "include/relatorio-tempo-real.php", {
            tipo: tipo,
            dia: dia
        }, function(resposta) {
            if(resposta.sucesso) {
                $("#tempo-real").html("<td>"+resposta.data+"</td><td>"+resposta.qtde_atual+"</td><td>R$ "+resposta.valor_atual+"</td>");
            } else {
                alert('O dia selecionado já está cadastrado.');
            }
        }, 'json');
    }

    //-----------------------------------------------------------------//

    //abrir input para editar dia
    $("#carnaval-dias-selecionados").on("click", "a.editar", function(){
        var id = $(this).attr("href");
        $("#form-edit-"+id).show();
        return false;
    });

    //editar dia
    $("#carnaval-dias-selecionados").on("click", "a.edit-data", function(){
        var editkey = $(this).attr("href");
        var editdata = $("input[name='editar-data["+editkey+"]']").val();
        var editano = $("#carnaval-novo").find("input[name='ano']").val();
        var datacod = $(this).attr("rel");
        var site = $("#base-site").val();

        // Data
        var regexpdata = /(^\d{1,2}\/\d{1,2}\/\d{4}$)|(^\d{1,2}\/\d{1,2}$)/;
       
        if(!regexpdata.test(editdata) || editdata == "") {
            $(this).closest("div").find("input").addClass('invalid');
        } else {
            $(this).removeClass('invalid');
            $.post(site + "include/carnaval-adicionar-dia.php", {
                edit: true,
                editkey: editkey,
                editdata: editdata,
                editano: editano,   
                datacod: datacod
            }, function(resposta) {
                if(resposta.sucesso) {
                    $("#carnaval-dias-selecionados ul").html(resposta.lista);
                    $("#carnaval-dias-atracoes ul").html(resposta.escolas);
                    $("#carnaval-dias-atracoes ul li label.infield").inFieldLabels({ fadeOpacity:0.3 });
                } else {
                    alert('O dia selecionado já está cadastrado.');
                }
            }, 'json');
            return false;
        }
        return false;
    });

    //Insere sugestão de nome pro carnaval
    $("#carnaval-novo-escolas").find("input[name='ano']").change(function(){
        var ano = $(this).val();
        $("#carnaval-novo-escolas").find("input[name='nome']").val("Carnaval "+ano);
        $("#carnaval-novo").find("input[name='ano']").val(ano);
        var site = $("#base-site").val();
        $.post(site + "include/carnaval-adicionar-dia.php", { limpar: true }, function(resposta) {
            if(!resposta.sucesso) {                                               
                //Retorno
                $("#carnaval-dias-selecionados ul").html(resposta.lista);
                $("#carnaval-dias-atracoes ul").html(resposta.escolas);
                $("#carnaval-dias-selecionados").hide();
                $("#carnaval-dias-atracoes").hide();
            }                            
        }, 'json');

        $.post(site + "include/carnaval-adicionar-setor.php", { limpar: true }, function(resposta) {
            if(!resposta.sucesso) {                                               
                //Retorno
                $("#carnaval-setores ul").html(resposta.lista);
                $("#carnaval-setores ul").hide();
                $("#carnaval-setores small").show();
            }                          
        }, 'json');
    });

    //Adiciona dias no carnaval
    $("form#carnaval-novo").submit(function(e) {

        if(!$(this).hasClass('return-false')){
            var site = $("#base-site").val();
            $.post(site + "include/carnaval-adicionar-dia.php", $(this).serialize(), function(resposta) {
                if(resposta.sucesso) {                                                
                    //Retorno 
                    $("#carnaval-dias-selecionados ul").html(resposta.lista);
                    $("#carnaval-dias-atracoes ul").html(resposta.escolas);
                    $("#carnaval-dias-atracoes ul li label.infield").inFieldLabels({ fadeOpacity:0.3 });
                    $("#carnaval-dias-selecionados").show();
                    $("#carnaval-dias-atracoes").show();
                    $("#carnaval-novo").find("input[name='dia']").val("");
                }                               
            }, 'json');
            return false;
        }

    });

    //remover dias
    $("#carnaval-dias-selecionados").on("click", "a.remover", function(){
        var dia = $(this).find("small").html();
        if(!confirm('Tem certeza que deseja o dia '+dia+' do Carnaval?')) {
            return false;
        } else {
            var site = $("#base-site").val();
            $.post(site + "include/carnaval-adicionar-dia.php", { key: $(this).attr("href") }, function(resposta) {
                if(resposta.sucesso) {                                               
                    //Retorno
                    $("#carnaval-dias-selecionados ul").html(resposta.lista);
                    $("#carnaval-dias-atracoes ul").html(resposta.escolas);
                    $("#carnaval-dias-atracoes ul li label.infield").inFieldLabels({ fadeOpacity:0.3 });
                } else {
                    $("#carnaval-dias-selecionados ul").html();
                    $("#carnaval-dias-atracoes").html();
                    $("#carnaval-dias-selecionados").hide();
                    $("#carnaval-dias-atracoes").hide();
                }                              
            }, 'json');
        }
        return false;
    });

    //adicionar setores
    $("form#carnaval-novo-setores").submit(function() {
        var site = $("#base-site").val();

        if(!$(this).hasClass('return-false')){
            $.post(site + "include/carnaval-adicionar-setor.php", $(this).serialize(), function(resposta) {
                if(resposta.sucesso) {                                                
                    //Retorno 
                    $("#carnaval-setores ul").show();
                    $("#carnaval-setores ul").html(resposta.lista);
                    $("#carnaval-setores small").hide();
                    $("#carnaval-setores").find("input[name='setor']").val("");
                }                               
            }, 'json');
            return false;
        }
    });

    //remover setor
    $("#carnaval-setores").on("click", "a.remover", function(){
        var setor = $(this).html();
        if(!confirm('Tem certeza que deseja remover o Setor '+setor+'?')) {
            return false;
        } else {
            if(!$(this).hasClass("edit")) { var edit = false; } else { var edit = true; }
            var site = $("#base-site").val();            
            $.post(site + "include/carnaval-adicionar-setor.php", { 
                edit: edit, 
                key: $(this).attr("href") 
            }, function(resposta) {
                if(resposta.sucesso) {                                                
                    //Retorno
                    $("#carnaval-setores ul").html(resposta.lista);
                } else {
                    $("#carnaval-setores ul").html();
                    $("#carnaval-setores ul").hide();
                    $("#carnaval-setores small").show();
                }                              
            }, 'json');
        }
        return false;
    });

    //-----------------------------------------------------------------//

    //adicionar item
    $("#roteiro-novo").on("click", "a.adicionar", function(){
        var $alvo = $(this).closest('ul').find("li").last();
        var key = (parseInt($alvo.attr("rel")) + 1);

        $alvo.after('<li rel="'+key+'"><a href="#" class="remover novo confirm" title="Tem certeza que deseja remover esse item?"></a><p class="coluna"><label for="roteiro-item-'+key+'-nome" class="infield">Nome do Local</label><input type="text" name="nome['+key+']" class="input nome" id="roteiro-item-'+key+'-nome" value="" /></p><p class="coluna"><label for="roteiro-item-'+key+'-endereco" class="infield">Endereço</label><input type="text" name="endereco['+key+']" class="input horario" id="roteiro-item-'+key+'-endereco" value="" /></p><p class="coluna"><label for="roteiro-item-'+key+'-telefone" class="infield">Telefone</label><input type="text" name="telefone['+key+']" class="input horario" id="roteiro-item-'+key+'-telefone" value="" /></p><p class="coluna"><label for="roteiro-item-'+key+'-horario-1" class="infield">Horário 01</label><input type="text" name="horario['+key+'][1]" class="input horario" id="roteiro-item-'+key+'-horario-1" value="" /></p><p class="coluna"><label for="roteiro-item-'+key+'-horario-2" class="infield">Horário 02</label><input type="text" name="horario['+key+'][2]" class="input horario" id="roteiro-item-'+key+'-horario-2" value="" /></p><p class="coluna"><label for="roteiro-item-'+key+'-horario-3" class="infield">Horário 03</label><input type="text" name="horario['+key+'][3]" class="input horario" id="roteiro-item-'+key+'-horario-3" value="" /></p><p class="coluna"><label for="roteiro-item-'+key+'-horario-4" class="infield">Horário 04</label><input type="text" name="horario['+key+'][4]" class="input horario" id="roteiro-item-'+key+'-horario-4" value="" /></p><div class="clear"></div></li>');

        $("#roteiro-novo ul li label.infield").inFieldLabels({ fadeOpacity:0.3 });
        $('form input[name^="hora"]').mask('99:99');
        return false;
    });

    //remover item
    $("#roteiro-novo").on("click", "a.remover.novo", function(){
        var $alvo = $(this).closest('li').last();
        $alvo.remove();
    });

    //-----------------------------------------------------------------//

    //lista os locais pelo roteiro
    $(".selectbox[id^=item-roteiro]").on("change", "input[name='item-roteiro']", function(){
        var $select = $(this);
        if(!$select.closest('.selectbox').hasClass('start')) {

            var site = $("#base-site").val();
            var roteiro = $select.val();
            var $select_alvo = $select.closest("form").find(".selectbox[id^=item-transporte] ul.drop");
            
            $.post(site + "include/agendamento-buscar-roteiros.php", { 
                roteiro: roteiro
            }, function(resposta) {
                if(resposta.sucesso) {                                            
                    //Retorno
                    $select.closest("form").find(".selectbox[id^=item-transporte] a.arrow strong").html("Local");
                    $select_alvo.html(resposta.lista);
                    $select.closest("form").find(".selectbox[id^=item-horario] a.arrow strong").html("Horário");
                    $select.closest("form").find(".selectbox[id^=item-horario] ul.drop").html("");
                }                              
            }, 'json');
            return false;
        }
    });

    //lista os horarios pelo local
    $(".selectbox[id^=item-transporte]").on("change", "input[name='item-transporte']", function(){
        var $select = $(this);
        if(!$select.closest('.selectbox').hasClass('start')) {

            var site = $("#base-site").val();
            var transporte = $select.val();
            var $select_alvo = $select.closest("form").find(".selectbox[id^=item-horario] ul.drop");
            
            $.post(site + "include/agendamento-buscar-roteiros.php", { 
                transporte: transporte
            }, function(resposta) {
                if(resposta.sucesso) {                                            
                    //Retorno
                    $select.closest("form").find(".selectbox[id^=item-horario] a.arrow strong").html("Horário");
                    $select_alvo.html(resposta.lista);
                }                              
            }, 'json');
            return false;
        }
    });


    //pagamento cartao
    $('form#pagamento-cartao').submit(function(event) {
        var $form=$(this);
        if(!$form.hasClass('return-false')){
        }else{
            if($('form#pagamento-cartao').find('input[name="endereco"]:checked').length!=1){
                swal("Aviso", "Selecione o endereço de pagamento!", "warning");
            }
            return false;
        }
    });
//-----------------------------------------------------------------------//

    var $cadastro = $("form");

        $cadastro.find("input[name='cep']").blur(function(){
            
            var site = $("#base-site").val();
            
            // Pegamos o valor do input CEP
            var cep = $cadastro.find("input[name='cep']").val();
            
            // Se o CEP nÃ£o estiver em branco
            if(cep != '') {

                // Adiciona imagem de "Loading"
                $cadastro.find(".endereco input[name='cep']").addClass('loading');
                
                $.getJSON(site + "include/busca-cep.php", {
                    cep: cep
                }, function(resultado) {                
                    $cadastro.find(".endereco input[name='cep']").removeClass('loading');

                    //Valores
                    $cadastro.find("input[name='endereco']").val(resultado.logradouro).blur();
                    $cadastro.find("input[name='bairro']").val(resultado.bairro).blur();
                    $cadastro.find("input[name='cidade']").val(resultado.cidade).blur();

                    if($cadastro.find("input[name='estado']").data('uf') != null) $cadastro.find("input[name='estado'][data-uf='"+resultado.uf+"']").trigger('click');
                    else $cadastro.find("input[name='estado']").radioSel(resultado.uf);

                    //alteração para o cadastro de enderecos do pagamento
                    $cadastro.find("input[id='estado']").val(resultado.uf);

                    $cadastro.find("input[name='numero']").focus();
                });
            } else {
                // Se o campo CEP estiver em branco, apresenta mensagem de erro
                // alert('Para que o endereÃ§o seja completado automaticamente vocÃª deve preencher o campo CEP!');
            }
            return false;
        });

        //-------------------------------------------------------------------//

    
    // $('form').on('focus', 'input[name^="telefone"],input[name^="celular"]',function () { $(this).mask("(99) 9999-9999?9"); });
    // $('form').on('focusout', 'input[name^="telefone"],input[name^="celular"]',function () {
    //     var phone, element;
    //     element = $(this);
    //     element.unmask();
    //     phone = element.val().replace(/\D/g, '');
    //     if (phone.length > 10) element.mask("(99) 99999-999?9");
    //     else element.mask("(99) 9999-9999?9");
    // });

    $('form input[name="cpfcnpj"]').keyup(function(e){
        var $cpfcnpj = $(this);
        var valor = $cpfcnpj.val();
        
        posicao = $cpfcnpj.cursorpos();
        
        if((valor.length > 0) && (posicao == 0)) { $cpfcnpj.unmask().val(''); }

        if(valor.length <= 4) {
            var ponto = (valor.indexOf('.'));
            if(ponto == 2) $cpfcnpj.unmask().mask('?99.999.999/9999-99');
            else if(ponto == 3) $cpfcnpj.unmask().mask('?999.999.999-99');
        }
    });

    //-----------------------------------------------------------------//
    
// section#conteudo form#ingresso-compra-novo p input.input#ingresso-estoque {
    $('section#conteudo form#ingresso-compra-novo #arquibancada-opcao-fileira input, section#conteudo form#ingresso-compra-novo #arquibancada-opcao-numero-de input, section#conteudo form#ingresso-compra-novo #arquibancada-opcao-numero-ate input').attr('disabled', true);

    $('section#conteudo form#ingresso-compra-novo .checkbox input[name="numerada"]').change(function(){
        if($(this).is(':checked')) {

            $('section#conteudo form#ingresso-compra-novo #arquibancada-opcao-estoque').hide().find('input').attr('disabled', true);
            $('section#conteudo form#ingresso-compra-novo #arquibancada-opcao-fileira, section#conteudo form#ingresso-compra-novo #arquibancada-opcao-numero-de, section#conteudo form#ingresso-compra-novo #arquibancada-opcao-numero-ate').show();
            $('section#conteudo form#ingresso-compra-novo #arquibancada-opcao-fileira input, section#conteudo form#ingresso-compra-novo #arquibancada-opcao-numero-de input, section#conteudo form#ingresso-compra-novo #arquibancada-opcao-numero-ate input').removeAttr('disabled');

        } else {

            $('section#conteudo form#ingresso-compra-novo #arquibancada-opcao-fileira, section#conteudo form#ingresso-compra-novo #arquibancada-opcao-numero-de, section#conteudo form#ingresso-compra-novo #arquibancada-opcao-numero-ate').hide();
            $('section#conteudo form#ingresso-compra-novo #arquibancada-opcao-fileira input, section#conteudo form#ingresso-compra-novo #arquibancada-opcao-numero-de input, section#conteudo form#ingresso-compra-novo #arquibancada-opcao-numero-ate input').attr('disabled', true);
            $('section#conteudo form#ingresso-compra-novo #arquibancada-opcao-estoque').show().find('input').removeAttr('disabled');

        }
            
    });

    //------------------- Check Adicionais -------------------//

    $('section#conteudo form#ingresso-venda-novo section#ingresso-vendas-adicionais input[name="adicionaiscod[]"]').change(function(){
        if($(this).is(':checked')) {
            $(this).closest('tr').addClass('checked').find('input[name^="adicionaisvalor"]').removeAttr('disabled').removeClass('disabled');
        } else {
            $(this).closest('tr').removeClass('checked').find('input[name^="adicionaisincluso"]:checked').trigger('click');
            $(this).closest('tr').find('input[name^="adicionaisvalor"]').attr('disabled', true).addClass('disabled');
        }
    });

    $('section#conteudo form#ingresso-venda-novo section#ingresso-vendas-adicionais input[name^="adicionaisincluso"]').change(function(){
        if($(this).is(':checked')) {
            if(!$(this).closest('tr').hasClass('checked')) $(this).closest('tr').find('input[name="adicionaiscod[]"]').trigger('click');
            $(this).closest('tr').find('input[name^="adicionaisvalor"]').attr('disabled', true).addClass('disabled');
        } else {
            $(this).closest('tr.checked').find('input[name^="adicionaisvalor"]').removeAttr('disabled').removeClass('disabled');
        }
    });

    //------------------- Compras -------------------//

    $('section#conteudo form#compras-novo section#setor-ingresso input[name="setor"]:radio').not('.disabled').change(function() {
        var $setor = $(this);
        if($setor.is(':checked')){

            var dias = jQuery.parseJSON($setor.attr('rel'));
            $('section#conteudo form#compras-novo section#compra-dias label.item').each(function() {
                var $dia = $(this);

                $('section#conteudo form#compras-novo section#compra-dias').removeClass('empty');
                $dia.removeClass('checked').addClass('disabled');
                $dia.find('input[name="dia"]:radio').removeAttr('checked').addClass('disabled');

                if(dias.indexOf($dia.find('input[name="dia"]:radio').val()) != '-1') {
                    $dia.removeClass('disabled');
                    $dia.find('input[name="dia"]:radio').removeClass('disabled');
                }
            });

            //Limpar os itens
            $('section#conteudo form#compras-novo section#compras-itens').html('');
            $('section#conteudo form#compras-novo section#compra-dias section.aviso-descontos').fadeOut('fast');
        }
    });

    $('section#conteudo form#compras-novo section#compras-itens').on("change", 'input[name="item[]"]', function(){
        if($(this).is(':checked')) $(this).closest('section.item-compra').addClass('checked');
        else $(this).closest('section.item-compra').removeClass('checked');
    });


    $('section#conteudo form#compras-novo section#compra-dias input[name="dia"]').change(function(){
        if($(this).is(':checked')) {
            var valores = $('section#conteudo form#compras-novo').serialize();
            var site = $("#base-site").val();

            $('section#conteudo form#compras-novo section#compras-itens').addClass('loading');

            $.post(site + "include/compras-adicionar-itens.php", valores, function(resposta) {
                if(resposta.sucesso) {
                    $('section#conteudo form#compras-novo section#compras-itens').html(resposta.itens).removeClass('loading').find('.checkbox').checkbox();
                    $('section#conteudo form#compras-novo section#compras-itens input.input.money').maskMoney({symbol:'', thousands:'.', decimal:',', symbolStay: false, allowZero: true });
                    
                    if(resposta.quantidade > 0)  $('section#conteudo form#compras-novo section#compra-dias section.aviso-descontos').fadeIn('fast');
                    else $('section#conteudo form#compras-novo section#compra-dias section.aviso-descontos').fadeOut('fast');
                } else { 
                    $('section#conteudo form#compras-novo section#compra-dias section.aviso-descontos').fadeOut('fast');
                }
            }, 'json');
        }

    });

    //------------------- Compras carrinho quantidade -------------------//

    $('section#conteudo form#compras-carrinho td.qtde input[name^="quantidade"]').blur(function(){
        var site = $("#base-site").val();

        var $qtde = $(this);
        var qtd = parseInt($qtde.val());
        if(!(qtd > 0)) {
            $qtde.val('1')
            qtd = 1;
        }

        //Estoque
        var estoque = $qtde.closest('td.qtde').find('input[name="estoque"]').val();
        if(qtd > estoque) $qtde.closest('tr').addClass('block');
        else $qtde.closest('tr.block').removeClass('block');
        
        //Total 
        var total = 0.00;
        var valor = parseFloat($qtde.closest('td.qtde').find('input[name="valor"]').val());
        
        total = parseFloat(valor*qtd);
        total = total.toFixed(2).replace(".",",");
        if(total.length > 6) total = total.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

        $qtde.closest('tr').find('td.valor').html('R$ '+ total);

        //Quantidade
        var key = $qtde.attr('rel');

        //Enviar a quantidade para sessao
        $.getJSON(site + "e-compras-adicionar.php", {
            quantidade: qtd,
            a: 'quantidade',
            c: key
        });

    });


    //------------------- Check Adicionais -------------------//

    $('section#conteudo form#compras-adicionais td.valor.text input.input').maskMoney({symbol:'', thousands:'', decimal:'.', symbolStay: false, allowZero: true });

    // Calcular adicionais
    if($('section#conteudo form#compras-adicionais')[0]) compras_adicionais_total();
    
    $('section#conteudo form#compras-adicionais input.adicional').change(function(){
        if($(this).is(':checked')) $(this).closest('tr').addClass('checked');
        else $(this).closest('tr').removeClass('checked');

        compras_adicionais_total();
    });

    $('section#conteudo form#compras-adicionais td.valor.text input.input').keyup(function(){
        $(this).closest('tr').find('input:checkbox').not(':checked').trigger('click');
        compras_adicionais_total();
    });

    $('section#conteudo form#compras-adicionais .item-carrinho input[name^="quantidade"]').blur(function(){
        var site = $("#base-site").val();

        var $qtde = $(this);
        var qtd = parseInt($qtde.val());
        if(!(qtd > 0)) {
            $qtde.val('1')
            qtd = 1;
        }

        //Estoque
        var estoque = $qtde.closest('.item-carrinho').find('input[name="estoque"]').val();
        if(qtd > estoque) $qtde.closest('.item-carrinho').addClass('indisponivel');
        else $qtde.closest('.item-carrinho').removeClass('indisponivel');
        
        //Quantidade
        var key = $qtde.attr('rel');

        if(!$('form#compras-adicionais').hasClass('modificar')) {
            //Enviar a quantidade para sessao
            $.getJSON(site + "e-compras-adicionar.php", {
                quantidade: qtd,
                a: 'quantidade',
                c: key
            });            
        } else {
            //Enviar a quantidade para sessao
            $.getJSON(site + "e-compras-modificar-adicionar.php", {
                quantidade: qtd,
                a: 'quantidade',
                cod: $('form#compras-adicionais input[name="compra"]').val(),
                c: key
            });  
        }
        
        // Atualizar valor total
        var total = 0.00;
        var valor = parseFloat($qtde.closest('.item-carrinho').find('input[name="valoritem"]').val());
        
        total = parseFloat(valor*qtd);
        total = total.toFixed(2).replace(".",",");
        if(total.length > 6) total = total.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

        $qtde.closest('.item-carrinho').find('header .valor').html('R$ '+ total);

        compras_adicionais_total();
    });

    $('section#conteudo form#compras-adicionais input[name^="transferqtde"]').change(function(){
        var $transfer = $(this);
        var qtd = $transfer.val();
        // var qtd_item = $transfer.closest('.item-carrinho').find('input[name^="quantidade"]').val();
        
        var valor = parseFloat($transfer.closest('tr').find('input[name="valoradicionaltransfer"]').val());

        if(valor > 0) {
            // totalf = parseFloat(valor*qtd*qtd_item);
            totalf = parseFloat(valor*qtd);
            total = totalf.toFixed(2).replace(".",",");
            if(total.length > 6) total = total.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");
            
            $transfer.closest('tr').find('input[name="valoradicional"]').val(totalf);
            $transfer.closest('tr').find('td.valor').html('R$ '+ total);

            compras_adicionais_total();
        }
    });

    //------------------- Canal -------------------//

    if($('section#conteudo form#compras-adicionais section#compra-forma-pagamento.selectbox')[0]){
        
        var $deadline = $('section#conteudo form#compras-adicionais input#compra-deadline.input');

        $('section#conteudo form#compras-adicionais section#compra-forma-pagamento.selectbox input[name="forma"]').change(function(){
            if($(this).val() == 5) $deadline.removeClass('disabled').removeAttr('disabled').focus();
            else $deadline.val('').addClass('disabled').attr('disabled', true);
        });

    }

    //------------------- Canal -------------------//

    if($('section#conteudo form#compras-adicionais section#compra-canal-venda.selectbox')[0]){

        var $vendedor = $('section#conteudo form#compras-adicionais section#compra-vendedor-externo.selectbox');

        $('section#conteudo form#compras-adicionais section#compra-canal-venda.selectbox input[name="canal"]').change(function() {
            
            var canal = $(this).val();

            //if(canal == 54) $('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.incluso.desconto.folia').show().find('input.desconto').removeAttr('disabled');
            //else $('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.incluso.desconto.folia').hide().find('input.desconto').attr('disabled', true);
            compras_adicionais_total();

            //Alterar comissao
            var comissao = $('section#conteudo form#compras-adicionais section#compra-canal-venda input[name="canal"]:checked').attr('rel');
            $('section#conteudo form#compras-adicionais p.comissao input#compra-comissao').val(comissao);
            
            //Comissao
            $('section#conteudo form#compras-adicionais p.comissao-modificar').addClass('comissao').removeClass('comissao-modificar');

            //------------------- Vendedor -------------------//

            var site = $("#base-site").val();

            $.post(site + "include/vendedores-externos.php", { cod: canal }, function(resposta) {
                if(resposta.sucesso) {
                    var lista = '';

                    if(resposta.quantidade > 0) $vendedor.removeClass('disabled').find('ul.drop').html(resposta.vendedores);
                    else $vendedor.addClass('disabled').removeClass('empty').find('ul.drop').html('');

                    //Modificacao
                    if($('input[name="vendedor-externo-checked"]')[0]) {
                        $vendedor.find('input[name="vendedor-externo"]').radioSel($('input[name="vendedor-externo-checked"]').val());
                        $('input[name="vendedor-externo-checked"]').remove();
                    }

                } else {                    
                    $vendedor.addClass('disabled').removeClass('empty').find('ul.drop').html('');
                }

            }, 'json');
        })

    }

    function compras_adicionais_total() {

        // calcular o total
        var total = 0.00;

        $('section#conteudo form#compras-adicionais .item-carrinho').not('.extra').each(function () {
            $item = $(this);
            var itemval = parseFloat($item.find('input[name="valoritem"]').val()) * parseInt($item.find('input[name^="quantidade"]').val());
            total += parseFloat(itemval);
        });

        //Se houver desconto de 10% (Novos combos)
        if($('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.desconto.folia input.desconto').not(':disabled')[0]) {
            // var $desconto = $('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.desconto.folia input.desconto');
            var $desconto = $('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.desconto.folia input.desconto:checked');
            var desconto = ($desconto.hasClass('novo')) ? parseFloat($desconto.val()) : 10;
            total = ((100 - desconto) * total) / 100;
        }

        //Se houver desconto de frisa
        if($('section#conteudo form#compras-adicionais .item-carrinho.frisa')[0]) {
            
            var frisas_qtde = 0;            
            $('section#conteudo form#compras-adicionais .item-carrinho.frisa input.qtde').each(function() {
                var frisas_fechadas = Math.floor($(this).val() / 6);
                if(frisas_fechadas > 0) frisas_qtde = frisas_qtde + frisas_fechadas;
            });
            
            var $frisa_desconto = $('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.desconto.frisa');
            
            if(frisas_qtde > 0){

                var frisas_total = frisas_qtde * 50;
                
                $frisa_desconto.show().find('input.desconto').removeAttr('disabled').val(frisas_total);

                frisas_total = frisas_total.toFixed(2).replace(".",",");
                if(frisas_total.length > 6) frisas_total = frisas_total.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");
                $frisa_desconto.find('td.valor').html('- R$ '+ frisas_total);

            } else {                
                $frisa_desconto.hide().find('input.desconto').attr('disabled', true);
            }
        }

        if($('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.desconto.frisa input.desconto:checked').not(':disabled')[0]) {
            // var desconto = parseFloat($('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.desconto.frisa input.desconto').val());
            var desconto = parseFloat($('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.desconto.frisa input.desconto:checked').val());
            total = (total - desconto);
        }

        // $('section#conteudo form#compras-adicionais tr.checked input[name="valoradicional"]').each(function () {
        $('section#conteudo form#compras-adicionais tr input[name^="valoradicional"]').not('input[name="valoradicionaltransfer"]').each(function () {
            var $adicional = $(this);
            adicional = parseFloat($(this).val());
            if($adicional.hasClass('multi')) adicional = adicional * parseInt($adicional.closest('.item-carrinho').find('input[name^="quantidade"]').val());

            if($adicional.closest('tr').hasClass('checked')) total += parseFloat(adicional);

            adicional = adicional.toFixed(2).replace(".",",");
            if(adicional.length > 6) adicional = adicional.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

            $adicional.closest('tr').find('td.valor').not('.text').html('R$ '+ adicional);
        });

        total = total.toFixed(2).replace(".",",");
        if(total.length > 6) total = total.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

        $('section#conteudo form#compras-adicionais').find('header .valor-total, footer .valor-total').html('R$ '+ total);
    }

    //------------------- No submit -------------------//
    
    $('section#conteudo form#compras-adicionais input[name="delivery"].adicional').change(function(){
        if($(this).is(':checked')) {
            $('section#conteudo form#compras-adicionais .item-carrinho table.lista tr.retirada .selectbox').addClass('disabled').find('input:radio').attr('disabled', true);
            $('section#conteudo form#compras-adicionais .item-carrinho table.lista tr.retirada p').addClass('disabled').find('input.input').addClass('disabled').attr('disabled', true);
        } else {
            $('section#conteudo form#compras-adicionais .item-carrinho table.lista tr.retirada .selectbox').removeClass('disabled').find('input:radio').removeAttr('disabled');
            $('section#conteudo form#compras-adicionais .item-carrinho table.lista tr.retirada p').removeClass('disabled').find('input.input').removeClass('disabled').removeAttr('disabled');
        }
    });

    //------------------- No submit -------------------//

    //Prevent Form Submit        
    $('section#conteudo form#compras-adicionais').on('keydown', 'input#carrinho-cliente', function(event){
        if(event.keyCode == 13) {            
            event.preventDefault();
            return false;
        }
    });

    //------------------- Sugestao -------------------//

    if($('section#conteudo section#compras-cliente')[0]){

        var currentTimer = null;
        var $sugestao = $("section#conteudo section#compras-cliente");

        $sugestao.find("input[name='cliente-sugestao']").keyup(function(event){
            
            var $input = $(this);

            if(event.which == 40) {
                if($sugestao.find('.drop li').size() > 0) $sugestao.find('.drop').addClass("show").show();
            } else {

                if (!currentTimer) {
                    currentTimer = true;
                    setTimeout(function() {
                        if (currentTimer) {
                            currentTimer = null;

                            // if (event.which == 13) {
                            //  event.preventDefault();
                            //  return false;
                            // }
                        
                            var q = $input.val();
                            var site = $("#base-site").val();

                            $input.addClass('loading');

                            $.getJSON(site+"include/compras-sugestao.php", {
                                b: q
                            }, function(resposta) {

                                $input.removeClass('loading');
                                
                                if(resposta.sucesso) {
                                    $sugestao.find('.drop ul').html(resposta.resposta);

                                    //Exibir
                                    if(resposta.encontradas > 0) $sugestao.find('.drop').addClass("show").show();
                                    else $sugestao.find('.drop').removeClass("show").hide();

                                } else {
                                    $sugestao.find('.drop').removeClass("show").hide();
                                }
                            });             
                    
                        
                        }
                    }, 2000);
                }
            
            }


            
        });

        $sugestao.find("input[name='cliente-sugestao']").focus(function(){
            $input = $(this);
            $input.addClass("focusenter");
            setTimeout(function(){ $input.removeClass("focusenter"); }, 300);
            if($sugestao.find('.drop li').size() > 0) $sugestao.find('.drop').addClass("show").show();
        });

        //-------------------------------------------------------------------------//

        $(document).keydown(function(event){

            if ((event.which >= 37) && (event.which <= 40)) {

                var direcao;
                if((event.which == 37) || (event.which == 38)) direcao = 'prev';
                if((event.which == 39) || (event.which == 40)) direcao = 'next';

                if($sugestao.find('.drop').hasClass("show")) {

                    $active = $sugestao.find('.drop li.active');
                    switch(direcao){
                        case 'prev':
                            $next = $active.prev().length ? $active.prev() : false;
                            if(!$next) $sugestao.find("input[name='cliente-sugestao']").focus();
                        break;

                        case 'next':
                        default:
                            $next = $active.next().length ? $active.next() : $sugestao.find('.drop li:first');
                        break;
                    }

                    $active.removeClass('active');
                    if($next) $next.addClass('active').find('a').focus();

                    // Evitar scroll
                    event.preventDefault();
                }

            }
            
        });

        //-------------------------------------------------------------------------//

        // $(sugestao).on('click','.drop a', function(){
        $sugestao.on('click', '.drop a' ,function(){
            var valores = $(this).attr('rel').split(',');
            $sugestao.find("input[name='cliente-sugestao']").val(valores[1]);
            $sugestao.find(".drop").hide();

            //marcar o selecionado
            var sel = valores[0];
            $sugestao.removeClass('empty').find('input[name="cliente"]:checkbox').val(sel).trigger('click');

            return false;
        });

        //-------------------------------------------------------------------------//
        $sugestao.on('click', '.drop', function(event) {
            event.stopPropagation();
        });
        $sugestao.on('click', '.drop a', function(event) {
            event.stopPropagation();
        });
        //Sumir Drop
        $(document).click(function(event) {
            if(($(event.target) != $sugestao) && !$sugestao.find("input[name='cliente-sugestao']").hasClass("focusenter")) {
                 $sugestao.find(".drop").hide();
                 $sugestao.removeClass("show");
            }
        });
    }

    //------------------- Check Alterar -------------------//

    $('section#conteudo form#compras-alterar table.lista input[name="loja[]"]').change(function(){
        if($(this).is(':checked')) $(this).closest('tr').addClass('checked');
        else $(this).closest('tr').removeClass('checked');
    });

    // On change class
    if($('section#conteudo form#compras-alterar table.lista td.check section.checkbox.verify')[0]) {

        classFuncs = {add:$.fn.addClass,remove:$.fn.removeClass}
        $.fn.addClass = function() {
            classFuncs.add.apply(this,arguments);
            if ($(this).is('section.checkbox.verify')) $(this).closest('tbody').find('tr').not('.alocado').addClass('empty');
            return $(this);
        }
        $.fn.removeClass = function() {
            classFuncs.remove.apply(this,arguments);
            if ($(this).is('section.checkbox.verify')) $(this).closest('tbody').find('tr').not('.alocado').removeClass('empty');
            return $(this);
        }
    }

    //------------------- Alterar -------------------//

    $('section#conteudo form#compras-alterar input[name="tipo"]:radio').not('.disabled').change(function() {
        var $tipo = $(this);
        if($tipo.is(':checked')){

            var info = jQuery.parseJSON($tipo.attr('rel'));
            var setores = info.setores;

            $('section#conteudo form#compras-alterar section#setor-ingresso label.item').each(function() {
                var $setor = $(this);

                $('section#conteudo form#compras-alterar section#setor-ingresso').removeClass('empty');
                $setor.removeClass('checked').addClass('disabled');
                $setor.find('input[name="setor"]:radio').removeAttr('checked rel').addClass('disabled');

                if(setores.indexOf($setor.find('input[name="setor"]:radio').val()) != '-1') {
                    var setor = $setor.find('input[name="setor"]:radio').val();
                    var dias = info.dias[setor];

                    $setor.removeClass('disabled');
                    $setor.find('input[name="setor"]:radio').removeClass('disabled').attr('rel',dias);

                }
            });

            // Limpar os dias
            $('section#conteudo form#compras-alterar section#compra-dias label.item').each(function() {             
                $('section#conteudo form#compras-alterar section#compra-dias').removeClass('empty');
                var $dia = $(this);
                $dia.removeClass('checked').addClass('disabled');
                $dia.find('input[name="dia"]:radio').removeAttr('checked').addClass('disabled');
            });

            //Limpar os itens
            $('section#conteudo form#compras-alterar section#compras-itens').html('');
        }
    });

    $('section#conteudo form#compras-alterar input[name="setor"]:radio').change(function() {
        var $setor = $(this);

        if(!$setor.is('.disabled') && $setor.is(':checked')){

            // var info = jQuery.parseJSON($setor.attr('rel'));
            var dias = $setor.attr('rel');
            
            $('section#conteudo form#compras-alterar section#compra-dias label.item').each(function() {
                var $dia = $(this);

                $('section#conteudo form#compras-alterar section#compra-dias').removeClass('empty');
                $dia.removeClass('checked').addClass('disabled');
                $dia.find('input[name="dia"]:radio').removeAttr('checked').addClass('disabled');

                if(dias.indexOf($dia.find('input[name="dia"]:radio').val()) != '-1') {
                    $dia.removeClass('disabled');
                    $dia.find('input[name="dia"]:radio').removeClass('disabled');

                }
            });

            //Limpar os itens
            $('section#conteudo form#compras-alterar section#compras-itens').html('');
        }
    });

    $('section#conteudo form#compras-alterar section#compra-dias input[name="dia"]').change(function(){
        if($(this).is(':checked')) {
            var valores = $('section#conteudo form#compras-alterar').serialize();
            var site = $("#base-site").val();

            $.post(site + "include/compras-adicionar-itens.php", valores, function(resposta) {
                if(resposta.sucesso) {
                    $('section#conteudo form#compras-alterar section#compras-itens').html(resposta.itens).find('.radio').radio();
                }
            }, 'json');
        }

    });

    $('section#conteudo form#compras-alterar section#compras-itens').on("change", 'input[name="item"]', function(){
        /*if($(this).is(':checked')) $(this).closest('section.item-compra').addClass('checked');
        else $(this).closest('section.item-compra').removeClass('checked');*/
        $('section#conteudo form#compras-alterar section#compras-itens .item-compra').each(function(){
            $item = $(this);
            if($item.find('input[name="item"]').is(':checked')) $item.addClass('checked');
            else $item.removeClass('checked');
        });
    });

    //------------------- Alocacao -------------------//

    $('section#conteudo section#compras-alocacao .radio').radio();
    
    $('section#conteudo section#compras-alocacao section#alocacao-dias a.item.disabled').click(function(){ return false; });
    $('section#conteudo section#compras-alocacao section#setor-ingresso input[name="setor"]:radio').not('.disabled').change(function() {
        var $setor = $(this);
        if($setor.is(':checked')){

            var dias = jQuery.parseJSON($setor.attr('rel'));
            $('section#conteudo section#compras-alocacao section#alocacao-dias a.item').each(function() {
                var $dia = $(this);

                $dia.removeClass('checked').addClass('disabled').click(function(){ return false; }).attr('href','#');

                var rel = jQuery.parseJSON($dia.attr('rel'));
                if(dias.indexOf(rel.dia) != '-1') {

                    var href = rel.link;
                    href = href.replace('$1', $setor.val());

                    $dia.removeClass('disabled').unbind('click').attr('href', href);
                }
            });
        }
    });

    //-----------------------------------------------------------------//
    
    $('section#conteudo section#compras-alocacao section#lista-cliente div.box-clientes .row a.slide').click(function(){
        if($(this).hasClass('aberto')) $(this).closest('div.row').addClass('aberto');
        else $(this).closest('div.row').removeClass('aberto');
    });
    
    //-----------------------------------------------------------------//

    var timeoutinbusca, timeoutoutbusca;

    $("form.busca-alocacao").mouseover(function(){
        $form = $(this);
        timeoutinbusca = setTimeout(function(){
            if(!$form.hasClass("over")){ $form.addClass("over")};
        }, 400);
        clearTimeout(timeoutoutbusca);
    });

    $("form.busca-alocacao").mouseout(function(){
        $form = $(this);
        timeoutoutbusca = setTimeout(function(){
            if($form.hasClass("over") && !$form.find("input:text").is(":focus")){ $form.removeClass("over");}
        }, 400);
        clearTimeout(timeoutinbusca);       
    });

    /*$("form.busca-alocacao").find("input:text").focus(function(){ clearTimeout(timeoutinbusca); clearTimeout(timeoutoutbusca); $("#alocacao .busca-alocacao").addClass("over"); });
    $("form.busca-alocacao").find("input:text").blur(function(){ clearTimeout(timeoutinbusca); timeoutoutbusca = setTimeout('sleepoutbusca()', 1000); });*/

    //------------------- Forms -------------------//

    $('form input.input.money').maskMoney({symbol:'R$ ', thousands:'.', decimal:',', symbolStay: false, allowZero: true});
    $('form input.input.money.visible').maskMoney({symbol:'R$ ', thousands:'.', decimal:',', symbolStay: true, allowZero: true });

    //Criando os requires
    $('form#ingresso-compra-novo').validation({
        rules: {
            valor: { tipo: 'money' },
            numerada: { required: false },
            super: { required: false },
            estoque: { tipo: 'int' }
        }
    });

    //Criando os requires
    $('form#ingresso-venda-novo').validation({
        rules: {
            valor: { tipo: 'money' },
            estoque: { tipo: 'int' },
            exclusividade: { tipo: 'money' },
            numerada: { required: false },
            adicionaiscod: { required: false },
            adicionaisincluso: { required: false },
            adicionaisvalor: { required: false }

        }
    });

    //Criando os requires
    $('form#compras-novo').validation({
        rules: {
            valor: { tipo: 'money' },
            estoque: { tipo: 'int' },
            exclusividade: { tipo: 'money' },
            grupo: { tipo: 'int' }
        }
    });

    //Criando os requires
    $('form#compras-carrinho').validation({
        rules: {
            quantidade: { tipo: 'int' }
        }
    });
    
    //Criando os requires
    $('form#compras-adicionais').validation({
        rules: {
            quantidade: { tipo: 'int' },
            deadline: { tipo: 'data' },
            'cliente-sugestao': { required: false },
            valoritem: { required: false },
            valoradicional: { required: false },
            adicionaiscod: { required: false },
            exclusividade: { required: false },
            delivery: { required: false },
            comentarios: { required: false },
            comentariosinternos: { required: false },
            canal: { required: false },
            folia: { required: false },
            frisa: { required: false },
            retida: { required: false }
        }
    });

    $('form#compras-adicionais input[name="deadline"]:text').mask('99/99/9999');

    //Criando os requires
    $('form#compras-alterar').validation();
    // $('form#comentario').validation();

    if($('form#comentario textarea#item-comentario')[0]) {
        //$('form#comentario textarea#item-comentario').setCursorToTextEnd();

        var comentarioanterior;
        $('form#comentario textarea#item-comentario').focus(function(){
            var $comentario = $(this);

            if(($comentario.val() != '') && !$comentario.hasClass('changed')) {

                comentarioanterior = $comentario.val();

                d = new Date,
                dformat = [(d.getDate().padLeft(),
                d.getMonth()+1).padLeft(),
                d.getFullYear()].join('/') +' ' +
                [d.getHours().padLeft(),
                d.getMinutes().padLeft(),
                d.getSeconds().padLeft()].join(':');
                
                var conteudo = $comentario.val();
                var conteudo = conteudo + "\n-\n"+dformat+": ";

                $comentario.val(conteudo).addClass('changed datainsert').setCursorToTextEnd();
                $comentario.keydown(function(){ $comentario.removeClass('datainsert'); });
            }
        }).blur(function(){
            var $comentario = $(this);
            if($comentario.hasClass('datainsert')) {
                console.log(comentarioanterior);
                $comentario.val(comentarioanterior).removeClass('datainsert changed');
            }
        });
    }


    if($('form#comentario textarea#item-comentario-interno')[0]) {
        
        var comentariointernoanterior;
        $('form#comentario textarea#item-comentario-interno').focus(function(){
            var $comentario = $(this);

            if(($comentario.val() != '') && !$comentario.hasClass('changed')) {

                comentariointernoanterior = $comentario.val();

                d = new Date,
                dformat = [(d.getDate().padLeft(),
                d.getMonth()+1).padLeft(),
                d.getFullYear()].join('/') +' ' +
                [d.getHours().padLeft(),
                d.getMinutes().padLeft(),
                d.getSeconds().padLeft()].join(':');
                
                var conteudo = $comentario.val();
                var conteudo = conteudo + "\n-\n"+dformat+": ";

                $comentario.val(conteudo).addClass('changed datainsert').setCursorToTextEnd();
                $comentario.keydown(function(){ $comentario.removeClass('datainsert'); });
            }
        }).blur(function(){
            var $comentario = $(this);
            if($comentario.hasClass('datainsert')) {
                console.log(comentariointernoanterior);
                $comentario.val(comentariointernoanterior).removeClass('datainsert changed');
            }
        });
    }



     //Criando os requires
    $('form#cadastro-boleto').validation({
        rules: {
            quantidade: { tipo: 'int' },
            data: { tipo: 'date' }
        }
    });

    //-----------------------------------------------------------------------//

    //Limpar

    $('section#conteudo section#compras-alocacao section#lista-cliente form.busca-alocacao a.limpar').click(function(){
        $box = $form.closest('.box-clientes');
        $box.find('form.busca-alocacao input[name="q"]').val('');
        $box.find('ul .row.first').show();
        $(this).hide();
        
        return false;
    });

    // Busca
    $('section#conteudo section#compras-alocacao section#lista-cliente form.busca-alocacao').submit(function(){
        
        $form = $(this);
        $box = $form.closest('.box-clientes');

        var filtros = $form.serialize();

        var site = $("#base-site").val();
        $.post(site + "include/alocacao-busca.php", filtros, function(resposta){
            
            if(resposta.sucesso) {

                if(resposta.quantidade > 0) {
                    
                    //Nao exibimos os que nao estao no array
                    if(resposta.ingressos != null) {
                        
                        var ingressos = resposta.ingressos;

                        $box.find('ul .row.first').each(function(e){
                            var compra = parseInt($(this).data('compra'));
                            if(ingressos.indexOf(compra) > -1) $(this).show();
                            else $(this).hide();
                        });
                    }

                } else {

                    // Ocultamos todos
                    $box.find('ul .row.first').hide();

                }

                $form.find('a.limpar').show();

            }

        }, 'json');
        
        return false;
    });

    //-----------------------------------------------------------------------//

    //Marcar alocados
    if($("section#conteudo section#compras-alocacao section#lista-alocacao")[0]) marcarlugares();

    //Inserir função de drag
    if($("section#conteudo section#compras-alocacao section#lista-cliente span.drag")[0]){

        var retornar = true;
        
        var $alocacao = $('section#conteudo section#compras-alocacao section#lista-alocacao');
        $drag = null,
        $marcado = null;

        $('section#conteudo section#compras-alocacao section#lista-cliente span.drag').draggable({
            opacity: 0.9,
            helper: 'clone',
            cursor: 'move',
            snap: 'li.item div.lugar',
            snapMode: 'inner',
            snapTolerance: 55,
            
            start: function() {

                $('body').addClass('dragging');

                // Drag
                $drag = $(this);
                $drag.closest('.row').addClass('on-drag');


                $alocacao.find("div.lugar").hover(function(){

                    if($('body').hasClass('dragging')) {

                        var $lugar = $(this);
                        if(!$lugar.hasClass('carregado')) $lugar.addClass('simulado');
                        else $('body').addClass('dragging-error');

                    }

                }, function(){
                    $alocacao.find("div.lugar.simulado").removeClass("simulado");
                    $('body').removeClass('dragging-error')
                });

            },

            //-----------------------------------------------------------------------//
            
            drag: function() {

            },
            
            //-----------------------------------------------------------------------//
            
            stop: function() {

                $('body').removeClass('dragging');


                // Hover
                $alocacao.find("div.lugar").off('hover');


                // Encontramos um marcado
                if($alocacao.find("div.lugar.simulado").not('.nao-permitir')[0]) {

                    $marcado = $alocacao.find("div.lugar.simulado:last").not('.nao-permitir');

                    marcado_compra = $drag.data('compra');
                    marcado_lugar = $marcado.data('lugar');

                    var site = $("#base-site").val();

                    //-----------------------------------------------------------------------//
                    
                    if(confirm('Tem certeza que deseja alocar este cliente?')) {
                        
                        $.post(site + "include/alocacao.php", {
                            acao: 'marcar',
                            compra: marcado_compra,
                            lugar: marcado_lugar

                        }).done(function(r) {

                            var resposta = jQuery.parseJSON(r);

                            if(resposta.sucesso) {
                                
                                // Limpar dados antigos
                                if($drag.closest('ul.drop').length) {
                                    if($drag.closest('ul.drop').find('.row').not('.alocado').size() == 1){
                                        $drag.closest('ul.drop').closest('li').find('.row').addClass('alocado');
                                    }
                                }

                                $drag.closest('.row').addClass('alocado');
                                $drag.remove();
                                

                                //-----------------------------------------------------------------------//

                                // Marcar os lugares
                                marcarlugares()

                                retornar = false;

                                // Anular o drag

                            } else {
                                retornar = true;
                            }

                            $alocacao.find("div.lugar").removeClass("simulado");

                        }).fail(function() {
                            retornar = true;
                            $alocacao.find("div.lugar").removeClass("simulado");
                        });

                        retornar = false;
                    }

                    //-----------------------------------------------------------------------//

                    // $drag = $marcado = marcado_cod = marcado_tipo = marcado_horario = marcado_plataforma = marcado_titulo = null;

                }

                if(retornar) {
                    $alocacao.find("div.lugar").removeClass("simulado nao-permitir");
                }
                
                // Drag
                $drag.closest('.row').removeClass('on-drag');

            }
        });
    }
    
    //-----------------------------------------------------------------------//

    $('section#conteudo section.secao table.lista tbody tr td .selectbox.alterar-pagamento').selectbox();

    //-----------------------------------------------------------------------//

    $('section#conteudo section.secao table.lista tbody tr td.detalhes-voucher, section#conteudo section#compras-alocacao section#lista-cliente .box-clientes td.cod.detalhes-voucher').hover(function(){
        var $detalhes = $(this);
        
        if(!$detalhes.hasClass('carregado')) {
            var cod = $detalhes.data('cod');
            var cancelado = $detalhes.data('cancelado');
            var site = $('#base-site').val();

            $detalhes.addClass('loading');

            $.post(site+'include/voucher-detalhes.php', {
                cod: cod,
                cancelado: cancelado
            } , function(resposta) {
                if(resposta.sucesso) {

                    $detalhes.removeClass('loading').addClass('carregado')
                    .find('.detalhes').html(resposta.dados);
                    
                }
            },'json');
        }

    });

    //-----------------------------------------------------------------------//

    /*$('section#conteudo section.secao#compra-dados .informacoes-compra a.print').click(function(){
        window.print();
        return false;
    });*/
    
    //-----------------------------------------------------------------------//

    if($('section#conteudo form#compras-novo.modificar')[0]) {

        $modificar = $('section#conteudo form#compras-novo.modificar');
        $modificar.find('header.titulo a.adicionar').click(function(){
            if($modificar.find('section.hidden').css('display') == 'none') {
                $modificar.find('section.hidden').slideDown('fast');
                $modificar.find('header.titulo a.adicionar').html('-');
            } else {
                $modificar.find('section.hidden').slideUp('fast');
                $modificar.find('header.titulo a.adicionar').html('+');
            }
            return false;
        });

        compras_adicionais_total();
        
    }

    //-----------------------------------------------------------------------//

    if($('section#conteudo.camisas')[0]) {

        $('section#conteudo.camisas section#camisas-adicionar.secao form').validation({
            rules: {
                quantidade: { tipo: 'int' }
            }
        });

        $('section#conteudo.camisas section#camisas-adicionar.secao form').submit(function(){
            var $adicionar = $(this);
            var valores = $adicionar.serialize();
            var site = $("#base-site").val();

            if(!$adicionar.hasClass('return-false')) {

                $.post(site + "include/camisas-adicionar.php", valores, function(resposta) {
                    if(resposta.sucesso) {
                        $('section#conteudo section#camisas-lista ul').html(resposta.itens);
                        camisas_total(0);
                    }
                }, 'json');

                $adicionar.find('input#camisa-quantidade').val('1');
                $adicionar.find('section#camisa-tamanho.selectbox a.arrow strong').html('Tamanho:');
                $adicionar.find('section#camisa-tamanho.selectbox label.checked').removeClass('checked').find('input:checked').removeAttr('checked');
                
            }


            return false;
        });

        $('section#conteudo.camisas section#camisas-lista').on('click', 'a.remover', function(){
            var $adicionar = $(this);
            var valores = $adicionar.attr('href');

            $.getJSON(valores, function(resposta) {
                if(resposta.sucesso) {
                    $('section#conteudo section#camisas-lista ul').html(resposta.itens);
                    camisas_total(0);
                }
            });


            return false;
        });
        
        $('section#conteudo.camisas section#camisas-adicionar.secao form input[name="quantidade"').blur(function(){
            var valor = parseInt($(this).val());
            if(!camisas_total(valor)) {
                alert('O limite de camisas foi atingido');
                $(this).val('1');
            }
        });

        //-----------------------------------------------------------------------//

        function camisas_total(adicionar) {

            var $camisas = $('section#conteudo.camisas');
            
            var adicionar = parseInt(adicionar);
            var limite = parseInt($camisas.find('input[name="total-ingressos"]').val());
            var total = 0;

            $camisas.find('input[name="quantidade-item"]').each(function(){
                total += parseInt($(this).val());
            });

            if(adicionar > 0) {

                total += adicionar;
                return (parseInt(total) <= limite) ? true : false;

            } else if(parseInt(total) <= limite) {
                $camisas.find('header.titulo .tamanhos span').html(total);

                if(parseInt(total) > 0) $camisas.find('footer.controle input.submit').show();
                else $camisas.find('footer.controle input.submit').hide();
            }

        }

    }


    //-----------------------------------------------------------------------//

    if($('section#conteudo.multiplo')[0]) {

        multiplo_total(0);

        $('section#conteudo.multiplo section#multiplo-adicionar.secao form').validation({
            rules: {
                valor: { tipo: 'money' }
            }
        });

        $('section#conteudo.multiplo section#multiplo-adicionar.secao form').submit(function(){
            var $adicionar = $(this);
            var valores = $adicionar.serialize();
            var site = $("#base-site").val();

            if(!$adicionar.hasClass('return-false')) {

                $.post(site + "include/multiplo-adicionar.php", valores, function(resposta) {
                    if(resposta.sucesso) {
                        $('section#conteudo section#multiplo-lista ul').html(resposta.itens);
                        multiplo_total(0);
                    }
                }, 'json');

                $adicionar.find('input#multiplo-valor').val('0,00');
                $adicionar.find('section#multiplo-forma.selectbox a.arrow strong').html('Forma de pagamento:');
                $adicionar.find('section#multiplo-forma.selectbox label.checked').removeClass('checked').find('input:checked').removeAttr('checked');
                
            }


            return false;
        });

        $('section#conteudo.multiplo section#multiplo-lista').on('click', 'a.remover', function(){
            var $adicionar = $(this);
            var valores = $adicionar.attr('href');

            $.getJSON(valores, function(resposta) {
                if(resposta.sucesso) {
                    $('section#conteudo section#multiplo-lista ul').html(resposta.itens);
                    multiplo_total(0);
                }
            });


            return false;
        });
        
        $('section#conteudo.multiplo section#multiplo-adicionar.secao form input[name="valor"').blur(function(){
            var valor = $(this).val();
            valor = valor.replace('.', '');
            valor = parseFloat(valor.replace(',', '.'));

            if(valor > 0) {
                if(!multiplo_total(valor)) {
                    alert('Para que o valor total não seja ultrapassado esta forma de pagamento terá seu valor ajustado');
                    var $multiplo = $('section#conteudo.multiplo');
                    var limite = parseFloat($multiplo.find('input[name="total-ingressos"]').val());
                    var total = 0;
                    
                    $multiplo.find('input[name^="multiplo"]').each(function(){ total += parseFloat($(this).val()); });

                    var valor = parseFloat(limite - total);
                    valor = valor.toFixed(2);
                    valor = valor.replace('.', ',');

                    $(this).val(valor);
                }                
            }
        });

        //-----------------------------------------------------------------------//

        function multiplo_total(adicionar) {


            var $multiplo = $('section#conteudo.multiplo');
            
            var adicionar = parseFloat(adicionar);
            var limite = parseFloat($multiplo.find('input[name="total-ingressos"]').val());
            var total = 0;
            var exibir = false;

            $multiplo.find('input[name^="multiplo"]').each(function(){
                total += parseFloat($(this).val());
                if($(this).hasClass('sessao')) exibir = true;
            });

            if(adicionar > 0) {

                total += adicionar;
                return (parseFloat(total) <= limite) ? true : false;

            } else if(parseFloat(total) <= limite) {
                totalf = total.toFixed(2);
                totalf = totalf.replace('.', ',');
                $multiplo.find('header.titulo .valor-total span').html(totalf);

                if(parseFloat(total) == parseFloat(limite)) {
                    if(exibir) $multiplo.find('footer.controle input.submit').show();
                    $multiplo.find('section#multiplo-adicionar.secao').hide();
                } else {
                    $multiplo.find('footer.controle input.submit').hide();
                    $multiplo.find('section#multiplo-adicionar.secao').show();
                }
            }

        }

    }

    //-----------------------------------------------------------------------//
    
    $('form#expedicao.encaminhado').submit(function(){

        var $form = $(this);
        var $nome = $form.find('input[name="nome"]');
        var $atendente = $form.find('input[name="atendente"]');
        var nome = $nome.val();
        var atendente = $atendente.val();

        if((nome.length > 0) && (atendente.length == 0)) $atendente.attr('disabled', true).removeClass('empty invalid');
        if((nome.length == 0) && (atendente.length > 0)) $nome.attr('disabled', true).removeClass('empty invalid');

    });

    $('form#expedicao').validation({
        rules: {
            data: { tipo: 'data' },
            hora: { tipo: 'hora' }
        }
    });  


    //-----------------------------------------------------------------------//

    //Sumir Drop
    if($('section#select-relatorio')[0]){
        $selectrelatorio = $('section#select-relatorio');
        $selectrelatorio.on('click', '.arrow', function(){            
            if($selectrelatorio.find(".drop").css("display") == 'none')
                $selectrelatorio.addClass("ativo").find(".drop").slideDown("fast");
            else
                $selectrelatorio.removeClass("ativo").find(".drop").slideUp("fast");            
            return false;
        });

        $(document).click(function(event) {
            if($(event.target) != $selectrelatorio) {
                 $selectrelatorio.find(".drop").slideUp('fast');
                 $selectrelatorio.removeClass("ativo");
            }
        });
    }

    //-----------------------------------------------------------------------//

    var dropleft = 0;
    var navtimer;
    $('header#topo nav ul li.drop > a').click(function(){ return false; });
    $('header#topo nav ul li.drop *').mouseenter(function() { $(this).closest('li.drop').addClass('over'); });
    $('header#topo nav ul li.drop').mouseenter(function() {
        $(this).addClass('over');
    }).mouseleave(function() {
        var $lidrop = $(this);
        navtimer = setTimeout(function(){ $lidrop.removeClass('over'); }, 150);        
    });

    $('header#topo nav ul > li').each(function(){
        var $drop = $(this);
        
        if($drop.hasClass('drop right')) {

            var dropnav = $('header#topo nav ul').width();
            
            var dropw = 0;
            $drop.find('ul li').each(function () {
                dropw += $(this).width();
            });

            dropw = dropnav - dropw;

            if(dropw > dropleft) {
                $drop.removeClass('right').find('ul').css('right', 'auto');
                $drop.find('ul li a.first-child').removeClass('first-child');
                $drop.find('ul li:first a').addClass('first-child');
            } else {

                var drophiddenwidth = $drop.width();
                var drophiddenright = dropnav - ($drop.position().left + drophiddenwidth);
                var drophiddenulwidth = 0;
                $drop.find('ul > li').each(function() { drophiddenulwidth += $(this).width(); });

                if((drophiddenright+drophiddenwidth) > drophiddenulwidth) {
                    var drophiddenulright = (drophiddenright + (drophiddenwidth / 2)) - (drophiddenulwidth / 2);
                    $drop.find('ul').css('right', drophiddenulright);
                }
                
            }


            
        }

        dropleft += $drop.width();

        //Mudar posicionamento de drop hidden left
        if($drop.hasClass('drop') && !$drop.hasClass('right')) {
            var drophiddenleft = $drop.position().left;
            var drophiddenwidth = $drop.width();
            var drophiddenulwidth = 0;
            $drop.find('ul > li').each(function() { drophiddenulwidth += $(this).width(); });

            if((drophiddenleft+drophiddenwidth) > drophiddenulwidth) {
                var drophiddenulleft = (drophiddenleft + (drophiddenwidth / 2)) - (drophiddenulwidth / 2);
                $drop.find('ul').css('left', drophiddenulleft);
            }
            
        }

    });

    $('header#topo nav ul li.drop ul').addClass('hidden');

});


function marcarlugares() {

    var site = $("#base-site").val();
    var filtros = $('form#alocacao-marcacao').serialize();
    var $alocacao = $('section#conteudo section#compras-alocacao section#lista-alocacao');

    $alocacao.find('div.lugar').removeClass('carregado liberado enviado').removeData('compra');

    $.post(site + "include/alocacao-marcacao.php", filtros, function(resposta){
        if(resposta.sucesso) {

            //Inserir os marcados dentro da tabela
            if(resposta.lugares != null) {

                $.each(resposta.lugares, function(i, item) {
                
                    //Encontrando o lugar
                    $item = $alocacao.find("div.lugar[data-lugar='"+item.ingresso+"']");

                    var classe = item.enviado ? 'enviado' : 'liberado';

                    var html = item.compra;
                    if(item.exibir_id) html += '/'+item.compra_id;
                    if(item.comentario) html += '<a href="'+site+'ingressos/comentario/'+item.comentario+'/" class="comentario fancybox fancybox.iframe width600"></a>';
                    if(item.outros) html += '<strong class="outros"></strong>';
                    html += '<span>'+item.cliente_classe+'</span> <div class="tooltip"><h3>'+item.nome+'</h3><h4>'+item.canal+'</h4><p>R$ '+item.valor+'</p></div>';

                    if(!item.pago) classe += ' nao-pago';

                    $item.addClass('carregado ' + classe).html(html).data('compra',item.cod);

                    $item.find("a.fancybox.width600").fancybox({
                        //padding: 0,
                        width : 600,
                        helpers : { title : null }
                    });
                    
                });

                //-----------------------------------------------------------------------//
                
                var retornar = true;
                
                $drag = null,
                $marcado = null;

                $alocacao.find('div.lugar.carregado.liberado').draggable({
                    opacity: 0.7,
                    helper: 'clone',
                    cursor: 'move',
                    snap: 'li.item div.lugar',
                    snapMode: 'inner',
                    snapTolerance: 40,
                    
                    start: function() {


                        $('body').addClass('dragging desalocar');

                        // Drag
                        $drag = $(this);

                        // $drag.closest('.row').addClass('on-drag');
                        $drag.addClass('on-drag');


                        $alocacao.find("div.lugar").hover(function(){

                            if($('body').hasClass('dragging')) {

                                var $lugar = $(this);
                                if(!$lugar.hasClass('carregado')) $lugar.addClass('simulado');
                                else $('body').addClass('dragging-error');

                            }

                        }, function(){
                            $alocacao.find("div.lugar.simulado").removeClass("simulado");
                            $('body').removeClass('dragging-error');
                        });

                        //-----------------------------------------------------------------------//
                        // Desalocar

                        $('#ingressos-desalocar').hover(function(){

                            if($('body').hasClass('dragging desalocar')) {
                                $('body').addClass('desalocar-over');
                                $('#ingressos-desalocar').addClass('desalocar');
                            }

                        }, function(){
                            $('#ingressos-desalocar').removeClass('desalocar');
                            $('body').removeClass('desalocar-over');
                        });

                    },

                    //-----------------------------------------------------------------------//
                    
                    drag: function() {

                    },
                    
                    //-----------------------------------------------------------------------//
                    
                    stop: function() {

                        $('body').removeClass('dragging');


                        // Hover
                        $alocacao.find("div.lugar").off('hover');


                        // Encontramos um marcado
                        if($alocacao.find("div.lugar.simulado").not('.nao-permitir')[0]) {

                            $marcado = $alocacao.find("div.lugar.simulado").not('.nao-permitir');

                            marcado_compra = $drag.data('compra');
                            marcado_lugar = $marcado.data('lugar');

                            var site = $("#base-site").val();

                            //-----------------------------------------------------------------------//
                            
                            if(confirm('Tem certeza que deseja realocar este cliente?')) {
                                
                                $.post(site + "include/alocacao.php", {
                                    acao: 'alterar',
                                    compra: marcado_compra,
                                    lugar: marcado_lugar

                                }).done(function(r) {

                                    var resposta = jQuery.parseJSON(r);

                                    if(resposta.sucesso) {
                                        
                                        // Limpar dados antigos
                                        $drag.html('');

                                        //-----------------------------------------------------------------------//

                                        // Marcar os lugares
                                        marcarlugares();

                                        retornar = false;

                                        // Anular o drag

                                    } else {
                                        retornar = true;
                                    }

                                }).fail(function() {
                                    retornar = true;
                                });

                                retornar = false;
                            }

                        }

                        //-----------------------------------------------------------------------//
                        // Desalocar

                        if($('body.desalocar section#ingressos-desalocar.desalocar')[0]) {

                            marcado_compra = $drag.data('compra');
                            marcado_lugar = $drag.data('lugar');
                            
                            var site = $("#base-site").val();

                            //-----------------------------------------------------------------------//
                            
                            if(confirm('Tem certeza que deseja desalocar este cliente?')) {
                                
                                $.post(site + "include/alocacao.php", {
                                    acao: 'desalocar',
                                    compra: marcado_compra,
                                    lugar: marcado_lugar

                                }).done(function(r) {

                                    var resposta = jQuery.parseJSON(r);

                                    if(resposta.sucesso) {
                                        
                                        retornar = false;
                                        location.reload();                                        

                                    } else {
                                        retornar = true;
                                    }

                                }).fail(function() {
                                    retornar = true;
                                });

                                retornar = false;
                            }

                        }
                        
                        //-----------------------------------------------------------------------//

                        if(retornar) {
                            $alocacao.find("div.lugar").removeClass("simulado nao-permitir");
                        }

                        
                        // Drag
                        // $drag.closest('.row').removeClass('on-drag');
                        $drag.removeClass('on-drag');
                        $('body').removeClass('desalocar');

                    }
                });

            }
        }

    }, 'json');

}

//-----------------------------------------------------------------//

function scrolldown(target){

	if(target.indexOf("#/") > 0){

		var query = target.split("#/");
		var acao = query[1];
		var id = "#" + acao;

		//$.scrollTo($(id),300,{axis:"y"});
		$(id).ScrollTo(300);

	}	
}

//-----------------------------------------------------------------------//

$.fn.setslide = function(settings){
    var config = {
        'limit' 	: 1,
        'timer' 	: 0,
        'restart'	: false
    };
    if (settings){$.extend(config, settings);}

    return this.each(function(n){

    	var $setslide = $(this);
    	var limit = config.limit;
    	var timer = config.timer;
    	var restart = config.restart;

    	if($setslide.attr("id") != undefined) var id = $setslide.attr("id");
    	else { var id = "setslide-"+n; $setslide.attr("id", id); }

    	var obj = "#"+id;

        //Prev Next
        var count = 0;
        $setslide.find(".list .item").each(function() {
        	count ++;
        	$(this).attr("id", id + "-pg-" + count);
        });

        if($setslide.find(".ctrls a")[0]){
        	//Thumbs
        	var ctrls = 0;
        	$setslide.find(".ctrls a").each(function() {
        		ctrls ++;
        		$(this).attr("href", "#"+ id + "-pg-" + ctrls);
        		$(this).attr("id", id + "-pg-" + ctrls + "-ctrl");			
        	});
        }
        
        var total = count - (limit - 1);
        
        $setslide.find(".prev").attr("href", "#").addClass("disabled");
        $setslide.find(".next").attr("href", "#").addClass("disabled");
        
        var prev = "#";
        var next = "#";
        
        //Se tivermais que 3 inicia a função
        if(total > 1) {

        //Banner slideshow
        if(timer > 0) var slidestart = setInterval(function(){ $setslide.find(".next").trigger("click"); },timer);
        
        $setslide.find(".next").attr("href", obj + "-pg-2").removeClass("disabled");
        if(restart) $setslide.find(".prev").attr("href", obj + "-pg-" + total).removeClass("disabled");
        
        	$setslide.find("a.ctrl").click(function(){
        		if($(this).hasClass("disabled")) {
        			return false;
        		} else {
        		
        			//Banner slideshow
        			if(timer > 0) {
        				clearInterval(slidestart);
        				slidestart = setInterval(function(){ $setslide.find(".next").trigger("click"); },timer);
        			}

        			var alvo = $(this).attr("href");
        			var id = parseInt(alvo.replace(obj + "-pg-",""));
        			
        			if(alvo != "#"){
        				$setslide.find(".overflow").scrollTo($(alvo),300,{axis:"x"});
        				
        				if (parseInt(id-1) > 0) {  
        					prev = obj + "-pg-" + (id-1);
        					$setslide.find('.prev').removeClass('disabled');
        				} else {
        					if(restart) {
        						prev = obj + "-pg-" + total;
        						$setslide.find('.prev').removeClass('disabled');	
        					} else {
        						prev = "#";
        						$setslide.find('.prev').addClass('disabled');
        					}
        					
        				}
        				
        				if (parseInt(id+1) <= parseInt(total) ) {  
        					next = obj + "-pg-" + (id+1);
        					$setslide.find('.next').removeClass('disabled');
        					
        				} else { 
        					if(restart) {
	        					next = obj + "-pg-1";
	        					$setslide.find('.next').removeClass('disabled');
	        				} else {
	        					next = "#";
	        					$setslide.find('.next').addClass('disabled');
	        				}
        				}
        				
        			}
        			
        			$setslide.find(".prev").attr("href", prev);
        			$setslide.find(".next").attr("href", next);
        		
        		}
        		return false;
        		
        	});

        	if($setslide.find(".ctrls a")[0]){

        		$setslide.find(".ctrls a:first").addClass("ativo");

        		//------------------------------------------------------//
        		
        		$setslide.find(".ctrls a").click(function(){
        			var alvo = $(this).attr("href");
        			var id = parseInt(alvo.replace(obj + "-pg-",""));
        			
        			if(alvo != "#"){
        				
        				//Banner slideshow
        				if(timer > 0) {
        					clearInterval(slidestart);
        					slidestart = setInterval(function(){ $setslide.find(".next").trigger("click"); },timer);
        				}

        				$setslide.find(".overflow").scrollTo($(alvo),300,{axis:"x"});
        				
        				if (parseInt(id+1) <= parseInt(total) ) { 
        					next = obj + "-pg-" + (id+1);
        				} else { 
        					next = obj + "-pg-" + 1;
        				}

        				$setslide.find(".next").attr("href", next);
        				$setslide.find(".ctrls a.ativo").removeClass("ativo");
        				$(this).addClass("ativo");
        							
        			}
        			
        			return false;
        			
        		});
        		
        		//------------------------------------------------------//
        	}
        
        } else {
        	$setslide.find("a.disabled").click(function(){
        		return false;
        	});
        }
    });
};

//-----------------------------------------------------------------------//

$.fn.selectbox = function(){
    return this.each(function(){
        var $selectbox = $(this);

        /*Drop Down*/
        $selectbox.on('click', '.arrow', function(){
            
            if($selectbox.find(".drop").css("display") == 'none')
                if($selectbox.hasClass('fade')) $selectbox.addClass("ativo").find(".drop").fadeIn("fast");
                else $selectbox.addClass("ativo").find(".drop").slideDown("fast");
            else
                if($selectbox.hasClass('fade')) $selectbox.removeClass("ativo").find(".drop").fadeOut("fast");
                else $selectbox.removeClass("ativo").find(".drop").slideUp("fast");
            
            return false;
        });
        

        //if(!$selectbox.hasClass('menu-impressao')) {
            //Sumir Drop
            $(document).click(function(event) {
                if(($(event.target) != $selectbox) && (!$(event.target).hasClass('tid'))) {
                    if($selectbox.hasClass('fade')) $selectbox.find(".drop").fadeOut('fast');
                    else $selectbox.find(".drop").slideUp('fast');
                    $selectbox.removeClass("ativo");
                }
            });
            
        //}
        
        $selectbox.on('change', 'input[type="radio"]', function(){

            $selectbox.find("label.item.checked").removeClass("checked");
            
            $selectbox.find("label.item").each(function(){
                if($(this).find("input[type='radio']").attr("checked")) $(this).addClass("checked");
            });
            
            var selected = false;
            $selectbox.find("input[type='radio']").each(function(){
                if($(this).is(":checked")) selected = true; 
            });
            
            if(selected == true) {
                var value = $selectbox.find("input[type='radio']:checked").attr("alt");
                $selectbox.find("a.arrow strong").html(value);
            } else {
                $selectbox.find("a.arrow strong").html("");
            }
        });
    });
};


//-----------------------------------------------------------------------//

$.fn.radio = function(){
    return this.each(function(){
        var $radio = $(this);
        $radio.on('change', 'input[type="radio"]', function(){
            if($(this).hasClass('disabled')) {
                $(this).removeAttr('checked');
            } else {
                $radio.find("label.item.checked").removeClass("checked");
                $radio.find("label.item").each(function(){
                    if($(this).find("input[type='radio']").is(":checked")) $(this).addClass("checked");
                });
            }
        });
    });
};

//-----------------------------------------------------------------------//

$.fn.checkbox = function(){
    return this.each(function(){
        var $checkbox = $(this);
        $checkbox.on('change', 'input[type="checkbox"]', function(){
            if ($(this).is(":checked")) $(this).closest('label.item').addClass("checked");
            else $(this).closest('label.item').removeClass("checked");            
        });
    });
};

//-----------------------------------------------------------------------//

$.fn.validation = function(settings){
    var config = {};
    if (settings){$.extend(config, settings);}

    return this.each(function(n){

        var $validation = $(this);

        //-----------------------------------------------------------------------//

        $validation.submit(function(e){

            $validation.addClass('return-false');

            $validation.find('.selectbox').each(function(){
                if($(this).hasClass('disabled')) $(this).find('input').attr('disabled', true).removeClass('required');
                else $(this).find('input').attr('disabled', false);
            });

            //Criando os requires
            $validation.find('input, textarea').not(':disabled, [type="submit"], [type="hidden"]').each(function(){ $(this).addClass('required'); });
            
            // Regras
            for(key in config.rules){
                if(config.rules[key].tipo !== undefined) $validation.find('input[name="'+key+'"],input[name^="'+key+'["],textarea[name="'+key+'"],textarea[name^="'+key+'["]').data('tipo', config.rules[key].tipo);
                if((config.rules[key].required !== undefined) && (config.rules[key].required === false)) $validation.find('input[name="'+key+'"],input[name^="'+key+'["],textarea[name="'+key+'"],textarea[name^="'+key+'["]').removeClass('required');
            }
            
            //-----------------------------------------------------------------------//

            var retorno = true;

            $validation.find('input.required').each(function(){
                
                switch($(this).attr('type')){
                    case 'text':

                        var valor = $(this).val();

                        if($(this).data('tipo') !== undefined) {

                            var regexp = new Array();
                            var tipo = $(this).data('tipo');

                            // Data
                            regexp['data'] = /(^\d{1,2}\/\d{1,2}\/\d{4}$)|(^\d{1,2}\/\d{1,2}$)/;
                            regexp['hora'] = /^([0-1][0-9]|[2][0-3]):[0-5][0-9]$/;
                            // regexp['email'] = /^[^0-9][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[.][a-zA-Z]{2,4}$/;
                            regexp['email'] = /^[a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[.][a-zA-Z]{2,4}$/;
                            regexp['cpf'] = /^[\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2}$/;
                            regexp['cnpj'] = /^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$/;
                            regexp['cpfcnpj'] = /(^\d{3}\.\d{3}\.\d{3}\-\d{2}$)|(^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$)/;
                            regexp['money'] = /^([0-9]{1,3}.){1,}[0-9]{2}$/;
                            regexp['cep']= /^[0-9]{5}-[0-9]{3}$/;
                            regexp['tel']= /^\(\d{2}\)\ \d{4,5}-\d{4}$/;
                            regexp['int']= /^[0-9]+$/;
                            
                            if(!regexp[tipo].test(valor)) {
                                retorno = false;
                                $(this).addClass('invalid');
                            } else {
                                $(this).removeClass('invalid');
                            }
                        }

                        if(!(valor.length > 0)){
                            retorno = false;
                            $(this).addClass('empty');
                        } else {
                            $(this).removeClass('empty');
                        }

                    break;
                    case 'radio':
                        
                        if($(this).data('validate') != true) {
                            var radioname = $(this).attr('name');
                            $validation.find('input[name="'+radioname+'"]').data('validate', true);
                            if($validation.find('input[name="'+radioname+'"]:checked').size() == 0){
                                retorno = false;
                                $(this).closest('.radio, .selectbox').addClass('empty');
                            } else {
                                $(this).closest('.radio, .selectbox').removeClass('empty');
                            }
                        }

                    break;
                    case 'checkbox':

                        if($(this).data('validate') != true) {
                            var checkboxname = $(this).attr('name');
                            $validation.find('input[name="'+checkboxname+'"]').data('validate', true);
                            if($validation.find('input[name="'+checkboxname+'"]:checked').size() == 0){
                                retorno = false;
                                $(this).closest('.checkbox').addClass('empty');
                            } else {
                                $(this).closest('.checkbox').removeClass('empty');
                            }
                        }

                    break;
                }
            });

            $validation.find('textarea.required').each(function(){                
                var valor = $(this).val();
                if(!(valor.length > 0)){
                    retorno = false;
                    $(this).addClass('empty');
                } else {
                    $(this).removeClass('empty');
                }
            });
            
            //-----------------------------------------------------------------------//
            
            // Retirar o informativo que o campo ja foi validado
            $validation.find('input[type="radio"].required').removeData('validate');
            $validation.find('input[type="checkbox"].required').removeData('validate');
            
            if(retorno) $validation.removeClass('return-false');

            return retorno;
        });

    });
};

//-----------------------------------------------------------------------//

$.fn.radioSel = function(valueToSel){

    if(arguments.length>0){
        return this.each(function(){ // itera sobre cada elemento encontrado
            if($(this).val()==valueToSel) this.click();
        })        
    }else{
        valorSelecionado = false;
        this.each(function(){ // itera sobre cada elemento encontrado
            if(this.checked){
                valorSelecionado = $(this).val();
                return valorSelecionado;
            }
        });
        return valorSelecionado;
    }
};

//-----------------------------------------------------------------//

$.fn.cursorpos = function(){
    var input = this;
    // Internet Explorer Caret Position (TextArea)
    if (document.selection && document.selection.createRange) {
        var range = document.selection.createRange();
        var bookmark = range.getBookmark();
        var posicao = bookmark.charCodeAt(2) - 2;
    } else {
        // Firefox Caret Position (TextArea)
        if (input.setSelectionRange)
            var posicao = input.selectionStart;
    }

    return posicao;
};

//-----------------------------------------------------------------//

$.fn.setCursorToTextEnd = function() {
    var initialVal = this.val();
    this.focus().val("").val(initialVal);    
};

//-----------------------------------------------------------------//

Number.prototype.padLeft = function(base,chr){
    var  len = (String(base || 10).length - String(this).length)+1;
    return len > 0? new Array(len).join(chr || '0')+this : this;
}

/*//-----------------------------------------------------------------// */
function getCreditCardLabel(cardNumber){

    var site = $("#base-site").val();

    // $.ajax({
    //   url : site + 'verifycard.php',
    //   method : 'POST',
    //   data : { card : cardNumber },
    //   success : function(html){
    var bandeira='';
    switch(true){
        case (/^(636368|438935|504175|451416|636297)/).test(cardNumber) :
            bandeira = 'elo';  
        break;
     
        case (/^(606282)/).test(cardNumber) :
        bandeira = 'hipercard';    
        break;
     
        case (/^(5067|4576|4011)/).test(cardNumber) :
        bandeira = 'elo';  
        break;
     
        case (/^(3841)/).test(cardNumber) :
        bandeira = 'hipercard';    
        break;
     
        case (/^(6011)/).test(cardNumber) :
        bandeira = 'discover'; 
        break;
     
        case (/^(622)/).test(cardNumber) :
        bandeira = 'discover'; 
        break;
     
        case (/^(301|305)/).test(cardNumber) :
        bandeira = 'diners';   
        break;
     
        case (/^(34|37)/).test(cardNumber) :
        bandeira = 'amex'; 
        break;
     
        case (/^(36,38)/).test(cardNumber) :
        bandeira = 'diners';   
        break;
     
        case (/^(64,65)/).test(cardNumber) :
        bandeira = 'discover'; 
        break;
     
        case (/^(50)/).test(cardNumber) :
        bandeira = 'aura'; 
        break;
     
        case (/^(35)/).test(cardNumber) :
        bandeira = 'jcb';  
        break;
     
        case (/^(60)/).test(cardNumber) :
        bandeira = 'hipercard';    
        break;
     
        case (/^(4)/).test(cardNumber) :
        bandeira = 'visa'; 
        break;
     
        case (/^(5)/).test(cardNumber) :
        bandeira = 'mastercard';   
        break;
        }
        // if(html!=''){

            $('#ncard').html('Número do cartão');
            var verifytotal = $('#total').val();
            var options = '';

            // if( (bandeira=='mastercard') || (bandeira=='diners') || (bandeira=='aura') ){
                
            //     for(i=2;i<=6;i++){
            //         options = options + '<option value="'+i+'">'+i+'x de R$'+ parseFloat( (verifytotal / i) ).toFixed(2).replace('.',',') +'</option>';
            //     }

            // }else{

            //     for(i=1;i<=6;i++){
            //         if(i=='1'){
            //             options = options + '<option value="'+i+'">Á vista por R$'+ parseFloat( (verifytotal / i) ).toFixed(2).replace('.',',') +'</option>';    
            //         }else{
            //             options = options + '<option value="'+i+'">'+i+'x de R$'+ parseFloat( (verifytotal / i) ).toFixed(2).replace('.',',') +'</option>';
            //         }
            //     }

            // }

            // if( (bandeira=='discover') ){
            //         options = '';
            //         i=1;
            //         options = options + '<option value="'+i+'">Á vista por R$'+ parseFloat( (verifytotal / i) ).toFixed(2).replace('.',',') +'</option>';    
            // }


            // $('#pnparcelas').html('<label>Número de parcelas</label><select id="formaPagamento" name="formaPagamento">'+options+'</select>');
            
            /*if( (bandeira=='discover') || (bandeira=='hiper') || (bandeira=='diners')  ){
                $('#numero-cartao').val('');
                $('#pnparcelas').html('<label>Número de parcelas</label><select id="formaPagamento" name="formaPagamento"><option value="">Informe o número do cartão</option></select>');
            }*/

            if(bandeira=='elo'){
               $('#bandeira').val('elo');
               $('img.cartoes').removeClass('opacity');
               $('img.cartoes').addClass('opacity');
               $('img#cardelo').removeClass('opacity');
               //altera a imagem
               $('.imgcodseg').attr('src',site+'img/numseg.png');
               return 'elo';
              }

              if(bandeira=='visa'){
               $('#bandeira').val('visa');
               $('img.cartoes').removeClass('opacity');
               $('img.cartoes').addClass('opacity');
               $('img#cardvisa').removeClass('opacity');
               //altera a imagem
               $('.imgcodseg').attr('src',site+'img/numseg.png');
               return 'visa';
              }

              if(bandeira=='aura'){
               $('#bandeira').val('aura');
               $('img.cartoes').removeClass('opacity');
               $('img.cartoes').addClass('opacity');
               $('img#cardaura').removeClass('opacity');
               //altera a imagem
               $('.imgcodseg').attr('src',site+'img/numseg.png');
               return 'aura';
              }

              if(bandeira=='mastercard'){
                $('#bandeira').val('mastercard');
               $('img.cartoes').removeClass('opacity');
               $('img.cartoes').addClass('opacity');
               $('img#cardmaster').removeClass('opacity');
               //altera a imagem
               $('.imgcodseg').attr('src',site+'img/numseg.png');  
               return 'mastercard';
              }
              if(bandeira=='amex'){
               $('#bandeira').val('amex');
               $('img.cartoes').removeClass('opacity');
               $('img.cartoes').addClass('opacity');
               $('img#cardamex').removeClass('opacity');
               //altera a imagem
               $('.imgcodseg').attr('src',site+'img/numseg2.png');
               return 'amex';
              }
              $('#ncard').html('Número do cartão <span style="color:red">(Bandeira inválida)</span>');
              $('#pnparcelas').html('<label>Número de parcelas</label><select id="formaPagamento" name="formaPagamento"><option value="">Informe o número do cartão</option></select>');
              $('img.cartoes').removeClass('opacity');
              $('img.cartoes').addClass('opacity');
              return '';
        // }

  //   }
  // });
  
  return false;
}

/*$.fn.maps = function(settings){
    var config = {
        'lt'    : 0,
        'lg'    : 0
    };
    if (settings){$.extend(config, settings);}

    return this.each(function(n){

        var $maps = $(this);
        var lt = config.lt;
        var lg = config.lg;

        var myLatlng = new google.maps.LatLng(lt,lg);
        var myOptions = {
          zoom: 16,
          center: myLatlng,
          // scrollwheel: false,
          // disableDoubleClickZoom: true,
          // draggable: false,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map($maps[0], myOptions);

        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            title:"CACE Clínica",
            clickable: false
        }); 
    });
};*/