// verifica se valor digitado é uma data/hora válida
function funcao_verifica_data_hora_valida(campo, timestamp){

    var value = campo.val();
    var erro = 0;
	    
    if((value.length == 16 || value.length == 19) && value.length > 0){
    
         // dividindo data e hora
        if(value.substr(10,1)!=' '){ erro = 1; } // verificando se há espaço
        var arrOpcoes = value.split(' ');
        if(arrOpcoes.length!=2){ erro = 1; } // verificando a divisão de data e hora]
        
        // verificando data
        var data        = arrOpcoes[0];
        var dia         = data.substr(0,2);
        var barra1      = data.substr(2,1);
        var mes         = data.substr(3,2);
        var barra2      = data.substr(5,1);
        var ano         = data.substr(6,4);
        if(data.length!=10||barra1!="-"||barra2!="-"||isNaN(dia)||isNaN(mes)||isNaN(ano)||dia>31||mes>12){ erro = 1; }
        if ((mes==4||mes==6||mes==9||mes==11)&&dia==31){ erro = 1; }
        if (mes==2 && (dia>29||(dia==29&&ano%4!=0))){ erro = 1; }

        // verificando hora
        var horario     = arrOpcoes[1];
        var hora        = horario.substr(0,2);
        var doispontos  = horario.substr(2,1);
        var minuto      = horario.substr(3,2);
        
        // segundos
        if(horario.length==5){
            
            var horario_length = 5;
            var doispontos2 = ":";
            var segundo = 0;
            
        } else if(horario.length==8){
            
            var horario_length = 8;
            var segundo		= horario.substr(6,2);
            var doispontos2 = horario.substr(5,1);
            
        } else {
			erro = 1;
		}
        // fim - segundos
        
        if(horario.length!=horario_length||isNaN(hora)||isNaN(minuto)||hora>23||minuto>59||segundo>59||doispontos!=":"){ erro = 1; }
		
		// valida se data é maior ou igual a atual
		
			// data_entrada
			var quebraDE=value.split("-");
			var diaDE = quebraDE[0];
			var mesDE = quebraDE[1];
			var anoDE = quebraDE[2].substr(0,4);
			var time_final = quebraDE[2].substr(5,8);
			var quebraTimeDE=time_final.split(":");
			var horaDE = quebraTimeDE[0];
			var minutoDE = quebraTimeDE[1];
			var segundoDE = quebraTimeDE[2];
			if(quebraTimeDE[2]==null){
				var segundoDE = '00';
			} else {
				var segundoDE = quebraTimeDE[2];
			}
			// fim - data_entrada
	
			// data_atual
			var hoje = new Date(timestamp);
			var dia = hoje.getDate();
			var mes = hoje.getMonth()+1;
			var ano = hoje.getFullYear();
			var hora = hoje.getHours();
			var minuto = hoje.getMinutes();
			if(quebraTimeDE[2]==null){
				var segundo = '0';
			} else {
				var segundo = hoje.getSeconds();
			}
			
			if (dia < 10){dia = '0' + dia;}
			if (mes < 10){mes = '0' + mes;}
			if (hora < 10){hora = '0' + hora;}
			if (minuto < 10){minuto = '0' + minuto;}
			if (segundo < 10){segundo = '0' + segundo;}
			// fim - data_atual
	
			var data_atual = ano+'-'+mes+'-'+dia+' '+hora+':'+minuto+':'+segundo;
			var data_entrada = anoDE+'-'+mesDE+'-'+diaDE+' '+horaDE+':'+minutoDE+':'+segundoDE;
			
			if(data_entrada < data_atual){erro = 1;}
		
		// fm - valida se data é maior ou igual a atual
		
    }else if(value.length > 0 && value.length < 16){
		
		erro = 1;
		
	}
    
    return erro;

}
// fim - verifica se valor digitado é uma data/hora válida


