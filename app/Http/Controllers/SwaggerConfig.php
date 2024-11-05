<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="TailorInch API",
 *     version="1.0.0",
 *     description="A brief description of your API"
 * )
 * @OA\Server(
 *     url="/api",
 *     description="Local API Server"
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Use a Bearer token to authorize requests",
 *     name="Authorization",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth",
 * )
 */
class SwaggerConfig
{
    // Additional Swagger configuration can go here
}
