<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ParentController;
use App\Models\Tag;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminTagController extends ParentController
{
    /**
     * Показать все теги
     */
    public function index()
    {
        self::checkAdmin();

        // $tags = Tag::withTrashed()
        //     ->orderBy('id', 'desc')
        //     ->get();

        return view('admin.tag.index')->with([
            'tags' => Tag::all(),
        ]);
    }

    /**
     * Сохраняет тег
     *
     * POST /admin/tag/store
     *
     * @param Request $request
     *
     * @return RedirectResponse | HttpException
     */
    public function store(Request $request)
    {
        self::checkAdmin();

        $this->validate($request, [
            'title' => ['required', 'max:255'],
        ]);

        Tag::create($request->all());

        return redirect()->back();
    }

    /**
     * Редактирует тег
     *
     * POST /admin/tag/update
     *
     * @param Request $request
     *
     * @return RedirectResponse | HttpException
     */
    public function update(Request $request)
    {
        self::checkAdmin();

        $this->validate($request, [
            'title' => ['required', 'max:255'],
        ]);

        Tag::find($request->id)
            ->update($request->all());

        return redirect()->back();
    }

    /**
     * Удаляет тег
     *
     * DELETE /admin/tag/delete/{id}
     *
     * @param $id
     *
     * @return RedirectResponse | HttpException
     */
    public function destroy($id)
    {
        self::checkAdmin();

        Tag::find($id)->delete();

        return redirect()->back();
    }

    /**
     * Восстанавливает тег
     *
     * GET /admin/tag/restore/{tag}
     *
     * @param $id
     *
     * @return RedirectResponse | HttpException
     */
    public function restore($id)
    {
        self::checkAdmin();

        Tag::withTrashed()
            ->where('id', $id)
            ->restore();

        return redirect()->back();
    }
}
