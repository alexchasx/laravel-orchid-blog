<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\ParentController;
use App\Models\Article;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminFileController extends ParentController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Загружает файл
     *
     * @param Request $request
     *
     * @return false|string
     */
    public function store(Request $request)
    {
        self::checkAdmin();

        $path = $request->file('file')
            ->store('upload');

        Article::find($request->id)
            ->files()
            ->save(new File([
                'path' => $path,
            ]));

        //TODO пересмотреть метод
        //        if ($request->hasFile('file')) {
        //            try{
        //                $filename = $request->file;
        //
        //                /**
        //                 * @var ?
        //                 */
        //                $request->file->move('uploads', $filename->getClientOriginalName());
        //            } catch (\Exception $exception) {
        //                echo 'Не удалось загрузить файл!';
        //            }
        //
        //        } else {
        //            return redirect()->back()
        //                ->with('message', 'Ошибка!');
        //        }
        //
        //        Article::find($request->id)
        //            ->first()
        //            ->files()
        //            ->save(new File([
        //                'path' => $request->file,
        //            ]));

        return redirect()->back()
            ->with('message', 'Готово!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Удаляет файл
     *
     * DELETE /admin/file/destroy/{id}
     *
     * @param $articleId
     *
     * @return RedirectResponse|HttpException
     * @internal param $id
     */
    public function destroy($articleId)
    {
        self::checkAdmin();

        $this->getFiles($articleId)->delete();

        return redirect()->back();
    }
}
