<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backpack Crud Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the CRUD interface.
    | You are free to change them to anything
    | you want to customize your views to better match your application.
    |
    */

    // Forms
    'save_action_save_and_new' => '保存并新建项目',
    'save_action_save_and_edit' => '保存并编辑项目',
    'save_action_save_and_back' => '保存并返回',
    'save_action_changed_notification' => '保存后的默认行为已更改。',

    // Create form
    'add'                 => '新建',
    'back_to_all'         => '返回到所有 ',
    'cancel'              => '取消',
    'add_a_new'           => '新建项目 ',

    // Edit form
    'edit'                 => '编辑',
    'save'                 => '保存',

    // Revisions
    'revisions'            => '修订版本',
    'no_revisions'         => '找不到修订版本',
    'created_this'          => '创建此项',
    'changed_the'          => '更改此项',
    'restore_this_value'   => '恢复此值',
    'from'                 => '从',
    'to'                   => '到',
    'undo'                 => '撤销',
    'revision_restored'    => '成功恢复修订版本',

    // CRUD table view
    'all'                       => '全部 ',
    'in_the_database'           => '在数据库中',
    'list'                      => '列表',
    'actions'                   => '动作',
    'preview'                   => '预览',
    'delete'                    => '删除',
    'admin'                     => '管理员',
    'details_row'               => '这是详细信息行。 你可以随时修改。',
    'details_row_loading_error' => '加载详细信息时出错，请重试。',

        // Confirmation messages and bubbles
        'delete_confirm'                              => '你确定要删除此项目吗？',
        'delete_confirmation_title'                   => '项目已删除',
        'delete_confirmation_message'                 => '项目删除成功。',
        'delete_confirmation_not_title'               => '未删除',
        'delete_confirmation_not_message'             => '发生错误，你的项目没有被删除。',
        'delete_confirmation_not_deleted_title'       => '未删除',
        'delete_confirmation_not_deleted_message'     => '什么都没有发生，你的项目是安全的。',

        // DataTables translation
        'emptyTable'     => '此表中暂无数据',
        'info'           => '正在显示 _START_ 到 _END_ 共 _TOTAL_ 个条目',
        'infoEmpty'      => '正在显示 0 到 0 共 0 个条目',
        'infoFiltered'   => '(已从 _MAX_ 过滤共 total 个条目)',
        'infoPostFix'    => '',
        'thousands'      => ',',
        'lengthMenu'     => '_MENU_ 条记录每页',
        'loadingRecords' => '正在加载...',
        'processing'     => '正在处理...',
        'search'         => '搜索： ',
        'zeroRecords'    => '找不到匹配的记录',
        'paginate'       => [
            'first'    => '第一页',
            'last'     => '最后一页',
            'next'     => '下一页',
            'previous' => '上一页',
        ],
        'aria' => [
            'sortAscending'  => ': 激活以对列进行升序排序',
            'sortDescending' => ': 激活以对列进行降序排序',
        ],

    // global crud - errors
        'unauthorized_access' => '未经授权的认证 - 你没有权限访问此页面。',
        'please_fix' => '请更正以下错误：',

    // global crud - success / error notification bubbles
        'insert_success' => '新建项目成功',
        'update_success' => '项目修改成功。',

    // CRUD reorder view
        'reorder'                      => '排序',
        'reorder_text'                 => '使用拖放来重新排序',
        'reorder_success_title'        => '完成',
        'reorder_success_message'      => '你的排序已保存。',
        'reorder_error_title'          => '错误',
        'reorder_error_message'        => '你的排序未保存。',

    // CRUD yes/no
        'yes' => '是',
        'no' => '否',

    // Fields
        'browse_uploads' => '浏览上传的文件',
        'clear' => '清除',
        'page_link' => '页面链接',
        'page_link_placeholder' => 'http://example.com/your-desired-page',
        'internal_link' => '内部链接',
        'internal_link_placeholder' => '内部链接占位符。例如： \'admin/page\' (no quotes) for \':url\'',
        'external_link' => '外部链接',
        'choose_file' => '选择文件',

    //Table field
        'table_cant_add' => '无法新建 :entity',
        'table_max_reached' => '已达到 :max 上限',

    // File manager
    'file_manager' => '文件管理器',
];
