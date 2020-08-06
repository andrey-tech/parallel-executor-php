# Parallel Executor

Простой класс на PHP 7.2+, позволяющий выполнять задачи в нескольких параллельных потоках исполнения при помощи PHP-расширения [parallel](https://www.php.net/manual/ru/book.parallel.php).

Docker-образ для быстрой проверки результата работы примера находится в репозитории на [Docker Hub](https://hub.docker.com/r/andreytech/parallel-executor-php-example).  

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

Выполнение 10 задач в 3 параллельных потоках PHP с буферизированным каналом:
```php
// Создаем исполнитель c 3-я отдельными параллельными потоками PHP и буферизированным каналом
$executor = new \App\ParallelExecutor(3);

$i = 0;
$startTime = microtime(true);
while ($i < 10) {
    $i++;
    printf("[%.4f]  execute" . PHP_EOL, microtime(true) - $startTime, $i);
    $executor->execute(
        function ($i) use ($startTime) {
            $sleep = random_int(1, 5);
            printf("[%.4f] %2d: Start sleeping {$sleep} s..." . PHP_EOL, microtime(true) - $startTime, $i);
            sleep($sleep);
            printf("[%.4f] %2d: DONE" . PHP_EOL, microtime(true) - $startTime, $i);
        },
        [ $i ]
    );
}
```

Результат:
```
[0.0000]  execute
[0.0001]  execute
[0.0002]  execute
[0.0002]  1: Start sleeping 5 s...
[0.0002]  execute
[0.0003]  2: Start sleeping 4 s...
[0.0003]  execute
[0.0003]  3: Start sleeping 5 s...
[0.0003]  execute
[0.0004]  execute
[0.0004]  execute
[0.0004]  execute
[0.0005]  execute
[4.0008]  2: DONE
[4.0010]  4: Start sleeping 4 s...
[5.0005]  3: DONE
[5.0005]  1: DONE
[5.0007]  5: Start sleeping 5 s...
[5.0008]  6: Start sleeping 1 s...
[6.0020]  6: DONE
[6.0022]  7: Start sleeping 4 s...
[8.0016]  4: DONE
[8.0018]  8: Start sleeping 1 s...
[9.0023]  8: DONE
[9.0025]  9: Start sleeping 5 s...
[10.0014]  5: DONE
[10.0015] 10: Start sleeping 2 s...
[10.0026]  7: DONE
[12.0017] 10: DONE
[14.0036]  9: DONE
```

Выполнение 10 задач в 3 параллельных потоках PHP с НЕ буферизированным каналом:
```php
// Создаем исполнитель c 3-я отдельными параллельными потоками PHP и НЕ буферизированным каналом
$executor = new \App\ParallelExecutor(3, 'taskChannel', 0);

$i = 0;
$startTime = microtime(true);
while ($i < 10) {
    $i++;
    printf("[%.4f]  execute" . PHP_EOL, microtime(true) - $startTime, $i);
    $executor->execute(
        function ($i) use ($startTime) {
            $sleep = random_int(1, 5);
            printf("[%.4f] %2d: Start sleeping {$sleep} s..." . PHP_EOL, microtime(true) - $startTime, $i);
            sleep($sleep);
            printf("[%.4f] %2d: DONE" . PHP_EOL, microtime(true) - $startTime, $i);
        },
        [ $i ]
    );
}
```

Результат:
```
[0.0000]  execute
[0.0002]  1: Start sleeping 2 s...
[0.0002]  execute
[0.0003]  2: Start sleeping 3 s...
[0.0004]  execute
[0.0005]  3: Start sleeping 4 s...
[0.0005]  execute
[2.0008]  1: DONE
[2.0009]  4: Start sleeping 5 s...
[2.0010]  execute
[3.0006]  2: DONE
[3.0007]  5: Start sleeping 2 s...
[3.0007]  execute
[4.0007]  3: DONE
[4.0009]  6: Start sleeping 3 s...
[4.0010]  execute
[5.0012]  5: DONE
[5.0013]  7: Start sleeping 5 s...
[5.0014]  execute
[7.0013]  4: DONE
[7.0013]  6: DONE
[7.0015]  8: Start sleeping 5 s...
[7.0015]  execute
[7.0018]  9: Start sleeping 1 s...
[7.0018]  execute
[8.0020]  9: DONE
[8.0022] 10: Start sleeping 4 s...
[10.0017]  7: DONE
[12.0025] 10: DONE
[12.0025]  8: DONE
```

<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80"></a>
## Автор
© 2020 andrey-tech

<a id="%D0%9B%D0%B8%D1%86%D0%B5%D0%BD%D0%B7%D0%B8%D1%8F"></a>
## Лицензия
Данный код распространяется на условиях лицензии [MIT](./LICENSE).
