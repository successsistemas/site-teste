<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
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

// categoria
$colname_categoria = "-1";
if (isset($_GET['IdTipoModuloCategoria'])) {
  $colname_categoria = $_GET['IdTipoModuloCategoria'];
}
mysql_select_db($database_conexao, $conexao);
$query_categoria = sprintf("SELECT * FROM geral_tipo_modulo_categoria WHERE IdTipoModuloCategoria = %s", GetSQLValueString($colname_categoria, "int"));
$categoria = mysql_query($query_categoria, $conexao) or die(mysql_error());
$row_categoria = mysql_fetch_assoc($categoria);
$totalRows_categoria = mysql_num_rows($categoria);
// fim - categoria

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the required classes
require_once('../../includes/tfi/TFI.php');
require_once('../../includes/tso/TSO.php');
require_once('../../includes/nav/NAV.php');

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

// Filter
$tfi_listgeral_tipo_modulo1 = new TFI_TableFilter($conn_conexao, "tfi_listgeral_tipo_modulo1");
$tfi_listgeral_tipo_modulo1->addColumn("geral_tipo_modulo.IdTipoModulo", "NUMERIC_TYPE", "IdTipoModulo", "=");
$tfi_listgeral_tipo_modulo1->addColumn("geral_tipo_modulo.descricao", "STRING_TYPE", "descricao", "%");
$tfi_listgeral_tipo_modulo1->Execute();

// Sorter
$tso_listgeral_tipo_modulo1 = new TSO_TableSorter("rsgeral_tipo_modulo1", "tso_listgeral_tipo_modulo1");
$tso_listgeral_tipo_modulo1->addColumn("geral_tipo_modulo.IdTipoModulo");
$tso_listgeral_tipo_modulo1->addColumn("geral_tipo_modulo.ordem");
$tso_listgeral_tipo_modulo1->addColumn("geral_tipo_modulo.descricao");
$tso_listgeral_tipo_modulo1->setDefault("geral_tipo_modulo.ordem");
$tso_listgeral_tipo_modulo1->Execute();

// Navigation
$nav_listgeral_tipo_modulo1 = new NAV_Regular("nav_listgeral_tipo_modulo1", "rsgeral_tipo_modulo1", "../../", $_SERVER['PHP_SELF'], 50);

//NeXTenesio3 Special List Recordset
$maxRows_rsgeral_tipo_modulo1 = $_SESSION['max_rows_nav_listgeral_tipo_modulo1'];
$pageNum_rsgeral_tipo_modulo1 = 0;
if (isset($_GET['pageNum_rsgeral_tipo_modulo1'])) {
  $pageNum_rsgeral_tipo_modulo1 = $_GET['pageNum_rsgeral_tipo_modulo1'];
}
$startRow_rsgeral_tipo_modulo1 = $pageNum_rsgeral_tipo_modulo1 * $maxRows_rsgeral_tipo_modulo1;

// Defining List Recordset variable
$NXTFilter_rsgeral_tipo_modulo1 = "1=1";
if (isset($_SESSION['filter_tfi_listgeral_tipo_modulo1'])) {
  $NXTFilter_rsgeral_tipo_modulo1 = $_SESSION['filter_tfi_listgeral_tipo_modulo1'];
}
// Defining List Recordset variable
$NXTSort_rsgeral_tipo_modulo1 = "geral_tipo_modulo.ordem";
if (isset($_SESSION['sorter_tso_listgeral_tipo_modulo1'])) {
  $NXTSort_rsgeral_tipo_modulo1 = $_SESSION['sorter_tso_listgeral_tipo_modulo1'];
}
mysql_select_db($database_conexao, $conexao);

$colname_rsgeral_tipo_modulo_categoria1 = "-1";
if (isset($_GET['IdTipoModuloCategoria'])) {
  $colname_rsgeral_tipo_modulo_categoria1 = $_GET['IdTipoModuloCategoria'];
}

$query_rsgeral_tipo_modulo1 = sprintf("SELECT geral_tipo_modulo.IdTipoModulo, geral_tipo_modulo.ordem, geral_tipo_modulo.descricao FROM geral_tipo_modulo WHERE {$NXTFilter_rsgeral_tipo_modulo1} and IdTipoModuloCategoria = %s ORDER BY {$NXTSort_rsgeral_tipo_modulo1}", GetSQLValueString($colname_rsgeral_tipo_modulo_categoria1, "int"));
$query_limit_rsgeral_tipo_modulo1 = sprintf("%s LIMIT %d, %d", $query_rsgeral_tipo_modulo1, $startRow_rsgeral_tipo_modulo1, $maxRows_rsgeral_tipo_modulo1);
$rsgeral_tipo_modulo1 = mysql_query($query_limit_rsgeral_tipo_modulo1, $conexao) or die(mysql_error());
$row_rsgeral_tipo_modulo1 = mysql_fetch_assoc($rsgeral_tipo_modulo1);

