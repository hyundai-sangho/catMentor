<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 고양이 질문과 관련된 데이터를 컨트롤하는 QuestionController 클래스(질문 CRUD)
 *
 * 1. index() 메소드
 * 질문 데이터 가져오기(1페이지당 9개의 데이터만 조회)
 *
 * input  : pageNumber(페이지 번호)
 * return : json 질문 데이터
 *
 * 2. index_by_id() 메소드
 * 질문 id에 해당하는 고양이 질문 데이터를 json으로 출력
 *
 * input  : id
 * return : [성공] json 질문 데이터
 *          [실패] ['message' => '존재하지 않는 질문입니다.'], 404
 *
 * 3. questions_and_answers() 메소드
 * 질문과 답변 가져오기
 *
 * return : json 질문과 답변 데이터
 *
 * 4. create() 메소드(질문 등록하기)
 * 5. update() 메소드(질문 수정하기)
 * 6. destroy() 메소드(질문 삭제하기)
 *
 * input  : 질문 id
 * return : [성공] ['message' => '해당 사용자가 삭제되었습니다.'], 200
 *          [실패] ['message' => '삭제할 사용자가 없습니다.'], 404
 */
class QuestionController extends Controller
{
  /**
   * 질문 데이터 가져오기(1페이지당 9개의 데이터만 조회)
   *
   * input  : pageNumber(페이지 번호)
   * return : json 질문 데이터
   *
   * [
   *   {
   *    "제목": "집사에 대한 질문입니다.",
   *    "내용": "제가 먼치킨 고양이인데 집사가 다리 짧다고 놀리는데 냥냥펀치 날려야 할까요?",
   *    "작성날짜": "2023-05-27",
   *    "유저정보": {
   *      "품종": "코리안숏헤어",
   *      "털색깔/무늬": "검정색",
   *      "유저형태": "멘토"
   *     }
   *   }
   * ]
   *
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
   *
   * input  : id
   * return : [성공] json 질문 데이터
   *          [실패] ['message' => '존재하지 않는 질문입니다.'], 404 Error
   *
   * [
   *   {
   *     "id": 3,
   *     "unique_id": "6471d4896eb68",
   *     "user_uniqueid": "6471d4896eb68",
   *     "title": "집사에 대한 질문입니다. 3",
   *     "content": "제가 먼치킨 고양이인데 집사가 다리 짧다고 놀리는데 냥냥펀치 날려야 할까요?",
   *     "type": "집사후기",
   *     "answer_uniqueid": "9243523||2634523",
   *     "created_at": "2023-05-27 19:10:48",
   *     "updated_at": "2023-05-27 19:08:44"
   *   }
   * ]
   *
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
   *
   * return : json 질문과 답변 데이터
   *
   * [
   *   {
   *     "제목": "집사에 대한 질문입니다.",
   *      "내용": "제가 먼치킨 고양이인데 집사가 다리 짧다고 놀리는데 냥냥펀치 날려야 할까요?",
   *      "작성날짜": "2023-05-27",
   *      "유저정보": {
   *         "품종": "코리안숏헤어",
   *         "털색깔/무늬": "검정색",
   *         "유저형태": "멘토"
   *     },
   *     "답변리스트": [
   *         {
   *             "답변내용": "다리 짧다고 놀리면 확 깨물어 버리면 됩니다.",
   *             "답변채택여부": "no",
   *             "답변날짜": "2023-05-27",
   *             "답변자유저정보": {
   *                 "품종": "스노우슈",
   *                 "털색깔/무늬": "삼색",
   *                 "유저형태": "멘토"
   *             }
   *         },
   *         {
   *             "답변내용": "츄르 주면 용서하는데 안 줬다면 날려도 무죄입니다.",
   *             "답변채택여부": "no",
   *             "답변날짜": "2023-05-27",
   *             "답변자유저정보": {
   *                 "품종": "러시안블루",
   *                 "털색깔/무늬": "고등어",
   *                 "유저형태": "멘티"
   *             }
   *         }
   *     ]
   *   }
   * ]
   *
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
    // 모든 HTTP 요청 헤더 가져오기
    $headers = apache_request_headers();

    // foreach로 돌리면서
    foreach ($headers as $header => $value) {

      // $header 값이 "Authorization"이라면
      if ($header == "Authorization") {
        function password_crypt($password, $action = 'encrypt') // $action 값은 기본값을 encrypt로 한다.
        {
          $secret_key = 'chosangho_secret_key';
          $secret_iv = 'chosangho_secret_iv';

          $output = false;
          $encrypt_method = "AES-256-CBC";
          $key = hash('sha256', $secret_key);
          $iv = substr(hash('sha256', $secret_iv), 0, 16);

          if ($action == 'encrypt') { // encrypt는 암호화
            $output = base64_encode(openssl_encrypt($password, $encrypt_method, $key, 0, $iv));

          } else if ($action == 'decrypt') { // decrypt는 복호화
            $output = openssl_decrypt(base64_decode($password), $encrypt_method, $key, 0, $iv);
          }

          return $output;
        }

        $value = explode('Basic ', $value);

        $str = strtr($value[1], array('-' => '+', '_' => '/'));
        $str = base64_decode($str);

        $nameAndPassword = explode(':', $str);
        $name = $nameAndPassword[0];
        $password = $nameAndPassword[1];

        // 비밀번호 암복호화 함수에 비밀번호를 넣어서 암호화한 뒤 리턴 값으로 받아서 $encryptedPassword 변수에 저장
        $encryptedPassword = password_crypt($password, 'encrypt');

        $users = DB::table('users')
          ->where("name", "=", $name)
          ->where("password", "=", $encryptedPassword)
          ->first();

        if (empty($users)) {
          return response()->json(['message' => '해당 사용자가 존재하지 않습니다.'], 401, [], JSON_UNESCAPED_UNICODE);
        }

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
