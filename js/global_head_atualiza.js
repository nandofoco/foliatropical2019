
$(document).ready(function(){   
    

    $(window).load(function () {
       if($.browser.msie && parseInt($.browser.version) <= 8) { $("body").addClass("ie"); }
    });

    // $("form .cancel.fancy-close").click(function(){ parent.$.fancybox.close(); });

    // $('section#conteudo section#cadastro form .selectbox').selectbox();
    // $("section#conteudo section#cadastro form .radio").radio();
    // $("section#conteudo section#cadastro form .checkbox").checkbox();
    // $("form.controle .selectbox").selectbox();
    // $("form.controle .radio").radio();
    // $("form.controle .checkbox").checkbox();

    // //-------------------------------------------------------------------//

    // if($(".tablesorter tbody td")[0]) {
    //     $(".tablesorter").tablesorter({
    //         sortInitialOrder: "desc",
    //         selectorHeaders: 'thead th',
    //         textExtraction: function(node){ 
    //             return $(node).text().replace('.','');
    //             return $(node).text().replace(',','.');
    //         }
    //     }).tablesorterPager({
    //         container: $(".pager-tablesorter"),
    //         positionFixed: false,
    //         size: 30
    //     });
        
    // }

    // //-------------------------------------------------------------------//

    // //------------------- Autocomplete -------------------//

    // $(document).on('focus', ':input', function(){ $( this ).attr('autocomplete', 'off'); });

    // //------------------- Infield -------------------//

    // //Label
    // $(".infield label, label.infield").inFieldLabels({ fadeOpacity:0.3 });

    // //Autosize
    // $("textarea.autosize").autosize();
    
    // //-------------------------------------------------------------------//
    
    // $('a.confirm').click(function(){
    //     var $this=$(this);
    //     var msg = $(this).attr('title');
    //     swal({
    //         title: "",
    //         text: $this.attr('title'),
    //         showCancelButton: true,
    //         confirmButtonText: $this.data('sim'),
    //         cancelButtonText: $this.data('cancelar'),
    //         closeOnConfirm: true
    //     },function(){
    //         return location.href = $this.prop('href');
    //     });
    //     return false;
    // });

    // //-------------------------------------------------------------------//

    // $("a.show-hide-slide").click(function(){
    //     var $alvo = $($(this).attr("href"));
    //     $(this).toggleClass('aberto'); 
    //     $alvo.slideToggle('fast');
    //     return false;
    // });

    // //-------------------------------------------------------------------//

    // if($("a.fancybox") [0]) {
    //     $("a.fancybox").fancybox({
    //         padding: 0 ,
    //         helpers : { title : null }
    //     });

    //     $("a.fancybox.modal").fancybox({ modal:true, helpers : { title : null } });
    //     $("a.fancybox.padding").fancybox({ padding: 15 });
    //     $("a.fancybox.width600").fancybox({ width : 600});
    // }
    
    // //-------------------------------------------------------------------//

    // $("#pop .closepop").click(function(){
    //     $("#pop").fadeOut('fast');
    //     return false;
    // });

    // //-------------------------------------------------------------------//

    // if($('header#topo.index')[0]) {
    //     $(window).scroll(function(){
    //         var scrolltop = $(window).scrollTop();

    //         if(scrolltop > 300) $('header#topo.index').addClass('scroll');
    //         else $('header#topo.index').removeClass('scroll');
    //     });
    // }
    // //-------------------------------------------------------------------//

    // if($(".parallaxouter")[0]) {

    //     $("body").mousemove(function(e){

    //         $(".parallaxouter").each(function(){

    //             $parallax = $(this);

    //             /* Work out mouse position */
    //             var offset = $parallax.offset();
    //             var xPos = e.pageX - offset.left;
    //             var yPos = e.pageY - offset.top;
    //             var wintop = $(window).scrollTop();
    //             var winh = $(window).height();

    //             // if((e.pageY <= $parallax.height()) && (e.pageY >= offset.top)) {
    //             if((wintop >= (offset.top - winh)) && ((wintop + winh) >= (offset.top + $parallax.height()))) {

    //                 /* Get percentage positions */
    //                 var mouseXPercent = Math.round(xPos / $parallax.width() * 100);
    //                 var mouseYPercent = Math.round(yPos / $parallax.height() * 100);

    //                 /* Position Each Layer */
    //                 $parallax.find('.parallax').each(function(){


    //                     var diffX = $(this).width() - 500;
    //                     var diffY = $(this).height() - 50;
                        
    //                     var myX = diffX * (mouseXPercent / 1500); //) / 100) / 2;
    //                     var myY = diffY * (mouseYPercent / 2000);

    //                     var cssObj = {
    //                         'left': myX + 'px',
    //                         'top': myY + 'px'
    //                     }

    //                     // console.log(myX);

    //                     // $(this).css(cssObj);
    //                     $(this).animate({left: myX, top: myY},{duration: 50, queue: false, easing: 'linear'});

    //                 });

    //             }

    //         });

    //     });
    // }

    // //-----------------------------------------------------------------//

    // $('section#pop-up-atendimento a.fechar').click(function () {
    //     $('section#pop-up-atendimento').fadeOut('fast');
    //     return false;
    // })

    // //-----------------------------------------------------------------//

    // if($('#contador')[0]) {
    //     liftoffTime = new Date(2015, 2-1, 13, 0, 0, 0);
    //     $("#contador").countdown({
    //         labels: ['Anos', 'Meses', 'Semanas', 'Dias', 'Horas', 'Min', 'Seg'],
    //         labels1: ['Ano', 'Mês', 'Semana', 'Dia', 'Hora', 'Min', 'Seg'],
    //         until: liftoffTime,
    //         format: 'ODHMS',
    //         serverSync: serverTime,
    //         layout: '<ul>{o<}<li><strong>{o10}{o1}</strong> {ol}</li>{o>}' + '{d<}<li><strong>{d10}{d1}</strong> {dl}</li>{d>}{h<}<li><strong>{h10}{h1}</strong> {hl}</li>{h>}' + '{m<}<li><strong>{m10}{m1}</strong> {ml}</li>{m>}{s<}<li><strong>{s10}{s1}</strong> {sl}</li>{s>}</ul>', expiryText: ''});
    // }

    // //-----------------------------------------------------------------//

    // $('section#comprar-tipos.slider').setslide({
    //     'limit'     : 1,
    //     'timer'     : 5000,
    //     'restart'   : true
    // });

    // $('section.produtos-imagens.slider').setslide({
    //     'limit'     : 1,
    //     'timer'     : 5000,
    //     'restart'   : true,
    //     'pause'     : true
    // });

    // $('section.marketing-imagens.slider').setslide({
    //     'limit'     : 1,
    //     'timer'     : 5000,
    //     'restart'   : true,
    //     'pause'     : true
    // });

    // //-----------------------------------------------------------------//

    // $('header#topo.index .wrapper a.logo').click(function() {
    //     //$.scrollTo($(id),300,{axis:"y"});
    //     $('section#video').ScrollTo(300);
    //     setTimeout(function () {
    //         $('section#video .wrapper a.cover').trigger('click');            
    //     }, 100);

    //     return false;
    // })

    // //-----------------------------------------------------------------//

    // if($('section#video img.bg')[0]){
        
    //     $img = $('section#video img.bg');
    //     var imgh = $img.height();
    //     $img.css('margin-top', '-'+(imgh/2)+'px');

    //     $(window).resize(function() {
    //         $img = $('section#video img.bg');
    //         var imgh = $img.height();
    //         $img.css('margin-top', '-'+(imgh/2)+'px');
    //     });
    // }

    // //-----------------------------------------------------------------//

    // if($('section#lang-select')[0]){
    //     $select = $('section#lang-select');

    //     $select.find('a.lang.arrow').click(function(){
    //         $select.toggleClass('ativo').find('.drop').slideToggle('fast');
    //         return false;
    //     });
    // }

    // //-----------------------------------------------------------------//

    // if($('section#produtos-media .overflow-media')[0]){
    //     $('section#produtos-capacidade ul li a').click(function(){
            
    //         //Classes
    //         $('section#produtos-capacidade ul li a').removeClass('ativo');
    //         $(this).addClass('ativo');

    //         var alvo = $(this).attr('rel');
    //         var $alvo = $('section#produtos-media-'+alvo);
            
    //         $("section#produtos-media .overflow-media").scrollTo($alvo,300,{axis:"x"});
    //         return false;
    //     });
    // }

    // if($('section#produtos-servicos')[0]){
    //     $('section#produtos-servicos ul li a').click(function(){
            
    //         //Classes
    //         $('section#produtos-servicos ul li a').removeClass('ativo');
    //         $(this).addClass('ativo');

    //         var alvo = $(this).attr('rel');
    //         var $alvo = $('section#produtos-servicos-descricao-'+alvo);
            
    //         $("section#produtos-servicos-descricao .overflow").scrollTo($alvo,300,{axis:"x"});
    //         return false;
    //     });
    // }

    // //-----------------------------------------------------------------//

    // if($('section#video')[0]) $('section#video').video();
    // if($('section#produtos-media section.produtos-video')[0]) $('section#produtos-media section.produtos-video').video();
    
    // //-----------------------------------------------------------------//

    // //Instagram
    // if($("section#instagram")[0]) instagram.init();

    // //-----------------------------------------------------------------//

    // if($('section#produtos-media section.produtos-foto360')[0]) {        
    //     var moveSpeed=150;

    //     $('section#produtos-media section.produtos-foto360').each(function(){

    //         var $foto360 = $(this);

    //         $foto360.mouseover(function(){ $foto360.find('a').fadeOut('fast'); })
    //         .mouseleave(function(){ $foto360.find('a').fadeIn('fast'); });

    //         $foto360.find('img').ddpanorama({ratio:460/1024}).bind("ddredraw", function(event){});

    //     })
    // }

    // //-----------------------------------------------------------------//

    // $('section#atendimento section.duvidas form a.limpar').click(function(){
    //     $box = $('section#atendimento section.duvidas');
    //     $box.find('ul.faq li').show();
    //     $box.find('input[name="q"]').val('').blur();
    //     $(this).hide();

    //     return false;
    // });

    // // Busca FAQ
    // $('section#atendimento section.duvidas form.atendimento').submit(function(){
        
    //     $form = $(this);
    //     $box = $('section#atendimento section.duvidas ul.faq');

    //     var filtros = $form.serialize();
    //     var site = $("#base-site").val();

    //     $.post(site + "include/faq.php", filtros, function(resposta){
            
    //         if(resposta.sucesso) {

    //             if(resposta.quantidade > 0) {
                    
    //                 //Nao exibimos os que nao estao no array
    //                 if(resposta.faq != null) {
                        
    //                     var faq = resposta.faq;

    //                     $box.find('li').each(function(e){
    //                         var cod = String($(this).data('codigo'));

    //                         if(faq.indexOf(cod) > -1) $(this).show();
    //                         else $(this).hide();
    //                     });
    //                 }

    //             } else {

    //                 // Ocultamos todos
    //                 $box.find('li').hide();

    //             }

    //             $form.find('a.limpar').show();

    //         }

    //     }, 'json');
        
    //     return false;
    // });
    // $('section#atendimento section.duvidas form.lounge').submit(function(){
        
    //     $form = $(this);
    //     $box = $('section#atendimento section.duvidas ul.faq');

    //     var filtros = $form.serialize();
    //     var site = $("#base-site").val();

    //     $.post(site + "include/lounge-faq.php", filtros, function(resposta){
            
    //         if(resposta.sucesso) {

    //             if(resposta.quantidade > 0) {
                    
    //                 //Nao exibimos os que nao estao no array
    //                 if(resposta.faq != null) {
                        
    //                     var faq = resposta.faq;

    //                     $box.find('li').each(function(e){
    //                         var cod = String($(this).data('codigo'));

    //                         if(faq.indexOf(cod) > -1) $(this).show();
    //                         else $(this).hide();
    //                     });
    //                 }

    //             } else {

    //                 // Ocultamos todos
    //                 $box.find('li').hide();

    //             }

    //             $form.find('a.limpar').show();

    //         }

    //     }, 'json');
        
    //     return false;
    // });

    // //-----------------------------------------------------------------------//

    // // $('form').on('focus', 'input[name^="telefone"],input[name^="celular"]',function () { $(this).mask("(99) 9999-9999?9"); });
    // // $('form').on('focusout', 'input[name^="telefone"],input[name^="celular"]',function () {
    // //     var phone, element;
    // //     element = $(this);
    // //     element.unmask();
    // //     phone = element.val().replace(/\D/g, '');
    // //     if (phone.length > 10) element.mask("(99) 99999-999?9");
    // //     else element.mask("(99) 9999-9999?9");
    // // });
    
    // //Criando os requires
    // $('section#atendimento section.contato form').validation({
    //     rules: {
    //         email: { tipo: 'email' }/*,
    //         telefone: { tipo: 'tel' }*/
    //     }
    // });

    // //-----------------------------------------------------------------------//
    
    // if($("section#twitter")[0]){


    //     updatetwitter();

    //     // Verificação
    //     setInterval('updatetwitter()', 300000);
    // }

    //-----------------------------------------------------------------------//

    //mudar formulario ao trocar pais
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

    if($('section#conteudo section#cadastro')[0]){

        // //Criando os requires
        // $('section#conteudo section#cadastro form').not('.editar').validation({
        //     rules: {
        //         email: { tipo: 'email' },
        //         //cpfcnpj: { tipo: 'cpfcnpj' }
        //     }
        // });

        // $('section#conteudo section#cadastro form.editar').validation({
        //     rules: {
        //         email: { tipo: 'email' },
        //         senha: { required: false },
        //         csenha: { required: false }
        //     }
        // });

        $('section#conteudo section#cadastro .radio').radio();

        $('section#conteudo section#cadastro input[name^="data"]:text').mask('99/99/9999');


        var cadastrocpfcnpj = $('section#conteudo section#cadastro input[name="cpfcnpj"]:text').val();
        var legendas = jQuery.parseJSON($('section#conteudo section#cadastro input[name="legendas"]').val());


        $('section#conteudo section#cadastro input[name="cpfcnpj"]:text').mask('999.999.999-99').val(cadastrocpfcnpj);
        
        $('section#conteudo section#cadastro input[name="pessoa"]:radio').change(function(){
            var pessoa = $(this).val();
            var $cpfcnpj = $('#cadastro-cpfcnpj-box');
            var $nome = $('#cadastro-nome-box');
            if($('#cadastro-sobrenome-box')[0]) var $sobrenome = $('#cadastro-sobrenome-box');
            var $razao = $('#cadastro-razao-box');
            var $datanasc = $('#cadastro-data-nascimento-box');

            var cadastrocpfcnpj = $('section#conteudo section#cadastro input[name="cpfcnpj"]:text').val();

            switch(pessoa) {
                case 'F':
                    $cpfcnpj.find('label').html(legendas.cpf+':');
                    $('label[for="cadastro-passaporte"]').html(legendas.passaporte);
                    $('label[for="cadastro-cpfcnpj"]').html(legendas.cpf);

                    $cpfcnpj.find('input[name="cpfcnpj"]:text').unmask().mask('999.999.999-99').val(cadastrocpfcnpj);
                    $('section#conteudo section#cadastro section#cadastro-sexo.radio').removeClass('empty').slideDown('fast').find('input[name="sexo"]:radio').removeAttr('disabled');
                    $nome.find('label').html(legendas.nome+':');
                    if($('#cadastro-sobrenome-box')[0]) $sobrenome.slideDown('fast').find('input[name="sobrenome"]:text').removeAttr('disabled');
                    $datanasc.find('label').html(legendas.datanascimento+':');
                    $razao.slideUp('fast').find('input[name="razao"]:text').attr('disabled', true);
                break;

                case 'J':
                    $cpfcnpj.find('label').html(legendas.cnpj+':');
                    $('label[for="cadastro-cpfcnpj"]').html(legendas.cnpj);
                    $cpfcnpj.find('input[name="cpfcnpj"]:text').unmask().mask('99.999.999/9999-99').val(cadastrocpfcnpj);
                    $('section#conteudo section#cadastro section#cadastro-sexo.radio').addClass('disabled').removeClass('empty').slideUp('fast').find('input[name="sexo"]:radio').attr('disabled', true);

                    $nome.find('label').html(legendas.nomefantasia+':');
                    if($('#cadastro-sobrenome-box')[0]) $sobrenome.slideUp('fast').find('input[name="sobrenome"]:text').attr('disabled', true);
                    $datanasc.find('label').html(legendas.datafundacao+':');
                    $razao.slideDown('fast').find('input[name="razao"]:text').removeAttr('disabled');
                break;
            }
        });

        $('section#conteudo section#cadastro select[name="pais"]').change(function(event) {
            var pais = $(this).val();
            if(pais=="BR"){
                $('#cadastro-cpfcnpj-box').find('input').attr('name','cpfcnpj').mask('999.999.999-99');
                $('#cadastro-cpfcnpj-box').find('label').html(legendas.cpf+':');
                $('#cadastro-pessoa').show();
            }else{
                $('#pessoa_fisica').closest('.item').trigger('click');
                $('#cadastro-cpfcnpj-box').find('label').html(legendas.passaporte+':');
                $('#cadastro-cpfcnpj-box').find('input').attr('name','passaporte').unmask();
                $('#cadastro-pessoa').hide();
            }
        });
        // $('section#conteudo section#cadastro form').on('change', 'select[name="pais"]', function(event) {
        //     var pais=$(this).val();
        //     if(pais=="BR"){
                
        //         $('#cadastro-cpfcnpj-box').find('label').html(legendas.cpf+':');
        //     }else{
                
                
        //         $('#cadastro-cpfcnpj-box').find('label').html(legendas.passaporte+':');
        //     }
        // });

        /*$('section#conteudo section#cadastro form.login input[name="cpfcnpj"]').unmask();
        $('section#conteudo section#cadastro form.login input[name="cpfcnpj"]').keyup(function(e){

            var $cpfcnpj = $(this);
            var valor = $cpfcnpj.val();
            
            posicao = $cpfcnpj.cursorpos();
            
            if((valor.length > 0) && (posicao == 0)) { $cpfcnpj.unmask().val(''); }

            if(valor.length <= 4) {
                var ponto = (valor.indexOf('.'));
                if(ponto == 2) $cpfcnpj.unmask().mask('?99.999.999/9999-99');
                else if(ponto == 3) $cpfcnpj.unmask().mask('?999.999.999-99');
            }
        });*/
    }


    //------------------- Compras -------------------//

    //Scroll
    if($('section#conteudo section#compre-aqui section#setor-ingresso.scroll')[0]){

        //Se o setor ja está marcado, vamos direto para a escolha do dia
        var $alvo = $('section#conteudo section#compre-aqui section#setor-ingresso.scroll input[name="setor"]').is(':checked') ? $('section#conteudo section#compre-aqui section#compra-dias') : $('section#conteudo section#compre-aqui section#setor-ingresso.scroll');
        setTimeout(function() { $.scrollTo($alvo, 800,{offset: -100, axis:"y"}); }, 100);
    }

    //Criando os requires
    $('section#conteudo section#compre-aqui form#form-compre-aqui').validation({
        rules: {
            valor: { tipo: 'money' },
            estoque: { tipo: 'int' },
            exclusividade: { tipo: 'money' }
        }
    });

    $('section#conteudo section#compre-aqui section.radio').radio();
    $('section#conteudo section#compre-aqui section.checkbox').checkbox();
    $('section#conteudo section#compre-aqui section#tipo-setor ul li a.tipo.disabled').click(function(){ return false; });
    $('section#conteudo section#compre-aqui section#setor-ingresso.radio input[name="setor"]:radio').not('.disabled').change(function() {

        var $setor = $(this);
        if($setor.is(':checked')){

            $('section#conteudo section#compre-aqui span.camiseta').fadeOut('fast');
            $('section#conteudo section#compre-aqui section#compras-especiais section.compras-especiais.hidden').fadeIn('fast');
            $('section#conteudo section#compre-aqui section#compras-especiais').slideUp('fast');

            var dias = jQuery.parseJSON($setor.attr('rel'));

            $('section#conteudo section#compre-aqui section#compra-dias label.item').each(function() {
                var $dia = $(this);

                $('section#conteudo section#compre-aqui section#compra-dias').removeClass('empty');
                $dia.removeClass('checked').addClass('disabled');
                $dia.find('input[name="dia"]:radio').removeAttr('checked').addClass('disabled');

                var dia = $dia.find('input[name="dia"]:radio').val();
                if($.inArray(dia, dias) != '-1') {
                    $dia.removeClass('disabled');
                    $dia.find('input[name="dia"]:radio').removeClass('disabled');
                }
            });

            //Limpar os itens
            $('section#conteudo section#compre-aqui section#compras-itens').slideUp('fast').find('.target').html('');

            //Scroll
            $.scrollTo($('section#conteudo section#compre-aqui section#compra-dias'), 800,{offset: -100, axis:"y"});
        }
    });

    $('section#conteudo section#compre-aqui section#compras-itens').on("change", 'input[name="item[]"]', function(){
        if($(this).is(':checked')) $(this).closest('section.item-compra').addClass('checked');
        else $(this).closest('section.item-compra').removeClass('checked');
    });


    $('section#conteudo section#compre-aqui section#compra-dias input[name="dia"]').change(function(){
        if($(this).is(':checked') && !$(this).hasClass('disabled')) {
            var valores = $('section#conteudo section#compre-aqui form#form-compre-aqui').serialize();
            var site = $("#base-site").val();
            
            //--------------------------------------//

            $('section#conteudo section#compre-aqui span.camiseta').fadeOut('fast');


            //--------------------------------------//

            $('section#conteudo section#compre-aqui section#compras-itens').addClass('loading').slideDown('fast').find('.target').html('');
            
            $.post(site + "include/compras-adicionar-itens.php", valores, function(resposta) {
                if(resposta.sucesso) {
                    $('section#conteudo section#compre-aqui section#compras-itens').removeClass('loading').find('.target').html(resposta.itens).find('.checkbox').checkbox();
                    // $('section#conteudo section#compre-aqui section#compras-itens input.input.money').maskMoney({symbol:'', thousands:'.', decimal:',', symbolStay: false, allowZero: true });
                }
            }, 'json');
            
            //--------------------------------------//

            if($('section#conteudo section#compre-aqui section#compras-itens h2 span.lote')[0]) {
                var atual = $(this).val();
                var $lote = $('section#conteudo section#compre-aqui section#compras-itens h2');
                switch(atual) {
                    case '17':
                        $lote.find('span.lote.candybox').show();
                        $lote.find('span.lote.folia').hide();                        
                    break;
                    default:
                        $lote.find('span.lote.candybox').hide();
                        $lote.find('span.lote.folia').show();                        
                    break;
                }
            }

            //Se existirem especiais
            if($('section#conteudo section#compre-aqui section#compras-especiais')[0]){
                var atual = $(this).val();
                var $especiais = $('section#conteudo section#compre-aqui section#compras-especiais');

                $especiais.find('section.compras-especiais.hidden').fadeOut('fast');

                switch(atual) {
                    case '27':
                        //if($('section#conteudo section#compre-aqui.folia-tropical')[0]) {
                        if($('section#compras-candybox')[0]) {
                            $especiais.slideDown('fast');
                            $especiais.find('section#compras-candybox.compras-especiais.hidden').fadeIn('fast');
                            //$('section#conteudo section#compre-aqui span.camiseta').fadeIn('fast');
                        } else {
                            $especiais.slideUp('fast');
                        }
                    break;
                    case '28':
                        
                        //if($('section#conteudo section#compre-aqui.folia-tropical')[0]){
                        if($('section#compras-especial-sabado')[0]) {
                            $especiais.slideDown('fast');
                            $especiais.find('section#compras-especial-sabado.compras-especiais.hidden').fadeIn('fast');                            
                        } else {
                            $especiais.slideUp('fast');
                        }
                    break;
                    case '29':
                        $especiais.slideDown('fast');
                        $especiais.find('section#compras-especial-domingo.compras-especiais.hidden').fadeIn('fast'); 
                        if($('section#conteudo section#compre-aqui.folia-tropical')[0]) $('section#conteudo section#compre-aqui span.camiseta').fadeIn('fast');
                    break;
                    case '30':
                        $especiais.slideDown('fast');
                        $especiais.find('section#compras-especial-segunda.compras-especiais.hidden').fadeIn('fast');
                        if($('section#conteudo section#compre-aqui.folia-tropical')[0]) $('section#conteudo section#compre-aqui span.camiseta').fadeIn('fast');
                    break;
                    case '31':
                        if($('section#compras-especial-campeas')[0]) {
                            $especiais.slideDown('fast');
                            $especiais.find('section#compras-especial-campeas.compras-especiais.hidden').fadeIn('fast');
                            if($('section#conteudo section#compre-aqui.folia-tropical')[0]) $('section#conteudo section#compre-aqui span.camiseta').fadeIn('fast');
                        } else {
                            $especiais.slideUp('fast');
                        }
                    break;
                    case '32':
                        if($('section#compras-especial-sexta')[0]) {
                            $especiais.slideDown('fast');
                            $especiais.find('section#compras-especial-sexta.compras-especiais.hidden').fadeIn('fast');
                        } else {
                            $especiais.slideUp('fast');
                        }
                    break;
                    default:
                        $especiais.slideUp('fast');
                    break;
                }

            }
        }

    });
    
    //------------------- Compras carrinho quantidade -------------------//

    $('section#conteudo form#compras-carrinho').validation();
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
        $.getJSON(site + "ingressos/adicionar/", {
            quantidade: qtd,
            a: 'quantidade',
            c: key
        });

    });


    //------------------- Check Adicionais -------------------//

    //Criando os requires
    $('section#conteudo form#compras-adicionais').validation({
        rules: {
            quantidade: { tipo: 'int' },
            valoritem: { required: false },
            valoradicional: { required: false },
            adicionaiscod: { required: false },
            exclusividade: { required: false },
            comentarios: { required: false }            
        }
    });
    //$('section#conteudo form#compras-adicionais input[name^="data"]:text').mask('99/99/9999');

    // Calcular adicionais
    if($('section#conteudo form#compras-adicionais')[0]) compras_adicionais_total();

    $('section#conteudo form#compras-adicionais input.adicional').change(function(){
        if($(this).is(':checked')) $(this).closest('tr').addClass('checked');
        else $(this).closest('tr').removeClass('checked');

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

        //Enviar a quantidade para sessao
        $.getJSON(site + "ingressos/adicionar/", {
            quantidade: qtd,
            a: 'quantidade',
            c: key
        });
        
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
    
    function compras_adicionais_total() {

        // calcular o total
        var total = 0.00;

        $('section#conteudo form#compras-adicionais .item-carrinho').not('.extra').each(function () {
            $item = $(this);
            var itemval = parseFloat($item.find('input[name="valoritem"]').val()) * parseInt($item.find('input[name^="quantidade"]').val());
            total += parseFloat(itemval);
        });

        //Se houver desconto de 10%
        if($('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.incluso.desconto.folia input.desconto').not(':disabled')[0]) {
            var $desconto = $('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.incluso.desconto.folia input.desconto');
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
            
            var $frisa_desconto = $('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.incluso.desconto.frisa');
            
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

        if($('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.incluso.desconto.frisa input.desconto').not(':disabled')[0]) {
            var desconto = parseFloat($('section#conteudo form#compras-adicionais .item-carrinho .compras-adicionais tr.incluso.desconto.frisa input.desconto').val());
            total = (total - desconto);
        }

        // $('section#conteudo form#compras-adicionais tr.checked input[name="valoradicional"]').each(function () {
        $('section#conteudo form#compras-adicionais tr input[name="valoradicional"]').each(function () {
            var $adicional = $(this);
            adicional = parseFloat($(this).val());
            if($adicional.hasClass('multi')) adicional = adicional * parseInt($adicional.closest('.item-carrinho').find('input[name^="quantidade"]').val());

            if($adicional.closest('tr').hasClass('checked')) total += parseFloat(adicional);

            adicional = adicional.toFixed(2).replace(".",",");
            if(adicional.length > 6) adicional = adicional.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

            $adicional.closest('tr').find('td.valor').html('R$ '+ adicional);
        });

        total = total.toFixed(2).replace(".",",");
        if(total.length > 6) total = total.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

        $('section#conteudo form#compras-adicionais').find('header .valor-total, footer .valor-total').html('R$ '+ total);
    }

    $('section#conteudo form#compras-adicionais input.pne').change(function(){
        var $pne = $(this);

        if($pne.is(':checked')) {

            swal({ 
                title: "Pessoa com Necessidades Especiais",
                text: "Notamos que você selecionou a opção “Ingressos para Pessoa com Necessidades Especiais“.\nPor favor nos informe as necessidades que precisam ser atendidas na caixa de comentários da compra.",
                type: "success" 
            },function(){
                setTimeout(function() { $pne.closest('.item-carrinho').find('.comentarios textarea').focus(); }, 100);
            });

        }
    });

    //------------------- No submit -------------------//

    //Prevent Form Submit        
    $('section#conteudo section#compre-aqui form#compras-adicionais').on('keydown', 'input#carrinho-cliente', function(event){
        if(event.keyCode == 13) {            
            event.preventDefault();
            return false;
        }
    });

    //------------------- CEP -------------------//

    if($('section#conteudo section#compre-aqui form#compras-delivery')[0]) {
        var $cadastro = $('section#conteudo section#compre-aqui form#compras-delivery');

        $cadastro.find("input[name='cep']:text").mask('99999-999');
        $cadastro.find("input[name='cep']").blur(function(){
            
            var site = $("#base-site").val();
            
            // Pegamos o valor do input CEP
            var cep = $cadastro.find("input[name='cep']").val();
            
            // Se o CEP nÃ£o estiver em branco
            if(cep != '') {

                //Validar o CEP

                // Adiciona imagem de "Loading"
                $cadastro.find(".endereco input[name='cep']").addClass('loading');
                
                $.getJSON(site + "include/busca-cep.php", {
                    cep: cep
                }, function(resultado) {
                    $cadastro.find(".endereco input[name='cep']").removeClass('loading');

                    if(resultado.sucesso) {
                        //Valores
                        $cadastro.find("input[name='endereco']").val(resultado.logradouro).blur();
                        $cadastro.find("input[name='bairro']").val(resultado.bairro).blur();
                        $cadastro.find("input[name='cidade']").val(resultado.cidade).blur();
                        $cadastro.find("input[name='estado']").radioSel(resultado.uf);
                        $cadastro.find("input[name='numero']").focus();        
                    } else {
                        alert('Infelizmente não entregamos para o endereço informado');

                        $cadastro.find("input[name='endereco']").val('').blur();
                        $cadastro.find("input[name='bairro']").val('').blur();
                        $cadastro.find("input[name='cidade']").val('').blur();
                        $cadastro.find("input[name='numero']").val('').blur();
                        $cadastro.find("input[name='cep']").val('').focus();
                    }
                });
            } else {
                // Se o campo CEP estiver em branco, apresenta mensagem de erro
                // alert('Para que o endereÃ§o seja completado automaticamente vocÃª deve preencher o campo CEP!');
            }
            return false;
        });

        $('section#conteudo section#compre-aqui form#compras-delivery').validation({
            rules: {
                cep: { tipo: 'cep' },
                complemento: { required: false }
            }
        });
    }
    
    //-----------------------------------------------------------------//
    
    if($('section#conteudo section#compre-aqui form#form-cupom-pagamento')[0]) {

        $('section#conteudo section#compre-aqui form#form-cupom-pagamento input#compra-cupom').keyup(function(){
            var cupom = $(this).val();
            if((cupom == 'PETROS') || (cupom == 'petros')) {
                $('section#conteudo section#compre-aqui form#form-cupom-pagamento .matricula ').slideDown('fast');
                $('section#conteudo section#compre-aqui form#form-cupom-pagamento .matricula input#compra-matricula').removeAttr('disabled').focus();
            } else {
                $('section#conteudo section#compre-aqui form#form-cupom-pagamento .matricula input#compra-matricula').attr('disabled', true);
                $('section#conteudo section#compre-aqui form#form-cupom-pagamento .matricula ').slideUp('fast');
            }
        });

        $('section#conteudo section#compre-aqui form#form-cupom-pagamento').validation();
        
    }

    //-----------------------------------------------------------------//

    if($('section#conteudo section#compre-aqui form#form-compra-pagamento')[0]) {

        $('section#conteudo section#compre-aqui form#form-compra-pagamento').validation();

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
    }


    //-----------------------------------------------------------------//

    if($('section#compre-aqui section#compra-itens')[0]) {

        //Organizar z-index
        var lista = $('section#conteudo section#compre-aqui section.secao#compra-itens ul.itens li.lista-itens').size() + 5;
        $('section#conteudo section#compre-aqui section.secao#compra-itens ul.itens li.lista-itens').each(function(){
            $(this).css('z-index', lista);
            lista--;
        })


        $('form[id^=agendamento]').validation();

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
                // return false;
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
                //return false;
            }
        });
        
    }

    //-----------------------------------------------------------------------//

    if($('section#conteudo.camisas section#compre-aqui')[0]) {

        $('section#conteudo.camisas section#compre-aqui section#camisas-adicionar.secao form').validation({
            rules: {
                quantidade: { tipo: 'int' }
            }
        });

        $('section#conteudo.camisas section#compre-aqui section#camisas-adicionar.secao form').submit(function(){
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

        $('section#conteudo.camisas section#compre-aqui section#camisas-lista').on('click', 'a.remover', function(){
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
        
        $('section#conteudo.camisas section#compre-aqui section#camisas-adicionar.secao form input[name="quantidade"').blur(function(){
            var valor = parseInt($(this).val());
            if(!camisas_total(valor)) {
                alert('O limite de camisas foi atingido');
                $(this).val('1');
            }
        });

        //-----------------------------------------------------------------------//

        function camisas_total(adicionar) {

            var $camisas = $('section#conteudo.camisas section#compre-aqui');
            
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

    //-----------------------------------------------------------------//

    //Criando os requires
    $('section#marketing section.contato form').validation({
        rules: {
            email: { tipo: 'email' },
            telefone: { tipo: 'tel' }
        }
    });

    $('section#conteudo section#marketing article .link a').click(function() {
        var $contato = $(this).closest('article');
        $contato.addClass('ativo').find('section.contato').slideDown('fast');
        return false;
    });

    if($('section#marketing')[0]) {

        var target = window.location.href;

        if(target.indexOf("#/") > 0){

            var query = target.split("#/");
            var acao = query[1];
            
            var $marketing = $('section#conteudo section#marketing article.'+acao);
            $marketing.addClass('ativo').find('section.contato').slideDown('fast', function(){
                $marketing.find('section.link').ScrollTo(300);
            });

        }

    }

    //-----------------------------------------------------------------//

    $('section#overlay-cupom article a.ctrl.fechar').click(function () {
        $('section#overlay-cupom').fadeOut('fast');
        return false;
    })

});


//-----------------------------------------------------------------//

function updatetwitter(){
    var $twitter = $("section#twitter");
    var site = $("#base-site").val();
    
    // Pegamos o valor da data
    var timestamp = parseInt($twitter.attr("data-timestamp"));
    
    $.getJSON(site + "include/twitter.php", { ver:true }, function(resultado) {
        
        var tweet = resultado[0];

        // Se houver mudanca
        if(tweet.sucesso && (timestamp != parseInt(tweet.timestamp)) && (parseInt(tweet.timestamp) > 0)){
            $twitter.attr("data-timestamp", tweet.timestamp);
            // $twitter.find("strong").html(tweet.data);
            $twitter.find("p").html(tweet.message);
        }

    });            
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
    	var pause = config.pause;

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
                        $setslide.find("a.ctrl-play-pause").removeClass("pause");
        			}

        			var alvo = $(this).attr("href");
        			var id = parseInt(alvo.replace(obj + "-pg-",""));
        			
        			if(alvo != "#"){

                        if($setslide.find(".ctrls a")[0]){
                            $setslide.find(".ctrls a.ativo").removeClass("ativo");
                            $setslide.find(".ctrls a[href='"+alvo+"']").addClass("ativo");
                        }

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
                            $setslide.find("a.ctrl-play-pause").removeClass("pause");
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

            //Banner pause/play
            if(pause && (timer > 0)) {

                //Criar botão
                $setslide.append('<a href="#" class="ctrl-play-pause"></a>');
                $setslide.find('a.ctrl-play-pause').click(function () {
                    $(this).toggleClass('pause');
                    if($(this).hasClass('pause')) clearInterval(slidestart);
                    else slidestart = setInterval(function(){ $setslide.find(".next").trigger("click"); },timer);
                    return false;
                });
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
                $selectbox.addClass("ativo").find(".drop").slideDown("fast");
            else
                $selectbox.removeClass("ativo").find(".drop").slideUp("fast");
            
            return false;
        });
        
        //Sumir Drop
        $(document).click(function(event) {
            if($(event.target) != $selectbox) {
                 $selectbox.find(".drop").slideUp('fast');
                 $selectbox.removeClass("ativo");
            }
        });
        
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
                $selectbox.find("a.arrow strong").html("Selecione");
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

            // $validation.addClass('return-false');

            // //Criando os requires
            // $validation.find('input, textarea').not(':disabled, [type="submit"], [type="hidden"]').each(function(){ $(this).addClass('required'); });
            
            // Regras
            // for(key in config.rules){
            //     if(config.rules[key].tipo !== undefined) $validation.find('input[name="'+key+'"],input[name^="'+key+'["],textarea[name="'+key+'"],textarea[name^="'+key+'["]').data('tipo', config.rules[key].tipo);
            //     if((config.rules[key].required !== undefined) && (config.rules[key].required === false)) $validation.find('input[name="'+key+'"],input[name^="'+key+'["],textarea[name="'+key+'"],textarea[name^="'+key+'["]').removeClass('required');
            // }
            
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
                            // regexp['email'] = /^[a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[.][a-zA-Z]{2,4}$/;
                            regexp['cpf'] = /^[\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2}$/;
                            regexp['cnpj'] = /^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$/;
                            regexp['cpfcnpj'] = /(^\d{3}\.\d{3}\.\d{3}\-\d{2}$)|(^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$)/;
                            // regexp['money'] = /^([0-9]{1,3}.){1,}[0-9]{2}$/;
                            // regexp['cep']= /^[0-9]{5}-[0-9]{3}$/;
                            // regexp['tel']= /^\(\d{2}\)\ \d{4,5}-\d{4}$/;
                            // regexp['int']= /^[0-9]+$/;
                            
                            if(!regexp[tipo].test(valor)) {
                                retorno = false;
                                $(this).addClass('invalid');
                            } else {
                                $(this).removeClass('invalid');
                            }
                        }

                        // if(!(valor.length > 0)){
                        //     retorno = false;
                        //     $(this).addClass('empty');
                        // } else {
                        //     $(this).removeClass('empty');
                        // }

                    break;
                    case 'password':

                        var valor = $(this).val();
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
            
            var $voltar = $validation.find('.empty, .invalid').first();

            if($voltar.closest('.item-carrinho.extra')) $voltar = $('.item-carrinho.extra');
            
            if(retorno) $validation.removeClass('return-false');
            else $voltar.ScrollTo(300);            

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

//------------------ video -----------------//

$.fn.video = function(){

    return this.each(function(){

        var $alvo = $(this);

        $alvo.find("a.loadvideo").click(function(){
            var site = $("#base-site").val();
            var page = "interna";
            var cod = $(this).attr('rel');

            if($alvo.hasClass("index"))page = "index"; 
            
            $.get(site + "include/videos.php",{
                cod: cod,
                page: page
            },function(data){
                $alvo.find("a.loadvideo").fadeOut('fast', function(){
                    $alvo.find(".iframe").html(data).fadeIn('fast');
                });
            });
            
            return false;
        });

        
    });
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


//------------------------ Instagram ------------------------//

var instagram = function() {
    var accessToken = null;
    var username= "foliatropical";
    var limit_visible = 5;
    var limit = 60;

    return {
        init: function() {
            //pegamos o token que está no servidor
            site = $("#base-site").val();
            $.getJSON( site+"instagram/accesstoken.txt", function(data) {
                accessToken = data.token;
                instagram.getUser();
            });
        },
        getUser: function() {
            var getUserURL = 'https://api.instagram.com/v1/users/search?q='+ username +'&access_token='+ accessToken +'';
            $.ajax({
                type: "GET",
                dataType: "jsonp",
                cache: false,
                url: getUserURL,
                success: function(data) {
                    if(data.meta.code == 400) {
                        site = $("#base-site").val();
                        $.getJSON( site+"instagram/gettoken.php", { access: true }, function(data) {
                            if(data.token !== undefined) {
                                accessToken = data.token;
                                instagram.getUser();
                            }
                        });

                    } else {
                        var getUserID = data.data[0].id;
                        instagram.loadImages(getUserID);
                    }
                }
            });
        },
        loadImages: function(userID) {
            var getImagesURL = 'https://api.instagram.com/v1/users/'+ userID +'/media/recent/?count='+limit+'&access_token='+ accessToken +'';
            $.ajax({
                type: "GET",
                dataType: "jsonp",
                cache: false,
                url: getImagesURL,
                success: function(data) {
                    
                    if($('section#instagram.index')[0]) limit_visible = 7;

                    var qtde = data.data.length;
                    if(qtde < limit){ limit = qtde; }
                
                    for(var i = 0; i < limit; i++) {
                        
                        var title = "";
                        var imglink = "";
                        var imgclass = "";
                        var imgextra = "";
                        
                        
                        if (data.data[i].caption != null) {
                            title = ' title="'+ data.data[i].caption.text +'" ';
                        }
                        
                        if(i == 0){
                            imglink = '<img src="'+data.data[i].images.standard_resolution.url+'"/>';
                            imgclass = "big";
                            imgextra = '<span class="zoom"></span><span class="scan"></span>';
                        } else if(i < limit_visible ) {
                            imglink = '<img src="'+data.data[i].images.thumbnail.url+'"/>';
                            imgclass = "small";
                        } else {
                            imgclass = "invisible";
                        }
                          
                        $("#instagram ul").append('<li class="'+imgclass+'"><a href="'+data.data[i].images.standard_resolution.url+'" '+title+' class="thumb" rel="instagram">'+imglink+imgextra+'</a></li>'); 
                    }
                    
                    $("#instagram ul a.thumb").fancybox({
                        'transitionIn'  : 'elastic',
                        'transitionOut' : 'elastic',
                        'overlayColor'  : '#000000',
                        'overlayOpacity': 0.75,
                        'padding'       : 0  
                    }); 
                }
            });
        }
    }
}();

//-----------------------------------------------------------------//

function serverTime() { 
    var site = $("#base-site").val();
    var time = null; 
    $.ajax({url: site + 'include/servertime.php', 
        async: false, dataType: 'text', 
        success: function(text) { 
            time = new Date(text); 
        }, error: function(http, message, exc) { 
            time = new Date(); 
    }}); 
    return time; 
}

/*//-----------------------------------------------------------------//

$.fn.maps = function(settings){
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