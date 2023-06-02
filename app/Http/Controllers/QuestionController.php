<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 고양이 질문과 관련된 데이터를 컨트롤하는 QuestionController 클래스(질문 CRUD)
 *
 * 1. index() 메소드(질문 데이터 가져오기(1페이지당 9개의 데이터만 조회))
 * 2. index_by_id() 메소드(질문 id에 해당하는 고양이 질문 데이터를 json으로 출력)
 * 3. questions_and_answers() 메소드(질문과 답변 가져오기)
 * 4. create() 메소드(질문 등록하기)
 * 5. update() 메소드(질문 수정하기)
 * 6. destroy() 메소드(질문 삭제하기)
 *
 */
class QuestionController extends Controller
{
  /**
   * 질문 데이터 가져오기(1페이지당 9개의 데이터만 조회)
   */
  public function index(Request $request)
  {
    // pageNumber 값이 있다면 그 값을 $pageNumber에 집어넣고 없다면 디폴트값인 1로 저장
    $pageNumber = $request->input('pageNumber', 1);

    // 1 페이지면 시작 번호 0부터
    if ($pageNumber == 1) {
      $startNumber = 0;
    }
    // 2페이지 이상부터
    else {
      $startNumber = ($pageNumber - 1) * 9;
    }

    // 1 페이지면 0, 9까지 조회
    // 2 페이지면 9, 9까지 조회
    $questions = DB::table('questions')
      ->leftJoin('users', 'questions.user_uniqueid', '=', 'users.unique_id')
      ->select('questions.title', 'questions.content', 'questions.created_at as questions_created_at', 'users.kind', 'users.haircolor_pattern', 'users.type as users_type')
      ->offset($startNumber)
      ->limit(9)
      ->get();

    $questionsArray = [];

    foreach ($questions as $questionIndex => $question) {

      // 질문 내용이 20자가 넘어가면 20자 이후의 글은 ... 으로 출력
      // 20자 이하라면 그대로 출력
      $question_content = mb_strlen($question->content, "UTF-8") > 20 ? mb_substr($question->content, 0, 20, "UTF-8") . "..." : $question->content;

      ${"questionsArray" . $questionIndex} = [
        "제목" => $question->title,
        "내용" => $question_content,
        "작성날짜" => $question->questions_created_at,
        "유저정보" => [
          "품종" => $question->kind,
          "털색깔/무늬" => $question->haircolor_pattern,
          "유저형태" => $question->users_type
        ]
      ];

      $questionsArray = array_merge([${"questionsArray" . $questionIndex}], $questionsArray);
    }


    return response()->json($questionsArray);
  }


