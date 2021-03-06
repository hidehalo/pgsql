<?php
require_once __DIR__.'/../vendor/autoload.php';

use Dazzle\Loop\Loop;
use Dazzle\PgSQL\Database;
use Dazzle\Loop\Model\SelectLoop;
use Dazzle\PgSQL\Connection\ConnectionInterface;
use Dazzle\PgSQL\Result\CommandResult;
use Dazzle\PgSQL\Result\TupleResultStatement;
use Dazzle\PgSQL\Transaction\TransactionInterface;

$loop = new Loop(new SelectLoop());

$db = new Database($loop, [
    'host' => '192.168.99.100',
    'port' => 35432,
    'user' => 'postgres',
    'dbname' => 'postgres'
]);

$db->on('transaction:begin', function () use ($loop, $db) {
    echo 'Transaction Stated'.PHP_EOL;
});

$db->on('transaction:end', function () use ($loop, $db) {
    $db->getConnection()->then(function (ConnectionInterface $conn) use ($loop) {
        $conn->query('select * from demo order by id desc limit 1')
        ->then(function (TupleResultStatement $tuple) use ($loop) {
            print_r($tuple->fetchAll());
            $loop->stop();
            echo 'Transaction End'.PHP_EOL;
        });
    });
});

$db->beginTransaction()
 ->then(function (TransactionInterface $trans) use ($loop) {
    $trans->query('select * from demo order by id desc limit 1')
    ->then(function (TupleResultStatement $tuple) {
        print_r($tuple->fetchAll());
    });
    $trans->execute('insert into demo default values')
    ->then(function (CommandResult $result) use ($trans) {
        if ($result->getAffectedRows() > 0) {
            $trans->rollback();
        }
    });
    
});

$loop->start();