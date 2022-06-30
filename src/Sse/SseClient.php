<?php

declare(strict_types=1);

namespace Empress\Sse;

use Amp\ByteStream\InputStream;
use Amp\ByteStream\IteratorStream;
use Amp\Emitter;
use Amp\Promise;

final class SseClient
{
    private Emitter $emitter;

    public function __construct()
    {
        $this->emitter = new Emitter();
    }

    public function comment(string $comment): Promise
    {
        return $this->emitter->emit($comment . "\n\n");
    }

    public function data(string $data): Promise
    {
        return $this->emitter->emit($this->chunkify($data) . "\n\n");
    }

    public function event(string $name, string $data): Promise
    {
        return $this->emitter->emit('event: ' . $name . "\n" . $this->chunkify($data) . "\n\n");
    }

    public function close(): void
    {
        $this->emitter->complete();
    }

    public function stream(): InputStream
    {
        return new IteratorStream($this->emitter->iterate());
    }

    private function chunkify(string $data): string
    {
        $chunks = explode("\n", $data);

        return \rtrim(\implode('', \array_map(fn ($chunk) => 'data: ' . $chunk . "\n", $chunks)));
    }
}
