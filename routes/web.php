<?php

use Inertia\Inertia;
use App\Models\Setting;
use App\Services\CheckUpdates;
use App\Services\ExamsService;
use App\Services\GradesService;
use App\Http\Middleware\IsTeacher;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Services\ApousiologosService;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AllowAnathesi;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use App\Http\Middleware\DateBackAllowed;
use App\Http\Middleware\IsAdministrator;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ExamsController;
use App\Http\Controllers\GradeController;
use App\Http\Middleware\RedirectAfterLogin;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\AdminGradesController;
use App\Http\Controllers\AdminProgramController;
use App\Http\Controllers\ApousiologosController;
use App\Http\Controllers\AdminStudentsController;
use App\Http\Controllers\AdminKathigitesController;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;
use App\Http\Controllers\AdminMyschoolApousiesController;


Route::get('/', function () {

    if (Schema::hasTable('settings')) {
        $isTeacher = Auth::user() ? Auth::user()->permissions['teacher'] : false;
        $isStudent = Auth::user() ? Auth::user()->permissions['student'] : false;
        // βρίσκω την αρχική σελίδα
        $landingPage = Setting::getValueOf('landingPage');
        if ($isStudent) {
            $landingPage = 'apousies';
        } elseif ($isTeacher) {
            if ($landingPage == 'exams') {
                $allowExams = Setting::getValueOf('allowExams') == '1' ?? false;
                if (!$allowExams) $landingPage = 'apousies';
            }
            if ($landingPage == 'grades') {
                $activeGradePeriod = Setting::getValueOf('activeGradePeriod') <> 0 ?? false;
                if (!$activeGradePeriod) $landingPage = 'apousies';
            }
        }
    } else {
        $landingPage = 'apousies';
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'welcome' => URL::route('welcome'),
        'landingPage' => $landingPage,
    ]);
})->name('welcome');



Route::get('/apousies', [ApousiologosController::class, 'index'])->middleware(['auth', 'verified', RedirectAfterLogin::class, DateBackAllowed::class])->name('apousies');
Route::post('/apousies/store/{selectedTmima?}/{date?}', [ApousiologosController::class, 'store'])->middleware(['auth', 'verified'])->name('apousies.store');

Route::get('/exportApouxls', [ApousiologosController::class, 'exportApousiesXls'])->middleware(['auth', 'verified', IsAdministrator::class])->name('exportApouxls');

Route::get('/emailParent/{am?}/{date?}/{tmima?}', function ($am, $date, $tmima = null) {
    return (new ApousiologosService)->sendEmailToParent($am, $date, $tmima);
})->middleware(['auth', 'verified'])->name('emailParent');



Route::get('/exams', [ExamsController::class, 'index'])->middleware(['auth', 'verified', IsTeacher::class])->name('exams');
Route::post('/exams/store', [ExamsController::class, 'store'])->middleware(['auth', 'verified', IsTeacher::class])->name('exams.store');
Route::put('/exams/update/{event}/{date}', [ExamsController::class, 'update'])->middleware(['auth', 'verified', IsTeacher::class])->name('exams.update');
Route::delete('/deleteExam/{id}', [ExamsController::class, 'destroy'])->middleware(['auth', 'verified', IsTeacher::class])->name('deleteExam');

Route::get('/exams/tmimata/{date}', function ($date) {
    return (new ExamsService)->tmimata($date);
})->middleware(['auth', 'verified', IsTeacher::class])->name('tmimata');
Route::get('/userExams', function () {
    return (new ExamsService)->userExams();
})->middleware(['auth', 'verified', IsTeacher::class])->name('userExams');
Route::get('/exportExamsXls', function () {
    return (new ExamsService)->exportExamsXls();
})->middleware(['auth', 'verified', IsAdministrator::class])->name('exportExamsXls');



Route::get('/grades', [GradeController::class, 'index'])->middleware(['auth', 'verified', IsTeacher::class, AllowAnathesi::class])->name('grades');
Route::post('/grades/store/{selectedAnathesiId?}', [GradeController::class, 'store'])->middleware(['auth', 'verified', IsTeacher::class])->name('grades.store');

Route::get('/insertedGrades/{status?}', function ($status = null) {
    return (new GradesService)->insertedGrades($status);
})->where('status', '[0-1]')->middleware(['auth', 'verified', IsTeacher::class])->name('insertedGrades');

Route::get('/insertedGradesStudents', function () {
    return (new GradesService)->insertedGradesStudents();
})->middleware(['auth', 'verified', IsTeacher::class])->name('insertedGradesStudents');



