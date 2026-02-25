<?php
namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\MedicalQuestionnaire;

class QuestionnaireController extends Controller
{
    public function create(Reservation $reservation)
    {
        return view('company.questionnaire', compact('reservation'));
    }

    public function store(Request $request, Reservation $reservation)
    {
        $request->validate([
            'symptoms' => 'required'
        ]);

        MedicalQuestionnaire::create([
            'reservation_id' => $reservation->id,
            'symptoms' => $request->symptoms,
            'medical_history' => $request->medical_history,
            'is_pregnant' => $request->is_pregnant ? true : false,
        ]);

        return redirect()->route('company.dashboard')
            ->with('success','問診登録完了');
    }
}