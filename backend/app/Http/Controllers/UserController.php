<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterPostRequest;
use App\Http\Requests\LoginPostRequest;
use App\Http\Requests\CreateUserPostRequest;
use App\Http\Requests\EditUserPostRequest;

use App\Imports\UserImport;

//import User class
use App\Models\User;
//import Auth
use Auth;
//import validator
use Validator;
//import hash for hashing password
use Illuminate\Support\Facades\Hash;

use Excel;

//import Resource/transformer
use App\Http\Resources\UserResource;


class UserController extends Controller
{
    //create user variable
    protected $user;

    public function __construct()
    {
        //only login and register are the functions that can be access without middleware
        $this->middleware("auth:api", ["except" => ["login", "register"]]);
        $this->user = new User;
    }

    //Register Function
    public function register(RegisterPostRequest $request)
    {
        // $validator = Validator::make($request->all(),[
        //     'name' => 'required|string',
        //     'email' => 'required|string|unique:users',
        //     'password' => 'required|min:6|confirmed',
        // ]);

        // if($validator->fails())
        // {
        //     return response()->json([
        //         'success' => false,
        //         'message' => $validator->messages()->toArray()
        //     ], 500);
        // }

        $data = [
            "name" => $request -> name,
            "email" => $request -> email,
            "password" => Hash::make($request->password)
        ];

        $this->user->create($data);

        $responseMessage = "Registration Successful";

        return response()->json([
            'success' => true,
            'message' => $responseMessage
        ],200);
    }


    //Login Function
    public function login(LoginPostRequest $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'email' => 'required|string',
        //     'password' => 'required|min:6',
        // ]);

        // if($validator->fails())
        // {
        //     return response()->json([
        //         'success' => false,
        //         'message' => $validator->messages()->toArray()
        //     ],500);
        // }

        $credentials = $request->only(["email", "password"]);
        $user = User::where('email', $credentials['email'])->first();

        if($user)
        {
            if(!auth()->attempt($credentials))
            {
                $responseMessage = "Ivalid username or password";

                return response()->json([
                    "success" => false,
                    "message" => $responseMessage,
                    "error" => $responseMessage
                ], 422);
            }

            $accessToken = auth()->user()->createToken('authToken')->accessToken;
            $responseMessage = "Login Successful";

            return $this->respondWithToken($accessToken, $responseMessage, auth()->user());

        }
        else{
            $responseMessage = "Sorry, this user does not exist";

            return response()->json([
                "success" => false,
                "message" => $responseMessage,
                "error" => $responseMessage
            ], 422);
        }
    }


    //View Profile Function
    public function viewProfile()
    {
        $responseMessage = "user profile";
        $data = Auth::guard("api")->user();

        return response()->json([
            'success'=> true,
            'message' => $responseMessage,
            'data' => $data
        ], 200);
    }


    //Logout Function
    public function logout()
    {
        $user = Auth::guard("api")->user()->token();
        $user->revoke();
        $responseMessage = "successfully logged out";

        return response()->json([
            'success' => true,
            'message' => $responseMessage
        ], 200);
    }

    //Create Function
    public function createUser(CreateUserPostRequest $request)
    {
        $user = new User();

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->gender = $request->gender;
        $user->department = $request->department;
        $user->state = $request->state;

        $user->save();

        $responseMessage = "New User Successfully Created";

        return response()->json([
            'success'=> true,
            'message'=> $responseMessage,
            'data'=> $user,
        ],200);
    }


    //Update Function
    public function updateUser(EditUserPostRequest $request, $id)
    {
        $user = User::find($id);
        //$user ->update($request->all());

        $user->name = $request->name;
        $user->email = $request->email;
        $user->gender = $request->gender;
        $user->department = $request->department;
        $user->state = $request->state;

        $result = $user->update();


        
        $responseMessage = "Updated User Successfully";

        return response()->json([
            'success' => true,
            'message' => $responseMessage,
            'result' => $user,

        ],200);
    }


    //Read Function with filter and pagination
    public function listUser(Request $request)
    {
        $user = Auth::guard("api")->user();

        $gender = $request->gender;

        if($gender)
        {
            //filter by gender
            $user = User::where('gender', $gender)
            //paginate data by showing only 5 row
            ->paginate(5);
            //->appends('gender', $gender);
        }
        else{
            $user = User::paginate(5);
        }
        
        //$user = User::where('gender', 'LIKE', '%'.$keyword.'%')->paginate(3);


        //$responseMessage = "User List";

        // return response()->json([
        //     'success' => true,
        //     'message' => $responseMessage,
        //     'data' => $user,
        // ],200);

        //return to User Resource 
        return UserResource::collection($user);
    }


    //Import Function
    public function import(Request $request)
    {
        $user = Auth::guard("api")->user();

        $path = $request->file('user');

        $user = \Excel::import(new UserImport,$path);

        return "records are imported";
    }
}