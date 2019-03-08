
    /**
    * @OA\Get({$auth}
    *     path="/{$uri}",
    *     tags={"{$Description}"},
    *     summary="获取全部{$Description}",
    {$params}*     @OA\Response(
    *     response=200,
    *     description="{$Description}列表",
    *     @OA\JsonContent(
    *     type="array",
    *     @OA\Items(
    {$items}*
    *     )
    *     )
    *     )
    * )
    */
    use \Rest\Index;

    /**
    * @OA\Get({$auth}
    *     path="/{$uri}/{id}",
    *     tags={"{$Description}"},
    *     summary="获取{$Description}",
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="主键",
    *         required=false,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="{$Description}",
    *         @OA\JsonContent(
{$items}*     ),
    *     ),
    * )
    */
    use \Rest\Read;

