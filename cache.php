<h3>Current cache</h3>
<p><?php $fmt=filemtime('posts.cache'); echo date('Y.m.d.H.i.s', $fmt) . ' (' . dechex($fmt) .')' ?></p>
<pre>
<?php system('hexdump -C posts.cache'); ?>
</pre>