Route::get('/teachers', [TeachersController::class, 'index'])->middleware(['auth', 'verified',  IsAdministrator::class])->name('teachers');
Route::post('/teachers/store', [TeachersController::class, 'store'])->middleware(['auth', 'verified',  IsAdministrator::class])->name('teachers.store');
Route::get('/uniqueEmail/{email}', [TeachersController::class, 'uniqueEmail'])->middleware(['auth', 'verified',  IsTeacher::class])->name('uniqueEmail');
Route::delete('/deleteTeacher/{id}', [TeachersController::class, 'delete'])->middleware(['auth', 'verified',  IsAdministrator::class])->name('deleteTeacher');



Route::get('/students', [StudentsController::class, 'index'])->middleware(['auth', 'verified',  IsTeacher::class])->name('students');
Route::post('/students/store', [StudentsController::class, 'store'])->middleware(['auth', 'verified',  IsTeacher::class])->name('students.store');
Route::get('/studentUnique/{id}', [StudentsController::class, 'studentUnique'])->middleware(['auth', 'verified',  IsTeacher::class])->name('studentUnique');
Route::delete('/studentDelete/{id}', [StudentsController::class, 'delete'])->middleware(['auth', 'verified',  IsTeacher::class])->name('studentDelete');

Route::post('/apousiesStore', [StudentsController::class, 'apousiesStore'])->middleware(['auth', 'verified', IsTeacher::class])->name('apousiesStore');
Route::delete('/apousiesDelete/{id}', [StudentsController::class, 'apousiesDelete'])->middleware(['auth', 'verified', IsTeacher::class])->name('apousiesDelete');



Route::get('settings', [AdminController::class, 'index'])->middleware(['auth', 'verified', IsAdministrator::class])->name('settings');
Route::post('settings/store', [AdminController::class, 'store'])->middleware(['auth', 'verified', IsAdministrator::class])->name('settings.store');
Route::get('importXls', [AdminController::class, 'importXls'])->middleware(['auth', 'verified', IsAdministrator::class])->name('importXls');
Route::get('exportXls', [AdminController::class, 'exportXls'])->middleware(['auth', 'verified', IsAdministrator::class])->name('exportXls');


Route::post('/importKathigites', [AdminKathigitesController::class, 'import'])->middleware(['auth', 'verified', IsAdministrator::class])->name('importKathigites');
Route::get('/exportKathXls', [AdminKathigitesController::class, 'export'])->middleware(['auth', 'verified', IsAdministrator::class])->name('exportKathXls');
Route::delete('/delKath', [AdminKathigitesController::class, 'delete'])->middleware(['auth', 'verified', IsAdministrator::class])->name('delKath');


Route::post('/importStudents', [AdminStudentsController::class, 'import'])->middleware(['auth', 'verified', IsAdministrator::class])->name('importStudents');
Route::get('/exportStudXls', [AdminStudentsController::class, 'export'])->middleware(['auth', 'verified', IsAdministrator::class])->name('exportStudXls');
Route::delete('/delStud', [AdminStudentsController::class, 'delete'])->middleware(['auth', 'verified', IsAdministrator::class])->name('delStud');


Route::post('/importProgram', [AdminProgramController::class, 'import'])->middleware(['auth', 'verified', IsAdministrator::class])->name('importProgram');
Route::get('/exportProgXls', [AdminProgramController::class, 'export'])->middleware(['auth', 'verified', IsAdministrator::class])->name('exportProgXls');
Route::delete('/delProg', [AdminProgramController::class, 'delete'])->middleware(['auth', 'verified', IsAdministrator::class])->name('delProg');


Route::post('/insertGradesToDB', [AdminGradesController::class, 'import'])->middleware(['auth', 'verified', IsAdministrator::class])->name('insertGradesToDB');
Route::get('/gradesXls', [AdminGradesController::class, 'export'])->middleware(['auth', 'verified', IsAdministrator::class])->name('gradesXls');
Route::post('/populateGradesXls', [AdminGradesController::class, 'update'])->middleware(['auth', 'verified', IsAdministrator::class])->name('populateGradesXls');


Route::post('/importMyschoolApousies', [AdminMyschoolApousiesController::class, 'import'])->middleware(['auth', 'verified', IsAdministrator::class])->name('importMyschoolApousies');
Route::get('/exportMyschoolApousies', [AdminMyschoolApousiesController::class, 'export'])->middleware(['auth', 'verified', IsAdministrator::class])->name('exportMyschoolApousies');



Route::get('about', function () {
    // return Inertia::render('Dashboard');
    return Inertia::render('About', [
        'infoUrl' => asset('files/Οδηγίες ρύθμισης κ χρήσης Ηλ.Απουσιολόγου.pdf')
    ]);
})->name('about');



Route::get('/logs', [LogViewerController::class, 'index'])->middleware(['auth', 'verified', IsAdministrator::class])->name('logs');



Route::get('/setUpdated/{sha}', function ($sha) {
    return (new CheckUpdates)->setUpdated($sha);
})->middleware(['auth', 'verified', IsAdministrator::class]);


require __DIR__ . '/auth.php';

Route::fallback(function () {
    return redirect()->route('welcome');
});
