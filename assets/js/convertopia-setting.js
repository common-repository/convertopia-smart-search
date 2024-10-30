var $messageInputSelectors = '.search-field ';
var $messageHeadingsSelectors = '.woocommerce-products-header__title';
var $searchResultsText = 'Search Results: ';

(function e() {
    var e = document.createElement("script");
    e.type = "text/javascript",
    e.async = true,
    e.src = convertopia_settings.cdnURL;
    var t = document.getElementsByTagName("script")[0];
    t.parentNode.insertBefore(e, t)
})();

function getParameterByName( name ){  
    var regexS = "[\\?&]"+name+"=([^&#]*)", 
    regex = new RegExp( regexS ),
    results = regex.exec( window.location.search );
    if ( results == null ) {
        return "";
    } else {
        return decodeURIComponent(results[1].replace(/\+/g, " "));
    }
  }
(
function updateUI() {
    var searchTerm = getParameterByName("searchTerm");
    var inputFields = document.querySelectorAll($messageInputSelectors);
    var headings = document.querySelectorAll($messageHeadingsSelectors);
    for (var index = 0; index < inputFields.length; index++) {
        inputFields[index].value = searchTerm;
    }
    for (var index = 0; index < headings.length; index++) {
        headings[index].innerHTML = $searchResultsText + searchTerm;
    }
}
)();