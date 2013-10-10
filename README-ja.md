# QueryPath RepeatExtension

RepeatExtension は [QueryPath](http://querypath.org/) 用エクステンションです。
以下の拡張メソッドを提供します。

1. `repeat($counter [, $callback])`
2. `repeatInner($counter [, $callback])`


## インストール

[Composer](http://getcomposer.org/) を使用して以下を実行してください。

```sh
$ php composer.phar require noi/querypath-repeat "*"
```

または、`composer.json` を編集し、以下の行を含めてください。

```json
{
    "require": {
        "noi/querypath-repeat": "*"
    }
}
```


## 使い方

### repeat()

```php
\QueryPath\DOMQuery repeat(int|array|\Traversable $counter [, callable $callback ])
```

* このメソッドは、選択中のノードをテンプレートとして、
  `$counter` で指定した回数分だけノードの複製を作ります。
* `$callback` を指定すると、
  `$counter` の現在値に応じたノードの修正が可能になります。

簡単な例：

```php
<?php
require_once '/path/to/vendor/autoload.php';
QueryPath::enable('Noi\QueryPath\RepeatExtension');

$qp = qp('<?xml version="1.0"?><root><item>Test</item></root>');
$qp->find('item')->repeat(5);
$qp->writeXML();
```

表示結果：

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

引数にコールバックを使う場合の例：

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

出力結果：

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

* `repeat()` との違いは、複製対象がノード自体ではなく子ノードになる点です。

簡単な例：

```php
<?php
require_once '/path/to/vendor/autoload.php';
QueryPath::enable('Noi\QueryPath\RepeatExtension');

$qp = qp('<?xml version="1.0"?><root><div>Test</div><div><span>Child A</span><b>Child B</b></div></root>');
$qp->find('div')->repeatInner(3);
$qp->writeXML();
```

出力結果：

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

引数にコールバックを使う場合の例：

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

出力結果：

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

RepeatExtensionクラスのライセンスは、MITライセンスです。
詳しくは `LICENSE` ファイルの規約を確認してください。
