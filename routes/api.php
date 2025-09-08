<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Student\StudentController;
use App\Http\Controllers\Api\Teacher\TeacherController;
use App\Http\Controllers\Api\Admin\ChapterController;
use App\Http\Controllers\Api\Admin\CourseController;
use App\Http\Controllers\Api\Admin\SlotController;
use App\Http\Controllers\Api\Student\EnrollmentController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::apiResource('students', StudentController::class);
Route::apiResource('teachers', TeacherController::class);
Route::apiResource('chapters', ChapterController::class);
Route::apiResource('courses', CourseController::class);
Route::apiResource('enrollments', EnrollmentController::class);
Route::apiResource('slots', SlotController::class);




