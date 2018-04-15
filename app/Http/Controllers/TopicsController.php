<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Topic;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use App\Handlers\ImageUploadHandler;

class TopicsController extends Controller
{
    public function __construct()
    {
        //限制未登录用户发帖
        //'except' => ['index', 'show'] —— 对除了 index() 和 show() 以外的方法使用 auth 中间件进行认证。
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

	public function index(Request $request,Topic $topic)
	{

        $topics = $topic->withOrder($request->order)->paginate(15);

        return view('topics.index', compact('topics'));
	}

    public function show(Topic $topic)
    {
        return view('topics.show', compact('topic'));
    }

	public function create(Topic $topic)
	{
        $categories = Category::all();
		return view('topics.create_and_edit', compact('topic','categories'));
	}

	public function store(TopicRequest $request,Topic $topic)
	{
        // $topic = Topic::create($request->all());
        /* 因为要使用到 Auth 类，所以需在文件顶部进行加载；
        // store() 方法的第二个参数，会创建一个空白的 $topic 实例；
        // $request->all() 获取所有用户的请求数据数组，如 ['title' => '标题', 'body' => '内容', ... ]；
        // $topic->fill($request->all()); fill 方法会将传参的键值数组填充到模型的属性中，如以上数组，$topic->title 的值为 标题；
        // Auth::id() 获取到的是当前登录的 ID；
        // $topic->save() 保存到数据库中。*/
        $topic->fill($request->all());
        $topic->user_id = Auth::id();

        // $topic->excerpt = $request->title;
        /*已由【模型观察器】生成*/

        $topic->save();
		return redirect()->route('topics.show', $topic->id)->with('message', 'Created successfully.');
	}

	public function edit(Topic $topic)
	{
        $this->authorize('update', $topic);
		return view('topics.create_and_edit', compact('topic'));
	}

	public function update(TopicRequest $request, Topic $topic)
	{
		$this->authorize('update', $topic);
		$topic->update($request->all());

		return redirect()->route('topics.show', $topic->id)->with('message', 'Updated successfully.');
	}

	public function destroy(Topic $topic)
	{
		$this->authorize('destroy', $topic);
		$topic->delete();

		return redirect()->route('topics.index')->with('message', 'Deleted successfully.');
	}

    public function uploadImage(Request $request,ImageUploadHandler $uploader){
        $data = [
            "success"=>false,
            "msg"=>'上传失败',
            "file_path"=>'',
        ];
        if($file = $request->upload_file){
            $res = $uploader->save($file, 'topics', \Auth::id(),1024);
            if($res){
                $data = [
                    "success"=>true,
                    "msg"=>'上传成功',
                    "file_path"=>$res['path'],
                ];
            }

        }
        return $data;
    }
}