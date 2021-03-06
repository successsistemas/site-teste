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

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the required classes
require_once('../../includes/tfi/TFI.php');
require_once('../../includes/tso/TSO.php');
require_once('../../includes/nav/NAV.php');

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

// Filter
$tfi_listprospeccao_contador1 = new TFI_TableFilter($conn_conexao, "tfi_listprospeccao_contador1");
$tfi_listprospeccao_contador1->addColumn("prospeccao_contador.id", "NUMERIC_TYPE", "id", "=");
$tfi_listprospeccao_contador1->addColumn("prospeccao_contador.razao", "STRING_TYPE", "razao", "%");
$tfi_listprospeccao_contador1->addColumn("prospeccao_contador.cidade", "STRING_TYPE", "cidade", "%");
$tfi_listprospeccao_contador1->Execute();

// Sorter
$tso_listprospeccao_contador1 = new TSO_TableSorter("rsprospeccao_contador1", "tso_listprospeccao_contador1");
$tso_listprospeccao_contador1->addColumn("prospeccao_contador.id");
$tso_listprospeccao_contador1->addColumn("prospeccao_contador.razao");
$tso_listprospeccao_contador1->addColumn("prospeccao_contador.cidade");
$tso_listprospeccao_contador1->setDefault("prospeccao_contador.id");
$tso_listprospeccao_contador1->Execute();

// Navigation
$nav_listprospeccao_contador1 = new NAV_Regular("nav_listprospeccao_contador1", "rsprospeccao_contador1", "../../", $_SERVER['PHP_SELF'], 50);

//NeXTenesio3 Special List Recordset
$maxRows_rsprospeccao_contador1 = $_SESSION['max_rows_nav_listprospeccao_contador1'];
$pageNum_rsprospeccao_contador1 = 0;
if (isset($_GET['pageNum_rsprospeccao_contador1'])) {
  $pageNum_rsprospeccao_contador1 = $_GET['pageNum_rsprospeccao_contador1'];
}
$startRow_rsprospeccao_contador1 = $pageNum_rsprospeccao_contador1 * $maxRows_rsprospeccao_contador1;

// Defining List Recordset variable
$NXTFilter_rsprospeccao_contador1 = "1=1";
if (isset($_SESSION['filter_tfi_listprospeccao_contador1'])) {
	$NXTFilter_rsprospeccao_contador1 = $_SESSION['filter_tfi_listprospeccao_contador1'];
}
// Defining List Recordset variable
$NXTSort_rsprospeccao_contador1 = "prospeccao_contador.id";
if (isset($_SESSION['sorter_tso_listprospeccao_contador1'])) {
	$NXTSort_rsprospeccao_contador1 = $_SESSION['sorter_tso_listprospeccao_contador1'];
}
mysql_select_db($database_conexao, $conexao);

$query_rsprospeccao_contador1 = "SELECT prospeccao_contador.id, prospeccao_contador.razao, prospeccao_contador.cidade FROM prospeccao_contador WHERE {$NXTFilter_rsprospeccao_contador1} ORDER BY {$NXTSort_rsprospeccao_contador1}";
$query_limit_rsprospeccao_contador1 = sprintf("%s LIMIT %d, %d", $query_rsprospeccao_contador1, $startRow_rsprospeccao_contador1, $maxRows_rsprospeccao_contador1);
$rsprospeccao_contador1 = mysql_query($query_limit_rsprospeccao_contador1, $conexao) or die(mysql_error());
$row_rsprospeccao_contador1 = mysql_fetch_assoc($rsprospeccao_contador1);

if (isset($_GET['totalRows_rsprospeccao_contador1'])) {
  $totalRows_rsprospeccao_contador1 = $_GET['totalRows_rsprospeccao_contador1'];
} else {
  $all_rsprospeccao_contador1 = mysql_query($query_rsprospeccao_contador1);
  $totalRows_rsprospeccao_contador1 = mysql_num_rows($all_rsprospeccao_contador1);
}
$totalPages_rsprospeccao_contador1 = ceil($totalRows_rsprospeccao_contador1/$maxRows_rsprospeccao_contador1)-1;
//End NeXTenesio3 Special List Recordset

$nav_listprospeccao_contador1->checkBoundries();

if(!isset($_SESSION['has_filter_tfi_listprospeccao_contador1'])){
	$_SESSION['has_filter_tfi_listprospeccao_contador1'] = 1;
}
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
  duplicate_buttons: false,
  duplicate_navigation: false,
  row_effects: false,
  show_as_buttons: false,
  record_counter: false
}
</script>
<style type="text/css">
  /* Dynamic List row settings */
  .KT_col_id {width:80px; overflow:hidden;}
  .KT_col_razao {width:250px; overflow:hidden;}
  .KT_col_cidade {width:250px; overflow:hidden;}
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
                
                <!-- titulo -->
                <div class="titulo">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="left">Prospec????o - Contador <?php
