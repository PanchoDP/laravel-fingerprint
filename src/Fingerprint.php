<?php

declare(strict_types=1);

namespace Panchodp\LaravelFingerprint;

use Illuminate\Http\Request;

final class Fingerprint
{
    public function __construct(
        private readonly Request $request,
    ) {}

    public function generateFingerPrint(): string
    {
        $parts = [$this->getUserAgent(), $this->getLanguage(), $this->getAcceptHeaders()];

        if (config('laravel_fingerprint.include_ip', false)) {
            array_unshift($parts, $this->getIp());
        }

        return hash('sha256', implode('|', $parts));
    }

    private function getUserAgent(): string
    {
        return $this->request->userAgent() ?? '';
    }

    private function getAcceptHeaders(): string
    {
        $value = $this->request->header('Accept-Encoding');

        return is_string($value) ? $value : '';
    }

    private function getLanguage(): string
    {
        $value = $this->request->header('Accept-Language');

        return is_string($value) ? $value : '';
    }

    private function getIp(): string
    {
        return $this->request->ip() ?? '';
    }
}
