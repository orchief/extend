const structure = {
    baseApi: '{$baseApi}', // 基础接口地址
    fields: {{$fields}
    },
    defaultSelectDataApi: [], //  源自接口的select选项默认数据
    defaultSelectData: {}, //  各位置select选项默认数据
    filterData: [
        {$filterData}
    ],
    excel: { // 导出excel配置信息
        fileName: '{$Description}列表', // 导出excel文件名称
        fields: [ // 导出字段 (范围必须在fields里边)
{$excelfields}
        ]
    },
    search: [ // 搜索选项{$search}
    ],
    defaultSearchConditions: { // 默认查询参数
        limit: 5,
        page: 1,
        isDelete: '0'
    },
    table: {
        sort: {
            fields: []
        },
        show: {
            fields: [
                {$showfields}
            ],
            width: []
        },
        hidden: { // 隐藏字段
            fields: []
        },
        batch: { // 批量操作
            buttons: []
        },
        operator: {
            del: true,
            edit: true
        }
    },
    add: {
        title: '新增{$Description}',
        show: true,
        form: [{$addform}
        ],
        rules: {
            {$addrules}
        }
    },
    edit: {
        title: '编辑{$Description}',
        show: true,
        form: [{$editform}
        ],
        rules: {
            {$editrules}
        }
    }
}

export default structure