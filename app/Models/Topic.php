<?php

namespace App\Models;

class Topic extends Model
{
    //protected $fillable = ['title', 'body', 'user_id', 'category_id', 'reply_count', 'view_count', 'last_reply_user_id', 'order', 'excerpt', 'slug'];
    // user_id —— 文章的作者，我们不希望文章的作者可以被随便指派；
    // last_reply_user_id —— 最后回复的用户 ID，将有程序来维护；
    // order —— 文章排序，将会是管理员专属的功能；
    // reply_count —— 回复数量，程序维护；
    // view_count —— 查看数量，程序维护；


    // 字段名称    描述  字段类型    加索引缘由   其他
    // title   帖子标题    字符串（String） 文章搜索    无
    // body    帖子内容    文本（text）    不需要 无
    // user_id 用户 ID   整数（int） 数据关联    unsigned()
    // category_id 分类 ID   整数（int） 数据关联    unsigned()
    // reply_count 回复数量    整数（int） 文章回复数量排序    unsigned(), default(0)
    // view_count  查看总数    整数（int） 文章查看数量排序    unsigned(), default(0)
    // last_reply_user_id  最后回复的用户 ID  整数（int） 数据关联    unsigned(), default(0)
    // order   可用来做排序使用    整数（int） 不需要 default(0)
    // excerpt 文章摘要，SEO 优化时使用  文本（text）    不需要 无
    // slug    SEO 友好的 URI 字符串（String） 不需要 nullable()

    protected $fillable = ['title', 'body', 'category_id', 'excerpt', 'slug'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWithOrder($query, $order)
    {
        // 不同的排序，使用不同的数据读取逻辑
        switch ($order) {
            case 'recent':
                $query = $this->recent();
                break;

            default:
                $query = $this->recentReplied();
                break;
        }
        // 预加载防止 N+1 问题
        return $query->with('user', 'category');
    }

    public function scopeRecentReplied($query)
    {
        // 当话题有新回复时，我们将编写逻辑来更新话题模型的 reply_count 属性，
        // 此时会自动触发框架对数据模型 updated_at 时间戳的更新
        return $query->orderBy('updated_at', 'desc');
    }

    public function scopeRecent($query)
    {
        // 按照创建时间排序
        return $query->orderBy('created_at', 'desc');
    }
}
