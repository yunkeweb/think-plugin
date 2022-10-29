<h1> think-plugin </h1>

<p> 一款tp6插件包.</p>


## Installing

```shell
$ composer require yunkeweb/think-plugin -vvv
```

## Usage
需要在 composer.json 中
```shell
"autoload": {
        "psr-4": {
            "plugin\\": "plugin"
        }
    },
```
运行
```shell
composer dump
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/yunkeweb/thinkphp-addon/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/yunkeweb/thinkphp-addon/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT