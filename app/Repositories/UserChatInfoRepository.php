<?php

namespace App\Repositories;

interface UserChatInfoRepository
{
    public function createNewViberUserInfo($date);

    public function getViberUsersInfo($token);

    public function getFbUser($appId,$userId,$projectId);

    public function createNewFbUserInfo($data);

    public function getFbUsers($appId);

    public function getAll($filter,$duplicates);

}