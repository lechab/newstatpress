jQuery(document).ready(function($){

  // Options Page > Mail Notification tab
  setTimeout(function() {
     $('#mailsent').fadeOut();
     $('#optionsupdated').fadeOut();

   }, 3000);

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
