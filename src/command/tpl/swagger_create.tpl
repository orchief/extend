
    /**
    * @OA\Post({$auth}
    *     path="/{$uri}",
    *     tags={"{$Description}"},
    *     summary="新增{$Description}",
    {$params}
    *     @OA\Response(
    *         response=400,
    *         description="Invalid input"
    *     ),
    * )
    */
    use \Rest\Save;