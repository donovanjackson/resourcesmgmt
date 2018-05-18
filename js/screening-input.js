$(document).ready(function(){
	
	$('.ajaxCalling').on('change keyup',function(){
		var formInput = $(this),
		inputId = formInput.attr('data-ref');
		console.log('ajaxCalling');
		console.log(inputId);
		if(inputId != null && inputId==="username"){
			var ajaxResult = $('.ajaxResult[data-ref="username"]').eq(0);
			if(formInput.val().trim().length<5){
				ajaxResult.html("Invalid");
				$('input[name="username"]').css("border-color","#a00");
				$('input[type="submit"]').attr("disabled",true);
			}
			else {
				ajaxCall = process(ajaxResult);
				$.when(ajaxCall).done(function(){
					setTimeout(validateUsername,100);
					setTimeout(checkRequiredInputs,200);
				});
			}
		}
	});
	
	$('.requiredInput').each(function(){
		var formInput = $(this);
		validateRInput(formInput);
	});
	checkRequiredInputs();
	
	$('.requiredInput').on('change keyup',riChanged);
	
	
	$( ".datepicker" ).datepicker();
	
	$('form').on('submit',function (event){
		$('[name]').each(function(){
				var formElement = $(this),
				valueTrimmed = formElement.val().trim();
				formElement.val(valueTrimmed);
			});
	});

});


function riChanged() {
		var formInput = $(this);
		validateRInput(formInput);
		checkRequiredInputs();
	}
	
function validateUsername(){
		var ajaxResult = $('.ajaxResult[data-ref="username"]').eq(0);
		if(ajaxResult.html() !== "Valid"){
			$('input[name="username"]').css("border-color","#a00");
			$('input[type="submit"]').attr("disabled",true);
		}
		else $('input[name="username"]').css("border-color","#0a0");
}

function validateRInput(formInput){
	if(formInput.hasClass('ajaxCalling'))return;
	if(formInput.val().trim()=="")formInput.css("border-color","#a00");
	else{ 
		var inputId = formInput.attr("id");
		if(inputId != null && inputId==="new_pass"){
			var confPass = $('#conf_pass').eq(0);
			if(formInput.val().trim().length<7 
			|| formInput.val() !== confPass.val()){
				formInput.css("border-color","#a00");
				confPass.css("border-color","#a00");
			}
			else{
				formInput.css("border-color","#0a0");
				confPas.css("border-color","#0a0");
			}
		}
		else if(inputId != null && inputId==="conf_pass"){
			var newPass = $('#new_pass').eq(0);
			if(formInput.val().trim().length<7 
			|| formInput.val() !== newPass.val()){
				formInput.css("border-color","#a00");
				newPass.css("border-color","#a00");
			}
			else{
				formInput.css("border-color","#0a0");
				newPass.css("border-color","#0a0");
			}
		}
		else formInput.css("border-color","#0a0");
	}
	
	
}

function checkRequiredInputs(){
	var enoughInfo = true;
	$('.requiredInput').each(function(){
		var required = $(this);
		if(required.css("border-color")=="rgb(170, 0, 0)")enoughInfo = false;
	});
	if(enoughInfo==true){
		$('input[type="submit"]').attr("disabled",false);
	}
	else{
		$('input[type="submit"]').attr("disabled",true);
	}
}

