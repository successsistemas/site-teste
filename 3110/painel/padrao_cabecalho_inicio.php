<?php require_once('../padrao_restrito.php'); ?>

<div class="topo" style="width: 1000px; margin-left: auto; margin-right: auto; margin-top: 20px;">
<img src="../../imagens/logo-inicio.png">
</div>

<? if (isset($_SESSION['MM_Username'])) { ?> 
	<?
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
    
    $editFormAction = $_SERVER['PHP_SELF'];
    if (isset($_SERVER['QUERY_STRING'])) {
      $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
    }
    
    // usuario_logado
    $colname_usuario_logado = "-1";
    if (isset($_SESSION['MM_Username'])) {
      $colname_usuario_logado = $_SESSION['MM_Username'];
    }
    mysql_select_db($database_conexao, $conexao);
    $query_usuario_logado = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario_logado, "text"));
    $usuario_logado = mysql_query($query_usuario_logado, $conexao) or die(mysql_error());
    $row_usuario_logado = mysql_fetch_assoc($usuario_logado);
    $totalRows_usuario_logado = mysql_num_rows($usuario_logado);
    // fim - usuario_logado
    ?>
    
    <div style="text-align: right; border: 2px solid #B3D0E7; padding: 5px; margin-top: 15px; margin-bottom: 10px;">
    
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            	<td align="left">
                Usuário atual: <strong><? echo $row_usuario_logado['nome']; ?></strong> (<? echo $row_usuario_logado['usuario']; ?>) | 
                Praça: <strong><?php echo $row_usuario_logado['praca']; ?></strong>
                </td>
                
            	<td align="right" width="150">
                <strong><a href="../usuarios/tabela_alterar_senha.php">Alterar senha</a></strong> | 
                <strong><a href="../padrao_sair.php">Sair</a></strong>
                </td>
            </tr>
        </table>
            
    </div>
<? } ?>