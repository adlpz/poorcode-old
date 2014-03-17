<style>
    body {
        color: #999;
        background: #222;
        font-family: monospace;
    }
</style>
<h3>Current cache</h3>
<p><?php $fmt=filemtime('posts.cache'); echo date('Y.m.d.H.i.s', $fmt) . ' (' . dechex($fmt) .')' ?></p>
<pre><code>
<?php echo htmlentities(`hexdump -C posts.cache`); ?>
</code></pre>