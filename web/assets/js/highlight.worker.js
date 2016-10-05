onmessage = function(event) {
    importScripts('/assets/js/highlight.pack.js');
    var result = self.hljs.highlightAuto(event.data[0], [event.data[1]]);
    postMessage(result.value);
};
