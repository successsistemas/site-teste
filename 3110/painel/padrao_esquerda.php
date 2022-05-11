<?php 
require_once('../padrao_restrito.php');
include("parametros.php");
if($praca_status == 0){ echo "<script language= \"JavaScript\">location.href=\"../../painel/index.php\"</script>"; exit; } 

?>
<script>
$(document).ready(function() {
	
	$("#accordion > li > div.menu_titulo").click(function(){
		if(false == $(this).next().is(':visible')) {
			$('#accordion > li > ul').slideUp(300);
		}
		$(this).next().slideToggle(300);
	});
	
});
</script>
<ul id="accordion">

	<li>
        <a href="../padrao/index.php" target="_top">
        <div class="menu_titulo" style="font-weight: bold;">Início</div>
        </a>
    </li>   


    <li>
        <a href="../../painel.php?padrao=sim">
        <div class="menu_titulo" style="font-weight: bold">Painel</div>
        </a>
	</li>

	<? if($row_usuario['administrador_site']=="Y"){ ?>
    <li>
        <a href="../../painel2.php?data_inicio=<? echo date('d-m-Y', strtotime('-1 days')); ?>&data_fim=<? echo date('d-m-Y', strtotime('-1 days')); ?>">
        <div class="menu_titulo" style="font-weight: bold">Histórico do painel</div>
        </a>
	</li>
	<? } ?>
    
    <li>
        <a href="../../solicitacao.php?padrao=sim&<? echo $situacao_padrao; ?>">
        <div class="menu_titulo" style="font-weight: bold">Controle de solicitação</div>
        </a>
	</li>
    
    
    <li>
        <a href="../../suporte.php?padrao=sim&<? echo $suporte_padrao; ?>">
        <div class="menu_titulo" style="font-weight: bold">Controle de suporte</div>
        </a>
	</li>
    
    <li>
        <a href="../../prospeccao.php?padrao=sim&<? echo $prospeccao_padrao; ?>">
        <div class="menu_titulo" style="font-weight: bold">Controle de prospecção</div>
        </a>    
	</li>

    <li>
        <a href="../../venda.php?padrao=sim&<? echo $venda_padrao; ?>">
        <div class="menu_titulo" style="font-weight: bold">Controle de vendas</div>
        </a>    
	</li>
    
    <li>
        <a href="../../agenda.php?padrao=sim&<? echo $agenda_padrao; ?>">
        <div class="menu_titulo" style="font-weight: bold">Agenda</div>
        </a>    
	</li>

	<li>
		<div class="menu_titulo"><strong>Relatórios</strong></div>
		<ul>
        
			<li>
            <a href="../../relatorio.php?padrao=sim&tela=digital&relatorio_id_grupo=1&relatorio_id_grupo_subgrupo=0">
            <div class="menu_subopcao">Solicitações</div>
            </a>
            </li>
            
			<li>
            <a href="../../relatorio.php?padrao=sim&tela=digital&relatorio_id_grupo=2&relatorio_id_grupo_subgrupo=0">
            <div class="menu_subopcao">Suportes</div>
            </a>
            </li>
            
			<li>
            <a href="../../relatorio.php?padrao=sim&tela=digital&relatorio_id_grupo=3&relatorio_id_grupo_subgrupo=0">
            <div class="menu_subopcao">Prospecções</div>
            </a>
            </li>
            
			<li>
            <a href="../../relatorio.php?padrao=sim&tela=digital&relatorio_id_grupo=4&relatorio_id_grupo_subgrupo=0">
            <div class="menu_subopcao">Vendas</div>
            </a>
            </li>
            
			<li>
            <a href="../../relatorio.php?padrao=sim&tela=digital&relatorio_id_grupo=5&relatorio_id_grupo_subgrupo=0">
            <div class="menu_subopcao">Administrativos</div>
            </a>
            </li>
            
            <? if($row_usuario['controle_relatorio']=='Y' or $row_usuario['controle_praca']=='Y'){ ?>
			<li>
            <a href="../../relatorio.php?padrao=sim&amp;tela=digital&amp;relatorio_id_grupo=0&amp;relatorio_id_grupo_subgrupo=0&amp;filtro_geral_praca=<? if($row_usuario['controle_relatorio'] != "Y"){ echo $row_usuario['praca']; } ?>&amp;filtro_geral_data_criacao=<? echo date('01-m-Y'); ?>&amp;filtro_geral_data_criacao_fim=<? echo date('t-m-Y'); ?>">
            <div class="menu_subopcao">Resultados Mensais</div>
            </a>
            </li>
            <? } ?>
            
		</ul>
	</li>
    
	<li>
    	<a href="../download/listar_grupo.php">
    	<div class="menu_titulo" style="font-weight: bold;">Downloads</div>
        </a>
	</li>


	<? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../site_link/listar.php">
    	<div class="menu_titulo">Site - Links</div>
        </a>
	</li>
	<? } ?>
	
	<? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../site_parceiro/listar.php">
    	<div class="menu_titulo">Site - Parceiros</div>
        </a>
	</li>
    <? } ?>	
    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
        <a href="../site_banner_principal/listar.php">
        <div class="menu_titulo">Site - Banner Principal</div>
        </a>
	</li>
    <? } ?>
    
    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../site_banner_inferior/listar.php">
    	<div class="menu_titulo">Site - Banner Inferior</div>
        </a>
	</li>
    <? } ?>
    
       
    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../site_evento/listar.php">
    	<div class="menu_titulo">Site - Eventos</div>
        </a>
	</li>
    <? } ?>
    
    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../geral_tipo_praca/listar.php">
    	<div class="menu_titulo">Geral - Praça/Executor</div>
        </a>
	</li>
    <? } ?>
    
    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../geral_tipo_modulo/listar_categoria.php">
    	<div class="menu_titulo">Geral - Módulo</div>
        </a>
	</li>
    <? } ?>
    
    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../geral_tipo_versao/listar.php">
    	<div class="menu_titulo">Geral - Versão</div>
        </a>
	</li>
    <? } ?>
    
    
    <? if($row_usuario['controle_programa_subprograma']=="Y" or $row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../geral_tipo_programa/listar.php">
    	<div class="menu_titulo">Geral - Programa/Sub.</div>
        </a>
	</li>
	<? } ?>
    
    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../geral_tipo_banco_de_dados/listar.php">
    	<div class="menu_titulo">Geral - Banco de dados</div>
        </a>
	</li>
    <? } ?>
    
    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../geral_tipo_ecf/listar.php">
    	<div class="menu_titulo">Geral - ECF</div>
        </a>
	</li>
	<? } ?>
    
    
	<? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../geral_tipo_ramo_atividade/listar.php">
    	<div class="menu_titulo">Geral - Ramo de Atividade</div>
        </a>
	</li>
    <? } ?>
    
	<? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../geral_procedimento_site/listar.php">
    	<div class="menu_titulo">Geral - Procedimentos</div>
        </a>
	</li>
    <? } ?>

	<? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../suporte_tipo_atendimento/listar.php">
    	<div class="menu_titulo">Suporte - Atendimento</div>
        </a>
	</li>
    <? } ?>
    
	<? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../suporte_tipo_parecer/listar.php">
    	<div class="menu_titulo">Suporte - Parecer</div>
        </a>
	</li>
    <? } ?>
    
	<? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../suporte_tipo_recomendacao/listar.php">
    	<div class="menu_titulo">Suporte - Recomendação</div>
        </a>
	</li>
    <? } ?>
    
    
	<li>
		<div class="menu_titulo"><strong>Prospecção - Cadastros</strong></div>
		<ul>
        
			<? if($row_usuario['administrador_site']=="Y"){ ?>
			<li>
            <a href="../prospeccao/listar_pergunta.php">
            <div class="menu_subopcao">Questionário</div>
            </a>
            </li>
    		<? } ?>
			
			<? if($row_usuario['administrador_site']=="Y"){ ?>
			<li>
            <a href="../prospeccao/listar_agenda_tipo.php">
            <div class="menu_subopcao">Tipo de agendamento</div>
            </a>
            </li>
            <? } ?>
			
			<? if($row_usuario['administrador_site']=="Y"){ ?>
			<li>
            <a href="../prospeccao/listar_perda_pergunta.php">
            <div class="menu_subopcao">Perda - Questionário</div>
            </a>
            </li>
			<? } ?>
			
			<li>
				<a href="../prospeccao/listar_contador.php">
				<div class="menu_subopcao">Contador</div>
				</a>
			</li> 
			
			<li>
				<a href="../prospeccao/listar_concorrente.php">
				<div class="menu_subopcao">Concorrente</div>
				</a>
			</li> 
						   
		</ul>
	</li>

	    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../implantacao/listar_avaliacao_pergunta.php">
    	<div class="menu_titulo">Implantação - Avaliação</div>
        </a>
	</li> 
    <? } ?>

    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../implantacao/listar_pergunta.php">
    	<div class="menu_titulo">Implantação - Quest.</div>
        </a>
	</li> 
    <? } ?>
    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../treinamento/listar_pergunta.php">
    	<div class="menu_titulo">Treinamento - Quest.</div>
        </a>
	</li> 
    <? } ?>
    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
    	<a href="../relatorio_evento/listar.php">
    	<div class="menu_titulo">Relatórios - Eventos</div>
        </a>
	</li> 
	<li>
    	<a href="../relatorio_classificacao_nivel/listar.php">
    	<div class="menu_titulo">Relatórios - Níveis de Clas.</div>
        </a>
	</li> 
    <? } ?>
    
	<? if($row_usuario['administrador_site']=="Y" or $row_usuario['controle_comunicado']=="Y"){ ?>
	<li>
    	<a href="../comunicado/listar.php">
    	<div class="menu_titulo">Comunicados</div>
        </a>
	</li>
	<? } ?>
	
	<? if($row_usuario['administrador_site']=="Y" or $row_usuario['controle_mala_direta']=="Y"){ ?>
	<li>
    	<a href="../mala_direta/listar.php">
    	<div class="menu_titulo">Mala Direta</div>
        </a>
	</li>
    <? } ?>
	
    <? if($row_usuario['administrador_site']=="Y"){ ?>    
	<li>
    	<a href="../emails_aviso/listar.php">
    	<div class="menu_titulo">E-mails de aviso</div>
        </a>
	</li>
	<? } ?>
    
    <? if($row_usuario['controle_usuarios']=="Y"){ ?>
	<li>
    	<a href="../usuarios/listar.php">
    	<div class="menu_titulo">Usuários</div>
        </a>
	</li>
	<? } ?>
    
    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
        <a href="../../dump_manual.php" target="_blank">
        <div class="menu_titulo" style="font-weight: bold;">Atualizar B.D. via FTP</div>
        </a>    
    </li>
    <? } ?>
    
    
    <? if($row_usuario['administrador_site']=="Y"){ ?>
	<li>
        <a href="../parametros/tabela.php" target="_top">
        <div class="menu_titulo" style="font-weight: bold;">Parâmetros</div>
        </a>
    </li>
    <? } ?>


	<li>
        <a href="../site/site_evento.php">
        <div class="menu_titulo">Eventos</div>
        </a>
    </li>
    
	<li>
        <a href="http://webmail.success.inf.br/" target="_blank">
        <div class="menu_titulo">Webmail</div>
        </a>
    </li>    
	
    
	<li>
		<a href="../padrao_sair.php" target="_top">
        <div class="menu_titulo" style="font-weight: bold;">Sair</div>
        </a>
    </li>      


</ul>