  /**
   * 질문 id에 해당하는 고양이 질문 데이터를 json으로 출력
   */
  public function index_by_id($id)
  {
    // 디비에서 질문 id에 해당하는 데이터 가져오기
    $questions = DB::table('questions')->where('id', $id)->get();

    // 질문 id에 해당하는 질문 데이터가 없을시 'message' => '존재하지 않는 질문입니다.' 출력
    if ($questions->isEmpty()) {
      return response()->json(['message' => '존재하지 않는 질문입니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }

    // json 형식으로 질문 데이터 return
    return response()->json($questions);
  }


  /**
   * 질문과 답변 가져오기
   */
  public function questions_and_answers()
  {
    // left join으로 questions 테이블과 users 테이블을 엮어서
    // 질문.제목, 질문.내용, 질문.작성날짜, 질문.답변자_uniqueid
    // 유저.품종, 유저.품종, 유저.형태 가져오기
    $questions = DB::table('questions')
      ->leftJoin('users', 'questions.user_uniqueid', '=', 'users.unique_id')
      ->select('questions.id as questions_id', 'questions.title', 'questions.content', 'questions.created_at as questions_created_at', 'questions.answer_uniqueid', 'users.kind', 'users.haircolor_pattern', 'users.type as users_type')
      ->orderBy('questions_id', 'desc')
      ->get();

    // return 시에 화면에 보여줄 최종 데이터를 담은 배열
    $resultArray = [];

    // 질문 데이터를 foreach로 돌려서 하나씩 출력
    foreach ($questions as $questionIndex => $question) {

      $answerUniqueId = $question->answer_uniqueid;
      $resultAnswers = [];


      if ($answerUniqueId != "" || $answerUniqueId != null) {
        $answerUniqueIdArray = explode('||', $answerUniqueId);

        foreach ($answerUniqueIdArray as $answerUniqueIdIndex => $answerUniqueId) {

          if ($answerUniqueId == "") {
            continue;
          }

          $resultAnswersData = DB::table('answers')
            ->leftJoin('users', 'answers.user_uniqueid', '=', 'users.unique_id')
            ->select('answers.id as answers_id', 'answers.content', 'answers.accept', 'answers.created_at as answers_created_at', 'users.kind', 'users.haircolor_pattern', 'users.type as users_type')
            ->where('answers.unique_id', '=', $answerUniqueId)
            ->first();

          ${"resultAnswers" . $answerUniqueIdIndex} = [
            "답변내용" => $resultAnswersData->content,
            "답변채택여부" => $resultAnswersData->accept,
            "답변날짜" => $resultAnswersData->answers_created_at,
            "답변자유저정보" => [
              "품종" => $resultAnswersData->kind,
              "털색깔/무늬" => $resultAnswersData->haircolor_pattern,
              "유저형태" => $resultAnswersData->users_type
            ]
          ];

          $resultAnswers = array_merge([${"resultAnswers" . $answerUniqueIdIndex}], $resultAnswers);

        }

        ${"result" . $questionIndex} = [
          "제목" => $question->title,
          "내용" => $question->content,
          "작성날짜" => substr($question->questions_created_at, 0, 10),
          "유저정보" => [
            "품종" => $question->kind,
            "털색깔/무늬" => $question->haircolor_pattern,
            "유저형태" => $question->users_type
          ],
          "답변리스트" => $resultAnswers
        ];

        $resultArray = array_merge([${"result" . $questionIndex}], $resultArray);

      } else {

        // 답변자가 없을 때(답변 정보 없이 출력)
        $result = [
          "제목" => $question->title,
          "내용" => $question->content,
          "작성날짜" => $question->questions_created_at,
          "유저정보" => [
            "품종" => $question->kind,
            "털색깔/무늬" => $question->haircolor_pattern,
            "유저형태" => $question->users_type
          ]
        ];

        $resultArray = array_merge([$result], $resultArray);
      }


    }

    return response()->json($resultArray, 200, [], JSON_UNESCAPED_UNICODE);

  }


  /**
   * 질문 등록하기
   */
  public function create(Request $request)
  {
    $unique_id = uniqid();
    $user_uniqueid = $request->input('user_uniqueid');
    $title = $request->input('title');
    $content = $request->input('content');
    $type = $request->input('type');

    if (empty($title)) {
      return response()->json(['message' => '질문 등록시 제목은 반드시 입력해야 합니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($content)) {
      return response()->json(['message' => '질문 등록시 내용은 반드시 입력해야 합니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($type)) {
      return response()->json(['message' => '질문 등록시 질문 타입은 반드시 입력해야 합니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }


    $createQuestions = DB::table('questions')->insert([
      "unique_id" => $unique_id,
      "user_uniqueid" => $user_uniqueid,
      "title" => $title,
      "content" => $content,
      "type" => $type
    ]);

    if ($createQuestions) {
      return response()->json(['message' => '질문이 등록되었습니다.'], 200, [], JSON_UNESCAPED_UNICODE);
    } else {
      return response()->json(['message' => '질문이 등록되지 않았습니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }
  }

  /**
   * 질문 수정하기
   */
  public function update(Request $request)
  {

    $id = $request->input('id');
    $title = $request->input('title');
    $content = $request->input('content');
    $type = $request->input('type');
    $uniqueId = $request->input('unique_id');
    $userUniqueId = $request->input('user_uniqueid');

    $questions =
      DB::table('questions')
        ->where("id", "=", $id)
        ->where("unique_id", "=", $uniqueId)
        ->where("user_uniqueid", "=", $userUniqueId)
        ->first();

    if (isset($questions->answer_uniqueid)) {
      return response()->json(['message' => '답변이 달린 질문은 수정할 수 없습니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }


    $updateQuestions =
      DB::table('questions')
        ->where("id", "=", $id)
        ->where("unique_id", "=", $uniqueId)
        ->where("user_uniqueid", "=", $userUniqueId)
        ->update([
          "title" => $title,
          "content" => $content,
          "type" => $type
        ]);

    if ($updateQuestions) {
      return response()->json(['message' => '질문이 수정되었습니다.'], 200, [], JSON_UNESCAPED_UNICODE);
    } else {
      return response()->json(['message' => '질문이 수정되지 않았습니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }
  }


  /**
   * 질문 id에 해당하는 데이터 삭제하기
   */
  public function destroy(Request $request)
  {

    $id = $request->input('id');
    $uniqueId = $request->input('unique_id');
    $userUniqueId = $request->input('user_uniqueid');


    $questions = DB::table('questions')
      ->where('id', '=', $id)
      ->where('unique_id', '=', $uniqueId)
      ->where('user_uniqueid', '=', $userUniqueId)
      ->first();

    if (isset($questions->answer_uniqueid)) {
      return response()->json(['message' => '답변이 달린 질문은 삭제할 수 없습니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }

    $deleteQuestion =
      DB::table('questions')
        ->where('id', $id)
        ->delete();

    if ($deleteQuestion) {
      return response()->json(['message' => '해당 질문이 삭제되었습니다.'], 200, [], JSON_UNESCAPED_UNICODE);
    } else {
      return response()->json(['message' => '삭제할 질문이 없습니다.'], 404, [], JSON_UNESCAPED_UNICODE);
    }
  }
}
