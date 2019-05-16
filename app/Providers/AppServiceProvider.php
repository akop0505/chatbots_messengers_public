<?php

namespace App\Providers;

use App\Models\AnswerableMessage;
use App\Models\GatewaySetting;
use App\Models\PresentReply;
use App\Models\Template;
use App\Models\UsersChatInfo;
use App\Repositories\AnswerableMessageRepository;
use App\Repositories\GatewaySettingsRepository;
use App\Repositories\PresentReplyRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\UserChatInfoRepository;
use App\Repositories\UserRepository;
use App\Services\AppFacebook;
use App\Services\AppReplyMessage;
use App\Services\AppViber;
use App\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('appReplyMessage',AppReplyMessage::class);
        $this->app->bind('appViber',AppViber::class);
        $this->app->bind('appFacebook',AppFacebook::class);
        $this->app->singleton(UserRepository::class,User::class);
        $this->app->singleton(GatewaySettingsRepository::class,GatewaySetting::class);
        $this->app->singleton(AnswerableMessageRepository::class,AnswerableMessage::class);
        $this->app->singleton(TemplateRepository::class,Template::class);
        $this->app->singleton(PresentReplyRepository::class,PresentReply::class);
        $this->app->singleton(UserChatInfoRepository::class,UsersChatInfo::class);
    }
}
