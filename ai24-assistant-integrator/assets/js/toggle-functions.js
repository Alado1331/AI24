// toggle-functions.js
function toggleLock(checkbox, fieldSelector) {
    var field = jQuery(fieldSelector);
    if (jQuery(checkbox).is(':checked')) {
        field.removeAttr('readonly');
    } else {
        field.attr('readonly', 'readonly');
    }
}

function toggleVisibility(fieldSelector) {
    var field = jQuery(fieldSelector);
    if (field.attr('type') === 'password') {
        field.attr('type', 'text');
    } else {
        field.attr('type', 'password');
    }
}