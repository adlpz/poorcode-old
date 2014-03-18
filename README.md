poorcode
=======


The code that powers poorcode.com, a random blog I started. I sort of start new blogs every weekend, so there's that.

Poorcode is powered by a bunch of PHP scattered all over the place. As I already made a full-client-side-single-page-tingiemagic two weeks ago, and a hardcore-written-in-Pascal-static-page-generator yesterday, so now I just went back to having a PHP CGI/mod_whatever/etc renderer like if this was 2008 or something.

Of course using a database would mean losing my cool coder card so I just use text files for storage. As this happens to be slow as hell, the system builds a binary ultra-optimised cache monstrosity and reads the info from there. On any change (mtime anyone?) on the posts, it regenerates this cache on the next load. Now I only have to add indexing, transactions and relational integrity and I will have an amazing blog system without using a database! Wait, nevermind...

Additionally, poorcode uses a router which is basically some regexes, a template engine which is basically some regexes, and probably also some more regexes at some point.

If this code is of any use it may be as a reference about how you should write crap PHP in the 21st century. That is, full of objects, inheritances, namespaces, but crap anyway. Best of both worlds.

Have fun people.
