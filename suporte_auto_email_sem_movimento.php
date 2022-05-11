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

require_once('suporte_funcao_update.php');
require_once('emails.php');
require_once('funcao_dia_util.php');

// parametros
mysql_select_db($database_conexao, $conexao);
$query_parametros = "SELECT * FROM parametros";
$parametros = mysql_query($query_parametros, $conexao) or die(mysql_error());
$row_parametros = mysql_fetch_assoc($parametros);
$totalRows_parametros = mysql_num_rows($parametros);
// fim - parametros

// suporte
mysql_select_db($database_conexao, $conexao);
$query_suporte = sprintf("
SELECT 
id, titulo, empresa, contrato, praca, data_suporte, situacao, id_usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_envolvido) as usuario_envolvido 
FROM suporte
WHERE suporte.tipo_suporte = 'r' and suporte.status_flag = 'a'
ORDER BY suporte.id ASC");
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - suporte

$prazo_suporte_auto_email_dias = $row_parametros['suporte_auto_email_sem_movimento_dias'];
$prazo_suporte_auto_email_segundos = $prazo_suporte_auto_email_dias * 86400;
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
Suportes: <? echo $totalRows_suporte; ?>
</div>

<? if($totalRows_suporte > 0 and $prazo_suporte_auto_email_dias > 0){ ?>

	<?php do { ?>
    
		<?
        // suporte_descricoes
        mysql_select_db($database_conexao, $conexao);
        $query_suporte_descricoes = sprintf("
                                               SELECT id, data 
                                               FROM suporte_descricoes 
                                               WHERE id_suporte = %s and id_usuario_responsavel <> '' 
                                               ORDER BY id DESC LIMIT 1", 
                                               GetSQLValueString($row_suporte['id'], "int"));
        $suporte_descricoes = mysql_query($query_suporte_descricoes, $conexao) or die(mysql_error());
        $row_suporte_descricoes = mysql_fetch_assoc($suporte_descricoes);
        $totalRows_suporte_descricoes = mysql_num_rows($suporte_descricoes);
        // fim - suporte_descricoes
        ?>
        
        <? if($totalRows_suporte_descricoes > 0){ ?>
        <div style="border: 1px solid #CCC; margin: 5px; padding: 5px;">
        
            <!-- dados -->
            <?
            $previsao_geral = $row_suporte_descricoes['data'];
            $previsao_geral_segundos = strtotime($previsao_geral);
            ?>
        
            <? $previsao_limite = proximoDiaUtil(date('Y-m-d H:i:s', $previsao_geral_segundos + $prazo_suporte_auto_email_segundos)); ?>
            <? $previsao_limite_segundos = strtotime($previsao_limite); ?>
            
            <? $previsao_geral_passados_dias = (($data_atual_segundos - $previsao_limite_segundos) + $prazo_suporte_auto_email_segundos) / 86400; ?>
        
            Número da suporte: <strong><?php echo $id = $row_suporte['id']; ?></strong>
            <br>
            Praça: <strong><? echo $row_suporte['praca']; ?></strong>
            <br>
            Número da descrição: <strong><?php echo $row_suporte_descricoes['id']; ?></strong>
            <br>
            Data da suporte: <? echo date('d-m-Y  H:i', strtotime($row_suporte['data_suporte'])); ?>
            <br>
            Situação: <strong><? echo $row_suporte['situacao']; ?></strong>
            <br>
            Data último movimento: <? echo date('d-m-Y  H:i:s', $previsao_geral_segundos); ?>
            <br>
            Previsão limite: <? echo date('d-m-Y  H:i:s', $previsao_limite_segundos); ?>
            <br>
            Dias passados: <strong><? echo $previsao_geral_passados_dias; ?></strong>
            <br>
            <!-- fim - dados -->
            
            
            <!-- email_para -->
            <?
            $email_para = $row_suporte['usuario_responsavel'];
            ?> 
            <!-- fim - email_para -->
            
              
            <!-- fora do prazo -->
            <? if ($previsao_geral_passados_dias >= $prazo_suporte_auto_email_dias){ ?>
            
                <!-- atualiza suporte/envia e-mail -->
                <div style="color: #09F;">
                Envia um e-mail para: <? echo $email_para; ?>
                <br>
                Este suporte completou mais de <? echo $prazo_suporte_auto_email_dias; ?> dia(s) sem movimento. Por favor verifique.
                </div>
                <?
                // atualiza suporte
                $dados_suporte = array(
                        "situacao" => $row_suporte['situacao']
                );	
                $dados_suporte_descricao = array(
                        "id_suporte" => $id,
                        "id_usuario_responsavel" => "",
                        "descricao" => "Esta suporte completou mais de ".$prazo_suporte_auto_email_dias." dia(s) sem movimento. Por favor verifique.",
                        "data" => date("Y-m-d H:i:s"),
                        "tipo_postagem" => "Aviso de suporte sem movimento"
                );	
                funcao_suporte_update($id, $dados_suporte, $dados_suporte_descricao);
                // fim - atualiza suporte
                
                // função que envia e-mail
                email_suporte_sem_movimento($id, $email_para, $dados_suporte_descricao['tipo_postagem'], $dados_suporte_descricao['descricao']);
                // fim - função que envia e-mail
                ?>
                <!-- fim - atualiza suporte/envia e-mail -->
                
            <? } ?>
            <!-- fora do prazo -->
        
        </div>  
        <? } ?>
        
        <? mysql_free_result($suporte_descricoes); ?>
    
    <?php } while ($row_suporte = mysql_fetch_assoc($suporte)); ?>
    
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
mysql_free_result($suporte);
mysql_free_result($parametros);
?>