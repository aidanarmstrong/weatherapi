<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
* @OA\Schema(
*     schema="Post",
*     title="Post",
*     description="Post model",
*     @OA\Property(property="id", type="integer", example=1),
*     @OA\Property(property="title", type="string", example="Sample Post"),
*     @OA\Property(property="content", type="string", example="This is a post content."),
*     @OA\Property(property="user_id", type="integer", example=1),
*     @OA\Property(property="created_at", type="string", format="date-time"),
*     @OA\Property(property="updated_at", type="string", format="date-time")
* )
* @OA\SecurityScheme(
*      securityScheme="PostBearerAuth",
*      type="http",
*      scheme="bearer"
* )
*/
class PostController extends Controller
{
    use AuthorizesRequests;

    /**
    * @OA\Get(
    *     path="/api/posts",
    *     summary="Get a list of posts",
    *     tags={"Post"},
    *     @OA\Response(
    *         response=200,
    *         description="A list of posts",
    *         @OA\JsonContent(
    *             @OA\Property(property="current_page", type="integer"),
    *             @OA\Property(property="data", type="array", 
    *                 @OA\Items(ref="#/components/schemas/Post")
    *             ),
    *             @OA\Property(property="total", type="integer"),
    *             @OA\Property(property="per_page", type="integer")
    *         )
    *     )
    * )
    */
    public function index() {
        $posts = Post::paginate(10);
        return response()->json($posts, 200);
    }

    /**
    * @OA\Get(
    *     path="/api/posts/{id}",
    *     summary="Get a specific post",
    *     tags={"Post"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="The ID of the post",
    *         required=true,
    *         @OA\Schema(type="integer")
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Post details",
    *         @OA\JsonContent(ref="#/components/schemas/Post")
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Post not found",
    *         @OA\JsonContent(
    *             @OA\Property(property="error", type="string")
    *         )
    *     )
    * )
    */
    public function show($id) {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        return response()->json($post, 200);
    }

    /**
    * @OA\Post(
    *     path="/api/posts",
    *     summary="Create a new post",
    *     security={{"bearerAuth":{}}},
    *     tags={"Post"},
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             @OA\Property(property="title", type="string", example="My first post"),
    *             @OA\Property(property="content", type="string", example="This is the content of the post")
    *         )
    *     ),
    *     @OA\Response(
    *         response=201,
    *         description="Post created successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="post", ref="#/components/schemas/Post")
    *         )
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="An error occurred while creating the post",
    *         @OA\JsonContent(
    *             @OA\Property(property="error", type="string")
    *         )
    *     )
    * )
    */
    public function store(Request $request) {
        
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            
            $post = Post::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'user_id' => Auth::user()->id,
            ]);

            return response()->json([
                'message' => 'Post created successfully',
                'post' => $post
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while creating the post.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

   /**
    * @OA\Patch(
    *     path="/api/posts/{id}",
    *     summary="Update a post",
    *     security={{"bearerAuth":{}}},
    *     tags={"Post"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="The ID of the post to update",
    *         required=true,
    *         @OA\Schema(type="integer")
    *     ),
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             @OA\Property(property="title", type="string", example="Updated post title"),
    *             @OA\Property(property="content", type="string", example="Updated post content")
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Post updated successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="post", ref="#/components/schemas/Post")
    *         )
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Post not found",
    *         @OA\JsonContent(
    *             @OA\Property(property="error", type="string")
    *         )
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Internal server error",
    *         @OA\JsonContent(
    *             @OA\Property(property="error", type="string"),
    *             @OA\Property(property="message", type="string")
    *         )
    *     )
    * )
    */
    public function update(Request $request, $id) {

        try {
            $post = Post::find($id);

            if (!$post) {
                return response()->json(['error' => 'Post not found'], 404);
            }

            $this->authorize('update', $post);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            $post->update($validated);

            return response()->json([
                'message' => 'Post updated successfully',
                'post' => $post
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while updating the post.',
                'message' => $e->getMessage(),
            ], 500);
        }

    }


   /**
    * @OA\Delete(
    *     path="/api/posts/{id}",
    *     summary="Delete a post",
    *     security={{"bearerAuth":{}}},
    *     tags={"Post"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="The ID of the post to delete",
    *         required=true,
    *         @OA\Schema(type="integer")
    *     ),
    *     @OA\Response(
    *         response=204,
    *         description="Post deleted successfully"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Post not found",
    *         @OA\JsonContent(
    *             @OA\Property(property="error", type="string")
    *         )
    *     )
    * )
    */
    public function destroy($id) {
        try {
            $post = Post::find($id);

            if (!$post) {
                return response()->json(['error' => 'Post not found'], 404);
            }

            $this->authorize('delete', $post);

            $post->delete();

            return response()->json(['message' => 'Post deleted successfully'], 204);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting the post.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
