requirejs(['src/formval']);

var loginForm = F('test-form', function(form, response){


  if(response == 0){
    alert('You are logged in!');
  }else {
    r = JSON.parse(response);
    for (var name in r) {
      if (r.hasOwnProperty(name)) {
        //get element
        var el = form.querySelector('[name=' + name + ']');
        el.style.color = 'red';
      }
    }
  }

},
{
  //options
  url: 'f.php'
}
);
