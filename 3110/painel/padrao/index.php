<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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

require_once('../parametros.php');

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

// dump
mysql_select_db($database_conexao, $conexao);
$query_dump = "SELECT * FROM dump";
$dump = mysql_query($query_dump, $conexao) or die(mysql_error());
$row_dump = mysql_fetch_assoc($dump);
$totalRows_dump = mysql_num_rows($dump);
// fim - dump

// função que limita caracteres
function limita_caracteres($string, $tamanho, $encode = 'UTF-8') {
    if( strlen($string) > $tamanho ){
        $string = mb_substr($string, 0, $tamanho, $encode);
	}
    return $string;
}
// fim - função que limita caracteres

// comunicado_listar
mysql_select_db($database_conexao, $conexao);
$query_comunicado_listar = sprintf("
SELECT comunicado_destinatario.*, 
comunicado.data_criacao, comunicado.assunto, comunicado.texto, comunicado.prioridade, comunicado.data_limite, comunicado.tipo, comunicado.data_reenvio, 
(SELECT COUNT(comunicado_destinatario.IdComunicadoDestinatario) FROM comunicado_destinatario WHERE comunicado_destinatario.IdComunicado = comunicado.IdComunicado and IdComunicadoHistorico IS NULL and comunicado_destinatario.responsavel = 0) AS comunicado_destinatario_contador, 
usuarios.nome AS usuario_nome 
FROM comunicado_destinatario 
LEFT JOIN comunicado ON comunicado.IdComunicado = comunicado_destinatario.IdComunicado 
LEFT JOIN usuarios ON usuarios.IdUsuario = comunicado.IdUsuario 
WHERE comunicado_destinatario.IdUsuario = %s and comunicado_destinatario.lido = 0 and comunicado_destinatario.IdComunicadoHistorico IS NULL  
ORDER BY comunicado.data_reenvio IS NULL ASC, comunicado.data_criacao DESC, comunicado.IdComunicado DESC", 
GetSQLValueString($row_usuario['IdUsuario'], "int"));
$comunicado_listar = mysql_query($query_comunicado_listar, $conexao) or die(mysql_error());
$row_comunicado_listar = mysql_fetch_assoc($comunicado_listar);
$totalRows_comunicado_listar = mysql_num_rows($comunicado_listar);
//if($totalRows_comunicado_listar ==0){header("Location: ../index.php"); exit;}
// fim - comunicado_listar

// reclamacao_aberto_suporte
// $where
$where = "1=1";
if($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y"){
	$where .= NULL;
} else {
	$where .= " and suporte.praca = '".$row_usuario['praca']."' ";
}
// fim - $where

mysql_select_db($database_conexao, $conexao);
$query_reclamacao_aberto_suporte = "
SELECT id, empresa, id_usuario_responsavel, data_suporte, situacao, praca, contrato, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_envolvido) as usuario_envolvido 
FROM suporte 
WHERE $where and tipo_suporte = 'r' and status_flag <> 'f' 
ORDER BY praca ASC, id DESC
";
$reclamacao_aberto_suporte = mysql_query($query_reclamacao_aberto_suporte, $conexao) or die(mysql_error());
$row_reclamacao_aberto_suporte = mysql_fetch_assoc($reclamacao_aberto_suporte);
$totalRows_reclamacao_aberto_suporte = mysql_num_rows($reclamacao_aberto_suporte);
$reclamacao_aberto_suporte_array = NULL;
// fim - reclamacao_aberto_suporte

// usuarios_aniversario
mysql_select_db($database_conexao, $conexao);
$query_usuarios_aniversario = "
SELECT IdUsuario, nome, praca, aniversario, telefone 
FROM usuarios
WHERE 

status = 1 and 
aniversario IS NOT NULL and 
CASE
	WHEN WEEKDAY( CONCAT( YEAR(NOW()),'-', MONTH(aniversario),'-', DAY(aniversario) ) ) = 6 THEN WEEKOFYEAR( CONCAT( YEAR(NOW()),'-', MONTH(aniversario),'-', DAY(aniversario) ) )+1 = WEEKOFYEAR( NOW()+1 ) 
	ELSE WEEKOFYEAR( CONCAT( YEAR(NOW()),'-', MONTH(aniversario),'-', DAY(aniversario) ) ) = WEEKOFYEAR( NOW() ) 
