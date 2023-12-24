<?php

namespace Modules\KnowledgeBank\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Validator;
use Modules\KnowledgeBank\app\Models\Category;
use Modules\KnowledgeBank\app\Models\Content;
use Modules\KnowledgeBank\app\Transformers\ContentTransformer;

class ContentController extends Controller
{
    public $successStatus = 200;

    //Create new content, create a Category also if single content
    public function createContent(Request $request)
    {
        $validator1 = Validator::make($request->all(),[
            "title" => "required|min:3|unique:contents",
            "is_active" => "required|in:0,1",
            "is_nested" => "required|in:0,1",
            "details" => "required",
            "category_id" => ['required_if:is_nested,1',
            Rule::exists('categories', 'id')->where(function (Builder $query) {
                return $query->where("is_nested", 1);
            })],
            "tab_category_id" => "required_if:is_nested,0|exists:tab_categories,id"
        ]);

        if ($validator1->fails()) {
            $errorMessage = response()->json($validator1->errors()->all(), 422);
            return response()->json( $errorMessage);
        } else {
            if ($request->file('image')) {
                $validator2 = Validator::make($request->only("image"), [
                    "image" => "mimes:jpg,jpeg,png|max:2048"
                ]);

                if ($validator2->fails()) {
                    $errorMessage = response()->json($validator2->errors()->all(), 422);
                    return response()->json($errorMessage->original);
                } else {
                    //need to fix a way to delete previous image on update
                    $destination_path1 = 'uploads/images/contents/';
                    $image = $request->file("image");
                    $image->move($destination_path1, $request->file("image")->getClientOriginalName());
                    $image = $request->file("image")->getClientOriginalName();
                }
            } else {$image = NULL;}

            if ($request->file('pdf')) {
                $validator3 = Validator::make($request->only("pdf"), [
                    "pdf" => "mimes:pdf|max:10240"
                ]);

                if ($validator3->fails()) {
                    $errorMessage = response()->json($validator3->errors()->all(), 422);
                    return response()->json($errorMessage->original);
                } else {
                    $destination_path2 = 'uploads/documents/contents/';
                    $pdf = $request->file("pdf");
                    $pdf->move($destination_path2, $request->file("pdf")->getClientOriginalName());
                    $pdf = $request->file("pdf")->getClientOriginalName();
                }
            } else {$pdf = NULL;}

            if ($request->is_nested == 1) {
                //need to fix a way to delete previous image, file on update
                $postContent = new Content;
                $postContent->title = $request->title;
                $postContent->is_nested = 1;
                $postContent->is_active = $request->is_active;
                $postContent->details = $request->details;
                $postContent->category_id = $request->category_id;
                $postContent->image = $image;
                $postContent->pdf = $pdf;
            } else {
                $newCategory = new Category;
                $newCategory->name = $request->title;
                $newCategory->is_nested = 0;
                $newCategory->is_active = 1;
                $newCategory->tab_category_id = $request->tab_category_id;
                $newCategory->save();

                $postContent = new Content;
                $postContent->title = $request->title;
                $postContent->is_nested = 0;
                $postContent->is_active = $request->is_active;
                $postContent->details = $request->details;
                $postContent->category_id = $newCategory->id;
                $postContent->image = $image;
                $postContent->pdf = $pdf;
            }

            $postContent->save();
            if ($postContent) {
                return response()->json(["status" => $this->successStatus, "result" => $postContent]);
            } else {
                return response()->json(["message" => 'Data not found']);
            }
        }
    }

    //List all Content, regardless of is_active status
    public function getAllContent()
    {
        $contents = Content::all();
        foreach ($contents as $content) {
            $contentList[] = (new ContentTransformer)->transformAdminContentList($content);
        }
        return response()->json(["status" => $this->successStatus, 'results' => $contentList]);
    }

    //List all active Contents
    public function getAllActiveContent() {
        $contents = Content::where("is_active", true)->get();
        foreach ($contents as $content) {
            $contentList[] = (new ContentTransformer)->transformEndUserContentList($content);
        }
        return response()->json(["status" => $this->successStatus, "results" => $contentList]);
    }

    //Get single Content
    public function getSingleContent($id) {
        $content = Content::find($id);
        $contentInfo = (new ContentTransformer)->transformEndUserContentDetails($content);
        if ($contentInfo) {
            return response()->json(["status" => $this->successStatus, "results" => $contentInfo]);
        } else {
            return response()->json(["message" => "Content does not exist."]);
        }
    }

