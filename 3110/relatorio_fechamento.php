<?php 
require('restrito.php');
require_once('Connections/conexao.php');

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

require_once('parametros.php');

// usuarios
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuarios

// relatorio_fechamento_confirma
mysql_select_db($database_conexao, $conexao);
$query_relatorio_fechamento_confirma = sprintf("
SELECT * 
FROM relatorio_fechamento 
WHERE relatorio_fechamento.praca = %s and YEAR(data_criacao) = YEAR(now()) and MONTH(data_criacao) =  MONTH(now()) ORDER BY id DESC", 
GetSQLValueString($_SESSION['MM_praca'], "text"));
$relatorio_fechamento_confirma = mysql_query($query_relatorio_fechamento_confirma, $conexao) or die(mysql_error());
$row_relatorio_fechamento_confirma = mysql_fetch_assoc($relatorio_fechamento_confirma);
$totalRows_relatorio_fechamento_confirma = mysql_num_rows($relatorio_fechamento_confirma);
// fim - relatorio_fechamento_confirma

// se o relatório do mês atual já foi gerado... então volta para o site
if($row_relatorio_fechamento_confirma['status'] == 1 or $praca_status == 1){
	header("Location: painel/index.php");
	exit;	
}
// fim - se o relatório do mês atual já foi gerado... então volta para o site

