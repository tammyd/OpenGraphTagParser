OpenGraphTagParser
==================

Utility class to retrieve the open graph tags from a snippet (or full page) of html.

####Note: Requires PHP 5.4+

Sample usage:

```php
<?php
use OpenGraph\Parser;

//grab some html content however you see fit
$html = file_get_contents('http://www.cbc.ca/player/News/ID/2397337814/');

$parser = new Parser($html); //parse it

echo $parser->title; //Skydiving seniors
```

The only file you need to actually use this class can be found [here](https://raw.github.com/tammyd/OpenGraphTagParser/master/OpenGraph/Parser.php).

For more examples in the form of unit tests, please clone the entire repo and see OpenGraph/Parser/Tests/ParserTest.php.
You can run the full suite of unit tests as follows:

```bash

$ cd OpenGraphTagParser
$ phpunit

```
