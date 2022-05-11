<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
<?php
$uf = @$_POST['uf'];
$cidade = @$_POST['cidade'];

// ibge_listar
mysql_select_db($database_conexao, $conexao);
$query_ibge_listar = "
SELECT * 
FROM ibge 
WHERE uf = '$uf' 
ORDER BY uf ASC, cidade ASC";
$ibge_listar = mysql_query($query_ibge_listar, $conexao) or die(mysql_error());
$row_ibge_listar = mysql_fetch_assoc($ibge_listar);
$totalRows_ibge_listar = mysql_num_rows($ibge_listar);
// fim - ibge_listar
?>

<? if($uf == ""){ ?>

	<option value="">Selecione primeiro o estado...</option>

<? } else if($totalRows_ibge_listar > 0){ ?>

	<option value="">Escolha...</option>
	<?php do { ?>
	
		<option value="<?php echo $row_ibge_listar['cidade']?>" <?php if ((!(strcmp($row_ibge_listar['cidade'], $cidade)))) {echo "selected=\"selected\"";} ?>>
		<?php echo utf8_encode($row_ibge_listar['cidade'])?>
		</option>
	
	<?php } while ($row_ibge_listar = mysql_fetch_assoc($ibge_listar)); ?>

<? } else { ?>

	<option value="">Nenhuma cidade encontrada...</option>

<? } ?>

<? mysql_free_result($ibge_listar); ?>