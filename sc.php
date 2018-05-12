<?php
$i = 0;
while (true) {



    $url = "http://m.yyygcs.vip/index.php/mobile/test/get_auto_recharge";
    if ($i % 60 == 0) {
        echo "$url\n";
        file_get_contents($url);
    }

    
    //sleep(60);
    usleep(1000000 * 0.8);
    $i++;
    echo "第{$i}次\n";
}

