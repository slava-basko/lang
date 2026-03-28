BLang
=====

Language to write business expressions.

Standalone no-dependency library.

```php
use Basko\Lang\Parser;

$parser = new Parser();

$context = new EvaluateContext();
$context->addVariable('user', $user);

$result = $parser->parse('user.countOrders > 10 && user.status in ["vip", "premium"]')->evaluate($context);
```
