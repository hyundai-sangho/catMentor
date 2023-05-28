<?php

return [

  /*
  |--------------------------------------------------------------------------
  | 애플리케이션 이름
  |--------------------------------------------------------------------------
  |
  | 이 값은 애플리케이션의 이름입니다. 이 값은
  | 프레임워크는 애플리케이션의 이름을 알림에 배치하거나
  | 응용 프로그램 또는 해당 패키지에서 요구하는 기타 모든 위치.
  |
  */

  'name' => env('APP_NAME', 'Laravel'),

  /*
  |--------------------------------------------------------------------------
  | 응용 환경
  |--------------------------------------------------------------------------
  |
  | 이 값은 애플리케이션이 현재 있는 "환경"을 결정합니다.
  | 실행 중입니다. 이것은 다양한 구성 방법을 선호하는 방법을 결정할 수 있습니다.
  | 응용 프로그램이 사용하는 서비스. ".env" 파일에서 이것을 설정하십시오.
  |
  */

  'env' => env('APP_ENV', 'production'),

  /*
  |--------------------------------------------------------------------------
  | 애플리케이션 디버그 모드
  |------------------------------------------------- -------------------------
  |
  | 애플리케이션이 디버그 모드에 있을 때 자세한 오류 메시지는 다음과 같습니다.
  | 내에서 발생하는 모든 오류에 스택 추적이 표시됩니다.
  | 애플리케이션. 비활성화된 경우 간단한 일반 오류 페이지가 표시됩니다.
  |
  */

  'debug' => (bool) env('APP_DEBUG', false),

  /*
  |--------------------------------------------------------------------------
  | 애플리케이션 URL
  |------------------------------------------------- -------------------------
  |
  | 이 URL은 다음을 사용할 때 콘솔에서 URL을 올바르게 생성하는 데 사용됩니다.
  | Artisan 명령줄 도구. 이것을 루트로 설정해야 합니다.
  | Artisan 작업을 실행할 때 사용되도록 애플리케이션을 만듭니다.
  |
  */

  'url' => env('APP_URL', 'http://localhost'),

  'asset_url' => env('ASSET_URL', null),

  /*
  |--------------------------------------------------------------------------
  | 애플리케이션 시간대
  |--------------------------------------------------------------------------
  |
  | 여기에서 애플리케이션의 기본 시간대를 지정할 수 있습니다.
  | PHP 날짜 및 날짜-시간 함수에서 사용됩니다. 우리는 갔다
  | 바로 사용할 수 있는 합리적인 기본값으로 설정하십시오.
  |
  */

  'timezone' => 'UTC',

  /*
  |--------------------------------------------------------------------------
  | 애플리케이션 로케일 구성
  |--------------------------------------------------------------------------
  |
  | 응용 프로그램 로케일은 사용될 기본 로케일을 결정합니다.
  | 번역 서비스 제공자에 의해. 이 값을 자유롭게 설정할 수 있습니다.
  | 응용 프로그램에서 지원하게 될 모든 로케일에.
  |
  */

  'locale' => 'en',

  /*
  |--------------------------------------------------------------------------
  | 애플리케이션 폴백 로케일
  |--------------------------------------------------------------------------
  |
  | 대체 로케일은 현재 로케일을 사용할 때 사용할 로케일을 결정합니다.
  | 사용할 수 없습니다. 다음에 해당하는 값을 변경할 수 있습니다.
  | 애플리케이션을 통해 제공되는 언어 폴더.
  |
  */

  'fallback_locale' => 'en',

  /*
  |--------------------------------------------------------------------------
  | 페이커 로케일
  |--------------------------------------------------------------------------
  |
  | 이 로케일은 가짜를 생성할 때 Faker PHP 라이브러리에서 사용됩니다.
  | 데이터베이스 시드에 대한 데이터. 예를 들어, 이것은 다음을 얻는 데 사용됩니다.
  | 현지화된 전화번호, 거리 주소 정보 등.
  |
  */

  'faker_locale' => 'en_US',

  /*
  |--------------------------------------------------------------------------
  | 암호화 키
  |------------------------------------------------- -------------------------
  |
  | 이 키는 Illuminate 암호화 서비스에서 사용되며 설정해야 합니다.
  | 임의의 32자 문자열, 그렇지 않으면 이러한 암호화된 문자열
  | 안전하지 않습니다. 애플리케이션을 배포하기 전에 이 작업을 수행하십시오!
  |
  */

  'key' => env('APP_KEY'),

  'cipher' => 'AES-256-CBC',

  /*
  |--------------------------------------------------------------------------
  | Autoloaded Service Providers
  |--------------------------------------------------------------------------
  |
  | The service providers listed here will be automatically loaded on the
  | request to your application. Feel free to add your own services to
  | this array to grant expanded functionality to your applications.
  |
  */

  'providers' => [

      /*
       * Laravel Framework Service Providers...
       */
    Illuminate\Auth\AuthServiceProvider::class,
    Illuminate\Broadcasting\BroadcastServiceProvider::class,
    Illuminate\Bus\BusServiceProvider::class,
    Illuminate\Cache\CacheServiceProvider::class,
    Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
    Illuminate\Cookie\CookieServiceProvider::class,
    Illuminate\Database\DatabaseServiceProvider::class,
    Illuminate\Encryption\EncryptionServiceProvider::class,
    Illuminate\Filesystem\FilesystemServiceProvider::class,
    Illuminate\Foundation\Providers\FoundationServiceProvider::class,
    Illuminate\Hashing\HashServiceProvider::class,
    Illuminate\Mail\MailServiceProvider::class,
    Illuminate\Notifications\NotificationServiceProvider::class,
    Illuminate\Pagination\PaginationServiceProvider::class,
    Illuminate\Pipeline\PipelineServiceProvider::class,
    Illuminate\Queue\QueueServiceProvider::class,
    Illuminate\Redis\RedisServiceProvider::class,
    Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
    Illuminate\Session\SessionServiceProvider::class,
    Illuminate\Translation\TranslationServiceProvider::class,
    Illuminate\Validation\ValidationServiceProvider::class,
    Illuminate\View\ViewServiceProvider::class,

      /*
       * Package Service Providers...
       */

      /*
       * Application Service Providers...
       */
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
      // App\Providers\BroadcastServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,

  ],

  /*
  |--------------------------------------------------------------------------
  | Class Aliases
  |--------------------------------------------------------------------------
  |
  | This array of class aliases will be registered when this application
  | is started. However, feel free to register as many as you wish as
  | the aliases are "lazy" loaded so they don't hinder performance.
  |
  */

  'aliases' => [

    'App' => Illuminate\Support\Facades\App::class,
    'Arr' => Illuminate\Support\Arr::class,
    'Artisan' => Illuminate\Support\Facades\Artisan::class,
    'Auth' => Illuminate\Support\Facades\Auth::class,
    'Blade' => Illuminate\Support\Facades\Blade::class,
    'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
    'Bus' => Illuminate\Support\Facades\Bus::class,
    'Cache' => Illuminate\Support\Facades\Cache::class,
    'Config' => Illuminate\Support\Facades\Config::class,
    'Cookie' => Illuminate\Support\Facades\Cookie::class,
    'Crypt' => Illuminate\Support\Facades\Crypt::class,
    'DB' => Illuminate\Support\Facades\DB::class,
    'Eloquent' => Illuminate\Database\Eloquent\Model::class,
    'Event' => Illuminate\Support\Facades\Event::class,
    'File' => Illuminate\Support\Facades\File::class,
    'Gate' => Illuminate\Support\Facades\Gate::class,
    'Hash' => Illuminate\Support\Facades\Hash::class,
    'Http' => Illuminate\Support\Facades\Http::class,
    'Lang' => Illuminate\Support\Facades\Lang::class,
    'Log' => Illuminate\Support\Facades\Log::class,
    'Mail' => Illuminate\Support\Facades\Mail::class,
    'Notification' => Illuminate\Support\Facades\Notification::class,
    'Password' => Illuminate\Support\Facades\Password::class,
    'Queue' => Illuminate\Support\Facades\Queue::class,
    'Redirect' => Illuminate\Support\Facades\Redirect::class,
    // 'Redis' => Illuminate\Support\Facades\Redis::class,
    'Request' => Illuminate\Support\Facades\Request::class,
    'Response' => Illuminate\Support\Facades\Response::class,
    'Route' => Illuminate\Support\Facades\Route::class,
    'Schema' => Illuminate\Support\Facades\Schema::class,
    'Session' => Illuminate\Support\Facades\Session::class,
    'Storage' => Illuminate\Support\Facades\Storage::class,
    'Str' => Illuminate\Support\Str::class,
    'URL' => Illuminate\Support\Facades\URL::class,
    'Validator' => Illuminate\Support\Facades\Validator::class,
    'View' => Illuminate\Support\Facades\View::class,

  ],

];
