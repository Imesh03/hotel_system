<?php
// send_whatsapp.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $qr_token = $_POST['qr_token'] ?? '';
    $qr_url = $_POST['qr_url'] ?? '';

    if (empty($name) || empty($phone) || empty($qr_token)) {
        echo json_encode(['success' => false, 'message' => 'Missing required data.']);
        exit;
    }

    // --- 94 වලට Phone Number එක සකස් කිරීම ---
    $clean_phone = preg_replace('/[^0-9]/', '', $phone);
    if (substr($clean_phone, 0, 1) === '0') {
        $formatted_phone = '94' . substr($clean_phone, 1);
    } else if (substr($clean_phone, 0, 2) !== '94') {
        $formatted_phone = '94' . $clean_phone;
    } else {
        $formatted_phone = $clean_phone;
    }

    $instance_id = "instance185244"; 
    $token = "tt80ob22i8fkcm4f"; 

    $msg = "ආයුබෝවන් $name, Hotel Panama වෙත ඔබව සාදරයෙන් පිළිගනිමු! \n\n"
         . "ඔබේ QR සබැඳිය: $qr_url \n"
         . "සෘජුව ලොග් වීමට (Manual Login): \n"
         . "Username: $phone \n"
         . "Password: $qr_token \n\n"
         . "ස්තුතියි!";

    $params = array(
        'token' => $token,
        'to' => $formatted_phone,
        'body' => $msg
    );

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.ultramsg.com/" . $instance_id . "/messages/chat",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_HTTPHEADER => array("content-type: application-x-www-form-urlencoded"),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo json_encode(['success' => false, 'message' => 'CURL Error: ' . $err]);
    } else {
        $result = json_decode($response, true);
        
        // Ultramsg නව API එකේ 'success' හෝ පැරණි API එකේ 'sent' තියෙදැයි පරීක්ෂාව
        if ( (isset($result['success']) && ($result['success'] === 'true' || $result['success'] === true)) || 
             (isset($result['sent']) && ($result['sent'] === 'true' || $result['sent'] === true)) ) {
            echo json_encode(['success' => true]);
        } else {
            // ගැටලුවක් ආවොත් සර්වර් එකෙන් ආපු මුල්ම පිළිතුර කෙලින්ම එවන්න
            echo json_encode(['success' => false, 'message' => 'API Error Response: ' . $response]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>