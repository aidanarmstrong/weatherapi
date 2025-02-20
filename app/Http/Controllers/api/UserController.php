<?php
namespace App\Http\Controllers\api;

use App\Jobs\SendWelcomeEmail;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


/**
*  @OA\Schema(
*     schema="Users",
*     title="Users",
*     description="User model",
*     @OA\Property(property="id", type="integer", example=1),
*     @OA\Property(property="name", type="string", example="Sample Name"),
*     @OA\Property(property="email", type="string", example="example@example.com."),
*     @OA\Property(property="password", type="string", example="#############"),
*     @OA\Property(property="remember_token", type="string", example="#############"),
*     @OA\Property(property="created_at", type="string", format="date-time"),
*     @OA\Property(property="updated_at", type="string", format="date-time")
* )
* @OA\SecurityScheme(
*      securityScheme="userBearerAuth",
*      type="http",
*      scheme="bearer"
* )
*/
class UserController extends Controller {

    /**
    * @OA\Get(
    *     path="/api/users",
    *     summary="Get list of users",
    *     security={{"bearerAuth":{}}},
    *     tags={"Users"},
    *     @OA\Response(
    *         response=200,
    *         description="Returns a paginated list of users"
    *     )
    * )
    */
    public function index() {
        $users = User::paginate(10);
        return response()->json($users, 200);
    }

    /**
    * @OA\Get(
    *     path="/api/users/{id}",
    *     summary="Get a user by ID",
    *     security={{"bearerAuth":{}}},
    *     tags={"Users"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         description="ID of the user",
    *         @OA\Schema(type="integer")
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="User details"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="User not found"
    *     )
    * )
    */
    public function show($id) {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user, 200);
    }


    /**
    * @OA\Post(
    *     path="/api/register",
    *     summary="Register a new user",
    *     tags={"Auth"},
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             @OA\Property(property="name", type="string", example="John Doe"),
    *             @OA\Property(property="email", type="string", example="john@example.com"),
    *             @OA\Property(property="password", type="string", example="password123"),
    *             @OA\Property(property="password_confirmation", type="string", example="password123")
    *         )
    *     ),
    *     @OA\Response(
    *         response=201,
    *         description="User registered successfully"
    *     ),
    *     @OA\Response(
    *         response=422,
    *         description="Validation error"
    *     )
    * )
    */
    public function register(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Handle user creation
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            
            // SendWelcomeEmail::dispatch($user);

            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred during registration. Please try again.'], 500);
        }
    }

    /**
    * @OA\Post(
    *     path="/api/login",
    *     summary="Login a user and get a token",
    *     tags={"Auth"},
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             @OA\Property(property="email", type="string", example="john@example.com"),
    *             @OA\Property(property="password", type="string", example="password123")
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Login successful, returns token",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="token", type="string")
    *         )
    *     ),
    *     @OA\Response(
    *         response=401,
    *         description="Unauthorized - Invalid credentials"
    *     )
    * )
    */
    public function login(Request $request) {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($validated)) {
            /** @var \App\Models\User $user **/
            $user = Auth::user();
            return response()->json([
                'message' => 'Login successful',
                'token' => $user->createToken('JuiceBox')->plainTextToken
            ], 200);
        }

        return response()->json(['error' => 'Unauthorized. Invalid credentials.'], 401);
    }

    /**
    * @OA\Post(
    *     path="/api/logout",
    *     summary="Logout the authenticated user",
    *     security={{"bearerAuth":{}}},
    *     tags={"Auth"},
    *     @OA\Response(
    *         response=200,
    *         description="Logged out successfully"
    *     ),
    *     @OA\Response(
    *         response=401,
    *         description="Unauthorized"
    *     )
    * )
    */
    public function logout() {
        if (Auth::check()) {
            Auth::user()->tokens->each(function ($token) {
                $token->delete();
            });

            return response()->json(['message' => 'Logged out successfully'], 200);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
