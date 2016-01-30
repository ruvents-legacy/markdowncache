# Description

This tool parses your markdown files using [Parsedown](http://parsedown.org/) and stores html versions in a specified location.

# Installation

    $ composer require vudaltsov/markdowncache

# Usage
```php
$mc = new MarkdownCache('path/to/cache/dir');
echo $mc->getHtml('path/to/file.md');
// or
include $mc->getPath('path/to/file.md');
// or
$mc->render('path/to/file.md');
```