$nav_listprospeccao_contador1->Prepare();
require("../../includes/nav/NAV_Text_Statistics.inc.php");
?>
</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">P??gina inicial</a> &gt;&gt; Prospec????o - Contador</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">

                <div class="KT_tng">
                                
                    <div class="KT_tnglist">
                    <form action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" method="post" id="form1">
                      <div class="KT_options"> <a href="<?php echo $nav_listprospeccao_contador1->getShowAllLink(); ?>"><?php echo NXT_getResource("Show"); ?>
                        <?php 
                    // Show IF Conditional region1
                    if (@$_GET['show_all_nav_listprospeccao_contador1'] == 1) {
                    ?>
                          <?php echo $_SESSION['default_max_rows_nav_listprospeccao_contador1']; ?>
                          <?php 
                    // else Conditional region1
                    } else { ?>
                          <?php echo NXT_getResource("all"); ?>
                          <?php } 
                    // endif Conditional region1
                    ?>
                    <?php echo NXT_getResource("records"); ?></a>
                      </div>
                      <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                        <thead>
                          <tr class="KT_row_order">
                            <th> <input type="checkbox" name="KT_selAll" id="KT_selAll"/>
                            </th>
                            <th id="id" class="KT_sorter KT_col_id <?php echo $tso_listprospeccao_contador1->getSortIcon('prospeccao_contador.id'); ?>"> <a href="<?php echo $tso_listprospeccao_contador1->getSortLink('prospeccao_contador.id'); ?>">C??digo</a> </th>
                            <th id="razao" class="KT_sorter KT_col_razao <?php echo $tso_listprospeccao_contador1->getSortIcon('prospeccao_contador.razao'); ?>"> <a href="<?php echo $tso_listprospeccao_contador1->getSortLink('prospeccao_contador.razao'); ?>">Nome</a> </th>
                            <th id="cidade" class="KT_sorter KT_col_cidade <?php echo $tso_listprospeccao_contador1->getSortIcon('prospeccao_contador.cidade'); ?>"> <a href="<?php echo $tso_listprospeccao_contador1->getSortLink('prospeccao_contador.cidade'); ?>">Cidade</a> </th>
                            <th>&nbsp;</th>
                          </tr>
                            <tr class="KT_row_filter">
                              <td>&nbsp;</td>
                              <td><input type="text" name="tfi_listprospeccao_contador1_id" id="tfi_listprospeccao_contador1_id" value="<?php echo KT_escapeAttribute(@$_SESSION['tfi_listprospeccao_contador1_id']); ?>" size="10" maxlength="50" /></td>
                              <td><input type="text" name="tfi_listprospeccao_contador1_razao" id="tfi_listprospeccao_contador1_razao" value="<?php echo KT_escapeAttribute(@$_SESSION['tfi_listprospeccao_contador1_razao']); ?>" size="35" maxlength="50" /></td>
                              <td><input type="text" name="tfi_listprospeccao_contador1_cidade" id="tfi_listprospeccao_contador1_cidade" value="<?php echo KT_escapeAttribute(@$_SESSION['tfi_listprospeccao_contador1_cidade']); ?>" size="35" maxlength="50" /></td>
                              <td><input type="submit" name="tfi_listprospeccao_contador1" value="<?php echo NXT_getResource("Filter"); ?>" /></td>
                            </tr>
                        </thead>
                        <tbody>
                          <?php if ($totalRows_rsprospeccao_contador1 == 0) { // Show if recordset empty ?>
                            <tr>
                              <td colspan="5"><?php echo NXT_getResource("The table is empty or the filter you've selected is too restrictive."); ?></td>
                            </tr>
                            <?php } // Show if recordset empty ?>
                          <?php if ($totalRows_rsprospeccao_contador1 > 0) { // Show if recordset not empty ?>
                            <?php do { ?>
                              <tr class="<?php echo @$cnt1++%2==0 ? "" : "KT_even"; ?>">
                                <td><input type="checkbox" name="kt_pk_prospeccao_contador" class="id_checkbox" value="<?php echo $row_rsprospeccao_contador1['id']; ?>" />
                                  <input type="hidden" name="id" class="id_field" value="<?php echo $row_rsprospeccao_contador1['id']; ?>" /></td>
                                <td><div class="KT_col_id" title="<?php echo $row_rsprospeccao_contador1['id']; ?>"><?php echo KT_FormatForList($row_rsprospeccao_contador1['id'], 35); ?></div></td>
                                <td><div class="KT_col_razao" title="<?php echo $row_rsprospeccao_contador1['razao']; ?>"><?php echo KT_FormatForList($row_rsprospeccao_contador1['razao'], 35); ?></div></td>
                                <td><div class="KT_col_cidade" title="<?php echo $row_rsprospeccao_contador1['cidade']; ?>"><?php echo KT_FormatForList($row_rsprospeccao_contador1['cidade'], 35); ?></div></td>
                                <td>
                                	<a class="KT_edit_link" href="tabela_contador.php?id=<?php echo $row_rsprospeccao_contador1['id']; ?>&amp;KT_back=1"><?php echo NXT_getResource("edit_one"); ?></a> 
                                	
                                	<a class="KT_delete_link" href="#delete"><?php echo NXT_getResource("delete_one"); ?></a>
                                	
                                	</td>
                              </tr>
                              <?php } while ($row_rsprospeccao_contador1 = mysql_fetch_assoc($rsprospeccao_contador1)); ?>
                            <?php } // Show if recordset not empty ?>
                        </tbody>
                      </table>
                      <div class="KT_bottomnav">
                        <div>
                          <?php
                            $nav_listprospeccao_contador1->Prepare();
                            require("../../includes/nav/NAV_Text_Navigation.inc.php");
                          ?>
                        </div>
                      </div>
                      
                      <div class="KT_bottombuttons">
                        <div class="KT_operations"> <a class="KT_edit_op_link" href="#" onClick="nxt_list_edit_link_form(this); return false;"><?php echo NXT_getResource("edit_all"); ?></a> <a class="KT_delete_op_link" href="#" onClick="nxt_list_delete_link_form(this); return false;"><?php echo NXT_getResource("delete_all"); ?></a> </div>
                        <span>&nbsp;</span>
                        <input type="hidden" name="no_new" id="no_new" value="1">
                        <a class="KT_additem_op_link" href="tabela_contador.php?KT_back=1" onClick="return nxt_list_additem(this)"><?php echo NXT_getResource("add new"); ?></a> </div>
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
mysql_free_result($usuario);
mysql_free_result($rsprospeccao_contador1);
?>