if (isset($_GET['totalRows_rsgeral_tipo_modulo1'])) {
  $totalRows_rsgeral_tipo_modulo1 = $_GET['totalRows_rsgeral_tipo_modulo1'];
} else {
  $all_rsgeral_tipo_modulo1 = mysql_query($query_rsgeral_tipo_modulo1);
  $totalRows_rsgeral_tipo_modulo1 = mysql_num_rows($all_rsgeral_tipo_modulo1);
}
$totalPages_rsgeral_tipo_modulo1 = ceil($totalRows_rsgeral_tipo_modulo1/$maxRows_rsgeral_tipo_modulo1)-1;
//End NeXTenesio3 Special List Recordset

$nav_listgeral_tipo_modulo1->checkBoundries();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="pt-BR" />
<meta name="language" content="pt-BR" />
<meta name="robots" content="noindex,nofollow" />
<meta name="author" content="Juliano Martins - (34) 8805 3396" />
<meta name="copyright" content=" Copyright ?? Success Sistemas - Todos os direitos reservados." />
<title>??rea do Parceiro - Success Sistemas</title>
<link href="../../css/guia_painel.css" rel="stylesheet" type="text/css">
<script src="../../js/jquery.js"></script>
<link href="../../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<script src="../../includes/common/js/base.js" type="text/javascript"></script>
<script src="../../includes/common/js/utility.js" type="text/javascript"></script>
<script src="../../includes/skins/style.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/list.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/list.js.php" type="text/javascript"></script>
<script type="text/javascript">
$NXT_LIST_SETTINGS = {
  duplicate_buttons: true,
  duplicate_navigation: true,
  row_effects: false,
  show_as_buttons: false,
  record_counter: false
}
</script>
<style type="text/css">
  /* Dynamic List row settings */
  .KT_col_IdTipoModulo {width: 80px; overflow:hidden;}
  .KT_col_descricao {width: 300px; overflow:hidden;}
  
</style>
</head>
<body>

<div class="cabecalho"><? require_once('../padrao_cabecalho.php'); ?></div>

<!-- corpo -->
<div class="corpo">
	<div class="texto"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            
                <td class="padrao_esquerda"><? require_once('../padrao_esquerda.php'); ?></td>
                                
                <td class="padrao_centro">                
                
                <!-- descricao -->
                <div class="titulo">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="left">Tipo de M??dulo <?php
  $nav_listgeral_tipo_modulo1->Prepare();
  require("../../includes/nav/NAV_Text_Statistics.inc.php");
?></td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">P??gina inicial</a> &gt;&gt; <a href="listar_categoria.php">Categorias</a> &gt;&gt; Tipo de M??dulo</div>
                <!-- fim - descricao -->
                
                <div class="conteudo">
                
                <div style="font-weight: bold; color: #C00; font-size: 16px; margin-bottom: 20px;">Categoria: <? echo $row_categoria['descricao']; ?></div>
                
                  <div class="KT_tng" id="listgeral_tipo_modulo1">
                    <div class="KT_tnglist">
                      <form action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" method="post" id="form1">
                        <div class="KT_options"> <a href="<?php echo $nav_listgeral_tipo_modulo1->getShowAllLink(); ?>"><?php echo NXT_getResource("Show"); ?>
                          <?php 
  // Show IF Conditional region1
  if (@$_GET['show_all_nav_listgeral_tipo_modulo1'] == 1) {
?>
                            <?php echo $_SESSION['default_max_rows_nav_listgeral_tipo_modulo1']; ?>
                            <?php 
  // else Conditional region1
  } else { ?>
                            <?php echo NXT_getResource("all"); ?>
                            <?php } 
  // endif Conditional region1
