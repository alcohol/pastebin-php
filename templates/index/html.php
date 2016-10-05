<?php $this->layout('template'); ?>

<div class="container-fluid m-b-2 p-x-0">
    <div class="topbar w-100 d-block clearfix">
        <p class="d-inline">
            <span class="fa fa-fw fa-terminal" aria-hidden="true"></span>
            version <a href="https://github.com/alcohol/paste.robbast.nl/commit/<?php echo $version; ?>"><?php echo $version; ?></a>
        </p>
        <p class="d-inline m-l-1 hidden-md-down">
            <span class="fa fa-fw fa-github" aria-hidden="true"></span>
            source <a href="https://github.com/alcohol/paste.robbast.nl/" aria-label="View source on github.com">
                github.com/alcohol/paste.robbast.nl
            </a>
        </p>
    </div>
</div>

<div class="container">
    <form action="<?php echo $href; ?>" method="POST" accept-charset="UTF-8">
        <div class="form-group">
            <label for="paste">Paste</label>
            <textarea class="form-control" name="paste" id="paste" rows="20"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
