<?php
// +----------------------------------------------------------------------
// | Description: {$Description}
// +----------------------------------------------------------------------
// | Author: {$Author}
// +----------------------------------------------------------------------
// | Date: {$Date}
// +----------------------------------------------------------------------

namespace {$namespace}\controller;

use Utility\Controller;

/**
 * @route('{$uri}')
 */
class {$class} extends Controller
{
    public $modelName = '{$class}';{$read}{$create}{$update}{$delete}
}