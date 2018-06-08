<?php
require_once __DIR__ . "/Setting.php";
require_once __DIR__ . "/MySqliDBProcess.php";
require_once __DIR__ . "/SongBotService.php";
require_once __DIR__ . "/SpreadSheetService.php";
$setting = Setting::geSetting();
$access_token = $setting["Setting"]["LineMessagingAPI"]["access_token"];

//-----------------------
$isLogReceive = false;
$isLogSend = false;
$isLogSendResult = false;
//-------------------------
$isSend = true;

$json_string = file_get_contents('php://input');

if ($isLogReceive) {
    $file = fopen("Line_log.txt", "a+");
    fwrite($file, $json_string . "\n");
}
//-------- 取得傳遞資料
$json_obj = json_decode($json_string, true);
$event = $json_obj["events"][0];
$type = $event["message"]["type"];
$source = $event["source"];
$message = $json_obj["events"][0]["message"];
$reply_token = $event["replyToken"];

//--------Command Key
$recordSentenceKey = "#宋語錄";
$lookupSentenceKey = "#查語錄";
$helpSentenceKey = "#help";
$helpHint = "1. [#宋語錄] 加入柏儀語錄\n2. [#查語錄] 查經典\n3. [#TC 四位數字] 查TC卡號";
$lookupPageKey = "#P";
$lookupOnePieceTreasureCruise = "#TC";
//--------
if (empty($source["userId"])) {
    $sourceId = $source["groupId"];
} elseif (empty($source["groupId"])) {
    $sourceId = $source["userId"];
} else {
    $sourceId = "unknown";
}
$botService = new SongBotService($sourceId);
$request = $message["text"]; //儲存文字訊息
$result = $botService->getSession($recordSentenceKey);
$sessionValue = $result["SessionValue"];

/**處理紀錄文字 Process record sentence*/
//判斷Session是否是寫入值狀態
if (strcasecmp($message["text"], $helpSentenceKey) == 0 || $message["text"] == "肥豬出來面對") {
    $message["text"] = $helpHint;
} else if ($i = strpos($request, $lookupOnePieceTreasureCruise) !== false) {
    $lookupOnePieceTreasureCruiseData = explode(' ', $request);
    $cardNo = $lookupOnePieceTreasureCruiseData[1];
    $data = $botService->getTreasureCruiseData($cardNo);
    if (!isset($data)) {
        $message["text"]='找不到編號，或請確認是4位數編號，如編號127請輸入，#TC 0127';
    } else {
        foreach ($data as $row) {
            if ($row[0] == $cardNo) {
                $message["text"] = "編號:{$row['ID']}\n技能初始CD:{$row['OriCD']}\n技能滿技CD:{$row['MaxCD']}\n必殺技效果:{$row['Skill']}\n船長效果:{$row['CaptainSkill']}\n船員效果:{$row['CrewSkill']}";
                break;
            }
        }
    }
//    $message["text"] = $spreadservice->getFull();
} else if (strcasecmp($sessionValue, "write") === 0) {
    $record = explode(":", $request);
    //寫入值狀態 寫入指令是否正確
    if (!empty($record[0]) && !empty($record[1])) {
        //指令正確處理
        error_log($record[0]);
        $sentence = $botService->getSentence($record[0]);
        error_log($sentence["fld_sentence"]);
        if (empty($sentence["fld_sentence"])) {
            $result = $botService->addNewSentence($record[0], $record[1], 1, "text", $sourceId);
            if ($botService->removeSession($recordSentenceKey)) {
                $message["text"] = "經典句「{$record[0]}」 已經被記錄";
            } else {
                $message["text"] = "Session錯誤";
                $botService->removeSession($recordSentenceKey);
            }
        } else {
            $message["text"] = "經典句重覆";
        }
    } else {
        //指令錯誤處理
        $result = $botService->getSentence($message["text"]);
        $sentence = $result["fld_sentence"];
        $botService->removeSession($recordSentenceKey);
        if (empty($sentence))
            $message["text"] = "指令錯誤!";
        else
            $message["text"] = $sentence;
    }
} //處理關鍵字、指令文字開頭提示字
else {
    //紀錄宋語錄
    if ($i = strpos($recordSentenceKey, $request) !== false && $i >= 0) {
        $message["text"] = "你想記些什麼?  [關鍵字]:[回應內容]";
        $botService->setSession($recordSentenceKey, "write");
    } //查詢宋語錄
    else if ($i = strpos($request, $lookupSentenceKey) !== false) {
        $rowNum = $botService->getSongSentenceRow();
        //計算頁數
        if ($rowNum % 10 > 0)
            $pageAmount = floor($rowNum / 10 + 1);
        else
            $pageAmount = floor($rowNum / 10);
        $message["text"] = "一共有{$pageAmount}頁，請輸入#P[頁數]查詢。";
    } else if ($i = strpos($request, $lookupPageKey) !== false) {
        //驗證是否為關鍵字
        if ($i >= 0) {
            $pageNumber = substr($request, 2);
            if (intval($pageNumber) >= 1) {

                $dataSet = $botService->getPageData($pageNumber);
                $message["text"] = '';
                $i = ($pageNumber - 1) * 10 + 1;
                foreach ($dataSet as $row) {
                    $message["text"] .= "{$i}. {$row['fld_trigger']}->{$row['fld_sentence']}\n";
                    $i++;
                }
            } else {
                $message["text"] = 不存在頁碼;
            }
        } else {
            $message["text"] = 不存在頁碼;
        }

    } //發送已存在內容
    else {
        $result = $botService->getSentence($message["text"]);
        if (empty($result)) {
            $isSend = false;
        } else {
            $message["text"] = $result["fld_sentence"];
        }
    }

}

if ($isSend) {
    $post_data = array(
        "replyToken" => $reply_token,
        "messages" => array(
            array(
                "type" => "text",
                "text" => $message["text"]
            )
        )
    );
    //fwrite($file, json_encode($post_data) . "\n");

    $ch = curl_init($setting["Setting"]["LineMessagingAPI"]["replyRequest"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
        //'Authorization: Bearer '. TOKEN
    ));
    $result = curl_exec($ch);
    curl_close($ch);

    if ($isLogSendResult) {
        fwrite($file, $result . "\n");
    }
    if ($isLogReceive || $isLogSend || $isLogSendResult) {
        fclose($file);
    }

}
?>