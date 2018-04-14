<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Handlers\ImageUploadHandler;

class UsersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['show']]);
    }


    public function show(User $user){
        return view('users.show',compact('user'));
    }


    public function edit(User $user){
        try {
            $this->authorize('update', $user);
            return view('users.edit',compact('user'));
        } catch (AuthorizationException $e) {
            return  view('common.403');
        }
    }


    public function update(UserRequest $request, ImageUploadHandler $uploader, User $user)
    {
        try {
            $this->authorize('update', $user);
            $data = $request->all();
            if ($request->avatar) {
                $result = $uploader->save($request->avatar, 'avatars', $user->id,362);
                if ($result) {
                    $data['avatar'] = $result['path'];
                }
            }
            $user->update($data);
            return redirect()->route('users.show', $user->id)->with('success', '个人资料更新成功！');
        } catch (AuthorizationException $e) {
            return  view('common.403');
        }
    }

}
