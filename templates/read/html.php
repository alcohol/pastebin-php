<?php $this->layout('template'); ?>

<div class="container-fluid m-b-2 p-x-0">
    <div class="topbar w-100 d-block clearfix">
        <p class="d-inline">
            <span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span>
            <a href="<?php echo $hrefNew; ?>">new</a>
        </p>
        <p class="d-inline m-l-1">
            <span class="fa fa-fw fa-code" aria-hidden="true"></span>
            <a href="<?php echo $hrefRaw; ?>">raw</a>
        </p>
    </div>
</div>

<div class="container-fluid">

    <form class="form-inline pull-xs-right" id="hljs-form">
        <select class="form-control form-control-sm" id="hljs-selector">
            <option disabled selected>Syntax:</option>
        </select>
    </form>

    <pre><code id="code"><?php echo $this->e($paste); ?></code></pre>
</div>

<script src="/assets/js/highlight.pack.js"></script>
<script>
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
</script>
