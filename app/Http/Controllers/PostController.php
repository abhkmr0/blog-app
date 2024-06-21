<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PostController extends Controller
{
    public function index()
    {
        // Fetch data from API
        $response = Http::get('https://jsonplaceholder.typicode.com/posts');

        return json_decode($response);
    }

    public function storePost()
    {
        // Fetch data from API
        $response = Http::get('https://jsonplaceholder.typicode.com/posts');

        // Check if the request was successful
        if ($response->successful()) {
            $posts = $response->json();

            // Iterate through each post and save it to the database
            foreach ($posts as $post) {
                // Save each post to the database
                Post::create([
                    'external_id' => $post['id'], 
                    'user_id' => $post['userId'],
                    'title' => $post['title'],
                    'body' => $post['body'],
                ]);
            }

            return response()->json(['message' => 'Posts saved successfully'], 200);
        } else {
            return response()->json(['error' => 'Failed to fetch posts from API'], $response->status());
        }
    }
}
