<?php
/**
* Formval
*
* @author Julian Hernandez
* @package Formval
* PHP Version: 7.0.8
*/



/**
 * Formval Class
 */
class Formval{

  /**
  * Store expected inputs here...to be checked
  * against passed in values.
  */
  public $expectedInputs = array();

  /**
  * Store inputs that passed the validity test.
  */
  public $goodInputs = array();

  /**
  * Store inputs that failed the validity test.
  */
  public $badInputs = array();

  /**
  * Store types here
  */
  public $types = array();


  /**
  * Valid properties for a type
  * array( 'property-name' => 'datatype')
  */
  public static $validTypeProps = array();

  function __construct(){

    $this::$validTypeProps = array(
    'min-len' => array(
      'dataType' => 'integer',
      'validate' => function($v, $p){
          // String must be a minimum of $v
          if( strlen($v) >= $p ):
            return true;
          else:
            return false;
          endif;
        }
    ),
    'max-len' => array(
      'dataType' => 'integer',
      'validate' => function($v, $p){
        // String must be less than or equal to $p
        if( strlen($v) <= $p ):
          return true;
        else:
          return false;
        endif;
        }
      ),
    'required' => array(
      'dataType' => 'boolean',
      'validate' => function($v, $p){
          // String cannot be empty (if $p is set to true)
          // there is not use in making $p false
          if( $p ){
            if( !$this->isValueEmpty($v) ):
              return true;
            else:
              return false;
            endif;
          }
        }
      ),
    'match' => array(
      'dataType' => 'string',
      'validate' => function($v, $p){
        // Value must match another value that has already been passed in
        // check that other variable exists
        if( !array_key_exists($p, $this->goodInputs) ):
          return false;
        endif;
        // it exists...check if it matches
        if( $this->goodInputs[$p] === $v ):
          return true;
        else:
          return false;
        endif;
        }
      ),
    'regex' => array(
      'dataType' => 'string',
      'validate' => function($v, $p){
        // Match string against regex pattern
        if( preg_match($p, $v) ):
          return true;
        else:
          return false;
        endif;
        }
      ),
    'cannot-contain' => array(
      'dataType' => 'string',
      'validate' => function($v, $p){
          if( strpos($v, $p) === false):
            return true;
          endif;
          return false;
        }
    ),
    'should-contain' => array(
      'dataType' => 'string',
      'validate' => function($v, $p){
        if( strpos($v, $p) === true):
          return true;
        endif;
        return false;
        }
    )
    );
  }

  function __get($inputName){
    return $this->goodInputs[$inputName];
  }

  /**
  * setType
  *
  * Set an input type
  *
  * @param string $type Name value pair
  * @param array $properties Type properties (name => [properties => value, prop => val])
  */
  public function setType($name, $properties){
    if( is_string($name) && is_array($properties) ) {
      // Check that the passed in properties are valid props
        $validProps = $this->filterProperties($properties);
        // at this point no Fatal Errors...add type to object
        $this->types[$name] = $validProps;
    }
  }

  /**
  * filterProperties
  *
  * Loop through an array of properties and check that
  * the property and value are valid
  *
  * @param array $properties Properties array( 'prop' => value )
  * @return array Array of valid properties
  */
  private function filterProperties($properties){
    // store passed/valid properties here
    $validProps = array();
    // loop through properties
    foreach ($properties as $prop => $value):
      // check that property name is valid
      if(array_key_exists($prop, $this::$validTypeProps)){
        // check that property value is valid
        if( gettype($value) === $this::$validTypeProps[$prop]['dataType'] ){
          // everything checks out...add property to passed values
          $validProps[$prop] = $value;
        }else {
          throw new Exception("Invalid property value \"$value\" passed to $prop");
        }
      }else {
        // property doesnt exist...throw error
        throw new Exception("Invalid property $prop");
      }
    endforeach;

    return $validProps;
  }

  /**
  * expecting
  *
  * Set the expected inputs, and their type
  *
  * @param array $e 2D array ('inputName' => 'type') or ('inputName' => array(properties on the fly))
  */
  public function expecting($e){
    // e must be an Array
    if( is_array($e) ){
      // Check if any properties were passed in on the fly...
        // if so, filter them
        foreach ($e as $inputName => $option):
          if( is_array($option) ){
            //replace value with filtered properties
            $e[$inputName] = $this->filterProperties($option);
          }elseif( is_string($option) ) {
            //check that this is a set type
            if(!array_key_exists($option, $this->types)){
              throw new Exception("\"$option\" type not set");
            }
          }
        endforeach;
      $this->expectedInputs = $e;
    }
  }

