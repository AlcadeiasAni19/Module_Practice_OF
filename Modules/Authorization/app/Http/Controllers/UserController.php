<?php

namespace Modules\Authorization\app\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Authorization\app\Models\User;
use Modules\Authorization\app\Transformers\UserTransformer;

class UserController extends Controller
{
    public $successStatus = 200;

    //Login
    public function Login(Request $request){
        $data = $request->all();
        $validator = Validator::make($request->only('email', 'password'), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $errorMessage = response()->json($validator->errors()->all(), 422);
            return response()->json( $errorMessage);
        }
        $user=User::where('email',$data['email'])->first();

        if(Auth::attempt(['email' => $data['email'], 'password' => $data['password']])){
            if($user->role == '1' || $user->role == '2' || $user->role == '3'|| $user->role == '4'|| $user->role == '5') {
                // Token Creating
                $token = $user->createToken('Laravel Password')->plainTextToken;
                $response = [
                    'token' => 'Bearer '.$token
                ];
                return response()->json($response);
            }
        } else {
            return response()->json(['message'=>'Data not found']);
        }
    }

    //Logout
    public function Logout () {
        Auth::logout();
        return response()->json(["status" => $this->successStatus, "message" => "Logged out successfully"]);
    }

    //Create new Non-Company User
    public function createNonCompanyUser(Request $request)
    {
        $input = $request->all();
        $date = date("d-m-Y");
        $validator = Validator::make($request->all(),[
            "name" => 'required|min:3',
            "email" => 'email|unique:users|nullable',
            "role" => 'in:3,4',
            "phone" => "digits:11|unique:users|starts_with:0|nullable",
            "dob" => "before: $date|nullable",
            "gender" => "in:Male, Female, Others|nullable",
            "district" => "string|nullable",
            "sub_district" => "string|required_with:district",
            "password" => 'nullable|min:5'
        ]);

        if ($validator->fails()) {
            $errorMessage = response()->json($validator->errors()->all(), 422);
            return response()->json($errorMessage);
        } else {
            if (isset($input['password'])) {
                $input['password'] = bcrypt($input['password']);
                $userList = User::all();
                //If first, set role to SuperAdmin, otherwise Admin
                if ($userList->isEmpty()) {
                    $role = 1;
                } elseif (auth()->user() == NULL){
                    return response()->json(["message" => "Unauthenticated access"], 401);
                } elseif (auth()->user()->role == 1 || auth()->user()->role == 2) {
                    $role = 2;
                } else {
                    return response()->json(["message" => "Unauthorized access"], 403);
                }
            } else {
                if ($input['role'] == 3 || $input['role'] == 4) {
                    if (isset($input['phone'])) {
                        $role = $input['role'];
                    } else {
                        return response()->json(["message" => "Phone required for this User."]);
                    }
                } else {
                    return response()->json(["message" => 'Restricted role selected.']);
                }
            }
            $input['role'] = $role;
            $input['is_company'] = 2;
            $postUser = User::create($input);
            if ($postUser) {
                return response()->json(["status" => $this->successStatus, "result" => $postUser]);
            } else {
                return response()->json(["message" => 'Data not found']);
            }
        }
    }

    //create new Company User
    public function createCompanyUser (Request $request) {
        $input = $request->all();
        $validator = Validator::make($request->all(),[
            "name" => 'required|min:3',
            "email" => 'email|required|unique:users',
            "password" => 'required|min:5'
        ]);

        if ($validator->fails()) {
            $errorMessage = response()->json($validator->errors()->all(), 422);
            return response()->json($errorMessage);
        } else {
            $input['password'] = bcrypt($input['password']);
            $input['is_company'] = 1;
            $input['has_login_permission'] = 1;
            $postUser = User::create($input);
            if ($postUser) {
                return response()->json(["status" => $this->successStatus, "result" => $postUser]);
            } else {
                return response()->json(["message" => 'Data not found']);
            }
        }
    }

    //List all Farmers
    public function getAllFarmer()
    {
        $farmers = User::where("role", 3)->get();
        foreach ($farmers as $farmer) {
            $farmerList[] = (new UserTransformer)->transformFarmer($farmer);
        }
        return response()->json(["status" => $this->successStatus, 'results' => $farmerList]);
    }

    //List all Traders
    public function getAllTrader()
    {
        $traders = User::where("role", 4)->get();
        foreach ($traders as $trader) {
            $traderList[] = (new UserTransformer)->transformTrader($trader);
        }
        return response()->json(["status" => $this->successStatus, 'results' => $traderList]);
    }

    //List all Companies
    public function getAllCompany()
    {
        $companies = User::where("role", 5)->get();
        foreach ($companies as $company) {
            $companyList[] = (new UserTransformer)->transformCompany($company);
        }
        return response()->json(["status" => $this->successStatus, 'results' => $companyList]);
    }

