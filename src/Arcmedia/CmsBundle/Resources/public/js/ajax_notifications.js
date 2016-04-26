toastr.options.closeButton = true;
toastr.options.positionClass = "toast-bottom-full-width";
$(document).ajaxError(function(event, jqXHR) {
    if ("responseJSON" in jqXHR) {
        if (Math.floor(jqXHR.status / 100) === 4) {
            toastr.warning(jqXHR.responseJSON.error.message);
        } else {
            toastr.error(jqXHR.responseJSON.error.message);
        }
    } else {
        if (Math.floor(jqXHR.status / 100) === 4) {
            toastr.warning("" + jqXHR.status + " " + jqXHR.statusText);
        } else {
            toastr.error("" + jqXHR.status + " " + jqXHR.statusText);
        }
    }
});
