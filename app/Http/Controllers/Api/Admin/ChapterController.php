<?php

namespace App\Http\Controllers\Api\Admin;

//Facades
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

//Models
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Slot;


class ChapterController extends Controller
{
    
    public function index()
    {
        $chapters = Chapter::all();
        return response()->json(['chapters' => $chapters]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateChapter($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $chapter = Chapter::create($request->all());

        return response()->json([
            'message' => 'Chapter created successfully',
            'chapter' => $chapter
        ], 201);
    }

    public function show($id)
    {
        $chapter = Chapter::findOrFail($id);
        return response()->json(['chapter' => $chapter]);
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validateChapter($request, $id);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $chapter = Chapter::findOrFail($id);
        $chapter->update($request->all());

        return response()->json([
            'message' => 'Chapter updated successfully',
            'chapter' => $chapter
        ]);
    }

    public function destroy($id)
    {
        $chapter = Chapter::findOrFail($id);
        $chapter->delete();

        return response()->json([
            'message' => 'Chapter deleted successfully'
        ]);
    }


    protected function validateChapter(Request $request, $id = null)
    {
        $rules = [
            'course_id' => 'required|exists:courses,id',
            'chapter_title' => 'required|string|max:255',
            'chapter_number' => 'required|integer|min:1',
            'content' => 'required|string',
            'other' => 'nullable|string|max:500',
        ];
        $messages = [
            'course_id.required' => 'The course ID is required.',
            'course_id.exists' => 'The selected course does not exist.',
            'chapter_title.required' => 'The chapter title is required.',
            'chapter_title.string' => 'The chapter title must be a string.',
            'chapter_title.max' => 'The chapter title may not be greater than 255 characters.',
            'chapter_number.required' => 'The chapter number is required.',
            'chapter_number.integer' => 'The chapter number must be an integer.',
            'chapter_number.min' => 'The chapter number must be at least 1.',
            'content.required' => 'The content is required.',
            'content.string' => 'The content must be a string.',
            'other.string' => 'The other field must be a string.',
            'other.max' => 'The other field may not be greater than 500 characters.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }



    /*
     Example JSON object for POST/PUT requests (Postman):
     {
       "course_id": 1,
       "chapter_title": "Introduction to Quran",
       "chapter_number": 1,
       "content": "This chapter covers the basics.",
       "other": "Additional notes or remarks"
     }
     */
}