END
ORDER BY aniversario ASC, nome DESC
";
$usuarios_aniversario = mysql_query($query_usuarios_aniversario, $conexao) or die(mysql_error());
$row_usuarios_aniversario = mysql_fetch_assoc($usuarios_aniversario);
$totalRows_usuarios_aniversario = mysql_num_rows($usuarios_aniversario);
$usuarios_aniversario_array = NULL;
// fim - usuarios_aniversario
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="pt-BR" />
<meta name="language" content="pt-BR" />
<meta name="robots" content="noindex,nofollow" />
<meta name="author" content="Juliano Martins - (34) 8805 3396" />
<meta name="copyright" content=" Copyright © Success Sistemas - Todos os direitos reservados." />
<title>Área de Parceiro - Success Sistemas</title>
<link href="../../css/guia_painel.css" rel="stylesheet" type="text/css">
<style>
.tabela_inicio table {
	
}
.tabela_inicio thead th {
	padding: 3px 3px 3px 3px;
	background-color: #E6F2FD;
	color: #2E6E9E;
	font-size: 11px;
	border-bottom: 1px solid #A6C9E2;
	border-left: 1px solid #A6C9E2;
	border-right: 1px solid #A6C9E2;
}
.tabela_inicio tbody td {
	padding: 2px;
	font-size: 11px;
	border-bottom: 1px solid #A6C9E2;
	border-left: 1px solid #A6C9E2;
	border-right: 1px solid #A6C9E2;
	font-family: Lucida Grande, Lucida Sans, Arial, sans-serif; 
}
.tabela_inicio .linha1 {
	/* background-color: #FFFFFF; */
}
.tabela_inicio .linha2 {
	/* background-color: #EEEEEE; */
}
.titulo_inicio { /* caixa titulo */
	padding: 3px;
	border: 1px solid #4297d7; 
	background: #5c9ccc url(../../imagens/jqgrid/ui-bg_gloss-wave_55_5c9ccc_500x100.png) 50% 50% repeat-x; 
	color: #ffffff; 
	font-weight: bold;
	font-size: 11px;
}
</style>

<script src="../../js/jquery.js"></script>

<link rel="stylesheet" href="../../css/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="../../js/thickbox.js"></script>

<script>
$(document).ready(function() {

<? if(isset($_GET['acao']) and $_GET['acao']=='sucesso'){ ?>

	tb_show("Início","inicio.php?placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true","");

<? } ?>

});
</script>
</head>
<body>

<div class="cabecalho"><? require_once('../padrao_cabecalho.php'); ?></div>

