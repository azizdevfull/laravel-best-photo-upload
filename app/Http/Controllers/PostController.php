<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function index()
    {
        return response()->json(PostResource::collection(Post::with('photos')->get()));
    }
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

    public function storePost(StorePostRequest $request)
    {
        $post = Post::create($request->validated());

        if ($request->hasFile('photos')) {
            $photos = $this->getPhotos($request->photos, $post->id);
        }
        // return response()->json($photos);
        $post->photos()->insert($photos); // optimal yo'l
        return response()->json($post);
    }
    public function getPhotos($data, $postId)
    {
        foreach ($data as $photo) {
            $photoPath = $photo->store('post_photos', 'public');

            $photos[] = [
                'path' => $photoPath,
                'post_id' => $postId,
            ];
        }

        return $photos;
    }
}
