
    /**
    * @OA\Delete({$auth}
    *     path="/{$uri}/{id}",
    *     tags={"{$Description}"},
    *     summary="删除{$Description}",
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
    *         response=400,
    *         description="Invalid input"
    *     ),
    * )
    */
    use \Rest\Delete;