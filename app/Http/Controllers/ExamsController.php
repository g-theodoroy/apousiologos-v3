<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Event;
use App\Services\ExamsService;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\HasMiddleware;


class ExamsController extends Controller implements HasMiddleware
{

    /**

     * Get the middleware that should be assigned to the controller.

     */

    public static function middleware(): array

    {

        return [
            'auth',
        ];
    }

    public function index(ExamsService $examsService)
    {

        // δημιουργώ το δικό μου validator για να ρυθμίσω τη σελίδα επιστροφής
        // όταν το validation αποτύχει στα Διαγωνίσματα

        $validator = Validator::make(request()->all(), [
            'y' => 'numeric|digits:4',
            'm' => 'numeric|min:1|max:12',
            'g' => 'in:0,1'
        ]);

        if ($validator->fails()) {
            return redirect('/exams');
        }

        $validated = $validator->validated();



        return Inertia::render('Exams', $examsService->indexCreateData());
    }


    public function store(ExamsService $examsService)
    {

        if (Carbon::createFromFormat('Y-m-d', request()->date)->isWeekend()) {
            return redirect()->back()->with(['message' => ['error' => 'Δεν επιτρέπεται διαγώνισμα το Σαββατοκύριακο.']]);
        }

        Event::create($examsService->storeCreateData());

        return redirect()->back()->with(['message' => ['success' =>  'Επιτυχής καταχώριση διαγωνίσματος.']]);
    }


    public function update(Event $event, $date, ExamsService $examsService)
    {
        // όχι διαγωνίσματα το Σαββατοκύριακο. Ενεργοποιείται όταν γίνεται με την modal φόρμα αλλαγή ημερομηνίας
        if (Carbon::createFromFormat('Y-m-d', $date)->isWeekend()) {
            return redirect()->back()->with(['message' => ['error' => 'Δεν επιτρέπεται διαγώνισμα το Σαββατοκύριακο.']]);
        }

        // οταν ο διαχειριστής αλλάζει το "ΟΧΙ ΔΙΑΓΩΝΙΣΜΑΤΑ" δεν χρειάζεται έλεγχος
        if ($event->tmima1 == 'ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ') {
            $event->update([
                'date' => Carbon::createFromFormat('Y-m-d', $date)->format('Ymd'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            return redirect()->back()->with(['message' => ['success' =>   'Επιτυχής ενημέρωση ημέρας απαγόρευσης διαγωνισμάτων.']]);
        }

        // γίνεται έλεγχος αν μπορεί να μπει διαγώνισμα
        // αν επιστραφεί $message υπάρχει λάθος - αν επιστραφεί null είναι ok
        $message = $examsService->checkIfUpdateOk($event, $date);

        if ($message) {
            return redirect()->back()->with(['message' => ['error' => $message]]);
        }

        $event->update([
            'date' => Carbon::createFromFormat('Y-m-d', $date)->format('Ymd'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with(['message' => ['success' =>   'Επιτυχής ενημέρωση διαγωνίσματος.']]);
    }


    public function destroy($id)
    {
        Event::where('id', $id)->delete();
        return redirect()->back()->with(['message' => ['success' =>  'Επιτυχής διαγραφή διαγωνίσματος.']]);
    }
}
