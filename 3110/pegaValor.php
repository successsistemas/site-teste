<?
$id = $_POST["id"];
if( $id == "Selecione algo"){
	/* Se o valor tiver vazio retona vazio */
	echo "<option>Selecione algo</option>";
}else{
	/* Se tiver o valor então faz checagem no
	   banco ou outra como abaixo */
	switch($id){
		case 1;
			echo "<option>Voc&ecirc; selecionou o um</option>";
			break;
		case 2;
			echo "<option>Voc&ecirc; selecionou o dois</option>";
			break;
		case 3;
			echo "<option>Voc&ecirc; selecionou o tr&ecirc;s</option>";
			break;
	}
}
?>