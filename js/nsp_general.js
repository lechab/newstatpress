jQuery(document).ready(function($){
  // console.log("toto");

if ($("#pagecredits").length) {
  $.getJSON('../wp-content/plugins/newstatpress/includes/credit.json', function(data) {
   $.each(data.contacts, function(keyp, valp) {
     var addressr="<tr>\n<td class='cell-l'>" + valp.name + "</td>\n<td class='cell-r'>" + valp.properties + "</td>\n</tr>\n";
     $(addressr).appendTo("#addresses");

   });
  });
  $.getJSON('../wp-content/plugins/newstatpress/includes/lang.json', function(data) {
   $.each(data.translation, function(keyp, valp) {
     var addressr="<tr>"+
                  "<td class='cell-l'>" +
                  "<img style='border:0px;height:16px;' alt='" + valp.domain + "' title='"+valp.domain+"'" + "src='../wp-content/plugins/newstatpress/images/domain/"+ valp.domain + ".png' /> " +
                  valp.lang + "</td>\n<td class='cell-r'>" + valp.properties + "</td>" +
                  "<td class='cell-r'>" + valp.status + "</td></tr>\n";
     $(addressr).appendTo("#langr");

   });
  });
}

  // Options Page > Mail Notification tab
  setTimeout(function() {
     $('#mailsent').fadeOut();
     $('#optionsupdated').fadeOut();

   }, 4000);

   $( "#close" ).click(function() {
     $('#nspnotice').fadeOut();
   });

  //  $('#myoptions').get(0).reset();

  // $("#myform input[type='radio']:checked").val();
  // $('#dis').on('change', function() {
  //   var set;
    if($('#dis:checked').val()==='disabled') {
      $("#mail_freq").attr("disabled", true);
      $("#mail_time").attr("disabled", true);
      $("#mail_address").attr("disabled", true);
      $("#testmail").attr("disabled", true);
    }

    if($('#ena:checked').val()==='enabled') {
      $("#mail_freq").attr("disabled", false);
      $("#mail_time").attr("disabled", false);
      $("#mail_address").attr("disabled", false);
      $("#testmail").attr("disabled", false);
    }
    //   if($('#ena:checked').val()==='enabled')
    //
    //   set=false;
    // $("#mail_freq").attr("disabled", set);
    // $("#mail_time").attr("disabled", set);
    // $("#mail_address").attr("disabled", set);
  //  alert($('#dis:checked').val());
  //  alert($('#dis:checked').attr());

  // alert("toto");
// });

  $( "#dis" ).click(function() {
    $("#mail_freq").attr("disabled", true);
    $("#mail_time").attr("disabled", true);
    $("#mail_address").attr("disabled", true);
    $("#testmail").attr("disabled", true);
  });
  $( "#ena" ).click(function() {
    $("#mail_freq").attr("disabled", false);
    $("#mail_time").attr("disabled", false);
    $("#mail_address").attr("disabled", false);
    $("#testmail").attr("disabled", false);
  });





});


function validateCode() {
  // var TCode = document.getElementById('TCode').value;
  var obj = document.getElementById("newstatpress_apikey").value;

  if( /[^a-zA-Z0-9]/.test( obj ) ) {
     alert('Input is not alphanumeric');
     return false;
  }
  return true;
}

function randomString(length, chars) {
   var result = '';
   for (var i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
   return result;
}

function nspGenerateAPIKey() {
   var obj = document.getElementById("newstatpress_apikey");
   var txt = randomString(128, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
   obj.value = txt;
}
