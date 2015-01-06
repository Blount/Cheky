
(function () {
    var titles = document.querySelectorAll(".formbox");
    for (var i = 0; i < titles.length; i++) {
        titles[i].addEventListener("click", function (e) {
            e.stopPropagation();
            var dlElement = document.getElementById(this.id + "-content");
            var span = this.querySelector("span");
            if (dlElement.style.display != "block") {
                dlElement.style.display = "block";
                span.innerHTML = "(cliquez pour fermer)";
            } else {
                dlElement.style.display = "none";
                span.style.display = "inline";
                span.innerHTML = "(cliquez pour ouvrir)";
            }
        }, false);
    }
})();