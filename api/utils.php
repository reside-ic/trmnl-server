<?php

if (!function_exists('bailOut')) {
    function bailOut($code, $message)
    {
        echo json_encode(["status" => $code, "error" => $message]);
    }
}

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $header = str_replace(
                    ' ',
                    '-',
                    ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))
                );
                $headers[$header] = $value;
            }
        }
        return $headers;
    }
}
