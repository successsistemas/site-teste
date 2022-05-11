<?
function funcao_agenda_calendario($dia, $mes, $ano, $link){	
	$dia_atual = $dia;
	$mes_atual = $mes;
	$ano_atual = $ano;
	
	// parametros
	$meses = array(1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 
				   7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro');
	$dias_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');
	$primeiro_dia = mktime(0, 0, 0, $mes, 1, $ano);
	$dias_mes = date('t', $primeiro_dia);
	$dia_inicio = date('w', $primeiro_dia);
	// fim - parametros
	
	// navegacao
	echo '<div class="agenda_calendario_navegacao" >';
		echo '<a href="'.$link.'data_atual='.date('d-m-Y', strtotime("-1 month",strtotime("$ano-$mes-01"))).'" style="color: #000; font-weight: bold;">&lt;&lt; anterior</a> | ';
		echo '<a href="'.$link.'data_atual='.date('d-m-Y', strtotime("+1 month",strtotime("$ano-$mes-01"))).'" style="color: #000; font-weight: bold;">próximo &gt;&gt;</a>';	
	echo '</div>';
	// fim - navegacao
	
	echo '<table cellspacing="0" cellpadding="0" align="center" class="agenda_calendario">';
		
	// mês/ano
	echo '<tr>';
		echo '<th colspan="7">'. $meses[intval($mes)] . ' - ' . $ano . '</th>';
	echo '</tr>';
	// fim - mês/ano
	
	// dias da semana
	echo '<tr>';
		echo '<td align="center">'; 
			echo implode('</td><td align="center">', $dias_semana);
		echo '</td>';
	echo '</tr>';
	// fim - dias da semana
	
	
	// quadros vazios no INÍCIO do mês
	if($dia_inicio > 0){
		for($i = 0; $i < $dia_inicio; $i++){ 
			echo '<td>&nbsp;</td>'; 
		}
	}
	// fim - quadros vazios no INÍCIO do mês
	
	
	// dias (números)
	for($dia = 1; $dia <= $dias_mes; $dia++ ){
		
		// se é Domingo
		if($dia_inicio == 0){
			$estilo = 'color: #F00; text-decoration: none;'; 
		} 
		// fim - se é Domingo
		
		// se NÃO é Domingo
		else { 
			$estilo = 'color: #000; text-decoration: none;';
		}
		// fim - se NÃO é Domingo
		
		// se é Hoje
		if(($dia == date("j")) && ($mes == date("n")) && ($ano == date("Y"))) {
			$estilo = 'color: #000; font-weight: bold; text-decoration: underline;';
		}
		// fim - se é Hoje
		
		// se é dia selecionado
		if(($dia == $dia_atual) && ($mes == $mes_atual) && ($ano == $ano_atual)) {
			$estilo = 'color: #FFCC00; font-weight: bold; text-decoration: none;';
		}
		// fim - se é dia selecionado
		
		// dia
		echo '<td align="center">';
			echo '<a href="'.$link.'data_atual='.str_pad($dia, 2, "0", STR_PAD_LEFT).'-'.str_pad($mes, 2, "0", STR_PAD_LEFT).'-'.$ano.'" style="'.$estilo.'">';
			echo $dia;
			echo '</a>';	
		echo '</td>';
		// fim - dia
		
		$dia_inicio++;
		
		if($dia_inicio == 7){
			$dia_inicio = 0;
			echo "</tr>";
		
			if($dia < $dias_mes){
				echo '<tr>';
			}
		}
		
	}
	// fim - dias (números)
	
	
	// quadros vazios no FIM do mês
	if($dia_inicio > 0){
		for($i = $dia_inicio; $i < 7; $i++){
			echo '<td>&nbsp;</td>';
		}
	
		echo '</tr>';
	}
	// fim - quadros vazios no FIM do mês
	
	echo '</table>';	
}
?>