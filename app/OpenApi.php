<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Laravel CRUD API",
    description: "Laravel 12 REST API Documentation"
)]
#[OA\Server(
    url: "http://127.0.0.1:8000/api/v1",
    description: "Local Server"
)]
class OpenApi
{
}