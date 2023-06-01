<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API 경로
|--------------------------------------------------------------------------
|
| 여기에서 애플리케이션에 대한 API 경로를 등록할 수 있습니다. 이것들
| 경로는 그룹 내에서 RouteServiceProvider에 의해 로드됩니다.
| "api" 미들웨어 그룹이 할당됩니다. API 구축을 즐겨보세요!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
  return $request->user();
});

// 사용자 CRUD =============================================================================================
// ========================================================================================================
Route::get('/users', [UserController::class, 'index']); // 사용자 정보 가져오기
Route::post('/users', [UserController::class, 'create']); // 사용자 정보 등록하기
Route::put('/users', [UserController::class, 'update']); // 사용자 정보 수정하기
Route::delete('/users', [UserController::class, 'destroy']); // 사용자 정보 삭제하기

// 질문 CRUD ==============================================================================================
// ========================================================================================================
Route::get('/questions', [QuestionController::class, 'index']); // 질문 가져오기
Route::get('/questions/{id}', [QuestionController::class, 'index_by_id']); // id로 질문 가져오기
Route::get('/questions-answers', [QuestionController::class, 'questions_and_answers']); // 질문과 답변 가져오기
Route::post('/questions', [QuestionController::class, 'create']); // 질문 등록하기
Route::put('/questions', [QuestionController::class, 'update']); // 질문 수정하기
Route::delete('/questions', [QuestionController::class, 'destroy']); // 질문 삭제하기

// 답변 CRUD ==============================================================================================
// ========================================================================================================
Route::post('/answers', [AnswerController::class, 'create']); // 답변 등록하기
Route::put('/answers', [AnswerController::class, 'update']); // 답변 수정하기
Route::patch('/answers', [AnswerController::class, 'accept']); // 답변 채택 및 취소
Route::delete('/answers', [AnswerController::class, 'destroy']); // 답변 삭제하기
