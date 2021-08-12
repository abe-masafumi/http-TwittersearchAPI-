<?php
function h($str)
{
  return htmlspecialchars($str, ENT_QUOTES, 'utf-8');
}
/**************************************************

	[GET search/tweets]のお試しプログラム

	認証方式: アクセストークン

	配布: SYNCER
	公式ドキュメント: https://dev.twitter.com/rest/reference/get/search/tweets
	日本語解説ページ: https://syncer.jp/Web/API/Twitter/REST_API/GET/search/tweets/

 **************************************************/

// 設定
$api_key = '';    // APIキー
$api_secret = '';    // APIシークレット
$access_token = '';    // アクセストークン
$access_token_secret = '';    // アクセストークンシークレット
$request_url = 'https://api.twitter.com/1.1/search/tweets.json';    // エンドポイント
$request_method = 'GET';


// パラメータA (オプション)
$params_a = array(
  // ハッシュタグは増やせる---
  "q" => "＃動物 OR ＃拡散希望 犬 OR ＃犬 OR ＃猫 OR ＃犬猫 OR ＃小動物 OR ＃迷子犬 OR ＃野良 OR ＃野犬 OR ＃首輪 OR ＃保健所 OR ＃収容 OR ＃保護  OR ＃ミルクボランティア OR ＃里親 OR ＃飼い主 OR ＃遺棄 OR ＃虐待 OR ＃ダンボール",

  //		"geocode" => "35.794507,139.790788,1km",
  "lang" => "ja",
  "locale" => "ja",
  // "result_type" => "popular",
  "count" => "10",
  //		"until" => "2017-01-17",
  //		"since_id" => "643299864344788992",
  //		"max_id" => "643299864344788992",
  "include_entities" => "true",
);

// キーを作成する (URLエンコードする)
$signature_key = rawurlencode($api_secret) . '&' . rawurlencode($access_token_secret);

// パラメータB (署名の材料用)
$params_b = array(
  'oauth_token' => $access_token,
  'oauth_consumer_key' => $api_key,
  'oauth_signature_method' => 'HMAC-SHA1',
  'oauth_timestamp' => time(),
  'oauth_nonce' => microtime(),
  'oauth_version' => '1.0',
);

// パラメータAとパラメータBを合成してパラメータCを作る
$params_c = array_merge($params_a, $params_b);

// 連想配列をアルファベット順に並び替える
ksort($params_c);

// パラメータの連想配列を[キー=値&キー=値...]の文字列に変換する
$request_params = http_build_query($params_c, '', '&');

// 一部の文字列をフォロー
$request_params = str_replace(array('+', '%7E'), array('%20', '~'), $request_params);

// 変換した文字列をURLエンコードする
$request_params = rawurlencode($request_params);

// リクエストメソッドをURLエンコードする
// ここでは、URL末尾の[?]以下は付けないこと
$encoded_request_method = rawurlencode($request_method);

// リクエストURLをURLエンコードする
$encoded_request_url = rawurlencode($request_url);

// リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
$signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params;

// キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
$hash = hash_hmac('sha1', $signature_data, $signature_key, TRUE);

// base64エンコードして、署名[$signature]が完成する
$signature = base64_encode($hash);

// パラメータの連想配列、[$params]に、作成した署名を加える
$params_c['oauth_signature'] = $signature;

// パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
$header_params = http_build_query($params_c, '', ',');

// リクエスト用のコンテキスト
$context = array(
  'http' => array(
    'method' => $request_method, // リクエストメソッド
    'header' => array(        // ヘッダー
      'Authorization: OAuth ' . $header_params,
    ),
  ),
);

// パラメータがある場合、URLの末尾に追加
if ($params_a) {
  $request_url .= '?' . http_build_query($params_a);
}


// オプションがある場合、コンテキストにPOSTフィールドを作成する (GETの場合は不要)
//	if( $params_a ) {
//		$context['http']['content'] = http_build_query( $params_a ) ;
//	}

// cURLを使ってリクエスト
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $request_url);
curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $context['http']['method']);  // メソッド
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  // 証明書の検証を行わない
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  // curl_execの結果を文字列で返す
curl_setopt($curl, CURLOPT_HTTPHEADER, $context['http']['header']);  // ヘッダー
//	if( isset( $context['http']['content'] ) && !empty( $context['http']['content'] ) ) {		// GETの場合は不要
//		curl_setopt( $curl , CURLOPT_POSTFIELDS , $context['http']['content'] ) ;	// リクエストボディ
//	}
curl_setopt($curl, CURLOPT_TIMEOUT, 5);  // タイムアウトの秒数
$res1 = curl_exec($curl);
$res2 = curl_getinfo($curl);
curl_close($curl);

// 取得したデータ
$json = substr($res1, $res2['header_size']);    // 取得したデータ(JSONなど)
$header = substr($res1, 0, $res2['header_size']);  // レスポンスヘッダー (検証に利用したい場合にどうぞ)

// [cURL]ではなく、[file_get_contents()]を使うには下記の通りです…
// $json = file_get_contents( $request_url , false , stream_context_create( $context ) ) ;




// JSONをオブジェクトに変換
// $obj = json_decode($json);


// $obj_json = json_encode($json,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
// header("Access-Control-Allow-Origin: *");
// echo $obj_json;
// echo "<pre>";
// echo $obj_json;
// echo "</pre>";

// 実験これ綺麗！！↓
// header("Access-Control-Allow-Origin: *");
// $person = [
//   'name' => '太郎',
//   'age' => 18,
//   'email' => 'taro@example.com',
// ];
// echo json_encode($person, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
// exit;

// これが一番まし↓
// $result = json_decode($json, true);
// var_dump($result);
// $result = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
// header("Access-Control-Allow-Origin: *");
// echo json_encode($result);

// これでいけた↓
header("Access-Control-Allow-Origin: *");
// これはいらなかった
// header('Content-Type: text/html; charset=utf-8');
// この２つはブラウザでデータを読みたい場合
$obj = json_decode($json,true);
echo json_encode($obj, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
// echo $json;
exit();

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <style>
    body {
      word-wrap: break-word;
      white-space: pre-wrap;
    }

    pre {
      display: block;
      font-family: monospace;
      white-space: pre;
      margin: 1em 0px;
    }
    
  </style>

</head>

<body>
  <div id="root"></div>
  <script>
  
  
    // var xhr = new XMLHttpRequest();
    // xhr.open('GET', 'https://usefulapis.net/api');
    // xhr.addEventListener('load', onLoadFunc, false);
    // xhr.send(null);
    const root = document.getElementById('root');

    let js_array = <?= $json ?>;
    console.log(js_array);
    root.innerHTML = JSON.stringify(js_array, null, 2);
  </script>
</body>

</html>