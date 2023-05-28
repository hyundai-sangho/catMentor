<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AnswerController extends Controller
{
  public function create(Request $request)
  {
    $questionId = $request->input('question_id');
    $questionsUniqueId = $request->input('questions_uniqueid');
    $unique_id = uniqid();
    $user_uniqueid = $request->input('user_uniqueid');
    $content = $request->input('content');

    $questions = DB::table('questions')->where('id', $questionId)->first();
    $answerUniqueIdArray = explode('||', $questions->answer_uniqueid);

    if (count($answerUniqueIdArray) == 4) {
      return response()->json("질문에 3개의 답변이 있기 때문에, 더 이상 답변을 달 수 없습니다.", 400, [], JSON_UNESCAPED_UNICODE);
    } else if (count($answerUniqueIdArray) == 1) {
      $questions_answer_uniqueid_merge = $unique_id . '||';

      DB::table('questions')->where('id', $questionId)->update(['answer_uniqueid' => $questions_answer_uniqueid_merge]);

      DB::table('answers')->insert([
        'unique_id' => $unique_id,
        'user_uniqueid' => $user_uniqueid,
        'content' => $content,
        'questions_uniqueid' => $questionsUniqueId
      ]);
    } else {
      $questions_answer_uniqueid_merge = $questions->answer_uniqueid . $unique_id . '||';

      DB::table('questions')->where('id', $questionId)->update(['answer_uniqueid' => $questions_answer_uniqueid_merge]);

      DB::table('answers')->insert([
        'unique_id' => $unique_id,
        'user_uniqueid' => $user_uniqueid,
        'content' => $content,
        'questions_uniqueid' => $questionsUniqueId
      ]);
    }


  }

  public function update(Request $request)
  {
    $id = $request->input('id');
    $content = $request->input('content');

    $answers = DB::table('answers')->where('id', $id)->first();

    if ($answers->accept == 'yes') {
      return response()->json(['message' => '답변이 채택된 상태라 수정이 불가능합니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    } else {
      DB::table('answers')->where('id', $id)->update(['content' => $content]);

      return response()->json(['message' => '답변이 수정되었습니다.'], 200, [], JSON_UNESCAPED_UNICODE);
    }
  }


  /**
   * 답변 채택 및 취소
   */
  public function accept(Request $request)
  {

    $id = $request->input('id');
    $cancel = $request->input('cancel');

    $answers = DB::table('answers')->where('id', $id)->first();

    if ($cancel == 'yes') {
      if ($answers->accept == 'yes') {
        DB::table('answers')->where('id', $id)->update(['accept' => 'no']);

        return response()->json(['message' => '답변이 채택이 취소되었습니다.'], 200, [], JSON_UNESCAPED_UNICODE);
      } else {
        return response()->json(['message' => '답변이 채택되지 않는 상태라 채택 취소를 할 수 없습니다.'], 400, [], JSON_UNESCAPED_UNICODE);
      }
    } elseif (empty($cancel)) {
      if ($answers->accept == 'no') {
        DB::table('answers')->where('id', $id)->update(['accept' => 'yes']);

        return response()->json(['message' => '답변이 채택되었습니다.'], 200, [], JSON_UNESCAPED_UNICODE);
      } else {
        return response()->json(['message' => '답변이 이미 채택된 상태입니다.'], 400, [], JSON_UNESCAPED_UNICODE);
      }
    }
  }


  public function destroy(Request $request)
  {
    $id = $request->input('id');
    $answers = DB::table('answers')->where('id', $id)->first();

    if ($answers->accept == 'no') {

      $questions = DB::table('questions')->where('unique_id', $answers->questions_uniqueid)->first();

      $answers->unique_id;
      if ($questions) {

        $answerUniqueIdArray = explode('||', $questions->answer_uniqueid);
        $search = array_search($answers->questions_uniqueid, $answerUniqueIdArray);

        var_dump($search);

        array_splice($answerUniqueIdArray, $search, 1);

        $answerUniqueIdArray2 = '';
        foreach ($answerUniqueIdArray as $key => $value) {
          var_dump($answerUniqueIdArray);
          exit;

          if ($value == "") {
            continue;
          }

          $answerUniqueIdArray2 .= $value . '||';
        }

        DB::table('questions')->where('unique_id', $answers->questions_uniqueid)->update(['answer_uniqueid' => $answerUniqueIdArray2]);
      }

      DB::table('answers')->where('id', $id)->delete();

      return response()->json(['message' => '답변이 삭제되었습니다.'], 200, [], JSON_UNESCAPED_UNICODE);
    } else {
      return response()->json(['message' => '답변이 채택된 상태라 삭제가 불가능합니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }
  }
}
