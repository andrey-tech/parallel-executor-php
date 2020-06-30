# Parallel Executor

Простой класс на PHP 7.2+, позволяющий выполнять задачи в нескольких отдельных параллельных потоках PHP при помощи расширения [parallel](https://www.php.net/manual/ru/book.parallel.php).

## Содержание
<!-- MarkdownTOC levels="1,2,3,4,5,6" autoanchor="true" autolink="true" -->

- [Требования](#%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F)
- [Установка](#%D0%A3%D1%81%D1%82%D0%B0%D0%BD%D0%BE%D0%B2%D0%BA%D0%B0)
- [Класс `\App\ParallelExecutor`](#%D0%9A%D0%BB%D0%B0%D1%81%D1%81-appparallelexecutor)
    - [Методы класса](#%D0%9C%D0%B5%D1%82%D0%BE%D0%B4%D1%8B-%D0%BA%D0%BB%D0%B0%D1%81%D1%81%D0%B0)
    - [Дополнительные параметры](#%D0%94%D0%BE%D0%BF%D0%BE%D0%BB%D0%BD%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5-%D0%BF%D0%B0%D1%80%D0%B0%D0%BC%D0%B5%D1%82%D1%80%D1%8B)
- [Примеры](#%D0%9F%D1%80%D0%B8%D0%BC%D0%B5%D1%80%D1%8B)
- [Автор](#%D0%90%D0%B2%D1%82%D0%BE%D1%80)
- [Лицензия](#%D0%9B%D0%B8%D1%86%D0%B5%D0%BD%D0%B7%D0%B8%D1%8F)

<!-- /MarkdownTOC -->

<a id="%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F"></a>
## Требования

- PHP >=7.2 Thread Safe.
- PHP-расширение [parallel](https://www.php.net/manual/ru/book.parallel.php).
- Произвольный автозагрузчик классов, реализующий стандарт [PSR-4](https://www.php-fig.org/psr/psr-4/).

<a id="%D0%A3%D1%81%D1%82%D0%B0%D0%BD%D0%BE%D0%B2%D0%BA%D0%B0"></a>
## Установка

Установка через composer:
```
$ composer require andrey-tech/parallel-executor:"^1.0"
```
или добавить
```
"andrey-tech/parallel-executor": "^1.0"
```
в секцию require файла composer.json.

<a id="%D0%9A%D0%BB%D0%B0%D1%81%D1%81-appparallelexecutor"></a>
## Класс `\App\ParallelExecutor`

<a id="%D0%9C%D0%B5%D1%82%D0%BE%D0%B4%D1%8B-%D0%BA%D0%BB%D0%B0%D1%81%D1%81%D0%B0"></a>
### Методы класса

- `__construct(int $threads = 5, string $channelName = __CLASS__, int $channelСapacity = Channel::Infinite)`  
    Конструктор класса.
    * `$threads` - количество создаваемых сред исполнения, как отдельных потоков PHP;
    * `$channelName` - имя создаваемого именованного канала;
    * `$channelСapacity` - емкость создаваемого именованного канала, МиБ (0 - небуферизированный канал)
- `execute(\Closure $closure, array $argv = []) :void`  
    Отправляет на исполнение переданную задачу.
    * `$closure` - функция-замыкание, исполняющяя задачу;
    * `$argv` - аргументы функции.

<a id="%D0%94%D0%BE%D0%BF%D0%BE%D0%BB%D0%BD%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5-%D0%BF%D0%B0%D1%80%D0%B0%D0%BC%D0%B5%D1%82%D1%80%D1%8B"></a>
### Дополнительные параметры

Дополнительные параметры устанавливаются через публичные статические свойства класса `\App\ParallelExecutor`:

Свойство                | По умолчанию       | Описание
----------------------- | ------------------ | --------
`$autoloader`           | ''                 | Устанавливает файл автозагрузчика классов, подключаемый в каждой среде исполнения

<a id="%D0%9F%D1%80%D0%B8%D0%BC%D0%B5%D1%80%D1%8B"></a>
## Примеры

Выполнение 10 задач в 3 параллельных потоках PHP:
```php
/*
 * Создаем исполнитель c 3-я отдельными параллельными потоками PHP
 * и буферизированным каналом с именем по умолчанию '\App\ParallelExecutor'
 */ 
$executor = new \App\ParallelExecutor(3);

$i = 0;
$startTime = microtime(true);
while ($i < 10) {
    $i++;
    $executor->execute(
        function ($i, $startTime) {
            $sleep = random_int(1, 5);
            printf("[%.4f] %2d: Start sleeping {$sleep} s..." . PHP_EOL, microtime(true) - $startTime, $i);
            sleep($sleep);
            printf("[%.4f] %2d: DONE" . PHP_EOL, microtime(true) - $startTime, $i);
        },
        [ $i, $startTime ]
    );
}
```

Результат:
```
[0.0002]  1: Start sleeping 4 s...
[0.0002]  3: Start sleeping 1 s...
[0.0002]  2: Start sleeping 1 s...
[1.0015]  2: DONE
[1.0015]  3: DONE
[1.0019]  4: Start sleeping 1 s...
[1.0061]  5: Start sleeping 3 s...
[2.0069]  4: DONE
[2.0073]  6: Start sleeping 5 s...
[4.0011]  1: DONE
[4.0014]  7: Start sleeping 4 s...
[4.0073]  5: DONE
[4.0077]  8: Start sleeping 2 s...
[6.0084]  8: DONE
[6.0088]  9: Start sleeping 2 s...
[7.0118]  6: DONE
[7.0121] 10: Start sleeping 1 s...
[8.0066]  7: DONE
[8.0136]  9: DONE
[8.0166] 10: DONE
```





<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80"></a>
## Автор
© 2020 andrey-tech

<a id="%D0%9B%D0%B8%D1%86%D0%B5%D0%BD%D0%B7%D0%B8%D1%8F"></a>
## Лицензия
Данный код распространяется на условиях лицензии [MIT](./LICENSE).
