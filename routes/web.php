<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TrainingModuleController;
use App\Http\Controllers\UserTrainingController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\UserExamController;
use App\Http\Controllers\MasterDocumentController;
use App\Http\Controllers\MasterQuestionController;
use App\Http\Controllers\ModuleLinkController;
use Illuminate\Support\Facades\Artisan;



Route::get('/system-update', function () {
    try {
        // 1. Run database migrations
        // The --force flag is required for production environments
        Artisan::call('migrate', ['--force' => true]);
        $migrationOutput = Artisan::output();

        // 2. Clear all caches (Application, Route, Config, View)
        Artisan::call('optimize:clear');
        $cacheOutput = Artisan::output();

        // 3. Optional: Re-cache for performance (only for production)
        // Artisan::call('optimize');

        return back()->with('success', 'System Updated Successfully! <br><strong>Migrations:</strong> ' . $migrationOutput . '<br><strong>Cache:</strong> ' . $cacheOutput);
        
    } catch (\Exception $e) {
        return back()->with('error', 'Update Failed: ' . $e->getMessage());
    }
})->name('system.update');


Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->name('login.post');
Route::any('logout', [LoginController::class, 'logout'])->name('logout');


Route::group(['middleware' => ['auth']], function () {

Route::get('/', function () {
    return view('home');
})->name('dashboard');

Route::get('/dashboard', function () {
    return view('home');
})->name('dashboard');

Route::resource('users', UserController::class);
Route::resource('roles', RoleController::class);
Route::resource('permissions', PermissionController::class);
Route::resource('trainings', TrainingModuleController::class);


Route::get('trainee-progress', [UserTrainingController::class, 'index'])->name('user.training.index');

Route::get('trainee-progress/{user}/{training}', [UserTrainingController::class, 'show'])->name('user.training.show');
Route::post('trainee-progress/{user}/{training}', [UserTrainingController::class, 'store'])->name('user.training.store');

// Example: /report/user/5/training/1
Route::get('report/user/{user}/training/{training_id}', [UserTrainingController::class, 'report'])->name('user.training.report');

Route::get('masters', [MasterController::class, 'index'])->name('masters.index');
Route::post('masters/dept', [MasterController::class, 'storeDepartment'])->name('masters.dept.store');
Route::delete('masters/dept/{department}', [MasterController::class, 'destroyDepartment'])->name('masters.dept.destroy');
Route::post('masters/desg', [MasterController::class, 'storeDesignation'])->name('masters.desg.store');
Route::delete('masters/desg/{designation}', [MasterController::class, 'destroyDesignation'])->name('masters.desg.destroy');

Route::get('masters/trainers', [MasterController::class, 'showTrainers'])->name('masters.trainers');
Route::get('training-register', [TrainingSessionController::class, 'index'])->name('sessions.index');
Route::post('training-register', [TrainingSessionController::class, 'store'])->name('sessions.store');
Route::get('report/training-card/{user}', [TrainingSessionController::class, 'userReport'])->name('user.training.card');

Route::patch('training-register/{id}/approve', [TrainingSessionController::class, 'approve'])->name('sessions.approve');

Route::get('/trainings/{moduleId}/questions', [QuestionController::class, 'index'])->name('questions.manage');
    // Save the builder
Route::post('/trainings/{moduleId}/questions', [QuestionController::class, 'sync'])->name('questions.sync');


Route::get('/trainings/{id}/trainers', [TrainingModuleController::class, 'manageTrainers'])->name('manage-trainers');
Route::post('/trainings/{id}/trainers', [TrainingModuleController::class, 'saveTrainers'])->name('trainings.save-trainers');

    // 2. Manage Users/Trainees (Simple Enrollment)
Route::get('/trainings/{id}/users', [TrainingModuleController::class, 'manageUsers'])->name('manage-users');
Route::post('/trainings/{id}/users', [TrainingModuleController::class, 'saveUsers'])->name('trainings.save-users');
Route::patch('/trainings/{id}/toggle', [TrainingModuleController::class, 'toggleStatus'])->name('trainings.toggle-status');


Route::get('/exam/list', [UserExamController::class, 'index'])->name('exam.list');
Route::get('/exams/take/{moduleId}', [QuestionController::class, 'takeExam'])->name('exams.take');

Route::post('/exams/submit/{moduleId}', [QuestionController::class, 'submitExam'])->name('exams.submit');

Route::get('/exams/result/{resultId}', [QuestionController::class, 'showResult'])->name('exams.result');
Route::get('/exams/my-history', [QuestionController::class, 'userHistory'])->name('exams.history');

Route::get('/admin/exam-logs', [QuestionController::class, 'adminLogs'])->name('admin.exams.logs');

Route::resource('master-documents', MasterDocumentController::class);


Route::get('/master-documents/{docId}/questions', [MasterQuestionController::class, 'index'])
         ->name('master-questions.index');
         
    Route::post('/master-documents/{docId}/questions/sync', [MasterQuestionController::class, 'sync'])
         ->name('master-questions.sync');


    // --- 3. Module Linking (The "Mink" Logic) ---
    // Connects a Training Module to Master Documents and sets the Quota
    Route::get('/trainings/{moduleId}/link-documents', [ModuleLinkController::class, 'showLinkPage'])
         ->name('admin.modules.linkDocs');
         
    Route::post('/trainings/{moduleId}/save-links', [ModuleLinkController::class, 'saveLinks'])
         ->name('admin.modules.saveLinks');

         Route::get('/admin/exams/logs/{resultId}', [QuestionController::class, 'showExamDetails'])
         ->name('admin.exams.details');

});