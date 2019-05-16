<?php

namespace App\Repositories;

interface AnswerableMessageRepository
{
    /**
     * @param $chatId
     * @param $botUsername
     * @return mixed
     */
    public function getTelegramsLastQuestion($chatId,$botUsername);

    /**
     * @param $chatId
     * @param $from
     * @return mixed
     */
    public function getWhatsappLastQuestion($chatId,$from);

    /**
     * @param $data
     * @return mixed
     */
    public function createTelegramAnswerableQuestion($data);

    /**
     * @param $sender
     * @param $appId
     * @return mixed
     */
    public function getFbLastQuestion($sender,$appId);

    public function getAll($channel,$filter);

    public function createSimpleMessage($data,$channel,$state = null);

    public function updateSeen($ides);
}