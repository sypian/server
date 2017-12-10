<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Returns the given strings formatted in double quotes and separated by a comma and space.
     *
     * @return string
     */
    public function getFormattedParams(array $params): string
    {
        $paramStr = '';

        foreach ($params as $paramKey) {
            $paramStr .= '"'.str_replace('"', '\"', $paramKey).'", ';
        }

        return trim($paramStr, ', ');
    }
}
