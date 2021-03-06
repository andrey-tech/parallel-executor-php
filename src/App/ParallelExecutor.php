<?php

/**
 * Класс ParallelExecutor.
 * Выполняет задачи в нескольких отдельных параллельных потоках при помощи расширения parallel.
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @license   MIT
 * @see https://github.com/andrey-tech/parallel-executor-php
 *
 * @version 1.0.1
 *
 * v1.0.0 (28.06.2020) Начальный релиз
 * v1.0.1 (29.06.2020) Исправлен баг с именем переменной $channelCapacity
 *
 */

declare(strict_types = 1);

namespace App;

use parallel\{Runtime, Channel};
use Closure;

class ParallelExecutor
{
    /**
     * Файл автозагрузчика классов, подключаемый в каждой среде исполнения
     * @var string
     */
    public static string $autoloader = '';

    /**
     * Объект класса именованного канала \parallel\Channel
     * @var object
     */
    private \parallel\Channel $channel;

    /**
     * Массив объектов класса среды исполнения \parallel\Runtime
     * @var array
     */
    private array $workers = [];

    /**
     * Конструктор
     * @param int $threads Количество создаваемых сред исполнения, как отдельных потоков
     * @param string $channelName Имя создаваемого именованного канала
     * @param int $channelCapacity Емкость именованного канала, МиБ (0 - небуферизированный канал)
     */
    public function __construct(
        int $threads = 5,
        string $channelName = __CLASS__,
        int $channelCapacity = Channel::Infinite
    ) {
        // Создаем именованный небуферизированный или буферизированный канал
        if ($channelCapacity == 0) {
            $this->channel = Channel::make($channelName);
        } else {
            $this->channel = Channel::make($channelName, $channelCapacity);
        }

        // Создаем заданное число сред исполнения, как отдельных потоков
        for ($thread = 0; $thread < $threads; $thread++) {
            $this->workers[ $thread ] = new Runtime();
            $this->workers[ $thread ]->run(
                Closure::fromCallable([ $this, 'thread' ]),
                [ $channelName, self::$autoloader ]
            );
        }
    }

    /**
     * Отправляет на исполнение переданную задачу
     * @param  Closure $closure Функция-замыкание, исполняющая задачу
     * @param  array    $argv    Аргументы функции
     * @return void
     */
    public function execute(Closure $closure, array $argv = []) :void
    {
        // Отправляем данные в именованный канал
        $this->channel->send([
            'closure' => $closure,
            'argv'    => $argv
        ]);
    }

    /**
     * Исполняет задачи в отдельном потоке
     * @param  string $channelName Имя именованного канала
     * @param  string $autoloader Файл автозагрузчика классов, подключаемый в каждой среде исполнения
     * @return void
     */
    private function thread(string $channelName, string $autoloader) :void
    {
        // Подключаем файл автозагрузчика классов, если он задан
        if (! empty($autoloader)) {
            require_once $autoloader;
        }

        // Открываем именованный канал
        $channel = Channel::open($channelName);

        // Извлекаем данные из именованного канала и выполняем задачи
        while ($data = $channel->recv()) {
            ($data['closure'])(...$data['argv']);
        }
    }

    /**
     * Деструктор
     */
    public function __destruct()
    {
        // Посылаем в канал признак завершения работы для каждой среды исполнения
        foreach ($this->workers as $worker) {
            $this->channel->send(false);
        }

        // Отправляем всем средам исполнения сигнал завершения работы
        foreach ($this->workers as $worker) {
            $worker->close();
        }

        // Закрываем именованный канал
        $this->channel->close();
    }
}
