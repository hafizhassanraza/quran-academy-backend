<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Student\StudentController;
use App\Http\Controllers\Api\Teacher\TeacherController;
use App\Http\Controllers\Api\Admin\ChapterController;
use App\Http\Controllers\Api\Admin\CourseController;
use App\Http\Controllers\Api\Admin\SlotController;
use App\Http\Controllers\Api\Admin\TimeTableController;
use App\Http\Controllers\Api\Student\EnrollmentController;
use App\Http\Controllers\Api\Teacher\AccountController;






Route::apiResource('students', StudentController::class);
Route::apiResource('teachers', TeacherController::class);
Route::apiResource('chapters', ChapterController::class);
Route::apiResource('courses', CourseController::class);
Route::apiResource('enrollments', EnrollmentController::class);
Route::apiResource('accounts', AccountController::class);
Route::apiResource('slots', SlotController::class);



Route::post('slots/teacherSlots', [SlotController::class, 'todayTeacherSlots']);
Route::post('slots/slotOfTeacher', [SlotController::class, 'slotOfTeacher']);


Route::prefix('timetable')->group(function () {
    Route::get('teacher/{teacherId}', [TimeTableController::class, 'getTimeTableByTeacher']);
    Route::get('slot/{slotNumber}', [TimeTableController::class, 'getTimeTableBySlotNumber']);
    Route::get('student/{studentId}', [TimeTableController::class, 'getTimeTableByStudent']);
});

Route::post('student/login', [StudentController::class, 'studentLogin']);
Route::post('teacher/login', [TeacherController::class, 'teacherLogin']);