    //Update SuperAdmin, Admin or Company
    public function updateNonEndUserDetails(Request $request, $id) {
        $getInput = $request->all();
        $validator = Validator::make($request->only('name', 'email'. 'password'), [
            "name" => "min:3",
            "email" => "email|unique:users",
            "password" => "min:5"
        ]);

        if ($validator->fails()) {
            $errorMessage = response()->json($validator->errors()->all(), 422);
            return response()->json($errorMessage);
        } else {
            $userUpdate = User::find($id);
            if ($userUpdate) {
                if (auth()->user()->role == 1 || auth()->user()->id == $userUpdate->id) {
                    $userUpdate->name  = empty($getInput['name'])? $userUpdate->name:$getInput['name'];
                    $userUpdate->email  = empty($getInput['email'])? $userUpdate->email:$getInput['email'];
                    $userUpdate->password  = empty($getInput['password'])? $userUpdate->password:$getInput['password'];
                } else {
                    return response()->json(["message" => "You don't have permission to change this User."], 403);
                }
            } else {
                return response()->json(["message" => "User does not exist."]);
            }

            if($userUpdate->save()){
                return response()->json(["status"=>$this->successStatus,'result'=>$userUpdate]);
            }
            else {
                return response()->json(['message'=>'Data not found']);
            }
        }
    }

    //Update Farmers or Traders
    public function updateEndUserDetails (Request $request, $id) {
        $date = date("d-m-Y");
        $validator = Validator::make($request->all(), [
            "name" => "min:3",
            "phone" => "digits:11|starts_with:0|unique:users",
            "dob" => "before: $date",
            "gender" => "in:Male, Female, Others",
            "district" => "string",
            "sub_district" => "string"
        ]);

        if ($validator->fails()) {
            $errorMessage = response()->json($validator->errors()->all(), 422);
            return response()->json($errorMessage);
        } else {
            $userUpdate = User::find($id);
            if ($userUpdate) {
                $getInput = $request->all();
                if (auth()->user()->role == 1 || auth()->user()->role == 2) {
                    $userUpdate->name  = empty($getInput['name'])? $userUpdate->name:$getInput['name'];
                    $userUpdate->phone  = empty($getInput['phone'])? $userUpdate->phone:$getInput['phone'];
                    $userUpdate->dob  = empty($getInput['dob'])? $userUpdate->dob:$getInput['dob'];
                    $userUpdate->gender  = empty($getInput['gender'])? $userUpdate->gender:$getInput['gender'];
                    $userUpdate->district  = empty($getInput['district'])? $userUpdate->district:$getInput['district'];
                    $userUpdate->sub_district  = empty($getInput['sub_district'])? $userUpdate->sub_district:$getInput['sub_district'];
                } elseif (auth()->user()->id == $id) {
                    $userUpdate->name  = empty($getInput['name'])? $userUpdate->name:$getInput['name'];
                    $userUpdate->dob  = empty($getInput['dob'])? $userUpdate->dob:$getInput['dob'];
                } else {
                    return response()->json(["message" => "You don't have permission to change this User."], 403);
                }
                if ($userUpdate->save()) {
                    return response()->json(["status"=>$this->successStatus,'result'=>$userUpdate]);
                } else {
                    return response()->json(['message'=>'Data not found']);
                }
            } else {
                return response()->json(["message" => "User does not exist."]);
            }
        }
    }

    //Update role
    public function updateUserRole (Request $request, $id) {
        $validator = Validator::make($request->only('role'), [
            "role" => "required|in:1,2"
        ]);

        if ($validator->fails()) {
            $errorMessage = response()->json($validator->errors()->all(), 422);
            return response()->json($errorMessage);
        } else {
            $userRoleUpdate = User::find($id);
            if ($userRoleUpdate) {
                $getInput = $request->all();
                $userRoleUpdate->role = $getInput['role'];
                $userRoleUpdate->save();
                return response()->json(["status"=>$this->successStatus,'result'=>$userRoleUpdate]);
            } else {
                response()->json(["message" => "User does not exist."]);
            }
        }
    }

    //Delete User
    public function deleteUser ($id) {
        $userDelete = User::find($id);
        if ($userDelete) {
            if (auth()->user()->role == 1) {
                $userDelete->delete();
            } elseif (auth()->user()->role == 2 && ($userDelete->role == 3 || $userDelete->role == 4 || auth()->user()->id == $userDelete->id)) {
                $userDelete->delete();
            }
            else return response()->json(["message"=> "Unauthorized access"], 403);
        } else {
            return response()->json(["status"=> 404, "message" => "User does not exist."]);
        }
    }
}
