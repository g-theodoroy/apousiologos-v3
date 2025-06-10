<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Grade;
use App\Models\Setting;
use App\Services\GradesService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class GradeController extends Controller
{


    public function index(GradesService $gradesService)
    {

        // δημιουργώ το δικό μου validator για να ρυθμίσω τη σελίδα επιστροφής
        // όταν το validation αποτύχει στη Βαθμολογία

        $validator = Validator::make(request()->all(), [
            'a' => 'exists:anathesis,id'
        ]);

        if ($validator->fails()) {
            return redirect('/grades');
        }

        $validated = $validator->validated();

        $selectedAnathesiId = $validated["a"] ?? 0;

        return Inertia::render('Grades', $gradesService->indexCreateData($selectedAnathesiId));
    }


    public function store($selectedAnathesiId = 0)
    {

        $activeGradePeriod = Setting::getValueOf('activeGradePeriod');
        $data = request()->all();

        foreach ($data as $am => $periods) {
            $grade = $periods[$activeGradePeriod] ?? null;
            if ($grade != null) {
                Grade::updateOrCreate([
                    'anathesi_id' => $selectedAnathesiId,
                    'student_id' =>  $am,
                    'period_id' =>  $activeGradePeriod
                ], [
                    'grade' => str_replace(".", ",", $grade),
                ]);
            } else {
                Grade::where('anathesi_id', $selectedAnathesiId)
                    ->where('student_id', $am)
                    ->where('period_id', $activeGradePeriod)
                    ->delete();
            }
        }

        return redirect("grades?a=$selectedAnathesiId")->with(['message' => 'Επιτυχής ενημέρωση.']);
    }
}
