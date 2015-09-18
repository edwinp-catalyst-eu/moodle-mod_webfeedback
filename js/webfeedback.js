// get the screen size and javascript version
$(document).ready(function() {
    // set the flash version
    $('input[name="uaflashversion"]').val(FlashDetect.major + "." + FlashDetect.minor + "r" + FlashDetect.revision);

    // screen width x height
    $('input[name="uascreensize"]').val(screen.width+'x'+screen.height);
});