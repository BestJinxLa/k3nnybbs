<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Topic;
use App\Handlers\SlugTranslateHandler;

class TranslateSlug implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    //引入了 SerializesModels trait，Eloquent 模型会被优雅的序列化和反序列化。队列任务构造器中接收了 Eloquent 模型，将会只序列化模型的 ID。这样子在任务执行时，队列系统会从数据库中自动的根据 ID 检索出模型实例。这样可以避免序列化完整的模型可能在队列中出现的问题。

    protected $topic;

    public function __construct(Topic $topic)
    {
        // 队列任务构造器中接收了 Eloquent 模型，将会只序列化模型的 ID
        $this->topic = $topic;
    }

    //handle 方法会在队列任务执行时被调用。值得注意的是，我们可以在任务的 handle 方法中可以使用类型提示来进行依赖的注入。Laravel 的服务容器会自动的将这些依赖注入进去，与控制器方法类似。

    //还有一点需要注意，我们将会在模型监控器中分发任务，任务中要避免使用 Eloquent 模型接口调用，如：create(), update(), save() 等操作。否则会陷入调用死循环 —— 模型监控器分发任务，任务触发模型监控器，模型监控器再次分发任务，任务再次触发模型监控器.... 死循环。在这种情况下，使用 DB 类直接对数据库进行操作即可。

    public function handle()
    {
        // 请求百度 API 接口进行翻译
        $slug = app(SlugTranslateHandler::class)->translate($this->topic->title);

        // 为了避免模型监控器死循环调用，我们使用 DB 类直接对数据库进行操作
        \DB::table('topics')->where('id', $this->topic->id)->update(['slug' => $slug]);
    }
}