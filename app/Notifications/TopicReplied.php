<?php

namespace App\Notifications;


// use Illuminate\Queue\SerializesModels;
// use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Reply;

// class TopicReplied extends Notification

// 引入队列
// Laravel 会检测 ShouldQueue 接口并自动将通知的发送放入队列中，所以我们不需要做其他修改。
// 将 QUEUE_DRIVER 的值改为 redis：

// .env

// QUEUE_DRIVER=redis
// 命令行运行队列监控：

// $ php artisan horizon
// 发送邮件测试，可以发现速度大大提高，队列监控也接收到队列任务，并成功处理
class TopicReplied extends Notification implements ShouldQueue
{
    use Queueable;
    // use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reply;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Reply $reply)
    {
        // 注入回复实体，方便 toDatabase 方法中的使用
        $this->reply = $reply;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // 开启通知的频道

        // return ['database'];

        //新增邮箱通知频道
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // return (new MailMessage)
        //             ->line('The introduction to the notification.')
        //             ->action('Notification Action', url('/'))
        //             ->line('Thank you for using our application!');

        $url = $this->reply->topic->link(['#reply' . $this->reply->id]);

        return (new MailMessage)
                    ->line('你的话题有新回复！')
                    ->action('查看回复', $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }


    public function toDatabase($notifiable)
    {
        $topic = $this->reply->topic;
        $link =  $topic->link(['#reply' . $this->reply->id]);

        // 存入数据库里的数据
        return [
            'reply_id' => $this->reply->id,
            'reply_content' => $this->reply->content,
            'user_id' => $this->reply->user->id,
            'user_name' => $this->reply->user->name,
            'user_avatar' => $this->reply->user->avatar,
            'topic_link' => $link,
            'topic_id' => $topic->id,
            'topic_title' => $topic->title,
        ];
    }
}