<!-- corpo -->
<div class="corpo">
	<div class="texto"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            
                <td class="padrao_esquerda"><? require_once('../padrao_esquerda.php'); ?></td>
                                
                <td class="padrao_centro">                
                
                <!-- titulo -->
                <div class="titulo">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="left">Área do Parceiro</td>
                        <td align="right">&nbsp;</td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="index.php">Página inicial</a> &gt;&gt; Área do Parceiro</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
                	<div style="margin-bottom: 15px;">Seja bem vindo parceiro Success a sua área restrita.</div>
					
					<div style="margin-bottom: 15px; font-weight: bold;">Última atualização do site: <? echo date('d-m-Y H:i', strtotime($row_dump['data'])); ?></div>

                    
                    <!-- comunicado_listar -->
                  	<div class="titulo_inicio" style="margin-top: 20px;">Comunicados (<? echo $totalRows_comunicado_listar; ?>):</div>
    
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tabela_inicio">
                    <? if($totalRows_comunicado_listar > 0){ ?>
                    
                    <thead>
                    <tr>
						<th width="50">Núm</th>
                        <th width="120">Data Envio</th>
                        <th width="130">Remetente</th>
                        <th>Assunto</th>
                        <th width="80">Tipo</th>
                        <th width="80">Prioridade</th>
                        <th width="20">&nbsp;</th>
                      </tr>
                    </thead>
                    
                    <tbody>
                    <? $comunicado_listar_contador = 0; ?>
                    <?php do { ?>
                        <? $comunicado_listar_contador = $comunicado_listar_contador+1; ?>
                        <tr class="<? if (($comunicado_listar_contador % 2)==0){echo "linha1";}else{echo "linha2";} ?>">
							<td><? echo $row_comunicado_listar['IdComunicado']; ?></td>
                            <td><? echo date('d/m/Y H:i', strtotime($row_comunicado_listar['data_criacao'])); ?></td>
                            <td title="<? echo $row_comunicado_listar['usuario_nome']; ?>"><? echo  limita_caracteres($row_comunicado_listar['usuario_nome'], 22); ?></td>
                            <td title="<? echo $row_comunicado_listar['assunto']; ?>"><?php if($row_comunicado_listar['data_reenvio'] <> NULL){ ?>*<? } ?><? echo limita_caracteres($row_comunicado_listar['assunto'], 48); ?></td>
                            <td><? if($row_comunicado_listar['tipo'] == "m"){ ?><span style="color: red; font-weight: bold;">Memorando</span><? } else { ?>Comunicado<? } ?></td>
                            <td><? echo $row_comunicado_listar['prioridade']; ?></td>
                            <td align="right"><a href="../padrao/comunicado_detalhe.php?IdComunicado=<? echo $row_comunicado_listar['IdComunicado']; ?>&janela=index&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true" class="thickbox"><img src="../../imagens/visualizar.png" /></a></td>
                        </tr>
                    <?php } while ($row_comunicado_listar = mysql_fetch_assoc($comunicado_listar)); ?>
                    </tbody>
                    
                    <? } else { ?>
                    <tbody>
                        <tr>
                            <td class="linha2">Nenhum comunicado até o momento.</td>
                        </tr>
                    </tbody>
                    
                    <? } ?>
                    </table>
                    <!-- fim - comunicado_listar -->
                    
                                    
                	<!-- reclamacao_aberto_suporte -->
                    <div class="titulo_inicio" style="margin-top: 20px;">Reclamações em andamento (<? echo $totalRows_reclamacao_aberto_suporte; ?>):</div>

                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tabela_inicio">
                    <? if($totalRows_reclamacao_aberto_suporte > 0){ ?>
                    
                    <thead>
                    <tr>
                    	<th width="60">Núm</th>
                        <? if($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y"){ ?>
                        <th width="85">Praça</th>
                        <? } ?>
                        <th>Cliente</th>
                        <th width="120">Responsável</th>
                        <th width="90">Início</th>
                        <th  width="100">Situação</th>
                        <th width="20">&nbsp;</th>
                    </tr>
                    </thead>
                    
                    <tbody>
                    <? $reclamacao_aberto_suporte_contador = 0; ?>
                    <?php do { ?>
                        <? $reclamacao_aberto_suporte_contador = $reclamacao_aberto_suporte_contador+1; ?>
                        <? $reclamacao_aberto_suporte_array[] = array('id' => $row_reclamacao_aberto_suporte['id'], 'contrato' => $row_reclamacao_aberto_suporte['contrato'], 'praca' => $row_reclamacao_aberto_suporte['praca'], 'empresa' => limita_caracteres(utf8_encode($row_reclamacao_aberto_suporte['empresa']), 30), 'situacao' => $row_reclamacao_aberto_suporte['situacao']); ?>
                        <tr class="<? if (($reclamacao_aberto_suporte_contador % 2)==0){echo "linha1";}else{echo "linha2";} ?>">
                            <td><? echo $row_reclamacao_aberto_suporte['id']; ?></td>
							<? if($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y"){ ?>
                            <td><? echo limita_caracteres($row_reclamacao_aberto_suporte['praca'], 13); ?></td>
                            <? } ?>
                            <td><? echo limita_caracteres(utf8_encode($row_reclamacao_aberto_suporte['empresa']), 35); ?></td>
                            <td><? echo limita_caracteres($row_reclamacao_aberto_suporte['usuario_responsavel'], 15); ?></td>
                            <td><? echo date('d-m-Y', strtotime($row_reclamacao_aberto_suporte['data_suporte'])); ?></td>
                            <td><? echo $row_reclamacao_aberto_suporte['situacao']; ?></td>
                            <td align="right"><a href="../../suporte_editar.php?id_suporte=<? echo $row_reclamacao_aberto_suporte['id']; ?>&padrao=sim" target="_blank"><img src="../../imagens/visualizar.png" /></a></td>
                        </tr>
                    <?php } while ($row_reclamacao_aberto_suporte = mysql_fetch_assoc($reclamacao_aberto_suporte)); ?>
                    </tbody>
                    
                    <? } else { ?>
                    <tbody>
                        <tr>
                            <td class="linha2">Nenhuma reclamação até o momento.</td>
                        </tr>
                    </tbody>
                    
                    <? } ?>
                    </table>

                    <? if($totalRows_reclamacao_aberto_suporte > 0){ ?>
                                                
                    <? } ?>
                    <!-- fim - reclamacao_aberto_suporte -->

                    
                	<!-- usuarios_aniversario -->
                    <div class="titulo_inicio" style="margin-top: 20px;">Aniversariantes da semana (<? echo $totalRows_usuarios_aniversario; ?>):</div>

                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tabela_inicio" style="margin-bottom: 20px;">
                    <? if($totalRows_usuarios_aniversario > 0){ ?>
                    
                    <thead>
                    <tr>
                        <th width="300">Usuário</th>
                        <th width="150">Praça</th>
                        <th width="150">Data</th>
                        <th>Telefone</th>
                        <? if($row_usuario['controle_comunicado'] == "Y"){ ?>
                        <th width="20">&nbsp;</th>
                        <? } ?>
                    </tr>
                    </thead>
                    
                    <tbody>
                    <? $usuarios_aniversario_contador = 0; ?>
                    <?php do { ?>
                        <? $usuarios_aniversario_contador = $usuarios_aniversario_contador+1; ?>
                        <tr class="<? if (($usuarios_aniversario_contador % 2)==0){echo "linha1";}else{echo "linha2";} ?>">
                            <td <? if((date('m-d', strtotime($row_usuarios_aniversario['aniversario']))) == (date('m-d'))){ ?>style="color: green; font-weight: bold;"<? } ?>><? echo limita_caracteres($row_usuarios_aniversario['nome'], 35); ?></td>
                            <td><? echo limita_caracteres($row_usuarios_aniversario['praca'], 15); ?></td>
                            <td><? echo date('d-m-Y', strtotime($row_usuarios_aniversario['aniversario'])); ?></td>
                            <td><? echo $row_usuarios_aniversario['telefone']; ?></td>
                            <? if($row_usuario['controle_comunicado'] == "Y"){ ?>
                            <td align="right"><a href="../comunicado/tabela.php?destinatario=<? echo $row_usuarios_aniversario['IdUsuario']; ?>&aniversario=<? echo $row_usuarios_aniversario['aniversario']; ?>&padrao=sim"><img src="../../imagens/carta.png" /></a></td>
                            <? } ?>
                        </tr>
                    <?php } while ($row_usuarios_aniversario = mysql_fetch_assoc($usuarios_aniversario)); ?>
                    </tbody>
                    
                    <? } else { ?>
                    <tbody>
                        <tr>
                            <td class="linha2">Nenhum aniversariante disponível.</td>
                        </tr>
                    </tbody>
                    
                    <? } ?>
                    </table>

                    <? if($totalRows_usuarios_aniversario > 0){ ?>
                                                
                    <? } ?>
                    <!-- fim - usuarios_aniversario -->

                </div>

                </td>
                
            </tr>
        </table>
  	</div>
</div>
<!-- fim - corpo -->

<div class="rodape"><? require_once('../padrao_rodape.php'); ?></div>

</body>
</html>
<?php 
mysql_free_result($usuario);
mysql_free_result($dump);
mysql_free_result($comunicado_listar);
mysql_free_result($reclamacao_aberto_suporte);
mysql_free_result($usuarios_aniversario);
?>