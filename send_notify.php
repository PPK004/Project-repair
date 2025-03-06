<?php
function sendLineNotify($message) {
    $token = 'PUy0GwPETd97Ae3u0ODUhL5tTgr7ehBQYWI4VZQ8aQK';
    $url = 'https://notify-api.line.me/api/notify';

    if (empty($token)) {
        error_log('LINE Notify token ไม่สามารถเป็นค่าว่างได้');
        return false;
    }

    if (empty($message)) {
        error_log('ข้อความแจ้งเตือนไม่สามารถเป็นค่าว่างได้');
        return false;
    }

    $data = ['message' => $message];
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/x-www-form-urlencoded'
    ];

    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new Exception('ข้อผิดพลาด cURL: ' . curl_error($ch));
        }

        if ($http_code !== 200) {
            throw new Exception('ส่ง LINE Notify ไม่สำเร็จ: HTTP Code ' . $http_code . ', Response: ' . $response);
        }

        curl_close($ch);
        return json_decode($response, true);

    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}
?>
