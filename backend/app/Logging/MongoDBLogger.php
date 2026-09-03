<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\MongoDBHandler;
use Monolog\Handler\StreamHandler;
use MongoDB\Client;

class MongoDBLogger
{
    public function __invoke(array $config)
    {
        $logger = new Logger('mongodb');

        try {
            $mongo = config('database.connections.mongodb');

            $client = new Client(
                sprintf(
                    'mongodb://%s:%s@%s:%s',
                    urlencode($mongo['username']),
                    urlencode($mongo['password']),
                    $mongo['host'],
                    $mongo['port']
                )
            );

            $logger->pushHandler(new MongoDBHandler($client, $mongo['database'], 'logs'));
        } catch (\Exception $e) {
            // MongoDB indisponível: faz fallback para arquivo em vez de descartar os logs
            $logger->pushHandler(new StreamHandler(storage_path('logs/mongodb-fallback.log'), Logger::WARNING));
            $logger->warning('MongoDBLogger: falha ao conectar no MongoDB', ['exception' => $e->getMessage()]);
        }

        return $logger;
    }
}
