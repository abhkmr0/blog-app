<?php

namespace App\Http\Controllers;

use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TempImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[

            'image'=>'required|image',

        ]);

        if ($validator->fails()) {
            return response()->json([
             'status' => false,
             'message' => 'Fix errors',
             'errors' => $validator->errors()
            ]);
        }

        //Upload image Hear

        $image = $request->image;
        $ext = $image->getClientOriginalExtension();
        $imageNmae= time().'.' .$ext;
        $tempImage = new TempImage();
        $tempImage->name = $imageNmae;
        $tempImage->save();

        //Move Image

        $image->move(public_path('uploads/temp'),$imageNmae);

        return response()->json([
            'status' => true,
            'message' => 'Image upload successfully',
            'image' => $tempImage,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TempImage $tempImage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TempImage $tempImage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TempImage $tempImage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TempImage $tempImage)
    {
        //
    }
}
