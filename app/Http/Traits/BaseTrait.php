<?php

namespace App\Http\Traits;

trait BaseTrait {
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * In the local environment, notification emails are redirected to a
     * fixed testing inbox instead of the real recipient, so local
     * development never sends mail to actual students/lecturers.
     */
    function notificationRecipient($email) {
        if (config('app.env') === 'local') {
            return 'vyvy1777@gmail.com';
        }

        return $email;
    }

}