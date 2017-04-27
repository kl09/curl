#Example

    $obj = self::response()
        ->setSiteHost('https://example.org/')
        ->setUrl('/api/v1/MethodName')
        ->setMethod('POST')
        ->setBody($query)
        ->execute();

    $arData = [
        'status' => $obj->getStatusCode(),
        'timer' => $obj->getTimer(),
        'url' => $obj->getUrl(),
        'method' => $obj->getMethod(),
        'body' => $obj->getBody(),
    ];

