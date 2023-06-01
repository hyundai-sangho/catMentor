const mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | 혼합 자산 관리
 |------------------------------------------------- -------------------------
 |
 | Mix는 일부 Webpack 빌드 단계를 정의하기 위한 깨끗하고 유창한 API를 제공합니다.
 | 당신의 라라벨 애플리케이션을 위해. 기본적으로 CSS를 컴파일하고 있습니다.
 | 애플리케이션용 파일과 모든 JS 파일을 번들로 묶습니다.
 |
 */

mix.js("resources/js/app.js", "public/js").postCss(
    "resources/css/app.css",
    "public/css",
    [
        //
    ]
);
