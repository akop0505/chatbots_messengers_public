<?php

namespace App\Library\Messengers;


interface MessengerInterface
{
    public function textMessage(array $data): array;
    public function imageMessage(array $data): array;
    public function linkMessage(array $data): array;
    public function mediaMessage(array $data): array;
    public function checkStatus(array $data): array;

    public function hook(array $data): array;
}
