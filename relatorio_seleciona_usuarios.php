<?php require('restrito.php'); ?>
<?php
require_once('Connections/conexao.php');
mysql_select_db($database_conexao, $conexao);

$where = "1 = 1";

// filtro_geral_praca
if( (isset($_POST["filtro_geral_praca"])) && ($_POST['filtro_geral_praca'] !="") ) {
	$colname_filtro_geral_praca = $_POST["filtro_geral_praca"];
	$where .= " and usuarios.praca = '$colname_filtro_geral_praca' "; 	
} 
// fim - se existe filtro de filtro_geral_praca

// usuario_atual
$usuario_atual = NULL;
if( (isset($_POST["usuario_atual"])) && ($_POST['usuario_atual'] !="") ) {
	$usuario_atual = $_POST["usuario_atual"];
} 
// fim - se existe filtro de usuario_atual


$query_filtro_geral_usuario_responsavel = "
SELECT IdUsuario, nome  
FROM usuarios 
WHERE $where and usuarios.status = 1 
ORDER BY usuarios.nome ASC";
$filtro_geral_usuario_responsavel = mysql_query($query_filtro_geral_usuario_responsavel) or die(mysql_error());
$row_filtro_geral_usuario_responsavel = mysql_fetch_assoc($filtro_geral_usuario_responsavel);
$totalRows_filtro_geral_usuario_responsavel = mysql_num_rows($filtro_geral_usuario_responsavel);
?>

<? if($totalRows_filtro_geral_usuario_responsavel == 0){ ?>

	<option value="">Nenhum usuário encontrado</option>
   
<? }else{ ?>
    
    <option value=""
    <?php if (!(strcmp("", isset($usuario_atual)))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
    <?php do {  ?>
    <option value="<?php echo $row_filtro_geral_usuario_responsavel['IdUsuario']; ?>"
    <?php if ( (isset($usuario_atual)) and (!(strcmp($row_filtro_geral_usuario_responsavel['IdUsuario'], $usuario_atual))) ) {echo "selected=\"selected\"";} ?>
    >
    <?php echo $row_filtro_geral_usuario_responsavel['nome']; ?>
    </option>
    <?php
    } while ($row_filtro_geral_usuario_responsavel = mysql_fetch_assoc($filtro_geral_usuario_responsavel));
    $rows = mysql_num_rows($filtro_geral_usuario_responsavel);
    if($rows > 0) {
    mysql_data_seek($filtro_geral_usuario_responsavel, 0);
    $row_filtro_geral_usuario_responsavel = mysql_fetch_assoc($filtro_geral_usuario_responsavel);
    }
    ?>
    
<? } ?>

<? mysql_free_result($filtro_geral_usuario_responsavel); ?>