// verifica se valor digitado é uma data válida
function funcao_verifica_data_valida(campo){

    var value = campo.val();
    var erro = 0;

    if(value.length == 10 && value.length > 0){
    
        var arrOpcoes = value.split(' ');
        
        // verificando data
        var data        = arrOpcoes[0];
        var dia         = data.substr(0,2);
        var barra1      = data.substr(2,1);
        var mes         = data.substr(3,2);
        var barra2      = data.substr(5,1);
        var ano         = data.substr(6,4);
        if(data.length!=10||barra1!="-"||barra2!="-"||isNaN(dia)||isNaN(mes)||isNaN(ano)||dia>31||mes>12){ erro = 1; }
        if ((mes==4||mes==6||mes==9||mes==11)&&dia==31){ erro = 1; }
        if (mes==2 && (dia>29||(dia==29&&ano%4!=0))){ erro = 1; }
        // fim - verificando data
		
    }else if(value.length > 0 && value.length < 10){
		
		erro = 1;
		
	}
    
    return erro;

}
// fim - verifica se valor digitado é uma data válida

// verifica se valor digitado é uma data válida (entre 01-01-1970 e 31-12-2037)
function funcao_verifica_data_valida2(campo){

    var value = campo.val();
    var erro = 0;

    if(value.length == 10 && value.length > 0){
    
        var arrOpcoes = value.split(' ');
        
        // verificando data
        var data        = arrOpcoes[0];
        var dia         = data.substr(0,2);
        var barra1      = data.substr(2,1);
        var mes         = data.substr(3,2);
        var barra2      = data.substr(5,1);
        var ano         = data.substr(6,4);
        if(data.length!=10||barra1!="-"||barra2!="-"||isNaN(dia)||isNaN(mes)||isNaN(ano)||dia>31||mes>12){ erro = 1; }
        if ((mes==4||mes==6||mes==9||mes==11)&&dia==31){ erro = 1; }
        if (mes==2 && (dia>29||(dia==29&&ano%4!=0))){ erro = 1; }
        // fim - verificando data
		
		if(ano+"-"+mes+"-"+dia < '1970-01-01' || ano+"-"+mes+"-"+dia > '2037-12-31'){ erro = 1; } // limites do strtotime (php)
		
    }else if(value.length > 0 && value.length < 10){
		
		erro = 1;
		
	}

    return erro;

}
// fim - verifica se valor digitado é uma data válida

// somar_dias_uteis a uma data
somar_dias_uteis = function (startingDate, daysToAdjust) {
	
	var data = startingDate.valueOf();
	var quebraDI = data.split("-");
	var anoDI = quebraDI[0];
	var mesDI = quebraDI[1] - 1;
	var diaDI = quebraDI[2].substr(0,2);
	var time_inicial = quebraDI[2].substr(3,8);
	var quebraTimeDI=time_inicial.split(":");
	var horaDI = quebraTimeDI[0];
	var minutoDI = quebraTimeDI[1];
	var segundoDI = quebraTimeDI[2];
	
	var newDate = new Date(anoDI, mesDI, diaDI, horaDI, minutoDI, segundoDI, 00);
    var businessDaysLeft, isWeekend, direction;

    // Timezones are scary, let's work with whole-days only
    if (daysToAdjust !== parseInt(daysToAdjust, 10)) {
        throw new TypeError('somar_dias_uteis can only adjust by whole days');
    }

    // short-circuit no work; make direction assignment simpler
    if (daysToAdjust === 0) {
        return startingDate;
    }
    direction = daysToAdjust > 0 ? 1 : -1;

    // Move the date in the correct direction
    // but only count business days toward movement
    businessDaysLeft = Math.abs(daysToAdjust);
    while (businessDaysLeft) {
        newDate.setDate(newDate.getDate() + direction);
        isWeekend = newDate.getDay() in {0: 'Sunday', 6: 'Saturday'};
        if (!isWeekend) {
            businessDaysLeft--;
        }
    }
    return newDate;
};
// fim - somar_dias_uteis a uma data