  /**
  * validate
  *
  * Grab and validate the json data passed in
  *
  * @return boolean False on any failed inputs
  */
  public function validate(){
    // Grab JSON Data
      $json = $_POST["Formval"];
      // decode json
      $rawData = json_decode($json, true);
      // get Formval data
      $formData = $rawData['_f'];
      // remove formval from rawValues
      unset($rawData['_f']);
    // Now move extra values into object (like a passed value)
      foreach ($rawData as $key => $value) {
        $this->goodInputs[$key] = $value;
      }
    // Start Validating
      // store failed inputs here
      $failedInputs = array();

      foreach ($this->expectedInputs as $inputName => $type):
        // check if input name exists in passed values
        if(!array_key_exists($inputName, $formData)){
          // does not exist...was it required
          if( array_key_exists('required', $this->types[$type]) && $this->types[$type]['required'] === true ){
            //this input was required...add to failed inputs
            $failedInputs[$inputName] = array('required' => true);
          }
          // this input was not received and was not required...skip
          continue;
        }

        // if the input was a checkbox, $formValue will be an array with every selected checkbox
        $formValue = $formData[$inputName];
        // properties were passed in on the fly, in expecting(), $properties will be an array
          // alternatively we can simply add the properties to the object's type array, with a predefined string
          // when they are passed in at setType()
        $properties = (is_array($type)) ? $type : $this->types[$type];

        // Check form value against its properties
          $failedProperties = $this->valueVsProperties($formValue, $properties);
          // failed properties?
          if( count($failedProperties) > 0 ){
            // there were failed props...add input to failed inputs
            $failedInputs[$inputName] = $failedProperties;
          }else {
            // value checks out...add to object
            $this->goodInputs[$inputName] = $formValue;
          }
      endforeach;

      if(!empty($failedInputs)){
        // Add failed inputs to obejct
        $this->badInputs = $failedInputs;
        // no invalid inputs
        return false;
      }else {
        // All inputs check out!
        return true;
      }


    }

    /**
    * return
    *
    * Echo the json encoded failed inputs array and optionally kill script
    *
    * @param boolean $die Kills script on true, default false
    */
    public function return($die = true){
      // convert failed inputs into JSON
      $json = json_encode($this->badInputs);
      // spit out json
      echo $json;
      // kill script?
      if($die){
        die();
      }
    }

      /**
      * valueVsProperties
      *
      * Validate a value against properties
      *
      * @param mixed $value string || array $value
      * @param array $properties Properties
      * @return array Failed Properties and their values
      */
      private function valueVsProperties($val, $properties){
        // Store failed properties and their values here
          $failedProps = array();
          // when checkboxes are collected in JS the selected boxes are passed as an array
          if( is_array($val) ):
            // Loop through properties AND values
              foreach ($properties as $prop => $propVal):
                // Get Property Function
                  $func = $this::$validTypeProps[$prop]['validate'];
                  // loop through values
                  foreach ($val as $inputVal) {
                    // pass property function value
                    if( !call_user_func($func, $inputVal, $propVal) ){
                      // value failed property...add to failed properties
                      $failedProps[$inputVal] = array( $prop => $propVal );
                    }
                  }
              endforeach;
          else:
            // Loop through properties
              foreach ($properties as $prop => $propVal):
                // Get Property Function
                  $func = $this::$validTypeProps[$prop]['validate'];
                  // pass property function value
                  if( !call_user_func($func, $val, $propVal) ){
                    // value failed property...add to failed properties
                    $failedProps[$prop] = $propVal;
                  }
              endforeach;
          endif;
        // Return failed props
        return $failedProps;
      }

      /**
      * isValueEmpty
      *
      * Decides whether a value is empty
      *
      * @param string $val
      * @return boolean True on empty
      */
      private function isValueEmpty($val){
        if($val === '' || $val === null){
          return true;
        }
        return false;
      }

  }

?>
