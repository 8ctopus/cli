<?php

declare(strict_types=1);

require_once('stubs/Commands/SendMailCommand.php');

use Swew\Cli\SwewCommander;
use Swew\Cli\Testing\TestHelper;
use TestSwew\Commands\SendMailCommand;

class TestSwewCommander extends SwewCommander
{
    public function __call(string $name, array $args): mixed
    {
        return $this->$name(...$args);
    }
}

function testFactory(array $args, ?array $commands = null): array
{
    $helper = new TestHelper();
    $sc = new TestSwewCommander($args, $helper->getOutput(), false);

    $sc->setCommands($commands ?? [
        SendMailCommand::class,
    ]);

    $sc->run();

    return [
        'SwewCommander' => $sc,
        'TestHelper' => $helper,
    ];
}

// = = = = = = = = = = = = =

it('SwewCommander :setCommands :getCommand', function () {
    $args = ['send:mail', 'user@mail.com', '--count', '1', '-silent'];

    [
        'SwewCommander' => $sc,
        'TestHelper' => $helper,
    ] = testFactory($args);

    expect($helper->getOutputContentAndClear())->toBe('Email sended');
});

it('SwewCommander :setCommands :getCommand - with error', function () {
    $args = ['send:mail', 'user@mail.com', '--count', '1'];

    expect(fn () => testFactory($args))->not()->toThrow("Get error for command 'send:mail': <b>-tax</> - is required");
});


it('SwewCommander :isNeedHelp :showHelp - Commander', function () {
    $args = ['-h'];

    [
        'SwewCommander' => $sc,
        'TestHelper' => $helper,
    ] = testFactory($args);

    $msg = <<<MSG
Available commands:
 send:mail: Command to send email

MSG;

    expect($helper->getOutputContentAndClear())->toBe($msg);
});

it('SwewCommander :isNeedHelp :showHelp - Command', function () {
    $args = ['send:mail', '-h'];

    [
        'SwewCommander' => $sc,
        'TestHelper' => $helper,
    ] = testFactory($args);

    $msg = <<<MSG
Description:
 Command to send email

Usage:
 send:mail [options]

Options:
 email
 -count      Count of mails
 -id         User ids
 -silent,-S  No log message

MSG;

    expect($helper->getOutputContentAndClear())->toBe($msg);
});
