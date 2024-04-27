<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Post::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'photos' => 'array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $post = Post::create([
            'title' => $validatedData['title'],
            'body' => $validatedData['body'],
        ]);

        if (isset($validatedData['photos']) && is_array($validatedData['photos'])) {
            foreach ($validatedData['photos'] as $photo) {
                $photoPath = $photo->store('photos');
                $post->photos()->create(['path' => $photoPath]);
            }
        }

        return response()->json($post);
    }

    public function storePost(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'photos' => 'array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $post = Post::create([
            'title' => $validatedData['title'],
            'body' => $validatedData['body'],
        ]);

        $postId = $post->id;

        if ($request->hasFile('photos')) {
            $photos = $this->getImage($request->photos, $postId);
            $post->photos()->insert($photos);
        }

        return response()->json($post);
    }



    public function getImage($data, $postId)
    {
        $photos = [];

        foreach ($data as $photo) {
            $photoPath = $photo->store('post_photos');

            $photos[] = [
                'path' => $photoPath,
                'post_id' => $postId,
            ];
        }

        return $photos;
    }
}
