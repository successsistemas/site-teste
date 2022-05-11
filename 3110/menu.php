<?php require_once('Connections/conexao.php'); ?>
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

// site_link
mysql_select_db($database_conexao, $conexao);
$query_site_link_menu = "SELECT * FROM site_link ORDER BY ordem ASC";
$site_link_menu = mysql_query($query_site_link_menu, $conexao) or die(mysql_error());
$row_site_link_menu = mysql_fetch_assoc($site_link_menu);
$totalRows_site_link_menu = mysql_num_rows($site_link_menu);
// fim - site_link

?>
<style type="text/css"> 
#menu_lateral {
	width: 200px;
	margin: 0; padding: 0;
	float: left;
	}

#menu_lateral ul {
	margin: 0px;
	padding: 0px;
	}
 
#menu_lateral ul li {
	margin: 0px;
	padding: 0px;
	border-bottom: 1px solid #CCC;
	text-align: left;
	list-style-type: none;
	}
 
#menu_lateral a:link, #menu_lateral a:visited, #menu_lateral a:active {
	color: #666;
	text-decoration: none;
	padding: 8px;
	display: block;
	}
 
#menu_lateral a:hover {
	background: #E5F0FF;
	color: #039;
	text-decoration: none;
	padding: 8px;
	display: block;
	}
 
#menu_lateral_marcador {
	border: 0px;
	padding-right: 5px;
	vertical-align: text-bottom;
	}
</style> 

<div id="menu_lateral">
<ul>

<li><a href="index.php"><img src="imagens/marcador_menu_lateral.png" id="menu_lateral_marcador" />Página inicial</a></li>

<!-- site_link -->
<?php do { ?>
	<li>
	    <? if($row_site_link_menu['link']==""){ ?>
        <a href="site_link.php?IdLink=<?php echo $row_site_link_menu['IdLink']; ?>">
            <img src="imagens/marcador_menu_lateral.png" id="menu_lateral_marcador" /><?php echo $row_site_link_menu['titulo']; ?>
        </a>
        <? } else { ?>
        <a href="<?php echo $row_site_link_menu['link']; ?>" target="<?php echo $row_site_link_menu['link_janela']; ?>">
            <img src="imagens/marcador_menu_lateral.png" id="menu_lateral_marcador" /><?php echo $row_site_link_menu['titulo']; ?>
        </a>
        <? } ?>
    </li>
<?php } while ($row_site_link_menu = mysql_fetch_assoc($site_link_menu)); ?>
<!-- fim - site_link -->

<li><a href="site_evento.php"><img src="imagens/marcador_menu_lateral.png" id="menu_lateral_marcador" />Eventos</a></li>
<li><a href="seja_um_parceiro.php"><img src="imagens/marcador_menu_lateral.png" id="menu_lateral_marcador" />Seja um parceiro</a></li>
<li><a href="http://webmail.success.inf.br" target="_blank"><img src="imagens/marcador_menu_lateral.png" id="menu_lateral_marcador" />Webmail</a></li>

</ul>
</div>

<?php
mysql_free_result($site_link_menu);
?>
