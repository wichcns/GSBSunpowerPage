<?php

return [
    'title' => '系统集成',
    'description' => '设置仅发送到由超级管理员确认的 LINE 中央群组的通知。',
    'token_secret' => '令牌和密钥', 'configured' => '已配置', 'not_configured' => '尚未配置',
    'environment_sending' => '环境发送设置', 'enabled' => '已启用', 'disabled' => '已停用',
    'delivery_scope' => '发送范围', 'confirmed_group_only' => '仅推送到一个已确认的群组',
    'no_broadcast' => '不会向 OA 关注者群发', 'setup_steps' => '连接中央群组的步骤',
    'step_enable_api' => '启用 Messaging API 和 Allow bot to join group chats',
    'step_webhook' => '将 Webhook URL 设置为 :url', 'step_invite' => '邀请 LINE OA 加入中央群组，系统将在下方显示群组 ID。',
    'step_confirm' => '检查并确认群组，开启发送，然后发送测试消息。',
    'automatic_events' => '自动通知事件', 'automatic_events_description' => '默认全部停用，请仅选择需要发送到中央群组的事件。',
    'save_notifications' => '保存通知设置', 'detected_groups' => 'Webhook 检测到的群组',
    'detected_groups_description' => '检测到群组并不代表允许发送，必须手动确认。',
    'last_seen' => '最近发现：:date', 'enable_delivery' => '开启正式发送', 'confirmed' => '已确认',
    'pending_confirmation' => '等待确认', 'confirm_group' => '确认此群组',
    'no_groups' => '尚未检测到群组，请先设置 Webhook 并邀请 OA 加入群组。', 'send_test' => '发送测试消息',
    'recent_history' => '最近发送记录', 'retry_description' => '系统最多自动重试 3 次（5 分钟和 15 分钟后）。已停止的项目可手动重试。',
    'no_recipient' => '尚无目标群组', 'attempt' => '第 :current/:total 次', 'next_attempt' => '下次重试：:date',
    'retry' => '重新发送', 'no_history' => '尚无发送记录',
    'statuses' => ['pending' => '等待发送', 'processing' => '发送中', 'failed' => '等待重试', 'exhausted' => '已停止自动重试', 'sent' => '发送成功'],
    'events' => [
        'site_owner_assigned' => '已分配场地负责人', 'sales_stage_changed' => '销售阶段已变更',
        'engineering_stage_changed' => '工程阶段已变更', 'construction_started' => '施工已开始',
        'construction_progress_updated' => '施工进度已更新', 'service_opened' => '服务已开通',
        'task_due_soon' => '任务即将到期', 'task_overdue' => '任务已逾期',
    ],
    'messages' => [
        'group_confirmed' => 'LINE 中央群组已确认。', 'test_sent' => '测试消息已发送到确认的群组。',
        'test_failed' => '无法发送，请检查令牌、发送设置和已确认的群组。', 'settings_saved' => '通知事件设置已保存。',
        'retry_sent' => '通知重新发送成功。', 'retry_failed' => '无法重新发送，请检查目标群组或项目状态。',
    ],
];
