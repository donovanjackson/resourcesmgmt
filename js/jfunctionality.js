$(document).ready(function(event){
	resizeTextAreas();
	$('.table-dataTable').dataTable({
	"lengthMenu": [[5,10, 25, 50, -1], [5,10, 25, 50, "All"]],
	"iDisplayLength": 5
	});
	
	/*on the resize
		//resizeTextAreas();
		//resizeTableBodies();*/
	
	
	
	$(document).on('click','a.deleteEntry',function(e){
		e.preventDefault();
		var commitDelete = confirm('Are You Sure You Want To Delete?');
		if(commitDelete == true)window.location.href = $(this).attr('href');
	});
	
	$(document).on('click','button.deleteEntry',function(e){
		var commitDelete = confirm('Are You Sure You Want To Delete?');
		if(commitDelete == true) window.location.href = $(this).attr('data-url');
	});
	
	$(document).on('click','.addOption',function(){
		var buttonClicked = $(this);
		optionsName = buttonClicked.attr('data-giveName');
		var newEntryDiv = buttonClicked.siblings('div.skillsToAdd');
		newEntryDiv.append('<input type="text" name="'+optionsName+'" class="newSkill"><br/>');
	});
	$(document).on('click','.addNewOption',function(){
		var buttonClicked = $(this);
		optionsName = buttonClicked.attr('data-giveName');
		var newEntryDiv = buttonClicked.siblings('div.newOptions');
		newEntryDiv.append('<input type="text" name="'+optionsName+'" class="newOption"><br/>');
	});
	
	
	$(document).on('click','#another_contact',function(){
		$('#clients_contacts').append('<div><hr/><li>'+
			'<label for="contact_name[]">Full Name:</label><input type="text" name="contact_name[]" class="contact_info requiredInput">'+
			'<label for="office_phone[]"></label>Office Phone#:<input type="text" name="office_phone[]" class="contact_info">'+
			'<label for="cell_phone[]"></label>Cell Phone#:<input type="text" name="cell_phone[]" class="contact_info">'+
			'<label for="home_phone[]"></label>Home Phone#:<input type="text" name="home_phone[]" class="contact_info">'+
			'<label for="email[]">Email:</label><input type="text" name="email[]" class="contact_info"><br/>'+
			'<label for="secretary_name[]">Secretary Name:</label><input type="text" name="secretary_name[]" class="contact_info">'+
			'<label for="secretary_phone[]">Secretary Phone:</label><input type="text" name="secretary_phone[]" class="contact_info">'+
			'<label for="fax[]"></label>Fax:<input type="text" name="fax[]" class="contact_info">'+
			'<label for="contact_notes[]">Brief Notes</label><textarea name="contact_notes[]" class="contact_info" rows="5" cols="50"></textarea>'+
			'<input name="contact_id[]" class="contactId" type="hidden">'+
		'<button type="button"" class="delete_contact">Delete Contact</button></li></div>');
		$('.requiredInput').on('change keyup',riChanged);
	});
	
	$(document).on('click','input#another_hSale',function(){
		$('#clients_hSales').append('<div><li>'+
			'<label for="sold_hardware_name[]">Hardware Name:</label><input type="text" name="sold_hardware_name[]">'+
				' <label for="hs_buying_contact[]">Sold To(Contact): </label><input type="text" name="hs_buying_contact[]">'+
				' <label for="hs_bc_phone[]"> Buyer\'s Phone Number: </label><input type="text" name="hs_bc_phone[]">'+
				' <label for="hs_selling_date[]"> Selling Date: </label><input type="text" class="datepicker" name="hs_selling_date[]">'+
				'<input name="hardware_sale_id[]" class="hsId" type="hidden">'+
		'<button class="delete_hSale">Delete Sale</button></li></div>');
		var hsDiv  = $('#hardware_sales').eq(0);
		//hsDiv.prop('scrollTop',hsDiv.prop('scrollHeight'));
		hsDiv.animate({scrollTop:hsDiv.prop('scrollHeight')},300);
		$( ".datepicker" ).datepicker();
	});
	$(document).on('click','input#another_sSale',function(){
		$('#clients_sSales').append('<div><li>'+
			'<label for="sold_software_name[]">Software Name:</label><input type="text" name="sold_software_name[]">'+
				'<label for="ss_buying_contact[]"> Sold To(Contact): </label><input type="text" name="ss_buying_contact[]">'+
				'<label for="ss_bc_phone[]"> Buyer\'s Phone Number: </label><input type="text" name="ss_bc_phone[]">'+
				'<label for="ss_selling_date[]"> Selling Date: </label><input type="text" class="datepicker" name="ss_selling_date[]">'+
				'<input name="software_sale_id[]" class="ssId" type="hidden">'+
		'<button class="delete_sSale">Delete Sale</button></li></div>');
		var ssDiv  = $('#software_sales').eq(0);
		//ssDiv.prop('scrollTop',ssDiv.prop('scrollHeight'));
		ssDiv.animate({scrollTop:ssDiv.prop('scrollHeight')},300);
		$( ".datepicker" ).datepicker();
	});
	
	$(document).on('click','input#another_skill',function(){
		$('#skillsWanted').append('<div class="requiredSkill">'+
		'Skill: <input name="wantedSkill[]" class="wantedSkill" type="text" maxlength="45">'+
		' <select name="conditional[]">'+
		'<option value="Must Have">Must Have</option>'+
		'<option value="A Plus">A Plus</option>'+
		'</select>'+
		' <input type="checkbox" name="is_pskill[]" class="is_pskill">'+
		'<button class="delete_reqSkill">Delete Skill Requirement</button>'+
		'</div>');
	});
	

	
	
	$(document).on('click','input#another_keyphrase',function(){
		$('#keyphrasesWanted').append('<div class="reqKeyphrase">'+
		'Keyphrase: <input name="keyphrase[]" type="text">'+
		'<select name="conditional[]">'+
		'<option value="Must Have">Must Have</option>'+
		'<option value="A Plus">A Plus</option>'+
		'</select>'+
		'<button class="delete_keyphrase">Delete Keyphrase</button>'+
		'</div>');
	});
	
	
	
	//event made for firefox and IE
	$(document).on('click','button.buttonLink',function(){
		var hyperlink = $(this).find('a').eq(0),
		href = hyperlink.attr("href");
		window.location.href = href;
	});
	
	
	$('#clients_contacts').on('click','button.delete_contact',function(){
		var contactid = $(this).prev().val();
		var contactsDeleted = $('#contactsDeleted').eq(0);
		if(contactsDeleted){
			if(!contactsDeleted.val())contactsDeleted.val(contactid);
			else contactsDeleted.val(contactsDeleted.val()+','+contactid);
			console.log('csDeleted '+contactsDeleted.val());
		}
		$(this).parent().parent().remove();
	});
	
	$('#clients_hSales').on('click','button.delete_hSale',function(){
		var hSaleid = $(this).prev().val();
		var hsDeleted = $('#hsDeleted').eq(0);
		if(hsDeleted){
			if(!hsDeleted.val())hsDeleted.val(hSaleid);
			else hsDeleted.val(hsDeleted.val()+','+hSaleid);
			console.log('hsDeleted '+hsDeleted.val());
		}
		$(this).parent().parent().remove();
	})
	
	$('#clients_sSales').on('click','button.delete_sSale',function(){
		var sSaleid = $(this).prev().val();
		var ssDeleted = $('#ssDeleted').eq(0);
		if(ssDeleted){
			if(!ssDeleted.val())ssDeleted.val(sSaleid);
			else ssDeleted.val(ssDeleted.val()+','+sSaleid);
			console.log('ssDeleted '+ssDeleted.val());
		}
		$(this).parent().parent().remove();
	});
		
	
	
	$('#skillsWanted').on('click','button.delete_reqSkill',function(){
		$(this).parent().remove();
	});
	
	$('#keyphrasesWanted').on('click','button.delete_keyphrase',function(){
		$(this).parent().remove();
	});
	
	$(document).on('submit','form#findQRes',function(e){
		var $allWSkills = $(this).find('.requiredSkill');
		for(var i in $allWSkills){
			var skillThatsPrimary =  $($allWSkills).eq(i).find('input.wantedSkill').val(),
			$isPSkill = $($allWSkills).eq(i).find('input.is_pskill').val(skillThatsPrimary);
			
		}
	});
	
	
	
	
	$('button.tglBtn.checked').each(function(){
		$(this).find('input.tglCB').prop('checked',true);
	});
	
	
	
	//event made for firefox and IE
	$(document).on('click','button.tglBtn',function(event){
		$(this).toggleClass("checked");
		var checkbox = $(this).find('input.tglCB').eq(0);
		checkbox.prop("checked", !checkbox.prop("checked"));
	});
	
	
	
	
	$(document).on('click','button.togglePassType',function(event){
		event.preventDefault();
		var buttonClicked = $(this);
		togglePassFieldType(buttonClicked);
	});
	
	$(document).on('click','.viewer',function(event){
		//var url = $(this).val();
		var url = $(this).attr("data-url");
		var win=window.open(url, '_blank');
		win.focus();
	});
	
});

	

function togglePassFieldType(buttonClicked){
	var passName = buttonClicked.attr("data-ref"),
	passField = $('input[name="'+passName+'"]').eq(0);
	if(passField.attr("type")=="password"){
		passField.attr("type","text");
		buttonClicked.html("Hide");
	}
	else{
		passField.attr("type","password");
		buttonClicked.html("View");
	}
	
}

function resizeTextAreas(){
	$('textarea').each(function(index, element){
	console.log($(window).width());
	if($(window).width()<1650 && element.cols > 30){
		element.cols = 30;
		element.rows = 8;
	}
	else if($(window).width()>1650 && element.rows > 6){
		element.cols = 40;
		element.rows = 6;
	}
	});
}
	
function goBack() {
    window.history.back();
}
