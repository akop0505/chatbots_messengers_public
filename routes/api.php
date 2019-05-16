<?php

use Illuminate\Http\Request;
use Twilio\TwiML\MessagingResponse;
//use Image;

//use Telegram;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
})*/;

Route::get('storage/{filename}', function ($filename)
{
    $path = storage_path('/app/public/files/dialog/' . $filename);

    if (!\Illuminate\Support\Facades\File::exists($path)) {
        abort(404);
    }

    $file = \Illuminate\Support\Facades\File::get($path);
    $type = \Illuminate\Support\Facades\File::mimeType($path);

    $response = \Illuminate\Support\Facades\Response::make($file, 200);
    $response->header("Content-Type", $type);
    \Illuminate\Support\Facades\Log::debug('GET FILE',[$path]);
    return $response;
//    return Image::make(storage_path('/app/public/files/telegram/' . $filename))->response();
});

Route::post('setWebHook','Telegram\\TelegramController@setWebHook');

Route::prefix('/telegram/{token}')->group(function () {
    Route::post('/', 'Telegram\\TelegramController@telegramCatchMessage');
});

//Route::post('/telegram/{token?}', 'Telegram\\TelegramController@telegramCatchMessage');
Route::post('/viber/{token}', 'Viber\\ViberController@catchMessage');

Route::post('/viberSetWebHook', 'Viber\\ViberController@setWebHook');

Route::get('/fb-webhook-verify/{appId}', 'FacebookMessenger\\FacebookMessengerController@validateWebHook');
Route::post('/fb-webhook-verify/{appId}', 'FacebookMessenger\\FacebookMessengerController@catchMessage');

Route::post('testMenuCallback', function(Request $request) {
    return response()->json(['variables'=>['project_name'=>'My Test Project','project'=>'Armenian Project','user'=>'Alik Oganyan','hello'=>'welcome']],200);
});


Route::get('unauthorized', function(){
    return response()
        //->header('Content-Type', 'application/json')
        ->json([
            "errorCode"=>"UNAUTHORIZED",
            "errors"=>[
                "user not authenticated"
            ]
        ],401,[]);
})->name('unauthorized');

Route::get('incorrect_project_key', function(){
    return response()
        //->header('Content-Type', 'application/json')
        ->json([
            "errorCode"=>"INCORRECT_PROJECT_KEY",
            "errors"=>[
                "The project key is incorrect or inactive."
            ]
        ],401,[]);
})->name('incorrect_project_key');

Route::post('login', 'Auth\LoginController@login');
Route::post('register', 'Auth\RegisterController@register');

Route::post("/come_in",'WhatsApp\\WhatsappController@catchMessage');

Route::post("/callback",function(Request $request) {
    //\Illuminate\Support\Facades\Log::debug("WhatsApp Callback");
    //\Illuminate\Support\Facades\Log::debug("WhatsApp All =  ",$request->all());
});

Route::group(['middleware' => 'valid.project.key'], function() {
    Route::post('send','Mailing\\MailingController@send');
});

//protected routes
Route::group(['middleware' => 'auth:api'], function() {
    Route::resource('roles','RoleController');
    Route::resource('users','UserController');
    Route::resource('menu','MenuController');
    Route::resource('events','EventController');
    Route::resource('gateways','GatewayController');
    Route::resource('projects','ProjectController',[
        'names' => [
            'index' => 'projects.index',
            'store' => 'projects.store',
        ]]);
    Route::resource('sequences','SequencesController',[
        'names' => [
            'index' => 'sequences.index',
            'store' => 'sequences.store',
        ]]);
    Route::resource('leads','LeadsController',[
        'names' => [
            'index' => 'leads.index',
            'store' => 'leads.store',
        ]]);
    Route::resource('passage-actions','PassageActionsController',[
        'names' => [
            'destroy' => 'leads.destroy',
        ]]);
    Route::resource('permissions','PermissionController');
    Route::resource('project/office-phones','ProjectOfficePhonesController');
    Route::resource('messengers','MessengerController');
    Route::resource('templates','TemplateController');
    Route::resource('receivers','ReceiverController');
    Route::resource('sidebar_navs','SidebarNavController');
    Route::get('sidebar_navs_closed_access','SidebarNavController@getWithNotAccessRoles');
    Route::get('sidebar_navs_opened_access','SidebarNavController@getWithAccessRoles');
    Route::resource('project/parameters','ParameterController');
    Route::get('project/week-days', 'WeekDaysController@index');
    Route::post('project/week-days', 'WeekDaysController@store');
    Route::post('project/timezone/{project_id}', 'ProjectController@saveTimezone');
    Route::post('projects/keys/{project_id}', 'ProjectKeyController@create');
    Route::put('projects/keys/{id}', 'ProjectKeyController@update');
    Route::delete('projects/keys/{id}', 'ProjectKeyController@destroy');
    Route::post('sequences/validate-task-options/{type}', 'SequencesController@validateTaskOptions')->name("validateTaskOptions");
    Route::post('sequences/start/{sequence}', 'SequencesController@start')->name("start");
    Route::get('languages', 'LanguageController@index')->name("languages.index");
    Route::get('projects-count', 'ProjectController@projectsCount')->name("projectsCount");
    Route::get('select/templates/', 'TemplateController@getTemplatesToSelect')->name("templatesToSelect");
    Route::get('select/projects', 'ProjectController@projectsToSelect')->name("projectsToSelect");
    Route::get('select/sequences/', 'SequencesController@sequencesToSelect')->name("sequencesToSelect");
    Route::get('select/events/', 'EventController@eventsToSelect')->name("eventsToSelect");
    Route::get('select/receivers/', 'ReceiverController@receiversToSelect')->name("receiversToSelect");
    Route::get('select/menus/', 'MenuController@menusToSelect')->name("menusToSelect");
    Route::get('select/variables/', 'ParameterController@parametersToSelect')->name("parametersToSelect");
    Route::get('select/clients/', 'UserController@getClients')->name("clientsToSelect");
    Route::get('select/managers/', 'UserController@getManagers')->name("managersToSelect");
    Route::get('select/lead-statuses/', 'LeadsController@getStatuses')->name("leadStatusesToSelect");
    Route::get('select/timezones/', 'TimezonesController@index')->name("timezonesToSelect");
    Route::prefix('/dialog')->group(function () {
        Route::get('/users', 'DialogController@getUsers');
        Route::get('/messages', 'DialogController@getMessages')->name("dialog.messages");
        Route::post('/send', 'DialogController@send')->name("dialog.send");
        Route::post('/uploadFile', 'DialogController@uploadFile')->name("dialog.uploadFile");
        Route::post('/sendFile', 'DialogController@sendFile')->name("dialog.sendFile");
    });
    Route::get('logout', 'Auth\LoginController@logout');
});