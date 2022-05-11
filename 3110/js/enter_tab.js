window.onload=function(){
   var nodes = document.getElementsByTagName('*');	   
   var elements = new Array();
   for(var j=0;j<nodes.length;j++){		
	   if(nodes[j].tagName.toLowerCase()=="input" || nodes[j].tagName.toLowerCase()=="textarea" || nodes[j].tagName.toLowerCase()=="select" ){			
			elements.push(nodes[j]);
	   } 
   }
   for(var i=0;i<elements.length;i++){
	   if(elements[i].type.toLowerCase()=="submit" || elements[i].type.toLowerCase()=="reset") continue;
	   elements[i].onkeypress=function(e){
				var k = document.all?event.keyCode:e.keyCode;					  																
				if(k==13){									   				
				   var nodes = document.getElementsByTagName('*');	   
				   var elements = new Array();
				   for(var j=0;j<nodes.length;j++){		
					   if( nodes[j].tagName.toLowerCase()=="input"  || nodes[j].tagName.toLowerCase()=="textarea" || nodes[j].tagName.toLowerCase()=="select" ){			
							elements.push(nodes[j]);
					   } 
				   }
					for(var i=0;i<elements.length;i++){				   				
						if(this==elements[i] && i<elements.length-1){
							elements[i+1].focus();
							return false;
						}
					}
					elements[0].focus();
					return false;
				}
				return true;
			};
   }
};