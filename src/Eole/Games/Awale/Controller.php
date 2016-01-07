<?php

namespace Eole\Games\Awale;

use Eole\Core\ApiResponse;

class Controller
{
    /**
     * @return ApiResponse
     */
    public function getTest()
    {
        return new ApiResponse(array(
            'test' => 'ok',
        ));
    }
}
