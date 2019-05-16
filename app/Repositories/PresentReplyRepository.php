<?php

namespace App\Repositories;

interface PresentReplyRepository
{
    public function getRandomReplyMessage($point,$menuId);
}