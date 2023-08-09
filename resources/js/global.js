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

// Darkens / lightens a hex color
function shadeColor(color, percent) {

    var R = parseInt(color.substring(1,3),16);
    var G = parseInt(color.substring(3,5),16);
    var B = parseInt(color.substring(5,7),16);

    R = parseInt(R * (100 + percent) / 100);
    G = parseInt(G * (100 + percent) / 100);
    B = parseInt(B * (100 + percent) / 100);

    R = (R<255)?R:255;  
    G = (G<255)?G:255;  
    B = (B<255)?B:255;  

    R = Math.round(R)
    G = Math.round(G)
    B = Math.round(B)

    var RR = ((R.toString(16).length==1)?"0"+R.toString(16):R.toString(16));
    var GG = ((G.toString(16).length==1)?"0"+G.toString(16):G.toString(16));
    var BB = ((B.toString(16).length==1)?"0"+B.toString(16):B.toString(16));

    return "#"+RR+GG+BB;
}

// Adds opacity to hex color
function addAlpha(color, opacity) {
    // coerce values so ti is between 0 and 1.
    var _opacity = Math.round(Math.min(Math.max(opacity || 1, 0), 1) * 255);
    return color + _opacity.toString(16).toUpperCase();
}