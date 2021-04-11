<?php

class Notification
{
    public function init(): void {
        $elephantArt = <<<ART
                _ __ _
               / |..| \
               \/ || \/
                |_''_|
               PHP CODE
        ART;
        echo "\033[34m{$elephantArt}\n";

        $logoArt = <<<ART
             FURRY UNICORN
        ART;
        echo "\033[35m{$logoArt}\n\n";
        echo "\033[37m";
    }

    public function drawOtherNotification(string $message): void {
        echo "\033[37m{$message}\e[0m\n";
    }

    public function drawInformNotification(string $message): void {
        echo "\033[33m{$message}\e[0m\n";
    }

    public function drawSuccessNotification(string $message): void {
        echo "\033[32m{$message}\033[0m\n";
    }

    public function drawErrorNotification(string $error): void {
		echo "\033[31m{$error}\033[0m\n";
    }
}
