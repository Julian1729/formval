<?php

require 'formval.php';

$f = new Formval();

$f->setType('username', array(
    'min-len' => 6,
    'max-len' => 30
));

$f->setType('password', array(
  'min-len' => 8,
  'max-len' => 30
));

$f->setType('password-confirm', array(
  'match' => 'password'
));

$f->expecting(array(
  'username' => 'username',
  'password' => 'password',
  'confirm-password' => 'password-confirm'
));

if( $f->validate() ){
  echo 0;
}else {
  $f->return();
}

?>
