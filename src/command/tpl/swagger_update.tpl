
    /**
    * @OA\Put({$auth}
    *     path="/{$uri}/{id}",
    *     tags={"{$Description}"},
    *     summary="更新{$Description}",
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
    {$params}
    *     @OA\Response(
    *         response=400,
    *         description="Invalid input"
    *     ),
    * )
    */
    use \Rest\Update;