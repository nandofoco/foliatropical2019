        <footer id="rodape"></footer>
        <?

        switch ($_SESSION['ALERT'][0]) {
        case 'sucesso':
            echo '<script>'.'swal("", "'.$_SESSION['ALERT'][1].'", "success")'.'</script>';
            break;
        case 'erro':
            echo '<script>'.'swal("", "'.$_SESSION['ALERT'][1].'", "error")'.'</script>';
            break;
        case 'aviso':
            echo '<script>'.'swal("", "'.$_SESSION['ALERT'][1].'", "warning")'.'</script>';
            break;
        }
        // Limpando todos os avisos
        unset($_SESSION['ALERT']);

        ?>
        <script>
            (function (a, b, c, d, e, f, g) {
            a['CsdmObject'] = e; a[e] = a[e] || function () {
            (a[e].q = a[e].q || []).push(arguments)
            }, a[e].l = 1 * new Date(); f = b.createElement(c),
            g = b.getElementsByTagName(c)[0]; f.async = 1; f.src = d; g.parentNode.insertBefore(f, g)
            })(window, document, 'script', '//device.clearsale.com.br/m/cs.js', 'csdm');
            csdm('app', '<? echo CLEARSALE_APP; ?>');
            csdm('mode', 'manual');

            if($('#page').length && $('#page_key').length){
                csdm('send', $('#page').val(),$('#page_key').val());
            }else if($('#page').length){
                csdm('send', $('#page').val());
            }
        </script>

        

        <!-- DO NOT MODIFY -->
        <!-- End Facebook Pixel Code -->

        <!-- Código do Google para tag de remarketing -->
        <!--As tags de remarketing não podem ser associadas a informações pessoais de identificação nem inseridas em páginas relacionadas a categorias de confidencialidade. Veja mais informações e instruções sobre como configurar a tag em: http://google.com/ads/remarketingsetup-->

        <script type="text/javascript">
            /* <![CDATA[ */
            var google_conversion_id = 868380864;
            var google_custom_params = window.google_tag_params;
            var google_remarketing_only = true;
            /* ]]> */
        </script>
        <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>
        <noscript>
            <div style="display:inline;">
                <img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/868380864/?guid=ON&amp;script=0"/>
            </div>
        </noscript>
    </body>
</html>