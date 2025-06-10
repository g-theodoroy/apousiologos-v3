<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Event;
use App\Models\Grade;
use App\Models\Anathesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Middleware\IsAdministrator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\HasMiddleware;


class TeachersController extends Controller implements HasMiddleware
{

  /**

   * Get the middleware that should be assigned to the controller.

   */

  public static function middleware(): array

  {

    return [
      'auth',
      'web',
      IsAdministrator::class
    ];
  }

  public function index()
  {

    // βρίσκω το id του πρώτου Διαχειριστή (συνήθως 1)
    $firstUserId = User::first()->id;

    // μεταβλητές για το table
    $tableLabels = ['Α/Α', 'Ονοματεπώνυμο', 'Email', 'Τμήμα -> Μάθημα', 'Ενέργεια'];
    $fields = ['', 'name', 'email', 'strAnatheseis', ''];

    // δημιουργώ το δικό μου validator για να ρυθμίσω τη σελίδα επιστροφής
    // όταν το validation αποτύχει στους Καθηγητές
    $validator = Validator::make(request()->all(), [
      'page' => 'int',
      'rows' => 'int',
      'field' => "in:name,email,strAnatheseis",
      'direction' => 'in:asc,desc'
    ]);

    if ($validator->fails()) {
      return redirect('/teachers');
    }

    // φτιάχνω queryStr για να το προσθέσω στα pagination urls στο component Pagination.vue
    $queryStr = '';
    foreach (request()->all() as $key => $value) {
      if ($key == 'page') continue;
      if ($value)  $queryStr .= "&$key=$value";
    }

    // αποθηκεύω τα φίλτρα για να τα γυρίσω πίσω
    $filters = array();
    $filters['page'] = request()->page ?? 1;
    $filters['rows'] = request()->rows ?? 10;
    $filters['search'] = request()->search ?? '';
    $filters['field'] = request()->field ?? '';
    $filters['direction'] = request()->direction;

    // συνένωση στηλών με τον ανάλογο operator ή function (sqlite -> ||, mysql -> CONCAT())
    if (env('DB_CONNECTION') == 'sqlite') {
      $concatStr = "tmima || ' -> ' || mathima";
    } else {
      $concatStr = "CONCAT(tmima , ' -> ' , mathima)";
    }

    // παίρνω τους καθηγητές
    $kathigites = User::with('anatheseis:id,tmima,mathima')->select('id', 'name', 'email', 'role_id', 'strAnatheseis')
      ->leftjoin(DB::raw("
                  (select user_id, GROUP_CONCAT(anathesi, '<br>') AS strAnatheseis
                  from

                  (select user_id, tmima || ' -> ' || mathima as anathesi

                  from anathesis left join anathesi_user
                  on id = anathesi_id

                  order by length(tmima), tmima, mathima)

                  group by user_id)
                  
                  "), 'id', '=', 'user_id');


    // φιλτράρω με ότι ήρθε από την πληκτρολόγηση στο input
    if (request()->search) {
      $searchStr = request()->search ?? '';
      $kathigites = $kathigites->where('lower(name)', 'LIKE',  '%' . $searchStr . '%')
        ->orWhere('name', 'LIKE', '%' . mb_strtoupper($searchStr) . '%')
        ->orWhere('email', 'LIKE', '%' . $searchStr . '%')
        ->orWhere('email', 'LIKE', '%' . mb_strtoupper($searchStr) . '%')
        ->orWhere('strAnatheseis', 'LIKE', '%' . $searchStr . '%')
        ->orWhere('strAnatheseis', 'LIKE', '%' . mb_strtoupper($searchStr) . '%');
    }

    // ταξινόμηση
    if (request()->field) {
      $kathigites = $kathigites->orderby(request()->field, request()->direction);
    } else {
      $kathigites = $kathigites->orderby('name');
      $filters['field'] = 'name';
      $filters['direction'] = 'asc';
    }
    // αν είμαστε στην τελευταία σελίδα πχ 28
    // ξαναπροωθώ στην νέα τελευτάια σελίδα πχ 13
    if (request()->rows) {
      $newPage = ceil(($kathigites->count() / request()->rows));
      if ($newPage < request()->page) {
        return redirect("teachers?page=" . $newPage . $queryStr);
      }
    }

    $kathigites = $kathigites->paginate(request()->rows ?? 10);

    $formRowsCount = 1;
    foreach ($kathigites as $kath) {
      if ($formRowsCount < count($kath->anatheseis)) $formRowsCount = count($kath->anatheseis);
      $sorted = array_values($kath->anatheseis->sortBy('tmima')->toArray());
      unset($kath->anatheseis);
      $kath->anatheseis = $sorted;
    }

    $formRows = intval($formRowsCount / 2) + 1;
    $anatheseis = array();
    for ($i = 0; $i < $formRows * 2; $i++) {
      $anatheseis[$i]['tmima'] = '';
      $anatheseis[$i]['mathima'] = '';
    }

    $formTeachers = [
      'id' => '',
      'name' => '',
      'password' => '',
      'email' => '',
      'role_id' => false,
      'anathesi' => $anatheseis
    ];


    return Inertia::render('Teachers', [
      'firstUserId' => $firstUserId,
      'teachers' => $kathigites,
      'tableLabels' => $tableLabels,
      'filters' => $filters,
      'fields' =>  $fields,
      'queryStr' => $queryStr,
      'formTeachers' => $formTeachers,
      'initFormRows' => $formRows
    ]);
  }

  public function store(Request $request)
  {
    $chkMathimata = false;

    foreach ($request->anathesi as $anathesi) {
      if ($anathesi['mathima']) {
        $chkMathimata = true;
        break;
      }
    }

    //πάιρνω το role_id
    // αν είναι επιλεγμένο το checkbox τότε 1 = Διαχειριστής
    if ($request->role_id) {
      $role =  1;
    } else {
      // αν δεν έχει μαθήματα τότε 3 = Μαθητής
      if (!$chkMathimata) {
        $role =  3;
      } else {
        // αλλιώς καθηγητής
        $role =  2;
      }
    }

    if ($request->id === null) {
      // δημιουργία
      $user = User::updateOrCreate(['email' => trim($request->email)], [
        'name' => trim($request->name),
        'password' => Hash::make(trim($request->password)),
        'role_id' => $role,
      ]);
      $message = "Επιτυχής εισαγωγη καθηγητή.";
    } else {
      // δεν αφήνω τον πρώτο χρήστη που γράφτηκε ως Διαχειριστής να πάψει να είναι
      if ($request->id == User::first()->id) $role = 1;
      // ενημέρωση
      $user = User::find($request->id);
      $user->name = trim($request->name);
      $user->email = trim($request->email);
      $user->role_id = $role;
      if ($request->password) $user->password = Hash::make(trim($request->password));
      $user->save();
      $message = "Επιτυχής ενημέρωση καθηγητή.";
    }
    // κρατάω τα id των υπαρχόντων αναθέσεων
    $oldAnatheseisIds = $user->anatheseis()->pluck("anathesi_id");

    $newAnatheseisIds = [];
    foreach ($request->anathesi as $anathesi) {
      if (trim($anathesi['tmima'])) {
        $newAnathesi = Anathesi::updateOrCreate(['tmima' => trim($anathesi['tmima']), 'mathima' => trim($anathesi['mathima'])], [
          'tmima' => trim($anathesi['tmima']),
          'mathima' => trim($anathesi['mathima']),
        ]);
        // βάζω τα νέα ή ενημερωμένα id σε πίνακα
        $newAnatheseisIds[] = $newAnathesi->id;
      }
    }

    // συγχρονίζω τις αναθέσεις του χρήστη
    $user->anatheseis()->sync($newAnatheseisIds);

    // ελέγχω αν οι παλιές αναθέσεις έχουν χρήστες ή / και βαθμούς
    // αν ΔΕΝ έχουν χρήστη και ΔΕΝ έχουν καταχωριστεί βαθμοί διαγράφεται η ανάθεση
    foreach ($oldAnatheseisIds as $anathesi_id) {
      $anathesi = Anathesi::find($anathesi_id);
      if (!$anathesi->users()->count()) {
        if (!Grade::where('anathesi_id', $anathesi_id)->count()) {
          $anathesi->delete();
        }
      }
    }
    return redirect()->back()->with(['message' => $message]);
  }

  public function delete($id)
  {
    // παίρνω τον χρήστη
    $user = User::find($id);
    // παίρνω τις αναθέσεις του χρήστη
    $anathesi_ids = $user->anatheseis()->pluck("anathesi_id");
    // αποσυνδέω τον χρήστη από όλες τις αναθέσεις
    $user->anatheseis()->detach();
    // ελέγχω για όλες τις αναθέσεις που είχε ο χρήστης
    // αν ΔΕΝ έχουν χρήστη και ΔΕΝ έχουν καταχωριστεί βαθμοί διαγράφεται η ανάθεση
    foreach ($anathesi_ids as $anathesi_id) {
      $anathesi = Anathesi::find($anathesi_id);
      if (!$anathesi->users()->count()) {
        if (!Grade::where('anathesi_id', $anathesi_id)->count()) {
          $anathesi->delete();
        }
      }
    }
    // διαγράφω το χρήστη
    $user->delete();
    // διαγράφω τα διαγωνίσματα
    Event::where('user_id', $id)->delete();
    return redirect()->back()->with(['message' => 'Επιτυχής διαγραφή καθηγητή.']);
  }

  // Το χρησιμοποιώ με axios.get στο Teachers.vue
  // για να διαπιστώσω άν το email είναι ελεύθερο για νέα εγγραφή 
  public function uniqueEmail($email)
  {
    return User::where('email', $email)->first() ? 1 : 0;
  }
}