// confirmar
if ( 
(isset($_GET['confirmar']) and $_GET['confirmar']=="sim") and 
($row_usuario['controle_relatorio'] == "Y" or $row_usuario['praca'] == $_SESSION['MM_praca']) and 
($praca_status == 0 and $row_relatorio_fechamento_confirma['status'] == 0) 
){
	
	// update (relatorio_fechamento)
	mysql_select_db($database_conexao, $conexao);
	$updateSQL_relatorio_fechamento = sprintf("
	UPDATE relatorio_fechamento 
	SET status = 1
	WHERE id = %s and status = 0",
	GetSQLValueString($row_relatorio_fechamento_confirma['id'], "int"));
	mysql_select_db($database_conexao, $conexao);
	$Result_relatorio_fechamento_update = mysql_query($updateSQL_relatorio_fechamento, $conexao) or die(mysql_error());
	// fim - update (relatorio_fechamento)
	
	// update (geral_tipo_praca)
	mysql_select_db($database_conexao, $conexao);
	$updateSQL_geral_tipo_praca = sprintf("
	UPDATE geral_tipo_praca 
	SET status = 1
	WHERE praca = %s",
	GetSQLValueString($_SESSION['MM_praca'], "text"));
	mysql_select_db($database_conexao, $conexao);
	$Result_geral_tipo_praca_update = mysql_query($updateSQL_geral_tipo_praca, $conexao) or die(mysql_error());
	// fim - update (geral_tipo_praca)

	// repair_table
	mysql_select_db($database_conexao, $conexao);
	$query_repair_table = "
	REPAIR TABLE 
	agenda,
	auto,
	comunicado,
	comunicado_anexo,
	comunicado_destinatario,
	comunicado_historico,
	contrato,
	da01,
	da03,
	da11,
	da11s9,
	da14,
	da15,
	da37,
	dbct,
	dbcts8,
	dbcts9,
	dc01,
	downloads,
	downloads_grupos,
	downloads_subgrupos,
	dpifs2,
	dpifs8,
	dpifs9,
	dump,
	emails_aviso,
	geral_contrato_alterado,
	geral_credito,
	geral_procedimento_site,
	geral_tipo_banco_de_dados,
	geral_tipo_contrato,
	geral_tipo_distribuicao,
	geral_tipo_ecf,
	geral_tipo_estacao,
	geral_tipo_modulo,
	geral_tipo_modulo_categoria,
	geral_tipo_praca,
	geral_tipo_praca_executor,
	geral_tipo_programa,
	geral_tipo_ramo_atividade,
	geral_tipo_subprograma,
	geral_tipo_versao,
	geral_tipo_visita,
	ibge,
	implantacao_avaliacao_pergunta,
	implantacao_avaliacao_resposta,
	implantacao_pergunta,
	mala_direta,
	mala_direta_anexo,
	mala_direta_destinatario,
	modcon,
	modsuc,
	parametros,
	pm01,
	prospeccao,
	prospeccao_agenda_tipo,
	prospeccao_arquivos,
	prospeccao_concorrente,
	prospeccao_contador,
	prospeccao_contato,
	prospeccao_descricoes,
	prospeccao_formulario,
	prospeccao_participacao,
	prospeccao_perda_participacao,
	prospeccao_perda_pergunta,
	prospeccao_perda_resposta,
	prospeccao_pergunta,
	prospeccao_resposta,
	prospeccao_tipo_status,
	relatorio,
	relatorio_campos,
	relatorio_classificacao_nivel,
	relatorio_contador,
	relatorio_evento,
	relatorio_fechamento,
	relatorio_grupo,
	relatorio_grupo_geral,
	relatorio_grupo_subgrupo,
	relatorio2,
	site_banner_inferior,
	site_banner_principal,
	site_evento,
	site_galeria,
	site_link,
	site_link_foto,
	solicitacao,
	solicitacao_arquivos,
	solicitacao_copy,
	solicitacao_descricoes,
	solicitacao_tempo_gasto,
	solicitacao_tipo_parecer,
	solicitacao_tipo_prioridade,
	solicitacao_tipo_situacao,
	solicitacao_tipo_solicitacao,
	solicitacao2,
	SR_MGMNTCONSTRAINTS,
	SR_MGMNTCONSTRSRCCOLS,
	SR_MGMNTCONSTRTGTCOLS,
	SR_MGMNTINDEXES,
	SR_MGMNTLANG,
	SR_MGMNTLOCKS,
	sr_mgmntlogchg,
	SR_MGMNTTABLES,
	SR_MGMNTVERSION,
	suporte,
	suporte_arquivos,
	suporte_contato,
	suporte_descricoes,
	suporte_formulario,
	suporte_tempo_gasto,
	suporte_tipo_atendimento,
	suporte_tipo_formulario,
	suporte_tipo_parecer,
	suporte_tipo_percepcao,
	suporte_tipo_prioridade,
	suporte_tipo_recomendacao,
	suporte_tipo_status,
	treinamento_pergunta,
	usuarios,
	venda,
	venda_arquivos,
	venda_contato,
	venda_descricoes,
	venda_modulos,
	venda_validade
	";
	$repair_table = mysql_query($query_repair_table, $conexao) or die(mysql_error());
	$row_repair_table = mysql_fetch_assoc($repair_table);
	$totalRows_repair_table = mysql_num_rows($repair_table);
	// fim - repair_table
	mysql_free_result($repair_table);
	
	$fim = "relatorio.php?padrao=sim&tela=digital&relatorio_id_grupo=0&relatorio_id_grupo_subgrupo=0&filtro_geral_praca=".$_SESSION['MM_praca']."&filtro_geral_data_criacao=.".date('01-m-Y')."&filtro_geral_data_criacao_fim=".date('t-m-Y');
	header("Location: $fim");
	exit;
	
}
// fim - confirmar
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link rel="stylesheet" href="css/suporte.css" type="text/css" />
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript"> 
$(document).ready(function() {
	
	<? if (
	(isset($_GET['exibir']) and $_GET['exibir']=="sim") and 
	$totalRows_relatorio_fechamento_confirma > 0 and 
	($row_usuario['controle_relatorio'] == "Y" or $row_usuario['praca'] == $_SESSION['MM_praca']) and 
	$praca_status == 0 and 
	$row_relatorio_fechamento_confirma['status'] == 0 ){ ?>

		window.open('<? echo 'relatorio/'.$row_relatorio_fechamento_confirma['arquivo']; ?>', '_blank', '');
		//window.open('relatorio_fechamento.php', '_top', '');
	
	<? } ?>
	
	$("#confirmar_relatorio").click(function(event) {
		var agree=confirm("Confira os dados do relatório no arquivo PDF aberto automaticamente na pagina ao lado. \nO relatório foi gerado corretamente?");
		if (agree){
			var agree2=confirm("Relatório gerado com sucesso. \nData: <? echo date('d-m-Y H:i:s', strtotime($row_relatorio_fechamento_confirma['data_criacao'])); ?> \nCompetência: <? echo date('m-Y', strtotime($row_relatorio_fechamento_confirma['data'])); ?> \nUsuário Responsável: <? echo $row_relatorio_fechamento_confirma['usuario_responsavel']; ?> \nArquivo: <? echo $row_relatorio_fechamento_confirma['arquivo']; ?> \nPara reimpressão acesse: \nRelatórios > Resultados Mensais > Relatórios Gerenciais Success Sistemas.");
			if (agree2){
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	});
	
	$("#gerar_novo_relatorio").click(function(event) {
		var agree=confirm("Deseja realmente gerar um novo relatório?");
		if (agree){			
			return true;
		} else {
			return false;
		}
	});
	
});	
</script>
<title>Relatório de Fechamento</title>
</head>

<body>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Relatório de Fechamento
		</td>

		<td style="text-align: right">
        Usuário logado: <? echo $row_usuario['nome']; ?> |
        <a href="painel/padrao_sair.php">Sair</a>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align: center">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td align="left" style="font-size: 12px;">
                <span style="color: #FF0004; font-weight: bold; font-size: 16px;">Atenção usuário!</span>
                <div style="line-height: 1.5em;">
                O acesso à área restrita do site está temporariamente bloqueado para sua Praça.
                <br>
                O desbloqueio ocorrerá automaticamente após a geração do Relatório Gerencial Success Sistemas, qual tem como intuito o fechamento do mês anterior.
                <br>
                Favor gerar e efetivar o Relatório clicando no botão &quot;Gerar Relatório Gerencial Success Sistemas&quot;.
                <br>
                Após o processo de efetivação, o relatório poderá ser reimpresso ilimitadamente através do caminho: Relatórios &gt; Resultados Mensais &gt; Relatórios Gerenciais Success Sistemas."
                </div>
            
                <? if($totalRows_relatorio_fechamento_confirma > 0){ ?>
                <br>
                Ver relatório gerado: <strong><a href="relatorio/<? echo $row_relatorio_fechamento_confirma['arquivo']; ?>" target="_blank"><? echo $row_relatorio_fechamento_confirma['arquivo']; ?></a></strong>
                <br><br>
                <? } ?>
                
                <div style="margin-top: 10px;">
                    
					<? if($totalRows_relatorio_fechamento_confirma == 0){ ?>

                        <a href="relatorio.php?padrao=sim&amp;tela=impressao&amp;fechamento=sim&amp;filtro_geral_praca=<? echo $_SESSION['MM_praca']; ?>&amp;filtro_geral_data_criacao=<? echo date('01-m-Y', strtotime('-1 months', strtotime(date('Y-m-d')))); ?>&amp;filtro_geral_data_criacao_fim=<? echo date('t-m-Y', strtotime('-1 months', strtotime(date('Y-m-d')))); ?>" class="botao_geral2">Gerar Relatório Gerencial Success Sistemas</a>

                    <? } else { ?>
                    
                        <a href="relatorio.php?padrao=sim&amp;tela=impressao&amp;fechamento=sim&amp;filtro_geral_praca=<? echo $_SESSION['MM_praca']; ?>&amp;filtro_geral_data_criacao=<? echo date('01-m-Y', strtotime('-1 months', strtotime(date('Y-m-d')))); ?>&amp;filtro_geral_data_criacao_fim=<? echo date('t-m-Y', strtotime('-1 months', strtotime(date('Y-m-d')))); ?>" class="botao_geral2" id="gerar_novo_relatorio">Gerar novo Relatório Gerencial Success Sistemas</a>

                        <div>
                        <a href="relatorio_fechamento.php?confirmar=sim" class="botao_geral2" id="confirmar_relatorio">Efetivar Relatório Gerencial Success Sistemas</a>
                        </div>

                    <? } ?>
                    
                	<a href="painel/padrao_sair.php" class="botao_geral2">Voltar</a>
                    
                </div>
                </td>
            </tr>
            </table>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Praça: </span>
		<? echo $_SESSION['MM_praca']; ?>
        </td>
	</tr>
</table>
</div>

</body>
</html>
<?php
mysql_free_result($usuario);
mysql_free_result($relatorio_fechamento_confirma);
?>