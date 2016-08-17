<?php

use Carbon\Carbon;
use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

$tweets = [];
foreach (explode(PHP_EOL, file_get_contents(__DIR__ . '/tweets.tsv')) as $line) {
    if (empty($line)) continue;

    list($date, $text) = explode("\t", $line);
    $tweets[] = [
        'date' => Carbon::parse($date),
        'text' => $text,
    ];
}

(new Dotenv(__DIR__))->load();

$twitter = new \Twitter(
    getenv('TWITTER_CONSUMER_KEY'),
    getenv('TWITTER_CONSUMER_SECRET'),
    getenv('TWITTER_ACCESS_TOKEN'),
    getenv('TWITTER_ACCESS_TOKEN_SECRET')
);

$last_file = __DIR__ . '/' . getenv('LAST_FILE');
if (file_exists($last_file)) {
    $last = Carbon::createFromTimestamp(file_get_contents($last_file));
} else {
    $last = Carbon::now();
}

$now = Carbon::now();

echo 'Exec: ' . $last->toDateTimeString() . ' - ' . $now->toDateTimeString() . PHP_EOL;
foreach ($tweets as $tweet) {
    if ($tweet['date']->gt($last) && $tweet['date']->lte($now)) {
        echo 'Send: ' . $tweet['text'] . PHP_EOL;
        $twitter->send($tweet['text']);
    }
}

echo PHP_EOL;

file_put_contents($last_file, $now->timestamp);
