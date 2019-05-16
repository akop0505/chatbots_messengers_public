<?php

namespace App\Repositories;

interface UserRepository
{
    public function getProjectByAccessKey($accessKey);
}