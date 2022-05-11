<?php 
ob_start();
set_time_limit(0);
require_once('../padrao_restrito.php');
require_once('../../Connections/conexao.php');

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

require_once('../funcao.php');
require_once "../../mpdf/mpdf.php";

// usuario
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuario

// rsmala_direta
$colname_rsmala_direta = "-1";
if (isset($_GET['IdMalaDireta'])) {
  $colname_rsmala_direta = $_GET['IdMalaDireta'];
}
mysql_select_db($database_conexao, $conexao);
$query_rsmala_direta = sprintf("
SELECT 
mala_direta.*, 
usuarios.nome AS usuario_nome
FROM mala_direta 
LEFT JOIN usuarios ON usuarios.IdUsuario = mala_direta.IdUsuario
WHERE mala_direta.IdMalaDireta = %s", 
GetSQLValueString($colname_rsmala_direta, "int"));
$rsmala_direta = mysql_query($query_rsmala_direta, $conexao) or die(mysql_error());
$row_rsmala_direta = mysql_fetch_assoc($rsmala_direta);
$totalRows_rsmala_direta = mysql_num_rows($rsmala_direta);
// fim - rsmala_direta

// destinatario_listar
mysql_select_db($database_conexao, $conexao);

if($row_rsmala_direta['perfil'] == "p"){
	
	$query_destinatario_listar = sprintf("
	SELECT mala_direta_destinatario.*, 
	prospeccao.nome_razao_social, prospeccao.endereco, prospeccao.endereco_numero, prospeccao.endereco_complemento, prospeccao.bairro, prospeccao.cidade, prospeccao.uf, prospeccao.cep, prospeccao.tipo_cliente   
	FROM mala_direta_destinatario 
	LEFT JOIN prospeccao ON mala_direta_destinatario.id_prospeccao = prospeccao.id 
	WHERE mala_direta_destinatario.IdMalaDireta=%s 
	ORDER BY mala_direta_destinatario.IdMalaDiretaDestinatario ASC
	", 
	GetSQLValueString($row_rsmala_direta['IdMalaDireta'], "int"));
	
} else {

	$query_destinatario_listar = sprintf("
	SELECT mala_direta_destinatario.*, 
	da01.nome1, da01.endereco1, da01.bairro1, da01.cep1, da01.cidade1, da01.uf1, da01.contato1    
	FROM mala_direta_destinatario 
	LEFT JOIN da37 ON mala_direta_destinatario.contrato = da37.codigo17 
	LEFT JOIN da01 ON da37.cliente17 = da01.codigo1 
	WHERE mala_direta_destinatario.IdMalaDireta=%s 
	ORDER BY mala_direta_destinatario.IdMalaDiretaDestinatario ASC
	", 
	GetSQLValueString($row_rsmala_direta['IdMalaDireta'], "int"));
	
}
$destinatario_listar = mysql_query($query_destinatario_listar, $conexao) or die(mysql_error());
$row_destinatario_listar = mysql_fetch_assoc($destinatario_listar);
$totalRows_destinatario_listar = mysql_num_rows($destinatario_listar);
// fim - destinatario_listar

$mpdf = new mPDF('utf-8', 'A4', '', '', 3, 3, 3, 3, 0, 0); 
$mpdf->charset_in='UTF-8';
$stylesheet = "
table{
  width: 50%;
  margin-bottom: 5px;
  font-family: Segoe, 'Segoe UI', 'DejaVu Sans', 'Trebuchet MS', Verdana, sans-serif;
  font-size: 10px;
  line-height: 1.8em;
}
table td{
	border: 1px solid black;
	padding: 0px 5px 0px 5px; 
}
";
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
<title>Etiquetas</title>
</head>
<body>		
<?
$texto = NULL;
if($totalRows_destinatario_listar > 0){
	
	do {
		$texto .= "<table cellspacing='0' cellpadding='0'>";
		$texto .= "<tr>";
			$texto .= "<td>";
				if($row_rsmala_direta['perfil'] == 'p'){
					
					if($row_destinatario_listar['tipo_cliente']=="a"){
						$texto .= "<p>".utf8_encode($row_destinatario_listar['nome_razao_social'])."</p>";
						$texto .= "<p>".utf8_encode($row_destinatario_listar['endereco'])."</p>";
						$texto .= "
						<p>
						".utf8_encode($row_destinatario_listar['bairro'])."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						".utf8_encode($row_destinatario_listar['cep'])."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						".utf8_encode($row_destinatario_listar['cidade'])." - 
						".utf8_encode($row_destinatario_listar['uf'])."
						</p>
						";	
					} else { 
						$texto .= "<p>".$row_destinatario_listar['nome_razao_social']."</p>";
						$texto .= "<p>".$row_destinatario_listar['endereco']."</p>";
						$texto .= "
						<p>
						".$row_destinatario_listar['bairro']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						".$row_destinatario_listar['cep']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						".$row_destinatario_listar['cidade']." - 
						".$row_destinatario_listar['uf']."
						</p>
						";					
					}

				} else {
					$texto .= "<p>".utf8_encode($row_destinatario_listar['nome1'])."</p>";
					$texto .= "<p>".utf8_encode($row_destinatario_listar['endereco1'])."</p>";
					$texto .= "
					<p>
					".utf8_encode($row_destinatario_listar['bairro1'])."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					".utf8_encode($row_destinatario_listar['cep1'])."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					".utf8_encode($row_destinatario_listar['cidade1'])." - 
					".utf8_encode($row_destinatario_listar['uf1'])."
					</p>
					";
					$texto .= "
					<p>
					CONTATO/RESPONSÁVEL: ".utf8_encode($row_destinatario_listar['contato1'])."
					</p>
					";
				}
			$texto .= "</td>";
		$texto .= "</tr>";
		$texto .= "</table>";
		
	} while ($row_destinatario_listar = mysql_fetch_assoc($destinatario_listar));

}

$mpdf->WriteHTML($stylesheet, 1);
//$mpdf->SetHTMLHeader('titulo');
//$mpdf->AddPage('P','','','','',15,15,15,15,5,7);
$mpdf->WriteHTML($texto, 2);
//$mpdf->Output('etiqueta.pdf','F');
$mpdf->Output();
?>
</body>
</html>
<?php 
mysql_free_result($usuario); 
mysql_free_result($rsmala_direta); 
mysql_free_result($destinatario_listar);
?>
