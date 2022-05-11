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

// parametros
mysql_select_db($database_conexao, $conexao);
$query_parametros = "SELECT * FROM parametros";
$parametros = mysql_query($query_parametros, $conexao) or die(mysql_error());
$row_parametros = mysql_fetch_assoc($parametros);
$totalRows_parametros = mysql_num_rows($parametros);
// fim - parametros

// venda
mysql_select_db($database_conexao, $conexao);
$query_venda = sprintf("
							 SELECT id, contrato, empresa  
							 FROM venda 
							 WHERE 
							 codigo_empresa IS NULL						 
							 ORDER BY id ASC");
$venda = mysql_query($query_venda, $conexao) or die(mysql_error());
$row_venda = mysql_fetch_assoc($venda);
$totalRows_venda = mysql_num_rows($venda);
// fim - venda

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
</head>

<body style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; margin: 0px;">
<? if($totalRows_venda > 0){ ?>   
<?php do { ?>
<div style="border: 1px solid #CCC; margin: 5px; padding: 5px;">

	<!-- dados -->
    Número da venda: <?php echo $id = $row_venda['id'];; ?>
	<br>
	Contrato: <?php echo $colname_contrato = $row_venda['contrato']; ?>
    <br>
    Empresa: <?php echo $row_venda['empresa']; ?>
    <br>
	<!-- fim - dados -->
    
    
    <!-- consulta 'DA37s9' / 'DA01s9' -->
    <?	
	// manutencao_dados
	mysql_select_db($database_conexao, $conexao);
	$query_manutencao_dados = sprintf("
	SELECT codigo17, cliente17 	
	FROM da37 
	WHERE da37.codigo17 = %s and da37.sr_deleted <> 'T'", 
	GetSQLValueString($colname_contrato, "text"));
	$manutencao_dados = mysql_query($query_manutencao_dados, $conexao) or die(mysql_error());
	$row_manutencao_dados = mysql_fetch_assoc($manutencao_dados);
	$totalRows_manutencao_dados = mysql_num_rows($manutencao_dados);
	mysql_free_result($manutencao_dados);
	// fim - manutencao_dados	
	?>
    
    <?
	// empresa_dados
	mysql_select_db($database_conexao, $conexao);
	$query_empresa_dados = sprintf("
	SELECT codigo1, nome1 
	FROM da01 
	WHERE codigo1 = %s and da01.sr_deleted <> 'T'", 
	GetSQLValueString($row_manutencao_dados['cliente17'], "text"));
	$empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
	$row_empresa_dados = mysql_fetch_assoc($empresa_dados);
	$totalRows_empresa_dados = mysql_num_rows($empresa_dados);
	mysql_free_result($empresa_dados);
	// fim - empresa_dados
    ?>
    <!-- fim - consulta 'DA37s9' / 'DA01s9' -->

    
    <!-- 'contrato' NÃO existe em 'DA37S9' -->
    <? if ($totalRows_manutencao_dados == 0){ ?>
    
    	<div style="color: red;">
        <br>
        'contrato' NÃO existe em 'DA37S9'
        </div>
        
    <? } ?>
    <!-- fim - 'contrato' NÃO existe em 'DA37S9' -->

       
    <!-- 'contrato' existe em 'DA37S9' -->
    <? if ($totalRows_manutencao_dados > 0){ ?>
    
    	<div style="color: green;">
        <br>
        'contrato' existe em 'DA37S9' - ATUALIZA 'venda'
        <br>
        Código da empresa: <? echo $row_empresa_dados['codigo1']; ?>
        <br>
        Empresa: <? echo $row_empresa_dados['nome1']; ?>
        </div>
    	
        <?
		// atualiza venda
		$dados_venda = array(
				"empresa" => $row_empresa_dados['nome1'],
				"codigo_empresa" => $row_empresa_dados['codigo1'],
				"data_atualizacao_contrato" => date("Y-m-d H:i:s")
				
		);	
		$dados_venda_descricao = array(
				"id_venda" => $id,
				"id_usuario_responsavel" => "",
				"descricao" => "",
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Atualização de contrato da venda"
		);	
		funcao_venda_update($id, $dados_venda, $dados_venda_descricao);
		// fim - atualiza venda
		
    	?>
        
    <? } ?>
    <!-- fim - 'contrato' existe em 'DA37S9' -->
    
</div>        
<?php } while ($row_venda = mysql_fetch_assoc($venda)); ?>
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
mysql_free_result($venda);
mysql_free_result($parametros);
?>