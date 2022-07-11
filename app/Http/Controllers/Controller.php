<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="My-Advantage App",
 *      description="",
 *      @OA\Contact(
 *          email="solomon.ahamba@botosoft.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Demo API Server"
 * )
 *
 * @OAS\SecurityScheme(
 *      securityScheme="bearer_token",
 *      type="https",
 *      scheme="bearer"
 * )
 *
 * @OA\Tag(
 *     name="Customer",
 *     description="Customer Endpoints "
 * )
 *
 * @OA\PathItem(
 *      path="api/v1"
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

}
