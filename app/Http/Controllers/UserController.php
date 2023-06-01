<?php

namespace App\Http\Controllers;

use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

  /**
   * 사용자 조회하기
   */
  public function index()
  {
    function password_crypt($string, $action = 'encrypt') // $action 값은 기본값을 encrypt로 한다.
    {
      $secret_key = 'chosangho_secret_key';
      $secret_iv = 'chosangho_secret_iv';

      $output = false;
      $encrypt_method = "AES-256-CBC";
      $key = hash('sha256', $secret_key);
      $iv = substr(hash('sha256', $secret_iv), 0, 16);

      if ($action == 'encrypt') { // encrypt는 암호화
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));

      } else if ($action == 'decrypt') { // decrypt는 복호화
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
        $encryptedPassword = password_crypt($password, 'encrypt');

        $users = DB::table('users')
          ->where("name", "=", $name)
          ->where("password", "=", $encryptedPassword)
          ->get();

        if ($users->count() == 0) {
          return response()->json(['message' => '해당 사용자가 존재하지 않습니다.'], 401, [], JSON_UNESCAPED_UNICODE);
        }

        foreach ($users as $user) {

          // 비밀번호 암복호화 함수에 비밀번호를 넣어서 복호화한 뒤 리턴 값으로 받아서 $decryptedPassword 변수에 저장
          $decryptedPassword = password_crypt($user->password, 'decrypt');

          if ($name == $user->name && $password == $decryptedPassword) {

            return response()->json([
              "품종" => $user->kind,
              "나이" => $user->age,
              "털색깔/무늬" => $user->haircolor_pattern,
              "유저형태" => $user->type
            ]);
          } else {
            return response()->json(['message' => '인증되지 않은 회원입니다.'], 401, [], JSON_UNESCAPED_UNICODE);
          }
        }
      }
    }
  }


  /**
   * 사용자 등록하기
   */
  public function create(Request $request)
  {
    $name = $request->input('name');
    $email = $request->input('email');
    $password = $request->input('password');
    $kind = $request->input('kind');
    $age = $request->input('age');
    $haircolor_pattern = $request->input('haircolor_pattern');
    $type = $request->input('type');

    if (empty($name)) {
      return response()->json(['message' => '이름을 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($email)) {
      return response()->json(['message' => '이메일을 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($password)) {
      return response()->json(['message' => '비밀번호를 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($kind)) {
      return response()->json(['message' => '고양이 품종를 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($age)) {
      return response()->json(['message' => '고양이 나이를 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($haircolor_pattern)) {
      return response()->json(['message' => '고양이 털 색깔/무늬를 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($type)) {
      return response()->json(['message' => '유저 형태(멘토/멘티)를 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    }

    // $name 변수의 공백 제거(이름에 띄어쓰기가 있을 경우 공백 제거)
    $name = preg_replace("/\s+/", "", $name);

    // 이메일 검사 시작
    if ($email) {
      // 이메일 유효성 검사 시작
      // 정규식에 안 맞는 유형의 이메일이 들어오면 바로
      // 에러 출력 "올바른 이메일 주소를 입력해 주세요."
      if (preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email) == false) {
        return response()->json(['message' => '올바른 이메일 주소를 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
      }
    }

    // 비밀번호 유효성 검사
    if ($password) {
      $num = preg_match('/[0-9]/u', $password);
      $eng = preg_match('/[a-z]/u', $password);
      $spe = preg_match("/[\!\@\#\$\%\^\&\*]/u", $password);

      if (strlen($password) < 10 || strlen($password) > 30) {
        return response()->json(['message' => '비밀번호는 영문, 숫자, 특수문자를 혼합하여 최소 10자리 ~ 최대 30자리 이내로 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
      } elseif (preg_match("/\s/u", $password) == true) {
        return response()->json(['message' => '비밀번호는 공백 없이 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
      } elseif ($num == 0 || $eng == 0 || $spe == 0) {
        return response()->json(['message' => '비밀번호는 영문, 숫자, 특수문자를 혼합하여 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
      }
    }


    function password_crypt($string, $action = 'encrypt') // $action 값은 기본값을 encrypt로 한다.
    {
      $secret_key = 'chosangho_secret_key';
      $secret_iv = 'chosangho_secret_iv';

      $output = false;
      $encrypt_method = "AES-256-CBC";
      $key = hash('sha256', $secret_key);
      $iv = substr(hash('sha256', $secret_iv), 0, 16);

      if ($action == 'encrypt') { // encrypt는 암호화
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));

      } else if ($action == 'decrypt') { // decrypt는 복호화
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
      }

      return $output;
    }

    // 비밀번호 암복호화 함수에 비밀번호를 넣어서 암호화한 뒤 리턴 값으로 받아서 $encryptedPassword 변수에 저장
    $encryptedPassword = password_crypt($password, 'encrypt');

    $usersEmail =
      DB::table('users')
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
      $user =
        DB::table('users')
          ->insert([
            'unique_id' => uniqid(),
            'name' => $name,
            'email' => $email,
            'password' => $encryptedPassword,
            'kind' => $kind,
            'age' => $age,
            'haircolor_pattern' => $haircolor_pattern,
            'type' => $type
          ]);

      if ($user) {
        return response()->json(['message' => '회원 가입이 되었습니다.']);
      } else {
        return response()->json(['message' => '회원 가입이 되지 않았습니다.']);
      }
    }
  }

  /**
   * 사용자 정보 업데이트
   */
  public function update(Request $request)
  {

    $name = $request->input('name');
    $email = $request->input('email');
    $password = $request->input('password');
    $kind = $request->input('kind');
    $age = $request->input('age');
    $haircolor_pattern = $request->input('haircolor_pattern');
    $type = $request->input('type');

    if (empty($name)) {
      return response()->json(['message' => '이름을 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($email)) {
      return response()->json(['message' => '이메일을 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($password)) {
      return response()->json(['message' => '비밀번호를 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($kind)) {
      return response()->json(['message' => '고양이 품종를 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($age)) {
      return response()->json(['message' => '고양이 나이를 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($haircolor_pattern)) {
      return response()->json(['message' => '고양이 털 색깔/무늬를 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    } elseif (empty($type)) {
      return response()->json(['message' => '유저 형태(멘토/멘티)를 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
    }

    // $name 변수의 공백 제거(이름에 띄어쓰기가 있을 경우 공백 제거)
    $name = preg_replace("/\s+/", "", $name);

    // 이메일 검사 시작
    if ($email) {
      // 이메일 유효성 검사 시작
      // 정규식에 안 맞는 유형의 이메일이 들어오면 바로
      // 에러 출력 "올바른 이메일 주소를 입력해 주세요."
      if (preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email) == false) {
        return response()->json(['message' => '올바른 이메일 주소를 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
      }
    }

    // 비밀번호 유효성 검사
    if ($password) {
      $num = preg_match('/[0-9]/u', $password);
      $eng = preg_match('/[a-z]/u', $password);
      $spe = preg_match("/[\!\@\#\$\%\^\&\*]/u", $password);

      if (strlen($password) < 10 || strlen($password) > 30) {
        return response()->json(['message' => '비밀번호는 영문, 숫자, 특수문자를 혼합하여 최소 10자리 ~ 최대 30자리 이내로 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
      } elseif (preg_match("/\s/u", $password) == true) {
        return response()->json(['message' => '비밀번호는 공백 없이 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
      } elseif ($num == 0 || $eng == 0 || $spe == 0) {
        return response()->json(['message' => '비밀번호는 영문, 숫자, 특수문자를 혼합하여 입력해 주세요.'], 400, [], JSON_UNESCAPED_UNICODE);
      }
    }

    $date = new DateTime();
    $date->setTimezone(new DateTimeZone('Asia/Seoul'));
    $DateAndTime = $date->format("Y-m-d h:i:s");

    $uniqueId = $request->input('unique_id');
    $password = $request->input('password');

    if ($uniqueId == null) {
      return response()->json(['message' => '업데이트를 할 수 없습니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }

    function password_crypt($string, $action = 'encrypt') // $action 값은 기본값을 encrypt로 한다.
    {
      $secret_key = 'chosangho_secret_key';
      $secret_iv = 'chosangho_secret_iv';

      $output = false;
      $encrypt_method = "AES-256-CBC";
      $key = hash('sha256', $secret_key);
      $iv = substr(hash('sha256', $secret_iv), 0, 16);

      if ($action == 'encrypt') { // encrypt는 암호화
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));

      } else if ($action == 'decrypt') { // decrypt는 복호화
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
      }

      return $output;
    }

    // 비밀번호 암복호화 함수에 비밀번호를 넣어서 암호화한 뒤 리턴 값으로 받아서 $encryptedPassword 변수에 저장
    $encryptedPassword = password_crypt($password, 'encrypt');

    // unique_id로 users 테이블 조회 후에 업데이트
    $users =
      DB::table('users')
        ->where('unique_id', '=', $uniqueId)
        ->update([
          'name' => $name,
          'email' => $email,
          'password' => $encryptedPassword,
          'kind' => $kind,
          'age' => $age,
          'haircolor_pattern' => $haircolor_pattern,
          'type' => $type,
          'updated_at' => $DateAndTime
        ]);

    if ($users) {
      return response()->json(['message' => '업데이트 되었습니다.'], 200, [], JSON_UNESCAPED_UNICODE);
    } else {
      return response()->json(['message' => '업데이트 되지 않았습니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }
  }


  /**
   * 사용자 삭제하기
   */
  public function destroy(Request $request)
  {
    $uniqueId = $request->input('unique_id');

    $users =
      DB::table('users')
        ->where('unique_id', '=', $uniqueId)
        ->delete();

    if ($users) {
      return response()->json(['message' => '삭제되었습니다.'], 200, [], JSON_UNESCAPED_UNICODE);
    } else {
      return response()->json(['message' => '존재하지 않는 unique_id 입니다.'], 400, [], JSON_UNESCAPED_UNICODE);
    }
  }
}
