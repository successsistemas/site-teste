<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
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

// geral_procedimento_site
mysql_select_db($database_conexao, $conexao);
$query_geral_procedimento_site = "SELECT * FROM geral_procedimento_site ORDER BY ordem ASC";
$geral_procedimento_site = mysql_query($query_geral_procedimento_site, $conexao) or die(mysql_error());
$row_geral_procedimento_site = mysql_fetch_assoc($geral_procedimento_site);
$totalRows_geral_procedimento_site = mysql_num_rows($geral_procedimento_site);
// fim - geral_procedimento_site

// update
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "usuario_atual")) {
	
	// nao_exibir
	$nao_exibir = (isset($_POST['nao_exibir']));

	if ($nao_exibir == 1){
		
		// upload
		$updateSQL = sprintf("
						   UPDATE usuarios 
						   SET primeiro_acesso='0' 
						   WHERE IdUsuario=%s", GetSQLValueString($row_usuario['IdUsuario'], "int"));
		
		mysql_select_db($database_conexao, $conexao);
		$Result1 = mysql_query($updateSQL, $conexao) or die(mysql_error());
		// fim - upload
		
	}
	// fim - nao_exibir
	
	$_SESSION['MM_primeiro_acesso_controle'] = 0;
	
	// redireciona
	$updateGoTo = "../index.php";
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $updateGoTo); 
	exit;
	// fim - redireciona
  
}
// fim - update


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
<title>Área do Parceiro - Success Sistemas</title>
<link href="../../css/guia_painel.css" rel="stylesheet" type="text/css">
<script src="../../js/jquery.js"></script>

<link rel="stylesheet" href="../../css/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="../../js/thickbox.js"></script>

</head>
<body>

<div class="cabecalho"><? require_once('../padrao_cabecalho.php'); ?></div>

<!-- corpo -->
<div class="corpo">
	<div class="texto"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            
                <td class="padrao_centro">                
                
                <!-- titulo -->
                <div class="titulo">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="left">Área do parceiro - Instruções iniciais</td>
                        <td align="right"></td>
                    </tr>
                </table>
                </div>
                <!-- fim - titulo -->
                
                <div style="margin-top: 10px; font-weight: bold;">Clique sobre o procedimento desejado para visualizar:</div>
                
				<?php do { ?>
                
                	<div style="margin-top: 10px;"><a href="primeiro_acesso_procedimento.php?IdProcedimentoSite=<?php echo $row_geral_procedimento_site['IdProcedimentoSite']; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=500&width=700&modal=true" class="thickbox"><?php echo $row_geral_procedimento_site['titulo']; ?></a></div>
                    
                <?php } while ($row_geral_procedimento_site = mysql_fetch_assoc($geral_procedimento_site)); ?>
                
                
          		<div style="border: 1px solid #CCC; padding: 5px; margin-top: 10px;">
                <form id="usuario_atual" name="usuario_atual" method="POST" action="<?php echo $editFormAction; ?>" class="cmxform">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                
                	<td align="left" valign="middle">
                    <input name="nao_exibir" id="nao_exibir" type="checkbox" value="1" /> Não exibir esta tela novamente
                    </td>
                    
                	<td align="right" valign="middle">
					<input type="submit" name="Acessar Área do Paceiro" value="Acessar Área do Paceiro" />
					<input type="hidden" name="MM_update" value="usuario_atual" />
                    </td>
                    
                </tr>
                </table>  
                </form>
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
mysql_free_result($geral_procedimento_site);
 ?>
