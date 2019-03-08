<?php
// +----------------------------------------------------------------------
// | Description: {$Description}
// +----------------------------------------------------------------------
// | Author: {$Author}
// +----------------------------------------------------------------------
// | Date: {$Date}
// +----------------------------------------------------------------------

namespace {$namespace}\validate;

use Utility\Validate;

class {$class} extends Validate
{
    protected $rule = array({$rule}
    );
    protected $field = array({$field}
    );
    protected $scene = [
        "create" => [{$create}],
        "update" => [{$update}
        ]
    ];
}

