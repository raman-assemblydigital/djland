window.myNameSpace = window.myNameSpace || { };

$(document).ready ( function() {
    if(new Date().getMonth() < 5) $('renew').hide();
	var id = getText('member_id');	
	loadMember(id);
	
	addListeners();
	//periodically check if the user has filled out all fields
	window.setInterval(checkBlocking,1000);
});

function addListeners() {
    //RADIO BUTTON LISTENERS
    $('.can_status').unbind().click(function () {
        if (this.id == 'can1') {
            $('#can2').removeAttr("checked");
        }
        else {
            $('#can1').removeAttr("checked");
        }
    });
    $('.show_select').unbind().click(function () {
        if (this.id == 'show1') {
            $('#show2').removeAttr("checked");
        }
        else {
            $('#show1').removeAttr("checked");
        }
    });
    $('.alumni_select').unbind().click(function () {
        if (this.id == 'alumni1') {
            $('#alumni2').removeAttr("checked");
        }
        else {
            $('#alumni1').removeAttr("checked");
        }
    });
    $('#faculty').change(function () {

        if ($('#faculty').val() == "Other") {
            $('#faculty2').show();
        } else {
            $('#faculty2').hide();
        }
    });

    $('#submit_user').click(function () {
        if ($(this).text() == 'Renew') {
            renewMember(getMemberInfoFromPage(), getMemberInterestsFromPage());
        } else {
            updateMember(getMemberInfoFromPage(), getMemberInterestsFromPage());
        }

    });
    $('#renew').click(function(){
        renew_membership_form();
    });

//MEMBER TYPE: HIDE/SHOW STUDENT
    $('#member_type').unbind().change( function(){     
        if($('#member_type').val() == 'Student'){
            $('.student').show();
            $('.student').children().show();
        }else{
            $('.student').hide();
            //console.log("Hide student fields");
        }
    });

    $('#student_no').unbind().on('keyup', function () {
        var student_no = getVal('student_no');
        if (student_no != "") {
            $.ajax({
                type: "POST",
                url: "form-handlers/student_no-handler.php",
                data: {"student_no": student_no},
                dataType: "json"
            }).success(function (data) {

                if (data == true) {
                    $('#student_no_ok').remove();
                    $('#student_no_check').append("<div id='student_no_ok'></div>");
                    $('#student_no_ok').text("This student number is already registered!");
                }
                else if (student_no.length < 8) {
                    $('#student_no_ok').remove();
                    $('#student_no_check').append("<div id='student_no_ok'></div>");
                    $('#student_no_ok').text("Student number must be 8 characters long.");
                }
                else {
                    $('#student_no_ok').remove();
                    $('#student_no_check').append("<div class='green' id='student_no_ok'></div>");
                    $('#student_no_ok').text("Okay");
                }
            }).fail(function () {
                console.log('fail');
                $('#student_no_ok').text('connection error');

            });
        }
    });
    $('#student_no').focusout( function(){
        if($('#student_no_ok').text() == 'Okay') $('#student_no_ok').hide();
    });
}
function checkBlocking(){
		var allOkay = true;
        $('.required').each(function(){
            if($.trim($(this).val()).length <=0) allOkay=false;
        });
		if(getVal('member_type')=='Student'){
			if(!$.trim(getVal('student_no'))){
				allOkay=false;
			}
			if($('#student_no_ok').text().length != 8 && $('#student_no_ok').text() != "Okay"){
				allOkay=false;
			}
		}
		if (allOkay){
		$('#submit_user').attr('disabled',false);
            if($('#renew').is(':visible')){
                $('#submit_user').text("Submit");
            }else{
                $('#submit_user').text("Renew");
            }
		$('#submit_user').removeClass("red");
		}else{
			$('#submit_user').attr('disabled',true);
			$('#submit_user').text("Form Not Complete");
			$('#submit_user').addClass("red");
		}
	}

function renew_membership_form(){
    $('#title').text("Renew Membership");
    $('#subtitle').text("Please update your contact information and interests!");
    $('#submit_user').text("Renew Membership");
    $('#renew').hide();
    var date = new Date().getFullYear()+"/"+ (new Date().getFullYear()+1);
    $('#membership_year').append("<option value="+date +">"+date + "</option>").val(date);

    $('.renew').each(function(){
        if($(this).attr('type') == 'checkbox'){
            $(this).removeAttr('checked');
        }else{
            $(this).val("");
        }
    });
    
}

	