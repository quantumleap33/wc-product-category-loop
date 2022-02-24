document.addEventListener( 'DOMContentLoaded', function() {
  var elms = document.getElementsByClassName( 'splide' );  
  for ( var i = 0; i < elms.length; i++ ) {
    var splide = new Splide( elms[ i ], {
        type: 'loop',
        perPage: 3,
        breakpoints: {
          768: {
            perPage: 1,
            pagination: true,
          },
        },
        autoplay: 'pause',
        gap: 20,
        arrows: false,
        pagination: false,
    } ).mount(); 
  }
} );
