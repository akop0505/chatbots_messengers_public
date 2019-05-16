<?php

namespace App\Repositories;

interface GatewaySettingsRepository
{
    public function getUsernameByToken($token);

    public function getFbSettingsByAppId($appId);

    public function getTokenByUsernameAndId($username,$id);

    public function getProjectIdByChannelConfig($field,$value);
}