<?php

namespace App\Http\Controllers;

use App\Models\Annotation;
use App\Models\Image;
use Illuminate\Http\Request;

class AnnotationController extends Controller
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
    public function store(Request $request, $id)
    {
        $image = Image::findOrFail($id);
        $this->authorize('update', $image);

        $request->validate([
            'x' => 'required|numeric|min:0',
            'y'=> 'required|numeric|min:0',
            'label' => 'required|string|max:128',
        ]);
        return $image->annotations()->create($request->only(['x','y','label']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Annotation $annotation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Annotation $annotation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Annotation $annotation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Annotation $annotation)
    {
        //
    }
}
