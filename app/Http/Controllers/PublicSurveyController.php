<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Support\Str;

class PublicSurveyController extends Controller
{
    public function index()
    {
        $surveys = Survey::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('public.surveys.index', compact('surveys'));
    }

    public function fill($id)
    {
        $survey = Survey::with('questions')->where('is_active', true)->findOrFail($id);
        
        // Cek validitas waktu
        if ($survey->start_date && $survey->start_date > now()) abort(404);
        if ($survey->end_date && $survey->end_date < now()) abort(404);

        return view('public.surveys.fill', compact('survey'));
    }

    public function submit(Request $request, $id)
    {
        $survey = Survey::with('questions')->where('is_active', true)->findOrFail($id);
        
        // Basic validation rules based on question type
        $rules = [];
        if (!$survey->is_anonymous) {
            $rules['respondent_name'] = 'required|string|max:255';
            $rules['respondent_type'] = 'required|string|max:100';
            // respondent_email optiona/required based on business logic, let's make it optional here
        }

        foreach ($survey->questions as $question) {
            if ($question->is_required) {
                $rules['q_' . $question->id] = 'required';
            }
        }
        
        $request->validate($rules);

        $sessionToken = Str::uuid()->toString();

        foreach ($survey->questions as $question) {
            $answer = $request->input('q_' . $question->id);
            
            // Handle array answers (e.g. checkbox)
            if (is_array($answer)) {
                $answer = json_encode($answer);
            }

            if ($answer !== null) {
                SurveyResponse::create([
                    'survey_id' => $survey->id,
                    'survey_question_id' => $question->id,
                    'respondent_name' => collect($request->input('respondent_name', 'Anonim'))->first(),
                    'respondent_email' => collect($request->input('respondent_email'))->first(),
                    'respondent_type' => collect($request->input('respondent_type'))->first(),
                    'answer' => $answer,
                    'session_token' => $sessionToken,
                ]);
            }
        }

        return redirect()->route('public.surveys.index')->with('success', 'Terima kasih, respons survei Anda telah tersimpan.');
    }
}
