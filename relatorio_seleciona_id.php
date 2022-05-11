<?php require('restrito.php'); ?>
<?php
require_once('Connections/conexao.php');
mysql_select_db($database_conexao, $conexao);

$relatorio_id = @$_POST['relatorio_id'];
$relatorio_id_grupo_subgrupo = @$_POST['relatorio_id_grupo_subgrupo'];

$query_filtro_relatorio_id = "
SELECT relatorio.id, relatorio.titulo, relatorio_grupo.titulo AS relatorio_grupo_titulo, relatorio_grupo_subgrupo.titulo AS relatorio_grupo_subgrupo_titulo 
FROM relatorio 
LEFT JOIN relatorio_grupo ON relatorio.id_grupo = relatorio_grupo.id 
LEFT JOIN relatorio_grupo_subgrupo ON relatorio.id_grupo_subgrupo = relatorio_grupo_subgrupo.id 
WHERE relatorio.id_grupo_subgrupo = '$relatorio_id_grupo_subgrupo' and relatorio.status = 1 
ORDER BY relatorio_grupo.id ASC, relatorio_grupo_subgrupo.id ASC, relatorio.id ASC";
$filtro_relatorio_id = mysql_query($query_filtro_relatorio_id) or die(mysql_error());
$row_filtro_relatorio_id = mysql_fetch_assoc($filtro_relatorio_id);
$totalRows_filtro_relatorio_id = mysql_num_rows($filtro_relatorio_id);
?>
<? if(mysql_num_rows($filtro_relatorio_id) == 0){ ?>
   <option value="">Nenhum relatório encontrado</option>
<? }else{ ?>
    
    <select id="relatorio_id" name="relatorio_id" style="width: 300px;">
    <option value="" <?php if (!(strcmp("", isset($_POST['relatorio_id'])))) {echo "selected=\"selected\"";} ?>>Escolha...</option>
    <?php do { ?>
    <option value="<?php echo $row_filtro_relatorio_id['id']?>" 
    <?php if ($row_filtro_relatorio_id['id'] == $_POST['relatorio_id']) {echo "selected=\"selected\"";} ?>>
    <?php //echo utf8_encode($row_filtro_relatorio_id['relatorio_grupo_subgrupo_titulo']); ?>
    <?php //echo utf8_encode($row_filtro_relatorio_id['relatorio_grupo_titulo']); ?>
    <?php echo utf8_encode($row_filtro_relatorio_id['titulo']); ?>
    </option>
    <?php
    } while ($row_filtro_relatorio_id = mysql_fetch_assoc($filtro_relatorio_id));
    $rows = mysql_num_rows($filtro_relatorio_id);
    if($rows > 0) {
    mysql_data_seek($filtro_relatorio_id, 0);
    $row_filtro_relatorio_id = mysql_fetch_assoc($filtro_relatorio_id);
    }
    ?>
    </select>
    
<? } ?>
<? mysql_free_result($filtro_relatorio_id); ?>