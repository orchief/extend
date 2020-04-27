<?php
// +----------------------------------------------------------------------
// | Description: {$Description}
// +----------------------------------------------------------------------
// | Author: {$Author}
// +----------------------------------------------------------------------
// | Date: {$Date}
// +----------------------------------------------------------------------

namespace {$namespace}\controller;

use app\BaseController;

/**
 * @route('{$uri}')
 */
class {$class} extends BaseController
{
    public $modelName = '{$class}';{$read}{$create}{$update}{$delete}
}