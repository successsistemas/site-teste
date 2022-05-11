<?php
// converter entrada de data em portugues para ingles
if ( isset($_POST['data_inicio']) ) {
	$data_data = substr($_POST['data_inicio'],0,10);
	$data_hora = substr($_POST['data_inicio'],10,9);
	$_POST['data_inicio'] = implode("-",array_reverse(explode("-",$data_data))).$data_hora;
}
// fim - converter entrada de data em portugues para ingles - fim

function somar_dias_uteis($data_atual, $int_qtd_dias_somar) {
	$str_data = substr($data_atual,0,10);
	$str_hora = substr($data_atual,10,9);
	
	$array_data = explode('-', $str_data);
	$count_days = 0;
	$int_qtd_dias_uteis = 0;
	
	while ( $int_qtd_dias_uteis < $int_qtd_dias_somar ) {
		$count_days++;
		if ( ( $dias_da_semana = gmdate('w', strtotime('+'.$count_days.' day', mktime(0, 0, 0, $array_data[1], $array_data[2], $array_data[0]))) ) != '0' && $dias_da_semana != '6' ) {
			$int_qtd_dias_uteis++;
		}
	}

	return date('Y-m-d H:i:s', strtotime("+$count_days days", strtotime($data_atual)));
}

echo somar_dias_uteis($_POST['data_inicio'], $_POST['alteracao_previsao_prazo']);
?>