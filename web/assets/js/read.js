addEventListener('load', function() {
    var code = document.querySelector('#code');
    var selector = document.querySelector('#hljs-selector');

    if (window.Worker) {
        var worker = new Worker('/assets/js/highlight.worker.js');
        worker.onmessage = function(event) { code.innerHTML = event.data; }
    }

    hljs.listLanguages().forEach(function (language) {
        var option = document.createElement('option');
        option.value = language;
        option.innerHTML = language;
        this.appendChild(option);
    }, selector);

    selector.addEventListener('input', function (event) {
        var list = event.target;
        var index = list.selectedIndex;
        var language = list[index].value;
        if (window.Worker) {
            worker.postMessage([code.textContent, language]);
        } else {
            code.className = language;
            hljs.highlightBlock(code);
        }
    });
});
