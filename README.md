# QueryPath RepeatExtension

RepeatExtension is a [QueryPath](http://querypath.org/) extension that adds the
following methods:

1. `repeat($counter [, $callback])`
2. `repeatInner($counter [, $callback])`


## Installation

With [Composer](http://getcomposer.org/), run:

```sh
$ php composer.phar require noi/querypath-repeat "*"
```

Alternatively, you can edit your `composer.json` manually and add the following:

```json
{
    "require": {
        "noi/querypath-repeat": "*"
    }
}
```


## Usage

### repeat()

```php
\QueryPath\DOMQuery repeat(int|array|\Traversable $counter [, callable $callback ])
```

A quick example:

```php
<?php
require_once '/path/to/vendor/autoload.php';
QueryPath::enable('Noi\QueryPath\RepeatExtension');

$qp = qp('<?xml version="1.0"?><root><item>Test</item></root>');
$qp->find('item')->repeat(5);
$qp->writeXML();
```

OUTPUT:

```xml
<?xml version="1.0"?>
<root>
  <item>Test</item>
  <item>Test</item>
  <item>Test</item>
  <item>Test</item>
  <item>Test</item>
</root>
```

And here is an example with a callback function:

```php
<?php
require_once '/path/to/vendor/autoload.php';
QueryPath::enable('Noi\QueryPath\RepeatExtension');

$qp = qp('<?xml version="1.0"?><root><number>Test</number><name>Test</name></root>');

$qp->find('number')->repeat(3, function ($i, $node) {
    qp($node)->append(':' . $i);
});

$names = array('Apple', 'Orange', 'Lemon');
$qp->find('name')->repeat($names, function ($name, $node) {
    qp($node)->text(strtoupper($name));
});

$qp->writeXML();
```

OUTPUT:

```xml
<?xml version="1.0"?>
<root>
  <number>Test:0</number>
  <number>Test:1</number>
  <number>Test:2</number>
  <name>APPLE</name>
  <name>ORANGE</name>
  <name>LEMON</name>
</root>
```


### repeatInner()

```php
\QueryPath\DOMQuery repeatInner(int|array|\Traversable $counter [, callable $callback ])
```

A quick example:

```php
<?php
require_once '/path/to/vendor/autoload.php';
QueryPath::enable('Noi\QueryPath\RepeatExtension');

$qp = qp('<?xml version="1.0"?><root><div>Test</div><div><span>Child A</span><b>Child B</b></div></root>');
$qp->find('div')->repeatInner(3);
$qp->writeXML();
```

OUTPUT:

```xml
<?xml version="1.0"?>
<root>
  <div>TestTestTest</div>
  <div>
    <span>Child A</span>
    <b>Child B</b>
    <span>Child A</span>
    <b>Child B</b>
    <span>Child A</span>
    <b>Child B</b>
  </div>
</root>
```

And here is an example with a callback function:

```php
<?php
require_once '/path/to/vendor/autoload.php';
QueryPath::enable('Noi\QueryPath\RepeatExtension');

$qp = qp('<?xml version="1.0"?><root><number>Test</number><items><name/></items></root>');

$qp->find('number')->repeatInner(3, function ($i, $node) {
    qp($node)->text($i);
});

$names = array('Apple', 'Orange', 'Lemon');
$qp->find('items')->repeatInner($names, function ($name, $node) {
    qp($node)->find('name')->text(strtoupper($name));
});

$qp->writeXML();
```

OUTPUT:

```xml
<?xml version="1.0"?>
<root>
  <number>012</number>
  <items>
    <name>APPLE</name>
    <name>ORANGE</name>
    <name>LEMON</name>
  </items>
</root>
```


## License

RepeatExtension is licensed under the MIT License - see the `LICENSE` file for details.
