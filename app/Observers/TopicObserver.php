<?php

namespace App\Observers;

use App\Models\Topic;
// use App\Handlers\SlugTranslateHandler;

//队列出来
use App\Jobs\TranslateSlug;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class TopicObserver
{
    public function creating(Topic $topic)
    {
        //
    }

    public function updating(Topic $topic)
    {
        //
    }

    public function saving(Topic $topic)
    {
        // XSS 过滤
        $topic->body = clean($topic->body, 'user_topic_body');

        // 生成话题摘录
        $topic->excerpt = make_excerpt($topic->body);

        // 1.如 slug 字段无内容，即使用翻译器对 title 进行翻译
        // if ( ! $topic->slug) {
        //     $topic->slug = app(SlugTranslateHandler::class)->translate($topic->title);
        // }

        // 2.如 slug 字段无内容，即使用翻译器对 title 进行翻译  队列处理
        // if ( ! $topic->slug) {
        //     // 推送任务到队列
        //     dispatch(new TranslateSlug($topic));
        // }
    }


    // 模型监控器的 saved() 方法对应 Eloquent 的 saved 事件，此事件发生在创建和编辑时、数据入库以后。在 saved() 方法中调用，确保了我们在分发任务时，$topic->id 永远有值。

    // 需要注意的是，artisan horizon 队列工作的守护进程是一个常驻进程，它不会在你的代码改变时进行重启，当我们修改代码以后，需要在命令行中对其进行重启操作。


    public function saved(Topic $topic)
    {
        // 3.如 slug 字段无内容，即使用翻译器对 title 进行翻译 新建的时候 还有 topic 模型实例
        if ( ! $topic->slug) {

            // 推送任务到队列
            dispatch(new TranslateSlug($topic));
        }
    }

    //话题连带删除回复
    public function deleted(Topic $topic)
    {
        \DB::table('replies')->where('topic_id', $topic->id)->delete();
    }
}