/**
 * Check if inputs are empty
 *
 * @requires Array
 */
function hasEmpty(map, checkForEmpty = true) {
    var hasEmpty = false;
    map.forEach((id) => {
        if (!$("#" + id).val() || !checkForEmpty) {
            hasEmpty = true;
            $("#" + id).addClass("animate__animated animate__headShake");
            $("#" + id).addClass("empty-warning");
            setTimeout(() => {
                $("#" + id).removeClass("animate__animated animate__headShake");
                $("#" + id).removeClass("empty-warning");
            }, 800);
        }
    });
    return hasEmpty;
}