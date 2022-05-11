<?php
// calcula a quantidade de dias NÃO úteis entre duas datas (sem contar feriados)
function dias_nao_uteis($datainicial, $datafinal){
		if (!isset($datainicial)) return false;
		$segundos_datainicial = strtotime(str_replace("/","-",$datainicial));
		if (!isset($datafinal)) $segundos_datafinal=time();
		else $segundos_datafinal = strtotime(str_replace("/","-",$datafinal));
		$dias = abs(floor(floor(($segundos_datafinal-$segundos_datainicial)/3600)/24 ) );
		$uteis=0;
		
		for($i=1;$i<=$dias;$i++){
			$diai = $segundos_datainicial+($i*3600*24);
			$w = date('w',$diai);
			if ($w>0 && $w<6){ $uteis++; }
		}
		
		return $dias - $uteis;
}
// fim - calcula a quantidade de dias úteis entre duas datas (sem contar feriados)

// função que acrescenta dias NÂO úteis a uma data a partir do dia de hoje
function funcaoAcrescentaDiasNaoUteis($data_atual){
	$nao_uteis = dias_nao_uteis($data_atual, date('Y-m-d H:i:s'));
	return date('Y-m-d H:i:s', strtotime("+$nao_uteis days",strtotime($data_atual)));
}
// fim - função que acrescenta dias NÂO úteis a uma data a partir do dia de hoje

// proximoDiaUtil - função que altera o a data informada para o próximo dia útil
function proximoDiaUtil($data) {
	
	$timestamp = strtotime($data); // Converte $data em um UNIX TIMESTAMP
	$dia = date('N', $timestamp); // Calcula qual o dia da semana de $data // O resultado será um valor numérico: // 1 -> Segunda ... 7 -> Domingo

	// Se for sábado (6) ou domingo (7), calcula a próxima segunda-feira
	if ($dia >= 6) {
		$timestamp_final = $timestamp + ((8 - $dia) * 3600 * 24);
		
	// Senão é sábado nem domingo, mantém a data de entrada	
	} else {
		$timestamp_final = $timestamp;
	}
	return date('Y-m-d H:i:s', $timestamp_final);
	
}
// fim - proximoDiaUtil - função que altera o a data informada para o próximo dia útil
?>