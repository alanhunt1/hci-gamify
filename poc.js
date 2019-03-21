// make a post request.  This is genericized so that the post can either take a form (in which case it will build JSON
// from the form elements) or a JSON formatted string.  If you include a callback, it will get called with the response from the API,
// and if you don't it just prints out the response to the page.
function submitJson(formname, suburl, data, callback) {

  // default the JSON to what is passed in the data param
  var form = data;

  // if the data block is not defined, then serialize the form that was passed in
  if (!form) {
    form = $(formname).serializeJSON();
  }

  // make an ajax call to post the form and deal with the response
  $.ajax({
    url: suburl,
    type: "POST",
    dataType: 'json',
    contentType: 'application/json; charset=UTF-8',
    data: JSON.stringify(form),
    success: function(maindta) {
      if (callback) {
        callback(maindta);
      } else {
        $("#display").html("<pre>" + JSON.stringify(maindta, null, 2) + "</pre>");
      }
    },
    error: function(jqXHR, textStatus, errorThrown) {
      // if there is an error, pop up an alert box.  You may want to replace this with a more subtle and context
      // friendly way of dealing with errors.
      var message = "ERROR:" + errorThrown;
      alert(message);
    }
  })
  return false;
}

// make a get request.  This assumes that the URL already contains the parameters necessary for the call.
// If you include a callback, it will get called with the response from the API, and if you don't it just
// prints out the response to the page.
function getJson(suburl) {
  $.ajax({
    url: suburl,
    type: "GET",

    success: function(maindta) {
      if (callback) {
        callback(maindta);
      } else {
        // note, this accomplishes the same thing as the jQuery notation in the post version of the function
        // above.  It's all just javascript - jQuery is providing "shorthand" for the DOM access
        document.getElementById("display").innerHTML = "<pre>" + JSON.stringify(maindta, null, 2) + "</pre>";
      }
    },
    error: function(jqXHR, textStatus, errorThrown) {
      var message = "ERROR:" + errorThrown;
      alert(errorThrown);
    }
  })
};

// the next two functions are an example of loading human readable values from a related values table
// and using them to populate a dropdown input, rather than requiring you know the specific id
function loadGametypeDropdown(gameTypes) {
  console.log(gameTypes);
  gameTypes['game_types'].forEach(gameType => {
    console.log(gameType);
    let option = document.createElement("option");
    option.text = gameType.game_type_name;
    option.value = gameType.game_type_id;
    document.getElementById('gametype_games').add(option);
  });
}

function loadDropdowns() {
  submitJson(null, 'gtcontroller.php', {
    'action': 'getGameTypes'
  }, loadGametypeDropdown);
}