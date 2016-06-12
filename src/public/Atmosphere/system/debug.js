
function debuggerToggleCode (id) {
    var element = document.getElementById(id);
    if (element.length == 0) {
        console.log("Element dont exists");
        return false;
    }
    if (element.style.display == "block") {
        element.style.display = "none";
    } else {
        element.style.display = "block";
    }
    return false;
};

function debuggerRemoveItem (id) {
    var element = document.getElementById(id);
    if (element.length == 0) {
        console.log("Element dont exists");
        return false;
    }
    element.parentNode.removeChild(element);
    
    if (document.querySelectorAll(".debug.debug-container .debug-item").length == 0) {
        var overlay     =   document.querySelector(".debug.debug-overlay");
        var container   =   document.querySelector(".debug.debug-container");
        overlay.parentNode.removeChild(overlay);
        container.parentNode.removeChild(container);
    }
    return false;
}


function debuggerRemoveOverlay (event) {
    var code = event.keyCode || event.charCode|| event.which;
    if (code === 27) {
        document.querySelector(".debug-header .debug-close").click();
    }
}
// esc key
window.onkeydown = debuggerRemoveOverlay;
window.addEventListener('keydown', "debuggerRemoveOverlay");