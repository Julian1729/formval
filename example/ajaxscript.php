<?php

require '../src/formval.php';

// new formval object
$f = new Formval();


// set a type by adding a type name, and requirements for that type
$f->setType('name', array(
  'required' => true,
  'max-len' => 30
));

$f->setType('username', array(
  'required' => true,
  'min-len' => 5,
  'max-len' => 30
));

$f->setType('password', array(
  'required' => true,
  'min-len' => 8,
  'max-len' => 25
));

// tell formval the values you are expecting to receive
$f->expecting(array(
  // array( inputName => type ) || array( inputName => array(requirement => value) )
  'firstname' => 'name',
  'lastname' => 'name',
  'username' => 'username',
  'password' => 'password',
  //when a type wont be reused for another input, we can just pass
  //in its requirements here
  'password-confirm' => array(
    'match' => 'password'
  )
));

//now validate values
$results = $f->validate();

//$results is true on success, false on fail
if( $results === false ){
  // send failed inputs back to js and kill script
  $f->return();
}


// values are now accessible from the Formval object ... extra values to
addUserToDatabase( $f->firstname,  $f->username, $f->{'password-confirm'}, $f->extraValue ... );


?>
