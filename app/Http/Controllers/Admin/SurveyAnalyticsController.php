<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Survey;

class SurveyAnalyticsController extends Controller
{
    public function show(Survey $survey)
    {
        $survey->load('questions.responses');

        $totalResponses = $survey->responses()->distinct('session_token')->count('session_token');

        $analytics = [];
        $ikm = null; // Indeks Kepuasan Masyarakat

        $totalRatingScores = 0;
        $totalRatingQuestions = 0;

        foreach ($survey->questions as $question) {
            $responses = $question->responses;
            $data = [];

            if ($question->question_type == 'rating') {
                $sums = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                foreach ($responses as $response) {
                    $val = (int)$response->answer;
                    if (isset($sums[$val])) {
                        $sums[$val]++;
                        $totalRatingScores += $val;
                    }
                }
                $data = $sums;
                if ($responses->count() > 0) $totalRatingQuestions++;
            } elseif (in_array($question->question_type, ['multiple_choice', 'checkbox'])) {
                // Count frequencies
                $counts = [];
                foreach ($responses as $response) {
                    $answer = $response->answer;
                    if ($question->question_type == 'checkbox') {
                        $arr = json_decode($answer, true) ?: [];
                        foreach ($arr as $item) {
                            $counts[$item] = ($counts[$item] ?? 0) + 1;
                        }
                    } else {
                        $counts[$answer] = ($counts[$answer] ?? 0) + 1;
                    }
                }
                $data = $counts;
            } else {
                // Text answers
                $data = $responses->take(10); // Show recent 10 text answers
            }

            $analytics[$question->id] = $data;
        }

        // Hitung IKM sederhana (jika menggunakan skala 1-4)
        if ($totalRatingQuestions > 0 && $totalResponses > 0) {
            // Rata-rata nilai per unsur / total unsur dirata-rata ke responden
            $averageScore = $totalRatingScores / ($totalResponses * $totalRatingQuestions);
            // Konversi ke IKM (x 25)
            $ikm = number_format($averageScore * 25, 2);
        }

        return view('admin.surveys.analytics', compact('survey', 'totalResponses', 'analytics', 'ikm'));
    }
}
