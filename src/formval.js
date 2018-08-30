(function(global){

  var Formval = function(form, callback, options, extra){
    return new Formval.init(form, callback, options, extra);
  }

  /**
  * validateFormElement
  *
  * Checks whether an HTML Element is a valid form element
  *
  * @param HTMLElement formElement The DOM Element
  * @return boolean True on valid
  */
  var validateFormElement = function(formElement){
    if(formElement === null || formElement.nodeName !== 'FORM'){
      throw Error('Invalid form element.');
      return false;
    }
    this.form = formElement;
    console.log('Valid Form element');
    return true;
  };

  /**
  * getValues
  *
  * Retreive all values from a form DOM Element
  *
  * @param HTMLElement form Form Element
  * @return object Contains name and vale {inputName : inputValue}
  */
  var getValues = function(form){
    // store values here (name:value)
    var values = {};
    // Handle Text Inputs (Excludes checkbox and radio)
    // OPTIMIZE: Expand getting and setting of inputs into seperate functions
      var textInputs = form.querySelectorAll('INPUT:not([type="checkbox"]):not([type="radio"]), TEXTAREA');
      for(var i=0;i<textInputs.length;i++){
        var input = textInputs[i];
        // set variable inside if statement
        if( name = input.getAttribute('name') ){
          // store input name and value
          values[name] = input.value;
        }
      }
    // Handle Checkboxes
      var checkboxInputs = form.querySelectorAll('INPUT[type=checkbox]');
      for(var i=0;i<checkboxInputs.length;i++){
        var input = checkboxInputs[i];
        if( (name = input.getAttribute('name')) && input.checked){
          // check if an object with this name has already been created
          if(values[name]){
            values[name].push(input.value)
          }else {
            //create object and set to array
            values[name] = [input.value];
          }
        }
      }
    // Handle Radio Buttons
      var radioInputs = form.querySelectorAll('INPUT[type=radio]');
      for(var i=0;i<radioInputs.length;i++){
        var input =radioInputs[i];
        if( (name = input.getAttribute('name')) && input.checked){
          values[name] = input.value;
        }
      }
    // Handle Select Elements
      var selectElements = form.querySelectorAll('SELECT');
      for(var i=0;i<selectElements.length;i++){
        var input = selectElements[i];
        if(name = input.name){
          values[name] = input.value;
        }
      }

      return values;

  };

  /**
   * Encode key value pair to be used as query string
   * @param  object params Parameters object {key:value}
   * @return string Encoded query string
   */
  function encodeParams(params) {
    var encodedString = '';
    for (var prop in params) {
        if (params.hasOwnProperty(prop)) {
            if (encodedString.length > 0) {
                encodedString += '&';
            }
            encodedString += encodeURI(prop + '=' + params[prop]);
        }
    }
    return encodedString;
}

  /**
  * sendValues
  *
  * Send values to URL w/ AJAX
  *
  * @param string url URL to send ajax request to
  * @param string method POST
  * @param object values Form values from getValues()
  * @param object extra Extra values to be passed to script along with values
  * @param function callback Callback function to be executed after Ajax Call...
  *   will be passed (1) Form element (2) Response
  * @return void
  */
  var sendValues = function(url, method, values, extra, callback){
    var self = this;

    //console.log(JSON.stringify(formvalObject));
    // init xhttp request
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function(){
      if(this.readyState == 4 && this.status == 200){
        callback(self.form, this.responseText);
      }
    };
    xhttp.open(method, url, true);
    xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhttp.send('_f=' + encodeURIComponent(JSON.stringify(values)) + '&' + encodeParams(extra));
  }

  /*
  * Bind Function to Formval's Prototype
  */
  Formval.prototype = {
    /**
    * Set the submit button for the form, the element will
    * then have an event listener on click.
    * @param HTMLElement element Submit button
    */
    setSubmit: function(element){
      // only one element
      if(typeof element === 'string'){
        // id of element passed in
        element = document.getElementById(element);
      }
      if(element === null || element.length || element.toString() === '[object HTMLCollection]'){
        throw Error('Invalid submit element.');
      }
      //set click handler on element
      element.onclick = this.submit.bind(this, element, this.form);
      // make chainable
      return this;
    },
    /**
    * Do action on submit button after click.
    * @param function callback 'this' refers to button, passed param ... form
    */
    submitButtonClick: function(callback){
      if(typeof callback === 'function'){
        this.submitbtnCallback = callback;
      }
    },
    /**
    * Set extra values to be passed in with form values
    * @param object Extra Values
    */
    extras: function(extras){
      if(typeof extras === 'object'){
        if(this.extra){
          // extra has already been created when object was constructed
          // Combine objects
          for (var ex in extras) {
            if (extras.hasOwnProperty(ex)) {
              this.extra[ex] = extras[ex];
            }
          }
        }else {
          this.extra = extras;
        }
      }
      return this;
    },
    /**
    * Submits form. Called when submit button is clicked.
    * @param HTMLElement element Submit button
    * @param HTMLElement form The Form from the DOM
    */
    submit: function(element, form){
      // is there a callback defined for the button?
      if(this.submitbtnCallback && element){
        // make 'this' refer to element, pass in form
        this.submitbtnCallback.call(element, form);
      }
      //get values from the form
      var values = getValues(form);
      // get params and set defaults
      var url = this.options.url || form.getAttribute('action') || global.location.href;
      var method = this.options.method || form.getAttribute('method') || 'POST';
      var extra = this.extra;
      sendValues.call(this, url, method, values, extra, this.callback);
    }
  }


  /**
  * Formval.init
  *
  * This is what is returned when F() or Formval() is called
  */
  Formval.init = function(form, callback, options, extra){

    // if callback and option were passed in, attach to object
    if(typeof callback === 'function'){
      this.callback = callback;
      console.log('function');
    }
    if(typeof extra === 'object'){
      this.extra = extra;
    }
    if(typeof options === 'object'){
      this.options = options;
    }else {
      this.options = {};
    }

    // query selector has been passed in
    if(typeof form === 'string'){
      form = document.getElementById(form);
    }
    // throw an error if this is not a form element
    if( (this.options.ignoreElement === FALSE) && !validateFormElement.call(this, form) ){
      throw Error( 'First argument expected to be FORM element.' );
    }

    // disable default form submit
    this.form.onsubmit = function(){
      return false;
    }

    // if a submit button exists inside form by adding 'data-value="formval"' to element
    // init var in statement
    if(submitBtn = form.querySelector('[data-formval=submit]')){
      submitBtn.onclick = this.submit.bind(this, submitBtn, this.form);
    }

  };

  // Inherit Formal protos
  Formval.init.prototype = Formval.prototype;

  // attach function to window obj
  // add alies "F"
  global.Formval = global.F = Formval;

}(window))
