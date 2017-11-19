//pass form as formval form, pass id, or element
var signUpForm = F('the-form', function(form, response){
  // this is executed after the ajax call
  console.log(response);
  //do what you want with the response ... even manipulate the form
},
{
  //options object
    //url to send ajax request to
    url: 'ajaxscript.php'
});

// (optional) pass extra values to script
signUpForm.extras({
  extraValue : 'this is an extra value',
  //useful to send WordPress AJAX action if needed
  action: 'signup'
});

// (optional) set another submit button
signUpForm.setSubmit('submit2');

// (optional) pass a callback to take action on the clicked submit button
// 'this' refers to the button
signUpForm.submitButtonClick(function(form){

});
