requirejs(['src/formval']);

var form = F( 'form', function(form, response){
  console.log(form);
  console.log(response);
},
{
  //options
  url : 'test.php'
}
);

form.setSubmit( 'fsubmit' );
form.extras({'hre':'here'});
form.submitButtonClick(function(form){
  this.style.display = 'none';
  form.style.display = 'none';
});
//F( form, callback, options, extra )
