<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use App\Models\Like;
use App\Models\TempImage;
use App\Events\CommentAdded;
use App\Events\BlogLiked;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     // Get the authenticated user
    //     $user = auth()->user();

    //     // Retrieve the blogs created by the authenticated user, ordered by creation date
    //     $blogs = Blog::where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();

    //     // Return the blogs in a JSON response
    //     return response()->json([
    //         'success' => true,
    //         'blogs' => $blogs
    //     ]);
    // }

    public function index()
    {
        // Retrieve all blogs ordered by creation date
        $blogs = Blog::orderBy('created_at', 'DESC')->get();

        // Return the blogs in a JSON response
        return response()->json([
            'success' => true,
            'blogs' => $blogs
        ]);
    }




    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:10',
            'author' => 'required|min:3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'fix errors',
                'errors' => $validator->errors()
            ]);
        }

        // Get the authenticated user
        $user = auth()->user();

        // Create a new blog entry
        $blogs = new Blog();
        $blogs->title = $request->title;
        $blogs->short_desc = $request->short_desc;
        $blogs->description = $request->description;
        $blogs->author = $request->author;
        $blogs->user_id = $user->id;
        $blogs->save();

        // Save image
        $tempImage = TempImage::where('name', $request->image_id)->first();

        if ($tempImage != null) {
            $imgExtArray = explode('.', $tempImage->name);
            $ext = last($imgExtArray);
            $imageName = time() . '_' . $blogs->id . '.' . $ext;
            $blogs->image = $imageName;
            $blogs->save();

            $sourcePath = public_path('uploads/temp/' . $tempImage->name);
            $destPath = public_path('uploads/blogs/' . $imageName);

            // Check if sourcePath is a file
            if (File::isFile($sourcePath)) {
                File::copy($sourcePath, $destPath);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Source image file not found'
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Data stored successfully',
            'blogs' => $blogs
        ]);
    }


    public function singleBlog($id)
    {
        $blog = Blog::where('id', $id)->first();

        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Blog not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'blog' => $blog
        ]);
    }


    public function update(Request $request, $id)
    {

        $blogs = Blog::find($id);

        if ($blogs === null) {
            return response()->json([
                'success' => false,
                'message' => 'Blog not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:10',
            'author' => 'required|min:3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'fix errors',
                'errors' => $validator->errors()
            ]);
        }

        $blogs->title = $request->title;
        $blogs->short_desc = $request->short_desc;
        $blogs->description = $request->description;
        $blogs->author = $request->author;
        $blogs->save();

        // Save image
        $tempImage = TempImage::where('name', $request->image_id)->first();

        if ($tempImage != null) {
            $imgExtArray = explode('.', $tempImage->name);
            $ext = last($imgExtArray);
            $imageName = time() . '_' . $blogs->id . '.' . $ext;
            $blogs->image = $imageName;
            $blogs->save();

            $sourcePath = public_path('uploads/temp/' . $tempImage->name);
            $destPath = public_path('uploads/blogs/' . $imageName);

            // Check if sourcePath is a file
            if (File::isFile($sourcePath)) {
                File::copy($sourcePath, $destPath);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Source image file not found'
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Update successfully',
            'blogs' => $blogs
        ]);
    }

    public function searchBlog(Request $request)
    {
        $query = Blog::query();

        if (!empty($request->title)) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Modify the query to prioritize a specific blog post
        $query->orderByRaw("CASE WHEN title = 'Featured Blog Post' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'DESC');

        $blogs = $query->get();

        return response()->json([
            'success' => true,
            'blogs' => $blogs
        ]);
    }



    public function destroy($id)
    {
        $blogs = Blog::find($id);

        if (!$blogs) {
            return response()->json(['status' => false, 'message' => 'Blog not found']);
        }
        // Delete Blog image first

        File::delete(public_path('uploads/blogs/' . $blogs->image));

        $blogs = $blogs->delete();

        //delete Blog from database

        return response()->json([
            'status' => true,
            'message' => 'Blog deleted successfully'
        ]);
    }

    // public function like(Request $request, $id)
    // {
    //     $blog = Blog::findOrFail($id);

    //     // Check if the user has already liked the blog
    //     $existingLike = Like::where('user_id', auth()->id())->where('blog_id', $blog->id)->first();
    //     if ($existingLike) {
    //         return response()->json(['message' => 'You have already liked this blog'], 422);
    //     }

    //     // Create a new like
    //     $like = new Like();
    //     $like->user_id = auth()->id();
    //     $like->blog_id = $blog->id;
    //     $like->save();

    //     // Increment the likes count in the blog table
    //     $blog->increment('likes');

    //     return response()->json(['message' => 'Blog liked successfully']);
    // }

    public function like(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        // Check if the user has already liked the blog
        $existingLike = Like::where('user_id', auth()->id())->where('blog_id', $blog->id)->first();
        if ($existingLike) {
            return response()->json(['message' => 'You have already liked this blog'], 422);
        }

        // Create a new like
        $like = new Like();
        $like->user_id = auth()->id();
        $like->blog_id = $blog->id;
        $like->save();

        // Increment the likes count in the blog table
        $blog->increment('likes');

        // Broadcast the BlogLiked event
        broadcast(new BlogLiked($blog));

        return response()->json(['message' => 'Blog liked successfully']);
    }

    // public function comment(Request $request)
    // {
    //     $blog = Blog::find($request->input('blog_id'));

    //     if (!$blog) {
    //         return response()->json(['error' => 'Blog not found'], 404);
    //     }

    //     $comment = new Comment();
    //     $comment->user_id = auth()->id();
    //     $comment->blog_id = $blog->id;
    //     $comment->content = $request->input('content');
    //     $comment->save();

    //     // Debugging statement
    //     \Log::info('Incrementing count...');

    //     // Update comment count in the database
    //     $blog->increment('comment_count');

    //     return response()->json(['message' => 'Comment added successfully']);
    // }


    public function comment(Request $request)
    {
        $blog = Blog::find($request->input('blog_id'));

        if (!$blog) {
            return response()->json(['error' => 'Blog not found'], 404);
        }

        $comment = new Comment();
        $comment->user_id = auth()->id();
        $comment->blog_id = $blog->id;
        $comment->content = $request->input('content');
        $comment->save();

        // Debugging statement
        \Log::info('Incrementing count...');

        // Update comment count in the database
        $blog->increment('comment_count');

        // Broadcast the CommentAdded event
        broadcast(new CommentAdded($comment));

        return response()->json(['message' => 'Comment added successfully']);
    }



    public function showComments($id)
    {
        $comments = Comment::where('blog_id', $id)
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('blogs', 'comments.blog_id', '=', 'blogs.id')
            ->select('comments.id','comments.content','comments.created_at', 'users.name as user_name', 'blogs.title as blog_title','blogs.comment_count as blog_count')
            ->orderBy('comments.id', 'DESC')
            ->limit(50)
            ->get();
            return response()->json([
                'comments' => $comments
            ]);
    }



    public function commentCount()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Get the user's blogs along with comment counts user-wise
        $blogs = Blog::forUserWithCommentsCount($user->id)->get();

        // Return JSON response with blogs and comment counts user-wise
        return response()->json($blogs);
    }

}