?>
<?php echo NXT_getResource("records"); ?></a> &nbsp;
                          &nbsp; </div>
                        <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                          <thead>
                            <tr class="KT_row_order">
                              <th> <input type="checkbox" name="KT_selAll" id="KT_selAll"/>
                              </th>
                              <th id="IdTipoModulo" class="KT_sorter KT_col_IdTipoModulo <?php echo $tso_listgeral_tipo_modulo1->getSortIcon('geral_tipo_modulo.IdTipoModulo'); ?>"> <a href="<?php echo $tso_listgeral_tipo_modulo1->getSortLink('geral_tipo_modulo.IdTipoModulo'); ?>">C??digo</a></th>
                              <th id="ordem" class="KT_sorter KT_col_ordem <?php echo $tso_listgeral_tipo_modulo1->getSortIcon('geral_tipo_modulo.ordem'); ?>"> <a href="<?php echo $tso_listgeral_tipo_modulo1->getSortLink('geral_tipo_modulo.ordem'); ?>">Ordem</a></th>
                              <th id="descricao" class="KT_sorter KT_col_descricao <?php echo $tso_listgeral_tipo_modulo1->getSortIcon('geral_tipo_modulo.descricao'); ?>"> <a href="<?php echo $tso_listgeral_tipo_modulo1->getSortLink('geral_tipo_modulo.descricao'); ?>">T??tulo</a></th>
                              <th>&nbsp;</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if ($totalRows_rsgeral_tipo_modulo1 == 0) { // Show if recordset empty ?>
                              <tr>
                                <td colspan="5"><?php echo NXT_getResource("The table is empty or the filter you've selected is too restrictive."); ?></td>
                              </tr>
                              <?php } // Show if recordset empty ?>
                            <?php if ($totalRows_rsgeral_tipo_modulo1 > 0) { // Show if recordset not empty ?>
                              <?php do { ?>
                                <tr class="<?php echo @$cnt1++%2==0 ? "" : "KT_even"; ?>">
                                  <td><input type="checkbox" name="kt_pk_geral_tipo_modulo" class="id_checkbox" value="<?php echo $row_rsgeral_tipo_modulo1['IdTipoModulo']; ?>" />
                                    <input type="hidden" name="IdTipoModulo" class="id_field" value="<?php echo $row_rsgeral_tipo_modulo1['IdTipoModulo']; ?>" /></td>
                                  <td><div class="KT_col_IdTipoModulo"><?php echo $row_rsgeral_tipo_modulo1['IdTipoModulo']; ?></div></td>
                                  <td><div class="KT_col_ordem"><?php echo $row_rsgeral_tipo_modulo1['ordem']; ?></div></td>
                                  <td><div class="KT_col_descricao"><?php echo KT_FormatForList($row_rsgeral_tipo_modulo1['descricao'], 70); ?></div></td>
                                  <td>
                                  
                                  <a class="KT_edit_link" href="tabela.php?IdTipoModulo=<?php echo $row_rsgeral_tipo_modulo1['IdTipoModulo']; ?>&amp;KT_back=1"><?php echo NXT_getResource("edit_one"); ?></a> 
                                  
                                  <a class="KT_delete_link" href="#delete"><?php echo NXT_getResource("delete_one"); ?></a>
                                  
                                  </td>
                                </tr>
                                <?php } while ($row_rsgeral_tipo_modulo1 = mysql_fetch_assoc($rsgeral_tipo_modulo1)); ?>
                              <?php } // Show if recordset not empty ?>
                          </tbody>
                        </table>
                        <div class="KT_bottomnav">
                          <div>
                            <?php
            $nav_listgeral_tipo_modulo1->Prepare();
            require("../../includes/nav/NAV_Text_Navigation.inc.php");
          ?>
                          </div>
                        </div>
                        <div class="KT_bottombuttons">
                          <div class="KT_operations"> <a class="KT_edit_op_link" href="#" onclick="nxt_list_edit_link_form(this); return false;"><?php echo NXT_getResource("edit_all"); ?></a> <a class="KT_delete_op_link" href="#" onclick="nxt_list_delete_link_form(this); return false;"><?php echo NXT_getResource("delete_all"); ?></a></div>
                          <span>&nbsp;</span>
                          <select name="no_new" id="no_new">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                          </select>
                          <a class="KT_additem_op_link" href="tabela.php?KT_back=1" onclick="return nxt_list_additem(this)"><?php echo NXT_getResource("add new"); ?></a></div>
                      </form>
                    </div>
                    <br class="clearfixplain" />
                  </div>
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
mysql_free_result($categoria);
mysql_free_result($rsgeral_tipo_modulo1); 
mysql_free_result($usuario); ?>
