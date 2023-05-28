<?php

namespace App\Http\Controllers;

use App\Models\User;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

  public function index()
  {
    function password_crypt($string, $action = 'e') // $action 값은 기본값을 e(ncryted)로 한다.
    {
      $secret_key = 'chosangho_secret_key';
      $secret_iv = 'chosangho_secret_iv';

      $output = false;
      $encrypt_method = "AES-256-CBC";
      $key = hash('sha256', $secret_key);
      $iv = substr(hash('sha256', $secret_iv), 0, 16);

      if ($action == 'e') { // e는 암호화
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));

      } else if ($action == 'd') { // d는 복호화
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
      }

      return $output;
    }

    // 모든 HTTP 요청 헤더 가져오기
    $headers = apache_request_headers();

    // foreach로 돌리면서
    foreach ($headers as $header => $value) {

      // $header 값이 "Authorization"이라면
      if ($header == "Authorization") {

        $value = explode('Basic ', $value);

        $str = strtr($value[1], array('-' => '+', '_' => '/'));
        $str = base64_decode($str);

        $nameAndPassword = explode(':', $str);
        $name = $nameAndPassword[0];
        $password = $nameAndPassword[1];

        // 비밀번호 암복호화 함수에 비밀번호를 넣어서 암호화한 뒤 리턴 값으로 받아서 $encryptedPassword 변수에 저장
        $encryptedPassword = password_crypt($password, 'e');

        $cats = DB::table('users')
          ->where("name", "=", $name)
          ->where("password", "=", $encryptedPassword)
          ->get();

        if ($cats->count() == 0) {
          return response()->json(['message' => '해당 사용자가 존재하지 않습니다.'], 401, [], JSON_UNESCAPED_UNICODE);
        }

        foreach ($cats as $cat) {

          // 비밀번호 암복호화 함수에 비밀번호를 넣어서 복호화한 뒤 리턴 값으로 받아서 $decryptedPassword 변수에 저장
          $decryptedPassword = password_crypt($cat->password, 'd');

          if ($name == $cat->name && $password == $decryptedPassword) {
            // $table = DB::table('cats')->get();

            return response()->json([
              "품종" => $cat->kind,
              "나이" => $cat->age,
              "털색깔/무늬" => $cat->haircolor_pattern,
              "유저형태" => $cat->type
            ]);

          } else {

            return response()->json(['message' => '인증되지 않은 회원입니다.'], 401, [], JSON_UNESCAPED_UNICODE);

          }

        }
      }
    }
  }


  public function create(Request $request)
  {
    $name = $request->input('name');
    $email = $request->input('email');
    $password = $request->input('password');
    $kind = $request->input('kind');
    $age = $request->input('age');
    $haircolor_pattern = $request->input('haircolor_pattern');
    $type = $request->input('type');

    function password_crypt($string, $action = 'e') // $action 값은 기본값을 e(ncryted)로 한다.
    {
      $secret_key = 'chosangho_secret_key';
      $secret_iv = 'chosangho_secret_iv';

      $output = false;
      $encrypt_method = "AES-256-CBC";
      $key = hash('sha256', $secret_key);
      $iv = substr(hash('sha256', $secret_iv), 0, 16);

      if ($action == 'e') { // e는 암호화
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));

      } else if ($action == 'd') { // d는 복호화
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
      }

      return $output;
    }

    // 비밀번호 암복호화 함수에 비밀번호를 넣어서 암호화한 뒤 리턴 값으로 받아서 $encryptedPassword 변수에 저장
    $encryptedPassword = password_crypt($password, 'e');

    $usersEmail = DB::table('users')
      ->where("email", "=", $email)
      ->get();

    // 디비에 존재하는 이메일은 회원 가입 불가
    if ($usersEmail->count() > 0) {
      return response()->json(['message' => '존재하는 이메일입니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }

    if ($age < 1) {
      return response()->json(['message' => '나이는 1살 이상이어야 합니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif ($age > 15) {
      return response()->json(['message' => '나이는 15살 이하여야 합니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    } else {
      $user = new User();
      $user->unique_id = uniqid();
      $user->name = $name;
      $user->email = $email;
      $user->password = $encryptedPassword;
      $user->kind = $kind;
      $user->age = $age;
      $user->haircolor_pattern = $haircolor_pattern;
      $user->type = $type;
      $user->save();

      return response()->json($user);
    }
  }

  public function update(Request $request)
  {

    $Object = new DateTime();
    $Object->setTimezone(new DateTimeZone('Asia/Seoul'));
    $DateAndTime = $Object->format("Y-m-d h:i:s");

    $uniqueId = $request->input('unique_id');
    $password = $request->input('password');

    if ($uniqueId == null) {
      return response()->json(['message' => '업데이트를 할 수 없습니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }

    function password_crypt($string, $action = 'e') // $action 값은 기본값을 e(ncryted)로 한다.
    {
      $secret_key = 'chosangho_secret_key';
      $secret_iv = 'chosangho_secret_iv';

      $output = false;
      $encrypt_method = "AES-256-CBC";
      $key = hash('sha256', $secret_key);
      $iv = substr(hash('sha256', $secret_iv), 0, 16);

      if ($action == 'e') { // e는 암호화
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));

      } else if ($action == 'd') { // d는 복호화
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
      }

      return $output;
    }

    // 비밀번호 암복호화 함수에 비밀번호를 넣어서 암호화한 뒤 리턴 값으로 받아서 $encryptedPassword 변수에 저장
    $encryptedPassword = password_crypt($password, 'e');

    $users = DB::table('users')
      ->where('unique_id', '=', $uniqueId)
      ->update([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password' => $encryptedPassword,
        'kind' => $request->input('kind'),
        'age' => $request->input('age'),
        'haircolor_pattern' => $request->input('haircolor_pattern'),
        'type' => $request->input('type'),
        'updated_at' => $DateAndTime
      ]);

    if ($users) {
      return response()->json(['message' => '업데이트 되었습니다.'], 200, [], JSON_UNESCAPED_UNICODE);
    } else {
      return response()->json(['message' => '업데이트 되지 않았습니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }
  }

  public function destroy(Request $request)
  {
    $uniqueId = $request->input('unique_id');

    $users = DB::table('users')
      ->where('unique_id', '=', $uniqueId)->delete();

    if ($users) {
      return response()->json(['message' => '삭제되었습니다.'], 200, [], JSON_UNESCAPED_UNICODE);
    } else {
      return response()->json(['message' => '존재하지 않는 unique_id 입니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }
  }
}
