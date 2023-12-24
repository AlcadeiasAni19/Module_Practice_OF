<?php

namespace Modules\KnowledgeBank\app\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\KnowledgeBank\app\Models\TabCategory;

class TabCategoryController extends Controller
{
    public $successStatus = 200;

    //Create new TabCategory
    public function createTabCategory(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($request->all(),[
            "name" => "required|min:3|unique:tab_categories",
            "is_active" => "required|in:0,1"
        ]);

        if ($validator->fails()) {
            $errorMessage = response()->json($validator->errors()->all(), 422);
            return response()->json( $errorMessage);
        } else {
            $postTabCategory = TabCategory::create($input);
            if ($postTabCategory) {
                return response()->json(["status" => $this->successStatus, "result" => $postTabCategory]);
            } else {
                return response()->json(["message" => 'Data not found']);
            }
        }
    }

    //List all TabCategory
    public function getAllTabCategory()
    {
        $tabCategoryList = TabCategory::all();
        return response()->json(["status" => $this->successStatus, 'results' => $tabCategoryList]);
    }

    //Update single TabCategory
    public function updateTabCategory (Request $request, $id){
        $getInput = $request->all();
        $validator = Validator::make($request->only("name", "is_set"), [
            "name" => "min:3| unique:tab_categories",
            "is_active" => "in:0,1"
        ]);

        if ($validator->fails()) {
            $errorMessage = response()->json($validator->errors()->all(), 422);
            return response()->json( $errorMessage);
        }

        $tabCategoryUpdate = TabCategory::find($id);

        if(isset($getInput['name'])) {
            $tabCategoryUpdate->name = $getInput['name'];
        }

       if(isset($getInput['is_active'])){
        $tabCategoryUpdate->is_active = $getInput['is_active'];
        }
        $tabCategoryUpdate->save();
        return response()->json(["status"=>$this->successStatus,'result'=>$tabCategoryUpdate]);
    }

    //Delete TabCategory
    public function deleteTabCategory ($id) {
        $tabCategoryDelete = TabCategory::find($id);
        if ($tabCategoryDelete) {
            if (auth()->user()->role == 1 || auth()->user()->role == 2) {
                $tabCategoryDelete->delete();
            } else return response()->json(["message"=> "Unauthorized access"], 403);
        } else {
            return response()->json(["status"=> 404, "message" => "TabCategory does not exist."]);
        }
    }
}
