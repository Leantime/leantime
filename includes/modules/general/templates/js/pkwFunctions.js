function calcDays(day1, day2, day3, day4){

		var dayFromPlane = day1;
		var dayToPlane = day2;

		var days = 0;
		
		if(dayFromPlane != "" && dayToPlane != ""){
		
			var arrDate = dayFromPlane.split(".");
			var arrDate2 = dayToPlane.split(".");
			
			// Neues Datum berechnen
			var dateFrom = new Date(parseInt(arrDate[2]), parseInt(arrDate[1]), parseInt(arrDate[0]));
			var dateTo = new Date(parseInt(arrDate2[2]), parseInt(arrDate2[1]), parseInt(arrDate2[0]));
	
			var day = 1000*60*60*24;		
				
			days =  Math.ceil((dateTo.getTime()-dateFrom.getTime())/(day) + 1);
			
		}

		if(day3 != ""){
			days++;
		}

		if(day4 != ""){
			days++;
		}

		if(days < 0) {

			alert('Fehler bei den Daten, bitte überprüfen Sie ihre Eingabe');

			$('#tage').val('');
			
		}else{

			$('#tage').val(days);

		}
		

	}

	function calcPrice(faktor1, faktor2, faktor3) {

		var sum = Math.round(100* faktor1 * faktor2 * faktor3)/100;
		//alert(''+faktor1+' * '+faktor2+' * '+faktor3+'');
		$('#praemie').val(sum);		
		
	}
	
	function activateButton (){

		var check1 = $('#clues:checked').val();
		var check2 = $('#confirm:checked').val();

		

		if((check1 == "on") && (check2 == "on")){
			
			$('#abschicken').removeAttr("disabled");
		}else{

			$('#abschicken').attr("disabled", "disabled");
			
		}

	}