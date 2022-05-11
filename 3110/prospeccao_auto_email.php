<?
if($_SERVER["HTTP_X_CRON_AUTH"] != "3d5a01b606de5e79d6bcdaac8db316a0"){
	die("Acesso nao Autorizado");
}
?>
<?php require_once('Connections/conexao.php'); ?>
<?php
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

require_once('prospeccao_funcao_update.php');
require_once('emails.php');
require_once('funcao_dia_util.php');

// parametros
mysql_select_db($database_conexao, $conexao);
$query_parametros = "SELECT * FROM parametros";
$parametros = mysql_query($query_parametros, $conexao) or die(mysql_error());
$row_parametros = mysql_fetch_assoc($parametros);
$totalRows_parametros = mysql_num_rows($parametros);
// fim - parametros

// prospeccao_agenda
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_agenda = sprintf("
SELECT 
prospeccao.id, prospeccao.status, prospeccao.situacao, prospeccao.data_prospeccao, prospeccao.id_usuario_responsavel, prospeccao.praca,  
prospeccao.ativo_passivo, prospeccao.uf, prospeccao.cidade, prospeccao.nome_razao_social, prospeccao.quantidade_agendado, prospeccao.status_flag, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = prospeccao.id_usuario_responsavel) as usuario_responsavel, 

agenda.id_agenda AS prospeccao_agenda_id_agenda, 
agenda.data AS prospeccao_agenda_data, 
agenda.data_inicio AS prospeccao_agenda_data_inicio, 
agenda.descricao AS prospeccao_agenda_descricao, 
agenda.status AS prospeccao_agenda_status

FROM agenda
LEFT JOIN prospeccao ON agenda.id_prospeccao = prospeccao.id
WHERE prospeccao.status_flag = 'a' and agenda.status = 'a' 
ORDER BY agenda.data ASC");
$prospeccao_agenda = mysql_query($query_prospeccao_agenda, $conexao) or die(mysql_error());
$row_prospeccao_agenda = mysql_fetch_assoc($prospeccao_agenda);
$totalRows_prospeccao_agenda = mysql_num_rows($prospeccao_agenda);
// fim - prospeccao_agenda

$prazo_prospeccao_auto_email_dias = $row_parametros['prospeccao_auto_email_dias'];
$prazo_prospeccao_auto_email_segundos = $prazo_prospeccao_auto_email_dias * 86400;
$data_atual_segundos = strtotime("now");
$email_para = "";
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<style>
.cor_black {
	padding: 1px;
}
.cor_orange {
	color: #FF9900; !important;
	font-weight:bold;
	padding: 1px; !important;
}
.cor_red {
	color: #FF0000; !important;
	font-weight:bold;
	padding: 1px; !important;
}
</style>
</head>

<body style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; margin: 0px;"> 

<div style="border: 1px solid #000; padding: 5px; margin: 5px">
Prospecções: <? echo $totalRows_prospeccao_agenda; ?>
</div>

<? if($totalRows_prospeccao_agenda > 0 and $prazo_prospeccao_auto_email_dias > 0){ ?>
<?php do { ?>
<? $envia_email = 0; ?>
<div style="border: 1px solid #CCC; margin: 5px; padding: 5px;">

	<!-- dados -->
    <?
    $previsao_geral = $row_prospeccao_agenda['prospeccao_agenda_data'];
    $previsao_geral_segundos = strtotime($previsao_geral);
	?>

	<? $previsao_limite = proximoDiaUtil(date('Y-m-d H:i:s', $previsao_geral_segundos + $prazo_prospeccao_auto_email_segundos)); ?>
    <? $previsao_limite_segundos = strtotime($previsao_limite); ?>
    
    <? $previsao_geral_passados_dias = (($data_atual_segundos - $previsao_limite_segundos) + $prazo_prospeccao_auto_email_segundos) / 86400; ?>

    Número da prospecção: <strong><?php echo $id = $row_prospeccao_agenda['id']; ?></strong>
	<br>
    Praça: <strong><? echo $row_prospeccao_agenda['praca']; ?></strong>
    <br>
    Data da prospecção: <? echo date('d-m-Y  H:i', strtotime($row_prospeccao_agenda['data_prospeccao'])); ?>
    <br>
    Situação: <strong><? echo $row_prospeccao_agenda['situacao']; ?></strong>
    <br>
    Número do Agendamento: <strong><?php echo $id_agenda = $row_prospeccao_agenda['prospeccao_agenda_id_agenda']; ?></strong>
    <br>
	Agendamento Fim: <? echo date('d-m-Y  H:i:s', $previsao_geral_segundos); ?>
	<br>
    Previsão limite: <? echo date('d-m-Y  H:i:s', $previsao_limite_segundos); ?>
    <br>
	Dias passados: <strong><? echo $previsao_geral_passados_dias; ?></strong>
	<br>
	<!-- fim - dados -->
    
    
    <!-- email_para -->
	<?
    $email_para = $row_prospeccao_agenda['usuario_responsavel'];
    ?> 
    <!-- fim - email_para -->
    
      
    <!-- fora do prazo -->
    <? if ($previsao_geral_passados_dias >= $prazo_prospeccao_auto_email_dias){ ?>
    
        <!-- atualiza prospecção/envia e-mail -->
        <div style="color: #09F;">
        Envia um e-mail para: <? echo $email_para; ?>
        <br>
        Esta prospecção completou mais de <? echo $row_parametros['prospeccao_auto_email_dias']; ?> dia(s) de agendamento em aberto atrasado. Por favor verifique.
        </div>
        <?
        // atualiza prospecção
        $dados_prospeccao = array(
                "situacao" => $row_prospeccao_agenda['situacao']
        );	
        $dados_prospeccao_descricao = array(
                "id_prospeccao" => $id,
                "id_usuario_responsavel" => "",
                "descricao" => "Esta prospecção completou mais de ".$prazo_prospeccao_auto_email_dias." dia(s) de agendamento em aberto atrasado. Por favor verifique.",
                "data" => date("Y-m-d H:i:s"),
                "tipo_postagem" => "Aviso de agendamento de prospecção atrasado"
        );	
        funcao_prospeccao_update($id, $dados_prospeccao, $dados_prospeccao_descricao);
        // fim - atualiza prospecção
        
        // função que envia e-mail
        email_prospeccao_agenda_atraso($id, $id_agenda, $email_para, $dados_prospeccao_descricao['tipo_postagem'], $dados_prospeccao_descricao['descricao']);
        // fim - função que envia e-mail
        ?>
        <!-- fim - atualiza prospecção/envia e-mail -->
        
    <? } ?>
    <!-- fora do prazo -->

</div>  
<?php } while ($row_prospeccao_agenda = mysql_fetch_assoc($prospeccao_agenda)); ?>
<? } ?>

<?
// insert - auto *****************************************************************
$insertSQL_auto = sprintf("
INSERT INTO auto (titulo, data, ip) 
VALUES (%s, %s, %s)", 
GetSQLValueString(basename($_SERVER['PHP_SELF']), "text"), 
GetSQLValueString(date('Y-m-d H:i:s'), "date"),
GetSQLValueString(basename($_SERVER["REMOTE_ADDR"]), "text"));
mysql_select_db($database_conexao, $conexao);
$Result_auto = mysql_query($insertSQL_auto, $conexao) or die(mysql_error());
// fim - insert - auto ***********************************************************
?>
</body>
</html>
<?php
mysql_free_result($prospeccao_agenda);
mysql_free_result($parametros);
?>