<?php
// توکن ربات
define('BOT_TOKEN', '8139020616:AAGiuDwWnnqUL9Df5Vto6V8q6qIKqEi92b0');
// فایل لاگ
define('LOG_FILE', 'log.txt');

// دریافت اطلاعات ارسالی از تلگرام
$content = file_get_contents("php://input");
$update = json_decode($content, true);

$chat_id = $update['message']['chat']['id'] ?? null;
$text = $update['message']['text'] ?? '';
$message_id = $update['message']['message_id'] ?? null;

// حافظه ساده برای ذخیره وضعیت ورود (برای نمونه فقط با فایل ساده)
// تو واقعی باید دیتابیس یا روش بهتری استفاده کنی
$auth_file = "auth_$chat_id.txt";

// ارسال پیام به تلگرام
function sendMessage($chat_id, $text, $reply_markup = null) {
    $url = "https://api.telegram.org/bot".BOT_TOKEN."/sendMessage";
    $data = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];

    if ($reply_markup !== null) {
        $data['reply_markup'] = json_encode($reply_markup);
    }

    $options = [
        'http' => [
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context  = stream_context_create($options);
    file_get_contents($url, false, $context);
}

// ساخت کیبورد دکمه‌ها
function makeKeyboard($buttons) {
    return [
        'keyboard' => [$buttons],
        'one_time_keyboard' => true,
        'resize_keyboard' => true
    ];
}

// چک کردن وضعیت لاگین
function isLoggedIn($chat_id) {
    global $auth_file;
    return file_exists("auth_$chat_id.txt") && trim(file_get_contents("auth_$chat_id.txt")) === '1';
}

// ذخیره وضعیت لاگین
function setLoggedIn($chat_id, $state) {
    file_put_contents("auth_$chat_id.txt", $state ? '1' : '0');
}

// پاسخ به دستورات و پیام‌ها
if ($chat_id) {
    if ($text === '/start') {
        setLoggedIn($chat_id, false);
        sendMessage($chat_id, "سلام! لطفا رمز ورود را وارد کنید:", makeKeyboard(['لغو']));
    }
    elseif (!isLoggedIn($chat_id)) {
        if ($text === '1234') {
            setLoggedIn($chat_id, true);
            sendMessage($chat_id, "ورود موفقیت آمیز بود!", makeKeyboard(['دیدن لاگ']));
        } elseif ($text === 'لغو') {
            sendMessage($chat_id, "ورود لغو شد. /start را بزنید دوباره.");
        } else {
            sendMessage($chat_id, "رمز اشتباه است. لطفا دوباره تلاش کنید یا لغو را بزنید.");
        }
    }
    else {
        if ($text === 'دیدن لاگ') {
            if (file_exists(LOG_FILE)) {
                $log = file_get_contents(LOG_FILE);
                if (strlen($log) > 4000) {
                    $log = substr($log, -4000);  // برای جلوگیری از طول زیاد پیام، فقط آخرش رو می‌فرسته
                }
                sendMessage($chat_id, "<pre>".htmlspecialchars($log)."</pre>");
            } else {
                sendMessage($chat_id, "فایل لاگ پیدا نشد.");
            }
        }
        else {
            sendMessage($chat_id, "دستور نامعتبر. از دکمه‌ها استفاده کنید.");
        }
    }
}

echo "ok"; // پاسخ به تلگرام
