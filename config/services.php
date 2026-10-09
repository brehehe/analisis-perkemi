<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-6-sol'),
        'reasoning_effort' => env('OPENAI_REASONING_EFFORT', 'high'),
        'connect_timeout' => (int) env('OPENAI_CONNECT_TIMEOUT', 10),
        'timeout' => (int) env('OPENAI_TIMEOUT', 240),
        'max_output_tokens' => (int) env('OPENAI_MAX_OUTPUT_TOKENS', 20000),
        'frame_count' => (int) env('OPENAI_VIDEO_FRAME_COUNT', 48),
        'frame_max_width' => (int) env('OPENAI_VIDEO_FRAME_MAX_WIDTH', 1280),
        'frame_detail' => env('OPENAI_VIDEO_FRAME_DETAIL', 'high'),
        'ffmpeg_binary' => env('FFMPEG_BINARY', 'ffmpeg'),
        'ffprobe_binary' => env('FFPROBE_BINARY', 'ffprobe'),
    ],

    'youtube' => [
        'yt_dlp_binary' => env('YTDLP_BINARY', 'yt-dlp'),
        'download_timeout' => (int) env('YOUTUBE_DOWNLOAD_TIMEOUT', 600),
        'max_file_size_mb' => (int) env('YOUTUBE_MAX_FILE_SIZE_MB', 500),
        'max_duration_seconds' => (int) env('YOUTUBE_MAX_DURATION_SECONDS', 7200),
    ],

];
