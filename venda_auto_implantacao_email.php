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

require_once('venda_funcao_update.php');
require_once('emails.php');
require_once('funcao_dia_util.php');

// parametros
mysql_select_db($database_conexao, $conexao);
$query_parametros = "SELECT * FROM parametros";
$parametros = mysql_query($query_parametros, $conexao) or die(mysql_error());
$row_parametros = mysql_fetch_assoc($parametros);
$totalRows_parametros = mysql_num_rows($parametros);
// fim - parametros

// venda_agenda_implantacao
mysql_select_db($database_conexao, $conexao);
$query_venda_agenda_implantacao = sprintf("
SELECT 
venda.id, venda.status, venda.situacao, venda.data_venda, venda.id_usuario_responsavel, 
venda.empresa, venda.quantidade_agendado_implantacao, venda.quantidade_agendado_implantacao, venda.status_flag, 
venda.id_prospeccao, venda.contrato, venda.praca, venda.data_inicio, venda.data_fim, venda.observacao, 
(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel, 

agenda.id_agenda AS venda_agenda_implantacao_id_agenda, 
agenda.data AS venda_agenda_implantacao_data, 
agenda.data_inicio AS venda_agenda_implantacao_data_inicio, 
agenda.descricao AS venda_agenda_implantacao_descricao, 
agenda.status AS venda_agenda_implantacao_status

FROM agenda 
LEFT JOIN venda ON agenda.id_venda_implantacao = venda.id
WHERE venda.status_flag = 'a' and agenda.status = 'a' 
ORDER BY agenda.data ASC");
$venda_agenda_implantacao = mysql_query($query_venda_agenda_implantacao, $conexao) or die(mysql_error());
$row_venda_agenda_implantacao = mysql_fetch_assoc($venda_agenda_implantacao);
$totalRows_venda_agenda_implantacao = mysql_num_rows($venda_agenda_implantacao);
// fim - venda_agenda_implantacao

$prazo_venda_auto_email_dias = $row_parametros['venda_auto_email_dias'];
$prazo_venda_auto_email_segundos = $prazo_venda_auto_email_dias * 86400;
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
Vendas: <? echo $totalRows_venda_agenda_implantacao; ?>
</div>

<? if($totalRows_venda_agenda_implantacao > 0){ ?>
<?php do { ?>
<? $envia_email = 0; ?>
<div style="border: 1px solid #CCC; margin: 5px; padding: 5px;">

	<!-- dados -->
    <?
    $previsao_geral = $row_venda_agenda_implantacao['venda_agenda_implantacao_data'];
    $previsao_geral_segundos = strtotime($previsao_geral);
	?>

	<? $previsao_limite = proximoDiaUtil(date('Y-m-d H:i:s', $previsao_geral_segundos + $prazo_venda_auto_email_segundos)); ?>
    <? $previsao_limite_segundos = strtotime($previsao_limite); ?>
    
    <? $previsao_geral_passados_dias = (($data_atual_segundos - $previsao_limite_segundos) + $prazo_venda_auto_email_segundos) / 86400; ?>

    Número da venda (implantação): <strong><?php echo $id = $row_venda_agenda_implantacao['id']; ?></strong>
    <br>
    Praça: <strong><?php echo $row_venda_agenda_implantacao['praca']; ?></strong>
	<br>
    Data da venda (implantação): <? echo date('d-m-Y  H:i', strtotime($row_venda_agenda_implantacao['data_venda'])); ?>
    <br>
    Situação: <strong><? echo $row_venda_agenda_implantacao['situacao']; ?></strong>
    <br>
    Número do Agendamento: <strong><?php echo $id_agenda = $row_venda_agenda_implantacao['venda_agenda_implantacao_id_agenda']; ?></strong>
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
    $email_para = $row_venda_agenda_implantacao['usuario_responsavel'];
    ?> 
    <!-- fim - email_para -->
    
      
    <!-- fora do prazo -->
    <? if ($previsao_geral_passados_dias >= $prazo_venda_auto_email_dias){ ?>
    
        <!-- atualiza venda (implantação)/envia e-mail -->
        <div style="color: #09F;">
        Envia um e-mail para: <? echo $email_para; ?>
        <br>
        Esta venda (implantação) completou mais de 24 horas de agendamento em aberto atrasado. Por favor verifique.
        </div>
        <?
        // atualiza venda (implantação)
        $dados_venda = array(
                "situacao" => $row_venda_agenda_implantacao['situacao']
        );	
        $dados_venda_descricao = array(
                "id_venda" => $id,
                "id_usuario_responsavel" => "",
                "descricao" => "Esta venda (implantação) completou mais de 24 horas de agendamento em aberto atrasado. Por favor verifique.",
                "data" => date("Y-m-d H:i:s"),
                "tipo_postagem" => "Aviso de agendamento de venda (implantação) atrasado"
        );	
        funcao_venda_update($id, $dados_venda, $dados_venda_descricao);
        // fim - atualiza venda (implantação)
        
        // função que envia e-mail
        email_venda_agenda_implantacao_atraso($id, $id_agenda, $email_para, $dados_venda_descricao['tipo_postagem'], $dados_venda_descricao['descricao']);
        // fim - função que envia e-mail
        ?>
        <!-- fim - atualiza venda (implantação)/envia e-mail -->
        
    <? } ?>
    <!-- fora do prazo -->

</div>  
<?php } while ($row_venda_agenda_implantacao = mysql_fetch_assoc($venda_agenda_implantacao)); ?>
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
mysql_free_result($venda_agenda_implantacao);
mysql_free_result($parametros);
?>