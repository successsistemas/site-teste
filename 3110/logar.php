<?php require_once('Connections/conexao.php'); ?>
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

// *** Acessar área do parceiro
if (!isset($_SESSION)) {
  session_start();
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['usuario'])) {
	$usuario=$_POST['usuario'];
	$senha=$_POST['senha'];
	$MM_fldUserAuthorization = "";
	$MM_redirectLoginSuccess = "painel/index.php";
	$MM_redirectLoginFailed = "logar.php?acao=erro";
	$MM_redirecttoReferrer = false;
	
	mysql_select_db($database_conexao, $conexao);  

	// primeiro vamos verificar apenas se o campo usuário existe ou não E status
	$TestaUsuario__query=sprintf("SELECT usuario, senha, status FROM usuarios WHERE usuario=%s", 
										GetSQLValueString($loginUsername, "text")); 
	$TestaUsuario = mysql_query($TestaUsuario__query, $conexao) or die(mysql_error());
	$row_TestaUsuario = mysql_fetch_assoc($TestaUsuario);
	$TestaUsuarioEncontrados = mysql_num_rows($TestaUsuario);

		if($TestaUsuarioEncontrados) {
			$usuario_existe = "S";
			$usuario_status = $row_TestaUsuario['status'];
		} else {
			$usuario_existe = "N";
		}
	// fim - primeiro vamos verificar apenas se o campo usuário existe ou não E status

	$LoginRS__query=sprintf("SELECT usuario, senha FROM usuarios WHERE usuario=%s AND senha=%s and status = '1' ", 
										GetSQLValueString($usuario, "text"), GetSQLValueString($senha, "text")); 
	$LoginRS = mysql_query($LoginRS__query, $conexao) or die(mysql_error());
	$loginFoundUser = mysql_num_rows($LoginRS);

	
	// se usuário e senha existem
	if ($loginFoundUser) {
			$loginStrGroup = "";
			
			// declara 2 sessões
			$_SESSION['usuario'] = $usuario;
			$_SESSION['usuarios'] = $loginStrGroup;	      
			
			if (isset($_SESSION['PrevUrl']) && false) {
					$MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
			}
			header("Location: " . $MM_redirectLoginSuccess );
	}
	// se usuário e senha NÃO existem
	else {
			if($usuario_existe=="S"){
					header("Location: ". $MM_redirectLoginFailed ."&usuario=".$loginUsername."&status=".$usuario_status);
			}
			if($usuario_existe=="N"){
					header("Location: ". $MM_redirectLoginFailed);
			}
	}
}
// *** FIM - Acessar área do parceiro
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
</head>

<body>
<div style="font-size:12px; font-family:Verdana, Arial, Helvetica, sans-serif">
<strong>Área do parceiro</strong>
<br><br>
Para acessar esta área é necessário informar um usuário e senha.
<br><br>
<?
$erro = "-1";
if (isset($_GET['acao'])) {
	  $erro = $_GET['acao'];
}

if ($erro == 'erro') { ?>
  <div style="color:#FF0000">
		<?
        if (isset($_GET['usuario'])) {
                    if (isset($_GET['status']) and $_GET['status']=="0") {
                        echo "Usuário bloqueado.";
                    } else if (isset($_GET['status']) and $_GET['status']=="1") {
                        echo "Senha incorreta, verifique.";
                    }
        } else {
                echo "Usuário não existe.";
        }
        ?>
		<br><br>
  </div>
<? } ?>

<form action="<?php echo $loginFormAction; ?>" method="POST" name="logar" id="logar">
Usuário:
<br>
<input type="text" name="usuario" id="usuario" value="<? if ((isset($_GET['acao']) and $_GET['acao']=='erro') and (isset($_GET['usuario']))) { echo $_GET['usuario'];} ?>">
<br><br>
Senha:
<br>
<input type="password" name="senha" id="senha">
<br>
<input type="submit" name="button" id="button" value="Logar">
</form>


</div>
</body>
</html>