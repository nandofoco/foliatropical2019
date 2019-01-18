<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

$meta_title = "Ingressos carnaval 2019 RJ, Camarote Folia Tropical e Frisas";
$meta_description = "Ingressos carnaval 2019 Rio de Janeiro, camarotes e frisas! Serviços individuais e para empresas, internacionais e domésticos, translado e hospedagem.";

define('PGRESPOSTA', 'true');

//arquivos de layout
include("include/head.php");
?>
    <section id="resposta">
        <a href="<? echo SITE; ?>" id="logo"><span>Folia Tropical</span></a>
        <h2><? echo $lg['404_nao_encontrado']; ?></h2>
        <a href="<? echo SITE.$link; ?>" class="voltar"><? echo $lg['404_voltar']; ?></a>
    </section>

</body>
</html>