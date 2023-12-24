<?php

namespace Modules\KnowledgeBank\app\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\KnowledgeBank\app\Models\Category;

class CategoryController extends Controller
{
    public $successStatus = 200;

    //Create new Category
    public function createCategory(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($request->all(),[
            "name" => "required|min:3|unique:categories",
            "tab_category_id" => "required|exists:tab_categories,id",
        ]);

        if ($validator->fails()) {
            $errorMessage = response()->json($validator->errors()->all(), 422);
            return response()->json( $errorMessage);
        } else {
            $postCategory = Category::create($input);
            if ($postCategory) {
                return response()->json(["status" => $this->successStatus, "result" => $postCategory]);
            } else {
                return response()->json(["message" => 'Data not found']);
            }
        }
    }

    //List all Category
    public function getAllCategory()
    {
        $categoryList = Category::all();
        return response()->json(["status" => $this->successStatus, 'results' => $categoryList]);
    }

    //Update single Category
    public function updateCategory (Request $request, $id) {
        $getInput = $request->all();
        $validator = Validator::make($request->only("name", "is_active", "tab_category_id"), [
            "name" => "min:3| unique:categories",
            "is_active" => "in:0,1",
            "tab_category_id" => "exists:tab_categories,id"
        ]);

        if ($validator->fails()) {
            $errorMessage = response()->json($validator->errors()->all(), 422);
            return response()->json( $errorMessage);
        } else {
            $categoryUpdate = Category::find($id);

            if(isset($getInput['name'])) {
                $categoryUpdate->name = $getInput['name'];
            }

            if(isset($getInput['is_active'])) {
                $categoryUpdate->is_active = $getInput['is_active'];
            }

            if(isset($getInput['tab_category_id'])) {
                $categoryUpdate->tab_category_id = $getInput['tab_category_id'];
            }

            $categoryUpdate->save();
            return response()->json(["status"=>$this->successStatus,'result'=>$categoryUpdate]);
        }
    }

    //Delete Category
    public function deleteCategory ($id) {
        $categoryDelete = Category::find($id);
        if ($categoryDelete) {
            if (auth()->user()->role == 1 || auth()->user()->role == 2) {
                $categoryDelete->delete();
            } else return response()->json(["message"=> "Unauthorized access"], 403);
        } else {
            return response()->json(["status"=> 404, "message" => "Category does not exist."]);
        }
    }
}
