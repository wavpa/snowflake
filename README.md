# Snowflake

雪花算法生成全局唯一ID

## 安装

```sh
composer require wavpa/snowflake -vvv
```

## 使用

```php
use Wavpa\Snowflake\Snowflake;

$snowflake = new Snowflake();

$id = $snowflake->nextId();
// 412471893908520960
```

## 在 Laravel 中使用

可以用两种方式来获取`Wavpa\Snowflake\Snowflake`实例：

1. 方法参数注入

```php
public function nextId(Request $request, Snowflake $snowflake)
{
    return $snowflake->nextId();
}
```

2. 服务名访问

```php
public function nextId(Request $request)
{
    return app('snowflake')->nextId();
}
```

## License

MIT