    //Update single Content
    public function updateContent (Request $request, $id) {
        $getInput = $request->all();
        $validator1 = Validator::make($request->all(), [
            "title" => "min:3| unique:contents",
            "is_active" => "in:0,1",
            "category_id" => [Rule::exists('categories', 'id')->where(function (Builder $query) {
                return $query->where("is_nested", 1);
            })],
            "tab_category_id" => [Rule::exists('tab_categories', 'id')->where(function (Builder $query) {
                return $query->where("is_active", 1);
            })]
        ]);
        if ($validator1->fails()) {
            $errorMessage = response()->json($validator1->errors()->all(), 422);
            return response()->json( $errorMessage);
        } else {
            $contentUpdate = Content::find($id);
            if ($contentUpdate) {
                $contentUpdate->title = empty($getInput['title'])? $contentUpdate->title:$getInput['title'];
                $contentUpdate->details = empty($getInput['details'])? $contentUpdate->details:$getInput['details'];
                $contentUpdate->is_active = isset($getInput['is_active'])? intval($getInput['is_active']):$contentUpdate->is_active;
                if ($request->file('image')) {
                    $validator2 = Validator::make($request->only("image"), [
                        "image" => "mimes:jpg,jpeg,png|max:2048"
                    ]);

                    if ($validator2->fails()) {
                        $errorMessage = response()->json($validator2->errors()->all(), 422);
                        return response()->json($errorMessage->original);
                    } else {
                        //Image processing
                        $image_path = 'uploads/images/contents/'.$contentUpdate->image;
                        //If an image previously linked, delete that image
                        if (file_exists($image_path)) {
                            @unlink($image_path);
                        }
                        $destination_path1 = 'uploads/images/contents/';
                        $image = $request->file("image");
                        $image->move($destination_path1, $request->file("image")->getClientOriginalName());
                        $image = $request->file("image")->getClientOriginalName();
                    }
                } else {$image = $contentUpdate->image;}

                if ($request->file('pdf')) {
                    $validator3 = Validator::make($request->only("pdf"), [
                        "pdf" => "mimes:pdf|max:10240"
                    ]);

                    if ($validator3->fails()) {
                        $errorMessage = response()->json($validator3->errors()->all(), 422);
                        return response()->json($errorMessage->original);
                    } else {
                        //Pdf processing
                        $pdf_path = 'uploads/documents/contents/'.$contentUpdate->pdf;
                        //If a pdf previously linked, delete that pdf
                        if (file_exists($pdf_path)) {
                            @unlink($pdf_path);
                        }
                        $destination_path2 = 'uploads/documents/contents/';
                        $pdf = $request->file("pdf");
                        $pdf->move($destination_path2, $request->file("pdf")->getClientOriginalName());
                        $pdf = $request->file("pdf")->getClientOriginalName();
                    }
                } else {$pdf = $contentUpdate->pdf;}

                if ($contentUpdate->is_nested == 1) {
                    $contentUpdate->image = $image;
                    $contentUpdate->pdf = $pdf;
                    $contentUpdate->category_id = empty($getInput['category_id'])? $contentUpdate->category_id:$getInput['category_id'];

                } else {
                    if (isset($getInput["title"])) {
                        $categoryUpdate = $contentUpdate->category;
                        $categoryUpdate->name = $getInput["title"];
                        $categoryUpdate->tab_category_id = empty($getInput['tab_category_id'])? $categoryUpdate->tab_category_id:$getInput['tab_category_id'];
                        $categoryUpdate->save();
                        $contentUpdate->image = $image;
                        $contentUpdate->pdf = $pdf;

                    } else {
                        $contentUpdate->image = $image;
                        $contentUpdate->pdf = $pdf;
                        $contentUpdate->category()->tab_category_id = empty($getInput['tab_category_id'])? $contentUpdate->category()->tab_category_id:$getInput['tab_category_id'];
                    }
                }

                    $contentUpdate->save();
                    return response()->json(["status" => $this->successStatus, "result" => $contentUpdate]);
            } else {
                return response()->json(["message" => "Content does not exist."]);
            }
        }
    }

    //Delete Content
    public function deleteContent ($id) {
        $contentDelete = Content::find($id);
        if ($contentDelete) {
            if (auth()->user()->role == 1 || auth()->user()->role == 2) {
                $contentDelete->delete();
            } else return response()->json(["message"=> "Unauthorized access"], 403);
        } else {
            return response()->json(["status"=> 404, "message" => "Content does not exist."]);
        }
    }
}
