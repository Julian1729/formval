# formval
Formval is a form validation "framework" that makes the tedious job of validating HTML Forms using AJAX a lot easier, concise and fun.
## Example
### HTML Form
```
<form id="signup-form">
  <!-- inputs -->
  <input type="text" name="firstname">
  <input type="text" name="lastname">
  <input type="text" name="email">
  <input type="password" name="password">
  <input type="password" name="confirm-password">
  <!-- submit button -->
  <button data-formal="submit">Sign Up!</button>
</form>
```
### JavaScript
```
var signUpForm = F("signup-form", function(form, response){

  //what to do after validation
  if( response == 0 ){
  
    alert( "You have been signed up!" );
    
    //even manipulate the form
    form.style.display = "none";
  }else{
  
    //handle the list of errors returned by formval
    var errorObj = JSON.parse( response );
    myErrorHandler( errorObj );
    
  }
  
},
{

  //options
  url : "register_user.php",
  method : "post"
  
});
```
### PHP
```
require "path/to/formval.php";

$f = new Formval();

// set reusable types, with commonly used input requirements
$f->setType('name', array(
  'required' => true,
  'max-len' => 30
));

$f->setType('username', array(
'required' => true,
'min-len' => 6,
'max-len' => 20
));

//tell formval what inputs it should be receiving, and what type to validate against

$f->expecting(array(
  "firstname" => "name",
  "lastname" => "name",
  
  // no need to specify a type if only used once...
  // pass requirements in on the fly
  "email" => array(
    "required" => true,
    "regex" => "/^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/"
  ),
  
  "username" => "username",
  
  "password" => array(
    "required" => true,
    "min-len" => 10,
    "max-len" => 30,
    //contain two special characters (reg expression below not tested)
    "regex" => "/(?:[^`!@#$%^&*\-_=+'\/.,]*[`!@#$%^&*\-_=+'\/.,]){2}/"
  ),
  
  "confirm-password" => array(
    //should match password
    "match" = "password"
  )
));

if( $f->validate() === false ){

  // return failed input names with list of failed requirements
  // encoded in JSON... and kill script
  $f->return();
  
}

// passed inputs are now accesible from inside object

registerUserinDatabase( $f->firstname, $f->lastname, $f->{"confirm-password"} ... );

```
