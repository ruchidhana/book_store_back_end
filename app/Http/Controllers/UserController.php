<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use DB;
use App\Models\User;
use App\Models\Author;
use Hash;
use Auth;
use Log;
use stdClass;

class UserController extends Controller
{
    /**
     * register a newly created Author
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'email|required|unique:users|max:255',
            'password' => 'required|string|min:8',
            'no_of_published_books'=> 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                "message" => $validator->errors()->first(),
            ]);
        }

        DB::beginTransaction();
        try
        {
            $user = User::create([
                'no_of_published_books' => $request->no_of_published_books,
                'last_name' => $request->last_name,
                'password' => Hash::make($request->password),
                'email' => $request->email,
                'is_active'=>0
            ]);


            if($user)
            {
                Author::create([
                    'first_name' => $request->first_name,
                    'user_id' => $user->id,
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'User successfully registered',
                "error" => false
            ]);
        }
        catch (\Exception $e) {
            DB::rollback();
            Log::critical($e->getMessage());
            return response()->json([
                "error" => true,
                "message" => $e->getMessage(),
            ]);
        }

    }

    public function getProfile()
    {
        $userData = auth()->user();
        //dd($userData);
        if (empty($userData))
            return response()->json([
                'error' => true,
                'message' => 'User cannot be found'
            ]);
        $student_details = User::where('id', $userData->id)->first();
        return response()->json([
            'u_id' => $userData->id,
            'name' => $userData->first_name.' '.$userData->last_name,
            'email' => $userData->email,
            'role_name' => $userData->role->role
        ]);
    }

    public function login(Request $request)
    {
        $userCredentials = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);

        if ($userCredentials->fails()) {
            return response()->json([
                'error' => true,
                "message" => $userCredentials->errors(),
            ]);
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json([
                'error' => true,
                "message" => "Invalid login credentials",
            ]);
        } else {
            if(Auth::user()->is_active==0){
                return response()->json([
                    'error' => true,
                    "message" => "Activation is required by admin",
                ]);
            }
            $client = \DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();

            $data = [
                'grant_type' => 'password',
                'username' => Auth::user()->email,
                'password' => $request->password,
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'scope' => ''
            ];
            $request = Request::create('/oauth/token', 'POST', $data);
            $content = json_decode(app()->handle($request)->getContent());
            $authToken = $content->access_token;
            $refreshToken =  $content->refresh_token;
            return response()->json([
                "error" => false,
                "message" => "User logged in successfully!",
                "authToken" => $authToken,
                "refresh_token" => $refreshToken
            ]);
        }
    }

    public function view_uploaded_books(Request $request)
    {
        $userData = auth()->user();
        //dd($userData);
        if (empty($userData))
            return response()->json([
                'error' => true,
                'message' => 'User cannot be found'
            ]);

        $email = $userData->email;

        $books = DB::table('books')
            ->join('book_authors','books.id','=','book_authors.book_id')
            ->join('authors','book_authors.author_id','=','authors.id')
            ->join('users','users.id','=','authors.user_id')
            ->select('books.*')
            ->where('users.email','=',$email)
            ->get();

        $obj = new stdClass();
        $obj->error = false;
        $obj->result = $books;
        return response()->json($obj);
    }

    public function get_all_users(Request $request)
    {
        $users = DB::table('users')
            ->select('users.id','users.first_name','users.last_name','users.email','users.is_active')
            ->where('users.role_id','=',2)
            ->get();

        $obj = new stdClass();
        $obj->error = false;
        $obj->result = $users;
        return response()->json($obj);
    }

    public function logout(Request $request)
    {

        $authToken = $request->user()->token();

        if (empty($authToken))
            return response()->json([
                'error' => true,
                'message' => 'User already logged out!'
            ]);

        $authToken->revoke();
        return response()->json([
            'error' => false,
            'message' => 'Authenticated user logged out successfully!',
        ]);
    }

    /**
     * Update Verification Status
     */
    public function switch_active_status(Request $request)
    {
        $verifyValidator = Validator::make($request->all(), [
            'is_active' => 'required',
            'id' => 'required',
        ]);

        if ($verifyValidator->fails()) {
            return response()->json([
                'error' => true,
                "message" => $verifyValidator->errors(),
                "status" => false,
            ]);
        }
        $user = User::find($request->id);
        $email = $user->email;

        DB::beginTransaction();
        try {
            $is_active = $request->is_active;

            if ($request->is_active==0) {
                User::where('email',  $email)->update([
                    'is_active' => 1,
                ]);
                DB::commit();
                return response()->json([
                    'error' => false,
                    "message" => 'Your account has been activated. Please, Log In to the system.',
                    "status" => true,
                ]);
            }
            else if($request->is_active==1)
            {
                User::where('email',  $email)->update([
                    'is_active' => 0,
                ]);
                DB::commit();
                return response()->json([
                    'error' => false,
                    "message" => 'Your account has been deactivated. Please, Log In to the system.',
                    "status" => true,
                ]);

            }
            else{
                DB::commit();
                return response()->json([
                    'error' => false,
                    "message" => 'There was an error with activating your account. Please click the link and try again.',
                    "status" => false,
                ]);
            }
            } catch (\Throwable $e) {
                DB::rollback();
                Log::critical($e->getMessage());
                return response()->json([
                    'error' => true,
                    "message" => $e->getMessage(),
                    "status" => false,
                ]);
        }
    }

}
