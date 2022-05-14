<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\ParentController;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminUserController extends ParentController
{
    /**
     * Показывает всех пользователей в админ-панели
     *
     * GET /admin/user/index
     *
     * @return View | HttpException
     */
    public function index()
    {
        self::checkAdmin();

        // $users = User::withTrashed()
        //     ->orderBy('created_at', 'desc')
        //     ->get();
        $users = User::all();

        return view('admin.user.index')->with([
            'users' => $users,
        ]);
    }

    /**
     * Выводит форму для редактирования статьи
     *
     * GET /admin/user/update.{id}
     *
     * @var int $id
     *
     * @return View | HttpException
     */
    public function edit($id)
    {
        self::checkAdmin();

        // $user = User::withTrashed()
        //     ->where('id', $id)
        //     ->first();
        $user = User::where('id', $id)->first();

        return view('admin.user.update')->with([
            'user' => $user,
        ]);
    }

    /**
     * Редактирует статью
     *
     * POST /admin/user/update
     *
     * @param Request $request
     *
     * @return RedirectResponse | HttpException
     */
    public function update(Request $request)
    {
        self::checkAdmin();

        $this->validate($request, [
            'name' => ['required', 'max:255'],
        ]);

        user::withTrashed()
            ->where('id', $request->id)
            ->update($request->all());

        return redirect()->back();
    }

    /**
     * Удаляет статью
     *
     * DELETE /admin/user/delete/{id}
     *
     * @param $id
     *
     * @return RedirectResponse | HttpException
     */
    public function destroy($id)
    {
        self::checkAdmin();

        User::find($id)->delete();

        return redirect()->back();
    }

    /**
     * Восстанавливает s
     *
     * GET /admin/category/restore/{id}
     *
     * @param $id
     *
     * @return RedirectResponse | HttpException
     */
    public function restore($id)
    {
        self::checkAdmin();

        user::withTrashed()
            ->where('id', $id)
            ->restore();

        return redirect()->back();
    }
}
