<?php

require 'formval.php';

// $_POST["Formval"] =
//   '{
//     "_f":
//       {
//         "it":"sdfddd",
//         "name":"sdfddd",
//         "car":["mazda", "nissan"],
//         "rad":"test2",
//         "matchRad":"test2",
//         "select-test":"test3",
//         "phone":"2154000468"
//       },
//     "hre":"here"
//   }';

$f = new Formval();

//print_r($f::$validTypeProps)
$f->setType('test', array(
  'max-len' => 6,
  )
);
$f->setType('match', array(
  'match' => 'it'
  )
);
$f->setType('phonenumber', array(
    'regex' => '/^\+?[\d]?\(?([\d]{3})\)?\.?-?([\d]{3})-?.?([\d]{4})$/'
  )
);
$f->setType('emailRegex', array(
    'regex' => '//'
  )
);
$f->expecting(array(
  'it' => 'test',
  'name' => 'match',
  'phone' => array(
    // pass in props on the fly
    'min-len' => 3
  ),
  'car' => array(
    'min-len' => 5
  ),
  'matchRad' => array(
    'match' => 'rad'
  )
));
//print_r($f->expectedInputs);
if( $f->validate() ){
  echo "All inputs check out! Entering into DB.";
}else {
  $f->return();
}



?>
