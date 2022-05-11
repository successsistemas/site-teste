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

// registro
mysql_select_db($database_conexao, $conexao);
$query_registro = "SELECT suporte.id, suporte.data_suporte, suporte.empresa, suporte.usuario_responsavel, suporte.situacao, suporte.tipo_atendimento, suporte.parecer FROM suporte WHERE 1=1 and (suporte.data_suporte between '2014-01-20 00:00:00' and '2014-02-31 23:59:59') and tipo_suporte = 'p' ORDER BY suporte.id ASC";
$registro = mysql_query($query_registro, $conexao) or die(mysql_error());
$row_registro = mysql_fetch_assoc($registro);
$totalRows_registro = mysql_num_rows($registro);
// fim - registro

require_once "mpdf/mpdf.php";  

$mpdf = new mPDF('utf-8', 'A4', '', '', 15, 15, 30, 20, 5, 10);

// header
$header = "
<table class='registro_cabecalho' width='100%'>  
<tr>  
<td align='center'>
	<div style='font-size: 14px; font-weight: bold;'>Tìtulo do Relatório Aqui (00)</div>
	<div style='font-size: 10px;'>Suportes tal tal</div>
</td>  
</tr> 
</table>

<div style='height: 1px; background-color: black;'></div>

<table class='filtro_dados_tabela' border='0' cellspacing='0' cellpadding='0' width='100%'>
	<tr>
		<td width='50%' valign='top' align='left'>
		<strong>Período</strong>: xxx
		<br>
		<strong>Praça</strong>: xxx
		</td>

		<td width='50%' valign='top' align='left'>
		<strong>Usuário Responsável</strong>: xxx
		</td>
	</tr>
</table>

<table class='registro_tabela' cellspacing='0' cellpadding='0' border='0' width='100%'>
	<thead>
	<tr>  
		<th width='100'>Número</th>  
		<th width='150'>Data</th>  
		<th width='150'>Cliente</th>  
		<th width='200'>Atendente</th>  
		<th width='100'>Tempo</th>  
		<th width='100'>Situação</th>  
		<th width='100'>Tipo</th>  
		<th width='100'>Parecer</th>  
	</tr>
	</head>
</table>
";  
// fim - header

// html
$html_color  = false;  
$html = "";
$html .= "
<table class='registro_tabela' cellspacing='0' cellpadding='0' border='0' width='100%'>
<tbody>
";

do {
	$html .= ($html_color) ? "<tr class='linha1'>" : "<tr class='linha2'>";  
	$html .= "<td width='100'>{$row_registro['id']}</td>";  
	$html .= "<td width='150'>{$row_registro['data_suporte']}</td>";  
	$html .= "<td width='150'>{$row_registro['id']}</td>";  
	$html .= "<td width='200'>{$row_registro['usuario_responsavel']}</td>";  
	$html .= "<td width='100'>{$row_registro['id']}</td>";  
	$html .= "<td width='100'>{$row_registro['situacao']}</td>";  
	$html .= "<td width='100'>{$row_registro['tipo_atendimento']}</td>";  
	$html .= "<td width='100'>{$row_registro['parecer']}</td>";  
	$html .= "<tr>";  
	$html_color = !$html_color;  

} while ($row_registro = mysql_fetch_assoc($registro));

$html .= "</tbody></table>";
// fim - html

// footer
$footer = "
<div style='height: 1px; background-color: black;'></div>

<table class='registro_rodape' width='1000'>  
<tr>  
<td align='left'>Success Sistemas</td>  
<td align='right'>Página: {PAGENO}</td>  
</tr>  
</table>
";
// fim - footer

//$stylesheet = file_get_contents('css/guia_registro2.css');
//$mpdf->WriteHTML($stylesheet,1);
//$mpdf->SetHTMLHeader($header);  
//$mpdf->SetHTMLFooter($footer);  
$mpdf->WriteHTML($html);   


$mpdf->Output();
?>