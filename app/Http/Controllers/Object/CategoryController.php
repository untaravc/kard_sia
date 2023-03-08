<?php

namespace App\Http\Controllers\Object;

use App\Http\Controllers\Controller;
use App\Models\Object\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $dataContent = Category::orderByDesc('id');
        $dataContent = $this->withFilter($dataContent, $request);
        $dataContent = $dataContent->paginate(25);
        return $dataContent;
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        Category::create($request->all());

        return $this->response;
    }

    public function update(Request $request, $id)
    {
        $this->validateData($request);

        Category::find($id)->update($request->all());

        return $this->response;
    }

    public function show($id)
    {
        $this->response['result'] = Category::find($id);

        return $this->response;
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();
        return $this->response;
    }

    public function validateData($request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
    }

    public function withFilter($dataContent, $request)
    {
        if ($request->name != null) {
            $dataContent = $dataContent->where('name', 'LIKE', '%' . $request->name . '%');
        }

        return $dataContent;
    }

    public function list(){
        $categories = ImportController::CAT_LIST;

        $this->response['result'] = $categories;
        return $this->response;
    }

}
