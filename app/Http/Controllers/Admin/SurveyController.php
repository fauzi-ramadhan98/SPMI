<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\SurveyQuestion;

class SurveyController extends Controller
{
    public function index()
    {
        $surveys = Survey::withCount(['questions', 'responses'])->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.surveys.index', compact('surveys'));
    }

    public function create()
    {
        return view('admin.surveys.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required',
            'is_anonymous' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $survey = Survey::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_anonymous' => $request->has('is_anonymous'),
            'is_active' => $request->has('is_active'),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.surveys.builder', $survey->id)->with('success', 'Survei berhasil dibuat. Silakan tambahkan pertanyaan.');
    }

    public function builder(Survey $survey)
    {
        $questions = $survey->questions()->orderBy('order')->get();
        return view('admin.surveys.builder', compact('survey', 'questions'));
    }

    public function storeQuestion(Request $request, Survey $survey)
    {
        $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:rating,text,multiple_choice,checkbox',
            'is_required' => 'boolean',
        ]);

        $options = $request->options;
        if (in_array($request->question_type, ['multiple_choice', 'checkbox']) && is_string($options)) {
            $options = array_map('trim', explode(',', $options));
        }

        SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'options' => in_array($request->question_type, ['multiple_choice', 'checkbox']) ? $options : null,
            'order' => $survey->questions()->max('order') + 1,
            'is_required' => $request->has('is_required'),
        ]);

        return redirect()->route('admin.surveys.builder', $survey->id)->with('success', 'Pertanyaan berhasil ditambahkan.');
    }

    public function destroyQuestion(Survey $survey, SurveyQuestion $question)
    {
        $question->delete();
        return redirect()->route('admin.surveys.builder', $survey->id)->with('success', 'Pertanyaan berhasil dihapus.');
    }

    public function destroy(Survey $survey)
    {
        $survey->delete();
        return redirect()->route('admin.surveys.index')->with('success', 'Survei berhasil dihapus.');
    }
}
